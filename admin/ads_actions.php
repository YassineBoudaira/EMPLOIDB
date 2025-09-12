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

// Handle ad actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'create') {
            // Create new ad
            $title = $_POST['title'] ?? '';
            $ad_type = $_POST['ad_type'] ?? '';
            $position = $_POST['position'] ?? '';
            $content = $_POST['content'] ?? '';
            $link_url = $_POST['link_url'] ?? '';
            $description = $_POST['description'] ?? '';
            $image_url = $_POST['image_url'] ?? '';
            $priority = $_POST['priority'] ?? 1;
            $start_date = $_POST['start_date'] ?? null;
            $end_date = $_POST['end_date'] ?? null;
            
            if (empty($title) || empty($ad_type) || empty($position) || empty($content) || empty($link_url)) {
                throw new Exception('Titre, type, position, contenu et URL de destination sont requis');
            }
            
            $ad_id = $db->insert("
                INSERT INTO advertisements (title, ad_type, position, content, link_url, description, image_url, priority, start_date, end_date, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ", [$title, $ad_type, $position, $content, $link_url, $description, $image_url, $priority, $start_date, $end_date]);
            
            echo json_encode(['success' => true, 'message' => 'Publicité créée avec succès', 'id' => $ad_id]);
            
        } elseif ($action === 'update') {
            // Update ad
            $ad_id = $_POST['ad_id'] ?? 0;
            $title = $_POST['title'] ?? '';
            $ad_type = $_POST['ad_type'] ?? '';
            $position = $_POST['position'] ?? '';
            $content = $_POST['content'] ?? '';
            $link_url = $_POST['link_url'] ?? '';
            $description = $_POST['description'] ?? '';
            $image_url = $_POST['image_url'] ?? '';
            $priority = $_POST['priority'] ?? 1;
            $status = $_POST['status'] ?? 'pending';
            $start_date = $_POST['start_date'] ?? null;
            $end_date = $_POST['end_date'] ?? null;
            
            if (empty($title) || empty($ad_type) || empty($position) || empty($content) || empty($link_url)) {
                throw new Exception('Titre, type, position, contenu et URL de destination sont requis');
            }
            
            $db->update("
                UPDATE advertisements 
                SET title = ?, ad_type = ?, position = ?, content = ?, link_url = ?, description = ?, image_url = ?, priority = ?, status = ?, start_date = ?, end_date = ?, updated_at = NOW()
                WHERE id = ?
            ", [$title, $ad_type, $position, $content, $link_url, $description, $image_url, $priority, $status, $start_date, $end_date, $ad_id]);
            
            echo json_encode(['success' => true, 'message' => 'Publicité mise à jour avec succès']);
            
        } elseif ($action === 'delete') {
            // Delete ad
            $ad_id = $_POST['ad_id'] ?? 0;
            
            // Delete related records first
            $db->delete("DELETE FROM ad_performance WHERE ad_id = ?", [$ad_id]);
            $db->delete("DELETE FROM ad_clicks WHERE ad_id = ?", [$ad_id]);
            $db->delete("DELETE FROM ad_impressions WHERE ad_id = ?", [$ad_id]);
            $db->delete("DELETE FROM ad_targeting WHERE ad_id = ?", [$ad_id]);
            $db->delete("DELETE FROM ad_scheduling WHERE ad_id = ?", [$ad_id]);
            $db->delete("DELETE FROM campaign_ads WHERE ad_id = ?", [$ad_id]);
            $db->delete("DELETE FROM ad_category_relations WHERE ad_id = ?", [$ad_id]);
            $db->delete("DELETE FROM advertisements WHERE id = ?", [$ad_id]);
            
            echo json_encode(['success' => true, 'message' => 'Publicité supprimée avec succès']);
            
        } elseif ($action === 'activate') {
            // Activate ad
            $ad_id = $_POST['ad_id'] ?? 0;
            
            $db->update("UPDATE advertisements SET status = 'active' WHERE id = ?", [$ad_id]);
            
            echo json_encode(['success' => true, 'message' => 'Publicité activée avec succès']);
            
        } elseif ($action === 'deactivate') {
            // Deactivate ad
            $ad_id = $_POST['ad_id'] ?? 0;
            
            $db->update("UPDATE advertisements SET status = 'inactive' WHERE id = ?", [$ad_id]);
            
            echo json_encode(['success' => true, 'message' => 'Publicité désactivée avec succès']);
            
        } else {
            throw new Exception('Action non reconnue');
        }
        
    } catch (Exception $e) {
        error_log("Database error in ads_actions.php: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
?>
