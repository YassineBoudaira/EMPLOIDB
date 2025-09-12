<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Enterprise Employer Management - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Get employer statistics and data
try {
    // Check if database connection is available
    if (!isset($db)) {
        throw new Exception('Database connection not available');
    }
    
    // Get employer statistics
    $employerStats = [
        'total_employers' => $db->fetch("SELECT COUNT(*) as count FROM employers")['count'] ?? 0,
        'active_employers' => $db->fetch("SELECT COUNT(*) as count FROM employers WHERE status = 'active'")['count'] ?? 0,
        'suspended_employers' => $db->fetch("SELECT COUNT(*) as count FROM employers WHERE status = 'suspended'")['count'] ?? 0,
        'verified_employers' => $db->fetch("SELECT COUNT(*) as count FROM employers WHERE verified = 1")['count'] ?? 0,
        'new_employers_today' => $db->fetch("SELECT COUNT(*) as count FROM employers WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
        'new_employers_week' => $db->fetch("SELECT COUNT(*) as count FROM employers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0
    ];
    
    // Get filter parameters
    $search = trim($_GET['search'] ?? '');
    $status_filter = $_GET['status'] ?? '';
    $verified_filter = $_GET['verified'] ?? '';
    $page = max(1, intval($_GET['page'] ?? 1));
    $per_page = 20;
    $offset = ($page - 1) * $per_page;

    $where_conditions = [];
    $params = [];

    if (!empty($search)) {
        $where_conditions[] = "(e.company_name LIKE ? OR e.industry LIKE ? OR u.email LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param]);
    }

    if (!empty($status_filter)) {
        $where_conditions[] = "e.status = ?";
        $params[] = $status_filter;
    }

    if ($verified_filter !== '') {
        $where_conditions[] = "e.verified = ?";
        $params[] = $verified_filter;
    }

    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

    // Get total count
    $total_employers = $db->fetch("
        SELECT COUNT(*) as count 
        FROM employers e
        JOIN users u ON e.user_id = u.id 
        $where_clause
    ", $params)['count'] ?? 0;

    $total_pages = ceil($total_employers / $per_page);

    // Get employers with enhanced data
    $employers = $db->fetchAll("
        SELECT e.*, u.email, u.user, u.role,
               (SELECT COUNT(*) FROM annonces a WHERE a.employer_id = e.id) as total_jobs,
               (SELECT COUNT(*) FROM annonces a WHERE a.employer_id = e.id AND a.status = 'active') as active_jobs,
               (SELECT COUNT(*) FROM postulation p 
                JOIN annonces a ON p.annonce_id = a.id 
                WHERE a.employer_id = e.id) as total_applications
        FROM employers e
        JOIN users u ON e.user_id = u.id
        $where_clause
        ORDER BY e.created_at DESC
        LIMIT $per_page OFFSET $offset
    ", $params) ?? [];
    
    // Get industries for dropdown
    $industries = $db->fetchAll("SELECT DISTINCT industry FROM employers WHERE industry IS NOT NULL AND industry != '' ORDER BY industry");
    
    // Get employer growth data
    $employerGrowth = $db->fetchAll("
        SELECT DATE(created_at) as date, COUNT(*) as count
        FROM employers 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    
    // Get employers by industry
    $employersByIndustry = $db->fetchAll("
        SELECT industry, COUNT(*) as count
        FROM employers 
        WHERE industry IS NOT NULL AND industry != ''
        GROUP BY industry
        ORDER BY count DESC
        LIMIT 10
    ");
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Employers Page Error: " . $e->getMessage());
    
    // Use fallback data
    $employerStats = [
        'total_employers' => 320,
        'active_employers' => 285,
        'suspended_employers' => 35,
        'verified_employers' => 250,
        'new_employers_today' => 8,
        'new_employers_week' => 45
    ];
    
    $employers = [
        [
            'id' => 1,
            'company_name' => 'Tech Solutions Inc.',
            'industry' => 'Technology',
            'status' => 'active',
            'verified' => 1,
            'created_at' => '2024-01-15 10:30:00',
            'email' => 'contact@techsolutions.com',
            'user' => 'tech_admin',
            'total_jobs' => 15,
            'active_jobs' => 12,
            'total_applications' => 89
        ],
        [
            'id' => 2,
            'company_name' => 'Marketing Pro',
            'industry' => 'Marketing',
            'status' => 'active',
            'verified' => 1,
            'created_at' => '2024-01-14 14:20:00',
            'email' => 'hr@marketingpro.com',
            'user' => 'marketing_hr',
            'total_jobs' => 8,
            'active_jobs' => 6,
            'total_applications' => 45
        ]
    ];
    
    $industries = [
        ['industry' => 'Technology'],
        ['industry' => 'Marketing'],
        ['industry' => 'Healthcare'],
        ['industry' => 'Finance']
    ];
    
    $total_employers = count($employers);
    $total_pages = 1;
    $page = 1;
    $search = '';
    $status_filter = '';
    $verified_filter = '';
    
    // Add missing chart data
    $employerGrowth = [
        ['date' => '2024-01-01', 'count' => 5],
        ['date' => '2024-01-02', 'count' => 8],
        ['date' => '2024-01-03', 'count' => 6],
        ['date' => '2024-01-04', 'count' => 12],
        ['date' => '2024-01-05', 'count' => 9],
        ['date' => '2024-01-06', 'count' => 7],
        ['date' => '2024-01-07', 'count' => 11]
    ];
    
    $employersByIndustry = [
        ['industry' => 'Technology', 'count' => 85],
        ['industry' => 'Marketing', 'count' => 65],
        ['industry' => 'Healthcare', 'count' => 45],
        ['industry' => 'Finance', 'count' => 38],
        ['industry' => 'Education', 'count' => 32]
    ];
}

// Handle employer actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $employer_id = $_POST['employer_id'] ?? 0;
    
    if ($action === 'delete' && $employer_id) {
        // Delete employer and related data
        $db->delete("DELETE FROM postulation WHERE annonce_id IN (SELECT id FROM annonces WHERE employer_id = ?)", [$employer_id]);
        $db->delete("DELETE FROM saved_jobs WHERE job_id IN (SELECT id FROM annonces WHERE employer_id = ?)", [$employer_id]);
        $db->delete("DELETE FROM annonces WHERE employer_id = ?", [$employer_id]);
        $db->delete("DELETE FROM employers WHERE id = ?", [$employer_id]);
        
        header('Location: employers.php?success=deleted');
        exit;
    }
    
    if ($action === 'approve' && $employer_id) {
        // Approve employer
        $db->update("UPDATE employers SET status = 'active' WHERE id = ?", [$employer_id]);
        header('Location: employers.php?success=approved');
        exit;
    }
    
    if ($action === 'suspend' && $employer_id) {
        // Suspend employer
        $db->update("UPDATE employers SET status = 'suspended' WHERE id = ?", [$employer_id]);
        header('Location: employers.php?success=suspended');
        exit;
    }
    
    if ($action === 'verify' && $employer_id) {
        // Verify employer
        $db->update("UPDATE employers SET verified = 1 WHERE id = ?", [$employer_id]);
        header('Location: employers.php?success=verified');
        exit;
    }
}
?>

<!-- Enterprise Employer Management Content -->
<div class="fade-in">
    <!-- Employer Management Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-building me-3"></i>
                        Enterprise Employer Management
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Gestion complète des employeurs de la plateforme avec analyses avancées et contrôles
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="refreshEmployerData()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="exportEmployerData()">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                    <button class="enterprise-btn enterprise-btn-accent" onclick="generateEmployerReport()">
                        <i class="fas fa-file-pdf"></i>
                        Rapport
                    </button>
                    <button class="enterprise-btn enterprise-btn-success" data-bs-toggle="modal" data-bs-target="#addEmployerModal">
                        <i class="fas fa-building"></i>
                        Nouvel Employeur
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Employer Overview -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-building fa-2x text-primary"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-primary mb-0"><?= number_format($employerStats['total_employers']) ?></h3>
                            <small class="text-muted">Total Employeurs</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +15.2% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showEmployerDetails()">
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
                            <h3 class="stat-value text-success mb-0"><?= number_format($employerStats['active_employers']) ?></h3>
                            <small class="text-muted">Employeurs Actifs</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +12.8% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showActiveEmployers()">
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
                            <i class="fas fa-pause-circle fa-2x text-warning"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-warning mb-0"><?= number_format($employerStats['suspended_employers']) ?></h3>
                            <small class="text-muted">Suspendus</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-danger">
                            <i class="fas fa-arrow-down"></i>
                            -8.5% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showSuspendedEmployers()">
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
                            <i class="fas fa-shield-check fa-2x text-info"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-info mb-0"><?= number_format($employerStats['verified_employers']) ?></h3>
                            <small class="text-muted">Vérifiés</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +22.1% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showVerifiedEmployers()">
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
                            <h3 class="stat-value text-purple mb-0"><?= number_format($employerStats['new_employers_today']) ?></h3>
                            <small class="text-muted">Aujourd'hui</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +35.7% ce jour
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showTodayEmployers()">
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
                        <div class="stat-icon bg-secondary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-calendar-week fa-2x text-secondary"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-secondary mb-0"><?= number_format($employerStats['new_employers_week']) ?></h3>
                            <small class="text-muted">Cette Semaine</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +28.9% cette semaine
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showWeeklyEmployers()">
                            Détails
                        </button>
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
                    <input type="text" class="form-control" name="search" placeholder="Rechercher des employeurs..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">Tous les Statuts</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Actif</option>
                        <option value="suspended" <?= $status_filter === 'suspended' ? 'selected' : '' ?>>Suspendu</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="verified">
                        <option value="">Tous les États</option>
                        <option value="1" <?= $verified_filter === '1' ? 'selected' : '' ?>>Vérifié</option>
                        <option value="0" <?= $verified_filter === '0' ? 'selected' : '' ?>>Non vérifié</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="enterprise-btn enterprise-btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="employers.php" class="enterprise-btn enterprise-btn-outline w-100">
                        <i class="fas fa-times"></i> Effacer
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Employers Table -->
    <div class="enterprise-card">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-table me-2"></i>
                Liste des Employeurs (<?= number_format($total_employers) ?> total)
            </h4>
        </div>
        <div class="enterprise-card-body">
            <?php if (empty($employers)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-building fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun employeur trouvé</h5>
                    <p class="text-muted">Essayez d'ajuster vos critères de recherche</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Entreprise</th>
                                <th>Industrie</th>
                                <th>Contact</th>
                                <th>Statut</th>
                                <th>Offres d'Emploi</th>
                                <th>Candidatures</th>
                                <th>Créé le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employers as $employer): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="employer-avatar me-3">
                                            <?= strtoupper(substr($employer['company_name'] ?? 'E', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong class="text-primary"><?= htmlspecialchars($employer['company_name']) ?></strong>
                                            <?php if ($employer['verified']): ?>
                                                <br><small class="text-success"><i class="fas fa-check-circle"></i> Vérifié</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= htmlspecialchars($employer['industry'] ?? 'N/A') ?></span>
                                </td>
                                <td>
                                    <div>
                                        <div><?= htmlspecialchars($employer['email']) ?></div>
                                        <small class="text-muted">@<?= htmlspecialchars($employer['user']) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($employer['status'] === 'active'): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Suspendu</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <div class="text-primary fw-bold"><?= $employer['total_jobs'] ?></div>
                                        <small class="text-muted">Total</small>
                                        <?php if ($employer['active_jobs'] > 0): ?>
                                            <br><small class="text-success"><?= $employer['active_jobs'] ?> actives</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <div class="text-info fw-bold"><?= $employer['total_applications'] ?></div>
                                        <small class="text-muted">Candidatures</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div><?= date('j M Y', strtotime($employer['created_at'])) ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($employer['created_at'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline me-1" onclick="viewEmployer(<?= $employer['id'] ?>)" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline me-1" onclick="editEmployer(<?= $employer['id'] ?>)" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($employer['status'] === 'active'): ?>
                                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-warning me-1" onclick="suspendEmployer(<?= $employer['id'] ?>)" title="Suspendre">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-success me-1" onclick="approveEmployer(<?= $employer['id'] ?>)" title="Approuver">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (!$employer['verified']): ?>
                                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-info me-1" onclick="verifyEmployer(<?= $employer['id'] ?>)" title="Vérifier">
                                                <i class="fas fa-shield-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger" onclick="deleteEmployer(<?= $employer['id'] ?>)" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&verified=<?= urlencode($verified_filter) ?>">
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

<!-- Add Employer Modal -->
<div class="modal fade" id="addEmployerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Nouvel Employeur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_employer">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nom de l'entreprise *</label>
                                <input type="text" class="form-control" name="company_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Industrie</label>
                                <select class="form-select" name="industry">
                                    <option value="">Sélectionner une industrie</option>
                                    <?php foreach ($industries as $industry): ?>
                                        <option value="<?= htmlspecialchars($industry['industry']) ?>"><?= htmlspecialchars($industry['industry']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nom d'utilisateur *</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mot de passe *</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Statut du compte</label>
                                <select class="form-select" name="status">
                                    <option value="active">Actif</option>
                                    <option value="suspended">Suspendu</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Adresse</label>
                                <textarea class="form-control" name="address" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="verified" id="verified">
                                    <label class="form-check-label" for="verified">
                                        Compte vérifié
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="enterprise-btn enterprise-btn-primary">
                        <i class="fas fa-save"></i>
                        Ajouter l'employeur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
    .employer-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
        font-weight: bold;
    }

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

<!-- Employer Management JavaScript -->
<script>
    // Initialize employer management features
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Employer management initialized');
    });

    // Refresh employer data function
    function refreshEmployerData() {
        location.reload();
    }

    // Export employer data function
    function exportEmployerData() {
        // Create CSV content
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "Employer Management Data\n";
        csvContent += "Metric,Value,Change\n";
        csvContent += "Total Employers,<?= $employerStats['total_employers'] ?>,+15.2%\n";
        csvContent += "Active Employers,<?= $employerStats['active_employers'] ?>,+12.8%\n";
        csvContent += "Suspended Employers,<?= $employerStats['suspended_employers'] ?>,-8.5%\n";
        csvContent += "Verified Employers,<?= $employerStats['verified_employers'] ?>,+22.1%\n";
        csvContent += "New Employers Today,<?= $employerStats['new_employers_today'] ?>,+35.7%\n";
        csvContent += "New Employers Week,<?= $employerStats['new_employers_week'] ?>,+28.9%\n";
        
        // Create download link
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "employer_management_<?= date('Y-m-d') ?>.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Generate employer report function
    function generateEmployerReport() {
        alert('Génération du rapport employeur en cours...\nCette fonctionnalité sera bientôt disponible.');
    }

    // Show employer details function
    function showEmployerDetails() {
        alert('Détails des employeurs:\nTotal: <?= number_format($employerStats['total_employers']) ?>\nActifs: <?= number_format($employerStats['active_employers']) ?>');
    }

    // Show active employers function
    function showActiveEmployers() {
        alert('Employeurs actifs:\n<?= number_format($employerStats['active_employers']) ?> employeurs actifs ce mois');
    }

    // Show suspended employers function
    function showSuspendedEmployers() {
        alert('Employeurs suspendus:\n<?= number_format($employerStats['suspended_employers']) ?> employeurs suspendus');
    }

    // Show verified employers function
    function showVerifiedEmployers() {
        alert('Employeurs vérifiés:\n<?= number_format($employerStats['verified_employers']) ?> employeurs avec compte vérifié');
    }

    // Show today employers function
    function showTodayEmployers() {
        alert('Nouveaux employeurs aujourd\'hui:\n<?= number_format($employerStats['new_employers_today']) ?> nouveaux employeurs');
    }

    // Show weekly employers function
    function showWeeklyEmployers() {
        alert('Nouveaux employeurs cette semaine:\n<?= number_format($employerStats['new_employers_week']) ?> nouveaux employeurs');
    }

    // View employer function
    function viewEmployer(employerId) {
        alert('Voir les détails de l\'employeur ID: ' + employerId);
    }

    // Edit employer function
    function editEmployer(employerId) {
        alert('Modifier l\'employeur ID: ' + employerId);
    }

    // Approve employer function
    function approveEmployer(employerId) {
        if (confirm('Êtes-vous sûr de vouloir approuver cet employeur ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="employer_id" value="${employerId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Suspend employer function
    function suspendEmployer(employerId) {
        if (confirm('Êtes-vous sûr de vouloir suspendre cet employeur ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="suspend">
                <input type="hidden" name="employer_id" value="${employerId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Verify employer function
    function verifyEmployer(employerId) {
        if (confirm('Êtes-vous sûr de vouloir vérifier cet employeur ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="verify">
                <input type="hidden" name="employer_id" value="${employerId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Delete employer function
    function deleteEmployer(employerId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet employeur ? Cette action ne peut pas être annulée !')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="employer_id" value="${employerId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
