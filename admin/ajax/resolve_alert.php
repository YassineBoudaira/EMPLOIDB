<?php
// AJAX endpoint for resolving security alerts
include '../../include/config.php';
include '../../include/connexion.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $alertId = $input['alert_id'] ?? null;
    
    if (!$alertId) {
        throw new Exception('ID d\'alerte requis');
    }
    
    $db->update("
        UPDATE security_alerts 
        SET is_resolved = TRUE, resolved_at = NOW() 
        WHERE id = ?
    ", [$alertId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Alerte résolue avec succès'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
