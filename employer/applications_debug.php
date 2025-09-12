<?php
// Debug version of applications page to identify blank page issues
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Start output buffering to catch any issues
ob_start();

echo "<!-- Debug: Starting applications page -->\n";

try {
    // Include configuration first (before any session starts)
    echo "<!-- Debug: Including config -->\n";
    include __DIR__ . '/../include/config.php';
    echo "<!-- Debug: Config included successfully -->\n";
    
    echo "<!-- Debug: Including session -->\n";
    include __DIR__ . '/../include/sess.php';
    echo "<!-- Debug: Session included successfully -->\n";
    
    echo "<!-- Debug: Including database -->\n";
    include __DIR__ . '/../include/connexion.php';
    echo "<!-- Debug: Database included successfully -->\n";
    
    // Check if user is logged in as employer
    echo "<!-- Debug: Checking login status -->\n";
    if (!Security::isLoggedIn()) {
        echo "<!-- Debug: User not logged in, redirecting -->\n";
        header('Location: ../login.php');
        exit();
    }
    
    if (!isset($_SESSION['role'])) {
        echo "<!-- Debug: No role in session, redirecting -->\n";
        header('Location: ../login.php');
        exit();
    }
    
    if ($_SESSION['role'] !== 'employer') {
        echo "<!-- Debug: Role is not employer: " . $_SESSION['role'] . ", redirecting -->\n";
        header('Location: ../login.php');
        exit();
    }
    
    echo "<!-- Debug: User is logged in as employer -->\n";
    
    $user_id = $_SESSION['user_id'];
    echo "<!-- Debug: User ID: " . $user_id . " -->\n";
    
    // Get employer information
    echo "<!-- Debug: Getting employer information -->\n";
    $employer = $db->fetch("SELECT * FROM employers WHERE user_id = ?", [$user_id]);
    if (!$employer) {
        echo "<!-- Debug: No employer record found, redirecting -->\n";
        header('Location: register.php');
        exit();
    }
    
    echo "<!-- Debug: Employer found: " . $employer['company_name'] . " -->\n";
    
    // Get employer profile ID
    echo "<!-- Debug: Getting employer profile -->\n";
    $employer_profile = $db->fetch("SELECT id FROM profiles WHERE user_id = ?", [$user_id]);
    if (!$employer_profile) {
        echo "<!-- Debug: No employer profile found, redirecting -->\n";
        header('Location: register.php');
        exit();
    }
    
    $employer_profile_id = $employer_profile['id'];
    echo "<!-- Debug: Employer profile ID: " . $employer_profile_id . " -->\n";
    
    // Get applications for this employer's jobs
    echo "<!-- Debug: Getting applications -->\n";
    $applications = $db->fetchAll("
        SELECT 
            a.*,
            j.title as job_title,
            j.company_name,
            u.username,
            u.email,
            p.first_name,
            p.last_name,
            p.phone,
            p.cv_path
        FROM applications a
        JOIN annonces j ON a.job_id = j.id
        JOIN users u ON a.user_id = u.id
        LEFT JOIN profiles p ON u.id = p.user_id
        WHERE j.profile_id = ?
        ORDER BY a.created_at DESC
    ", [$employer_profile_id]);
    
    echo "<!-- Debug: Found " . count($applications) . " applications -->\n";
    
    // Count applications by status
    $stats = [
        'total' => count($applications),
        'pending' => 0,
        'viewed' => 0,
        'shortlisted' => 0,
        'interviewed' => 0,
        'hired' => 0,
        'rejected' => 0
    ];
    
    foreach ($applications as $app) {
        if (isset($stats[$app['status']])) {
            $stats[$app['status']]++;
        }
    }
    
    echo "<!-- Debug: Statistics calculated -->\n";
    
    // Handle status updates
    if ($_POST && isset($_POST['action']) && isset($_POST['application_id'])) {
        echo "<!-- Debug: Processing status update -->\n";
        $application_id = (int)$_POST['application_id'];
        $action = $_POST['action'];
        
        $valid_statuses = ['pending', 'viewed', 'shortlisted', 'interviewed', 'hired', 'rejected'];
        
        if (in_array($action, $valid_statuses)) {
            $db->execute("UPDATE applications SET status = ? WHERE id = ? AND job_id IN (SELECT id FROM annonces WHERE profile_id = ?)", 
                        [$action, $application_id, $employer_profile_id]);
            
            // Refresh page to show updated data
            header('Location: applications.php');
            exit();
        }
    }
    
    echo "<!-- Debug: All PHP processing completed, starting HTML output -->\n";
    
} catch (Exception $e) {
    echo "<!-- Debug: Exception caught: " . $e->getMessage() . " -->\n";
    echo "<!-- Debug: Exception in file: " . $e->getFile() . " line " . $e->getLine() . " -->\n";
    ob_end_flush();
    exit();
} catch (Error $e) {
    echo "<!-- Debug: Fatal error caught: " . $e->getMessage() . " -->\n";
    echo "<!-- Debug: Error in file: " . $e->getFile() . " line " . $e->getLine() . " -->\n";
    ob_end_flush();
    exit();
}

// Function to get status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending': return 'bg-warning';
        case 'viewed': return 'bg-info';
        case 'shortlisted': return 'bg-primary';
        case 'interviewed': return 'bg-secondary';
        case 'hired': return 'bg-success';
        case 'rejected': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

// Function to get status text
function getStatusText($status) {
    switch ($status) {
        case 'pending': return 'En attente';
        case 'viewed': return 'Vue';
        case 'shortlisted': return 'Sélectionnée';
        case 'interviewed': return 'Entretien';
        case 'hired': return 'Embauchée';
        case 'rejected': return 'Refusée';
        default: return 'Inconnu';
    }
}

echo "<!-- Debug: Functions defined, starting HTML -->\n";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les candidatures - <?= htmlspecialchars($employer['company_name']) ?> | EMPLOIDB</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        
        .employer-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .employer-sidebar {
            width: 280px;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .employer-content {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .stats-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }
        
        .stats-label {
            color: #6c757d;
            margin: 0.5rem 0 0 0;
        }
        
        .application-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #007bff;
        }
        
        .candidate-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #007bff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .employer-sidebar {
                transform: translateX(-100%);
            }
            
            .employer-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php echo "<!-- Debug: HTML head completed -->\n"; ?>
    
    <div class="employer-wrapper">
        <!-- Include Enhanced Employer Sidebar -->
        <?php 
        echo "<!-- Debug: Including sidebar -->\n";
        include 'includes/employer_sidebar.php'; 
        echo "<!-- Debug: Sidebar included -->\n";
        ?>
        
        <!-- Main Content -->
        <div class="employer-content">
            <?php echo "<!-- Debug: Starting main content -->\n"; ?>
            
            <!-- Page Header -->
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="mb-2">
                            <i class="fas fa-file-alt me-2"></i>
                            Gérer les candidatures
                        </h1>
                        <p class="text-muted mb-0">
                            Gérez et suivez toutes les candidatures reçues pour vos offres d'emploi
                        </p>
                    </div>
                    <div>
                        <a href="manage_jobs.php" class="btn btn-outline-primary me-2">
                            <i class="fas fa-briefcase me-1"></i>
                            Mes offres
                        </a>
                        <a href="post_job.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Nouvelle offre
                        </a>
                    </div>
                </div>
            </div>
            
            <?php echo "<!-- Debug: Header completed -->\n"; ?>
            
            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="stats-card">
                        <h3 class="stats-number text-primary"><?= $stats['total'] ?></h3>
                        <p class="stats-label">Total</p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card">
                        <h3 class="stats-number text-warning"><?= $stats['pending'] ?></h3>
                        <p class="stats-label">En attente</p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card">
                        <h3 class="stats-number text-info"><?= $stats['viewed'] ?></h3>
                        <p class="stats-label">Vues</p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card">
                        <h3 class="stats-number text-primary"><?= $stats['shortlisted'] ?></h3>
                        <p class="stats-label">Sélectionnées</p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card">
                        <h3 class="stats-number text-secondary"><?= $stats['interviewed'] ?></h3>
                        <p class="stats-label">Entretiens</p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="stats-card">
                        <h3 class="stats-number text-success"><?= $stats['hired'] ?></h3>
                        <p class="stats-label">Embauchées</p>
                    </div>
                </div>
            </div>
            
            <?php echo "<!-- Debug: Statistics completed -->\n"; ?>
            
            <!-- Applications List -->
            <?php if (empty($applications)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Aucune candidature reçue</h3>
                    <p class="text-muted">
                        Les candidatures apparaîtront ici une fois que vous aurez publié des offres d'emploi.
                    </p>
                    <a href="post_job.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Publier une offre
                    </a>
                </div>
            <?php else: ?>
                <?php echo "<!-- Debug: Starting applications loop -->\n"; ?>
                <?php foreach ($applications as $application): ?>
                    <div class="application-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex">
                                <div class="candidate-avatar me-3">
                                    <?= strtoupper(substr($application['first_name'] ?? 'U', 0, 1) . substr($application['last_name'] ?? 'N', 0, 1)) ?>
                                </div>
                                <div>
                                    <h5 class="mb-1">
                                        <?= htmlspecialchars(($application['first_name'] ?? '') . ' ' . ($application['last_name'] ?? '')) ?>
                                    </h5>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-briefcase me-1"></i>
                                        <?= htmlspecialchars($application['job_title']) ?>
                                    </p>
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-envelope me-1"></i>
                                        <?= htmlspecialchars($application['email']) ?>
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        Candidature du <?= date('d/m/Y à H:i', strtotime($application['created_at'])) ?>
                                    </small>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="mb-2">
                                    <span class="status-badge <?= getStatusBadgeClass($application['status']) ?> text-white">
                                        <?= getStatusText($application['status']) ?>
                                    </span>
                                </div>
                                <div class="btn-group" role="group">
                                    <a href="view_application.php?id=<?= $application['id'] ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>
                                        Voir
                                    </a>
                                    <?php if (!empty($application['cv_path'])): ?>
                                        <a href="../<?= htmlspecialchars($application['cv_path']) ?>" 
                                           class="btn btn-outline-success btn-sm" target="_blank">
                                            <i class="fas fa-download me-1"></i>
                                            CV
                                        </a>
                                    <?php endif; ?>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-cog me-1"></i>
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="application_id" value="<?= $application['id'] ?>">
                                                    <input type="hidden" name="action" value="viewed">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-eye me-2"></i>Marquer comme vue
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="application_id" value="<?= $application['id'] ?>">
                                                    <input type="hidden" name="action" value="shortlisted">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-star me-2"></i>Sélectionner
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="application_id" value="<?= $application['id'] ?>">
                                                    <input type="hidden" name="action" value="interviewed">
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="fas fa-handshake me-2"></i>Programmer entretien
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="application_id" value="<?= $application['id'] ?>">
                                                    <input type="hidden" name="action" value="hired">
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="fas fa-check-circle me-2"></i>Embaucher
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="application_id" value="<?= $application['id'] ?>">
                                                    <input type="hidden" name="action" value="rejected">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-times-circle me-2"></i>Refuser
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php echo "<!-- Debug: Applications loop completed -->\n"; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php echo "<!-- Debug: Main content completed -->\n"; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php echo "<!-- Debug: Scripts included -->\n"; ?>
</body>
</html>

<?php 
echo "<!-- Debug: HTML completed -->\n";
ob_end_flush();
?>

