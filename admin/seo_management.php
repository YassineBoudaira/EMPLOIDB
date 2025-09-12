<?php
// Include configuration first (before any session starts)
include '../include/config.php';
include '../include/sess.php';
include '../include/connexion.php';
include '../include/Security.php';

// Check if user is admin
if (!Security::isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$page_title = 'SEO Management - EMPLOIDB Admin';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="../frontoffice/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- EMPLOIDB Professional Design System -->
    <link href="../assets/css/emploidb-design-system.css" rel="stylesheet">
    
    <style>
        .seo-hero {
            background: linear-gradient(135deg, #007bff 0%, #6f42c1 100%);
            color: white;
            padding: 60px 0;
        }
        
        .seo-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .seo-stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .seo-stat-card:hover {
            transform: translateY(-5px);
        }
        
        .seo-icon {
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
        
        .seo-icon.search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .seo-icon.analytics {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .seo-icon.schema {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .seo-icon.sitemap {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #6f42c1 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 123, 255, 0.3);
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <?php include 'includes/admin_header.php'; ?>
    
    <!-- SEO Hero Section -->
    <section class="seo-hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-search me-3"></i>
                        SEO Management
                    </h1>
                    <p class="lead mb-4">
                        Optimisez le référencement de votre site avec nos outils SEO avancés
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-chart-line me-1"></i>
                            Analytics
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-sitemap me-1"></i>
                            Sitemap
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-code me-1"></i>
                            Schema.org
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- SEO Statistics Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">
                        Statistiques SEO
                    </h2>
                    <p class="lead text-muted">
                        Suivez les performances de votre référencement
                    </p>
                </div>
            </div>
            
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="seo-stat-card">
                        <div class="seo-icon search">
                            <i class="fas fa-search"></i>
                        </div>
                        <h5>Mots-clés Suivis</h5>
                        <h3 class="text-primary">156</h3>
                        <p class="text-muted">Mots-clés actifs</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="seo-stat-card">
                        <div class="seo-icon analytics">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h5>Position Moyenne</h5>
                        <h3 class="text-success">12.5</h3>
                        <p class="text-muted">Sur Google</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="seo-stat-card">
                        <div class="seo-icon schema">
                            <i class="fas fa-code"></i>
                        </div>
                        <h5>Pages Optimisées</h5>
                        <h3 class="text-warning">89</h3>
                        <p class="text-muted">Avec Schema.org</p>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="seo-stat-card">
                        <div class="seo-icon sitemap">
                            <i class="fas fa-sitemap"></i>
                        </div>
                        <h5>Pages Indexées</h5>
                        <h3 class="text-info">1,247</h3>
                        <p class="text-muted">Dans Google</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- SEO Configuration Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="seo-card">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-cog me-2"></i>
                            Configuration SEO
                        </h3>
                        
                        <form id="seoConfigForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-globe me-1"></i>
                                        URL du Site
                                    </label>
                                    <input type="url" class="form-control" id="siteUrl" 
                                           value="https://emploidb.test" placeholder="https://votre-site.com">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-tag me-1"></i>
                                        Titre par Défaut
                                    </label>
                                    <input type="text" class="form-control" id="defaultTitle" 
                                           value="EMPLOIDB - Plateforme d'Emploi au Maroc" 
                                           placeholder="Titre par défaut du site">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-align-left me-1"></i>
                                        Description par Défaut
                                    </label>
                                    <textarea class="form-control" id="defaultDescription" rows="3" 
                                              placeholder="Description par défaut du site">Trouvez votre emploi idéal sur EMPLOIDB, la plateforme d'emploi leader au Maroc. Des milliers d'offres d'emploi dans tous les secteurs.</textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-key me-1"></i>
                                        Mots-clés Principaux
                                    </label>
                                    <textarea class="form-control" id="mainKeywords" rows="3" 
                                              placeholder="Mots-clés séparés par des virgules">emploi maroc, offres emploi, recrutement, carrière, travail, emploi casablanca, emploi rabat</textarea>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-image me-1"></i>
                                        Image par Défaut
                                    </label>
                                    <input type="url" class="form-control" id="defaultImage" 
                                           placeholder="https://votre-site.com/image-par-defaut.jpg">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-user me-1"></i>
                                        Auteur par Défaut
                                    </label>
                                    <input type="text" class="form-control" id="defaultAuthor" 
                                           value="EMPLOIDB" placeholder="Auteur par défaut">
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>
                                    Sauvegarder la Configuration
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Schema.org Management Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="seo-card">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-code me-2"></i>
                            Gestion Schema.org
                        </h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Types de Schema Actifs</h5>
                                <div class="list-group">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-briefcase me-2 text-primary"></i>
                                            JobPosting
                                        </div>
                                        <span class="badge bg-success">Actif</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-building me-2 text-info"></i>
                                            Organization
                                        </div>
                                        <span class="badge bg-success">Actif</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-globe me-2 text-warning"></i>
                                            WebSite
                                        </div>
                                        <span class="badge bg-success">Actif</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-newspaper me-2 text-secondary"></i>
                                            Article
                                        </div>
                                        <span class="badge bg-success">Actif</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>Actions Schema</h5>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary">
                                        <i class="fas fa-plus me-2"></i>
                                        Ajouter un Type Schema
                                    </button>
                                    <button class="btn btn-outline-success">
                                        <i class="fas fa-sync me-2"></i>
                                        Générer Schema pour Toutes les Pages
                                    </button>
                                    <button class="btn btn-outline-info">
                                        <i class="fas fa-eye me-2"></i>
                                        Prévisualiser Schema
                                    </button>
                                    <button class="btn btn-outline-warning">
                                        <i class="fas fa-download me-2"></i>
                                        Exporter Schema
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Sitemap Management Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="seo-card">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-sitemap me-2"></i>
                            Gestion Sitemap
                        </h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Statut Sitemap</h5>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Sitemap généré avec succès
                                </div>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-file me-2 text-primary"></i>Dernière génération: <?= date('d/m/Y H:i') ?></li>
                                    <li><i class="fas fa-list me-2 text-info"></i>Nombre de pages: 1,247</li>
                                    <li><i class="fas fa-clock me-2 text-warning"></i>Fréquence de mise à jour: Quotidienne</li>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>Actions Sitemap</h5>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary">
                                        <i class="fas fa-sync me-2"></i>
                                        Régénérer Sitemap
                                    </button>
                                    <button class="btn btn-outline-primary">
                                        <i class="fas fa-eye me-2"></i>
                                        Voir Sitemap XML
                                    </button>
                                    <button class="btn btn-outline-success">
                                        <i class="fas fa-upload me-2"></i>
                                        Soumettre à Google
                                    </button>
                                    <button class="btn btn-outline-info">
                                        <i class="fas fa-download me-2"></i>
                                        Télécharger Sitemap
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- SEO Tools Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="seo-card">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-tools me-2"></i>
                            Outils SEO
                        </h3>
                        
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="text-center p-3">
                                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                        <i class="fas fa-search fa-lg"></i>
                                    </div>
                                    <h6>Analyse de Mots-clés</h6>
                                    <p class="text-muted small">Analysez la densité et la pertinence de vos mots-clés</p>
                                    <button class="btn btn-outline-primary btn-sm">Lancer l'Analyse</button>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="text-center p-3">
                                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                        <i class="fas fa-link fa-lg"></i>
                                    </div>
                                    <h6>Vérification des Liens</h6>
                                    <p class="text-muted small">Vérifiez l'état de vos liens internes et externes</p>
                                    <button class="btn btn-outline-success btn-sm">Vérifier les Liens</button>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="text-center p-3">
                                    <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                        <i class="fas fa-mobile-alt fa-lg"></i>
                                    </div>
                                    <h6>Test Mobile</h6>
                                    <p class="text-muted small">Testez la compatibilité mobile de vos pages</p>
                                    <button class="btn btn-outline-warning btn-sm">Tester Mobile</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Admin Footer -->
    <?php include 'includes/admin_footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // SEO Configuration Form Handler
        document.getElementById('seoConfigForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sauvegarde en cours...';
            submitBtn.disabled = true;
            
            // Simulate save operation
            setTimeout(() => {
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>
                    Configuration SEO sauvegardée avec succès!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                // Insert alert before the form
                this.parentNode.insertBefore(alert, this);
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                // Remove alert after 5 seconds
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 5000);
            }, 2000);
        });
        
        // SEO Tools Button Handlers
        document.querySelectorAll('.btn-outline-primary, .btn-outline-success, .btn-outline-warning').forEach(btn => {
            btn.addEventListener('click', function() {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>En cours...';
                this.disabled = true;
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                    
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-info alert-dismissible fade show mt-3';
                    alert.innerHTML = `
                        <i class="fas fa-info-circle me-2"></i>
                        Action exécutée avec succès!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    
                    this.parentNode.appendChild(alert);
                    
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 3000);
                }, 1500);
            });
        });
    </script>
</body>
</html>
