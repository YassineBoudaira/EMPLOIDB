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

// Check if it's a GET request with offer ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $offer_id = intval($_GET['id']);
    
    try {
        // Get offer data with enhanced information
        $offer = $db->fetch("
            SELECT o.*, 
                   (SELECT COUNT(*) FROM offer_applications WHERE offer_id = o.id) as applications_count,
                   (SELECT COUNT(*) FROM offer_applications WHERE offer_id = o.id AND status = 'accepted') as accepted_applications,
                   (SELECT COUNT(*) FROM offer_applications WHERE offer_id = o.id AND status = 'rejected') as rejected_applications,
                   (SELECT COUNT(*) FROM offer_applications WHERE offer_id = o.id AND status = 'pending') as pending_applications
            FROM offers o 
            WHERE o.id = ?
        ", [$offer_id]);
        
        if ($offer) {
            // Get recent applications for this offer
            $recent_applications = $db->fetchAll("
                SELECT oa.*, u.nom, u.prenom, u.email, u.phone
                FROM offer_applications oa
                LEFT JOIN users u ON oa.user_id = u.id
                WHERE oa.offer_id = ?
                ORDER BY oa.created_at DESC
                LIMIT 10
            ", [$offer_id]);
            
            // Get offer statistics
            $offer_stats = [
                'total_applications' => $offer['applications_count'],
                'accepted_applications' => $offer['accepted_applications'],
                'rejected_applications' => $offer['rejected_applications'],
                'pending_applications' => $offer['pending_applications'],
                'acceptance_rate' => $offer['applications_count'] > 0 ? round(($offer['accepted_applications'] / $offer['applications_count']) * 100, 2) : 0,
                'days_since_created' => $offer['created_at'] ? round((time() - strtotime($offer['created_at'])) / 86400) : 0
            ];
            
            // Return offer data as JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'offer' => $offer,
                'recent_applications' => $recent_applications,
                'statistics' => $offer_stats
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Offre non trouvée']);
        }
    } catch (Exception $e) {
        error_log('Database error in get_offer_data.php: ' . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erreur de base de données']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
