<?php
/**
 * Advanced User Tracking System
 * EMPLOIDB - Professional Analytics
 */

class UserTracker {
    private $db;
    private $session_id;
    private $user_id;
    private $ip_address;
    private $user_agent;
    private $device_info;
    
    public function __construct($db) {
        $this->db = $db;
        $this->session_id = session_id();
        $this->user_id = $_SESSION['user_id'] ?? null;
        $this->ip_address = $this->getClientIP();
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $this->device_info = $this->parseUserAgent();
        
        // Start tracking session
        $this->startSession();
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Parse user agent to get device information
     */
    private function parseUserAgent() {
        $user_agent = $this->user_agent;
        $device_info = [
            'browser' => 'Unknown',
            'browser_version' => '',
            'operating_system' => 'Unknown',
            'os_version' => '',
            'device_type' => 'desktop'
        ];
        
        // Browser detection
        if (preg_match('/Chrome\/([0-9.]+)/', $user_agent, $matches)) {
            $device_info['browser'] = 'Chrome';
            $device_info['browser_version'] = $matches[1];
        } elseif (preg_match('/Firefox\/([0-9.]+)/', $user_agent, $matches)) {
            $device_info['browser'] = 'Firefox';
            $device_info['browser_version'] = $matches[1];
        } elseif (preg_match('/Safari\/([0-9.]+)/', $user_agent, $matches)) {
            $device_info['browser'] = 'Safari';
            $device_info['browser_version'] = $matches[1];
        } elseif (preg_match('/Edge\/([0-9.]+)/', $user_agent, $matches)) {
            $device_info['browser'] = 'Edge';
            $device_info['browser_version'] = $matches[1];
        }
        
        // Operating system detection
        if (preg_match('/Windows NT ([0-9.]+)/', $user_agent, $matches)) {
            $device_info['operating_system'] = 'Windows';
            $device_info['os_version'] = $this->getWindowsVersion($matches[1]);
        } elseif (preg_match('/Mac OS X ([0-9._]+)/', $user_agent, $matches)) {
            $device_info['operating_system'] = 'macOS';
            $device_info['os_version'] = str_replace('_', '.', $matches[1]);
        } elseif (preg_match('/Linux/', $user_agent)) {
            $device_info['operating_system'] = 'Linux';
        } elseif (preg_match('/Android ([0-9.]+)/', $user_agent, $matches)) {
            $device_info['operating_system'] = 'Android';
            $device_info['os_version'] = $matches[1];
        } elseif (preg_match('/iPhone OS ([0-9._]+)/', $user_agent, $matches)) {
            $device_info['operating_system'] = 'iOS';
            $device_info['os_version'] = str_replace('_', '.', $matches[1]);
        }
        
        // Device type detection
        if (preg_match('/(iPad|Android.*Tablet|Tablet)/', $user_agent)) {
            $device_info['device_type'] = 'tablet';
        } elseif (preg_match('/(iPhone|Android|Mobile|BlackBerry|Windows Phone)/', $user_agent)) {
            $device_info['device_type'] = 'mobile';
        }
        
        return $device_info;
    }
    
    /**
     * Get Windows version from NT version
     */
    private function getWindowsVersion($nt_version) {
        $versions = [
            '10.0' => '11',
            '6.3' => '8.1',
            '6.2' => '8',
            '6.1' => '7',
            '6.0' => 'Vista'
        ];
        
        return $versions[$nt_version] ?? $nt_version;
    }
    
    /**
     * Get geographic information from IP
     */
    private function getGeographicInfo() {
        // You can integrate with services like MaxMind GeoIP2 or IP-API
        // For now, returning default values
        return [
            'country' => 'Unknown',
            'city' => 'Unknown',
            'region' => 'Unknown',
            'timezone' => 'UTC'
        ];
    }
    
    /**
     * Start tracking session
     */
    private function startSession() {
        $geo_info = $this->getGeographicInfo();
        
        $sql = "INSERT INTO user_sessions (
            user_id, session_id, ip_address, user_agent, browser, browser_version,
            operating_system, os_version, device_type, country, city, timezone,
            entry_page, session_start, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), TRUE)
        ON DUPLICATE KEY UPDATE 
            last_activity = NOW(),
            is_active = TRUE";
        
        $params = [
            $this->user_id,
            $this->session_id,
            $this->ip_address,
            $this->user_agent,
            $this->device_info['browser'],
            $this->device_info['browser_version'],
            $this->device_info['operating_system'],
            $this->device_info['os_version'],
            $this->device_info['device_type'],
            $geo_info['country'],
            $geo_info['city'],
            $geo_info['timezone'],
            $_SERVER['REQUEST_URI'] ?? '/'
        ];
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        } catch (Exception $e) {
            error_log("User tracking error: " . $e->getMessage());
        }
    }
    
    /**
     * Track page view
     */
    public function trackPageView($page_url, $page_title = '', $page_category = '') {
        $sql = "INSERT INTO page_views (
            session_id, user_id, page_url, page_title, page_category,
            referrer_page, view_timestamp
        ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $this->session_id,
            $this->user_id,
            $page_url,
            $page_title,
            $page_category,
            $_SERVER['HTTP_REFERER'] ?? ''
        ];
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Page view tracking error: " . $e->getMessage());
        }
    }
    
    /**
     * Track user activity
     */
    public function trackActivity($activity_type, $description = '', $data = []) {
        $sql = "INSERT INTO user_activity_log (
            user_id, session_id, activity_type, activity_description,
            activity_data, ip_address, user_agent, activity_timestamp
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $this->user_id,
            $this->session_id,
            $activity_type,
            $description,
            json_encode($data),
            $this->ip_address,
            $this->user_agent
        ];
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Activity tracking error: " . $e->getMessage());
        }
    }
    
    /**
     * Update online user status
     */
    public function updateOnlineStatus($current_page = '') {
        if (!$this->user_id) return;
        
        $sql = "INSERT INTO online_users (
            user_id, session_id, username, full_name, user_role,
            current_page, device_type, browser, operating_system,
            ip_address, country, city, last_activity, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), TRUE)
        ON DUPLICATE KEY UPDATE 
            current_page = VALUES(current_page),
            last_activity = NOW(),
            is_active = TRUE";
        
        $user_info = $this->getUserInfo();
        $geo_info = $this->getGeographicInfo();
        
        $params = [
            $this->user_id,
            $this->session_id,
            $user_info['username'] ?? '',
            $user_info['full_name'] ?? '',
            $user_info['role'] ?? 'candidate',
            $current_page,
            $this->device_info['device_type'],
            $this->device_info['browser'],
            $this->device_info['operating_system'],
            $this->ip_address,
            $geo_info['country'],
            $geo_info['city']
        ];
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Online status update error: " . $e->getMessage());
        }
    }
    
    /**
     * Get user information
     */
    private function getUserInfo() {
        if (!$this->user_id) return [];
        
        $sql = "SELECT u.user, u.role, p.nom, p.prenom 
                FROM users u 
                LEFT JOIN profiles p ON u.id = p.user_id 
                WHERE u.id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->user_id]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($result && count($result) > 0) {
                $user = $result[0];
                return [
                    'username' => $user['user'],
                    'role' => $user['role'],
                    'full_name' => trim($user['nom'] . ' ' . $user['prenom'])
                ];
            }
        } catch (Exception $e) {
            error_log("User info error: " . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * End session
     */
    public function endSession() {
        $sql = "UPDATE user_sessions 
                SET session_end = NOW(), 
                    session_duration = TIMESTAMPDIFF(SECOND, session_start, NOW()),
                    is_active = FALSE 
                WHERE session_id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->session_id]);
        } catch (Exception $e) {
            error_log("Session end error: " . $e->getMessage());
        }
        
        // Update online status
        $sql = "UPDATE online_users 
                SET is_active = FALSE 
                WHERE session_id = ?";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->session_id]);
        } catch (Exception $e) {
            error_log("Online status end error: " . $e->getMessage());
        }
    }
    
    /**
     * Get analytics data
     */
    public function getAnalyticsData($period = 'today') {
        $data = [];
        
        switch ($period) {
            case 'today':
                $date_condition = "DATE(created_at) = CURDATE()";
                break;
            case 'week':
                $date_condition = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $date_condition = "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
            case 'year':
                $date_condition = "created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                break;
            default:
                $date_condition = "DATE(created_at) = CURDATE()";
        }
        
        // Total users
        $sql = "SELECT COUNT(DISTINCT user_id) as total_users 
                FROM user_sessions 
                WHERE $date_condition AND user_id IS NOT NULL";
        $result = $this->db->query($sql);
        $data['total_users'] = $result[0]['total_users'] ?? 0;
        
        // Online users
        $sql = "SELECT COUNT(*) as online_users 
                FROM online_users 
                WHERE is_active = TRUE AND last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
        $result = $this->db->query($sql);
        $data['online_users'] = $result[0]['online_users'] ?? 0;
        
        // Active sessions
        $sql = "SELECT COUNT(*) as active_sessions 
                FROM user_sessions 
                WHERE $date_condition AND is_active = TRUE";
        $result = $this->db->query($sql);
        $data['active_sessions'] = $result[0]['active_sessions'] ?? 0;
        
        // Average session duration
        $sql = "SELECT AVG(session_duration) as avg_duration 
                FROM user_sessions 
                WHERE $date_condition AND session_duration > 0";
        $result = $this->db->query($sql);
        $data['avg_session_duration'] = round($result[0]['avg_duration'] ?? 0);
        
        return $data;
    }
    
    /**
     * Get geographic statistics
     */
    public function getGeographicStats($period = 'today') {
        $date_condition = $this->getDateCondition($period);
        
        $sql = "SELECT country, city, COUNT(*) as visitors_count,
                       SUM(page_views_count) as page_views_count,
                       AVG(avg_session_duration) as avg_session_duration
                FROM geographic_statistics 
                WHERE $date_condition 
                GROUP BY country, city 
                ORDER BY visitors_count DESC 
                LIMIT 10";
        
        try {
            $result = $this->db->query($sql);
            return $result;
        } catch (Exception $e) {
            error_log("Geographic stats error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get device statistics
     */
    public function getDeviceStats($period = 'today') {
        $date_condition = $this->getDateCondition($period);
        
        $sql = "SELECT browser, operating_system, device_type,
                       SUM(visitors_count) as visitors_count,
                       SUM(page_views_count) as page_views_count,
                       AVG(avg_session_duration) as avg_session_duration
                FROM browser_os_statistics 
                WHERE $date_condition 
                GROUP BY browser, operating_system, device_type 
                ORDER BY visitors_count DESC";
        
        try {
            $result = $this->db->query($sql);
            return $result;
        } catch (Exception $e) {
            error_log("Device stats error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get online users list
     */
    public function getOnlineUsers() {
        $sql = "SELECT ou.*, u.user as username, p.nom, p.prenom
                FROM online_users ou
                LEFT JOIN users u ON ou.user_id = u.id
                LEFT JOIN profiles p ON u.id = p.user_id
                WHERE ou.is_active = TRUE 
                AND ou.last_activity >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                ORDER BY ou.last_activity DESC
                LIMIT 20";
        
        try {
            $result = $this->db->query($sql);
            return $result;
        } catch (Exception $e) {
            error_log("Online users error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get date condition for queries
     */
    private function getDateCondition($period) {
        switch ($period) {
            case 'today':
                return "date = CURDATE()";
            case 'week':
                return "date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            case 'month':
                return "date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            case 'year':
                return "date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
            default:
                return "date = CURDATE()";
        }
    }
}

// Auto-initialize tracker if included (with error handling)
if (isset($bd) && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/') !== false) {
    try {
        $user_tracker = new UserTracker($bd);
        
        // Track current page view
        $user_tracker->trackPageView(
            $_SERVER['REQUEST_URI'] ?? '/',
            $page_title ?? 'EMPLOIDB',
            $page_category ?? 'general'
        );
        
        // Update online status
        $user_tracker->updateOnlineStatus($_SERVER['REQUEST_URI'] ?? '');
    } catch (Exception $e) {
        // Log error but don't break the page
        error_log("UserTracker error: " . $e->getMessage());
    }
}
?>
