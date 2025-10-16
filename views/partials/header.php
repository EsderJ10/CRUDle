<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'CRUD PHP'; ?></title>
    
    <!-- Theme Initialization - Must run before CSS loads to prevent flash -->
    <script src="<?php echo getWebPath('assets/js/theme-init.js'); ?>"></script>
    
    <link rel="stylesheet" href="<?php echo getWebPath('assets/css/styles.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- JavaScript Files -->
    <script src="<?php echo getWebPath('assets/js/app.js'); ?>" defer></script>
    <script src="<?php echo getWebPath('assets/js/dashboard.js'); ?>" defer></script>
</head>
<body class="dashboard-layout">
    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <i class="fas fa-database brand-icon"></i>
                <span class="brand-text">CRUD System</span>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="nav-items">
                <li class="nav-item">
                    <a href="<?php echo getWebPath('index.php'); ?>" class="nav-link" data-page="dashboard">
                        <i class="fas fa-chart-pie nav-icon"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo getWebPath('pages/users/user_index.php'); ?>" class="nav-link" data-page="users">
                        <i class="fas fa-users nav-icon"></i>
                        <span class="nav-text">Usuarios</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo getWebPath('pages/users/user_create.php'); ?>" class="nav-link" data-page="create">
                        <i class="fas fa-user-plus nav-icon"></i>
                        <span class="nav-text">Crear Usuario</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="profile-info">
                        <span class="profile-name">Admin</span>
                        <span class="profile-role">Administrador</span>
                    </div>
                </div>
            </div>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="main-wrapper">
        <!-- Top Header Bar -->
        <header class="top-header">
            <div class="header-left">
                <button class="mobile-toggle" id="mobileToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title"><?php echo isset($pageHeader) ? $pageHeader : 'Dashboard'; ?></h1>
            </div>
            <div class="header-right">
                <div class="header-actions">
                    <button class="theme-toggle" id="themeToggle" title="Cambiar tema">
                        <i class="fas fa-sun theme-icon-light"></i>
                        <i class="fas fa-moon theme-icon-dark"></i>
                    </button>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="page-content">
            <div class="content-container">

