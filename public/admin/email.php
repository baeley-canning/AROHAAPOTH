<?php
require_once __DIR__ . '/_inc/bootstrap.php';
require_once __DIR__ . '/_inc/layout.php';

require_login();

$configPath = dirname(__DIR__) . '/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

$emailPortal = defined('EMAIL_PORTAL_URL') ? EMAIL_PORTAL_URL : '';

if ($emailPortal) {
    header('Location: ' . $emailPortal);
    exit;
}

render_header('Email Login');
?>
<div class="admin-card">
    <p class="admin-muted">Email login link not configured. Set EMAIL_PORTAL_URL in config.php.</p>
</div>
<?php
render_footer();
