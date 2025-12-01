<?php
/*
 * Class for session management and flash messages.
 * Allows storing temporary messages to be shown in the next request.
 * Author: José Antonio Cortés Ferre
 */

class Session {
    /**
     * Starts the session if not already started.
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Sets a flash message.
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message content
     */
    public static function setFlash($type, $message) {
        self::init();
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Gets and clears flash messages.
     * @return array List of flash messages
     */
    public static function getFlashes() {
        self::init();
        $flashes = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $flashes;
    }

    /**
     * Checks if there are pending flash messages.
     * @return bool
     */
    public static function hasFlashes() {
        self::init();
        return !empty($_SESSION['flash_messages']);
    }

    /**
     * Gets a session value.
     * @param string $variable Session variable name.
     * @return mixed Session variable value or null if not exists.
     */
    public static function get($variable = null) {
        self::init();
        return $_SESSION[$variable] ?? null;
    }

    /**
     * Checks if a session variable exists.
     * @param string $variable Session variable name.
     * @return bool
     */
    public static function has($variable) {
        self::init();
        return isset($_SESSION[$variable]);
    }

    /**
     * Sets a session value.
     * @param string $variable Session variable name.
     * @param mixed $value Value to store.
     */
    public static function set($variable, $value) {
        self::init();
        $_SESSION[$variable] = $value;
    }

    /**
     * Deletes a session value.
     * @param string $variable Session variable name.
     */
    public static function delete($variable) {
        self::init();
        if (isset($_SESSION[$variable])) {
            unset($_SESSION[$variable]);
        }
    }

    /**
     * Destroys the session and clears cookies.
     */
    public static function destroy() {
        self::init();
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
}
?>