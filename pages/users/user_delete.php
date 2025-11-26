<?php
/*
 * Página para eliminar un usuario.
 * Maneja la confirmación y el procesamiento de la eliminación.
 * Utiliza funciones de los módulos lib/business/user_operations y lib/presentation/user_views.
 * Autor: José Antonio Cortés Ferre
 */

require_once '../../config/init.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/business/auth_operations.php');
require_once getPath('lib/presentation/user_views.php');

requireAdmin();

$pageTitle = "Eliminar Usuario";
$pageHeader = "Confirmar Eliminación";

try {
    // Mostrar confirmación en GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        include getPath('views/partials/header.php');
        $userId = $_GET['id'];
        echo renderDeleteConfirmation($userId, CSRF::generate());
        include getPath('views/partials/footer.php');
    }
    
    // Procesar eliminación en POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['confirm'])) {
        // Validar CSRF
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Error de seguridad: Token CSRF inválido.');
            header('Location: user_index.php');
            exit;
        }

        $userId = $_POST['id'];
        
        try {
            if ($_POST['confirm'] === 'yes') {
                // Obtener datos del usuario antes de eliminarlo para limpiar el avatar
                $user = getUserById($userId);
                
                // Eliminar usuario
                $success = deleteUserById($userId);
                
                // Limpiar avatar si existe
                if ($user && !empty($user['avatar'])) {
                    try {
                        deleteAvatarFile($user['avatar']);
                    } catch (AvatarException $e) {
                        error_log('Avatar cleanup failed: ' . $e->getMessage());
                        // No fallar la operación entera por esto
                    }
                }
                
                Session::setFlash('success', "Usuario con ID " . $userId . " eliminado exitosamente.");
            } else {
                Session::setFlash('info', "Eliminación cancelada.");
            }
            header('Location: user_index.php');
            exit;
            
        } catch (AppException $e) {
            Session::setFlash('error', $e->getUserMessage());
            header('Location: user_index.php');
            exit;
        }
    } else if (!isset($_POST['id']) && !isset($_GET['id'])) {
        // La petición no es válida
        Session::setFlash('error', 'No se ha proporcionado un ID de usuario.');
        header('Location: user_index.php');
        exit;
    }
} catch (Exception $e) {
    // Error no esperado - Dejar que el Global Handler lo maneje
    throw $e;
}
?>
