<?php
/*
 * Clase para protección contra Cross-Site Request Forgery (CSRF).
 * Genera y valida tokens para asegurar que los envíos de formularios provienen de la aplicación.
 * Autor: José Antonio Cortés Ferre
 */

class CSRF {
    /**
     * Genera un nuevo token CSRF y lo almacena en la sesión.
     * Si ya existe uno, lo devuelve (para permitir múltiples pestañas/formularios).
     * @return string El token CSRF
     */
    public static function generate() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Obtiene el token CSRF actual.
     * @return string|null El token o null si no existe
     */
    public static function getToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['csrf_token'] ?? null;
    }

    /**
     * Valida un token CSRF.
     * @param string $token El token a validar
     * @return bool True si es válido, False si no
     */
    public static function validate($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Renderiza un campo input hidden con el token CSRF.
     * @return string HTML del input
     */
    public static function renderInput() {
        $token = self::generate();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}
?>
