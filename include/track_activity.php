<?php
/**
 * AJAX Activity Tracking Handler
 * Receives tracking data from client-side JavaScript
 */

// Include necessary files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/connexion.php';
require_once __DIR__ . '/user_tracker.php';

// Set headers for AJAX
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Initialize user tracker
    $user_tracker = new UserTracker($db);
    
    // Process different types of activities
    $action = $input['action'] ?? '';
    $page = $input['page'] ?? '';
    
    switch ($action) {
        case 'form_submit':
            $user_tracker->trackActivity('form_submit', 'Form submitted', [
                'form_id' => $input['form_id'] ?? 'unknown',
                'page' => $page
            ]);
            break;
            
        case 'job_apply_click':
            $user_tracker->trackActivity('job_apply_click', 'Job apply button clicked', [
                'job_id' => $input['job_id'] ?? 'unknown',
                'page' => $page
            ]);
            break;
            
        case 'search':
            $user_tracker->trackActivity('search', 'Search performed', [
                'query' => $input['query'] ?? '',
                'page' => $page
            ]);
            break;
            
        case 'page_exit':
            // Update page view with time spent and scroll depth
            $sql = "UPDATE page_views 
                    SET time_on_page = ?, scroll_depth = ? 
                    WHERE session_id = ? AND page_url = ? 
                    ORDER BY view_timestamp DESC LIMIT 1";
            
            $params = [
                $input['time_spent'] ?? 0,
                $input['scroll_depth'] ?? 0,
                session_id(),
                $page
            ];
            
            $db->query($sql, $params);
            break;
            
        case 'scroll_depth':
            // Update scroll depth for current page view
            $sql = "UPDATE page_views 
                    SET scroll_depth = ? 
                    WHERE session_id = ? AND page_url = ? 
                    ORDER BY view_timestamp DESC LIMIT 1";
            
            $params = [
                $input['scroll_depth'] ?? 0,
                session_id(),
                $page
            ];
            
            $db->query($sql, $params);
            break;
            
        case 'click':
            $user_tracker->trackActivity('click', 'Element clicked', [
                'element' => $input['element'] ?? 'unknown',
                'page' => $page
            ]);
            break;
            
        default:
            // Generic activity tracking
            $user_tracker->trackActivity('custom', $action, $input);
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Activity tracked successfully',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log("Activity tracking error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to track activity',
        'message' => $e->getMessage()
    ]);
}
?>
