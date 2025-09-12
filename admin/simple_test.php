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

// Enhanced test data
$tests = [
    'config' => file_exists(__DIR__ . '/../include/config.php'),
    'connexion' => file_exists(__DIR__ . '/../include/connexion.php'),
    'security' => class_exists('Security'),
    'database' => isset($db),
    'session' => session_status() === PHP_SESSION_ACTIVE,
    'admin_user' => Security::isAdmin(),
    'database_connection' => false,
    'css_files' => file_exists(__DIR__ . '/../assets/css/emploidb-design-system.css'),
    'js_files' => file_exists(__DIR__ . '/../assets/js/emploidb-admin.js')
];

// Test database connection
try {
    if (isset($db)) {
        $db->fetch("SELECT 1");
        $tests['database_connection'] = true;
    }
} catch (Exception $e) {
    $tests['database_connection'] = false;
}

// Get system information
$system_info = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size')
];

$page_title = 'Test Simple - EMPLOIDB Admin';
include __DIR__ . '/includes/admin_header.php';
?>

<!-- Enhanced Test Dashboard -->
<div class="enterprise-content-card mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 style="color: var(--enterprise-primary); margin: 0;">
                <i class="fas fa-flask me-2"></i>Test Simple du Système
            </h5>
            <small class="text-muted">Vérification de l'intégrité du système EMPLOIDB</small>
        </div>
        <div class="d-flex gap-2">
            <button class="enterprise-action-btn enterprise-info" onclick="runAllTests()">
                <i class="fas fa-sync-alt me-1"></i>Relancer Tests
            </button>
            <button class="enterprise-action-btn enterprise-success" onclick="exportTestResults()">
                <i class="fas fa-download me-1"></i>Exporter Résultats
            </button>
        </div>
    </div>
</div>

<!-- Test Results Grid -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-check-circle me-2"></i>Résultats des Tests
            </h5>
            
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead style="background: var(--enterprise-gray-50);">
                        <tr>
                            <th style="color: var(--enterprise-text-primary);">Test</th>
                            <th style="color: var(--enterprise-text-primary);">Statut</th>
                            <th style="color: var(--enterprise-text-primary);">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $test_descriptions = [
                            'config' => 'Fichier de configuration principal',
                            'connexion' => 'Fichier de connexion à la base de données',
                            'security' => 'Classe de sécurité et authentification',
                            'database' => 'Instance de base de données',
                            'session' => 'Gestion des sessions PHP',
                            'admin_user' => 'Authentification administrateur',
                            'database_connection' => 'Connexion active à la base de données',
                            'css_files' => 'Fichiers CSS du design system',
                            'js_files' => 'Fichiers JavaScript admin'
                        ];
                        
                        foreach ($tests as $test => $result): ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--enterprise-primary);">
                                        <?= ucfirst(str_replace('_', ' ', $test)) ?>
                                    </strong>
                                </td>
                                <td>
                                    <?php if ($result): ?>
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
                                    <span style="color: var(--enterprise-text-secondary);">
                                        <?= $test_descriptions[$test] ?? 'Test système' ?>
                                    </span>
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
                <i class="fas fa-info-circle me-2"></i>Informations Système
            </h5>
            
            <div class="enterprise-info-grid">
                <?php foreach ($system_info as $key => $value): ?>
                    <div class="enterprise-info-item">
                        <div class="enterprise-info-label"><?= ucfirst(str_replace('_', ' ', $key)) ?></div>
                        <div class="enterprise-info-value"><?= htmlspecialchars($value) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="enterprise-content-card">
    <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
        <i class="fas fa-tools me-2"></i>Actions Rapides
    </h5>
    <div class="row">
        <div class="col-md-3">
            <button class="enterprise-action-btn enterprise-primary w-100 mb-2" onclick="testDatabaseConnection()">
                <i class="fas fa-database me-2"></i>Test Base de Données
            </button>
        </div>
        <div class="col-md-3">
            <button class="enterprise-action-btn enterprise-info w-100 mb-2" onclick="testFilePermissions()">
                <i class="fas fa-file-alt me-2"></i>Test Permissions
            </button>
        </div>
        <div class="col-md-3">
            <button class="enterprise-action-btn enterprise-success w-100 mb-2" onclick="testEmailSystem()">
                <i class="fas fa-envelope me-2"></i>Test Email
            </button>
        </div>
        <div class="col-md-3">
            <button class="enterprise-action-btn enterprise-warning w-100 mb-2" onclick="generateSystemReport()">
                <i class="fas fa-file-pdf me-2"></i>Rapport Système
            </button>
        </div>
    </div>
</div>

<script>
function runAllTests() {
    location.reload();
}

function exportTestResults() {
    const results = <?= json_encode($tests) ?>;
    const systemInfo = <?= json_encode($system_info) ?>;
    
    const data = {
        timestamp: new Date().toISOString(),
        tests: results,
        system_info: systemInfo
    };
    
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'emploidb-test-results.json';
    a.click();
    URL.revokeObjectURL(url);
}

function testDatabaseConnection() {
    alert('Test de connexion à la base de données...');
    // Add actual database connection test here
}

function testFilePermissions() {
    alert('Test des permissions de fichiers...');
    // Add actual file permission test here
}

function testEmailSystem() {
    alert('Test du système d\'email...');
    // Add actual email system test here
}

function generateSystemReport() {
    alert('Génération du rapport système...');
    // Add actual system report generation here
}
</script>

<style>
.enterprise-info-grid {
    display: grid;
    gap: 1rem;
}

.enterprise-info-item {
    background: var(--enterprise-gray-50);
    border-radius: 8px;
    padding: 1rem;
}

.enterprise-info-label {
    font-size: 0.875rem;
    color: var(--enterprise-text-secondary);
    margin-bottom: 0.5rem;
}

.enterprise-info-value {
    font-weight: 600;
    color: var(--enterprise-text-primary);
}
</style>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
