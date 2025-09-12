<?php 
// Simple test dashboard to check if content displays
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
    <title>Test Dashboard - <?= htmlspecialchars($employer['company_name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .card { border: 1px solid #ccc; padding: 20px; margin: 10px 0; border-radius: 5px; }
        .stats { display: flex; gap: 20px; }
        .stat { background: #f0f0f0; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Test Employer Dashboard</h1>
    <p><strong>Company:</strong> <?= htmlspecialchars($employer['company_name']) ?></p>
    <p><strong>User ID:</strong> <?= $user_id ?></p>
    <p><strong>Profile ID:</strong> <?= $employer_profile_id ?></p>
    
    <div class="stats">
        <div class="stat">
            <h3><?= $total_jobs ?></h3>
            <p>Total Jobs</p>
        </div>
        <div class="stat">
            <h3><?= $active_jobs ?></h3>
            <p>Active Jobs</p>
        </div>
        <div class="stat">
            <h3><?= $total_applications ?></h3>
            <p>Total Applications</p>
        </div>
        <div class="stat">
            <h3><?= $new_applications ?></h3>
            <p>New Applications</p>
        </div>
    </div>
    
    <div class="card">
        <h2>Session Data</h2>
        <pre><?= print_r($_SESSION, true) ?></pre>
    </div>
    
    <div class="card">
        <h2>Employer Data</h2>
        <pre><?= print_r($employer, true) ?></pre>
    </div>
    
    <p><a href="dashboard.php">Go to Full Dashboard</a></p>
    <p><a href="../login.php">Logout</a></p>
</body>
</html>
