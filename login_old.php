<?php 
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';

// Check if user is already logged in
if (Security::isLoggedIn()) {
    // Redirect based on role
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } elseif ($_SESSION['user_role'] === 'employer') {
        header('Location: employer/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

$errors = [];
$success = false;

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
        
        // Check if user exists (any role)
        $user = $db->fetch("SELECT id, user, pass, email, role, account_status FROM users WHERE email = ?", [$email]);
        if (!$user) {
            // User doesn't exist - redirect to appropriate registration page
            // For now, redirect to candidate registration (can be enhanced later)
            header('Location: signup.php?message=account_not_found');
            exit();
        }
        
        // Verify password
        if (!Security::verifyPassword($password, $user['pass'])) {
            throw new Exception('Email ou mot de passe incorrect');
        }
        
        // Check if account is active
        if (isset($user['account_status']) && $user['account_status'] === 'suspended') {
            throw new Exception('Votre compte a été suspendu. Contactez l\'administrateur.');
        }
        
                // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['username'] = $user['user'];
                $_SESSION['login_time'] = time();
                
        // Get additional role-specific data
        if ($user['role'] === 'employer') {
            $employer = $db->fetch("SELECT * FROM employers WHERE user_id = ?", [$user['id']]);
            if ($employer) {
                $_SESSION['employer_id'] = $employer['id'];
                $_SESSION['company_name'] = $employer['company_name'];
                } else {
                // Employer user exists but no profile - redirect to employer registration
                header('Location: employer/register.php?message=complete_profile');
                exit();
            }
        }
        
        if ($user['role'] === 'user') {
            $candidate = $db->fetch("SELECT * FROM candidates WHERE user_id = ?", [$user['id']]);
            if (!$candidate) {
                // User exists but no candidate profile - redirect to candidate registration
                header('Location: signup.php?message=complete_profile');
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
        } elseif ($user['role'] === 'user') {
            header('Location: index.php');
        } else {
            // Unknown role - redirect to main page
            header('Location: index.php');
        }
        exit();
        
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
        error_log("Login failed: " . $e->getMessage());
    }
}

// Generate CSRF token
$csrf_token = Security::generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - EMPLOIDB</title>
    
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
        }
        
        .login-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
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
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            background: white;
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
        }
        
        .alert-danger {
            background: #fef2f2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }
        
        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border-left: 4px solid #16a34a;
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
        
        .social-login {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-btn {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            background: white;
            color: #64748b;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }
        
        .social-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            transform: translateY(-2px);
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
        }
        
        .register-link a:hover {
            text-decoration: underline;
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fas fa-sign-in-alt me-2"></i>Connexion</h1>
            <p>Accédez à votre compte JobMaroc</p>
        </div>
        
        <div class="login-body">
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
            
            <form method="POST" action="">
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
                           required>
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
                           required>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                </button>
            </form>
            
            <div class="divider">
                <span>ou</span>
            </div>
            
            <div class="social-login">
                <a href="#" class="social-btn">
                    <i class="fab fa-google"></i>
                </a>
                <a href="#" class="social-btn">
                    <i class="fab fa-linkedin"></i>
                </a>
                <a href="#" class="social-btn">
                    <i class="fab fa-facebook"></i>
                </a>
            </div>
            
            <div class="register-link">
                <p>Pas encore de compte? 
                    <a href="signup.php">
                        <i class="fas fa-user-plus me-1"></i>S'inscrire maintenant
                    </a>
                </p>
                <p>
                    <a href="forgot_password.php">
                        <i class="fas fa-key me-1"></i>Mot de passe oublié?
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
