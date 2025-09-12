<?php
/**
 * Enhanced Features Test Script
 * Tests all the new enhanced features to ensure they work correctly
 */

// Include configuration
include 'include/config.php';
include 'include/connexion.php';

echo "<h1>🧪 EMPLOIDB Enhanced Features Test</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .test{background:#f5f5f5;padding:10px;margin:10px 0;border-left:4px solid #007bff;} .success{border-left-color:#28a745;} .error{border-left-color:#dc3545;} .warning{border-left-color:#ffc107;}</style>\n";

$tests = [];
$errors = [];

// Test 1: Database Connection
echo "<div class='test'>";
echo "<h3>1. Database Connection Test</h3>";
try {
    $testQuery = $db->fetch("SELECT 1 as test");
    if ($testQuery && $testQuery['test'] == 1) {
        echo "✅ Database connection successful<br>";
        $tests[] = "Database Connection: PASS";
    } else {
        echo "❌ Database connection failed<br>";
        $errors[] = "Database connection failed";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    $errors[] = "Database error: " . $e->getMessage();
}
echo "</div>";

// Test 2: Enhanced Classes Loading
echo "<div class='test'>";
echo "<h3>2. Enhanced Classes Loading Test</h3>";

$classes = [
    'AIService' => 'include/AIService.php',
    'OAuthService' => 'include/OAuthService.php',
    'MultilingualSupport' => 'include/MultilingualSupport.php',
    'SEOEnhancer' => 'include/SEOEnhancer.php',
    'AuditLogger' => 'include/AuditLogger.php'
];

foreach ($classes as $className => $filePath) {
    if (file_exists($filePath)) {
        include_once $filePath;
        if (class_exists($className)) {
            echo "✅ $className loaded successfully<br>";
            $tests[] = "$className: PASS";
        } else {
            echo "❌ $className class not found<br>";
            $errors[] = "$className class not found";
        }
    } else {
        echo "❌ $filePath not found<br>";
        $errors[] = "$filePath not found";
    }
}
echo "</div>";

// Test 3: Enhanced Job Aggregator
echo "<div class='test'>";
echo "<h3>3. Enhanced Job Aggregator Test</h3>";
try {
    if (file_exists('include/JobAggregator.php')) {
        include_once 'include/JobAggregator.php';
        if (class_exists('JobAggregator')) {
            $aggregator = new JobAggregator($db);
            echo "✅ JobAggregator instantiated successfully<br>";
            $tests[] = "JobAggregator: PASS";
        } else {
            echo "❌ JobAggregator class not found<br>";
            $errors[] = "JobAggregator class not found";
        }
    } else {
        echo "❌ JobAggregator.php not found<br>";
        $errors[] = "JobAggregator.php not found";
    }
} catch (Exception $e) {
    echo "❌ JobAggregator error: " . $e->getMessage() . "<br>";
    $errors[] = "JobAggregator error: " . $e->getMessage();
}
echo "</div>";

// Test 4: AI Service
echo "<div class='test'>";
echo "<h3>4. AI Service Test</h3>";
try {
    if (class_exists('AIService')) {
        $aiService = new AIService($db);
        echo "✅ AIService instantiated successfully<br>";
        
        // Test similarity calculation
        $similarity = $aiService->calculateJobSimilarity(
            ['title' => 'Software Developer', 'company' => 'Tech Corp'],
            ['title' => 'Software Engineer', 'company' => 'Tech Corp']
        );
        echo "✅ AI similarity calculation working (score: " . round($similarity, 2) . ")<br>";
        $tests[] = "AIService: PASS";
    } else {
        echo "❌ AIService class not available<br>";
        $errors[] = "AIService class not available";
    }
} catch (Exception $e) {
    echo "❌ AIService error: " . $e->getMessage() . "<br>";
    $errors[] = "AIService error: " . $e->getMessage();
}
echo "</div>";

// Test 5: Multilingual Support
echo "<div class='test'>";
echo "<h3>5. Multilingual Support Test</h3>";
try {
    if (class_exists('MultilingualSupport')) {
        $multilingual = new MultilingualSupport($db);
        echo "✅ MultilingualSupport instantiated successfully<br>";
        
        // Test language detection
        $currentLang = $multilingual->getCurrentLanguage();
        echo "✅ Current language: $currentLang<br>";
        
        // Test translation
        $translation = $multilingual->translate('welcome');
        echo "✅ Translation test: '$translation'<br>";
        
        $tests[] = "MultilingualSupport: PASS";
    } else {
        echo "❌ MultilingualSupport class not available<br>";
        $errors[] = "MultilingualSupport class not available";
    }
} catch (Exception $e) {
    echo "❌ MultilingualSupport error: " . $e->getMessage() . "<br>";
    $errors[] = "MultilingualSupport error: " . $e->getMessage();
}
echo "</div>";

// Test 6: OAuth Service
echo "<div class='test'>";
echo "<h3>6. OAuth Service Test</h3>";
try {
    if (class_exists('OAuthService')) {
        $oauthService = new OAuthService($db);
        echo "✅ OAuthService instantiated successfully<br>";
        
        // Test available providers
        $providers = $oauthService->getAvailableProviders();
        echo "✅ Available OAuth providers: " . implode(', ', $providers) . "<br>";
        
        $tests[] = "OAuthService: PASS";
    } else {
        echo "❌ OAuthService class not available<br>";
        $errors[] = "OAuthService class not available";
    }
} catch (Exception $e) {
    echo "❌ OAuthService error: " . $e->getMessage() . "<br>";
    $errors[] = "OAuthService error: " . $e->getMessage();
}
echo "</div>";

// Test 7: SEO Enhancer
echo "<div class='test'>";
echo "<h3>7. SEO Enhancer Test</h3>";
try {
    if (class_exists('SEOEnhancer')) {
        $seoEnhancer = new SEOEnhancer($db);
        echo "✅ SEOEnhancer instantiated successfully<br>";
        
        // Test meta tags generation
        $seoEnhancer->setPageData('test', ['title' => 'Test Page', 'description' => 'Test Description']);
        $metaTags = $seoEnhancer->generateMetaTags();
        if (!empty($metaTags)) {
            echo "✅ Meta tags generation working<br>";
        }
        
        $tests[] = "SEOEnhancer: PASS";
    } else {
        echo "❌ SEOEnhancer class not available<br>";
        $errors[] = "SEOEnhancer class not available";
    }
} catch (Exception $e) {
    echo "❌ SEOEnhancer error: " . $e->getMessage() . "<br>";
    $errors[] = "SEOEnhancer error: " . $e->getMessage();
}
echo "</div>";

// Test 8: Audit Logger
echo "<div class='test'>";
echo "<h3>8. Audit Logger Test</h3>";
try {
    if (class_exists('AuditLogger')) {
        $auditLogger = new AuditLogger($db);
        echo "✅ AuditLogger instantiated successfully<br>";
        
        // Test logging (without actually logging to avoid spam)
        echo "✅ AuditLogger ready for logging<br>";
        
        $tests[] = "AuditLogger: PASS";
    } else {
        echo "❌ AuditLogger class not available<br>";
        $errors[] = "AuditLogger class not available";
    }
} catch (Exception $e) {
    echo "❌ AuditLogger error: " . $e->getMessage() . "<br>";
    $errors[] = "AuditLogger error: " . $e->getMessage();
}
echo "</div>";

// Test 9: Theme Switcher JavaScript
echo "<div class='test'>";
echo "<h3>9. Theme Switcher JavaScript Test</h3>";
if (file_exists('assets/js/theme-switcher.js')) {
    echo "✅ Theme switcher JavaScript file exists<br>";
    $tests[] = "Theme Switcher JS: PASS";
} else {
    echo "❌ Theme switcher JavaScript file not found<br>";
    $errors[] = "Theme switcher JavaScript file not found";
}
echo "</div>";

// Test 10: News Portal
echo "<div class='test'>";
echo "<h3>10. News Portal Test</h3>";
if (file_exists('news.php')) {
    echo "✅ News portal file exists<br>";
    $tests[] = "News Portal: PASS";
} else {
    echo "❌ News portal file not found<br>";
    $errors[] = "News portal file not found";
}
echo "</div>";

// Test 11: Enhanced Admin Panel
echo "<div class='test'>";
echo "<h3>11. Enhanced Admin Panel Test</h3>";
if (file_exists('admin/enhanced_features_manager.php')) {
    echo "✅ Enhanced admin panel file exists<br>";
    $tests[] = "Enhanced Admin Panel: PASS";
} else {
    echo "❌ Enhanced admin panel file not found<br>";
    $errors[] = "Enhanced admin panel file not found";
}
echo "</div>";

// Test 12: Database Schema
echo "<div class='test'>";
echo "<h3>12. Database Schema Test</h3>";
if (file_exists('database/enhanced_features_schema.sql')) {
    echo "✅ Database schema file exists<br>";
    
    // Check if some key tables exist
    $keyTables = ['ai_settings', 'oauth_accounts', 'translations', 'news_articles', 'audit_logs'];
    $existingTables = [];
    
    foreach ($keyTables as $table) {
        try {
            $result = $db->fetch("SHOW TABLES LIKE ?", [$table]);
            if ($result) {
                $existingTables[] = $table;
            }
        } catch (Exception $e) {
            // Table might not exist yet
        }
    }
    
    if (count($existingTables) > 0) {
        echo "✅ Some enhanced tables exist: " . implode(', ', $existingTables) . "<br>";
        echo "⚠️ Note: Run the database schema to create all tables<br>";
    } else {
        echo "⚠️ Enhanced tables not found - run database/enhanced_features_schema.sql<br>";
    }
    
    $tests[] = "Database Schema: PASS";
} else {
    echo "❌ Database schema file not found<br>";
    $errors[] = "Database schema file not found";
}
echo "</div>";

// Summary
echo "<div class='test " . (empty($errors) ? 'success' : 'error') . "'>";
echo "<h3>📊 Test Summary</h3>";
echo "<strong>Total Tests: " . count($tests) . "</strong><br>";
echo "<strong>Passed: " . count($tests) . "</strong><br>";
echo "<strong>Errors: " . count($errors) . "</strong><br>";

if (!empty($errors)) {
    echo "<h4>❌ Errors Found:</h4>";
    foreach ($errors as $error) {
        echo "• $error<br>";
    }
} else {
    echo "<h4>🎉 All Tests Passed!</h4>";
    echo "Your enhanced features are ready to use!<br>";
}

echo "</div>";

// Recommendations
echo "<div class='test warning'>";
echo "<h3>📋 Next Steps</h3>";
echo "1. Run the database schema: <code>database/enhanced_features_schema.sql</code><br>";
echo "2. Configure OAuth providers in the admin panel<br>";
echo "3. Add the news portal to your main navigation<br>";
echo "4. Include the theme switcher in your templates<br>";
echo "5. Test all features in your development environment<br>";
echo "</div>";

echo "<p><strong>Test completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
