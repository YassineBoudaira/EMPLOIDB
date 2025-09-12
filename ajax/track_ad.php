<?php
/**
 * Ad Tracking AJAX Endpoint
 * Handles ad impression and click tracking
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include configuration
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/connexion.php';

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
    
    $type = $input['type'] ?? '';
    $ad_id = intval($input['ad_id'] ?? 0);
    $page_url = $input['page_url'] ?? '';
    $timestamp = $input['timestamp'] ?? date('Y-m-d H:i:s');
    
    // Validate required fields
    if (empty($type) || !$ad_id) {
        throw new Exception('Missing required fields');
    }
    
    if (!in_array($type, ['impression', 'click'])) {
        throw new Exception('Invalid tracking type');
    }
    
    // Get user information
    $user_id = $_SESSION['user_id'] ?? null;
    $user_type = $_SESSION['user_type'] ?? 'guest';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Check if ad exists and is active
    $ad = $db->fetch("
        SELECT id, status, start_date, end_date 
        FROM advertisements 
        WHERE id = ? AND status = 'active' 
        AND start_date <= NOW() AND end_date >= NOW()
    ", [$ad_id]);
    
    if (!$ad) {
        throw new Exception('Ad not found or inactive');
    }
    
    // Track based on type
    if ($type === 'impression') {
        // Check if impression already tracked for this session
        $existing_impression = $db->fetch("
            SELECT id FROM ad_impressions 
            WHERE ad_id = ? AND ip_address = ? 
            AND DATE(created_at) = CURDATE()
            LIMIT 1
        ", [$ad_id, $ip_address]);
        
        if (!$existing_impression) {
            $db->insert("
                INSERT INTO ad_impressions (ad_id, user_id, user_type, page_url, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ", [$ad_id, $user_id, $user_type, $page_url, $ip_address, $user_agent, $timestamp]);
        }
        
    } elseif ($type === 'click') {
        // Always track clicks
        $db->insert("
            INSERT INTO ad_clicks (ad_id, user_id, user_type, page_url, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ", [$ad_id, $user_id, $user_type, $page_url, $ip_address, $user_agent, $timestamp]);
        
        // Update ad performance
        $db->update("
            UPDATE ad_performance 
            SET clicks = clicks + 1 
            WHERE ad_id = ? AND DATE(date) = CURDATE()
        ", [$ad_id]);
        
        // If no performance record exists for today, create one
        if ($db->affectedRows() === 0) {
            $db->insert("
                INSERT INTO ad_performance (ad_id, date, impressions, clicks, conversions, revenue) 
                VALUES (?, CURDATE(), 0, 1, 0, 0)
            ", [$ad_id]);
        }
    }
    
    // Update daily performance stats
    $db->update("
        UPDATE ad_performance 
        SET impressions = (
            SELECT COUNT(*) FROM ad_impressions 
            WHERE ad_id = ? AND DATE(created_at) = CURDATE()
        )
        WHERE ad_id = ? AND DATE(date) = CURDATE()
    ", [$ad_id, $ad_id]);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => ucfirst($type) . ' tracked successfully',
        'ad_id' => $ad_id,
        'type' => $type
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
