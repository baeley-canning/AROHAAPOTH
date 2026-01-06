<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../_shared/db.php';

const CONFIG_LOCATIONS = [
    1,
    2,
    3,
    4,
];

const CATALOG = [
    'balm-kawakawa' => ['name' => 'Kawakawa Balm', 'unit_amount' => 1500],
    'balm-calendula' => ['name' => 'Calendula Balm', 'unit_amount' => 1500],
    'lip-balm-hemp-honey' => ['name' => 'Hemp + Honey Lip Balm', 'unit_amount' => 800],
    'scrub-rose-sugar' => ['name' => 'Rose Sugar Scrub', 'unit_amount' => 1500],
    'body-butter-fairy-kisses' => ['name' => 'Fairy Kisses Body Butter', 'unit_amount' => 2000],
    'soap-lavender-foam' => ['name' => 'Lavender Foam Soap', 'unit_amount' => 1500],
    'tea-kawakawa-blend' => ['name' => 'Kawakawa Tea Blend', 'unit_amount' => 1200],
    'elixir-rose-face' => ['name' => 'Rose Face Elixir', 'unit_amount' => 2800],
    'oil-nail-cuticle' => ['name' => 'Nail + Cuticle Oil', 'unit_amount' => 1200],
    'tincture-custom' => ['name' => 'Custom Herbal Tincture', 'unit_amount' => 3500],
    'remedy-rescue' => ['name' => 'Rescue Remedy', 'unit_amount' => 1800],
    'horseshoe-art-custom' => ['name' => 'Custom Horseshoe Art', 'unit_amount' => 7000],
    'bundle-calm-night' => ['name' => 'Calm Night Ritual Set', 'unit_amount' => 5200],
    'bundle-soft-skin' => ['name' => 'Soft Skin Essentials', 'unit_amount' => 4800],
    'bundle-aroha-gift' => ['name' => 'Aroha Gift Bundle', 'unit_amount' => 6200],
];

const CURRENCY = 'nzd';

function respond($code, $message) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, 'Method not allowed.');
}

$secret = getenv('STRIPE_SECRET_KEY');
$configPath = dirname(__DIR__) . '/config.php';
if (!$secret && file_exists($configPath)) {
    require $configPath;
    if (defined('STRIPE_SECRET_KEY')) {
        $secret = STRIPE_SECRET_KEY;
    }
}
if (!$secret) {
    foreach (CONFIG_LOCATIONS as $level) {
        $path = dirname(__DIR__, $level) . '/stripe-config.php';
        if (file_exists($path)) {
            require $path;
            break;
        }
    }
    if (defined('STRIPE_SECRET_KEY')) {
        $secret = STRIPE_SECRET_KEY;
    }
}

if (!$secret) {
    respond(500, 'Stripe is not configured.');
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

if (!is_array($payload) || empty($payload['items']) || !is_array($payload['items'])) {
    respond(400, 'Invalid cart payload.');
}

$pdo = db();
$dbProducts = [];
if ($pdo) {
    $ids = [];
    foreach ($payload['items'] as $item) {
        if (!is_array($item)) {
            continue;
        }
        $id = $item['id'] ?? null;
        if ($id) {
            $ids[] = $id;
        }
    }
    $ids = array_values(array_unique($ids));
    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id, name, offer_price FROM products WHERE id IN ($placeholders) AND is_active = 1");
        $stmt->execute($ids);
        while ($row = $stmt->fetch()) {
            $dbProducts[$row['id']] = $row;
        }
    }
}

$lineItems = [];
$orderItems = [];
$totalAmount = 0;
foreach ($payload['items'] as $item) {
    if (!is_array($item)) {
        continue;
    }
    $id = $item['id'] ?? null;
    $quantity = (int)($item['quantity'] ?? 0);
    if (!$id || $quantity < 1) {
        continue;
    }
    if ($quantity > 20) {
        $quantity = 20;
    }
    $product = null;
    $unitAmount = null;
    if (isset($dbProducts[$id])) {
        $product = $dbProducts[$id]['name'];
        $unitAmount = (int)round(((float)$dbProducts[$id]['offer_price']) * 100);
    } elseif (isset(CATALOG[$id])) {
        $product = CATALOG[$id]['name'];
        $unitAmount = CATALOG[$id]['unit_amount'];
    }
    if (!$product || !$unitAmount) {
        continue;
    }
    $lineTotal = $unitAmount * $quantity;
    $lineItems[] = [
        'price_data' => [
            'currency' => CURRENCY,
            'product_data' => [
                'name' => $product,
                'metadata' => [
                    'id' => $id,
                ],
            ],
            'unit_amount' => $unitAmount,
        ],
        'quantity' => $quantity,
    ];
    $orderItems[] = [
        'product_id' => $id,
        'product_name' => $product,
        'quantity' => $quantity,
        'unit_amount' => $unitAmount,
        'line_total' => $lineTotal,
    ];
    $totalAmount += $lineTotal;
}

if (!$lineItems) {
    respond(400, 'Cart is empty.');
}

$host = $_SERVER['HTTP_HOST'] ?? '';
if (!$host) {
    respond(500, 'Missing host.');
}
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    $scheme = 'https';
} else {
    $scheme = 'http';
}

$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$basePath = preg_replace('#/stripe$#', '', $scriptDir);
$basePath = rtrim($basePath, '/');
$origin = $scheme . '://' . $host;

$successUrlBase = $origin . $basePath . '/order-placed/';
$cancelUrl = $origin . $basePath . '/cart/';

$orderRef = null;
$orderId = null;
if ($pdo) {
    try {
        $orderRef = 'AO' . strtoupper(bin2hex(random_bytes(4)));
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('INSERT INTO orders (order_ref, status, amount_total, currency) VALUES (?, ?, ?, ?)');
        $stmt->execute([$orderRef, 'pending', $totalAmount, CURRENCY]);
        $orderId = (int)$pdo->lastInsertId();

        $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_amount, line_total) VALUES (?, ?, ?, ?, ?, ?)');
        foreach ($orderItems as $item) {
            $itemStmt->execute([
                $orderId,
                $item['product_id'],
                $item['product_name'],
                $item['quantity'],
                $item['unit_amount'],
                $item['line_total'],
            ]);
        }
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $orderRef = null;
        $orderId = null;
    }
}

$successUrl = $successUrlBase;
if ($orderRef) {
    $successUrl = $successUrlBase . '?ref=' . urlencode($orderRef);
}

$params = [
    'mode' => 'payment',
    'success_url' => $successUrl,
    'cancel_url' => $cancelUrl,
    'line_items' => $lineItems,
    'billing_address_collection' => 'required',
    'shipping_address_collection' => [
        'allowed_countries' => ['NZ'],
    ],
    'allow_promotion_codes' => 'true',
];

if ($orderRef) {
    $params['client_reference_id'] = $orderRef;
}

$body = http_build_query($params);

$ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $secret,
    'Content-Type: application/x-www-form-urlencoded',
]);

$response = curl_exec($ch);
if ($response === false) {
    curl_close($ch);
    if ($pdo && $orderId) {
        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute(['failed', $orderId]);
    }
    respond(500, 'Stripe request failed.');
}

$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$decoded = json_decode($response, true);
if ($status >= 400) {
    $message = $decoded['error']['message'] ?? 'Stripe error.';
    if ($pdo && $orderId) {
        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute(['failed', $orderId]);
    }
    respond($status, $message);
}

if (empty($decoded['url'])) {
    respond(500, 'Stripe response missing URL.');
}

if ($pdo && $orderId && !empty($decoded['id'])) {
    $stmt = $pdo->prepare('UPDATE orders SET stripe_session_id = ? WHERE id = ?');
    $stmt->execute([$decoded['id'], $orderId]);
}

echo json_encode(['url' => $decoded['url']]);
