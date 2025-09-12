<?php
/**
 * Ads Display Component
 * Include this file in any front office page to display advertisements
 */

// Include ads helper if not already included
if (!class_exists('AdsHelper')) {
    include_once __DIR__ . '/ads_helper.php';
}

// Initialize ads helper
$ads_helper = new AdsHelper($db);

// Function to display ads for a specific position
function displayAds($position, $limit = 1) {
    global $ads_helper;
    
    $ads = $ads_helper->getActiveAds($position, $limit);
    
    if (empty($ads)) {
        return ''; // No ads to display
    }
    
    $html = '';
    foreach ($ads as $ad) {
        $html .= $ads_helper->renderAd($ad);
    }
    
    return $html;
}

// Function to display header ads
function displayHeaderAds() {
    return displayAds('header', 1);
}

// Function to display footer ads
function displayFooterAds() {
    return displayAds('footer', 1);
}

// Function to display sidebar ads
function displaySidebarAds() {
    return displayAds('sidebar', 2);
}

// Function to display content top ads
function displayContentTopAds() {
    return displayAds('content_top', 1);
}

// Function to display content bottom ads
function displayContentBottomAds() {
    return displayAds('content_bottom', 1);
}

// Function to display popup ads
function displayPopupAds() {
    return displayAds('popup', 1);
}

// Function to display inline ads
function displayInlineAds() {
    return displayAds('inline', 1);
}
?>

<style>
/* Ad Styles */
.ad-banner {
    display: flex;
    align-items: center;
    padding: 15px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px;
    margin-bottom: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.ad-banner:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.ad-banner .ad-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
    margin-right: 15px;
}

.ad-banner .ad-content h3 {
    margin: 0 0 5px 0;
    font-size: 18px;
    font-weight: 600;
}

.ad-banner .ad-content p {
    margin: 0 0 10px 0;
    opacity: 0.9;
    font-size: 14px;
}

.ad-banner .ad-cta {
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 8px 16px;
    text-decoration: none;
    border-radius: 4px;
    transition: background 0.3s;
    font-size: 12px;
    font-weight: 500;
}

.ad-banner .ad-cta:hover {
    background: rgba(255,255,255,0.3);
    color: white;
    text-decoration: none;
}

/* Sidebar Ads */
.ad-sidebar {
    background: white;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.ad-sidebar:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.ad-sidebar .ad-sidebar-image {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 4px;
    margin-bottom: 10px;
}

.ad-sidebar .ad-sidebar-content h4 {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.ad-sidebar .ad-sidebar-content p {
    margin: 0;
    font-size: 13px;
    color: #666;
    line-height: 1.4;
}

/* Inline Ads */
.ad-inline {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    text-align: center;
    transition: transform 0.3s ease;
}

.ad-inline:hover {
    transform: translateY(-2px);
}

.ad-inline .ad-inline-content h3 {
    margin: 0 0 10px 0;
    font-size: 20px;
    font-weight: 600;
}

.ad-inline .ad-inline-content p {
    margin: 0 0 15px 0;
    opacity: 0.9;
}

.ad-inline .ad-inline-cta {
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 4px;
    transition: background 0.3s;
    font-weight: 500;
}

.ad-inline .ad-inline-cta:hover {
    background: rgba(255,255,255,0.3);
    color: white;
    text-decoration: none;
}

/* Popup Ads */
.ad-popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ad-popup {
    background: white;
    border-radius: 12px;
    padding: 30px;
    max-width: 400px;
    position: relative;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.ad-popup .ad-close {
    position: absolute;
    top: 10px;
    right: 15px;
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.ad-popup .ad-close:hover {
    color: #333;
}

.ad-popup .ad-popup-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 20px;
}

.ad-popup .ad-popup-content h2 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 20px;
    font-weight: 600;
}

.ad-popup .ad-popup-content p {
    margin: 0 0 20px 0;
    color: #666;
    line-height: 1.5;
}

.ad-popup .ad-popup-cta {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 24px;
    text-decoration: none;
    border-radius: 6px;
    display: inline-block;
    font-weight: 500;
    transition: transform 0.3s ease;
}

.ad-popup .ad-popup-cta:hover {
    transform: translateY(-2px);
    color: white;
    text-decoration: none;
}

/* Video Ads */
.ad-video {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    margin: 20px 0;
}

.ad-video .ad-video-player {
    width: 100%;
    height: 300px;
    object-fit: cover;
}

.ad-video .ad-video-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    color: white;
    padding: 20px;
}

.ad-video .ad-video-overlay h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
    font-weight: 600;
}

.ad-video .ad-video-overlay p {
    margin: 0;
    opacity: 0.9;
    font-size: 14px;
}

/* Carousel Ads */
.ad-carousel {
    margin: 20px 0;
}

.ad-carousel .ad-carousel-image {
    width: 100%;
    height: 250px;
    object-fit: cover;
}

.ad-carousel .ad-carousel-content {
    background: white;
    padding: 20px;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.ad-carousel .ad-carousel-content h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.ad-carousel .ad-carousel-content p {
    margin: 0;
    color: #666;
    font-size: 14px;
    line-height: 1.4;
}

/* Responsive Design */
@media (max-width: 768px) {
    .ad-banner {
        flex-direction: column;
        text-align: center;
    }
    
    .ad-banner .ad-image {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .ad-popup {
        margin: 20px;
        max-width: none;
    }
    
    .ad-video .ad-video-player {
        height: 200px;
    }
}

/* Ad Link Styles */
.ad-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.ad-link:hover {
    text-decoration: none;
    color: inherit;
}

/* Ad Badge */
.ad-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>

<script>
// Close popup ads
function closeAdPopup(adId) {
    const popup = document.querySelector(`[data-ad-id="${adId}"]`);
    if (popup) {
        popup.style.display = 'none';
    }
}

// Auto-close popup ads after 10 seconds
document.addEventListener('DOMContentLoaded', function() {
    const popups = document.querySelectorAll('.ad-popup-overlay');
    popups.forEach(popup => {
        setTimeout(() => {
            popup.style.display = 'none';
        }, 10000);
    });
});

// Track ad visibility for better analytics
function trackAdVisibility() {
    const ads = document.querySelectorAll('[data-ad-id]');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const adId = entry.target.getAttribute('data-ad-id');
                // You can send additional tracking data here
                console.log('Ad visible:', adId);
            }
        });
    });
    
    ads.forEach(ad => observer.observe(ad));
}

// Initialize ad tracking
document.addEventListener('DOMContentLoaded', trackAdVisibility);
</script>
