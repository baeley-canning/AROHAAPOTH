<?php
session_start();
require_once __DIR__ . '/../../_shared/db.php';
require_once __DIR__ . '/auth.php';

$pdo = db();
