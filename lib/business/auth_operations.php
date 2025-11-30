<?php
/*
 * Business logic for user authentication.
 * Handles login, logout, and status verification.
 */

require_once __DIR__ . '/../../config/paths.php';
require_once getPath('lib/core/Database.php');
require_once getPath('lib/core/Session.php');
require_once getPath('lib/core/exceptions.php');
require_once getPath('lib/helpers/utils.php');

/**
 * Attempts to authenticate a user with email and password.
 * 
 * @param string $email
 * @param string $password
 * @return bool True if authentication was successful
 * @throws AuthException If credentials are invalid
 */
function login($email, $password) {
    try {
        if (empty($email) || empty($password)) {
            throw new AuthException('Email and password are required', 'Email and password are required.');
        }

        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM users WHERE email = ?", [$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            throw new AuthException('Invalid credentials', 'Invalid credentials.');
        }

        if ($user['status'] !== 'active') {
            throw new AuthException('Account not active', 'Account not active.');
        }

        // Start session
        Session::init();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_avatar'] = normalizeAvatarPath($user['avatar_path']);
        $_SESSION['last_activity'] = time();

        return true;
    } catch (AuthException $e) {
        throw $e;
    } catch (Exception $e) {
        throw new AuthException(
            'Login error: ' . $e->getMessage(),
            'Error at login. Please try again later.',
            0,
            $e
        );
    }
}

/**
 * Closes the current session.
 */
function logout() {
    Session::init();
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Checks if the user is authenticated.
 * 
 * @return bool
 */
function isLoggedIn() {
    Session::init();
    return isset($_SESSION['user_id']);
}

/**
 * Requires the user to be authenticated.
 * If not, redirects to login.
 */
function requireLogin() {
    if (!isLoggedIn()) {
        Session::setFlash('warning', 'You must be logged in to access this page.');
        header('Location: ' . getWebPath('pages/auth/login.php'));
        exit;
    }
}

/**
 * Checks if the current user is an administrator.
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Checks if the current user is an editor.
 */
function isEditor() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'editor';
}

/**
 * Checks if the current user is a viewer.
 */
function isViewer() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'viewer';
}

/**
 * Requires the user to be an administrator.
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        Session::setFlash('error', 'Access denied. Admin permissions required.');
        header('Location: ' . getWebPath('index.php'));
        exit;
    }
}

/**
 * Requires the user to be an editor or administrator.
 */
function requireEditorOrAdmin() {
    requireLogin();
    if (!isAdmin() && !isEditor()) {
        Session::setFlash('error', 'Access denied. Editor or admin permissions required.');
        header('Location: ' . getWebPath('index.php'));
        exit;
    }
}
?>