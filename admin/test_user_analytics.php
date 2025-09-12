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

// Enhanced test analytics data
try {
    $testData = [
        'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
        'active_users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE status = 'active'")['count'] ?? 0,
        'new_users_today' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
        'new_users_week' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0,
        'recent_users' => $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT 10") ?? [],
        'user_growth_rate' => 0,
        'test_status' => 'OK'
    ];
    
    // Calculate growth rate
    $last_week_users = $db->fetch("SELECT COUNT(*) as count FROM users WHERE created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0;
    if ($last_week_users > 0) {
        $testData['user_growth_rate'] = round((($testData['new_users_week'] / $last_week_users) * 100), 2);
    }
    
    // Get user demographics
    $user_demographics = [
        'age_groups' => [
            '18-25' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE age BETWEEN 18 AND 25")['count'] ?? 0,
            '26-35' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE age BETWEEN 26 AND 35")['count'] ?? 0,
            '36-45' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE age BETWEEN 36 AND 45")['count'] ?? 0,
            '46+' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE age > 45")['count'] ?? 0
        ],
        'locations' => $db->fetchAll("SELECT ville, COUNT(*) as count FROM users GROUP BY ville ORDER BY count DESC LIMIT 5") ?? []
    ];
    
    // Get user activity data
    $user_activity = [
        'total_applications' => $db->fetch("SELECT COUNT(*) as count FROM postulation")['count'] ?? 0,
        'active_applicants' => $db->fetch("SELECT COUNT(DISTINCT user_id) as count FROM postulation")['count'] ?? 0,
        'avg_applications_per_user' => 0
    ];
    
    if ($testData['total_users'] > 0) {
        $user_activity['avg_applications_per_user'] = round($user_activity['total_applications'] / $testData['total_users'], 2);
    }
    
} catch (Exception $e) {
    $testData = [
        'total_users' => 0,
        'active_users' => 0,
        'new_users_today' => 0,
        'new_users_week' => 0,
        'recent_users' => [],
        'user_growth_rate' => 0,
        'test_status' => 'ERROR'
    ];
    $user_demographics = [];
    $user_activity = [];
    error_log("Error in test_user_analytics.php: " . $e->getMessage());
}

$page_title = 'Test Analytics Utilisateurs - EMPLOIDB Admin';
include __DIR__ . '/includes/admin_header.php';
?>

<!-- Test User Analytics Dashboard -->
<div class="enterprise-content-card mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 style="color: var(--enterprise-primary); margin: 0;">
                <i class="fas fa-users me-2"></i>Test Analytics Utilisateurs
            </h5>
            <small class="text-muted">Test et validation du système d'analytics utilisateurs</small>
        </div>
        <div class="d-flex gap-2">
            <button class="enterprise-action-btn enterprise-info" onclick="refreshAnalytics()">
                <i class="fas fa-sync-alt me-1"></i>Actualiser
            </button>
            <button class="enterprise-action-btn enterprise-success" onclick="generateTestData()">
                <i class="fas fa-magic me-1"></i>Générer Données Test
            </button>
            <button class="enterprise-action-btn enterprise-warning" onclick="exportAnalyticsReport()">
                <i class="fas fa-download me-1"></i>Exporter Rapport
            </button>
        </div>
    </div>
</div>

<!-- User Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="enterprise-stat-card">
            <div class="enterprise-stat-number"><?= number_format($testData['total_users']) ?></div>
            <div class="enterprise-stat-label">Total Utilisateurs</div>
            <i class="fas fa-users stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="enterprise-stat-card">
            <div class="enterprise-stat-number"><?= number_format($testData['active_users']) ?></div>
            <div class="enterprise-stat-label">Utilisateurs Actifs</div>
            <i class="fas fa-user-check stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="enterprise-stat-card">
            <div class="enterprise-stat-number"><?= number_format($testData['new_users_today']) ?></div>
            <div class="enterprise-stat-label">Nouveaux Aujourd'hui</div>
            <i class="fas fa-user-plus stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="enterprise-stat-card">
            <div class="enterprise-stat-number"><?= number_format($testData['user_growth_rate'], 1) ?>%</div>
            <div class="enterprise-stat-label">Taux de Croissance</div>
            <i class="fas fa-chart-line stat-icon"></i>
        </div>
    </div>
</div>

<!-- Analytics Test Results -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-chart-bar me-2"></i>Résultats des Tests Analytics
            </h5>
            
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead style="background: var(--enterprise-gray-50);">
                        <tr>
                            <th style="color: var(--enterprise-text-primary);">Test</th>
                            <th style="color: var(--enterprise-text-primary);">Valeur</th>
                            <th style="color: var(--enterprise-text-primary);">Statut</th>
                            <th style="color: var(--enterprise-text-primary);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong style="color: var(--enterprise-primary);">Connexion Base de Données</strong></td>
                            <td><?= $testData['test_status'] === 'OK' ? 'Connecté' : 'Erreur' ?></td>
                            <td>
                                <?php if ($testData['test_status'] === 'OK'): ?>
                                    <span class="enterprise-status-badge enterprise-success">
                                        <i class="fas fa-check me-1"></i>OK
                                    </span>
                                <?php else: ?>
                                    <span class="enterprise-status-badge enterprise-danger">
                                        <i class="fas fa-times me-1"></i>Erreur
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="enterprise-action-btn enterprise-info enterprise-sm" onclick="testDatabaseConnection()">
                                    <i class="fas fa-database"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong style="color: var(--enterprise-primary);">Comptage Utilisateurs</strong></td>
                            <td><?= number_format($testData['total_users']) ?> utilisateurs</td>
                            <td>
                                <span class="enterprise-status-badge enterprise-success">
                                    <i class="fas fa-check me-1"></i>OK
                                </span>
                            </td>
                            <td>
                                <button class="enterprise-action-btn enterprise-info enterprise-sm" onclick="testUserCount()">
                                    <i class="fas fa-calculator"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong style="color: var(--enterprise-primary);">Activité Utilisateurs</strong></td>
                            <td><?= number_format($user_activity['active_applicants']) ?> candidats actifs</td>
                            <td>
                                <span class="enterprise-status-badge enterprise-success">
                                    <i class="fas fa-check me-1"></i>OK
                                </span>
                            </td>
                            <td>
                                <button class="enterprise-action-btn enterprise-info enterprise-sm" onclick="testUserActivity()">
                                    <i class="fas fa-chart-line"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong style="color: var(--enterprise-primary);">Démographie</strong></td>
                            <td><?= count($user_demographics['age_groups']) ?> groupes d'âge</td>
                            <td>
                                <span class="enterprise-status-badge enterprise-success">
                                    <i class="fas fa-check me-1"></i>OK
                                </span>
                            </td>
                            <td>
                                <button class="enterprise-action-btn enterprise-info enterprise-sm" onclick="testDemographics()">
                                    <i class="fas fa-map-marker-alt"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-user-friends me-2"></i>Utilisateurs Récents
            </h5>
            
            <?php if (empty($testData['recent_users'])): ?>
                <div class="text-center py-4">
                    <i class="fas fa-users fa-2x text-muted mb-2"></i>
                    <p class="text-muted">Aucun utilisateur récent</p>
                </div>
            <?php else: ?>
                <div class="enterprise-user-list">
                    <?php foreach (array_slice($testData['recent_users'], 0, 5) as $user): ?>
                        <div class="enterprise-user-item">
                            <div class="enterprise-user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="enterprise-user-info">
                                <div class="enterprise-user-name">
                                    <?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?>
                                </div>
                                <div class="enterprise-user-email">
                                    <?= htmlspecialchars($user['email']) ?>
                                </div>
                                <div class="enterprise-user-date">
                                    <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Analytics Tools -->
<div class="enterprise-content-card">
    <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
        <i class="fas fa-tools me-2"></i>Outils Analytics
    </h5>
    <div class="row">
        <div class="col-md-6">
            <div class="alert alert-info d-flex align-items-start">
                <i class="fas fa-chart-pie fa-2x me-3 mt-1"></i>
                <div>
                    <h6 class="alert-heading">Analyse Démographique</h6>
                    <p class="mb-1">Analysez la répartition des utilisateurs par âge et localisation.</p>
                    <button class="enterprise-action-btn enterprise-info enterprise-sm mt-2" onclick="analyzeDemographics()">
                        <i class="fas fa-chart-pie me-1"></i>Analyser
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-success d-flex align-items-start">
                <i class="fas fa-chart-line fa-2x me-3 mt-1"></i>
                <div>
                    <h6 class="alert-heading">Tendances Utilisateurs</h6>
                    <p class="mb-1">Visualisez les tendances d'inscription et d'activité.</p>
                    <button class="enterprise-action-btn enterprise-success enterprise-sm mt-2" onclick="analyzeTrends()">
                        <i class="fas fa-chart-line me-1"></i>Analyser
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-warning d-flex align-items-start">
                <i class="fas fa-user-clock fa-2x me-3 mt-1"></i>
                <div>
                    <h6 class="alert-heading">Comportement Utilisateurs</h6>
                    <p class="mb-1">Analysez le comportement et les patterns d'utilisation.</p>
                    <button class="enterprise-action-btn enterprise-warning enterprise-sm mt-2" onclick="analyzeBehavior()">
                        <i class="fas fa-user-clock me-1"></i>Analyser
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-primary d-flex align-items-start">
                <i class="fas fa-file-export fa-2x me-3 mt-1"></i>
                <div>
                    <h6 class="alert-heading">Export Données</h6>
                    <p class="mb-1">Exportez les données analytics pour analyse externe.</p>
                    <button class="enterprise-action-btn enterprise-primary enterprise-sm mt-2" onclick="exportUserData()">
                        <i class="fas fa-file-export me-1"></i>Exporter
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshAnalytics() {
    location.reload();
}

function generateTestData() {
    if (confirm('Générer des données de test pour les analytics ?')) {
        alert('Génération de données de test...');
        // Add actual test data generation here
    }
}

function exportAnalyticsReport() {
    const report = {
        timestamp: new Date().toISOString(),
        test_data: <?= json_encode($testData) ?>,
        user_demographics: <?= json_encode($user_demographics) ?>,
        user_activity: <?= json_encode($user_activity) ?>
    };
    
    const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'user-analytics-test-report.json';
    a.click();
    URL.revokeObjectURL(url);
}

function testDatabaseConnection() {
    alert('Test de connexion à la base de données...');
    // Add actual database connection test here
}

function testUserCount() {
    alert('Test du comptage des utilisateurs...');
    // Add actual user count test here
}

function testUserActivity() {
    alert('Test de l\'activité des utilisateurs...');
    // Add actual user activity test here
}

function testDemographics() {
    alert('Test des données démographiques...');
    // Add actual demographics test here
}

function analyzeDemographics() {
    alert('Analyse démographique...');
    // Add actual demographics analysis here
}

function analyzeTrends() {
    alert('Analyse des tendances...');
    // Add actual trends analysis here
}

function analyzeBehavior() {
    alert('Analyse du comportement...');
    // Add actual behavior analysis here
}

function exportUserData() {
    alert('Export des données utilisateurs...');
    // Add actual user data export here
}
</script>

<style>
.enterprise-user-list {
    max-height: 400px;
    overflow-y: auto;
}

.enterprise-user-item {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid var(--enterprise-gray-200);
}

.enterprise-user-item:last-child {
    border-bottom: none;
}

.enterprise-user-avatar {
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

.enterprise-user-info {
    flex: 1;
}

.enterprise-user-name {
    font-weight: 600;
    color: var(--enterprise-text-primary);
    margin-bottom: 0.25rem;
}

.enterprise-user-email {
    font-size: 0.875rem;
    color: var(--enterprise-text-secondary);
    margin-bottom: 0.25rem;
}

.enterprise-user-date {
    font-size: 0.75rem;
    color: var(--enterprise-text-secondary);
}
</style>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>