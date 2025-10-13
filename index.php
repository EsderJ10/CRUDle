<?php
// Dashboard - Main entry point for the CRUD PHP Application

// Include configuration and paths
require_once 'config/paths.php';
require_once 'lib/business/user_operations.php';
require_once 'lib/presentation/user_views.php';

// Set page variables for partials
$pageTitle = "Dashboard - CRUD PHP";
$pageHeader = "Dashboard - Sistema de Gestión de Usuarios";
$isInLibFolder = false;

// Get data from business layer
$stats = getUserStatistics();
$systemStatus = checkSystemStatus();

// Include header
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
// Include footer
include 'views/partials/footer.php';
?>
