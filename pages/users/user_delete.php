<?php
// It will be called from the user_index.php and it will receive the user id (user_delete?id=x). It will show a confirmation message and if confirmed, it will handle the deletion of the user from the CSV.
// The user always exists, because the delete link is only shown for existing users.
// After deletion, it will redirect to the user_index.php page.

// Include business logic and presentation
require_once '../../config/paths.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/presentation/user_views.php');

// Set page variables for partials
$pageTitle = "Eliminar Usuario";
$pageHeader = "Confirmar Eliminación";

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        // Include header
        include getPath('views/partials/header.php');
        // Use presentation layer to show confirmation
        $userId = $_GET['id'];
        echo renderDeleteConfirmation($userId);
        include getPath('views/partials/footer.php');
    }

    // If the request is POST, it means the user has confirmed or cancelled the deletion.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['confirm'])) {
        // Include header
        include getPath('views/partials/header.php');
        $userId = $_POST['id'];
        
        if ($_POST['confirm'] === 'yes') {
            // Get user data before deletion to clean up avatar
            $user = getUserById($userId);
            
            // Use business logic to delete user
            $success = deleteUserById($userId);
            
            if ($success) {
                // Clean up avatar file if it exists
                if ($user && !empty($user['avatar'])) {
                    deleteAvatarFile($user['avatar']);
                }
                echo renderMessage("Usuario con ID " . $userId . " eliminado exitosamente.", 'success');
            } else {
                echo renderMessage("ERROR: al eliminar el usuario con ID " . $userId . ".", 'error');
            }
        } else {
            // Cancellation message
            echo renderMessage("Eliminación cancelada. El usuario con ID " . $userId . " no ha sido eliminado.", 'info');
        }
        
        echo '<p><a href="user_index.php">Volver a la lista de usuarios</a></p>';
        include getPath('views/partials/footer.php');
    }

?>
