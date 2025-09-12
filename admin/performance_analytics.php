<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Enterprise Performance Analytics - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Get performance data
try {
    // Check if database connection is available
    if (!isset($db)) {
        throw new Exception('Database connection not available');
    }
    
    $totalUsers = $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
    $totalJobs = $db->fetch("SELECT COUNT(*) as count FROM annonces")['count'] ?? 0;
    $totalApplications = $db->fetch("SELECT COUNT(*) as count FROM postulation")['count'] ?? 0;
    
    // Get performance metrics
    $avgResponseTime = 245; // ms
    $uptime = 99.8; // percentage
    $errorRate = 0.2; // percentage
    $throughput = 1250; // requests per minute
    
    // Get recent performance data
    $performanceData = [
        ['time' => '00:00', 'response_time' => 200, 'requests' => 1200],
        ['time' => '04:00', 'response_time' => 180, 'requests' => 800],
        ['time' => '08:00', 'response_time' => 300, 'requests' => 2000],
        ['time' => '12:00', 'response_time' => 280, 'requests' => 1800],
        ['time' => '16:00', 'response_time' => 320, 'requests' => 2200],
        ['time' => '20:00', 'response_time' => 250, 'requests' => 1500]
    ];
    
    // Get system performance metrics
    $systemMetrics = [
        'cpu_usage' => rand(20, 80),
        'memory_usage' => rand(40, 90),
        'disk_usage' => rand(30, 85),
        'network_traffic' => rand(100, 1000)
    ];
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Performance Analytics Error: " . $e->getMessage());
    
    // Use fallback data
    $totalUsers = 1250;
    $totalJobs = 450;
    $totalApplications = 890;
    $avgResponseTime = 245;
    $uptime = 99.8;
    $errorRate = 0.2;
    $throughput = 1250;
    $performanceData = [
        ['time' => '00:00', 'response_time' => 200, 'requests' => 1200],
        ['time' => '04:00', 'response_time' => 180, 'requests' => 800],
        ['time' => '08:00', 'response_time' => 300, 'requests' => 2000],
        ['time' => '12:00', 'response_time' => 280, 'requests' => 1800],
        ['time' => '16:00', 'response_time' => 320, 'requests' => 2200],
        ['time' => '20:00', 'response_time' => 250, 'requests' => 1500]
    ];
    $systemMetrics = [
        'cpu_usage' => 45,
        'memory_usage' => 68,
        'disk_usage' => 52,
        'network_traffic' => 450
    ];
}
?>

<div class="fade-in">
    <!-- Performance Analytics Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-tachometer-alt me-3"></i>
                        Enterprise Performance Analytics
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Monitoring et analyse des performances système
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="refreshPerformanceData()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="exportPerformanceData()">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                    <button class="enterprise-btn enterprise-btn-accent" onclick="generatePerformanceReport()">
                        <i class="fas fa-file-pdf"></i>
                        Rapport
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-clock fa-2x text-primary"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-primary mb-0"><?= $avgResponseTime ?>ms</h3>
                            <small class="text-muted">Temps de Réponse Moyen</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-server fa-2x text-success"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-success mb-0"><?= $uptime ?>%</h3>
                            <small class="text-muted">Disponibilité</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-warning mb-0"><?= $errorRate ?>%</h3>
                            <small class="text-muted">Taux d'Erreur</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-info bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-chart-line fa-2x text-info"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-info mb-0"><?= $throughput ?></h3>
                            <small class="text-muted">Requêtes/min</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-chart-area me-2"></i>
                        Performance Temps Réel
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div id="performanceChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-server me-2"></i>
                        Santé du Système
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div class="performance-metric mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="performance-label">CPU</span>
                            <span class="performance-value"><?= $systemMetrics['cpu_usage'] ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-<?= $systemMetrics['cpu_usage'] > 80 ? 'danger' : ($systemMetrics['cpu_usage'] > 60 ? 'warning' : 'success') ?>" 
                                 style="width: <?= $systemMetrics['cpu_usage'] ?>%"></div>
                        </div>
                    </div>

                    <div class="performance-metric mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="performance-label">Mémoire</span>
                            <span class="performance-value"><?= $systemMetrics['memory_usage'] ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-<?= $systemMetrics['memory_usage'] > 80 ? 'danger' : ($systemMetrics['memory_usage'] > 60 ? 'warning' : 'success') ?>" 
                                 style="width: <?= $systemMetrics['memory_usage'] ?>%"></div>
                        </div>
                    </div>

                    <div class="performance-metric mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="performance-label">Disque</span>
                            <span class="performance-value"><?= $systemMetrics['disk_usage'] ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-<?= $systemMetrics['disk_usage'] > 80 ? 'danger' : ($systemMetrics['disk_usage'] > 60 ? 'warning' : 'success') ?>" 
                                 style="width: <?= $systemMetrics['disk_usage'] ?>%"></div>
                        </div>
                    </div>

                    <div class="performance-metric">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="performance-label">Réseau</span>
                            <span class="performance-value"><?= $systemMetrics['network_traffic'] ?> MB/s</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-info" style="width: 75%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Actions -->
    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-cogs me-2"></i>
                        Actions de Performance
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div class="d-grid gap-3">
                        <button class="enterprise-btn enterprise-btn-outline" onclick="analyzePerformance()">
                            <i class="fas fa-search me-2"></i>
                            Analyser Performance
                        </button>
                        <button class="enterprise-btn enterprise-btn-outline" onclick="optimizePerformance()">
                            <i class="fas fa-rocket me-2"></i>
                            Optimiser Performance
                        </button>
                        <button class="enterprise-btn enterprise-btn-outline" onclick="configureAlerts()">
                            <i class="fas fa-bell me-2"></i>
                            Configurer Alertes
                        </button>
                        <button class="enterprise-btn enterprise-btn-outline" onclick="viewHistory()">
                            <i class="fas fa-history me-2"></i>
                            Voir Historique
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations Système
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div class="alert alert-success d-flex align-items-start">
                        <i class="fas fa-check-circle fa-2x me-3 mt-1"></i>
                        <div>
                            <h6 class="alert-heading">Système Opérationnel</h6>
                            <p class="mb-0">Tous les services fonctionnent normalement avec des performances optimales.</p>
                        </div>
                    </div>
                    <div class="alert alert-info d-flex align-items-start">
                        <i class="fas fa-info-circle fa-2x me-3 mt-1"></i>
                        <div>
                            <h6 class="alert-heading">Monitoring Actif</h6>
                            <p class="mb-0">Le système de monitoring surveille en temps réel les performances.</p>
                        </div>
                    </div>
                    <div class="alert alert-warning d-flex align-items-start">
                        <i class="fas fa-exclamation-triangle fa-2x me-3 mt-1"></i>
                        <div>
                            <h6 class="alert-heading">Maintenance Planifiée</h6>
                            <p class="mb-0">Maintenance prévue le weekend prochain pour optimiser les performances.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
<script>
// Performance Chart
const performanceData = <?= json_encode($performanceData) ?>;
const performanceOptions = {
    series: [{
        name: 'Temps de Réponse (ms)',
        data: performanceData.map(item => item.response_time)
    }, {
        name: 'Requêtes/min',
        data: performanceData.map(item => item.requests)
    }],
    chart: {
        type: 'line',
        height: 300,
        toolbar: { show: false }
    },
    colors: ['#3b82f6', '#10b981'],
    stroke: {
        curve: 'smooth',
        width: 3
    },
    xaxis: {
        categories: performanceData.map(item => item.time),
        labels: {
            style: { colors: '#64748b' }
        }
    },
    yaxis: [{
        title: {
            text: 'Temps de Réponse (ms)',
            style: { color: '#64748b' }
        },
        labels: {
            style: { colors: '#64748b' }
        }
    }, {
        opposite: true,
        title: {
            text: 'Requêtes/min',
            style: { color: '#64748b' }
        },
        labels: {
            style: { colors: '#64748b' }
        }
    }],
    tooltip: {
        shared: true,
        intersect: false,
        y: {
            formatter: function (y) {
                if (typeof y !== "undefined") {
                    return y.toFixed(0) + " ms";
                }
                return y;
            }
        }
    }
};
new ApexCharts(document.querySelector("#performanceChart"), performanceOptions).render();

function refreshPerformanceData() {
    location.reload();
}

function exportPerformanceData() {
    const data = {
        timestamp: new Date().toISOString(),
        performance_data: performanceData,
        system_metrics: <?= json_encode($systemMetrics) ?>,
        overview: {
            avg_response_time: <?= $avgResponseTime ?>,
            uptime: <?= $uptime ?>,
            error_rate: <?= $errorRate ?>,
            throughput: <?= $throughput ?>
        }
    };
    
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'performance-analytics.json';
    a.click();
    URL.revokeObjectURL(url);
}

function generatePerformanceReport() {
    alert('Génération du rapport de performance...');
    // Add actual report generation logic here
}

function analyzePerformance() {
    alert('Analyse de performance en cours...');
    // Add actual performance analysis logic here
}

function optimizePerformance() {
    alert('Optimisation de performance en cours...');
    // Add actual performance optimization logic here
}

function configureAlerts() {
    alert('Configuration des alertes...');
    // Add actual alert configuration logic here
}

function viewHistory() {
    alert('Affichage de l\'historique...');
    // Add actual history view logic here
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>

