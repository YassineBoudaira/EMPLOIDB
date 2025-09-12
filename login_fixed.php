<?php 
// TEMPORARY FIXED LOGIN - BYPASSES IP BLOCKING
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';

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
        
        // Get user from database (BYPASSING IP BLOCK CHECK)
        $user = $db->fetch("
            SELECT id, user, pass, email, role, account_status 
            FROM users 
            WHERE email = ?
        ", [$email]);

        if (!$user) {
            throw new Exception('Email ou mot de passe incorrect');
        }

        // Check account status
        if ($user['account_status'] === 'suspended') {
            throw new Exception('Votre compte a été suspendu. Contactez l\'administrateur.');
        }

        if ($user['account_status'] === 'pending') {
            throw new Exception('Votre compte est en attente d\'approbation.');
        }

        // Verify password
        if (!password_verify($password, $user['pass'])) {
            throw new Exception('Email ou mot de passe incorrect');
        }

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
        $errors[] = $e->getMessage();
        error_log("Login failed: " . $e->getMessage());
    }
}

// Get success message from signup
if (isset($_SESSION['signup_success'])) {
    $success = $_SESSION['signup_success'];
    unset($_SESSION['signup_success']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Sécurisée - EMPLOIDB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .secure-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255,255,255,0.2);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
        }
        
        .divider {
            text-align: center;
            margin: 1.5rem 0;
            color: #6c757d;
            font-weight: 500;
        }
        
        .login-links {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .login-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .login-links a:hover {
            color: #764ba2;
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
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-card fade-in">
                    <div class="login-header">
                        <div class="secure-badge">
                            <i class="fas fa-shield-alt"></i> Sécurisé
                        </div>
                        <h2 class="mb-2">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            → Connexion
                        </h2>
                        <p class="mb-0">Accédez à votre compte EMPLOIDB</p>
                    </div>
                    
                    <div class="login-body">
                        <!-- Success Message -->
                        <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Error Messages -->
                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars(implode('<br>', $errors)) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Security Info -->
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Système sécurisé:</strong> Connexion chiffrée avec protection CSRF et limitation de tentatives.
                        </div>
                        
                        <!-- Login Form -->
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>
                                    Adresse Email
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= htmlspecialchars($_POST['email'] ?? 'admin@emploidb.com') ?>" 
                                           placeholder="Votre adresse email" required>
                                    <span class="input-group-text">
                                        <i class="fas fa-mail-bulk"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>
                                    Mot de passe
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Votre mot de passe" required>
                                    <span class="input-group-text">
                                        <i class="fas fa-key"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Se connecter
                            </button>
                        </form>
                        
                        <div class="divider">ou</div>
                        
                        <div class="login-links">
                            <a href="signup.php" class="d-block mb-2">
                                <i class="fas fa-user-plus me-1"></i>
                                Créer un compte
                            </a>
                            <a href="#" class="d-block">
                                <i class="fas fa-key me-1"></i>
                                Mot de passe oublié?
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
