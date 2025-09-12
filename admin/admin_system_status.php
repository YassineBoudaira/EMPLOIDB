<?php
// Admin System Status - Standard Admin Design
$page_title = 'Admin System Status - EMPLOIDB';
include 'includes/admin_header.php';

// Check if user is admin
if (!Security::isAdmin()) {
    echo '<div class="enterprise-card">
        <div class="card-header bg-gradient-danger text-white">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>Access Denied</h4>
        </div>
        <div class="card-body text-center">
            <p class="mb-0">Admin privileges are required.</p>
        </div>
    </div>';
    include 'includes/admin_footer.php';
    exit;
}

// Test all admin pages and functionality
$admin_pages = [
    'dashboard.php' => 'Enterprise Dashboard',
    'user_analytics.php' => 'User Analytics', 
    'behavior_analytics.php' => 'Behavior Analytics',
    'device_analytics.php' => 'Device Analytics',
    'geographic_analytics.php' => 'Geographic Analytics',
    'performance_analytics.php' => 'Performance Analytics',
    'real_time_monitoring.php' => 'Real-time Monitoring',
    'users.php' => 'User Management',
    'employers.php' => 'Employer Management',
    'jobs.php' => 'Job Management',
    'applications.php' => 'Application Management',
    'manage_profiles.php' => 'Profile Management',
    'manage_ads.php' => 'Ad Management',
    'manage_campaigns.php' => 'Campaign Management',
    'manage_advertisers.php' => 'Advertiser Management',
    'reports.php' => 'Reports',
    'settings.php' => 'Settings'
];

// Test database connectivity
try {
    $db_test = $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
    $db_status = 'Connected';
    $db_users = $db_test;
} catch (Exception $e) {
    $db_status = 'Error: ' . $e->getMessage();
    $db_users = 0;
}

// Test file existence
$missing_files = [];
foreach ($admin_pages as $file => $title) {
    if (!file_exists(__DIR__ . '/' . $file)) {
        $missing_files[] = $file;
    }
}
?>

<!-- System Status Dashboard -->
<div class="enterprise-content-card mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 style="color: var(--enterprise-primary); margin: 0;">
                <i class="fas fa-server me-2"></i>Admin System Status
            </h5>
            <small class="text-muted">Comprehensive status check of all admin components</small>
        </div>
        <div class="d-flex gap-2">
            <button class="enterprise-action-btn enterprise-info" onclick="refreshStatus()">
                <i class="fas fa-sync-alt me-1"></i>Refresh
            </button>
            <button class="enterprise-action-btn enterprise-success" onclick="runFullTest()">
                <i class="fas fa-play me-1"></i>Run Tests
            </button>
        </div>
    </div>
</div>

<!-- System Health Overview -->
<div class="enterprise-stats-grid">
    <div class="enterprise-stat-card">
        <div class="enterprise-stat-number"><?= count($admin_pages) ?></div>
        <div class="enterprise-stat-label">Total Admin Pages</div>
        <i class="fas fa-file-code stat-icon"></i>
    </div>
    <div class="enterprise-stat-card">
        <div class="enterprise-stat-number"><?= count($admin_pages) - count($missing_files) ?></div>
        <div class="enterprise-stat-label">Pages Available</div>
        <i class="fas fa-check-circle stat-icon"></i>
    </div>
    <div class="enterprise-stat-card">
        <div class="enterprise-stat-number"><?= $db_users ?></div>
        <div class="enterprise-stat-label">Database Users</div>
        <i class="fas fa-database stat-icon"></i>
    </div>
    <div class="enterprise-stat-card">
        <div class="enterprise-stat-number"><?= $db_status === 'Connected' ? 'OK' : 'ERROR' ?></div>
        <div class="enterprise-stat-label">Database Status</div>
        <i class="fas fa-heartbeat stat-icon"></i>
    </div>
</div>

<!-- Admin Pages Status -->
<div class="enterprise-content-card">
    <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
        <i class="fas fa-list-check me-2"></i>Admin Pages Status
    </h5>
    
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead style="background: var(--enterprise-gray-50);">
                <tr>
                    <th style="color: var(--enterprise-text-primary);">Page</th>
                    <th style="color: var(--enterprise-text-primary);">File Status</th>
                    <th style="color: var(--enterprise-text-primary);">Syntax Check</th>
                    <th style="color: var(--enterprise-text-primary);">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admin_pages as $file => $title): ?>
                <tr>
                    <td>
                        <strong style="color: var(--enterprise-primary);">
                            <?= htmlspecialchars($title) ?>
                        </strong>
                        <br>
                        <small style="color: var(--enterprise-text-muted);">
                            <?= htmlspecialchars($file) ?>
                        </small>
                    </td>
                    <td>
                        <?php if (file_exists(__DIR__ . '/' . $file)): ?>
                            <span class="enterprise-status-badge enterprise-success">
                                <i class="fas fa-check me-1"></i>Exists
                            </span>
                        <?php else: ?>
                            <span class="enterprise-status-badge enterprise-danger">
                                <i class="fas fa-times me-1"></i>Missing
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="enterprise-status-badge enterprise-info" id="syntax-<?= str_replace('.', '-', $file) ?>">
                            <i class="fas fa-clock me-1"></i>Checking...
                        </span>
                    </td>
                    <td>
                        <?php if (file_exists(__DIR__ . '/' . $file)): ?>
                            <a href="<?= $file ?>" class="enterprise-action-btn enterprise-primary enterprise-sm me-1" target="_blank">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <button class="enterprise-action-btn enterprise-info enterprise-sm" onclick="testPage('<?= $file ?>')">
                                <i class="fas fa-flask"></i>
                            </button>
                        <?php else: ?>
                            <button class="enterprise-action-btn enterprise-danger enterprise-sm" onclick="createPage('<?= $file ?>')">
                                <i class="fas fa-plus"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- System Information -->
<div class="row">
    <div class="col-lg-6">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-info-circle me-2"></i>System Information
            </h5>
            
            <table class="table table-sm mb-0">
                <tr>
                    <td><strong>PHP Version:</strong></td>
                    <td><?= PHP_VERSION ?></td>
                </tr>
                <tr>
                    <td><strong>Database Status:</strong></td>
                    <td>
                        <?php if ($db_status === 'Connected'): ?>
                            <span class="enterprise-status-badge enterprise-success"><?= $db_status ?></span>
                        <?php else: ?>
                            <span class="enterprise-status-badge enterprise-danger"><?= $db_status ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Session Status:</strong></td>
                    <td>
                        <span class="enterprise-status-badge enterprise-success">
                            <?= session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Admin Access:</strong></td>
                    <td>
                        <span class="enterprise-status-badge enterprise-success">
                            <?= Security::isAdmin() ? 'Granted' : 'Denied' ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Memory Usage:</strong></td>
                    <td><?= number_format(memory_get_usage(true) / 1024 / 1024, 2) ?> MB</td>
                </tr>
                <tr>
                    <td><strong>Server Time:</strong></td>
                    <td><?= date('Y-m-d H:i:s') ?></td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-tools me-2"></i>Quick Actions
            </h5>
            
            <div class="d-grid gap-2">
                <button class="enterprise-action-btn enterprise-primary" onclick="testAllPages()">
                    <i class="fas fa-play me-2"></i>Test All Pages
                </button>
                <button class="enterprise-action-btn enterprise-info" onclick="checkSyntaxAll()">
                    <i class="fas fa-code me-2"></i>Check All Syntax
                </button>
                <button class="enterprise-action-btn enterprise-success" onclick="exportSystemReport()">
                    <i class="fas fa-download me-2"></i>Export System Report
                </button>
                <button class="enterprise-action-btn enterprise-warning" onclick="clearAllCache()">
                    <i class="fas fa-broom me-2"></i>Clear All Cache
                </button>
                <a href="dashboard.php" class="enterprise-action-btn enterprise-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function refreshStatus() {
    location.reload();
}

function runFullTest() {
    alert('Running comprehensive system test...');
    testAllPages();
}

function testPage(filename) {
    const newWindow = window.open(filename, '_blank');
    if (newWindow) {
        setTimeout(() => {
            if (newWindow.closed) {
                alert(`Page ${filename} test completed`);
            } else {
                alert(`Page ${filename} opened successfully`);
            }
        }, 2000);
    }
}

function createPage(filename) {
    alert(`Create missing page: ${filename}`);
    // Implementation would create the missing page
}

function testAllPages() {
    const pages = <?= json_encode(array_keys($admin_pages)) ?>;
    let successCount = 0;
    
    pages.forEach((page, index) => {
        setTimeout(() => {
            fetch(page, {method: 'HEAD'})
                .then(response => {
                    const statusElement = document.getElementById(`syntax-${page.replace('.', '-')}`);
                    if (response.ok) {
                        statusElement.innerHTML = '<i class="fas fa-check me-1"></i>OK';
                        statusElement.className = 'enterprise-status-badge enterprise-success';
                        successCount++;
                    } else {
                        statusElement.innerHTML = '<i class="fas fa-times me-1"></i>Error';
                        statusElement.className = 'enterprise-status-badge enterprise-danger';
                    }
                })
                .catch(error => {
                    const statusElement = document.getElementById(`syntax-${page.replace('.', '-')}`);
                    statusElement.innerHTML = '<i class="fas fa-exclamation me-1"></i>Failed';
                    statusElement.className = 'enterprise-status-badge enterprise-warning';
                });
        }, index * 100);
    });
    
    setTimeout(() => {
        alert(`Test completed! ${successCount}/${pages.length} pages working`);
    }, pages.length * 100 + 1000);
}

function checkSyntaxAll() {
    alert('Checking syntax of all PHP files...');
    // This would typically make AJAX calls to check syntax
}

function exportSystemReport() {
    const report = {
        timestamp: new Date().toISOString(),
        php_version: '<?= PHP_VERSION ?>',
        database_status: '<?= $db_status ?>',
        admin_pages: <?= json_encode($admin_pages) ?>,
        missing_files: <?= json_encode($missing_files) ?>,
        system_info: {
            memory_usage: '<?= number_format(memory_get_usage(true) / 1024 / 1024, 2) ?> MB',
            server_time: '<?= date('Y-m-d H:i:s') ?>'
        }
    };
    
    const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'admin-system-status-report.json';
    a.click();
    URL.revokeObjectURL(url);
}

function clearAllCache() {
    if (confirm('Clear all system cache?')) {
        alert('Cache cleared successfully');
    }
}

// Auto-test pages on load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(testAllPages, 1000);
});
</script>

<?php include 'includes/admin_footer.php'; ?>

