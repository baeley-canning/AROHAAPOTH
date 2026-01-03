<?php
require_once __DIR__ . '/_inc/bootstrap.php';
require_once __DIR__ . '/_inc/layout.php';

require_login();

$id = trim($_GET['id'] ?? '');
$dbReady = (bool)$pdo;
$product = [
    'id' => '',
    'name' => '',
    'description' => '',
    'price' => '',
    'offer_price' => '',
    'category' => '',
    'images' => '',
    'is_active' => 1,
];

if ($pdo && $id !== '') {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        $product = $row;
        $images = json_decode($row['images'] ?? '', true);
        if (is_array($images)) {
            $product['images'] = implode(', ', $images);
        }
    }
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $idValue = trim($_POST['id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $offerPrice = (float)($_POST['offer_price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $imagesRaw = trim($_POST['images'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    $images = array_values(array_filter(array_map('trim', explode(',', $imagesRaw))));
    $imagesJson = $images ? json_encode($images) : json_encode(['/images/aroha_product_balm.svg']);

    if ($pdo && $idValue !== '' && $name !== '') {
        if ($id !== '') {
            $stmt = $pdo->prepare('UPDATE products SET name = ?, description = ?, price = ?, offer_price = ?, category = ?, images = ?, is_active = ? WHERE id = ?');
            $stmt->execute([$name, $description, $price, $offerPrice, $category, $imagesJson, $isActive, $id]);
            $message = 'Product updated.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO products (id, name, description, price, offer_price, category, images, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$idValue, $name, $description, $price, $offerPrice, $category, $imagesJson, $isActive]);
            $message = 'Product created.';
            $id = $idValue;
        }
    } else {
        $message = 'Please fill in the required fields.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$pdo) {
    $message = 'Database not configured. Update config.php and run /admin/install.php.';
}

render_header($id ? 'Edit Product' : 'Add Product');
?>
<div class="admin-header">
    <h2><?php echo $id ? 'Edit Product' : 'Add Product'; ?></h2>
    <a class="admin-button" href="/admin/products.php">Back to products</a>
</div>

<?php if ($message): ?>
    <p class="admin-muted"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<?php if (!$dbReady): ?>
    <p class="admin-muted">Database not configured. Update config.php and run /admin/install.php.</p>
<?php endif; ?>

<div class="admin-card">
    <form method="post" class="admin-form">
        <label>Product ID (slug)</label>
        <input type="text" name="id" value="<?php echo htmlspecialchars($id ?: ($product['id'] ?? '')); ?>" <?php echo $id ? 'readonly' : 'required'; ?>>
        <span class="admin-muted">Use lowercase and hyphens, e.g. balm-kawakawa.</span>

        <label>Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>

        <label>Description</label>
        <textarea name="description"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>

        <label>Price</label>
        <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" required>

        <label>Offer Price</label>
        <input type="number" name="offer_price" step="0.01" value="<?php echo htmlspecialchars($product['offer_price'] ?? ''); ?>" required>

        <label>Category</label>
        <input type="text" name="category" value="<?php echo htmlspecialchars($product['category'] ?? ''); ?>">

        <label>Images (comma separated URLs)</label>
        <textarea name="images" placeholder="/images/aroha_product_balm.svg, /images/aroha_product_balm.svg"><?php echo htmlspecialchars($product['images'] ?? ''); ?></textarea>
        <span class="admin-muted">Upload images to /images and paste the paths here.</span>

        <label>
            <input type="checkbox" name="is_active" value="1" <?php echo !empty($product['is_active']) ? 'checked' : ''; ?>>
            Active (visible in shop)
        </label>

        <div style="margin-top: 20px;">
            <button class="admin-button primary" type="submit">Save product</button>
        </div>
    </form>
</div>
<?php
render_footer();
