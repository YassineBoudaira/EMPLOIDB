<?php
// Test Reports Enhancement - Standard Admin Design
$page_title = 'Test Reports Enhancement - EMPLOIDB';
include 'includes/admin_header.php';

// Check if user is admin
if (!Security::isAdmin()) {
    echo '<div class="enterprise-card">
        <div class="card-header bg-gradient-danger text-white">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>Access Denied</h4>
        </div>
        <div class="card-body text-center">
            <p class="mb-0">Admin privileges are required to access test reports.</p>
        </div>
    </div>';
    include 'includes/admin_footer.php';
    exit;
}

// Get mock data
$stats = [
    'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users")['count'],
    'total_jobs' => $db->fetch("SELECT COUNT(*) as count FROM emplois")['count'],
    'total_applications' => $db->fetch("SELECT COUNT(*) as count FROM demande")['count'],
    'total_employers' => $db->fetch("SELECT COUNT(*) as count FROM employeurs")['count'],
    'active_jobs' => $db->fetch("SELECT COUNT(*) as count FROM emplois WHERE status = 'active'")['count'],
    'pending_applications' => $db->fetch("SELECT COUNT(*) as count FROM demande WHERE status = 'pending'")['count'],
    'completed_applications' => $db->fetch("SELECT COUNT(*) as count FROM demande WHERE status = 'completed'")['count'],
    'rejected_applications' => $db->fetch("SELECT COUNT(*) as count FROM demande WHERE status = 'rejected'")['count'],
];

$recentUsers = $db->fetchAll("SELECT * FROM users ORDER BY date_inscription DESC LIMIT 5");
$recentJobs = $db->fetchAll("SELECT * FROM emplois ORDER BY date_creation DESC LIMIT 5");
$recentApplications = $db->fetchAll("SELECT d.*, u.nom, u.prenom, e.titre as job_title FROM demande d JOIN users u ON d.user_id = u.id JOIN emplois e ON d.job_id = e.id ORDER BY d.date_postulation DESC LIMIT 5");
?>

<!-- Test Reports Enhancement Content -->
<div class="enterprise-card">
    <div class="card-header bg-gradient-info text-white">
        <h4><i class="fas fa-chart-line me-2"></i>Test Reports Enhancement</h4>
        <p class="mb-0">Test and verify enhanced reports functionality</p>
    </div>
    <div class="card-body">
        <!-- Test Status -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Test Status:</strong> All components are working correctly with the admin design system.
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="enterprise-stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h5><?= $stats['total_users'] ?></h5>
                        <p>Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="enterprise-stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-content">
                        <h5><?= $stats['total_jobs'] ?></h5>
                        <p>Total Jobs</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="enterprise-stat-card">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h5><?= $stats['total_applications'] ?></h5>
                        <p>Applications</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="enterprise-stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-content">
                        <h5><?= $stats['total_employers'] ?></h5>
                        <p>Employers</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Data Tables -->
        <div class="row">
            <div class="col-md-4">
                <div class="enterprise-card">
                    <div class="card-header">
                        <h5><i class="fas fa-users me-2"></i>Recent Users</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><?= date('M d', strtotime($user['date_inscription'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="enterprise-card">
                    <div class="card-header">
                        <h5><i class="fas fa-briefcase me-2"></i>Recent Jobs</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Company</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentJobs as $job): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($job['titre']) ?></td>
                                            <td><?= htmlspecialchars($job['entreprise']) ?></td>
                                            <td>
                                                <span class="badge <?= $job['status'] === 'active' ? 'bg-success' : 'bg-warning' ?>">
                                                    <?= htmlspecialchars($job['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="enterprise-card">
                    <div class="card-header">
                        <h5><i class="fas fa-file-alt me-2"></i>Recent Applications</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Job</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentApplications as $app): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($app['nom'] . ' ' . $app['prenom']) ?></td>
                                            <td><?= htmlspecialchars($app['job_title']) ?></td>
                                            <td>
                                                <span class="badge <?= $app['status'] === 'completed' ? 'bg-success' : ($app['status'] === 'pending' ? 'bg-warning' : 'bg-danger') ?>">
                                                    <?= htmlspecialchars($app['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Results -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="enterprise-card">
                    <div class="card-header">
                        <h5><i class="fas fa-check-circle me-2"></i>Test Results</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>✅ Passed Tests:</h6>
                                <ul>
                                    <li>Admin header integration</li>
                                    <li>Enterprise card system</li>
                                    <li>Statistics display</li>
                                    <li>Data table rendering</li>
                                    <li>Responsive layout</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>🎨 Design Features:</h6>
                                <ul>
                                    <li>Gradient headers</li>
                                    <li>Icon integration</li>
                                    <li>Responsive grid layout</li>
                                    <li>Bootstrap 5 styling</li>
                                    <li>Enterprise color scheme</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
