<?php
/**
 * Job Aggregation Admin Panel
 * Manage job sources, scraping settings, and aggregated jobs
 */

$page_title = 'Job Aggregation Management - EMPLOIDB';

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

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'run_aggregation':
            try {
                $result = $jobAggregator->runAggregation();
                echo json_encode(['success' => true, 'data' => $result]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
            
        case 'toggle_source':
            try {
                $sourceId = (int)$_POST['source_id'];
                $status = $_POST['status'];
                $db->update("UPDATE job_sources SET status = ? WHERE id = ?", [$status, $sourceId]);
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
            
        case 'update_source':
            try {
                $sourceId = (int)$_POST['source_id'];
                $name = $_POST['name'];
                $url = $_POST['url'];
                $interval = (int)$_POST['interval'];
                $priority = (int)$_POST['priority'];
                
                $db->update(
                    "UPDATE job_sources SET name = ?, url = ?, scraping_interval = ?, priority = ? WHERE id = ?",
                    [$name, $url, $interval, $priority, $sourceId]
                );
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
            
        case 'import_job':
            try {
                $jobId = (int)$_POST['job_id'];
                $result = $jobAggregator->importToAnnonces($jobId);
                echo json_encode(['success' => true, 'annonce_id' => $result]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
            
        case 'delete_job':
            try {
                $jobId = (int)$_POST['job_id'];
                $db->delete("DELETE FROM aggregated_jobs WHERE id = ?", [$jobId]);
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
    }
}

// Get statistics with error handling
try {
    $stats = $jobAggregator ? $jobAggregator->getStatistics() : ['total_aggregated_jobs' => 0];
} catch (Exception $e) {
    $stats = ['total_aggregated_jobs' => 0];
    error_log("Error getting statistics: " . $e->getMessage());
}

// Get job sources with error handling
try {
    $sources = $db->fetchAll("SELECT * FROM job_sources ORDER BY priority ASC");
} catch (Exception $e) {
    $sources = [];
    error_log("Error getting job sources: " . $e->getMessage());
}

// Get recent aggregated jobs with error handling
try {
    $recentJobs = $db->fetchAll("
        SELECT aj.*, js.name as source_name 
        FROM aggregated_jobs aj 
        JOIN job_sources js ON aj.source_id = js.id 
        ORDER BY aj.created_at DESC 
        LIMIT 50
    ");
} catch (Exception $e) {
    $recentJobs = [];
    error_log("Error getting recent jobs: " . $e->getMessage());
}

// Get scraping logs with error handling
try {
    $scrapingLogs = $db->fetchAll("
        SELECT sl.*, js.name as source_name 
        FROM scraping_logs sl 
        JOIN job_sources js ON sl.source_id = js.id 
        ORDER BY sl.created_at DESC 
        LIMIT 20
    ");
} catch (Exception $e) {
    $scrapingLogs = [];
    error_log("Error getting scraping logs: " . $e->getMessage());
}

?>

<style>
        .stats-card {
            background: linear-gradient(135deg, var(--emploidb-primary-50) 0%, var(--emploidb-secondary-50) 100%);
            border: 1px solid var(--emploidb-primary-200);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .source-card {
            border: 1px solid var(--emploidb-neutral-200);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .source-card:hover {
            box-shadow: var(--emploidb-shadow-md);
            transform: translateY(-2px);
        }
        
        .source-active {
            border-left: 4px solid var(--emploidb-success);
        }
        
        .source-inactive {
            border-left: 4px solid var(--emploidb-neutral-300);
        }
        
        .source-error {
            border-left: 4px solid var(--emploidb-error);
        }
        
        .job-item {
            border: 1px solid var(--emploidb-neutral-200);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            background: white;
        }
        
        .job-item.imported {
            background: var(--emploidb-success-50);
            border-color: var(--emploidb-success-200);
        }
        
        .log-item {
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 0.5rem;
        }
        
        .log-success {
            background: var(--emploidb-success-50);
            border-left: 4px solid var(--emploidb-success);
        }
        
        .log-error {
            background: var(--emploidb-error-50);
            border-left: 4px solid var(--emploidb-error);
        }
        
        .log-warning {
            background: var(--emploidb-warning-50);
            border-left: 4px solid var(--emploidb-warning);
        }
        
        .btn-run-aggregation {
            background: linear-gradient(135deg, var(--emploidb-primary) 0%, var(--emploidb-secondary) 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-run-aggregation:hover {
            transform: translateY(-2px);
            box-shadow: var(--emploidb-shadow-lg);
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-active {
            background: var(--emploidb-success-100);
            color: var(--emploidb-success-800);
        }
        
        .status-inactive {
            background: var(--emploidb-neutral-100);
            color: var(--emploidb-neutral-600);
        }
        
        .status-error {
            background: var(--emploidb-error-100);
            color: var(--emploidb-error-800);
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
                        <i class="fas fa-sync-alt me-2"></i>
                        Job Aggregation Management
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-run-aggregation" onclick="runAggregation()">
                            <i class="fas fa-play me-2"></i>
                            Run Aggregation
                        </button>
                    </div>
                </div>
                
                <!-- Setup Message -->
                <?php if (empty($sources) && empty($recentJobs)): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <h4 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Setup Required</h4>
                    <p>It looks like the job aggregation system hasn't been set up yet. To get started:</p>
                    <ol>
                        <li><strong>Run the setup script:</strong> <a href="../setup_job_aggregation.php" class="btn btn-sm btn-primary">Setup Job Aggregation</a></li>
                        <li><strong>Configure job sources</strong> in the API Management section</li>
                        <li><strong>Enable automation</strong> in the Automation Settings section</li>
                    </ol>
                    <hr>
                    <p class="mb-0">Once setup is complete, you'll see job sources, statistics, and aggregated jobs here.</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <h3 class="text-primary"><?= $stats['total_aggregated_jobs'] ?? 0 ?></h3>
                            <p class="mb-0">Total Aggregated Jobs</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <h3 class="text-success"><?= count($sources) ?></h3>
                            <p class="mb-0">Active Sources</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <h3 class="text-info"><?= count($recentJobs) ?></h3>
                            <p class="mb-0">Recent Jobs</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card text-center">
                            <h3 class="text-warning"><?= count($scrapingLogs) ?></h3>
                            <p class="mb-0">Recent Logs</p>
                        </div>
                    </div>
                </div>
                
                <!-- Job Sources -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-globe me-2"></i>
                                    Job Sources
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($sources)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-globe fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Job Sources Configured</h5>
                                    <p class="text-muted">Add job sources in the API Management section to start aggregating jobs.</p>
                                    <a href="api_management.php" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Add Job Sources
                                    </a>
                                </div>
                                <?php else: ?>
                                <?php foreach ($sources as $source): ?>
                                <div class="source-card source-<?= $source['status'] ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?= htmlspecialchars($source['name']) ?></h6>
                                            <p class="text-muted small mb-2"><?= htmlspecialchars($source['url']) ?></p>
                                            <div class="d-flex gap-2">
                                                <span class="status-badge status-<?= $source['status'] ?>">
                                                    <?= ucfirst($source['status']) ?>
                                                </span>
                                                <span class="badge bg-secondary">Priority: <?= $source['priority'] ?></span>
                                                <span class="badge bg-info">Interval: <?= $source['scraping_interval'] ?>min</span>
                                            </div>
                                        </div>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="editSource(<?= $source['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-<?= $source['status'] === 'active' ? 'warning' : 'success' ?>" 
                                                    onclick="toggleSource(<?= $source['id'] ?>, '<?= $source['status'] === 'active' ? 'inactive' : 'active' ?>')">
                                                <i class="fas fa-<?= $source['status'] === 'active' ? 'pause' : 'play' ?>"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php if ($source['last_scraped']): ?>
                                    <small class="text-muted">
                                        Last scraped: <?= date('M j, Y H:i', strtotime($source['last_scraped'])) ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Jobs -->
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-briefcase me-2"></i>
                                    Recent Aggregated Jobs
                                </h5>
                            </div>
                            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                <?php if (empty($recentJobs)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Jobs Aggregated Yet</h5>
                                    <p class="text-muted">Run the job aggregation to start collecting jobs from configured sources.</p>
                                    <button class="btn btn-primary" onclick="runAggregation()">
                                        <i class="fas fa-play me-2"></i>Run Aggregation
                                    </button>
                                </div>
                                <?php else: ?>
                                <?php foreach ($recentJobs as $job): ?>
                                <div class="job-item <?= $job['imported_to_annonces'] ? 'imported' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?= htmlspecialchars($job['title']) ?></h6>
                                            <p class="text-muted small mb-1"><?= htmlspecialchars($job['company_name']) ?></p>
                                            <p class="text-muted small mb-2"><?= htmlspecialchars($job['location']) ?></p>
                                            <div class="d-flex gap-2">
                                                <span class="badge bg-primary"><?= htmlspecialchars($job['source_name']) ?></span>
                                                <?php if ($job['imported_to_annonces']): ?>
                                                <span class="badge bg-success">Imported</span>
                                                <?php else: ?>
                                                <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="btn-group">
                                            <?php if (!$job['imported_to_annonces']): ?>
                                            <button class="btn btn-sm btn-outline-success" onclick="importJob(<?= $job['id'] ?>)">
                                                <i class="fas fa-download"></i>
                                            </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteJob(<?= $job['id'] ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('M j, Y H:i', strtotime($job['created_at'])) ?>
                                    </small>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Scraping Logs -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-list-alt me-2"></i>
                                    Recent Scraping Logs
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($scrapingLogs)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-list-alt fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Scraping Logs Yet</h5>
                                    <p class="text-muted">Logs will appear here after running job aggregation.</p>
                                </div>
                                <?php else: ?>
                                <?php foreach ($scrapingLogs as $log): ?>
                                <div class="log-item log-<?= $log['status'] ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?= htmlspecialchars($log['source_name']) ?></h6>
                                            <p class="mb-1"><?= htmlspecialchars($log['message']) ?></p>
                                            <div class="d-flex gap-3">
                                                <small>Jobs found: <?= $log['jobs_found'] ?></small>
                                                <small>Jobs imported: <?= $log['jobs_imported'] ?></small>
                                                <small>Duplicates: <?= $log['jobs_duplicates'] ?></small>
                                                <small>Errors: <?= $log['jobs_errors'] ?></small>
                                                <?php if ($log['execution_time']): ?>
                                                <small>Time: <?= number_format($log['execution_time'], 2) ?>s</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <?= date('M j, Y H:i:s', strtotime($log['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Edit Source Modal -->
    <div class="modal fade" id="editSourceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Job Source</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editSourceForm">
                        <input type="hidden" id="editSourceId" name="source_id">
                        <div class="mb-3">
                            <label for="editSourceName" class="form-label">Source Name</label>
                            <input type="text" class="form-control" id="editSourceName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSourceUrl" class="form-label">URL</label>
                            <input type="url" class="form-control" id="editSourceUrl" name="url" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSourceInterval" class="form-label">Scraping Interval (minutes)</label>
                            <input type="number" class="form-control" id="editSourceInterval" name="interval" min="5" max="1440" required>
                        </div>
                        <div class="mb-3">
                            <label for="editSourcePriority" class="form-label">Priority</label>
                            <input type="number" class="form-control" id="editSourcePriority" name="priority" min="1" max="10" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveSource()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        function runAggregation() {
            if (confirm('Are you sure you want to run job aggregation? This may take several minutes.')) {
                $.post('', {
                    action: 'run_aggregation'
                }, function(response) {
                    if (response.success) {
                        alert('Aggregation completed successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }, 'json');
            }
        }
        
        function toggleSource(sourceId, newStatus) {
            $.post('', {
                action: 'toggle_source',
                source_id: sourceId,
                status: newStatus
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }, 'json');
        }
        
        function editSource(sourceId) {
            // Load source data and show modal
            // This would need to be implemented with actual data loading
            $('#editSourceModal').modal('show');
        }
        
        function saveSource() {
            $.post('', {
                action: 'update_source',
                source_id: $('#editSourceId').val(),
                name: $('#editSourceName').val(),
                url: $('#editSourceUrl').val(),
                interval: $('#editSourceInterval').val(),
                priority: $('#editSourcePriority').val()
            }, function(response) {
                if (response.success) {
                    $('#editSourceModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }, 'json');
        }
        
        function importJob(jobId) {
            if (confirm('Import this job to the main job listings?')) {
                $.post('', {
                    action: 'import_job',
                    job_id: jobId
                }, function(response) {
                    if (response.success) {
                        alert('Job imported successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }, 'json');
            }
        }
        
        function deleteJob(jobId) {
            if (confirm('Are you sure you want to delete this job?')) {
                $.post('', {
                    action: 'delete_job',
                    job_id: jobId
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }, 'json');
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
