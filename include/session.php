<?php
// Ensure config.php is included before any session operations
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/config.php';
}

// Now start the session with the configured settings
session_start();

if(empty($_SESSION)){
    header('location: login.php');
}