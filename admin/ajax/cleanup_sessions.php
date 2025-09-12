<?php
// AJAX endpoint for cleaning up old sessions
include '../../include/config.php';
include '../../include/connexion.php';
include '../../include/LoginSecurity.php';

header('Content-Type: application/json');

try {
    $loginSecurity = new LoginSecurity($db);
    $loginSecurity->cleanupOldSessions();
    
    echo json_encode([
        'success' => true,
        'message' => 'Sessions nettoyées avec succès'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
