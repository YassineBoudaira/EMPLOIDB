<?php
// AJAX endpoint for unblocking IP addresses
include '../../include/config.php';
include '../../include/connexion.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $ipAddress = $input['ip_address'] ?? null;
    
    if (!$ipAddress) {
        throw new Exception('Adresse IP requise');
    }
    
    $db->delete("DELETE FROM ip_blocklist WHERE ip_address = ?", [$ipAddress]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Adresse IP débloquée avec succès'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
