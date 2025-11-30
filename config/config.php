<?php
/*
 * Global application configuration constants.
 */

// Application
define('APP_NAME', 'CRUD PHP Application');
define('APP_VERSION', '2.0.0');
define('APP_ENV', 'development'); // Change to 'production' in production environment

// Database
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'crudle');
define('DB_USER', getenv('DB_USER') ?: 'crudle_user');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'crudle_password');

// Date format
define('DATE_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd/m/Y H:i');

// Value limits for validation
define('MAX_NAME_LENGTH', 100);
define('MAX_EMAIL_LENGTH', 150);
define('MIN_NAME_LENGTH', 2);
define('ERROR_LOG_MAX_SIZE', 5 * 1024 * 1024);

// SMTP Configuration
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'mailhog');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 1025);
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_FROM', getenv('SMTP_FROM') ?: 'no-reply@crudle.com');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'CRUDle System');

// Base Application URL (for emails)
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8080');
?>
