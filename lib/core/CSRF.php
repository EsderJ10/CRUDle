<?php
/*
 * Class for Cross-Site Request Forgery (CSRF) protection.
 * Generates and validates tokens to ensure form submissions come from the application.
 * Author: José Antonio Cortés Ferre
 */

class CSRF {
    /**
     * Generates a new CSRF token and stores it in the session.
     * If one already exists, returns it (to allow multiple tabs/forms).
     * @return string The CSRF token
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
     * Gets the current CSRF token.
     * @return string|null The token or null if it doesn't exist
     */
    public static function getToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['csrf_token'] ?? null;
    }

    /**
     * Validates a CSRF token.
     * @param string $token The token to validate
     * @return bool True if valid, False if not
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
     * Renders a hidden input field with the CSRF token.
     * @return string HTML of the input
     */
    public static function renderInput() {
        $token = self::generate();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}
?>
