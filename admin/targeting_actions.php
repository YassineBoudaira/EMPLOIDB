<?php
// Include configuration first (before any session starts)
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';
include __DIR__ . '/../include/connexion.php';

// Check if user is admin
if (!Security::isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Handle targeting actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'create') {
            // Create new targeting rule
            $ad_id = $_POST['ad_id'] ?? 0;
            $target_type = $_POST['target_type'] ?? '';
            $target_value = $_POST['target_value'] ?? '';
            $priority = $_POST['priority'] ?? 1;
            
            if (empty($target_type) || empty($target_value)) {
                throw new Exception('Type et valeur de ciblage sont requis');
            }
            
            $targeting_id = $db->insert("
                INSERT INTO ad_targeting (ad_id, target_type, target_value, priority, status, created_at) 
                VALUES (?, ?, ?, ?, 'active', NOW())
            ", [$ad_id, $target_type, $target_value, $priority]);
            
            echo json_encode(['success' => true, 'message' => 'Règle de ciblage créée avec succès', 'id' => $targeting_id]);
            
        } elseif ($action === 'update') {
            // Update targeting rule
            $targeting_id = $_POST['targeting_id'] ?? 0;
            $target_type = $_POST['target_type'] ?? '';
            $target_value = $_POST['target_value'] ?? '';
            $priority = $_POST['priority'] ?? 1;
            $status = $_POST['status'] ?? 'active';
            
            if (empty($target_type) || empty($target_value)) {
                throw new Exception('Type et valeur de ciblage sont requis');
            }
            
            $db->update("
                UPDATE ad_targeting 
                SET target_type = ?, target_value = ?, priority = ?, status = ?, updated_at = NOW()
                WHERE id = ?
            ", [$target_type, $target_value, $priority, $status, $targeting_id]);
            
            echo json_encode(['success' => true, 'message' => 'Règle de ciblage mise à jour avec succès']);
            
        } elseif ($action === 'delete') {
            // Delete targeting rule
            $targeting_id = $_POST['targeting_id'] ?? 0;
            
            $db->delete("DELETE FROM ad_targeting WHERE id = ?", [$targeting_id]);
            
            echo json_encode(['success' => true, 'message' => 'Règle de ciblage supprimée avec succès']);
            
        } elseif ($action === 'activate') {
            // Activate targeting rule
            $targeting_id = $_POST['targeting_id'] ?? 0;
            
            $db->update("UPDATE ad_targeting SET status = 'active' WHERE id = ?", [$targeting_id]);
            
            echo json_encode(['success' => true, 'message' => 'Règle de ciblage activée avec succès']);
            
        } elseif ($action === 'deactivate') {
            // Deactivate targeting rule
            $targeting_id = $_POST['targeting_id'] ?? 0;
            
            $db->update("UPDATE ad_targeting SET status = 'inactive' WHERE id = ?", [$targeting_id]);
            
            echo json_encode(['success' => true, 'message' => 'Règle de ciblage désactivée avec succès']);
            
        } else {
            throw new Exception('Action non reconnue');
        }
        
    } catch (Exception $e) {
        error_log("Database error in targeting_actions.php: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>
