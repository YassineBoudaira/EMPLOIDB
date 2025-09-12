<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Enterprise Ad Management - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Check if user has access to ad management
if (!$rbac->hasPageAccess($_SESSION['admin_id'] ?? 1, 'manage_ads')) {
    $_SESSION['error'] = 'Accès non autorisé à cette page.';
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$success = false;

// Handle ad actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $ad_id = $_POST['ad_id'] ?? 0;
    
    try {
        if ($action === 'delete' && $ad_id) {
            // Validate CSRF token
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Token de sécurité invalide');
            }
            
            // Delete related records first
            $db->delete("DELETE FROM ad_performance WHERE ad_id = ?", [$ad_id]);
            $db->delete("DELETE FROM ad_clicks WHERE ad_id = ?", [$ad_id]);
            $db->delete("DELETE FROM ad_impressions WHERE ad_id = ?", [$ad_id]);
            $db->delete("DELETE FROM ad_targeting WHERE ad_id = ?", [$ad_id]);
            $db->delete("DELETE FROM ad_scheduling WHERE ad_id = ?", [$ad_id]);
            $db->delete("DELETE FROM campaign_ads WHERE ad_id = ?", [$ad_id]);
            $db->delete("DELETE FROM ad_category_relations WHERE ad_id = ?", [$ad_id]);
            // Delete the ad
            $db->delete("DELETE FROM advertisements WHERE id = ?", [$ad_id]);
            
            $_SESSION['ad_notification'] = [
                'type' => 'success',
                'message' => 'Publicité supprimée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: manage_ads.php?success=deleted');
            exit;
        }
        
        if ($action === 'activate' && $ad_id) {
            $db->update("UPDATE advertisements SET status = 'active', updated_at = NOW() WHERE id = ?", [$ad_id]);
            
            $_SESSION['ad_notification'] = [
                'type' => 'success',
                'message' => 'Publicité activée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: manage_ads.php?success=activated');
            exit;
        }
        
        if ($action === 'deactivate' && $ad_id) {
            $db->update("UPDATE advertisements SET status = 'inactive', updated_at = NOW() WHERE id = ?", [$ad_id]);
            
            $_SESSION['ad_notification'] = [
                'type' => 'success',
                'message' => 'Publicité désactivée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: manage_ads.php?success=deactivated');
            exit;
        }
        
        if ($action === 'approve' && $ad_id) {
            $db->update("UPDATE advertisements SET status = 'active', approved_at = NOW(), updated_at = NOW() WHERE id = ?", [$ad_id]);
            
            $_SESSION['ad_notification'] = [
                'type' => 'success',
                'message' => 'Publicité approuvée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: manage_ads.php?success=approved');
            exit;
        }
        
        if ($action === 'reject' && $ad_id) {
            $rejection_reason = Security::sanitizeInput($_POST['rejection_reason'] ?? '');
            $db->update("UPDATE advertisements SET status = 'rejected', rejection_reason = ?, rejected_at = NOW(), updated_at = NOW() WHERE id = ?", [$rejection_reason, $ad_id]);
            
            $_SESSION['ad_notification'] = [
                'type' => 'success',
                'message' => 'Publicité rejetée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: manage_ads.php?success=rejected');
            exit;
        }
        
        // Bulk operations
        if ($action === 'bulk_approve') {
            $ad_ids = $_POST['ad_ids'] ?? [];
            if (!empty($ad_ids)) {
                $placeholders = str_repeat('?,', count($ad_ids) - 1) . '?';
                $db->update("UPDATE advertisements SET status = 'active', approved_at = NOW(), updated_at = NOW() WHERE id IN ($placeholders)", $ad_ids);
                
                $_SESSION['ad_notification'] = [
                    'type' => 'success',
                    'message' => count($ad_ids) . ' publicités approuvées avec succès !',
                    'title' => 'Succès'
                ];
            }
            header('Location: manage_ads.php?success=bulk_approved');
            exit;
        }
        
        if ($action === 'bulk_reject') {
            $ad_ids = $_POST['ad_ids'] ?? [];
            $rejection_reason = Security::sanitizeInput($_POST['rejection_reason'] ?? '');
            if (!empty($ad_ids)) {
                $placeholders = str_repeat('?,', count($ad_ids) - 1) . '?';
                $db->update("UPDATE advertisements SET status = 'rejected', rejection_reason = ?, rejected_at = NOW(), updated_at = NOW() WHERE id IN ($placeholders)", array_merge([$rejection_reason], $ad_ids));
                
                $_SESSION['ad_notification'] = [
                    'type' => 'success',
                    'message' => count($ad_ids) . ' publicités rejetées avec succès !',
                    'title' => 'Succès'
                ];
            }
            header('Location: manage_ads.php?success=bulk_rejected');
            exit;
        }
        
        if ($action === 'bulk_delete') {
            $ad_ids = $_POST['ad_ids'] ?? [];
            if (!empty($ad_ids)) {
                $placeholders = str_repeat('?,', count($ad_ids) - 1) . '?';
                
                // Delete related records first
                $db->delete("DELETE FROM ad_performance WHERE ad_id IN ($placeholders)", $ad_ids);
                $db->delete("DELETE FROM ad_clicks WHERE ad_id IN ($placeholders)", $ad_ids);
                $db->delete("DELETE FROM ad_impressions WHERE ad_id IN ($placeholders)", $ad_ids);
                $db->delete("DELETE FROM campaign_ads WHERE ad_id IN ($placeholders)", $ad_ids);
                
                // Delete the ads
                $db->delete("DELETE FROM advertisements WHERE id IN ($placeholders)", $ad_ids);
                
                $_SESSION['ad_notification'] = [
                    'type' => 'success',
                    'message' => count($ad_ids) . ' publicités supprimées avec succès !',
                    'title' => 'Succès'
                ];
            }
            header('Location: manage_ads.php?success=bulk_deleted');
            exit;
        }
        
        if ($action === 'bulk_feature') {
            $ad_ids = $_POST['ad_ids'] ?? [];
            if (!empty($ad_ids)) {
                $placeholders = str_repeat('?,', count($ad_ids) - 1) . '?';
                $db->update("UPDATE advertisements SET featured = 1, updated_at = NOW() WHERE id IN ($placeholders)", $ad_ids);
                
                $_SESSION['ad_notification'] = [
                    'type' => 'success',
                    'message' => count($ad_ids) . ' publicités mises en avant avec succès !',
                    'title' => 'Succès'
                ];
            }
            header('Location: manage_ads.php?success=bulk_featured');
            exit;
        }
        
        // Export functionality
        if ($action === 'export_csv') {
            $export_ads = $db->fetchAll($ads_query, $params) ?? [];
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="ads_export_' . date('Y-m-d_H-i-s') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV headers
            fputcsv($output, [
                'ID', 'Titre', 'Type', 'Position', 'Audience', 'Budget', 'Statut', 
                'Date Création', 'Date Début', 'Date Fin', 'Impressions', 'Clics', 
                'Revenus', 'CTR', 'Description'
            ]);
            
            // CSV data
            foreach ($export_ads as $ad) {
                $ctr = $ad['impressions_count'] > 0 ? ($ad['clicks_count'] / $ad['impressions_count']) * 100 : 0;
                fputcsv($output, [
                    $ad['id'],
                    $ad['title'],
                    $ad['ad_type'],
                    $ad['position'],
                    $ad['target_audience'],
                    $ad['budget'],
                    $ad['status'],
                    $ad['created_at'],
                    $ad['start_date'],
                    $ad['end_date'],
                    $ad['impressions_count'],
                    $ad['clicks_count'],
                    $ad['total_revenue'],
                    number_format($ctr, 2) . '%',
                    strip_tags($ad['description'])
                ]);
            }
            
            fclose($output);
            exit;
        }

        // Add new ad
        if ($action === 'add_ad') {
            // Validate CSRF token
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Token de sécurité invalide');
            }
            
            // Sanitize and validate input
            $title = Security::sanitizeInput($_POST['title'] ?? '');
            $description = Security::sanitizeInput($_POST['description'] ?? '');
            $ad_type = Security::sanitizeInput($_POST['ad_type'] ?? 'banner');
            $position = Security::sanitizeInput($_POST['position'] ?? 'header');
            $image_url = Security::sanitizeInput($_POST['image_url'] ?? '');
            $link_url = Security::sanitizeInput($_POST['link_url'] ?? '');
            $target_audience = Security::sanitizeInput($_POST['target_audience'] ?? 'all');
            $target_device = Security::sanitizeInput($_POST['target_device'] ?? 'all');
            $target_location = Security::sanitizeInput($_POST['target_location'] ?? 'all');
            $budget = (float)($_POST['budget'] ?? 0);
            $daily_budget = (float)($_POST['daily_budget'] ?? 0);
            $bid_type = Security::sanitizeInput($_POST['bid_type'] ?? 'cpc');
            $bid_amount = (float)($_POST['bid_amount'] ?? 0);
            $frequency_cap = (int)($_POST['frequency_cap'] ?? 0);
            $bidding_strategy = Security::sanitizeInput($_POST['bidding_strategy'] ?? 'manual');
            $cta_text = Security::sanitizeInput($_POST['cta_text'] ?? '');
            $cta_color = Security::sanitizeInput($_POST['cta_color'] ?? '#007bff');
            $creative_style = Security::sanitizeInput($_POST['creative_style'] ?? 'modern');
            $animations = Security::sanitizeInput($_POST['animations'] ?? '');
            $behavioral_targeting = Security::sanitizeInput($_POST['behavioral_targeting'] ?? '');
            $time_targeting = Security::sanitizeInput($_POST['time_targeting'] ?? '');
            $optimization = Security::sanitizeInput($_POST['optimization'] ?? '');
            $start_date = Security::sanitizeInput($_POST['start_date'] ?? date('Y-m-d'));
            $end_date = Security::sanitizeInput($_POST['end_date'] ?? date('Y-m-d', strtotime('+30 days')));
            $status = Security::sanitizeInput($_POST['status'] ?? 'pending');
            $advertiser_id = (int)($_POST['advertiser_id'] ?? 0);

            // Validation
            if (empty($title)) {
                throw new Exception('Le titre de la publicité est obligatoire');
            }
            
            if (empty($description)) {
                throw new Exception('La description est obligatoire');
            }
            
            if (!empty($link_url) && !filter_var($link_url, FILTER_VALIDATE_URL)) {
                throw new Exception('URL de destination invalide');
            }
            
            if (!empty($image_url) && !filter_var($image_url, FILTER_VALIDATE_URL)) {
                throw new Exception('URL de l\'image invalide');
            }
            
            if ($budget < 0) {
                throw new Exception('Le budget ne peut pas être négatif');
            }
            
            if ($daily_budget < 0) {
                throw new Exception('Le budget quotidien ne peut pas être négatif');
            }
            
            if ($bid_amount < 0) {
                throw new Exception('Le montant de l\'enchère ne peut pas être négatif');
            }
            
            if (!empty($end_date) && strtotime($end_date) <= strtotime($start_date)) {
                throw new Exception('La date de fin doit être après la date de début');
            }

            // Insert ad with enhanced fields
            $ad_id = $db->insert("
                INSERT INTO advertisements (title, description, ad_type, position, image_url, 
                link_url, target_audience, target_device, target_location, budget, daily_budget, 
                bid_type, bid_amount, frequency_cap, bidding_strategy, cta_text, cta_color, 
                creative_style, animations, behavioral_targeting, time_targeting, optimization, 
                start_date, end_date, status, advertiser_id, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ", [$title, $description, $ad_type, $position, $image_url, $link_url, 
                $target_audience, $target_device, $target_location, $budget, $daily_budget, 
                $bid_type, $bid_amount, $frequency_cap, $bidding_strategy, $cta_text, $cta_color, 
                $creative_style, $animations, $behavioral_targeting, $time_targeting, $optimization, 
                $start_date, $end_date, $status, $advertiser_id]);

            $_SESSION['ad_notification'] = [
                'type' => 'success',
                'message' => 'Publicité ajoutée avec succès !',
                'title' => 'Succès'
            ];

            header('Location: manage_ads.php?success=ad_added');
            exit;
        }

        // Edit ad
        if ($action === 'edit_ad') {
            // Validate CSRF token
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Token de sécurité invalide');
            }
            
            $ad_id = (int)($_POST['ad_id'] ?? 0);
            $title = Security::sanitizeInput($_POST['title'] ?? '');
            $description = Security::sanitizeInput($_POST['description'] ?? '');
            $ad_type = Security::sanitizeInput($_POST['ad_type'] ?? 'banner');
            $position = Security::sanitizeInput($_POST['position'] ?? 'header');
            $image_url = Security::sanitizeInput($_POST['image_url'] ?? '');
            $link_url = Security::sanitizeInput($_POST['link_url'] ?? '');
            $target_audience = Security::sanitizeInput($_POST['target_audience'] ?? 'all');
            $target_device = Security::sanitizeInput($_POST['target_device'] ?? 'all');
            $target_location = Security::sanitizeInput($_POST['target_location'] ?? 'all');
            $budget = (float)($_POST['budget'] ?? 0);
            $daily_budget = (float)($_POST['daily_budget'] ?? 0);
            $bid_type = Security::sanitizeInput($_POST['bid_type'] ?? 'cpc');
            $bid_amount = (float)($_POST['bid_amount'] ?? 0);
            $frequency_cap = (int)($_POST['frequency_cap'] ?? 0);
            $bidding_strategy = Security::sanitizeInput($_POST['bidding_strategy'] ?? 'manual');
            $cta_text = Security::sanitizeInput($_POST['cta_text'] ?? '');
            $cta_color = Security::sanitizeInput($_POST['cta_color'] ?? '#007bff');
            $creative_style = Security::sanitizeInput($_POST['creative_style'] ?? 'modern');
            $animations = Security::sanitizeInput($_POST['animations'] ?? '');
            $behavioral_targeting = Security::sanitizeInput($_POST['behavioral_targeting'] ?? '');
            $time_targeting = Security::sanitizeInput($_POST['time_targeting'] ?? '');
            $optimization = Security::sanitizeInput($_POST['optimization'] ?? '');
            $start_date = Security::sanitizeInput($_POST['start_date'] ?? date('Y-m-d'));
            $end_date = Security::sanitizeInput($_POST['end_date'] ?? date('Y-m-d', strtotime('+30 days')));
            $status = Security::sanitizeInput($_POST['status'] ?? 'pending');
            $advertiser_id = (int)($_POST['advertiser_id'] ?? 0);

            // Validation
            if (empty($title)) {
                throw new Exception('Le titre de la publicité est obligatoire');
            }
            
            if (empty($description)) {
                throw new Exception('La description est obligatoire');
            }
            
            if (!$ad_id) {
                throw new Exception('ID de publicité invalide');
            }
            
            if (!empty($link_url) && !filter_var($link_url, FILTER_VALIDATE_URL)) {
                throw new Exception('URL de destination invalide');
            }
            
            if (!empty($image_url) && !filter_var($image_url, FILTER_VALIDATE_URL)) {
                throw new Exception('URL de l\'image invalide');
            }
            
            if ($budget < 0) {
                throw new Exception('Le budget ne peut pas être négatif');
            }
            
            if ($daily_budget < 0) {
                throw new Exception('Le budget quotidien ne peut pas être négatif');
            }
            
            if ($bid_amount < 0) {
                throw new Exception('Le montant de l\'enchère ne peut pas être négatif');
            }
            
            if (!empty($end_date) && strtotime($end_date) <= strtotime($start_date)) {
                throw new Exception('La date de fin doit être après la date de début');
            }

            // Update ad with enhanced fields
            $db->update("
                UPDATE advertisements SET title = ?, description = ?, ad_type = ?, position = ?, 
                image_url = ?, link_url = ?, target_audience = ?, target_device = ?, target_location = ?, 
                budget = ?, daily_budget = ?, bid_type = ?, bid_amount = ?, frequency_cap = ?, 
                bidding_strategy = ?, cta_text = ?, cta_color = ?, creative_style = ?, animations = ?, 
                behavioral_targeting = ?, time_targeting = ?, optimization = ?, start_date = ?, 
                end_date = ?, status = ?, advertiser_id = ?, updated_at = NOW()
                WHERE id = ?
            ", [$title, $description, $ad_type, $position, $image_url, $link_url, 
                $target_audience, $target_device, $target_location, $budget, $daily_budget, 
                $bid_type, $bid_amount, $frequency_cap, $bidding_strategy, $cta_text, $cta_color, 
                $creative_style, $animations, $behavioral_targeting, $time_targeting, $optimization, 
                $start_date, $end_date, $status, $advertiser_id, $ad_id]);

            $_SESSION['ad_notification'] = [
                'type' => 'success',
                'message' => 'Publicité mise à jour avec succès !',
                'title' => 'Succès'
            ];

            header('Location: manage_ads.php?success=ad_updated');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['ad_notification'] = [
            'type' => 'error',
            'message' => 'Erreur: ' . $e->getMessage(),
            'title' => 'Erreur'
        ];
        
        header('Location: manage_ads.php?error=database');
        exit;
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';
$position_filter = $_GET['position'] ?? '';
$advertiser_filter = $_GET['advertiser'] ?? '';
$audience_filter = $_GET['audience'] ?? '';
$device_filter = $_GET['device'] ?? '';
$location_filter = $_GET['location'] ?? '';
$featured_filter = $_GET['featured'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$budget_min = $_GET['budget_min'] ?? '';
$budget_max = $_GET['budget_max'] ?? '';
$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query conditions
$where_conditions = ['1=1'];
$params = [];

if ($status_filter) {
    $where_conditions[] = "a.status = ?";
    $params[] = $status_filter;
}

if ($type_filter) {
    $where_conditions[] = "a.ad_type = ?";
    $params[] = $type_filter;
}

if ($position_filter) {
    $where_conditions[] = "a.position = ?";
    $params[] = $position_filter;
}

if ($advertiser_filter) {
    $where_conditions[] = "a.advertiser_id = ?";
    $params[] = $advertiser_filter;
}

if ($audience_filter) {
    $where_conditions[] = "a.target_audience = ?";
    $params[] = $audience_filter;
}

if ($device_filter) {
    $where_conditions[] = "a.target_device = ?";
    $params[] = $device_filter;
}

if ($location_filter) {
    $where_conditions[] = "a.target_location = ?";
    $params[] = $location_filter;
}

if ($featured_filter !== '') {
    $where_conditions[] = "a.featured = ?";
    $params[] = $featured_filter;
}

if ($date_from) {
    $where_conditions[] = "DATE(a.created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "DATE(a.created_at) <= ?";
    $params[] = $date_to;
}

if ($budget_min) {
    $where_conditions[] = "a.budget >= ?";
    $params[] = $budget_min;
}

if ($budget_max) {
    $where_conditions[] = "a.budget <= ?";
    $params[] = $budget_max;
}

if ($search) {
    $where_conditions[] = "(a.title LIKE ? OR a.description LIKE ? OR adv.company_name LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
}

$where_clause = implode(" AND ", $where_conditions);

// Get ads with enhanced data
try {
    $ads_query = "
        SELECT a.*, adv.company_name as advertiser_name,
               (SELECT COUNT(*) FROM ad_impressions ai WHERE ai.ad_id = a.id) as impressions_count,
               (SELECT COUNT(*) FROM ad_clicks ac WHERE ac.ad_id = a.id) as clicks_count,
               (SELECT COALESCE(SUM(ap.conversions), 0) FROM ad_performance ap WHERE ap.ad_id = a.id) as conversions_count,
               (SELECT COALESCE(SUM(ap.revenue), 0) FROM ad_performance ap WHERE ap.ad_id = a.id) as total_revenue,
               DATEDIFF(CURDATE(), a.created_at) as days_since_created,
               CASE 
                   WHEN a.impressions_count > 0 THEN (a.clicks_count / a.impressions_count) * 100 
                   ELSE 0 
               END as ctr
        FROM advertisements a 
        LEFT JOIN advertisers adv ON a.advertiser_id = adv.id
        WHERE $where_clause
        ORDER BY a.created_at DESC
        LIMIT $per_page OFFSET $offset
    ";
    $ads = $db->fetchAll($ads_query, $params) ?? [];

    // Get total count
    $total_ads = $db->fetch("
        SELECT COUNT(*) as count 
        FROM advertisements a
        LEFT JOIN advertisers adv ON a.advertiser_id = adv.id
        WHERE $where_clause
    ", $params)['count'] ?? 0;

    $total_pages = ceil($total_ads / $per_page);

    // Get statistics
    $adStats = [
        'total_ads' => $db->fetch("SELECT COUNT(*) as count FROM advertisements")['count'] ?? 0,
        'active_ads' => $db->fetch("SELECT COUNT(*) as count FROM advertisements WHERE status = 'active'")['count'] ?? 0,
        'pending_ads' => $db->fetch("SELECT COUNT(*) as count FROM advertisements WHERE status = 'pending'")['count'] ?? 0,
        'rejected_ads' => $db->fetch("SELECT COUNT(*) as count FROM advertisements WHERE status = 'rejected'")['count'] ?? 0,
        'featured_ads' => $db->fetch("SELECT COUNT(*) as count FROM advertisements WHERE featured = 1")['count'] ?? 0,
        'total_impressions' => $db->fetch("SELECT COALESCE(SUM(impressions), 0) as count FROM ad_performance")['count'] ?? 0,
        'total_clicks' => $db->fetch("SELECT COALESCE(SUM(clicks), 0) as count FROM ad_performance")['count'] ?? 0,
        'total_revenue' => $db->fetch("SELECT COALESCE(SUM(revenue), 0) as count FROM ad_performance")['count'] ?? 0,
        'avg_ctr' => $db->fetch("
            SELECT AVG(CASE WHEN impressions > 0 THEN (clicks / impressions) * 100 ELSE 0 END) as avg_ctr 
            FROM ad_performance
        ")['avg_ctr'] ?? 0,
        'new_ads_today' => $db->fetch("SELECT COUNT(*) as count FROM advertisements WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
        'new_ads_week' => $db->fetch("SELECT COUNT(*) as count FROM advertisements WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0
    ];

    // Get filter options
    $ad_types = ['banner', 'popup', 'sidebar', 'inline', 'video', 'carousel', 'native', 'interstitial', 'sticky', 'floating'];
    $positions = ['header', 'footer', 'sidebar', 'content_top', 'content_bottom', 'popup', 'floating_left', 'floating_right', 'sticky_top', 'sticky_bottom', 'modal_overlay', 'fullscreen'];
    $target_audiences = ['all', 'job_seekers', 'employers', 'premium_users', 'new_users', 'returning_users', 'mobile_users', 'desktop_users'];
    $target_devices = ['all', 'mobile', 'tablet', 'desktop'];
    $target_locations = ['all', 'casablanca', 'rabat', 'marrakech', 'fes', 'agadir', 'tanger', 'international'];
    $bid_types = ['cpc', 'cpm', 'cpa', 'cpi', 'cpe'];
    $bidding_strategies = ['manual', 'auto', 'target_cpa'];
    $creative_styles = ['modern', 'classic', 'minimalist', 'bold', 'elegant'];
    
    // Get advertisers for filter dropdown
    $advertisers = $db->fetchAll("SELECT id, company_name FROM advertisers ORDER BY company_name", []) ?? [];

} catch (Exception $e) {
    $ads = [];
    $total_ads = 0;
    $total_pages = 1;
    $adStats = [
        'total_ads' => 0,
        'active_ads' => 0,
        'pending_ads' => 0,
        'rejected_ads' => 0,
        'featured_ads' => 0,
        'total_impressions' => 0,
        'total_clicks' => 0,
        'total_revenue' => 0,
        'avg_ctr' => 0,
        'new_ads_today' => 0,
        'new_ads_week' => 0
    ];
    $ad_types = [];
    $positions = [];
    $target_audiences = [];
    $target_devices = [];
    $target_locations = [];
    $bid_types = [];
    $bidding_strategies = [];
    $creative_styles = [];
    $advertisers = [];
    error_log("Database error in manage_ads.php: " . $e->getMessage());
}

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'get_ad') {
    $ad_id = Security::sanitizeInput($_GET['id'] ?? '', 'int');
    
    if ($ad_id) {
        $ad = $db->fetch("SELECT * FROM advertisements WHERE id = ?", [$ad_id]);
        if ($ad) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'ad' => $ad]);
            exit;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Ad not found']);
    exit;
}

// Get notification if exists
$notification = $_SESSION['ad_notification'] ?? null;
unset($_SESSION['ad_notification']);
?>

<!-- Enterprise Ad Management Content -->
<div class="fade-in">
    <!-- Notification Display -->
    <?php if ($notification): ?>
    <div class="alert alert-<?= $notification['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4" role="alert">
        <strong><?= htmlspecialchars($notification['title']) ?>:</strong> <?= htmlspecialchars($notification['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Ad Management Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-ad me-3"></i>
                        Enterprise Ad Management
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Gestion complète des publicités avec analyses avancées et contrôles
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="refreshAdData()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="exportToCSV()">
                        <i class="fas fa-download"></i>
                        Exporter CSV
                    </button>
                    <button class="enterprise-btn enterprise-btn-accent" onclick="generateAdReport()">
                        <i class="fas fa-file-pdf"></i>
                        Rapport PDF
                    </button>
                    <button class="enterprise-btn enterprise-btn-info" onclick="showAdAnalytics()">
                        <i class="fas fa-chart-line"></i>
                        Analyses
                    </button>
                    <button class="enterprise-btn enterprise-btn-warning" onclick="checkAdDuplicates()">
                        <i class="fas fa-copy"></i>
                        Détecter Doublons
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Ad Overview Statistics -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-ad fa-2x text-primary"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-primary mb-0"><?= number_format($adStats['total_ads']) ?></h3>
                            <small class="text-muted">Total Publicités</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +<?= $adStats['new_ads_week'] ?> cette semaine
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showAdDetails()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-success mb-0"><?= number_format($adStats['active_ads']) ?></h3>
                            <small class="text-muted">Actives</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +<?= $adStats['new_ads_today'] ?> aujourd'hui
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showActiveAds()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-warning mb-0"><?= number_format($adStats['pending_ads']) ?></h3>
                            <small class="text-muted">En Attente</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-warning">
                            <i class="fas fa-arrow-up"></i>
                            +18.3% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showPendingAds()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-info bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-eye fa-2x text-info"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-info mb-0"><?= number_format($adStats['total_impressions']) ?></h3>
                            <small class="text-muted">Impressions</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +32.1% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showImpressions()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-mouse-pointer fa-2x text-danger"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-danger mb-0"><?= number_format($adStats['total_clicks']) ?></h3>
                            <small class="text-muted">Clics</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +8.5% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showClicks()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-purple bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-dollar-sign fa-2x text-purple"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-purple mb-0"><?= number_format($adStats['total_revenue'], 2) ?> MAD</h3>
                            <small class="text-muted">Revenus</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +15.2% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showRevenue()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-search me-2"></i>
                Recherche et Filtres Avancés
            </h4>
        </div>
        <div class="enterprise-card-body">
            <form method="GET" class="row g-3">
                <!-- Basic Search -->
                <div class="col-md-3">
                    <label class="form-label">Recherche</label>
                    <input type="text" class="form-control" name="search" placeholder="Titre, description, annonceur..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select class="form-select" name="status">
                        <option value="">Tous les Statuts</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Actif</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>En Attente</option>
                        <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejeté</option>
                        <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="type">
                        <option value="">Tous les Types</option>
                        <?php foreach ($ad_types as $type): ?>
                            <option value="<?= $type ?>" <?= $type_filter === $type ? 'selected' : '' ?>>
                                <?= ucfirst($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Position</label>
                    <select class="form-select" name="position">
                        <option value="">Toutes Positions</option>
                        <?php foreach ($positions as $pos): ?>
                            <option value="<?= $pos ?>" <?= $position_filter === $pos ? 'selected' : '' ?>>
                                <?= ucfirst(str_replace('_', ' ', $pos)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Annonceur</label>
                    <select class="form-select" name="advertiser">
                        <option value="">Tous les Annonceurs</option>
                        <?php foreach ($advertisers as $advertiser): ?>
                            <option value="<?= $advertiser['id'] ?>" <?= $advertiser_filter == $advertiser['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($advertiser['company_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="enterprise-btn enterprise-btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                
                <!-- Advanced Filters -->
                <div class="col-md-2">
                    <label class="form-label">Audience</label>
                    <select class="form-select" name="audience">
                        <option value="">Toutes Audiences</option>
                        <?php foreach ($target_audiences as $audience): ?>
                            <option value="<?= $audience ?>" <?= $audience_filter === $audience ? 'selected' : '' ?>>
                                <?= ucfirst(str_replace('_', ' ', $audience)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Appareil</label>
                    <select class="form-select" name="device">
                        <option value="">Tous Appareils</option>
                        <?php foreach ($target_devices as $device): ?>
                            <option value="<?= $device ?>" <?= $device_filter === $device ? 'selected' : '' ?>>
                                <?= ucfirst($device) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Localisation</label>
                    <select class="form-select" name="location">
                        <option value="">Toutes Localisations</option>
                        <?php foreach ($target_locations as $location): ?>
                            <option value="<?= $location ?>" <?= $location_filter === $location ? 'selected' : '' ?>>
                                <?= ucfirst($location) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Mis en avant</label>
                    <select class="form-select" name="featured">
                        <option value="">Tous</option>
                        <option value="1" <?= $featured_filter === '1' ? 'selected' : '' ?>>Oui</option>
                        <option value="0" <?= $featured_filter === '0' ? 'selected' : '' ?>>Non</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date de début</label>
                    <input type="date" class="form-control" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date de fin</label>
                    <input type="date" class="form-control" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
                </div>
                
                <!-- Budget Filters -->
                <div class="col-md-2">
                    <label class="form-label">Budget min (MAD)</label>
                    <input type="number" class="form-control" name="budget_min" value="<?= htmlspecialchars($budget_min) ?>" placeholder="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Budget max (MAD)</label>
                    <input type="number" class="form-control" name="budget_max" value="<?= htmlspecialchars($budget_max) ?>" placeholder="100000">
                </div>
                
                <!-- Action Buttons -->
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                        <button type="submit" class="enterprise-btn enterprise-btn-primary">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                        <a href="manage_ads.php" class="enterprise-btn enterprise-btn-outline">
                            <i class="fas fa-times"></i> Effacer
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Ads Table -->
    <div class="enterprise-card">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="enterprise-card-title">
                    <i class="fas fa-table me-2"></i>
                    Liste des Publicités (<?= number_format($total_ads) ?> total)
                </h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-success" onclick="bulkApprove()" id="bulkApproveBtn" disabled>
                        <i class="fas fa-check"></i> Approuver
                    </button>
                    <button class="btn btn-sm btn-outline-warning" onclick="bulkReject()" id="bulkRejectBtn" disabled>
                        <i class="fas fa-times"></i> Rejeter
                    </button>
                    <button class="btn btn-sm btn-outline-info" onclick="bulkFeature()" id="bulkFeatureBtn" disabled>
                        <i class="fas fa-star"></i> Mettre en avant
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="bulkDelete()" id="bulkDeleteBtn" disabled>
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                    <button class="enterprise-btn enterprise-btn-success" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Nouvelle Publicité
                    </button>
                </div>
            </div>
        </div>
        <div class="enterprise-card-body">
            <?php if (empty($ads)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-ad fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune publicité trouvée</h5>
                    <p class="text-muted">Essayez d'ajuster vos critères de recherche</p>
                    <button class="enterprise-btn enterprise-btn-success" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Créer une Publicité
                    </button>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>Publicité</th>
                                <th>Annonceur</th>
                                <th>Type & Position</th>
                                <th>Performance</th>
                                <th>Budget</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ads as $ad): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="ad-checkbox" value="<?= $ad['id'] ?>" onchange="updateBulkButtons()">
                                </td>
                                <td>
                                    <div>
                                        <strong class="text-primary"><?= htmlspecialchars($ad['title']) ?></strong>
                                        <br><small class="text-muted">ID: <?= $ad['id'] ?></small>
                                        <br><small class="text-muted"><?= htmlspecialchars(substr($ad['description'], 0, 100)) ?>...</small>
                                        <?php if ($ad['featured']): ?>
                                            <br><span class="badge bg-warning"><i class="fas fa-star"></i> En vedette</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($ad['advertiser_name'] ?: 'Non assigné') ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="badge bg-primary"><?= ucfirst($ad['ad_type']) ?></span>
                                        <br><span class="badge bg-secondary"><?= ucfirst(str_replace('_', ' ', $ad['position'])) ?></span>
                                        <br><small class="text-muted"><?= ucfirst(str_replace('_', ' ', $ad['target_audience'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <small style="color: var(--enterprise-text-secondary);">
                                            <i class="fas fa-eye me-1"></i><?= number_format($ad['impressions_count']) ?> impressions
                                        </small>
                                        <small style="color: var(--enterprise-text-secondary);">
                                            <i class="fas fa-mouse-pointer me-1"></i><?= number_format($ad['clicks_count']) ?> clics
                                        </small>
                                        <small style="color: var(--enterprise-text-secondary);">
                                            <i class="fas fa-percentage me-1"></i><?= number_format($ad['ctr'], 2) ?>% CTR
                                        </small>
                                        <small style="color: var(--enterprise-success); font-weight: 600;">
                                            <i class="fas fa-dollar-sign me-1"></i><?= number_format($ad['total_revenue'], 2) ?> MAD
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong class="text-success"><?= number_format($ad['budget'], 2) ?> MAD</strong>
                                        <?php if ($ad['daily_budget'] > 0): ?>
                                            <br><small class="text-muted"><?= number_format($ad['daily_budget'], 2) ?> MAD/jour</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $status_class = '';
                                    $status_text = '';
                                    switch($ad['status']) {
                                        case 'active':
                                            $status_class = 'bg-success';
                                            $status_text = 'Actif';
                                            break;
                                        case 'pending':
                                            $status_class = 'bg-warning';
                                            $status_text = 'En Attente';
                                            break;
                                        case 'rejected':
                                            $status_class = 'bg-danger';
                                            $status_text = 'Rejeté';
                                            break;
                                        case 'inactive':
                                            $status_class = 'bg-secondary';
                                            $status_text = 'Inactif';
                                            break;
                                        default:
                                            $status_class = 'bg-secondary';
                                            $status_text = ucfirst($ad['status']);
                                    }
                                    ?>
                                    <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                                </td>
                                <td>
                                    <div>
                                        <div><?= date('j M Y', strtotime($ad['created_at'])) ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($ad['created_at'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline me-1" onclick="viewAd(<?= $ad['id'] ?>)" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-info me-1" onclick="editAd(<?= $ad['id'] ?>)" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($ad['status'] === 'pending'): ?>
                                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-success me-1" onclick="approveAd(<?= $ad['id'] ?>)" title="Approuver">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger me-1" onclick="rejectAd(<?= $ad['id'] ?>)" title="Rejeter">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger" onclick="deleteAd(<?= $ad['id'] ?>)" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&type=<?= urlencode($type_filter) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add/Edit Ad Modal -->
<div class="modal fade" id="adModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adModalTitle">Ajouter une Publicité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="adForm" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" id="adAction" value="add_ad">
                    <input type="hidden" name="ad_id" id="adId" value="">
                    <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                    
                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="adTitle" class="form-label">Titre de la Publicité *</label>
                                <input type="text" class="form-control" id="adTitle" name="title" required maxlength="200">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="advertiserId" class="form-label">Annonceur</label>
                                <select class="form-select" id="advertiserId" name="advertiser_id">
                                    <option value="">Sélectionner un annonceur</option>
                                    <?php foreach ($advertisers as $advertiser): ?>
                                        <option value="<?= $advertiser['id'] ?>"><?= htmlspecialchars($advertiser['company_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adDescription" class="form-label">Description *</label>
                        <textarea class="form-control" id="adDescription" name="description" rows="4" required></textarea>
                    </div>
                    
                    <!-- Ad Type and Position -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="adType" class="form-label">Type de Publicité</label>
                                <select class="form-select" id="adType" name="ad_type">
                                    <?php foreach ($ad_types as $type): ?>
                                        <option value="<?= $type ?>"><?= ucfirst($type) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="adPosition" class="form-label">Position</label>
                                <select class="form-select" id="adPosition" name="position">
                                    <?php foreach ($positions as $pos): ?>
                                        <option value="<?= $pos ?>"><?= ucfirst(str_replace('_', ' ', $pos)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="adStatus" class="form-label">Statut</label>
                                <select class="form-select" id="adStatus" name="status">
                                    <option value="pending">En attente</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Media and Links -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adImageUrl" class="form-label">URL de l'Image</label>
                                <input type="url" class="form-control" id="adImageUrl" name="image_url" placeholder="https://example.com/image.jpg">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adLinkUrl" class="form-label">URL de Destination</label>
                                <input type="url" class="form-control" id="adLinkUrl" name="link_url" placeholder="https://example.com">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Targeting -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="adTargetAudience" class="form-label">Audience Cible</label>
                                <select class="form-select" id="adTargetAudience" name="target_audience">
                                    <?php foreach ($target_audiences as $audience): ?>
                                        <option value="<?= $audience ?>"><?= ucfirst(str_replace('_', ' ', $audience)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="adTargetDevice" class="form-label">Appareil Cible</label>
                                <select class="form-select" id="adTargetDevice" name="target_device">
                                    <?php foreach ($target_devices as $device): ?>
                                        <option value="<?= $device ?>"><?= ucfirst($device) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="adTargetLocation" class="form-label">Localisation Cible</label>
                                <select class="form-select" id="adTargetLocation" name="target_location">
                                    <?php foreach ($target_locations as $location): ?>
                                        <option value="<?= $location ?>"><?= ucfirst($location) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Budget and Bidding -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="adBudget" class="form-label">Budget Total (MAD)</label>
                                <input type="number" class="form-control" id="adBudget" name="budget" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="adDailyBudget" class="form-label">Budget Quotidien (MAD)</label>
                                <input type="number" class="form-control" id="adDailyBudget" name="daily_budget" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="adBidType" class="form-label">Type d'Enchère</label>
                                <select class="form-select" id="adBidType" name="bid_type">
                                    <?php foreach ($bid_types as $bid_type): ?>
                                        <option value="<?= $bid_type ?>"><?= strtoupper($bid_type) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="adBidAmount" class="form-label">Montant d'Enchère (MAD)</label>
                                <input type="number" class="form-control" id="adBidAmount" name="bid_amount" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Creative and CTA -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="adCtaText" class="form-label">Texte CTA</label>
                                <input type="text" class="form-control" id="adCtaText" name="cta_text" placeholder="En savoir plus">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="adCtaColor" class="form-label">Couleur CTA</label>
                                <input type="color" class="form-control" id="adCtaColor" name="cta_color" value="#007bff">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="adCreativeStyle" class="form-label">Style Créatif</label>
                                <select class="form-select" id="adCreativeStyle" name="creative_style">
                                    <?php foreach ($creative_styles as $style): ?>
                                        <option value="<?= $style ?>"><?= ucfirst($style) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Scheduling -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adStartDate" class="form-label">Date de Début</label>
                                <input type="date" class="form-control" id="adStartDate" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adEndDate" class="form-label">Date de Fin</label>
                                <input type="date" class="form-control" id="adEndDate" name="end_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="enterprise-btn enterprise-btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Ads Table -->
<div class="enterprise-content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color: var(--enterprise-primary); margin: 0;">
            <i class="fas fa-list me-2"></i>Liste des Publicités
        </h5>
        <span class="badge bg-info"><?= number_format(count($ads)) ?> résultat(s)</span>
    </div>
    
    <?php if (empty($ads)): ?>
    <div class="text-center py-5">
        <i class="fas fa-ad fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Aucune publicité trouvée</h5>
        <p class="text-muted">Aucune publicité ne correspond à vos critères de recherche.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead style="background: var(--enterprise-gray-50);">
                <tr>
                    <th style="color: var(--enterprise-text-primary);">Publicité</th>
                    <th style="color: var(--enterprise-text-primary);">Type & Position</th>
                    <th style="color: var(--enterprise-text-primary);">Performance</th>
                    <th style="color: var(--enterprise-text-primary);">Statut</th>
                    <th style="color: var(--enterprise-text-primary);">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ads as $ad): ?>
                <tr>
                    <td>
                        <div>
                            <strong style="color: var(--enterprise-primary);">
                                <?= htmlspecialchars($ad['title']) ?>
                            </strong>
                            <br>
                            <small style="color: var(--enterprise-text-muted);">
                                <?= htmlspecialchars(substr($ad['description'], 0, 100)) ?>...
                            </small>
                        </div>
                    </td>
                    <td>
                        <div style="color: var(--enterprise-text-secondary);">
                            <span class="badge bg-primary"><?= ucfirst($ad['ad_type']) ?></span>
                            <span class="badge bg-secondary"><?= ucfirst($ad['position']) ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <small style="color: var(--enterprise-text-secondary);">
                                <i class="fas fa-eye me-1"></i><?= number_format($ad['impressions_count']) ?> impressions
                            </small>
                            <small style="color: var(--enterprise-text-secondary);">
                                <i class="fas fa-mouse-pointer me-1"></i><?= number_format($ad['clicks_count']) ?> clics
                            </small>
                            <small style="color: var(--enterprise-text-secondary);">
                                <i class="fas fa-dollar-sign me-1"></i><?= number_format($ad['total_revenue'], 2) ?> MAD
                            </small>
                        </div>
                    </td>
                    <td>
                        <?php if ($ad['status'] === 'active'): ?>
                        <span class="enterprise-status-badge enterprise-success">
                            <i class="fas fa-check me-1"></i>Active
                        </span>
                        <?php elseif ($ad['status'] === 'pending'): ?>
                        <span class="enterprise-status-badge enterprise-warning">
                            <i class="fas fa-clock me-1"></i>En attente
                        </span>
                        <?php else: ?>
                        <span class="enterprise-status-badge enterprise-danger">
                            <i class="fas fa-times me-1"></i>Inactive
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="enterprise-action-btn enterprise-info enterprise-sm me-1" 
                            onclick="openEditAdModal(<?= $ad['id'] ?>)"
                            title="Modifier la publicité">
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php if ($ad['status'] === 'active'): ?>
                        <button class="enterprise-action-btn enterprise-warning enterprise-sm me-1" 
                            onclick="deactivateAd(<?= $ad['id'] ?>, '<?= htmlspecialchars($ad['title'], ENT_QUOTES) ?>')"
                            title="Désactiver la publicité">
                            <i class="fas fa-pause"></i>
                        </button>
                        <?php else: ?>
                        <button class="enterprise-action-btn enterprise-success enterprise-sm me-1" 
                            onclick="activateAd(<?= $ad['id'] ?>, '<?= htmlspecialchars($ad['title'], ENT_QUOTES) ?>')"
                            title="Activer la publicité">
                            <i class="fas fa-play"></i>
                        </button>
                        <?php endif; ?>
                        <button class="enterprise-action-btn enterprise-danger enterprise-sm" 
                            onclick="deleteAd(<?= $ad['id'] ?>, '<?= htmlspecialchars($ad['title'], ENT_QUOTES) ?>')"
                            title="Supprimer la publicité">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Ad Modal -->
<div class="modal fade" id="adModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adModalTitle">Ajouter une Publicité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="adForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="adAction" value="add_ad">
                    <input type="hidden" name="ad_id" id="adId" value="">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adTitle" class="form-label">Titre *</label>
                                <input type="text" class="form-control" id="adTitle" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adType" class="form-label">Type de Publicité</label>
                                <select class="form-select" id="adType" name="ad_type">
                                    <?php foreach ($ad_types as $type): ?>
                                    <option value="<?= $type ?>"><?= ucfirst($type) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adDescription" class="form-label">Description *</label>
                        <textarea class="form-control" id="adDescription" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adPosition" class="form-label">Position</label>
                                <select class="form-select" id="adPosition" name="position">
                                    <?php foreach ($positions as $pos): ?>
                                    <option value="<?= $pos ?>"><?= ucfirst($pos) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adTarget" class="form-label">Audience Cible</label>
                                <select class="form-select" id="adTarget" name="target_audience">
                                    <?php foreach ($target_audiences as $audience): ?>
                                    <option value="<?= $audience ?>"><?= ucfirst(str_replace('_', ' ', $audience)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adImageUrl" class="form-label">URL de l'Image</label>
                                <input type="url" class="form-control" id="adImageUrl" name="image_url">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="adLinkUrl" class="form-label">URL de Destination</label>
                                <input type="url" class="form-control" id="adLinkUrl" name="link_url">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="adBudget" class="form-label">Budget (MAD)</label>
                                <input type="number" class="form-control" id="adBudget" name="budget" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="adStartDate" class="form-label">Date de Début</label>
                                <input type="date" class="form-control" id="adStartDate" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="adEndDate" class="form-label">Date de Fin</label>
                                <input type="date" class="form-control" id="adEndDate" name="end_date">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adStatus" class="form-label">Statut</label>
                        <select class="form-select" id="adStatus" name="status">
                            <option value="pending">En attente</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="enterprise-action-btn enterprise-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="enterprise-action-btn enterprise-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Ad Modal -->
<div class="modal fade" id="viewAdModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de la Publicité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewAdContent">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Ad Modal -->
<div class="modal fade" id="rejectAdModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rejeter la Publicité</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="reject_ad">
                    <input type="hidden" name="ad_id" id="rejectAdId">
                    <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                    
                    <div class="mb-3">
                        <label for="rejectionReason" class="form-label">Raison du Rejet *</label>
                        <textarea class="form-control" id="rejectionReason" name="rejection_reason" rows="4" required placeholder="Expliquez pourquoi cette publicité est rejetée..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="enterprise-btn enterprise-btn-danger">Rejeter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Ad Analytics Modal -->
<div class="modal fade" id="adAnalyticsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Analytiques des Publicités</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="adStatusChart" width="400" height="200"></canvas>
                    </div>
                    <div class="col-md-6">
                        <canvas id="adTypeChart" width="400" height="200"></canvas>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-12">
                        <canvas id="adPerformanceChart" width="800" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="enterprise-btn enterprise-btn-primary" onclick="exportAdAnalytics()">Exporter</button>
            </div>
        </div>
    </div>
</div>

<!-- Duplicate Detection Modal -->
<div class="modal fade" id="adDuplicatesModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détection de Doublons</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="duplicatesContent">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="enterprise-btn enterprise-btn-warning" onclick="mergeAdDuplicates()">Fusionner</button>
            </div>
        </div>
    </div>
</div>

<script>
function activateAd(id, title) {
    if (confirm(`Activer la publicité "${title}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="activate">
            <input type="hidden" name="ad_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function deactivateAd(id, title) {
    if (confirm(`Désactiver la publicité "${title}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="deactivate">
            <input type="hidden" name="ad_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteAd(id, title) {
    if (confirm(`Supprimer définitivement la publicité "${title}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="ad_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function refreshData() {
    location.reload();
}

function exportAdsData() {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('export', 'csv');
    window.location.href = currentUrl.toString();
}

function openAddAdModal() {
    document.getElementById('adModalTitle').textContent = 'Ajouter une Publicité';
    document.getElementById('adAction').value = 'add_ad';
    document.getElementById('adId').value = '';
    document.getElementById('adForm').reset();
    document.getElementById('adStartDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('adEndDate').value = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    new bootstrap.Modal(document.getElementById('adModal')).show();
}

function openEditAdModal(adId) {
    // This would typically fetch ad data via AJAX
    alert('Fonctionnalité de modification - à implémenter avec AJAX');
}

// Enhanced Enterprise Functions
function refreshAdData() {
    location.reload();
}

function exportToCSV() {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('export', 'csv');
    window.location.href = currentUrl.toString();
}

function generateAdReport() {
    // Generate comprehensive ad report
    alert('Génération du rapport des publicités...');
}

function showAdAnalytics() {
    new bootstrap.Modal(document.getElementById('adAnalyticsModal')).show();
    initializeAdCharts();
}

function checkAdDuplicates() {
    new bootstrap.Modal(document.getElementById('adDuplicatesModal')).show();
    // Load duplicates via AJAX
}

function showAdDetails(adId) {
    // Show detailed ad information
    alert('Affichage des détails de la publicité ' + adId);
}

function showActiveAds() {
    window.location.href = '?status=active';
}

function showPendingAds() {
    window.location.href = '?status=pending';
}

function showImpressions() {
    window.location.href = '?sort=impressions&order=desc';
}

function showClicks() {
    window.location.href = '?sort=clicks&order=desc';
}

function showRevenue() {
    window.location.href = '?sort=revenue&order=desc';
}

function viewAd(adId) {
    // Load ad details via AJAX
    fetch(`ajax/get_ad_details.php?id=${adId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('viewAdContent').innerHTML = data;
            new bootstrap.Modal(document.getElementById('viewAdModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du chargement des détails');
        });
}

function editAd(adId) {
    // Load ad data for editing
    fetch(`ajax/get_ad_data.php?id=${adId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('adModalTitle').textContent = 'Modifier la Publicité';
            document.getElementById('adAction').value = 'edit_ad';
            document.getElementById('adId').value = adId;
            
            // Populate form fields
            document.getElementById('adTitle').value = data.title;
            document.getElementById('adDescription').value = data.description;
            document.getElementById('adType').value = data.ad_type;
            document.getElementById('adPosition').value = data.position;
            document.getElementById('adStatus').value = data.status;
            document.getElementById('adImageUrl').value = data.image_url;
            document.getElementById('adLinkUrl').value = data.link_url;
            document.getElementById('adTargetAudience').value = data.target_audience;
            document.getElementById('adTargetDevice').value = data.target_device;
            document.getElementById('adTargetLocation').value = data.target_location;
            document.getElementById('adBudget').value = data.budget;
            document.getElementById('adDailyBudget').value = data.daily_budget;
            document.getElementById('adBidType').value = data.bid_type;
            document.getElementById('adBidAmount').value = data.bid_amount;
            document.getElementById('adCtaText').value = data.cta_text;
            document.getElementById('adCtaColor').value = data.cta_color;
            document.getElementById('adCreativeStyle').value = data.creative_style;
            document.getElementById('adStartDate').value = data.start_date;
            document.getElementById('adEndDate').value = data.end_date;
            
            new bootstrap.Modal(document.getElementById('adModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du chargement des données');
        });
}

function approveAd(adId) {
    if (confirm('Approuver cette publicité ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="approve">
            <input type="hidden" name="ad_id" value="${adId}">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectAd(adId) {
    document.getElementById('rejectAdId').value = adId;
    new bootstrap.Modal(document.getElementById('rejectAdModal')).show();
}

function deleteAd(adId) {
    if (confirm('Supprimer définitivement cette publicité ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="ad_id" value="${adId}">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function openAddModal() {
    document.getElementById('adModalTitle').textContent = 'Ajouter une Publicité';
    document.getElementById('adAction').value = 'add_ad';
    document.getElementById('adId').value = '';
    document.getElementById('adForm').reset();
    document.getElementById('adStartDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('adEndDate').value = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    new bootstrap.Modal(document.getElementById('adModal')).show();
}

function openRejectAdModal(adId) {
    document.getElementById('rejectAdId').value = adId;
    new bootstrap.Modal(document.getElementById('rejectAdModal')).show();
}

// Bulk Operations
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.ad-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    updateBulkButtons();
}

function updateBulkButtons() {
    const selected = document.querySelectorAll('.ad-checkbox:checked');
    const bulkButtons = document.querySelectorAll('.bulk-action-btn');
    bulkButtons.forEach(btn => {
        btn.disabled = selected.length === 0;
    });
}

function bulkApprove() {
    const selectedIds = getSelectedAdIds();
    if (selectedIds.length === 0) return;
    
    if (confirm(`Approuver ${selectedIds.length} publicité(s) ?`)) {
        submitBulkAction('bulk_approve', selectedIds);
    }
}

function bulkReject() {
    const selectedIds = getSelectedAdIds();
    if (selectedIds.length === 0) return;
    
    if (confirm(`Rejeter ${selectedIds.length} publicité(s) ?`)) {
        submitBulkAction('bulk_reject', selectedIds);
    }
}

function bulkFeature() {
    const selectedIds = getSelectedAdIds();
    if (selectedIds.length === 0) return;
    
    if (confirm(`Mettre en vedette ${selectedIds.length} publicité(s) ?`)) {
        submitBulkAction('bulk_feature', selectedIds);
    }
}

function bulkDelete() {
    const selectedIds = getSelectedAdIds();
    if (selectedIds.length === 0) return;
    
    if (confirm(`Supprimer définitivement ${selectedIds.length} publicité(s) ?`)) {
        submitBulkAction('bulk_delete', selectedIds);
    }
}

function getSelectedAdIds() {
    const checkboxes = document.querySelectorAll('.ad-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function submitBulkAction(action, adIds) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="${action}">
        <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
        ${adIds.map(id => `<input type="hidden" name="ad_ids[]" value="${id}">`).join('')}
    `;
    document.body.appendChild(form);
    form.submit();
}

// Chart Functions
function initializeAdCharts() {
    // Ad Status Chart
    const statusCtx = document.getElementById('adStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Actives', 'En attente', 'Inactives'],
                datasets: [{
                    data: [<?= $adStats['active_ads'] ?>, <?= $adStats['pending_ads'] ?>, <?= $adStats['inactive_ads'] ?>],
                    backgroundColor: ['#28a745', '#ffc107', '#6c757d']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    // Ad Type Chart
    const typeCtx = document.getElementById('adTypeChart');
    if (typeCtx) {
        new Chart(typeCtx, {
            type: 'bar',
            data: {
                labels: ['Banner', 'Popup', 'Sidebar', 'Inline'],
                datasets: [{
                    label: 'Nombre de publicités',
                    data: [12, 8, 15, 10], // Sample data
                    backgroundColor: '#007bff'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Performance Chart
    const performanceCtx = document.getElementById('adPerformanceChart');
    if (performanceCtx) {
        new Chart(performanceCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun'],
                datasets: [{
                    label: 'Impressions',
                    data: [1200, 1900, 3000, 5000, 2000, 3000],
                    borderColor: '#007bff',
                    tension: 0.1
                }, {
                    label: 'Clics',
                    data: [120, 190, 300, 500, 200, 300],
                    borderColor: '#28a745',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

function exportAdAnalytics() {
    // Export analytics data
    alert('Export des analytiques...');
}

function mergeAdDuplicates() {
    // Merge duplicate ads
    alert('Fusion des doublons...');
}

// Initialize charts on page load
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh data every 30 seconds
    setInterval(refreshAdData, 30000);
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
