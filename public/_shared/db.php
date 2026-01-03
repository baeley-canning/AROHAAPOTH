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

    return $pdo;
}
