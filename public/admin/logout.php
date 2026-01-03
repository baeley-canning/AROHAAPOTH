<?php
require_once __DIR__ . '/_inc/bootstrap.php';

logout_admin();
header('Location: /admin/login.php');
exit;
