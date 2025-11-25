<?php
/*
 * Archivo de inicialización central.
 * Carga todas las dependencias necesarias y configura el entorno.
 */

// 1. Cargar rutas
require_once __DIR__ . '/paths.php';

// 2. Cargar configuración
require_once getPath('config/config.php');

// 3. Cargar núcleo
require_once getPath('lib/core/exceptions.php');
require_once getPath('lib/core/error_handler.php');
require_once getPath('lib/core/Database.php');
require_once getPath('lib/core/Session.php');

// 4. Cargar helpers comunes
require_once getPath('lib/helpers/utils.php');

// 5. Inicializar sesión
Session::init();

// 6. Configurar zona horaria (opcional, buena práctica)
date_default_timezone_set('Europe/Madrid'); // Ajustar según necesidad
?>
