<?php
// Ensure config.php is included before any session operations
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/../include/config.php';
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set session timeout (use default if not defined)
$session_timeout = defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT : 3600; // Default 1 hour

if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $session_timeout) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration']) || (time() - $_SESSION['last_regeneration']) > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Update login time on each request
if (isset($_SESSION['login_time'])) {
    $_SESSION['login_time'] = time();
}