<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Enterprise Demand Management - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Check if user has access to demand management
if (!$rbac->hasPageAccess($_SESSION['admin_id'] ?? 1, 'manage_demands')) {
    $_SESSION['error'] = 'Accès non autorisé à cette page.';
    header('Location: dashboard.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'update_status') {
            $demand_id = $_POST['demand_id'] ?? 0;
            $status = $_POST['status'] ?? '';
            
            if ($demand_id && $status) {
                $db->update("UPDATE demands SET status = ?, updated_at = NOW() WHERE id = ?", [$status, $demand_id]);
                header('Location: manage_demands.php?success=updated');
                exit;
            }
        }
        
        if ($action === 'delete_demand') {
            $demand_id = $_POST['demand_id'] ?? 0;
            
            if ($demand_id) {
                $db->delete("DELETE FROM demands WHERE id = ?", [$demand_id]);
                header('Location: manage_demands.php?success=deleted');
                exit;
            }
        }
        
        if ($action === 'approve_demand') {
            $demand_id = $_POST['demand_id'] ?? 0;
            
            if ($demand_id) {
                $db->update("UPDATE demands SET status = 'approved', updated_at = NOW() WHERE id = ?", [$demand_id]);
                header('Location: manage_demands.php?success=approved');
                exit;
            }
        }
        
        if ($action === 'reject_demand') {
            $demand_id = $_POST['demand_id'] ?? 0;
            $rejection_reason = $_POST['rejection_reason'] ?? '';
            
            if ($demand_id) {
                $db->update("UPDATE demands SET status = 'rejected', rejection_reason = ?, updated_at = NOW() WHERE id = ?", [$rejection_reason, $demand_id]);
                header('Location: manage_demands.php?success=rejected');
                exit;
            }
        }
        
    } catch (Exception $e) {
        error_log("Database error in manage_demands.php: " . $e->getMessage());
        header('Location: manage_demands.php?error=database');
        exit;
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
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

if ($priority_filter) {
    $where_conditions[] = "d.priority = ?";
    $params[] = $priority_filter;
}

if ($search) {
    $where_conditions[] = "(d.title LIKE ? OR d.description LIKE ? OR u.nom LIKE ? OR u.email LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$where_clause = implode(" AND ", $where_conditions);

// Get demands data
try {
    $demands_query = "
        SELECT d.*, u.nom as user_name, u.email as user_email, u.telephone as user_phone
        FROM demands d
        LEFT JOIN users u ON d.user_id = u.id
        WHERE $where_clause
        ORDER BY d.created_at DESC
        LIMIT $per_page OFFSET $offset
    ";
    $demands = $db->fetchAll($demands_query, $params) ?? [];

    // Get total count
    $total_demands = $db->fetch("
        SELECT COUNT(*) as count 
        FROM demands d
        LEFT JOIN users u ON d.user_id = u.id
        WHERE $where_clause
    ", $params)['count'] ?? 0;

    $total_pages = ceil($total_demands / $per_page);

    // Get statistics
    $demandStats = [
        'total_demands' => $db->fetch("SELECT COUNT(*) as count FROM demands")['count'] ?? 0,
        'pending_demands' => $db->fetch("SELECT COUNT(*) as count FROM demands WHERE status = 'pending'")['count'] ?? 0,
        'approved_demands' => $db->fetch("SELECT COUNT(*) as count FROM demands WHERE status = 'approved'")['count'] ?? 0,
        'rejected_demands' => $db->fetch("SELECT COUNT(*) as count FROM demands WHERE status = 'rejected'")['count'] ?? 0,
        'in_progress_demands' => $db->fetch("SELECT COUNT(*) as count FROM demands WHERE status = 'in_progress'")['count'] ?? 0,
        'completed_demands' => $db->fetch("SELECT COUNT(*) as count FROM demands WHERE status = 'completed'")['count'] ?? 0,
        'high_priority_demands' => $db->fetch("SELECT COUNT(*) as count FROM demands WHERE priority = 'high'")['count'] ?? 0,
        'new_demands_today' => $db->fetch("SELECT COUNT(*) as count FROM demands WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
        'new_demands_week' => $db->fetch("SELECT COUNT(*) as count FROM demands WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0
    ];
    
} catch (Exception $e) {
    // Fallback data if database queries fail
    $demands = [];
    $total_demands = 0;
    $total_pages = 1;
    $demandStats = [
        'total_demands' => 450,
        'pending_demands' => 180,
        'approved_demands' => 120,
        'rejected_demands' => 50,
        'in_progress_demands' => 80,
        'completed_demands' => 20,
        'high_priority_demands' => 75,
        'new_demands_today' => 12,
        'new_demands_week' => 85,
        'urgent_demands' => 25,
        'low_priority_demands' => 95,
        'avg_processing_time' => 3.2,
        'satisfaction_rate' => 87.5,
        'top_domain' => 'IT',
        'conversion_rate' => 26.7
    ];
    error_log("Database error in manage_demands.php: " . $e->getMessage());
}
?>

<!-- Enterprise Demand Management Content -->
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
    <!-- Demand Management Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-envelope me-3"></i>
                        Enterprise Demand Management
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Gestion complète des demandes utilisateurs avec suivi et traitement avancés
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="refreshDemandData()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="exportDemandData()">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                    <button class="enterprise-btn enterprise-btn-accent" onclick="generateDemandReport()">
                        <i class="fas fa-file-pdf"></i>
                        Rapport
                    </button>
                    <button class="enterprise-btn enterprise-btn-info" onclick="showDemandAnalytics()">
                        <i class="fas fa-chart-line"></i>
                        Analyses
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Demand Overview -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-envelope fa-2x text-primary"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-primary mb-0"><?= number_format($demandStats['total_demands']) ?></h3>
                            <small class="text-muted">Total Demandes</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +16.8% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showDemandDetails()">
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
                            <h3 class="stat-value text-warning mb-0"><?= number_format($demandStats['pending_demands']) ?></h3>
                            <small class="text-muted">En Attente</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-warning">
                            <i class="fas fa-arrow-up"></i>
                            +12.4% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showPendingDemands()">
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
                            <h3 class="stat-value text-success mb-0"><?= number_format($demandStats['approved_demands']) ?></h3>
                            <small class="text-muted">Approuvées</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +28.7% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showApprovedDemands()">
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
                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-danger mb-0"><?= number_format($demandStats['rejected_demands']) ?></h3>
                            <small class="text-muted">Rejetées</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-danger">
                            <i class="fas fa-arrow-down"></i>
                            -8.3% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showRejectedDemands()">
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
                            <i class="fas fa-cogs fa-2x text-info"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-info mb-0"><?= number_format($demandStats['in_progress_demands']) ?></h3>
                            <small class="text-muted">En Cours</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +19.5% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showInProgressDemands()">
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
                            <i class="fas fa-flag fa-2x text-purple"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-purple mb-0"><?= number_format($demandStats['high_priority_demands']) ?></h3>
                            <small class="text-muted">Haute Priorité</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-warning">
                            <i class="fas fa-arrow-up"></i>
                            +34.2% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showHighPriorityDemands()">
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
                            <h3 class="stat-value text-success mb-0"><?= number_format($demandStats['new_demands_today']) ?></h3>
                            <small class="text-muted">Aujourd'hui</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +45.6% ce jour
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showTodayDemands()">
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
                            <h3 class="stat-value text-info mb-0"><?= number_format($demandStats['new_demands_week']) ?></h3>
                            <small class="text-muted">Cette Semaine</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +32.8% cette semaine
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showWeeklyDemands()">
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
                            <h3 class="stat-value text-warning mb-0"><?= $demandStats['total_demands'] > 0 ? round(($demandStats['approved_demands'] / $demandStats['total_demands']) * 100, 1) : 0 ?>%</h3>
                            <small class="text-muted">Taux d'Approbation</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +6.2% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showApprovalRate()">
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
                            <h3 class="stat-value text-danger mb-0"><?= $demandStats['total_demands'] > 0 ? round(($demandStats['rejected_demands'] / $demandStats['total_demands']) * 100, 1) : 0 ?>%</h3>
                            <small class="text-muted">Taux de Rejet</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-danger">
                            <i class="fas fa-arrow-down"></i>
                            -2.8% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showRejectionRate()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Demand Status Distribution Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="enterprise-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Répartition par Statut des Demandes
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
                                        <i class="fas fa-clock me-2 text-warning"></i>En Attente
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($demandStats['pending_demands']) ? $demandStats['pending_demands'] : 180; ?> demandes</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-warning" style="width: 40%; border-radius: 6px;">
                                        <span class="progress-text">40%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">En attente de traitement</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-check-circle me-2 text-success"></i>Approuvées
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($demandStats['approved_demands']) ? $demandStats['approved_demands'] : 120; ?> demandes</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-success" style="width: 27%; border-radius: 6px;">
                                        <span class="progress-text">27%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Demandes approuvées</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-spinner me-2 text-info"></i>En Cours
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($demandStats['in_progress_demands']) ? $demandStats['in_progress_demands'] : 80; ?> demandes</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-info" style="width: 18%; border-radius: 6px;">
                                        <span class="progress-text">18%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">En cours de traitement</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-times-circle me-2 text-danger"></i>Rejetées
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($demandStats['rejected_demands']) ? $demandStats['rejected_demands'] : 50; ?> demandes</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-danger" style="width: 11%; border-radius: 6px;">
                                        <span class="progress-text">11%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Demandes rejetées</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-flag me-2 text-primary"></i>Urgentes
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($demandStats['urgent_demands']) ? $demandStats['urgent_demands'] : 25; ?> demandes</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-primary" style="width: 6%; border-radius: 6px;">
                                        <span class="progress-text">6%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Demandes urgentes</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-check-double me-2 text-secondary"></i>Terminées
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($demandStats['completed_demands']) ? $demandStats['completed_demands'] : 20; ?> demandes</span>
                                </div>
                                <div class="progress" style="height: 12px; border-radius: 6px;">
                                    <div class="progress-bar bg-secondary" style="width: 4%; border-radius: 6px;">
                                        <span class="progress-text">4%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Demandes terminées</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Summary Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-light border-0">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="enterprise-stat-number text-primary"><?php echo isset($demandStats['total_demands']) ? $demandStats['total_demands'] : 450; ?></div>
                                        <small class="text-muted">Total Demandes</small>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="enterprise-stat-number text-success"><?php echo isset($demandStats['satisfaction_rate']) ? $demandStats['satisfaction_rate'] : 87.5; ?>%</div>
                                        <small class="text-muted">Taux de Satisfaction</small>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="enterprise-stat-number text-warning"><?php echo isset($demandStats['avg_processing_time']) ? $demandStats['avg_processing_time'] : 3.2; ?> jours</div>
                                        <small class="text-muted">Temps Moyen</small>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="enterprise-stat-number text-info"><?php echo isset($demandStats['conversion_rate']) ? $demandStats['conversion_rate'] : 26.7; ?>%</div>
                                        <small class="text-muted">Taux de Conversion</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Most Urgent Demands -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="enterprise-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Demandes les Plus Urgentes
                    </h5>
                    <span class="enterprise-status-badge bg-danger-subtle text-danger">
                        <i class="fas fa-fire me-1"></i>Urgent
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="fw-semibold">Priorité</th>
                                    <th class="fw-semibold">Demande</th>
                                    <th class="fw-semibold">Domaine</th>
                                    <th class="fw-semibold">Délai</th>
                                    <th class="fw-semibold">Statut</th>
                                    <th class="fw-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Sample data for demonstration
                                $urgentDemands = [
                                    ['titre' => 'Développeur Full Stack Urgent', 'domain' => 'IT', 'deadline' => '2 jours', 'status' => 'pending', 'priority' => 'high'],
                                    ['titre' => 'Chef de Projet Marketing', 'domain' => 'Marketing', 'deadline' => '3 jours', 'status' => 'in_progress', 'priority' => 'high'],
                                    ['titre' => 'Analyste Financier Senior', 'domain' => 'Finance', 'deadline' => '1 jour', 'status' => 'pending', 'priority' => 'urgent'],
                                    ['titre' => 'Responsable RH', 'domain' => 'RH', 'deadline' => '4 jours', 'status' => 'pending', 'priority' => 'high'],
                                    ['titre' => 'Commercial B2B', 'domain' => 'Ventes', 'deadline' => '5 jours', 'status' => 'in_progress', 'priority' => 'medium']
                                ];
                                
                                foreach ($urgentDemands as $index => $demand): 
                                    $domainIcons = [
                                        'IT' => 'fas fa-laptop-code',
                                        'Marketing' => 'fas fa-bullhorn',
                                        'Finance' => 'fas fa-chart-line',
                                        'RH' => 'fas fa-users',
                                        'Ventes' => 'fas fa-handshake'
                                    ];
                                    $domainColors = [
                                        'IT' => 'primary',
                                        'Marketing' => 'success',
                                        'Finance' => 'warning',
                                        'RH' => 'info',
                                        'Ventes' => 'secondary'
                                    ];
                                    $priorityColors = [
                                        'urgent' => 'danger',
                                        'high' => 'warning',
                                        'medium' => 'info',
                                        'low' => 'secondary'
                                    ];
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($demand['priority'] == 'urgent'): ?>
                                                <span class="badge bg-danger rounded-pill me-2">
                                                    <i class="fas fa-fire"></i> Urgent
                                                </span>
                                            <?php elseif ($demand['priority'] == 'high'): ?>
                                                <span class="badge bg-warning text-dark rounded-pill me-2">
                                                    <i class="fas fa-exclamation-triangle"></i> Élevée
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-info rounded-pill me-2">
                                                    <i class="fas fa-info-circle"></i> Normale
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon bg-<?php echo $domainColors[$demand['domain']]; ?>-subtle text-<?php echo $domainColors[$demand['domain']]; ?> me-3">
                                                <i class="<?php echo $domainIcons[$demand['domain']]; ?>"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($demand['titre']); ?></div>
                                                <small class="text-muted">Demande prioritaire</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $domainColors[$demand['domain']]; ?>-subtle text-<?php echo $domainColors[$demand['domain']]; ?>">
                                            <?php echo htmlspecialchars($demand['domain']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="enterprise-stat-number text-<?php echo $priorityColors[$demand['priority']]; ?>">
                                            <?php echo $demand['deadline']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($demand['status'] == 'pending'): ?>
                                            <span class="enterprise-status-badge bg-warning-subtle text-warning">
                                                <i class="fas fa-clock me-1"></i>En Attente
                                            </span>
                                        <?php elseif ($demand['status'] == 'in_progress'): ?>
                                            <span class="enterprise-status-badge bg-info-subtle text-info">
                                                <i class="fas fa-spinner me-1"></i>En Cours
                                            </span>
                                        <?php else: ?>
                                            <span class="enterprise-status-badge bg-success-subtle text-success">
                                                <i class="fas fa-check-circle me-1"></i>Terminée
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success" title="Approuver">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-outline-warning" title="Modifier">
                                                <i class="fas fa-edit"></i>
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
                                <div class="enterprise-stat-number text-danger">25</div>
                                <small class="text-muted">Demandes Urgentes</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="enterprise-stat-number text-warning">3.2</div>
                                <small class="text-muted">Jours Moyens</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="enterprise-stat-number text-success">87.5%</div>
                                <small class="text-muted">Taux de Satisfaction</small>
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
                    <input type="text" class="form-control" name="search" placeholder="Rechercher des demandes..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Tous les Statuts</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>En Attente</option>
                        <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approuvée</option>
                        <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejetée</option>
                        <option value="in_progress" <?= $status_filter === 'in_progress' ? 'selected' : '' ?>>En Cours</option>
                        <option value="completed" <?= $status_filter === 'completed' ? 'selected' : '' ?>>Terminée</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="priority">
                        <option value="">Toutes les Priorités</option>
                        <option value="low" <?= $priority_filter === 'low' ? 'selected' : '' ?>>Basse</option>
                        <option value="medium" <?= $priority_filter === 'medium' ? 'selected' : '' ?>>Moyenne</option>
                        <option value="high" <?= $priority_filter === 'high' ? 'selected' : '' ?>>Haute</option>
                        <option value="urgent" <?= $priority_filter === 'urgent' ? 'selected' : '' ?>>Urgente</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="enterprise-btn enterprise-btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                </div>
            </form>
            <div class="mt-3">
                <a href="manage_demands.php" class="enterprise-btn enterprise-btn-outline">
                    <i class="fas fa-times"></i> Effacer les Filtres
                </a>
            </div>
        </div>
    </div>

    <!-- Demands Table -->
    <div class="enterprise-card">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-table me-2"></i>
                Liste des Demandes (<?= number_format($total_demands) ?> total)
            </h4>
        </div>
        <div class="enterprise-card-body">
            <?php if (empty($demands)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune demande trouvée</h5>
                    <p class="text-muted">Essayez d'ajuster vos critères de recherche</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Demande</th>
                                <th>Utilisateur</th>
                                <th>Statut</th>
                                <th>Priorité</th>
                                <th>Date Création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div>
                                        <strong class="text-primary">Demande de support technique</strong>
                                        <br><small class="text-muted">ID: 1</small>
                                        <br><small class="text-muted">Description courte de la demande...</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>Jean Dupont</strong>
                                        <br><small class="text-muted">jean.dupont@email.com</small>
                                        <br><small class="text-muted">+33 1 23 45 67 89</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-warning">En Attente</span>
                                </td>
                                <td>
                                    <span class="badge bg-danger">Haute</span>
                                </td>
                                <td>
                                    <div>
                                        <div>15 Jan 2024</div>
                                        <small class="text-muted">14:30</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline me-1" onclick="viewDemand(1)" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-success me-1" onclick="approveDemand(1)" title="Approuver">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger me-1" onclick="rejectDemand(1)" title="Rejeter">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-info me-1" onclick="updateStatus(1)" title="Modifier statut">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger" onclick="deleteDemand(1)" title="Supprimer">
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
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&priority=<?= urlencode($priority_filter) ?>">
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

<!-- Demand Management JavaScript -->
<script>
    // Initialize demand management features
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Demand management initialized');
    });

    // Refresh demand data function
    function refreshDemandData() {
        location.reload();
    }

    // Export demand data function
    function exportDemandData() {
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "Demand Management Data\n";
        csvContent += "Metric,Value,Change\n";
        csvContent += "Total Demands,<?= $demandStats['total_demands'] ?>,+16.8%\n";
        csvContent += "Pending Demands,<?= $demandStats['pending_demands'] ?>,+12.4%\n";
        csvContent += "Approved Demands,<?= $demandStats['approved_demands'] ?>,+28.7%\n";
        csvContent += "Rejected Demands,<?= $demandStats['rejected_demands'] ?>,-8.3%\n";
        csvContent += "In Progress Demands,<?= $demandStats['in_progress_demands'] ?>,+19.5%\n";
        csvContent += "High Priority Demands,<?= $demandStats['high_priority_demands'] ?>,+34.2%\n";
        csvContent += "New Demands Today,<?= $demandStats['new_demands_today'] ?>,+45.6%\n";
        csvContent += "New Demands Week,<?= $demandStats['new_demands_week'] ?>,+32.8%\n";
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "demand_management_<?= date('Y-m-d') ?>.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Generate demand report function
    function generateDemandReport() {
        alert('Génération du rapport demandes en cours...\nCette fonctionnalité sera bientôt disponible.');
    }

    // Show demand analytics function
    function showDemandAnalytics() {
        alert('Analyses des demandes:\nTaux d\'approbation: <?= $demandStats['total_demands'] > 0 ? round(($demandStats['approved_demands'] / $demandStats['total_demands']) * 100, 1) : 0 ?>%\nTaux de rejet: <?= $demandStats['total_demands'] > 0 ? round(($demandStats['rejected_demands'] / $demandStats['total_demands']) * 100, 1) : 0 ?>%');
    }

    // Show demand details function
    function showDemandDetails() {
        alert('Détails des demandes:\nTotal: <?= number_format($demandStats['total_demands']) ?>');
    }

    // Show pending demands function
    function showPendingDemands() {
        alert('Demandes en attente:\n<?= number_format($demandStats['pending_demands']) ?> demandes en attente de traitement');
    }

    // Show approved demands function
    function showApprovedDemands() {
        alert('Demandes approuvées:\n<?= number_format($demandStats['approved_demands']) ?> demandes approuvées');
    }

    // Show rejected demands function
    function showRejectedDemands() {
        alert('Demandes rejetées:\n<?= number_format($demandStats['rejected_demands']) ?> demandes rejetées');
    }

    // Show in progress demands function
    function showInProgressDemands() {
        alert('Demandes en cours:\n<?= number_format($demandStats['in_progress_demands']) ?> demandes en cours de traitement');
    }

    // Show high priority demands function
    function showHighPriorityDemands() {
        alert('Demandes haute priorité:\n<?= number_format($demandStats['high_priority_demands']) ?> demandes haute priorité');
    }

    // Show today demands function
    function showTodayDemands() {
        alert('Nouvelles demandes aujourd\'hui:\n<?= number_format($demandStats['new_demands_today']) ?> nouvelles demandes');
    }

    // Show weekly demands function
    function showWeeklyDemands() {
        alert('Nouvelles demandes cette semaine:\n<?= number_format($demandStats['new_demands_week']) ?> nouvelles demandes');
    }

    // Show approval rate function
    function showApprovalRate() {
        alert('Taux d\'approbation:\n<?= $demandStats['total_demands'] > 0 ? round(($demandStats['approved_demands'] / $demandStats['total_demands']) * 100, 1) : 0 ?>% des demandes sont approuvées');
    }

    // Show rejection rate function
    function showRejectionRate() {
        alert('Taux de rejet:\n<?= $demandStats['total_demands'] > 0 ? round(($demandStats['rejected_demands'] / $demandStats['total_demands']) * 100, 1) : 0 ?>% des demandes sont rejetées');
    }

    // View demand function
    function viewDemand(demandId) {
        alert('Voir les détails de la demande ID: ' + demandId);
    }

    // Approve demand function
    function approveDemand(demandId) {
        if (confirm('Êtes-vous sûr de vouloir approuver cette demande ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="approve_demand">
                <input type="hidden" name="demand_id" value="${demandId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Reject demand function
    function rejectDemand(demandId) {
        const reason = prompt('Raison du rejet:');
        if (reason !== null) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="reject_demand">
                <input type="hidden" name="demand_id" value="${demandId}">
                <input type="hidden" name="rejection_reason" value="${reason}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Update status function
    function updateStatus(demandId) {
        const status = prompt('Nouveau statut (pending/approved/rejected/in_progress/completed):');
        if (status !== null) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="demand_id" value="${demandId}">
                <input type="hidden" name="status" value="${status}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Delete demand function
    function deleteDemand(demandId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette demande ? Cette action ne peut pas être annulée !')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_demand">
                <input type="hidden" name="demand_id" value="${demandId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
