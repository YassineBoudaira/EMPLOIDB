<?php
/**
 * Enterprise Login Security System
 * Handles authentication, rate limiting, session management, and security monitoring
 */
class LoginSecurity {
    private $db;
    private $maxAttempts;
    private $blockDuration;
    private $sessionTimeout;

    public function __construct($database) {
        $this->db = $database;
        $this->loadSecuritySettings();
    }

    /**
     * Load security settings from database
     */
    private function loadSecuritySettings() {
        try {
            $settings = $this->db->fetchAll("SELECT setting_name, setting_value FROM security_settings");
            foreach ($settings as $setting) {
                switch ($setting['setting_name']) {
                    case 'max_login_attempts':
                        $this->maxAttempts = (int)$setting['setting_value'];
                        break;
                    case 'block_duration_minutes':
                        $this->blockDuration = (int)$setting['setting_value'];
                        break;
                    case 'session_timeout_minutes':
                        $this->sessionTimeout = (int)$setting['setting_value'];
                        break;
                }
            }
        } catch (Exception $e) {
            // Use defaults if database settings not available
            $this->maxAttempts = 5;
            $this->blockDuration = 5; // Changed to 5 minutes for first block
            $this->sessionTimeout = 120;
        }
    }

    /**
     * Check if IP is blocked
     */
    public function isIpBlocked($ip) {
        try {
            // First check if IP is in admin whitelist
            $whitelisted = $this->db->fetch("
                SELECT * FROM admin_ip_whitelist 
                WHERE ip_address = ? AND is_active = TRUE
            ", [$ip]);
            
            if ($whitelisted) {
                return false; // Admin IPs are never blocked
            }
            
            $block = $this->db->fetch("
                SELECT * FROM ip_blocklist 
                WHERE ip_address = ? 
                AND (blocked_until IS NULL OR blocked_until > NOW())
            ", [$ip]);
            
            return $block !== null;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Record failed login attempt with enhanced blocking logic
     */
    public function recordFailedAttempt($email, $ip, $userAgent, $reason = 'Invalid credentials') {
        try {
            // Record in login_audit
            $this->db->insert("
                INSERT INTO login_audit (email, ip_address, user_agent, login_status, failure_reason) 
                VALUES (?, ?, ?, 'failed', ?)
            ", [$email, $ip, $userAgent, $reason]);

            // Check if IP is in admin whitelist - don't block admin IPs
            $whitelisted = $this->db->fetch("
                SELECT * FROM admin_ip_whitelist 
                WHERE ip_address = ? AND is_active = TRUE
            ", [$ip]);
            
            if ($whitelisted) {
                // Admin IP - just log the attempt but don't block
                error_log("Admin IP {$ip} failed login attempt - not blocking due to whitelist");
                return;
            }

            // Check if user exists in database
            $userExists = $this->db->fetch("SELECT id, role FROM users WHERE email = ?", [$email]);

            // Update or create IP blocklist entry for non-admin IPs
            $existing = $this->db->fetch("SELECT * FROM ip_blocklist WHERE ip_address = ?", [$ip]);
            
            if ($existing) {
                $newAttempts = $existing['failed_attempts'] + 1;
                $blockUntil = null;
                $blockDuration = $this->blockDuration; // Default 5 minutes
                
                // Enhanced blocking logic
                if ($newAttempts >= $this->maxAttempts) {
                    // First block: 5 minutes
                    if ($newAttempts == $this->maxAttempts) {
                        $blockDuration = 5; // 5 minutes for first block
                    }
                    // Second block: 1 hour (if user exists and continues trying)
                    elseif ($newAttempts > $this->maxAttempts && $userExists) {
                        $blockDuration = 60; // 1 hour for repeat offenders
                    }
                    // For non-existing users, keep 5 minutes
                    else {
                        $blockDuration = 5;
                    }
                    
                    $blockUntil = date('Y-m-d H:i:s', strtotime("+{$blockDuration} minutes"));
                    
                    $this->createSecurityAlert('ip_blocked', 'high', 
                        'IP Address Blocked', 
                        "IP {$ip} blocked for {$blockDuration} minutes due to {$newAttempts} failed login attempts", 
                        $ip);
                }
                
                $this->db->update("
                    UPDATE ip_blocklist 
                    SET failed_attempts = ?, last_attempt = NOW(), blocked_until = ?
                    WHERE ip_address = ?
                ", [$newAttempts, $blockUntil, $ip]);
            } else {
                $this->db->insert("
                    INSERT INTO ip_blocklist (ip_address, failed_attempts, first_attempt, last_attempt) 
                    VALUES (?, 1, NOW(), NOW())
                ", [$ip]);
            }
        } catch (Exception $e) {
            error_log("LoginSecurity::recordFailedAttempt error: " . $e->getMessage());
        }
    }

    /**
     * Record successful login
     */
    public function recordSuccessfulLogin($userId, $email, $ip, $userAgent, $sessionId) {
        try {
            // Record in login_audit
            $this->db->insert("
                INSERT INTO login_audit (user_id, email, ip_address, user_agent, login_status, session_id) 
                VALUES (?, ?, ?, ?, 'success', ?)
            ", [$userId, $email, $ip, $userAgent, $sessionId]);

            // Update user's last_login
            $this->db->update("UPDATE users SET last_login = NOW() WHERE id = ?", [$userId]);

            // Create or update user session
            $this->db->insert("
                INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                last_activity = NOW(), is_active = TRUE, logout_time = NULL
            ", [$userId, $sessionId, $ip, $userAgent]);

            // Clear IP blocklist if exists
            $this->db->delete("DELETE FROM ip_blocklist WHERE ip_address = ?", [$ip]);

        } catch (Exception $e) {
            error_log("LoginSecurity::recordSuccessfulLogin error: " . $e->getMessage());
        }
    }

    /**
     * Authenticate user with enhanced security
     */
    public function authenticate($email, $password, $ip, $userAgent) {
        // Check if IP is blocked
        if ($this->isIpBlocked($ip)) {
            $this->createSecurityAlert('blocked_ip_attempt', 'medium',
                'Blocked IP Login Attempt',
                "Blocked IP {$ip} attempted to login with email {$email}",
                $ip);
            throw new Exception('Votre adresse IP est temporairement bloquée. Veuillez réessayer plus tard.');
        }

        // Get user from database
        $user = $this->db->fetch("
            SELECT id, user, pass, email, role, account_status 
            FROM users 
            WHERE email = ?
        ", [$email]);

        if (!$user) {
            $this->recordFailedAttempt($email, $ip, $userAgent, 'User not found');
            // Get appropriate redirect URL for user not found
            $redirectUrl = $this->getRedirectUrl($email, 'User not found');
            throw new Exception('Email ou mot de passe incorrect|' . $redirectUrl);
        }

        // Check account status
        if ($user['account_status'] === 'suspended') {
            $this->recordFailedAttempt($email, $ip, $userAgent, 'Account suspended');
            throw new Exception('Votre compte a été suspendu. Contactez l\'administrateur.');
        }

        if ($user['account_status'] === 'pending') {
            $this->recordFailedAttempt($email, $ip, $userAgent, 'Account pending approval');
            throw new Exception('Votre compte est en attente d\'approbation.');
        }

        // Verify password
        if (!password_verify($password, $user['pass'])) {
            $this->recordFailedAttempt($email, $ip, $userAgent, 'Invalid password');
            // Get appropriate redirect URL for wrong password
            $redirectUrl = $this->getRedirectUrl($email, 'Invalid password');
            throw new Exception('Email ou mot de passe incorrect|' . $redirectUrl);
        }

        // Successful authentication
        $sessionId = session_id();
        $this->recordSuccessfulLogin($user['id'], $email, $ip, $userAgent, $sessionId);

        return $user;
    }

    /**
     * Create security alert
     */
    public function createSecurityAlert($type, $severity, $title, $message, $ip = null, $userId = null, $metadata = null) {
        try {
            $this->db->insert("
                INSERT INTO security_alerts (alert_type, severity, title, message, ip_address, user_id, metadata) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ", [$type, $severity, $title, $message, $ip, $userId, $metadata]);
        } catch (Exception $e) {
            error_log("LoginSecurity::createSecurityAlert error: " . $e->getMessage());
        }
    }

    /**
     * Get login statistics for monitoring
     */
    public function getLoginStats($days = 7) {
        try {
            $stats = [];
            
            // Total login attempts
            $totalAttempts = $this->db->fetch("
                SELECT COUNT(*) as count 
                FROM login_audit 
                WHERE login_time >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ", [$days]);
            $stats['total_attempts'] = $totalAttempts['count'];

            // Successful logins
            $successfulLogins = $this->db->fetch("
                SELECT COUNT(*) as count 
                FROM login_audit 
                WHERE login_status = 'success' 
                AND login_time >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ", [$days]);
            $stats['successful_logins'] = $successfulLogins['count'];

            // Failed logins
            $failedLogins = $this->db->fetch("
                SELECT COUNT(*) as count 
                FROM login_audit 
                WHERE login_status = 'failed' 
                AND login_time >= DATE_SUB(NOW(), INTERVAL ? DAY)
            ", [$days]);
            $stats['failed_logins'] = $failedLogins['count'];

            // Blocked IPs
            $blockedIps = $this->db->fetch("
                SELECT COUNT(*) as count 
                FROM ip_blocklist 
                WHERE blocked_until IS NOT NULL 
                AND blocked_until > NOW()
            ");
            $stats['blocked_ips'] = $blockedIps['count'];

            // Active sessions
            $activeSessions = $this->db->fetch("
                SELECT COUNT(*) as count 
                FROM user_sessions 
                WHERE is_active = TRUE 
                AND last_activity >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
            ", [$this->sessionTimeout]);
            $stats['active_sessions'] = $activeSessions['count'];

            return $stats;
        } catch (Exception $e) {
            error_log("LoginSecurity::getLoginStats error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent login attempts
     */
    public function getRecentLoginAttempts($limit = 50) {
        try {
            return $this->db->fetchAll("
                SELECT la.*, u.user as username 
                FROM login_audit la 
                LEFT JOIN users u ON la.user_id = u.id 
                ORDER BY la.login_time DESC 
                LIMIT ?
            ", [$limit]);
        } catch (Exception $e) {
            error_log("LoginSecurity::getRecentLoginAttempts error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clean up old sessions
     */
    public function cleanupOldSessions() {
        try {
            $this->db->update("
                UPDATE user_sessions 
                SET is_active = FALSE, logout_time = NOW() 
                WHERE is_active = TRUE 
                AND last_activity < DATE_SUB(NOW(), INTERVAL ? MINUTE)
            ", [$this->sessionTimeout]);

            // Clean up old audit logs (keep 90 days)
            $this->db->delete("
                DELETE FROM login_audit 
                WHERE login_time < DATE_SUB(NOW(), INTERVAL 90 DAY)
            ");

            // Clean up expired IP blocks
            $this->db->delete("
                DELETE FROM ip_blocklist 
                WHERE blocked_until IS NOT NULL 
                AND blocked_until < NOW() 
                AND is_permanent = FALSE
            ");

        } catch (Exception $e) {
            error_log("LoginSecurity::cleanupOldSessions error: " . $e->getMessage());
        }
    }

    /**
     * Get user's login history
     */
    public function getUserLoginHistory($userId, $limit = 20) {
        try {
            return $this->db->fetchAll("
                SELECT * FROM login_audit 
                WHERE user_id = ? 
                ORDER BY login_time DESC 
                LIMIT ?
            ", [$userId, $limit]);
        } catch (Exception $e) {
            error_log("LoginSecurity::getUserLoginHistory error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get appropriate redirect URL based on failure reason and user status
     */
    public function getRedirectUrl($email, $reason) {
        // Check if user exists
        $user = $this->db->fetch("SELECT id, role FROM users WHERE email = ?", [$email]);
        
        if (!$user) {
            // User doesn't exist - redirect to registration selection
            return 'registration/select.php?message=account_not_found&type=info';
        }
        
        // User exists but wrong password
        if ($reason === 'Invalid password' || $reason === 'Invalid credentials') {
            return 'forgot_password.php?email=' . urlencode($email) . '&message=wrong_password&type=warning';
        }
        
        // Account suspended
        if ($reason === 'Account suspended') {
            return 'contact_admin.php?message=account_suspended&type=error';
        }
        
        // Account pending
        if ($reason === 'Account pending approval') {
            return 'registration/select.php?message=account_pending&type=warning';
        }
        
        // Default fallback
        return 'registration/select.php?message=login_failed&type=error';
    }

    /**
     * Logout user and clean up session
     */
    public function logout($userId, $sessionId) {
        try {
            // Update session as inactive
            $this->db->update("
                UPDATE user_sessions 
                SET is_active = FALSE, logout_time = NOW() 
                WHERE user_id = ? AND session_id = ?
            ", [$userId, $sessionId]);

            // Destroy session
            session_destroy();
        } catch (Exception $e) {
            error_log("LoginSecurity::logout error: " . $e->getMessage());
        }
    }
}
?>
