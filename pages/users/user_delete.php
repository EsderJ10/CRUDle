<?php
/*
 * Página para eliminar un usuario.
 * Maneja la confirmación y el procesamiento de la eliminación.
 * Utiliza funciones de los módulos lib/business/user_operations y lib/presentation/user_views.
 * Autor: José Antonio Cortés Ferre
 */

require_once '../../config/paths.php';
require_once getPath('config/config.php');
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/presentation/user_views.php');
require_once getPath('lib/core/exceptions.php');
require_once getPath('lib/core/error_handler.php');

$pageTitle = "Eliminar Usuario";
$pageHeader = "Confirmar Eliminación";

try {
    // Mostrar confirmación en GET
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        include getPath('views/partials/header.php');
        $userId = $_GET['id'];
        echo renderDeleteConfirmation($userId);
        include getPath('views/partials/footer.php');
    }
    
    // Procesar eliminación en POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['confirm'])) {
        include getPath('views/partials/header.php');
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
                
                echo renderMessage("* Usuario con ID " . $userId . " eliminado exitosamente.", 'success');
            } else {
                echo renderMessage("* Eliminación cancelada. El usuario con ID " . $userId . " no ha sido eliminado.", 'info');
            }
        } catch (ResourceNotFoundException $e) {
            echo renderMessage('ERROR: ' . $e->getUserMessage(), 'error');
        } catch (CSVException $e) {
            echo renderMessage('ERROR: ' . $e->getUserMessage(), 'error');
        } catch (UserOperationException $e) {
            echo renderMessage('ERROR: ' . $e->getUserMessage(), 'error');
        } catch (Exception $e) {
            echo renderMessage('ERROR: Ocurrió un error al eliminar el usuario. ' . $e->getMessage(), 'error');
            error_log('Error deleting user: ' . $e->getMessage());
        }
        
        echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
        include getPath('views/partials/footer.php');
    } else if (!isset($_POST['id']) && !isset($_GET['id'])) {
        // La petición no es válida
        include getPath('views/partials/header.php');
        echo renderMessage('ERROR: No se ha proporcionado un ID de usuario.', 'error');
        echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
        include getPath('views/partials/footer.php');
    }
} catch (Exception $e) {
    // Error no esperado
    include getPath('views/partials/header.php');
    echo renderMessage('ERROR: Ocurrió un error inesperado. ' . $e->getMessage(), 'error');
    echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
    include getPath('views/partials/footer.php');
    error_log('Unexpected error in user_delete.php: ' . $e->getMessage());
    exit;
}
?>
