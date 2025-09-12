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
    <title>Enterprise Performance Analytics - EMPLOIDB</title>
    
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
        }
        
        .progress {
            background-color: var(--enterprise-gray-200);
            border-radius: 4px;
        }
        
        .progress-bar {
            border-radius: 4px;
            transition: width 0.6s ease;
        }
    </style>
</head>
<body style="background-color: #f8fafc; font-family: 'Inter', sans-serif;">

<!-- Include Admin Sidebar -->
<?php include 'includes/admin_sidebar.php'; ?>

<div class="container-fluid mt-4">
    <!-- Performance Analytics Dashboard -->
    <div class="enterprise-content-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 style="color: var(--enterprise-primary); margin: 0;">
                    <i class="fas fa-tachometer-alt me-2"></i>Enterprise Performance Analytics
                </h5>
                <small class="text-muted">Monitoring et analyse des performances système</small>
            </div>
            <div class="d-flex gap-2">
                <button class="enterprise-action-btn enterprise-info" onclick="refreshPerformanceData()">
                    <i class="fas fa-sync-alt me-1"></i>Actualiser
                </button>
                <button class="enterprise-action-btn enterprise-success" onclick="exportPerformanceData()">
                    <i class="fas fa-download me-1"></i>Exporter
                </button>
                <button class="enterprise-action-btn enterprise-warning" onclick="generatePerformanceReport()">
                    <i class="fas fa-file-pdf me-1"></i>Rapport
                </button>
            </div>
        </div>
    </div>

    <!-- Performance Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="enterprise-stat-card">
                <div class="enterprise-stat-number">245ms</div>
                <div class="enterprise-stat-label">Temps de Réponse Moyen</div>
                <i class="fas fa-clock stat-icon"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="enterprise-stat-card">
                <div class="enterprise-stat-number">99.8%</div>
                <div class="enterprise-stat-label">Disponibilité</div>
                <i class="fas fa-server stat-icon"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="enterprise-stat-card">
                <div class="enterprise-stat-number">0.2%</div>
                <div class="enterprise-stat-label">Taux d'Erreur</div>
                <i class="fas fa-exclamation-triangle stat-icon"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="enterprise-stat-card">
                <div class="enterprise-stat-number">1,250</div>
                <div class="enterprise-stat-label">Débit (req/min)</div>
                <i class="fas fa-chart-line stat-icon"></i>
            </div>
        </div>
    </div>

    <!-- System Performance Metrics -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="enterprise-content-card">
                <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
                    <i class="fas fa-chart-line me-2"></i>Performance en Temps Réel
                </h5>
                <div style="height: 400px; background: var(--enterprise-gray-50); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Graphique de performance en temps réel</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="enterprise-content-card">
                <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
                    <i class="fas fa-server me-2"></i>Métriques Système
                </h5>
                
                <div class="enterprise-metric-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="color: var(--enterprise-text-primary);">CPU</span>
                        <span style="color: var(--enterprise-primary); font-weight: 600;">65%</span>
                    </div>
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: 65%; background-color: var(--enterprise-primary);"></div>
                    </div>
                </div>
                
                <div class="enterprise-metric-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="color: var(--enterprise-text-primary);">Mémoire</span>
                        <span style="color: var(--enterprise-primary); font-weight: 600;">78%</span>
                    </div>
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: 78%; background-color: var(--enterprise-success);"></div>
                    </div>
                </div>
                
                <div class="enterprise-metric-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="color: var(--enterprise-text-primary);">Disque</span>
                        <span style="color: var(--enterprise-primary); font-weight: 600;">45%</span>
                    </div>
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: 45%; background-color: var(--enterprise-warning);"></div>
                    </div>
                </div>
                
                <div class="enterprise-metric-item mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="color: var(--enterprise-text-primary);">Réseau</span>
                        <span style="color: var(--enterprise-primary); font-weight: 600;">250 MB/s</span>
                    </div>
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: 25%; background-color: var(--enterprise-info);"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Tools -->
    <div class="enterprise-content-card">
        <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
            <i class="fas fa-tools me-2"></i>Outils de Performance
        </h5>
        <div class="row">
            <div class="col-md-6">
                <div class="alert alert-info d-flex align-items-start">
                    <i class="fas fa-chart-line fa-2x me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading">Analyse de Performance</h6>
                        <p class="mb-1">Analysez les tendances de performance et identifiez les goulots d'étranglement.</p>
                        <button class="enterprise-action-btn enterprise-info" onclick="analyzePerformance()">
                            <i class="fas fa-search me-1"></i>Analyser
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-success d-flex align-items-start">
                    <i class="fas fa-magic fa-2x me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading">Optimisation Automatique</h6>
                        <p class="mb-1">Optimisez automatiquement les performances du système.</p>
                        <button class="enterprise-action-btn enterprise-success" onclick="optimizePerformance()">
                            <i class="fas fa-magic me-1"></i>Optimiser
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function refreshPerformanceData() {
    location.reload();
}

function exportPerformanceData() {
    alert('Export des données de performance...');
}

function generatePerformanceReport() {
    alert('Génération du rapport de performance...');
}

function analyzePerformance() {
    alert('Analyse de performance en cours...');
}

function optimizePerformance() {
    alert('Optimisation de performance en cours...');
}
</script>

</body>
</html>


