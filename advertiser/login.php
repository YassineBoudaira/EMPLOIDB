<?php
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';
include __DIR__ . '/../include/connexion.php';

// Redirect if already logged in
if (isset($_SESSION['advertiser_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        try {
            // Get advertiser by email
            $advertiser = $db->fetch("SELECT * FROM advertisers WHERE email = ?", [$email]);
            
            if ($advertiser && password_verify($password, $advertiser['password'])) {
                // Check if account is approved
                if ($advertiser['status'] === 'pending') {
                    $error = "Votre compte est en attente d'approbation par l'administrateur.";
                } elseif ($advertiser['status'] === 'suspended') {
                    $error = "Votre compte a été suspendu. Contactez l'administrateur.";
                } else {
                    // Set session
                    $_SESSION['advertiser_id'] = $advertiser['id'];
                    $_SESSION['advertiser_name'] = $advertiser['company_name'];
                    $_SESSION['advertiser_email'] = $advertiser['email'];
                    $_SESSION['advertiser_type'] = 'advertiser';
                    
                    // Update last login
                    $db->update("UPDATE advertisers SET last_login = NOW() WHERE id = ?", [$advertiser['id']]);
                    
                    // Redirect to dashboard
                    header('Location: dashboard.php');
                    exit;
                }
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        } catch (Exception $e) {
            $error = "Erreur de connexion. Veuillez réessayer.";
        }
    }
}

$page_title = "Connexion Annonceur";
include __DIR__ . '/../include/header.php';
?>

<div class="emploidb-container">
    <div class="emploidb-content">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="advertiser-login-card">
                    <div class="login-header">
                        <div class="login-icon">
                            <i class="fa fa-bullhorn"></i>
                        </div>
                        <h1>Connexion Annonceur</h1>
                        <p>Accédez à votre espace publicitaire</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fa fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="login-form">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fa fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Se souvenir de moi
                                </label>
                            </div>
                        </div>

                        <div class="login-actions">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fa fa-sign-in-alt"></i> Se connecter
                            </button>
                        </div>

                        <div class="login-footer">
                            <div class="row">
                                <div class="col-6">
                                    <a href="forgot-password.php">Mot de passe oublié?</a>
                                </div>
                                <div class="col-6 text-end">
                                    <a href="register.php">Créer un compte</a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="login-divider">
                        <span>ou</span>
                    </div>

                    <div class="social-login">
                        <button class="btn btn-outline-primary w-100 mb-2">
                            <i class="fa fa-google"></i> Continuer avec Google
                        </button>
                        <button class="btn btn-outline-primary w-100">
                            <i class="fa fa-facebook"></i> Continuer avec Facebook
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.advertiser-login-card {
    background: var(--emploidb-card-bg);
    border-radius: var(--emploidb-border-radius);
    padding: 3rem 2rem;
    box-shadow: var(--emploidb-shadow);
    margin: 2rem 0;
}

.login-header {
    text-align: center;
    margin-bottom: 2rem;
}

.login-icon {
    width: 70px;
    height: 70px;
    background: var(--emploidb-gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: white;
    font-size: 1.8rem;
}

.login-header h1 {
    color: var(--emploidb-text-primary);
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.login-header p {
    color: var(--emploidb-text-secondary);
    font-size: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    font-weight: 600;
    color: var(--emploidb-text-primary);
    margin-bottom: 0.5rem;
    display: block;
}

.input-group-text {
    background: var(--emploidb-bg-secondary);
    border: 2px solid var(--emploidb-border-color);
    border-right: none;
    color: var(--emploidb-text-secondary);
}

.form-control {
    border: 2px solid var(--emploidb-border-color);
    border-left: none;
    border-radius: 0 var(--emploidb-border-radius) var(--emploidb-border-radius) 0;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: var(--emploidb-primary);
    box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
}

.btn-outline-secondary {
    border: 2px solid var(--emploidb-border-color);
    border-left: none;
    color: var(--emploidb-text-secondary);
}

.btn-outline-secondary:hover {
    background: var(--emploidb-bg-secondary);
    color: var(--emploidb-text-primary);
}

.form-check-input:checked {
    background-color: var(--emploidb-primary);
    border-color: var(--emploidb-primary);
}

.login-actions {
    margin-top: 2rem;
}

.btn-primary {
    background: var(--emploidb-gradient-primary);
    border: none;
    padding: 1rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
}

.login-footer {
    text-align: center;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--emploidb-border-color);
}

.login-footer a {
    color: var(--emploidb-primary);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
}

.login-footer a:hover {
    text-decoration: underline;
}

.login-divider {
    text-align: center;
    margin: 2rem 0;
    position: relative;
}

.login-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: var(--emploidb-border-color);
}

.login-divider span {
    background: var(--emploidb-card-bg);
    padding: 0 1rem;
    color: var(--emploidb-text-secondary);
    font-size: 0.9rem;
}

.social-login .btn {
    border: 2px solid var(--emploidb-border-color);
    color: var(--emploidb-text-primary);
    font-weight: 600;
    transition: all 0.3s ease;
}

.social-login .btn:hover {
    background: var(--emploidb-primary);
    border-color: var(--emploidb-primary);
    color: white;
    transform: translateY(-2px);
}

.alert {
    border-radius: var(--emploidb-border-radius);
    border: none;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
}

@media (max-width: 768px) {
    .advertiser-login-card {
        padding: 2rem 1.5rem;
        margin: 1rem 0;
    }
    
    .login-header h1 {
        font-size: 1.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    
    togglePassword.addEventListener('click', function() {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    });
    
    // Form submission enhancement
    const form = document.querySelector('.login-form');
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Connexion...';
        submitBtn.disabled = true;
    });
    
    // Auto-focus on email field
    document.getElementById('email').focus();
});
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>
