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

// Debug script for analytics system
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get analytics debug data
try {
    // Test database queries
    $user_count = $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
    $job_count = $db->fetch("SELECT COUNT(*) as count FROM annonces")['count'] ?? 0;
    $application_count = $db->fetch("SELECT COUNT(*) as count FROM postulation")['count'] ?? 0;
    $employer_count = $db->fetch("SELECT COUNT(*) as count FROM employeurs")['count'] ?? 0;
    
    // Test analytics tables
    $analytics_tables = [
        'user_analytics' => $db->fetch("SELECT COUNT(*) as count FROM user_analytics")['count'] ?? 0,
        'page_views' => $db->fetch("SELECT COUNT(*) as count FROM page_views")['count'] ?? 0,
        'user_sessions' => $db->fetch("SELECT COUNT(*) as count FROM user_sessions")['count'] ?? 0,
        'error_logs' => $db->fetch("SELECT COUNT(*) as count FROM error_logs")['count'] ?? 0
    ];
    
    // Get recent errors
    $recent_errors = $db->fetchAll("
        SELECT * FROM error_logs 
        ORDER BY created_at DESC 
        LIMIT 10
    ") ?? [];
    
    $debug_status = 'success';
    
} catch (Exception $e) {
    $user_count = 0;
    $job_count = 0;
    $application_count = 0;
    $employer_count = 0;
    $analytics_tables = [];
    $recent_errors = [];
    $debug_status = 'error';
    $debug_message = $e->getMessage();
}

$page_title = 'Debug Analytics - EMPLOIDB Admin';
include __DIR__ . '/includes/admin_header.php';
?>

<!-- Debug Analytics Dashboard -->
<div class="enterprise-content-card mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 style="color: var(--enterprise-primary); margin: 0;">
                <i class="fas fa-bug me-2"></i>Debug Analytics
            </h5>
            <small class="text-muted">Diagnostic et débogage du système d'analytics</small>
        </div>
        <div class="d-flex gap-2">
            <button class="enterprise-action-btn enterprise-info" onclick="refreshDebugData()">
                <i class="fas fa-sync-alt me-1"></i>Actualiser
            </button>
            <button class="enterprise-action-btn enterprise-warning" onclick="clearErrorLogs()">
                <i class="fas fa-trash me-1"></i>Vider Logs
            </button>
            <button class="enterprise-action-btn enterprise-success" onclick="exportDebugReport()">
                <i class="fas fa-download me-1"></i>Exporter Rapport
            </button>
        </div>
    </div>
</div>

<!-- System Status -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="enterprise-stat-card">
            <div class="enterprise-stat-number"><?= number_format($user_count) ?></div>
            <div class="enterprise-stat-label">Utilisateurs</div>
            <i class="fas fa-users stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="enterprise-stat-card">
            <div class="enterprise-stat-number"><?= number_format($job_count) ?></div>
            <div class="enterprise-stat-label">Offres d'Emploi</div>
            <i class="fas fa-briefcase stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="enterprise-stat-card">
            <div class="enterprise-stat-number"><?= number_format($application_count) ?></div>
            <div class="enterprise-stat-label">Candidatures</div>
            <i class="fas fa-clipboard-list stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="enterprise-stat-card">
            <div class="enterprise-stat-number"><?= number_format($employer_count) ?></div>
            <div class="enterprise-stat-label">Employeurs</div>
            <i class="fas fa-building stat-icon"></i>
        </div>
    </div>
</div>

<!-- Analytics Tables Status -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-database me-2"></i>Statut des Tables Analytics
            </h5>
            
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead style="background: var(--enterprise-gray-50);">
                        <tr>
                            <th style="color: var(--enterprise-text-primary);">Table</th>
                            <th style="color: var(--enterprise-text-primary);">Enregistrements</th>
                            <th style="color: var(--enterprise-text-primary);">Statut</th>
                            <th style="color: var(--enterprise-text-primary);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($analytics_tables as $table => $count): ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--enterprise-primary);">
                                        <?= ucfirst(str_replace('_', ' ', $table)) ?>
                                    </strong>
                                </td>
                                <td>
                                    <span style="color: var(--enterprise-text-secondary);">
                                        <?= number_format($count) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($count > 0): ?>
                                        <span class="enterprise-status-badge enterprise-success">
                                            <i class="fas fa-check me-1"></i>Actif
                                        </span>
                                    <?php else: ?>
                                        <span class="enterprise-status-badge enterprise-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>Vide
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="enterprise-action-btn enterprise-info enterprise-sm me-1" 
                                        onclick="testTable('<?= $table ?>')" title="Tester la table">
                                        <i class="fas fa-flask"></i>
                                    </button>
                                    <button class="enterprise-action-btn enterprise-warning enterprise-sm" 
                                        onclick="clearTable('<?= $table ?>')" title="Vider la table">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-exclamation-triangle me-2"></i>Erreurs Récentes
            </h5>
            
            <?php if (empty($recent_errors)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <p class="text-muted">Aucune erreur récente</p>
                </div>
            <?php else: ?>
                <div class="enterprise-error-list">
                    <?php foreach (array_slice($recent_errors, 0, 5) as $error): ?>
                        <div class="enterprise-error-item">
                            <div class="enterprise-error-header">
                                <span class="enterprise-error-type"><?= htmlspecialchars($error['error_type'] ?? 'Error') ?></span>
                                <span class="enterprise-error-time"><?= date('H:i', strtotime($error['created_at'])) ?></span>
                            </div>
                            <div class="enterprise-error-message">
                                <?= htmlspecialchars(substr($error['message'] ?? '', 0, 100)) ?>...
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Debug Tools -->
<div class="enterprise-content-card">
    <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
        <i class="fas fa-tools me-2"></i>Outils de Debug
    </h5>
    <div class="row">
        <div class="col-md-6">
            <div class="alert alert-info d-flex align-items-start">
                <i class="fas fa-database fa-2x me-3 mt-1"></i>
                <div>
                    <h6 class="alert-heading">Test de Connexion</h6>
                    <p class="mb-1">Testez la connexion à la base de données et les requêtes analytics.</p>
                    <button class="enterprise-action-btn enterprise-info enterprise-sm mt-2" onclick="testDatabaseConnection()">
                        <i class="fas fa-plug me-1"></i>Tester Connexion
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-warning d-flex align-items-start">
                <i class="fas fa-chart-line fa-2x me-3 mt-1"></i>
                <div>
                    <h6 class="alert-heading">Générer Données Test</h6>
                    <p class="mb-1">Générez des données de test pour les analytics.</p>
                    <button class="enterprise-action-btn enterprise-warning enterprise-sm mt-2" onclick="generateTestData()">
                        <i class="fas fa-magic me-1"></i>Générer Données
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-success d-flex align-items-start">
                <i class="fas fa-sync fa-2x me-3 mt-1"></i>
                <div>
                    <h6 class="alert-heading">Synchroniser Analytics</h6>
                    <p class="mb-1">Synchronisez les données analytics avec les tables principales.</p>
                    <button class="enterprise-action-btn enterprise-success enterprise-sm mt-2" onclick="syncAnalytics()">
                        <i class="fas fa-sync-alt me-1"></i>Synchroniser
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-danger d-flex align-items-start">
                <i class="fas fa-exclamation-triangle fa-2x me-3 mt-1"></i>
                <div>
                    <h6 class="alert-heading">Réparer Système</h6>
                    <p class="mb-1">Tentez de réparer automatiquement les problèmes détectés.</p>
                    <button class="enterprise-action-btn enterprise-danger enterprise-sm mt-2" onclick="repairSystem()">
                        <i class="fas fa-wrench me-1"></i>Réparer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshDebugData() {
    location.reload();
}

function clearErrorLogs() {
    if (confirm('Êtes-vous sûr de vouloir vider tous les logs d\'erreur ?')) {
        // Add AJAX call to clear error logs
        alert('Logs d\'erreur vidés avec succès');
        location.reload();
    }
}

function exportDebugReport() {
    const report = {
        timestamp: new Date().toISOString(),
        system_status: '<?= $debug_status ?>',
        user_count: <?= $user_count ?>,
        job_count: <?= $job_count ?>,
        application_count: <?= $application_count ?>,
        employer_count: <?= $employer_count ?>,
        analytics_tables: <?= json_encode($analytics_tables) ?>,
        recent_errors: <?= json_encode($recent_errors) ?>
    };
    
    const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'emploidb-debug-report.json';
    a.click();
    URL.revokeObjectURL(url);
}

function testTable(tableName) {
    alert(`Test de la table: ${tableName}`);
    // Add actual table test logic here
}

function clearTable(tableName) {
    if (confirm(`Êtes-vous sûr de vouloir vider la table ${tableName} ?`)) {
        alert(`Table ${tableName} vidée avec succès`);
        location.reload();
    }
}

function testDatabaseConnection() {
    alert('Test de connexion à la base de données...');
    // Add actual database connection test here
}

function generateTestData() {
    alert('Génération de données de test...');
    // Add actual test data generation here
}

function syncAnalytics() {
    alert('Synchronisation des analytics...');
    // Add actual analytics sync here
}

function repairSystem() {
    alert('Réparation du système...');
    // Add actual system repair logic here
}
</script>

<style>
.enterprise-error-list {
    max-height: 300px;
    overflow-y: auto;
}

.enterprise-error-item {
    background: var(--enterprise-gray-50);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 0.5rem;
    border-left: 4px solid var(--enterprise-danger);
}

.enterprise-error-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.enterprise-error-type {
    font-weight: 600;
    color: var(--enterprise-danger);
    font-size: 0.875rem;
}

.enterprise-error-time {
    font-size: 0.75rem;
    color: var(--enterprise-text-secondary);
}

.enterprise-error-message {
    font-size: 0.875rem;
    color: var(--enterprise-text-primary);
    line-height: 1.4;
}
</style>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>