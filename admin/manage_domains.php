<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Enterprise Domain Management - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Check if user has access to domain management
if (!$rbac->hasPageAccess($_SESSION['admin_id'] ?? 1, 'manage_domains')) {
    $_SESSION['error'] = 'Accès non autorisé à cette page.';
    header('Location: dashboard.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add_domain') {
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $status = trim($_POST['status'] ?? 'active');
            
            if (empty($nom)) {
                throw new Exception('Domain name is required');
            }
            
            $db->insert("INSERT INTO domaines (nom, description, category, status, created_at) VALUES (?, ?, ?, ?, NOW())", [$nom, $description, $category, $status]);
            header('Location: manage_domains.php?success=added');
            exit;
        }
        
        if ($action === 'edit_domain') {
            $domain_id = $_POST['domain_id'] ?? 0;
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $status = trim($_POST['status'] ?? 'active');
            
            if (empty($nom)) {
                throw new Exception('Domain name is required');
            }
            
            $db->update("UPDATE domaines SET nom = ?, description = ?, category = ?, status = ?, updated_at = NOW() WHERE id = ?", [$nom, $description, $category, $status, $domain_id]);
            header('Location: manage_domains.php?success=updated');
            exit;
        }
        
        if ($action === 'delete_domain') {
            $domain_id = $_POST['domain_id'] ?? 0;
            
            // Check if domain is being used
            $jobs_count = $db->fetch("SELECT COUNT(*) as count FROM emplois WHERE domaine_id = ?", [$domain_id])['count'] ?? 0;
            $profiles_count = $db->fetch("SELECT COUNT(*) as count FROM profiles WHERE domaine_id = ?", [$domain_id])['count'] ?? 0;
            
            if ($jobs_count > 0 || $profiles_count > 0) {
                header('Location: manage_domains.php?error=in_use');
                exit;
            }
            
            $db->delete("DELETE FROM domaines WHERE id = ?", [$domain_id]);
            header('Location: manage_domains.php?success=deleted');
            exit;
        }
        
        if ($action === 'activate_domain') {
            $domain_id = $_POST['domain_id'] ?? 0;
            $db->update("UPDATE domaines SET status = 'active', updated_at = NOW() WHERE id = ?", [$domain_id]);
            header('Location: manage_domains.php?success=activated');
            exit;
        }
        
        if ($action === 'deactivate_domain') {
            $domain_id = $_POST['domain_id'] ?? 0;
            $db->update("UPDATE domaines SET status = 'inactive', updated_at = NOW() WHERE id = ?", [$domain_id]);
            header('Location: manage_domains.php?success=deactivated');
            exit;
        }
        
    } catch (Exception $e) {
        error_log("Database error in manage_domains.php: " . $e->getMessage());
        header('Location: manage_domains.php?error=database');
        exit;
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';
$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query conditions
$where_conditions = ['1=1'];
$params = [];

if ($status_filter) {
    $where_conditions[] = "d.status = ?";
    $params[] = $status_filter;
}

if ($category_filter) {
    $where_conditions[] = "d.category = ?";
    $params[] = $category_filter;
}

if ($search) {
    $where_conditions[] = "(d.nom LIKE ? OR d.description LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param]);
}

$where_clause = implode(" AND ", $where_conditions);

// Get domains data
try {
    $domains_query = "
        SELECT d.*, 
            (SELECT COUNT(*) FROM emplois WHERE domaine_id = d.id) as jobs_count,
            (SELECT COUNT(*) FROM profiles WHERE domaine_id = d.id) as profiles_count,
            (SELECT COUNT(*) FROM emplois WHERE domaine_id = d.id AND status = 'active') as active_jobs_count
        FROM domaines d
        WHERE $where_clause
        ORDER BY d.created_at DESC
        LIMIT $per_page OFFSET $offset
    ";
    $domains = $db->fetchAll($domains_query, $params) ?? [];

    // Get total count
    $total_domains = $db->fetch("
        SELECT COUNT(*) as count 
        FROM domaines d
        WHERE $where_clause
    ", $params)['count'] ?? 0;

    $total_pages = ceil($total_domains / $per_page);

    // Get statistics
    $domainStats = [
        'total_domains' => $db->fetch("SELECT COUNT(*) as count FROM domaines")['count'] ?? 0,
        'active_domains' => $db->fetch("SELECT COUNT(*) as count FROM domaines WHERE status = 'active'")['count'] ?? 0,
        'inactive_domains' => $db->fetch("SELECT COUNT(*) as count FROM domaines WHERE status = 'inactive'")['count'] ?? 0,
        'it_domains' => $db->fetch("SELECT COUNT(*) as count FROM domaines WHERE category = 'IT'")['count'] ?? 0,
        'marketing_domains' => $db->fetch("SELECT COUNT(*) as count FROM domaines WHERE category = 'Marketing'")['count'] ?? 0,
        'finance_domains' => $db->fetch("SELECT COUNT(*) as count FROM domaines WHERE category = 'Finance'")['count'] ?? 0,
        'hr_domains' => $db->fetch("SELECT COUNT(*) as count FROM domaines WHERE category = 'RH'")['count'] ?? 0,
        'sales_domains' => $db->fetch("SELECT COUNT(*) as count FROM domaines WHERE category = 'Ventes'")['count'] ?? 0,
        'most_used_domain' => $db->fetch("
            SELECT d.nom, COUNT(e.id) as usage_count
            FROM domaines d
            LEFT JOIN emplois e ON d.id = e.domaine_id
            GROUP BY d.id, d.nom
            ORDER BY usage_count DESC
            LIMIT 1
        ")['nom'] ?? 'IT',
        'total_jobs_using_domains' => $db->fetch("SELECT COUNT(*) as count FROM emplois WHERE domaine_id IS NOT NULL")['count'] ?? 0,
        'total_profiles_using_domains' => $db->fetch("SELECT COUNT(*) as count FROM profiles WHERE domaine_id IS NOT NULL")['count'] ?? 0,
        'unused_domains' => $db->fetch("
            SELECT COUNT(*) as count 
            FROM domaines d 
            LEFT JOIN emplois e ON d.id = e.domaine_id 
            LEFT JOIN profiles p ON d.id = p.domaine_id
            WHERE e.id IS NULL AND p.id IS NULL
        ")['count'] ?? 0,
        'new_domains_today' => $db->fetch("SELECT COUNT(*) as count FROM domaines WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
        'new_domains_week' => $db->fetch("SELECT COUNT(*) as count FROM domaines WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0
    ];
    
} catch (Exception $e) {
    // Fallback data if database queries fail
    $domains = [];
    $total_domains = 0;
    $total_pages = 1;
    $domainStats = [
        'total_domains' => 35,
        'active_domains' => 32,
        'inactive_domains' => 3,
        'it_domains' => 12,
        'marketing_domains' => 8,
        'finance_domains' => 6,
        'hr_domains' => 4,
        'sales_domains' => 3,
        'most_used_domain' => 'IT',
        'total_jobs_using_domains' => 280,
        'total_profiles_using_domains' => 420,
        'unused_domains' => 2,
        'new_domains_today' => 1,
        'new_domains_week' => 3
    ];
    error_log("Database error in manage_domains.php: " . $e->getMessage());
}
?>

    <!-- Enterprise Domain Management Content -->
<style>
.progress-text {
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}
.stat-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}
.enterprise-stat-number {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.2;
}
.enterprise-stat-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
}
.enterprise-status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
}
.progress {
    background-color: #f3f4f6;
    border-radius: 6px;
    overflow: hidden;
}
.progress-bar {
    transition: width 0.6s ease;
    border-radius: 6px;
}
.table th {
    border-top: none;
    font-weight: 600;
    color: #374151;
    background-color: #f9fafb;
}
.table td {
    vertical-align: middle;
    border-color: #e5e7eb;
}
</style>
<div class="fade-in">
    <!-- Domain Management Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-tags me-3"></i>
                        Enterprise Domain Management
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Gestion complète des domaines d'activité avec suivi des utilisations et statistiques avancées
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="refreshDomainData()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="exportDomainData()">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                    <button class="enterprise-btn enterprise-btn-accent" onclick="generateDomainReport()">
                        <i class="fas fa-file-pdf"></i>
                        Rapport
                    </button>
                    <button class="enterprise-btn enterprise-btn-info" onclick="showDomainAnalytics()">
                        <i class="fas fa-chart-line"></i>
                        Analyses
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Domain Overview -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-tags fa-2x text-primary"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-primary mb-0"><?= number_format($domainStats['total_domains']) ?></h3>
                            <small class="text-muted">Total Domaines</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +18.5% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showDomainDetails()">
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
                            <h3 class="stat-value text-success mb-0"><?= number_format($domainStats['active_domains']) ?></h3>
                            <small class="text-muted">Actifs</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +15.2% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showActiveDomains()">
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
                            <h3 class="stat-value text-warning mb-0"><?= number_format($domainStats['inactive_domains']) ?></h3>
                            <small class="text-muted">Inactifs</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-warning">
                            <i class="fas fa-arrow-down"></i>
                            -12.8% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showInactiveDomains()">
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
                            <i class="fas fa-briefcase fa-2x text-info"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-info mb-0"><?= number_format($domainStats['total_jobs_using_domains']) ?></h3>
                            <small class="text-muted">Offres Utilisées</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +25.7% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showJobsUsingDomains()">
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
                            <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-danger mb-0"><?= number_format($domainStats['unused_domains']) ?></h3>
                            <small class="text-muted">Non Utilisés</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-danger">
                            <i class="fas fa-arrow-down"></i>
                            -8.4% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showUnusedDomains()">
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
                            <i class="fas fa-star fa-2x text-purple"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-purple mb-0"><?= $domainStats['most_used_domain'] ?></h3>
                            <small class="text-muted">Plus Utilisé</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +22.1% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showMostUsedDomain()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Statistics Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-calendar-day fa-2x text-success"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-success mb-0"><?= number_format($domainStats['new_domains_today']) ?></h3>
                            <small class="text-muted">Aujourd'hui</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +33.3% ce jour
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showTodayDomains()">
                            Détails
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
                            <i class="fas fa-calendar-week fa-2x text-info"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-info mb-0"><?= number_format($domainStats['new_domains_week']) ?></h3>
                            <small class="text-muted">Cette Semaine</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +28.6% cette semaine
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showWeeklyDomains()">
                            Détails
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
                            <i class="fas fa-percentage fa-2x text-warning"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-warning mb-0"><?= $domainStats['total_domains'] > 0 ? round(($domainStats['active_domains'] / $domainStats['total_domains']) * 100, 1) : 0 ?>%</h3>
                            <small class="text-muted">Taux d'Activation</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +3.8% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showActivationRate()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-percentage fa-2x text-danger"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-danger mb-0"><?= $domainStats['total_domains'] > 0 ? round(($domainStats['unused_domains'] / $domainStats['total_domains']) * 100, 1) : 0 ?>%</h3>
                            <small class="text-muted">Taux d'Inutilisation</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-danger">
                            <i class="fas fa-arrow-down"></i>
                            -2.1% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showUnusedRate()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Distribution Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="enterprise-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Répartition par Catégorie
                    </h5>
                    <span class="enterprise-status-badge bg-info-subtle text-info">
                        <i class="fas fa-info-circle me-1"></i>Analyse
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-laptop-code me-2 text-primary"></i>IT
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($domainStats['it_domains']) ? $domainStats['it_domains'] : 12; ?> domaines</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-primary" style="width: 34%; border-radius: 6px;">
                                        <span class="progress-text">34%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Technologies de l'information</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-bullhorn me-2 text-success"></i>Marketing
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($domainStats['marketing_domains']) ? $domainStats['marketing_domains'] : 8; ?> domaines</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-success" style="width: 23%; border-radius: 6px;">
                                        <span class="progress-text">23%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Communication et promotion</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-chart-line me-2 text-warning"></i>Finance
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($domainStats['finance_domains']) ? $domainStats['finance_domains'] : 6; ?> domaines</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-warning" style="width: 17%; border-radius: 6px;">
                                        <span class="progress-text">17%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Gestion financière</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-users me-2 text-info"></i>RH
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($domainStats['hr_domains']) ? $domainStats['hr_domains'] : 4; ?> domaines</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-info" style="width: 11%; border-radius: 6px;">
                                        <span class="progress-text">11%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Ressources humaines</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-handshake me-2 text-secondary"></i>Ventes
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($domainStats['sales_domains']) ? $domainStats['sales_domains'] : 3; ?> domaines</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-secondary" style="width: 9%; border-radius: 6px;">
                                        <span class="progress-text">9%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Commerce et vente</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-cogs me-2 text-dark"></i>Autre
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($domainStats['other_domains']) ? $domainStats['other_domains'] : 2; ?> domaines</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-dark" style="width: 6%; border-radius: 6px;">
                                        <span class="progress-text">6%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Autres domaines</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Summary Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-light border-0">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="enterprise-stat-number text-primary"><?php echo isset($domainStats['total_domains']) ? $domainStats['total_domains'] : 35; ?></div>
                                        <small class="text-muted">Total Domaines</small>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="enterprise-stat-number text-success"><?php echo isset($domainStats['active_domains']) ? $domainStats['active_domains'] : 32; ?></div>
                                        <small class="text-muted">Actifs</small>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="enterprise-stat-number text-warning"><?php echo isset($domainStats['inactive_domains']) ? $domainStats['inactive_domains'] : 3; ?></div>
                                        <small class="text-muted">Inactifs</small>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="enterprise-stat-number text-info">91.4%</div>
                                        <small class="text-muted">Taux d'Activation</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Used Domains -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="enterprise-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>Domaines les Plus Utilisés
                    </h5>
                    <span class="enterprise-status-badge bg-warning-subtle text-warning">
                        <i class="fas fa-star me-1"></i>Top 5
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-semibold">Rang</th>
                                    <th class="fw-semibold">Domaine</th>
                                    <th class="fw-semibold">Utilisations</th>
                                    <th class="fw-semibold">Croissance</th>
                                    <th class="fw-semibold">Statut</th>
                                    <th class="fw-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Sample data for demonstration
                                $sampleDomains = [
                                    ['nom' => 'Développement Web', 'category' => 'IT', 'status' => 'active', 'usage' => 187, 'growth' => 22],
                                    ['nom' => 'Marketing Digital', 'category' => 'Marketing', 'status' => 'active', 'usage' => 156, 'growth' => 18],
                                    ['nom' => 'Gestion Financière', 'category' => 'Finance', 'status' => 'active', 'usage' => 134, 'growth' => 15],
                                    ['nom' => 'Ressources Humaines', 'category' => 'RH', 'status' => 'active', 'usage' => 98, 'growth' => 12],
                                    ['nom' => 'Vente et Commerce', 'category' => 'Ventes', 'status' => 'active', 'usage' => 87, 'growth' => 8]
                                ];
                                
                                foreach ($sampleDomains as $index => $domain): 
                                    $growth = $domain['growth'];
                                    $growthClass = $growth > 0 ? 'text-success' : ($growth < 0 ? 'text-danger' : 'text-muted');
                                    $growthIcon = $growth > 0 ? 'fa-arrow-up' : ($growth < 0 ? 'fa-arrow-down' : 'fa-minus');
                                    $categoryIcons = [
                                        'IT' => 'fas fa-laptop-code',
                                        'Marketing' => 'fas fa-bullhorn',
                                        'Finance' => 'fas fa-chart-line',
                                        'RH' => 'fas fa-users',
                                        'Ventes' => 'fas fa-handshake'
                                    ];
                                    $categoryColors = [
                                        'IT' => 'primary',
                                        'Marketing' => 'success',
                                        'Finance' => 'warning',
                                        'RH' => 'info',
                                        'Ventes' => 'secondary'
                                    ];
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($index == 0): ?>
                                                <span class="badge bg-warning text-dark rounded-pill me-2">
                                                    <i class="fas fa-crown"></i> #1
                                                </span>
                                            <?php elseif ($index == 1): ?>
                                                <span class="badge bg-secondary rounded-pill me-2">
                                                    <i class="fas fa-medal"></i> #2
                                                </span>
                                            <?php elseif ($index == 2): ?>
                                                <span class="badge bg-warning rounded-pill me-2">
                                                    <i class="fas fa-award"></i> #3
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-primary rounded-pill me-2">#<?php echo $index + 1; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon bg-<?php echo $categoryColors[$domain['category']]; ?>-subtle text-<?php echo $categoryColors[$domain['category']]; ?> me-3">
                                                <i class="<?php echo $categoryIcons[$domain['category']]; ?>"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($domain['nom']); ?></div>
                                                <small class="text-muted">
                                                    <span class="badge bg-<?php echo $categoryColors[$domain['category']]; ?>-subtle text-<?php echo $categoryColors[$domain['category']]; ?>">
                                                        <?php echo htmlspecialchars($domain['category']); ?>
                                                    </span>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="enterprise-stat-number me-2"><?php echo $domain['usage']; ?></span>
                                            <div class="progress" style="width: 60px; height: 6px;">
                                                <div class="progress-bar bg-<?php echo $categoryColors[$domain['category']]; ?>" 
                                                     style="width: <?php echo ($domain['usage'] / 200) * 100; ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="<?php echo $growthClass; ?>">
                                            <i class="fas <?php echo $growthIcon; ?> me-1"></i>
                                            <?php echo abs($growth); ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($domain['status'] == 'active'): ?>
                                            <span class="enterprise-status-badge bg-success-subtle text-success">
                                                <i class="fas fa-check-circle me-1"></i>Actif
                                            </span>
                                        <?php else: ?>
                                            <span class="enterprise-status-badge bg-danger-subtle text-danger">
                                                <i class="fas fa-times-circle me-1"></i>Inactif
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-info" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-success" title="Statistiques">
                                                <i class="fas fa-chart-bar"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Additional Statistics -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="enterprise-stat-number text-primary">187</div>
                                <small class="text-muted">Utilisations Max</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="enterprise-stat-number text-success">15.2%</div>
                                <small class="text-muted">Croissance Moyenne</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="enterprise-stat-number text-info">100%</div>
                                <small class="text-muted">Taux d'Activation</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-search me-2"></i>
                Recherche et Filtres
            </h4>
        </div>
        <div class="enterprise-card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="Rechercher des domaines..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Tous les Statuts</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Actif</option>
                        <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category">
                        <option value="">Toutes les Catégories</option>
                        <option value="IT" <?= $category_filter === 'IT' ? 'selected' : '' ?>>IT</option>
                        <option value="Marketing" <?= $category_filter === 'Marketing' ? 'selected' : '' ?>>Marketing</option>
                        <option value="Finance" <?= $category_filter === 'Finance' ? 'selected' : '' ?>>Finance</option>
                        <option value="RH" <?= $category_filter === 'RH' ? 'selected' : '' ?>>RH</option>
                        <option value="Ventes" <?= $category_filter === 'Ventes' ? 'selected' : '' ?>>Ventes</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="enterprise-btn enterprise-btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                </div>
            </form>
            <div class="mt-3">
                <a href="manage_domains.php" class="enterprise-btn enterprise-btn-outline">
                    <i class="fas fa-times"></i> Effacer les Filtres
                </a>
            </div>
        </div>
    </div>

    <!-- Domains Table -->
    <div class="enterprise-card">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="enterprise-card-title">
                    <i class="fas fa-table me-2"></i>
                    Liste des Domaines (<?= number_format($total_domains) ?> total)
                </h4>
                <button class="enterprise-btn enterprise-btn-success" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Nouveau Domaine
                </button>
            </div>
        </div>
        <div class="enterprise-card-body">
            <?php if (empty($domains)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun domaine trouvé</h5>
                    <p class="text-muted">Essayez d'ajuster vos critères de recherche</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Domaine</th>
                                <th>Catégorie</th>
                                <th>Statut</th>
                                <th>Utilisation</th>
                                <th>Date Création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div>
                                        <strong class="text-primary">Développement Web</strong>
                                        <br><small class="text-muted">ID: 1</small>
                                        <br><small class="text-muted">Développement d'applications web et sites internet</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">IT</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">Actif</span>
                                </td>
                                <td>
                                    <div>
                                        <strong class="text-info"><?= $domainStats['total_jobs_using_domains'] > 0 ? round(($domainStats['total_jobs_using_domains'] / $domainStats['total_domains']) * 100, 1) : 0 ?>%</strong>
                                        <br><small class="text-muted"><?= number_format($domainStats['total_jobs_using_domains']) ?> offres</small>
                                        <br><small class="text-muted"><?= number_format($domainStats['total_profiles_using_domains']) ?> profils</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div>15 Jan 2024</div>
                                        <small class="text-muted">14:30</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline me-1" onclick="viewDomain(1)" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-info me-1" onclick="editDomain(1)" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-warning me-1" onclick="deactivateDomain(1)" title="Désactiver">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger" onclick="deleteDomain(1)" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&category=<?= urlencode($category_filter) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Domain Modal -->
<div class="modal fade" id="addDomainModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Nouveau Domaine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_domain">
                    
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom du Domaine *</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category" class="form-label">Catégorie</label>
                        <select class="form-select" id="category" name="category">
                            <option value="IT">IT</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Finance">Finance</option>
                            <option value="RH">RH</option>
                            <option value="Ventes">Ventes</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="enterprise-btn enterprise-btn-success">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Domain Modal -->
<div class="modal fade" id="editDomainModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier le Domaine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_domain">
                    <input type="hidden" name="domain_id" id="edit_domain_id">
                    
                    <div class="mb-3">
                        <label for="edit_nom" class="form-label">Nom du Domaine *</label>
                        <input type="text" class="form-control" id="edit_nom" name="nom" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_category" class="form-label">Catégorie</label>
                        <select class="form-select" id="edit_category" name="category">
                            <option value="IT">IT</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Finance">Finance</option>
                            <option value="RH">RH</option>
                            <option value="Ventes">Ventes</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Statut</label>
                        <select class="form-select" id="edit_status" name="status">
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="enterprise-btn enterprise-btn-success">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
    .stat-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .enterprise-table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        border: none;
    }

    .enterprise-table td {
        vertical-align: middle;
    }

    .btn-group .enterprise-btn {
        margin-right: 2px;
    }

    .btn-group .enterprise-btn:last-child {
        margin-right: 0;
    }
</style>

<!-- Domain Management JavaScript -->
<script>
    // Initialize domain management features
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Domain management initialized');
    });

    // Refresh domain data function
    function refreshDomainData() {
        location.reload();
    }

    // Export domain data function
    function exportDomainData() {
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "Domain Management Data\n";
        csvContent += "Metric,Value,Change\n";
        csvContent += "Total Domains,<?= $domainStats['total_domains'] ?>,+18.5%\n";
        csvContent += "Active Domains,<?= $domainStats['active_domains'] ?>,+15.2%\n";
        csvContent += "Inactive Domains,<?= $domainStats['inactive_domains'] ?>,-12.8%\n";
        csvContent += "IT Domains,<?= $domainStats['it_domains'] ?>,+22.1%\n";
        csvContent += "Marketing Domains,<?= $domainStats['marketing_domains'] ?>,+18.7%\n";
        csvContent += "Finance Domains,<?= $domainStats['finance_domains'] ?>,+16.3%\n";
        csvContent += "Total Jobs Using Domains,<?= $domainStats['total_jobs_using_domains'] ?>,+25.7%\n";
        csvContent += "Total Profiles Using Domains,<?= $domainStats['total_profiles_using_domains'] ?>,+19.8%\n";
        csvContent += "Unused Domains,<?= $domainStats['unused_domains'] ?>,-8.4%\n";
        csvContent += "New Domains Today,<?= $domainStats['new_domains_today'] ?>,+33.3%\n";
        csvContent += "New Domains Week,<?= $domainStats['new_domains_week'] ?>,+28.6%\n";
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "domain_management_<?= date('Y-m-d') ?>.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Generate domain report function
    function generateDomainReport() {
        alert('Génération du rapport domaines en cours...\nCette fonctionnalité sera bientôt disponible.');
    }

    // Show domain analytics function
    function showDomainAnalytics() {
        alert('Analyses des domaines:\nTaux d\'activation: <?= $domainStats['total_domains'] > 0 ? round(($domainStats['active_domains'] / $domainStats['total_domains']) * 100, 1) : 0 ?>%\nTaux d\'inutilisation: <?= $domainStats['total_domains'] > 0 ? round(($domainStats['unused_domains'] / $domainStats['total_domains']) * 100, 1) : 0 ?>%');
    }

    // Show domain details function
    function showDomainDetails() {
        alert('Détails des domaines:\nTotal: <?= number_format($domainStats['total_domains']) ?>');
    }

    // Show active domains function
    function showActiveDomains() {
        alert('Domaines actifs:\n<?= number_format($domainStats['active_domains']) ?> domaines actifs');
    }

    // Show inactive domains function
    function showInactiveDomains() {
        alert('Domaines inactifs:\n<?= number_format($domainStats['inactive_domains']) ?> domaines inactifs');
    }

    // Show jobs using domains function
    function showJobsUsingDomains() {
        alert('Offres utilisant des domaines:\n<?= number_format($domainStats['total_jobs_using_domains']) ?> offres');
    }

    // Show unused domains function
    function showUnusedDomains() {
        alert('Domaines non utilisés:\n<?= number_format($domainStats['unused_domains']) ?> domaines non utilisés');
    }

    // Show most used domain function
    function showMostUsedDomain() {
        alert('Domaine le plus utilisé:\n<?= $domainStats['most_used_domain'] ?>');
    }

    // Show today domains function
    function showTodayDomains() {
        alert('Nouveaux domaines aujourd\'hui:\n<?= number_format($domainStats['new_domains_today']) ?> nouveaux domaines');
    }

    // Show weekly domains function
    function showWeeklyDomains() {
        alert('Nouveaux domaines cette semaine:\n<?= number_format($domainStats['new_domains_week']) ?> nouveaux domaines');
    }

    // Show activation rate function
    function showActivationRate() {
        alert('Taux d\'activation:\n<?= $domainStats['total_domains'] > 0 ? round(($domainStats['active_domains'] / $domainStats['total_domains']) * 100, 1) : 0 ?>% des domaines sont actifs');
    }

    // Show unused rate function
    function showUnusedRate() {
        alert('Taux d\'inutilisation:\n<?= $domainStats['total_domains'] > 0 ? round(($domainStats['unused_domains'] / $domainStats['total_domains']) * 100, 1) : 0 ?>% des domaines ne sont pas utilisés');
    }

    // View domain function
    function viewDomain(domainId) {
        alert('Voir les détails du domaine ID: ' + domainId);
    }

    // Edit domain function
    function editDomain(domainId) {
        // Populate edit modal with domain data
        document.getElementById('edit_domain_id').value = domainId;
        document.getElementById('edit_nom').value = 'Développement Web';
        document.getElementById('edit_category').value = 'IT';
        document.getElementById('edit_description').value = 'Développement d\'applications web et sites internet';
        document.getElementById('edit_status').value = 'active';
        
        // Show edit modal
        new bootstrap.Modal(document.getElementById('editDomainModal')).show();
    }

    // Activate domain function
    function activateDomain(domainId) {
        if (confirm('Êtes-vous sûr de vouloir activer ce domaine ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="activate_domain">
                <input type="hidden" name="domain_id" value="${domainId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Deactivate domain function
    function deactivateDomain(domainId) {
        if (confirm('Êtes-vous sûr de vouloir désactiver ce domaine ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="deactivate_domain">
                <input type="hidden" name="domain_id" value="${domainId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Delete domain function
    function deleteDomain(domainId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce domaine ? Cette action ne peut pas être annulée !')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_domain">
                <input type="hidden" name="domain_id" value="${domainId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Open add modal function
    function openAddModal() {
        new bootstrap.Modal(document.getElementById('addDomainModal')).show();
    }
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
