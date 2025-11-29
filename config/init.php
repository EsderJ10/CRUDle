<?php
/*
 * Archivo de inicialización central.
 * Carga todas las dependencias necesarias y configura el entorno.
 */

// Rutas
require_once __DIR__ . '/paths.php';

// Configuración
require_once getPath('config/config.php');

// Núcleo
require_once getPath('lib/core/exceptions.php');
require_once getPath('lib/core/error_handler.php');
require_once getPath('lib/core/Session.php');
require_once getPath('lib/core/CSRF.php');
require_once getPath('lib/core/Permissions.php');

// Helpers
require_once getPath('lib/helpers/utils.php');

// Database Core
require_once getPath('lib/core/Database.php');

// Sesión
Session::init();
date_default_timezone_set('Europe/Madrid');

// Session Synchronization & Security Check
if (Session::get('user_id')) {
    try {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT id, role, status, name, email, avatar_path FROM users WHERE id = ?", [Session::get('user_id')]);
        $user = $stmt->fetch();

        // Ghost User Check
        if (!$user) {
            session_destroy();
            header('Location: ' . getWebPath('pages/auth/login.php?error=account_deleted'));
            exit;
        }

        // Status Check
        if ($user['status'] !== 'active') {
            session_destroy();
            header('Location: ' . getWebPath('pages/auth/login.php?error=account_inactive'));
            exit;
        }

        // Data Synchronization
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_avatar'] = $user['avatar_path']; 

    } catch (Exception $e) {
        error_log("Session Sync Error: " . $e->getMessage());
    }
}
?>
