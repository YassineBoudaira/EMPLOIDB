<?php
// Login Monitoring Dashboard for Admin Panel
$page_title = 'Login Security Monitoring - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Check if user has access to monitoring
if (!$rbac->hasPageAccess($_SESSION['admin_id'] ?? 1, 'monitoring')) {
    $_SESSION['error'] = 'Accès non autorisé à cette page.';
    header('Location: dashboard.php');
    exit;
}

// Include LoginSecurity class
include __DIR__ . '/../include/LoginSecurity.php';
$loginSecurity = new LoginSecurity($db);

// Get monitoring data
$loginStats = $loginSecurity->getLoginStats(30); // Last 30 days
$recentAttempts = $loginSecurity->getRecentLoginAttempts(100);
$securityAlerts = $db->fetchAll("
    SELECT * FROM security_alerts 
    WHERE is_resolved = FALSE 
    ORDER BY created_at DESC 
    LIMIT 20
");

// Get daily login statistics for chart
$dailyStats = $db->fetchAll("
    SELECT 
        DATE(login_time) as date,
        COUNT(*) as total_attempts,
        SUM(CASE WHEN login_status = 'success' THEN 1 ELSE 0 END) as successful,
        SUM(CASE WHEN login_status = 'failed' THEN 1 ELSE 0 END) as failed
    FROM login_audit 
    WHERE login_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(login_time)
    ORDER BY date DESC
");

// Get IP statistics
$ipStats = $db->fetchAll("
    SELECT 
        ip_address,
        COUNT(*) as attempts,
        SUM(CASE WHEN login_status = 'success' THEN 1 ELSE 0 END) as successful,
        SUM(CASE WHEN login_status = 'failed' THEN 1 ELSE 0 END) as failed,
        MAX(login_time) as last_attempt
    FROM login_audit 
    WHERE login_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY ip_address
    HAVING attempts > 5
    ORDER BY attempts DESC
    LIMIT 20
");

// Get blocked IPs
$blockedIps = $db->fetchAll("
    SELECT * FROM ip_blocklist 
    WHERE blocked_until IS NULL OR blocked_until > NOW()
    ORDER BY last_attempt DESC
");
?>

<!-- Enterprise Login Monitoring Content -->
<div class="fade-in">
    <!-- Page Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-shield-alt me-3"></i>
                        Login Security Monitoring
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Surveillance des connexions, sécurité et alertes système
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="refreshMonitoringData()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="exportSecurityReport()">
                        <i class="fas fa-download"></i>
                        Exporter Rapport
                    </button>
                    <button class="enterprise-btn enterprise-btn-warning" onclick="cleanupOldSessions()">
                        <i class="fas fa-broom"></i>
                        Nettoyer Sessions
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Statistics -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-sign-in-alt fa-2x text-primary"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-primary mb-0"><?= number_format($loginStats['total_attempts'] ?? 0) ?></h3>
                            <small class="text-muted">Tentatives Total</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-info">
                            <i class="fas fa-calendar"></i>
                            30 derniers jours
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showLoginDetails()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-success mb-0"><?= number_format($loginStats['successful_logins'] ?? 0) ?></h3>
                            <small class="text-muted">Connexions Réussies</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            <?= $loginStats['total_attempts'] > 0 ? round(($loginStats['successful_logins'] / $loginStats['total_attempts']) * 100, 1) : 0 ?>%
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showSuccessfulLogins()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-danger mb-0"><?= number_format($loginStats['failed_logins'] ?? 0) ?></h3>
                            <small class="text-muted">Échecs de Connexion</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-danger">
                            <i class="fas fa-arrow-up"></i>
                            <?= $loginStats['total_attempts'] > 0 ? round(($loginStats['failed_logins'] / $loginStats['total_attempts']) * 100, 1) : 0 ?>%
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showFailedLogins()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-ban fa-2x text-warning"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-warning mb-0"><?= number_format($loginStats['blocked_ips'] ?? 0) ?></h3>
                            <small class="text-muted">IPs Bloquées</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-warning">
                            <i class="fas fa-shield-alt"></i>
                            Actives
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showBlockedIPs()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-info bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-info mb-0"><?= number_format($loginStats['active_sessions'] ?? 0) ?></h3>
                            <small class="text-muted">Sessions Actives</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-info">
                            <i class="fas fa-clock"></i>
                            En cours
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showActiveSessions()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-purple bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-exclamation-triangle fa-2x text-purple"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-purple mb-0"><?= count($securityAlerts) ?></h3>
                            <small class="text-muted">Alertes Sécurité</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-purple">
                            <i class="fas fa-bell"></i>
                            Non résolues
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showSecurityAlerts()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="enterprise-card">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-chart-line me-2"></i>
                        Évolution des Connexions (30 derniers jours)
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <canvas id="loginChart" height="100"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="enterprise-card">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-chart-pie me-2"></i>
                        Répartition des Tentatives
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <canvas id="loginPieChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Login Attempts -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="enterprise-card">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-history me-2"></i>
                        Tentatives de Connexion Récentes
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>IP</th>
                                    <th>Statut</th>
                                    <th>Date/Heure</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recentAttempts, 0, 20) as $attempt): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-envelope me-2"></i>
                                        <?= htmlspecialchars($attempt['email']) ?>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($attempt['ip_address']) ?></code>
                                    </td>
                                    <td>
                                        <?php if ($attempt['login_status'] === 'success'): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Réussi
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times"></i> Échec
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?= date('d/m/Y H:i', strtotime($attempt['login_time'])) ?></small>
                                    </td>
                                    <td>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" 
                                                onclick="viewLoginDetails(<?= $attempt['id'] ?>)">
                                            <i class="fas fa-eye"></i>
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
        
        <div class="col-lg-4 mb-4">
            <div class="enterprise-card">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Alertes Sécurité
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <?php if (empty($securityAlerts)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-shield-check fa-3x text-success mb-3"></i>
                            <p class="text-muted">Aucune alerte de sécurité active</p>
                        </div>
                    <?php else: ?>
                        <?php foreach (array_slice($securityAlerts, 0, 10) as $alert): ?>
                        <div class="alert alert-<?= $alert['severity'] === 'critical' ? 'danger' : ($alert['severity'] === 'high' ? 'warning' : 'info') ?> mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong><?= htmlspecialchars($alert['title']) ?></strong>
                                    <p class="mb-1"><?= htmlspecialchars($alert['message']) ?></p>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        <?= date('d/m/Y H:i', strtotime($alert['created_at'])) ?>
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary" 
                                        onclick="resolveAlert(<?= $alert['id'] ?>)">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Blocked IPs -->
    <div class="enterprise-card">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-ban me-2"></i>
                Adresses IP Bloquées
            </h4>
        </div>
        <div class="enterprise-card-body">
            <?php if (empty($blockedIps)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-shield-check fa-3x text-success mb-3"></i>
                    <p class="text-muted">Aucune adresse IP bloquée</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Adresse IP</th>
                                <th>Tentatives Échouées</th>
                                <th>Première Tentative</th>
                                <th>Dernière Tentative</th>
                                <th>Bloqué Jusqu'à</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blockedIps as $blocked): ?>
                            <tr>
                                <td>
                                    <code><?= htmlspecialchars($blocked['ip_address']) ?></code>
                                </td>
                                <td>
                                    <span class="badge bg-danger"><?= $blocked['failed_attempts'] ?></span>
                                </td>
                                <td>
                                    <small><?= date('d/m/Y H:i', strtotime($blocked['first_attempt'])) ?></small>
                                </td>
                                <td>
                                    <small><?= date('d/m/Y H:i', strtotime($blocked['last_attempt'])) ?></small>
                                </td>
                                <td>
                                    <?php if ($blocked['blocked_until']): ?>
                                        <small class="text-warning">
                                            <?= date('d/m/Y H:i', strtotime($blocked['blocked_until'])) ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Permanent</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="enterprise-btn enterprise-btn-sm enterprise-btn-success" 
                                            onclick="unblockIP('<?= $blocked['ip_address'] ?>')">
                                        <i class="fas fa-unlock"></i> Débloquer
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Login Statistics Chart
const loginCtx = document.getElementById('loginChart').getContext('2d');
const loginChart = new Chart(loginCtx, {
    type: 'line',
    data: {
        labels: [<?php 
            $labels = array_reverse(array_column($dailyStats, 'date'));
            echo "'" . implode("','", $labels) . "'";
        ?>],
        datasets: [{
            label: 'Connexions Réussies',
            data: [<?php 
                $successful = array_reverse(array_column($dailyStats, 'successful'));
                echo implode(',', $successful);
            ?>],
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4
        }, {
            label: 'Échecs de Connexion',
            data: [<?php 
                $failed = array_reverse(array_column($dailyStats, 'failed'));
                echo implode(',', $failed);
            ?>],
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Login Pie Chart
const pieCtx = document.getElementById('loginPieChart').getContext('2d');
const pieChart = new Chart(pieCtx, {
    type: 'doughnut',
    data: {
        labels: ['Connexions Réussies', 'Échecs de Connexion'],
        datasets: [{
            data: [<?= $loginStats['successful_logins'] ?? 0 ?>, <?= $loginStats['failed_logins'] ?? 0 ?>],
            backgroundColor: ['#10b981', '#ef4444'],
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

// Functions
function refreshMonitoringData() {
    location.reload();
}

function exportSecurityReport() {
    window.open('export_security_report.php', '_blank');
}

function cleanupOldSessions() {
    if (confirm('Êtes-vous sûr de vouloir nettoyer les anciennes sessions?')) {
        fetch('ajax/cleanup_sessions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'cleanup_sessions'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Sessions nettoyées avec succès!');
                location.reload();
            } else {
                alert('Erreur lors du nettoyage: ' + data.message);
            }
        });
    }
}

function viewLoginDetails(attemptId) {
    // Implementation for viewing login attempt details
    console.log('View login details for attempt:', attemptId);
}

function resolveAlert(alertId) {
    if (confirm('Marquer cette alerte comme résolue?')) {
        fetch('ajax/resolve_alert.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                alert_id: alertId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    }
}

function unblockIP(ipAddress) {
    if (confirm('Débloquer cette adresse IP?')) {
        fetch('ajax/unblock_ip.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                ip_address: ipAddress
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Adresse IP débloquée avec succès!');
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    }
}

// Auto-refresh every 30 seconds
setInterval(function() {
    // Refresh only the statistics cards
    fetch('ajax/get_login_stats.php')
        .then(response => response.json())
        .then(data => {
            // Update statistics display
            console.log('Login stats updated:', data);
        });
}, 30000);
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
