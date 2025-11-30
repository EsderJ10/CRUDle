<?php
require_once '../../config/init.php';
require_once getPath('lib/business/auth_operations.php');

logout();
Session::setFlash('success', 'You have logged out successfully.');
header('Location: ' . getWebPath('pages/auth/login.php'));
exit;
?>
