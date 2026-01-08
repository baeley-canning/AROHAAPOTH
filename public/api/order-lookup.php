<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../_shared/db.php';

function respond($code, $message) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

function client_ip()
{
    $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($forwarded) {
        $parts = explode(',', $forwarded);
        $ip = trim($parts[0]);
        if ($ip !== '') {
            return $ip;
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function rate_limit($key, $limit, $windowSeconds)
{
    $safeKey = preg_replace('/[^a-zA-Z0-9_\-:.]/', '_', $key);
    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'aroha_order_lookup_' . sha1($safeKey) . '.json';
    $now = time();
    $data = [
        'count' => 0,
        'reset' => $now + $windowSeconds,
    ];

    if (file_exists($path)) {
        $decoded = json_decode((string)file_get_contents($path), true);
        if (is_array($decoded)) {
            $data = array_merge($data, $decoded);
        }
    }

    if (!isset($data['reset']) || $data['reset'] <= $now) {
        $data['count'] = 0;
        $data['reset'] = $now + $windowSeconds;
    }

    $data['count'] = (int)$data['count'] + 1;
    file_put_contents($path, json_encode($data));

    $allowed = $data['count'] <= $limit;
    $retryAfter = max(0, (int)$data['reset'] - $now);

    return [$allowed, $retryAfter];
}

function verify_turnstile($secret, $token, $remoteIp)
{
    if ($secret === '' || $token === '') {
        return false;
    }

    $payload = http_build_query([
        'secret' => $secret,
        'response' => $token,
        'remoteip' => $remoteIp,
    ]);

    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
    ]);

    $response = curl_exec($ch);
    if ($response === false) {
        curl_close($ch);
        return false;
    }
    curl_close($ch);

    $decoded = json_decode($response, true);
    return is_array($decoded) && !empty($decoded['success']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, 'Method not allowed.');
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$honeypot = trim($payload['company'] ?? '');
if ($honeypot !== '') {
    respond(400, 'Invalid request.');
}

$ip = client_ip();
[$allowed, $retryAfter] = rate_limit($ip, 10, 600);
if (!$allowed) {
    respond(429, 'Too many requests. Try again in ' . ceil($retryAfter / 60) . ' minutes.');
}

$configPath = dirname(__DIR__) . '/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

$turnstileSecret = '';
if (defined('TURNSTILE_SECRET_KEY')) {
    $turnstileSecret = TURNSTILE_SECRET_KEY;
} else {
    $turnstileSecret = getenv('TURNSTILE_SECRET_KEY') ?: '';
}

if ($turnstileSecret !== '') {
    $token = trim($payload['turnstileToken'] ?? '');
    if (!verify_turnstile($turnstileSecret, $token, $ip)) {
        respond(400, 'CAPTCHA verification failed.');
    }
}

$orderRef = strtoupper(trim($payload['orderRef'] ?? ''));
$email = strtolower(trim($payload['email'] ?? ''));

if ($orderRef === '' || $email === '') {
    respond(400, 'Order reference and email are required.');
}

$pdo = db();
if (!$pdo) {
    respond(500, 'Database not configured.');
}

$stmt = $pdo->prepare('SELECT * FROM orders WHERE order_ref = ? LIMIT 1');
$stmt->execute([$orderRef]);
$order = $stmt->fetch();
if (!$order) {
    respond(404, 'Order not found.');
}

$storedEmail = strtolower(trim((string)($order['email'] ?? '')));
if ($storedEmail !== '' && $storedEmail !== $email) {
    respond(403, 'Email does not match this order. Use the email from your Stripe receipt.');
}

$itemsStmt = $pdo->prepare('SELECT product_name, quantity, unit_amount, line_total FROM order_items WHERE order_id = ?');
$itemsStmt->execute([$order['id']]);
$items = $itemsStmt->fetchAll();

echo json_encode([
    'orderRef' => $order['order_ref'],
    'status' => $order['status'] ?? 'pending',
    'paymentStatus' => $order['payment_status'] ?? '',
    'refundStatus' => $order['refund_status'] ?? '',
    'fulfillmentStatus' => $order['fulfillment_status'] ?? 'pending',
    'trackingUrl' => $order['tracking_url'] ?? '',
    'amountTotal' => (int)($order['amount_total'] ?? 0),
    'amountRefunded' => (int)($order['amount_refunded'] ?? 0),
    'currency' => $order['currency'] ?? 'nzd',
    'createdAt' => $order['created_at'] ?? '',
    'customerName' => $order['customer_name'] ?? '',
    'shippingAddress' => $order['shipping_address'] ?? '',
    'items' => $items,
]);
