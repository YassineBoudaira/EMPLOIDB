<?php 
require_once 'config.php';
require_once 'Database.php';
require_once 'Security.php';

// Initialize secure database connection
$db = new Database();
$bd = $db->getConnection(); // Keep $bd for backward compatibility
?>