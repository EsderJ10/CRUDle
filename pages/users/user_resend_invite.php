<?php
/*
 * Script para reenviar la invitación a un usuario.
 */

require_once '../../config/init.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/business/auth_operations.php');

requireLogin();

// Verificar si es admin
if (!isAdmin()) {
    Session::setFlash('error', 'Acceso denegado.');
    header('Location: user_index.php');
    exit;
}

$userId = $_GET['id'] ?? null;

if (!$userId) {
    Session::setFlash('error', 'ID de usuario no especificado.');
    header('Location: user_index.php');
    exit;
}

try {
    resendInvitation($userId);
    Session::setFlash('success', 'Invitación reenviada exitosamente.');
} catch (AppException $e) {
    Session::setFlash('error', $e->getUserMessage());
} catch (Exception $e) {
    Session::setFlash('error', 'Error inesperado al reenviar la invitación.');
}

header('Location: user_index.php');
exit;
?>
