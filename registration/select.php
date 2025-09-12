<?php
// Include configuration first
include '../include/config.php';
include '../include/sess.php';
include '../include/connexion.php';

// Check if user is already logged in
if (Security::isLoggedIn()) {
    // Redirect based on role
    if ($_SESSION['role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
    } elseif ($_SESSION['role'] === 'employer') {
        header('Location: ../employer/dashboard.php');
    } elseif ($_SESSION['role'] === 'advertiser') {
        header('Location: ../advertiser/dashboard.php');
    } else {
        header('Location: ../index.php');
    }
    exit();
}

// Get message from URL
$message = $_GET['message'] ?? '';
$messageType = $_GET['type'] ?? 'info';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir votre type de compte - EMPLOIDB</title>
    
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
            --pink-color: #ec4899;
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
        
        .registration-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            position: relative;
        }
        
        .registration-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
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
            padding: 40px 30px;
        }
        
        .account-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .account-card {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 30px 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .account-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-color);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .account-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(37, 99, 235, 0.1);
        }
        
        .account-card:hover::before {
            transform: scaleX(1);
        }
        
        .account-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
        }
        
        .admin-icon { background: linear-gradient(135deg, var(--danger-color), #dc2626); }
        .employer-icon { background: linear-gradient(135deg, var(--success-color), #059669); }
        .advertiser-icon { background: linear-gradient(135deg, var(--warning-color), #d97706); }
        .candidate-icon { background: linear-gradient(135deg, var(--purple-color), #7c3aed); }
        
        .account-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 10px;
        }
        
        .account-description {
            color: var(--secondary-color);
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .account-features {
            list-style: none;
            padding: 0;
            margin-bottom: 25px;
        }
        
        .account-features li {
            padding: 5px 0;
            color: var(--secondary-color);
            font-size: 0.9rem;
        }
        
        .account-features li::before {
            content: '✓';
            color: var(--success-color);
            font-weight: bold;
            margin-right: 8px;
        }
        
        .btn-register {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
            color: white;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 30px;
        }
        
        .alert-info {
            background: #eff6ff;
            color: #1d4ed8;
            border-left: 4px solid #1d4ed8;
        }
        
        .alert-warning {
            background: #fffbeb;
            color: #d97706;
            border-left: 4px solid #d97706;
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .back-to-login a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-to-login a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .registration-container {
                margin: 10px;
                max-width: 100%;
            }
            
            .registration-header {
                padding: 30px 20px;
            }
            
            .registration-header h1 {
                font-size: 2rem;
            }
            
            .registration-body {
                padding: 30px 20px;
            }
            
            .account-types {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <div class="registration-header">
            <h1><i class="fas fa-user-plus me-2"></i>Choisir votre type de compte</h1>
            <p>Créez votre compte selon votre profil professionnel</p>
        </div>
        
        <div class="registration-body">
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType === 'error' ? 'danger' : ($messageType === 'warning' ? 'warning' : 'info') ?>">
                    <i class="fas fa-<?= $messageType === 'error' ? 'exclamation-triangle' : ($messageType === 'warning' ? 'exclamation-triangle' : 'info-circle') ?> me-2"></i>
                    <?php
                    switch ($message) {
                        case 'account_not_found':
                            echo 'Aucun compte trouvé avec cette adresse email. Veuillez créer un nouveau compte.';
                            break;
                        case 'complete_profile':
                            echo 'Veuillez compléter votre profil en choisissant le type de compte approprié.';
                            break;
                        default:
                            echo htmlspecialchars($message);
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="account-types">
                <!-- Administration Account -->
                <div class="account-card" onclick="window.location.href='../admin/register.php'">
                    <div class="account-icon admin-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="account-title">Administration</h3>
                    <p class="account-description">
                        Gestion complète de la plateforme, supervision des utilisateurs et configuration système.
                    </p>
                    <ul class="account-features">
                        <li>Gestion des utilisateurs</li>
                        <li>Configuration système</li>
                        <li>Rapports et analyses</li>
                        <li>Support technique</li>
                    </ul>
                    <a href="../admin/register.php" class="btn-register">
                        <i class="fas fa-shield-alt me-2"></i>Créer un compte Admin
                    </a>
                </div>
                
                <!-- Employer/Recruiter Account -->
                <div class="account-card" onclick="window.location.href='../employer/register.php'">
                    <div class="account-icon employer-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 class="account-title">Recruteur / Employeur</h3>
                    <p class="account-description">
                        Publiez des offres d'emploi, gérez les candidatures et trouvez les meilleurs talents.
                    </p>
                    <ul class="account-features">
                        <li>Publication d'offres d'emploi</li>
                        <li>Gestion des candidatures</li>
                        <li>Recherche de candidats</li>
                        <li>Analytics et rapports</li>
                    </ul>
                    <a href="../employer/register.php" class="btn-register">
                        <i class="fas fa-building me-2"></i>Créer un compte Employeur
                    </a>
                </div>
                
                <!-- Advertiser Account -->
                <div class="account-card" onclick="window.location.href='../advertiser/register.php'">
                    <div class="account-icon advertiser-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h3 class="account-title">Annonceur</h3>
                    <p class="account-description">
                        Promouvez vos produits et services avec des publicités ciblées sur la plateforme.
                    </p>
                    <ul class="account-features">
                        <li>Création de campagnes publicitaires</li>
                        <li>Gestion des budgets</li>
                        <li>Analytics de performance</li>
                        <li>Ciblage avancé</li>
                    </ul>
                    <a href="../advertiser/register.php" class="btn-register">
                        <i class="fas fa-bullhorn me-2"></i>Créer un compte Annonceur
                    </a>
                </div>
                
                <!-- Candidate Account -->
                <div class="account-card" onclick="window.location.href='../candidate/register.php'">
                    <div class="account-icon candidate-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3 class="account-title">Candidat</h3>
                    <p class="account-description">
                        Trouvez votre emploi idéal, postulez aux offres et développez votre carrière.
                    </p>
                    <ul class="account-features">
                        <li>Recherche d'emplois</li>
                        <li>Postulation en ligne</li>
                        <li>Profil professionnel</li>
                        <li>Alertes personnalisées</li>
                    </ul>
                    <a href="../candidate/register.php" class="btn-register">
                        <i class="fas fa-user-tie me-2"></i>Créer un compte Candidat
                    </a>
                </div>
            </div>
            
            <div class="back-to-login">
                <p>Vous avez déjà un compte? 
                    <a href="../login.php">
                        <i class="fas fa-sign-in-alt me-1"></i>Se connecter
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
