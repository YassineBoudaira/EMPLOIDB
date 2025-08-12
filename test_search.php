<?php
// Test file to verify search functionality
include 'include/sess.php';
include 'include/connexion.php';

echo "<h2>Search Functionality Test</h2>";

// Test 1: Check if database connection works
echo "<h3>Test 1: Database Connection</h3>";
try {
    $test = $db->fetch("SELECT COUNT(*) as count FROM annonces");
    echo "✅ Database connection: OK<br>";
    echo "Total announcements: " . $test['count'] . "<br><br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br><br>";
}

// Test 2: Check if domaines table has data
echo "<h3>Test 2: Domaines Table</h3>";
try {
    $domaines = $db->fetchAll("SELECT * FROM domaines LIMIT 5");
    echo "✅ Domaines table: OK<br>";
    echo "Sample domaines:<br>";
    foreach ($domaines as $domaine) {
        echo "- " . htmlspecialchars($domaine['nom']) . " (ID: " . $domaine['id'] . ")<br>";
    }
    echo "<br>";
} catch (Exception $e) {
    echo "❌ Domaines table failed: " . $e->getMessage() . "<br><br>";
}

// Test 3: Check if villes table has data
echo "<h3>Test 3: Villes Table</h3>";
try {
    $villes = $db->fetchAll("SELECT * FROM villes LIMIT 5");
    echo "✅ Villes table: OK<br>";
    echo "Sample villes:<br>";
    foreach ($villes as $ville) {
        echo "- " . htmlspecialchars($ville['nom']) . " (ID: " . $ville['id'] . ")<br>";
    }
    echo "<br>";
} catch (Exception $e) {
    echo "❌ Villes table failed: " . $e->getMessage() . "<br><br>";
}

// Test 4: Test search query
echo "<h3>Test 4: Search Query Test</h3>";
try {
    // Test search with keyword
    $keyword = "développeur";
    $sql = "SELECT * FROM annonces WHERE (titre LIKE ? OR description LIKE ? OR entreprise LIKE ?) LIMIT 3";
    $params = ["%$keyword%", "%$keyword%", "%$keyword%"];
    
    $results = $db->fetchAll($sql, $params);
    echo "✅ Search query with keyword '$keyword': OK<br>";
    echo "Found " . count($results) . " results<br>";
    
    if (!empty($results)) {
        echo "Sample results:<br>";
        foreach ($results as $result) {
            echo "- " . htmlspecialchars($result['titre']) . "<br>";
        }
    }
    echo "<br>";
} catch (Exception $e) {
    echo "❌ Search query failed: " . $e->getMessage() . "<br><br>";
}

// Test 5: Test Security class
echo "<h3>Test 5: Security Class</h3>";
try {
    $test_input = "test<script>alert('xss')</script>";
    $sanitized = Security::sanitizeInput($test_input, 'string');
    echo "✅ Security::sanitizeInput: OK<br>";
    echo "Original: " . htmlspecialchars($test_input) . "<br>";
    echo "Sanitized: " . htmlspecialchars($sanitized) . "<br><br>";
} catch (Exception $e) {
    echo "❌ Security class failed: " . $e->getMessage() . "<br><br>";
}

echo "<h3>✅ Search functionality test completed!</h3>";
echo "<p><a href='index.php'>Return to homepage</a></p>";
?>
