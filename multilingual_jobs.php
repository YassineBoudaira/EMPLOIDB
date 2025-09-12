<?php
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';

// Simple language handling with error prevention
$currentLang = 'fr'; // Default to French
$allowedLanguages = ['fr', 'ar', 'en'];

if (isset($_GET['lang']) && in_array($_GET['lang'], $allowedLanguages)) {
    $currentLang = $_GET['lang'];
    $_SESSION['lang'] = $currentLang;
} elseif (isset($_SESSION['lang']) && in_array($_SESSION['lang'], $allowedLanguages)) {
    $currentLang = $_SESSION['lang'];
} else {
    // Ensure we always have a valid language
    $currentLang = 'fr';
    $_SESSION['lang'] = $currentLang;
}

// Simple translation function
function t($key, $default = '') {
    $translations = [
        'fr' => [
            'multilingual_jobs_title' => 'Offres d\'Emploi Multilingues - EMPLOIDB',
            'multilingual_jobs_subtitle' => 'Découvrez des opportunités d\'emploi dans votre langue préférée',
            'language_switcher' => 'Changer de langue',
            'search_placeholder' => 'Rechercher un emploi...',
            'location_placeholder' => 'Localisation',
            'search_button' => 'Rechercher',
            'job_title_1' => 'Développeur Full Stack',
            'company_name_1' => 'Tech Solutions',
            'location_1' => 'Casablanca',
            'job_desc_1' => 'Développement d\'applications web full stack avec les dernières technologies.',
            'job_title_2' => 'Chef de Projet IT',
            'company_name_2' => 'Innovate Corp',
            'location_2' => 'Rabat',
            'job_desc_2' => 'Gestion de projets informatiques complexes et coordination d\'équipes.',
            'job_title_3' => 'Spécialiste Marketing Digital',
            'company_name_3' => 'Digital Growth',
            'location_3' => 'Marrakech',
            'job_desc_3' => 'Développement et exécution de stratégies marketing digital.',
            'job_title_4' => 'Ingénieur Réseaux et Sécurité',
            'company_name_4' => 'SecureNet',
            'location_4' => 'Tanger',
            'job_desc_4' => 'Conception et maintenance d\'infrastructures réseau sécurisées.',
            'job_title_5' => 'Assistant Administratif',
            'company_name_5' => 'Global Services',
            'location_5' => 'Fès',
            'job_desc_5' => 'Support administratif et gestion de bureau.',
            'view_details' => 'Voir les détails',
            'apply_now' => 'Postuler maintenant',
            'no_jobs_found' => 'Aucune offre d\'emploi trouvée pour le moment.',
        ],
        'ar' => [
            'multilingual_jobs_title' => 'عروض العمل متعددة اللغات - EMPLOIDB',
            'multilingual_jobs_subtitle' => 'اكتشف فرص العمل بلغتك المفضلة',
            'language_switcher' => 'تغيير اللغة',
            'search_placeholder' => 'البحث عن وظيفة...',
            'location_placeholder' => 'الموقع',
            'search_button' => 'بحث',
            'job_title_1' => 'مطور Full Stack',
            'company_name_1' => 'Tech Solutions',
            'location_1' => 'الدار البيضاء',
            'job_desc_1' => 'تطوير تطبيقات ويب كاملة باستخدام أحدث التقنيات.',
            'job_title_2' => 'مدير مشاريع تقنية',
            'company_name_2' => 'Innovate Corp',
            'location_2' => 'الرباط',
            'job_desc_2' => 'إدارة المشاريع التقنية المعقدة وتنسيق الفرق.',
            'job_title_3' => 'أخصائي التسويق الرقمي',
            'company_name_3' => 'Digital Growth',
            'location_3' => 'مراكش',
            'job_desc_3' => 'تطوير وتنفيذ استراتيجيات التسويق الرقمي.',
            'job_title_4' => 'مهندس شبكات وأمان',
            'company_name_4' => 'SecureNet',
            'location_4' => 'طنجة',
            'job_desc_4' => 'تصميم وصيانة البنى التحتية للشبكات الآمنة.',
            'job_title_5' => 'مساعد إداري',
            'company_name_5' => 'Global Services',
            'location_5' => 'فاس',
            'job_desc_5' => 'الدعم الإداري وإدارة المكتب.',
            'view_details' => 'عرض التفاصيل',
            'apply_now' => 'تقدم الآن',
            'no_jobs_found' => 'لم يتم العثور على عروض عمل حالياً.',
        ],
        'en' => [
            'multilingual_jobs_title' => 'Multilingual Job Offers - EMPLOIDB',
            'multilingual_jobs_subtitle' => 'Discover job opportunities in your preferred language',
            'language_switcher' => 'Change Language',
            'search_placeholder' => 'Search for a job...',
            'location_placeholder' => 'Location',
            'search_button' => 'Search',
            'job_title_1' => 'Full Stack Developer',
            'company_name_1' => 'Tech Solutions',
            'location_1' => 'Casablanca',
            'job_desc_1' => 'Full stack web application development with the latest technologies.',
            'job_title_2' => 'IT Project Manager',
            'company_name_2' => 'Innovate Corp',
            'location_2' => 'Rabat',
            'job_desc_2' => 'Management of complex IT projects and team coordination.',
            'job_title_3' => 'Digital Marketing Specialist',
            'company_name_3' => 'Digital Growth',
            'location_3' => 'Marrakech',
            'job_desc_3' => 'Development and execution of digital marketing strategies.',
            'job_title_4' => 'Network and Security Engineer',
            'company_name_4' => 'SecureNet',
            'location_4' => 'Tangier',
            'job_desc_4' => 'Design and maintenance of secure network infrastructures.',
            'job_title_5' => 'Administrative Assistant',
            'company_name_5' => 'Global Services',
            'location_5' => 'Fez',
            'job_desc_5' => 'Administrative support and office management.',
            'view_details' => 'View Details',
            'apply_now' => 'Apply Now',
            'no_jobs_found' => 'No job offers found at the moment.',
        ]
    ];
    
    return $translations[$GLOBALS['currentLang']][$key] ?? $default;
}

$page_title = t('multilingual_jobs_title', 'Offres d\'Emploi Multilingues - EMPLOIDB');

// Simulate fetching jobs from database or use fallback data
$jobs = [];
try {
    // Attempt to fetch real jobs if 'annonces' table exists
    if (isset($db) && $db !== null) {
        $stmt = $db->query("SELECT a.*, d.nom as domaine_name, v.nom as ville_name FROM annonces a LEFT JOIN domaines d ON a.id_domaine = d.id LEFT JOIN villes v ON a.id_ville = v.id WHERE a.status = 'active' ORDER BY a.date_a DESC LIMIT 20");
        if ($stmt) {
            $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    error_log("Could not fetch jobs from database: " . $e->getMessage());
}

if (empty($jobs)) {
    // Fallback to sample data if table doesn't exist or query fails
    $jobs = [
        ['id' => 1, 'title' => t('job_title_1'), 'company_name' => t('company_name_1'), 'location' => t('location_1'), 'description' => t('job_desc_1'), 'salary_range' => '10,000 - 15,000 DH'],
        ['id' => 2, 'title' => t('job_title_2'), 'company_name' => t('company_name_2'), 'location' => t('location_2'), 'description' => t('job_desc_2'), 'salary_range' => '15,000 - 20,000 DH'],
        ['id' => 3, 'title' => t('job_title_3'), 'company_name' => t('company_name_3'), 'location' => t('location_3'), 'description' => t('job_desc_3'), 'salary_range' => '8,000 - 12,000 DH'],
        ['id' => 4, 'title' => t('job_title_4'), 'company_name' => t('company_name_4'), 'location' => t('location_4'), 'description' => t('job_desc_4'), 'salary_range' => '12,000 - 18,000 DH'],
        ['id' => 5, 'title' => t('job_title_5'), 'company_name' => t('company_name_5'), 'location' => t('location_5'), 'description' => t('job_desc_5'), 'salary_range' => '4,000 - 6,000 DH'],
    ];
}
?>

<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $currentLang === 'ar' ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="frontoffice/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- EMPLOIDB Professional Design System -->
    <link href="assets/css/emploidb-design-system.css" rel="stylesheet">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Découvrez des emplois en français, arabe et anglais sur EMPLOIDB">
    <meta name="keywords" content="emploi multilingue, français, arabe, anglais, EMPLOIDB">
    
    <style>
        .multilingual-hero {
            background: linear-gradient(135deg, #007bff 0%, #6f42c1 100%);
            color: white;
            padding: 80px 0;
        }
        
        .language-switcher {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .language-btn {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 20px;
            margin: 5px;
            text-decoration: none;
            color: #64748b;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .language-btn:hover,
        .language-btn.active {
            border-color: #007bff;
            background: #007bff;
            color: white;
        }
        
        .job-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
            border-left: 4px solid #007bff;
        }
        
        .job-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .job-title {
            color: #007bff;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .job-meta {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .job-description {
            color: #374151;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .job-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .rtl {
            direction: rtl;
            text-align: right;
        }
        
        .rtl .job-actions {
            flex-direction: row-reverse;
        }
    </style>
</head>
<body class="<?= $currentLang === 'ar' ? 'rtl' : '' ?>">
    <!-- Header -->
    <?php include 'frontoffice/include/header2.php'; ?>
    
    <!-- Navigation -->
    <?php include 'frontoffice/include/menu2.php'; ?>
    
    <!-- Multilingual Hero Section -->
    <section class="multilingual-hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-globe me-3"></i>
                        <?= t('multilingual_jobs_title', 'Offres d\'Emploi Multilingues') ?>
                    </h1>
                    <p class="lead mb-4">
                        <?= t('multilingual_jobs_subtitle', 'Découvrez des emplois en français, arabe et anglais') ?>
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-language me-1"></i>
                            3 Langues
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-briefcase me-1"></i>
                            Tous les Emplois
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-sync me-1"></i>
                            Temps Réel
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Language Switcher Section -->
    <section class="py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="language-switcher text-center">
                        <h5 class="mb-3">
                            <i class="fas fa-globe me-2"></i>
                            Choisissez votre langue
                        </h5>
                        <div class="d-flex justify-content-center flex-wrap">
                            <a href="?lang=fr" class="language-btn <?= $currentLang === 'fr' ? 'active' : '' ?>">
                                <i class="fas fa-flag me-2"></i>Français
                            </a>
                            <a href="?lang=ar" class="language-btn <?= $currentLang === 'ar' ? 'active' : '' ?>">
                                <i class="fas fa-flag me-2"></i>العربية
                            </a>
                            <a href="?lang=en" class="language-btn <?= $currentLang === 'en' ? 'active' : '' ?>">
                                <i class="fas fa-flag me-2"></i>English
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Jobs Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h3 mb-0">
                            <i class="fas fa-briefcase me-2"></i>
                            Emplois Disponibles
                        </h2>
                        <span class="badge bg-primary">
                            <?= count($jobs) ?> emplois trouvés
                        </span>
                    </div>
                    
                    <?php if (!empty($jobs)): ?>
                        <?php foreach ($jobs as $job): ?>
                            <div class="job-card">
                                <h4 class="job-title">
                                    <?= htmlspecialchars($job['titre']) ?>
                                </h4>
                                
                                <div class="job-meta">
                                    <i class="fas fa-building me-1"></i>
                                    <strong><?= htmlspecialchars($job['entreprise'] ?? 'Entreprise') ?></strong>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?= htmlspecialchars($job['ville_name'] ?? 'Non spécifié') ?>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-folder me-1"></i>
                                    <?= htmlspecialchars($job['domaine_name'] ?? 'Non spécifié') ?>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-calendar me-1"></i>
                                    <?= date('d/m/Y', strtotime($job['date_a'])) ?>
                                </div>
                                
                                <div class="job-description">
                                    <?= nl2br(htmlspecialchars(substr($job['description'], 0, 200))) ?>
                                    <?= strlen($job['description']) > 200 ? '...' : '' ?>
                                </div>
                                
                                <div class="job-actions">
                                    <div>
                                        <span class="badge bg-success me-2">
                                            <i class="fas fa-check me-1"></i>
                                            Actif
                                        </span>
                                        <?php if (!empty($job['salaire'])): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-dollar-sign me-1"></i>
                                                <?= htmlspecialchars($job['salaire']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <a href="annoncedetaile.php?id=<?= $job['id'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>
                                            Voir Détails
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">
                                Aucun emploi trouvé
                            </h4>
                            <p class="text-muted">
                                Essayez de modifier vos critères de recherche
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Language Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">
                        Fonctionnalités Multilingues
                    </h2>
                    <p class="lead text-muted">
                        Une expérience utilisateur adaptée à votre langue
                    </p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-language fa-2x"></i>
                        </div>
                        <h5>Support RTL</h5>
                        <p class="text-muted">
                            Interface adaptée pour l'arabe avec support RTL complet
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-sync fa-2x"></i>
                        </div>
                        <h5>Traduction Temps Réel</h5>
                        <p class="text-muted">
                            Changement de langue instantané sans rechargement de page
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-globe fa-2x"></i>
                        </div>
                        <h5>Contenu Localisé</h5>
                        <p class="text-muted">
                            Contenu adapté à chaque région et culture
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <?php include 'frontoffice/include/footer2.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Language switcher with smooth transition
        document.querySelectorAll('.language-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                // Add loading state
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>' + this.textContent.trim();
                
                // The page will reload with new language
            });
        });
        
        // Auto-detect user language preference
        function detectUserLanguage() {
            const browserLang = navigator.language || navigator.userLanguage;
            const langCode = browserLang.split('-')[0];
            
            // Check if we support this language
            const supportedLangs = ['fr', 'ar', 'en'];
            if (supportedLangs.includes(langCode) && !window.location.search.includes('lang=')) {
                // Optionally redirect to user's preferred language
                // window.location.href = `?lang=${langCode}`;
            }
        }
        
        // Run on page load
        document.addEventListener('DOMContentLoaded', detectUserLanguage);
    </script>
</body>
</html>