<?php
function is_logged_in()
{
    return isset($_SESSION['admin_id']);
}

function require_login()
{
    if (!is_logged_in()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function login_admin($admin)
{
    $_SESSION['admin_id'] = $admin['id'];
    if (isset($admin['username'])) {
        $_SESSION['admin_username'] = $admin['username'];
    }
}

function logout_admin()
{
    session_destroy();
}
