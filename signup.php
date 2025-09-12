

<?php
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';

// Check if user is already logged in
if (Security::isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$errors = [];
$success = false;

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de sécurité invalide');
        }
        
        // Get and sanitize form data
        $username = Security::sanitizeInput($_POST['username'] ?? '');
        $email = Security::sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $first_name = Security::sanitizeInput($_POST['first_name'] ?? '');
        $last_name = Security::sanitizeInput($_POST['last_name'] ?? '');
        $phone = Security::sanitizeInput($_POST['phone'] ?? '');
        $date_of_birth = Security::sanitizeInput($_POST['date_of_birth'] ?? '');
        $gender = Security::sanitizeInput($_POST['gender'] ?? '');
        $address = Security::sanitizeInput($_POST['address'] ?? '');
        $city = Security::sanitizeInput($_POST['city'] ?? '');
        $postal_code = Security::sanitizeInput($_POST['postal_code'] ?? '');
        $country = Security::sanitizeInput($_POST['country'] ?? '');
        $education_level = Security::sanitizeInput($_POST['education_level'] ?? '');
        $field_of_study = Security::sanitizeInput($_POST['field_of_study'] ?? '');
        $institution = Security::sanitizeInput($_POST['institution'] ?? '');
        $graduation_year = Security::sanitizeInput($_POST['graduation_year'] ?? '');
        $experience_years = Security::sanitizeInput($_POST['experience_years'] ?? '');
        $current_position = Security::sanitizeInput($_POST['current_position'] ?? '');
        $skills = Security::sanitizeInput($_POST['skills'] ?? '');
        $languages = Security::sanitizeInput($_POST['languages'] ?? '');
        $linkedin = Security::sanitizeInput($_POST['linkedin'] ?? '');
        $website = Security::sanitizeInput($_POST['website'] ?? '');
        $about_me = Security::sanitizeInput($_POST['about_me'] ?? '');
        $salary_expectation = Security::sanitizeInput($_POST['salary_expectation'] ?? '');
        $preferred_job_type = Security::sanitizeInput($_POST['preferred_job_type'] ?? '');
        $willing_to_relocate = Security::sanitizeInput($_POST['willing_to_relocate'] ?? '');
        $remote_work = Security::sanitizeInput($_POST['remote_work'] ?? '');
        
        // Validation
        if (empty($username) || strlen($username) < 3) {
            throw new Exception('Le nom d\'utilisateur doit contenir au moins 3 caractères');
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Une adresse email valide est requise');
        }
        
        if (empty($password) || strlen($password) < 8) {
            throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
        }
        
        if ($password !== $confirm_password) {
            throw new Exception('Les mots de passe ne correspondent pas');
        }
        
        if (empty($first_name) || empty($last_name)) {
            throw new Exception('Le prénom et le nom sont requis');
        }
        
        // Check if username or email already exists
        $existing_user = $db->fetch("SELECT id FROM users WHERE user = ? OR email = ?", [$username, $email]);
        if ($existing_user) {
            throw new Exception('Ce nom d\'utilisateur ou email existe déjà');
        }
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Create user account
            $user_data = [
                'user' => $username,
                'email' => $email,
                'pass' => Security::hashPassword($password),
                'role' => 'user',
                'account_status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $user_id = $db->insert('users', $user_data);
            
            // Create candidate profile
            $candidate_data = [
                'user_id' => $user_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone' => $phone,
                'date_of_birth' => $date_of_birth,
                'gender' => $gender,
                'address' => $address,
                'city' => $city,
                'postal_code' => $postal_code,
                'country' => $country,
                'education_level' => $education_level,
                'field_of_study' => $field_of_study,
                'institution' => $institution,
                'graduation_year' => $graduation_year,
                'experience_years' => $experience_years,
                'current_position' => $current_position,
                'skills' => $skills,
                'languages' => $languages,
                'linkedin' => $linkedin,
                'website' => $website,
                'about_me' => $about_me,
                'salary_expectation' => $salary_expectation,
                'preferred_job_type' => $preferred_job_type,
                'willing_to_relocate' => $willing_to_relocate,
                'remote_work' => $remote_work,
                'profile_completion' => 100,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $candidate_id = $db->insert('candidates', $candidate_data);
            
            // Commit transaction
            $db->commit();
            
            $success = true;
            
            // Log successful registration
            error_log("Candidate registration successful: {$email} (ID: {$user_id})");
            
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
        
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
        error_log("Candidate registration failed: " . $e->getMessage());
    }
}

// Generate CSRF token
$csrf_token = Security::generateCSRFToken();

// Get countries for dropdown
$countries = [
    'Maroc', 'France', 'Canada', 'Belgique', 'Suisse', 'Allemagne', 'Espagne', 'Italie',
    'Pays-Bas', 'Royaume-Uni', 'États-Unis', 'Australie', 'Japon', 'Corée du Sud',
    'Singapour', 'Émirats arabes unis', 'Qatar', 'Arabie saoudite', 'Tunisie', 'Algérie'
];

// Get education levels
$education_levels = [
    'Baccalauréat', 'Bac+2 (DUT/BTS)', 'Bac+3 (Licence)', 'Bac+4 (Maîtrise)',
    'Bac+5 (Master)', 'Doctorat', 'Autre'
];

// Get job types
$job_types = [
    'Temps plein', 'Temps partiel', 'CDI', 'CDD', 'Stage', 'Freelance', 'Contrat'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Candidat - EMPLOIDB</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .registration-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .registration-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        /* Professional Sections */
        .professional-section {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        }
        
        .professional-section h3 {
            color: var(--dark-color);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .professional-section h3 i {
            color: var(--primary-color);
            margin-right: 15px;
        }
        
        .enhanced-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }
        
        .enhanced-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .enhanced-card-title {
            color: var(--dark-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        
        .enhanced-card-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-action-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            color: white;
        }
        
        .btn-action-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
            color: white;
        }
        
        .btn-action-warning {
            background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
            color: white;
        }
        
        .btn-action-info {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
            color: white;
        }
        
        .color-scheme-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
        }
        
        .color-scheme-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
        }
        
        .color-scheme-warning {
            background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
        }
        
        .color-scheme-info {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 5px;
        }
        
        .stats-label {
            color: #64748b;
            font-weight: 500;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
            display: block;
        }
        
        .registration-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .registration-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .registration-body {
            padding: 40px;
        }
        
        .form-section {
            background: #f8fafc;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .btn-register {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }
        
        .alert-danger {
            background: #fef2f2;
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }
        
        .alert-success {
            background: #f0fdf4;
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }
        
        .progress-bar {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
        }
        
        .registration-footer {
            text-align: center;
            padding: 20px 40px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
        }
        
        .registration-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .registration-footer a:hover {
            text-decoration: underline;
        }
        
        .required {
            color: var(--danger-color);
        }
        
        .form-text {
            font-size: 0.875rem;
            color: var(--secondary-color);
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="registration-container">
    <div class="registration-header">
        <h1><i class="fas fa-user-plus me-2"></i>EMPLOIDB</h1>
        <p>Créez votre compte candidat et trouvez votre emploi de rêve</p>
    </div>
    
    <div class="registration-body">
        <!-- Professional Header Section -->
        <div class="professional-section">
            <h3><i class="fas fa-user-plus"></i>Inscription Candidat</h3>
            <div class="row">
                <div class="col-md-8">
                    <div class="enhanced-card">
                        <div class="enhanced-card-header">
                            <h4 class="enhanced-card-title">Créez votre profil professionnel</h4>
                            <div class="enhanced-card-actions">
                                <button class="btn-action btn-action-primary" onclick="window.location.href='login.php'">
                                    <i class="fas fa-sign-in-alt"></i>Se connecter
                                </button>
                                <button class="btn-action btn-action-success" onclick="window.location.href='employer/register.php'">
                                    <i class="fas fa-building"></i>Employeur
                                </button>
                                <button class="btn-action btn-action-warning" onclick="window.location.href='advertiser/register.php'">
                                    <i class="fas fa-bullhorn"></i>Annoncer
                                </button>
                            </div>
                        </div>
                        <p class="text-muted">Rejoignez notre communauté de professionnels et accédez à des milliers d'offres d'emploi au Maroc.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="action-buttons">
                        <button class="btn-action btn-action-primary" onclick="window.location.href='index.php'">
                            <i class="fas fa-home"></i>Accueil
                        </button>
                        <button class="btn-action btn-action-success" onclick="window.location.href='enhanced_search.php'">
                            <i class="fas fa-search"></i>Rechercher des Emplois
                        </button>
                        <button class="btn-action btn-action-info" onclick="window.location.href='contact.php'">
                            <i class="fas fa-envelope"></i>Nous Contacter
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Statistics Section -->
        <div class="professional-section">
            <h3><i class="fas fa-chart-bar"></i>Nos Statistiques</h3>
            <div class="row">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon color-scheme-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stats-number">10,000+</div>
                        <div class="stats-label">Candidats Inscrits</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon color-scheme-success">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="stats-number">5,000+</div>
                        <div class="stats-label">Offres d'Emploi</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon color-scheme-warning">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stats-number">500+</div>
                        <div class="stats-label">Entreprises</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon color-scheme-info">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div class="stats-number">2,000+</div>
                        <div class="stats-label">Placements Réussis</div>
                    </div>
                </div>
            </div>
        </div>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($errors[0]) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Inscription réussie!</strong> Votre compte a été créé avec succès. 
                <a href="login.php" class="alert-link">Cliquez ici pour vous connecter</a>
            </div>
        <?php endif; ?>
        
        <form method="post" action="" id="registrationForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            
            <!-- Account Information -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-user-circle text-primary"></i>
                    Informations du compte
                </h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="username" class="form-label">
                                Nom d'utilisateur <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   id="username" 
                                   name="username" 
                                   class="form-control" 
                                   placeholder="Votre nom d'utilisateur"
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                   required>
                            <div class="form-text">Minimum 3 caractères</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="form-label">
                                Adresse Email <span class="required">*</span>
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-control" 
                                   placeholder="votre@email.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password" class="form-label">
                                Mot de passe <span class="required">*</span>
                            </label>
                            <input type="password" 
                                   id="password" 
                                   name="password" 
                                   class="form-control" 
                                   placeholder="Votre mot de passe"
                                   required>
                            <div class="form-text">Minimum 8 caractères</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                Confirmer le mot de passe <span class="required">*</span>
                            </label>
                            <input type="password" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   class="form-control" 
                                   placeholder="Confirmez votre mot de passe"
                                   required>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Personal Information -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-id-card text-primary"></i>
                    Informations personnelles
                </h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="first_name" class="form-label">
                                Prénom <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   class="form-control" 
                                   placeholder="Votre prénom"
                                   value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="last_name" class="form-label">
                                Nom <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   id="last_name" 
                                   name="last_name" 
                                   class="form-control" 
                                   placeholder="Votre nom"
                                   value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                                   required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   class="form-control" 
                                   placeholder="+212 6 12 34 56 78"
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="date_of_birth" class="form-label">Date de naissance</label>
                            <input type="date" 
                                   id="date_of_birth" 
                                   name="date_of_birth" 
                                   class="form-control"
                                   value="<?= htmlspecialchars($_POST['date_of_birth'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="gender" class="form-label">Genre</label>
                            <select id="gender" name="gender" class="form-select">
                                <option value="">Sélectionnez</option>
                                <option value="Homme" <?= ($_POST['gender'] ?? '') === 'Homme' ? 'selected' : '' ?>>Homme</option>
                                <option value="Femme" <?= ($_POST['gender'] ?? '') === 'Femme' ? 'selected' : '' ?>>Femme</option>
                                <option value="Autre" <?= ($_POST['gender'] ?? '') === 'Autre' ? 'selected' : '' ?>>Autre</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="country" class="form-label">Pays</label>
                            <select id="country" name="country" class="form-select">
                                <option value="">Sélectionnez un pays</option>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?= $country ?>" <?= ($_POST['country'] ?? '') === $country ? 'selected' : '' ?>>
                                        <?= $country ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="address" class="form-label">Adresse</label>
                            <textarea id="address" 
                                      name="address" 
                                      class="form-control" 
                                      rows="3" 
                                      placeholder="Votre adresse complète"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="city" class="form-label">Ville</label>
                            <input type="text" 
                                   id="city" 
                                   name="city" 
                                   class="form-control" 
                                   placeholder="Votre ville"
                                   value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="postal_code" class="form-label">Code postal</label>
                            <input type="text" 
                                   id="postal_code" 
                                   name="postal_code" 
                                   class="form-control" 
                                   placeholder="Code postal"
                                   value="<?= htmlspecialchars($_POST['postal_code'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Education Information -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-graduation-cap text-primary"></i>
                    Formation et éducation
                </h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="education_level" class="form-label">Niveau d'éducation</label>
                            <select id="education_level" name="education_level" class="form-select">
                                <option value="">Sélectionnez</option>
                                <?php foreach ($education_levels as $level): ?>
                                    <option value="<?= $level ?>" <?= ($_POST['education_level'] ?? '') === $level ? 'selected' : '' ?>>
                                        <?= $level ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="field_of_study" class="form-label">Domaine d'études</label>
                            <input type="text" 
                                   id="field_of_study" 
                                   name="field_of_study" 
                                   class="form-control" 
                                   placeholder="Ex: Informatique, Marketing, Finance..."
                                   value="<?= htmlspecialchars($_POST['field_of_study'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="institution" class="form-label">Établissement</label>
                            <input type="text" 
                                   id="institution" 
                                   name="institution" 
                                   class="form-control" 
                                   placeholder="Nom de l'établissement"
                                   value="<?= htmlspecialchars($_POST['institution'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="graduation_year" class="form-label">Année d'obtention</label>
                            <input type="number" 
                                   id="graduation_year" 
                                   name="graduation_year" 
                                   class="form-control" 
                                   placeholder="2023"
                                   min="1950" 
                                   max="<?= date('Y') + 5 ?>"
                                   value="<?= htmlspecialchars($_POST['graduation_year'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Professional Information -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-briefcase text-primary"></i>
                    Informations professionnelles
                </h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="experience_years" class="form-label">Années d'expérience</label>
                            <select id="experience_years" name="experience_years" class="form-select">
                                <option value="">Sélectionnez</option>
                                <option value="0" <?= ($_POST['experience_years'] ?? '') === '0' ? 'selected' : '' ?>>Débutant (0-1 an)</option>
                                <option value="1-3" <?= ($_POST['experience_years'] ?? '') === '1-3' ? 'selected' : '' ?>>1-3 ans</option>
                                <option value="3-5" <?= ($_POST['experience_years'] ?? '') === '3-5' ? 'selected' : '' ?>>3-5 ans</option>
                                <option value="5-10" <?= ($_POST['experience_years'] ?? '') === '5-10' ? 'selected' : '' ?>>5-10 ans</option>
                                <option value="10+" <?= ($_POST['experience_years'] ?? '') === '10+' ? 'selected' : '' ?>>10+ ans</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="current_position" class="form-label">Poste actuel</label>
                            <input type="text" 
                                   id="current_position" 
                                   name="current_position" 
                                   class="form-control" 
                                   placeholder="Votre poste actuel"
                                   value="<?= htmlspecialchars($_POST['current_position'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="skills" class="form-label">Compétences</label>
                            <textarea id="skills" 
                                      name="skills" 
                                      class="form-control" 
                                      rows="3" 
                                      placeholder="Ex: PHP, JavaScript, MySQL, Photoshop, Gestion de projet..."><?= htmlspecialchars($_POST['skills'] ?? '') ?></textarea>
                            <div class="form-text">Séparez les compétences par des virgules</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="languages" class="form-label">Langues</label>
                            <textarea id="languages" 
                                      name="languages" 
                                      class="form-control" 
                                      rows="2" 
                                      placeholder="Ex: Français (Natif), Anglais (Courant), Arabe (Intermédiaire)..."><?= htmlspecialchars($_POST['languages'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Preferences -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-cog text-primary"></i>
                    Préférences et attentes
                </h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="salary_expectation" class="form-label">Attentes salariales</label>
                            <input type="text" 
                                   id="salary_expectation" 
                                   name="salary_expectation" 
                                   class="form-control" 
                                   placeholder="Ex: 8000-12000 MAD"
                                   value="<?= htmlspecialchars($_POST['salary_expectation'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="preferred_job_type" class="form-label">Type de contrat préféré</label>
                            <select id="preferred_job_type" name="preferred_job_type" class="form-select">
                                <option value="">Sélectionnez</option>
                                <?php foreach ($job_types as $type): ?>
                                    <option value="<?= $type ?>" <?= ($_POST['preferred_job_type'] ?? '') === $type ? 'selected' : '' ?>>
                                        <?= $type ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="willing_to_relocate" class="form-label">Disponible pour déménager</label>
                            <select id="willing_to_relocate" name="willing_to_relocate" class="form-select">
                                <option value="">Sélectionnez</option>
                                <option value="Oui" <?= ($_POST['willing_to_relocate'] ?? '') === 'Oui' ? 'selected' : '' ?>>Oui</option>
                                <option value="Non" <?= ($_POST['willing_to_relocate'] ?? '') === 'Non' ? 'selected' : '' ?>>Non</option>
                                <option value="Peut-être" <?= ($_POST['willing_to_relocate'] ?? '') === 'Peut-être' ? 'selected' : '' ?>>Peut-être</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="remote_work" class="form-label">Télétravail</label>
                            <select id="remote_work" name="remote_work" class="form-select">
                                <option value="">Sélectionnez</option>
                                <option value="Sur site" <?= ($_POST['remote_work'] ?? '') === 'Sur site' ? 'selected' : '' ?>>Sur site</option>
                                <option value="Hybride" <?= ($_POST['remote_work'] ?? '') === 'Hybride' ? 'selected' : '' ?>>Hybride</option>
                                <option value="100% télétravail" <?= ($_POST['remote_work'] ?? '') === '100% télétravail' ? 'selected' : '' ?>>100% télétravail</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Social Links -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-share-alt text-primary"></i>
                    Liens professionnels
                </h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="linkedin" class="form-label">LinkedIn</label>
                            <input type="url" 
                                   id="linkedin" 
                                   name="linkedin" 
                                   class="form-control" 
                                   placeholder="https://linkedin.com/in/votre-profil"
                                   value="<?= htmlspecialchars($_POST['linkedin'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="website" class="form-label">Site web personnel</label>
                            <input type="url" 
                                   id="website" 
                                   name="website" 
                                   class="form-control" 
                                   placeholder="https://votre-site.com"
                                   value="<?= htmlspecialchars($_POST['website'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- About Me -->
            <div class="form-section">
                <h3 class="section-title">
                    <i class="fas fa-user-edit text-primary"></i>
                    À propos de moi
                </h3>
                <div class="form-group">
                    <label for="about_me" class="form-label">Présentation</label>
                    <textarea id="about_me" 
                              name="about_me" 
                              class="form-control" 
                              rows="5" 
                              placeholder="Présentez-vous brièvement, vos objectifs professionnels, vos passions..."><?= htmlspecialchars($_POST['about_me'] ?? '') ?></textarea>
                    <div class="form-text">Maximum 500 caractères</div>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-register">
                    <i class="fas fa-user-plus me-2"></i>
                    Créer mon compte candidat
                </button>
            </div>
        </form>
    </div>
    
    <div class="registration-footer">
        <p>Vous avez déjà un compte? <a href="login.php">Connectez-vous ici</a></p>
        <p><a href="index.php">← Retour à l'accueil</a></p>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Form validation
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Les mots de passe ne correspondent pas!');
        return false;
    }
    
    if (password.length < 8) {
        e.preventDefault();
        alert('Le mot de passe doit contenir au moins 8 caractères!');
        return false;
    }
});

// Character counter for about me
document.getElementById('about_me').addEventListener('input', function() {
    const maxLength = 500;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    if (currentLength > maxLength) {
        this.value = this.value.substring(0, maxLength);
    }
});
</script>

</body>
</html>

