<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../_shared/db.php';

$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

$configPath = dirname(__DIR__) . '/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}
require_once __DIR__ . '/../_shared/notify.php';

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
            $customerEmail = $customer['email'] ?? ($session['customer_email'] ?? '');

            $stmt = $pdo->prepare('UPDATE orders SET status = ?, stripe_session_id = ?, stripe_payment_intent_id = ?, email = ?, amount_total = ?, currency = ?, customer_name = ?, customer_phone = ?, shipping_address = ?, payment_status = ? WHERE order_ref = ?');
            $stmt->execute([
                $status,
                $session['id'] ?? '',
                $session['payment_intent'] ?? '',
                $customerEmail,
                $session['amount_total'] ?? 0,
                $session['currency'] ?? 'nzd',
                $customer['name'] ?? ($shipping['name'] ?? ''),
                $customer['phone'] ?? '',
                format_address($address),
                $paymentStatus,
                $orderRef,
            ]);
            send_order_notification($pdo, $orderRef);
            if ($paymentStatus === 'paid') {
                send_customer_notification($pdo, $orderRef, 'paid');
            }
        }
        break;
    case 'checkout.session.expired':
        $session = $event['data']['object'] ?? [];
        $orderRef = $session['client_reference_id'] ?? '';
        if ($orderRef !== '') {
            $customer = $session['customer_details'] ?? [];
            $customerEmail = $customer['email'] ?? ($session['customer_email'] ?? '');
            if ($customerEmail !== '') {
                $stmt = $pdo->prepare('UPDATE orders SET status = ?, payment_status = ?, email = ? WHERE order_ref = ?');
                $stmt->execute(['expired', 'expired', $customerEmail, $orderRef]);
            } else {
                $stmt = $pdo->prepare('UPDATE orders SET status = ?, payment_status = ? WHERE order_ref = ?');
                $stmt->execute(['expired', 'expired', $orderRef]);
            }
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
