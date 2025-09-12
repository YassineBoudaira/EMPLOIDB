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

// Check if it's a GET request with profile ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $profile_id = intval($_GET['id']);
    
    try {
        // Get profile data with enhanced information
        $profile = $db->fetch("
            SELECT p.*, u.email, u.nom, u.prenom, u.phone, u.status as user_status, u.created_at as user_created_at,
                   (SELECT COUNT(*) FROM postulation WHERE user_id = u.id) as total_applications,
                   (SELECT COUNT(*) FROM postulation WHERE user_id = u.id AND status = 'accepted') as accepted_applications,
                   (SELECT COUNT(*) FROM postulation WHERE user_id = u.id AND status = 'rejected') as rejected_applications,
                   (SELECT COUNT(*) FROM postulation WHERE user_id = u.id AND status = 'pending') as pending_applications
            FROM profiles p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
        ", [$profile_id]);
        
        if ($profile) {
            // Get recent applications for this user
            $recent_applications = $db->fetchAll("
                SELECT p.*, a.titre, a.salaire, e.company_name
                FROM postulation p
                LEFT JOIN annonces a ON p.annonce_id = a.id
                LEFT JOIN employeurs e ON a.employer_id = e.id
                WHERE p.user_id = ?
                ORDER BY p.date_postulation DESC
                LIMIT 10
            ", [$profile['user_id']]);
            
            // Get user activity statistics
            $activity_stats = [
                'total_applications' => $profile['total_applications'],
                'accepted_applications' => $profile['accepted_applications'],
                'rejected_applications' => $profile['rejected_applications'],
                'pending_applications' => $profile['pending_applications'],
                'acceptance_rate' => $profile['total_applications'] > 0 ? round(($profile['accepted_applications'] / $profile['total_applications']) * 100, 2) : 0,
                'days_since_registration' => round((time() - strtotime($profile['user_created_at'])) / 86400),
                'last_activity' => $profile['updated_at'] ?? $profile['user_created_at']
            ];
            
            // Return profile data as JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'profile' => $profile,
                'recent_applications' => $recent_applications,
                'activity_statistics' => $activity_stats
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Profil non trouvé'
            ]);
        }
    } catch (Exception $e) {
        error_log("Database error in get_profile_data.php: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Erreur de base de données'
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
