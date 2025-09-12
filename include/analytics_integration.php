<?php
/**
 * Analytics Integration Script
 * Include this file in any page to enable user tracking
 */

// Set page title and category for tracking
$page_title = $page_title ?? 'EMPLOIDB';
$page_category = $page_category ?? 'general';

// Include the user tracker
if (file_exists(__DIR__ . '/user_tracker.php')) {
    include_once __DIR__ . '/user_tracker.php';
}

// Track specific activities based on page
$current_page = $_SERVER['REQUEST_URI'] ?? '/';

// Track different activities based on the page
if (strpos($current_page, 'login.php') !== false) {
    if (isset($user_tracker)) {
        $user_tracker->trackActivity('login', 'User login attempt', [
            'page' => $current_page,
            'method' => 'form'
        ]);
    }
} elseif (strpos($current_page, 'register.php') !== false) {
    if (isset($user_tracker)) {
        $user_tracker->trackActivity('register', 'User registration', [
            'page' => $current_page,
            'method' => 'form'
        ]);
    }
} elseif (strpos($current_page, 'apply_job.php') !== false) {
    if (isset($user_tracker)) {
        $user_tracker->trackActivity('job_apply', 'Job application submitted', [
            'page' => $current_page,
            'job_id' => $_GET['id'] ?? null
        ]);
    }
} elseif (strpos($current_page, 'post_job.php') !== false) {
    if (isset($user_tracker)) {
        $user_tracker->trackActivity('job_post', 'Job posting created', [
            'page' => $current_page
        ]);
    }
} elseif (strpos($current_page, 'admin/') !== false) {
    if (isset($user_tracker)) {
        $user_tracker->trackActivity('admin_access', 'Admin panel access', [
            'page' => $current_page,
            'section' => basename($current_page, '.php')
        ]);
    }
}

// Add JavaScript for enhanced tracking
?>
<script>
// Enhanced client-side tracking
document.addEventListener('DOMContentLoaded', function() {
    // Track page view time
    let startTime = Date.now();
    
    // Track scroll depth
    let maxScroll = 0;
    window.addEventListener('scroll', function() {
        const scrollPercent = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
        if (scrollPercent > maxScroll) {
            maxScroll = scrollPercent;
        }
    });
    
    // Track form submissions
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            // Send tracking data via AJAX
            fetch('include/track_activity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'form_submit',
                    form_id: form.id || 'unknown',
                    page: window.location.pathname
                })
            });
        });
    });
    
    // Track clicks on important elements
    document.querySelectorAll('a[href*="apply"], .btn-apply, .apply-job').forEach(link => {
        link.addEventListener('click', function() {
            fetch('include/track_activity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'job_apply_click',
                    job_id: this.dataset.jobId || 'unknown',
                    page: window.location.pathname
                })
            });
        });
    });
    
    // Track page exit
    window.addEventListener('beforeunload', function() {
        const timeSpent = Date.now() - startTime;
        const data = {
            action: 'page_exit',
            time_spent: timeSpent,
            scroll_depth: maxScroll,
            page: window.location.pathname
        };
        
        // Use sendBeacon for reliable data sending on page unload
        if (navigator.sendBeacon) {
            navigator.sendBeacon('include/track_activity.php', JSON.stringify(data));
        }
    });
});

// Track search queries
if (typeof trackSearch !== 'undefined') {
    function trackSearch(query) {
        fetch('include/track_activity.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'search',
                query: query,
                page: window.location.pathname
            })
        });
    }
}
</script>
