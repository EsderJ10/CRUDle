<?php
// Path definitions for the application

// Web root path
define('WEB_ROOT', '/crud_php');

// Folders
define('BASE_PATH', __DIR__ . '/../');
define('LIB_PATH', BASE_PATH . 'lib/');
define('PAGES_PATH', BASE_PATH . 'pages/');
define('VIEWS_PATH', BASE_PATH . 'views/');
define('CONFIG_PATH', BASE_PATH . 'config/');

// Library paths
define('CORE_PATH', LIB_PATH . 'core/');
define('BUSINESS_PATH', LIB_PATH . 'business/');
define('PRESENTATION_PATH', LIB_PATH . 'presentation/');
define('HELPERS_PATH', LIB_PATH . 'helpers/');

// View paths
define('PARTIALS_PATH', VIEWS_PATH . 'partials/');
define('COMPONENTS_PATH', VIEWS_PATH . 'components/');
define('FORMS_PATH', COMPONENTS_PATH . 'forms/');

// Data path
define('DATA_PATH', BASE_PATH . 'data/');

// Assets path
define('ASSETS_PATH', BASE_PATH . 'assets/');
define('CSS_PATH', ASSETS_PATH . 'css/');

// Helper function to get correct path based on current location
function getPath($relativePath) {
    return realpath(BASE_PATH . ltrim($relativePath, '/'));
}

// File include helpers
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

// Helper function to get web URL paths
function getWebPath($relativePath) {
    return WEB_ROOT . '/' . ltrim($relativePath, '/');
}
?>
