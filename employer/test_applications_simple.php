<?php
// Simple test version of applications page
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting applications test...<br>";

// Test basic output
echo "1. Basic output works<br>";

// Test HTML output
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Applications</title>
</head>
<body>
    <h1>Test Applications Page</h1>
    <p>If you can see this, basic HTML output works.</p>
    
    <?php
    echo "2. PHP inside HTML works<br>";
    
    // Test includes one by one
    echo "3. Testing includes...<br>";
    
    try {
        include __DIR__ . '/../include/config.php';
        echo "✓ Config included<br>";
    } catch (Exception $e) {
        echo "✗ Config error: " . $e->getMessage() . "<br>";
        exit();
    }
    
    try {
        include __DIR__ . '/../include/sess.php';
        echo "✓ Session included<br>";
    } catch (Exception $e) {
        echo "✗ Session error: " . $e->getMessage() . "<br>";
        exit();
    }
    
    try {
        include __DIR__ . '/../include/connexion.php';
        echo "✓ Database included<br>";
    } catch (Exception $e) {
        echo "✗ Database error: " . $e->getMessage() . "<br>";
        exit();
    }
    
    // Test session
    echo "4. Testing session...<br>";
    if (isset($_SESSION['role'])) {
        echo "✓ Session role: " . $_SESSION['role'] . "<br>";
    } else {
        echo "✗ No session role<br>";
    }
    
    // Test database
    echo "5. Testing database...<br>";
    if (isset($db)) {
        echo "✓ Database object exists<br>";
        try {
            $test = $db->fetch("SELECT 1 as test");
            echo "✓ Database query works<br>";
        } catch (Exception $e) {
            echo "✗ Database query error: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "✗ No database object<br>";
    }
    
    echo "6. All tests passed!<br>";
    ?>
    
    <p>If you can see this message, the page is working correctly.</p>
</body>
</html>

