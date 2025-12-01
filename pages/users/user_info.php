<?php
/*
 * Page to show detailed user information.
 * Uses functions from lib/business/user_operations and lib/presentation/user_views modules.
 * Author: José Antonio Cortés Ferre
 */

require_once '../../config/init.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('views/user_views.php');

$pageTitle = "User Information";
$pageHeader = "User Details";

try {
    // Validate that an ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        include getPath('views/partials/header.php');
        echo renderMessage('ERROR: No user ID provided.', 'error');
        echo '<p><a href="user_index.php">Back to User List</a></p>';
        include getPath('views/partials/footer.php');
        exit;
    }
    
    $userId = $_GET['id'];
    
    // Load user
    try {
        $user = getUserById($userId);
        
        if ($user === null) {
            throw new ResourceNotFoundException(
                'User not found: ' . $userId,
                'User does not exist.'
            );
        }
    } catch (ResourceNotFoundException $e) {
        include getPath('views/partials/header.php');
        echo renderMessage('ERROR: ' . $e->getUserMessage(), 'error');
        echo '<p><a href="user_index.php">Back to User List</a></p>';
        include getPath('views/partials/footer.php');
        exit;
    }catch (UserOperationException $e) {
        include getPath('views/partials/header.php');
        echo renderMessage('ERROR: ' . $e->getUserMessage(), 'error');
        echo '<p><a href="user_index.php">Back to User List</a></p>';
        include getPath('views/partials/footer.php');
        exit;
    }
    
    // Show user information
    include getPath('views/partials/header.php');
    echo renderUserInfo($user);
    include getPath('views/partials/footer.php');
    
} catch (Exception $e) {
    // Unexpected error
    include getPath('views/partials/header.php');
    echo renderMessage('ERROR: An unexpected error occurred. ' . $e->getMessage(), 'error');
    echo '<p><a href="user_index.php">Back to User List</a></p>';
    include getPath('views/partials/footer.php');
    error_log('Unexpected error in user_info.php: ' . $e->getMessage());
    exit;
}
?>
