<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'CRUD PHP'; ?></title>

    <!-- Inicialización del tema -->
    <script src="<?php echo getWebPath('assets/js/theme-init.js'); ?>"></script>
    
    <link rel="stylesheet" href="<?php echo getWebPath('assets/css/styles.css'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- JavaScript -->
    <script src="<?php echo getWebPath('assets/js/app.js'); ?>" defer></script>
    <script src="<?php echo getWebPath('assets/js/dashboard.js'); ?>" defer></script>
    <script src="<?php echo getWebPath('assets/js/user-form.js'); ?>" defer></script>
</head>
<body class="dashboard-layout">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <i class="fas fa-database brand-icon"></i>
                <span class="brand-text">CRUDle</span>
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
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-profile" id="userProfileDropdown">
                    <div class="profile-avatar">
                        <?php if (isset($_SESSION['user_avatar']) && $_SESSION['user_avatar']): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['user_avatar']); ?>" alt="Avatar">
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="profile-info">
                        <span class="profile-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
                        <span class="profile-role"><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'Rol'); ?></span>
                    </div>
                    <i class="fas fa-chevron-down profile-chevron"></i>
                    
                    <div class="profile-dropdown-menu">
                        <div class="dropdown-user-header">
                            <span class="dropdown-user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuario'); ?></span>
                            <span class="dropdown-user-email"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></span>
                        </div>
                        <ul class="dropdown-list">
                            <li>
                                <a href="<?php echo getWebPath('pages/users/user_info.php?id=' . $_SESSION['user_id']); ?>" class="dropdown-item">
                                    <i class="fas fa-user"></i> Ver Perfil
                                </a>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                            </li>
                            <li>
                                <a href="<?php echo getWebPath('pages/auth/logout.php'); ?>" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <?php else: ?>
                <div class="user-profile justify-content-center">
                    <a href="<?php echo getWebPath('pages/auth/login.php'); ?>" class="btn btn-primary btn-sm w-100 login-btn btn-sidebar-login">
                        <i class="fas fa-sign-in-alt"></i>
                        <span class="login-text">Iniciar Sesión</span>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
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
                <?php
                // Mostrar mensajes flash
                if (class_exists('Session') && Session::hasFlashes()) {
                    $flashes = Session::getFlashes();
                    foreach ($flashes as $flash) {
                        echo renderMessage($flash['message'], $flash['type']);
                    }
                }

                if (isset($_GET['message'])) {
                    $type = $_GET['type'] ?? 'success';
                    echo renderMessage($_GET['message'], $type);
                }
                ?>