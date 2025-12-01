<?php
require_once '../../config/init.php';

logout();
Session::setFlash('success', 'You have logged out successfully.');
header('Location: ' . getWebPath('pages/auth/login.php'));
exit;
?>
