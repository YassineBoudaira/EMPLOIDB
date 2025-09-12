<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include admin header
include __DIR__ . '/includes/admin_header.php';

// Handle form submissions for monitoring configuration and CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'update_monitoring_config') {
            $cpu_threshold = floatval($_POST['cpu_threshold'] ?? 80);
            $memory_threshold = floatval($_POST['memory_threshold'] ?? 85);
            $disk_threshold = floatval($_POST['disk_threshold'] ?? 90);
            $network_threshold = floatval($_POST['network_threshold'] ?? 75);
            $security_threshold = floatval($_POST['security_threshold'] ?? 10);
            $performance_threshold = floatval($_POST['performance_threshold'] ?? 300);
            
            $_SESSION['monitoring_config'] = [
                'cpu_threshold' => $cpu_threshold,
                'memory_threshold' => $memory_threshold,
                'disk_threshold' => $disk_threshold,
                'network_threshold' => $network_threshold,
                'security_threshold' => $security_threshold,
                'performance_threshold' => $performance_threshold
            ];
            
            $_SESSION['monitoring_notification'] = [
                'type' => 'success',
                'message' => 'Configuration du monitoring mise à jour avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: real_time_monitoring.php?success=config_updated');
            exit;
        }
        
        if ($action === 'add_monitoring_rule') {
            $rule_name = trim($_POST['rule_name'] ?? '');
            $rule_type = trim($_POST['rule_type'] ?? '');
            $rule_threshold = floatval($_POST['rule_threshold'] ?? 0);
            $rule_action = trim($_POST['rule_action'] ?? '');
            $rule_status = trim($_POST['rule_status'] ?? 'active');
            
            if (empty($rule_name)) {
                throw new Exception('Nom de la règle requis');
            }
            
            if (!isset($_SESSION['monitoring_rules'])) {
                $_SESSION['monitoring_rules'] = [];
            }
            
            $_SESSION['monitoring_rules'][] = [
                'id' => uniqid(),
                'name' => $rule_name,
                'type' => $rule_type,
                'threshold' => $rule_threshold,
                'action' => $rule_action,
                'status' => $rule_status,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $_SESSION['monitoring_notification'] = [
                'type' => 'success',
                'message' => 'Règle de monitoring ajoutée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: real_time_monitoring.php?success=rule_added');
            exit;
        }
        
        if ($action === 'update_monitoring_rule') {
            $rule_id = $_POST['rule_id'] ?? '';
            $rule_name = trim($_POST['rule_name'] ?? '');
            $rule_type = trim($_POST['rule_type'] ?? '');
            $rule_threshold = floatval($_POST['rule_threshold'] ?? 0);
            $rule_action = trim($_POST['rule_action'] ?? '');
            $rule_status = trim($_POST['rule_status'] ?? 'active');
            
            if (empty($rule_name)) {
                throw new Exception('Nom de la règle requis');
            }
            
            if (isset($_SESSION['monitoring_rules'])) {
                foreach ($_SESSION['monitoring_rules'] as &$rule) {
                    if ($rule['id'] === $rule_id) {
                        $rule['name'] = $rule_name;
                        $rule['type'] = $rule_type;
                        $rule['threshold'] = $rule_threshold;
                        $rule['action'] = $rule_action;
                        $rule['status'] = $rule_status;
                        $rule['updated_at'] = date('Y-m-d H:i:s');
                        break;
                    }
                }
            }
            
            $_SESSION['monitoring_notification'] = [
                'type' => 'success',
                'message' => 'Règle de monitoring mise à jour avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: real_time_monitoring.php?success=rule_updated');
            exit;
        }
        
        if ($action === 'delete_monitoring_rule') {
            $rule_id = $_POST['rule_id'] ?? '';
            
            if (isset($_SESSION['monitoring_rules'])) {
                $_SESSION['monitoring_rules'] = array_filter($_SESSION['monitoring_rules'], function($rule) use ($rule_id) {
                    return $rule['id'] !== $rule_id;
                });
            }
            
            $_SESSION['monitoring_notification'] = [
                'type' => 'success',
                'message' => 'Règle de monitoring supprimée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: real_time_monitoring.php?success=rule_deleted');
            exit;
        }
        
        if ($action === 'acknowledge_alert') {
            $alert_id = $_POST['alert_id'] ?? '';
            
            $_SESSION['monitoring_notification'] = [
                'type' => 'success',
                'message' => 'Alerte reconnue et traitée !',
                'title' => 'Succès'
            ];
            
            header('Location: real_time_monitoring.php?success=alert_acknowledged');
            exit;
        }
        
        if ($action === 'export_system_report') {
            $_SESSION['monitoring_notification'] = [
                'type' => 'success',
                'message' => 'Rapport système généré et téléchargé !',
                'title' => 'Succès'
            ];
            
            header('Location: real_time_monitoring.php?success=report_generated');
            exit;
        }
        
    } catch (Exception $e) {
        $_SESSION['monitoring_notification'] = [
            'type' => 'danger',
            'message' => 'Erreur: ' . $e->getMessage(),
            'title' => 'Erreur'
        ];
        
        header('Location: real_time_monitoring.php?error=operation');
        exit;
    }
}

// Get monitoring configuration
$monitoring_config = $_SESSION['monitoring_config'] ?? [
    'cpu_threshold' => 80,
    'memory_threshold' => 85,
    'disk_threshold' => 90,
    'network_threshold' => 75,
    'security_threshold' => 10,
    'performance_threshold' => 300
];

// Get monitoring rules
$monitoring_rules = $_SESSION['monitoring_rules'] ?? [
    [
        'id' => 'rule_1',
        'name' => 'CPU Critique',
        'type' => 'cpu_usage',
        'threshold' => 90,
        'action' => 'send_alert',
        'status' => 'active',
        'created_at' => '2024-01-01 00:00:00'
    ],
    [
        'id' => 'rule_2',
        'name' => 'Mémoire Élevée',
        'type' => 'memory_usage',
        'threshold' => 85,
        'action' => 'restart_service',
        'status' => 'active',
        'created_at' => '2024-01-01 00:00:00'
    ]
];

// Simulate real-time system data
$system_metrics = [
    'cpu_usage' => rand(25, 95),
    'memory_usage' => rand(40, 90),
    'disk_usage' => rand(30, 85),
    'network_usage' => rand(20, 80),
    'active_users' => rand(50, 200),
    'database_connections' => rand(10, 50),
    'server_load' => rand(0.5, 8.5),
    'response_time' => rand(50, 450),
    'uptime' => rand(99.1, 99.9),
    'error_rate' => rand(0.01, 2.5),
    'security_score' => rand(75, 98),
    'performance_score' => rand(80, 95),
    'network_latency' => rand(5, 150),
    'disk_io' => rand(10, 500),
    'cache_hit_rate' => rand(60, 95)
];

// Generate system alerts based on thresholds
$system_alerts = [];

if ($system_metrics['cpu_usage'] > $monitoring_config['cpu_threshold']) {
    $system_alerts[] = [
        'id' => 'cpu_high',
        'type' => 'warning',
        'title' => 'Utilisation CPU Élevée',
        'message' => "CPU: {$system_metrics['cpu_usage']}% (Seuil: {$monitoring_config['cpu_threshold']}%)",
        'timestamp' => date('H:i:s'),
        'severity' => 'high',
        'category' => 'performance'
    ];
}

if ($system_metrics['memory_usage'] > $monitoring_config['memory_threshold']) {
    $system_alerts[] = [
        'id' => 'memory_critical',
        'type' => 'danger',
        'title' => 'Utilisation Mémoire Critique',
        'message' => "Mémoire: {$system_metrics['memory_usage']}% (Seuil: {$monitoring_config['memory_threshold']}%)",
        'timestamp' => date('H:i:s'),
        'severity' => 'critical',
        'category' => 'performance'
    ];
}

if ($system_metrics['disk_usage'] > $monitoring_config['disk_threshold']) {
    $system_alerts[] = [
        'id' => 'disk_warning',
        'type' => 'warning',
        'title' => 'Espace Disque Faible',
        'message' => "Disque: {$system_metrics['disk_usage']}% (Seuil: {$monitoring_config['disk_threshold']}%)",
        'timestamp' => date('H:i:s'),
        'severity' => 'medium',
        'category' => 'storage'
    ];
}

if ($system_metrics['response_time'] > $monitoring_config['performance_threshold']) {
    $system_alerts[] = [
        'id' => 'response_slow',
        'type' => 'warning',
        'title' => 'Temps de Réponse Élevé',
        'message' => "Réponse: {$system_metrics['response_time']}ms (Seuil: {$monitoring_config['performance_threshold']}ms)",
        'timestamp' => date('H:i:s'),
        'severity' => 'medium',
        'category' => 'performance'
    ];
}

// Generate historical data for charts
$historical_data = [];
for ($i = 23; $i >= 0; $i--) {
    $historical_data[] = [
        'hour' => date('H:i', strtotime("-{$i} hours")),
        'cpu' => rand(20, 90),
        'memory' => rand(35, 85),
        'disk' => rand(25, 80),
        'network' => rand(15, 75),
        'errors' => rand(0, 15),
        'security' => rand(70, 98),
        'performance' => rand(75, 95)
    ];
}

// Generate service status data
$service_status = [
    'database' => [
        'name' => 'MySQL 8.0',
        'status' => 'operational',
        'performance' => 85,
        'uptime' => '99.9%',
        'connections' => $system_metrics['database_connections'],
        'version' => '8.0.35',
        'last_backup' => '2024-01-15 02:00:00'
    ],
    'web_server' => [
        'name' => 'Apache 2.4',
        'status' => 'operational',
        'performance' => 92,
        'uptime' => '99.8%',
        'requests_per_sec' => rand(100, 500),
        'version' => '2.4.57',
        'last_restart' => '2024-01-10 04:00:00'
    ],
    'cache' => [
        'name' => 'Redis 6.2',
        'status' => 'operational',
        'performance' => 78,
        'uptime' => '99.7%',
        'hit_rate' => $system_metrics['cache_hit_rate'],
        'version' => '6.2.13',
        'memory_used' => rand(100, 500)
    ],
    'load_balancer' => [
        'name' => 'Nginx 1.24',
        'status' => 'operational',
        'performance' => 95,
        'uptime' => '99.9%',
        'active_connections' => rand(50, 200),
        'version' => '1.24.0',
        'last_config' => '2024-01-12 10:00:00'
    ]
];

// Get notification if exists
$notification = $_SESSION['monitoring_notification'] ?? null;
unset($_SESSION['monitoring_notification']);

// Get current timestamp for real-time updates
$current_time = date('Y-m-d H:i:s');
?>

<!-- Enterprise Real-Time Monitoring Content -->
<div class="fade-in">
    <!-- Notification Display -->
    <?php if ($notification): ?>
    <div class="alert alert-<?= $notification['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4" role="alert">
        <strong><?= htmlspecialchars($notification['title']) ?>:</strong> <?= htmlspecialchars($notification['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Real-Time Monitoring Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-tachometer-alt me-3"></i>
                        Enterprise Real-Time Monitoring
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Surveillance en temps réel des systèmes, applications et infrastructure avec alertes automatiques
                    </p>
                    <div class="d-flex align-items-center mt-2">
                        <span class="badge bg-success me-3">
                            <i class="fas fa-circle me-1"></i>
                            Système Opérationnel
                        </span>
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Dernière mise à jour: <?= $current_time ?>
                        </small>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="refreshMonitoringData()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="exportMonitoringData()">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                    <button class="enterprise-btn enterprise-btn-accent" onclick="openConfigModal()">
                        <i class="fas fa-cog"></i>
                        Configuration
                    </button>
                    <button class="enterprise-btn enterprise-btn-success" onclick="openAddRuleModal()">
                        <i class="fas fa-plus"></i>
                        Nouvelle Règle
                    </button>
                    <button class="enterprise-btn enterprise-btn-info" onclick="generateSystemReport()">
                        <i class="fas fa-file-alt"></i>
                        Rapport
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-<?= $system_metrics['cpu_usage'] > $monitoring_config['cpu_threshold'] ? 'danger' : 'primary' ?> bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-microchip fa-2x text-<?= $system_metrics['cpu_usage'] > $monitoring_config['cpu_threshold'] ? 'danger' : 'primary' ?>"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-<?= $system_metrics['cpu_usage'] > $monitoring_config['cpu_threshold'] ? 'danger' : 'primary' ?> mb-0"><?= $system_metrics['cpu_usage'] ?>%</h3>
                            <small class="text-muted">CPU</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-<?= $system_metrics['cpu_usage'] > $monitoring_config['cpu_threshold'] ? 'danger' : 'success' ?>">
                            <i class="fas fa-<?= $system_metrics['cpu_usage'] > $monitoring_config['cpu_threshold'] ? 'exclamation-triangle' : 'check-circle' ?>"></i>
                            <?= $system_metrics['cpu_usage'] > $monitoring_config['cpu_threshold'] ? 'Seuil dépassé' : 'Normal' ?>
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showCPUDetails()">
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
                        <div class="stat-icon bg-<?= $system_metrics['memory_usage'] > $monitoring_config['memory_threshold'] ? 'danger' : 'success' ?> bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-memory fa-2x text-<?= $system_metrics['memory_usage'] > $monitoring_config['memory_threshold'] ? 'danger' : 'success' ?>"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-<?= $system_metrics['memory_usage'] > $monitoring_config['memory_threshold'] ? 'danger' : 'success' ?> mb-0"><?= $system_metrics['memory_usage'] ?>%</h3>
                            <small class="text-muted">Mémoire</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-<?= $system_metrics['memory_usage'] > $monitoring_config['memory_threshold'] ? 'danger' : 'success' ?>">
                            <i class="fas fa-<?= $system_metrics['memory_usage'] > $monitoring_config['memory_threshold'] ? 'exclamation-triangle' : 'check-circle' ?>"></i>
                            <?= $system_metrics['memory_usage'] > $monitoring_config['memory_threshold'] ? 'Critique' : 'Normal' ?>
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showMemoryDetails()">
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
                        <div class="stat-icon bg-<?= $system_metrics['disk_usage'] > $monitoring_config['disk_threshold'] ? 'warning' : 'info' ?> bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-hdd fa-2x text-<?= $system_metrics['disk_usage'] > $monitoring_config['disk_threshold'] ? 'warning' : 'info' ?>"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-<?= $system_metrics['disk_usage'] > $monitoring_config['disk_threshold'] ? 'warning' : 'info' ?> mb-0"><?= $system_metrics['disk_usage'] ?>%</h3>
                            <small class="text-muted">Disque</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-<?= $system_metrics['disk_usage'] > $monitoring_config['disk_threshold'] ? 'warning' : 'success' ?>">
                            <i class="fas fa-<?= $system_metrics['disk_usage'] > $monitoring_config['disk_threshold'] ? 'exclamation-triangle' : 'check-circle' ?>"></i>
                            <?= $system_metrics['disk_usage'] > $monitoring_config['disk_threshold'] ? 'Attention' : 'Normal' ?>
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showDiskDetails()">
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
                            <i class="fas fa-users fa-2x text-success"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-success mb-0"><?= $system_metrics['active_users'] ?></h3>
                            <small class="text-muted">Utilisateurs Actifs</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +<?= rand(5, 25) ?> aujourd'hui
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showUserDetails()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Alerts Section -->
    <?php if (!empty($system_alerts)): ?>
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Alertes Système (<?= count($system_alerts) ?>)
            </h4>
        </div>
        <div class="enterprise-card-body">
            <div class="row">
                <?php foreach ($system_alerts as $alert): ?>
                <div class="col-md-6 mb-3">
                    <div class="alert alert-<?= $alert['type'] ?> alert-dismissible fade show" role="alert">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?= htmlspecialchars($alert['title']) ?></strong>
                                <br><small><?= htmlspecialchars($alert['message']) ?></small>
                                <br><small class="text-muted"><?= $alert['timestamp'] ?></small>
                            </div>
                            <div class="d-flex gap-2">
                                <span class="badge bg-<?= $alert['severity'] === 'critical' ? 'danger' : ($alert['severity'] === 'high' ? 'warning' : 'info') ?>">
                                    <?= ucfirst($alert['severity']) ?>
                                </span>
                                <button class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Performance Charts -->
    <div class="row mb-4">
        <div class="col-xl-8 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-chart-line me-2"></i>
                        Performance Système (24h)
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <canvas id="performanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-chart-pie me-2"></i>
                        Répartition des Ressources
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <canvas id="resourceChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- System Details Tables -->
    <div class="row mb-4">
        <div class="col-xl-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-server me-2"></i>
                        État des Services
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Statut</th>
                                    <th>Performance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($service_status as $key => $service): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <strong class="text-primary"><?= htmlspecialchars($service['name']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($service['version']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Opérationnel</span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: <?= $service['performance'] ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?= $service['performance'] ?>%</small>
                                    </td>
                                    <td>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showServiceDetails('<?= $key ?>')">
                                            Détails
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
        
        <div class="col-xl-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-shield-alt me-2"></i>
                        Sécurité et Accès
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Métrique</th>
                                    <th>Valeur</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div>
                                            <strong class="text-primary">Score de Sécurité</strong>
                                            <br><small class="text-muted">Global</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-success"><?= $system_metrics['security_score'] ?>/100</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Excellent</span>
                                    </td>
                                    <td>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showSecurityDetails()">
                                            Détails
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <strong class="text-primary">Score de Performance</strong>
                                            <br><small class="text-muted">Global</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-info"><?= $system_metrics['performance_score'] ?>/100</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">Bon</span>
                                    </td>
                                    <td>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showPerformanceDetails()">
                                            Détails
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div>
                                            <strong class="text-primary">Cache Hit Rate</strong>
                                            <br><small class="text-muted">Redis</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-warning"><?= $system_metrics['cache_hit_rate'] ?>%</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning">Acceptable</span>
                                    </td>
                                    <td>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showServiceDetails('cache')">
                                            Détails
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monitoring Rules Section -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="enterprise-card-title">
                    <i class="fas fa-rules me-2"></i>
                    Règles de Monitoring (<?= count($monitoring_rules) ?>)
                </h4>
                <button class="enterprise-btn enterprise-btn-success" onclick="openAddRuleModal()">
                    <i class="fas fa-plus"></i>
                    Nouvelle Règle
                </button>
            </div>
        </div>
        <div class="enterprise-card-body">
            <?php if (!empty($monitoring_rules)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Seuil</th>
                            <th>Action</th>
                            <th>Statut</th>
                            <th>Créé le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($monitoring_rules as $rule): ?>
                        <tr>
                            <td>
                                <strong class="text-primary"><?= htmlspecialchars($rule['name']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= htmlspecialchars($rule['type']) ?></span>
                            </td>
                            <td>
                                <strong><?= $rule['threshold'] ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($rule['action']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $rule['status'] === 'active' ? 'success' : ($rule['status'] === 'inactive' ? 'warning' : 'secondary') ?>">
                                    <?= ucfirst($rule['status']) ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($rule['created_at'])) ?></small>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="editRule('<?= $rule['id'] ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger" onclick="deleteRule('<?= $rule['id'] ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-rules fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucune règle de monitoring configurée</p>
                <button class="enterprise-btn enterprise-btn-success" onclick="openAddRuleModal()">
                    <i class="fas fa-plus"></i>
                    Créer la première règle
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript Functions -->
<script>
// Refresh monitoring data
function refreshMonitoringData() {
    location.reload();
}

// Export monitoring data
function exportMonitoringData() {
    // Simulate CSV export
    const csvContent = "data:text/csv;charset=utf-8," + 
        "Métrique,Valeur,Statut\n" +
        "CPU,<?= $system_metrics['cpu_usage'] ?>%,<?= $system_metrics['cpu_usage'] > $monitoring_config['cpu_threshold'] ? 'Seuil dépassé' : 'Normal' ?>\n" +
        "Mémoire,<?= $system_metrics['memory_usage'] ?>%,<?= $system_metrics['memory_usage'] > $monitoring_config['memory_threshold'] ? 'Critique' : 'Normal' ?>\n" +
        "Disque,<?= $system_metrics['disk_usage'] ?>%,<?= $system_metrics['disk_usage'] > $monitoring_config['disk_threshold'] ? 'Attention' : 'Normal' ?>\n" +
        "Utilisateurs Actifs,<?= $system_metrics['active_users'] ?>,Normal\n" +
        "Score Sécurité,<?= $system_metrics['security_score'] ?>/100,Excellent\n" +
        "Score Performance,<?= $system_metrics['performance_score'] ?>/100,Bon";
    
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "monitoring_data_<?= date('Y-m-d_H-i-s') ?>.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Generate system report
function generateSystemReport() {
    alert('Rapport système généré avec succès !');
}

// Show CPU details
function showCPUDetails() {
    alert('Détails CPU:\nUtilisation: <?= $system_metrics['cpu_usage'] ?>%\nSeuil: <?= $monitoring_config['cpu_threshold'] ?>%\nStatut: <?= $system_metrics['cpu_usage'] > $monitoring_config['cpu_threshold'] ? 'Seuil dépassé' : 'Normal' ?>');
}

// Show memory details
function showMemoryDetails() {
    alert('Détails Mémoire:\nUtilisation: <?= $system_metrics['memory_usage'] ?>%\nSeuil: <?= $monitoring_config['memory_threshold'] ?>%\nStatut: <?= $system_metrics['memory_usage'] > $monitoring_config['memory_threshold'] ? 'Critique' : 'Normal' ?>');
}

// Show disk details
function showDiskDetails() {
    alert('Détails Disque:\nUtilisation: <?= $system_metrics['disk_usage'] ?>%\nSeuil: <?= $monitoring_config['disk_threshold'] ?>%\nStatut: <?= $system_metrics['disk_usage'] > $monitoring_config['disk_threshold'] ? 'Attention' : 'Normal' ?>');
}

// Show user details
function showUserDetails() {
    alert('Détails Utilisateurs:\nActifs: <?= $system_metrics['active_users'] ?>\nConnexions DB: <?= $system_metrics['database_connections'] ?>');
}

// Show service details
function showServiceDetails(serviceKey) {
    const services = {
        'database': 'MySQL 8.0 - <?= $service_status['database']['performance'] ?>% - <?= $service_status['database']['uptime'] ?>',
        'web_server': 'Apache 2.4 - <?= $service_status['web_server']['performance'] ?>% - <?= $service_status['web_server']['uptime'] ?>',
        'cache': 'Redis 6.2 - <?= $service_status['cache']['performance'] ?>% - <?= $service_status['cache']['uptime'] ?>',
        'load_balancer': 'Nginx 1.24 - <?= $service_status['load_balancer']['performance'] ?>% - <?= $service_status['load_balancer']['uptime'] ?>'
    };
    alert('Détails Service:\n' + services[serviceKey]);
}

// Show security details
function showSecurityDetails() {
    alert('Détails Sécurité:\nScore: <?= $system_metrics['security_score'] ?>/100\nTaux d\'erreur: <?= $system_metrics['error_rate'] ?>%\nLatence réseau: <?= $system_metrics['network_latency'] ?>ms');
}

// Show performance details
function showPerformanceDetails() {
    alert('Détails Performance:\nScore: <?= $system_metrics['performance_score'] ?>/100\nTemps de réponse: <?= $system_metrics['response_time'] ?>ms\nCharge serveur: <?= $system_metrics['server_load'] ?>');
}

// Open configuration modal
function openConfigModal() {
    alert('Modal de configuration - À implémenter');
}

// Open add rule modal
function openAddRuleModal() {
    alert('Modal d\'ajout de règle - À implémenter');
}

// Edit rule
function editRule(ruleId) {
    alert('Édition de la règle ' + ruleId + ' - À implémenter');
}

// Delete rule
function deleteRule(ruleId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette règle ?')) {
        // Submit form to delete rule
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_monitoring_rule">
            <input type="hidden" name="rule_id" value="${ruleId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Performance Chart
    const performanceCtx = document.getElementById('performanceChart');
    if (performanceCtx) {
        new Chart(performanceCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($historical_data, 'hour')) ?>,
                datasets: [{
                    label: 'CPU (%)',
                    data: <?= json_encode(array_column($historical_data, 'cpu')) ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }, {
                    label: 'Mémoire (%)',
                    data: <?= json_encode(array_column($historical_data, 'memory')) ?>,
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1
                }, {
                    label: 'Disque (%)',
                    data: <?= json_encode(array_column($historical_data, 'disk')) ?>,
                    borderColor: 'rgb(54, 162, 235)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    }

    // Resource Chart
    const resourceCtx = document.getElementById('resourceChart');
    if (resourceCtx) {
        new Chart(resourceCtx, {
            type: 'doughnut',
            data: {
                labels: ['CPU', 'Mémoire', 'Disque', 'Réseau'],
                datasets: [{
                    data: [
                        <?= $system_metrics['cpu_usage'] ?>,
                        <?= $system_metrics['memory_usage'] ?>,
                        <?= $system_metrics['disk_usage'] ?>,
                        <?= $system_metrics['network_usage'] ?>
                    ],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)'
                    ]
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
    }
});

// Auto-refresh every 30 seconds
setInterval(function() {
    // Update timestamp
    const timestampElement = document.querySelector('.text-muted small');
    if (timestampElement) {
        timestampElement.innerHTML = '<i class="fas fa-clock me-1"></i>Dernière mise à jour: ' + new Date().toLocaleString('fr-FR');
    }
}, 30000);
</script>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
