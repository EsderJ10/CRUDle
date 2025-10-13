<?php
// Here are defined the main configuration constants settings for the applicaction

// Application settings
define('APP_NAME', 'CRUD PHP Application');
define('APP_VERSION', '1.0.0');

// Data settings
define('DATA_FILE', 'data/usuarios.csv');
define('DATA_DIR', 'data/');

// Default values
define('DEFAULT_ROLE', 'viewer');
define('ROLES', ['admin', 'editor', 'viewer']);

// Pagination
define('USERS_PER_PAGE', 10);

// Date format
define('DATE_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd/m/Y H:i');

// Validation limits
define('MAX_NAME_LENGTH', 100);
define('MAX_EMAIL_LENGTH', 150);
define('MIN_NAME_LENGTH', 2);
?>
