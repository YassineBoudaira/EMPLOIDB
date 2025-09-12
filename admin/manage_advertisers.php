<?php
$page_title = 'Enterprise Advertiser Management - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// RBAC Check - Temporarily disabled for debugging
// if (!$rbac->hasPageAccess('manage_advertisers')) {
//     header('Location: dashboard.php?error=access_denied');
//     exit;
// }

// Handle advertiser actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $advertiser_id = $_POST['advertiser_id'] ?? 0;
    
    // CSRF Protection
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        header('Location: manage_advertisers.php?error=csrf');
        exit;
    }
    
    try {
        if ($action === 'delete' && $advertiser_id) {
            // Delete advertiser and related data
            $db->delete("DELETE FROM ad_performance WHERE ad_id IN (SELECT id FROM advertisements WHERE advertiser_id = ?)", [$advertiser_id]);
            $db->delete("DELETE FROM ad_clicks WHERE ad_id IN (SELECT id FROM advertisements WHERE advertiser_id = ?)", [$advertiser_id]);
            $db->delete("DELETE FROM ad_impressions WHERE ad_id IN (SELECT id FROM advertisements WHERE advertiser_id = ?)", [$advertiser_id]);
            $db->delete("DELETE FROM advertisements WHERE advertiser_id = ?", [$advertiser_id]);
            $db->delete("DELETE FROM advertisers WHERE id = ?", [$advertiser_id]);
            
            $_SESSION['advertiser_notification'] = ['type' => 'success', 'message' => 'Annonceur supprimé avec succès'];
            header('Location: manage_advertisers.php');
            exit;
        }
        
        if ($action === 'approve' && $advertiser_id) {
            $db->update("UPDATE advertisers SET status = 'approved', approved_at = NOW() WHERE id = ?", [$advertiser_id]);
            $_SESSION['advertiser_notification'] = ['type' => 'success', 'message' => 'Annonceur approuvé avec succès'];
            header('Location: manage_advertisers.php');
            exit;
        }
        
        if ($action === 'suspend' && $advertiser_id) {
            $db->update("UPDATE advertisers SET status = 'suspended', suspended_at = NOW() WHERE id = ?", [$advertiser_id]);
            $_SESSION['advertiser_notification'] = ['type' => 'success', 'message' => 'Annonceur suspendu avec succès'];
            header('Location: manage_advertisers.php');
            exit;
        }
        
        // Handle bulk operations
        if (in_array($action, ['bulk_approve', 'bulk_suspend', 'bulk_delete'])) {
            $advertiser_ids = $_POST['advertiser_ids'] ?? [];
            if (!empty($advertiser_ids)) {
                $placeholders = str_repeat('?,', count($advertiser_ids) - 1) . '?';
                
                switch ($action) {
                    case 'bulk_approve':
                        $db->update("UPDATE advertisers SET status = 'approved', approved_at = NOW() WHERE id IN ($placeholders)", $advertiser_ids);
                        $_SESSION['advertiser_notification'] = ['type' => 'success', 'message' => count($advertiser_ids) . ' annonceurs approuvés avec succès'];
                        break;
                    case 'bulk_suspend':
                        $db->update("UPDATE advertisers SET status = 'suspended', suspended_at = NOW() WHERE id IN ($placeholders)", $advertiser_ids);
                        $_SESSION['advertiser_notification'] = ['type' => 'success', 'message' => count($advertiser_ids) . ' annonceurs suspendus avec succès'];
                        break;
                    case 'bulk_delete':
                        foreach ($advertiser_ids as $id) {
                            $db->delete("DELETE FROM ad_performance WHERE ad_id IN (SELECT id FROM advertisements WHERE advertiser_id = ?)", [$id]);
                            $db->delete("DELETE FROM ad_clicks WHERE ad_id IN (SELECT id FROM advertisements WHERE advertiser_id = ?)", [$id]);
                            $db->delete("DELETE FROM ad_impressions WHERE ad_id IN (SELECT id FROM advertisements WHERE advertiser_id = ?)", [$id]);
                            $db->delete("DELETE FROM advertisements WHERE advertiser_id = ?", [$id]);
                        }
                        $db->delete("DELETE FROM advertisers WHERE id IN ($placeholders)", $advertiser_ids);
                        $_SESSION['advertiser_notification'] = ['type' => 'success', 'message' => count($advertiser_ids) . ' annonceurs supprimés avec succès'];
                        break;
                }
                header('Location: manage_advertisers.php');
                exit;
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['advertiser_notification'] = ['type' => 'error', 'message' => 'Erreur: ' . $e->getMessage()];
        header('Location: manage_advertisers.php');
        exit;
    }
}

// Get filter parameters
$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';
$industry_filter = $_GET['industry'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where_conditions = ['1=1'];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(a.company_name LIKE ? OR a.contact_name LIKE ? OR a.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "a.status = ?";
    $params[] = $status_filter;
}

if (!empty($industry_filter)) {
    $where_conditions[] = "a.industry = ?";
    $params[] = $industry_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Get advertisers with enhanced data
try {
    // Check if advertisers table exists
    $table_exists = $db->fetch("SHOW TABLES LIKE 'advertisers'");
    
    if ($table_exists) {
        $advertisers_query = "SELECT a.*, 
                              (SELECT COUNT(*) FROM advertisements WHERE advertiser_id = a.id) as total_ads,
                              (SELECT COUNT(*) FROM advertisements WHERE advertiser_id = a.id AND status = 'active') as active_ads,
                              (SELECT COALESCE(SUM(ap.impressions), 0) FROM ad_performance ap 
                               JOIN advertisements ads ON ap.ad_id = ads.id WHERE ads.advertiser_id = a.id) as total_impressions,
                              (SELECT COALESCE(SUM(ap.clicks), 0) FROM ad_performance ap 
                               JOIN advertisements ads ON ap.ad_id = ads.id WHERE ads.advertiser_id = a.id) as total_clicks,
                              (SELECT COALESCE(SUM(ap.revenue), 0) FROM ad_performance ap 
                               JOIN advertisements ads ON ap.ad_id = ads.id WHERE ads.advertiser_id = a.id) as total_revenue
                              FROM advertisers a 
                              WHERE $where_clause
                              ORDER BY a.created_at DESC";
        $advertisers = $db->fetchAll($advertisers_query, $params) ?? [];

        // Get enhanced statistics
        $advertiserStats = [
            'total_advertisers' => $db->fetch("SELECT COUNT(*) as count FROM advertisers")['count'] ?? 0,
            'approved_advertisers' => $db->fetch("SELECT COUNT(*) as count FROM advertisers WHERE status = 'approved'")['count'] ?? 0,
            'pending_advertisers' => $db->fetch("SELECT COUNT(*) as count FROM advertisers WHERE status = 'pending'")['count'] ?? 0,
            'suspended_advertisers' => $db->fetch("SELECT COUNT(*) as count FROM advertisers WHERE status = 'suspended'")['count'] ?? 0,
            'new_advertisers_today' => $db->fetch("SELECT COUNT(*) as count FROM advertisers WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
            'new_advertisers_week' => $db->fetch("SELECT COUNT(*) as count FROM advertisers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0,
            'total_budget' => $db->fetch("SELECT COALESCE(SUM(budget), 0) as total FROM advertisers WHERE status = 'approved'")['total'] ?? 0,
            'avg_budget' => $db->fetch("SELECT COALESCE(AVG(budget), 0) as avg FROM advertisers WHERE status = 'approved'")['avg'] ?? 0,
            'total_revenue' => $db->fetch("SELECT COALESCE(SUM(ap.revenue), 0) as total FROM ad_performance ap 
                                          JOIN advertisements ads ON ap.ad_id = ads.id 
                                          JOIN advertisers a ON ads.advertiser_id = a.id")['total'] ?? 0
        ];
    } else {
        // Tables don't exist yet, use sample data
        $advertisers = [];
        $advertiserStats = [
            'total_advertisers' => 0,
            'approved_advertisers' => 0,
            'pending_advertisers' => 0,
            'suspended_advertisers' => 0,
            'new_advertisers_today' => 0,
            'new_advertisers_week' => 0,
            'total_budget' => 0,
            'avg_budget' => 0,
            'total_revenue' => 0
        ];
    }

    // Get filter options
    $industries = ['Technology', 'Finance', 'Healthcare', 'Education', 'Retail', 'Manufacturing', 'Services', 'Other'];
    $payment_methods = ['Credit Card', 'Bank Transfer', 'PayPal', 'Stripe', 'Other'];
    $billing_cycles = ['monthly', 'quarterly', 'yearly', 'one-time'];

} catch (Exception $e) {
    $advertisers = [];
    $advertiserStats = [
        'total_advertisers' => 0,
        'approved_advertisers' => 0,
        'pending_advertisers' => 0,
        'suspended_advertisers' => 0,
        'new_advertisers_today' => 0,
        'new_advertisers_week' => 0,
        'total_budget' => 0,
        'avg_budget' => 0,
        'total_revenue' => 0
    ];
    $industries = [];
    $payment_methods = [];
    $billing_cycles = [];
    error_log("Database error in manage_advertisers.php: " . $e->getMessage());
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
    <p><strong>Advertisers Count:</strong> <?= count($advertisers ?? []) ?></p>
</div>
<?php endif; ?>

<!-- Enterprise Advertiser Management Content -->
<div class="fade-in">
    <!-- Notification Display -->
    <?php if (isset($_SESSION['advertiser_notification'])): ?>
    <div class="alert alert-<?= $_SESSION['advertiser_notification']['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4" role="alert">
        <strong><?= htmlspecialchars($_SESSION['advertiser_notification']['title'] ?? 'Notification') ?>:</strong> <?= htmlspecialchars($_SESSION['advertiser_notification']['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['advertiser_notification']); ?>
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

        <!-- Advertiser Overview Statistics -->
        <div class="row mb-4">
            <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
                <div class="enterprise-card h-100">
                    <div class="enterprise-card-body text-center">
                        <div class="d-flex align-items-center justify-content-center mb-3">
                            <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fas fa-building fa-2x text-primary"></i>
                            </div>
                            <div class="text-end">
                                <h3 class="stat-value text-primary mb-0"><?= number_format($advertiserStats['total_advertisers']) ?></h3>
                                <small class="text-muted">Total Annonceurs</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-success">
                                <i class="fas fa-arrow-up"></i>
                                +<?= $advertiserStats['new_advertisers_week'] ?> cette semaine
                            </span>
                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showAdvertiserDetails()">
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
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                            <div class="text-end">
                                <h3 class="stat-value text-success mb-0"><?= number_format($advertiserStats['approved_advertisers']) ?></h3>
                                <small class="text-muted">Approuvés</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-success">
                                <i class="fas fa-arrow-up"></i>
                                +<?= $advertiserStats['new_advertisers_today'] ?> aujourd'hui
                            </span>
                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showApprovedAdvertisers()">
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
                                <h3 class="stat-value text-warning mb-0"><?= number_format($advertiserStats['pending_advertisers']) ?></h3>
                                <small class="text-muted">En Attente</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-warning">
                                <i class="fas fa-arrow-up"></i>
                                +12.5% ce mois
                            </span>
                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showPendingAdvertisers()">
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
                                <h3 class="stat-value text-info mb-0"><?= number_format($advertiserStats['total_impressions'] ?? 0) ?></h3>
                                <small class="text-muted">Impressions</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-success">
                                <i class="fas fa-arrow-up"></i>
                                +28.7% ce mois
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
                                <h3 class="stat-value text-danger mb-0"><?= number_format($advertiserStats['total_budget'], 0) ?> MAD</h3>
                                <small class="text-muted">Budget Total</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-success">
                                <i class="fas fa-arrow-up"></i>
                                +6.8% ce mois
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
                                <h3 class="stat-value text-purple mb-0"><?= number_format($advertiserStats['total_revenue'], 0) ?> MAD</h3>
                                <small class="text-muted">Revenus</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-success">
                                <i class="fas fa-arrow-up"></i>
                                +22.1% ce mois
                            </span>
                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showRevenueStats()">
                                Détails
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- Advertiser Management Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-building me-3"></i>
                        Enterprise Advertiser Management
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Gestion complète des annonceurs avec analyses avancées et contrôles
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="exportAdvertisersData()">
                        <i class="fas fa-download"></i>
                        Exporter CSV
                    </button>
                    <button class="enterprise-btn enterprise-btn-accent" onclick="generateAdvertiserReport()">
                        <i class="fas fa-file-pdf"></i>
                        Rapport PDF
                    </button>
                    <button class="enterprise-btn enterprise-btn-info" onclick="showAdvertiserAnalytics()">
                        <i class="fas fa-chart-line"></i>
                        Analyses
                    </button>
                    <button class="enterprise-btn enterprise-btn-success" onclick="openAddAdvertiserModal()">
                        <i class="fas fa-plus"></i>
                        Nouvel Annonceur
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
                    <input type="text" class="form-control" name="search" placeholder="Nom, contact, email..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select class="form-select" name="status">
                        <option value="">Tous les Statuts</option>
                        <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approuvé</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>En Attente</option>
                        <option value="suspended" <?= $status_filter === 'suspended' ? 'selected' : '' ?>>Suspendu</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Industrie</label>
                    <select class="form-select" name="industry">
                        <option value="">Toutes les Industries</option>
                        <?php foreach ($industries as $industry): ?>
                        <option value="<?= $industry ?>" <?= $industry_filter === $industry ? 'selected' : '' ?>>
                            <?= htmlspecialchars($industry) ?>
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
                        <a href="manage_advertisers.php" class="enterprise-btn enterprise-btn-outline">
                            <i class="fas fa-times"></i> Effacer
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Advertisers Table -->
    <div class="enterprise-card">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="enterprise-card-title">
                    <i class="fas fa-table me-2"></i>
                    Liste des Annonceurs (<?= number_format(count($advertisers)) ?> total)
                </h4>
                <div class="d-flex gap-2">
                    <button class="enterprise-btn enterprise-btn-success" onclick="openAddAdvertiserModal()">
                        <i class="fas fa-plus"></i> Nouvel Annonceur
                    </button>
                </div>
            </div>
        </div>
        <div class="enterprise-card-body">
            
            <?php if (empty($advertisers)): ?>
            <div class="text-center py-5">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Aucun annonceur trouvé</h5>
                <p class="text-muted">Aucun annonceur ne correspond à vos critères de recherche.</p>
                <?php if (!isset($table_exists) || !$table_exists): ?>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> Les tables de base de données pour les annonceurs n'existent pas encore. 
                    <a href="?debug=1" class="alert-link">Cliquez ici pour voir les informations de débogage</a>.
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Annonceur</th>
                            <th>Contact</th>
                            <th>Performance</th>
                            <th>Budget</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                <tbody>
                    <?php foreach ($advertisers as $advertiser): ?>
                    <tr>
                        <td>
                            <div>
                                <strong class="text-primary"><?= htmlspecialchars($advertiser['company_name']) ?></strong>
                                <br><small class="text-muted">ID: <?= $advertiser['id'] ?></small>
                                <br><small class="text-muted"><?= htmlspecialchars($advertiser['industry'] ?? 'Non spécifié') ?></small>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong><?= htmlspecialchars($advertiser['contact_name']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($advertiser['email']) ?></small>
                                <br><small class="text-muted"><?= htmlspecialchars($advertiser['phone'] ?? 'Non fourni') ?></small>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <small class="text-muted">
                                    <i class="fas fa-ad me-1"></i><?= $advertiser['total_ads'] ?> publicités totales
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-check me-1"></i><?= $advertiser['active_ads'] ?> publicités actives
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-eye me-1"></i><?= number_format($advertiser['total_impressions']) ?> impressions
                                </small>
                                <small class="text-success fw-bold">
                                    <i class="fas fa-dollar-sign me-1"></i><?= number_format($advertiser['total_revenue'], 2) ?> MAD
                                </small>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong class="text-success"><?= number_format($advertiser['budget'], 2) ?> MAD</strong>
                            </div>
                        </td>
                        <td>
                            <?php 
                            $status_class = '';
                            $status_text = '';
                            switch($advertiser['status']) {
                                case 'approved':
                                    $status_class = 'bg-success';
                                    $status_text = 'Approuvé';
                                    break;
                                case 'pending':
                                    $status_class = 'bg-warning';
                                    $status_text = 'En Attente';
                                    break;
                                case 'suspended':
                                    $status_class = 'bg-danger';
                                    $status_text = 'Suspendu';
                                    break;
                                default:
                                    $status_class = 'bg-secondary';
                                    $status_text = ucfirst($advertiser['status']);
                            }
                            ?>
                            <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline me-1" onclick="openEditAdvertiserModal(<?= $advertiser['id'] ?>)" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if ($advertiser['status'] === 'pending'): ?>
                                <button class="enterprise-btn enterprise-btn-sm enterprise-btn-success me-1" 
                                    onclick="approveAdvertiser(<?= $advertiser['id'] ?>, '<?= htmlspecialchars($advertiser['company_name'], ENT_QUOTES) ?>')"
                                    title="Approuver">
                                    <i class="fas fa-check"></i>
                                </button>
                                <?php elseif ($advertiser['status'] === 'approved'): ?>
                                <button class="enterprise-btn enterprise-btn-sm enterprise-btn-warning me-1" 
                                    onclick="suspendAdvertiser(<?= $advertiser['id'] ?>, '<?= htmlspecialchars($advertiser['company_name'], ENT_QUOTES) ?>')"
                                    title="Suspendre">
                                    <i class="fas fa-pause"></i>
                                </button>
                                <?php else: ?>
                                <button class="enterprise-btn enterprise-btn-sm enterprise-btn-success me-1" 
                                    onclick="approveAdvertiser(<?= $advertiser['id'] ?>, '<?= htmlspecialchars($advertiser['company_name'], ENT_QUOTES) ?>')"
                                    title="Approuver">
                                    <i class="fas fa-check"></i>
                                </button>
                                <?php endif; ?>
                                <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger" 
                                    onclick="deleteAdvertiser(<?= $advertiser['id'] ?>, '<?= htmlspecialchars($advertiser['company_name'], ENT_QUOTES) ?>')"
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

<!-- Add/Edit Advertiser Modal -->
<div class="modal fade" id="advertiserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="advertiserModalTitle">Ajouter un Annonceur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="advertiserForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="advertiser_id" id="advertiserId">
                    <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="companyName" class="form-label">Nom de l'Entreprise *</label>
                                <input type="text" class="form-control" id="companyName" name="company_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contactName" class="form-label">Nom du Contact *</label>
                                <input type="text" class="form-control" id="contactName" name="contact_name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="advertiserEmail" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="advertiserEmail" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="advertiserPhone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="advertiserPhone" name="phone">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="advertiserIndustry" class="form-label">Industrie</label>
                                <select class="form-select" id="advertiserIndustry" name="industry">
                                    <option value="">Sélectionner une industrie</option>
                                    <?php foreach ($industries as $industry): ?>
                                    <option value="<?= $industry ?>"><?= htmlspecialchars($industry) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="advertiserBudget" class="form-label">Budget (MAD) *</label>
                                <input type="number" class="form-control" id="advertiserBudget" name="budget" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="advertiserDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="advertiserDescription" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="advertiserAddress" class="form-label">Adresse</label>
                                <textarea class="form-control" id="advertiserAddress" name="address" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="advertiserStatus" class="form-label">Statut</label>
                                <select class="form-select" id="advertiserStatus" name="status">
                                    <option value="pending">En Attente</option>
                                    <option value="approved">Approuvé</option>
                                    <option value="suspended">Suspendu</option>
                                </select>
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
// Advertiser Management Functions
function refreshData() {
    location.reload();
}

function exportAdvertisersData() {
    // Implementation for CSV export
    Swal.fire('Info', 'Fonctionnalité d\'export en cours de développement', 'info');
}

function generateAdvertiserReport() {
    // Implementation for PDF report generation
    Swal.fire('Info', 'Génération de rapport en cours de développement', 'info');
}

function showAdvertiserAnalytics() {
    // Implementation for advertiser analytics
    Swal.fire('Info', 'Analyses d\'annonceur en cours de développement', 'info');
}

function openAddAdvertiserModal() {
    document.getElementById('advertiserModalTitle').textContent = 'Ajouter un Annonceur';
    document.querySelector('input[name="action"]').value = 'add';
    document.getElementById('advertiserId').value = '';
    document.getElementById('advertiserForm').reset();
    new bootstrap.Modal(document.getElementById('advertiserModal')).show();
}

function openEditAdvertiserModal(advertiserId) {
    // Implementation for editing advertiser
    Swal.fire('Info', 'Modification d\'annonceur en cours de développement', 'info');
}

function approveAdvertiser(advertiserId, advertiserName) {
    Swal.fire({
        title: 'Approuver l\'Annonceur',
        text: `Êtes-vous sûr de vouloir approuver l'annonceur "${advertiserName}" ?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Oui, approuver',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="advertiser_id" value="${advertiserId}">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function suspendAdvertiser(advertiserId, advertiserName) {
    Swal.fire({
        title: 'Suspendre l\'Annonceur',
        text: `Êtes-vous sûr de vouloir suspendre l'annonceur "${advertiserName}" ?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, suspendre',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="suspend">
                <input type="hidden" name="advertiser_id" value="${advertiserId}">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function deleteAdvertiser(advertiserId, advertiserName) {
    Swal.fire({
        title: 'Supprimer l\'Annonceur',
        text: `Êtes-vous sûr de vouloir supprimer définitivement l'annonceur "${advertiserName}" ?`,
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
                <input type="hidden" name="advertiser_id" value="${advertiserId}">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Statistics functions
function showAdvertiserDetails() {
    Swal.fire('Info', 'Détails des annonceurs en cours de développement', 'info');
}

function showApprovedAdvertisers() {
    Swal.fire('Info', 'Annonceurs approuvés en cours de développement', 'info');
}

function showPendingAdvertisers() {
    Swal.fire('Info', 'Annonceurs en attente en cours de développement', 'info');
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

// Initialize DataTable if advertisers exist
<?php if (!empty($advertisers)): ?>
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
