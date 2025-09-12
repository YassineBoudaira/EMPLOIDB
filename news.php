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

$page_title = 'Actualités - EMPLOIDB';

// Get news articles (using a simple approach for now)
try {
    // Check if news_articles table exists, if not create sample data
    $articles = [];
    $featuredArticles = [];
    $categories = [];
    
    // Try to get real data first
    try {
        // Check if database connection is available
        if (isset($db) && $db !== null) {
            $articles = $db->fetchAll("
                SELECT n.*, u.user as author_name
                FROM news_articles n
                LEFT JOIN users u ON n.author_id = u.id
                WHERE n.status = 'published'
                ORDER BY n.published_at DESC
                LIMIT 20
            ");
            
            $featuredArticles = $db->fetchAll("
                SELECT n.*, u.user as author_name
                FROM news_articles n
                LEFT JOIN users u ON n.author_id = u.id
                WHERE n.status = 'published' AND n.featured = 1
                ORDER BY n.published_at DESC
                LIMIT 3
            ");
            
            $categories = $db->fetchAll("
                SELECT c.*, COUNT(n.id) as article_count
                FROM news_categories c
                LEFT JOIN news_articles n ON c.id = n.category_id AND n.status = 'published'
                GROUP BY c.id
                ORDER BY c.name
            ");
        } else {
            throw new Exception("Database connection not available");
        }
    } catch (Exception $e) {
        // If tables don't exist, use sample data
        $articles = [
            [
                'id' => 1,
                'title' => 'EMPLOIDB lance ses nouvelles fonctionnalités IA',
                'excerpt' => 'Découvrez les dernières innovations en intelligence artificielle pour la recherche d\'emploi.',
                'content' => 'EMPLOIDB continue d\'innover avec le lancement de nouvelles fonctionnalités basées sur l\'intelligence artificielle. Ces outils révolutionnaires permettront aux candidats de trouver des emplois parfaitement adaptés à leur profil.',
                'author_name' => 'Admin EMPLOIDB',
                'published_at' => date('Y-m-d H:i:s'),
                'featured' => 1,
                'image_url' => 'frontoffice/assets/img/news-1.jpg'
            ],
            [
                'id' => 2,
                'title' => 'Le marché de l\'emploi au Maroc en 2024',
                'excerpt' => 'Analyse complète des tendances du marché de l\'emploi marocain cette année.',
                'content' => 'Le marché de l\'emploi au Maroc connaît une évolution positive en 2024, avec une croissance significative dans les secteurs technologiques et des services.',
                'author_name' => 'Équipe Éditoriale',
                'published_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'featured' => 0,
                'image_url' => 'frontoffice/assets/img/news-2.jpg'
            ],
            [
                'id' => 3,
                'title' => 'Conseils pour réussir votre entretien d\'embauche',
                'excerpt' => 'Nos experts partagent leurs meilleurs conseils pour briller lors de vos entretiens.',
                'content' => 'Un entretien d\'embauche réussi nécessite une préparation minutieuse. Voici nos conseils d\'experts pour vous aider à décrocher le poste de vos rêves.',
                'author_name' => 'Conseillers Carrière',
                'published_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'featured' => 0,
                'image_url' => 'frontoffice/assets/img/news-3.jpg'
            ],
            [
                'id' => 4,
                'title' => 'Les métiers du digital en pleine expansion',
                'excerpt' => 'Le secteur digital offre de nombreuses opportunités de carrière au Maroc.',
                'content' => 'Avec la digitalisation croissante des entreprises, les métiers du digital sont en pleine expansion et offrent de nombreuses opportunités.',
                'author_name' => 'Équipe Éditoriale',
                'published_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'featured' => 1,
                'image_url' => 'frontoffice/assets/img/news-4.jpg'
            ],
            [
                'id' => 5,
                'title' => 'Formation continue : un atout pour votre carrière',
                'excerpt' => 'Découvrez l\'importance de la formation continue dans l\'évolution professionnelle.',
                'content' => 'La formation continue est devenue un élément essentiel pour maintenir sa compétitivité sur le marché du travail.',
                'author_name' => 'Conseillers Carrière',
                'published_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'featured' => 0,
                'image_url' => 'frontoffice/assets/img/news-5.jpg'
            ]
        ];
        
        $featuredArticles = array_filter($articles, function($article) {
            return $article['featured'] == 1;
        });
        
        $categories = [
            ['id' => 1, 'name' => 'Actualités', 'article_count' => 2],
            ['id' => 2, 'name' => 'Conseils Carrière', 'article_count' => 2],
            ['id' => 3, 'name' => 'Marché de l\'Emploi', 'article_count' => 1]
        ];
    }
} catch (Exception $e) {
    $articles = [];
    $featuredArticles = [];
    $categories = [];
    error_log("Error fetching news: " . $e->getMessage());
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
    <meta name="description" content="Découvrez les dernières actualités et conseils carrière sur EMPLOIDB, la plateforme d'emploi leader au Maroc">
    <meta name="keywords" content="actualités emploi, conseils carrière, marché emploi maroc, EMPLOIDB">
    
    <style>
        .news-hero {
            background: linear-gradient(135deg, #007bff 0%, #6f42c1 100%);
            color: white;
            padding: 80px 0;
        }
        
        .news-card {
            background: white;
            border-radius: 16px;
            padding: 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .news-card:hover {
            transform: translateY(-5px);
        }
        
        .news-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .news-card-body {
            padding: 25px;
        }
        
        .news-card-title {
            color: #007bff;
            font-weight: 600;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        
        .news-card-excerpt {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .news-meta {
            color: #94a3b8;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .featured-article {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-left: 4px solid #007bff;
        }
        
        .category-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        
        .category-card:hover {
            transform: translateY(-3px);
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
    
    <!-- News Hero Section -->
    <section class="news-hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-newspaper me-3"></i>
                        Actualités & Conseils
                    </h1>
                    <p class="lead mb-4">
                        Découvrez les dernières actualités du marché de l'emploi et nos conseils carrière
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-clock me-1"></i>
                            Mis à jour quotidiennement
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-users me-1"></i>
                            Conseils d'experts
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-chart-line me-1"></i>
                            Tendances du marché
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
    
    <!-- Featured Articles Section -->
    <?php if (!empty($featuredArticles)): ?>
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">
                        <i class="fas fa-star me-2"></i>
                        Articles en Vedette
                    </h2>
                    <p class="lead text-muted">
                        Nos articles les plus populaires et les plus récents
                    </p>
                </div>
            </div>
            
            <div class="row g-4">
                <?php foreach ($featuredArticles as $article): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="news-card featured-article">
                        <img src="<?= htmlspecialchars($article['image_url'] ?? 'frontoffice/assets/img/news-default.jpg') ?>" 
                             alt="<?= htmlspecialchars($article['title']) ?>"
                             onerror="this.src='frontoffice/assets/img/news-default.jpg'">
                        <div class="news-card-body">
                            <h5 class="news-card-title">
                                <?= htmlspecialchars($article['title']) ?>
                            </h5>
                            <p class="news-card-excerpt">
                                <?= htmlspecialchars($article['excerpt']) ?>
                            </p>
                            <div class="news-meta">
                                <i class="fas fa-user me-1"></i>
                                <?= htmlspecialchars($article['author_name']) ?>
                                <span class="mx-2">•</span>
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('d/m/Y', strtotime($article['published_at'])) ?>
                            </div>
                            <a href="#" class="btn btn-primary btn-sm">
                                <i class="fas fa-read me-1"></i>
                                Lire la suite
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Main Content Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <!-- Articles Column -->
                <div class="col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h3 mb-0">
                            <i class="fas fa-newspaper me-2"></i>
                            Tous les Articles
                        </h2>
                        <span class="badge bg-primary">
                            <?= count($articles) ?> articles
                        </span>
                    </div>
                    
                    <?php if (!empty($articles)): ?>
                        <?php foreach ($articles as $article): ?>
                            <div class="news-card">
                                <div class="row g-0">
                                    <div class="col-md-4">
                                        <img src="<?= htmlspecialchars($article['image_url'] ?? 'frontoffice/assets/img/news-default.jpg') ?>" 
                                             alt="<?= htmlspecialchars($article['title']) ?>"
                                             class="h-100"
                                             style="object-fit: cover;"
                                             onerror="this.src='frontoffice/assets/img/news-default.jpg'">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="news-card-body">
                                            <h5 class="news-card-title">
                                                <?= htmlspecialchars($article['title']) ?>
                                            </h5>
                                            <p class="news-card-excerpt">
                                                <?= htmlspecialchars($article['excerpt']) ?>
                                            </p>
                                            <div class="news-meta">
                                                <i class="fas fa-user me-1"></i>
                                                <?= htmlspecialchars($article['author_name']) ?>
                                                <span class="mx-2">•</span>
                                                <i class="fas fa-calendar me-1"></i>
                                                <?= date('d/m/Y', strtotime($article['published_at'])) ?>
                                                <?php if ($article['featured']): ?>
                                                    <span class="mx-2">•</span>
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-star me-1"></i>
                                                        En vedette
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <a href="#" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-read me-1"></i>
                                                Lire la suite
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">
                                Aucun article disponible
                            </h4>
                            <p class="text-muted">
                                Revenez bientôt pour découvrir nos derniers articles
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Sidebar Column -->
                <div class="col-lg-4">
                    <!-- Categories -->
                    <?php if (!empty($categories)): ?>
                    <div class="mb-5">
                        <h4 class="mb-4">
                            <i class="fas fa-folder me-2"></i>
                            Catégories
                        </h4>
                        <div class="row g-3">
                            <?php foreach ($categories as $category): ?>
                            <div class="col-12">
                                <div class="category-card">
                                    <h6><?= htmlspecialchars($category['name']) ?></h6>
                                    <span class="badge bg-primary">
                                        <?= $category['article_count'] ?> articles
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Newsletter Signup -->
                    <div class="news-card">
                        <div class="news-card-body text-center">
                            <h5 class="mb-3">
                                <i class="fas fa-envelope me-2"></i>
                                Newsletter
                            </h5>
                            <p class="text-muted mb-3">
                                Recevez nos dernières actualités directement dans votre boîte mail
                            </p>
                            <form>
                                <div class="mb-3">
                                    <input type="email" class="form-control" placeholder="Votre adresse email" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    S'abonner
                                </button>
                            </form>
                        </div>
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
        // Language switcher with smooth transition and error handling
        document.addEventListener('DOMContentLoaded', function() {
            const languageBtns = document.querySelectorAll('.language-btn');
            if (languageBtns.length > 0) {
                languageBtns.forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        // Add loading state
                        const originalText = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Chargement...';
                        this.style.pointerEvents = 'none';
                        
                        // Get the language from the href
                        const href = this.getAttribute('href');
                        const lang = href.split('lang=')[1];
                        
                        // Redirect to the new language
                        if (lang && ['fr', 'ar', 'en'].includes(lang)) {
                            window.location.href = href;
                        } else {
                            // Reset button if invalid language
                            this.innerHTML = originalText;
                            this.style.pointerEvents = 'auto';
                            console.error('Invalid language:', lang);
                        }
                    });
                });
            }
            
            // Newsletter form handler with error handling
            const newsletterForm = document.querySelector('form');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (!submitBtn) return;
                    
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>En cours...';
                    submitBtn.disabled = true;
                    
                    setTimeout(() => {
                        submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Inscrit!';
                        submitBtn.classList.remove('btn-primary');
                        submitBtn.classList.add('btn-success');
                        
                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.classList.remove('btn-success');
                            submitBtn.classList.add('btn-primary');
                            submitBtn.disabled = false;
                            this.reset();
                        }, 3000);
                    }, 2000);
                });
            }
        });
    </script>
</body>
</html>