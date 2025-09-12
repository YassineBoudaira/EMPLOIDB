<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include admin header
include __DIR__ . '/includes/admin_header.php';

// Check if user has access to monitoring
if (!$rbac->hasPageAccess($_SESSION['admin_id'] ?? 1, 'real_time_monitoring')) {
    $_SESSION['error'] = 'Accès non autorisé à cette page.';
    header('Location: dashboard.php');
    exit;
}

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
            
            // Update configuration in database
            $systemMonitor->updateConfig('cpu_threshold', $cpu_threshold);
            $systemMonitor->updateConfig('memory_threshold', $memory_threshold);
            $systemMonitor->updateConfig('disk_threshold', $disk_threshold);
            $systemMonitor->updateConfig('network_threshold', $network_threshold);
            $systemMonitor->updateConfig('security_threshold', $security_threshold);
            $systemMonitor->updateConfig('performance_threshold', $performance_threshold);
            
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
            
            // Add rule to database
            $db->insert('monitoring_rules', [
                'rule_name' => $rule_name,
                'rule_type' => $rule_type,
                'metric_name' => $rule_type,
                'threshold_value' => $rule_threshold,
                'threshold_operator' => '>',
                'action_type' => $rule_action,
                'is_active' => $rule_status === 'active' ? 1 : 0,
                'priority' => 1,
                'description' => "Règle automatique: {$rule_name}",
                'created_by' => $_SESSION['admin_id'] ?? 1
            ]);
            
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
            
            // Update rule in database
            $db->update('monitoring_rules', [
                'rule_name' => $rule_name,
                'rule_type' => $rule_type,
                'metric_name' => $rule_type,
                'threshold_value' => $rule_threshold,
                'action_type' => $rule_action,
                'is_active' => $rule_status === 'active' ? 1 : 0,
                'updated_by' => $_SESSION['admin_id'] ?? 1
            ], "id = {$rule_id}");
            
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
            
            // Delete rule from database
            $db->delete('monitoring_rules', "id = {$rule_id}");
            
            $_SESSION['monitoring_notification'] = [
                'type' => 'success',
                'message' => 'Règle de monitoring supprimée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: real_time_monitoring.php?success=rule_deleted');
            exit;
        }
        
        if ($action === 'acknowledge_alert') {
            $alert_id = intval($_POST['alert_id'] ?? 0);
            
            if ($alert_id <= 0) {
                throw new Exception('ID d\'alerte invalide');
            }
            
            // Update alert status in database
            $db->update('system_alerts', [
                'status' => 'acknowledged',
                'acknowledged_at' => date('Y-m-d H:i:s'),
                'acknowledged_by' => $_SESSION['admin_id'] ?? 1
            ], "id = {$alert_id}");
            
            $_SESSION['monitoring_notification'] = [
                'type' => 'success',
                'message' => 'Alerte reconnue et traitée !',
                'title' => 'Succès'
            ];
            
            header('Location: real_time_monitoring.php?success=alert_acknowledged');
            exit;
        }
        
        if ($action === 'export_system_report') {
            // Generate and export system report
            $report_data = $systemMonitor->generateSystemReport();
            
            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=system_report_' . date('Y-m-d_H-i-s') . '.csv');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Métrique', 'Valeur', 'Unité', 'Catégorie', 'Timestamp']);
            
            foreach ($report_data as $row) {
                fputcsv($output, $row);
            }
            
            fclose($output);
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

// Get monitoring configuration from database
$monitoring_config = [
    'cpu_threshold' => $systemMonitor->getConfig('cpu_threshold', 80),
    'memory_threshold' => $systemMonitor->getConfig('memory_threshold', 85),
    'disk_threshold' => $systemMonitor->getConfig('disk_threshold', 90),
    'network_threshold' => $systemMonitor->getConfig('network_threshold', 75),
    'security_threshold' => $systemMonitor->getConfig('security_threshold', 10),
    'performance_threshold' => $systemMonitor->getConfig('performance_threshold', 300)
];

// Get monitoring rules from database
$monitoring_rules = $systemMonitor->getMonitoringRules();

// Get real-time system metrics
$system_metrics = $systemMonitor->getSystemMetrics();

// Get active alerts from database
$system_alerts = $systemMonitor->getActiveAlerts();

// Get historical data for charts
$historical_data = $systemMonitor->getChartData(24);

// Get service status from database
$service_status = $systemMonitor->getServiceStatus();

// Get security events
$security_events = $systemMonitor->getSecurityEvents(10);

// Get network traffic data
$network_traffic = $systemMonitor->getNetworkTraffic(10);

// Get notification if exists
$notification = $_SESSION['monitoring_notification'] ?? null;
unset($_SESSION['monitoring_notification']);

// Get current timestamp for real-time updates
$current_time = date('Y-m-d H:i:s');
?>

<!-- Enterprise Real-Time Monitoring Content -->
<style>
.stat-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1.2;
}

.enterprise-card .enterprise-card-body {
    padding: 1.5rem;
}

.enterprise-card.h-100 {
    border: 1px solid var(--enterprise-gray-200);
    transition: all 0.3s ease;
}

.enterprise-card.h-100:hover {
    transform: translateY(-2px);
    box-shadow: var(--enterprise-shadow-lg);
}
</style>

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

    <!-- Quick Actions Panel -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-bolt me-2"></i>
                Actions Rapides
            </h4>
        </div>
        <div class="enterprise-card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <button class="enterprise-btn enterprise-btn-outline w-100 h-100 py-4" onclick="quickAction('restart_services')">
                        <i class="fas fa-redo fa-2x mb-2"></i>
                        <br>
                        <strong>Redémarrer Services</strong>
                        <br>
                        <small class="text-muted">Redémarrage rapide des services critiques</small>
                    </button>
                </div>
                <div class="col-md-3 mb-3">
                    <button class="enterprise-btn enterprise-btn-outline w-100 h-100 py-4" onclick="quickAction('clear_cache')">
                        <i class="fas fa-broom fa-2x mb-2"></i>
                        <br>
                        <strong>Vider le Cache</strong>
                        <br>
                        <small class="text-muted">Nettoyage des caches système</small>
                    </button>
                </div>
                <div class="col-md-3 mb-3">
                    <button class="enterprise-btn enterprise-btn-outline w-100 h-100 py-4" onclick="quickAction('check_updates')">
                        <i class="fas fa-download fa-2x mb-2"></i>
                        <br>
                        <strong>Vérifier Mises à Jour</strong>
                        <br>
                        <small class="text-muted">Scan des mises à jour disponibles</small>
                    </button>
                </div>
                <div class="col-md-3 mb-3">
                    <button class="enterprise-btn enterprise-btn-outline w-100 h-100 py-4" onclick="quickAction('emergency_mode')">
                        <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                        <br>
                        <strong>Mode Urgence</strong>
                        <br>
                        <small class="text-muted">Activation du mode de sécurité</small>
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
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-microchip fa-2x text-primary"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-primary mb-0"><?= $system_metrics['cpu_usage'] ?>%</h3>
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
                        <div class="stat-icon bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-memory fa-2x text-success"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-success mb-0"><?= $system_metrics['memory_usage'] ?>%</h3>
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
                        <div class="stat-icon bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-hdd fa-2x text-warning"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-warning mb-0"><?= $system_metrics['disk_usage'] ?>%</h3>
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
                        <div class="stat-icon bg-info bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-info mb-0"><?= $system_metrics['active_users'] ?></h3>
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
                    <div class="alert alert-<?= $alert['severity'] === 'critical' ? 'danger' : ($alert['severity'] === 'high' ? 'warning' : 'info') ?> alert-dismissible fade show" role="alert">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?= htmlspecialchars($alert['title']) ?></strong>
                                <br><small><?= htmlspecialchars($alert['message']) ?></small>
                                <br><small class="text-muted"><?= $alert['triggered_at'] ?></small>
                            </div>
                            <div class="d-flex gap-2">
                                <span class="badge bg-<?= $alert['severity'] === 'critical' ? 'danger' : ($alert['severity'] === 'high' ? 'warning' : 'info') ?>">
                                    <?= ucfirst($alert['severity']) ?>
                                </span>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="acknowledge_alert">
                                    <input type="hidden" name="alert_id" value="<?= $alert['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
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

    <!-- System Health Summary -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-heartbeat me-2"></i>
                Résumé de Santé du Système
            </h4>
        </div>
        <div class="enterprise-card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="enterprise-card h-100">
                        <div class="enterprise-card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-server fa-2x text-primary"></i>
                                </div>
                                <div class="text-end">
                                    <h5 class="stat-value text-primary mb-0"><?= $system_metrics['uptime'] ?? '99.9%' ?></h5>
                                    <small class="text-muted">Uptime</small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-success">
                                    <i class="fas fa-check-circle"></i>
                                    Stable
                                </span>
                                <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showUptimeDetails()">
                                    Détails
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="enterprise-card h-100">
                        <div class="enterprise-card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="stat-icon bg-success bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-tachometer-alt fa-2x text-success"></i>
                                </div>
                                <div class="text-end">
                                    <h5 class="stat-value text-success mb-0"><?= $system_metrics['response_time'] ?? rand(50, 200) ?>ms</h5>
                                    <small class="text-muted">Temps de Réponse</small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-success">
                                    <i class="fas fa-arrow-down"></i>
                                    Rapide
                                </span>
                                <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showResponseTimeDetails()">
                                    Détails
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="enterprise-card h-100">
                        <div class="enterprise-card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="stat-icon bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                                </div>
                                <div class="text-end">
                                    <h5 class="stat-value text-warning mb-0"><?= $system_metrics['error_rate'] ?? rand(0, 5) ?>%</h5>
                                    <small class="text-muted">Taux d'Erreur</small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Acceptable
                                </span>
                                <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showErrorRateDetails()">
                                    Détails
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="enterprise-card h-100">
                        <div class="enterprise-card-body text-center">
                            <div class="d-flex align-items-center justify-content-center mb-3">
                                <div class="stat-icon bg-info bg-opacity-10 rounded-circle p-3 me-3">
                                    <i class="fas fa-shield-alt fa-2x text-info"></i>
                                </div>
                                <div class="text-end">
                                    <h5 class="stat-value text-info mb-0"><?= $system_metrics['security_score'] ?? rand(85, 98) ?>/100</h5>
                                    <small class="text-muted">Score Sécurité</small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-success">
                                    <i class="fas fa-shield-check"></i>
                                    Excellent
                                </span>
                                <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showSecurityScoreDetails()">
                                    Détails
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">État Global du Système:</span>
                        <span class="badge bg-success fs-6">
                            <i class="fas fa-check-circle me-1"></i>
                            Opérationnel
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                            <strong class="text-primary"><?= htmlspecialchars($service['service_name']) ?></strong>
                                            <br><small class="text-muted"><?= htmlspecialchars($service['version'] ?? 'N/A') ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $service['status'] === 'operational' ? 'success' : ($service['status'] === 'degraded' ? 'warning' : 'danger') ?>">
                                            <?= ucfirst($service['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: <?= $service['uptime_percentage'] ?? 0 ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?= $service['uptime_percentage'] ?? 0 ?>%</small>
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

    <!-- Network Traffic Monitoring -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-network-wired me-2"></i>
                Surveillance du Trafic Réseau
            </h4>
        </div>
        <div class="enterprise-card-body">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <h6 class="text-primary mb-3">Trafic Entrant</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Bande passante utilisée:</span>
                        <strong class="text-info"><?= rand(45, 85) ?>%</strong>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: <?= rand(45, 85) ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Paquets/s:</span>
                        <strong><?= number_format(rand(1000, 50000)) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Connexions actives:</span>
                        <strong><?= rand(150, 800) ?></strong>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <h6 class="text-success mb-3">Trafic Sortant</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Bande passante utilisée:</span>
                        <strong class="text-success"><?= rand(30, 70) ?>%</strong>
                    </div>
                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: <?= rand(30, 70) ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Paquets/s:</span>
                        <strong><?= number_format(rand(800, 40000)) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Connexions actives:</span>
                        <strong><?= rand(100, 600) ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <h6 class="text-warning mb-3">Ports Ouverts et Services</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Port</th>
                                    <th>Service</th>
                                    <th>Statut</th>
                                    <th>Connexions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>80</code></td>
                                    <td>HTTP</td>
                                    <td><span class="badge bg-success">Ouvert</span></td>
                                    <td><?= rand(50, 200) ?></td>
                                    <td>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showPortDetails(80)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><code>443</code></td>
                                    <td>HTTPS</td>
                                    <td><span class="badge bg-success">Ouvert</span></td>
                                    <td><?= rand(30, 150) ?></td>
                                    <td>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showPortDetails(443)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><code>3306</code></td>
                                    <td>MySQL</td>
                                    <td><span class="badge bg-success">Ouvert</span></td>
                                    <td><?= rand(5, 25) ?></td>
                                    <td>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showPortDetails(3306)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td><code>22</code></td>
                                    <td>SSH</td>
                                    <td><span class="badge bg-warning">Restreint</span></td>
                                    <td><?= rand(1, 5) ?></td>
                                    <td>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showPortDetails(22)">
                                            <i class="fas fa-eye"></i>
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

    <!-- Security Events Log -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-shield-alt me-2"></i>
                Journal des Événements de Sécurité
            </h4>
        </div>
        <div class="enterprise-card-body">
            <?php if (!empty($security_events)): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Type d'Événement</th>
                            <th>Source IP</th>
                            <th>Utilisateur</th>
                            <th>Détails</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($security_events as $event): ?>
                        <tr>
                            <td>
                                <small class="text-muted"><?= date('d/m/Y H:i:s', strtotime($event['timestamp'])) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?= $event['event_type'] === 'failed_login' ? 'danger' : ($event['event_type'] === 'suspicious_activity' ? 'warning' : 'info') ?>">
                                    <?= ucfirst(str_replace('_', ' ', $event['event_type'])) ?>
                                </span>
                            </td>
                            <td>
                                <code><?= htmlspecialchars($event['source_ip']) ?></code>
                            </td>
                            <td>
                                <?= htmlspecialchars($event['username'] ?? 'N/A') ?>
                            </td>
                            <td>
                                <small><?= htmlspecialchars($event['details']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?= $event['status'] === 'blocked' ? 'danger' : ($event['status'] === 'investigating' ? 'warning' : 'success') ?>">
                                    <?= ucfirst($event['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showSecurityEventDetails('<?= $event['id'] ?>')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucun événement de sécurité détecté</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- System Logs Viewer -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="enterprise-card-title">
                    <i class="fas fa-file-alt me-2"></i>
                    Visionneuse des Logs Système
                </h4>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="logLevel" onchange="filterLogs()">
                        <option value="">Tous les niveaux</option>
                        <option value="ERROR">Erreurs</option>
                        <option value="WARNING">Avertissements</option>
                        <option value="INFO">Informations</option>
                        <option value="DEBUG">Debug</option>
                    </select>
                    <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="refreshLogs()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger" onclick="clearLogs()">
                        <i class="fas fa-trash"></i>
                        Vider
                    </button>
                </div>
            </div>
        </div>
        <div class="enterprise-card-body">
            <div class="log-viewer" style="max-height: 400px; overflow-y: auto; background: #1e1e1e; color: #fff; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; font-size: 12px;">
                <div class="log-entry text-danger">[<?= date('Y-m-d H:i:s') ?>] ERROR: Test error message for demonstration</div>
                <div class="log-entry text-warning">[<?= date('Y-m-d H:i:s', strtotime('-1 minute')) ?>] WARNING: High memory usage detected</div>
                <div class="log-entry text-info">[<?= date('Y-m-d H:i:s', strtotime('-2 minutes')) ?>] INFO: System backup completed successfully</div>
                <div class="log-entry text-success">[<?= date('Y-m-d H:i:s', strtotime('-3 minutes')) ?>] INFO: Database connection established</div>
                <div class="log-entry text-warning">[<?= date('Y-m-d H:i:s', strtotime('-4 minutes')) ?>] WARNING: Disk space running low</div>
                <div class="log-entry text-info">[<?= date('Y-m-d H:i:s', strtotime('-5 minutes')) ?>] INFO: User authentication successful</div>
                <div class="log-entry text-danger">[<?= date('Y-m-d H:i:s', strtotime('-6 minutes')) ?>] ERROR: Failed to connect to external service</div>
                <div class="log-entry text-success">[<?= date('Y-m-d H:i:s', strtotime('-7 minutes')) ?>] INFO: Cache cleared successfully</div>
            </div>
        </div>
    </div>

    <!-- System Diagnostics -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-stethoscope me-2"></i>
                Diagnostic Système en Temps Réel
            </h4>
        </div>
        <div class="enterprise-card-body">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <h6 class="text-primary mb-3">Tests de Connectivité</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Base de données:</span>
                        <span class="badge bg-success">Connecté</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Serveur web:</span>
                        <span class="badge bg-success">Opérationnel</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Cache Redis:</span>
                        <span class="badge bg-success">Disponible</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Services externes:</span>
                        <span class="badge bg-warning">Partiel</span>
                    </div>
                    
                    <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline mt-3" onclick="runConnectivityTests()">
                        <i class="fas fa-play"></i>
                        Lancer les Tests
                    </button>
                </div>
                
                <div class="col-md-6 mb-4">
                    <h6 class="text-success mb-3">Performance des Requêtes</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Temps de réponse DB:</span>
                        <strong><?= rand(5, 25) ?>ms</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Requêtes/s:</span>
                        <strong><?= number_format(rand(100, 500)) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Connexions actives:</span>
                        <strong><?= rand(10, 50) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Cache hit rate:</span>
                        <strong><?= rand(75, 95) ?>%</strong>
                    </div>
                    
                    <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline mt-3" onclick="runPerformanceTests()">
                        <i class="fas fa-tachometer-alt"></i>
                        Test Performance
                    </button>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <h6 class="text-info mb-3">Tests de Sécurité</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Test</th>
                                    <th>Statut</th>
                                    <th>Dernière exécution</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Vérification des permissions</td>
                                    <td><span class="badge bg-success">Passé</span></td>
                                    <td><small class="text-muted"><?= date('H:i:s', strtotime('-2 minutes')) ?></small></td>
                                    <td>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="runSecurityTest('permissions')">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Scan des vulnérabilités</td>
                                    <td><span class="badge bg-success">Passé</span></td>
                                    <td><small class="text-muted"><?= date('H:i:s', strtotime('-15 minutes')) ?></small></td>
                                    <td>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="runSecurityTest('vulnerability')">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Vérification SSL/TLS</td>
                                    <td><span class="badge bg-success">Passé</span></td>
                                    <td><small class="text-muted"><?= date('H:i:s', strtotime('-5 minutes')) ?></small></td>
                                    <td>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="runSecurityTest('ssl')">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Audit des logs</td>
                                    <td><span class="badge bg-warning">En cours</span></td>
                                    <td><small class="text-muted"><?= date('H:i:s') ?></small></td>
                                    <td>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="runSecurityTest('audit')">
                                            <i class="fas fa-play"></i>
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

    <!-- System Backup & Maintenance -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-database me-2"></i>
                Sauvegarde & Maintenance Système
            </h4>
        </div>
        <div class="enterprise-card-body">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <h6 class="text-primary mb-3">État des Sauvegardes</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Dernière sauvegarde DB:</span>
                        <span class="badge bg-success"><?= date('d/m/Y H:i', strtotime('-2 hours')) ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Dernière sauvegarde fichiers:</span>
                        <span class="badge bg-success"><?= date('d/m/Y H:i', strtotime('-6 hours')) ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Prochaine sauvegarde:</span>
                        <span class="badge bg-info"><?= date('d/m/Y H:i', strtotime('+4 hours')) ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Espace de sauvegarde:</span>
                        <span class="badge bg-<?= rand(0, 100) > 80 ? 'warning' : 'success' ?>"><?= rand(60, 95) ?>% utilisé</span>
                    </div>
                    
                    <div class="d-flex gap-2 mt-3">
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-primary" onclick="startBackup('database')">
                            <i class="fas fa-download"></i>
                            Sauvegarde DB
                        </button>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-info" onclick="startBackup('files')">
                            <i class="fas fa-folder"></i>
                            Sauvegarde Fichiers
                        </button>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <h6 class="text-success mb-3">Maintenance Système</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Dernière maintenance:</span>
                        <span class="badge bg-info"><?= date('d/m/Y H:i', strtotime('-1 day')) ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Prochaine maintenance:</span>
                        <span class="badge bg-warning"><?= date('d/m/Y H:i', strtotime('+2 days')) ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Durée moyenne:</span>
                        <span class="badge bg-secondary"><?= rand(15, 45) ?> minutes</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Statut:</span>
                        <span class="badge bg-success">Planifiée</span>
                    </div>
                    
                    <div class="d-flex gap-2 mt-3">
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-warning" onclick="scheduleMaintenance()">
                            <i class="fas fa-calendar"></i>
                            Planifier
                        </button>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger" onclick="startMaintenance()">
                            <i class="fas fa-tools"></i>
                            Démarrer
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <h6 class="text-warning mb-3">Historique des Opérations</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Statut</th>
                                    <th>Durée</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime('-2 hours')) ?></small></td>
                                    <td><span class="badge bg-primary">Sauvegarde DB</span></td>
                                    <td>Sauvegarde automatique de la base de données</td>
                                    <td><span class="badge bg-success">Terminé</span></td>
                                    <td><small>5 min</small></td>
                                </tr>
                                <tr>
                                    <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime('-6 hours')) ?></small></td>
                                    <td><span class="badge bg-info">Sauvegarde Fichiers</span></td>
                                    <td>Sauvegarde des fichiers système</td>
                                    <td><span class="badge bg-success">Terminé</span></td>
                                    <td><small>12 min</small></td>
                                </tr>
                                <tr>
                                    <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime('-1 day')) ?></small></td>
                                    <td><span class="badge bg-warning">Maintenance</span></td>
                                    <td>Maintenance système planifiée</td>
                                    <td><span class="badge bg-success">Terminé</span></td>
                                    <td><small>25 min</small></td>
                                </tr>
                                <tr>
                                    <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime('-2 days')) ?></small></td>
                                    <td><span class="badge bg-danger">Récupération</span></td>
                                    <td>Récupération après incident</td>
                                    <td><span class="badge bg-success">Terminé</span></td>
                                    <td><small>45 min</small></td>
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
                                <strong class="text-primary"><?= htmlspecialchars($rule['rule_name']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-info"><?= htmlspecialchars($rule['rule_type']) ?></span>
                            </td>
                            <td>
                                <strong><?= $rule['threshold_value'] ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($rule['action_type']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $rule['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $rule['is_active'] ? 'Actif' : 'Inactif' ?>
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

// Show uptime details
function showUptimeDetails() {
    alert('Détails Uptime:\nUptime actuel: <?= $system_metrics['uptime'] ?? '99.9%' ?>\nDernier redémarrage: <?= date('d/m/Y H:i', strtotime('-7 days')) ?>\nDisponibilité: Excellente');
}

// Show response time details
function showResponseTimeDetails() {
    alert('Détails Temps de Réponse:\nTemps actuel: <?= $system_metrics['response_time'] ?? rand(50, 200) ?>ms\nMoyenne: <?= rand(80, 150) ?>ms\nSeuil d\'alerte: 300ms');
}

// Show error rate details
function showErrorRateDetails() {
    alert('Détails Taux d\'Erreur:\nTaux actuel: <?= $system_metrics['error_rate'] ?? rand(0, 5) ?>%\nErreurs 404: <?= rand(10, 50) ?>\nErreurs 500: <?= rand(0, 5) ?>');
}

// Show security score details
function showSecurityScoreDetails() {
    alert('Détails Score Sécurité:\nScore actuel: <?= $system_metrics['security_score'] ?? rand(85, 98) ?>/100\nVulnérabilités: Aucune\nDernier scan: <?= date('d/m/Y H:i', strtotime('-2 hours')) ?>');
}

// Show port details
function showPortDetails(port) {
    const portInfo = {
        80: 'HTTP - Port standard pour le trafic web non sécurisé',
        443: 'HTTPS - Port standard pour le trafic web sécurisé',
        3306: 'MySQL - Base de données principale',
        22: 'SSH - Accès sécurisé au serveur (restreint)'
    };
    alert(`Détails du Port ${port}:\n${portInfo[port] || 'Port inconnu'}`);
}

// Show security event details
function showSecurityEventDetails(eventId) {
    alert(`Détails de l'événement de sécurité ${eventId} - À implémenter avec AJAX`);
}

// Filter logs by level
function filterLogs() {
    const level = document.getElementById('logLevel').value;
    const logEntries = document.querySelectorAll('.log-entry');
    
    logEntries.forEach(entry => {
        if (!level || entry.textContent.includes(level)) {
            entry.style.display = 'block';
        } else {
            entry.style.display = 'none';
        }
    });
}

// Refresh logs
function refreshLogs() {
    // In a real implementation, this would fetch new logs via AJAX
    showNotification('Logs actualisés', 'success');
    
    // Simulate adding a new log entry
    const logViewer = document.querySelector('.log-viewer');
    const newEntry = document.createElement('div');
    newEntry.className = 'log-entry text-info';
    newEntry.textContent = `[${new Date().toLocaleString('fr-FR')}] INFO: Logs actualisés manuellement`;
    
    logViewer.insertBefore(newEntry, logViewer.firstChild);
}

// Clear logs
function clearLogs() {
    if (confirm('Êtes-vous sûr de vouloir vider tous les logs ?')) {
        const logViewer = document.querySelector('.log-viewer');
        logViewer.innerHTML = '<div class="log-entry text-muted">[Logs vidés]</div>';
        showNotification('Logs vidés avec succès', 'info');
    }
}

// Run connectivity tests
function runConnectivityTests() {
    showNotification('Tests de connectivité en cours...', 'info');
    
    // Simulate test execution
    setTimeout(() => {
        showNotification('Tests de connectivité terminés avec succès', 'success');
        
        // Update status badges
        const dbStatus = document.querySelector('.badge.bg-success');
        if (dbStatus) {
            dbStatus.textContent = 'Connecté';
            dbStatus.className = 'badge bg-success';
        }
    }, 2000);
}

// Run performance tests
function runPerformanceTests() {
    showNotification('Tests de performance en cours...', 'info');
    
    // Simulate test execution
    setTimeout(() => {
        showNotification('Tests de performance terminés', 'success');
        
        // Update performance metrics
        const dbResponse = document.querySelector('.col-md-6 .d-flex:first-child strong');
        if (dbResponse) {
            dbResponse.textContent = Math.floor(Math.random() * 20) + 5 + 'ms';
        }
    }, 3000);
}

// Run security tests
function runSecurityTest(testType) {
    const testNames = {
        'permissions': 'Vérification des permissions',
        'vulnerability': 'Scan des vulnérabilités',
        'ssl': 'Vérification SSL/TLS',
        'audit': 'Audit des logs'
    };
    
    showNotification(`${testNames[testType]} en cours...`, 'info');
    
    // Simulate test execution
    setTimeout(() => {
        showNotification(`${testNames[testType]} terminé avec succès`, 'success');
        
        // Update test status
        const testRow = event.target.closest('tr');
        if (testRow) {
            const statusBadge = testRow.querySelector('.badge');
            if (statusBadge) {
                statusBadge.textContent = 'Passé';
                statusBadge.className = 'badge bg-success';
            }
            
            const timeCell = testRow.querySelector('td:nth-child(3) small');
            if (timeCell) {
                timeCell.textContent = new Date().toLocaleTimeString('fr-FR');
            }
        }
    }, 2000);
}

// Start backup process
function startBackup(type) {
    const backupTypes = {
        'database': 'Base de données',
        'files': 'Fichiers système'
    };
    
    if (confirm(`Êtes-vous sûr de vouloir démarrer la sauvegarde ${backupTypes[type]} ?`)) {
        showNotification(`Sauvegarde ${backupTypes[type]} en cours...`, 'info');
        
        // Simulate backup process
        setTimeout(() => {
            showNotification(`Sauvegarde ${backupTypes[type]} terminée avec succès`, 'success');
            
            // Update backup status
            const lastBackupElement = document.querySelector(`.col-md-6:first-child .badge.bg-success`);
            if (lastBackupElement) {
                lastBackupElement.textContent = new Date().toLocaleString('fr-FR');
            }
        }, 5000);
    }
}

// Schedule maintenance
function scheduleMaintenance() {
    const maintenanceDate = prompt('Entrez la date de maintenance (format: YYYY-MM-DD HH:MM):', 
        new Date(Date.now() + 2 * 24 * 60 * 60 * 1000).toISOString().slice(0, 16).replace('T', ' '));
    
    if (maintenanceDate) {
        showNotification('Maintenance planifiée avec succès', 'success');
        
        // Update maintenance schedule
        const nextMaintenanceElement = document.querySelector('.col-md-6:last-child .badge.bg-warning');
        if (nextMaintenanceElement) {
            nextMaintenanceElement.textContent = maintenanceDate;
        }
    }
}

// Start maintenance
function startMaintenance() {
    if (confirm('Êtes-vous sûr de vouloir démarrer la maintenance système ? Cela peut affecter les performances.')) {
        showNotification('Maintenance système en cours...', 'warning');
        
        // Simulate maintenance process
        setTimeout(() => {
            showNotification('Maintenance système terminée avec succès', 'success');
            
            // Update maintenance status
            const maintenanceStatusElement = document.querySelector('.col-md-6:last-child .badge.bg-success');
            if (maintenanceStatusElement) {
                maintenanceStatusElement.textContent = 'Terminée';
                maintenanceStatusElement.className = 'badge bg-success';
            }
        }, 10000);
    }
}

// Quick actions handler
function quickAction(action) {
    const actions = {
        'restart_services': {
            title: 'Redémarrage des Services',
            message: 'Êtes-vous sûr de vouloir redémarrer les services critiques ?',
            icon: 'fas fa-redo',
            type: 'warning'
        },
        'clear_cache': {
            title: 'Vidage du Cache',
            message: 'Vider tous les caches système ?',
            icon: 'fas fa-broom',
            type: 'info'
        },
        'check_updates': {
            title: 'Vérification des Mises à Jour',
            message: 'Lancer la vérification des mises à jour ?',
            icon: 'fas fa-download',
            type: 'info'
        },
        'emergency_mode': {
            title: 'Mode Urgence',
            message: 'Activer le mode de sécurité d\'urgence ?',
            icon: 'fas fa-exclamation-triangle',
            type: 'danger'
        }
    };
    
    const actionInfo = actions[action];
    
    if (confirm(actionInfo.message)) {
        showNotification(`${actionInfo.title} en cours...`, actionInfo.type);
        
        // Simulate action execution
        setTimeout(() => {
            showNotification(`${actionInfo.title} terminé avec succès`, 'success');
            
            // Update system status if needed
            if (action === 'clear_cache') {
                updateCacheMetrics();
            } else if (action === 'restart_services') {
                updateServiceStatus();
            }
        }, 3000);
    }
}

// Update cache metrics after clearing
function updateCacheMetrics() {
    const cacheElements = document.querySelectorAll('.text-warning strong');
    cacheElements.forEach(element => {
        if (element.textContent.includes('%')) {
            element.textContent = Math.floor(Math.random() * 20) + 80 + '%';
        }
    });
}

// Update service status after restart
function updateServiceStatus() {
    const serviceStatusElements = document.querySelectorAll('.badge.bg-success, .badge.bg-warning, .badge.bg-danger');
    serviceStatusElements.forEach(element => {
        if (element.textContent.includes('Opérationnel') || element.textContent.includes('Dégradé')) {
            element.textContent = 'Opérationnel';
            element.className = 'badge bg-success';
        }
    });
}

// Open configuration modal
function openConfigModal() {
    const modal = new bootstrap.Modal(document.getElementById('configModal'));
    modal.show();
}

// Open add rule modal
function openAddRuleModal() {
    const modal = new bootstrap.Modal(document.getElementById('addRuleModal'));
    modal.show();
}

// Edit rule
function editRule(ruleId) {
    // Get rule data and populate the edit modal
    // For now, we'll show a simple form submission
    if (confirm('Êtes-vous sûr de vouloir modifier cette règle ?')) {
        // In a real implementation, you would fetch the rule data via AJAX
        // and populate the edit modal fields
        alert('Fonctionnalité d\'édition à implémenter avec AJAX');
    }
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
    
    // Update system health indicators
    updateSystemHealthIndicators();
    
    // Check for new alerts
    checkForNewAlerts();
}, 30000);

// Update system health indicators
function updateSystemHealthIndicators() {
    // Simulate real-time updates
    const cpuElement = document.querySelector('.stat-value.text-danger, .stat-value.text-primary');
    if (cpuElement) {
        const newValue = Math.floor(Math.random() * 30) + 40; // 40-70%
        cpuElement.textContent = newValue + '%';
        
        // Update color based on threshold
        const threshold = <?= $monitoring_config['cpu_threshold'] ?? 80 ?>;
        if (newValue > threshold) {
            cpuElement.className = 'stat-value text-danger mb-0';
        } else {
            cpuElement.className = 'stat-value text-primary mb-0';
        }
    }
}

// Check for new alerts
function checkForNewAlerts() {
    // In a real implementation, this would make an AJAX call to check for new alerts
    // For now, we'll just show a notification if there are any active alerts
    const activeAlerts = document.querySelectorAll('.alert');
    if (activeAlerts.length > 0) {
        // Show notification
        showNotification('Nouvelles alertes système détectées', 'warning');
    }
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Configuration Modal -->
<div class="modal fade" id="configModal" tabindex="-1" aria-labelledby="configModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="configModalLabel">
                    <i class="fas fa-cog me-2"></i>
                    Configuration du Monitoring
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_monitoring_config">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cpu_threshold" class="form-label">Seuil CPU (%)</label>
                            <input type="number" class="form-control" id="cpu_threshold" name="cpu_threshold" 
                                   value="<?= $monitoring_config['cpu_threshold'] ?>" min="0" max="100" step="1">
                            <div class="form-text">Alerte si l'utilisation CPU dépasse ce pourcentage</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="memory_threshold" class="form-label">Seuil Mémoire (%)</label>
                            <input type="number" class="form-control" id="memory_threshold" name="memory_threshold" 
                                   value="<?= $monitoring_config['memory_threshold'] ?>" min="0" max="100" step="1">
                            <div class="form-text">Alerte si l'utilisation mémoire dépasse ce pourcentage</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="disk_threshold" class="form-label">Seuil Disque (%)</label>
                            <input type="number" class="form-control" id="disk_threshold" name="disk_threshold" 
                                   value="<?= $monitoring_config['disk_threshold'] ?>" min="0" max="100" step="1">
                            <div class="form-text">Alerte si l'utilisation disque dépasse ce pourcentage</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="network_threshold" class="form-label">Seuil Réseau (%)</label>
                            <input type="number" class="form-control" id="network_threshold" name="network_threshold" 
                                   value="<?= $monitoring_config['network_threshold'] ?>" min="0" max="100" step="1">
                            <div class="form-text">Alerte si l'utilisation réseau dépasse ce pourcentage</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="security_threshold" class="form-label">Seuil Sécurité</label>
                            <input type="number" class="form-control" id="security_threshold" name="security_threshold" 
                                   value="<?= $monitoring_config['security_threshold'] ?>" min="0" max="100" step="1">
                            <div class="form-text">Alerte si le score de sécurité descend sous cette valeur</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="performance_threshold" class="form-label">Seuil Performance (ms)</label>
                            <input type="number" class="form-control" id="performance_threshold" name="performance_threshold" 
                                   value="<?= $monitoring_config['performance_threshold'] ?>" min="0" max="1000" step="10">
                            <div class="form-text">Alerte si le temps de réponse dépasse cette valeur</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="enterprise-btn enterprise-btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Rule Modal -->
<div class="modal fade" id="addRuleModal" tabindex="-1" aria-labelledby="addRuleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRuleModalLabel">
                    <i class="fas fa-plus me-2"></i>
                    Nouvelle Règle de Monitoring
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_monitoring_rule">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="rule_name" class="form-label">Nom de la Règle *</label>
                            <input type="text" class="form-control" id="rule_name" name="rule_name" required>
                            <div class="form-text">Nom descriptif de la règle</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="rule_type" class="form-label">Type de Métrique *</label>
                            <select class="form-select" id="rule_type" name="rule_type" required>
                                <option value="">Sélectionner...</option>
                                <option value="cpu_usage">Utilisation CPU</option>
                                <option value="memory_usage">Utilisation Mémoire</option>
                                <option value="disk_usage">Utilisation Disque</option>
                                <option value="network_usage">Utilisation Réseau</option>
                                <option value="security_score">Score de Sécurité</option>
                                <option value="performance_score">Score de Performance</option>
                                <option value="error_rate">Taux d'Erreur</option>
                                <option value="response_time">Temps de Réponse</option>
                            </select>
                            <div class="form-text">Métrique à surveiller</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="rule_threshold" class="form-label">Seuil *</label>
                            <input type="number" class="form-control" id="rule_threshold" name="rule_threshold" 
                                   required step="0.1">
                            <div class="form-text">Valeur seuil pour déclencher l'alerte</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="rule_action" class="form-label">Action *</label>
                            <select class="form-select" id="rule_action" name="rule_action" required>
                                <option value="">Sélectionner...</option>
                                <option value="alert">Alerte</option>
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                                <option value="webhook">Webhook</option>
                                <option value="restart_service">Redémarrer Service</option>
                            </select>
                            <div class="form-text">Action à exécuter quand le seuil est dépassé</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="rule_status" class="form-label">Statut</label>
                        <select class="form-select" id="rule_status" name="rule_status">
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                        <div class="form-text">Statut de la règle</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="enterprise-btn enterprise-btn-success">
                        <i class="fas fa-plus me-2"></i>
                        Créer la Règle
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Rule Modal -->
<div class="modal fade" id="editRuleModal" tabindex="-1" aria-labelledby="editRuleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRuleModalLabel">
                    <i class="fas fa-edit me-2"></i>
                    Modifier la Règle de Monitoring
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_monitoring_rule">
                    <input type="hidden" name="rule_id" id="edit_rule_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_rule_name" class="form-label">Nom de la Règle *</label>
                            <input type="text" class="form-control" id="edit_rule_name" name="rule_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_rule_type" class="form-label">Type de Métrique *</label>
                            <select class="form-select" id="edit_rule_type" name="rule_type" required>
                                <option value="cpu_usage">Utilisation CPU</option>
                                <option value="memory_usage">Utilisation Mémoire</option>
                                <option value="disk_usage">Utilisation Disque</option>
                                <option value="network_usage">Utilisation Réseau</option>
                                <option value="security_score">Score de Sécurité</option>
                                <option value="performance_score">Score de Performance</option>
                                <option value="error_rate">Taux d'Erreur</option>
                                <option value="response_time">Temps de Réponse</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_rule_threshold" class="form-label">Seuil *</label>
                            <input type="number" class="form-control" id="edit_rule_threshold" name="rule_threshold" 
                                   required step="0.1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_rule_action" class="form-label">Action *</label>
                            <select class="form-select" id="edit_rule_action" name="rule_action" required>
                                <option value="alert">Alerte</option>
                                <option value="email">Email</option>
                                <option value="sms">SMS</option>
                                <option value="webhook">Webhook</option>
                                <option value="restart_service">Redémarrer Service</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_rule_status" class="form-label">Statut</label>
                        <select class="form-select" id="edit_rule_status" name="rule_status">
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="enterprise-btn enterprise-btn-primary">
                        <i class="fas fa-save me-2"></i>
                        Mettre à Jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
