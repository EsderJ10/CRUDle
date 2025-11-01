<?php
/*
 * Constantes globales de configuración de la aplicación.
 */

// Aplicación
define('APP_NAME', 'CRUD PHP Application');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // Cambiar a 'production' en entorno de producción

// Data
define('DATA_FILE', 'data/usuarios.csv');
define('DATA_DIR', 'data/');

// Formato de fecha
define('DATE_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd/m/Y H:i');

// Límites de valores para la validación
define('MAX_NAME_LENGTH', 100);
define('MAX_EMAIL_LENGTH', 150);
define('MIN_NAME_LENGTH', 2);
define('ERROR_LOG_MAX_SIZE', 5 * 1024 * 1024);
?>
