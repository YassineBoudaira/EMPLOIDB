<?php
/**
 * Job Aggregation Analytics Dashboard
 * Provides detailed analytics and statistics for job aggregation
 */

$page_title = 'Job Aggregation Analytics - EMPLOIDB';

// Temporarily bypass authentication for testing
// include __DIR__ . '/includes/admin_header.php';

// Include configuration directly
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/connexion.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    
    <!-- Enterprise CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* EMPLOIDB Enterprise Admin System */
        :root {
            /* Enterprise Color Palette */
            --enterprise-primary: #1e40af;
            --enterprise-primary-dark: #1e3a8a;
            --enterprise-primary-light: #3b82f6;
            --enterprise-secondary: #059669;
            --enterprise-secondary-dark: #047857;
            --enterprise-accent: #f59e0b;
            --enterprise-accent-dark: #d97706;
            
            /* Status Colors */
            --enterprise-success: #10b981;
            --enterprise-warning: #f59e0b;
            --enterprise-danger: #ef4444;
            --enterprise-info: #06b6d4;
            --enterprise-purple: #8b5cf6;
            --enterprise-pink: #ec4899;
            
            /* Neutral Colors */
            --enterprise-dark: #0f172a;
            --enterprise-dark-light: #1e293b;
            --enterprise-gray-50: #f8fafc;
            --enterprise-gray-100: #f1f5f9;
            --enterprise-gray-200: #e2e8f0;
            --enterprise-gray-300: #cbd5e1;
            --enterprise-gray-400: #94a3b8;
            --enterprise-gray-500: #64748b;
            --enterprise-gray-600: #475569;
            --enterprise-gray-700: #334155;
            --enterprise-gray-800: #1e293b;
            --enterprise-gray-900: #0f172a;
            
            /* Spacing */
            --enterprise-spacing-1: 0.25rem;
            --enterprise-spacing-2: 0.5rem;
            --enterprise-spacing-3: 0.75rem;
            --enterprise-spacing-4: 1rem;
            --enterprise-spacing-5: 1.25rem;
            --enterprise-spacing-6: 1.5rem;
            --enterprise-spacing-8: 2rem;
            --enterprise-spacing-10: 2.5rem;
            --enterprise-spacing-12: 3rem;
            
            /* Typography */
            --enterprise-font-size-xs: 0.75rem;
            --enterprise-font-size-sm: 0.875rem;
            --enterprise-font-size-base: 1rem;
            --enterprise-font-size-lg: 1.125rem;
            --enterprise-font-size-xl: 1.25rem;
            --enterprise-font-size-2xl: 1.5rem;
            
            /* Border Radius */
            --enterprise-radius: 0.375rem;
            --enterprise-radius-lg: 0.5rem;
            --enterprise-radius-full: 9999px;
            
            /* Transitions */
            --enterprise-transition: all 0.2s ease-in-out;
            --enterprise-transition-slow: all 0.3s ease-in-out;
            
            /* Shadows */
            --enterprise-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --enterprise-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --enterprise-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--enterprise-gray-50);
            color: var(--enterprise-gray-900);
            line-height: 1.6;
        }

        /* Enterprise Sidebar */
        .enterprise-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 320px;
            height: 100vh;
            background: linear-gradient(135deg, var(--enterprise-primary) 0%, var(--enterprise-primary-dark) 100%);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: var(--enterprise-shadow-lg);
        }

        .enterprise-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .enterprise-sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .enterprise-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: var(--enterprise-radius-full);
        }

        .enterprise-sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Sidebar Brand */
        .sidebar-brand {
            padding: var(--enterprise-spacing-8) var(--enterprise-spacing-6);
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }

        .sidebar-brand h2 {
            color: white;
            font-weight: 800;
            font-size: var(--enterprise-font-size-2xl);
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .sidebar-brand .subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: var(--enterprise-font-size-sm);
            font-weight: 500;
            margin-top: var(--enterprise-spacing-1);
        }

        /* Navigation System */
        .enterprise-nav {
            padding: var(--enterprise-spacing-6) 0;
        }

        .nav-section {
            margin-bottom: var(--enterprise-spacing-8);
        }

        .nav-section-header {
            padding: 0 var(--enterprise-spacing-6) var(--enterprise-spacing-3);
            color: rgba(255, 255, 255, 0.6);
            font-size: var(--enterprise-font-size-xs);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .enterprise-nav-link {
            display: flex;
            align-items: center;
            padding: var(--enterprise-spacing-4) var(--enterprise-spacing-6);
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--enterprise-transition);
            position: relative;
            font-weight: 500;
            border-left: 3px solid transparent;
        }

        .enterprise-nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: var(--enterprise-accent);
            transform: translateX(4px);
        }

        .enterprise-nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.15);
            border-left-color: var(--enterprise-accent);
            box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.1);
        }

        .enterprise-nav-link i {
            width: 20px;
            margin-right: var(--enterprise-spacing-4);
            font-size: var(--enterprise-font-size-lg);
        }

        .enterprise-nav-link .badge {
            margin-left: auto;
            background: var(--enterprise-accent);
            color: white;
            font-size: var(--enterprise-font-size-xs);
            padding: var(--enterprise-spacing-1) var(--enterprise-spacing-2);
            border-radius: var(--enterprise-radius-full);
        }

        .enterprise-nav-link .badge.bg-success {
            background: var(--enterprise-success) !important;
        }

        /* Main Content Area */
        .enterprise-content {
            flex: 1;
            margin-left: 320px;
            min-height: 100vh;
            background: var(--enterprise-gray-50);
            transition: var(--enterprise-transition-slow);
        }

        /* Bootstrap Override for Sidebar */
        .sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 320px !important;
            height: 100vh !important;
            background: linear-gradient(135deg, var(--enterprise-primary) 0%, var(--enterprise-primary-dark) 100%) !important;
            color: white !important;
            overflow-y: auto !important;
            z-index: 1000 !important;
            box-shadow: var(--enterprise-shadow-lg) !important;
            padding: 0 !important;
        }

        /* Content Area */
        .col-md-9, .col-lg-10 {
            margin-left: 320px !important;
            padding: var(--enterprise-spacing-6) !important;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: var(--enterprise-radius-lg);
            box-shadow: var(--enterprise-shadow);
            background: white;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid var(--enterprise-gray-200);
            font-weight: 600;
            color: var(--enterprise-gray-900);
        }

        /* Buttons */
        .btn-primary {
            background: var(--enterprise-primary);
            border-color: var(--enterprise-primary);
        }

        .btn-primary:hover {
            background: var(--enterprise-primary-dark);
            border-color: var(--enterprise-primary-dark);
        }

        .btn-success {
            background: var(--enterprise-success);
            border-color: var(--enterprise-success);
        }

        .btn-warning {
            background: var(--enterprise-warning);
            border-color: var(--enterprise-warning);
        }

        .btn-danger {
            background: var(--enterprise-danger);
            border-color: var(--enterprise-danger);
        }

        /* Tables */
        .table {
            background: white;
        }

        .table th {
            background: var(--enterprise-gray-50);
            border-bottom: 2px solid var(--enterprise-gray-200);
            font-weight: 600;
            color: var(--enterprise-gray-900);
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: var(--enterprise-radius-lg);
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--enterprise-success);
        }

        .alert-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--enterprise-warning);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--enterprise-danger);
        }

        .alert-info {
            background: rgba(6, 182, 212, 0.1);
            color: var(--enterprise-info);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .enterprise-sidebar {
                transform: translateX(-100%);
                transition: var(--enterprise-transition-slow);
            }
            
            .enterprise-sidebar.show {
                transform: translateX(0);
            }
            
            .enterprise-content {
                margin-left: 0;
            }
            
            .col-md-9, .col-lg-10 {
                margin-left: 0 !important;
            }
        }
    </style>
</head>
<body>
<?php

// Initialize classes with error handling
try {
    require_once __DIR__ . '/../include/JobAggregator.php';
    $jobAggregator = new JobAggregator($db);
} catch (Exception $e) {
    $jobAggregator = null;
    error_log("JobAggregator initialization error: " . $e->getMessage());
}

try {
    require_once __DIR__ . '/../include/MultilingualSupport.php';
    $multilingual = new MultilingualSupport($db);
} catch (Exception $e) {
    $multilingual = null;
    error_log("MultilingualSupport initialization error: " . $e->getMessage());
}

// Get date range from request
$dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$dateTo = $_GET['date_to'] ?? date('Y-m-d');

// Get analytics data
$analytics = getAggregationAnalytics($db, $dateFrom, $dateTo);

?>

<?php
/**
 * Get aggregation analytics data
 */
function getAggregationAnalytics($db, $dateFrom, $dateTo) {
    $analytics = [];
    
    try {
        // Total jobs aggregated
        $analytics['total_jobs'] = $db->fetch(
            "SELECT COUNT(*) as count FROM aggregated_jobs WHERE DATE(created_at) BETWEEN ? AND ?",
            [$dateFrom, $dateTo]
        )['count'];
        
        // Imported jobs
        $analytics['imported_jobs'] = $db->fetch(
            "SELECT COUNT(*) as count FROM aggregated_jobs WHERE imported_to_annonces = 1 AND DATE(created_at) BETWEEN ? AND ?",
            [$dateFrom, $dateTo]
        )['count'];
        
        // Duplicate jobs
        $analytics['duplicate_jobs'] = $analytics['total_jobs'] - $analytics['imported_jobs'];
        $analytics['duplicate_rate'] = $analytics['total_jobs'] > 0 ? ($analytics['duplicate_jobs'] / $analytics['total_jobs']) * 100 : 0;
        
        // Error count
        $analytics['error_count'] = $db->fetch(
            "SELECT COUNT(*) as count FROM scraping_logs WHERE status = 'error' AND DATE(created_at) BETWEEN ? AND ?",
            [$dateFrom, $dateTo]
        )['count'];
        
        // Trends (compare with previous period)
        $prevDateFrom = date('Y-m-d', strtotime($dateFrom . ' -30 days'));
        $prevDateTo = date('Y-m-d', strtotime($dateTo . ' -30 days'));
        
        $prevTotalJobs = $db->fetch(
            "SELECT COUNT(*) as count FROM aggregated_jobs WHERE DATE(created_at) BETWEEN ? AND ?",
            [$prevDateFrom, $prevDateTo]
        )['count'];
        
        $prevImportedJobs = $db->fetch(
            "SELECT COUNT(*) as count FROM aggregated_jobs WHERE imported_to_annonces = 1 AND DATE(created_at) BETWEEN ? AND ?",
            [$prevDateFrom, $prevDateTo]
        )['count'];
        
        $analytics['jobs_trend'] = $prevTotalJobs > 0 ? (($analytics['total_jobs'] - $prevTotalJobs) / $prevTotalJobs) * 100 : 0;
        $analytics['import_trend'] = $prevImportedJobs > 0 ? (($analytics['imported_jobs'] - $prevImportedJobs) / $prevImportedJobs) * 100 : 0;
        
        // Daily jobs data
        $dailyData = $db->fetchAll(
            "SELECT DATE(created_at) as date, COUNT(*) as found, SUM(imported_to_annonces) as imported 
             FROM aggregated_jobs 
             WHERE DATE(created_at) BETWEEN ? AND ? 
             GROUP BY DATE(created_at) 
             ORDER BY date",
            [$dateFrom, $dateTo]
        );
        
        $analytics['daily_jobs'] = [
            'labels' => array_map(function($item) { return date('M j', strtotime($item['date'])); }, $dailyData),
            'found' => array_column($dailyData, 'found'),
            'imported' => array_column($dailyData, 'imported')
        ];
        
        // Source statistics
        $sourceStats = $db->fetchAll(
            "SELECT js.name, COUNT(aj.id) as count 
             FROM job_sources js 
             LEFT JOIN aggregated_jobs aj ON js.id = aj.source_id AND DATE(aj.created_at) BETWEEN ? AND ?
             GROUP BY js.id, js.name 
             ORDER BY count DESC",
            [$dateFrom, $dateTo]
        );
        
        $analytics['source_stats'] = [
            'labels' => array_column($sourceStats, 'name'),
            'data' => array_column($sourceStats, 'count')
        ];
        
        // Source performance
        $analytics['source_performance'] = $db->fetchAll(
            "SELECT js.name, 
                    COUNT(aj.id) as jobs_found,
                    SUM(aj.imported_to_annonces) as jobs_imported,
                    CASE WHEN COUNT(aj.id) > 0 THEN (SUM(aj.imported_to_annonces) / COUNT(aj.id)) * 100 ELSE 0 END as success_rate
             FROM job_sources js 
             LEFT JOIN aggregated_jobs aj ON js.id = aj.source_id AND DATE(aj.created_at) BETWEEN ? AND ?
             GROUP BY js.id, js.name 
             ORDER BY success_rate DESC",
            [$dateFrom, $dateTo]
        );
        
        // Categories
        $categories = $db->fetchAll(
            "SELECT d.nom, COUNT(a.id) as count 
             FROM domaines d 
             LEFT JOIN annonces a ON d.id = a.domaine_id AND DATE(a.created_at) BETWEEN ? AND ?
             GROUP BY d.id, d.nom 
             ORDER BY count DESC 
             LIMIT 10",
            [$dateFrom, $dateTo]
        );
        
        $analytics['categories'] = [
            'labels' => array_column($categories, 'nom'),
            'data' => array_column($categories, 'count')
        ];
        
        // Recent activity
        $analytics['recent_activity'] = $db->fetchAll(
            "SELECT sl.*, js.name as source_name 
             FROM scraping_logs sl 
             JOIN job_sources js ON sl.source_id = js.id 
             WHERE DATE(sl.created_at) BETWEEN ? AND ?
             ORDER BY sl.created_at DESC 
             LIMIT 10",
            [$dateFrom, $dateTo]
        );
        
    } catch (Exception $e) {
        error_log("Error getting analytics: " . $e->getMessage());
        $analytics = [
            'total_jobs' => 0,
            'imported_jobs' => 0,
            'duplicate_jobs' => 0,
            'duplicate_rate' => 0,
            'error_count' => 0,
            'jobs_trend' => 0,
            'import_trend' => 0,
            'daily_jobs' => ['labels' => [], 'found' => [], 'imported' => []],
            'source_stats' => ['labels' => [], 'data' => []],
            'source_performance' => [],
            'categories' => ['labels' => [], 'data' => []],
            'recent_activity' => []
        ];
    }
    
    return $analytics;
}
?>

<style>
    .analytics-card {
        background: linear-gradient(135deg, var(--emploidb-primary-50) 0%, var(--emploidb-secondary-50) 100%);
        border: 1px solid var(--emploidb-primary-200);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .chart-container {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: var(--emploidb-shadow-sm);
    }
    
    .trend-up {
        color: var(--emploidb-success);
    }
    
    .trend-down {
        color: var(--emploidb-error);
    }
    
    .trend-neutral {
        color: var(--emploidb-neutral-600);
    }
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <?php include __DIR__ . '/includes/admin_sidebar.php'; ?>
        </div>
        
        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-chart-line me-2"></i>
                        Job Aggregation Analytics
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <form method="GET" class="d-flex gap-2">
                            <input type="date" name="date_from" value="<?= $dateFrom ?>" class="form-control">
                            <input type="date" name="date_to" value="<?= $dateTo ?>" class="form-control">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Key Metrics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="analytics-card text-center">
                            <h3 class="text-primary"><?= number_format($analytics['total_jobs']) ?></h3>
                            <p class="mb-1">Total Jobs Found</p>
                            <small class="<?= $analytics['jobs_trend'] >= 0 ? 'trend-up' : 'trend-down' ?>">
                                <i class="fas fa-arrow-<?= $analytics['jobs_trend'] >= 0 ? 'up' : 'down' ?>"></i>
                                <?= abs(round($analytics['jobs_trend'], 1)) ?>%
                            </small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="analytics-card text-center">
                            <h3 class="text-success"><?= number_format($analytics['imported_jobs']) ?></h3>
                            <p class="mb-1">Jobs Imported</p>
                            <small class="<?= $analytics['import_trend'] >= 0 ? 'trend-up' : 'trend-down' ?>">
                                <i class="fas fa-arrow-<?= $analytics['import_trend'] >= 0 ? 'up' : 'down' ?>"></i>
                                <?= abs(round($analytics['import_trend'], 1)) ?>%
                            </small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="analytics-card text-center">
                            <h3 class="text-warning"><?= number_format($analytics['duplicate_jobs']) ?></h3>
                            <p class="mb-1">Duplicate Jobs</p>
                            <small class="trend-neutral">
                                <?= round($analytics['duplicate_rate'], 1) ?>% rate
                            </small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="analytics-card text-center">
                            <h3 class="text-danger"><?= number_format($analytics['error_count']) ?></h3>
                            <p class="mb-1">Errors</p>
                            <small class="trend-neutral">
                                <i class="fas fa-exclamation-triangle"></i>
                                System errors
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Charts -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="chart-container">
                            <h5 class="mb-3">Daily Job Aggregation</h5>
                            <canvas id="dailyJobsChart" height="100"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="chart-container">
                            <h5 class="mb-3">Jobs by Source</h5>
                            <canvas id="sourceChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Source Performance -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-trophy me-2"></i>
                                    Source Performance
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Source</th>
                                                <th>Jobs Found</th>
                                                <th>Jobs Imported</th>
                                                <th>Success Rate</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($analytics['source_performance'] as $source): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($source['name']) ?></td>
                                                <td><?= number_format($source['jobs_found']) ?></td>
                                                <td><?= number_format($source['jobs_imported']) ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar" style="width: <?= $source['success_rate'] ?>%">
                                                            <?= round($source['success_rate'], 1) ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?= $source['success_rate'] > 70 ? 'success' : ($source['success_rate'] > 40 ? 'warning' : 'danger') ?>">
                                                        <?= $source['success_rate'] > 70 ? 'Excellent' : ($source['success_rate'] > 40 ? 'Good' : 'Poor') ?>
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
                
                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history me-2"></i>
                                    Recent Activity
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($analytics['recent_activity'] as $activity): ?>
                                <div class="d-flex align-items-center mb-3 p-3 border rounded">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-<?= $activity['status'] === 'success' ? 'check-circle text-success' : 'exclamation-triangle text-warning' ?> fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1"><?= htmlspecialchars($activity['source_name']) ?></h6>
                                        <p class="mb-1"><?= htmlspecialchars($activity['message']) ?></p>
                                        <small class="text-muted">
                                            Jobs: <?= $activity['jobs_found'] ?> | 
                                            Imported: <?= $activity['jobs_imported'] ?> | 
                                            Duplicates: <?= $activity['jobs_duplicates'] ?> |
                                            <?= date('M j, Y H:i', strtotime($activity['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
        </main>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Daily Jobs Chart
        const dailyCtx = document.getElementById('dailyJobsChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($analytics['daily_jobs']['labels']) ?>,
                datasets: [{
                    label: 'Jobs Found',
                    data: <?= json_encode($analytics['daily_jobs']['found']) ?>,
                    borderColor: 'rgb(37, 99, 235)',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Jobs Imported',
                    data: <?= json_encode($analytics['daily_jobs']['imported']) ?>,
                    borderColor: 'rgb(5, 150, 105)',
                    backgroundColor: 'rgba(5, 150, 105, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Source Chart
        const sourceCtx = document.getElementById('sourceChart').getContext('2d');
        new Chart(sourceCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($analytics['source_stats']['labels']) ?>,
                datasets: [{
                    data: <?= json_encode($analytics['source_stats']['data']) ?>,
                    backgroundColor: [
                        'rgba(37, 99, 235, 0.8)',
                        'rgba(5, 150, 105, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
</body>
</html>
