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

// Sesión
Session::init();
date_default_timezone_set('Europe/Madrid');
?>
