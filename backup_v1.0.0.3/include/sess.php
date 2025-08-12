<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set session timeout
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > SESSION_TIMEOUT) {
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

// Helper functions - these are now in Security.php, so we'll use those instead
// Remove duplicate function declarations to avoid conflicts
?>