<?php
/* 
 * Dashboard main page.
 * Serves as the main entry point for the PHP CRUD application.
 * Uses functions from lib/business/user_operations and lib/presentation/user_views modules.
 * Author: José Antonio Cortés Ferre
 */

require_once 'config/init.php';
require_once getPath('lib/business/user_operations.php');
require_once getPath('lib/presentation/user_views.php');
require_once getPath('lib/business/auth_operations.php');

// Check for first run
try {
    if (getUserCount() === 0) {
        header('Location: pages/setup.php');
        exit;
    }
} catch (Exception $e) {
    // For now, let it fall through to requireLogin which might fail too
}

requireLogin();

$pageTitle = "Dashboard - CRUDle";
$pageHeader = "Dashboard";
$isInLibFolder = false;

try {
    try {
        $stats = getUserStatistics();
        $systemStatus = checkSystemStatus();
    } catch (UserOperationException $e) {
        $stats = [
            'userCount' => 0,
            'usersByRole' => ['admin' => 0, 'editor' => 0, 'viewer' => 0],
            'recentUsers' => []
        ];
        $systemStatus = checkSystemStatus();
    }
    
    include 'views/partials/header.php';
?>

        <div class="card page-transition">
            <h2>Main Actions</h2>
            <div class="mb-6">
                <h3>Users Management</h3>
                <div class="actions mt-4">
                    <a href="pages/users/user_index.php" class="btn btn-primary">View All Users</a>
                    <a href="pages/users/user_create.php" class="btn btn-success">Invite New User</a>
                </div>
            </div>
        </div>

<?php 
    echo renderDashboardStats($stats);
    echo renderRecentUsers($stats['recentUsers']); 
?>

<?php
    include 'views/partials/footer.php';
} catch (Exception $e) {
    // Unexpected error
    include 'views/partials/header.php';
    echo renderMessage('ERROR: An unexpected error occurred. ' . $e->getMessage(), 'error');
    include 'views/partials/footer.php';
    error_log('Unexpected error in dashboard: ' . $e->getMessage());
    exit;
}
?>
