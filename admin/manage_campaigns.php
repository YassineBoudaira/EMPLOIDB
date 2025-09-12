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
            $_SESSION['campaign_notification'] = ['type' => 'success', 'message' => 'Campagne désactivée avec succès'];
            header('Location: manage_campaigns.php');
            exit;
        }
        
        // Handle bulk operations
        if (in_array($action, ['bulk_activate', 'bulk_deactivate', 'bulk_delete'])) {
            $campaign_ids = $_POST['campaign_ids'] ?? [];
            if (!empty($campaign_ids)) {
                $placeholders = str_repeat('?,', count($campaign_ids) - 1) . '?';
                
                switch ($action) {
                    case 'bulk_activate':
                        $db->update("UPDATE campaigns SET status = 'active', activated_at = NOW() WHERE id IN ($placeholders)", $campaign_ids);
                        $_SESSION['campaign_notification'] = ['type' => 'success', 'message' => count($campaign_ids) . ' campagnes activées avec succès'];
                        break;
                    case 'bulk_deactivate':
                        $db->update("UPDATE campaigns SET status = 'inactive', deactivated_at = NOW() WHERE id IN ($placeholders)", $campaign_ids);
                        $_SESSION['campaign_notification'] = ['type' => 'success', 'message' => count($campaign_ids) . ' campagnes désactivées avec succès'];
                        break;
                    case 'bulk_delete':
                        $db->delete("DELETE FROM campaign_ads WHERE campaign_id IN ($placeholders)", $campaign_ids);
                        $db->delete("DELETE FROM campaign_performance WHERE campaign_id IN ($placeholders)", $campaign_ids);
                        $db->delete("DELETE FROM campaigns WHERE id IN ($placeholders)", $campaign_ids);
                        $_SESSION['campaign_notification'] = ['type' => 'success', 'message' => count($campaign_ids) . ' campagnes supprimées avec succès'];
                        break;
                }
                header('Location: manage_campaigns.php');
                exit;
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['campaign_notification'] = ['type' => 'error', 'message' => 'Erreur: ' . $e->getMessage()];
        header('Location: manage_campaigns.php');
        exit;
    }
}

// Get filter parameters
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';
$advertiser_filter = $_GET['advertiser'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where_conditions = ['1=1'];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(c.name LIKE ? OR c.description LIKE ? OR a.company_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "c.status = ?";
    $params[] = $status_filter;
}

if (!empty($advertiser_filter)) {
    $where_conditions[] = "c.advertiser_id = ?";
    $params[] = $advertiser_filter;
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

<!-- Enterprise Campaign Management Content -->
<div class="fade-in">
    <!-- Notification Display -->
    <?php if (isset($_SESSION['campaign_notification'])): ?>
    <div class="alert alert-<?= $_SESSION['campaign_notification']['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4" role="alert">
        <strong><?= htmlspecialchars($_SESSION['campaign_notification']['title'] ?? 'Notification') ?>:</strong> <?= htmlspecialchars($_SESSION['campaign_notification']['message']) ?>
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
                                <i class="fas fa-mouse-pointer fa-2x text-danger"></i>
                            </div>
                            <div class="text-end">
                                <h3 class="stat-value text-danger mb-0"><?= number_format($campaignStats['total_clicks']) ?></h3>
                                <small class="text-muted">Clics</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-success">
                                <i class="fas fa-arrow-up"></i>
                                +8.5% ce mois
                            </span>
                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showClicks()">
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
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="campaign_id" id="campaignId">
                    <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="campaignName" class="form-label">Nom de la Campagne *</label>
                                <input type="text" class="form-control" id="campaignName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="campaignAdvertiser" class="form-label">Annonceur *</label>
                                <select class="form-select" id="campaignAdvertiser" name="advertiser_id" required>
                                    <option value="">Sélectionner un annonceur</option>
                                    <?php foreach ($advertisers as $advertiser): ?>
                                    <option value="<?= $advertiser['id'] ?>"><?= htmlspecialchars($advertiser['company_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="campaignDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="campaignDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="campaignBudget" class="form-label">Budget Total (MAD) *</label>
                                <input type="number" class="form-control" id="campaignBudget" name="budget" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="campaignDailyBudget" class="form-label">Budget Quotidien (MAD)</label>
                                <input type="number" class="form-control" id="campaignDailyBudget" name="daily_budget" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="campaignStatus" class="form-label">Statut</label>
                                <select class="form-select" id="campaignStatus" name="status">
                                    <option value="pending">En Attente</option>
                                    <option value="active">Actif</option>
                                    <option value="inactive">Inactif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="campaignStartDate" class="form-label">Date de Début</label>
                                <input type="date" class="form-control" id="campaignStartDate" name="start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="campaignEndDate" class="form-label">Date de Fin</label>
                                <input type="date" class="form-control" id="campaignEndDate" name="end_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Sauvegarder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Campaign Management Functions
function refreshData() {
    location.reload();
}

function exportCampaignsData() {
    // Implementation for CSV export
    Swal.fire('Info', 'Fonctionnalité d\'export en cours de développement', 'info');
}

function generateCampaignReport() {
    // Implementation for PDF report generation
    Swal.fire('Info', 'Génération de rapport en cours de développement', 'info');
}

function showCampaignAnalytics() {
    // Implementation for campaign analytics
    Swal.fire('Info', 'Analyses de campagne en cours de développement', 'info');
}

function openAddCampaignModal() {
    document.getElementById('campaignModalTitle').textContent = 'Ajouter une Campagne';
    document.querySelector('input[name="action"]').value = 'add';
    document.getElementById('campaignId').value = '';
    document.getElementById('campaignForm').reset();
    new bootstrap.Modal(document.getElementById('campaignModal')).show();
}

function openEditCampaignModal(campaignId) {
    // Implementation for editing campaign
    Swal.fire('Info', 'Modification de campagne en cours de développement', 'info');
}

function activateCampaign(campaignId, campaignName) {
    Swal.fire({
        title: 'Activer la Campagne',
        text: `Êtes-vous sûr de vouloir activer la campagne "${campaignName}" ?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Oui, activer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="activate">
                <input type="hidden" name="campaign_id" value="${campaignId}">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function deactivateCampaign(campaignId, campaignName) {
    Swal.fire({
        title: 'Désactiver la Campagne',
        text: `Êtes-vous sûr de vouloir désactiver la campagne "${campaignName}" ?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, désactiver',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="deactivate">
                <input type="hidden" name="campaign_id" value="${campaignId}">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function deleteCampaign(campaignId, campaignName) {
    Swal.fire({
        title: 'Supprimer la Campagne',
        text: `Êtes-vous sûr de vouloir supprimer définitivement la campagne "${campaignName}" ?`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="campaign_id" value="${campaignId}">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Statistics functions
function showCampaignDetails() {
    Swal.fire('Info', 'Détails des campagnes en cours de développement', 'info');
}

function showActiveCampaigns() {
    Swal.fire('Info', 'Campagnes actives en cours de développement', 'info');
}

function showPendingCampaigns() {
    Swal.fire('Info', 'Campagnes en attente en cours de développement', 'info');
}

function showImpressions() {
    Swal.fire('Info', 'Détails des impressions en cours de développement', 'info');
}

function showBudgetStats() {
    Swal.fire('Info', 'Statistiques de budget en cours de développement', 'info');
}

function showRevenueStats() {
    Swal.fire('Info', 'Statistiques de revenus en cours de développement', 'info');
}

// Initialize DataTable if campaigns exist
<?php if (!empty($campaigns)): ?>
$(document).ready(function() {
    $('.table').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
        },
        pageLength: 10,
        order: [[0, 'desc']]
    });
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
