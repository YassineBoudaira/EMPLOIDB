<?php 
// Debug version of dashboard to check content visibility
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';
include __DIR__ . '/../include/connexion.php';

// Check if user is logged in as employer
if (!Security::isLoggedIn() || !isset($_SESSION['role']) || $_SESSION['role'] !== 'employer') {
    echo "<h1>Not logged in as employer</h1>";
    echo "<p>Session data: " . print_r($_SESSION, true) . "</p>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Get employer information
$employer = $db->fetch("SELECT * FROM employers WHERE user_id = ?", [$user_id]);
if (!$employer) {
    echo "<h1>Employer profile not found</h1>";
    exit();
}

// Get employer profile ID
$employer_profile = $db->fetch("SELECT id FROM profiles WHERE user_id = ?", [$user_id]);
if (!$employer_profile) {
    echo "<h1>Employer profile ID not found</h1>";
    exit();
}

$employer_profile_id = $employer_profile['id'];

// Get statistics
$total_jobs = $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE profile_id = ?", [$employer_profile_id])['count'];
$active_jobs = $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE profile_id = ? AND status = 'active'", [$employer_profile_id])['count'];
$total_applications = $db->fetch("SELECT COUNT(*) as count FROM postulation p JOIN annonces a ON p.annonce_id = a.id WHERE a.profile_id = ?", [$employer_profile_id])['count'];
$new_applications = $db->fetch("SELECT COUNT(*) as count FROM postulation p JOIN annonces a ON p.annonce_id = a.id WHERE a.profile_id = ? AND p.status = 'applied'", [$employer_profile_id])['count'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Dashboard - <?= htmlspecialchars($employer['company_name']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: #f8fafc; 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            padding: 20px; 
        }
        .debug-card { 
            background: white; 
            border: 1px solid #e2e8f0; 
            border-radius: 8px; 
            padding: 20px; 
            margin: 20px 0; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); 
        }
        .stat-card { 
            background: linear-gradient(135deg, #2563eb, #1d4ed8); 
            color: white; 
            padding: 20px; 
            border-radius: 12px; 
            text-align: center; 
            margin: 10px; 
        }
        .stat-number { 
            font-size: 2rem; 
            font-weight: bold; 
            margin-bottom: 5px; 
        }
        .stat-label { 
            font-size: 0.9rem; 
            opacity: 0.9; 
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h1 class="mb-4">
            <i class="fas fa-chart-line text-primary"></i> 
            Debug Dashboard - <?= htmlspecialchars($employer['company_name']) ?>
        </h1>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_jobs ?></div>
                    <div class="stat-label">Total Jobs</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $active_jobs ?></div>
                    <div class="stat-label">Active Jobs</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_applications ?></div>
                    <div class="stat-label">Total Applications</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $new_applications ?></div>
                    <div class="stat-label">New Applications</div>
                </div>
            </div>
        </div>
        
        <!-- Debug Information -->
        <div class="debug-card">
            <h3><i class="fas fa-bug text-warning"></i> Debug Information</h3>
            <div class="row">
                <div class="col-md-6">
                    <h5>Session Data:</h5>
                    <pre style="background: #f1f5f9; padding: 15px; border-radius: 5px; font-size: 12px;"><?= htmlspecialchars(print_r($_SESSION, true)) ?></pre>
                </div>
                <div class="col-md-6">
                    <h5>Employer Data:</h5>
                    <pre style="background: #f1f5f9; padding: 15px; border-radius: 5px; font-size: 12px;"><?= htmlspecialchars(print_r($employer, true)) ?></pre>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="debug-card">
            <h3><i class="fas fa-bolt text-warning"></i> Quick Actions</h3>
            <div class="row">
                <div class="col-md-3">
                    <a href="post_job.php" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-plus me-2"></i>Post Job
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="manage_jobs.php" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-briefcase me-2"></i>Manage Jobs
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="applications.php" class="btn btn-outline-success w-100 mb-2">
                        <i class="fas fa-file-alt me-2"></i>Applications
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="profile.php" class="btn btn-outline-secondary w-100 mb-2">
                        <i class="fas fa-building me-2"></i>Profile
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Navigation Links -->
        <div class="debug-card">
            <h3><i class="fas fa-link text-info"></i> Navigation Links</h3>
            <div class="row">
                <div class="col-md-6">
                    <h5>Internal Links:</h5>
                    <ul>
                        <li><a href="dashboard.php">Full Dashboard</a></li>
                        <li><a href="post_job.php">Post Job</a></li>
                        <li><a href="manage_jobs.php">Manage Jobs</a></li>
                        <li><a href="applications.php">Applications</a></li>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="settings.php">Settings</a></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5>External Links:</h5>
                    <ul>
                        <li><a href="../login.php">Main Login</a></li>
                        <li><a href="../index.php">Home Page</a></li>
                        <li><a href="../admin/dashboard.php">Admin Dashboard</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
