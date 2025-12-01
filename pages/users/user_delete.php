<?php
/*
 * Page to delete a user.
 * Handles confirmation and deletion processing.
 * Uses functions from lib/business/user_operations and lib/presentation/user_views modules.
 * Author: José Antonio Cortés Ferre
 */

require_once '../../config/init.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/business/auth_operations.php');
require_once getPath('views/user_views.php');

Permissions::require(Permissions::USER_DELETE);

$pageTitle = "Delete User";
$pageHeader = "Confirm Deletion";

try {
    // Show confirmation on GET
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
    
    // Process deletion on POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['confirm'])) {
        // Validate CSRF
        if (!CSRF::validate($_POST['csrf_token'] ?? '')) {
            Session::setFlash('error', 'Security error: Invalid CSRF token.');
            header('Location: user_index.php');
            exit;
        }

        $userId = $_POST['id'];
        
        try {
            if ($_POST['confirm'] === 'yes') {
                // Get user data before deleting to clean up avatar
                $user = getUserById($userId);
                
                // Delete user
                $success = deleteUserById($userId);
                
                // Clean up avatar if exists
                if ($user && !empty($user['avatar'])) {
                    try {
                        deleteAvatarFile($user['avatar']);
                    } catch (AvatarException $e) {
                        error_log('Avatar cleanup failed: ' . $e->getMessage());
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
        // Invalid request
        Session::setFlash('error', 'No user ID provided.');
        header('Location: user_index.php');
        exit;
    }
} catch (Exception $e) {
    // Unexpected error - Let Global Handler handle it
    throw $e;
}
?>
