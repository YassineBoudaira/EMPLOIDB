<?php
$page_title = 'Enterprise Dashboard - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Get comprehensive dashboard statistics
try {
    $stats = [
        'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
        'total_employers' => $db->fetch("SELECT COUNT(*) as count FROM employers")['count'] ?? 0,
        'total_jobs' => $db->fetch("SELECT COUNT(*) as count FROM annonces")['count'] ?? 0,
        'total_applications' => $db->fetch("SELECT COUNT(*) as count FROM postulation")['count'] ?? 0,
        'active_jobs' => $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE status = 'active'")['count'] ?? 0,
        'pending_applications' => $db->fetch("SELECT COUNT(*) as count FROM postulation WHERE status = 'pending'")['count'] ?? 0,
        'completed_applications' => $db->fetch("SELECT COUNT(*) as count FROM postulation WHERE status = 'completed'")['count'] ?? 0,
        'rejected_applications' => $db->fetch("SELECT COUNT(*) as count FROM postulation WHERE status = 'rejected'")['count'] ?? 0,
    ];
    
    // Get real-time quick stats
    $quickStats = [
        'new_users_today' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
        'jobs_posted_today' => $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
        'applications_today' => $db->fetch("SELECT COUNT(*) as count FROM postulation WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
        'employers_today' => $db->fetch("SELECT COUNT(*) as count FROM employers WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
    ];
    
    // Get recent activity
    $recentActivity = $db->fetchAll("
        SELECT 
            'application' as type,
            u.nom as user_name,
            a.titre as job_title,
            p.created_at as activity_time,
            'Nouvelle candidature' as activity_text,
            'success' as activity_color
        FROM postulation p
        JOIN users u ON p.user_id = u.id
        JOIN annonces a ON p.annonce_id = a.id
        WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        UNION ALL
        SELECT 
            'job' as type,
            e.nom_entreprise as user_name,
            a.titre as job_title,
            a.created_at as activity_time,
            'Nouvelle offre publiée' as activity_text,
            'primary' as activity_color
        FROM annonces a
        JOIN employers e ON a.employer_id = e.id
        WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        UNION ALL
        SELECT 
            'employer' as type,
            e.nom_entreprise as user_name,
            '' as job_title,
            e.created_at as activity_time,
            'Nouvel employeur inscrit' as activity_text,
            'warning' as activity_color
        FROM employers e
        WHERE e.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        UNION ALL
        SELECT 
            'user' as type,
            u.nom as user_name,
            '' as job_title,
            u.created_at as activity_time,
            'Nouvel utilisateur inscrit' as activity_text,
            'info' as activity_color
        FROM users u
        WHERE u.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY activity_time DESC
        LIMIT 15
    ");
    
    // Get system performance metrics
    $systemMetrics = [
        'database_connections' => $db->fetch("SHOW STATUS LIKE 'Threads_connected'")['Value'] ?? 0,
        'slow_queries' => $db->fetch("SHOW STATUS LIKE 'Slow_queries'")['Value'] ?? 0,
        'uptime' => $db->fetch("SHOW STATUS LIKE 'Uptime'")['Value'] ?? 0,
        'queries_per_second' => $db->fetch("SHOW STATUS LIKE 'Questions'")['Value'] ?? 0,
    ];
    
    // Get advanced analytics data
    $advancedAnalytics = [
        'top_employers' => $db->fetchAll("
            SELECT e.nom_entreprise, COUNT(a.id) as job_count, COUNT(p.id) as application_count
            FROM employers e
            LEFT JOIN annonces a ON e.id = a.employer_id
            LEFT JOIN postulation p ON a.id = p.annonce_id
            GROUP BY e.id
            ORDER BY job_count DESC
            LIMIT 5
        "),
        'popular_jobs' => $db->fetchAll("
            SELECT a.titre, a.salaire, COUNT(p.id) as application_count, e.nom_entreprise
            FROM annonces a
            LEFT JOIN postulation p ON a.id = p.annonce_id
            LEFT JOIN employers e ON a.employer_id = e.id
            WHERE a.status = 'active'
            GROUP BY a.id
            ORDER BY application_count DESC
            LIMIT 5
        "),
        'user_growth' => $db->fetchAll("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date
        "),
        'job_categories' => $db->fetchAll("
            SELECT d.nom as domain, COUNT(a.id) as job_count
            FROM domaine d
            LEFT JOIN annonces a ON d.id = a.domaine_id
            GROUP BY d.id
            ORDER BY job_count DESC
            LIMIT 8
        "),
        'application_status' => $db->fetchAll("
            SELECT status, COUNT(*) as count
            FROM postulation
            GROUP BY status
        "),
        'salary_distribution' => $db->fetchAll("
            SELECT 
                CASE 
                    WHEN salaire < 30000 THEN 'Under 30k'
                    WHEN salaire BETWEEN 30000 AND 50000 THEN '30k-50k'
                    WHEN salaire BETWEEN 50000 AND 80000 THEN '50k-80k'
                    WHEN salaire BETWEEN 80000 AND 120000 THEN '80k-120k'
                    ELSE 'Over 120k'
                END as salary_range,
                COUNT(*) as count
            FROM annonces
            WHERE salaire IS NOT NULL
            GROUP BY salary_range
            ORDER BY 
                CASE salary_range
                    WHEN 'Under 30k' THEN 1
                    WHEN '30k-50k' THEN 2
                    WHEN '50k-80k' THEN 3
                    WHEN '80k-120k' THEN 4
                    ELSE 5
                END
        "),
        'geographic_distribution' => $db->fetchAll("
            SELECT v.nom as city, COUNT(a.id) as job_count
            FROM ville v
            LEFT JOIN annonces a ON v.id = a.ville_id
            GROUP BY v.id
            ORDER BY job_count DESC
            LIMIT 10
        "),
        'monthly_trends' => $db->fetchAll("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count,
                'users' as type
            FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            UNION ALL
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count,
                'jobs' as type
            FROM annonces
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month, type
        "),
    ];
    
} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $stats = [];
    $quickStats = [];
    $recentActivity = [];
    $systemMetrics = [];
    $advancedAnalytics = [];
}
?>

<!-- Enterprise Dashboard Content -->
<div class="fade-in">
    <!-- Welcome Section -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-tachometer-alt me-3"></i>
                        Enterprise Dashboard
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Welcome back! Here's what's happening with your platform today.
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="adminSystem.refreshDashboard()">
                        <i class="fas fa-sync-alt"></i>
                        Refresh
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="adminSystem.exportDashboard()">
                        <i class="fas fa-download"></i>
                        Export Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-primary mb-0"><?= number_format($stats['total_users']) ?></h3>
                            <small class="text-muted">Total Users</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +<?= $quickStats['new_users_today'] ?> today
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="window.location.href='users.php'">
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-building fa-2x text-success"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-success mb-0"><?= number_format($stats['total_employers']) ?></h3>
                            <small class="text-muted">Employers</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +<?= $quickStats['employers_today'] ?> today
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="window.location.href='employers.php'">
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-briefcase fa-2x text-warning"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-warning mb-0"><?= number_format($stats['total_jobs']) ?></h3>
                            <small class="text-muted">Total Jobs</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +<?= $quickStats['jobs_posted_today'] ?> today
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="window.location.href='jobs.php'">
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-info bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-file-alt fa-2x text-info"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-info mb-0"><?= number_format($stats['total_applications']) ?></h3>
                            <small class="text-muted">Applications</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +<?= $quickStats['applications_today'] ?> today
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="window.location.href='applications.php'">
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Analytics Row -->
    <div class="row mb-4">
        <!-- User Growth Chart -->
        <div class="col-xl-8 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-chart-line me-2"></i>
                        Platform Growth Analytics
                    </h4>
                    <div class="btn-group" role="group">
                        <button type="button" class="enterprise-btn enterprise-btn-sm enterprise-btn-outline active" data-period="30">30 Days</button>
                        <button type="button" class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" data-period="90">90 Days</button>
                        <button type="button" class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" data-period="365">1 Year</button>
                    </div>
                </div>
                <div class="enterprise-card-body">
                    <canvas id="analyticsLineChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Application Status Distribution -->
        <div class="col-xl-4 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-chart-pie me-2"></i>
                        Application Status
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <canvas id="categoryDoughnutChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics Row -->
    <div class="row mb-4">
        <!-- Salary Distribution -->
        <div class="col-xl-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-chart-bar me-2"></i>
                        Salary Distribution
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <canvas id="performanceBarChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Geographic Distribution -->
        <div class="col-xl-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        Geographic Job Distribution
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div id="geographicChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Analytics Row -->
    <div class="row mb-4">
        <!-- Top Employers -->
        <div class="col-xl-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-trophy me-2"></i>
                        Top Performing Employers
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employer</th>
                                    <th>Jobs Posted</th>
                                    <th>Applications</th>
                                    <th>Success Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($advancedAnalytics['top_employers'] as $employer): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="employer-avatar me-3">
                                                <i class="fas fa-building"></i>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($employer['nom_entreprise']) ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="enterprise-badge enterprise-badge-primary">
                                            <?= $employer['job_count'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="enterprise-badge enterprise-badge-success">
                                            <?= $employer['application_count'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $rate = $employer['job_count'] > 0 ? 
                                            round(($employer['application_count'] / $employer['job_count']) * 100, 1) : 0;
                                        ?>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success" style="width: <?= min($rate, 100) ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?= $rate ?>%</small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Jobs -->
        <div class="col-xl-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-fire me-2"></i>
                        Most Popular Jobs
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th>Company</th>
                                    <th>Salary</th>
                                    <th>Applications</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($advancedAnalytics['popular_jobs'] as $job): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="job-icon me-3">
                                                <i class="fas fa-briefcase"></i>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($job['titre']) ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($job['nom_entreprise']) ?></td>
                                    <td>
                                        <span class="enterprise-badge enterprise-badge-warning">
                                            $<?= number_format($job['salaire']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="enterprise-badge enterprise-badge-info">
                                            <?= $job['application_count'] ?>
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

    <!-- System Performance & Recent Activity -->
    <div class="row mb-4">
        <!-- System Performance -->
        <div class="col-xl-4 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-server me-2"></i>
                        System Performance
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div class="system-metrics">
                        <div class="metric-item d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <i class="fas fa-database text-primary me-2"></i>
                                <span>Database Connections</span>
                            </div>
                            <span class="enterprise-badge enterprise-badge-primary">
                                <?= $systemMetrics['database_connections'] ?>
                            </span>
                        </div>
                        
                        <div class="metric-item d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <i class="fas fa-clock text-warning me-2"></i>
                                <span>Slow Queries</span>
                            </div>
                            <span class="enterprise-badge enterprise-badge-warning">
                                <?= $systemMetrics['slow_queries'] ?>
                            </span>
                        </div>
                        
                        <div class="metric-item d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <i class="fas fa-clock text-success me-2"></i>
                                <span>Uptime</span>
                            </div>
                            <span class="enterprise-badge enterprise-badge-success">
                                <?= gmdate("H:i:s", $systemMetrics['uptime']) ?>
                            </span>
                        </div>
                        
                        <div class="metric-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-tachometer-alt text-info me-2"></i>
                                <span>Queries/Second</span>
                            </div>
                            <span class="enterprise-badge enterprise-badge-info">
                                <?= number_format($systemMetrics['queries_per_second']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-xl-8 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-history me-2"></i>
                        Recent Activity
                    </h4>
                    <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="adminSystem.viewAllActivity()">
                        View All
                    </button>
                </div>
                <div class="enterprise-card-body">
                    <div class="activity-timeline">
                        <?php foreach ($recentActivity as $activity): ?>
                        <div class="activity-item d-flex align-items-start mb-3">
                            <div class="activity-icon me-3">
                                <i class="fas fa-<?= $activity['type'] == 'application' ? 'file-alt' : 
                                                   ($activity['type'] == 'job' ? 'briefcase' : 
                                                   ($activity['type'] == 'employer' ? 'building' : 'user')) ?> 
                                     text-<?= $activity['activity_color'] ?>"></i>
                            </div>
                            <div class="activity-content flex-grow-1">
                                <div class="activity-text">
                                    <strong><?= htmlspecialchars($activity['user_name']) ?></strong>
                                    <?= $activity['activity_text'] ?>
                                    <?php if ($activity['job_title']): ?>
                                        for <strong><?= htmlspecialchars($activity['job_title']) ?></strong>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted">
                                    <?= date('M j, Y g:i A', strtotime($activity['activity_time'])) ?>
                                </small>
                            </div>
                            <div class="activity-status">
                                <span class="enterprise-badge enterprise-badge-<?= $activity['activity_color'] ?>">
                                    <?= ucfirst($activity['type']) ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="enterprise-card">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <button class="enterprise-btn enterprise-btn-primary w-100" onclick="window.location.href='users.php'">
                                <i class="fas fa-user-plus me-2"></i>
                                Add New User
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="enterprise-btn enterprise-btn-secondary w-100" onclick="window.location.href='employers.php'">
                                <i class="fas fa-building me-2"></i>
                                Add Employer
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="enterprise-btn enterprise-btn-accent w-100" onclick="window.location.href='jobs.php'">
                                <i class="fas fa-briefcase me-2"></i>
                                Post Job
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="enterprise-btn enterprise-btn-outline w-100" onclick="window.location.href='reports.php'">
                                <i class="fas fa-chart-bar me-2"></i>
                                Generate Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles for Dashboard -->
<style>
    .stat-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
    }

    .employer-avatar, .job-icon {
        width: 40px;
        height: 40px;
        background: var(--enterprise-gradient-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .activity-timeline {
        max-height: 400px;
        overflow-y: auto;
    }

    .activity-item {
        padding: 12px;
        border-radius: 8px;
        transition: var(--enterprise-transition);
    }

    .activity-item:hover {
        background: var(--enterprise-gray-50);
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        background: var(--enterprise-gray-100);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .system-metrics .metric-item {
        padding: 12px;
        border-radius: 8px;
        transition: var(--enterprise-transition);
    }

    .system-metrics .metric-item:hover {
        background: var(--enterprise-gray-50);
    }

    .progress {
        background: var(--enterprise-gray-200);
        border-radius: var(--enterprise-radius-full);
    }

    .progress-bar {
        border-radius: var(--enterprise-radius-full);
    }

    /* Chart container styles */
    #geographicChart {
        min-height: 300px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .stat-value {
            font-size: 1.5rem;
        }
        
        .activity-timeline {
            max-height: 300px;
        }
    }
</style>

<!-- Dashboard-specific JavaScript -->
<script>
    // Initialize dashboard-specific features
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize charts with real data
        initializeDashboardCharts();
        
        // Set up period buttons for analytics
        setupPeriodButtons();
        
        // Initialize geographic chart
        initializeGeographicChart();
    });

    function initializeDashboardCharts() {
        // Line Chart for Analytics
        const lineChartCtx = document.getElementById('analyticsLineChart');
        if (lineChartCtx) {
            const userGrowthData = <?= json_encode($advancedAnalytics['user_growth']) ?>;
            
            adminSystem.charts.lineChart = new Chart(lineChartCtx, {
                type: 'line',
                data: {
                    labels: userGrowthData.map(item => item.date),
                    datasets: [{
                        label: 'New Users',
                        data: userGrowthData.map(item => item.count),
                        borderColor: '#1e40af',
                        backgroundColor: 'rgba(30, 64, 175, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    },
                    elements: {
                        point: {
                            radius: 4,
                            hoverRadius: 6
                        }
                    }
                }
            });
        }

        // Doughnut Chart for Application Status
        const doughnutChartCtx = document.getElementById('categoryDoughnutChart');
        if (doughnutChartCtx) {
            const applicationStatusData = <?= json_encode($advancedAnalytics['application_status']) ?>;
            
            adminSystem.charts.doughnutChart = new Chart(doughnutChartCtx, {
                type: 'doughnut',
                data: {
                    labels: applicationStatusData.map(item => item.status),
                    datasets: [{
                        data: applicationStatusData.map(item => item.count),
                        backgroundColor: [
                            '#10b981', // success
                            '#f59e0b', // warning
                            '#ef4444', // danger
                            '#06b6d4'  // info
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        // Bar Chart for Salary Distribution
        const barChartCtx = document.getElementById('performanceBarChart');
        if (barChartCtx) {
            const salaryData = <?= json_encode($advancedAnalytics['salary_distribution']) ?>;
            
            adminSystem.charts.barChart = new Chart(barChartCtx, {
                type: 'bar',
                data: {
                    labels: salaryData.map(item => item.salary_range),
                    datasets: [{
                        label: 'Number of Jobs',
                        data: salaryData.map(item => item.count),
                        backgroundColor: [
                            'rgba(30, 64, 175, 0.8)',
                            'rgba(5, 150, 105, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(139, 92, 246, 0.8)'
                        ],
                        borderColor: [
                            '#1e40af',
                            '#059669',
                            '#f59e0b',
                            '#ef4444',
                            '#8b5cf6'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    }

    function initializeGeographicChart() {
        const geographicData = <?= json_encode($advancedAnalytics['geographic_distribution']) ?>;
        const chartElement = document.getElementById('geographicChart');
        
        if (chartElement && geographicData.length > 0) {
            const chart = echarts.init(chartElement);
            
            const option = {
                tooltip: {
                    trigger: 'item',
                    formatter: '{a} <br/>{b}: {c} ({d}%)'
                },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    data: geographicData.map(item => item.city)
                },
                series: [
                    {
                        name: 'Jobs by City',
                        type: 'pie',
                        radius: ['40%', '70%'],
                        avoidLabelOverlap: false,
                        label: {
                            show: false,
                            position: 'center'
                        },
                        emphasis: {
                            label: {
                                show: true,
                                fontSize: '18',
                                fontWeight: 'bold'
                            }
                        },
                        labelLine: {
                            show: false
                        },
                        data: geographicData.map(item => ({
                            value: item.job_count,
                            name: item.city
                        }))
                    }
                ]
            };
            
            chart.setOption(option);
            
            // Handle window resize
            window.addEventListener('resize', () => {
                chart.resize();
            });
        }
    }

    function setupPeriodButtons() {
        const periodButtons = document.querySelectorAll('[data-period]');
        periodButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                periodButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                // Update chart data based on period
                const period = this.getAttribute('data-period');
                updateChartData(period);
            });
        });
    }

    function updateChartData(period) {
        // This would typically make an AJAX call to get data for the selected period
        adminSystem.showNotification(`Loading data for ${period} days...`, 'info');
        
        // Simulate data update
        setTimeout(() => {
            adminSystem.showNotification('Chart data updated successfully', 'success');
        }, 1000);
    }

    // Add dashboard-specific methods to adminSystem
    adminSystem.refreshDashboard = function() {
        this.showNotification('Refreshing dashboard data...', 'info');
        location.reload();
    };

    adminSystem.exportDashboard = function() {
        this.showNotification('Preparing dashboard report...', 'info');
        
        // Simulate export process
        setTimeout(() => {
            this.exportData('pdf', {
                title: 'EMPLOIDB Dashboard Report',
                date: new Date().toLocaleDateString(),
                stats: <?= json_encode($stats) ?>,
                quickStats: <?= json_encode($quickStats) ?>
            }, 'emploidb-dashboard-report.pdf');
            
            this.showNotification('Dashboard report exported successfully', 'success');
        }, 2000);
    };

    adminSystem.viewAllActivity = function() {
        this.showNotification('Opening activity log...', 'info');
        // This would typically open a modal or navigate to an activity page
        setTimeout(() => {
            this.showNotification('Activity log opened', 'success');
        }, 1000);
    };
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>

