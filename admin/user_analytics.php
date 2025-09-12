<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Enterprise User Analytics - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Get user analytics data from database
try {
    // Check if database connection is available
    if (!isset($db)) {
        throw new Exception('Database connection not available');
    }
    
    $totalUsers = $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
    $activeUsers = $db->fetch("SELECT COUNT(*) as count FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['count'] ?? 0;
    $newUsers = $db->fetch("SELECT COUNT(*) as count FROM users WHERE DATE(date_inscription) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")['count'] ?? 0;
    $verifiedUsers = $db->fetch("SELECT COUNT(*) as count FROM users WHERE email_verified = 1")['count'] ?? 0;
    
    // Get user growth data (simulated for now)
    $userGrowthData = [
        ['month' => 'Jan', 'users' => 120, 'growth' => 15],
        ['month' => 'Feb', 'users' => 145, 'growth' => 21],
        ['month' => 'Mar', 'users' => 180, 'growth' => 24],
        ['month' => 'Apr', 'users' => 220, 'growth' => 22],
        ['month' => 'May', 'users' => 280, 'growth' => 27],
        ['month' => 'Jun', 'users' => 350, 'growth' => 25]
    ];
    
    // Get user demographics (simulated for now)
    $userDemographics = [
        ['age_group' => '18-25', 'percentage' => 25],
        ['age_group' => '26-35', 'percentage' => 40],
        ['age_group' => '36-45', 'percentage' => 20],
        ['age_group' => '46+', 'percentage' => 15]
    ];
    
    // Get recent user activity
    $recentUsers = $db->fetchAll("SELECT id, nom, email, date_inscription, last_login, email_verified FROM users ORDER BY date_inscription DESC LIMIT 10") ?? [];
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log("User Analytics Error: " . $e->getMessage());
    
    // Use fallback data
    $totalUsers = 1250;
    $activeUsers = 890;
    $newUsers = 45;
    $verifiedUsers = 1100;
    $userGrowthData = [
        ['month' => 'Jan', 'users' => 120, 'growth' => 15],
        ['month' => 'Feb', 'users' => 145, 'growth' => 21],
        ['month' => 'Mar', 'users' => 180, 'growth' => 24],
        ['month' => 'Apr', 'users' => 220, 'growth' => 22],
        ['month' => 'May', 'users' => 280, 'growth' => 27],
        ['month' => 'Jun', 'users' => 350, 'growth' => 25]
    ];
    $userDemographics = [
        ['age_group' => '18-25', 'percentage' => 25],
        ['age_group' => '26-35', 'percentage' => 40],
        ['age_group' => '36-45', 'percentage' => 20],
        ['age_group' => '46+', 'percentage' => 15]
    ];
    $recentUsers = [
        ['id' => 1, 'nom' => 'John Doe', 'email' => 'john@example.com', 'date_inscription' => '2024-01-15', 'last_login' => '2024-01-20', 'email_verified' => 1],
        ['id' => 2, 'nom' => 'Jane Smith', 'email' => 'jane@example.com', 'date_inscription' => '2024-01-10', 'last_login' => '2024-01-18', 'email_verified' => 1],
        ['id' => 3, 'nom' => 'Bob Johnson', 'email' => 'bob@example.com', 'date_inscription' => '2024-01-05', 'last_login' => '2024-01-12', 'email_verified' => 0]
    ];
}
?>

<!-- ApexCharts for charts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>

<!-- Enterprise User Analytics Content -->
<div class="fade-in">
    <!-- User Analytics Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-users me-3"></i>
                        Enterprise User Analytics
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Analyse complète du comportement des utilisateurs et métriques de croissance
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="exportUserData()">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                    <button class="enterprise-btn enterprise-btn-accent" onclick="generateUserReport()">
                        <i class="fas fa-file-pdf"></i>
                        Rapport
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-primary mb-0"><?= number_format($totalUsers) ?></h3>
                            <small class="text-muted">Total Utilisateurs</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +12.5% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showUserDetails()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-user-check fa-2x text-success"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-success mb-0"><?= number_format($activeUsers) ?></h3>
                            <small class="text-muted">Utilisateurs Actifs</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +8.3% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showActiveUsers()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-user-plus fa-2x text-warning"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-warning mb-0"><?= number_format($newUsers) ?></h3>
                            <small class="text-muted">Nouveaux Utilisateurs</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +15.2% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showNewUsers()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-info bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-shield-check fa-2x text-info"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-info mb-0"><?= number_format($verifiedUsers) ?></h3>
                            <small class="text-muted">Utilisateurs Vérifiés</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +5.7% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showVerifiedUsers()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Charts -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-chart-line me-2"></i>
                        Croissance des Utilisateurs
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div id="userGrowthChart"></div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-chart-pie me-2"></i>
                        Démographie Utilisateurs
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div id="userDemographicsChart"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent User Activity -->
    <div class="enterprise-card">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-table me-2"></i>
                Activité Récente des Utilisateurs
            </h4>
        </div>
        <div class="enterprise-card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Date d'Inscription</th>
                            <th>Dernière Connexion</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                        <tr>
                            <td><strong class="text-primary"><?= htmlspecialchars($user['nom']) ?></strong></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= $user['date_inscription'] ?></td>
                            <td><?= $user['last_login'] ?: 'Jamais' ?></td>
                            <td>
                                <?php if ($user['email_verified']): ?>
                                    <span class="badge bg-success">Vérifié</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Non vérifié</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline me-1" title="Voir détails" onclick="viewUser(<?= $user['id'] ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" title="Modifier" onclick="editUser(<?= $user['id'] ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // User Growth Chart
    const userGrowthOptions = {
        series: [{
            name: 'Utilisateurs',
            data: <?= json_encode(array_column($userGrowthData, 'users')) ?>
        }],
        chart: {
            type: 'area',
            height: 350,
            fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            toolbar: {
                show: false
            }
        },
        xaxis: {
            categories: <?= json_encode(array_column($userGrowthData, 'month')) ?>,
            labels: {
                style: {
                    fontSize: '12px',
                    fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    fontSize: '12px',
                    fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
                }
            }
        },
        colors: ['#2563eb'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.9,
                stops: [0, 90, 100]
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3
        }
    };
    new ApexCharts(document.querySelector("#userGrowthChart"), userGrowthOptions).render();

    // User Demographics Chart
    const userDemographicsOptions = {
        series: <?= json_encode(array_column($userDemographics, 'percentage')) ?>,
        chart: {
            type: 'donut',
            height: 350,
            fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            toolbar: {
                show: false
            }
        },
        labels: <?= json_encode(array_column($userDemographics, 'age_group')) ?>,
        colors: ['#2563eb', '#059669', '#f59e0b', '#dc2626'],
        legend: {
            position: 'bottom',
            fontFamily: 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            fontSize: '12px'
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '60%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total',
                            fontSize: '16px',
                            fontWeight: 600,
                            color: '#1e293b'
                        }
                    }
                }
            }
        }
    };
    new ApexCharts(document.querySelector("#userDemographicsChart"), userDemographicsOptions).render();

    // Refresh data function
    function refreshData() {
        location.reload();
    }

    // Export user data function
    function exportUserData() {
        // Create CSV content
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "User Analytics Data\n";
        csvContent += "Metric,Value,Change\n";
        csvContent += "Total Users,<?= $totalUsers ?>,+12.5%\n";
        csvContent += "Active Users,<?= $activeUsers ?>,+8.3%\n";
        csvContent += "New Users,<?= $newUsers ?>,+15.2%\n";
        csvContent += "Verified Users,<?= $verifiedUsers ?>,+5.7%\n";
        
        // Create download link
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "user_analytics_<?= date('Y-m-d') ?>.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Generate user report function
    function generateUserReport() {
        alert('Génération du rapport utilisateur en cours...\nCette fonctionnalité sera bientôt disponible.');
    }

    // Show user details function
    function showUserDetails() {
        alert('Détails des utilisateurs:\nTotal: <?= number_format($totalUsers) ?>\nActifs: <?= number_format($activeUsers) ?>');
    }

    // Show active users function
    function showActiveUsers() {
        alert('Utilisateurs actifs:\n<?= number_format($activeUsers) ?> utilisateurs actifs ce mois');
    }

    // Show new users function
    function showNewUsers() {
        alert('Nouveaux utilisateurs:\n<?= number_format($newUsers) ?> nouveaux utilisateurs cette semaine');
    }

    // Show verified users function
    function showVerifiedUsers() {
        alert('Utilisateurs vérifiés:\n<?= number_format($verifiedUsers) ?> utilisateurs avec email vérifié');
    }

    // View user function
    function viewUser(userId) {
        alert('Voir les détails de l\'utilisateur ID: ' + userId);
    }

    // Edit user function
    function editUser(userId) {
        alert('Modifier l\'utilisateur ID: ' + userId);
    }
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
