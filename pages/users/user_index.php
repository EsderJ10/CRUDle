<?php
// User listing page

require_once '../../config/paths.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/presentation/user_views.php');

// Set page variables for partials
$pageTitle = "Gestión de Usuarios";
$pageHeader = "Lista de Usuarios";

// Include header
include getPath('views/partials/header.php');

// Check for messages in URL parameters
if (isset($_GET['message'])) {
    echo renderMessage($_GET['message'], 'success');
}
if (isset($_GET['error'])) {
    echo renderMessage($_GET['error'], 'error');
}

// Get users from business layer
$users = getAllUsers();

// Render users table
echo renderUserTable($users);

// Include footer
include getPath('views/partials/footer.php');
?>
