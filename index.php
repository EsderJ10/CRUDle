<?php
/* 
 * Página principal del dashboard.
 * Sirve como punto de entrada principal para la aplicación CRUD PHP.
 * Utiliza funciones de los módulos lib/business/user_operations y lib/presentation/user_views.
 * Autor: José Antonio Cortés Ferre
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
    // If DB fails, we might be in trouble, but let's try to proceed or show error
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
            <h2>Acciones Principales</h2>
            <div class="mb-6">
                <h3>Gestión de Usuarios</h3>
                <div class="actions mt-4">
                    <a href="pages/users/user_index.php" class="btn btn-primary">Ver Todos los Usuarios</a>
                    <a href="pages/users/user_create.php" class="btn btn-success">Crear Nuevo Usuario</a>
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
    // Error inesperado
    include 'views/partials/header.php';
    echo renderMessage('ERROR: Ocurrió un error inesperado. ' . $e->getMessage(), 'error');
    include 'views/partials/footer.php';
    error_log('Unexpected error in dashboard: ' . $e->getMessage());
    exit;
}
?>
