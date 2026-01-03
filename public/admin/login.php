<?php
require_once __DIR__ . '/_inc/bootstrap.php';
require_once __DIR__ . '/_inc/layout.php';

if (is_logged_in()) {
    header('Location: /admin/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$pdo) {
        $error = 'Database not configured.';
    } else {
        $stmt = $pdo->prepare('SELECT id, email, password_hash FROM admins WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            login_admin($admin);
            header('Location: /admin/index.php');
            exit;
        }
        $error = 'Invalid email or password.';
    }
}

render_header('Admin Login');
?>
<div class="admin-card" style="max-width: 420px; margin: 60px auto;">
    <h2>Owner Login</h2>
    <p class="admin-muted">Use the admin credentials created during install.</p>
    <?php if ($error): ?>
        <p class="admin-muted" style="color:#9a4b3b;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="post" class="admin-form">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <div style="margin-top: 18px;">
            <button class="admin-button primary" type="submit">Log in</button>
        </div>
    </form>
</div>
<?php
render_footer();
