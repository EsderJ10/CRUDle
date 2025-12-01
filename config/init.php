<?php
/*
 * Initialization file.
 * Loads all necessary dependencies and configures the environment.
 */

// Paths
require_once __DIR__ . '/paths.php';

// Configuration
require_once getPath('config/config.php');

// Core
require_once getPath('lib/core/exceptions.php');
require_once getPath('lib/core/error_handler.php');
require_once getPath('lib/core/Session.php');
require_once getPath('lib/core/CSRF.php');
require_once getPath('lib/core/Permissions.php');

// Business Logic
require_once getPath('lib/business/auth_operations.php');

// Helpers
require_once getPath('lib/helpers/utils.php');

// Database Core
require_once getPath('lib/core/Database.php');

// Session
Session::init();
date_default_timezone_set('Europe/Madrid');

// Session Synchronization & Security Check
if (Session::get('user_id')) {
    try {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT id, role, status, name, email, avatar_path FROM users WHERE id = ?", [Session::get('user_id')]);
        $sessionUser = $stmt->fetch();

        // Ghost User Check
        if (!$sessionUser) {
            Session::destroy();
            header('Location: ' . getWebPath('pages/auth/login.php?error=account_deleted'));
            exit;
        }

        // Status Check
        if ($sessionUser['status'] !== 'active') {
            Session::destroy();
            header('Location: ' . getWebPath('pages/auth/login.php?error=account_inactive'));
            exit;
        }

        // Data Synchronization
        Session::set('user_role', $sessionUser['role']);
        Session::set('user_name', $sessionUser['name']);
        Session::set('user_email', $sessionUser['email']);
        Session::set('user_avatar', $sessionUser['avatar_path']); 

    } catch (Exception $e) {
        error_log("Session Sync Error: " . $e->getMessage());
    }
}
?>
