<?php
/**
 * Enhanced Features Setup Script
 * Sets up the database tables and initial data for enhanced features
 */

// Include configuration
include 'include/config.php';
include 'include/connexion.php';

echo "<h1>🚀 EMPLOIDB Enhanced Features Setup</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .step{background:#f5f5f5;padding:15px;margin:15px 0;border-left:4px solid #007bff;} .success{border-left-color:#28a745;} .error{border-left-color:#dc3545;} .warning{border-left-color:#ffc107;}</style>\n";

$setupSteps = [];
$errors = [];

// Step 1: Check database connection
echo "<div class='step'>";
echo "<h3>Step 1: Database Connection Check</h3>";
try {
    $testQuery = $db->fetch("SELECT 1 as test");
    if ($testQuery && $testQuery['test'] == 1) {
        echo "✅ Database connection successful<br>";
        $setupSteps[] = "Database Connection: SUCCESS";
    } else {
        echo "❌ Database connection failed<br>";
        $errors[] = "Database connection failed";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
    $errors[] = "Database error: " . $e->getMessage();
}
echo "</div>";

if (!empty($errors)) {
    echo "<div class='step error'>";
    echo "<h3>❌ Setup Failed</h3>";
    echo "Cannot proceed with setup due to database connection issues.<br>";
    echo "Please check your database configuration and try again.";
    echo "</div>";
    exit;
}

// Step 2: Create enhanced features tables
echo "<div class='step'>";
echo "<h3>Step 2: Creating Enhanced Features Tables</h3>";

$tables = [
    'ai_settings' => "
        CREATE TABLE IF NOT EXISTS ai_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
    
    'duplicate_detection_logs' => "
        CREATE TABLE IF NOT EXISTS duplicate_detection_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            new_job_title VARCHAR(255) NOT NULL,
            existing_job_id INT,
            similarity_score DECIMAL(5,4) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_similarity_score (similarity_score),
            INDEX idx_created_at (created_at)
        )",
    
    'oauth_accounts' => "
        CREATE TABLE IF NOT EXISTS oauth_accounts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            provider ENUM('google', 'linkedin', 'microsoft', 'apple', 'x') NOT NULL,
            provider_id VARCHAR(255) NOT NULL,
            provider_data JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_provider_account (provider, provider_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_user_id (user_id),
            INDEX idx_provider (provider)
        )",
    
    'translations' => "
        CREATE TABLE IF NOT EXISTS translations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            language_code VARCHAR(5) NOT NULL,
            translation_key VARCHAR(255) NOT NULL,
            translation_value TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_translation (language_code, translation_key),
            INDEX idx_language_code (language_code),
            INDEX idx_translation_key (translation_key)
        )",
    
    'news_categories' => "
        CREATE TABLE IF NOT EXISTS news_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            language VARCHAR(5) DEFAULT 'fr',
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_slug (slug),
            INDEX idx_language (language),
            INDEX idx_status (status)
        )",
    
    'news_articles' => "
        CREATE TABLE IF NOT EXISTS news_articles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            excerpt TEXT,
            content LONGTEXT NOT NULL,
            featured_image VARCHAR(255),
            category_id INT,
            author_id INT NOT NULL,
            language VARCHAR(5) DEFAULT 'fr',
            status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
            featured BOOLEAN DEFAULT FALSE,
            meta_title VARCHAR(255),
            meta_description TEXT,
            meta_keywords TEXT,
            published_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES news_categories(id) ON DELETE SET NULL,
            FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_slug (slug),
            INDEX idx_status (status),
            INDEX idx_language (language),
            INDEX idx_featured (featured),
            INDEX idx_published_at (published_at),
            INDEX idx_category_id (category_id)
        )",
    
    'audit_logs' => "
        CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100) NOT NULL,
            resource VARCHAR(255),
            details JSON,
            ip_address VARCHAR(45),
            user_agent TEXT,
            session_id VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_user_id (user_id),
            INDEX idx_action (action),
            INDEX idx_created_at (created_at),
            INDEX idx_ip_address (ip_address)
        )"
];

$createdTables = 0;
foreach ($tables as $tableName => $sql) {
    try {
        $db->query($sql);
        echo "✅ Table '$tableName' created successfully<br>";
        $createdTables++;
    } catch (Exception $e) {
        echo "⚠️ Table '$tableName' creation failed: " . $e->getMessage() . "<br>";
    }
}

echo "Created $createdTables out of " . count($tables) . " tables<br>";
$setupSteps[] = "Database Tables: $createdTables/" . count($tables) . " created";
echo "</div>";

// Step 3: Insert default data
echo "<div class='step'>";
echo "<h3>Step 3: Inserting Default Data</h3>";

// Insert AI settings
$aiSettings = [
    ['similarity_threshold', '0.85', 'Threshold for job duplicate detection'],
    ['enable_ai_matching', '1', 'Enable AI-powered candidate matching'],
    ['enable_salary_prediction', '1', 'Enable AI salary prediction']
];

$insertedSettings = 0;
foreach ($aiSettings as $setting) {
    try {
        $db->insert("
            INSERT IGNORE INTO ai_settings (setting_key, setting_value, description) 
            VALUES (?, ?, ?)
        ", $setting);
        $insertedSettings++;
    } catch (Exception $e) {
        echo "⚠️ Failed to insert AI setting: " . $setting[0] . "<br>";
    }
}
echo "✅ Inserted $insertedSettings AI settings<br>";

// Insert default translations
$defaultTranslations = [
    ['fr', 'welcome', 'Bienvenue'],
    ['fr', 'login', 'Connexion'],
    ['fr', 'register', 'S\'inscrire'],
    ['fr', 'email', 'Email'],
    ['fr', 'password', 'Mot de passe'],
    ['fr', 'search', 'Rechercher'],
    ['fr', 'jobs', 'Emplois'],
    ['fr', 'companies', 'Entreprises'],
    ['fr', 'dashboard', 'Tableau de bord'],
    ['fr', 'profile', 'Profil'],
    ['fr', 'settings', 'Paramètres'],
    ['fr', 'logout', 'Déconnexion'],
    ['ar', 'welcome', 'مرحباً'],
    ['ar', 'login', 'تسجيل الدخول'],
    ['ar', 'register', 'إنشاء حساب'],
    ['ar', 'email', 'البريد الإلكتروني'],
    ['ar', 'password', 'كلمة المرور'],
    ['ar', 'search', 'بحث'],
    ['ar', 'jobs', 'الوظائف'],
    ['ar', 'companies', 'الشركات'],
    ['ar', 'dashboard', 'لوحة التحكم'],
    ['ar', 'profile', 'الملف الشخصي'],
    ['ar', 'settings', 'الإعدادات'],
    ['ar', 'logout', 'تسجيل الخروج'],
    ['en', 'welcome', 'Welcome'],
    ['en', 'login', 'Login'],
    ['en', 'register', 'Register'],
    ['en', 'email', 'Email'],
    ['en', 'password', 'Password'],
    ['en', 'search', 'Search'],
    ['en', 'jobs', 'Jobs'],
    ['en', 'companies', 'Companies'],
    ['en', 'dashboard', 'Dashboard'],
    ['en', 'profile', 'Profile'],
    ['en', 'settings', 'Settings'],
    ['en', 'logout', 'Logout']
];

$insertedTranslations = 0;
foreach ($defaultTranslations as $translation) {
    try {
        $db->insert("
            INSERT IGNORE INTO translations (language_code, translation_key, translation_value) 
            VALUES (?, ?, ?)
        ", $translation);
        $insertedTranslations++;
    } catch (Exception $e) {
        echo "⚠️ Failed to insert translation: " . $translation[1] . "<br>";
    }
}
echo "✅ Inserted $insertedTranslations translations<br>";

// Insert news categories
$newsCategories = [
    ['Actualités', 'actualites', 'Actualités générales', 'fr'],
    ['Conseils Carrière', 'conseils-carriere', 'Conseils pour votre carrière', 'fr'],
    ['Marché de l\'Emploi', 'marche-emploi', 'Tendances du marché de l\'emploi', 'fr'],
    ['Formation', 'formation', 'Formation et développement', 'fr'],
    ['Entreprises', 'entreprises', 'Actualités des entreprises', 'fr']
];

$insertedCategories = 0;
foreach ($newsCategories as $category) {
    try {
        $db->insert("
            INSERT IGNORE INTO news_categories (name, slug, description, language) 
            VALUES (?, ?, ?, ?)
        ", $category);
        $insertedCategories++;
    } catch (Exception $e) {
        echo "⚠️ Failed to insert news category: " . $category[0] . "<br>";
    }
}
echo "✅ Inserted $insertedCategories news categories<br>";

$setupSteps[] = "Default Data: Inserted successfully";
echo "</div>";

// Step 4: Verify installation
echo "<div class='step'>";
echo "<h3>Step 4: Installation Verification</h3>";

$verificationTables = ['ai_settings', 'oauth_accounts', 'translations', 'news_categories', 'audit_logs'];
$verifiedTables = 0;

foreach ($verificationTables as $table) {
    try {
        $result = $db->fetch("SELECT COUNT(*) as count FROM $table");
        if ($result) {
            echo "✅ Table '$table' verified (records: " . $result['count'] . ")<br>";
            $verifiedTables++;
        }
    } catch (Exception $e) {
        echo "❌ Table '$table' verification failed<br>";
    }
}

echo "Verified $verifiedTables out of " . count($verificationTables) . " tables<br>";
$setupSteps[] = "Verification: $verifiedTables/" . count($verificationTables) . " tables verified";
echo "</div>";

// Summary
echo "<div class='step " . (count($errors) == 0 ? 'success' : 'warning') . "'>";
echo "<h3>📊 Setup Summary</h3>";
echo "<strong>Setup Steps Completed:</strong><br>";
foreach ($setupSteps as $step) {
    echo "• $step<br>";
}

if (count($errors) == 0) {
    echo "<h4>🎉 Setup Completed Successfully!</h4>";
    echo "Your enhanced features are now ready to use!<br>";
    echo "<br><strong>Next Steps:</strong><br>";
    echo "1. Configure OAuth providers in the admin panel<br>";
    echo "2. Add the news portal to your main navigation<br>";
    echo "3. Include the theme switcher in your templates<br>";
    echo "4. Test all features in your development environment<br>";
} else {
    echo "<h4>⚠️ Setup Completed with Warnings</h4>";
    echo "Some issues were encountered but the core setup is complete.<br>";
}

echo "</div>";

echo "<p><strong>Setup completed at:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='test_enhanced_features.php'>🧪 Run Feature Tests</a> | <a href='admin/enhanced_features_manager.php'>⚙️ Enhanced Features Manager</a></p>";
?>
