<?php
/**
 * Advanced Ad Display System
 * Comprehensive advertising system for EMPLOIDB platform
 */

class AdDisplaySystem {
    private $db;
    private $user_id;
    private $user_type;
    private $page_context;
    
    public function __construct($database, $user_id = null, $user_type = 'guest', $page_context = 'general') {
        $this->db = $database;
        $this->user_id = $user_id;
        $this->user_type = $user_type;
        $this->page_context = $page_context;
    }
    
    /**
     * Get active ads for specific position and context
     */
    public function getActiveAds($position, $limit = 1, $targeting_options = []) {
        try {
            $where_conditions = [
                "status = 'active'",
                "start_date <= NOW()",
                "end_date >= NOW()",
                "position = ?"
            ];
            $params = [$position];
            
            // Add targeting conditions
            if (!empty($targeting_options)) {
                if (isset($targeting_options['audience']) && $targeting_options['audience'] !== 'all') {
                    $where_conditions[] = "(target_audience = ? OR target_audience = 'all')";
                    $params[] = $targeting_options['audience'];
                }
                
                if (isset($targeting_options['device'])) {
                    $where_conditions[] = "(target_device = ? OR target_device = 'all')";
                    $params[] = $targeting_options['device'];
                }
                
                if (isset($targeting_options['location'])) {
                    $where_conditions[] = "(target_location = ? OR target_location = 'all')";
                    $params[] = $targeting_options['location'];
                }
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            
            $query = "SELECT * FROM advertisements 
                     WHERE $where_clause 
                     ORDER BY RAND() 
                     LIMIT ?";
            $params[] = $limit;
            
            return $this->db->fetchAll($query, $params) ?? [];
            
        } catch (Exception $e) {
            error_log("Ad Display System Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Render ad HTML based on ad type
     */
    public function renderAd($ad) {
        if (empty($ad)) return '';
        
        $ad_type = $ad['ad_type'] ?? 'banner';
        $ad_id = $ad['id'];
        
        // Track impression
        $this->trackImpression($ad_id);
        
        switch ($ad_type) {
            case 'banner':
                return $this->renderBannerAd($ad);
            case 'popup':
                return $this->renderPopupAd($ad);
            case 'sidebar':
                return $this->renderSidebarAd($ad);
            case 'inline':
                return $this->renderInlineAd($ad);
            case 'video':
                return $this->renderVideoAd($ad);
            case 'carousel':
                return $this->renderCarouselAd($ad);
            case 'native':
                return $this->renderNativeAd($ad);
            case 'sticky':
                return $this->renderStickyAd($ad);
            case 'floating':
                return $this->renderFloatingAd($ad);
            default:
                return $this->renderBannerAd($ad);
        }
    }
    
    /**
     * Render Banner Ad
     */
    private function renderBannerAd($ad) {
        $ad_id = $ad['id'];
        $title = htmlspecialchars($ad['title']);
        $description = htmlspecialchars($ad['description']);
        $content = htmlspecialchars($ad['content']);
        $link_url = htmlspecialchars($ad['link_url']);
        $image_url = htmlspecialchars($ad['image_url']);
        $cta_text = htmlspecialchars($ad['cta_text'] ?? 'En savoir plus');
        $cta_color = $ad['cta_color'] ?? '#1e40af';
        
        $html = '<div class="ad-container ad-banner" data-ad-id="' . $ad_id . '" data-ad-type="banner">';
        $html .= '<a href="' . $link_url . '" class="ad-link" onclick="trackAdClick(' . $ad_id . ')">';
        $html .= '<div class="ad-banner-content">';
        
        if ($image_url) {
            $html .= '<img src="' . $image_url . '" alt="' . $title . '" class="ad-image">';
        }
        
        $html .= '<div class="ad-content">';
        $html .= '<h3>' . $title . '</h3>';
        $html .= '<p>' . $description . '</p>';
        $html .= '<div class="ad-cta" style="background-color: ' . $cta_color . '">' . $cta_text . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</a>';
        $html .= '<div class="ad-badge">Publicité</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Sidebar Ad
     */
    private function renderSidebarAd($ad) {
        $ad_id = $ad['id'];
        $title = htmlspecialchars($ad['title']);
        $description = htmlspecialchars($ad['description']);
        $link_url = htmlspecialchars($ad['link_url']);
        $image_url = htmlspecialchars($ad['image_url']);
        $cta_text = htmlspecialchars($ad['cta_text'] ?? 'Découvrir');
        
        $html = '<div class="ad-container ad-sidebar" data-ad-id="' . $ad_id . '" data-ad-type="sidebar">';
        $html .= '<a href="' . $link_url . '" class="ad-link" onclick="trackAdClick(' . $ad_id . ')">';
        
        if ($image_url) {
            $html .= '<img src="' . $image_url . '" alt="' . $title . '" class="ad-sidebar-image">';
        }
        
        $html .= '<div class="ad-sidebar-content">';
        $html .= '<h4>' . $title . '</h4>';
        $html .= '<p>' . $description . '</p>';
        $html .= '<div class="ad-cta">' . $cta_text . '</div>';
        $html .= '</div>';
        $html .= '</a>';
        $html .= '<div class="ad-badge">Publicité</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Inline Ad
     */
    private function renderInlineAd($ad) {
        $ad_id = $ad['id'];
        $title = htmlspecialchars($ad['title']);
        $description = htmlspecialchars($ad['description']);
        $link_url = htmlspecialchars($ad['link_url']);
        $cta_text = htmlspecialchars($ad['cta_text'] ?? 'Découvrir');
        
        $html = '<div class="ad-container ad-inline" data-ad-id="' . $ad_id . '" data-ad-type="inline">';
        $html .= '<a href="' . $link_url . '" class="ad-link" onclick="trackAdClick(' . $ad_id . ')">';
        $html .= '<div class="ad-inline-content">';
        $html .= '<h3>' . $title . '</h3>';
        $html .= '<p>' . $description . '</p>';
        $html .= '<div class="ad-inline-cta">' . $cta_text . '</div>';
        $html .= '</div>';
        $html .= '</a>';
        $html .= '<div class="ad-badge">Publicité</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Popup Ad
     */
    private function renderPopupAd($ad) {
        $ad_id = $ad['id'];
        $title = htmlspecialchars($ad['title']);
        $description = htmlspecialchars($ad['description']);
        $link_url = htmlspecialchars($ad['link_url']);
        $image_url = htmlspecialchars($ad['image_url']);
        $cta_text = htmlspecialchars($ad['cta_text'] ?? 'Découvrir');
        
        $html = '<div class="ad-popup-overlay" data-ad-id="' . $ad_id . '" data-ad-type="popup">';
        $html .= '<div class="ad-popup">';
        $html .= '<button class="ad-close" onclick="closeAdPopup(' . $ad_id . ')">&times;</button>';
        
        if ($image_url) {
            $html .= '<img src="' . $image_url . '" alt="' . $title . '" class="ad-popup-image">';
        }
        
        $html .= '<div class="ad-popup-content">';
        $html .= '<h2>' . $title . '</h2>';
        $html .= '<p>' . $description . '</p>';
        $html .= '<a href="' . $link_url . '" class="ad-popup-cta" onclick="trackAdClick(' . $ad_id . ')">' . $cta_text . '</a>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Video Ad
     */
    private function renderVideoAd($ad) {
        $ad_id = $ad['id'];
        $title = htmlspecialchars($ad['title']);
        $description = htmlspecialchars($ad['description']);
        $link_url = htmlspecialchars($ad['link_url']);
        $video_url = htmlspecialchars($ad['video_url'] ?? '');
        
        $html = '<div class="ad-container ad-video" data-ad-id="' . $ad_id . '" data-ad-type="video">';
        $html .= '<a href="' . $link_url . '" class="ad-link" onclick="trackAdClick(' . $ad_id . ')">';
        
        if ($video_url) {
            $html .= '<video class="ad-video-player" autoplay muted loop>';
            $html .= '<source src="' . $video_url . '" type="video/mp4">';
            $html .= '</video>';
        }
        
        $html .= '<div class="ad-video-overlay">';
        $html .= '<h3>' . $title . '</h3>';
        $html .= '<p>' . $description . '</p>';
        $html .= '</div>';
        $html .= '</a>';
        $html .= '<div class="ad-badge">Publicité</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Carousel Ad
     */
    private function renderCarouselAd($ad) {
        $ad_id = $ad['id'];
        $title = htmlspecialchars($ad['title']);
        $description = htmlspecialchars($ad['description']);
        $link_url = htmlspecialchars($ad['link_url']);
        $image_url = htmlspecialchars($ad['image_url']);
        
        $html = '<div class="ad-container ad-carousel" data-ad-id="' . $ad_id . '" data-ad-type="carousel">';
        $html .= '<a href="' . $link_url . '" class="ad-link" onclick="trackAdClick(' . $ad_id . ')">';
        
        if ($image_url) {
            $html .= '<img src="' . $image_url . '" alt="' . $title . '" class="ad-carousel-image">';
        }
        
        $html .= '<div class="ad-carousel-content">';
        $html .= '<h3>' . $title . '</h3>';
        $html .= '<p>' . $description . '</p>';
        $html .= '</div>';
        $html .= '</a>';
        $html .= '<div class="ad-badge">Publicité</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Native Ad
     */
    private function renderNativeAd($ad) {
        $ad_id = $ad['id'];
        $title = htmlspecialchars($ad['title']);
        $description = htmlspecialchars($ad['description']);
        $link_url = htmlspecialchars($ad['link_url']);
        $image_url = htmlspecialchars($ad['image_url']);
        
        $html = '<div class="ad-container ad-native" data-ad-id="' . $ad_id . '" data-ad-type="native">';
        $html .= '<a href="' . $link_url . '" class="ad-link" onclick="trackAdClick(' . $ad_id . ')">';
        $html .= '<div class="ad-native-content">';
        
        if ($image_url) {
            $html .= '<img src="' . $image_url . '" alt="' . $title . '" class="ad-native-image">';
        }
        
        $html .= '<div class="ad-native-text">';
        $html .= '<h4>' . $title . '</h4>';
        $html .= '<p>' . $description . '</p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</a>';
        $html .= '<div class="ad-badge">Publicité</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Sticky Ad
     */
    private function renderStickyAd($ad) {
        $ad_id = $ad['id'];
        $title = htmlspecialchars($ad['title']);
        $link_url = htmlspecialchars($ad['link_url']);
        $cta_text = htmlspecialchars($ad['cta_text'] ?? 'Découvrir');
        
        $html = '<div class="ad-container ad-sticky" data-ad-id="' . $ad_id . '" data-ad-type="sticky">';
        $html .= '<a href="' . $link_url . '" class="ad-link" onclick="trackAdClick(' . $ad_id . ')">';
        $html .= '<div class="ad-sticky-content">';
        $html .= '<span class="ad-sticky-text">' . $title . '</span>';
        $html .= '<span class="ad-sticky-cta">' . $cta_text . '</span>';
        $html .= '</div>';
        $html .= '</a>';
        $html .= '<button class="ad-sticky-close" onclick="closeStickyAd(' . $ad_id . ')">&times;</button>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Floating Ad
     */
    private function renderFloatingAd($ad) {
        $ad_id = $ad['id'];
        $title = htmlspecialchars($ad['title']);
        $link_url = htmlspecialchars($ad['link_url']);
        $image_url = htmlspecialchars($ad['image_url']);
        
        $html = '<div class="ad-container ad-floating" data-ad-id="' . $ad_id . '" data-ad-type="floating">';
        $html .= '<a href="' . $link_url . '" class="ad-link" onclick="trackAdClick(' . $ad_id . ')">';
        
        if ($image_url) {
            $html .= '<img src="' . $image_url . '" alt="' . $title . '" class="ad-floating-image">';
        }
        
        $html .= '<div class="ad-floating-content">';
        $html .= '<span class="ad-floating-text">' . $title . '</span>';
        $html .= '</div>';
        $html .= '</a>';
        $html .= '<button class="ad-floating-close" onclick="closeFloatingAd(' . $ad_id . ')">&times;</button>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Track ad impression
     */
    private function trackImpression($ad_id) {
        try {
            $this->db->insert("
                INSERT INTO ad_impressions (ad_id, user_id, user_type, page_context, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ", [
                $ad_id,
                $this->user_id,
                $this->user_type,
                $this->page_context,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log("Ad Impression Tracking Error: " . $e->getMessage());
        }
    }
    
    /**
     * Track ad click
     */
    public function trackClick($ad_id) {
        try {
            $this->db->insert("
                INSERT INTO ad_clicks (ad_id, user_id, user_type, page_context, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ", [
                $ad_id,
                $this->user_id,
                $this->user_type,
                $this->page_context,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log("Ad Click Tracking Error: " . $e->getMessage());
        }
    }
    
    /**
     * Get targeting options based on user context
     */
    public function getTargetingOptions() {
        $options = [
            'audience' => 'all',
            'device' => 'all',
            'location' => 'all'
        ];
        
        // Determine user type
        if ($this->user_type === 'job_seeker') {
            $options['audience'] = 'job_seekers';
        } elseif ($this->user_type === 'employer') {
            $options['audience'] = 'employers';
        }
        
        // Determine device type
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/Mobile|Android|iPhone|iPad/', $user_agent)) {
                $options['device'] = 'mobile';
            } else {
                $options['device'] = 'desktop';
            }
        }
        
        return $options;
    }
    
    /**
     * Display ads for specific position
     */
    public function displayAds($position, $limit = 1) {
        $targeting_options = $this->getTargetingOptions();
        $ads = $this->getActiveAds($position, $limit, $targeting_options);
        
        $html = '';
        foreach ($ads as $ad) {
            $html .= $this->renderAd($ad);
        }
        
        return $html;
    }
}

// Global functions for easy use in templates
function displayHeaderAds($limit = 1) {
    global $ad_system;
    return $ad_system->displayAds('header', $limit);
}

function displaySidebarAds($limit = 2) {
    global $ad_system;
    return $ad_system->displayAds('sidebar', $limit);
}

function displayFooterAds($limit = 1) {
    global $ad_system;
    return $ad_system->displayAds('footer', $limit);
}

function displayContentTopAds($limit = 1) {
    global $ad_system;
    return $ad_system->displayAds('content_top', $limit);
}

function displayContentBottomAds($limit = 1) {
    global $ad_system;
    return $ad_system->displayAds('content_bottom', $limit);
}

function displayInlineAds($limit = 1) {
    global $ad_system;
    return $ad_system->displayAds('inline', $limit);
}

function displayPopupAds($limit = 1) {
    global $ad_system;
    return $ad_system->displayAds('popup', $limit);
}

function displayFloatingAds($limit = 1) {
    global $ad_system;
    return $ad_system->displayAds('floating', $limit);
}

function displayStickyAds($limit = 1) {
    global $ad_system;
    return $ad_system->displayAds('sticky', $limit);
}
?>