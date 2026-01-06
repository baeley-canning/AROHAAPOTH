<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../_shared/db.php';

function respond($code, $message) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, 'Method not allowed.');
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) {
    $payload = $_POST;
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

$stmt = $pdo->prepare('SELECT * FROM orders WHERE order_ref = ? AND LOWER(email) = ? LIMIT 1');
$stmt->execute([$orderRef, $email]);
$order = $stmt->fetch();
if (!$order) {
    respond(404, 'Order not found.');
}

$itemsStmt = $pdo->prepare('SELECT product_name, quantity, unit_amount, line_total FROM order_items WHERE order_id = ?');
$itemsStmt->execute([$order['id']]);
$items = $itemsStmt->fetchAll();

echo json_encode([
    'orderRef' => $order['order_ref'],
    'status' => $order['status'] ?? 'pending',
    'paymentStatus' => $order['payment_status'] ?? '',
    'refundStatus' => $order['refund_status'] ?? '',
    'amountTotal' => (int)($order['amount_total'] ?? 0),
    'amountRefunded' => (int)($order['amount_refunded'] ?? 0),
    'currency' => $order['currency'] ?? 'nzd',
    'createdAt' => $order['created_at'] ?? '',
    'customerName' => $order['customer_name'] ?? '',
    'shippingAddress' => $order['shipping_address'] ?? '',
    'items' => $items,
]);
