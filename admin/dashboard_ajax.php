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

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    try {
        if ($action === 'get_stats') {
            // Get dashboard statistics
            $stats = getDashboardStats();
            echo json_encode(['success' => true, 'stats' => $stats]);
            
        } elseif ($action === 'get_recent_activity') {
            // Get recent activity
            $activity = getRecentActivity();
            echo json_encode(['success' => true, 'activity' => $activity]);
            
        } elseif ($action === 'get_system_status') {
            // Get system status
            $status = getSystemStatus();
            echo json_encode(['success' => true, 'status' => $status]);
            
        } else {
            throw new Exception('Action non reconnue');
        }
        
    } catch (Exception $e) {
        error_log('Dashboard AJAX error: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}

function getDashboardStats() {
    global $db;
    
    try {
        // Get user statistics
        $total_users = $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
        $active_users = $db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'] ?? 0;
        
        // Get job statistics
        $total_jobs = $db->fetch("SELECT COUNT(*) as count FROM annonces")['count'] ?? 0;
        $active_jobs = $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE status = 'active'")['count'] ?? 0;
        
        // Get application statistics
        $total_applications = $db->fetch("SELECT COUNT(*) as count FROM postulation")['count'] ?? 0;
        
        return [
            'users' => [
                'total' => $total_users,
                'active' => $active_users
            ],
            'jobs' => [
                'total' => $total_jobs,
                'active' => $active_jobs
            ],
            'applications' => [
                'total' => $total_applications
            ]
        ];
        
    } catch (Exception $e) {
        error_log('Error getting dashboard stats: ' . $e->getMessage());
        return [
            'users' => ['total' => 0, 'active' => 0],
            'jobs' => ['total' => 0, 'active' => 0],
            'applications' => ['total' => 0]
        ];
    }
}

function getRecentActivity() {
    global $db;
    
    try {
        // Get recent user registrations
        $recent_users = $db->fetchAll("
            SELECT 'user_registration' as type, u.id, u.nom, u.prenom, u.created_at, 'Nouvel utilisateur inscrit' as description
            FROM users u 
            WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY u.created_at DESC 
            LIMIT 5
        ");
        
        // Get recent job postings
        $recent_jobs = $db->fetchAll("
            SELECT 'job_posting' as type, a.id, a.titre, a.employer_id, a.date_publication, 'Nouvelle offre publiée' as description
            FROM annonces a 
            WHERE a.date_publication >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY a.date_publication DESC 
            LIMIT 5
        ");
        
        // Get recent applications
        $recent_applications = $db->fetchAll("
            SELECT 'application' as type, p.id, p.user_id, p.annonce_id, p.date_postulation, 'Nouvelle candidature soumise' as description
            FROM postulation p 
            WHERE p.date_postulation >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY p.date_postulation DESC 
            LIMIT 5
        ");
        
        // Combine all activities
        $all_activities = array_merge($recent_users, $recent_jobs, $recent_applications);
        
        // Return limited results
        return array_slice($all_activities, 0, 10);
        
    } catch (Exception $e) {
        error_log('Error getting recent activity: ' . $e->getMessage());
        return [];
    }
}

function getSystemStatus() {
    global $db;
    
    try {
        // Check database connection
        $db_status = 'healthy';
        try {
            $db->fetch("SELECT 1");
        } catch (Exception $e) {
            $db_status = 'error';
        }
        
        return [
            'database' => $db_status,
            'server_time' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION
        ];
        
    } catch (Exception $e) {
        error_log('Error getting system status: ' . $e->getMessage());
        return [
            'database' => 'unknown',
            'server_time' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION
        ];
    }
}
?>
