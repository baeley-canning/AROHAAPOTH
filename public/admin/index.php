<?php
require_once __DIR__ . '/_inc/bootstrap.php';
require_once __DIR__ . '/_inc/layout.php';

require_login();

$productCount = 0;
$orderCount = 0;
$dbReady = (bool)$pdo;

if ($dbReady) {
    $productCount = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    $orderCount = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
}

render_header('Admin Dashboard');
?>
<div class="admin-header">
    <h2>Dashboard</h2>
    <a class="admin-button primary" href="/admin/product-edit.php">Add product</a>
</div>

<?php if (!$dbReady): ?>
    <p class="admin-muted">Database not configured. Update config.php and run /admin/install.php.</p>
<?php endif; ?>

<div class="admin-grid">
    <a class="admin-pill admin-pill-link" href="/admin/products.php">
        <strong><?php echo $productCount; ?></strong>
        <div class="admin-muted">Products</div>
    </a>
    <a class="admin-pill admin-pill-link" href="/admin/orders.php">
        <strong><?php echo $orderCount; ?></strong>
        <div class="admin-muted">Orders</div>
    </a>
    <div class="admin-pill">
        <strong>Stripe</strong>
        <div class="admin-muted">Secure checkout</div>
    </div>
</div>
<?php
render_footer();
