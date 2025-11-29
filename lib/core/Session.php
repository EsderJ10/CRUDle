<?php
/*
 * Clase para la gestión de sesiones y mensajes flash.
 * Permite almacenar mensajes temporales que se muestran en la siguiente solicitud.
 * Autor: José Antonio Cortés Ferre
 */

class Session {
    /**
     * Inicia la sesión si no está iniciada.
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Establece un mensaje flash.
     * @param string $type Tipo de mensaje (success, error, warning, info)
     * @param string $message El contenido del mensaje
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
     * Obtiene y limpia los mensajes flash.
     * @return array Lista de mensajes flash
     */
    public static function getFlashes() {
        self::init();
        $flashes = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $flashes;
    }

    /**
     * Verifica si hay mensajes flash pendientes.
     * @return bool
     */
    public static function hasFlashes() {
        self::init();
        return !empty($_SESSION['flash_messages']);
    }

    /**
     * Obtiene un valor de sesión.
     * @param string $variable El nombre de la variable de sesión.
     * @return mixed El valor de la variable de sesión o null si no existe.
     */
    public static function get($variable = null) {
        self::init();
        return $_SESSION[$variable] ?? null;
    }
}
?>