<?php
// Fix IP Blocking Issue - Standard Admin Design
$page_title = 'IP Blocking Fix - EMPLOIDB';
include 'includes/admin_header.php';
include 'includes/admin_sidebar.php';

// Check if user is admin
if (!Security::isAdmin()) {
    echo '<div class="enterprise-card">
        <div class="card-header bg-gradient-danger text-white">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>Access Denied</h4>
        </div>
        <div class="card-body text-center">
            <p class="mb-0">Admin privileges are required to access IP blocking tools.</p>
        </div>
    </div>';
    include 'includes/admin_footer.php';
    exit;
}

// Check what IPs might be blocked
$possible_ips = [
    '127.0.0.1',
    'localhost', 
    '::1',
    '192.168.1.100',
    '192.168.1.1',
    '10.0.0.1'
];

$current_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$blocked_ips = [];
$unblocked_count = 0;

// Check for blocked IPs in database
try {
    // Check if blocked_ips table exists
    $table_exists = $db->fetch("SHOW TABLES LIKE 'blocked_ips'");
    
    if ($table_exists) {
        $blocked_ips = $db->fetchAll("SELECT * FROM blocked_ips");
    }
} catch (Exception $e) {
    // Table might not exist, that's okay
}

// Handle unblock action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unblock_all') {
    try {
        if ($table_exists) {
            $db->query("DELETE FROM blocked_ips");
            $unblocked_count = count($blocked_ips);
            $blocked_ips = [];
        }
    } catch (Exception $e) {
        $error = "Error unblocking IPs: " . $e->getMessage();
    }
}
?>

<!-- IP Blocking Fix Content -->
<div class="enterprise-card">
    <div class="card-header bg-gradient-warning text-dark">
        <h4><i class="fas fa-shield-alt me-2"></i>IP Blocking Fix Tool</h4>
        <p class="mb-0">Diagnose and fix IP blocking issues</p>
    </div>
    <div class="card-body">
        <?php if ($unblocked_count > 0): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                Successfully unblocked <?= $unblocked_count ?> IP address(es)!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Current IP Status -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Current IP Status</h5>
                <div class="alert alert-info">
                    <strong>Your Current IP:</strong> <?= htmlspecialchars($current_ip) ?><br>
                    <strong>Status:</strong> 
                    <?php if (empty($blocked_ips)): ?>
                        <span class="badge bg-success">Not Blocked</span>
                    <?php else: ?>
                        <span class="badge bg-warning">Check Required</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <h5>Blocked IPs Found</h5>
                <div class="alert <?= empty($blocked_ips) ? 'alert-success' : 'alert-warning' ?>">
                    <strong>Count:</strong> <?= count($blocked_ips) ?> blocked IP(s)<br>
                    <?php if (empty($blocked_ips)): ?>
                        <span class="badge bg-success">No IPs Blocked</span>
                    <?php else: ?>
                        <span class="badge bg-warning">IPs Blocked</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Blocked IPs List -->
        <?php if (!empty($blocked_ips)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <h5>Blocked IP Addresses</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>IP Address</th>
                                    <th>Blocked At</th>
                                    <th>Reason</th>
                                    <th>Attempts</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blocked_ips as $blocked): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($blocked['ip_address'] ?? 'Unknown') ?></code></td>
                                        <td><?= htmlspecialchars($blocked['blocked_at'] ?? 'Unknown') ?></td>
                                        <td><?= htmlspecialchars($blocked['reason'] ?? 'Failed login attempts') ?></td>
                                        <td><span class="badge bg-danger"><?= $blocked['attempts'] ?? 0 ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="row">
            <div class="col-12">
                <h5>Actions</h5>
                <div class="d-flex flex-wrap gap-2">
                    <?php if (!empty($blocked_ips)): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="unblock_all">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to unblock all IPs?')">
                                <i class="fas fa-unlock me-2"></i>Unblock All IPs
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Test Login Page
                    </a>
                    
                    <a href="admin/dashboard.php" class="btn btn-success">
                        <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                    </a>
                    
                    <button class="btn btn-info" onclick="location.reload()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh Status
                    </button>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="alert alert-light">
                    <h6><i class="fas fa-info-circle me-2"></i>Additional Information</h6>
                    <ul class="mb-0">
                        <li><strong>Current Time:</strong> <?= date('Y-m-d H:i:s') ?></li>
                        <li><strong>Server:</strong> <?= $_SERVER['SERVER_NAME'] ?? 'localhost' ?></li>
                        <li><strong>User Agent:</strong> <?= substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 50) ?>...</li>
                        <li><strong>Session ID:</strong> <?= session_id() ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
