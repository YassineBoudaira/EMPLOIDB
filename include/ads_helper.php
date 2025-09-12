<?php
/**
 * Ads Helper - Advertisement Management and Display System
 * EMPLOIDB - Enterprise Advertisement System
 */

class AdsHelper {
    private $db;
    private $user_id;
    private $session_id;
    private $ip_address;
    private $user_agent;
    private $device_type;
    private $country;
    private $city;
    
    public function __construct($db) {
        $this->db = $db;
        $this->user_id = $_SESSION['user_id'] ?? null;
        $this->session_id = session_id();
        $this->ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $this->device_type = $this->getDeviceType();
        $this->country = $this->getCountry();
        $this->city = $this->getCity();
    }
    
    /**
     * Get active ads for a specific position
     */
    public function getActiveAds($position, $limit = 1) {
        $current_date = date('Y-m-d');
        $current_time = date('H:i:s');
        $current_day = strtolower(date('l'));
        
        $query = "
            SELECT DISTINCT a.*
            FROM advertisements a
            LEFT JOIN ad_scheduling s ON a.id = s.ad_id
            WHERE a.status = 'active'
            AND a.position = ?
            AND (a.start_date IS NULL OR a.start_date <= ?)
            AND (a.end_date IS NULL OR a.end_date >= ?)
            AND (s.id IS NULL OR (
                s.is_active = 1 
                AND s.day_of_week = ? 
                AND (s.start_time IS NULL OR s.start_time <= ?)
                AND (s.end_time IS NULL OR s.end_time >= ?)
            ))
            ORDER BY a.priority DESC, RAND()
            LIMIT ?
        ";
        
        $ads = $this->db->query($query, [
            $position, $current_date, $current_date, $current_day, $current_time, $current_time, $limit
        ]);
        
        // Filter ads based on targeting
        $filtered_ads = [];
        foreach ($ads as $ad) {
            if ($this->matchesTargeting($ad['id'])) {
                $filtered_ads[] = $ad;
            }
        }
        
        return $filtered_ads;
    }
    
    /**
     * Check if ad matches targeting criteria
     */
    private function matchesTargeting($ad_id) {
        $targeting = $this->db->query("
            SELECT * FROM ad_targeting 
            WHERE ad_id = ? 
            ORDER BY target_condition, id
        ", [$ad_id]);
        
        if (empty($targeting)) {
            return true; // No targeting means show to everyone
        }
        
        $matches = true;
        $current_condition = 'AND';
        
        foreach ($targeting as $target) {
            $target_matches = $this->evaluateTarget($target);
            
            if ($target['target_condition'] === 'AND') {
                $matches = $matches && $target_matches;
            } else {
                $matches = $matches || $target_matches;
            }
        }
        
        return $matches;
    }
    
    /**
     * Evaluate a single targeting rule
     */
    private function evaluateTarget($target) {
        switch ($target['target_type']) {
            case 'location':
                return $this->evaluateLocationTarget($target);
            case 'device':
                return $this->evaluateDeviceTarget($target);
            case 'time':
                return $this->evaluateTimeTarget($target);
            case 'demographic':
                return $this->evaluateDemographicTarget($target);
            case 'behavior':
                return $this->evaluateBehaviorTarget($target);
            case 'custom':
                return $this->evaluateCustomTarget($target);
            default:
                return true;
        }
    }
    
    /**
     * Evaluate location targeting
     */
    private function evaluateLocationTarget($target) {
        $target_value = json_decode($target['target_value'], true);
        
        if ($target['target_operator'] === 'equals') {
            if (isset($target_value['country'])) {
                return $this->country === $target_value['country'];
            }
            if (isset($target_value['city'])) {
                return $this->city === $target_value['city'];
            }
        } elseif ($target['target_operator'] === 'contains') {
            if (isset($target_value['country'])) {
                return stripos($this->country, $target_value['country']) !== false;
            }
            if (isset($target_value['city'])) {
                return stripos($this->city, $target_value['city']) !== false;
            }
        }
        
        return false;
    }
    
    /**
     * Evaluate device targeting
     */
    private function evaluateDeviceTarget($target) {
        $target_value = json_decode($target['target_value'], true);
        
        if (isset($target_value['device_type'])) {
            return $this->device_type === $target_value['device_type'];
        }
        
        return false;
    }
    
    /**
     * Evaluate time targeting
     */
    private function evaluateTimeTarget($target) {
        $target_value = json_decode($target['target_value'], true);
        $current_hour = (int)date('H');
        $current_day = strtolower(date('l'));
        
        if (isset($target_value['hours'])) {
            $hours = $target_value['hours'];
            if (is_array($hours) && count($hours) === 2) {
                return $current_hour >= $hours[0] && $current_hour <= $hours[1];
            }
        }
        
        if (isset($target_value['days'])) {
            $days = $target_value['days'];
            if (is_array($days)) {
                return in_array($current_day, $days);
            }
        }
        
        return false;
    }
    
    /**
     * Evaluate demographic targeting
     */
    private function evaluateDemographicTarget($target) {
        if (!$this->user_id) {
            return false; // Can't target demographics for non-logged users
        }
        
        $target_value = json_decode($target['target_value'], true);
        
        // Get user profile data
        $user_profile = $this->db->fetch("
            SELECT p.*, u.age, u.gender 
            FROM profiles p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.user_id = ?
        ", [$this->user_id]);
        
        if (!$user_profile) {
            return false;
        }
        
        if (isset($target_value['age_range'])) {
            $age_range = $target_value['age_range'];
            if (is_array($age_range) && count($age_range) === 2) {
                $user_age = $user_profile['age'] ?? 0;
                return $user_age >= $age_range[0] && $user_age <= $age_range[1];
            }
        }
        
        if (isset($target_value['gender'])) {
            return $user_profile['gender'] === $target_value['gender'];
        }
        
        return false;
    }
    
    /**
     * Evaluate behavior targeting
     */
    private function evaluateBehaviorTarget($target) {
        if (!$this->user_id) {
            return false;
        }
        
        $target_value = json_decode($target['target_value'], true);
        
        if (isset($target_value['pages_visited'])) {
            $pages_visited = $this->db->fetch("
                SELECT COUNT(*) as count 
                FROM page_views 
                WHERE user_id = ? 
                AND page_url LIKE ?
            ", [$this->user_id, '%' . $target_value['pages_visited'] . '%']);
            
            return $pages_visited['count'] > 0;
        }
        
        if (isset($target_value['time_spent'])) {
            $time_spent = $this->db->fetch("
                SELECT SUM(view_duration) as total_time 
                FROM page_views 
                WHERE user_id = ? 
                AND view_timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ", [$this->user_id]);
            
            return $time_spent['total_time'] >= $target_value['time_spent'];
        }
        
        return false;
    }
    
    /**
     * Evaluate custom targeting
     */
    private function evaluateCustomTarget($target) {
        // Custom targeting logic can be implemented here
        // For now, return true to show the ad
        return true;
    }
    
    /**
     * Track ad impression
     */
    public function trackImpression($ad_id, $page_url = '') {
        $this->db->insert("
            INSERT INTO ad_impressions (
                ad_id, user_id, session_id, ip_address, user_agent, 
                page_url, device_type, browser, country, city, 
                impression_timestamp
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ", [
            $ad_id, $this->user_id, $this->session_id, $this->ip_address, $this->user_agent,
            $page_url, $this->device_type, $this->getBrowser(), $this->country, $this->city
        ]);
        
        // Update daily performance
        $this->updateDailyPerformance($ad_id, 'impression');
    }
    
    /**
     * Track ad click
     */
    public function trackClick($ad_id, $referrer_url = '') {
        $this->db->insert("
            INSERT INTO ad_clicks (
                ad_id, user_id, session_id, ip_address, user_agent, 
                referrer_url, click_timestamp
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ", [
            $ad_id, $this->user_id, $this->session_id, $this->ip_address, $this->user_agent, $referrer_url
        ]);
        
        // Update daily performance
        $this->updateDailyPerformance($ad_id, 'click');
    }
    
    /**
     * Update daily performance metrics
     */
    private function updateDailyPerformance($ad_id, $action) {
        $today = date('Y-m-d');
        
        // Check if record exists for today
        $existing = $this->db->fetch("
            SELECT id FROM ad_performance 
            WHERE ad_id = ? AND date = ?
        ", [$ad_id, $today]);
        
        if ($existing) {
            // Update existing record
            if ($action === 'impression') {
                $this->db->update("
                    UPDATE ad_performance 
                    SET impressions = impressions + 1 
                    WHERE ad_id = ? AND date = ?
                ", [$ad_id, $today]);
            } elseif ($action === 'click') {
                $this->db->update("
                    UPDATE ad_performance 
                    SET clicks = clicks + 1 
                    WHERE ad_id = ? AND date = ?
                ", [$ad_id, $today]);
            }
        } else {
            // Create new record
            $impressions = $action === 'impression' ? 1 : 0;
            $clicks = $action === 'click' ? 1 : 0;
            
            $this->db->insert("
                INSERT INTO ad_performance (
                    ad_id, date, impressions, clicks, conversions, 
                    revenue, cost, ctr, cpc, cpm, conversion_rate
                ) VALUES (?, ?, ?, ?, 0, 0, 0, 0, 0, 0, 0)
            ", [$ad_id, $today, $impressions, $clicks]);
        }
        
        // Update CTR
        $this->updateCTR($ad_id, $today);
    }
    
    /**
     * Update CTR for an ad on a specific date
     */
    private function updateCTR($ad_id, $date) {
        $performance = $this->db->fetch("
            SELECT impressions, clicks 
            FROM ad_performance 
            WHERE ad_id = ? AND date = ?
        ", [$ad_id, $date]);
        
        if ($performance && $performance['impressions'] > 0) {
            $ctr = ($performance['clicks'] / $performance['impressions']) * 100;
            $this->db->update("
                UPDATE ad_performance 
                SET ctr = ? 
                WHERE ad_id = ? AND date = ?
            ", [$ctr, $ad_id, $date]);
        }
    }
    
    /**
     * Render ad HTML
     */
    public function renderAd($ad, $track_impression = true) {
        if ($track_impression) {
            $this->trackImpression($ad['id'], $_SERVER['REQUEST_URI'] ?? '');
        }
        
        $html = '';
        
        switch ($ad['ad_type']) {
            case 'banner':
                $html = $this->renderBannerAd($ad);
                break;
            case 'popup':
                $html = $this->renderPopupAd($ad);
                break;
            case 'sidebar':
                $html = $this->renderSidebarAd($ad);
                break;
            case 'inline':
                $html = $this->renderInlineAd($ad);
                break;
            case 'video':
                $html = $this->renderVideoAd($ad);
                break;
            case 'carousel':
                $html = $this->renderCarouselAd($ad);
                break;
            default:
                $html = $this->renderBannerAd($ad);
        }
        
        return $html;
    }
    
    /**
     * Render banner ad
     */
    private function renderBannerAd($ad) {
        $click_url = "ads_click.php?ad_id=" . $ad['id'] . "&redirect=" . urlencode($ad['link_url']);
        
        return '
        <div class="ad-banner" data-ad-id="' . $ad['id'] . '">
            <a href="' . $click_url . '" target="_blank" class="ad-link">
                ' . ($ad['image_url'] ? '<img src="' . htmlspecialchars($ad['image_url']) . '" alt="' . htmlspecialchars($ad['title']) . '" class="ad-image">' : '') . '
                <div class="ad-content">
                    <h3>' . htmlspecialchars($ad['title']) . '</h3>
                    <p>' . htmlspecialchars($ad['description']) . '</p>
                    <span class="ad-cta">En savoir plus</span>
                </div>
            </a>
        </div>';
    }
    
    /**
     * Render popup ad
     */
    private function renderPopupAd($ad) {
        $click_url = "ads_click.php?ad_id=" . $ad['id'] . "&redirect=" . urlencode($ad['link_url']);
        
        return '
        <div class="ad-popup-overlay" data-ad-id="' . $ad['id'] . '">
            <div class="ad-popup">
                <button class="ad-close" onclick="closeAdPopup(' . $ad['id'] . ')">&times;</button>
                <a href="' . $click_url . '" target="_blank" class="ad-link">
                    ' . ($ad['image_url'] ? '<img src="' . htmlspecialchars($ad['image_url']) . '" alt="' . htmlspecialchars($ad['title']) . '" class="ad-popup-image">' : '') . '
                    <div class="ad-popup-content">
                        <h2>' . htmlspecialchars($ad['title']) . '</h2>
                        <p>' . htmlspecialchars($ad['description']) . '</p>
                        <span class="ad-popup-cta">En savoir plus</span>
                    </div>
                </a>
            </div>
        </div>';
    }
    
    /**
     * Render sidebar ad
     */
    private function renderSidebarAd($ad) {
        $click_url = "ads_click.php?ad_id=" . $ad['id'] . "&redirect=" . urlencode($ad['link_url']);
        
        return '
        <div class="ad-sidebar" data-ad-id="' . $ad['id'] . '">
            <a href="' . $click_url . '" target="_blank" class="ad-link">
                ' . ($ad['image_url'] ? '<img src="' . htmlspecialchars($ad['image_url']) . '" alt="' . htmlspecialchars($ad['title']) . '" class="ad-sidebar-image">' : '') . '
                <div class="ad-sidebar-content">
                    <h4>' . htmlspecialchars($ad['title']) . '</h4>
                    <p>' . htmlspecialchars($ad['description']) . '</p>
                </div>
            </a>
        </div>';
    }
    
    /**
     * Render inline ad
     */
    private function renderInlineAd($ad) {
        $click_url = "ads_click.php?ad_id=" . $ad['id'] . "&redirect=" . urlencode($ad['link_url']);
        
        return '
        <div class="ad-inline" data-ad-id="' . $ad['id'] . '">
            <a href="' . $click_url . '" target="_blank" class="ad-link">
                <div class="ad-inline-content">
                    <h3>' . htmlspecialchars($ad['title']) . '</h3>
                    <p>' . htmlspecialchars($ad['description']) . '</p>
                    <span class="ad-inline-cta">Découvrir</span>
                </div>
            </a>
        </div>';
    }
    
    /**
     * Render video ad
     */
    private function renderVideoAd($ad) {
        $click_url = "ads_click.php?ad_id=" . $ad['id'] . "&redirect=" . urlencode($ad['link_url']);
        
        return '
        <div class="ad-video" data-ad-id="' . $ad['id'] . '">
            <a href="' . $click_url . '" target="_blank" class="ad-link">
                ' . ($ad['video_url'] ? '<video autoplay muted loop class="ad-video-player"><source src="' . htmlspecialchars($ad['video_url']) . '" type="video/mp4"></video>' : '') . '
                <div class="ad-video-overlay">
                    <h3>' . htmlspecialchars($ad['title']) . '</h3>
                    <p>' . htmlspecialchars($ad['description']) . '</p>
                </div>
            </a>
        </div>';
    }
    
    /**
     * Render carousel ad
     */
    private function renderCarouselAd($ad) {
        // For carousel ads, we might have multiple images
        $images = json_decode($ad['image_url'], true) ?: [$ad['image_url']];
        $click_url = "ads_click.php?ad_id=" . $ad['id'] . "&redirect=" . urlencode($ad['link_url']);
        
        $carousel_items = '';
        foreach ($images as $index => $image) {
            $active_class = $index === 0 ? 'active' : '';
            $carousel_items .= '
            <div class="carousel-item ' . $active_class . '">
                <img src="' . htmlspecialchars($image) . '" alt="' . htmlspecialchars($ad['title']) . '" class="ad-carousel-image">
            </div>';
        }
        
        return '
        <div class="ad-carousel" data-ad-id="' . $ad['id'] . '">
            <a href="' . $click_url . '" target="_blank" class="ad-link">
                <div id="carousel-' . $ad['id'] . '" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        ' . $carousel_items . '
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carousel-' . $ad['id'] . '" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carousel-' . $ad['id'] . '" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
                <div class="ad-carousel-content">
                    <h3>' . htmlspecialchars($ad['title']) . '</h3>
                    <p>' . htmlspecialchars($ad['description']) . '</p>
                </div>
            </a>
        </div>';
    }
    
    /**
     * Get device type
     */
    private function getDeviceType() {
        $user_agent = $this->user_agent;
        
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($user_agent))) {
            return 'tablet';
        }
        
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($user_agent))) {
            return 'mobile';
        }
        
        return 'desktop';
    }
    
    /**
     * Get browser name
     */
    private function getBrowser() {
        $user_agent = $this->user_agent;
        
        if (preg_match('/MSIE|Trident/i', $user_agent)) {
            return 'Internet Explorer';
        } elseif (preg_match('/Firefox/i', $user_agent)) {
            return 'Firefox';
        } elseif (preg_match('/Chrome/i', $user_agent)) {
            return 'Chrome';
        } elseif (preg_match('/Safari/i', $user_agent)) {
            return 'Safari';
        } elseif (preg_match('/Opera|OPR/i', $user_agent)) {
            return 'Opera';
        } else {
            return 'Unknown';
        }
    }
    
    /**
     * Get country (simplified - in production, use a proper geolocation service)
     */
    private function getCountry() {
        // This is a simplified version. In production, use a proper geolocation service
        return 'Morocco'; // Default for EMPLOIDB
    }
    
    /**
     * Get city (simplified - in production, use a proper geolocation service)
     */
    private function getCity() {
        // This is a simplified version. In production, use a proper geolocation service
        return 'Casablanca'; // Default for EMPLOIDB
    }
}
