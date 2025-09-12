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
            'ai_search_title' => 'Recherche d\'Emploi Intelligente - EMPLOIDB',
            'ai_search_subtitle' => 'Découvrez des emplois parfaitement adaptés grâce à notre intelligence artificielle avancée',
            'search_placeholder' => 'Décrivez votre emploi idéal...',
            'search_button' => 'Rechercher avec l\'IA',
            'ai_features_title' => 'Fonctionnalités IA Avancées',
            'matching_title' => 'Matching Intelligent',
            'matching_desc' => 'Notre IA analyse votre profil et trouve les emplois qui correspondent parfaitement à vos compétences et aspirations.',
            'prediction_title' => 'Prédiction de Salaire',
            'prediction_desc' => 'Obtenez une estimation précise du salaire que vous pouvez espérer selon votre profil et le marché.',
            'insights_title' => 'Insights Personnalisés',
            'insights_desc' => 'Recevez des conseils personnalisés pour améliorer votre profil et augmenter vos chances d\'embauche.',
            'how_it_works_title' => 'Comment ça marche ?',
            'step1_title' => 'Décrivez votre profil',
            'step1_desc' => 'Parlez-nous de vos compétences, expérience et objectifs de carrière.',
            'step2_title' => 'IA analyse et match',
            'step2_desc' => 'Notre intelligence artificielle analyse votre profil et trouve les meilleures correspondances.',
            'step3_title' => 'Recevez vos résultats',
            'step3_desc' => 'Obtenez une liste personnalisée d\'emplois avec des scores de compatibilité et des insights.',
            'results_title' => 'Résultats de votre recherche IA',
            'no_results' => 'Aucun résultat trouvé. Essayez de modifier vos critères de recherche.',
            'compatibility_score' => 'Score de compatibilité',
            'view_details' => 'Voir les détails',
            'apply_now' => 'Postuler maintenant',
        ],
        'ar' => [
            'ai_search_title' => 'البحث الذكي عن الوظائف - EMPLOIDB',
            'ai_search_subtitle' => 'اكتشف الوظائف المثالية المناسبة لك بفضل ذكائنا الاصطناعي المتقدم',
            'search_placeholder' => 'اوصف وظيفتك المثالية...',
            'search_button' => 'البحث بالذكاء الاصطناعي',
            'ai_features_title' => 'ميزات الذكاء الاصطناعي المتقدمة',
            'matching_title' => 'المطابقة الذكية',
            'matching_desc' => 'يحلل ذكاؤنا الاصطناعي ملفك الشخصي ويجد الوظائف التي تناسب مهاراتك وطموحاتك بشكل مثالي.',
            'prediction_title' => 'توقع الراتب',
            'prediction_desc' => 'احصل على تقدير دقيق للراتب الذي يمكنك توقعه حسب ملفك الشخصي والسوق.',
            'insights_title' => 'رؤى شخصية',
            'insights_desc' => 'احصل على نصائح شخصية لتحسين ملفك الشخصي وزيادة فرصك في التوظيف.',
            'how_it_works_title' => 'كيف يعمل؟',
            'step1_title' => 'اوصف ملفك الشخصي',
            'step1_desc' => 'أخبرنا عن مهاراتك وخبرتك وأهدافك المهنية.',
            'step2_title' => 'الذكاء الاصطناعي يحلل ويطابق',
            'step2_desc' => 'يحلل ذكاؤنا الاصطناعي ملفك الشخصي ويجد أفضل المطابقات.',
            'step3_title' => 'احصل على نتائجك',
            'step3_desc' => 'احصل على قائمة شخصية من الوظائف مع درجات التوافق والرؤى.',
            'results_title' => 'نتائج بحثك بالذكاء الاصطناعي',
            'no_results' => 'لم يتم العثور على نتائج. حاول تعديل معايير البحث الخاصة بك.',
            'compatibility_score' => 'درجة التوافق',
            'view_details' => 'عرض التفاصيل',
            'apply_now' => 'تقدم الآن',
        ],
        'en' => [
            'ai_search_title' => 'AI-Powered Job Search - EMPLOIDB',
            'ai_search_subtitle' => 'Discover perfectly matched jobs thanks to our advanced artificial intelligence',
            'search_placeholder' => 'Describe your ideal job...',
            'search_button' => 'Search with AI',
            'ai_features_title' => 'Advanced AI Features',
            'matching_title' => 'Smart Matching',
            'matching_desc' => 'Our AI analyzes your profile and finds jobs that perfectly match your skills and aspirations.',
            'prediction_title' => 'Salary Prediction',
            'prediction_desc' => 'Get an accurate estimate of the salary you can expect based on your profile and the market.',
            'insights_title' => 'Personalized Insights',
            'insights_desc' => 'Receive personalized advice to improve your profile and increase your hiring chances.',
            'how_it_works_title' => 'How it works?',
            'step1_title' => 'Describe your profile',
            'step1_desc' => 'Tell us about your skills, experience and career goals.',
            'step2_title' => 'AI analyzes and matches',
            'step2_desc' => 'Our artificial intelligence analyzes your profile and finds the best matches.',
            'step3_title' => 'Get your results',
            'step3_desc' => 'Get a personalized list of jobs with compatibility scores and insights.',
            'results_title' => 'Your AI Search Results',
            'no_results' => 'No results found. Try modifying your search criteria.',
            'compatibility_score' => 'Compatibility Score',
            'view_details' => 'View Details',
            'apply_now' => 'Apply Now',
        ]
    ];
    
    return $translations[$GLOBALS['currentLang']][$key] ?? $default;
}

$page_title = t('ai_search_title', 'Recherche d\'Emploi Intelligente - EMPLOIDB');
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
    <meta name="description" content="Découvrez des emplois parfaitement adaptés grâce à notre intelligence artificielle avancée">
    <meta name="keywords" content="recherche emploi IA, intelligence artificielle, matching emploi, EMPLOIDB">
    
    <style>
        .ai-hero {
            background: linear-gradient(135deg, #007bff 0%, #6f42c1 100%);
            color: white;
            padding: 80px 0;
        }
        
        .ai-search-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .ai-feature-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .ai-feature-card:hover {
            transform: translateY(-5px);
        }
        
        .ai-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 24px;
            color: white;
        }
        
        .ai-icon.brain {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .ai-icon.match {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .ai-icon.predict {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .ai-icon.analyze {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .search-input {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        
        .ai-search-btn {
            background: linear-gradient(135deg, #007bff 0%, #6f42c1 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .ai-search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 123, 255, 0.3);
        }
        
        .rtl {
            direction: rtl;
            text-align: right;
        }
    </style>
</head>
<body class="<?= $currentLang === 'ar' ? 'rtl' : '' ?>">
    <!-- Header -->
    <?php include 'frontoffice/include/header2.php'; ?>
    
    <!-- Navigation -->
    <?php include 'frontoffice/include/menu2.php'; ?>
    
    <!-- AI Hero Section -->
    <section class="ai-hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-brain me-3"></i>
                        <?= t('ai_search_title', 'Recherche d\'Emploi Intelligente') ?>
                    </h1>
                    <p class="lead mb-4">
                        <?= t('ai_search_subtitle', 'Découvrez des emplois parfaitement adaptés grâce à notre intelligence artificielle avancée') ?>
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-robot me-1"></i>
                            Alimenté par IA
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-bolt me-1"></i>
                            Résultats Instantanés
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-target me-1"></i>
                            Matching Intelligent
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- AI Search Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="ai-search-card">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-search me-2"></i>
                            Recherche Intelligente
                        </h3>
                        
                        <form id="aiSearchForm" class="mb-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-briefcase me-1"></i>
                                        Titre du Poste
                                    </label>
                                    <input type="text" class="form-control search-input" id="jobTitle" 
                                           placeholder="Ex: Développeur Web">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        Localisation
                                    </label>
                                    <input type="text" class="form-control search-input" id="location" 
                                           placeholder="Ex: Casablanca">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-graduation-cap me-1"></i>
                                        Niveau d'Expérience
                                    </label>
                                    <select class="form-select search-input" id="experience">
                                        <option value="">Tous les niveaux</option>
                                        <option value="entry">Débutant</option>
                                        <option value="mid">Intermédiaire</option>
                                        <option value="senior">Senior</option>
                                        <option value="expert">Expert</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-dollar-sign me-1"></i>
                                        Fourchette de Salaire
                                    </label>
                                    <select class="form-select search-input" id="salary">
                                        <option value="">Tous les salaires</option>
                                        <option value="0-5000">0 - 5,000 DH</option>
                                        <option value="5000-10000">5,000 - 10,000 DH</option>
                                        <option value="10000-15000">10,000 - 15,000 DH</option>
                                        <option value="15000-25000">15,000 - 25,000 DH</option>
                                        <option value="25000+">25,000+ DH</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn ai-search-btn btn-lg">
                                    <i class="fas fa-brain me-2"></i>
                                    <?= t('search_button', 'Rechercher avec IA') ?>
                                </button>
                            </div>
                        </form>
                        
                        <div id="searchResults" class="mt-4" style="display: none;">
                            <h5>Résultats de Recherche</h5>
                            <div id="resultsContainer"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- AI Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">
                        Fonctionnalités IA Avancées
                    </h2>
                    <p class="lead text-muted">
                        Découvrez comment notre IA révolutionne la recherche d'emploi
                    </p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="ai-feature-card">
                        <div class="ai-icon brain">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h5>Matching Intelligent</h5>
                        <p class="text-muted">
                            Notre IA analyse votre profil et trouve les emplois parfaitement adaptés
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="ai-feature-card">
                        <div class="ai-icon match">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h5>Analyse des Compétences</h5>
                        <p class="text-muted">
                            Évaluation automatique de vos compétences et recommandations personnalisées
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="ai-feature-card">
                        <div class="ai-icon predict">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5>Prédiction de Salaire</h5>
                        <p class="text-muted">
                            Estimation intelligente des salaires basée sur le marché et vos compétences
                        </p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="ai-feature-card">
                        <div class="ai-icon analyze">
                            <i class="fas fa-analytics"></i>
                        </div>
                        <h5>Analyse du Marché</h5>
                        <p class="text-muted">
                            Insights sur les tendances du marché de l'emploi en temps réel
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- How It Works Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">
                        Comment ça marche
                    </h2>
                    <p class="lead text-muted">
                        Notre processus en 3 étapes simples
                    </p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <span class="fs-2 fw-bold">1</span>
                        </div>
                        <h5>Analyse de votre Profil</h5>
                        <p class="text-muted">
                            Notre IA analyse vos compétences, expérience et préférences
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <span class="fs-2 fw-bold">2</span>
                        </div>
                        <h5>Matching Intelligent</h5>
                        <p class="text-muted">
                            Recherche et correspondance avec les meilleures opportunités
                        </p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <span class="fs-2 fw-bold">3</span>
                        </div>
                        <h5>Recommandations Personnalisées</h5>
                        <p class="text-muted">
                            Recevez des suggestions d'emplois parfaitement adaptés
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
        // AI Search Form Handler
        document.getElementById('aiSearchForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const jobTitle = document.getElementById('jobTitle').value;
            const location = document.getElementById('location').value;
            const experience = document.getElementById('experience').value;
            const salary = document.getElementById('salary').value;
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Recherche en cours...';
            submitBtn.disabled = true;
            
            // Simulate AI search
            setTimeout(() => {
                // Show results
                document.getElementById('searchResults').style.display = 'block';
                document.getElementById('resultsContainer').innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Ceci est une démonstration. Les résultats réels seraient générés par notre IA.
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">Développeur Web Senior</h6>
                                    <p class="card-text text-muted">Casablanca • CDI</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-success">95% Match</span>
                                        <small class="text-muted">15,000 - 20,000 DH</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title">Full Stack Developer</h6>
                                    <p class="card-text text-muted">Rabat • CDI</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-warning">87% Match</span>
                                        <small class="text-muted">12,000 - 18,000 DH</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        });
        
        // Language switcher
        function switchLanguage(lang) {
            window.location.href = `?lang=${lang}`;
        }
    </script>
</body>
</html>