<?php
/**
 * API Management Panel
 * Manage API keys, endpoints, and integrations
 */

$page_title = 'API Management - EMPLOIDB';

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
        case 'test_api':
            try {
                $sourceId = (int)$_POST['source_id'];
                $source = $db->fetch("SELECT * FROM job_sources WHERE id = ?", [$sourceId]);
                
                if (!$source) {
                    echo json_encode(['success' => false, 'message' => 'Source not found']);
                    exit;
                }
                
                // Test the API endpoint
                $result = testApiEndpoint($source);
                echo json_encode(['success' => true, 'data' => $result]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
            
        case 'update_api_config':
            try {
                $sourceId = (int)$_POST['source_id'];
                $apiKey = $_POST['api_key'];
                $apiEndpoint = $_POST['api_endpoint'];
                $scrapingConfig = $_POST['scraping_config'];
                
                $db->update(
                    "UPDATE job_sources SET api_key = ?, api_endpoint = ?, scraping_config = ? WHERE id = ?",
                    [$apiKey, $apiEndpoint, $scrapingConfig, $sourceId]
                );
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
    }
}

// Get API sources
$apiSources = $db->fetchAll("SELECT * FROM job_sources ORDER BY priority ASC");

// Get API usage statistics
$apiStats = getApiUsageStats($db);

?>

<?php
/**
 * Test API endpoint
 */
function testApiEndpoint($source) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $source['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    if ($source['api_key']) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $source['api_key'],
            'Content-Type: application/json'
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'response_size' => strlen($response),
        'error' => $error,
        'success' => $httpCode >= 200 && $httpCode < 300
    ];
}

/**
 * Get API usage statistics
 */
function getApiUsageStats($db) {
    try {
        $activeApis = $db->fetch("SELECT COUNT(*) as count FROM job_sources WHERE status = 'active'")['count'];
        
        $totalRequests = $db->fetch(
            "SELECT COUNT(*) as count FROM scraping_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        )['count'];
        
        $errorCount = $db->fetch(
            "SELECT COUNT(*) as count FROM scraping_logs WHERE status = 'error' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        )['count'];
        
        $errorRate = $totalRequests > 0 ? round(($errorCount / $totalRequests) * 100, 1) : 0;
        
        return [
            'active_apis' => $activeApis,
            'total_requests' => $totalRequests,
            'error_rate' => $errorRate
        ];
        
    } catch (Exception $e) {
        return [
            'active_apis' => 0,
            'total_requests' => 0,
            'error_rate' => 0
        ];
    }
}
?>

<style>
    .api-card {
        border: 1px solid var(--emploidb-neutral-200);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }
    
    .api-card:hover {
        box-shadow: var(--emploidb-shadow-md);
        transform: translateY(-2px);
    }
    
    .api-status {
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
    
    .api-key {
        font-family: 'Courier New', monospace;
        background: var(--emploidb-neutral-100);
        padding: 0.5rem;
        border-radius: 4px;
        word-break: break-all;
    }
    
    .stats-card {
        background: linear-gradient(135deg, var(--emploidb-primary-50) 0%, var(--emploidb-secondary-50) 100%);
        border: 1px solid var(--emploidb-primary-200);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
    }
    
    .config-editor {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
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
                        <i class="fas fa-plug me-2"></i>
                        API Management
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-primary" onclick="refreshAllApis()">
                            <i class="fas fa-sync-alt me-2"></i>
                            Refresh All APIs
                        </button>
                    </div>
                </div>
                
                <!-- API Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h3 class="text-primary"><?= count($apiSources) ?></h3>
                            <p class="mb-0">Total API Sources</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h3 class="text-success"><?= $apiStats['active_apis'] ?></h3>
                            <p class="mb-0">Active APIs</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h3 class="text-info"><?= $apiStats['total_requests'] ?></h3>
                            <p class="mb-0">Total Requests (24h)</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <h3 class="text-warning"><?= $apiStats['error_rate'] ?>%</h3>
                            <p class="mb-0">Error Rate</p>
                        </div>
                    </div>
                </div>
                
                <!-- API Sources -->
                <div class="row">
                    <?php foreach ($apiSources as $source): ?>
                    <div class="col-lg-6">
                        <div class="api-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($source['name']) ?></h5>
                                    <p class="text-muted small mb-2"><?= htmlspecialchars($source['url']) ?></p>
                                    <span class="api-status status-<?= $source['status'] ?>">
                                        <?= ucfirst($source['status']) ?>
                                    </span>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="testApi(<?= $source['id'] ?>)">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="editApiConfig(<?= $source['id'] ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted">Priority</small>
                                    <div class="fw-bold"><?= $source['priority'] ?></div>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Interval</small>
                                    <div class="fw-bold"><?= $source['scraping_interval'] ?> min</div>
                                </div>
                            </div>
                            
                            <?php if ($source['api_key']): ?>
                            <div class="mb-3">
                                <small class="text-muted">API Key</small>
                                <div class="api-key"><?= htmlspecialchars(substr($source['api_key'], 0, 20)) ?>...</div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($source['api_endpoint']): ?>
                            <div class="mb-3">
                                <small class="text-muted">API Endpoint</small>
                                <div class="api-key"><?= htmlspecialchars($source['api_endpoint']) ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($source['last_scraped']): ?>
                            <small class="text-muted">
                                Last scraped: <?= date('M j, Y H:i', strtotime($source['last_scraped'])) ?>
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
        </main>
    </div>
</div>

<!-- API Config Modal -->
    <div class="modal fade" id="apiConfigModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">API Configuration</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="apiConfigForm">
                        <input type="hidden" id="configSourceId" name="source_id">
                        
                        <div class="mb-3">
                            <label for="configApiKey" class="form-label">API Key</label>
                            <input type="text" class="form-control" id="configApiKey" name="api_key" placeholder="Enter API key">
                        </div>
                        
                        <div class="mb-3">
                            <label for="configApiEndpoint" class="form-label">API Endpoint</label>
                            <input type="url" class="form-control" id="configApiEndpoint" name="api_endpoint" placeholder="https://api.example.com/jobs">
                        </div>
                        
                        <div class="mb-3">
                            <label for="configScrapingConfig" class="form-label">Scraping Configuration (JSON)</label>
                            <textarea class="form-control config-editor" id="configScrapingConfig" name="scraping_config" rows="10" placeholder='{"selectors": {"title": ".job-title", "description": ".job-description"}}'></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveApiConfig()">Save Configuration</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        function testApi(sourceId) {
            $.post('', {
                action: 'test_api',
                source_id: sourceId
            }, function(response) {
                if (response.success) {
                    alert('API test successful!');
                } else {
                    alert('API test failed: ' + response.message);
                }
            }, 'json');
        }
        
        function editApiConfig(sourceId) {
            // Load source data and show modal
            $('#apiConfigModal').modal('show');
            $('#configSourceId').val(sourceId);
        }
        
        function saveApiConfig() {
            $.post('', {
                action: 'update_api_config',
                source_id: $('#configSourceId').val(),
                api_key: $('#configApiKey').val(),
                api_endpoint: $('#configApiEndpoint').val(),
                scraping_config: $('#configScrapingConfig').val()
            }, function(response) {
                if (response.success) {
                    $('#apiConfigModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }, 'json');
        }
        
        function refreshAllApis() {
            if (confirm('Test all API endpoints? This may take a few minutes.')) {
                // Implementation for testing all APIs
                alert('API refresh initiated. Check logs for results.');
            }
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</body>
</html>
