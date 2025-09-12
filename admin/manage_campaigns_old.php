<?php
$page_title = 'Enterprise Campaign Management - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// RBAC Check - Temporarily disabled for debugging
// if (!$rbac->hasPageAccess('manage_campaigns')) {
//     header('Location: dashboard.php?error=access_denied');
//     exit;
// }

// Handle campaign actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $campaign_id = $_POST['campaign_id'] ?? 0;
    
    // CSRF Protection
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        header('Location: manage_campaigns.php?error=csrf');
        exit;
    }
    
    try {
        if ($action === 'delete' && $campaign_id) {
            // Delete campaign and related data
            $db->delete("DELETE FROM campaign_ads WHERE campaign_id = ?", [$campaign_id]);
            $db->delete("DELETE FROM campaign_performance WHERE campaign_id = ?", [$campaign_id]);
            $db->delete("DELETE FROM campaigns WHERE id = ?", [$campaign_id]);
            
            $_SESSION['campaign_notification'] = ['type' => 'success', 'message' => 'Campagne supprimée avec succès'];
            header('Location: manage_campaigns.php');
            exit;
        }
        
        if ($action === 'activate' && $campaign_id) {
            $db->update("UPDATE campaigns SET status = 'active', activated_at = NOW() WHERE id = ?", [$campaign_id]);
            $_SESSION['campaign_notification'] = ['type' => 'success', 'message' => 'Campagne activée avec succès'];
            header('Location: manage_campaigns.php');
            exit;
        }
        
        if ($action === 'deactivate' && $campaign_id) {
            $db->update("UPDATE campaigns SET status = 'inactive', deactivated_at = NOW() WHERE id = ?", [$campaign_id]);
            $_SESSION['campaign_notification'] = ['type' => 'warning', 'message' => 'Campagne désactivée avec succès'];
            header('Location: manage_campaigns.php');
            exit;
        }
        
        // Bulk operations
        if (in_array($action, ['bulk_activate', 'bulk_deactivate', 'bulk_delete']) && isset($_POST['campaign_ids'])) {
            $campaign_ids = $_POST['campaign_ids'];
            $count = 0;
            
            foreach ($campaign_ids as $id) {
                if ($action === 'bulk_activate') {
                    $db->update("UPDATE campaigns SET status = 'active', activated_at = NOW() WHERE id = ?", [$id]);
                } elseif ($action === 'bulk_deactivate') {
                    $db->update("UPDATE campaigns SET status = 'inactive', deactivated_at = NOW() WHERE id = ?", [$id]);
                } elseif ($action === 'bulk_delete') {
                    // Delete campaign and related data
                    $db->delete("DELETE FROM campaign_ads WHERE campaign_id = ?", [$id]);
                    $db->delete("DELETE FROM campaign_performance WHERE campaign_id = ?", [$id]);
                    $db->delete("DELETE FROM campaigns WHERE id = ?", [$id]);
                }
                $count++;
            }
            
            $messages = [
                'bulk_activate' => "$count campagne(s) activée(s) avec succès",
                'bulk_deactivate' => "$count campagne(s) désactivée(s) avec succès",
                'bulk_delete' => "$count campagne(s) supprimée(s) avec succès"
            ];
            
            $_SESSION['campaign_notification'] = ['type' => 'success', 'message' => $messages[$action]];
            header('Location: manage_campaigns.php');
            exit;
        }

        // Add new campaign
        if ($action === 'add_campaign') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $advertiser_id = $_POST['advertiser_id'] ?? 0;
            $budget = floatval($_POST['budget'] ?? 0);
            $start_date = $_POST['start_date'] ?? date('Y-m-d');
            $end_date = $_POST['end_date'] ?? date('Y-m-d', strtotime('+30 days'));
            $target_audience = $_POST['target_audience'] ?? 'all';
            $status = $_POST['status'] ?? 'pending';
            $daily_budget = floatval($_POST['daily_budget'] ?? 0);
            $bid_type = $_POST['bid_type'] ?? 'cpc';
            $bid_amount = floatval($_POST['bid_amount'] ?? 0);
            $frequency_cap = intval($_POST['frequency_cap'] ?? 0);
            $bidding_strategy = $_POST['bidding_strategy'] ?? 'manual';
            $optimization = $_POST['optimization'] ?? 'clicks';

            // Enhanced validation
            if (empty($name) || empty($description)) {
                throw new Exception('Nom et description sont obligatoires');
            }
            
            if ($budget < 0) {
                throw new Exception('Le budget ne peut pas être négatif');
            }
            
            if ($start_date > $end_date) {
                throw new Exception('La date de début doit être antérieure à la date de fin');
            }

            // Insert campaign
            $campaign_id = $db->insert("
                INSERT INTO campaigns (name, description, advertiser_id, budget, start_date, 
                end_date, target_audience, status, daily_budget, bid_type, bid_amount, 
                frequency_cap, bidding_strategy, optimization, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ", [$name, $description, $advertiser_id, $budget, $start_date, $end_date, 
                $target_audience, $status, $daily_budget, $bid_type, $bid_amount, 
                $frequency_cap, $bidding_strategy, $optimization]);

            $_SESSION['campaign_notification'] = ['type' => 'success', 'message' => 'Campagne ajoutée avec succès'];
            header('Location: manage_campaigns.php');
            exit;
        }

        // Edit campaign
        if ($action === 'edit_campaign') {
            $campaign_id = $_POST['campaign_id'] ?? 0;
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $advertiser_id = $_POST['advertiser_id'] ?? 0;
            $budget = floatval($_POST['budget'] ?? 0);
            $start_date = $_POST['start_date'] ?? date('Y-m-d');
            $end_date = $_POST['end_date'] ?? date('Y-m-d', strtotime('+30 days'));
            $target_audience = $_POST['target_audience'] ?? 'all';
            $status = $_POST['status'] ?? 'pending';
            $daily_budget = floatval($_POST['daily_budget'] ?? 0);
            $bid_type = $_POST['bid_type'] ?? 'cpc';
            $bid_amount = floatval($_POST['bid_amount'] ?? 0);
            $frequency_cap = intval($_POST['frequency_cap'] ?? 0);
            $bidding_strategy = $_POST['bidding_strategy'] ?? 'manual';
            $optimization = $_POST['optimization'] ?? 'clicks';

            // Enhanced validation
            if (empty($name) || empty($description)) {
                throw new Exception('Nom et description sont obligatoires');
            }
            
            if ($budget < 0) {
                throw new Exception('Le budget ne peut pas être négatif');
            }
            
            if ($start_date > $end_date) {
                throw new Exception('La date de début doit être antérieure à la date de fin');
            }

            // Update campaign
            $db->update("
                UPDATE campaigns SET name = ?, description = ?, advertiser_id = ?, budget = ?, 
                start_date = ?, end_date = ?, target_audience = ?, status = ?, daily_budget = ?, 
                bid_type = ?, bid_amount = ?, frequency_cap = ?, bidding_strategy = ?, 
                optimization = ?, updated_at = NOW()
                WHERE id = ?
            ", [$name, $description, $advertiser_id, $budget, $start_date, $end_date, 
                $target_audience, $status, $daily_budget, $bid_type, $bid_amount, 
                $frequency_cap, $bidding_strategy, $optimization, $campaign_id]);

            $_SESSION['campaign_notification'] = ['type' => 'success', 'message' => 'Campagne mise à jour avec succès'];
            header('Location: manage_campaigns.php');
            exit;
        }
    } catch (Exception $e) {
        error_log("Database error in manage_campaigns.php: " . $e->getMessage());
        header('Location: manage_campaigns.php?error=database');
        exit;
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$advertiser_filter = $_GET['advertiser'] ?? '';
$search = $_GET['search'] ?? '';

// Build query conditions
$where_conditions = ['1=1'];
$params = [];

if ($status_filter) {
    $where_conditions[] = "c.status = ?";
    $params[] = $status_filter;
}

if ($advertiser_filter) {
    $where_conditions[] = "c.advertiser_id = ?";
    $params[] = $advertiser_filter;
}

if ($search) {
    $where_conditions[] = "(c.name LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(' AND ', $where_conditions);

// Get campaigns with enhanced data
try {
    // Check if campaigns table exists
    $table_exists = $db->fetch("SHOW TABLES LIKE 'campaigns'");
    
    if ($table_exists) {
        $campaigns_query = "SELECT c.*, a.company_name as advertiser_name,
                            (SELECT COUNT(*) FROM campaign_ads ca WHERE ca.campaign_id = c.id) as total_ads,
                            (SELECT COUNT(*) FROM campaign_ads ca 
                             JOIN advertisements ad ON ca.ad_id = ad.id 
                             WHERE ca.campaign_id = c.id AND ad.status = 'active') as active_ads,
                            (SELECT COALESCE(SUM(cp.impressions), 0) FROM campaign_performance cp WHERE cp.campaign_id = c.id) as total_impressions,
                            (SELECT COALESCE(SUM(cp.clicks), 0) FROM campaign_performance cp WHERE cp.campaign_id = c.id) as total_clicks,
                            (SELECT COALESCE(SUM(cp.revenue), 0) FROM campaign_performance cp WHERE cp.campaign_id = c.id) as total_revenue
                            FROM campaigns c 
                            LEFT JOIN advertisers a ON c.advertiser_id = a.id
                            WHERE $where_clause
                            ORDER BY c.created_at DESC";
        $campaigns = $db->fetchAll($campaigns_query, $params) ?? [];

        // Get enhanced statistics
        $campaignStats = [
            'total_campaigns' => $db->fetch("SELECT COUNT(*) as count FROM campaigns")['count'] ?? 0,
            'active_campaigns' => $db->fetch("SELECT COUNT(*) as count FROM campaigns WHERE status = 'active'")['count'] ?? 0,
            'pending_campaigns' => $db->fetch("SELECT COUNT(*) as count FROM campaigns WHERE status = 'pending'")['count'] ?? 0,
            'inactive_campaigns' => $db->fetch("SELECT COUNT(*) as count FROM campaigns WHERE status = 'inactive'")['count'] ?? 0,
            'new_campaigns_today' => $db->fetch("SELECT COUNT(*) as count FROM campaigns WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
            'new_campaigns_week' => $db->fetch("SELECT COUNT(*) as count FROM campaigns WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0,
            'total_budget' => $db->fetch("SELECT COALESCE(SUM(budget), 0) as total FROM campaigns")['total'] ?? 0,
            'avg_budget' => $db->fetch("SELECT COALESCE(AVG(budget), 0) as avg FROM campaigns")['avg'] ?? 0,
            'total_revenue' => $db->fetch("SELECT COALESCE(SUM(cp.revenue), 0) as total FROM campaign_performance cp")['total'] ?? 0,
            'total_impressions' => $db->fetch("SELECT COALESCE(SUM(cp.impressions), 0) as total FROM campaign_performance cp")['total'] ?? 0,
            'total_clicks' => $db->fetch("SELECT COALESCE(SUM(cp.clicks), 0) as total FROM campaign_performance cp")['total'] ?? 0
        ];

        // Get advertisers for filter dropdown
        $advertisers = $db->fetchAll("SELECT id, company_name FROM advertisers ORDER BY company_name", []) ?? [];
    } else {
        // Tables don't exist yet, use sample data
        $campaigns = [];
        $campaignStats = [
            'total_campaigns' => 0,
            'active_campaigns' => 0,
            'pending_campaigns' => 0,
            'inactive_campaigns' => 0,
            'new_campaigns_today' => 0,
            'new_campaigns_week' => 0,
            'total_budget' => 0,
            'avg_budget' => 0,
            'total_revenue' => 0,
            'total_impressions' => 0,
            'total_clicks' => 0
        ];
        $advertisers = [];
    }
    
    // Get filter options
    $bid_types = ['cpc', 'cpm', 'cpa', 'cpi', 'cpe'];
    $bidding_strategies = ['manual', 'auto', 'target_cpa'];
    $optimization_types = ['clicks', 'impressions', 'conversions', 'revenue'];

} catch (Exception $e) {
    $campaigns = [];
    $campaignStats = [
        'total_campaigns' => 0,
        'active_campaigns' => 0,
        'pending_campaigns' => 0,
        'inactive_campaigns' => 0,
        'new_campaigns_today' => 0,
        'new_campaigns_week' => 0,
        'total_budget' => 0,
        'avg_budget' => 0,
        'total_revenue' => 0,
        'total_impressions' => 0,
        'total_clicks' => 0
    ];
    $advertisers = [];
    $bid_types = [];
    $bidding_strategies = [];
    $optimization_types = [];
    error_log("Database error in manage_campaigns.php: " . $e->getMessage());
}
?>

<!-- Debug Information -->
<?php if (isset($_GET['debug'])): ?>
<div style="background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc;">
    <h3>Debug Information</h3>
    <p><strong>Admin ID:</strong> <?= $_SESSION['admin_id'] ?? 'Not set' ?></p>
    <p><strong>User Type:</strong> <?= $_SESSION['user_type'] ?? 'Not set' ?></p>
    <p><strong>RBAC Object:</strong> <?= isset($rbac) ? 'Available' : 'Not available' ?></p>
    <p><strong>Database Object:</strong> <?= isset($db) ? 'Available' : 'Not available' ?></p>
    <p><strong>Campaigns Count:</strong> <?= count($campaigns ?? []) ?></p>
    <p><strong>Advertisers Count:</strong> <?= count($advertisers ?? []) ?></p>
</div>
<?php endif; ?>

<!-- Main Content -->
<div class="enterprise-content">
    <div class="enterprise-main">
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --light-color: #f8fafc;
            --dark-color: #1e293b;
            --border-color: #e2e8f0;
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
        }
        
        .enterprise-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-lg);
        }
        
        .enterprise-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .enterprise-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }
        
        .enterprise-stat {
            text-align: center;
            padding: 1.5rem;
        }
        
        .enterprise-stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .enterprise-stat-label {
            color: var(--secondary-color);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.875rem;
        }
        
        .btn-enterprise {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-enterprise:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            color: white;
        }
        
        .table-enterprise {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .table-enterprise thead {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            color: white;
        }
        
        .table-enterprise th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.875rem;
        }
        
        .table-enterprise td {
            padding: 1rem;
            border-color: var(--border-color);
            vertical-align: middle;
        }
        
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-active { background-color: #dcfce7; color: #166534; }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-inactive { background-color: #fee2e2; color: #991b1b; }
        
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }
        
        .main-content {
            margin-left: 0;
            padding: 2rem;
            min-height: 100vh;
        }
        
        @media (min-width: 768px) {
            .main-content {
                margin-left: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Include Admin Sidebar -->
    <?php include __DIR__ . '/includes/admin_sidebar.php'; ?>
    
    <!-- Include Admin Topbar -->
    <?php include __DIR__ . '/includes/admin_topbar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Enterprise Header -->
        <div class="enterprise-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-0">
                            <i class="fas fa-bullhorn me-3"></i>
                            Gestion des Campagnes
                        </h1>
                        <p class="mb-0 mt-2 opacity-75">Système de gestion des campagnes publicitaires d'entreprise</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-light btn-lg" onclick="openAddCampaignModal()">
                            <i class="fas fa-plus me-2"></i>
                            Nouvelle Campagne
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Messages -->
<?php if (isset($_SESSION['campaign_notification'])): ?>
<div class="alert alert-<?= $_SESSION['campaign_notification']['type'] === 'success' ? 'success' : ($_SESSION['campaign_notification']['type'] === 'warning' ? 'warning' : 'danger') ?> alert-dismissible fade show" role="alert">
    <i class="fas fa-<?= $_SESSION['campaign_notification']['type'] === 'success' ? 'check-circle' : ($_SESSION['campaign_notification']['type'] === 'warning' ? 'exclamation-triangle' : 'times-circle') ?> me-2"></i>
    <?= htmlspecialchars($_SESSION['campaign_notification']['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['campaign_notification']); ?>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <?php
    $message = '';
    switch ($_GET['error']) {
        case 'database': $message = 'Erreur de base de données. Veuillez réessayer.'; break;
        case 'csrf': $message = 'Erreur de sécurité. Veuillez réessayer.'; break;
        default: $message = 'Une erreur est survenue. Veuillez réessayer.'; break;
    }
    echo $message;
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Campaign Overview Statistics -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <div class="enterprise-card h-100">
            <div class="enterprise-card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="fas fa-bullhorn fa-2x text-primary"></i>
                    </div>
                    <div class="text-end">
                        <h3 class="stat-value text-primary mb-0"><?= number_format($campaignStats['total_campaigns']) ?></h3>
                        <small class="text-muted">Total Campagnes</small>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-success">
                        <i class="fas fa-arrow-up"></i>
                        +<?= $campaignStats['new_campaigns_week'] ?> cette semaine
                    </span>
                    <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showCampaignDetails()">
                        Détails
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <div class="enterprise-card h-100">
            <div class="enterprise-card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="stat-icon bg-success bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="fas fa-play-circle fa-2x text-success"></i>
                    </div>
                    <div class="text-end">
                        <h3 class="stat-value text-success mb-0"><?= number_format($campaignStats['active_campaigns']) ?></h3>
                        <small class="text-muted">Actives</small>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-success">
                        <i class="fas fa-arrow-up"></i>
                        +<?= $campaignStats['new_campaigns_today'] ?> aujourd'hui
                    </span>
                    <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showActiveCampaigns()">
                        Détails
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <div class="enterprise-card h-100">
            <div class="enterprise-card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="stat-icon bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                    <div class="text-end">
                        <h3 class="stat-value text-warning mb-0"><?= number_format($campaignStats['pending_campaigns']) ?></h3>
                        <small class="text-muted">En Attente</small>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-warning">
                        <i class="fas fa-arrow-up"></i>
                        +18.3% ce mois
                    </span>
                    <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showPendingCampaigns()">
                        Détails
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <div class="enterprise-card h-100">
            <div class="enterprise-card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="stat-icon bg-info bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="fas fa-eye fa-2x text-info"></i>
                    </div>
                    <div class="text-end">
                        <h3 class="stat-value text-info mb-0"><?= number_format($campaignStats['total_impressions']) ?></h3>
                        <small class="text-muted">Impressions</small>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-success">
                        <i class="fas fa-arrow-up"></i>
                        +32.1% ce mois
                    </span>
                    <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showImpressions()">
                        Détails
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <div class="enterprise-card h-100">
            <div class="enterprise-card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="stat-icon bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="fas fa-dollar-sign fa-2x text-danger"></i>
                    </div>
                    <div class="text-end">
                        <h3 class="stat-value text-danger mb-0"><?= number_format($campaignStats['total_budget'], 0) ?> MAD</h3>
                        <small class="text-muted">Budget Total</small>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-success">
                        <i class="fas fa-arrow-up"></i>
                        +8.5% ce mois
                    </span>
                    <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showBudgetStats()">
                        Détails
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
        <div class="enterprise-card h-100">
            <div class="enterprise-card-body text-center">
                <div class="d-flex align-items-center justify-content-center mb-3">
                    <div class="stat-icon bg-purple bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="fas fa-coins fa-2x text-purple"></i>
                    </div>
                    <div class="text-end">
                        <h3 class="stat-value text-purple mb-0"><?= number_format($campaignStats['total_revenue'], 0) ?> MAD</h3>
                        <small class="text-muted">Revenus</small>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-success">
                        <i class="fas fa-arrow-up"></i>
                        +15.2% ce mois
                    </span>
                    <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showRevenueStats()">
                        Détails
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Campaign Management Header -->
<div class="enterprise-card mb-4">
    <div class="enterprise-card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="enterprise-card-title">
                    <i class="fas fa-bullhorn me-3"></i>
                    Enterprise Campaign Management
                </h2>
                <p class="enterprise-card-subtitle">
                    Gestion complète des campagnes publicitaires avec analyses avancées et contrôles
                </p>
            </div>
            <div class="d-flex gap-3">
                <button class="enterprise-btn enterprise-btn-outline" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i>
                    Actualiser
                </button>
                <button class="enterprise-btn enterprise-btn-primary" onclick="exportCampaignsData()">
                    <i class="fas fa-download"></i>
                    Exporter CSV
                </button>
                <button class="enterprise-btn enterprise-btn-accent" onclick="generateCampaignReport()">
                    <i class="fas fa-file-pdf"></i>
                    Rapport PDF
                </button>
                <button class="enterprise-btn enterprise-btn-info" onclick="showCampaignAnalytics()">
                    <i class="fas fa-chart-line"></i>
                    Analyses
                </button>
                <button class="enterprise-btn enterprise-btn-success" onclick="openAddCampaignModal()">
                    <i class="fas fa-plus"></i>
                    Nouvelle Campagne
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="enterprise-card mb-4">
    <div class="enterprise-card-header">
        <h4 class="enterprise-card-title">
            <i class="fas fa-search me-2"></i>
            Recherche et Filtres Avancés
        </h4>
    </div>
    <div class="enterprise-card-body">
        <form method="GET" class="row g-3">
            <!-- Basic Search -->
            <div class="col-md-4">
                <label class="form-label">Recherche</label>
                <input type="text" class="form-control" name="search" placeholder="Nom, description, annonceur..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Statut</label>
                <select class="form-select" name="status">
                    <option value="">Tous les Statuts</option>
                    <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Actif</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>En Attente</option>
                    <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactif</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Annonceur</label>
                <select class="form-select" name="advertiser">
                    <option value="">Tous les Annonceurs</option>
                    <?php foreach ($advertisers as $advertiser): ?>
                    <option value="<?= $advertiser['id'] ?>" <?= $advertiser_filter == $advertiser['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($advertiser['company_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Action Buttons -->
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="enterprise-btn enterprise-btn-primary">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                    <a href="manage_campaigns.php" class="enterprise-btn enterprise-btn-outline">
                        <i class="fas fa-times"></i> Effacer
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Campaigns Table -->
<div class="enterprise-card">
    <div class="enterprise-card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="enterprise-card-title">
                <i class="fas fa-table me-2"></i>
                Liste des Campagnes (<?= number_format(count($campaigns)) ?> total)
            </h4>
            <div class="d-flex gap-2">
                <button class="enterprise-btn enterprise-btn-success" onclick="openAddCampaignModal()">
                    <i class="fas fa-plus"></i> Nouvelle Campagne
                </button>
            </div>
        </div>
    </div>
    <div class="enterprise-card-body">
    
    <?php if (empty($campaigns)): ?>
    <div class="text-center py-5">
        <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Aucune campagne trouvée</h5>
        <p class="text-muted">Aucune campagne ne correspond à vos critères de recherche.</p>
        <?php if (!isset($table_exists) || !$table_exists): ?>
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Note:</strong> Les tables de base de données pour les campagnes n'existent pas encore. 
            <a href="?debug=1" class="alert-link">Cliquez ici pour voir les informations de débogage</a>.
        </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Campagne</th>
                        <th>Annonceur</th>
                        <th>Performance</th>
                        <th>Budget</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            <tbody>
                <?php foreach ($campaigns as $campaign): ?>
                <tr>
                    <td>
                        <div>
                            <strong class="text-primary"><?= htmlspecialchars($campaign['name']) ?></strong>
                            <br><small class="text-muted">ID: <?= $campaign['id'] ?></small>
                            <br><small class="text-muted"><?= htmlspecialchars(substr($campaign['description'], 0, 100)) ?>...</small>
                        </div>
                    </td>
                    <td>
                        <div>
                            <strong><?= htmlspecialchars($campaign['advertiser_name'] ?: 'Non assigné') ?></strong>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <small class="text-muted">
                                <i class="fas fa-ad me-1"></i><?= $campaign['total_ads'] ?> publicités totales
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-check me-1"></i><?= $campaign['active_ads'] ?> publicités actives
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-eye me-1"></i><?= number_format($campaign['total_impressions']) ?> impressions
                            </small>
                            <small class="text-success fw-bold">
                                <i class="fas fa-dollar-sign me-1"></i><?= number_format($campaign['total_revenue'], 2) ?> MAD
                            </small>
                        </div>
                    </td>
                    <td>
                        <div>
                            <strong class="text-success"><?= number_format($campaign['budget'], 2) ?> MAD</strong>
                        </div>
                    </td>
                    <td>
                        <?php 
                        $status_class = '';
                        $status_text = '';
                        switch($campaign['status']) {
                            case 'active':
                                $status_class = 'bg-success';
                                $status_text = 'Actif';
                                break;
                            case 'pending':
                                $status_class = 'bg-warning';
                                $status_text = 'En Attente';
                                break;
                            case 'inactive':
                                $status_class = 'bg-danger';
                                $status_text = 'Inactif';
                                break;
                            default:
                                $status_class = 'bg-secondary';
                                $status_text = ucfirst($campaign['status']);
                        }
                        ?>
                        <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline me-1" onclick="openEditCampaignModal(<?= $campaign['id'] ?>)" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </button>
                            <?php if ($campaign['status'] === 'active'): ?>
                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-warning me-1" 
                                onclick="deactivateCampaign(<?= $campaign['id'] ?>, '<?= htmlspecialchars($campaign['name'], ENT_QUOTES) ?>')"
                                title="Désactiver">
                                <i class="fas fa-pause"></i>
                            </button>
                            <?php else: ?>
                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-success me-1" 
                                onclick="activateCampaign(<?= $campaign['id'] ?>, '<?= htmlspecialchars($campaign['name'], ENT_QUOTES) ?>')"
                                title="Activer">
                                <i class="fas fa-play"></i>
                            </button>
                            <?php endif; ?>
                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger" 
                                onclick="deleteCampaign(<?= $campaign['id'] ?>, '<?= htmlspecialchars($campaign['name'], ENT_QUOTES) ?>')"
                                title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Campaign Modal -->
<div class="modal fade" id="campaignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="campaignModalTitle">Ajouter une Campagne</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="campaignForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="campaignAction" value="add_campaign">
                    <input type="hidden" name="campaign_id" id="campaignId" value="">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="campaignName" class="form-label">Nom de la Campagne *</label>
                                <input type="text" class="form-control" id="campaignName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="campaignAdvertiser" class="form-label">Annonceur</label>
                                <select class="form-select" id="campaignAdvertiser" name="advertiser_id">
                                    <option value="">Sélectionner un annonceur</option>
                                    <?php foreach ($advertisers as $advertiser): ?>
                                    <option value="<?= $advertiser['id'] ?>"><?= htmlspecialchars($advertiser['company_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="campaignDescription" class="form-label">Description *</label>
                        <textarea class="form-control" id="campaignDescription" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="campaignBudget" class="form-label">Budget (MAD)</label>
                                <input type="number" class="form-control" id="campaignBudget" name="budget" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="campaignStartDate" class="form-label">Date de Début</label>
                                <input type="date" class="form-control" id="campaignStartDate" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="campaignEndDate" class="form-label">Date de Fin</label>
                                <input type="date" class="form-control" id="campaignEndDate" name="end_date">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="campaignTarget" class="form-label">Audience Cible</label>
                                <select class="form-select" id="campaignTarget" name="target_audience">
                                    <option value="all">Tous les utilisateurs</option>
                                    <option value="job_seekers">Chercheurs d'emploi</option>
                                    <option value="employers">Employeurs</option>
                                    <option value="premium_users">Utilisateurs premium</option>
                                    <option value="new_users">Nouveaux utilisateurs</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="campaignStatus" class="form-label">Statut</label>
                                <select class="form-select" id="campaignStatus" name="status">
                                    <option value="pending">En attente</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="enterprise-action-btn enterprise-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="enterprise-action-btn enterprise-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function activateCampaign(id, name) {
    if (confirm(`Activer la campagne "${name}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="activate">
            <input type="hidden" name="campaign_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function deactivateCampaign(id, name) {
    if (confirm(`Désactiver la campagne "${name}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="deactivate">
            <input type="hidden" name="campaign_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteCampaign(id, name) {
    if (confirm(`Supprimer définitivement la campagne "${name}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="campaign_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function refreshData() {
    location.reload();
}

function exportCampaignsData() {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('export', 'csv');
    window.location.href = currentUrl.toString();
}

function openAddCampaignModal() {
    document.getElementById('campaignModalTitle').textContent = 'Ajouter une Campagne';
    document.getElementById('campaignAction').value = 'add_campaign';
    document.getElementById('campaignId').value = '';
    document.getElementById('campaignForm').reset();
    document.getElementById('campaignStartDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('campaignEndDate').value = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    new bootstrap.Modal(document.getElementById('campaignModal')).show();
}

function openEditCampaignModal(campaignId) {
    // This would typically fetch campaign data via AJAX
    alert('Fonctionnalité de modification - à implémenter avec AJAX');
}
</script>

    </div> <!-- End main-content -->
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
</body>
</html>
