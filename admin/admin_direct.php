<?php
// Direct Admin Access - Standard Admin Design
$page_title = 'Direct Admin Access - EMPLOIDB';
include 'includes/admin_header.php';
include 'includes/admin_sidebar.php';

// Check if user is logged in and is admin
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

if (!Security::isAdmin()) {
    echo '<div class="enterprise-card">
        <div class="card-header bg-gradient-warning text-dark">
            <h4><i class="fas fa-user-shield me-2"></i>Insufficient Privileges</h4>
        </div>
        <div class="card-body text-center">
            <p class="mb-0">Your role: <strong>' . ($_SESSION['role'] ?? 'Unknown') . '</strong></p>
            <p class="mb-0">Admin privileges are required to access this page.</p>
        </div>
    </div>';
    include 'includes/admin_footer.php';
    exit;
}
?>

<!-- Direct Admin Access Content -->
<div class="enterprise-card">
    <div class="card-header bg-gradient-primary text-white">
        <h4><i class="fas fa-crown me-2"></i>Direct Admin Access</h4>
        <p class="mb-0">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?>!</p>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5>Admin Dashboard Links</h5>
                <div class="d-grid gap-2">
                    <a href="admin/dashboard.php" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt me-2"></i>Main Dashboard
                    </a>
                    <a href="admin/users.php" class="btn btn-info">
                        <i class="fas fa-users me-2"></i>User Management
                    </a>
                    <a href="admin/jobs.php" class="btn btn-success">
                        <i class="fas fa-briefcase me-2"></i>Job Management
                    </a>
                    <a href="admin/employers.php" class="btn btn-warning">
                        <i class="fas fa-building me-2"></i>Employer Management
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <h5>System Management</h5>
                <div class="d-grid gap-2">
                    <a href="admin/settings.php" class="btn btn-secondary">
                        <i class="fas fa-cog me-2"></i>System Settings
                    </a>
                    <a href="admin/reports.php" class="btn btn-dark">
                        <i class="fas fa-chart-bar me-2"></i>Reports
                    </a>
                    <a href="admin/real_time_monitoring.php" class="btn btn-danger">
                        <i class="fas fa-chart-line me-2"></i>Real-time Monitoring
                    </a>
                    <a href="admin_database_manager.php" class="btn btn-outline-primary">
                        <i class="fas fa-database me-2"></i>Database Manager
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="enterprise-stat-card">
            <div class="stat-icon bg-primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h5>Active</h5>
                <p>Admin Status</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="enterprise-stat-card">
            <div class="stat-icon bg-success">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="stat-content">
                <h5>Secure</h5>
                <p>Access Level</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="enterprise-stat-card">
            <div class="stat-icon bg-info">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h5><?= date('H:i') ?></h5>
                <p>Current Time</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="enterprise-stat-card">
            <div class="stat-icon bg-warning">
                <i class="fas fa-calendar"></i>
            </div>
            <div class="stat-content">
                <h5><?= date('M d') ?></h5>
                <p>Today's Date</p>
            </div>
        </div>
    </div>
</div>

<!-- Session Information -->
<div class="enterprise-card">
    <div class="card-header bg-gradient-info text-white">
        <h4><i class="fas fa-info-circle me-2"></i>Session Information</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>User Details:</h6>
                <ul class="list-unstyled">
                    <li><strong>Username:</strong> <?= htmlspecialchars($_SESSION['username'] ?? 'N/A') ?></li>
                    <li><strong>Role:</strong> <?= htmlspecialchars($_SESSION['role'] ?? 'N/A') ?></li>
                    <li><strong>User ID:</strong> <?= htmlspecialchars($_SESSION['user_id'] ?? 'N/A') ?></li>
                    <li><strong>Login Time:</strong> <?= date('Y-m-d H:i:s') ?></li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>System Status:</h6>
                <ul class="list-unstyled">
                    <li><strong>PHP Version:</strong> <?= PHP_VERSION ?></li>
                    <li><strong>Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></li>
                    <li><strong>Session ID:</strong> <?= session_id() ?></li>
                    <li><strong>IP Address:</strong> <?= $_SERVER['REMOTE_ADDR'] ?? 'Unknown' ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
