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
    <title>Enterprise Geographic Analytics - EMPLOIDB</title>
    
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
        
        .enterprise-status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .enterprise-status-badge.enterprise-info {
            background: var(--enterprise-info);
            color: white;
        }
        
        .enterprise-status-badge.enterprise-success {
            background: var(--enterprise-success);
            color: white;
        }
    </style>
</head>
<body style="background-color: #f8fafc; font-family: 'Inter', sans-serif;">

<!-- Include Admin Sidebar -->
<?php include 'includes/admin_sidebar.php'; ?>

<div class="container-fluid mt-4">
    <!-- Geographic Analytics Dashboard -->
    <div class="enterprise-content-card">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 style="color: var(--enterprise-primary); margin: 0;">
                    <i class="fas fa-globe me-2"></i>Enterprise Geographic Analytics
                </h5>
                <small class="text-muted">Analyse géographique et répartition des utilisateurs</small>
            </div>
            <div class="d-flex gap-2">
                <button class="enterprise-action-btn enterprise-info" onclick="refreshGeographicData()">
                    <i class="fas fa-sync-alt me-1"></i>Actualiser
                </button>
                <button class="enterprise-action-btn enterprise-success" onclick="exportGeographicData()">
                    <i class="fas fa-download me-1"></i>Exporter
                </button>
                <button class="enterprise-action-btn enterprise-warning" onclick="generateGeographicReport()">
                    <i class="fas fa-file-pdf me-1"></i>Rapport
                </button>
            </div>
        </div>
    </div>

    <!-- Geographic Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="enterprise-stat-card">
                <div class="enterprise-stat-number">5</div>
                <div class="enterprise-stat-label">Pays</div>
                <i class="fas fa-flag stat-icon"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="enterprise-stat-card">
                <div class="enterprise-stat-number">15</div>
                <div class="enterprise-stat-label">Villes</div>
                <i class="fas fa-city stat-icon"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="enterprise-stat-card">
                <div class="enterprise-stat-number">France</div>
                <div class="enterprise-stat-label">Pays Principal</div>
                <i class="fas fa-crown stat-icon"></i>
            </div>
        </div>
        <div class="col-md-3">
            <div class="enterprise-stat-card">
                <div class="enterprise-stat-number">Paris</div>
                <div class="enterprise-stat-label">Ville Principale</div>
                <i class="fas fa-map-marker-alt stat-icon"></i>
            </div>
        </div>
    </div>

    <!-- Geographic Charts -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="enterprise-content-card">
                <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
                    <i class="fas fa-chart-pie me-2"></i>Répartition par Pays
                </h5>
                <div style="height: 400px; background: var(--enterprise-gray-50); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center">
                        <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Graphique de répartition par pays</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="enterprise-content-card">
                <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
                    <i class="fas fa-chart-bar me-2"></i>Top Villes
                </h5>
                <div style="height: 400px; background: var(--enterprise-gray-50); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center">
                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Graphique des top villes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Geographic Data Tables -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="enterprise-content-card">
                <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
                    <i class="fas fa-table me-2"></i>Top Pays
                </h5>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead style="background: var(--enterprise-gray-50);">
                            <tr>
                                <th style="color: var(--enterprise-text-primary);">Pays</th>
                                <th style="color: var(--enterprise-text-primary);">Utilisateurs</th>
                                <th style="color: var(--enterprise-text-primary);">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong style="color: var(--enterprise-primary);">France</strong>
                                </td>
                                <td>450</td>
                                <td>
                                    <span class="enterprise-status-badge enterprise-info">45%</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong style="color: var(--enterprise-primary);">Canada</strong>
                                </td>
                                <td>200</td>
                                <td>
                                    <span class="enterprise-status-badge enterprise-info">20%</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong style="color: var(--enterprise-primary);">Belgique</strong>
                                </td>
                                <td>150</td>
                                <td>
                                    <span class="enterprise-status-badge enterprise-info">15%</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong style="color: var(--enterprise-primary);">Suisse</strong>
                                </td>
                                <td>100</td>
                                <td>
                                    <span class="enterprise-status-badge enterprise-info">10%</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong style="color: var(--enterprise-primary);">Autres</strong>
                                </td>
                                <td>100</td>
                                <td>
                                    <span class="enterprise-status-badge enterprise-info">10%</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="enterprise-content-card">
                <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
                    <i class="fas fa-table me-2"></i>Top Villes
                </h5>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead style="background: var(--enterprise-gray-50);">
                            <tr>
                                <th style="color: var(--enterprise-text-primary);">Ville</th>
                                <th style="color: var(--enterprise-text-primary);">Pays</th>
                                <th style="color: var(--enterprise-text-primary);">Utilisateurs</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong style="color: var(--enterprise-primary);">Paris</strong>
                                </td>
                                <td>France</td>
                                <td>
                                    <span class="enterprise-status-badge enterprise-success">200</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong style="color: var(--enterprise-primary);">Montréal</strong>
                                </td>
                                <td>Canada</td>
                                <td>
                                    <span class="enterprise-status-badge enterprise-success">120</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong style="color: var(--enterprise-primary);">Bruxelles</strong>
                                </td>
                                <td>Belgique</td>
                                <td>
                                    <span class="enterprise-status-badge enterprise-success">80</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong style="color: var(--enterprise-primary);">Genève</strong>
                                </td>
                                <td>Suisse</td>
                                <td>
                                    <span class="enterprise-status-badge enterprise-success">60</span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <strong style="color: var(--enterprise-primary);">Lyon</strong>
                                </td>
                                <td>France</td>
                                <td>
                                    <span class="enterprise-status-badge enterprise-success">50</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Geographic Tools -->
    <div class="enterprise-content-card">
        <h5 style="color: var(--enterprise-primary); margin-bottom: 1rem;">
            <i class="fas fa-tools me-2"></i>Outils Géographiques
        </h5>
        <div class="row">
            <div class="col-md-6">
                <div class="alert alert-info d-flex align-items-start">
                    <i class="fas fa-map fa-2x me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading">Carte Interactive</h6>
                        <p class="mb-1">Visualisez la répartition géographique sur une carte interactive.</p>
                        <button class="enterprise-action-btn enterprise-info" onclick="showInteractiveMap()">
                            <i class="fas fa-map me-1"></i>Afficher Carte
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-success d-flex align-items-start">
                    <i class="fas fa-chart-line fa-2x me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading">Tendances Régionales</h6>
                        <p class="mb-1">Analysez les tendances de croissance par région.</p>
                        <button class="enterprise-action-btn enterprise-success" onclick="analyzeRegionalTrends()">
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
function refreshGeographicData() {
    location.reload();
}

function exportGeographicData() {
    alert('Export des données géographiques...');
}

function generateGeographicReport() {
    alert('Génération du rapport géographique...');
}

function showInteractiveMap() {
    alert('Affichage de la carte interactive...');
}

function analyzeRegionalTrends() {
    alert('Analyse des tendances régionales...');
}
</script>

</body>
</html>


