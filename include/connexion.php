<?php 
// config.php is now included in session.php to avoid session setting conflicts
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/SystemMonitor.php';
require_once __DIR__ . '/RBAC.php';

// Initialize secure database connection
$db = new Database();
$bd = $db->getConnection(); // Keep $bd for backward compatibility

// Initialize system monitoring
$systemMonitor = new SystemMonitor($db);

// Initialize RBAC system
$rbac = new RBAC($db);

// Initialize default roles if they don't exist
try {
    $rbac->initializeDefaultRoles();
} catch (Exception $e) {
    error_log("Failed to initialize default roles: " . $e->getMessage());
}
?>