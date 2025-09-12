<?php
// Test Session and Admin Access - Standard Admin Design
$page_title = 'Session Test - EMPLOIDB';
include 'includes/admin_header.php';

// Check if user is logged in
if (!Security::isLoggedIn()) {
    echo '<div class="enterprise-card">
        <div class="card-header bg-gradient-danger text-white">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>Access Denied</h4>
        </div>
        <div class="card-body text-center">
            <p class="mb-0">You are not logged in. Please login first.</p>
        </div>
    </div>';
    
    include 'includes/admin_footer.php';
    exit;
}
?>

<!-- Session Test Content -->
<div class="enterprise-card">
    <div class="card-header bg-gradient-info text-white">
        <h4><i class="fas fa-search me-2"></i>Session Test & Diagnostics</h4>
        <p class="mb-0">Comprehensive session analysis and security checks</p>
    </div>
    <div class="card-body">
        <!-- Session Status -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Session Status</h5>
                <div class="alert alert-success">
                    <strong>Session Active:</strong> <?= session_status() === PHP_SESSION_ACTIVE ? 'Yes' : 'No' ?><br>
                    <strong>Session ID:</strong> <?= session_id() ?><br>
                    <strong>Session Name:</strong> <?= session_name() ?><br>
                    <strong>Session Path:</strong> <?= session_save_path() ?>
                </div>
            </div>
            <div class="col-md-6">
                <h5>Security Checks</h5>
                <div class="alert alert-info">
                    <strong>Logged In:</strong> <?= Security::isLoggedIn() ? 'Yes' : 'No' ?><br>
                    <strong>Is Admin:</strong> <?= Security::isAdmin() ? 'Yes' : 'No' ?><br>
                    <strong>User Role:</strong> <?= $_SESSION['role'] ?? 'Not Set' ?><br>
                    <strong>User ID:</strong> <?= $_SESSION['user_id'] ?? 'Not Set' ?>
                </div>
            </div>
        </div>

        <!-- Session Variables -->
        <div class="row">
            <div class="col-12">
                <h5>All Session Variables</h5>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Variable</th>
                                <th>Value</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION as $key => $value): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($key) ?></code></td>
                                    <td><?= htmlspecialchars(is_array($value) ? json_encode($value) : $value) ?></td>
                                    <td><span class="badge bg-secondary"><?= gettype($value) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
