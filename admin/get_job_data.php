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

// Check if it's a GET request with job ID
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $job_id = intval($_GET['id']);
    
    try {
        // Get job data with enhanced information
        $job = $db->fetch("
            SELECT a.*, e.company_name, e.email as employer_email, e.phone as employer_phone,
                   (SELECT COUNT(*) FROM postulation WHERE annonce_id = a.id) as applications_count,
                   (SELECT COUNT(*) FROM postulation WHERE annonce_id = a.id AND status = 'accepted') as accepted_applications,
                   (SELECT COUNT(*) FROM postulation WHERE annonce_id = a.id AND status = 'rejected') as rejected_applications,
                   (SELECT COUNT(*) FROM postulation WHERE annonce_id = a.id AND status = 'pending') as pending_applications
            FROM annonces a
            LEFT JOIN employeurs e ON a.employer_id = e.id
            WHERE a.id = ?
        ", [$job_id]);
        
        if ($job) {
            // Get recent applications for this job
            $recent_applications = $db->fetchAll("
                SELECT p.*, u.nom, u.prenom, u.email, u.phone
                FROM postulation p
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.annonce_id = ?
                ORDER BY p.date_postulation DESC
                LIMIT 10
            ", [$job_id]);
            
            // Get job statistics
            $job_stats = [
                'total_applications' => $job['applications_count'],
                'accepted_applications' => $job['accepted_applications'],
                'rejected_applications' => $job['rejected_applications'],
                'pending_applications' => $job['pending_applications'],
                'acceptance_rate' => $job['applications_count'] > 0 ? round(($job['accepted_applications'] / $job['applications_count']) * 100, 2) : 0,
                'days_since_posted' => $job['date_publication'] ? round((time() - strtotime($job['date_publication'])) / 86400) : 0
            ];
            
            // Return job data as JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'job' => $job,
                'recent_applications' => $recent_applications,
                'statistics' => $job_stats
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Offre non trouvée'
            ]);
        }
    } catch (Exception $e) {
        error_log("Database error in get_job_data.php: " . $e->getMessage());
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
