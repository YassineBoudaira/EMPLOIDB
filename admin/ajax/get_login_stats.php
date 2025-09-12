<?php
// AJAX endpoint for getting login statistics
include '../../include/config.php';
include '../../include/connexion.php';
include '../../include/LoginSecurity.php';

header('Content-Type: application/json');

try {
    $loginSecurity = new LoginSecurity($db);
    $stats = $loginSecurity->getLoginStats(1); // Last 24 hours
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
