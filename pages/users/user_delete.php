<?php
/*
 * Página para eliminar un usuario.
 * Maneja la confirmación y el procesamiento de la eliminación.
 * Utiliza funciones de los módulos lib/business/user_operations y lib/presentation/user_views.
 * Autor: José Antonio Cortés Ferre
 */

require_once '../../config/paths.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/presentation/user_views.php');

$pageTitle = "Eliminar Usuario";
$pageHeader = "Confirmar Eliminación";

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        include getPath('views/partials/header.php');
        $userId = $_GET['id'];
        echo renderDeleteConfirmation($userId);
        include getPath('views/partials/footer.php');
    }

    // Si la petición es POST, procesar la eliminación
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['confirm'])) {
        include getPath('views/partials/header.php');
        $userId = $_POST['id'];
        
        if ($_POST['confirm'] === 'yes') {
            // Obtener datos del usuario antes de la eliminación para limpiar el avatar
            $user = getUserById($userId);
            $success = deleteUserById($userId);
            
            if ($success) {
                if ($user && !empty($user['avatar'])) {
                    deleteAvatarFile($user['avatar']);
                }
                echo renderMessage("* Usuario con ID " . $userId . " eliminado exitosamente.", 'success');
            } else {
                echo renderMessage("* ERROR: al eliminar el usuario con ID " . $userId . ".", 'error');
            }
        } else {
            echo renderMessage("* Eliminación cancelada. El usuario con ID " . $userId . " no ha sido eliminado.", 'info');
        }
        
        echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
        include getPath('views/partials/footer.php');
    }

?>
