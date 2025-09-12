<?php
/**
 * Security Utility Class
 * Handles input validation, sanitization, and security functions
 */
class Security {
    
    /**
     * Sanitize and validate input
     */
    public static function sanitizeInput($input, $type = 'string') {
        if (empty($input)) {
            return null;
        }
        
        switch ($type) {
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'string':
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Validate email address
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate integer
     */
    public static function validateInt($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Hash password securely
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 5242880) {
        $result = ['valid' => false, 'error' => ''];
        
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $result['error'] = 'File upload error';
            return $result;
        }
        
        if ($file['size'] > $maxSize) {
            $result['error'] = 'File too large';
            return $result;
        }
        
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);
        
        if (!in_array($extension, $allowedTypes)) {
            $result['error'] = 'Invalid file type';
            return $result;
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        if (!isset($allowedMimes[$extension]) || $allowedMimes[$extension] !== $mimeType) {
            $result['error'] = 'Invalid MIME type';
            return $result;
        }
        
        $result['valid'] = true;
        return $result;
    }
    
    /**
     * Generate secure filename
     */
    public static function generateSecureFilename($originalName, $prefix = '') {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        return $prefix ? $prefix . $filename : $filename;
    }
    
    /**
     * Redirect with message
     */
    public static function redirect($url, $message = '', $type = 'success') {
        if ($message) {
            $_SESSION['message'] = $message;
            $_SESSION['message_type'] = $type;
        }
        header("Location: $url");
        exit();
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        return self::isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    /**
     * Require authentication
     */
    public static function requireAuth() {
        if (!self::isLoggedIn()) {
            self::redirect('login.php', 'Please login to access this page', 'error');
        }
    }
    
    /**
     * Require admin privileges
     */
    public static function requireAdmin() {
        self::requireAuth();
        if (!self::isAdmin()) {
            self::redirect('home.php', 'Access denied. Admin privileges required.', 'error');
        }
    }
}
?>
