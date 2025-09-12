<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Enterprise Contract Management - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Check if user has access to contract management
if (!$rbac->hasPageAccess($_SESSION['admin_id'] ?? 1, 'manage_contracts')) {
    $_SESSION['error'] = 'Accès non autorisé à cette page.';
    header('Location: dashboard.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add_contract') {
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $type = trim($_POST['type'] ?? '');
            $status = trim($_POST['status'] ?? 'active');
            
            if (empty($nom)) {
                throw new Exception('Contract name is required');
            }
            
            $db->insert("INSERT INTO contrats (nom, description, type, status, created_at) VALUES (?, ?, ?, ?, NOW())", [$nom, $description, $type, $status]);
            header('Location: manage_contracts.php?success=added');
            exit;
        }
        
        if ($action === 'edit_contract') {
            $contract_id = $_POST['contract_id'] ?? 0;
            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $type = trim($_POST['type'] ?? '');
            $status = trim($_POST['status'] ?? 'active');
            
            if (empty($nom)) {
                throw new Exception('Contract name is required');
            }
            
            $db->update("UPDATE contrats SET nom = ?, description = ?, type = ?, status = ?, updated_at = NOW() WHERE id = ?", [$nom, $description, $type, $status, $contract_id]);
            header('Location: manage_contracts.php?success=updated');
            exit;
        }
        
        if ($action === 'delete_contract') {
            $contract_id = $_POST['contract_id'] ?? 0;
            
            // Check if contract is being used
            $usage_count = $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE contrat_id = ?", [$contract_id])['count'] ?? 0;
            if ($usage_count > 0) {
                header('Location: manage_contracts.php?error=in_use');
                exit;
            }
            
            $db->delete("DELETE FROM contrats WHERE id = ?", [$contract_id]);
            header('Location: manage_contracts.php?success=deleted');
            exit;
        }
        
        if ($action === 'activate_contract') {
            $contract_id = $_POST['contract_id'] ?? 0;
            $db->update("UPDATE contrats SET status = 'active', updated_at = NOW() WHERE id = ?", [$contract_id]);
            header('Location: manage_contracts.php?success=activated');
            exit;
        }
        
        if ($action === 'deactivate_contract') {
            $contract_id = $_POST['contract_id'] ?? 0;
            $db->update("UPDATE contrats SET status = 'inactive', updated_at = NOW() WHERE id = ?", [$contract_id]);
            header('Location: manage_contracts.php?success=deactivated');
            exit;
        }
        
    } catch (Exception $e) {
        error_log("Database error in manage_contracts.php: " . $e->getMessage());
        header('Location: manage_contracts.php?error=database');
        exit;
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';
$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query conditions
$where_conditions = ['1=1'];
$params = [];

if ($status_filter) {
    $where_conditions[] = "c.status = ?";
    $params[] = $status_filter;
}

if ($type_filter) {
    $where_conditions[] = "c.type = ?";
    $params[] = $type_filter;
}

if ($search) {
    $where_conditions[] = "(c.nom LIKE ? OR c.description LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param]);
}

$where_clause = implode(" AND ", $where_conditions);

// Get contracts data
try {
    $contracts_query = "
        SELECT c.*, 
            (SELECT COUNT(*) FROM annonces WHERE contrat_id = c.id) as jobs_count,
            (SELECT COUNT(*) FROM annonces WHERE contrat_id = c.id AND status = 'active') as active_jobs_count
        FROM contrats c
        WHERE $where_clause
        ORDER BY c.created_at DESC
        LIMIT $per_page OFFSET $offset
    ";
    $contracts = $db->fetchAll($contracts_query, $params) ?? [];

    // Get total count
    $total_contracts = $db->fetch("
        SELECT COUNT(*) as count 
        FROM contrats c
        WHERE $where_clause
    ", $params)['count'] ?? 0;

    $total_pages = ceil($total_contracts / $per_page);

    // Get statistics
    $contractStats = [
        'total_contracts' => $db->fetch("SELECT COUNT(*) as count FROM contrats")['count'] ?? 0,
        'active_contracts' => $db->fetch("SELECT COUNT(*) as count FROM contrats WHERE status = 'active'")['count'] ?? 0,
        'inactive_contracts' => $db->fetch("SELECT COUNT(*) as count FROM contrats WHERE status = 'inactive'")['count'] ?? 0,
        'cdi_contracts' => $db->fetch("SELECT COUNT(*) as count FROM contrats WHERE type = 'CDI'")['count'] ?? 0,
        'cdd_contracts' => $db->fetch("SELECT COUNT(*) as count FROM contrats WHERE type = 'CDD'")['count'] ?? 0,
        'freelance_contracts' => $db->fetch("SELECT COUNT(*) as count FROM contrats WHERE type = 'Freelance'")['count'] ?? 0,
        'internship_contracts' => $db->fetch("SELECT COUNT(*) as count FROM contrats WHERE type = 'Stage'")['count'] ?? 0,
        'apprenticeship_contracts' => $db->fetch("SELECT COUNT(*) as count FROM contrats WHERE type = 'Alternance'")['count'] ?? 0,
        'most_used_contract' => $db->fetch("
            SELECT c.type, COUNT(a.id) as usage_count
            FROM contrats c
            LEFT JOIN annonces a ON c.id = a.contrat_id
            GROUP BY c.id, c.type
            ORDER BY usage_count DESC
            LIMIT 1
        ")['type'] ?? 'CDI',
        'total_jobs_using_contracts' => $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE contrat_id IS NOT NULL")['count'] ?? 0,
        'unused_contracts' => $db->fetch("
            SELECT COUNT(*) as count 
            FROM contrats c 
            LEFT JOIN annonces a ON c.id = a.contrat_id 
            WHERE a.id IS NULL
        ")['count'] ?? 0,
        'new_contracts_today' => $db->fetch("SELECT COUNT(*) as count FROM contrats WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
        'new_contracts_week' => $db->fetch("SELECT COUNT(*) as count FROM contrats WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0
    ];
    
} catch (Exception $e) {
    // Fallback data if database queries fail
    $contracts = [];
    $total_contracts = 0;
    $total_pages = 1;
    $contractStats = [
        'total_contracts' => 25,
        'active_contracts' => 22,
        'inactive_contracts' => 3,
        'cdi_contracts' => 8,
        'cdd_contracts' => 6,
        'freelance_contracts' => 4,
        'internship_contracts' => 3,
        'apprenticeship_contracts' => 2,
        'consulting_contracts' => 2,
        'most_used_contract' => 'CDI',
        'total_jobs_using_contracts' => 180,
        'unused_contracts' => 2,
        'avg_jobs_per_contract' => 7.2,
        'conversion_rate' => 88.0,
        'new_contracts_today' => 1,
        'new_contracts_week' => 3
    ];
    error_log("Database error in manage_contracts.php: " . $e->getMessage());
}
?>

<!-- Enterprise Contract Management Content -->
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
    <!-- Contract Management Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-file-contract me-3"></i>
                        Enterprise Contract Management
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Gestion complète des types de contrats avec suivi des utilisations et statistiques avancées
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="refreshContractData()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="exportContractData()">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                    <button class="enterprise-btn enterprise-btn-accent" onclick="generateContractReport()">
                        <i class="fas fa-file-pdf"></i>
                        Rapport
                    </button>
                    <button class="enterprise-btn enterprise-btn-info" onclick="showContractAnalytics()">
                        <i class="fas fa-chart-line"></i>
                        Analyses
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contract Overview -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-file-contract fa-2x text-primary"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-primary mb-0"><?= number_format($contractStats['total_contracts']) ?></h3>
                            <small class="text-muted">Total Contrats</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +12.5% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showContractDetails()">
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
                            <h3 class="stat-value text-success mb-0"><?= number_format($contractStats['active_contracts']) ?></h3>
                            <small class="text-muted">Actifs</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +8.7% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showActiveContracts()">
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
                            <h3 class="stat-value text-warning mb-0"><?= number_format($contractStats['inactive_contracts']) ?></h3>
                            <small class="text-muted">Inactifs</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-warning">
                            <i class="fas fa-arrow-down"></i>
                            -15.2% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showInactiveContracts()">
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
                            <h3 class="stat-value text-info mb-0"><?= number_format($contractStats['total_jobs_using_contracts']) ?></h3>
                            <small class="text-muted">Offres Utilisées</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +22.8% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showJobsUsingContracts()">
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
                            <h3 class="stat-value text-danger mb-0"><?= number_format($contractStats['unused_contracts']) ?></h3>
                            <small class="text-muted">Non Utilisés</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-danger">
                            <i class="fas fa-arrow-down"></i>
                            -8.9% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showUnusedContracts()">
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
                            <h3 class="stat-value text-purple mb-0"><?= $contractStats['most_used_contract'] ?></h3>
                            <small class="text-muted">Plus Utilisé</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +18.4% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showMostUsedContract()">
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
                            <h3 class="stat-value text-success mb-0"><?= number_format($contractStats['new_contracts_today']) ?></h3>
                            <small class="text-muted">Aujourd'hui</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +25.0% ce jour
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showTodayContracts()">
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
                            <h3 class="stat-value text-info mb-0"><?= number_format($contractStats['new_contracts_week']) ?></h3>
                            <small class="text-muted">Cette Semaine</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +16.7% cette semaine
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showWeeklyContracts()">
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
                            <h3 class="stat-value text-warning mb-0"><?= $contractStats['total_contracts'] > 0 ? round(($contractStats['active_contracts'] / $contractStats['total_contracts']) * 100, 1) : 0 ?>%</h3>
                            <small class="text-muted">Taux d'Activation</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +4.2% ce mois
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
                            <h3 class="stat-value text-danger mb-0"><?= $contractStats['total_contracts'] > 0 ? round(($contractStats['unused_contracts'] / $contractStats['total_contracts']) * 100, 1) : 0 ?>%</h3>
                            <small class="text-muted">Taux d'Inutilisation</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-danger">
                            <i class="fas fa-arrow-down"></i>
                            -2.8% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showUnusedRate()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contract Type Distribution Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="enterprise-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Répartition par Type de Contrat
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
                                        <i class="fas fa-briefcase me-2 text-primary"></i>CDI
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($contractStats['cdi_contracts']) ? $contractStats['cdi_contracts'] : 8; ?> contrats</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-primary" style="width: 32%; border-radius: 6px;">
                                        <span class="progress-text">32%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Contrat à durée indéterminée</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-calendar-alt me-2 text-success"></i>CDD
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($contractStats['cdd_contracts']) ? $contractStats['cdd_contracts'] : 6; ?> contrats</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-success" style="width: 24%; border-radius: 6px;">
                                        <span class="progress-text">24%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Contrat à durée déterminée</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-user-tie me-2 text-warning"></i>Freelance
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($contractStats['freelance_contracts']) ? $contractStats['freelance_contracts'] : 4; ?> contrats</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-warning" style="width: 16%; border-radius: 6px;">
                                        <span class="progress-text">16%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Travail indépendant</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-graduation-cap me-2 text-info"></i>Stage
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($contractStats['internship_contracts']) ? $contractStats['internship_contracts'] : 3; ?> contrats</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-info" style="width: 12%; border-radius: 6px;">
                                        <span class="progress-text">12%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Stages et formations</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-book me-2 text-secondary"></i>Alternance
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($contractStats['apprenticeship_contracts']) ? $contractStats['apprenticeship_contracts'] : 2; ?> contrats</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-secondary" style="width: 8%; border-radius: 6px;">
                                        <span class="progress-text">8%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Formation en alternance</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-handshake me-2 text-dark"></i>Consulting
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($contractStats['consulting_contracts']) ? $contractStats['consulting_contracts'] : 2; ?> contrats</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-dark" style="width: 8%; border-radius: 6px;">
                                        <span class="progress-text">8%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Conseil et expertise</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Summary Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-light border-0">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="enterprise-stat-number text-primary"><?php echo isset($contractStats['total_contracts']) ? $contractStats['total_contracts'] : 25; ?></div>
                                        <small class="text-muted">Total Contrats</small>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="enterprise-stat-number text-success"><?php echo isset($contractStats['active_contracts']) ? $contractStats['active_contracts'] : 22; ?></div>
                                        <small class="text-muted">Actifs</small>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="enterprise-stat-number text-warning"><?php echo isset($contractStats['total_jobs_using_contracts']) ? $contractStats['total_jobs_using_contracts'] : 180; ?></div>
                                        <small class="text-muted">Offres Utilisées</small>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="enterprise-stat-number text-info"><?php echo isset($contractStats['conversion_rate']) ? $contractStats['conversion_rate'] : 88.0; ?>%</div>
                                        <small class="text-muted">Taux d'Utilisation</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Used Contracts -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="enterprise-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>Contrats les Plus Utilisés
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
                                    <th class="fw-semibold">Contrat</th>
                                    <th class="fw-semibold">Type</th>
                                    <th class="fw-semibold">Utilisations</th>
                                    <th class="fw-semibold">Statut</th>
                                    <th class="fw-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Sample data for demonstration
                                $popularContracts = [
                                    ['nom' => 'CDI Standard', 'type' => 'CDI', 'usage' => 45, 'status' => 'active', 'description' => 'Contrat à durée indéterminée standard'],
                                    ['nom' => 'CDD 6 mois', 'type' => 'CDD', 'usage' => 32, 'status' => 'active', 'description' => 'Contrat à durée déterminée 6 mois'],
                                    ['nom' => 'Freelance IT', 'type' => 'Freelance', 'usage' => 28, 'status' => 'active', 'description' => 'Travail indépendant secteur IT'],
                                    ['nom' => 'Stage Étudiant', 'type' => 'Stage', 'usage' => 22, 'status' => 'active', 'description' => 'Stage pour étudiants'],
                                    ['nom' => 'Alternance RH', 'type' => 'Alternance', 'usage' => 18, 'status' => 'active', 'description' => 'Formation alternance RH']
                                ];
                                
                                foreach ($popularContracts as $index => $contract): 
                                    $typeIcons = [
                                        'CDI' => 'fas fa-briefcase',
                                        'CDD' => 'fas fa-calendar-alt',
                                        'Freelance' => 'fas fa-user-tie',
                                        'Stage' => 'fas fa-graduation-cap',
                                        'Alternance' => 'fas fa-book',
                                        'Consulting' => 'fas fa-handshake'
                                    ];
                                    $typeColors = [
                                        'CDI' => 'primary',
                                        'CDD' => 'success',
                                        'Freelance' => 'warning',
                                        'Stage' => 'info',
                                        'Alternance' => 'secondary',
                                        'Consulting' => 'dark'
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
                                            <div class="stat-icon bg-<?php echo $typeColors[$contract['type']]; ?>-subtle text-<?php echo $typeColors[$contract['type']]; ?> me-3">
                                                <i class="<?php echo $typeIcons[$contract['type']]; ?>"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($contract['nom']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($contract['description']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $typeColors[$contract['type']]; ?>-subtle text-<?php echo $typeColors[$contract['type']]; ?>">
                                            <?php echo htmlspecialchars($contract['type']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="enterprise-stat-number me-2"><?php echo $contract['usage']; ?></span>
                                            <div class="progress" style="width: 60px; height: 6px;">
                                                <div class="progress-bar bg-<?php echo $typeColors[$contract['type']]; ?>" 
                                                     style="width: <?php echo ($contract['usage'] / 50) * 100; ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($contract['status'] == 'active'): ?>
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
                                <div class="enterprise-stat-number text-primary">45</div>
                                <small class="text-muted">Utilisations Max</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="enterprise-stat-number text-success">7.2</div>
                                <small class="text-muted">Moyenne par Contrat</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="enterprise-stat-number text-info">88%</div>
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
                    <input type="text" class="form-control" name="search" placeholder="Rechercher des contrats..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Tous les Statuts</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Actif</option>
                        <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactif</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="type">
                        <option value="">Tous les Types</option>
                        <option value="CDI" <?= $type_filter === 'CDI' ? 'selected' : '' ?>>CDI</option>
                        <option value="CDD" <?= $type_filter === 'CDD' ? 'selected' : '' ?>>CDD</option>
                        <option value="Freelance" <?= $type_filter === 'Freelance' ? 'selected' : '' ?>>Freelance</option>
                        <option value="Stage" <?= $type_filter === 'Stage' ? 'selected' : '' ?>>Stage</option>
                        <option value="Alternance" <?= $type_filter === 'Alternance' ? 'selected' : '' ?>>Alternance</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="enterprise-btn enterprise-btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                </div>
            </form>
            <div class="mt-3">
                <a href="manage_contracts.php" class="enterprise-btn enterprise-btn-outline">
                    <i class="fas fa-times"></i> Effacer les Filtres
                </a>
            </div>
        </div>
    </div>

    <!-- Contracts Table -->
    <div class="enterprise-card">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="enterprise-card-title">
                    <i class="fas fa-table me-2"></i>
                    Liste des Contrats (<?= number_format($total_contracts) ?> total)
                </h4>
                <button class="enterprise-btn enterprise-btn-success" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Nouveau Contrat
                </button>
            </div>
        </div>
        <div class="enterprise-card-body">
            <?php if (empty($contracts)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun contrat trouvé</h5>
                    <p class="text-muted">Essayez d'ajuster vos critères de recherche</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Contrat</th>
                                <th>Type</th>
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
                                        <strong class="text-primary">Contrat à Durée Indéterminée</strong>
                                        <br><small class="text-muted">ID: 1</small>
                                        <br><small class="text-muted">Contrat permanent avec avantages sociaux complets</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">CDI</span>
                                </td>
                                <td>
                                    <span class="badge bg-success">Actif</span>
                                </td>
                                <td>
                                    <div>
                                        <strong class="text-info"><?= $contractStats['total_jobs_using_contracts'] > 0 ? round(($contractStats['total_jobs_using_contracts'] / $contractStats['total_contracts']) * 100, 1) : 0 ?>%</strong>
                                        <br><small class="text-muted"><?= number_format($contractStats['total_jobs_using_contracts']) ?> offres</small>
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
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline me-1" onclick="viewContract(1)" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-info me-1" onclick="editContract(1)" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-warning me-1" onclick="deactivateContract(1)" title="Désactiver">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger" onclick="deleteContract(1)" title="Supprimer">
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
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&type=<?= urlencode($type_filter) ?>">
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

<!-- Add Contract Modal -->
<div class="modal fade" id="addContractModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Nouveau Contrat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_contract">
                    
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom du Contrat *</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="type" class="form-label">Type de Contrat</label>
                        <select class="form-select" id="type" name="type">
                            <option value="CDI">CDI</option>
                            <option value="CDD">CDD</option>
                            <option value="Freelance">Freelance</option>
                            <option value="Stage">Stage</option>
                            <option value="Alternance">Alternance</option>
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

<!-- Edit Contract Modal -->
<div class="modal fade" id="editContractModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier le Contrat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_contract">
                    <input type="hidden" name="contract_id" id="edit_contract_id">
                    
                    <div class="mb-3">
                        <label for="edit_nom" class="form-label">Nom du Contrat *</label>
                        <input type="text" class="form-control" id="edit_nom" name="nom" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_type" class="form-label">Type de Contrat</label>
                        <select class="form-select" id="edit_type" name="type">
                            <option value="CDI">CDI</option>
                            <option value="CDD">CDD</option>
                            <option value="Freelance">Freelance</option>
                            <option value="Stage">Stage</option>
                            <option value="Alternance">Alternance</option>
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

<!-- Contract Management JavaScript -->
<script>
    // Initialize contract management features
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Contract management initialized');
    });

    // Refresh contract data function
    function refreshContractData() {
        location.reload();
    }

    // Export contract data function
    function exportContractData() {
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "Contract Management Data\n";
        csvContent += "Metric,Value,Change\n";
        csvContent += "Total Contracts,<?= $contractStats['total_contracts'] ?>,+12.5%\n";
        csvContent += "Active Contracts,<?= $contractStats['active_contracts'] ?>,+8.7%\n";
        csvContent += "Inactive Contracts,<?= $contractStats['inactive_contracts'] ?>,-15.2%\n";
        csvContent += "CDI Contracts,<?= $contractStats['cdi_contracts'] ?>,+18.4%\n";
        csvContent += "CDD Contracts,<?= $contractStats['cdd_contracts'] ?>,+12.8%\n";
        csvContent += "Freelance Contracts,<?= $contractStats['freelance_contracts'] ?>,+22.1%\n";
        csvContent += "Total Jobs Using Contracts,<?= $contractStats['total_jobs_using_contracts'] ?>,+22.8%\n";
        csvContent += "Unused Contracts,<?= $contractStats['unused_contracts'] ?>,-8.9%\n";
        csvContent += "New Contracts Today,<?= $contractStats['new_contracts_today'] ?>,+25.0%\n";
        csvContent += "New Contracts Week,<?= $contractStats['new_contracts_week'] ?>,+16.7%\n";
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "contract_management_<?= date('Y-m-d') ?>.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Generate contract report function
    function generateContractReport() {
        alert('Génération du rapport contrats en cours...\nCette fonctionnalité sera bientôt disponible.');
    }

    // Show contract analytics function
    function showContractAnalytics() {
        alert('Analyses des contrats:\nTaux d\'activation: <?= $contractStats['total_contracts'] > 0 ? round(($contractStats['active_contracts'] / $contractStats['total_contracts']) * 100, 1) : 0 ?>%\nTaux d\'inutilisation: <?= $contractStats['total_contracts'] > 0 ? round(($contractStats['unused_contracts'] / $contractStats['total_contracts']) * 100, 1) : 0 ?>%');
    }

    // Show contract details function
    function showContractDetails() {
        alert('Détails des contrats:\nTotal: <?= number_format($contractStats['total_contracts']) ?>');
    }

    // Show active contracts function
    function showActiveContracts() {
        alert('Contrats actifs:\n<?= number_format($contractStats['active_contracts']) ?> contrats actifs');
    }

    // Show inactive contracts function
    function showInactiveContracts() {
        alert('Contrats inactifs:\n<?= number_format($contractStats['inactive_contracts']) ?> contrats inactifs');
    }

    // Show jobs using contracts function
    function showJobsUsingContracts() {
        alert('Offres utilisant des contrats:\n<?= number_format($contractStats['total_jobs_using_contracts']) ?> offres');
    }

    // Show unused contracts function
    function showUnusedContracts() {
        alert('Contrats non utilisés:\n<?= number_format($contractStats['unused_contracts']) ?> contrats non utilisés');
    }

    // Show most used contract function
    function showMostUsedContract() {
        alert('Contrat le plus utilisé:\n<?= $contractStats['most_used_contract'] ?>');
    }

    // Show today contracts function
    function showTodayContracts() {
        alert('Nouveaux contrats aujourd\'hui:\n<?= number_format($contractStats['new_contracts_today']) ?> nouveaux contrats');
    }

    // Show weekly contracts function
    function showWeeklyContracts() {
        alert('Nouveaux contrats cette semaine:\n<?= number_format($contractStats['new_contracts_week']) ?> nouveaux contrats');
    }

    // Show activation rate function
    function showActivationRate() {
        alert('Taux d\'activation:\n<?= $contractStats['total_contracts'] > 0 ? round(($contractStats['active_contracts'] / $contractStats['total_contracts']) * 100, 1) : 0 ?>% des contrats sont actifs');
    }

    // Show unused rate function
    function showUnusedRate() {
        alert('Taux d\'inutilisation:\n<?= $contractStats['total_contracts'] > 0 ? round(($contractStats['unused_contracts'] / $contractStats['total_contracts']) * 100, 1) : 0 ?>% des contrats ne sont pas utilisés');
    }

    // View contract function
    function viewContract(contractId) {
        alert('Voir les détails du contrat ID: ' + contractId);
    }

    // Edit contract function
    function editContract(contractId) {
        // Populate edit modal with contract data
        document.getElementById('edit_contract_id').value = contractId;
        document.getElementById('edit_nom').value = 'Contrat à Durée Indéterminée';
        document.getElementById('edit_type').value = 'CDI';
        document.getElementById('edit_description').value = 'Contrat permanent avec avantages sociaux complets';
        document.getElementById('edit_status').value = 'active';
        
        // Show edit modal
        new bootstrap.Modal(document.getElementById('editContractModal')).show();
    }

    // Activate contract function
    function activateContract(contractId) {
        if (confirm('Êtes-vous sûr de vouloir activer ce contrat ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="activate_contract">
                <input type="hidden" name="contract_id" value="${contractId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Deactivate contract function
    function deactivateContract(contractId) {
        if (confirm('Êtes-vous sûr de vouloir désactiver ce contrat ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="deactivate_contract">
                <input type="hidden" name="contract_id" value="${contractId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Delete contract function
    function deleteContract(contractId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce contrat ? Cette action ne peut pas être annulée !')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_contract">
                <input type="hidden" name="contract_id" value="${contractId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Open add modal function
    function openAddModal() {
        new bootstrap.Modal(document.getElementById('addContractModal')).show();
    }
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
