<?php
function db()
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }

    $configPath = dirname(__DIR__) . '/config.php';
    if (!file_exists($configPath)) {
        return null;
    }

    require_once $configPath;
    if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER')) {
        return null;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $password = defined('DB_PASS') ? DB_PASS : '';
    try {
        $pdo = new PDO($dsn, DB_USER, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (Throwable $error) {
        return null;
    }

    ensure_product_columns($pdo);
    ensure_order_columns($pdo);
    return $pdo;
}

function ensure_product_columns($pdo)
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $columns = [
        'image_alt' => 'VARCHAR(255) DEFAULT NULL',
        'seo_title' => 'VARCHAR(255) DEFAULT NULL',
        'seo_description' => 'TEXT',
    ];

    foreach ($columns as $name => $definition) {
        try {
            $stmt = $pdo->prepare('SHOW COLUMNS FROM products LIKE ?');
            $stmt->execute([$name]);
            if (!$stmt->fetch()) {
                $pdo->exec("ALTER TABLE products ADD COLUMN {$name} {$definition}");
            }
        } catch (Throwable $error) {
            // Ignore if privileges or table are unavailable.
        }
    }
}

function ensure_order_columns($pdo)
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    $columns = [
        'customer_name' => 'VARCHAR(255) DEFAULT NULL',
        'customer_phone' => 'VARCHAR(64) DEFAULT NULL',
        'shipping_address' => 'TEXT',
        'payment_status' => 'VARCHAR(32) DEFAULT NULL',
        'amount_refunded' => 'INT DEFAULT 0',
        'refund_status' => 'VARCHAR(32) DEFAULT NULL',
        'fulfillment_status' => 'VARCHAR(32) DEFAULT NULL',
        'tracking_url' => 'VARCHAR(255) DEFAULT NULL',
        'admin_notified_at' => 'DATETIME DEFAULT NULL',
        'customer_notified_paid_at' => 'DATETIME DEFAULT NULL',
        'customer_notified_shipped_at' => 'DATETIME DEFAULT NULL',
    ];

    foreach ($columns as $name => $definition) {
        try {
            $stmt = $pdo->prepare('SHOW COLUMNS FROM orders LIKE ?');
            $stmt->execute([$name]);
            if (!$stmt->fetch()) {
                $pdo->exec("ALTER TABLE orders ADD COLUMN {$name} {$definition}");
            }
        } catch (Throwable $error) {
            // Ignore if privileges or table are unavailable.
        }
    }
}
