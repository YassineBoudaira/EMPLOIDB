<?php 
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';
include 'include/LoginSecurity.php';

// Initialize login security system
$loginSecurity = new LoginSecurity($db);

// Check if user is already logged in
if (Security::isLoggedIn()) {
    // Redirect based on role
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } elseif ($_SESSION['role'] === 'employer') {
        header('Location: employer/dashboard.php');
    } elseif ($_SESSION['role'] === 'advertiser') {
        header('Location: advertiser/dashboard.php');
    } elseif ($_SESSION['role'] === 'user') {
        header('Location: candidate/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

$errors = [];
$success = false;

// Get client information
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de sécurité invalide');
        }
        
        $email = Security::sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validation
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Une adresse email valide est requise');
        }
        
        if (empty($password)) {
            throw new Exception('Le mot de passe est requis');
        }
        
        // Authenticate user with enhanced security
        $user = $loginSecurity->authenticate($email, $password, $ip, $userAgent);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['user'];
        $_SESSION['login_time'] = time();
        $_SESSION['session_id'] = session_id();
        
        // Get additional role-specific data
        if ($user['role'] === 'employer') {
            $employer = $db->fetch("SELECT * FROM employers WHERE user_id = ?", [$user['id']]);
            if ($employer) {
                $_SESSION['employer_id'] = $employer['id'];
                $_SESSION['company_name'] = $employer['company_name'];
            } else {
                // Employer user exists but no profile - redirect to employer registration
                header('Location: registration/select.php?message=complete_profile&type=warning');
                exit();
            }
        }
        
        if ($user['role'] === 'user') {
            $candidate = $db->fetch("SELECT * FROM candidates WHERE user_id = ?", [$user['id']]);
            if (!$candidate) {
                // User exists but no candidate profile - redirect to candidate registration
                header('Location: registration/select.php?message=complete_profile&type=warning');
                exit();
            }
        }
        
        if ($user['role'] === 'advertiser') {
            $advertiser = $db->fetch("SELECT * FROM advertisers WHERE user_id = ?", [$user['id']]);
            if (!$advertiser) {
                // Advertiser user exists but no profile - redirect to advertiser registration
                header('Location: registration/select.php?message=complete_profile&type=warning');
                exit();
            }
        }
        
        // Log successful login
        error_log("Login successful: {$user['email']} (ID: {$user['id']}, Role: {$user['role']})");
        
        // Redirect based on role
        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } elseif ($user['role'] === 'employer') {
            header('Location: employer/dashboard.php');
        } elseif ($user['role'] === 'advertiser') {
            header('Location: advertiser/dashboard.php');
        } elseif ($user['role'] === 'user') {
            header('Location: candidate/dashboard.php');
        } else {
            // Unknown role - redirect to main page
            header('Location: index.php');
        }
        exit();
        
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        
        // Check if error message contains redirect URL
        if (strpos($errorMessage, '|') !== false) {
            list($message, $redirectUrl) = explode('|', $errorMessage, 2);
            header('Location: ' . $redirectUrl);
            exit();
        }
        
        $errors[] = $errorMessage;
        error_log("Login failed: " . $errorMessage);
    }
}

// Generate CSRF token
$csrf_token = Security::generateCSRFToken();

// Get login statistics for display
$loginStats = $loginSecurity->getLoginStats(1); // Last 24 hours
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Sécurisée - EMPLOIDB</title>
    
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
            --purple-color: #8b5cf6;
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            position: relative;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .login-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        .login-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .security-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 0.8rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
            z-index: 1;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
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
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
            position: relative;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            background: white;
        }
        
        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            z-index: 2;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }
        
        .btn-outline-primary {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            border-radius: 12px;
            padding: 15px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }
        
        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
        }
        
        .alert-danger::before {
            background: #dc2626;
        }
        
        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
        }
        
        .alert-success::before {
            background: #16a34a;
        }
        
        .security-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: #1e40af;
        }
        
        .security-info i {
            color: var(--primary-color);
            margin-right: 8px;
        }
        
        .divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }
        
        .divider span {
            background: white;
            padding: 0 20px;
            color: #64748b;
            font-weight: 500;
        }
        
        .register-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .register-link a:hover {
            text-decoration: underline;
            color: #1d4ed8;
        }
        
        .login-stats {
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.8rem;
            color: var(--secondary-color);
            text-align: center;
        }
        
        .login-stats strong {
            color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .login-container {
                margin: 10px;
                max-width: 100%;
            }
            
            .login-header {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 2rem;
            }
            
            .login-body {
                padding: 30px 20px;
            }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-container fade-in">
        <div class="login-header">
            <div class="security-badge">
                <i class="fas fa-shield-alt"></i> Sécurisé
            </div>
            <h1><i class="fas fa-sign-in-alt me-2"></i>Connexion</h1>
            <p>Accédez à votre compte EMPLOIDB</p>
        </div>
        
        <div class="login-body">
            <div class="security-info">
                <i class="fas fa-info-circle"></i>
                <strong>Système sécurisé:</strong> Connexion chiffrée avec protection CSRF et limitation de tentatives.
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
                    Connexion réussie! Redirection en cours...
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-2"></i>Adresse Email
                    </label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="votre@email.com"
                           required
                           autocomplete="email">
                    <i class="fas fa-envelope input-icon"></i>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Mot de passe
                    </label>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="Votre mot de passe"
                           required
                           autocomplete="current-password">
                    <i class="fas fa-lock input-icon"></i>
                </div>
                
                <button type="submit" class="btn btn-primary" id="loginBtn">
                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                </button>
            </form>
            
            <div class="divider">
                <span>ou</span>
            </div>
            
            <div class="register-link">
                <p>Pas encore de compte? 
                    <a href="registration/select.php">
                        <i class="fas fa-user-plus me-1"></i>Créer un compte
                    </a>
                </p>
                <p>
                    <a href="forgot_password.php">
                        <i class="fas fa-key me-1"></i>Mot de passe oublié?
                    </a>
                </p>
            </div>
            
            <?php if (!empty($loginStats)): ?>
            <div class="login-stats">
                <i class="fas fa-chart-line me-1"></i>
                <strong><?= $loginStats['successful_logins'] ?? 0 ?></strong> connexions réussies aujourd'hui
                | <strong><?= $loginStats['active_sessions'] ?? 0 ?></strong> sessions actives
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Connexion...';
            btn.disabled = true;
        });
        
        // Auto-focus on email field
        document.getElementById('email').focus();
        
        // Show/hide password functionality
        const passwordField = document.getElementById('password');
        const passwordIcon = passwordField.nextElementSibling;
        
        passwordIcon.addEventListener('click', function() {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash input-icon';
            } else {
                passwordField.type = 'password';
                passwordIcon.className = 'fas fa-lock input-icon';
            }
        });
        
        // Security monitoring
        console.log('🔒 Secure login system initialized');
        console.log('🛡️ CSRF protection active');
        console.log('📊 Login monitoring enabled');
    </script>
</body>
</html>
