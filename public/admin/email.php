<?php
require_once __DIR__ . '/_inc/bootstrap.php';
require_once __DIR__ . '/_inc/layout.php';
require_once __DIR__ . '/../_shared/notify.php';

require_login();

$configPath = dirname(__DIR__) . '/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

$emailPortal = defined('EMAIL_PORTAL_URL') ? EMAIL_PORTAL_URL : '';

$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testEmail = trim($_POST['test_email'] ?? '');
    if ($testEmail === '') {
        $error = 'Enter an email address to send the test message.';
    } else {
        $subject = 'Aroha Apothecary test email';
        $body = "This is a test email from your store.\n\nIf you can read this, email sending is working.";
        $fromEmail = $testEmail;
        if (defined('CUSTOMER_FROM_EMAIL') && trim((string)CUSTOMER_FROM_EMAIL) !== '') {
            $fromEmail = trim((string)CUSTOMER_FROM_EMAIL);
        } elseif (defined('ORDER_FROM_EMAIL') && trim((string)ORDER_FROM_EMAIL) !== '') {
            $fromEmail = trim((string)ORDER_FROM_EMAIL);
        } elseif (defined('ORDER_NOTIFICATION_EMAIL') && trim((string)ORDER_NOTIFICATION_EMAIL) !== '') {
            $fromEmail = trim((string)ORDER_NOTIFICATION_EMAIL);
        }
        $sent = send_email_message($testEmail, $subject, $body, $fromEmail);
        if ($sent) {
            $message = 'Test email sent. Check your inbox and spam folder.';
        } else {
            $error = 'Test email failed to send. The server mailer may be blocked.';
        }
    }
}

render_header('Email Login');
?>
<div class="admin-card">
    <?php if ($emailPortal): ?>
        <p class="admin-muted">
            <a class="admin-link" href="<?php echo htmlspecialchars($emailPortal); ?>" target="_blank" rel="noopener">Open webmail</a>
        </p>
    <?php else: ?>
        <p class="admin-muted">Email login link not configured. Set EMAIL_PORTAL_URL in config.php.</p>
    <?php endif; ?>
</div>

<div class="admin-card" style="margin-top: 16px;">
    <h3 style="margin-top: 0;">Send test email</h3>
    <?php if ($message): ?>
        <p class="admin-muted"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="admin-muted" style="color: #9b3b32;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="post" class="admin-form" style="margin-top: 12px;">
        <label>Email address</label>
        <input type="email" name="test_email" placeholder="hello@arohaapothecary.com" required>
        <div style="margin-top: 12px;">
            <button class="admin-button" type="submit">Send test email</button>
        </div>
    </form>
</div>
<?php
render_footer();
