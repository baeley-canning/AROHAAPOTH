<?php
function format_money($amount, $currency)
{
    $value = number_format(((int)$amount) / 100, 2);
    $code = strtoupper((string)$currency);
    return $code . ' ' . $value;
}

function build_origin()
{
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '') {
        return '';
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $host;
}

function build_base_path()
{
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $basePath = preg_replace('#/stripe$#', '', $scriptDir);
    return rtrim($basePath, '/');
}

function smtp_read_response($socket)
{
    $data = '';
    while ($line = fgets($socket, 515)) {
        $data .= $line;
        if (preg_match('/^\d{3} /', $line)) {
            break;
        }
    }
    return $data;
}

function smtp_expect($socket, $codes)
{
    $response = smtp_read_response($socket);
    foreach ((array)$codes as $code) {
        if (strpos($response, (string)$code) === 0) {
            return true;
        }
    }
    return false;
}

function smtp_command($socket, $command, $expectCodes)
{
    fwrite($socket, $command . "\r\n");
    return smtp_expect($socket, $expectCodes);
}

function smtp_send($host, $port, $secure, $username, $password, $from, $to, $subject, $body)
{
    $transport = '';
    $secure = strtolower($secure);
    if ($secure === 'ssl') {
        $transport = 'ssl://';
    }
    $socket = fsockopen($transport . $host, $port, $errno, $errstr, 20);
    if (!$socket) {
        return false;
    }

    if (!smtp_expect($socket, 220)) {
        fclose($socket);
        return false;
    }

    if (!smtp_command($socket, 'EHLO localhost', 250)) {
        fclose($socket);
        return false;
    }

    if ($secure === 'tls') {
        if (!smtp_command($socket, 'STARTTLS', 220)) {
            fclose($socket);
            return false;
        }
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            return false;
        }
        if (!smtp_command($socket, 'EHLO localhost', 250)) {
            fclose($socket);
            return false;
        }
    }

    if ($username !== '' && $password !== '') {
        if (!smtp_command($socket, 'AUTH LOGIN', 334)) {
            fclose($socket);
            return false;
        }
        if (!smtp_command($socket, base64_encode($username), 334)) {
            fclose($socket);
            return false;
        }
        if (!smtp_command($socket, base64_encode($password), 235)) {
            fclose($socket);
            return false;
        }
    }

    if (!smtp_command($socket, 'MAIL FROM:<' . $from . '>', 250)) {
        fclose($socket);
        return false;
    }

    if (!smtp_command($socket, 'RCPT TO:<' . $to . '>', [250, 251])) {
        fclose($socket);
        return false;
    }

    if (!smtp_command($socket, 'DATA', 354)) {
        fclose($socket);
        return false;
    }

    $headers = [
        'From: Aroha Apothecary <' . $from . '>',
        'To: <' . $to . '>',
        'Subject: ' . $subject,
        'Reply-To: ' . $from,
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
    ];
    $message = implode("\r\n", $headers) . "\r\n\r\n" . $body;
    $message = str_replace("\n.", "\n..", $message);
    fwrite($socket, $message . "\r\n.\r\n");

    if (!smtp_expect($socket, 250)) {
        fclose($socket);
        return false;
    }

    smtp_command($socket, 'QUIT', 221);
    fclose($socket);
    return true;
}

function send_order_notification($pdo, $orderRef)
{
    $notifyEmail = '';
    if (defined('ORDER_NOTIFICATION_EMAIL')) {
        $notifyEmail = trim((string)ORDER_NOTIFICATION_EMAIL);
    }
    if ($notifyEmail === '') {
        $notifyEmail = 'hello@arohaapothecary.com';
    }
    if (!filter_var($notifyEmail, FILTER_VALIDATE_EMAIL)) {
        return;
    }

    $stmt = $pdo->prepare('SELECT * FROM orders WHERE order_ref = ? LIMIT 1');
    $stmt->execute([$orderRef]);
    $order = $stmt->fetch();
    if (!$order || !empty($order['admin_notified_at'])) {
        return;
    }

    $itemStmt = $pdo->prepare('SELECT product_name, quantity, unit_amount, line_total FROM order_items WHERE order_id = ?');
    $itemStmt->execute([(int)$order['id']]);
    $items = $itemStmt->fetchAll();

    $lines = [];
    $lines[] = 'New order received.';
    $lines[] = '';
    $lines[] = 'Order reference: ' . $order['order_ref'];
    if (!empty($order['status'])) {
        $lines[] = 'Status: ' . $order['status'];
    }
    if (!empty($order['payment_status'])) {
        $lines[] = 'Payment status: ' . $order['payment_status'];
    }
    $lines[] = 'Total: ' . format_money($order['amount_total'] ?? 0, $order['currency'] ?? 'NZD');

    if (!empty($order['customer_name'])) {
        $lines[] = 'Customer: ' . $order['customer_name'];
    }
    if (!empty($order['email'])) {
        $lines[] = 'Email: ' . $order['email'];
    }
    if (!empty($order['customer_phone'])) {
        $lines[] = 'Phone: ' . $order['customer_phone'];
    }
    if (!empty($order['shipping_address'])) {
        $lines[] = '';
        $lines[] = "Shipping address:\n" . $order['shipping_address'];
    }

    if ($items) {
        $lines[] = '';
        $lines[] = 'Items:';
        foreach ($items as $item) {
            $name = $item['product_name'] ?? 'Item';
            $qty = (int)($item['quantity'] ?? 0);
            $unit = format_money($item['unit_amount'] ?? 0, $order['currency'] ?? 'NZD');
            $line = format_money($item['line_total'] ?? 0, $order['currency'] ?? 'NZD');
            $lines[] = '- ' . $name . ' x' . $qty . ' (' . $unit . ') = ' . $line;
        }
    }

    $origin = build_origin();
    $basePath = build_base_path();
    if ($origin !== '') {
        $lines[] = '';
        $lines[] = 'View order: ' . $origin . $basePath . '/admin/order.php?ref=' . urlencode($order['order_ref']);
    }

    $subject = 'New order ' . $order['order_ref'] . ' - ' . format_money($order['amount_total'] ?? 0, $order['currency'] ?? 'NZD');
    $fromEmail = $notifyEmail;
    if (defined('ORDER_FROM_EMAIL') && trim((string)ORDER_FROM_EMAIL) !== '') {
        $fromEmail = trim((string)ORDER_FROM_EMAIL);
    }
    $safeFrom = preg_replace('/[^a-zA-Z0-9@._+-]/', '', $fromEmail);
    if (!filter_var($safeFrom, FILTER_VALIDATE_EMAIL)) {
        $safeFrom = $notifyEmail;
    }

    $sent = false;
    if (defined('SMTP_HOST') && trim((string)SMTP_HOST) !== '') {
        $smtpHost = trim((string)SMTP_HOST);
        $smtpPort = defined('SMTP_PORT') ? (int)SMTP_PORT : 465;
        $smtpSecure = defined('SMTP_SECURE') ? trim((string)SMTP_SECURE) : 'ssl';
        $smtpUser = defined('SMTP_USER') ? trim((string)SMTP_USER) : '';
        $smtpPass = defined('SMTP_PASS') ? (string)SMTP_PASS : '';
        $sent = smtp_send($smtpHost, $smtpPort, $smtpSecure, $smtpUser, $smtpPass, $safeFrom, $notifyEmail, $subject, implode("\n", $lines));
    }

    if (!$sent) {
        if (function_exists('ini_set')) {
            @ini_set('sendmail_from', $safeFrom);
        }
        $headers = [
            'From: Aroha Apothecary <' . $safeFrom . '>',
            'Reply-To: ' . $safeFrom,
            'Return-Path: <' . $safeFrom . '>',
            'Content-Type: text/plain; charset=UTF-8',
        ];
        $extra = '-f ' . $safeFrom;
        $sent = @mail($notifyEmail, $subject, implode("\n", $lines), implode("\r\n", $headers), $extra);
    }

    if ($sent) {
        try {
            $updateStmt = $pdo->prepare('UPDATE orders SET admin_notified_at = NOW() WHERE order_ref = ?');
            $updateStmt->execute([$order['order_ref']]);
        } catch (Throwable $error) {
            // Ignore update failure; email already sent.
        }
    }
}
