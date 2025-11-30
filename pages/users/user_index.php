<?php
/*
 * Page to list users.
 * Displays a table with all users and options to edit or delete.
 * Uses functions from lib/business/user_operations and lib/presentation/user_views modules.
 * Author: José Antonio Cortés Ferre
 */

require_once '../../config/init.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/business/auth_operations.php');
require_once getPath('lib/presentation/user_views.php');

requireLogin();

$pageTitle = "User Management";
$pageHeader = "User List";

try {
    include getPath('views/partials/header.php');
    
    // Flash and URL messages are handled automatically in header.php
    
    // Get and display users
    try {
        $users = getAllUsers();
        echo renderUserTable($users);
    } catch (UserOperationException $e) {
        echo renderMessage('ERROR: ' . $e->getUserMessage(), 'error');
        echo '<p><a href="user_create.php" class="btn btn-primary">Create First User</a></p>';
    } catch (Exception $e) {
        echo renderMessage('ERROR: An error occurred while loading users. ' . $e->getMessage(), 'error');
        error_log('Error loading users: ' . $e->getMessage());
    }
    
    include getPath('views/partials/footer.php');
} catch (Exception $e) {
    // Unexpected error
    include getPath('views/partials/header.php');
    echo renderMessage('ERROR: An unexpected error occurred. ' . $e->getMessage(), 'error');
    include getPath('views/partials/footer.php');
    error_log('Unexpected error in user_index.php: ' . $e->getMessage());
    exit;
}
?>
