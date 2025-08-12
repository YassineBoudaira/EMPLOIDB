<?php
// Session configuration - MUST be at the top before any session starts
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Error reporting (disable in production)
if (isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'emploi');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application settings
define('SITE_NAME', 'JobMaroc');
define('SITE_URL', 'http://localhost/emploiDB');
define('UPLOAD_PATH', 'upload/');
define('FILES_PATH', 'fichiers/');
define('MAX_FILE_SIZE', 10485760); // 10MB

// Security settings
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 3600); // 1 hour
define('CSRF_TOKEN_EXPIRY', 1800); // 30 minutes

// Allowed file types for uploads
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx']);

// Email settings
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');

// Logging
define('LOG_PATH', 'logs/');
define('LOG_LEVEL', 'ERROR'); // DEBUG, INFO, WARNING, ERROR

// Create logs directory if it doesn't exist
if (!is_dir(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $errorMessage = date('Y-m-d H:i:s') . " - Error [$errno]: $errstr in $errfile on line $errline\n";
    error_log($errorMessage, 3, LOG_PATH . 'error.log');
    
    if (ini_get('display_errors')) {
        echo "<div style='color: red; background: #ffe6e6; padding: 10px; margin: 10px; border: 1px solid #ff9999;'>";
        echo "<strong>Error:</strong> $errstr<br>";
        echo "<strong>File:</strong> $errfile<br>";
        echo "<strong>Line:</strong> $errline";
        echo "</div>";
    }
    
    return true;
}

set_error_handler('customErrorHandler');

// Custom exception handler
function customExceptionHandler($exception) {
    $errorMessage = date('Y-m-d H:i:s') . " - Exception: " . $exception->getMessage() . 
                   " in " . $exception->getFile() . " on line " . $exception->getLine() . "\n";
    error_log($errorMessage, 3, LOG_PATH . 'exception.log');
    
    if (ini_get('display_errors')) {
        echo "<div style='color: red; background: #ffe6e6; padding: 10px; margin: 10px; border: 1px solid #ff9999;'>";
        echo "<strong>Exception:</strong> " . $exception->getMessage() . "<br>";
        echo "<strong>File:</strong> " . $exception->getFile() . "<br>";
        echo "<strong>Line:</strong> " . $exception->getLine();
        echo "</div>";
    }
}

set_exception_handler('customExceptionHandler');
?>




