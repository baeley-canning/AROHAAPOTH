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

if ($event['type'] === 'checkout.session.completed') {
    $session = $event['data']['object'] ?? [];
    $orderRef = $session['client_reference_id'] ?? '';
    if ($orderRef !== '') {
        $stmt = $pdo->prepare('UPDATE orders SET status = ?, stripe_session_id = ?, stripe_payment_intent_id = ?, email = ?, amount_total = ?, currency = ? WHERE order_ref = ?');
        $stmt->execute([
            'paid',
            $session['id'] ?? '',
            $session['payment_intent'] ?? '',
            $session['customer_details']['email'] ?? '',
            $session['amount_total'] ?? 0,
            $session['currency'] ?? 'nzd',
            $orderRef,
        ]);
    }
}

echo json_encode(['received' => true]);
