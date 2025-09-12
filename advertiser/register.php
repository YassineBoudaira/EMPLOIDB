<?php
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';
include __DIR__ . '/../include/connexion.php';

// Redirect if already logged in
if (isset($_SESSION['advertiser_id'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $company_name = trim($_POST['company_name'] ?? '');
    $contact_name = trim($_POST['contact_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $website = trim($_POST['website'] ?? '');
    $industry = $_POST['industry'] ?? '';
    $description = trim($_POST['description'] ?? '');
    
    // Validation
    if (empty($company_name)) {
        $errors[] = "Le nom de l'entreprise est requis.";
    }
    
    if (empty($contact_name)) {
        $errors[] = "Le nom du contact est requis.";
    }
    
    if (empty($email)) {
        $errors[] = "L'email est requis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide.";
    }
    
    if (empty($phone)) {
        $errors[] = "Le numéro de téléphone est requis.";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }
    
    // Check if email already exists
    $existing_user = $db->fetch("SELECT id FROM advertisers WHERE email = ?", [$email]);
    if ($existing_user) {
        $errors[] = "Cet email est déjà utilisé.";
    }
    
    // If no errors, create advertiser account
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $db->insert("
                INSERT INTO advertisers (company_name, contact_name, email, phone, password, website, industry, description, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ", [$company_name, $contact_name, $email, $phone, $hashed_password, $website, $industry, $description]);
            
            $success = true;
        } catch (Exception $e) {
            $errors[] = "Erreur lors de la création du compte: " . $e->getMessage();
        }
    }
}

$page_title = "Inscription Annonceur";
include __DIR__ . '/../include/header.php';
?>

<div class="emploidb-container">
    <div class="emploidb-content">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="advertiser-register-card">
                    <div class="register-header">
                        <div class="register-icon">
                            <i class="fa fa-bullhorn"></i>
                        </div>
                        <h1>Inscription Annonceur</h1>
                        <p>Créez votre compte annonceur et commencez à promouvoir votre entreprise</p>
                    </div>

                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="fa fa-check-circle"></i>
                            <strong>Compte créé avec succès!</strong><br>
                            Votre compte est en attente de validation par l'administrateur. 
                            Vous recevrez un email de confirmation une fois approuvé.
                            <div class="mt-3">
                                <a href="login.php" class="btn btn-primary">Se connecter</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fa fa-exclamation-circle"></i>
                                <strong>Erreurs de validation:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="register-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="company_name">Nom de l'entreprise *</label>
                                        <input type="text" class="form-control" id="company_name" name="company_name" 
                                               value="<?php echo htmlspecialchars($_POST['company_name'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact_name">Nom du contact *</label>
                                        <input type="text" class="form-control" id="contact_name" name="contact_name" 
                                               value="<?php echo htmlspecialchars($_POST['contact_name'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Téléphone *</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">Mot de passe *</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <small class="form-text text-muted">Minimum 8 caractères</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="confirm_password">Confirmer le mot de passe *</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="website">Site web</label>
                                        <input type="url" class="form-control" id="website" name="website" 
                                               value="<?php echo htmlspecialchars($_POST['website'] ?? ''); ?>" 
                                               placeholder="https://www.example.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="industry">Secteur d'activité</label>
                                        <select class="form-select" id="industry" name="industry">
                                            <option value="">Sélectionner un secteur</option>
                                            <option value="technology" <?php echo ($_POST['industry'] ?? '') === 'technology' ? 'selected' : ''; ?>>Technologie</option>
                                            <option value="healthcare" <?php echo ($_POST['industry'] ?? '') === 'healthcare' ? 'selected' : ''; ?>>Santé</option>
                                            <option value="finance" <?php echo ($_POST['industry'] ?? '') === 'finance' ? 'selected' : ''; ?>>Finance</option>
                                            <option value="education" <?php echo ($_POST['industry'] ?? '') === 'education' ? 'selected' : ''; ?>>Éducation</option>
                                            <option value="retail" <?php echo ($_POST['industry'] ?? '') === 'retail' ? 'selected' : ''; ?>>Commerce</option>
                                            <option value="manufacturing" <?php echo ($_POST['industry'] ?? '') === 'manufacturing' ? 'selected' : ''; ?>>Manufacture</option>
                                            <option value="services" <?php echo ($_POST['industry'] ?? '') === 'services' ? 'selected' : ''; ?>>Services</option>
                                            <option value="other" <?php echo ($_POST['industry'] ?? '') === 'other' ? 'selected' : ''; ?>>Autre</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Description de l'entreprise</label>
                                <textarea class="form-control" id="description" name="description" rows="4" 
                                          placeholder="Décrivez votre entreprise et vos services..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        J'accepte les <a href="#" target="_blank">conditions d'utilisation</a> et la 
                                        <a href="#" target="_blank">politique de confidentialité</a>
                                    </label>
                                </div>
                            </div>

                            <div class="register-actions">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fa fa-user-plus"></i> Créer mon compte
                                </button>
                            </div>

                            <div class="register-footer">
                                <p>Déjà inscrit? <a href="login.php">Se connecter</a></p>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.advertiser-register-card {
    background: var(--emploidb-card-bg);
    border-radius: var(--emploidb-border-radius);
    padding: 3rem;
    box-shadow: var(--emploidb-shadow);
    margin: 2rem 0;
}

.register-header {
    text-align: center;
    margin-bottom: 2rem;
}

.register-icon {
    width: 80px;
    height: 80px;
    background: var(--emploidb-gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    color: white;
    font-size: 2rem;
}

.register-header h1 {
    color: var(--emploidb-text-primary);
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.register-header p {
    color: var(--emploidb-text-secondary);
    font-size: 1.1rem;
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

.form-control, .form-select {
    border: 2px solid var(--emploidb-border-color);
    border-radius: var(--emploidb-border-radius);
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: var(--emploidb-primary);
    box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
}

.form-check-input:checked {
    background-color: var(--emploidb-primary);
    border-color: var(--emploidb-primary);
}

.register-actions {
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

.register-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--emploidb-border-color);
}

.register-footer a {
    color: var(--emploidb-primary);
    text-decoration: none;
    font-weight: 600;
}

.register-footer a:hover {
    text-decoration: underline;
}

.alert {
    border-radius: var(--emploidb-border-radius);
    border: none;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.alert-success {
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
}

.alert ul {
    padding-left: 1.5rem;
}

@media (max-width: 768px) {
    .advertiser-register-card {
        padding: 2rem;
        margin: 1rem 0;
    }
    
    .register-header h1 {
        font-size: 1.5rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password confirmation validation
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('change', validatePassword);
    confirmPassword.addEventListener('keyup', validatePassword);
    
    // Form submission enhancement
    const form = document.querySelector('.register-form');
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Création en cours...';
        submitBtn.disabled = true;
    });
});
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>
