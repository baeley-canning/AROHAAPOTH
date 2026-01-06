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
