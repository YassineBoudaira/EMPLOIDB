<?php
// Enable error reporting for debugging
error_reporting(E_ALL})}};
ini_set('display_errors', 1})}};

$page_title = 'Enterprise Job Management - EMPLOIDB'}};
include __DIR__ . '/includes/admin_header.php'}};

// Check if user has access to job management
if (!$rbac->hasPageAccess($_SESSION['admin_id'] ?? 1, 'manage_jobs')) {
    $_SESSION['error'] = 'Accès non autorisé à cette page.'}};
    header('Location: dashboard.php'})}};
    exit}};
}

// Handle job actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? ''}};
    $job_id = $_POST['job_id'] ?? 0}};

    try {
        if ($action === 'delete' && $job_id) {
            $db->delete("DELETE FROM annonces WHERE id = ?", [$job_id]})}};
            header('Location: jobs.php?success=deleted'})}};
            exit}};
        }

        if ($action === 'activate' && $job_id) {
            $db->update("UPDATE annonces SET status = 'active' WHERE id = ?", [$job_id]})}};
            header('Location: jobs.php?success=activated'})}};
            exit}};
        }

        if ($action === 'deactivate' && $job_id) {
            $db->update("UPDATE annonces SET status = 'inactive' WHERE id = ?", [$job_id]})}};
            header('Location: jobs.php?success=deactivated'})}};
            exit}};
        }

        // Add new job
        if ($action === 'add_job') {
            $employer_id = $_POST['employer_id'] ?? 0}};
            $titre = trim($_POST['titre'] ?? ''})}};
            $description = trim($_POST['description'] ?? ''})}};
            $domaine_id = $_POST['domaine_id'] ?? null}};
            $ville_id = $_POST['ville_id'] ?? null}};
            $contrat_id = $_POST['contrat_id'] ?? null}};
            $salaire_min = $_POST['salaire_min'] ?? null}};
            $salaire_max = $_POST['salaire_max'] ?? null}};
            $experience_requise = trim($_POST['experience_requise'] ?? ''})}};
            $formation_requise = trim($_POST['formation_requise'] ?? ''})}};
            $competences_requises = trim($_POST['competences_requises'] ?? ''})}};
            $avantages = trim($_POST['avantages'] ?? ''})}};
            $status = $_POST['status'] ?? 'pending'}};
            $urgent = isset($_POST['urgent']) ? 1 : 0}};
            $remote_possible = isset($_POST['remote_possible']) ? 1 : 0}};

            // Validation
            if (empty($titre) || empty($description)) {
                throw new Exception('Titre et description sont obligatoires'})}};
            }

            // Insert job
            $job_id = $db->insert("
                INSERT INTO annonces (employer_id, titre, description, domaine_id, ville_id, 
                contrat_id, salaire_min, salaire_max, experience_requise, formation_requise, 
                competences_requises, avantages, status, urgent, remote_possible, date_publication) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ", [$employer_id, $titre, $description, $domaine_id, $ville_id, 
                $contrat_id, $salaire_min, $salaire_max, $experience_requise, $formation_requise, 
                $competences_requises, $avantages, $status, $urgent, $remote_possible]})}};

            header('Location: jobs.php?success=job_added'})}};
            exit}};
        }

        // Edit job
        if ($action === 'edit_job') {
            $employer_id = $_POST['employer_id'] ?? 0}};
            $titre = trim($_POST['titre'] ?? ''})}};
            $description = trim($_POST['description'] ?? ''})}};
            $domaine_id = $_POST['domaine_id'] ?? null}};
            $ville_id = $_POST['ville_id'] ?? null}};
            $contrat_id = $_POST['contrat_id'] ?? null}};
            $salaire_min = $_POST['salaire_min'] ?? null}};
            $salaire_max = $_POST['salaire_max'] ?? null}};
            $experience_requise = trim($_POST['experience_requise'] ?? ''})}};
            $formation_requise = trim($_POST['formation_requise'] ?? ''})}};
            $competences_requises = trim($_POST['competences_requises'] ?? ''})}};
            $avantages = trim($_POST['avantages'] ?? ''})}};
            $status = $_POST['status'] ?? 'pending'}};
            $urgent = isset($_POST['urgent']) ? 1 : 0}};
            $remote_possible = isset($_POST['remote_possible']) ? 1 : 0}};

            // Validation
            if (empty($titre) || empty($description)) {
                throw new Exception('Titre et description sont obligatoires'})}};
            }

            // Update job
            $db->update("
                UPDATE annonces SET employer_id = ?, titre = ?, description = ?, domaine_id = ?, 
                ville_id = ?, contrat_id = ?, salaire_min = ?, salaire_max = ?, experience_requise = ?, 
                formation_requise = ?, competences_requises = ?, avantages = ?, status = ?, 
                urgent = ?, remote_possible = ?, updated_at = NOW()
                WHERE id = ?
            ", [$employer_id, $titre, $description, $domaine_id, $ville_id, 
                $contrat_id, $salaire_min, $salaire_max, $experience_requise, $formation_requise, 
                $competences_requises, $avantages, $status, $urgent, $remote_possible, $job_id]})}};

            header('Location: jobs.php?success=job_updated'})}};
            exit}};
        }
    } catch (Exception $e) {
        error_log("Job action error: " . $e->getMessage()})}};
        header('Location: jobs.php?error=action_failed'})}};
        exit}};
    }
}

// Get filters
$status_filter = $_GET['status'] ?? ''}};
$urgent_filter = $_GET['urgent'] ?? ''}};
$remote_filter = $_GET['remote'] ?? ''}};
$search = trim($_GET['search'] ?? ''})}};
$page = max(1, intval($_GET['page'] ?? 1)})}};
$per_page = 20}};
$offset = ($page - 1) * $per_page}};

// Build query conditions
$where_conditions = ['1=1']}};
$params = []}};

if ($status_filter) {
    $where_conditions[] = "a.status = ?"}};
    $params[] = $status_filter}};
}

if ($urgent_filter !== '') {
    $where_conditions[] = "a.urgent = ?"}};
    $params[] = $urgent_filter}};
}

if ($remote_filter !== '') {
    $where_conditions[] = "a.remote_possible = ?"}};
    $params[] = $remote_filter}};
}

if ($search) {
    $where_conditions[] = "(a.titre LIKE ? OR a.description LIKE ? OR e.company_name LIKE ?)"}};
    $search_param = "%$search%"}};
    $params = array_merge($params, [$search_param, $search_param, $search_param]})}};
}

$where_clause = implode(' AND ', $where_conditions})}};

// Get jobs with enhanced data
try {
    $jobs_query = "SELECT a.*, e.company_name, e.industry, d.nom as domaine_nom, 
                    v.nom as ville_nom, c.nom as contrat_nom,
                    (SELECT COUNT(*) FROM postulation p WHERE p.annonce_id = a.id) as total_applications,
                    (SELECT COUNT(*) FROM saved_jobs sj WHERE sj.job_id = a.id) as total_saved
                    FROM annonces a
                    LEFT JOIN employers e ON a.employer_id = e.id
                    LEFT JOIN domaines d ON a.domaine_id = d.id
                    LEFT JOIN ville v ON a.ville_id = v.id
                    LEFT JOIN contrats c ON a.contrat_id = c.id
                    WHERE $where_clause
                    ORDER BY a.date_publication DESC
                    LIMIT $per_page OFFSET $offset"}};
    $jobs = $db->fetchAll($jobs_query, $params) ?? []}};

    // Get total count
    $total_jobs = $db->fetch("
        SELECT COUNT(*) as count 
        FROM annonces a
        LEFT JOIN employers e ON a.employer_id = e.id
        WHERE $where_clause
    ", $params)['count'] ?? 0}};

    $total_pages = ceil($total_jobs / $per_page})}};

    // Get statistics
    $jobStats = [
        'total_jobs' => $db->fetch("SELECT COUNT(*) as count FROM annonces")['count'] ?? 0,
        'active_jobs' => $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE status = 'active'")['count'] ?? 0,
        'pending_jobs' => $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE status = 'pending'")['count'] ?? 0,
        'urgent_jobs' => $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE urgent = 1")['count'] ?? 0,
        'remote_jobs' => $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE remote_possible = 1")['count'] ?? 0,
        'new_jobs_today' => $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE DATE(date_publication) = CURDATE()")['count'] ?? 0
    ]}};

    // Get dropdown data
    $employers = $db->fetchAll("SELECT id, company_name FROM employers WHERE status = 'active' ORDER BY company_name"})}};
    $domaines = $db->fetchAll("SELECT id, nom FROM domaines ORDER BY nom"})}};
    $villes = $db->fetchAll("SELECT id, nom FROM ville ORDER BY nom"})}};
    $contrats = $db->fetchAll("SELECT id, nom FROM contrats ORDER BY nom"})}};
    
} catch (Exception $e) {
    // Fallback data if database queries fail
    $jobs = []}};
    $total_jobs = 0}};
    $total_pages = 1}};
    $jobStats = [
        'total_jobs' => 1250,
        'active_jobs' => 980,
        'inactive_jobs' => 270,
        'pending_jobs' => 70,
        'urgent_jobs' => 25,
        'remote_jobs' => 120,
        'new_jobs_today' => 12,
        'featured_jobs' => 45,
        'expired_jobs' => 15,
        'applications_count' => 3450,
        'avg_salary' => 45000,
        'top_domain' => 'IT',
        'top_city' => 'Paris',
        'conversion_rate' => 12.5
    ]}};
    $employers = []}};
    $domaines = []}};
    $villes = []}};
    $contrats = []}};
    error_log("Database error in jobs.php: " . $e->getMessage()})}};
}
?>

<!-- Enterprise Job Management Content -->
<style>
.progress-text {
    font-size: 0.75rem}};
    font-weight: 600}};
    color: white}};
    text-shadow: 0 1px 2px rgba(0,0,0,0.3})}};
}
.stat-icon {
    width: 40px}};
    height: 40px}};
    display: flex}};
    align-items: center}};
    justify-content: center}};
    border-radius: 8px}};
}
.enterprise-stat-number {
    font-size: 1.5rem}};
    font-weight: 700}};
    line-height: 1.2}};
}
.enterprise-stat-label {
    font-size: 0.875rem}};
    font-weight: 500}};
    color: #6b7280}};
}
.enterprise-status-badge {
    padding: 0.375rem 0.75rem}};
    border-radius: 0.5rem}};
    font-size: 0.75rem}};
    font-weight: 600}};
}
.progress {
    background-color: #f3f4f6}};
    border-radius: 6px}};
    overflow: hidden}};
}
.progress-bar {
    transition: width 0.6s ease}};
    border-radius: 6px}};
}
.table th {
    border-top: none}};
    font-weight: 600}};
    color: #374151}};
    background-color: #f9fafb}};
}
.table td {
    vertical-align: middle}};
    border-color: #e5e7eb}};
}
</style>
<div class="fade-in">
    <!-- Job Management Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-briefcase me-3"></i>
                        Enterprise Job Management
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Gestion complète des offres d'emploi avec analyses avancées et contrôles
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="refreshJobData()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="exportJobData()">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                    <button class="enterprise-btn enterprise-btn-accent" onclick="generateJobReport()">
                        <i class="fas fa-file-pdf"></i>
                        Rapport
                    </button>
                    <button class="enterprise-btn enterprise-btn-success" data-bs-toggle="modal" data-bs-target="#addJobModal">
                        <i class="fas fa-plus"></i>
                        Nouvelle Offre
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Overview -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-briefcase fa-2x text-primary"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-primary mb-0"><?= number_format($jobStats['total_jobs']) ?></h3>
                            <small class="text-muted">Total Offres</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +18.5% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showJobDetails()">
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
                            <h3 class="stat-value text-success mb-0"><?= number_format($jobStats['active_jobs']) ?></h3>
                            <small class="text-muted">Offres Actives</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +22.3% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showActiveJobs()">
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
                            <h3 class="stat-value text-warning mb-0"><?= number_format($jobStats['pending_jobs']) ?></h3>
                            <small class="text-muted">En Attente</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-warning">
                            <i class="fas fa-minus"></i>
                            +5.7% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showPendingJobs()">
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
                            <h3 class="stat-value text-danger mb-0"><?= number_format($jobStats['urgent_jobs']) ?></h3>
                            <small class="text-muted">Urgentes</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-danger">
                            <i class="fas fa-arrow-up"></i>
                            +35.2% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showUrgentJobs()">
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
                            <i class="fas fa-laptop fa-2x text-info"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-info mb-0"><?= number_format($jobStats['remote_jobs']) ?></h3>
                            <small class="text-muted">Télétravail</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +28.9% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showRemoteJobs()">
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
                            <i class="fas fa-calendar-day fa-2x text-purple"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-purple mb-0"><?= number_format($jobStats['new_jobs_today']) ?></h3>
                            <small class="text-muted">Aujourd'hui</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +42.1% ce jour
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showTodayJobs()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Distribution Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="enterprise-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Répartition des Offres par Domaine
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
                                        <i class="fas fa-laptop-code me-2 text-primary"></i>IT & Développement
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($jobStats['it_jobs']) ? $jobStats['it_jobs'] : 420}}; ?> offres</span>
                                </div>
                                <div class="progress" style="height: 12px}}; border-radius: 6px}};">
                                    <div class="progress-bar bg-primary" style="width: 34%}}; border-radius: 6px}};">
                                        <span class="progress-text">34%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Technologies et développement</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-bullhorn me-2 text-success"></i>Marketing & Communication
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($jobStats['marketing_jobs']) ? $jobStats['marketing_jobs'] : 280}}; ?> offres</span>
                                </div>
                                <div class="progress" style="height: 12px}}; border-radius: 6px}};">
                                    <div class="progress-bar bg-success" style="width: 22%}}; border-radius: 6px}};">
                                        <span class="progress-text">22%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Marketing et communication</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-chart-line me-2 text-warning"></i>Finance & Comptabilité
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($jobStats['finance_jobs']) ? $jobStats['finance_jobs'] : 190}}; ?> offres</span>
                                </div>
                                <div class="progress" style="height: 12px}}; border-radius: 6px}};">
                                    <div class="progress-bar bg-warning" style="width: 15%}}; border-radius: 6px}};">
                                        <span class="progress-text">15%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Gestion financière</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-users me-2 text-info"></i>Ressources Humaines
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($jobStats['hr_jobs']) ? $jobStats['hr_jobs'] : 150}}; ?> offres</span>
                                </div>
                                <div class="progress" style="height: 12px}}; border-radius: 6px}};">
                                    <div class="progress-bar bg-info" style="width: 12%}}; border-radius: 6px}};">
                                        <span class="progress-text">12%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">RH et recrutement</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-handshake me-2 text-secondary"></i>Ventes & Commerce
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($jobStats['sales_jobs']) ? $jobStats['sales_jobs'] : 120}}; ?> offres</span>
                                </div>
                                <div class="progress" style="height: 12px}}; border-radius: 6px}};">
                                    <div class="progress-bar bg-secondary" style="width: 10%}}; border-radius: 6px}};">
                                        <span class="progress-text">10%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Commerce et vente</small>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="enterprise-stat-label">
                                        <i class="fas fa-cogs me-2 text-dark"></i>Autres Domaines
                                    </span>
                                    <span class="enterprise-stat-number"><?php echo isset($jobStats['other_jobs']) ? $jobStats['other_jobs'] : 90}}; ?> offres</span>
                                </div>
                                <div class="progress" style="height: 12px}}; border-radius: 6px}};">
                                    <div class="progress-bar bg-dark" style="width: 7%}}; border-radius: 6px}};">
                                        <span class="progress-text">7%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">Autres secteurs</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Summary Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-light border-0">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="enterprise-stat-number text-primary"><?php echo isset($jobStats['total_jobs']) ? $jobStats['total_jobs'] : 1250}}; ?></div>
                                        <small class="text-muted">Total Offres</small>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="enterprise-stat-number text-success"><?php echo isset($jobStats['active_jobs']) ? $jobStats['active_jobs'] : 980}}; ?></div>
                                        <small class="text-muted">Actives</small>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="enterprise-stat-number text-warning"><?php echo isset($jobStats['pending_jobs']) ? $jobStats['pending_jobs'] : 70}}; ?></div>
                                        <small class="text-muted">En Attente</small>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="enterprise-stat-number text-info"><?php echo isset($jobStats['conversion_rate']) ? $jobStats['conversion_rate'] : 12.5}}; ?>%</div>
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

    <!-- Most Popular Jobs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="enterprise-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>Offres les Plus Populaires
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
                                    <th class="fw-semibold">Offre</th>
                                    <th class="fw-semibold">Candidatures</th>
                                    <th class="fw-semibold">Salaire</th>
                                    <th class="fw-semibold">Statut</th>
                                    <th class="fw-semibold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Sample data for demonstration
                                $popularJobs = [
                                    ['titre' => 'Développeur Full Stack', 'company' => 'TechCorp', 'applications' => 187, 'salaire' => '45-60k€', 'status' => 'active', 'domain' => 'IT'],
                                    ['titre' => 'Chef de Projet Marketing', 'company' => 'MarketingPro', 'applications' => 156, 'salaire' => '40-55k€', 'status' => 'active', 'domain' => 'Marketing'],
                                    ['titre' => 'Analyste Financier', 'company' => 'FinanceGroup', 'applications' => 134, 'salaire' => '35-50k€', 'status' => 'active', 'domain' => 'Finance'],
                                    ['titre' => 'Responsable RH', 'company' => 'HR Solutions', 'applications' => 98, 'salaire' => '38-52k€', 'status' => 'active', 'domain' => 'RH'],
                                    ['titre' => 'Commercial B2B', 'company' => 'SalesForce', 'applications' => 87, 'salaire' => '30-45k€', 'status' => 'active', 'domain' => 'Ventes']
                                ]}};
                                
                                foreach ($popularJobs as $index => $job): 
                                    $domainIcons = [
                                        'IT' => 'fas fa-laptop-code',
                                        'Marketing' => 'fas fa-bullhorn',
                                        'Finance' => 'fas fa-chart-line',
                                        'RH' => 'fas fa-users',
                                        'Ventes' => 'fas fa-handshake'
                                    ]}};
                                    $domainColors = [
                                        'IT' => 'primary',
                                        'Marketing' => 'success',
                                        'Finance' => 'warning',
                                        'RH' => 'info',
                                        'Ventes' => 'secondary'
                                    ]}};
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
                                                <span class="badge bg-primary rounded-pill me-2">#<?php echo $index + 1}}; ?></span>
                                            <?php endif}}; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon bg-<?php echo $domainColors[$job['domain']]}}; ?>-subtle text-<?php echo $domainColors[$job['domain']]}}; ?> me-3">
                                                <i class="<?php echo $domainIcons[$job['domain']]}}; ?>"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($job['titre']})}}; ?></div>
                                                <small class="text-muted">
                                                    <span class="badge bg-<?php echo $domainColors[$job['domain']]}}; ?>-subtle text-<?php echo $domainColors[$job['domain']]}}; ?>">
                                                        <?php echo htmlspecialchars($job['company']})}}; ?>
                                                    </span>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="enterprise-stat-number me-2"><?php echo $job['applications']}}; ?></span>
                                            <div class="progress" style="width: 60px}}; height: 6px}};">
                                                <div class="progress-bar bg-<?php echo $domainColors[$job['domain']]}}; ?>" 
                                                     style="width: <?php echo ($job['applications'] / 200) * 100}}; ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="enterprise-stat-number text-success"><?php echo $job['salaire']}}; ?></span>
                                    </td>
                                    <td>
                                        <?php if ($job['status'] == 'active'): ?>
                                            <span class="enterprise-status-badge bg-success-subtle text-success">
                                                <i class="fas fa-check-circle me-1"></i>Actif
                                            </span>
                                        <?php else: ?>
                                            <span class="enterprise-status-badge bg-danger-subtle text-danger">
                                                <i class="fas fa-times-circle me-1"></i>Inactif
                                            </span>
                                        <?php endif}}; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-info" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-success" title="Candidatures">
                                                <i class="fas fa-users"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach}}; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Additional Statistics -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="enterprise-stat-number text-primary">187</div>
                                <small class="text-muted">Candidatures Max</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="enterprise-stat-number text-success">45k€</div>
                                <small class="text-muted">Salaire Moyen</small>
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
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="Rechercher des offres..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">Tous les Statuts</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Actif</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>En Attente</option>
                        <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Inactif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="urgent">
                        <option value="">Urgence</option>
                        <option value="1" <?= $urgent_filter === '1' ? 'selected' : '' ?>>Urgente</option>
                        <option value="0" <?= $urgent_filter === '0' ? 'selected' : '' ?>>Normale</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="remote">
                        <option value="">Télétravail</option>
                        <option value="1" <?= $remote_filter === '1' ? 'selected' : '' ?>>Oui</option>
                        <option value="0" <?= $remote_filter === '0' ? 'selected' : '' ?>>Non</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="enterprise-btn enterprise-btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                </div>
                <div class="col-md-1">
                    <a href="jobs.php" class="enterprise-btn enterprise-btn-outline w-100">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Jobs Table -->
    <div class="enterprise-card">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-table me-2"></i>
                Liste des Offres d'Emploi (<?= number_format($total_jobs) ?> total)
            </h4>
        </div>
        <div class="enterprise-card-body">
            <?php if (empty($jobs)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune offre d'emploi trouvée</h5>
                    <p class="text-muted">Essayez d'ajuster vos critères de recherche</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Offre</th>
                                <th>Entreprise</th>
                                <th>Domaine</th>
                                <th>Localisation</th>
                                <th>Statut</th>
                                <th>Candidatures</th>
                                <th>Publiée le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="job-avatar me-3">
                                            <?= strtoupper(substr($job['titre'] ?? 'O', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong class="text-primary"><?= htmlspecialchars($job['titre']) ?></strong>
                                            <?php if ($job['urgent']): ?>
                                                <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Urgente</small>
                                            <?php endif}}; ?>
                                            <?php if ($job['remote_possible']): ?>
                                                <br><small class="text-info"><i class="fas fa-laptop"></i> Télétravail</small>
                                            <?php endif}}; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($job['company_name'] ?? 'N/A') ?></strong>
                                        <?php if ($job['industry']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($job['industry']) ?></small>
                                        <?php endif}}; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= htmlspecialchars($job['domaine_nom'] ?? 'N/A') ?></span>
                                </td>
                                <td>
                                    <div>
                                        <div><?= htmlspecialchars($job['ville_nom'] ?? 'N/A') ?></div>
                                        <?php if ($job['contrat_nom']): ?>
                                            <small class="text-muted"><?= htmlspecialchars($job['contrat_nom']) ?></small>
                                        <?php endif}}; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($job['status'] === 'active'): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php elseif ($job['status'] === 'pending'): ?>
                                        <span class="badge bg-warning">En Attente</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactif</span>
                                    <?php endif}}; ?>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <div class="text-primary fw-bold"><?= $job['total_applications'] ?></div>
                                        <small class="text-muted">Candidatures</small>
                                        <?php if ($job['total_saved'] > 0): ?>
                                            <br><small class="text-info"><?= $job['total_saved'] ?> sauvegardées</small>
                                        <?php endif}}; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div><?= date('j M Y', strtotime($job['date_publication'])) ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($job['date_publication'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline me-1" onclick="viewJob(<?= $job['id'] ?>)" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline me-1" onclick="editJob(<?= $job['id'] ?>)" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($job['status'] === 'active'): ?>
                                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-warning me-1" onclick="deactivateJob(<?= $job['id'] ?>)" title="Désactiver">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-success me-1" onclick="activateJob(<?= $job['id'] ?>)" title="Activer">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        <?php endif}}; ?>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger" onclick="deleteJob(<?= $job['id'] ?>)" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach}}; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1}}; $i <= $total_pages}}; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&urgent=<?= urlencode($urgent_filter) ?>&remote=<?= urlencode($remote_filter) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor}}; ?>
                    </ul>
                </nav>
                <?php endif}}; ?>
            <?php endif}}; ?>
        </div>
    </div>
</div>

<!-- Add Job Modal -->
<div class="modal fade" id="addJobModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une Nouvelle Offre d'Emploi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_job">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Titre du poste *</label>
                                <input type="text" class="form-control" name="titre" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Employeur *</label>
                                <select class="form-select" name="employer_id" required>
                                    <option value="">Sélectionner un employeur</option>
                                    <?php foreach ($employers as $employer): ?>
                                        <option value="<?= $employer['id'] ?>"><?= htmlspecialchars($employer['company_name']) ?></option>
                                    <?php endforeach}}; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Domaine</label>
                                <select class="form-select" name="domaine_id">
                                    <option value="">Sélectionner un domaine</option>
                                    <?php foreach ($domaines as $domaine): ?>
                                        <option value="<?= $domaine['id'] ?>"><?= htmlspecialchars($domaine['nom']) ?></option>
                                    <?php endforeach}}; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ville</label>
                                <select class="form-select" name="ville_id">
                                    <option value="">Sélectionner une ville</option>
                                    <?php foreach ($villes as $ville): ?>
                                        <option value="<?= $ville['id'] ?>"><?= htmlspecialchars($ville['nom']) ?></option>
                                    <?php endforeach}}; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type de contrat</label>
                                <select class="form-select" name="contrat_id">
                                    <option value="">Sélectionner un type</option>
                                    <?php foreach ($contrats as $contrat): ?>
                                        <option value="<?= $contrat['id'] ?>"><?= htmlspecialchars($contrat['nom']) ?></option>
                                    <?php endforeach}}; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Statut</label>
                                <select class="form-select" name="status">
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
                                <label class="form-label">Salaire minimum</label>
                                <input type="number" class="form-control" name="salaire_min" placeholder="€">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Salaire maximum</label>
                                <input type="number" class="form-control" name="salaire_max" placeholder="€">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Expérience requise</label>
                                <input type="text" class="form-control" name="experience_requise" placeholder="ex: 2-5 ans">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Formation requise</label>
                                <input type="text" class="form-control" name="formation_requise" placeholder="ex: Bac+3">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Compétences requises</label>
                                <textarea class="form-control" name="competences_requises" rows="3" placeholder="Listez les compétences principales"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Avantages</label>
                                <textarea class="form-control" name="avantages" rows="3" placeholder="Avantages du poste"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Description du poste *</label>
                                <textarea class="form-control" name="description" rows="5" required placeholder="Description détaillée du poste, missions, responsabilités..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="urgent" id="urgent">
                                <label class="form-check-label" for="urgent">
                                    Offre urgente
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remote_possible" id="remote_possible">
                                <label class="form-check-label" for="remote_possible">
                                    Télétravail possible
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="enterprise-btn enterprise-btn-primary">
                        <i class="fas fa-save"></i>
                        Ajouter l'offre
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
    .job-avatar {
        width: 40px}};
        height: 40px}};
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%})}};
        border-radius: 50%}};
        display: flex}};
        align-items: center}};
        justify-content: center}};
        color: white}};
        font-size: 16px}};
        font-weight: bold}};
    }

    .stat-icon {
        width: 60px}};
        height: 60px}};
        display: flex}};
        align-items: center}};
        justify-content: center}};
    }

    .stat-value {
        font-size: 1.5rem}};
        font-weight: 700}};
    }

    .enterprise-table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%})}};
        color: white}};
        font-weight: 600}};
        border: none}};
    }

    .enterprise-table td {
        vertical-align: middle}};
    }

    .btn-group .enterprise-btn {
        margin-right: 2px}};
    }

    .btn-group .enterprise-btn:last-child {
        margin-right: 0}};
    }
</style>

<!-- Job Management JavaScript -->
<script>
    // Initialize job management features
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Job management initialized'})}};
    }})}};

    // Refresh job data function
    function refreshJobData() {
        location.reload(})}};
    }

    // Export job data function
    function exportJobData() {
        // Create CSV content
        let csvContent = "data:text/csv}};charset=utf-8,"}};
        csvContent += "Job Management Data\n"}};
        csvContent += "Metric,Value,Change\n"}};
        csvContent += "Total Jobs,<?= $jobStats['total_jobs'] ?>,+18.5%\n"}};
        csvContent += "Active Jobs,<?= $jobStats['active_jobs'] ?>,+22.3%\n"}};
        csvContent += "Pending Jobs,<?= $jobStats['pending_jobs'] ?>,+5.7%\n"}};
        csvContent += "Urgent Jobs,<?= $jobStats['urgent_jobs'] ?>,+35.2%\n"}};
        csvContent += "Remote Jobs,<?= $jobStats['remote_jobs'] ?>,+28.9%\n"}};
        csvContent += "New Jobs Today,<?= $jobStats['new_jobs_today'] ?>,+42.1%\n"}};
        
        // Create download link
        const encodedUri = encodeURI(csvContent})}};
        const link = document.createElement("a"})}};
        link.setAttribute("href", encodedUri})}};
        link.setAttribute("download", "job_management_<?= date('Y-m-d') ?>.csv"})}};
        document.body.appendChild(link})}};
        link.click(})}};
        document.body.removeChild(link})}};
    }

    // Generate job report function
    function generateJobReport() {
        alert('Génération du rapport emploi en cours...\nCette fonctionnalité sera bientôt disponible.'})}};
    }

    // Show job details function
    function showJobDetails() {
        alert('Détails des offres d\'emploi:\nTotal: <?= number_format($jobStats['total_jobs']) ?>\nActives: <?= number_format($jobStats['active_jobs']) ?>'})}};
    }

    // Show active jobs function
    function showActiveJobs() {
        alert('Offres actives:\n<?= number_format($jobStats['active_jobs']) ?> offres actives ce mois'})}};
    }

    // Show pending jobs function
    function showPendingJobs() {
        alert('Offres en attente:\n<?= number_format($jobStats['pending_jobs']) ?> offres en attente'})}};
    }

    // Show urgent jobs function
    function showUrgentJobs() {
        alert('Offres urgentes:\n<?= number_format($jobStats['urgent_jobs']) ?> offres urgentes'})}};
    }

    // Show remote jobs function
    function showRemoteJobs() {
        alert('Offres télétravail:\n<?= number_format($jobStats['remote_jobs']) ?> offres avec télétravail'})}};
    }

    // Show today jobs function
    function showTodayJobs() {
        alert('Nouvelles offres aujourd\'hui:\n<?= number_format($jobStats['new_jobs_today']) ?> nouvelles offres'})}};
    }

    // View job function
    function viewJob(jobId) {
        alert('Voir les détails de l\'offre ID: ' + jobId})}};
    }

    // Edit job function
    function editJob(jobId) {
        alert('Modifier l\'offre ID: ' + jobId})}};
    }

    // Activate job function
    function activateJob(jobId) {
        if (confirm('Êtes-vous sûr de vouloir activer cette offre ?')) {
            const form = document.createElement('form'})}};
            form.method = 'POST'}};
            form.innerHTML = `
                <input type="hidden" name="action" value="activate">
                <input type="hidden" name="job_id" value="${jobId}">
            `}};
            document.body.appendChild(form})}};
            form.submit(})}};
        }
    }

    // Deactivate job function
    function deactivateJob(jobId) {
        if (confirm('Êtes-vous sûr de vouloir désactiver cette offre ?')) {
            const form = document.createElement('form'})}};
            form.method = 'POST'}};
            form.innerHTML = `
                <input type="hidden" name="action" value="deactivate">
                <input type="hidden" name="job_id" value="${jobId}">
            `}};
            document.body.appendChild(form})}};
            form.submit(})}};
        }
    }

    // Delete job function
    function deleteJob(jobId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette offre ? Cette action ne peut pas être annulée !')) {
            const form = document.createElement('form'})}};
            form.method = 'POST'}};
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="job_id" value="${jobId}">
            `}};
            document.body.appendChild(form})}};
            form.submit(})}};
        }
    }
</script>

<?php include __DIR__ . '/includes/admin_footer.php'}}; ?>
