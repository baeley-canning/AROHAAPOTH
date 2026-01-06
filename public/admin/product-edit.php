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
    'image_alt' => '',
    'seo_title' => '',
    'seo_description' => '',
    'is_active' => 1,
];
$categories = [];

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

if ($pdo) {
    $stmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$messages = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $idValue = trim($_POST['id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $offerPrice = (float)($_POST['offer_price'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $existingImagesRaw = trim($_POST['existing_images'] ?? '');
    $imageAlt = trim($_POST['image_alt'] ?? '');
    $seoTitle = trim($_POST['seo_title'] ?? '');
    $seoDescription = trim($_POST['seo_description'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    $uploadedImages = [];
    if (!empty($_FILES['images_upload']['name'][0])) {
        $uploadDir = dirname(__DIR__) . '/images/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
        $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
        $baseSlug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($idValue ?: $id ?: 'product'));
        $baseSlug = trim($baseSlug, '-');

        foreach ($_FILES['images_upload']['name'] as $index => $originalName) {
            $tmpName = $_FILES['images_upload']['tmp_name'][$index] ?? '';
            $error = $_FILES['images_upload']['error'][$index] ?? UPLOAD_ERR_NO_FILE;
            if ($error !== UPLOAD_ERR_OK || $tmpName === '') {
                continue;
            }

            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExt, true)) {
                $messages[] = 'Skipped unsupported file type: ' . basename($originalName);
                continue;
            }

            $mime = $finfo ? finfo_file($finfo, $tmpName) : '';
            if ($mime && !in_array($mime, $allowedMime, true)) {
                $messages[] = 'Skipped invalid image: ' . basename($originalName);
                continue;
            }

            $suffix = bin2hex(random_bytes(4));
            $filename = ($baseSlug ?: 'product') . '-' . date('YmdHis') . '-' . $suffix . '.' . $extension;
            $targetPath = $uploadDir . '/' . $filename;

            if (move_uploaded_file($tmpName, $targetPath)) {
                $uploadedImages[] = '/images/uploads/' . $filename;
            }
        }

        if ($finfo) {
            finfo_close($finfo);
        }
    }

    $existingImages = array_values(array_filter(array_map('trim', explode(',', $existingImagesRaw))));
    $images = array_merge($existingImages, $uploadedImages);
    $images = array_values(array_unique(array_filter($images)));
    $imagesJson = $images ? json_encode($images) : json_encode(['/images/aroha_product_balm.svg']);

    if ($idValue === '' && $name !== '') {
        $generated = preg_replace('/[^a-z0-9]+/i', '-', strtolower($name));
        $generated = trim($generated, '-');
        if ($generated !== '') {
            $idValue = $generated;
        }
    }

    if ($pdo && $idValue !== '' && $name !== '') {
        if ($id !== '') {
            $stmt = $pdo->prepare('UPDATE products SET name = ?, description = ?, price = ?, offer_price = ?, category = ?, images = ?, image_alt = ?, seo_title = ?, seo_description = ?, is_active = ? WHERE id = ?');
            $stmt->execute([$name, $description, $price, $offerPrice, $category, $imagesJson, $imageAlt, $seoTitle, $seoDescription, $isActive, $id]);
            $messages[] = 'Product updated.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO products (id, name, description, price, offer_price, category, images, image_alt, seo_title, seo_description, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$idValue, $name, $description, $price, $offerPrice, $category, $imagesJson, $imageAlt, $seoTitle, $seoDescription, $isActive]);
            $messages[] = 'Product created.';
            $id = $idValue;
        }
    } else {
        $messages[] = 'Please fill in the required fields.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$pdo) {
    $messages[] = 'Database not configured. Update config.php and run /admin/install.php.';
}

render_header($id ? 'Edit Product' : 'Add Product');
?>
<div class="admin-header">
    <h2><?php echo $id ? 'Edit Product' : 'Add Product'; ?></h2>
    <a class="admin-button" href="/admin/products.php">Back to products</a>
</div>

<?php if ($messages): ?>
    <?php foreach ($messages as $message): ?>
        <p class="admin-muted"><?php echo htmlspecialchars($message); ?></p>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (!$dbReady): ?>
    <p class="admin-muted">Database not configured. Update config.php and run /admin/install.php.</p>
<?php endif; ?>

<div class="admin-card">
    <form method="post" class="admin-form" enctype="multipart/form-data">
        <label>Product ID (slug)</label>
        <input type="text" name="id" value="<?php echo htmlspecialchars($id ?: ($product['id'] ?? '')); ?>" <?php echo $id ? 'readonly' : ''; ?>>
        <span class="admin-muted">Leave blank to auto-generate from the name.</span>

        <label>Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>

        <label>Description</label>
        <textarea name="description"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>

        <label>Price</label>
        <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" required>

        <label>Offer Price</label>
        <input type="number" name="offer_price" step="0.01" value="<?php echo htmlspecialchars($product['offer_price'] ?? ''); ?>" required>

        <label>Category</label>
        <input type="text" name="category" list="category-options" value="<?php echo htmlspecialchars($product['category'] ?? ''); ?>">
        <datalist id="category-options">
            <?php foreach ($categories as $categoryOption): ?>
                <option value="<?php echo htmlspecialchars($categoryOption); ?>"></option>
            <?php endforeach; ?>
        </datalist>
        <span class="admin-muted">Pick an existing category or type a new one.</span>

        <label>Upload Images</label>
        <input type="file" name="images_upload[]" accept="image/*" multiple>
        <span class="admin-muted">Uploads go to /images/uploads and will be added automatically.</span>

        <input type="hidden" name="existing_images" value="<?php echo htmlspecialchars($product['images'] ?? ''); ?>">

        <label>Image Alt Text (SEO)</label>
        <input type="text" name="image_alt" value="<?php echo htmlspecialchars($product['image_alt'] ?? ''); ?>">
        <span class="admin-muted">Used for the product image alt text. Keep it descriptive.</span>

        <label>SEO Title</label>
        <input type="text" name="seo_title" value="<?php echo htmlspecialchars($product['seo_title'] ?? ''); ?>">

        <label>SEO Description</label>
        <textarea name="seo_description"><?php echo htmlspecialchars($product['seo_description'] ?? ''); ?></textarea>

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
