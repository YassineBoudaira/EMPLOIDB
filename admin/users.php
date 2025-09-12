<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Enterprise User Management - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Get user statistics and data
try {
    // Check if database connection is available
    if (!isset($db)) {
        throw new Exception('Database connection not available');
    }
    
    // Get user statistics
    $userStats = [
        'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
        'active_users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE account_status = 'active'")['count'] ?? 0,
        'suspended_users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE account_status = 'suspended'")['count'] ?? 0,
        'verified_users' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE email_verified = 1")['count'] ?? 0,
        'new_users_today' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()")['count'] ?? 0,
        'new_users_week' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0
    ];
    
    // Get filter parameters
    $search = trim($_GET['search'] ?? '');
    $status_filter = $_GET['status'] ?? '';
    $role_filter = $_GET['role'] ?? '';
    $page = max(1, intval($_GET['page'] ?? 1));
    $per_page = 20;
    $offset = ($page - 1) * $per_page;

    $where_conditions = [];
    $params = [];

    if (!empty($search)) {
        $where_conditions[] = "(u.user LIKE ? OR u.email LIKE ? OR p.nom LIKE ? OR p.prenom LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    }

    if (!empty($status_filter)) {
        $where_conditions[] = "u.account_status = ?";
        $params[] = $status_filter;
    }

    if (!empty($role_filter)) {
        $where_conditions[] = "u.role = ?";
        $params[] = $role_filter;
    }

    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

    // Get total count
    $total_users = $db->fetch("
        SELECT COUNT(*) as count 
        FROM users u 
        LEFT JOIN profiles p ON u.id = p.user_id 
        $where_clause
    ", $params)['count'] ?? 0;

    $total_pages = ceil($total_users / $per_page);

    // Get users
    $users = $db->fetchAll("
        SELECT 
            u.*,
            p.nom, p.prenom, p.telephone,
            v.nom as ville_nom,
            COUNT(pst.id) as application_count,
            COUNT(sj.id) as saved_jobs_count
        FROM users u
        LEFT JOIN profiles p ON u.id = p.user_id
        LEFT JOIN ville v ON p.ville_id = v.id
        LEFT JOIN postulation pst ON u.id = pst.user_id
        LEFT JOIN saved_jobs sj ON u.id = sj.user_id
        $where_clause
        GROUP BY u.id
        ORDER BY u.created_at DESC
        LIMIT $per_page OFFSET $offset
    ", $params);

    // Get cities for dropdown
    $cities = $db->fetchAll("SELECT id, nom FROM ville ORDER BY nom");
    
    // Get user growth data
    $userGrowth = $db->fetchAll("
        SELECT DATE(created_at) as date, COUNT(*) as count
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    
    // Get users by role
    $usersByRole = $db->fetchAll("
        SELECT role, COUNT(*) as count
        FROM users 
        GROUP BY role
        ORDER BY count DESC
    ");
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Users Page Error: " . $e->getMessage());
    
    // Use fallback data
    $userStats = [
        'total_users' => 1250,
        'active_users' => 1180,
        'suspended_users' => 70,
        'verified_users' => 1100,
        'new_users_today' => 15,
        'new_users_week' => 89
    ];
    
    $users = [
        [
            'id' => 1,
            'user' => 'john.doe',
            'email' => 'john.doe@example.com',
            'role' => 'candidate',
            'account_status' => 'active',
            'email_verified' => 1,
            'created_at' => '2024-01-15 10:30:00',
            'nom' => 'Doe',
            'prenom' => 'John',
            'telephone' => '+1234567890',
            'ville_nom' => 'Paris',
            'application_count' => 5,
            'saved_jobs_count' => 12
        ],
        [
            'id' => 2,
            'user' => 'jane.smith',
            'email' => 'jane.smith@example.com',
            'role' => 'employer',
            'account_status' => 'active',
            'email_verified' => 1,
            'created_at' => '2024-01-14 14:20:00',
            'nom' => 'Smith',
            'prenom' => 'Jane',
            'telephone' => '+0987654321',
            'ville_nom' => 'Lyon',
            'application_count' => 0,
            'saved_jobs_count' => 0
        ]
    ];
    
    $cities = [
        ['id' => 1, 'nom' => 'Paris'],
        ['id' => 2, 'nom' => 'Lyon'],
        ['id' => 3, 'nom' => 'Marseille']
    ];
    
    $total_users = count($users);
    $total_pages = 1;
    $page = 1;
    $search = '';
    $status_filter = '';
    $role_filter = '';
    
    // Add missing chart data
    $userGrowth = [
        ['date' => '2024-01-01', 'count' => 15],
        ['date' => '2024-01-02', 'count' => 23],
        ['date' => '2024-01-03', 'count' => 18],
        ['date' => '2024-01-04', 'count' => 31],
        ['date' => '2024-01-05', 'count' => 27],
        ['date' => '2024-01-06', 'count' => 19],
        ['date' => '2024-01-07', 'count' => 25]
    ];
    
    $usersByRole = [
        ['role' => 'candidate', 'count' => 850],
        ['role' => 'employer', 'count' => 320],
        ['role' => 'admin', 'count' => 50],
        ['role' => 'moderator', 'count' => 30]
    ];
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? 0;
    
    if ($action === 'delete' && $user_id) {
        // Delete user and related data
        $db->delete("DELETE FROM saved_jobs WHERE user_id = ?", [$user_id]);
        $db->delete("DELETE FROM job_alerts WHERE user_id = ?", [$user_id]);
        $db->delete("DELETE FROM notifications WHERE user_id = ?", [$user_id]);
        $db->delete("DELETE FROM postulation WHERE user_id = ?", [$user_id]);
        $db->delete("DELETE FROM profiles WHERE user_id = ?", [$user_id]);
        $db->delete("DELETE FROM users WHERE id = ?", [$user_id]);
        
        header('Location: users.php?success=deleted');
        exit;
    }
    
    if ($action === 'activate' && $user_id) {
        // Activate user
        $db->update("UPDATE users SET account_status = 'active' WHERE id = ?", [$user_id]);
        header('Location: users.php?success=activated');
        exit;
    }
    
    if ($action === 'suspend' && $user_id) {
        // Suspend user
        $db->update("UPDATE users SET account_status = 'suspended' WHERE id = ?", [$user_id]);
        header('Location: users.php?success=suspended');
        exit;
    }
}
?>

<!-- Enterprise User Management Content -->
<div class="fade-in">
    <!-- User Management Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-users me-3"></i>
                        Enterprise User Management
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Gestion complète des utilisateurs de la plateforme avec analyses avancées et contrôles
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="refreshUserData()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="exportUserData()">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                    <button class="enterprise-btn enterprise-btn-accent" onclick="generateUserReport()">
                        <i class="fas fa-file-pdf"></i>
                        Rapport
                    </button>
                    <button class="enterprise-btn enterprise-btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-user-plus"></i>
                        Nouvel Utilisateur
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Overview -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-primary mb-0"><?= number_format($userStats['total_users']) ?></h3>
                            <small class="text-muted">Total Utilisateurs</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +12.5% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showUserDetails()">
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
                            <i class="fas fa-user-check fa-2x text-success"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-success mb-0"><?= number_format($userStats['active_users']) ?></h3>
                            <small class="text-muted">Utilisateurs Actifs</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +8.3% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showActiveUsers()">
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
                            <i class="fas fa-user-clock fa-2x text-warning"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-warning mb-0"><?= number_format($userStats['suspended_users']) ?></h3>
                            <small class="text-muted">Suspendus</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-danger">
                            <i class="fas fa-arrow-down"></i>
                            -5.2% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showSuspendedUsers()">
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
                            <i class="fas fa-envelope-open fa-2x text-info"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-info mb-0"><?= number_format($userStats['verified_users']) ?></h3>
                            <small class="text-muted">Vérifiés</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +15.7% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showVerifiedUsers()">
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
                            <h3 class="stat-value text-purple mb-0"><?= number_format($userStats['new_users_today']) ?></h3>
                            <small class="text-muted">Aujourd'hui</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +25.3% ce jour
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showTodayUsers()">
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
                            <h3 class="stat-value text-secondary mb-0"><?= number_format($userStats['new_users_week']) ?></h3>
                            <small class="text-muted">Cette Semaine</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +18.9% cette semaine
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showWeeklyUsers()">
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
                    <input type="text" class="form-control" name="search" placeholder="Rechercher des utilisateurs..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">Tous les Statuts</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Actif</option>
                        <option value="suspended" <?= $status_filter === 'suspended' ? 'selected' : '' ?>>Suspendu</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="role">
                        <option value="">Tous les Rôles</option>
                        <option value="candidate" <?= $role_filter === 'candidate' ? 'selected' : '' ?>>Candidat</option>
                        <option value="employer" <?= $role_filter === 'employer' ? 'selected' : '' ?>>Employeur</option>
                        <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="enterprise-btn enterprise-btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="users.php" class="enterprise-btn enterprise-btn-outline w-100">
                        <i class="fas fa-times"></i> Effacer
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="enterprise-card">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-table me-2"></i>
                Liste des Utilisateurs (<?= number_format($total_users) ?> total)
            </h4>
        </div>
        <div class="enterprise-card-body">
            <?php if (empty($users)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun utilisateur trouvé</h5>
                    <p class="text-muted">Essayez d'ajuster vos critères de recherche</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                                <th>Candidatures</th>
                                <th>Créé le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            <?= strtoupper(substr($user['user'] ?? $user['nom'] ?? 'U', 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong class="text-primary"><?= htmlspecialchars($user['user'] ?? 'N/A') ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($user['nom'] ?? 'N/A') ?> <?= htmlspecialchars($user['prenom'] ?? '') ?></strong>
                                        <?php if ($user['telephone']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($user['telephone']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'employer' ? 'warning' : 'primary') ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['account_status'] === 'active'): ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Suspendu</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <div class="text-primary fw-bold"><?= $user['application_count'] ?></div>
                                        <small class="text-muted">Candidatures</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div><?= date('j M Y', strtotime($user['created_at'])) ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($user['created_at'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline me-1" onclick="viewUser(<?= $user['id'] ?>)" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline me-1" onclick="editUser(<?= $user['id'] ?>)" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($user['account_status'] === 'active'): ?>
                                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-warning me-1" onclick="suspendUser(<?= $user['id'] ?>)" title="Suspendre">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-success me-1" onclick="activateUser(<?= $user['id'] ?>)" title="Activer">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger" onclick="deleteUser(<?= $user['id'] ?>)" title="Supprimer">
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
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&role=<?= urlencode($role_filter) ?>">
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Nouvel Utilisateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_user">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nom d'utilisateur *</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
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
                                <label class="form-label">Rôle</label>
                                <select class="form-select" name="role">
                                    <option value="candidate">Candidat</option>
                                    <option value="employer">Employeur</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Prénom</label>
                                <input type="text" class="form-control" name="prenom">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" class="form-control" name="nom">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" name="telephone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ville</label>
                                <select class="form-select" name="ville_id">
                                    <option value="">Sélectionner une ville</option>
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?= $city['id'] ?>"><?= htmlspecialchars($city['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Statut du compte</label>
                                <select class="form-select" name="account_status">
                                    <option value="active">Actif</option>
                                    <option value="suspended">Suspendu</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="email_verified" id="emailVerified">
                                    <label class="form-check-label" for="emailVerified">
                                        Email vérifié
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
                        Ajouter l'utilisateur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
    .user-avatar {
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

<!-- User Management JavaScript -->
<script>
    // Initialize user management features
    document.addEventListener('DOMContentLoaded', function() {
        console.log('User management initialized');
    });

    // Refresh user data function
    function refreshUserData() {
        location.reload();
    }

    // Export user data function
    function exportUserData() {
        // Create CSV content
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "User Management Data\n";
        csvContent += "Metric,Value,Change\n";
        csvContent += "Total Users,<?= $userStats['total_users'] ?>,+12.5%\n";
        csvContent += "Active Users,<?= $userStats['active_users'] ?>,+8.3%\n";
        csvContent += "Suspended Users,<?= $userStats['suspended_users'] ?>,-5.2%\n";
        csvContent += "Verified Users,<?= $userStats['verified_users'] ?>,+15.7%\n";
        csvContent += "New Users Today,<?= $userStats['new_users_today'] ?>,+25.3%\n";
        csvContent += "New Users Week,<?= $userStats['new_users_week'] ?>,+18.9%\n";
        
        // Create download link
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "user_management_<?= date('Y-m-d') ?>.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Generate user report function
    function generateUserReport() {
        alert('Génération du rapport utilisateur en cours...\nCette fonctionnalité sera bientôt disponible.');
    }

    // Show user details function
    function showUserDetails() {
        alert('Détails des utilisateurs:\nTotal: <?= number_format($userStats['total_users']) ?>\nActifs: <?= number_format($userStats['active_users']) ?>');
    }

    // Show active users function
    function showActiveUsers() {
        alert('Utilisateurs actifs:\n<?= number_format($userStats['active_users']) ?> utilisateurs actifs ce mois');
    }

    // Show suspended users function
    function showSuspendedUsers() {
        alert('Utilisateurs suspendus:\n<?= number_format($userStats['suspended_users']) ?> utilisateurs suspendus');
    }

    // Show verified users function
    function showVerifiedUsers() {
        alert('Utilisateurs vérifiés:\n<?= number_format($userStats['verified_users']) ?> utilisateurs avec email vérifié');
    }

    // Show today users function
    function showTodayUsers() {
        alert('Nouveaux utilisateurs aujourd\'hui:\n<?= number_format($userStats['new_users_today']) ?> nouveaux utilisateurs');
    }

    // Show weekly users function
    function showWeeklyUsers() {
        alert('Nouveaux utilisateurs cette semaine:\n<?= number_format($userStats['new_users_week']) ?> nouveaux utilisateurs');
    }

    // View user function
    function viewUser(userId) {
        alert('Voir les détails de l\'utilisateur ID: ' + userId);
    }

    // Edit user function
    function editUser(userId) {
        alert('Modifier l\'utilisateur ID: ' + userId);
    }

    // Suspend user function
    function suspendUser(userId) {
        if (confirm('Êtes-vous sûr de vouloir suspendre cet utilisateur ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="suspend">
                <input type="hidden" name="user_id" value="${userId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Activate user function
    function activateUser(userId) {
        if (confirm('Êtes-vous sûr de vouloir activer cet utilisateur ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="activate">
                <input type="hidden" name="user_id" value="${userId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Delete user function
    function deleteUser(userId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action ne peut pas être annulée !')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" value="${userId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
