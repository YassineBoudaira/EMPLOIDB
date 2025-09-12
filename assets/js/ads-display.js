/**
 * Advanced Ad Display System JavaScript
 * Handles ad interactions, tracking, and animations
 */

// Global ad tracking object
window.AdTracker = {
    impressions: new Set(),
    clicks: new Set(),
    
    // Track ad impression
    trackImpression: function(adId) {
        if (!this.impressions.has(adId)) {
            this.impressions.add(adId);
            this.sendTracking('impression', adId);
        }
    },
    
    // Track ad click
    trackClick: function(adId) {
        if (!this.clicks.has(adId)) {
            this.clicks.add(adId);
            this.sendTracking('click', adId);
        }
    },
    
    // Send tracking data to server
    sendTracking: function(type, adId) {
        fetch('ajax/track_ad.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                type: type,
                ad_id: adId,
                page_url: window.location.href,
                timestamp: new Date().toISOString()
            })
        }).catch(error => {
            console.log('Ad tracking error:', error);
        });
    }
};

// Global ad management functions
window.AdManager = {
    // Close popup ads
    closePopup: function(adId) {
        const popup = document.querySelector(`[data-ad-id="${adId}"][data-ad-type="popup"]`);
        if (popup) {
            popup.style.animation = 'popupSlideOut 0.3s ease-in forwards';
            setTimeout(() => {
                popup.remove();
            }, 300);
        }
    },
    
    // Close sticky ads
    closeSticky: function(adId) {
        const sticky = document.querySelector(`[data-ad-id="${adId}"][data-ad-type="sticky"]`);
        if (sticky) {
            sticky.style.animation = 'stickySlideDown 0.3s ease-in forwards';
            setTimeout(() => {
                sticky.remove();
            }, 300);
        }
    },
    
    // Close floating ads
    closeFloating: function(adId) {
        const floating = document.querySelector(`[data-ad-id="${adId}"][data-ad-type="floating"]`);
        if (floating) {
            floating.style.animation = 'floatingSlideOut 0.3s ease-in forwards';
            setTimeout(() => {
                floating.remove();
            }, 300);
        }
    },
    
    // Show ad with animation
    showAd: function(adId, animation = 'fadeIn') {
        const ad = document.querySelector(`[data-ad-id="${adId}"]`);
        if (ad) {
            ad.classList.add(`ad-${animation}`);
            ad.style.display = 'block';
        }
    },
    
    // Hide ad with animation
    hideAd: function(adId, animation = 'fadeOut') {
        const ad = document.querySelector(`[data-ad-id="${adId}"]`);
        if (ad) {
            ad.classList.add(`ad-${animation}`);
            setTimeout(() => {
                ad.style.display = 'none';
            }, 300);
        }
    }
};

// Ad click tracking
function trackAdClick(adId) {
    AdTracker.trackClick(adId);
    
    // Add click animation
    const ad = document.querySelector(`[data-ad-id="${adId}"]`);
    if (ad) {
        ad.style.transform = 'scale(0.98)';
        setTimeout(() => {
            ad.style.transform = '';
        }, 150);
    }
}

// Close popup ads
function closeAdPopup(adId) {
    AdManager.closePopup(adId);
}

// Close sticky ads
function closeStickyAd(adId) {
    AdManager.closeSticky(adId);
}

// Close floating ads
function closeFloatingAd(adId) {
    AdManager.closeFloating(adId);
}

// Intersection Observer for impression tracking
const adObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const adId = entry.target.getAttribute('data-ad-id');
            if (adId) {
                AdTracker.trackImpression(adId);
                
                // Add animation classes based on ad type
                const adType = entry.target.getAttribute('data-ad-type');
                if (adType === 'banner' || adType === 'sidebar') {
                    entry.target.classList.add('ad-slide-up');
                } else if (adType === 'inline') {
                    entry.target.classList.add('ad-bounce');
                } else {
                    entry.target.classList.add('ad-fade-in');
                }
            }
        }
    });
}, {
    threshold: 0.5,
    rootMargin: '0px 0px -50px 0px'
});

// Auto-close popup ads after delay
function autoClosePopups() {
    const popups = document.querySelectorAll('[data-ad-type="popup"]');
    popups.forEach(popup => {
        const adId = popup.getAttribute('data-ad-id');
        setTimeout(() => {
            if (popup.parentNode) {
                AdManager.closePopup(adId);
            }
        }, 10000); // 10 seconds
    });
}

// Handle popup ad display timing
function showPopupAds() {
    const popups = document.querySelectorAll('[data-ad-type="popup"]');
    popups.forEach((popup, index) => {
        setTimeout(() => {
            if (popup.parentNode) {
                popup.style.display = 'flex';
                popup.classList.add('ad-fade-in');
            }
        }, index * 2000); // Show each popup with 2-second delay
    });
}

// Handle sticky ad display
function showStickyAds() {
    const stickyAds = document.querySelectorAll('[data-ad-type="sticky"]');
    stickyAds.forEach((sticky, index) => {
        setTimeout(() => {
            if (sticky.parentNode) {
                sticky.style.display = 'flex';
                sticky.classList.add('ad-slide-up');
            }
        }, index * 3000); // Show each sticky with 3-second delay
    });
}

// Handle floating ad display
function showFloatingAds() {
    const floatingAds = document.querySelectorAll('[data-ad-type="floating"]');
    floatingAds.forEach((floating, index) => {
        setTimeout(() => {
            if (floating.parentNode) {
                floating.style.display = 'block';
                floating.classList.add('ad-slide-up');
            }
        }, index * 4000); // Show each floating with 4-second delay
    });
}

// Video ad controls
function initVideoAds() {
    const videoAds = document.querySelectorAll('[data-ad-type="video"] video');
    videoAds.forEach(video => {
        video.addEventListener('click', function() {
            if (this.paused) {
                this.play();
            } else {
                this.pause();
            }
        });
        
        // Auto-pause when not visible
        const videoObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) {
                    entry.target.pause();
                }
            });
        });
        
        videoObserver.observe(video);
    });
}

// Carousel ad functionality
function initCarouselAds() {
    const carouselAds = document.querySelectorAll('[data-ad-type="carousel"]');
    carouselAds.forEach(carousel => {
        const images = carousel.querySelectorAll('img');
        if (images.length > 1) {
            let currentIndex = 0;
            
            setInterval(() => {
                images[currentIndex].style.opacity = '0';
                currentIndex = (currentIndex + 1) % images.length;
                images[currentIndex].style.opacity = '1';
            }, 3000);
        }
    });
}

// Ad performance monitoring
function monitorAdPerformance() {
    const ads = document.querySelectorAll('[data-ad-id]');
    ads.forEach(ad => {
        const adId = ad.getAttribute('data-ad-id');
        const startTime = Date.now();
        
        // Monitor view time
        const viewObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const viewTime = Date.now() - startTime;
                    if (viewTime > 5000) { // 5 seconds
                        // Mark as high performance
                        ad.classList.add('high-performance');
                    }
                }
            });
        });
        
        viewObserver.observe(ad);
    });
}

// Responsive ad handling
function handleResponsiveAds() {
    const ads = document.querySelectorAll('[data-ad-id]');
    
    function updateAdSizes() {
        const isMobile = window.innerWidth <= 768;
        const isTablet = window.innerWidth <= 1024 && window.innerWidth > 768;
        
        ads.forEach(ad => {
            const adType = ad.getAttribute('data-ad-type');
            
            if (isMobile) {
                ad.classList.add('mobile-ad');
                if (adType === 'floating') {
                    ad.style.display = 'none'; // Hide floating ads on mobile
                }
            } else if (isTablet) {
                ad.classList.add('tablet-ad');
            } else {
                ad.classList.add('desktop-ad');
            }
        });
    }
    
    updateAdSizes();
    window.addEventListener('resize', updateAdSizes);
}

// Ad frequency capping
function implementFrequencyCapping() {
    const ads = document.querySelectorAll('[data-ad-id]');
    const frequencyCap = 3; // Max 3 impressions per ad per session
    
    ads.forEach(ad => {
        const adId = ad.getAttribute('data-ad-id');
        const impressions = parseInt(localStorage.getItem(`ad_impressions_${adId}`) || '0');
        
        if (impressions >= frequencyCap) {
            ad.style.display = 'none';
        } else {
            localStorage.setItem(`ad_impressions_${adId}`, (impressions + 1).toString());
        }
    });
}

// Initialize all ad functionality
document.addEventListener('DOMContentLoaded', function() {
    // Observe all ads for impression tracking
    const allAds = document.querySelectorAll('[data-ad-id]');
    allAds.forEach(ad => {
        adObserver.observe(ad);
    });
    
    // Initialize different ad types
    initVideoAds();
    initCarouselAds();
    
    // Handle responsive design
    handleResponsiveAds();
    
    // Implement frequency capping
    implementFrequencyCapping();
    
    // Monitor performance
    monitorAdPerformance();
    
    // Show timed ads
    setTimeout(showPopupAds, 2000);
    setTimeout(showStickyAds, 5000);
    setTimeout(showFloatingAds, 8000);
    
    // Auto-close popups
    setTimeout(autoClosePopups, 12000);
    
    console.log('Ad Display System initialized with', allAds.length, 'ads');
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes popupSlideOut {
        from {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
        to {
            opacity: 0;
            transform: scale(0.8) translateY(-20px);
        }
    }
    
    @keyframes stickySlideDown {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(100px);
        }
    }
    
    @keyframes floatingSlideOut {
        from {
            opacity: 1;
            transform: translateY(-50%) translateX(0);
        }
        to {
            opacity: 0;
            transform: translateY(-50%) translateX(100px);
        }
    }
    
    .ad-fade-out {
        animation: fadeOut 0.3s ease-in forwards;
    }
    
    @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
    }
    
    .high-performance {
        border: 2px solid #28a745;
    }
    
    .mobile-ad {
        font-size: 14px;
    }
    
    .tablet-ad {
        font-size: 16px;
    }
    
    .desktop-ad {
        font-size: 18px;
    }
`;
document.head.appendChild(style);
