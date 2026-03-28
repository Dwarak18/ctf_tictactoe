<?php
require_once __DIR__ . '/includes/auth.php';
session_start_safe();
session_destroy();
// Also clear the FLAG_XSS cookie if set
setcookie('FLAG_XSS', '', ['expires' => time() - 3600, 'path' => '/']);
header('Location: /login.php');
exit;
