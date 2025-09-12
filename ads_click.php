<?php
/**
 * Ad Click Tracking Handler
 * Records ad clicks and redirects to destination URL
 */

include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';
include 'include/ads_display_system.php';

// Get ad ID and redirect URL
$ad_id = $_GET['ad_id'] ?? $_POST['ad_id'] ?? 0;
$redirect_url = $_GET['redirect'] ?? '';

// Validate ad ID
if (!$ad_id || !is_numeric($ad_id)) {
    header('Location: index.php');
    exit;
}

try {
    // Get ad details
    $ad = $db->fetch("SELECT * FROM advertisements WHERE id = ? AND status = 'active'", [$ad_id]);
    
    if (!$ad) {
        header('Location: index.php');
        exit;
    }
    
    // Initialize ad display system
    $user_id = $_SESSION['user_id'] ?? null;
    $user_type = $_SESSION['user_type'] ?? 'guest';
    $ad_system = new AdDisplaySystem($db, $user_id, $user_type, $_SERVER['HTTP_REFERER'] ?? '');
    
    // Record click
    $ad_system->recordClick($ad_id);
    
    // Use redirect URL from GET parameter or ad's link_url
    $final_redirect = $redirect_url ?: $ad['link_url'];
    
    // Validate redirect URL
    if (!filter_var($final_redirect, FILTER_VALIDATE_URL)) {
        header('Location: index.php');
        exit;
    }
    
    // Redirect to destination
    header('Location: ' . $final_redirect);
    exit;
    
} catch (Exception $e) {
    error_log("Ad click error: " . $e->getMessage());
    header('Location: index.php');
    exit;
}
?>