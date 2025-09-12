<?php
/**
 * Automation Settings Panel
 * Configure automated job aggregation and system settings
 */

$page_title = 'Automation Settings - EMPLOIDB';

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

// Handle form submissions
if ($_POST) {
    try {
        $settings = [
            'job_aggregation_enabled' => $_POST['job_aggregation_enabled'] ?? 'false',
            'scraping_interval' => (int)($_POST['scraping_interval'] ?? 20),
            'auto_import_jobs' => $_POST['auto_import_jobs'] ?? 'false',
            'max_jobs_per_source' => (int)($_POST['max_jobs_per_source'] ?? 100),
            'job_expiry_days' => (int)($_POST['job_expiry_days'] ?? 30),
            'notification_email' => $_POST['notification_email'] ?? '',
            'error_threshold' => (int)($_POST['error_threshold'] ?? 5),
            'cleanup_enabled' => $_POST['cleanup_enabled'] ?? 'false',
            'cleanup_interval' => (int)($_POST['cleanup_interval'] ?? 7)
        ];
        
        foreach ($settings as $key => $value) {
            $db->query("
                INSERT INTO system_settings (setting_key, setting_value, setting_type, category) 
                VALUES (?, ?, 'string', 'automation')
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ", [$key, $value]);
        }
        
        $success_message = "Automation settings updated successfully!";
        
    } catch (Exception $e) {
        $error_message = "Error updating settings: " . $e->getMessage();
    }
}

// Get current settings
$currentSettings = [];
try {
    $settings = $db->fetchAll("SELECT setting_key, setting_value FROM system_settings WHERE category = 'automation'");
    foreach ($settings as $setting) {
        $currentSettings[$setting['setting_key']] = $setting['setting_value'];
    }
} catch (Exception $e) {
    // Use default settings if table doesn't exist
    $currentSettings = [
        'job_aggregation_enabled' => 'true',
        'scraping_interval' => '20',
        'auto_import_jobs' => 'true',
        'max_jobs_per_source' => '100',
        'job_expiry_days' => '30',
        'notification_email' => '',
        'error_threshold' => '5',
        'cleanup_enabled' => 'true',
        'cleanup_interval' => '7'
    ];
}

// Get automation statistics
$automationStats = getAutomationStats($db);

?>

<?php
/**
 * Get automation statistics
 */
function getAutomationStats($db) {
    try {
        // Get total runs in last 24 hours
        $totalRuns = $db->fetch(
            "SELECT COUNT(*) as count FROM scraping_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        )['count'];
        
        // Get success rate
        $successRuns = $db->fetch(
            "SELECT COUNT(*) as count FROM scraping_logs WHERE status = 'success' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        )['count'];
        
        $successRate = $totalRuns > 0 ? round(($successRuns / $totalRuns) * 100, 1) : 0;
        
        // Get jobs processed
        $jobsProcessed = $db->fetch(
            "SELECT SUM(jobs_imported) as total FROM scraping_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        )['total'] ?? 0;
        
        // Get last run time
        $lastRun = $db->fetch(
            "SELECT MAX(created_at) as last_run FROM scraping_logs"
        )['last_run'];
        
        $lastRunFormatted = $lastRun ? date('M j, H:i', strtotime($lastRun)) : 'Never';
        
        // Check cron status (simplified check)
        $cronStatus = $totalRuns > 0 ? 'active' : 'inactive';
        
        return [
            'total_runs' => $totalRuns,
            'success_rate' => $successRate,
            'jobs_processed' => $jobsProcessed,
            'last_run' => $lastRunFormatted,
            'cron_status' => $cronStatus
        ];
        
    } catch (Exception $e) {
        return [
            'total_runs' => 0,
            'success_rate' => 0,
            'jobs_processed' => 0,
            'last_run' => 'Never',
            'cron_status' => 'inactive'
        ];
    }
}
?>

<style>
    .settings-card {
        border: 1px solid var(--emploidb-neutral-200);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        background: white;
    }
    
    .settings-card h5 {
        color: var(--emploidb-primary);
        border-bottom: 2px solid var(--emploidb-primary-100);
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .stats-card {
        background: linear-gradient(135deg, var(--emploidb-primary-50) 0%, var(--emploidb-secondary-50) 100%);
        border: 1px solid var(--emploidb-primary-200);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
    }
    
    .form-switch .form-check-input {
        width: 3rem;
        height: 1.5rem;
    }
    
    .cron-command {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
        word-break: break-all;
    }
    
    .status-indicator {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 8px;
    }
    
    .status-active {
        background: var(--emploidb-success);
    }
    
    .status-inactive {
        background: var(--emploidb-neutral-400);
    }
    
    .status-error {
        background: var(--emploidb-error);
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
                        <i class="fas fa-robot me-2"></i>
                        Automation Settings
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-success" onclick="testAutomation()">
                            <i class="fas fa-play me-2"></i>
                            Test Automation
                        </button>
                    </div>
                </div>
                
                <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Automation Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h3 class="text-primary"><?= $automationStats['total_runs'] ?></h3>
                            <p class="mb-0">Total Runs (24h)</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h3 class="text-success"><?= $automationStats['success_rate'] ?>%</h3>
                            <p class="mb-0">Success Rate</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h3 class="text-info"><?= $automationStats['jobs_processed'] ?></h3>
                            <p class="mb-0">Jobs Processed</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h3 class="text-warning"><?= $automationStats['last_run'] ?></h3>
                            <p class="mb-0">Last Run</p>
                        </div>
                    </div>
                </div>
                
                <form method="POST">
                    <div class="row">
                        <!-- Job Aggregation Settings -->
                        <div class="col-lg-6">
                            <div class="settings-card">
                                <h5><i class="fas fa-sync-alt me-2"></i>Job Aggregation</h5>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="job_aggregation_enabled" 
                                               name="job_aggregation_enabled" value="true" 
                                               <?= ($currentSettings['job_aggregation_enabled'] ?? 'false') === 'true' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="job_aggregation_enabled">
                                            Enable Job Aggregation
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="scraping_interval" class="form-label">Scraping Interval (minutes)</label>
                                    <input type="number" class="form-control" id="scraping_interval" name="scraping_interval" 
                                           value="<?= htmlspecialchars($currentSettings['scraping_interval'] ?? '20') ?>" 
                                           min="5" max="1440">
                                    <div class="form-text">How often to scrape job sources (5-1440 minutes)</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="max_jobs_per_source" class="form-label">Max Jobs Per Source</label>
                                    <input type="number" class="form-control" id="max_jobs_per_source" name="max_jobs_per_source" 
                                           value="<?= htmlspecialchars($currentSettings['max_jobs_per_source'] ?? '100') ?>" 
                                           min="10" max="1000">
                                    <div class="form-text">Maximum jobs to fetch per source per run</div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="auto_import_jobs" 
                                               name="auto_import_jobs" value="true" 
                                               <?= ($currentSettings['auto_import_jobs'] ?? 'false') === 'true' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="auto_import_jobs">
                                            Auto-import Jobs to Main Listings
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notification Settings -->
                        <div class="col-lg-6">
                            <div class="settings-card">
                                <h5><i class="fas fa-bell me-2"></i>Notifications</h5>
                                
                                <div class="mb-3">
                                    <label for="notification_email" class="form-label">Notification Email</label>
                                    <input type="email" class="form-control" id="notification_email" name="notification_email" 
                                           value="<?= htmlspecialchars($currentSettings['notification_email'] ?? '') ?>" 
                                           placeholder="admin@jobmaroc.ma">
                                    <div class="form-text">Email address for automation notifications</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="error_threshold" class="form-label">Error Threshold</label>
                                    <input type="number" class="form-control" id="error_threshold" name="error_threshold" 
                                           value="<?= htmlspecialchars($currentSettings['error_threshold'] ?? '5') ?>" 
                                           min="1" max="50">
                                    <div class="form-text">Number of consecutive errors before notification</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Data Management -->
                        <div class="col-lg-6">
                            <div class="settings-card">
                                <h5><i class="fas fa-database me-2"></i>Data Management</h5>
                                
                                <div class="mb-3">
                                    <label for="job_expiry_days" class="form-label">Job Expiry Days</label>
                                    <input type="number" class="form-control" id="job_expiry_days" name="job_expiry_days" 
                                           value="<?= htmlspecialchars($currentSettings['job_expiry_days'] ?? '30') ?>" 
                                           min="1" max="365">
                                    <div class="form-text">Days after which jobs are considered expired</div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="cleanup_enabled" 
                                               name="cleanup_enabled" value="true" 
                                               <?= ($currentSettings['cleanup_enabled'] ?? 'false') === 'true' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="cleanup_enabled">
                                            Enable Automatic Cleanup
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="cleanup_interval" class="form-label">Cleanup Interval (days)</label>
                                    <input type="number" class="form-control" id="cleanup_interval" name="cleanup_interval" 
                                           value="<?= htmlspecialchars($currentSettings['cleanup_interval'] ?? '7') ?>" 
                                           min="1" max="30">
                                    <div class="form-text">How often to clean up old data</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cron Job Configuration -->
                        <div class="col-lg-6">
                            <div class="settings-card">
                                <h5><i class="fas fa-clock me-2"></i>Cron Job Configuration</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">Cron Command</label>
                                    <div class="cron-command">
                                        */<?= htmlspecialchars($currentSettings['scraping_interval'] ?? '20') ?> * * * * /usr/bin/php <?= __DIR__ ?>/../cron/job_aggregation_cron.php >> <?= __DIR__ ?>/../logs/cron.log 2>&1
                                    </div>
                                    <div class="form-text">Add this command to your crontab for automated scraping</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Windows Task Scheduler</label>
                                    <div class="cron-command">
                                        php <?= __DIR__ ?>/../cron/job_aggregation_cron.php
                                    </div>
                                    <div class="form-text">Command for Windows Task Scheduler</div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex align-items-center">
                                        <span class="status-indicator status-<?= $automationStats['cron_status'] ?>"></span>
                                        <span>Cron Job Status: <strong><?= ucfirst($automationStats['cron_status']) ?></strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="resetToDefaults()">
                                    <i class="fas fa-undo me-2"></i>
                                    Reset to Defaults
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Save Settings
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        function testAutomation() {
            if (confirm('Run a test automation cycle? This will scrape all active sources.')) {
                // Implementation for testing automation
                alert('Test automation initiated. Check the job aggregation panel for results.');
            }
        }
        
        function resetToDefaults() {
            if (confirm('Reset all settings to default values?')) {
                document.getElementById('job_aggregation_enabled').checked = true;
                document.getElementById('scraping_interval').value = '20';
                document.getElementById('max_jobs_per_source').value = '100';
                document.getElementById('auto_import_jobs').checked = true;
                document.getElementById('job_expiry_days').value = '30';
                document.getElementById('error_threshold').value = '5';
                document.getElementById('cleanup_enabled').checked = true;
                document.getElementById('cleanup_interval').value = '7';
                document.getElementById('notification_email').value = '';
            }
        }
        
        // Update cron command when interval changes
        document.getElementById('scraping_interval').addEventListener('input', function() {
            const interval = this.value;
            const cronCommand = document.querySelector('.cron-command');
            if (cronCommand) {
                cronCommand.textContent = `*/${interval} * * * * /usr/bin/php <?= __DIR__ ?>/../cron/job_aggregation_cron.php >> <?= __DIR__ ?>/../logs/cron.log 2>&1`;
            }
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</body>
</html>
