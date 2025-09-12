<?php
// Include core files first
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';
include __DIR__ . '/../include/connexion.php';

// Check if user is admin
if (!Security::isAdmin()) {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Enterprise Advertiser Management - EMPLOIDB';

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
            $_SESSION['advertiser_notification'] = ['type' => 'warning', 'message' => 'Annonceur suspendu avec succès'];
            header('Location: manage_advertisers.php');
            exit;
        }
        
        // Bulk operations
        if (in_array($action, ['bulk_approve', 'bulk_suspend', 'bulk_delete']) && isset($_POST['advertiser_ids'])) {
            $advertiser_ids = $_POST['advertiser_ids'];
            $count = 0;
            
            foreach ($advertiser_ids as $id) {
                if ($action === 'bulk_approve') {
                    $db->update("UPDATE advertisers SET status = 'approved', approved_at = NOW() WHERE id = ?", [$id]);
                } elseif ($action === 'bulk_suspend') {
                    $db->update("UPDATE advertisers SET status = 'suspended', suspended_at = NOW() WHERE id = ?", [$id]);
                } elseif ($action === 'bulk_delete') {
                    // Delete advertiser and related data
                    $db->delete("DELETE FROM ad_performance WHERE ad_id IN (SELECT id FROM advertisements WHERE advertiser_id = ?)", [$id]);
                    $db->delete("DELETE FROM ad_clicks WHERE ad_id IN (SELECT id FROM advertisements WHERE advertiser_id = ?)", [$id]);
                    $db->delete("DELETE FROM ad_impressions WHERE ad_id IN (SELECT id FROM advertisements WHERE advertiser_id = ?)", [$id]);
                    $db->delete("DELETE FROM advertisements WHERE advertiser_id = ?", [$id]);
                    $db->delete("DELETE FROM advertisers WHERE id = ?", [$id]);
                }
                $count++;
            }
            
            $messages = [
                'bulk_approve' => "$count annonceur(s) approuvé(s) avec succès",
                'bulk_suspend' => "$count annonceur(s) suspendu(s) avec succès",
                'bulk_delete' => "$count annonceur(s) supprimé(s) avec succès"
            ];
            
            $_SESSION['advertiser_notification'] = ['type' => 'success', 'message' => $messages[$action]];
            header('Location: manage_advertisers.php');
            exit;
        }

        // Add new advertiser
        if ($action === 'add_advertiser') {
            $company_name = trim($_POST['company_name'] ?? '');
            $contact_name = trim($_POST['contact_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $website = trim($_POST['website'] ?? '');
            $industry = $_POST['industry'] ?? '';
            $budget = floatval($_POST['budget'] ?? 0);
            $status = $_POST['status'] ?? 'pending';
            $address = trim($_POST['address'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $country = trim($_POST['country'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $payment_method = $_POST['payment_method'] ?? '';
            $billing_cycle = $_POST['billing_cycle'] ?? 'monthly';

            // Enhanced validation
            if (empty($company_name) || empty($contact_name) || empty($email)) {
                throw new Exception('Nom de l\'entreprise, contact et email sont obligatoires');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Format d\'email invalide');
            }
            
            if ($budget < 0) {
                throw new Exception('Le budget ne peut pas être négatif');
            }

            // Check for duplicate email
            $existing = $db->fetch("SELECT id FROM advertisers WHERE email = ?", [$email]);
            if ($existing) {
                throw new Exception('Un annonceur avec cet email existe déjà');
            }

            // Insert advertiser
            $advertiser_id = $db->insert("
                INSERT INTO advertisers (company_name, contact_name, email, phone, website, 
                industry, budget, status, address, city, country, description, payment_method, 
                billing_cycle, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ", [$company_name, $contact_name, $email, $phone, $website, $industry, $budget, 
                $status, $address, $city, $country, $description, $payment_method, $billing_cycle]);

            $_SESSION['advertiser_notification'] = ['type' => 'success', 'message' => 'Annonceur ajouté avec succès'];
            header('Location: manage_advertisers.php');
            exit;
        }

        // Edit advertiser
        if ($action === 'edit_advertiser') {
            $advertiser_id = $_POST['advertiser_id'] ?? 0;
            $company_name = trim($_POST['company_name'] ?? '');
            $contact_name = trim($_POST['contact_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $website = trim($_POST['website'] ?? '');
            $industry = $_POST['industry'] ?? '';
            $budget = floatval($_POST['budget'] ?? 0);
            $status = $_POST['status'] ?? 'pending';
            $address = trim($_POST['address'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $country = trim($_POST['country'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $payment_method = $_POST['payment_method'] ?? '';
            $billing_cycle = $_POST['billing_cycle'] ?? 'monthly';

            // Enhanced validation
            if (empty($company_name) || empty($contact_name) || empty($email)) {
                throw new Exception('Nom de l\'entreprise, contact et email sont obligatoires');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Format d\'email invalide');
            }
            
            if ($budget < 0) {
                throw new Exception('Le budget ne peut pas être négatif');
            }

            // Check for duplicate email (excluding current advertiser)
            $existing = $db->fetch("SELECT id FROM advertisers WHERE email = ? AND id != ?", [$email, $advertiser_id]);
            if ($existing) {
                throw new Exception('Un annonceur avec cet email existe déjà');
            }

            // Update advertiser
            $db->update("
                UPDATE advertisers SET company_name = ?, contact_name = ?, email = ?, phone = ?, 
                website = ?, industry = ?, budget = ?, status = ?, address = ?, city = ?, 
                country = ?, description = ?, payment_method = ?, billing_cycle = ?, updated_at = NOW()
                WHERE id = ?
            ", [$company_name, $contact_name, $email, $phone, $website, $industry, $budget, 
                $status, $address, $city, $country, $description, $payment_method, $billing_cycle, $advertiser_id]);

            $_SESSION['advertiser_notification'] = ['type' => 'success', 'message' => 'Annonceur mis à jour avec succès'];
            header('Location: manage_advertisers.php');
            exit;
        }
    } catch (Exception $e) {
        error_log("Database error in manage_advertisers.php: " . $e->getMessage());
        header('Location: manage_advertisers.php?error=database');
        exit;
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$industry_filter = $_GET['industry'] ?? '';
$search = $_GET['search'] ?? '';

// Build query conditions
$where_conditions = ['1=1'];
$params = [];

if ($status_filter) {
    $where_conditions[] = "a.status = ?";
    $params[] = $status_filter;
}

if ($industry_filter) {
    $where_conditions[] = "a.industry = ?";
    $params[] = $industry_filter;
}

if ($search) {
    $where_conditions[] = "(a.company_name LIKE ? OR a.contact_name LIKE ? OR a.email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
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

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="../frontoffice/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Enterprise CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Advanced Data Visualization -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Chart.js for Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom Enterprise Styles -->
    <style>
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
        
        .status-approved { background-color: #dcfce7; color: #166534; }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-suspended { background-color: #fee2e2; color: #991b1b; }
        
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
                            <i class="fas fa-users me-3"></i>
                            Gestion des Annonceurs
                        </h1>
                        <p class="mb-0 mt-2 opacity-75">Système de gestion des annonceurs d'entreprise</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-light btn-lg" onclick="openAddAdvertiserModal()">
                            <i class="fas fa-plus me-2"></i>
                            Nouvel Annonceur
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Messages -->
<?php if (isset($_SESSION['advertiser_notification'])): ?>
<div class="alert alert-<?= $_SESSION['advertiser_notification']['type'] === 'success' ? 'success' : ($_SESSION['advertiser_notification']['type'] === 'warning' ? 'warning' : 'danger') ?> alert-dismissible fade show" role="alert">
    <i class="fas fa-<?= $_SESSION['advertiser_notification']['type'] === 'success' ? 'check-circle' : ($_SESSION['advertiser_notification']['type'] === 'warning' ? 'exclamation-triangle' : 'times-circle') ?> me-2"></i>
    <?= htmlspecialchars($_SESSION['advertiser_notification']['message']) ?>
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

<!-- Quick Actions -->
<div class="enterprise-content-card mb-4">
    <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
        <i class="fas fa-bolt me-2"></i>Actions Rapides
    </h5>
    <div class="row">
        <div class="col-md-2">
            <button class="enterprise-action-btn enterprise-primary w-100 mb-2" onclick="openAddAdvertiserModal()">
                <i class="fas fa-plus me-2"></i>Ajouter Annonceur
            </button>
        </div>
        <div class="col-md-2">
            <button class="enterprise-action-btn enterprise-secondary w-100 mb-2" onclick="refreshData()">
                <i class="fas fa-sync-alt me-2"></i>Actualiser
            </button>
        </div>
        <div class="col-md-2">
            <button class="enterprise-action-btn enterprise-info w-100 mb-2" onclick="exportAdvertisersData()">
                <i class="fas fa-download me-2"></i>Exporter CSV
            </button>
        </div>
        <div class="col-md-2">
            <button class="enterprise-action-btn enterprise-warning w-100 mb-2" onclick="showAdvertiserAnalytics()">
                <i class="fas fa-chart-bar me-2"></i>Analytiques
            </button>
        </div>
        <div class="col-md-2">
            <button class="enterprise-action-btn enterprise-success w-100 mb-2" onclick="bulkApprove()">
                <i class="fas fa-check me-2"></i>Approuver Sélection
            </button>
        </div>
        <div class="col-md-2">
            <a href="manage_ads.php" class="enterprise-action-btn enterprise-info w-100 mb-2 text-decoration-none text-center d-block">
                <i class="fas fa-ad me-2"></i>Gérer Publicités
            </a>
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
    <!-- Bulk Actions -->
    <div class="row mt-3" id="bulkActions" style="display: none;">
        <div class="col-12">
            <div class="alert alert-info">
                <strong>Actions en lot :</strong>
                <button class="btn btn-sm btn-success ms-2" onclick="bulkApprove()">
                    <i class="fas fa-check me-1"></i>Approuver
                </button>
                <button class="btn btn-sm btn-warning ms-1" onclick="bulkSuspend()">
                    <i class="fas fa-pause me-1"></i>Suspendre
                </button>
                <button class="btn btn-sm btn-danger ms-1" onclick="bulkDelete()">
                    <i class="fas fa-trash me-1"></i>Supprimer
                </button>
                <span class="ms-3" id="selectedCount">0 sélectionné(s)</span>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="enterprise-content-card">
    <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
        <i class="fas fa-filter me-2"></i>Filtres et Recherche
    </h5>
    <form method="GET" class="row g-3">
        <div class="col-md-4">
            <div class="form-floating">
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher...">
                <label for="search">Rechercher</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-floating">
                <select class="form-select" id="status" name="status">
                    <option value="">Tous les statuts</option>
                    <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approuvés</option>
                    <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>En attente</option>
                    <option value="suspended" <?= $status_filter === 'suspended' ? 'selected' : '' ?>>Suspendus</option>
                </select>
                <label for="status">Statut</label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-floating">
                <select class="form-select" id="industry" name="industry">
                    <option value="">Tous secteurs</option>
                    <?php foreach ($industries as $industry): ?>
                    <option value="<?= $industry ?>" <?= $industry_filter === $industry ? 'selected' : '' ?>>
                        <?= $industry ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <label for="industry">Secteur</label>
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" class="enterprise-action-btn enterprise-primary w-100">
                <i class="fas fa-search me-2"></i>Filtrer
            </button>
        </div>
    </form>
</div>

<!-- Advertisers Table -->
<div class="enterprise-content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color: var(--enterprise-primary); margin: 0;">
            <i class="fas fa-list me-2"></i>Liste des Annonceurs
        </h5>
        <span class="badge bg-info"><?= number_format(count($advertisers)) ?> résultat(s)</span>
    </div>
    
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
            <thead style="background: var(--enterprise-gray-50);">
                <tr>
                    <th style="color: var(--enterprise-text-primary);">
                        <input type="checkbox" id="selectAllAdvertisers" onchange="toggleSelectAllAdvertisers()">
                    </th>
                    <th style="color: var(--enterprise-text-primary);">Entreprise</th>
                    <th style="color: var(--enterprise-text-primary);">Contact</th>
                    <th style="color: var(--enterprise-text-primary);">Performance</th>
                    <th style="color: var(--enterprise-text-primary);">Statut</th>
                    <th style="color: var(--enterprise-text-primary);">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($advertisers as $advertiser): ?>
                <tr>
                    <td>
                        <input type="checkbox" class="advertiser-checkbox" value="<?= $advertiser['id'] ?>" onchange="updateBulkButtons()">
                    </td>
                    <td>
                        <div>
                            <strong style="color: var(--enterprise-primary);">
                                <?= htmlspecialchars($advertiser['company_name']) ?>
                            </strong>
                            <br>
                            <small style="color: var(--enterprise-text-muted);">
                                <?= htmlspecialchars($advertiser['industry']) ?> • 
                                Budget: <?= number_format($advertiser['budget'], 2) ?> MAD
                            </small>
                        </div>
                    </td>
                    <td>
                        <div style="color: var(--enterprise-text-secondary);">
                            <div><?= htmlspecialchars($advertiser['contact_name']) ?></div>
                            <small><?= htmlspecialchars($advertiser['email']) ?></small>
                            <?php if ($advertiser['phone']): ?>
                            <br><small><?= htmlspecialchars($advertiser['phone']) ?></small>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <small style="color: var(--enterprise-text-secondary);">
                                <i class="fas fa-ad me-1"></i><?= $advertiser['total_ads'] ?> publicités
                            </small>
                            <small style="color: var(--enterprise-text-secondary);">
                                <i class="fas fa-eye me-1"></i><?= number_format($advertiser['total_impressions']) ?> impressions
                            </small>
                            <small style="color: var(--enterprise-text-secondary);">
                                <i class="fas fa-mouse-pointer me-1"></i><?= number_format($advertiser['total_clicks']) ?> clics
                            </small>
                            <small style="color: var(--enterprise-success); font-weight: 600;">
                                <i class="fas fa-dollar-sign me-1"></i><?= number_format($advertiser['total_revenue'], 2) ?> MAD
                            </small>
                        </div>
                    </td>
                    <td>
                        <?php if ($advertiser['status'] === 'approved'): ?>
                        <span class="enterprise-status-badge enterprise-success">
                            <i class="fas fa-check me-1"></i>Approuvé
                        </span>
                        <?php elseif ($advertiser['status'] === 'pending'): ?>
                        <span class="enterprise-status-badge enterprise-warning">
                            <i class="fas fa-clock me-1"></i>En attente
                        </span>
                        <?php else: ?>
                        <span class="enterprise-status-badge enterprise-danger">
                            <i class="fas fa-pause me-1"></i>Suspendu
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="enterprise-action-btn enterprise-info enterprise-sm me-1" 
                            onclick="openEditAdvertiserModal(<?= $advertiser['id'] ?>)"
                            title="Modifier l'annonceur">
                            <i class="fas fa-edit"></i>
                        </button>
                        <?php if ($advertiser['status'] === 'pending'): ?>
                        <button class="enterprise-action-btn enterprise-success enterprise-sm me-1" 
                            onclick="approveAdvertiser(<?= $advertiser['id'] ?>, '<?= htmlspecialchars($advertiser['company_name'], ENT_QUOTES) ?>')"
                            title="Approuver l'annonceur">
                            <i class="fas fa-check"></i>
                        </button>
                        <?php endif; ?>
                        <?php if ($advertiser['status'] !== 'suspended'): ?>
                        <button class="enterprise-action-btn enterprise-warning enterprise-sm me-1" 
                            onclick="suspendAdvertiser(<?= $advertiser['id'] ?>, '<?= htmlspecialchars($advertiser['company_name'], ENT_QUOTES) ?>')"
                            title="Suspendre l'annonceur">
                            <i class="fas fa-pause"></i>
                        </button>
                        <?php else: ?>
                        <button class="enterprise-action-btn enterprise-success enterprise-sm me-1" 
                            onclick="approveAdvertiser(<?= $advertiser['id'] ?>, '<?= htmlspecialchars($advertiser['company_name'], ENT_QUOTES) ?>')"
                            title="Réactiver l'annonceur">
                            <i class="fas fa-play"></i>
                        </button>
                        <?php endif; ?>
                        <button class="enterprise-action-btn enterprise-danger enterprise-sm" 
                            onclick="deleteAdvertiser(<?= $advertiser['id'] ?>, '<?= htmlspecialchars($advertiser['company_name'], ENT_QUOTES) ?>')"
                            title="Supprimer l'annonceur">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
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
                    <input type="hidden" name="action" id="advertiserAction" value="add_advertiser">
                    <input type="hidden" name="advertiser_id" id="advertiserId" value="">
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
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="website" class="form-label">Site Web</label>
                                <input type="url" class="form-control" id="website" name="website">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="industry" class="form-label">Secteur d'Activité</label>
                                <select class="form-select" id="industry" name="industry">
                                    <option value="">Sélectionner un secteur</option>
                                    <?php foreach ($industries as $industry): ?>
                                    <option value="<?= $industry ?>"><?= $industry ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="budget" class="form-label">Budget (MAD)</label>
                                <input type="number" class="form-control" id="budget" name="budget" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="advertiserStatus" class="form-label">Statut</label>
                                <select class="form-select" id="advertiserStatus" name="status">
                                    <option value="pending">En attente</option>
                                    <option value="approved">Approuvé</option>
                                    <option value="suspended">Suspendu</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="address" class="form-label">Adresse</label>
                                <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="city" class="form-label">Ville</label>
                                <input type="text" class="form-control" id="city" name="city">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="country" class="form-label">Pays</label>
                                <input type="text" class="form-control" id="country" name="country" value="Maroc">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description de l'Entreprise</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="paymentMethod" class="form-label">Méthode de Paiement</label>
                                <select class="form-select" id="paymentMethod" name="payment_method">
                                    <option value="">Sélectionner une méthode</option>
                                    <?php foreach ($payment_methods as $method): ?>
                                    <option value="<?= $method ?>"><?= $method ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="billingCycle" class="form-label">Cycle de Facturation</label>
                                <select class="form-select" id="billingCycle" name="billing_cycle">
                                    <?php foreach ($billing_cycles as $cycle): ?>
                                    <option value="<?= $cycle ?>"><?= ucfirst($cycle) ?></option>
                                    <?php endforeach; ?>
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
function approveAdvertiser(id, name) {
    if (confirm(`Approuver l'annonceur "${name}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="approve">
            <input type="hidden" name="advertiser_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function suspendAdvertiser(id, name) {
    if (confirm(`Suspendre l'annonceur "${name}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="suspend">
            <input type="hidden" name="advertiser_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteAdvertiser(id, name) {
    if (confirm(`Supprimer définitivement l'annonceur "${name}" ?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="advertiser_id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function refreshData() {
    location.reload();
}

function exportAdvertisersData() {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('export', 'csv');
    window.location.href = currentUrl.toString();
}

function openAddAdvertiserModal() {
    document.getElementById('advertiserModalTitle').textContent = 'Ajouter un Annonceur';
    document.getElementById('advertiserAction').value = 'add_advertiser';
    document.getElementById('advertiserId').value = '';
    document.getElementById('advertiserForm').reset();
    new bootstrap.Modal(document.getElementById('advertiserModal')).show();
}

function openEditAdvertiserModal(advertiserId) {
    // This would typically fetch advertiser data via AJAX
    alert('Fonctionnalité de modification - à implémenter avec AJAX');
}

// Enhanced Enterprise Functions
function toggleSelectAllAdvertisers() {
    const selectAll = document.getElementById('selectAllAdvertisers');
    const checkboxes = document.querySelectorAll('.advertiser-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    updateBulkButtons();
}

function updateBulkButtons() {
    const selected = document.querySelectorAll('.advertiser-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selected.length > 0) {
        bulkActions.style.display = 'block';
        selectedCount.textContent = `${selected.length} sélectionné(s)`;
    } else {
        bulkActions.style.display = 'none';
    }
}

function bulkApprove() {
    const selectedIds = getSelectedAdvertiserIds();
    if (selectedIds.length === 0) return;
    
    if (confirm(`Approuver ${selectedIds.length} annonceur(s) ?`)) {
        submitBulkAction('bulk_approve', selectedIds);
    }
}

function bulkSuspend() {
    const selectedIds = getSelectedAdvertiserIds();
    if (selectedIds.length === 0) return;
    
    if (confirm(`Suspendre ${selectedIds.length} annonceur(s) ?`)) {
        submitBulkAction('bulk_suspend', selectedIds);
    }
}

function bulkDelete() {
    const selectedIds = getSelectedAdvertiserIds();
    if (selectedIds.length === 0) return;
    
    if (confirm(`Supprimer définitivement ${selectedIds.length} annonceur(s) ?`)) {
        submitBulkAction('bulk_delete', selectedIds);
    }
}

function getSelectedAdvertiserIds() {
    const checkboxes = document.querySelectorAll('.advertiser-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function submitBulkAction(action, advertiserIds) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="${action}">
        <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
        ${advertiserIds.map(id => `<input type="hidden" name="advertiser_ids[]" value="${id}">`).join('')}
    `;
    document.body.appendChild(form);
    form.submit();
}

function showAdvertiserAnalytics() {
    // Show advertiser analytics modal
    alert('Analytiques des annonceurs - à implémenter');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh data every 30 seconds
    setInterval(refreshData, 30000);
});
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
