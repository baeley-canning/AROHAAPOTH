<?php
header('Content-Type: application/json');

const CONFIG_LOCATIONS = [
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

$lineItems = [];
foreach ($payload['items'] as $item) {
    if (!is_array($item)) {
        continue;
    }
    $id = $item['id'] ?? null;
    $quantity = (int)($item['quantity'] ?? 0);
    if (!$id || $quantity < 1 || !isset(CATALOG[$id])) {
        continue;
    }
    if ($quantity > 20) {
        $quantity = 20;
    }
    $product = CATALOG[$id];
    $lineItems[] = [
        'price_data' => [
            'currency' => CURRENCY,
            'product_data' => [
                'name' => $product['name'],
                'metadata' => [
                    'id' => $id,
                ],
            ],
            'unit_amount' => $product['unit_amount'],
        ],
        'quantity' => $quantity,
    ];
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

$successUrl = $origin . $basePath . '/order-placed/';
$cancelUrl = $origin . $basePath . '/cart/';

$params = [
    'mode' => 'payment',
    'success_url' => $successUrl,
    'cancel_url' => $cancelUrl,
    'line_items' => $lineItems,
    'billing_address_collection' => 'required',
    'shipping_address_collection' => [
        'countries' => ['NZ'],
    ],
    'allow_promotion_codes' => 'true',
];

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
    respond(500, 'Stripe request failed.');
}

$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$decoded = json_decode($response, true);
if ($status >= 400) {
    $message = $decoded['error']['message'] ?? 'Stripe error.';
    respond($status, $message);
}

if (empty($decoded['url'])) {
    respond(500, 'Stripe response missing URL.');
}

echo json_encode(['url' => $decoded['url']]);
