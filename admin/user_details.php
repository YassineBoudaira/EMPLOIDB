<?php
// Include configuration first (before any session starts)
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';
include __DIR__ . '/../include/connexion.php';

// Check if user is admin
if (!Security::isAdmin()) {
    header('Location: login.php');
    exit;
}

// Get user ID from URL
$user_id = intval($_GET['id'] ?? 0);
if (!$user_id) {
    header('Location: users.php?error=invalid_user');
    exit;
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'suspend') {
            $db->update("UPDATE users SET account_status = 'suspended' WHERE id = ?", [$user_id]);
            header('Location: user_details.php?id=' . $user_id . '&success=suspended');
            exit;
        }
        
        if ($action === 'activate') {
            $db->update("UPDATE users SET account_status = 'active' WHERE id = ?", [$user_id]);
            header('Location: user_details.php?id=' . $user_id . '&success=activated');
            exit;
        }
        
        if ($action === 'verify') {
            $db->update("UPDATE users SET email_verified = 1 WHERE id = ?", [$user_id]);
            header('Location: user_details.php?id=' . $user_id . '&success=verified');
            exit;
        }
        
        if ($action === 'delete') {
            // Delete user and related data
            $db->delete("DELETE FROM saved_jobs WHERE user_id = ?", [$user_id]);
            $db->delete("DELETE FROM job_alerts WHERE user_id = ?", [$user_id]);
            $db->delete("DELETE FROM notifications WHERE user_id = ?", [$user_id]);
            $db->delete("DELETE FROM postulation WHERE user_id = ?", [$user_id]);
            $db->delete("DELETE FROM profiles WHERE user_id = ?", [$user_id]);
            $db->delete("DELETE FROM users WHERE id = ?", [$user_id]);
            
            header('Location: users.php?success=user_deleted');
            exit;
        }
    } catch (Exception $e) {
        error_log("Database error in user_details.php POST actions: " . $e->getMessage());
        header('Location: user_details.php?id=' . $user_id . '&error=database');
        exit;
    }
}

// Get user details with enhanced data
try {
    $user_query = "SELECT u.*, p.nom, p.prenom, p.telephone, p.adresse, p.date_naissance, 
                   v.nom as ville_nom, d.nom as domaine_nom,
                   (SELECT COUNT(*) FROM postulation po WHERE po.user_id = u.id) as total_applications,
                   (SELECT COUNT(*) FROM saved_jobs sj WHERE sj.user_id = u.id) as saved_jobs_count,
                   (SELECT COUNT(*) FROM job_alerts ja WHERE ja.user_id = u.id) as job_alerts_count,
                   (SELECT COUNT(*) FROM notifications n WHERE n.user_id = u.id) as notifications_count
                   FROM users u
                   LEFT JOIN profiles p ON u.id = p.user_id
                   LEFT JOIN villes v ON p.ville_id = v.id
                   LEFT JOIN domaines d ON p.domaine_id = d.id
                   WHERE u.id = ?";
    $user = $db->fetch($user_query, [$user_id]);
} catch (Exception $e) {
    error_log("Database error in user_details.php getting user data: " . $e->getMessage());
    header('Location: users.php?error=user_not_found');
    exit;
}

if (!$user) {
    header('Location: users.php?error=user_not_found');
    exit;
}

// Get user's recent applications
try {
    $recent_applications = $db->fetchAll("
        SELECT p.*, a.titre as job_title, e.company_name
        FROM postulation p
        JOIN annonces a ON p.annonce_id = a.id
        LEFT JOIN employers e ON a.employer_id = e.id
        WHERE p.user_id = ?
        ORDER BY p.date_postulation DESC
        LIMIT 10
    ", [$user_id]) ?? [];

    // Get user's activity statistics
    $activity_stats = [
        'applications_this_month' => $db->fetch("
            SELECT COUNT(*) as count FROM postulation 
            WHERE user_id = ? AND date_postulation >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        ", [$user_id])['count'] ?? 0,
        'jobs_saved_this_month' => $db->fetch("
            SELECT COUNT(*) as count FROM saved_jobs 
            WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        ", [$user_id])['count'] ?? 0,
        'last_login' => $db->fetch("
            SELECT MAX(login_time) as last_login FROM user_sessions 
            WHERE user_id = ?
        ", [$user_id])['last_login'] ?? null
    ];
} catch (Exception $e) {
    // Fallback data if database queries fail
    $recent_applications = [];
    $activity_stats = [
        'applications_this_month' => 0,
        'jobs_saved_this_month' => 0,
        'last_login' => null
    ];
    error_log("Database error in user_details.php getting activity data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Utilisateur - Admin EMPLOIDB</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- EMPLOIDB Professional Design System -->
    <link href="../assets/css/emploidb-design-system.css" rel="stylesheet">
    
    <style>
        /* Using EMPLOIDB Design System Variables */
        body {
            background: var(--emploidb-bg-secondary);
            font-family: var(--emploidb-font-primary);
            color: var(--emploidb-text-primary);
        }
        
        .admin-layout {
            min-height: 100vh;
            display: flex;
        }
        
        .admin-sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--emploidb-primary) 0%, var(--emploidb-primary-700) 100%);
            color: var(--emploidb-text-inverse);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: var(--emploidb-shadow-lg);
        }
        
        .admin-content {
            flex: 1;
            margin-left: 280px;
            padding: var(--emploidb-spacing-6);
        }
        
        .sidebar-brand {
            padding: var(--emploidb-spacing-6);
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-brand h4 {
            color: var(--emploidb-text-inverse);
            font-weight: var(--emploidb-font-weight-bold);
            margin: 0;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: var(--emploidb-spacing-3) var(--emploidb-spacing-6);
            margin: var(--emploidb-spacing-1) var(--emploidb-spacing-4);
            border-radius: var(--emploidb-radius-lg);
            transition: var(--emploidb-transition-all);
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .nav-link:hover,
        .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: var(--emploidb-text-inverse);
            transform: translateX(5px);
        }
        
        .nav-link i {
            width: 20px;
            margin-right: var(--emploidb-spacing-3);
            font-size: var(--emploidb-text-base);
        }
        
        .content-card {
            background: var(--emploidb-bg-primary);
            border-radius: var(--emploidb-radius-xl);
            padding: var(--emploidb-spacing-6);
            box-shadow: var(--emploidb-shadow-sm);
            border: 1px solid var(--emploidb-neutral-200);
            margin-bottom: var(--emploidb-spacing-6);
        }
        
        .user-avatar {
            width: 120px;
            height: 120px;
            background: var(--emploidb-primary);
            border-radius: var(--emploidb-radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--emploidb-text-inverse);
            margin: 0 auto var(--emploidb-spacing-4);
        }
        
        .user-name {
            color: var(--emploidb-primary);
            font-weight: var(--emploidb-font-weight-bold);
            font-size: var(--emploidb-text-2xl);
            text-align: center;
            margin-bottom: var(--emploidb-spacing-2);
        }
        
        .user-email {
            color: var(--emploidb-text-secondary);
            text-align: center;
            margin-bottom: var(--emploidb-spacing-4);
        }
        
        .status-badge {
            padding: var(--emploidb-spacing-2) var(--emploidb-spacing-4);
            border-radius: var(--emploidb-radius-full);
            font-size: var(--emploidb-text-sm);
            font-weight: var(--emploidb-font-weight-semibold);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 auto;
            display: inline-block;
        }
        
        .status-badge.active {
            background: var(--emploidb-success-light);
            color: var(--emploidb-success-dark);
        }
        
        .status-badge.suspended {
            background: var(--emploidb-error-light);
            color: var(--emploidb-error-dark);
        }
        
        .verified-badge {
            background: var(--emploidb-info-light);
            color: var(--emploidb-info-dark);
            margin-left: var(--emploidb-spacing-2);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--emploidb-spacing-4);
            margin-bottom: var(--emploidb-spacing-6);
        }
        
        .stat-card {
            background: var(--emploidb-bg-primary);
            border-radius: var(--emploidb-radius-xl);
            padding: var(--emploidb-spacing-6);
            box-shadow: var(--emploidb-shadow-sm);
            border: 1px solid var(--emploidb-neutral-200);
            text-align: center;
            transition: var(--emploidb-transition-all);
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--emploidb-shadow-lg);
        }
        
        .stat-number {
            font-size: var(--emploidb-text-3xl);
            font-weight: var(--emploidb-font-weight-black);
            color: var(--emploidb-primary);
            margin-bottom: var(--emploidb-spacing-2);
        }
        
        .stat-label {
            color: var(--emploidb-text-secondary);
            font-weight: var(--emploidb-font-weight-medium);
            text-transform: uppercase;
            font-size: var(--emploidb-text-sm);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--emploidb-spacing-6);
        }
        
        .info-item {
            display: flex;
            align-items: center;
            padding: var(--emploidb-spacing-3) 0;
            border-bottom: 1px solid var(--emploidb-neutral-200);
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-item i {
            color: var(--emploidb-primary);
            width: 24px;
            margin-right: var(--emploidb-spacing-3);
        }
        
        .info-label {
            color: var(--emploidb-text-secondary);
            font-weight: var(--emploidb-font-weight-medium);
            width: 120px;
            flex-shrink: 0;
        }
        
        .info-value {
            color: var(--emploidb-text-primary);
            font-weight: var(--emploidb-font-weight-medium);
        }
        
        .action-btn {
            padding: var(--emploidb-spacing-3) var(--emploidb-spacing-4);
            border: none;
            border-radius: var(--emploidb-radius-lg);
            font-size: var(--emploidb-text-sm);
            font-weight: var(--emploidb-font-weight-semibold);
            transition: var(--emploidb-transition-all);
            cursor: pointer;
            margin: 0 var(--emploidb-spacing-2) var(--emploidb-spacing-2) 0;
            text-decoration: none;
            display: inline-block;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--emploidb-shadow-md);
        }
        
        .action-btn.primary {
            background: var(--emploidb-primary);
            color: var(--emploidb-text-inverse);
        }
        
        .action-btn.success {
            background: var(--emploidb-success);
            color: var(--emploidb-text-inverse);
        }
        
        .action-btn.warning {
            background: var(--emploidb-warning);
            color: var(--emploidb-text-inverse);
        }
        
        .action-btn.danger {
            background: var(--emploidb-error);
            color: var(--emploidb-text-inverse);
        }
        
        .action-btn.info {
            background: var(--emploidb-info);
            color: var(--emploidb-text-inverse);
        }
        
        .page-header {
            background: var(--emploidb-bg-primary);
            padding: var(--emploidb-spacing-6);
            border-radius: var(--emploidb-radius-xl);
            box-shadow: var(--emploidb-shadow-sm);
            border: 1px solid var(--emploidb-neutral-200);
            margin-bottom: var(--emploidb-spacing-6);
        }
        
        .application-item {
            background: var(--emploidb-bg-secondary);
            border-radius: var(--emploidb-radius-lg);
            padding: var(--emploidb-spacing-4);
            margin-bottom: var(--emploidb-spacing-3);
            border: 1px solid var(--emploidb-neutral-200);
            transition: var(--emploidb-transition-all);
        }
        
        .application-item:hover {
            border-color: var(--emploidb-primary-200);
            transform: translateY(-1px);
        }
        
        .nav-section-header {
            font-size: var(--emploidb-text-xs);
            letter-spacing: 1px;
            opacity: 0.7;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: var(--emploidb-spacing-2);
            margin: var(--emploidb-spacing-4) var(--emploidb-spacing-6) var(--emploidb-spacing-2);
            text-transform: uppercase;
            font-weight: var(--emploidb-font-weight-semibold);
            color: rgba(255, 255, 255, 0.7);
        }
        
        /* Responsive Design */
        @media (max-width: 991.98px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.show { transform: translateX(0); }
            .admin-content { margin-left: 0; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .info-grid { grid-template-columns: 1fr; }
        }
        
        @media (max-width: 575.98px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Include Admin Sidebar -->
        <?php include 'includes/admin_sidebar.php'; ?>
                </div>
                
                <!-- User Details -->
                <div class="col-lg-8">
                    <!-- Statistics -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?= $user['total_applications'] ?></div>
                            <div class="stat-label">Candidatures</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?= $user['saved_jobs_count'] ?></div>
                            <div class="stat-label">Emplois Sauvegardés</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?= $user['job_alerts_count'] ?></div>
                            <div class="stat-label">Alertes Emploi</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?= $activity_stats['applications_this_month'] ?></div>
                            <div class="stat-label">Ce Mois-ci</div>
                        </div>
                    </div>

                    <!-- Enhanced Analytics Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="emploidb-content-card">
                                <h5 style="color: var(--emploidb-primary); margin-bottom: var(--emploidb-spacing-4);">
                                    <i class="fas fa-chart-pie me-2"></i>Activité Utilisateur
                                </h5>
                                <div id="userActivityChart" style="height: 250px;"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="emploidb-content-card">
                                <h5 style="color: var(--emploidb-primary); margin-bottom: var(--emploidb-spacing-4);">
                                    <i class="fas fa-chart-bar me-2"></i>Statistiques Mensuelles
                                </h5>
                                <div id="userMonthlyChart" style="height: 250px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="emploidb-content-card mb-4">
                        <h5 style="color: var(--emploidb-primary); margin-bottom: var(--emploidb-spacing-4);">
                            <i class="fas fa-bolt me-2"></i>Actions Rapides
                        </h5>
                        <div class="row">
                            <div class="col-md-3">
                                <button class="emploidb-action-btn emploidb-info w-100 mb-2" onclick="exportUserData()">
                                    <i class="fas fa-download me-2"></i>Exporter Données
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="emploidb-action-btn emploidb-primary w-100 mb-2" onclick="viewUserAnalytics()">
                                    <i class="fas fa-chart-line me-2"></i>Analytics Détaillées
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="emploidb-action-btn emploidb-success w-100 mb-2" onclick="sendNotification()">
                                    <i class="fas fa-bell me-2"></i>Envoyer Notification
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button class="emploidb-action-btn emploidb-warning w-100 mb-2" onclick="resetPassword()">
                                    <i class="fas fa-key me-2"></i>Réinitialiser MDP
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Personal Information -->
                    <div class="content-card">
                        <h5 style="color: var(--emploidb-primary); margin-bottom: var(--emploidb-spacing-4);">
                            <i class="fas fa-user me-2"></i>Informations Personnelles
                        </h5>
                        
                        <div class="info-grid">
                            <div>
                                <div class="info-item">
                                    <i class="fas fa-user"></i>
                                    <span class="info-label">Nom d'utilisateur:</span>
                                    <span class="info-value"><?= htmlspecialchars($user['user'] ?? 'Non spécifié') ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-envelope"></i>
                                    <span class="info-label">Email:</span>
                                    <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-phone"></i>
                                    <span class="info-label">Téléphone:</span>
                                    <span class="info-value"><?= htmlspecialchars($user['telephone'] ?? 'Non spécifié') ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-calendar"></i>
                                    <span class="info-label">Date de naissance:</span>
                                    <span class="info-value">
                                        <?= $user['date_naissance'] ? date('d/m/Y', strtotime($user['date_naissance'])) : 'Non spécifiée' ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div>
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span class="info-label">Ville:</span>
                                    <span class="info-value"><?= htmlspecialchars($user['ville_nom'] ?? 'Non spécifiée') ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-home"></i>
                                    <span class="info-label">Adresse:</span>
                                    <span class="info-value"><?= htmlspecialchars($user['adresse'] ?? 'Non spécifiée') ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-briefcase"></i>
                                    <span class="info-label">Domaine:</span>
                                    <span class="info-value"><?= htmlspecialchars($user['domaine_nom'] ?? 'Non spécifié') ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-user-tag"></i>
                                    <span class="info-label">Rôle:</span>
                                    <span class="info-value"><?= ucfirst(htmlspecialchars($user['role'])) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Information -->
                    <div class="content-card">
                        <h5 style="color: var(--emploidb-primary); margin-bottom: var(--emploidb-spacing-4);">
                            <i class="fas fa-cog me-2"></i>Informations du Compte
                        </h5>
                        
                        <div class="info-grid">
                            <div>
                                <div class="info-item">
                                    <i class="fas fa-calendar-plus"></i>
                                    <span class="info-label">Inscription:</span>
                                    <span class="info-value"><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-clock"></i>
                                    <span class="info-label">Dernière connexion:</span>
                                    <span class="info-value">
                                        <?= $activity_stats['last_login'] ? date('d/m/Y H:i', strtotime($activity_stats['last_login'])) : 'Jamais' ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div>
                                <div class="info-item">
                                    <i class="fas fa-shield-check"></i>
                                    <span class="info-label">Email vérifié:</span>
                                    <span class="info-value">
                                        <?= $user['email_verified'] ? 'Oui' : 'Non' ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-user-check"></i>
                                    <span class="info-label">Statut du compte:</span>
                                    <span class="info-value"><?= ucfirst($user['account_status']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Applications -->
            <?php if (!empty($recent_applications)): ?>
                <div class="content-card">
                    <h5 style="color: var(--emploidb-primary); margin-bottom: var(--emploidb-spacing-4);">
                        <i class="fas fa-clipboard-list me-2"></i>Candidatures Récentes
                    </h5>
                    
                    <?php foreach (array_slice($recent_applications, 0, 5) as $application): ?>
                        <div class="application-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 style="color: var(--emploidb-primary); margin: 0;">
                                        <?= htmlspecialchars($application['job_title']) ?>
                                    </h6>
                                    <?php if ($application['company_name']): ?>
                                        <small style="color: var(--emploidb-text-secondary);">
                                            <i class="fas fa-building me-1"></i>
                                            <?= htmlspecialchars($application['company_name']) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end">
                                    <span class="status-badge <?= $application['status'] ?>">
                                        <?= ucfirst($application['status']) ?>
                                    </span>
                                    <br>
                                    <small style="color: var(--emploidb-text-muted);">
                                        <?= date('d/m/Y', strtotime($application['date_postulation'])) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($recent_applications) > 5): ?>
                        <div class="text-center mt-3">
                            <small style="color: var(--emploidb-text-muted);">
                                ... et <?= count($recent_applications) - 5 ?> autres candidatures
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
    
    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            sidebar.classList.toggle('show');
        }
        
        // Quick Actions Functions
        function exportUserData() {
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('export', 'user_data');
            window.location.href = currentUrl.toString();
        }

        function viewUserAnalytics() {
            window.open('user_analytics.php?user_id=<?= $user_id ?>', '_blank');
        }

        function sendNotification() {
            alert('Fonctionnalité d\'envoi de notification - à implémenter');
        }

        function resetPassword() {
            if (confirm('Voulez-vous réinitialiser le mot de passe de cet utilisateur ?')) {
                // Implement password reset functionality
                alert('Fonctionnalité de réinitialisation de mot de passe - à implémenter');
            }
        }

        // Initialize charts when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // User Activity Chart
            const activityData = [
                { name: 'Candidatures', value: <?= $user['total_applications'] ?>, color: '#2563eb' },
                { name: 'Emplois Sauvegardés', value: <?= $user['saved_jobs_count'] ?>, color: '#059669' },
                { name: 'Alertes Emploi', value: <?= $user['job_alerts_count'] ?>, color: '#dc2626' }
            ];
            
            if (activityData.some(item => item.value > 0)) {
                const activityOptions = {
                    series: activityData.map(item => item.value),
                    chart: {
                        type: 'donut',
                        height: 250
                    },
                    labels: activityData.map(item => item.name),
                    colors: activityData.map(item => item.color),
                    legend: {
                        position: 'bottom'
                    }
                };
                
                new ApexCharts(document.querySelector("#userActivityChart"), activityOptions).render();
            }

            // User Monthly Statistics Chart
            const monthlyData = [
                { name: 'Candidatures ce mois', value: <?= $activity_stats['applications_this_month'] ?>, color: '#2563eb' },
                { name: 'Emplois sauvegardés ce mois', value: <?= $activity_stats['jobs_saved_this_month'] ?>, color: '#059669' }
            ];
            
            if (monthlyData.some(item => item.value > 0)) {
                const monthlyOptions = {
                    series: [{
                        name: 'Activité mensuelle',
                        data: monthlyData.map(item => item.value)
                    }],
                    chart: {
                        type: 'bar',
                        height: 250,
                        toolbar: { show: false }
                    },
                    xaxis: {
                        categories: monthlyData.map(item => item.name)
                    },
                    colors: monthlyData.map(item => item.color),
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: false,
                        }
                    }
                };
                
                new ApexCharts(document.querySelector("#userMonthlyChart"), monthlyOptions).render();
            }
        });
        
        // Add mobile toggle button if needed
        if (window.innerWidth <= 991) {
            // Add mobile menu functionality here
        }
    </script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>