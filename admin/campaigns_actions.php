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

// Handle campaign actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'create') {
            // Create new campaign
            $name = $_POST['name'] ?? '';
            $campaign_type = $_POST['campaign_type'] ?? '';
            $budget = $_POST['budget'] ?? 0;
            $status = $_POST['status'] ?? 'pending';
            $description = $_POST['description'] ?? '';
            $start_date = $_POST['start_date'] ?? null;
            $end_date = $_POST['end_date'] ?? null;
            
            if (empty($name) || empty($campaign_type)) {
                throw new Exception('Nom et type de campagne sont requis');
            }
            
            $campaign_id = $db->insert("
                INSERT INTO ad_campaigns (name, campaign_type, budget, status, description, start_date, end_date, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ", [$name, $campaign_type, $budget, $status, $description, $start_date, $end_date]);
            
            echo json_encode(['success' => true, 'message' => 'Campagne créée avec succès', 'id' => $campaign_id]);
            
        } elseif ($action === 'update') {
            // Update campaign
            $campaign_id = $_POST['campaign_id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $campaign_type = $_POST['campaign_type'] ?? '';
            $budget = $_POST['budget'] ?? 0;
            $status = $_POST['status'] ?? 'pending';
            $description = $_POST['description'] ?? '';
            $start_date = $_POST['start_date'] ?? null;
            $end_date = $_POST['end_date'] ?? null;
            
            if (empty($name) || empty($campaign_type)) {
                throw new Exception('Nom et type de campagne sont requis');
            }
            
            $db->update("
                UPDATE ad_campaigns 
                SET name = ?, campaign_type = ?, budget = ?, status = ?, description = ?, start_date = ?, end_date = ?, updated_at = NOW()
                WHERE id = ?
            ", [$name, $campaign_type, $budget, $status, $description, $start_date, $end_date, $campaign_id]);
            
            echo json_encode(['success' => true, 'message' => 'Campagne mise à jour avec succès']);
            
        } elseif ($action === 'delete') {
            // Delete campaign
            $campaign_id = $_POST['campaign_id'] ?? 0;
            
            $db->delete("DELETE FROM campaign_ads WHERE campaign_id = ?", [$campaign_id]);
            $db->delete("DELETE FROM ad_campaigns WHERE id = ?", [$campaign_id]);
            
            echo json_encode(['success' => true, 'message' => 'Campagne supprimée avec succès']);
            
        } elseif ($action === 'activate') {
            // Activate campaign
            $campaign_id = $_POST['campaign_id'] ?? 0;
            
            $db->update("UPDATE ad_campaigns SET status = 'active' WHERE id = ?", [$campaign_id]);
            
            echo json_encode(['success' => true, 'message' => 'Campagne activée avec succès']);
            
        } elseif ($action === 'deactivate') {
            // Deactivate campaign
            $campaign_id = $_POST['campaign_id'] ?? 0;
            
            $db->update("UPDATE ad_campaigns SET status = 'inactive' WHERE id = ?", [$campaign_id]);
            
            echo json_encode(['success' => true, 'message' => 'Campagne désactivée avec succès']);
            
        } else {
            throw new Exception('Action non reconnue');
        }
        
    } catch (Exception $e) {
        error_log("Database error in campaigns_actions.php: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>
