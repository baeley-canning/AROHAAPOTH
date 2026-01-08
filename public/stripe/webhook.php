<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../_shared/db.php';

$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

$configPath = dirname(__DIR__) . '/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

if (!defined('STRIPE_WEBHOOK_SECRET') || STRIPE_WEBHOOK_SECRET === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Webhook secret not configured.']);
    exit;
}

if ($sigHeader === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing signature header.']);
    exit;
}

$parts = [];
foreach (explode(',', $sigHeader) as $part) {
    $pair = explode('=', $part, 2);
    if (count($pair) === 2) {
        $parts[trim($pair[0])] = trim($pair[1]);
    }
}

$timestamp = $parts['t'] ?? '';
$signature = $parts['v1'] ?? '';
if ($timestamp === '' || $signature === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature header.']);
    exit;
}

$signedPayload = $timestamp . '.' . $payload;
$expected = hash_hmac('sha256', $signedPayload, STRIPE_WEBHOOK_SECRET);

if (!hash_equals($expected, $signature)) {
    http_response_code(400);
    echo json_encode(['error' => 'Signature verification failed.']);
    exit;
}

$tolerance = 300;
if (abs(time() - (int)$timestamp) > $tolerance) {
    http_response_code(400);
    echo json_encode(['error' => 'Webhook timestamp too old.']);
    exit;
}

$event = json_decode($payload, true);
if (!is_array($event) || empty($event['type'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload.']);
    exit;
}

$pdo = db();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Database not configured.']);
    exit;
}

function format_address($address)
{
    if (!is_array($address)) {
        return '';
    }

    $lines = [];
    $line1 = trim((string)($address['line1'] ?? ''));
    $line2 = trim((string)($address['line2'] ?? ''));
    if ($line1 !== '') {
        $lines[] = $line1;
    }
    if ($line2 !== '') {
        $lines[] = $line2;
    }

    $city = trim((string)($address['city'] ?? ''));
    $state = trim((string)($address['state'] ?? ''));
    $postal = trim((string)($address['postal_code'] ?? ''));
    $cityLine = trim(implode(' ', array_filter([$city, $state, $postal])));
    if ($cityLine !== '') {
        $lines[] = $cityLine;
    }

    $country = trim((string)($address['country'] ?? ''));
    if ($country !== '') {
        $lines[] = $country;
    }

    return implode("\n", $lines);
}

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
    $sent = false;
    if (defined('SMTP_HOST') && trim((string)SMTP_HOST) !== '') {
        $smtpHost = trim((string)SMTP_HOST);
        $smtpPort = defined('SMTP_PORT') ? (int)SMTP_PORT : 465;
        $smtpSecure = defined('SMTP_SECURE') ? trim((string)SMTP_SECURE) : 'ssl';
        $smtpUser = defined('SMTP_USER') ? trim((string)SMTP_USER) : '';
        $smtpPass = defined('SMTP_PASS') ? (string)SMTP_PASS : '';
        $sent = smtp_send($smtpHost, $smtpPort, $smtpSecure, $smtpUser, $smtpPass, $fromEmail, $notifyEmail, $subject, implode("\n", $lines));
    }

    if (!$sent) {
        $headers = [
            'From: Aroha Apothecary <' . $fromEmail . '>',
            'Reply-To: ' . $fromEmail,
            'Content-Type: text/plain; charset=UTF-8',
        ];
        $sent = @mail($notifyEmail, $subject, implode("\n", $lines), implode("\r\n", $headers));
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

switch ($event['type']) {
    case 'checkout.session.completed':
        $session = $event['data']['object'] ?? [];
        $orderRef = $session['client_reference_id'] ?? '';
        if ($orderRef !== '') {
            $customer = $session['customer_details'] ?? [];
            $shipping = $session['shipping_details'] ?? [];
            $address = $customer['address'] ?? ($shipping['address'] ?? []);
            $paymentStatus = $session['payment_status'] ?? '';
            $status = $paymentStatus === 'paid' ? 'paid' : ($paymentStatus ?: 'pending');

            $stmt = $pdo->prepare('UPDATE orders SET status = ?, stripe_session_id = ?, stripe_payment_intent_id = ?, email = ?, amount_total = ?, currency = ?, customer_name = ?, customer_phone = ?, shipping_address = ?, payment_status = ? WHERE order_ref = ?');
            $stmt->execute([
                $status,
                $session['id'] ?? '',
                $session['payment_intent'] ?? '',
                $customer['email'] ?? '',
                $session['amount_total'] ?? 0,
                $session['currency'] ?? 'nzd',
                $customer['name'] ?? ($shipping['name'] ?? ''),
                $customer['phone'] ?? '',
                format_address($address),
                $paymentStatus,
                $orderRef,
            ]);
            send_order_notification($pdo, $orderRef);
        }
        break;
    case 'checkout.session.expired':
        $session = $event['data']['object'] ?? [];
        $orderRef = $session['client_reference_id'] ?? '';
        if ($orderRef !== '') {
            $stmt = $pdo->prepare('UPDATE orders SET status = ?, payment_status = ? WHERE order_ref = ?');
            $stmt->execute(['expired', 'expired', $orderRef]);
            send_order_notification($pdo, $orderRef);
        } elseif (!empty($session['id'])) {
            $stmt = $pdo->prepare('UPDATE orders SET status = ?, payment_status = ? WHERE stripe_session_id = ?');
            $stmt->execute(['expired', 'expired', $session['id']]);
            $refStmt = $pdo->prepare('SELECT order_ref FROM orders WHERE stripe_session_id = ? LIMIT 1');
            $refStmt->execute([$session['id']]);
            $row = $refStmt->fetch();
            if (!empty($row['order_ref'])) {
                send_order_notification($pdo, $row['order_ref']);
            }
        }
        break;
    case 'payment_intent.payment_failed':
        $intent = $event['data']['object'] ?? [];
        $paymentIntentId = $intent['id'] ?? '';
        $metadata = $intent['metadata'] ?? [];
        $orderRef = $metadata['order_ref'] ?? '';
        if ($orderRef !== '') {
            $stmt = $pdo->prepare('UPDATE orders SET status = ?, payment_status = ?, stripe_payment_intent_id = ? WHERE order_ref = ?');
            $stmt->execute(['failed', 'failed', $paymentIntentId, $orderRef]);
            send_order_notification($pdo, $orderRef);
        } elseif ($paymentIntentId !== '') {
            $stmt = $pdo->prepare('UPDATE orders SET status = ?, payment_status = ? WHERE stripe_payment_intent_id = ?');
            $stmt->execute(['failed', 'failed', $paymentIntentId]);
            $refStmt = $pdo->prepare('SELECT order_ref FROM orders WHERE stripe_payment_intent_id = ? LIMIT 1');
            $refStmt->execute([$paymentIntentId]);
            $row = $refStmt->fetch();
            if (!empty($row['order_ref'])) {
                send_order_notification($pdo, $row['order_ref']);
            }
        }
        break;
    case 'charge.refunded':
        $charge = $event['data']['object'] ?? [];
        $paymentIntentId = $charge['payment_intent'] ?? '';
        if ($paymentIntentId !== '') {
            $amountRefunded = (int)($charge['amount_refunded'] ?? 0);
            $stmt = $pdo->prepare('UPDATE orders SET status = ?, amount_refunded = ?, refund_status = ?, payment_status = ? WHERE stripe_payment_intent_id = ?');
            $stmt->execute(['refunded', $amountRefunded, 'refunded', 'refunded', $paymentIntentId]);
        }
        break;
    case 'charge.refund.updated':
        $refund = $event['data']['object'] ?? [];
        $paymentIntentId = $refund['payment_intent'] ?? '';
        if ($paymentIntentId !== '') {
            $refundStatus = $refund['status'] ?? '';
            $status = 'refunding';
            if ($refundStatus === 'succeeded') {
                $status = 'refunded';
            } elseif ($refundStatus === 'failed' || $refundStatus === 'canceled') {
                $status = 'failed';
            }
            $amountRefunded = (int)($refund['amount'] ?? 0);
            $stmt = $pdo->prepare('UPDATE orders SET status = ?, amount_refunded = ?, refund_status = ? WHERE stripe_payment_intent_id = ?');
            $stmt->execute([$status, $amountRefunded, $refundStatus, $paymentIntentId]);
        }
        break;
}

echo json_encode(['received' => true]);
