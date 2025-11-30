<?php
/*
 * Project path definitions.
 * Helper functions to get paths are also defined here.
 */ 

// Environment detection
if (getenv('APP_ENV') === 'development' || isset($_SERVER['APP_ENV'])) {
    // Docker execution
    define('WEB_ROOT', '');
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Production / Local execution
    define('WEB_ROOT', '/CRUDle');
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
}

// Directories
define('BASE_PATH', realpath(__DIR__ . '/../') . '/');
define('LIB_PATH', BASE_PATH . 'lib/');
define('PAGES_PATH', BASE_PATH . 'pages/');
define('VIEWS_PATH', BASE_PATH . 'views/');
define('CONFIG_PATH', BASE_PATH . 'config/');

// Libraries
define('CORE_PATH', LIB_PATH . 'core/');
define('BUSINESS_PATH', LIB_PATH . 'business/');
define('PRESENTATION_PATH', LIB_PATH . 'presentation/');
define('HELPERS_PATH', LIB_PATH . 'helpers/');

// Views
define('PARTIALS_PATH', VIEWS_PATH . 'partials/');
define('COMPONENTS_PATH', VIEWS_PATH . 'components/');
define('FORMS_PATH', COMPONENTS_PATH . 'forms/');

// Data and logs
define('DATA_PATH', BASE_PATH . 'data/');
define('LOGS_PATH', BASE_PATH . 'logs/');
define('ERROR_LOG_FILE', LOGS_PATH . 'error.log');

// Assets 
define('ASSETS_PATH', BASE_PATH . 'assets/');
define('CSS_PATH', ASSETS_PATH . 'css/');
define('JS_PATH', ASSETS_PATH . 'js/');
define('IMAGES_PATH', ASSETS_PATH . 'images/');

// File uploads
define('UPLOADS_PATH', BASE_PATH . 'uploads/');
define('AVATARS_PATH', UPLOADS_PATH . 'avatars/');

function getPath($relativePath) {
    return realpath(BASE_PATH . ltrim($relativePath, '/'));
}

function includeFile($filePath) {
    $fullPath = BASE_PATH . ltrim($filePath, '/');
    if (file_exists($fullPath)) {
        include_once $fullPath;
        return true;
    }
    return false;
}

function requireFile($filePath) {
    $fullPath = BASE_PATH . ltrim($filePath, '/');
    if (file_exists($fullPath)) {
        require_once $fullPath;
        return true;
    }
    return false;
}

function getWebPath($relativePath) {
    return WEB_ROOT . '/' . ltrim($relativePath, '/');
}

function getUploadPath($relativePath = '') {
    return UPLOADS_PATH . ltrim($relativePath, '/');
}

function getAvatarPath($filename = '') {
    return AVATARS_PATH . ltrim($filename, '/');
}

function getWebUploadPath($relativePath = '') {
    return getWebPath('uploads/' . ltrim($relativePath, '/'));
}
?>
