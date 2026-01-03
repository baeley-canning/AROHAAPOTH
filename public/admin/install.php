<?php
require_once __DIR__ . '/_inc/bootstrap.php';
require_once __DIR__ . '/_inc/layout.php';

$configPath = dirname(__DIR__) . '/config.php';
if (!file_exists($configPath)) {
    render_header('Install');
    echo '<div class="admin-card">Create <strong>public/config.php</strong> first.</div>';
    render_footer();
    exit;
}

require_once $configPath;

$setupKey = defined('APP_SETUP_KEY') ? APP_SETUP_KEY : '';
if ($setupKey && (!isset($_GET['key']) || $_GET['key'] !== $setupKey)) {
    http_response_code(403);
    render_header('Install');
    echo '<div class="admin-card">Invalid setup key.</div>';
    render_footer();
    exit;
}

$message = '';
$hasDb = (bool)$pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $seed = isset($_POST['seed']);

    if ($email === '' || $password === '') {
        $message = 'Email and password are required.';
    } else {
        $pdo->exec('CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');

        $pdo->exec('CREATE TABLE IF NOT EXISTS products (
            id VARCHAR(64) PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            offer_price DECIMAL(10,2) NOT NULL,
            category VARCHAR(64),
            images TEXT,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )');

        $pdo->exec('CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_ref VARCHAR(32) UNIQUE NOT NULL,
            stripe_session_id VARCHAR(255),
            stripe_payment_intent_id VARCHAR(255),
            email VARCHAR(255),
            amount_total INT,
            currency VARCHAR(8),
            status VARCHAR(32) DEFAULT "pending",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )');

        $pdo->exec('CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id VARCHAR(64),
            product_name VARCHAR(255),
            quantity INT,
            unit_amount INT,
            line_total INT,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
        )');

        $stmt = $pdo->prepare('INSERT IGNORE INTO admins (email, password_hash) VALUES (?, ?)');
        $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);

        if ($seed) {
            $products = [
                ['balm-kawakawa', 'Kawakawa Balm', 'Soothing balm crafted for dry or tender skin with kawakawa and calendula.', 18, 15, 'Balms', ['/images/aroha_product_balm.svg']],
                ['balm-calendula', 'Calendula Balm', 'Gentle, golden balm for everyday nourishment and sensitive skin.', 18, 15, 'Balms', ['/images/aroha_product_balm.svg']],
                ['lip-balm-hemp-honey', 'Hemp + Honey Lip Balm', 'A nourishing lip balm with hemp oil and honey for soft, protected lips.', 10, 8, 'Lip Balm', ['/images/aroha_product_balm.svg']],
                ['scrub-rose-sugar', 'Rose Sugar Scrub', 'An exfoliating sugar scrub with rose and coconut to smooth and soften.', 18, 15, 'Scrubs', ['/images/aroha_product_scrub.svg']],
                ['body-butter-fairy-kisses', 'Fairy Kisses Body Butter', 'Whipped body butter for deep hydration with a soft, sweet finish.', 24, 20, 'Body', ['/images/aroha_product_balm.svg']],
                ['soap-lavender-foam', 'Lavender Foam Soap', 'Creamy foam soap with lavender and tea tree essential oils.', 18, 15, 'Soap', ['/images/aroha_product_soap.svg']],
                ['tea-kawakawa-blend', 'Kawakawa Tea Blend', 'A grounding herbal blend with kawakawa and stinging nettle.', 14, 12, 'Tea', ['/images/aroha_product_tea.svg']],
                ['elixir-rose-face', 'Rose Face Elixir', 'A gentle blend of rose, calendula, and jojoba to nourish and glow.', 32, 28, 'Elixir', ['/images/aroha_product_tincture.svg']],
                ['oil-nail-cuticle', 'Nail + Cuticle Oil', 'Botanical oil to soften cuticles, strengthen nails, and promote growth.', 14, 12, 'Oil', ['/images/aroha_product_tincture.svg']],
                ['tincture-custom', 'Custom Herbal Tincture', 'A tailored tincture made to order for your unique needs.', 40, 35, 'Tincture', ['/images/aroha_product_tincture.svg']],
                ['remedy-rescue', 'Rescue Remedy', 'A heart-soothing blend for stressful days and emotional balance.', 22, 18, 'Remedy', ['/images/aroha_product_tincture.svg']],
                ['horseshoe-art-custom', 'Custom Horseshoe Art', 'Upcycled horseshoe art made to order with your story and charms.', 85, 70, 'Art', ['/images/aroha_product_horseshoe.svg']],
                ['bundle-calm-night', 'Calm Night Ritual Set', 'A soothing trio for evening wind-down: balm, tea blend, and body butter.', 60, 52, 'Bundle', ['/images/aroha_product_balm.svg']],
                ['bundle-soft-skin', 'Soft Skin Essentials', 'Scrub, balm, and foam soap for smooth, hydrated skin.', 55, 48, 'Bundle', ['/images/aroha_product_scrub.svg']],
                ['bundle-aroha-gift', 'Aroha Gift Bundle', 'A thoughtful set with tincture, balm, and sugar scrub.', 70, 62, 'Bundle', ['/images/aroha_product_tincture.svg']],
            ];

            $insert = $pdo->prepare('INSERT IGNORE INTO products (id, name, description, price, offer_price, category, images, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)');
            foreach ($products as $product) {
                $insert->execute([
                    $product[0],
                    $product[1],
                    $product[2],
                    $product[3],
                    $product[4],
                    $product[5],
                    json_encode($product[6]),
                ]);
            }
        }

        $message = 'Install complete. You can now log in.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$pdo) {
    $message = 'Database connection failed. Check config.php credentials.';
}

render_header('Install');
?>
<div class="admin-card">
    <h2>Admin Setup</h2>
    <p class="admin-muted">Create the database tables and an admin login.</p>
    <?php if ($message): ?>
        <p class="admin-muted"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="post" class="admin-form">
        <label>Admin Email</label>
        <input type="email" name="email" required>

        <label>Admin Password</label>
        <input type="password" name="password" required>

        <label>
            <input type="checkbox" name="seed" value="1" checked>
            Seed starter products
        </label>

        <div style="margin-top: 18px;">
            <button class="admin-button primary" type="submit" <?php echo $hasDb ? '' : 'disabled'; ?>>Run setup</button>
        </div>
    </form>
    <p class="admin-muted" style="margin-top: 16px;">
        After setup, delete or protect this file.
    </p>
</div>
<?php
render_footer();
