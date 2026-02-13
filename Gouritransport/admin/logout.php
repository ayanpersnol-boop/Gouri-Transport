<?php
/**
 * Gouri Transport - Admin Logout
 */

if (session_status() === PHP_SESSION_NONE) {
    session_name('gouri_transport_admin');
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// Clear session
$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

// Redirect to login
redirect(ADMIN_URL . '/login.php?logout=1&msg=loggedout');
