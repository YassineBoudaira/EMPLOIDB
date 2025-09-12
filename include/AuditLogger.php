<?php
/**
 * Audit Logger Class
 * Handles comprehensive audit logging for system activities
 */

class AuditLogger {
    private $db;
    private $config;
    
    public function __construct($database) {
        $this->db = $database;
        $this->loadConfig();
    }
    
    /**
     * Load audit configuration
     */
    private function loadConfig() {
        try {
            $settings = $this->db->fetchAll("SELECT setting_key, setting_value FROM system_settings WHERE category = 'audit'");
            $this->config = [];
            foreach ($settings as $setting) {
                $this->config[$setting['setting_key']] = $setting['setting_value'];
            }
        } catch (Exception $e) {
            error_log("Error loading audit config: " . $e->getMessage());
            $this->config = [];
        }
    }
    
    /**
     * Log user action
     */
    public function logUserAction($userId, $action, $resource = null, $details = null, $ipAddress = null) {
        try {
            $ipAddress = $ipAddress ?: $this->getClientIP();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $this->db->insert("
                INSERT INTO audit_logs (
                    user_id, action, resource, details, ip_address, user_agent, 
                    session_id, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ", [
                $userId,
                $action,
                $resource,
                json_encode($details),
                $ipAddress,
                $userAgent,
                session_id()
            ]);
            
        } catch (Exception $e) {
            error_log("Error logging user action: " . $e->getMessage());
        }
    }
    
    /**
     * Log system event
     */
    public function logSystemEvent($event, $level = 'info', $details = null) {
        try {
            $this->db->insert("
                INSERT INTO system_audit_logs (
                    event, level, details, created_at
                ) VALUES (?, ?, ?, NOW())
            ", [
                $event,
                $level,
                json_encode($details)
            ]);
            
        } catch (Exception $e) {
            error_log("Error logging system event: " . $e->getMessage());
        }
    }
    
    /**
     * Log data change
     */
    public function logDataChange($userId, $table, $recordId, $action, $oldData = null, $newData = null) {
        try {
            $this->db->insert("
                INSERT INTO data_change_logs (
                    user_id, table_name, record_id, action, old_data, new_data, 
                    ip_address, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ", [
                $userId,
                $table,
                $recordId,
                $action,
                json_encode($oldData),
                json_encode($newData),
                $this->getClientIP()
            ]);
            
        } catch (Exception $e) {
            error_log("Error logging data change: " . $e->getMessage());
        }
    }
    
    /**
     * Log security event
     */
    public function logSecurityEvent($event, $severity = 'medium', $details = null, $userId = null) {
        try {
            $this->db->insert("
                INSERT INTO security_audit_logs (
                    event, severity, details, user_id, ip_address, user_agent, 
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ", [
                $event,
                $severity,
                json_encode($details),
                $userId,
                $this->getClientIP(),
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
        } catch (Exception $e) {
            error_log("Error logging security event: " . $e->getMessage());
        }
    }
    
    /**
     * Log login attempt
     */
    public function logLoginAttempt($email, $success, $reason = null, $userId = null) {
        try {
            $this->db->insert("
                INSERT INTO login_audit_logs (
                    email, success, reason, user_id, ip_address, user_agent, 
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ", [
                $email,
                $success ? 1 : 0,
                $reason,
                $userId,
                $this->getClientIP(),
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
        } catch (Exception $e) {
            error_log("Error logging login attempt: " . $e->getMessage());
        }
    }
    
    /**
     * Log file access
     */
    public function logFileAccess($userId, $filePath, $action = 'read') {
        try {
            $this->db->insert("
                INSERT INTO file_access_logs (
                    user_id, file_path, action, ip_address, created_at
                ) VALUES (?, ?, ?, ?, NOW())
            ", [
                $userId,
                $filePath,
                $action,
                $this->getClientIP()
            ]);
            
        } catch (Exception $e) {
            error_log("Error logging file access: " . $e->getMessage());
        }
    }
    
    /**
     * Log API access
     */
    public function logAPIAccess($userId, $endpoint, $method, $responseCode, $responseTime = null) {
        try {
            $this->db->insert("
                INSERT INTO api_access_logs (
                    user_id, endpoint, method, response_code, response_time, 
                    ip_address, user_agent, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ", [
                $userId,
                $endpoint,
                $method,
                $responseCode,
                $responseTime,
                $this->getClientIP(),
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
        } catch (Exception $e) {
            error_log("Error logging API access: " . $e->getMessage());
        }
    }
    
    /**
     * Get audit logs
     */
    public function getAuditLogs($filters = [], $limit = 100, $offset = 0) {
        try {
            $where = ['1=1'];
            $params = [];
            
            if (isset($filters['user_id'])) {
                $where[] = 'user_id = ?';
                $params[] = $filters['user_id'];
            }
            
            if (isset($filters['action'])) {
                $where[] = 'action = ?';
                $params[] = $filters['action'];
            }
            
            if (isset($filters['date_from'])) {
                $where[] = 'created_at >= ?';
                $params[] = $filters['date_from'];
            }
            
            if (isset($filters['date_to'])) {
                $where[] = 'created_at <= ?';
                $params[] = $filters['date_to'];
            }
            
            $params[] = $limit;
            $params[] = $offset;
            
            return $this->db->fetchAll("
                SELECT al.*, u.user as username, u.email
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY al.created_at DESC
                LIMIT ? OFFSET ?
            ", $params);
            
        } catch (Exception $e) {
            error_log("Error getting audit logs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get security audit logs
     */
    public function getSecurityAuditLogs($filters = [], $limit = 100, $offset = 0) {
        try {
            $where = ['1=1'];
            $params = [];
            
            if (isset($filters['severity'])) {
                $where[] = 'severity = ?';
                $params[] = $filters['severity'];
            }
            
            if (isset($filters['event'])) {
                $where[] = 'event = ?';
                $params[] = $filters['event'];
            }
            
            if (isset($filters['date_from'])) {
                $where[] = 'created_at >= ?';
                $params[] = $filters['date_from'];
            }
            
            if (isset($filters['date_to'])) {
                $where[] = 'created_at <= ?';
                $params[] = $filters['date_to'];
            }
            
            $params[] = $limit;
            $params[] = $offset;
            
            return $this->db->fetchAll("
                SELECT sal.*, u.user as username, u.email
                FROM security_audit_logs sal
                LEFT JOIN users u ON sal.user_id = u.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY sal.created_at DESC
                LIMIT ? OFFSET ?
            ", $params);
            
        } catch (Exception $e) {
            error_log("Error getting security audit logs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get data change logs
     */
    public function getDataChangeLogs($filters = [], $limit = 100, $offset = 0) {
        try {
            $where = ['1=1'];
            $params = [];
            
            if (isset($filters['table_name'])) {
                $where[] = 'table_name = ?';
                $params[] = $filters['table_name'];
            }
            
            if (isset($filters['action'])) {
                $where[] = 'action = ?';
                $params[] = $filters['action'];
            }
            
            if (isset($filters['user_id'])) {
                $where[] = 'user_id = ?';
                $params[] = $filters['user_id'];
            }
            
            if (isset($filters['date_from'])) {
                $where[] = 'created_at >= ?';
                $params[] = $filters['date_from'];
            }
            
            if (isset($filters['date_to'])) {
                $where[] = 'created_at <= ?';
                $params[] = $filters['date_to'];
            }
            
            $params[] = $limit;
            $params[] = $offset;
            
            return $this->db->fetchAll("
                SELECT dcl.*, u.user as username, u.email
                FROM data_change_logs dcl
                LEFT JOIN users u ON dcl.user_id = u.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY dcl.created_at DESC
                LIMIT ? OFFSET ?
            ", $params);
            
        } catch (Exception $e) {
            error_log("Error getting data change logs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get audit statistics
     */
    public function getAuditStatistics($dateFrom = null, $dateTo = null) {
        try {
            $dateFrom = $dateFrom ?: date('Y-m-d', strtotime('-30 days'));
            $dateTo = $dateTo ?: date('Y-m-d');
            
            $stats = [];
            
            // User actions by type
            $stats['user_actions'] = $this->db->fetchAll("
                SELECT action, COUNT(*) as count
                FROM audit_logs
                WHERE created_at BETWEEN ? AND ?
                GROUP BY action
                ORDER BY count DESC
            ", [$dateFrom, $dateTo]);
            
            // Security events by severity
            $stats['security_events'] = $this->db->fetchAll("
                SELECT severity, COUNT(*) as count
                FROM security_audit_logs
                WHERE created_at BETWEEN ? AND ?
                GROUP BY severity
                ORDER BY count DESC
            ", [$dateFrom, $dateTo]);
            
            // Data changes by table
            $stats['data_changes'] = $this->db->fetchAll("
                SELECT table_name, action, COUNT(*) as count
                FROM data_change_logs
                WHERE created_at BETWEEN ? AND ?
                GROUP BY table_name, action
                ORDER BY count DESC
            ", [$dateFrom, $dateTo]);
            
            // Login attempts
            $stats['login_attempts'] = $this->db->fetch("
                SELECT 
                    COUNT(*) as total,
                    SUM(success) as successful,
                    COUNT(*) - SUM(success) as failed
                FROM login_audit_logs
                WHERE created_at BETWEEN ? AND ?
            ", [$dateFrom, $dateTo]);
            
            // Most active users
            $stats['active_users'] = $this->db->fetchAll("
                SELECT u.user, u.email, COUNT(al.id) as action_count
                FROM audit_logs al
                JOIN users u ON al.user_id = u.id
                WHERE al.created_at BETWEEN ? AND ?
                GROUP BY al.user_id, u.user, u.email
                ORDER BY action_count DESC
                LIMIT 10
            ", [$dateFrom, $dateTo]);
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error getting audit statistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Clean old audit logs
     */
    public function cleanOldLogs($daysToKeep = 90) {
        try {
            $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));
            
            // Clean audit logs
            $this->db->delete("
                DELETE FROM audit_logs WHERE created_at < ?
            ", [$cutoffDate]);
            
            // Clean security audit logs
            $this->db->delete("
                DELETE FROM security_audit_logs WHERE created_at < ?
            ", [$cutoffDate]);
            
            // Clean data change logs
            $this->db->delete("
                DELETE FROM data_change_logs WHERE created_at < ?
            ", [$cutoffDate]);
            
            // Clean login audit logs
            $this->db->delete("
                DELETE FROM login_audit_logs WHERE created_at < ?
            ", [$cutoffDate]);
            
            // Clean file access logs
            $this->db->delete("
                DELETE FROM file_access_logs WHERE created_at < ?
            ", [$cutoffDate]);
            
            // Clean API access logs
            $this->db->delete("
                DELETE FROM api_access_logs WHERE created_at < ?
            ", [$cutoffDate]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error cleaning old audit logs: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
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
     * Export audit logs
     */
    public function exportAuditLogs($filters = [], $format = 'csv') {
        try {
            $logs = $this->getAuditLogs($filters, 10000, 0);
            
            if ($format === 'csv') {
                return $this->exportToCSV($logs);
            } elseif ($format === 'json') {
                return json_encode($logs, JSON_PRETTY_PRINT);
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error exporting audit logs: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Export to CSV
     */
    private function exportToCSV($data) {
        if (empty($data)) {
            return '';
        }
        
        $output = fopen('php://temp', 'r+');
        
        // Write headers
        fputcsv($output, array_keys($data[0]));
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}
?>
