<?php
require_once '../../config/init.php';
require_once getPath('lib/business/auth_operations.php');

logout();
Session::setFlash('success', 'Has cerrado sesión correctamente.');
header('Location: ' . getWebPath('pages/auth/login.php'));
exit;
?>
