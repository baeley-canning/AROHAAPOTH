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
    $_SESSION['admin_email'] = $admin['email'];
}

function logout_admin()
{
    session_destroy();
}
