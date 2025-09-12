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

$page_title = 'AI Features - EMPLOIDB Admin';
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
        .ai-hero {
            background: linear-gradient(135deg, #007bff 0%, #6f42c1 100%);
            color: white;
            padding: 60px 0;
        }
        
        .ai-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .ai-service-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            height: 100%;
        }
        
        .ai-service-card:hover {
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
        
        .ai-icon.search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .ai-icon.matching {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .ai-icon.salary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .ai-icon.duplicate {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <?php include 'includes/admin_header.php'; ?>
    
    <!-- AI Hero Section -->
    <section class="ai-hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-4">
                        <i class="fas fa-brain me-3"></i>
                        AI Features Management
                    </h1>
                    <p class="lead mb-4">
                        Gérez et configurez toutes les fonctionnalités d'intelligence artificielle
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-robot me-1"></i>
                            Machine Learning
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-search me-1"></i>
                            Recherche Intelligente
                        </span>
                        <span class="badge bg-light text-dark px-3 py-2">
                            <i class="fas fa-bullseye me-1"></i>
                            Matching IA
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- AI Services Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">
                        Services IA Disponibles
                    </h2>
                    <p class="lead text-muted">
                        Tous les services d'intelligence artificielle de votre plateforme
                    </p>
                </div>
            </div>
            
            <div class="row g-4 mb-5">
                <div class="col-md-6 col-lg-3">
                    <div class="ai-service-card">
                        <div class="ai-icon search">
                            <i class="fas fa-search"></i>
                        </div>
                        <h5>Recherche Intelligente</h5>
                        <p class="text-muted">
                            Recherche sémantique et prédictive des emplois
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-success">Actif</span>
                            <small class="text-muted d-block mt-2">156 recherches/jour</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="ai-service-card">
                        <div class="ai-icon matching">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h5>Matching Candidat-Emploi</h5>
                        <p class="text-muted">
                            Correspondance intelligente entre profils et offres
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-success">Actif</span>
                            <small class="text-muted d-block mt-2">89% précision</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="ai-service-card">
                        <div class="ai-icon salary">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h5>Prédiction de Salaires</h5>
                        <p class="text-muted">
                            Estimation intelligente des salaires basée sur le marché
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-success">Actif</span>
                            <small class="text-muted d-block mt-2">92% précision</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="ai-service-card">
                        <div class="ai-icon duplicate">
                            <i class="fas fa-copy"></i>
                        </div>
                        <h5>Détection de Doublons</h5>
                        <p class="text-muted">
                            Identification automatique des offres dupliquées
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-success">Actif</span>
                            <small class="text-muted d-block mt-2">23 doublons détectés</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- AI Configuration Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="ai-card">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-cog me-2"></i>
                            Configuration IA
                        </h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Paramètres Généraux</h5>
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
                                    <div class="mb-3">
                                        <label class="form-label">Fréquence de Mise à Jour</label>
                                        <select class="form-select">
                                            <option value="realtime">Temps réel</option>
                                            <option value="hourly" selected>Horaire</option>
                                            <option value="daily">Quotidienne</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>
                                        Sauvegarder
                                    </button>
                                </form>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>Statistiques IA</h5>
                                <div class="list-group">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-search me-2 text-primary"></i>
                                            Recherches IA
                                        </div>
                                        <span class="badge bg-primary">1,247</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-bullseye me-2 text-success"></i>
                                            Matchings Réussis
                                        </div>
                                        <span class="badge bg-success">89%</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-dollar-sign me-2 text-warning"></i>
                                            Prédictions Salaires
                                        </div>
                                        <span class="badge bg-warning">92%</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-copy me-2 text-info"></i>
                                            Doublons Détectés
                                        </div>
                                        <span class="badge bg-info">23</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- AI Training Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="ai-card">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-graduation-cap me-2"></i>
                            Entraînement des Modèles IA
                        </h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Modèles Actifs</h5>
                                <div class="list-group">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-brain me-2 text-primary"></i>
                                            Modèle de Recherche
                                        </div>
                                        <div>
                                            <span class="badge bg-success me-2">v2.1</span>
                                            <small class="text-muted">95% précision</small>
                                        </div>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-bullseye me-2 text-success"></i>
                                            Modèle de Matching
                                        </div>
                                        <div>
                                            <span class="badge bg-success me-2">v1.8</span>
                                            <small class="text-muted">89% précision</small>
                                        </div>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-dollar-sign me-2 text-warning"></i>
                                            Modèle de Salaires
                                        </div>
                                        <div>
                                            <span class="badge bg-success me-2">v1.5</span>
                                            <small class="text-muted">92% précision</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>Actions d'Entraînement</h5>
                                <div class="d-grid gap-2">
                                    <button class="btn btn-outline-primary">
                                        <i class="fas fa-sync me-2"></i>
                                        Réentraîner Tous les Modèles
                                    </button>
                                    <button class="btn btn-outline-success">
                                        <i class="fas fa-upload me-2"></i>
                                        Importer Nouvelles Données
                                    </button>
                                    <button class="btn btn-outline-info">
                                        <i class="fas fa-chart-line me-2"></i>
                                        Analyser les Performances
                                    </button>
                                    <button class="btn btn-outline-warning">
                                        <i class="fas fa-download me-2"></i>
                                        Exporter les Modèles
                                    </button>
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
                        Configuration IA sauvegardée avec succès!
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
        
        // AI Training buttons
        document.querySelectorAll('.btn-outline-primary, .btn-outline-success, .btn-outline-info, .btn-outline-warning').forEach(btn => {
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
                        Action d'entraînement IA lancée avec succès!
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
