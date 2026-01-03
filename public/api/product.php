<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../_shared/db.php';

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
if ($id === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing id.']);
    exit;
}

$pdo = db();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Database not configured.']);
    exit;
}

$stmt = $pdo->prepare('SELECT id, name, description, price, offer_price, category, images FROM products WHERE id = ? AND is_active = 1 LIMIT 1');
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found.']);
    exit;
}

$images = [];
if (!empty($row['images'])) {
    $decoded = json_decode($row['images'], true);
    if (is_array($decoded)) {
        $images = $decoded;
    }
}

if (!$images) {
    $images = ['/images/aroha_product_balm.svg'];
}

echo json_encode([
    '_id' => $row['id'],
    'name' => $row['name'],
    'description' => $row['description'],
    'price' => (float)$row['price'],
    'offerPrice' => (float)$row['offer_price'],
    'category' => $row['category'],
    'image' => $images,
]);
