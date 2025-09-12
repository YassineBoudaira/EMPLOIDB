<?php
/**
 * Front-Office Ads Display Component
 * Displays advertisements based on position and targeting
 */

// Include database connection
if (!isset($db)) {
    include_once 'connexion.php';
}

/**
 * Get advertisements for a specific position
 */
function getAdsForPosition($position, $limit = 3) {
    global $db;
    
    try {
        // Get active advertisements for the position
        $ads = $db->fetchAll("
            SELECT a.*, 
                   (SELECT COUNT(*) FROM ad_impressions ai WHERE ai.ad_id = a.id) as impressions,
                   (SELECT COUNT(*) FROM ad_clicks ac WHERE ac.ad_id = a.id) as clicks
            FROM advertisements a 
            WHERE a.status = 'active' 
            AND a.position = ? 
            AND (a.start_date IS NULL OR a.start_date <= NOW())
            AND (a.end_date IS NULL OR a.end_date >= NOW())
            ORDER BY RAND()
            LIMIT ?
        ", [$position, $limit]);
        
        return $ads;
    } catch (Exception $e) {
        error_log("Error fetching ads: " . $e->getMessage());
        return [];
    }
}

/**
 * Record ad impression
 */
function recordImpression($ad_id) {
    global $db;
    
    try {
        $db->insert("
            INSERT INTO ad_impressions (ad_id, ip_address, user_agent, created_at) 
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
                SET impressions = impressions + 1 
                WHERE ad_id = ? AND date = ?
            ", [$ad_id, $today]);
        } else {
            $db->insert("
                INSERT INTO ad_performance (ad_id, date, impressions, clicks, revenue) 
                VALUES (?, ?, 1, 0, 0)
            ", [$ad_id, $today]);
        }
        
    } catch (Exception $e) {
        error_log("Error recording impression: " . $e->getMessage());
    }
}

/**
 * Record ad click
 */
function recordClick($ad_id) {
    global $db;
    
    try {
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
        
    } catch (Exception $e) {
        error_log("Error recording click: " . $e->getMessage());
    }
}

/**
 * Display ads for a specific position
 */
function displayAds($position, $limit = 3) {
    $ads = getAdsForPosition($position, $limit);
    
    if (empty($ads)) {
        return;
    }
    
    foreach ($ads as $ad) {
        // Record impression
        recordImpression($ad['id']);
        
        // Display ad based on type
        switch ($ad['ad_type']) {
            case 'banner':
                displayBannerAd($ad);
                break;
            case 'sidebar':
                displaySidebarAd($ad);
                break;
            case 'popup':
                displayPopupAd($ad);
                break;
            case 'inline':
                displayInlineAd($ad);
                break;
            default:
                displayBannerAd($ad);
                break;
        }
    }
}

/**
 * Display banner advertisement
 */
function displayBannerAd($ad) {
    ?>
    <div class="ad-banner" data-ad-id="<?php echo $ad['id']; ?>">
        <div class="ad-content">
            <?php if ($ad['image_url']): ?>
                <img src="<?php echo htmlspecialchars($ad['image_url']); ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>" class="ad-image">
            <?php endif; ?>
            <div class="ad-text">
                <h4><?php echo htmlspecialchars($ad['title']); ?></h4>
                <?php if ($ad['description']): ?>
                    <p><?php echo htmlspecialchars($ad['description']); ?></p>
                <?php endif; ?>
            </div>
            <a href="ads_click.php?id=<?php echo $ad['id']; ?>" class="ad-link" target="_blank" rel="nofollow">
                <span class="ad-cta">En savoir plus</span>
            </a>
        </div>
        <div class="ad-label">Publicité</div>
    </div>
    <?php
}

/**
 * Display sidebar advertisement
 */
function displaySidebarAd($ad) {
    ?>
    <div class="ad-sidebar" data-ad-id="<?php echo $ad['id']; ?>">
        <div class="ad-content">
            <?php if ($ad['image_url']): ?>
                <img src="<?php echo htmlspecialchars($ad['image_url']); ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>" class="ad-image">
            <?php endif; ?>
            <div class="ad-text">
                <h5><?php echo htmlspecialchars($ad['title']); ?></h5>
                <?php if ($ad['description']): ?>
                    <p><?php echo htmlspecialchars($ad['description']); ?></p>
                <?php endif; ?>
            </div>
            <a href="ads_click.php?id=<?php echo $ad['id']; ?>" class="ad-link" target="_blank" rel="nofollow">
                <span class="ad-cta">Voir plus</span>
            </a>
        </div>
        <div class="ad-label">Publicité</div>
    </div>
    <?php
}

/**
 * Display inline advertisement
 */
function displayInlineAd($ad) {
    ?>
    <div class="ad-inline" data-ad-id="<?php echo $ad['id']; ?>">
        <div class="ad-content">
            <div class="ad-text">
                <h4><?php echo htmlspecialchars($ad['title']); ?></h4>
                <?php if ($ad['description']): ?>
                    <p><?php echo htmlspecialchars($ad['description']); ?></p>
                <?php endif; ?>
            </div>
            <a href="ads_click.php?id=<?php echo $ad['id']; ?>" class="ad-link" target="_blank" rel="nofollow">
                <span class="ad-cta">Découvrir</span>
            </a>
        </div>
        <div class="ad-label">Publicité</div>
    </div>
    <?php
}

/**
 * Display popup advertisement (JavaScript required)
 */
function displayPopupAd($ad) {
    ?>
    <script>
        // Popup ad will be shown via JavaScript
        window.popupAd = {
            id: <?php echo $ad['id']; ?>,
            title: '<?php echo addslashes($ad['title']); ?>',
            description: '<?php echo addslashes($ad['description']); ?>',
            image: '<?php echo addslashes($ad['image_url']); ?>',
            link: 'ads_click.php?id=<?php echo $ad['id']; ?>'
        };
    </script>
    <?php
}
?>

<style>
/* Ad Display Styles */
.ad-banner, .ad-sidebar, .ad-inline {
    position: relative;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    margin: 1rem 0;
    transition: all 0.3s ease;
    overflow: hidden;
}

.ad-banner:hover, .ad-sidebar:hover, .ad-inline:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.ad-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.ad-image {
    max-width: 120px;
    max-height: 80px;
    object-fit: cover;
    border-radius: 4px;
}

.ad-text {
    flex: 1;
}

.ad-text h4, .ad-text h5 {
    margin: 0 0 0.5rem 0;
    color: #333;
    font-weight: 600;
}

.ad-text p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
    line-height: 1.4;
}

.ad-link {
    text-decoration: none;
    color: inherit;
}

.ad-cta {
    display: inline-block;
    background: #007bff;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-size: 0.9rem;
    font-weight: 500;
    transition: background 0.3s ease;
}

.ad-cta:hover {
    background: #0056b3;
    color: white;
}

.ad-label {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: rgba(0,0,0,0.6);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
    .ad-content {
        flex-direction: column;
        text-align: center;
    }
    
    .ad-image {
        max-width: 100%;
        max-height: 120px;
    }
    
    .ad-label {
        position: static;
        display: inline-block;
        margin-bottom: 0.5rem;
    }
}

/* Sidebar specific styles */
.ad-sidebar {
    margin: 0.5rem 0;
    padding: 0.75rem;
}

.ad-sidebar .ad-content {
    flex-direction: column;
    text-align: center;
}

.ad-sidebar .ad-image {
    max-width: 100%;
    max-height: 100px;
}

/* Inline specific styles */
.ad-inline {
    text-align: center;
    padding: 1.5rem;
}

.ad-inline .ad-content {
    flex-direction: column;
}
</style>
