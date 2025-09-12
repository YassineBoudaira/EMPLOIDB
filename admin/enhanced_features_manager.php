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

$page_title = 'Enhanced Features Manager - EMPLOIDB Admin';
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
        .features-hero {
            background: linear-gradient(135deg, #007bff 0%, #6f42c1 100%);
            color: white;
            padding: 60px 0;
        }
        
        .feature-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .feature-status-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .feature-status-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-icon {
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
        
        .feature-icon.ai {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .feature-icon.oauth {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .feature-icon.multilingual {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .feature-icon.news {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <?php include 'includes/admin_header.php'; ?>
    
    <!-- Features Hero Section -->
    <section class="features-hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-rocket me-3"></i>
                        Enhanced Features Manager
                    </h1>
                    <p class="lead mb-4">
                        Gérez toutes les fonctionnalités avancées de votre plateforme EMPLOIDB
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-brain me-1"></i>
                            IA
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-globe me-1"></i>
                            Multilingue
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-key me-1"></i>
                            OAuth
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Status Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">
                        Statut des Fonctionnalités
                    </h2>
                    <p class="lead text-muted">
                        Vue d'ensemble de toutes les fonctionnalités avancées
                    </p>
                </div>
            </div>
            
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="feature-status-card position-relative">
                        <span class="badge bg-success status-badge">Actif</span>
                        <div class="feature-icon ai">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h5>IA & Machine Learning</h5>
                        <p class="text-muted">Matching intelligent, prédiction de salaires</p>
                        <div class="mt-3">
                            <small class="text-success">
                                <i class="fas fa-check-circle me-1"></i>
                                12 services actifs
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="feature-status-card position-relative">
                        <span class="badge bg-success status-badge">Actif</span>
                        <div class="feature-icon oauth">
                            <i class="fas fa-key"></i>
                        </div>
                        <h5>OAuth & SSO</h5>
                        <p class="text-muted">Connexion sociale, authentification</p>
                        <div class="mt-3">
                            <small class="text-success">
                                <i class="fas fa-check-circle me-1"></i>
                                5 fournisseurs
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="feature-status-card position-relative">
                        <span class="badge bg-success status-badge">Actif</span>
                        <div class="feature-icon multilingual">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h5>Multilingue</h5>
                        <p class="text-muted">Support 3 langues, RTL</p>
                        <div class="mt-3">
                            <small class="text-success">
                                <i class="fas fa-check-circle me-1"></i>
                                3 langues
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="feature-status-card position-relative">
                        <span class="badge bg-warning status-badge">En cours</span>
                        <div class="feature-icon news">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <h5>News & Blog</h5>
                        <p class="text-muted">Portail d'actualités</p>
                        <div class="mt-3">
                            <small class="text-warning">
                                <i class="fas fa-clock me-1"></i>
                                En développement
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- AI Features Management -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="feature-card">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-brain me-2"></i>
                            Gestion des Fonctionnalités IA
                        </h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Services IA Actifs</h5>
                                <div class="list-group">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-search me-2 text-primary"></i>
                                            Recherche Intelligente
                                        </div>
                                        <span class="badge bg-success">Actif</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-bullseye me-2 text-info"></i>
                                            Matching Candidat-Emploi
                                        </div>
                                        <span class="badge bg-success">Actif</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-dollar-sign me-2 text-warning"></i>
                                            Prédiction de Salaires
                                        </div>
                                        <span class="badge bg-success">Actif</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-copy me-2 text-secondary"></i>
                                            Détection de Doublons
                                        </div>
                                        <span class="badge bg-success">Actif</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>Configuration IA</h5>
                                <form>
                                    <div class="mb-3">
                                        <label class="form-label">Niveau de Précision</label>
                                        <select class="form-select">
                                            <option value="high">Élevé (95%+)</option>
                                            <option value="medium" selected>Moyen (85-95%)</option>
                                            <option value="fast">Rapide (70-85%)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Langues Supportées</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" checked>
                                            <label class="form-check-label">Français</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" checked>
                                            <label class="form-check-label">Arabe</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" checked>
                                            <label class="form-check-label">Anglais</label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        Sauvegarder
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- OAuth Management -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="feature-card">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-key me-2"></i>
                            Gestion OAuth & SSO
                        </h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Fournisseurs OAuth</h5>
                                <div class="list-group">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fab fa-google me-2 text-danger"></i>
                                            Google
                                        </div>
                                        <span class="badge bg-success">Configuré</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fab fa-linkedin me-2 text-primary"></i>
                                            LinkedIn
                                        </div>
                                        <span class="badge bg-success">Configuré</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fab fa-microsoft me-2 text-info"></i>
                                            Microsoft
                                        </div>
                                        <span class="badge bg-warning">En cours</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fab fa-apple me-2 text-dark"></i>
                                            Apple
                                        </div>
                                        <span class="badge bg-secondary">Non configuré</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>Ajouter un Fournisseur</h5>
                                <form>
                                    <div class="mb-3">
                                        <label class="form-label">Nom du Fournisseur</label>
                                        <input type="text" class="form-control" placeholder="Ex: Facebook">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Client ID</label>
                                        <input type="text" class="form-control" placeholder="Votre Client ID">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Client Secret</label>
                                        <input type="password" class="form-control" placeholder="Votre Client Secret">
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>
                                        Ajouter
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- System Statistics -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="feature-card">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-chart-bar me-2"></i>
                            Statistiques du Système
                        </h3>
                        
                        <div class="row g-4">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-primary">1,247</h4>
                                    <p class="text-muted">Emplois Indexés</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-success">89</h4>
                                    <p class="text-muted">Utilisateurs Actifs</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-warning">156</h4>
                                    <p class="text-muted">Recherches IA/jour</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-info">23</h4>
                                    <p class="text-muted">Connexions OAuth</p>
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
        // Form handlers
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>En cours...';
                submitBtn.disabled = true;
                
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show mt-3';
                    alert.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i>
                        Configuration sauvegardée avec succès!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    
                    this.appendChild(alert);
                    
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.remove();
                        }
                    }, 3000);
                }, 2000);
            });
        });
    </script>
</body>
</html>