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

Permissions::require(Permissions::USER_DELETE);

$pageTitle = "Delete User";
$pageHeader = "Confirm Deletion";

try {
    // Mostrar confirmación en GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        $userId = $_GET['id'];
        
        // Prevent Self-Deletion
        if ($userId === Session::get('user_id')) {
            Session::setFlash('error', 'You cannot delete your own account.');
            header('Location: user_index.php');
            exit;
        }

        include getPath('views/partials/header.php');
        echo renderDeleteConfirmation($userId, CSRF::generate());
        include getPath('views/partials/footer.php');
    }
    
    // Procesar eliminación en POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['confirm'])) {
        // Validar CSRF
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Security error: Invalid CSRF token.');
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
                
                Session::setFlash('success', "User ID " . $userId . " deleted successfully.");
            } else {
                Session::setFlash('info', "Deletion canceled.");
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
        Session::setFlash('error', 'No user ID provided.');
        header('Location: user_index.php');
        exit;
    }
} catch (Exception $e) {
    // Error no esperado - Dejar que el Global Handler lo maneje
    throw $e;
}
?>
