<?php
/*
 * Lógica de negocio para la autenticación de usuarios.
 * Maneja el inicio de sesión, cierre de sesión y verificación de estado.
 */

require_once __DIR__ . '/../../config/paths.php';
require_once getPath('lib/core/Database.php');
require_once getPath('lib/core/Session.php');
require_once getPath('lib/core/exceptions.php');
require_once getPath('lib/helpers/utils.php');

/**
 * Intenta autenticar a un usuario con email y contraseña.
 * 
 * @param string $email
 * @param string $password
 * @return bool True si la autenticación fue exitosa
 * @throws AuthException Si las credenciales son inválidas
 */
function login($email, $password) {
    try {
        if (empty($email) || empty($password)) {
            throw new AuthException('Email and password are required', 'Email y contraseña son obligatorios.');
        }

        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM users WHERE email = ?", [$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            throw new AuthException('Invalid credentials', 'Credenciales incorrectas.');
        }

        // Iniciar sesión
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
            'Error al iniciar sesión.',
            0,
            $e
        );
    }
}

/**
 * Cierra la sesión actual.
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
 * Verifica si el usuario está autenticado.
 * 
 * @return bool
 */
function isLoggedIn() {
    Session::init();
    return isset($_SESSION['user_id']);
}

/**
 * Requiere que el usuario esté autenticado.
 * Si no lo está, redirige al login.
 */
function requireLogin() {
    if (!isLoggedIn()) {
        Session::setFlash('warning', 'Debes iniciar sesión para acceder a esta página.');
        header('Location: ' . getWebPath('pages/auth/login.php'));
        exit;
    }
}
?>