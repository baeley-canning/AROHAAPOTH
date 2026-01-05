<?php
require_once __DIR__ . '/_inc/bootstrap.php';
require_once __DIR__ . '/_inc/layout.php';

if (is_logged_in()) {
    header('Location: /admin/index.php');
    exit;
}

$configPath = dirname(__DIR__) . '/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username and password are required.';
    } else {
        $checkedDb = false;

        if ($pdo) {
            try {
                $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1');
                $stmt->execute([$username]);
                $admin = $stmt->fetch();
                $checkedDb = true;
                if ($admin && password_verify($password, $admin['password_hash'])) {
                    login_admin($admin);
                    header('Location: /admin/index.php');
                    exit;
                }
                $error = 'Invalid username or password.';
            } catch (Throwable $errorThrown) {
                $checkedDb = false;
            }
        }

        if (!$checkedDb) {
            $configUser = defined('ADMIN_USERNAME') ? ADMIN_USERNAME : '';
            $configHash = defined('ADMIN_PASSWORD_HASH') ? ADMIN_PASSWORD_HASH : '';
            $configPass = defined('ADMIN_PASSWORD') ? ADMIN_PASSWORD : '';

            if ($configUser === '') {
                $error = 'Admin credentials not configured.';
            } elseif ($username !== $configUser) {
                $error = 'Invalid username or password.';
            } elseif ($configHash !== '' && password_verify($password, $configHash)) {
                login_admin(['id' => 0, 'username' => $configUser]);
                header('Location: /admin/index.php');
                exit;
            } elseif ($configPass !== '' && hash_equals($configPass, $password)) {
                login_admin(['id' => 0, 'username' => $configUser]);
                header('Location: /admin/index.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }
    }
}

render_header('Admin Login');
?>
<div class="admin-card" style="max-width: 420px; margin: 60px auto;">
    <h2>Owner Login</h2>
    <p class="admin-muted">Use the username and password created during install.</p>
    <?php if ($error): ?>
        <p class="admin-muted" style="color:#9a4b3b;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="post" class="admin-form">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <div style="margin-top: 18px;">
            <button class="admin-button primary" type="submit">Log in</button>
        </div>
    </form>
</div>
<?php
render_footer();
