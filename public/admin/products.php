<?php
require_once __DIR__ . '/_inc/bootstrap.php';
require_once __DIR__ . '/_inc/layout.php';

require_login();

$message = '';
$dbReady = (bool)$pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = trim($_POST['delete_id']);
    if ($pdo && $deleteId !== '') {
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$deleteId]);
        $message = 'Product deleted.';
    }
}

$products = [];
if ($pdo) {
    $stmt = $pdo->query('SELECT id, name, category, price, offer_price, is_active FROM products ORDER BY created_at DESC');
    $products = $stmt->fetchAll();
}

render_header('Products');
?>
<div class="admin-header">
    <h2>Products</h2>
    <a class="admin-button primary" href="/admin/product-edit.php">Add product</a>
</div>

<?php if ($message): ?>
    <p class="admin-muted"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<?php if (!$dbReady): ?>
    <p class="admin-muted">Database not configured. Update config.php and run /admin/install.php.</p>
<?php endif; ?>

<div class="admin-card">
    <?php if (!$products): ?>
        <p class="admin-muted">No products yet. Add your first product.</p>
    <?php else: ?>
    <table class="admin-table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Category</th>
            <th>Price</th>
            <th>Offer</th>
            <th>Status</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?php echo htmlspecialchars($product['id']); ?></td>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td><?php echo htmlspecialchars($product['category']); ?></td>
                <td><?php echo number_format((float)$product['price'], 2); ?></td>
                <td><?php echo number_format((float)$product['offer_price'], 2); ?></td>
                <td><?php echo $product['is_active'] ? 'Active' : 'Hidden'; ?></td>
                <td>
                    <a class="admin-button" href="/admin/product-edit.php?id=<?php echo urlencode($product['id']); ?>">Edit</a>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                        <button class="admin-button" type="submit" onclick="return confirm('Delete this product?');">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php
render_footer();
