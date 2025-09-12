<?php
// System Logs - Standard Admin Design
$page_title = 'System Logs - EMPLOIDB';
include 'includes/admin_header.php';

// Check if user is admin
if (!Security::isAdmin()) {
    echo '<div class="enterprise-card">
        <div class="card-header bg-gradient-danger text-white">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>Access Denied</h4>
        </div>
        <div class="card-body text-center">
            <p class="mb-0">Admin privileges are required to access system logs.</p>
        </div>
    </div>';
    include 'includes/admin_footer.php';
    exit;
}

// Read log files
$log_files = [
    'error.log' => 'Error Log',
    'access.log' => 'Access Log',
    'security.log' => 'Security Log',
    'system.log' => 'System Log'
];

$log_content = [];
$selected_log = $_GET['log'] ?? 'error.log';

// Read log content
$log_path = __DIR__ . '/logs/' . $selected_log;
if (file_exists($log_path)) {
    $log_content = file($log_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $log_content = array_reverse($log_content); // Show newest first
    $log_content = array_slice($log_content, 0, 100); // Limit to 100 lines
}
?>

<!-- System Logs Content -->
<div class="enterprise-card">
    <div class="card-header bg-gradient-secondary text-white">
        <h4><i class="fas fa-file-alt me-2"></i>System Logs</h4>
        <p class="mb-0">View and manage application logs</p>
    </div>
    <div class="card-body">
        <!-- Log File Selection -->
        <div class="row mb-4">
            <div class="col-12">
                <h5>Select Log File</h5>
                <div class="btn-group" role="group">
                    <?php foreach ($log_files as $file => $name): ?>
                        <a href="?log=<?= $file ?>" 
                           class="btn <?= $selected_log === $file ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <i class="fas fa-file-alt me-2"></i><?= $name ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Log Content -->
        <div class="row">
            <div class="col-12">
                <h5>Log Content: <?= $log_files[$selected_log] ?? $selected_log ?></h5>
                <div class="log-content" style="background: #1e293b; color: #e2e8f0; padding: 1rem; border-radius: 0.5rem; font-family: 'Courier New', monospace; font-size: 0.875rem; max-height: 500px; overflow-y: auto;">
                    <?php if (!empty($log_content)): ?>
                        <?php foreach ($log_content as $line): ?>
                            <div style="margin-bottom: 0.25rem; padding: 0.25rem 0; border-bottom: 1px solid rgba(255,255,255,0.1);"><?= htmlspecialchars($line) ?></div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted">No log entries found or log file does not exist.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Log Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh Logs
                    </button>
                    <button class="btn btn-success" onclick="downloadLog()">
                        <i class="fas fa-download me-2"></i>Download Log
                    </button>
                    <button class="btn btn-warning" onclick="clearLog()">
                        <i class="fas fa-trash me-2"></i>Clear Log
                    </button>
                    <a href="admin/dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-tachometer-alt me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function downloadLog() {
    alert('Download functionality would be implemented here');
}

function clearLog() {
    if (confirm('Are you sure you want to clear this log file?')) {
        alert('Clear functionality would be implemented here');
    }
}
</script>

<?php include 'includes/admin_footer.php'; ?>
