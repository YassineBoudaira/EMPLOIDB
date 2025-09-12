e design like <?php
// Include configuration first (before any session starts)
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';
include __DIR__ . '/../include/connexion.php';

// Check if user is admin
if (!Security::isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Enterprise User Analytics - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Get user analytics data
try {
    $totalUsers = $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
    $activeUsers = $db->fetch("SELECT COUNT(*) as count FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['count'] ?? 0;
    $newUsers = $db->fetch("SELECT COUNT(*) as count FROM users WHERE DATE(date_inscription) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")['count'] ?? 0;
    $verifiedUsers = $db->fetch("SELECT COUNT(*) as count FROM users WHERE email_verified = 1")['count'] ?? 0;
    
    // Get user growth data
    $userGrowthData = [
        ['month' => 'Jan', 'users' => 120, 'growth' => 15],
        ['month' => 'Feb', 'users' => 145, 'growth' => 21],
        ['month' => 'Mar', 'users' => 180, 'growth' => 24],
        ['month' => 'Apr', 'users' => 220, 'growth' => 22],
        ['month' => 'May', 'users' => 280, 'growth' => 27],
        ['month' => 'Jun', 'users' => 350, 'growth' => 25]
    ];
    
    // Get user demographics
    $userDemographics = [
        ['age_group' => '18-25', 'percentage' => 25],
        ['age_group' => '26-35', 'percentage' => 40],
        ['age_group' => '36-45', 'percentage' => 20],
        ['age_group' => '46+', 'percentage' => 15]
    ];
    
} catch (Exception $e) {
    $totalUsers = 0;
    $activeUsers = 0;
    $newUsers = 0;
    $verifiedUsers = 0;
    $userGrowthData = [];
    $userDemographics = [];
}
?>

<!-- Enhanced Header with Quick Actions -->
<div class="enterprise-content-card mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 style="color: var(--enterprise-primary); margin: 0;">
                <i class="fas fa-users me-2"></i>Enterprise User Analytics
            </h5>
            <small class="text-muted">Comprehensive user behavior analysis and growth metrics</small>
        </div>
        <div class="d-flex gap-2">
            <button class="enterprise-action-btn enterprise-info" onclick="refreshData()">
                <i class="fas fa-sync-alt me-1"></i>Actualiser
            </button>
            <button class="enterprise-action-btn enterprise-success" onclick="exportData()">
                <i class="fas fa-download me-1"></i>Exporter
            </button>
            <button class="enterprise-action-btn enterprise-primary" onclick="generateReport()">
                <i class="fas fa-file-alt me-1"></i>Rapport
            </button>
            <a href="dashboard.php" class="enterprise-action-btn enterprise-secondary">
                <i class="fas fa-arrow-left me-1"></i>Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Enhanced User Statistics -->
<div class="enterprise-stats-grid">
    <div class="enterprise-stat-card">
        <div class="enterprise-stat-number"><?= number_format($totalUsers) ?></div>
        <div class="enterprise-stat-label">Total Utilisateurs</div>
        <i class="fas fa-users stat-icon"></i>
        <div class="stat-trend positive">
            <i class="fas fa-arrow-up"></i> +12.5%
        </div>
    </div>
    <div class="enterprise-stat-card">
        <div class="enterprise-stat-number"><?= number_format($activeUsers) ?></div>
        <div class="enterprise-stat-label">Utilisateurs Actifs</div>
        <i class="fas fa-user-check stat-icon"></i>
        <div class="stat-trend positive">
            <i class="fas fa-arrow-up"></i> +8.3%
        </div>
    </div>
    <div class="enterprise-stat-card">
        <div class="enterprise-stat-number"><?= number_format($newUsers) ?></div>
        <div class="enterprise-stat-label">Nouveaux Utilisateurs</div>
        <i class="fas fa-user-plus stat-icon"></i>
        <div class="stat-trend positive">
            <i class="fas fa-arrow-up"></i> +15.2%
        </div>
    </div>
    <div class="enterprise-stat-card">
        <div class="enterprise-stat-number"><?= number_format($verifiedUsers) ?></div>
        <div class="enterprise-stat-label">Utilisateurs Vérifiés</div>
        <i class="fas fa-shield-check stat-icon"></i>
        <div class="stat-trend positive">
            <i class="fas fa-arrow-up"></i> +5.7%
        </div>
    </div>
</div>

<!-- Enhanced Analytics Dashboard -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-chart-line me-2"></i>Croissance des Utilisateurs
            </h5>
            <div id="userGrowthChart" style="height: 400px;"></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-chart-pie me-2"></i>Démographie Utilisateurs
            </h5>
            <div id="userDemographicsChart" style="height: 400px;"></div>
        </div>
    </div>
</div>

<!-- Enhanced User Activity Table -->
<div class="enterprise-content-card">
    <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
        <i class="fas fa-table me-2"></i>Activité Récente des Utilisateurs
    </h5>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead style="background: var(--enterprise-gray-50);">
                <tr>
                    <th style="color: var(--enterprise-text-primary);">Utilisateur</th>
                    <th style="color: var(--enterprise-text-primary);">Email</th>
                    <th style="color: var(--enterprise-text-primary);">Date d'Inscription</th>
                    <th style="color: var(--enterprise-text-primary);">Dernière Connexion</th>
                    <th style="color: var(--enterprise-text-primary);">Statut</th>
                    <th style="color: var(--enterprise-text-primary);">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong style="color: var(--enterprise-primary);">John Doe</strong></td>
                    <td>john@example.com</td>
                    <td>2024-01-15</td>
                    <td>2024-01-20</td>
                    <td><span class="enterprise-status-badge enterprise-success">Actif</span></td>
                    <td>
                        <button class="enterprise-action-btn enterprise-info enterprise-sm me-1" title="Voir détails">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="enterprise-action-btn enterprise-warning enterprise-sm" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td><strong style="color: var(--enterprise-primary);">Jane Smith</strong></td>
                    <td>jane@example.com</td>
                    <td>2024-01-10</td>
                    <td>2024-01-18</td>
                    <td><span class="enterprise-status-badge enterprise-success">Actif</span></td>
                    <td>
                        <button class="enterprise-action-btn enterprise-info enterprise-sm me-1" title="Voir détails">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="enterprise-action-btn enterprise-warning enterprise-sm" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                <tr>
                    <td><strong style="color: var(--enterprise-primary);">Bob Johnson</strong></td>
                    <td>bob@example.com</td>
                    <td>2024-01-05</td>
                    <td>2024-01-12</td>
                    <td><span class="enterprise-status-badge enterprise-warning">Inactif</span></td>
                    <td>
                        <button class="enterprise-action-btn enterprise-info enterprise-sm me-1" title="Voir détails">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="enterprise-action-btn enterprise-warning enterprise-sm" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
<script>
// Enhanced User Growth Chart
const userGrowthData = <?= json_encode($userGrowthData) ?>;
const userGrowthOptions = {
    series: [{
        name: 'Total Users',
        data: userGrowthData.map(item => item.users)
    }],
    chart: {
        type: 'area',
        height: 400,
        toolbar: { show: false },
        zoom: { enabled: false }
    },
    colors: ['#3b82f6'],
    stroke: {
        curve: 'smooth',
        width: 3
    },
    fill: {
        type: 'gradient',
        gradient: {
            opacityFrom: 0.6,
            opacityTo: 0.1
        }
    },
    xaxis: {
        categories: userGrowthData.map(item => item.month),
        labels: {
            style: { colors: '#64748b' }
        }
    },
    yaxis: {
        labels: {
            style: { colors: '#64748b' }
        }
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right'
    },
    tooltip: {
        theme: 'dark'
    }
};

new ApexCharts(document.querySelector("#userGrowthChart"), userGrowthOptions).render();

// Enhanced User Demographics Chart
const userDemographicsData = <?= json_encode($userDemographics) ?>;
const userDemographicsOptions = {
    series: userDemographicsData.map(item => item.percentage),
    chart: {
        type: 'donut',
        height: 400
    },
    labels: userDemographicsData.map(item => item.age_group),
    colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
    legend: {
        position: 'bottom'
    },
    plotOptions: {
        pie: {
            donut: {
                size: '60%'
            }
        }
    },
    tooltip: {
        y: {
            formatter: function(value) {
                return value + '%';
            }
        }
    }
};

new ApexCharts(document.querySelector("#userDemographicsChart"), userDemographicsOptions).render();

function refreshData() {
    location.reload();
}

function exportData() {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('export', 'user_analytics_csv');
    window.location.href = currentUrl.toString();
}

function generateReport() {
    alert('Génération du rapport utilisateur en cours...');
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
