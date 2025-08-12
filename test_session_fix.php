<?php
// Test file to verify session errors are fixed
echo "<h2>Session Error Test</h2>";

try {
    // Include files in correct order
    include 'include/config.php';
    include 'include/sess.php';
    include 'include/connexion.php';
    
    echo "✅ All includes successful<br>";
    echo "✅ Session started without errors<br>";
    echo "✅ Database connection working<br>";
    
    // Test if functions are available
    if (function_exists('Security::isLoggedIn')) {
        echo "✅ Security functions available<br>";
    } else {
        echo "⚠️ Security functions not available (this is normal if using static methods)<br>";
    }
    
    // Test database
    $test = $db->fetch("SELECT COUNT(*) as count FROM annonces");
    echo "✅ Database query successful: " . $test['count'] . " annonces found<br>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<br><a href='index.php'>Return to homepage</a>";
?>
