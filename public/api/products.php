<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../_shared/db.php';

$pdo = db();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Database not configured.']);
    exit;
}

$stmt = $pdo->query('SELECT id, name, description, price, offer_price, category, images FROM products WHERE is_active = 1 ORDER BY created_at DESC');
$products = [];
while ($row = $stmt->fetch()) {
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

    $products[] = [
        '_id' => $row['id'],
        'name' => $row['name'],
        'description' => $row['description'],
        'price' => (float)$row['price'],
        'offerPrice' => (float)$row['offer_price'],
        'category' => $row['category'],
        'image' => $images,
    ];
}

echo json_encode($products);
