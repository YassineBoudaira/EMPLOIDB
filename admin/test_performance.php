<?php
// Simple test to check if the page loads
echo "Testing performance analytics page...<br>";

// Test includes
try {
    include __DIR__ . '/../include/config.php';
    echo "Config loaded successfully<br>";
} catch (Exception $e) {
    echo "Error loading config: " . $e->getMessage() . "<br>";
}

try {
    include __DIR__ . '/../include/sess.php';
    echo "Session loaded successfully<br>";
} catch (Exception $e) {
    echo "Error loading session: " . $e->getMessage() . "<br>";
}

try {
    include __DIR__ . '/../include/connexion.php';
    echo "Database connection loaded successfully<br>";
} catch (Exception $e) {
    echo "Error loading database: " . $e->getMessage() . "<br>";
}

// Test admin header
try {
    $page_title = 'Test Performance Analytics';
    include __DIR__ . '/includes/admin_header.php';
    echo "Admin header loaded successfully<br>";
} catch (Exception $e) {
    echo "Error loading admin header: " . $e->getMessage() . "<br>";
}

echo "Test completed!";
?>


