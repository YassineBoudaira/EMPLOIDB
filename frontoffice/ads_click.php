<?php
include 'include/connexion.php';

// Get ad ID from URL
$ad_id = intval($_GET['id'] ?? 0);

if ($ad_id > 0) {
    try {
        // Get advertisement details
        $ad = $db->fetch("SELECT * FROM advertisements WHERE id = ? AND status = 'active'", [$ad_id]);
        
        if ($ad) {
            // Record click
            $db->insert("
                INSERT INTO ad_clicks (ad_id, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, NOW())
            ", [$ad_id, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);
            
            // Update performance table
            $today = date('Y-m-d');
            $existing = $db->fetch("
                SELECT id FROM ad_performance 
                WHERE ad_id = ? AND date = ?
            ", [$ad_id, $today]);
            
            if ($existing) {
                $db->update("
                    UPDATE ad_performance 
                    SET clicks = clicks + 1 
                    WHERE ad_id = ? AND date = ?
                ", [$ad_id, $today]);
            } else {
                $db->insert("
                    INSERT INTO ad_performance (ad_id, date, impressions, clicks, revenue) 
                    VALUES (?, ?, 0, 1, 0)
                ", [$ad_id, $today]);
            }
            
            // Redirect to destination URL
            if (!empty($ad['link_url'])) {
                header('Location: ' . $ad['link_url']);
                exit;
            }
        }
    } catch (Exception $e) {
        error_log("Error processing ad click: " . $e->getMessage());
    }
}

// If something goes wrong, redirect to home page
header('Location: index.php');
exit;
?>
