<?php
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';
include __DIR__ . '/../include/connexion.php';

// Check if advertiser is logged in
if (!isset($_SESSION['advertiser_id'])) {
    header('Location: login.php');
    exit;
}

$advertiser_id = $_SESSION['advertiser_id'];
$success = false;
$errors = [];

// Get advertiser info
$advertiser = $db->fetch("SELECT * FROM advertisers WHERE id = ?", [$advertiser_id]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $company_name = trim($_POST['company_name'] ?? '');
        $contact_name = trim($_POST['contact_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
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
        
        if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
            $errors[] = "Format d'URL invalide.";
        }
        
        if (empty($errors)) {
            try {
                $db->update("
                    UPDATE advertisers SET 
                        company_name = ?, contact_name = ?, phone = ?, website = ?, 
                        industry = ?, description = ?, updated_at = NOW()
                    WHERE id = ?
                ", [$company_name, $contact_name, $phone, $website, $industry, $description, $advertiser_id]);
                
                $success = "Profil mis à jour avec succès.";
                $advertiser = $db->fetch("SELECT * FROM advertisers WHERE id = ?", [$advertiser_id]);
                
            } catch (Exception $e) {
                $errors[] = "Erreur lors de la mise à jour: " . $e->getMessage();
            }
        }
    }
    
    if ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($current_password)) {
            $errors[] = "Le mot de passe actuel est requis.";
        }
        
        if (empty($new_password)) {
            $errors[] = "Le nouveau mot de passe est requis.";
        }
        
        if (strlen($new_password) < 6) {
            $errors[] = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
        
        if (empty($errors)) {
            // Verify current password
            if (!password_verify($current_password, $advertiser['password'])) {
                $errors[] = "Le mot de passe actuel est incorrect.";
            } else {
                try {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $db->update("
                        UPDATE advertisers SET 
                            password = ?, updated_at = NOW()
                        WHERE id = ?
                    ", [$hashed_password, $advertiser_id]);
                    
                    $success = "Mot de passe modifié avec succès.";
                    
                } catch (Exception $e) {
                    $errors[] = "Erreur lors du changement de mot de passe: " . $e->getMessage();
                }
            }
        }
    }
}

$page_title = "Profil";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | EMPLOIDB</title>
    
    <!-- Favicon -->
    <link rel="icon" href="../frontoffice/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    
    <!-- EMPLOIDB Professional Design System -->
    <link href="../assets/css/emploidb-design-system.css" rel="stylesheet">
    
    <style>
        /* Advertiser Panel Professional Styles */
        body {
            background: var(--emploidb-bg-secondary);
            font-family: var(--emploidb-font-primary);
            color: var(--emploidb-text-primary);
        }
        
        /* Advertiser Layout */
        .advertiser-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .emploidb-sidebar {
            width: 280px;
            background: var(--emploidb-bg-primary);
            border-right: 1px solid var(--emploidb-border-color);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--emploidb-border-color);
            background: var(--emploidb-gradient-primary);
        }
        
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--emploidb-white);
            font-weight: 700;
            font-size: 1.25rem;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .sidebar-nav .nav-link {
            color: var(--emploidb-text-secondary);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            border: none;
            background: transparent;
        }
        
        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            color: var(--emploidb-primary);
            background: var(--emploidb-bg-hover);
            border-left: 3px solid var(--emploidb-primary);
        }
        
        .sidebar-nav .nav-link i {
            width: 20px;
            text-align: center;
        }
        
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--emploidb-border-color);
            background: var(--emploidb-bg-primary);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--emploidb-gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--emploidb-white);
        }
        
        .user-details {
            flex: 1;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--emploidb-text-primary);
        }
        
        .user-role {
            font-size: 0.8rem;
            color: var(--emploidb-text-secondary);
        }
        
        /* Main Content */
        .advertiser-main {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            background: var(--emploidb-bg-secondary);
        }
        
        /* Content Cards */
        .content-card {
            background: var(--emploidb-white);
            border-radius: var(--emploidb-border-radius-lg);
            box-shadow: var(--emploidb-shadow-sm);
            border: 1px solid var(--emploidb-border-color);
            margin-bottom: 2rem;
        }
        
        .content-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--emploidb-border-color);
            background: var(--emploidb-bg-light);
            border-radius: var(--emploidb-border-radius-lg) var(--emploidb-border-radius-lg) 0 0;
        }
        
        .content-body {
            padding: 1.5rem;
        }
        
        /* Profile Section */
        .profile-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: var(--emploidb-bg-light);
            border-radius: var(--emploidb-border-radius);
            border: 1px solid var(--emploidb-border-color);
        }
        
        .profile-section h4 {
            color: var(--emploidb-primary);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .emploidb-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .emploidb-sidebar.show {
                transform: translateX(0);
            }
            
            .advertiser-main {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="advertiser-wrapper">
        <!-- Sidebar -->
        <?php include 'include/advertiser_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="advertiser-main">
            <!-- Page Header -->
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-user text-primary"></i>
                            Profil
                        </h1>
                        <p class="text-muted mb-0">Gérez vos informations de compte</p>
                    </div>
                </div>
            </div>
            
            <div class="content-body">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Profile Information -->
                <div class="content-card">
                    <div class="content-header">
                        <h4><i class="fas fa-info-circle"></i> Informations du Profil</h4>
                    </div>
                    <div class="content-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="company_name" class="form-label">Nom de l'Entreprise *</label>
                                        <input type="text" class="form-control" id="company_name" name="company_name" required
                                               value="<?php echo htmlspecialchars($advertiser['company_name']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_name" class="form-label">Nom du Contact *</label>
                                        <input type="text" class="form-control" id="contact_name" name="contact_name" required
                                               value="<?php echo htmlspecialchars($advertiser['contact_name']); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($advertiser['email']); ?>" readonly>
                                        <small class="text-muted">L'email ne peut pas être modifié</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Téléphone</label>
                                        <input type="tel" class="form-control" id="phone" name="phone"
                                               value="<?php echo htmlspecialchars($advertiser['phone']); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="website" class="form-label">Site Web</label>
                                        <input type="url" class="form-control" id="website" name="website"
                                               value="<?php echo htmlspecialchars($advertiser['website']); ?>"
                                               placeholder="https://example.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="industry" class="form-label">Secteur d'Activité</label>
                                        <select class="form-select" id="industry" name="industry">
                                            <option value="">Sélectionner un secteur</option>
                                            <option value="technology" <?php echo $advertiser['industry'] === 'technology' ? 'selected' : ''; ?>>Technologie</option>
                                            <option value="healthcare" <?php echo $advertiser['industry'] === 'healthcare' ? 'selected' : ''; ?>>Santé</option>
                                            <option value="finance" <?php echo $advertiser['industry'] === 'finance' ? 'selected' : ''; ?>>Finance</option>
                                            <option value="education" <?php echo $advertiser['industry'] === 'education' ? 'selected' : ''; ?>>Éducation</option>
                                            <option value="retail" <?php echo $advertiser['industry'] === 'retail' ? 'selected' : ''; ?>>Commerce</option>
                                            <option value="automotive" <?php echo $advertiser['industry'] === 'automotive' ? 'selected' : ''; ?>>Automobile</option>
                                            <option value="real_estate" <?php echo $advertiser['industry'] === 'real_estate' ? 'selected' : ''; ?>>Immobilier</option>
                                            <option value="services" <?php echo $advertiser['industry'] === 'services' ? 'selected' : ''; ?>>Services</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"
                                          placeholder="Décrivez votre entreprise..."><?php echo htmlspecialchars($advertiser['description']); ?></textarea>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Mettre à Jour
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="content-card">
                    <div class="content-header">
                        <h4><i class="fas fa-lock"></i> Changer le Mot de Passe</h4>
                    </div>
                    <div class="content-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Mot de Passe Actuel *</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">Nouveau Mot de Passe *</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirmer le Mot de Passe *</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-key"></i> Changer le Mot de Passe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Account Information -->
                <div class="content-card">
                    <div class="content-header">
                        <h4><i class="fas fa-cog"></i> Informations du Compte</h4>
                    </div>
                    <div class="content-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Statut du Compte</label>
                                    <div>
                                        <span class="badge bg-<?php echo $advertiser['status'] === 'active' ? 'success' : ($advertiser['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                            <?php echo ucfirst($advertiser['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Solde du Compte</label>
                                    <div>
                                        <strong class="text-primary"><?php echo number_format($advertiser['balance'], 2); ?> DH</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Date de Création</label>
                                    <div>
                                        <?php echo date('d/m/Y H:i', strtotime($advertiser['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Dernière Mise à Jour</label>
                                    <div>
                                        <?php echo $advertiser['updated_at'] ? date('d/m/Y H:i', strtotime($advertiser['updated_at'])) : 'Jamais'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Les mots de passe ne correspondent pas');
            } else {
                this.setCustomValidity('');
            }
        });
        
        document.getElementById('new_password').addEventListener('input', function() {
            const confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword.value) {
                if (this.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
        });
    </script>
</body>
</html>
