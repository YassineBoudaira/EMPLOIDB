<?php
// Test file to diagnose blank page issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Test 1: Basic PHP output works<br>";

// Test includes
echo "Test 2: Testing includes...<br>";

try {
    include __DIR__ . '/../include/config.php';
    echo "✓ Config included successfully<br>";
} catch (Exception $e) {
    echo "✗ Config include failed: " . $e->getMessage() . "<br>";
}

try {
    include __DIR__ . '/../include/sess.php';
    echo "✓ Session included successfully<br>";
} catch (Exception $e) {
    echo "✗ Session include failed: " . $e->getMessage() . "<br>";
}

try {
    include __DIR__ . '/../include/connexion.php';
    echo "✓ Database connection included successfully<br>";
} catch (Exception $e) {
    echo "✗ Database connection include failed: " . $e->getMessage() . "<br>";
}

// Test session
echo "Test 3: Testing session...<br>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✓ Session is active<br>";
    echo "Session ID: " . session_id() . "<br>";
    echo "Session data: " . print_r($_SESSION, true) . "<br>";
} else {
    echo "✗ Session is not active<br>";
}

// Test database
echo "Test 4: Testing database...<br>";
if (isset($db)) {
    echo "✓ Database object exists<br>";
    try {
        $result = $db->fetch("SELECT 1 as test");
        echo "✓ Database query works: " . $result['test'] . "<br>";
    } catch (Exception $e) {
        echo "✗ Database query failed: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ Database object not found<br>";
}

// Test Security class
echo "Test 5: Testing Security class...<br>";
if (class_exists('Security')) {
    echo "✓ Security class exists<br>";
    try {
        $isLoggedIn = Security::isLoggedIn();
        echo "✓ Security::isLoggedIn() works: " . ($isLoggedIn ? 'true' : 'false') . "<br>";
    } catch (Exception $e) {
        echo "✗ Security::isLoggedIn() failed: " . $e->getMessage() . "<br>";
    }
} else {
    echo "✗ Security class not found<br>";
}

echo "Test 6: Testing output buffering...<br>";
if (ob_get_level() > 0) {
    echo "✓ Output buffering is active (level: " . ob_get_level() . ")<br>";
} else {
    echo "✓ No output buffering<br>";
}

echo "Test 7: Testing memory usage...<br>";
echo "Memory usage: " . memory_get_usage(true) / 1024 / 1024 . " MB<br>";
echo "Memory peak: " . memory_get_peak_usage(true) / 1024 / 1024 . " MB<br>";

echo "Test 8: Testing fatal errors...<br>";
// This should not cause a blank page if error reporting is working
try {
    $undefined_variable = $this_variable_does_not_exist;
    echo "This should not appear<br>";
} catch (Error $e) {
    echo "✓ Caught error: " . $e->getMessage() . "<br>";
}

echo "All tests completed successfully!<br>";
?>

