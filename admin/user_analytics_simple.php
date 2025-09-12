<?php
// Include configuration first (before any session starts)
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';
include __DIR__ . '/../include/connexion.php';

// Check if user is admin
if (!Security::isAdmin()) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise User Analytics - EMPLOIDB</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --enterprise-primary: #1e40af;
            --enterprise-success: #10b981;
            --enterprise-warning: #f59e0b;
            --enterprise-danger: #ef4444;
            --enterprise-info: #06b6d4;
            --enterprise-gray-50: #f8fafc;
            --enterprise-gray-200: #e2e8f0;
            --enterprise-text-primary: #1e293b;
            --enterprise-text-secondary: #64748b;
        }
        
        .enterprise-content-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .enterprise-stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .enterprise-stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--enterprise-primary);
            margin-bottom: 0.5rem;
        }
        
        .enterprise-stat-label {
            font-size: 0.875rem;
            color: var(--enterprise-text-secondary);
            margin-bottom: 1rem;
        }
        
        .stat-icon {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            color: var(--enterprise-primary);
            opacity: 0.2;
        }
        
        .enterprise-action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .enterprise-action-btn.enterprise-primary {
            background: var(--enterprise-primary);
            color: white;
        }
        
        .enterprise-action-btn.enterprise-info {
            background: var(--enterprise-info);
            color: white;
        }
        
        .enterprise-action-btn.enterprise-success {
            background: var(--enterprise-success);
            color: white;
        }
        
        .enterprise-action-btn.enterprise-warning {
            background: var(--enterprise-warning);
            color: white;
        }
        
        .enterprise-metric-item {
            background: var(--enterprise-gray-50);
            border-radius: 8px;
            padding: 1rem;
            border-left: 4px solid var(--enterprise-primary);
        }
        
        .enterprise-trend-item {
            background: var(--enterprise-gray-50);
            border-radius: 8px;
            padding: 1rem;
        }
        
        .enterprise-trend-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: white;
        }
        
        .enterprise-trend-icon.enterprise-success {
            background: var(--enterprise-success);
        }
        
        .enterprise-trend-icon.enterprise-info {
            background: var(--enterprise-info);
        }
        
        .enterprise-trend-icon.enterprise-warning {
            background: var(--enterprise-warning);
        }
        
        .enterprise-trend-icon.enterprise-primary {
            background: var(--enterprise-primary);
        }
        
        .enterprise-trend-content {
            flex: 1;
        }
        
        .enterprise-trend-label {
            font-size: 0.875rem;
            color: var(--enterprise-text-secondary);
            margin-bottom: 0.25rem;
        }
        
        .enterprise-trend-value {
            font-weight: 600;
            color: var(--enterprise-text-primary);
        }
    </style>
</head>
<body style="background-color: #f8fafc; font-family: 'Inter', sans-serif;">

<!-- Include Admin Sidebar -->
<?php include 'includes/admin_sidebar.php'; ?>

<div class="container-fluid mt-4">
    <!-- User Analytics Dashboard -->
    <div class="enterprise-content-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 style="color: var(--enterprise-primary); margin: 0;">
                    <i class="fas fa-users me-2"></i>Enterprise User Analytics
                </h5>
                <small class="text-muted">Analyse approfondie du comportement utilisateur</small>
            </div>
            <div class="d-flex gap-2">
                <button class="enterprise-action-btn enterprise-info" onclick="refreshUserData()">
                    <i class="fas fa-sync-alt me-1"></i>Actualiser
                </button>
                <button class="enterprise-action-btn enterprise-success" onclick="exportUserData()">
                    <i class="fas fa-download me-1"></i>Exporter
                </button>
                <button class="enterprise-action-btn enterprise-warning" onclick="generateUserReport()">
                    <i class="fas fa-file-pdf me-1"></i>Rapport
                </button>
            </div>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="enterprise-stat-card">
                <div class="enterprise-stat-number">1,250</div>
                <div class="enterprise-stat-label">Total Utilisateurs</div>
                <i class="fas fa-users stat-icon"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="enterprise-stat-card">
                <div class="enterprise-stat-number">890</div>
                <div class="enterprise-stat-label">Utilisateurs Actifs</div>
                <i class="fas fa-user-check stat-icon"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="enterprise-stat-card">
                <div class="enterprise-stat-number">45</div>
                <div class="enterprise-stat-label">Nouveaux (7j)</div>
                <i class="fas fa-user-plus stat-icon"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="enterprise-stat-card">
                <div class="enterprise-stat-number">1,180</div>
                <div class="enterprise-stat-label">Vérifiés</div>
                <i class="fas fa-user-shield stat-icon"></i>
            </div>
        </div>
    </div>

    <!-- User Growth & Demographics -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="enterprise-content-card">
                <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
                    <i class="fas fa-chart-line me-2"></i>Croissance Utilisateurs
                </h5>
                <div style="height: 400px; background: var(--enterprise-gray-50); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Graphique de croissance des utilisateurs</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="enterprise-content-card">
                <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
                    <i class="fas fa-chart-pie me-2"></i>Démographie Utilisateurs
                </h5>
                <div style="height: 300px; background: var(--enterprise-gray-50); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center">
                        <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Répartition démographique</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Engagement Metrics -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="enterprise-content-card">
                <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
                    <i class="fas fa-chart-bar me-2"></i>Métriques d'Engagement
                </h5>
                
                <div class="enterprise-metric-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="color: var(--enterprise-text-primary);">Total Candidatures</span>
                        <span style="color: var(--enterprise-primary); font-weight: 600;">3,450</span>
                    </div>
                </div>
                
                <div class="enterprise-metric-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="color: var(--enterprise-text-primary);">Candidats Actifs</span>
                        <span style="color: var(--enterprise-primary); font-weight: 600;">890</span>
                    </div>
                </div>
                
                <div class="enterprise-metric-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="color: var(--enterprise-text-primary);">Moy. Candidatures/User</span>
                        <span style="color: var(--enterprise-primary); font-weight: 600;">2.76</span>
                    </div>
                </div>
                
                <div class="enterprise-metric-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="color: var(--enterprise-text-primary);">Taux de Conversion</span>
                        <span style="color: var(--enterprise-primary); font-weight: 600;">71.2%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="enterprise-content-card">
                <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
                    <i class="fas fa-trending-up me-2"></i>Tendances Utilisateurs
                </h5>
                
                <div class="enterprise-trend-item mb-3">
                    <div class="d-flex align-items-center">
                        <div class="enterprise-trend-icon enterprise-success">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <div class="enterprise-trend-content">
                            <div class="enterprise-trend-label">Croissance Mensuelle</div>
                            <div class="enterprise-trend-value">+25%</div>
                        </div>
                    </div>
                </div>
                
                <div class="enterprise-trend-item mb-3">
                    <div class="d-flex align-items-center">
                        <div class="enterprise-trend-icon enterprise-info">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="enterprise-trend-content">
                            <div class="enterprise-trend-label">Nouveaux Utilisateurs</div>
                            <div class="enterprise-trend-value">+45 cette semaine</div>
                        </div>
                    </div>
                </div>
                
                <div class="enterprise-trend-item mb-3">
                    <div class="d-flex align-items-center">
                        <div class="enterprise-trend-icon enterprise-warning">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="enterprise-trend-content">
                            <div class="enterprise-trend-label">Taux d'Activation</div>
                            <div class="enterprise-trend-value">71.2%</div>
                        </div>
                    </div>
                </div>
                
                <div class="enterprise-trend-item mb-3">
                    <div class="d-flex align-items-center">
                        <div class="enterprise-trend-icon enterprise-primary">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="enterprise-trend-content">
                            <div class="enterprise-trend-label">Taux de Vérification</div>
                            <div class="enterprise-trend-value">94.4%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Analytics Tools -->
    <div class="enterprise-content-card">
        <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
            <i class="fas fa-tools me-2"></i>Outils Analytics
        </h5>
        <div class="row">
            <div class="col-md-6">
                <div class="alert alert-info d-flex align-items-start">
                    <i class="fas fa-chart-pie fa-2x me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading">Analyse Démographique</h6>
                        <p class="mb-1">Analysez la répartition des utilisateurs par âge et localisation.</p>
                        <button class="enterprise-action-btn enterprise-info" onclick="analyzeDemographics()">
                            <i class="fas fa-chart-pie me-1"></i>Analyser
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-success d-flex align-items-start">
                    <i class="fas fa-chart-line fa-2x me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading">Tendances Utilisateurs</h6>
                        <p class="mb-1">Visualisez les tendances d'inscription et d'activité.</p>
                        <button class="enterprise-action-btn enterprise-success" onclick="analyzeTrends()">
                            <i class="fas fa-chart-line me-1"></i>Analyser
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function refreshUserData() {
    location.reload();
}

function exportUserData() {
    alert('Export des données utilisateurs...');
}

function generateUserReport() {
    alert('Génération du rapport utilisateurs...');
}

function analyzeDemographics() {
    alert('Analyse démographique...');
}

function analyzeTrends() {
    alert('Analyse des tendances...');
}
</script>

</body>
</html>


