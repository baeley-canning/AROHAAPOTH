<?php
// Copy this file to stripe-config.php and replace the key below.
// Recommended: move stripe-config.php outside public_html for extra safety.
if (!empty($_SERVER['SCRIPT_FILENAME']) && basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    http_response_code(403);
    exit;
}

define('STRIPE_SECRET_KEY', 'sk_test_replace_me');
