<?php
/**
 * Gouri Transport - Configuration File
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'gouri_transport');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Settings
define('APP_NAME', 'Gouri Transport');
define('APP_URL', 'http://localhost/Gouritransport');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development or production

// Paths
define('BASE_PATH', __DIR__);
define('INCLUDES_PATH', BASE_PATH . '/includes');
define('ASSETS_PATH', BASE_PATH . '/assets');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('ADMIN_PATH', BASE_PATH . '/admin');

// URL Paths
define('ASSETS_URL', APP_URL . '/assets');
define('UPLOADS_URL', APP_URL . '/uploads');
define('ADMIN_URL', APP_URL . '/admin');

// Session Settings - only if session not already active
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', 'noreply@gouritransport.com');
define('SMTP_FROM_NAME', 'Gouri Transport');
define('ADMIN_EMAIL', 'admin@gouritransport.com');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_NAME', 'gouri_transport_session');

// Pagination
define('ITEMS_PER_PAGE', 10);
define('ADMIN_ITEMS_PER_PAGE', 20);
