<?php
// Copy this file to config.php and fill in your credentials.
// Recommended: keep config.php outside public_html if possible.
if (!empty($_SERVER['SCRIPT_FILENAME']) && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    http_response_code(403);
    exit;
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'cpanel_db_name');
define('DB_USER', 'cpanel_db_user');
define('DB_PASS', 'cpanel_db_password');

// Optional: set a setup key to protect admin/install.php
define('APP_SETUP_KEY', 'replace-with-a-random-string');

// Optional: fallback admin login without a database.
// Prefer ADMIN_PASSWORD_HASH (generated via password_hash) over plain text.
define('ADMIN_USERNAME', 'owner');
define('ADMIN_PASSWORD_HASH', '');
define('ADMIN_PASSWORD', '');

// Optional: link to webmail (used by /admin/email.php)
define('EMAIL_PORTAL_URL', '');

// Optional: order notification email (owner/admin)
define('ORDER_NOTIFICATION_EMAIL', '');
define('ORDER_FROM_EMAIL', '');
define('ORDER_NOTIFY_ON_CREATE', false);

// Optional: customer emails (order confirmation/shipping)
define('CUSTOMER_FROM_EMAIL', '');

// Optional: SMTP mailer (recommended for reliable delivery)
define('SMTP_HOST', '');
define('SMTP_PORT', 465);
define('SMTP_SECURE', 'ssl'); // ssl or tls
define('SMTP_USER', '');
define('SMTP_PASS', '');

// Stripe keys (optional here if you still use public/stripe/stripe-config.php)
define('STRIPE_SECRET_KEY', 'sk_test_replace_me');
define('STRIPE_WEBHOOK_SECRET', 'whsec_replace_me');

// Optional: Cloudflare Turnstile for order lookup
define('TURNSTILE_SITE_KEY', '');
define('TURNSTILE_SECRET_KEY', '');
