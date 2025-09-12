<?php
// Admin Debug - Standard Admin Design
$page_title = 'Admin Debug - EMPLOIDB';
include 'includes/admin_header.php';

// Check if user is admin
if (!Security::isAdmin()) {
    echo '<div class="enterprise-card">
        <div class="card-header bg-gradient-danger text-white">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>Access Denied</h4>
        </div>
        <div class="card-body text-center">
            <p class="mb-0">Admin privileges are required.</p>
        </div>
    </div>';
    include 'includes/admin_footer.php';
    exit;
}

// Debug script for admin pages
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if files exist
$admin_files = [
    'admin/dashboard.php',
    'admin/users.php', 
    'admin/employers.php',
    'admin/jobs.php',
    'admin/applications.php',
    'admin/reports.php',
    'admin/settings.php'
];

echo "<h3>File Existence Check:</h3>";
foreach ($admin_files as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "✅ $file (Size: " . number_format($size) . " bytes)<br>";
    } else {
        echo "❌ $file - MISSING<br>";
    }
}

// Test includes
echo "<h3>Include Path Test:</h3>";
try {
    include 'include/config.php';
    echo "✅ config.php loaded<br>";
} catch (Exception $e) {
    echo "❌ config.php error: " . $e->getMessage() . "<br>";
}

try {
    include 'include/sess.php';
    echo "✅ sess.php loaded<br>";
} catch (Exception $e) {
    echo "❌ sess.php error: " . $e->getMessage() . "<br>";
}

try {
    include 'include/connexion.php';
    echo "✅ connexion.php loaded<br>";
} catch (Exception $e) {
    echo "❌ connexion.php error: " . $e->getMessage() . "<br>";
}

// Test Security class
echo "<h3>Security Class Test:</h3>";
if (class_exists('Security')) {
    echo "✅ Security class exists<br>";
    
    // Test methods
    if (method_exists('Security', 'isLoggedIn')) {
        echo "✅ isLoggedIn method exists<br>";
    } else {
        echo "❌ isLoggedIn method missing<br>";
    }
    
    if (method_exists('Security', 'isAdmin')) {
        echo "✅ isAdmin method exists<br>";
    } else {
        echo "❌ isAdmin method missing<br>";
    }
} else {
    echo "❌ Security class not found<br>";
}

// Test database connection
echo "<h3>Database Test:</h3>";
if (isset($db)) {
    try {
        $result = $db->fetch("SELECT 1 as test");
        echo "✅ Database connection working<br>";
    } catch (Exception $e) {
        echo "❌ Database error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Database object not found<br>";
}

// Current session info
echo "<h3>Session Information:</h3>";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "ACTIVE" : "INACTIVE") . "<br>";
echo "Session ID: " . session_id() . "<br>";

if (!empty($_SESSION)) {
    echo "Session Data:<br>";
    foreach ($_SESSION as $key => $value) {
        if ($key !== 'csrf_token') {
            echo "- $key: " . htmlspecialchars($value) . "<br>";
        }
    }
} else {
    echo "No session data<br>";
}

// Test simple admin page loading
echo "<h3>Admin Page Loading Test:</h3>";
echo "<p>Try accessing these pages after logging in as admin:</p>";
foreach ($admin_files as $file) {
    if (file_exists($file)) {
        echo "<a href='$file' target='_blank'>$file</a><br>";
    }
}

echo "<hr>";
echo "<p><a href='../login.php'>Go to Login</a></p>";

include 'includes/admin_footer.php';

