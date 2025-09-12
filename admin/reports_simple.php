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
    <title>Enterprise Reports - EMPLOIDB</title>
    
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
            margin-bottom: 1rem;
        }
        
        .progress {
            background-color: var(--enterprise-gray-200);
            border-radius: 4px;
        }
        
        .progress-bar {
            border-radius: 4px;
            transition: width 0.6s ease;
        }
        
        .enterprise-user-list, .enterprise-job-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .enterprise-user-item, .enterprise-job-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--enterprise-gray-200);
        }
        
        .enterprise-user-item:last-child, .enterprise-job-item:last-child {
            border-bottom: none;
        }
        
        .enterprise-user-avatar, .enterprise-job-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--enterprise-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        
        .enterprise-job-icon {
            background: var(--enterprise-success);
        }
        
        .enterprise-user-info, .enterprise-job-info {
            flex: 1;
        }
        
        .enterprise-user-name, .enterprise-job-title {
            font-weight: 600;
            color: var(--enterprise-text-primary);
            margin-bottom: 0.25rem;
        }
        
        .enterprise-user-email, .enterprise-job-company {
            font-size: 0.875rem;
            color: var(--enterprise-text-secondary);
            margin-bottom: 0.25rem;
        }
        
        .enterprise-user-date, .enterprise-job-date {
            font-size: 0.75rem;
            color: var(--enterprise-text-secondary);
        }
    </style>
</head>
<body style="background-color: #f8fafc; font-family: 'Inter', sans-serif;">

<!-- Include Admin Sidebar -->
<?php include 'includes/admin_sidebar.php'; ?>

<div class="container-fluid mt-4">
    <!-- Reports Dashboard -->
    <div class="enterprise-content-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 style="color: var(--enterprise-primary); margin: 0;">
                    <i class="fas fa-chart-bar me-2"></i>Enterprise Reports
                </h5>
                <small class="text-muted">Rapports et analyses complètes du système</small>
            </div>
            <div class="d-flex gap-2">
                <button class="enterprise-action-btn enterprise-info" onclick="refreshReports()">
                    <i class="fas fa-sync-alt me-1"></i>Actualiser
                </button>
                <button class="enterprise-action-btn enterprise-success" onclick="exportReport()">
                    <i class="fas fa-download me-1"></i>Exporter
                </button>
                <button class="enterprise-action-btn enterprise-warning" onclick="generateReport()">
                    <i class="fas fa-file-pdf me-1"></i>Générer PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Overview -->
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
                <div class="enterprise-stat-number">450</div>
                <div class="enterprise-stat-label">Total Offres</div>
                <i class="fas fa-briefcase stat-icon"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="enterprise-stat-card">
                <div class="enterprise-stat-number">3,450</div>
                <div class="enterprise-stat-label">Total Candidatures</div>
                <i class="fas fa-clipboard-list stat-icon"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="enterprise-stat-card">
                <div class="enterprise-stat-number">180</div>
                <div class="enterprise-stat-label">Total Employeurs</div>
                <i class="fas fa-building stat-icon"></i>
            </div>
        </div>
    </div>

    <!-- Monthly Statistics -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="enterprise-content-card">
                <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
                    <i class="fas fa-chart-line me-2"></i>Statistiques Mensuelles
                </h5>
                <div style="height: 400px; background: var(--enterprise-gray-50); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center">
                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Graphique des statistiques mensuelles</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="enterprise-content-card">
                <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
                    <i class="fas fa-trending-up me-2"></i>Croissance (30j)
                </h5>
                
                <div class="enterprise-metric-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="color: var(--enterprise-text-primary);">Nouveaux Utilisateurs</span>
                        <span style="color: var(--enterprise-primary); font-weight: 600;">+45</span>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: 75%; background-color: var(--enterprise-primary);"></div>
                    </div>
                </div>
                
                <div class="enterprise-metric-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="color: var(--enterprise-text-primary);">Nouvelles Offres</span>
                        <span style="color: var(--enterprise-success); font-weight: 600;">+23</span>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: 60%; background-color: var(--enterprise-success);"></div>
                    </div>
                </div>
                
                <div class="enterprise-metric-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="color: var(--enterprise-text-primary);">Nouvelles Candidatures</span>
                        <span style="color: var(--enterprise-info); font-weight: 600;">+156</span>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: 85%; background-color: var(--enterprise-info);"></div>
                    </div>
                </div>
                
                <div class="enterprise-metric-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="color: var(--enterprise-text-primary);">Employeurs Actifs</span>
                        <span style="color: var(--enterprise-warning); font-weight: 600;">165</span>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: 92%; background-color: var(--enterprise-warning);"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="enterprise-content-card">
                <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
                    <i class="fas fa-users me-2"></i>Utilisateurs Récents
                </h5>
                <div class="enterprise-user-list">
                    <div class="enterprise-user-item">
                        <div class="enterprise-user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="enterprise-user-info">
                            <div class="enterprise-user-name">Jean Dupont</div>
                            <div class="enterprise-user-email">jean.dupont@email.com</div>
                            <div class="enterprise-user-date">15/01/2024 14:30</div>
                        </div>
                    </div>
                    <div class="enterprise-user-item">
                        <div class="enterprise-user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="enterprise-user-info">
                            <div class="enterprise-user-name">Marie Martin</div>
                            <div class="enterprise-user-email">marie.martin@email.com</div>
                            <div class="enterprise-user-date">14/01/2024 16:45</div>
                        </div>
                    </div>
                    <div class="enterprise-user-item">
                        <div class="enterprise-user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="enterprise-user-info">
                            <div class="enterprise-user-name">Pierre Durand</div>
                            <div class="enterprise-user-email">pierre.durand@email.com</div>
                            <div class="enterprise-user-date">13/01/2024 09:15</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="enterprise-content-card">
                <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
                    <i class="fas fa-briefcase me-2"></i>Offres Récentes
                </h5>
                <div class="enterprise-job-list">
                    <div class="enterprise-job-item">
                        <div class="enterprise-job-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="enterprise-job-info">
                            <div class="enterprise-job-title">Développeur Full Stack</div>
                            <div class="enterprise-job-company">TechCorp</div>
                            <div class="enterprise-job-date">15/01/2024</div>
                        </div>
                    </div>
                    <div class="enterprise-job-item">
                        <div class="enterprise-job-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="enterprise-job-info">
                            <div class="enterprise-job-title">Chef de Projet</div>
                            <div class="enterprise-job-company">InnovSoft</div>
                            <div class="enterprise-job-date">14/01/2024</div>
                        </div>
                    </div>
                    <div class="enterprise-job-item">
                        <div class="enterprise-job-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="enterprise-job-info">
                            <div class="enterprise-job-title">Designer UX/UI</div>
                            <div class="enterprise-job-company">CreativeLab</div>
                            <div class="enterprise-job-date">13/01/2024</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Tools -->
    <div class="enterprise-content-card">
        <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
            <i class="fas fa-tools me-2"></i>Outils de Rapports
        </h5>
        <div class="row">
            <div class="col-md-6">
                <div class="alert alert-info d-flex align-items-start">
                    <i class="fas fa-chart-bar fa-2x me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading">Rapport Complet</h6>
                        <p class="mb-1">Générez un rapport complet avec toutes les statistiques.</p>
                        <button class="enterprise-action-btn enterprise-info" onclick="generateFullReport()">
                            <i class="fas fa-file-alt me-1"></i>Générer
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-success d-flex align-items-start">
                    <i class="fas fa-chart-line fa-2x me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading">Rapport de Performance</h6>
                        <p class="mb-1">Analysez les performances et les tendances.</p>
                        <button class="enterprise-action-btn enterprise-success" onclick="generatePerformanceReport()">
                            <i class="fas fa-tachometer-alt me-1"></i>Analyser
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function refreshReports() {
    location.reload();
}

function exportReport() {
    alert('Export du rapport...');
}

function generateReport() {
    alert('Génération du rapport PDF...');
}

function generateFullReport() {
    alert('Génération du rapport complet...');
}

function generatePerformanceReport() {
    alert('Génération du rapport de performance...');
}
</script>

</body>
</html>


