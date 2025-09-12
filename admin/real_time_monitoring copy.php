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
