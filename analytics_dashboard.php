<?php
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';

// Check if user is logged in
if (!Security::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Set custom title for this page
$page_title = "Tableau de Bord Analytique | JobMaroc.ma";

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'candidate';

// Get user statistics
$user_stats = [];

if ($user_role === 'candidate') {
    // Candidate statistics
    $user_stats['total_applications'] = $db->fetch("SELECT COUNT(*) as count FROM postulation WHERE user_id = ?", [$user_id])['count'];
    $user_stats['saved_jobs'] = $db->fetch("SELECT COUNT(*) as count FROM saved_jobs WHERE user_id = ?", [$user_id])['count'];
    $user_stats['job_alerts'] = $db->fetch("SELECT COUNT(*) as count FROM job_alerts WHERE user_id = ?", [$user_id])['count'];
    $user_stats['profile_views'] = $db->fetch("SELECT COUNT(*) as count FROM profile_views WHERE profile_user_id = ?", [$user_id])['count'];
    
    // Application status breakdown
    $application_statuses = $db->fetchAll("
        SELECT status, COUNT(*) as count 
        FROM postulation 
        WHERE user_id = ? 
        GROUP BY status
    ", [$user_id]);
    
    $user_stats['application_statuses'] = $application_statuses;
    
    // Recent applications
    $recent_applications = $db->fetchAll("
        SELECT p.*, a.titre, a.entreprise, a.date_a
        FROM postulation p
        JOIN annonces a ON p.annonce_id = a.id
        WHERE p.user_id = ?
        ORDER BY p.applied_at DESC
        LIMIT 5
    ", [$user_id]);
    
    $user_stats['recent_applications'] = $recent_applications;
    
    // Job search activity
    $search_activity = $db->fetchAll("
        SELECT DATE(created_at) as date, COUNT(*) as searches
        FROM search_history
        WHERE user_id = ?
        GROUP BY DATE(created_at)
        ORDER BY date DESC
        LIMIT 7
    ", [$user_id]);
    
    $user_stats['search_activity'] = $search_activity;
    
    // Monthly applications trend
    $monthly_applications = $db->fetchAll("
        SELECT DATE_FORMAT(applied_at, '%Y-%m') as month, COUNT(*) as count
        FROM postulation
        WHERE user_id = ?
        GROUP BY DATE_FORMAT(applied_at, '%Y-%m')
        ORDER BY month DESC
        LIMIT 6
    ", [$user_id]);
    
    $user_stats['monthly_applications'] = $monthly_applications;
    
} elseif ($user_role === 'employer') {
    // Employer statistics
    $employer_id = $db->fetch("SELECT id FROM employers WHERE user_id = ?", [$user_id])['id'] ?? 0;
    
    if ($employer_id) {
        $user_stats['total_jobs'] = $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE employer_id = ?", [$employer_id])['count'];
        $user_stats['active_jobs'] = $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE employer_id = ? AND status = 'active'", [$employer_id])['count'];
        $user_stats['total_applications'] = $db->fetch("
            SELECT COUNT(*) as count 
            FROM postulation p
            JOIN annonces a ON p.annonce_id = a.id
            WHERE a.employer_id = ?
        ", [$employer_id])['count'];
        $user_stats['total_views'] = $db->fetch("
            SELECT SUM(views_count) as count 
            FROM annonces 
            WHERE employer_id = ?
        ", [$employer_id])['count'] ?? 0;
        
        // Job performance
        $job_performance = $db->fetchAll("
            SELECT a.titre, a.views_count, a.applications_count, a.status, a.date_a
            FROM annonces a
            WHERE a.employer_id = ?
            ORDER BY a.date_a DESC
            LIMIT 10
        ", [$employer_id]);
        
        $user_stats['job_performance'] = $job_performance;
    }
}

include 'frontoffice/include/header2.php';
?>

<div class="container-fluid bg-white p-0">
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Navbar End -->
    <?php include 'frontoffice/include/menu2.php'; ?>
    <!-- Header End -->

    <!-- Analytics Content -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- Professional Header Section -->
                    <div class="professional-section">
                        <h3><i class="fas fa-chart-line"></i>Tableau de Bord Analytique</h3>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="enhanced-card">
                                    <div class="enhanced-card-header">
                                        <h4 class="enhanced-card-title">
                                            <?php if ($user_role === 'candidate'): ?>
                                                Analyse de Mes Activités
                                            <?php else: ?>
                                                Performance des Offres d'Emploi
                                            <?php endif; ?>
                                        </h4>
                                        <div class="enhanced-card-actions">
                                            <button class="btn-action btn-action-primary" onclick="refreshAnalytics()">
                                                <i class="fas fa-sync-alt"></i>Actualiser
                                            </button>
                                            <button class="btn-action btn-action-success" onclick="exportAnalytics()">
                                                <i class="fas fa-download"></i>Exporter
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted">
                                            <?php if ($user_role === 'candidate'): ?>
                                                Données de vos activités de recherche d'emploi
                                            <?php else: ?>
                                                Données de performance de vos offres d'emploi
                                            <?php endif; ?>
                                        </span>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary active" onclick="switchPeriod('week')">
                                                <i class="fas fa-calendar-week"></i> Semaine
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" onclick="switchPeriod('month')">
                                                <i class="fas fa-calendar-alt"></i> Mois
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" onclick="switchPeriod('year')">
                                                <i class="fas fa-calendar"></i> Année
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="action-buttons">
                                    <button class="btn-action btn-action-info" onclick="window.location.href='user_profile.php'">
                                        <i class="fas fa-user"></i>Mon Profil
                                    </button>
                                    <button class="btn-action btn-action-warning" onclick="openJobAlert()">
                                        <i class="fas fa-bell"></i>Créer une Alerte
                                    </button>
                                    <button class="btn-action btn-action-success" onclick="openProfileEdit()">
                                        <i class="fas fa-user-edit"></i>Modifier le Profil
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Statistics Section -->
                    <div class="professional-section">
                        <h3><i class="fas fa-chart-bar"></i>Statistiques Principales</h3>
                        <div class="row">
                            <?php if ($user_role === 'candidate'): ?>
                                <!-- Candidate Statistics -->
                                <div class="col-md-3">
                                    <div class="stats-card">
                                        <div class="stats-icon color-scheme-primary">
                                            <i class="fas fa-paper-plane"></i>
                                        </div>
                                        <div class="stats-number"><?= $user_stats['total_applications'] ?></div>
                                        <div class="stats-label">Candidatures</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stats-card">
                                        <div class="stats-icon color-scheme-success">
                                            <i class="fas fa-heart"></i>
                                        </div>
                                        <div class="stats-number"><?= $user_stats['saved_jobs'] ?></div>
                                        <div class="stats-label">Emplois Sauvegardés</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stats-card">
                                        <div class="stats-icon color-scheme-warning">
                                            <i class="fas fa-bell"></i>
                                        </div>
                                        <div class="stats-number"><?= $user_stats['job_alerts'] ?></div>
                                        <div class="stats-label">Alertes Emploi</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stats-card">
                                        <div class="stats-icon color-scheme-info">
                                            <i class="fas fa-eye"></i>
                                        </div>
                                        <div class="stats-number"><?= $user_stats['profile_views'] ?></div>
                                        <div class="stats-label">Vues du Profil</div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Employer Statistics -->
                                <div class="col-md-3">
                                    <div class="stats-card">
                                        <div class="stats-icon color-scheme-primary">
                                            <i class="fas fa-briefcase"></i>
                                        </div>
                                        <div class="stats-number"><?= $user_stats['total_jobs'] ?? 0 ?></div>
                                        <div class="stats-label">Offres Publiées</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stats-card">
                                        <div class="stats-icon color-scheme-success">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="stats-number"><?= $user_stats['active_jobs'] ?? 0 ?></div>
                                        <div class="stats-label">Offres Actives</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stats-card">
                                        <div class="stats-icon color-scheme-warning">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="stats-number"><?= $user_stats['total_applications'] ?? 0 ?></div>
                                        <div class="stats-label">Candidatures Reçues</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="stats-card">
                                        <div class="stats-icon color-scheme-info">
                                            <i class="fas fa-eye"></i>
                                        </div>
                                        <div class="stats-number"><?= $user_stats['total_views'] ?? 0 ?></div>
                                        <div class="stats-label">Vues Totales</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                    </div>
                </div>

                <!-- Charts and Analytics Section -->
                <div class="professional-section">
                    <h3><i class="fas fa-chart-area"></i>Analyses et Graphiques</h3>
                    <div class="row">
                        <!-- Line Chart -->
                        <div class="col-lg-8 mb-4">
                            <div class="enhanced-card">
                                <div class="enhanced-card-header">
                                    <h4 class="enhanced-card-title">
                                        <?php if ($user_role === 'candidate'): ?>
                                            <i class="fas fa-chart-line me-2"></i>Évolution des Candidatures
                                        <?php else: ?>
                                            <i class="fas fa-chart-line me-2"></i>Performance des Offres
                                        <?php endif; ?>
                                    </h4>
                                    <div class="enhanced-card-actions">
                                        <button class="btn-action btn-action-primary" onclick="refreshChart()">
                                            <i class="fas fa-sync-alt"></i>Actualiser
                                        </button>
                                    </div>
                                </div>
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="analyticsChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Pie Chart -->
                        <div class="col-lg-4 mb-4">
                            <div class="enhanced-card">
                                <div class="enhanced-card-header">
                                    <h4 class="enhanced-card-title">
                                        <?php if ($user_role === 'candidate'): ?>
                                            <i class="fas fa-pie-chart me-2"></i>Statut des Candidatures
                                        <?php else: ?>
                                            <i class="fas fa-pie-chart me-2"></i>Répartition des Offres
                                        <?php endif; ?>
                                    </h4>
                                </div>
                                <div class="chart-container" style="position: relative; height: 400px;">
                                    <canvas id="statusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Section -->
                <div class="professional-section">
                    <h3><i class="fas fa-history"></i>Activité Récente</h3>
                    <div class="row">
                        <div class="col-12">
                            <div class="enhanced-card">
                                <div class="enhanced-card-header">
                                    <h4 class="enhanced-card-title">
                                        <i class="fas fa-history me-2"></i>Activité Récente
                                    </h4>
                                    <div class="enhanced-card-actions">
                                        <button class="btn-action btn-action-primary" onclick="refreshActivity()">
                                            <i class="fas fa-sync-alt"></i>Actualiser
                                        </button>
                                    </div>
                                </div>
                            <?php if ($user_role === 'candidate' && !empty($user_stats['recent_applications'])): ?>
                                <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Emploi</th>
                                                <th>Entreprise</th>
                                                <th>Statut</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($user_stats['recent_applications'] as $app): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($app['titre']) ?></strong>
                                                    </td>
                                                    <td><?= htmlspecialchars($app['entreprise']) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= getStatusBadgeClass($app['status']) ?>">
                                                            <?= getStatusLabel($app['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($app['applied_at'])) ?></td>
                                                    <td>
                                                        <a href="enhanced_job_details.php?id=<?= $app['annonce_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php elseif ($user_role === 'employer' && !empty($user_stats['job_performance'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Offre d'Emploi</th>
                                                <th>Vues</th>
                                                <th>Candidatures</th>
                                                <th>Statut</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($user_stats['job_performance'] as $job): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($job['titre']) ?></strong>
                                                    </td>
                                                    <td><?= $job['views_count'] ?? 0 ?></td>
                                                    <td><?= $job['applications_count'] ?? 0 ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $job['status'] === 'active' ? 'success' : 'secondary' ?>">
                                                            <?= $job['status'] === 'active' ? 'Active' : 'Inactive' ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="enhanced_job_details.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Aucune activité récente</h6>
                                    <p class="text-muted">Commencez à utiliser la plateforme pour voir vos statistiques</p>
                                </div>
                            <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #007bff, #0056b3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 1.5rem;
    color: white;
}

.card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    border: none;
}

.table {
    border-radius: 8px;
    overflow: hidden;
}

.table th {
    background-color: #f8f9fa;
    border: none;
    font-weight: 600;
    color: #495057;
}

.table td {
    border: none;
    vertical-align: middle;
}

.badge {
    border-radius: 6px;
    font-weight: 500;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

@media (max-width: 768px) {
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Analytics Chart
const analyticsCtx = document.getElementById('analyticsChart').getContext('2d');
const analyticsChart = new Chart(analyticsCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($user_stats['monthly_applications'] ?? [], 'month')) ?>,
        datasets: [{
            label: 'Candidatures',
            data: <?= json_encode(array_column($user_stats['monthly_applications'] ?? [], 'count')) ?>,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0,0,0,0.1)'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($user_stats['application_statuses'] ?? [], 'status')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($user_stats['application_statuses'] ?? [], 'count')) ?>,
            backgroundColor: [
                '#007bff',
                '#28a745',
                '#ffc107',
                '#dc3545',
                '#6c757d',
                '#17a2b8'
            ],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>

<?php
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'applied': return 'info';
        case 'viewed': return 'warning';
        case 'shortlisted': return 'secondary';
        case 'interviewed': return 'dark';
        case 'hired': return 'success';
        case 'rejected': return 'danger';
        default: return 'secondary';
    }
}

function getStatusLabel($status) {
    switch ($status) {
        case 'applied': return 'Postulée';
        case 'viewed': return 'Vue';
        case 'shortlisted': return 'Sélectionnée';
        case 'interviewed': return 'Entretien';
        case 'hired': return 'Embauchée';
        case 'rejected': return 'Refusée';
        default: return 'Inconnu';
    }
}
?>

<!-- Professional UX/UI Styles -->
<style>
/* Professional Sections */
.professional-section {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.professional-section h3 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.professional-section h3 i {
    color: #007bff;
    font-size: 1.2em;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 20px;
}

.btn-action {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.btn-action-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.btn-action-success {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    color: white;
}

.btn-action-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: #212529;
}

.btn-action-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
}

/* Enhanced Cards */
.enhanced-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.enhanced-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.enhanced-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f8f9fa;
}

.enhanced-card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.enhanced-card-actions {
    display: flex;
    gap: 10px;
}

/* Statistics Cards */
.stats-card {
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.5rem;
    color: white;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.stats-label {
    color: #6c757d;
    font-weight: 500;
}

/* Color Schemes */
.color-scheme-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
}

.color-scheme-success {
    background: linear-gradient(135deg, #28a745, #1e7e34);
}

.color-scheme-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
}

.color-scheme-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
}

.color-scheme-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
}

/* Popup Styles */
.popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
    z-index: 9999;
    display: none;
    animation: fadeIn 0.3s ease;
}

.popup-container {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 10000;
    animation: slideIn 0.3s ease;
}

.popup-header {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    padding: 25px 30px;
    border-radius: 20px 20px 0 0;
    position: relative;
}

.popup-header h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.popup-close {
    position: absolute;
    top: 20px;
    right: 25px;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
}

.popup-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

.popup-body {
    padding: 30px;
}

.popup-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    padding: 20px 30px;
    background: #f8f9fa;
    border-radius: 0 0 20px 20px;
}

.btn-popup {
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-popup-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.btn-popup-primary:hover {
    background: linear-gradient(135deg, #0056b3, #004085);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
}

.btn-popup-secondary {
    background: #6c757d;
    color: white;
}

.btn-popup-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translate(-50%, -60%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

@media (max-width: 768px) {
    .professional-section {
        padding: 20px;
    }
    
    .enhanced-card {
        padding: 20px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
}
</style>





<!-- Enhanced JavaScript Functions -->
<script>
function openPopup(popupId) {
    document.getElementById(popupId).style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closePopup(popupId) {
    document.getElementById(popupId).style.display = 'none';
    document.body.style.overflow = 'auto';
}

function openProfileEdit() {
    openPopup('profileEditPopup');
}

function openJobAlert() {
    loadDomains();
    loadCities();
    loadContrats();
    openPopup('jobAlertPopup');
}

function saveProfile() {
    const form = document.getElementById('profileEditForm');
    const formData = new FormData(form);
    
    fetch('ajax/update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Profil mis à jour avec succès!');
            closePopup('profileEditPopup');
            location.reload();
        } else {
            alert(data.message || 'Erreur lors de la mise à jour');
        }
    })
    .catch(error => {
        alert('Erreur de connexion');
    });
}

function createJobAlert() {
    const form = document.getElementById('jobAlertForm');
    const formData = new FormData(form);
    
    fetch('ajax/create_job_alert.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Alerte créée avec succès!');
            closePopup('jobAlertPopup');
            form.reset();
        } else {
            alert(data.message || 'Erreur lors de la création');
        }
    })
    .catch(error => {
        alert('Erreur de connexion');
    });
}

function refreshAnalytics() {
    location.reload();
}

function exportAnalytics() {
    // Create CSV export functionality
    const userRole = '<?= $user_role ?>';
    let csv = '';
    
    if (userRole === 'candidate') {
        csv = 'Métrique,Valeur\n';
        csv += `Candidatures,${<?= $user_stats['total_applications'] ?>}\n`;
        csv += `Emplois Sauvegardés,${<?= $user_stats['saved_jobs'] ?>}\n`;
        csv += `Alertes Emploi,${<?= $user_stats['job_alerts'] ?>}\n`;
        csv += `Vues du Profil,${<?= $user_stats['profile_views'] ?>}\n`;
    } else {
        csv = 'Métrique,Valeur\n';
        csv += `Offres d'Emploi,${<?= $user_stats['total_jobs'] ?? 0 ?>}\n`;
        csv += `Offres Actives,${<?= $user_stats['active_jobs'] ?? 0 ?>}\n`;
        csv += `Candidatures Reçues,${<?= $user_stats['total_applications'] ?? 0 ?>}\n`;
        csv += `Vues Totales,${<?= $user_stats['total_views'] ?? 0 ?>}\n`;
    }
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'analytics_export.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

function switchPeriod(period) {
    const buttons = document.querySelectorAll('.btn-group .btn');
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Here you would typically reload data for the selected period
    // For now, we'll just show a notification
    showNotification(`Période changée vers: ${period}`, 'info');
}

function loadCities() {
    fetch('ajax/get_cities.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('select[name="ville_id"]');
                selects.forEach(select => {
                    select.innerHTML = '<option value="">Sélectionner une ville</option>';
                    data.cities.forEach(city => {
                        select.innerHTML += `<option value="${city.id}">${city.nom}</option>`;
                    });
                });
            }
        });
}

function loadDomains() {
    fetch('ajax/get_domains.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('select[name="domaine_id"]');
                selects.forEach(select => {
                    select.innerHTML = '<option value="">Sélectionner un domaine</option>';
                    data.domains.forEach(domain => {
                        select.innerHTML += `<option value="${domain.id}">${domain.nom}</option>`;
                    });
                });
            }
        });
}

function loadContrats() {
    fetch('ajax/get_contrats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('select[name="contrat_id"]');
                selects.forEach(select => {
                    select.innerHTML = '<option value="">Sélectionner un contrat</option>';
                    data.contrats.forEach(contrat => {
                        select.innerHTML += `<option value="${contrat.id}">${contrat.nom}</option>`;
                    });
                });
            }
        });
}

// Close popup when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('popup-overlay')) {
        e.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
});

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Refresh chart
function refreshChart() {
    location.reload();
}

// Refresh activity
function refreshActivity() {
    location.reload();
}

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    
    // Ensure all popups are hidden by default
    const popups = document.querySelectorAll('.popup-overlay');
    popups.forEach(popup => {
        popup.style.display = 'none';
    });
});

// Chart initialization
function initializeCharts() {
    const userRole = '<?= $user_role ?>';
    
    // Line Chart
    const lineCtx = document.getElementById('analyticsChart').getContext('2d');
    const lineChart = new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($user_stats['monthly_applications'] ?? [], 'month')) ?>,
            datasets: [{
                label: userRole === 'candidate' ? 'Candidatures' : 'Vues',
                data: <?= json_encode(array_column($user_stats['monthly_applications'] ?? [], 'count')) ?>,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                }
            }
        }
    });
    
    // Pie Chart
    const pieCtx = document.getElementById('statusChart').getContext('2d');
    const pieChart = new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($user_stats['application_statuses'] ?? [], 'status')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($user_stats['application_statuses'] ?? [], 'count')) ?>,
                backgroundColor: [
                    '#2563eb',
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#8b5cf6',
                    '#06b6d4'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom'
                }
            }
        }
    });
}
</script>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


?>

<!-- Professional UX/UI Styles -->
<style>
/* Professional Sections */
.professional-section {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: 1px solid #e2e8f0;
}

.professional-section h3 {
    color: var(--dark-color);
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 20px;
    text-align: center;
}

.professional-section h3 i {
    color: var(--primary-color);
    margin-right: 15px;
}

.enhanced-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
    margin-bottom: 20px;
}

.enhanced-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f1f5f9;
}

.enhanced-card-title {
    color: var(--dark-color);
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
}

.enhanced-card-actions {
    display: flex;
    gap: 10px;
}

.btn-action {
    padding: 10px 20px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-action-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
    color: white;
}

.btn-action-success {
    background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
    color: white;
}

.btn-action-warning {
    background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
    color: white;
}

.btn-action-info {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    color: white;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.stats-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.5rem;
    color: white;
}

.color-scheme-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
}

.color-scheme-success {
    background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
    color: white;
}

.color-scheme-warning {
    background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
    color: white;
}

.color-scheme-info {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    color: white;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 5px;
}

.stats-label {
    color: #64748b;
    font-weight: 500;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

/* Chart container styles */
.chart-container {
    position: relative;
    height: 400px;
    margin: 20px 0;
}

/* Table styles */
.table {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.table thead th {
    background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
    color: white;
    border: none;
    font-weight: 600;
    padding: 15px;
}

.table tbody td {
    padding: 15px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
}

.table tbody tr:hover {
    background-color: #f8fafc;
}

/* Badge styles */
.badge {
    padding: 8px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8rem;
}

/* Responsive design */
@media (max-width: 768px) {
    .professional-section {
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .professional-section h3 {
        font-size: 1.5rem;
    }
    
    .enhanced-card {
        padding: 20px;
    }
    
    .enhanced-card-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .chart-container {
        height: 300px;
    }
    
    .stats-card {
        margin-bottom: 15px;
    }
}

/* CSS Variables */
:root {
    --primary-color: #2563eb;
    --secondary-color: #64748b;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --dark-color: #1e293b;
    --light-color: #f8fafc;
}
</style>

<!-- Profile Edit Popup -->
<div id="profileEditPopup" class="popup-overlay" style="display: none;">
    <div class="popup-content">
        <div class="popup-header">
            <h4><i class="fas fa-user-edit me-2"></i>Modifier le Profil</h4>
            <button type="button" class="popup-close" onclick="closePopup('profileEditPopup')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="popup-body">
            <form id="profileEditForm" class="popup-form">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" name="nom" placeholder="Votre nom" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Prénom *</label>
                            <input type="text" class="form-control" name="prenom" placeholder="Votre prénom" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" name="telephone" placeholder="Votre téléphone">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Ville</label>
                            <select class="form-select" name="ville_id">
                                <option value="">Sélectionner une ville</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Compétences</label>
                    <textarea class="form-control" name="skills" rows="3" placeholder="Vos compétences (séparées par des virgules)"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Expérience (années)</label>
                    <input type="number" class="form-control" name="experience_years" min="0" max="50" placeholder="Nombre d'années d'expérience">
                </div>
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea class="form-control" name="bio" rows="4" placeholder="Présentez-vous brièvement"></textarea>
                </div>
            </form>
        </div>
        <div class="popup-actions">
            <button type="button" class="btn-popup btn-popup-secondary" onclick="closePopup('profileEditPopup')">
                <i class="fas fa-times me-2"></i>Annuler
            </button>
            <button type="button" class="btn-popup btn-popup-primary" onclick="saveProfile()">
                <i class="fas fa-save me-2"></i>Sauvegarder
            </button>
        </div>
    </div>
</div>

<!-- Job Alert Popup -->
<div id="jobAlertPopup" class="popup-overlay" style="display: none;">
    <div class="popup-content">
        <div class="popup-header">
            <h4><i class="fas fa-bell me-2"></i>Créer une Alerte Emploi</h4>
            <button type="button" class="popup-close" onclick="closePopup('jobAlertPopup')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="popup-body">
            <form id="jobAlertForm" class="popup-form">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                <div class="form-group">
                    <label class="form-label">Nom de l'Alerte *</label>
                    <input type="text" class="form-control" name="alert_name" placeholder="Ex: Développeur Web Casablanca" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mots-clés</label>
                    <input type="text" class="form-control" name="keywords" placeholder="Ex: PHP, JavaScript, React">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Domaine</label>
                            <select class="form-select" name="domaine_id">
                                <option value="">Tous les domaines</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Ville</label>
                            <select class="form-select" name="ville_id">
                                <option value="">Toutes les villes</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Type de Contrat</label>
                            <select class="form-select" name="contrat_id">
                                <option value="">Tous les contrats</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Fréquence</label>
                            <select class="form-select" name="frequency" required>
                                <option value="daily">Quotidienne</option>
                                <option value="weekly">Hebdomadaire</option>
                                <option value="monthly">Mensuelle</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="popup-actions">
            <button type="button" class="btn-popup btn-popup-secondary" onclick="closePopup('jobAlertPopup')">
                <i class="fas fa-times me-2"></i>Annuler
            </button>
            <button type="button" class="btn-popup btn-popup-primary" onclick="createJobAlert()">
                <i class="fas fa-bell me-2"></i>Créer l'Alerte
            </button>
        </div>
    </div>
</div>

<!-- Popup Styles -->
<style>
.popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.popup-content {
    background: white;
    border-radius: 15px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 30px;
    border-bottom: 1px solid #e2e8f0;
    background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
    color: white;
    border-radius: 15px 15px 0 0;
}

.popup-header h4 {
    margin: 0;
    font-weight: 600;
}

.popup-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.3s ease;
}

.popup-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.popup-body {
    padding: 30px;
}

.popup-form .form-group {
    margin-bottom: 20px;
}

.popup-form .form-label {
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 8px;
    display: block;
}

.popup-form .form-control,
.popup-form .form-select {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 15px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.popup-form .form-control:focus,
.popup-form .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    outline: none;
}

.popup-actions {
    display: flex;
    gap: 15px;
    padding: 20px 30px;
    border-top: 1px solid #e2e8f0;
    justify-content: flex-end;
}

.btn-popup {
    padding: 12px 24px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-popup-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
    color: white;
}

.btn-popup-secondary {
    background: #e2e8f0;
    color: var(--dark-color);
}

.btn-popup:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

@media (max-width: 768px) {
    .popup-content {
        width: 95%;
        margin: 10px;
    }
    
    .popup-header,
    .popup-body,
    .popup-actions {
        padding: 15px 20px;
    }
    
    .popup-actions {
        flex-direction: column;
    }
    
    .btn-popup {
        width: 100%;
        justify-content: center;
    }
}
</style>

        </div>
    </div>
</div>

<?php include 'frontoffice/include/footer2.php'; ?>
