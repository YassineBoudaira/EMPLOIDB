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

// Get available campaigns
$campaigns = $db->fetchAll("SELECT id, name FROM ad_campaigns WHERE created_by = ? AND status = 'active'", [$advertiser_id]);

// Get ad categories
$categories = $db->fetchAll("SELECT id, name FROM ad_categories WHERE is_active = 1");

// Get ad templates
$templates = $db->fetchAll("SELECT id, name, template_type FROM ad_templates WHERE is_active = 1");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $link_url = trim($_POST['link_url'] ?? '');
    $ad_type = $_POST['ad_type'] ?? 'banner';
    $position = $_POST['position'] ?? 'header';
    $budget = floatval($_POST['budget'] ?? 0);
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $campaign_id = intval($_POST['campaign_id'] ?? 0);
    $template_id = intval($_POST['template_id'] ?? 0);
    $categories = $_POST['categories'] ?? [];
    
    // Validation
    if (empty($title)) {
        $errors[] = "Le titre de la publicité est requis.";
    }
    
    if (empty($content)) {
        $errors[] = "Le contenu de la publicité est requis.";
    }
    
    if (empty($link_url)) {
        $errors[] = "L'URL de destination est requise.";
    } elseif (!filter_var($link_url, FILTER_VALIDATE_URL)) {
        $errors[] = "Format d'URL invalide.";
    }
    
    if ($budget < 0) {
        $errors[] = "Le budget ne peut pas être négatif.";
    }
    
    if (!empty($start_date) && !empty($end_date)) {
        if (strtotime($start_date) >= strtotime($end_date)) {
            $errors[] = "La date de fin doit être postérieure à la date de début.";
        }
    }
    
    // Handle file upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WebP.";
        } elseif ($_FILES['image']['size'] > $max_size) {
            $errors[] = "La taille du fichier ne peut pas dépasser 5MB.";
        } else {
            $upload_dir = '../uploads/ads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'ad_' . time() . '_' . $advertiser_id . '.' . $file_extension;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                $image_url = 'uploads/ads/' . $filename;
            } else {
                $errors[] = "Erreur lors du téléchargement de l'image.";
            }
        }
    }
    
    // If no errors, create advertisement
    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            // Insert advertisement
            $ad_id = $db->insert("
                INSERT INTO advertisements (
                    title, description, content, image_url, link_url, ad_type, position, 
                    budget, start_date, end_date, created_by, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ", [$title, $description, $content, $image_url, $link_url, $ad_type, $position, 
                 $budget, $start_date ?: null, $end_date ?: null, $advertiser_id]);
            
            // Link to campaign if selected
            if ($campaign_id > 0) {
                $db->insert("
                    INSERT INTO campaign_ads (campaign_id, ad_id) VALUES (?, ?)
                ", [$campaign_id, $ad_id]);
            }
            
            // Link categories
            if (!empty($categories)) {
                foreach ($categories as $category_id) {
                    $db->insert("
                        INSERT INTO ad_category_relations (ad_id, category_id) VALUES (?, ?)
                    ", [$ad_id, $category_id]);
                }
            }
            
            $db->commit();
            $success = true;
            
        } catch (Exception $e) {
            $db->rollback();
            $errors[] = "Erreur lors de la création de la publicité: " . $e->getMessage();
        }
    }
}

$page_title = "Créer une Publicité";
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
        
        /* Form Styles */
        .form-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: var(--emploidb-bg-light);
            border-radius: var(--emploidb-border-radius);
            border: 1px solid var(--emploidb-border-color);
        }
        
        .form-section h4 {
            color: var(--emploidb-primary);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .preview-section {
            background: var(--emploidb-white);
            border: 2px dashed var(--emploidb-border-color);
            border-radius: var(--emploidb-border-radius);
            padding: 2rem;
            text-align: center;
            margin-top: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .preview-section:hover {
            border-color: var(--emploidb-primary);
            background: var(--emploidb-bg-hover);
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
        <div class="emploidb-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <i class="fas fa-bullhorn"></i>
                    <span>Annonceur</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Tableau de Bord</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link active" href="create_ad.php">
                            <i class="fas fa-plus-circle"></i>
                            <span>Créer Publicité</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="my_ads.php">
                            <i class="fas fa-ad"></i>
                            <span>Mes Publicités</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="create_campaign.php">
                            <i class="fas fa-bullhorn"></i>
                            <span>Nouvelle Campagne</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="my_campaigns.php">
                            <i class="fas fa-list"></i>
                            <span>Mes Campagnes</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="payment.php">
                            <i class="fas fa-credit-card"></i>
                            <span>Paiements</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="analytics.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-file-alt"></i>
                            <span>Rapports</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user"></i>
                            <span>Profil</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($advertiser['company_name']); ?></div>
                        <div class="user-role">Annonceur</div>
                    </div>
                </div>
                <div class="sidebar-actions">
                    <a href="logout.php" class="btn btn-outline-danger btn-sm" title="Déconnexion">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="advertiser-main">
            <!-- Page Header -->
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-plus-circle text-primary"></i>
                            Créer une Publicité
                        </h1>
                        <p class="text-muted mb-0">Remplissez le formulaire ci-dessous pour créer votre publicité</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="content-body">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        Votre publicité a été créée avec succès !
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
                
                <form method="POST" enctype="multipart/form-data">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h4><i class="fas fa-info-circle"></i> Informations de Base</h4>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Titre de la Publicité *</label>
                                    <input type="text" class="form-control" id="title" name="title" required 
                                           value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="ad_type" class="form-label">Type de Publicité</label>
                                    <select class="form-select" id="ad_type" name="ad_type">
                                        <option value="banner" <?php echo ($_POST['ad_type'] ?? 'banner') === 'banner' ? 'selected' : ''; ?>>Bannière</option>
                                        <option value="popup" <?php echo ($_POST['ad_type'] ?? '') === 'popup' ? 'selected' : ''; ?>>Popup</option>
                                        <option value="sidebar" <?php echo ($_POST['ad_type'] ?? '') === 'sidebar' ? 'selected' : ''; ?>>Sidebar</option>
                                        <option value="inline" <?php echo ($_POST['ad_type'] ?? '') === 'inline' ? 'selected' : ''; ?>>Inline</option>
                                        <option value="video" <?php echo ($_POST['ad_type'] ?? '') === 'video' ? 'selected' : ''; ?>>Vidéo</option>
                                        <option value="carousel" <?php echo ($_POST['ad_type'] ?? '') === 'carousel' ? 'selected' : ''; ?>>Carrousel</option>
                                        <option value="native" <?php echo ($_POST['ad_type'] ?? '') === 'native' ? 'selected' : ''; ?>>Native</option>
                                        <option value="interstitial" <?php echo ($_POST['ad_type'] ?? '') === 'interstitial' ? 'selected' : ''; ?>>Interstitiel</option>
                                        <option value="sticky" <?php echo ($_POST['ad_type'] ?? '') === 'sticky' ? 'selected' : ''; ?>>Sticky</option>
                                        <option value="floating" <?php echo ($_POST['ad_type'] ?? '') === 'floating' ? 'selected' : ''; ?>>Flottant</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                      placeholder="Description courte de votre publicité"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="content" class="form-label">Contenu de la Publicité *</label>
                            <textarea class="form-control" id="content" name="content" rows="4" required
                                      placeholder="Contenu principal de votre publicité"><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="link_url" class="form-label">URL de Destination *</label>
                            <input type="url" class="form-control" id="link_url" name="link_url" required
                                   value="<?php echo htmlspecialchars($_POST['link_url'] ?? ''); ?>"
                                   placeholder="https://example.com">
                        </div>
                    </div>
                    
                    <!-- Advanced Targeting & Positioning -->
                    <div class="form-section">
                        <h4><i class="fas fa-crosshairs"></i> Ciblage et Positionnement Avancé</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="position" class="form-label">Position d'Affichage</label>
                                    <select class="form-select" id="position" name="position">
                                        <option value="header" <?php echo ($_POST['position'] ?? 'header') === 'header' ? 'selected' : ''; ?>>En-tête</option>
                                        <option value="sidebar" <?php echo ($_POST['position'] ?? '') === 'sidebar' ? 'selected' : ''; ?>>Barre latérale</option>
                                        <option value="footer" <?php echo ($_POST['position'] ?? '') === 'footer' ? 'selected' : ''; ?>>Pied de page</option>
                                        <option value="content_top" <?php echo ($_POST['position'] ?? '') === 'content_top' ? 'selected' : ''; ?>>Haut du contenu</option>
                                        <option value="content_bottom" <?php echo ($_POST['position'] ?? '') === 'content_bottom' ? 'selected' : ''; ?>>Bas du contenu</option>
                                        <option value="floating_left" <?php echo ($_POST['position'] ?? '') === 'floating_left' ? 'selected' : ''; ?>>Flottant Gauche</option>
                                        <option value="floating_right" <?php echo ($_POST['position'] ?? '') === 'floating_right' ? 'selected' : ''; ?>>Flottant Droite</option>
                                        <option value="sticky_top" <?php echo ($_POST['position'] ?? '') === 'sticky_top' ? 'selected' : ''; ?>>Sticky Haut</option>
                                        <option value="sticky_bottom" <?php echo ($_POST['position'] ?? '') === 'sticky_bottom' ? 'selected' : ''; ?>>Sticky Bas</option>
                                        <option value="modal_overlay" <?php echo ($_POST['position'] ?? '') === 'modal_overlay' ? 'selected' : ''; ?>>Modal Overlay</option>
                                        <option value="fullscreen" <?php echo ($_POST['position'] ?? '') === 'fullscreen' ? 'selected' : ''; ?>>Plein Écran</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="campaign_id" class="form-label">Campagne (Optionnel)</label>
                                    <select class="form-select" id="campaign_id" name="campaign_id">
                                        <option value="">Aucune campagne</option>
                                        <?php foreach ($campaigns as $campaign): ?>
                                            <option value="<?php echo $campaign['id']; ?>" 
                                                    <?php echo ($_POST['campaign_id'] ?? '') == $campaign['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($campaign['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Advanced Targeting Options -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="target_audience" class="form-label">Audience Cible</label>
                                    <select class="form-select" id="target_audience" name="target_audience">
                                        <option value="all" <?php echo ($_POST['target_audience'] ?? 'all') === 'all' ? 'selected' : ''; ?>>Tous les utilisateurs</option>
                                        <option value="job_seekers" <?php echo ($_POST['target_audience'] ?? '') === 'job_seekers' ? 'selected' : ''; ?>>Candidats</option>
                                        <option value="employers" <?php echo ($_POST['target_audience'] ?? '') === 'employers' ? 'selected' : ''; ?>>Employeurs</option>
                                        <option value="premium_users" <?php echo ($_POST['target_audience'] ?? '') === 'premium_users' ? 'selected' : ''; ?>>Utilisateurs Premium</option>
                                        <option value="new_users" <?php echo ($_POST['target_audience'] ?? '') === 'new_users' ? 'selected' : ''; ?>>Nouveaux utilisateurs</option>
                                        <option value="returning_users" <?php echo ($_POST['target_audience'] ?? '') === 'returning_users' ? 'selected' : ''; ?>>Utilisateurs récurrents</option>
                                        <option value="mobile_users" <?php echo ($_POST['target_audience'] ?? '') === 'mobile_users' ? 'selected' : ''; ?>>Utilisateurs mobiles</option>
                                        <option value="desktop_users" <?php echo ($_POST['target_audience'] ?? '') === 'desktop_users' ? 'selected' : ''; ?>>Utilisateurs desktop</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="target_location" class="form-label">Localisation Cible</label>
                                    <select class="form-select" id="target_location" name="target_location">
                                        <option value="all" <?php echo ($_POST['target_location'] ?? 'all') === 'all' ? 'selected' : ''; ?>>Toutes les régions</option>
                                        <option value="casablanca" <?php echo ($_POST['target_location'] ?? '') === 'casablanca' ? 'selected' : ''; ?>>Casablanca</option>
                                        <option value="rabat" <?php echo ($_POST['target_location'] ?? '') === 'rabat' ? 'selected' : ''; ?>>Rabat</option>
                                        <option value="marrakech" <?php echo ($_POST['target_location'] ?? '') === 'marrakech' ? 'selected' : ''; ?>>Marrakech</option>
                                        <option value="fes" <?php echo ($_POST['target_location'] ?? '') === 'fes' ? 'selected' : ''; ?>>Fès</option>
                                        <option value="agadir" <?php echo ($_POST['target_location'] ?? '') === 'agadir' ? 'selected' : ''; ?>>Agadir</option>
                                        <option value="tanger" <?php echo ($_POST['target_location'] ?? '') === 'tanger' ? 'selected' : ''; ?>>Tanger</option>
                                        <option value="international" <?php echo ($_POST['target_location'] ?? '') === 'international' ? 'selected' : ''; ?>>International</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="target_device" class="form-label">Appareil Cible</label>
                                    <select class="form-select" id="target_device" name="target_device">
                                        <option value="all" <?php echo ($_POST['target_device'] ?? 'all') === 'all' ? 'selected' : ''; ?>>Tous les appareils</option>
                                        <option value="mobile" <?php echo ($_POST['target_device'] ?? '') === 'mobile' ? 'selected' : ''; ?>>Mobile</option>
                                        <option value="tablet" <?php echo ($_POST['target_device'] ?? '') === 'tablet' ? 'selected' : ''; ?>>Tablette</option>
                                        <option value="desktop" <?php echo ($_POST['target_device'] ?? '') === 'desktop' ? 'selected' : ''; ?>>Desktop</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Behavioral Targeting -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ciblage Comportemental</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="behavioral_targeting[]" value="high_engagement" id="high_engagement">
                                        <label class="form-check-label" for="high_engagement">
                                            Utilisateurs très engagés
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="behavioral_targeting[]" value="frequent_visitors" id="frequent_visitors">
                                        <label class="form-check-label" for="frequent_visitors">
                                            Visiteurs fréquents
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="behavioral_targeting[]" value="job_searchers" id="job_searchers">
                                        <label class="form-check-label" for="job_searchers">
                                            Chercheurs d'emploi actifs
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ciblage Temporel</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="time_targeting[]" value="business_hours" id="business_hours">
                                        <label class="form-check-label" for="business_hours">
                                            Heures de bureau (9h-18h)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="time_targeting[]" value="evening" id="evening">
                                        <label class="form-check-label" for="evening">
                                            Soirée (18h-22h)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="time_targeting[]" value="weekend" id="weekend">
                                        <label class="form-check-label" for="weekend">
                                            Week-end
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Catégories de Ciblage</label>
                            <div class="row">
                                <?php foreach ($categories as $category): ?>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="categories[]" 
                                                   value="<?php echo $category['id']; ?>" id="category_<?php echo $category['id']; ?>"
                                                   <?php echo in_array($category['id'], $_POST['categories'] ?? []) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="category_<?php echo $category['id']; ?>">
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Advanced Budget & Bidding -->
                    <div class="form-section">
                        <h4><i class="fas fa-dollar-sign"></i> Budget et Stratégie de Surenchère</h4>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="budget" class="form-label">Budget Total (DH)</label>
                                    <input type="number" class="form-control" id="budget" name="budget" step="0.01" min="0"
                                           value="<?php echo htmlspecialchars($_POST['budget'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="daily_budget" class="form-label">Budget Quotidien (DH)</label>
                                    <input type="number" class="form-control" id="daily_budget" name="daily_budget" step="0.01" min="0"
                                           value="<?php echo htmlspecialchars($_POST['daily_budget'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="bid_type" class="form-label">Type de Surenchère</label>
                                    <select class="form-select" id="bid_type" name="bid_type">
                                        <option value="cpc" <?php echo ($_POST['bid_type'] ?? 'cpc') === 'cpc' ? 'selected' : ''; ?>>CPC (Coût par Clic)</option>
                                        <option value="cpm" <?php echo ($_POST['bid_type'] ?? '') === 'cpm' ? 'selected' : ''; ?>>CPM (Coût par Mille)</option>
                                        <option value="cpa" <?php echo ($_POST['bid_type'] ?? '') === 'cpa' ? 'selected' : ''; ?>>CPA (Coût par Action)</option>
                                        <option value="cpi" <?php echo ($_POST['bid_type'] ?? '') === 'cpi' ? 'selected' : ''; ?>>CPI (Coût par Installation)</option>
                                        <option value="cpe" <?php echo ($_POST['bid_type'] ?? '') === 'cpe' ? 'selected' : ''; ?>>CPE (Coût par Engagement)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="bid_amount" class="form-label">Montant de Surenchère (DH)</label>
                                    <input type="number" class="form-control" id="bid_amount" name="bid_amount" step="0.01" min="0"
                                           value="<?php echo htmlspecialchars($_POST['bid_amount'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Date de Début</label>
                                    <input type="datetime-local" class="form-control" id="start_date" name="start_date"
                                           value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">Date de Fin</label>
                                    <input type="datetime-local" class="form-control" id="end_date" name="end_date"
                                           value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="frequency_cap" class="form-label">Limite de Fréquence</label>
                                    <select class="form-select" id="frequency_cap" name="frequency_cap">
                                        <option value="unlimited" <?php echo ($_POST['frequency_cap'] ?? 'unlimited') === 'unlimited' ? 'selected' : ''; ?>>Illimité</option>
                                        <option value="1_per_day" <?php echo ($_POST['frequency_cap'] ?? '') === '1_per_day' ? 'selected' : ''; ?>>1 fois par jour</option>
                                        <option value="3_per_day" <?php echo ($_POST['frequency_cap'] ?? '') === '3_per_day' ? 'selected' : ''; ?>>3 fois par jour</option>
                                        <option value="5_per_day" <?php echo ($_POST['frequency_cap'] ?? '') === '5_per_day' ? 'selected' : ''; ?>>5 fois par jour</option>
                                        <option value="10_per_day" <?php echo ($_POST['frequency_cap'] ?? '') === '10_per_day' ? 'selected' : ''; ?>>10 fois par jour</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Advanced Bidding Options -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Stratégie de Surenchère</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="bidding_strategy" value="manual" id="manual_bidding" checked>
                                        <label class="form-check-label" for="manual_bidding">
                                            Surenchère manuelle
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="bidding_strategy" value="auto" id="auto_bidding">
                                        <label class="form-check-label" for="auto_bidding">
                                            Surenchère automatique
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="bidding_strategy" value="target_cpa" id="target_cpa">
                                        <label class="form-check-label" for="target_cpa">
                                            CPA cible
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Optimisation</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="optimization[]" value="maximize_clicks" id="maximize_clicks">
                                        <label class="form-check-label" for="maximize_clicks">
                                            Maximiser les clics
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="optimization[]" value="maximize_impressions" id="maximize_impressions">
                                        <label class="form-check-label" for="maximize_impressions">
                                            Maximiser les impressions
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="optimization[]" value="maximize_conversions" id="maximize_conversions">
                                        <label class="form-check-label" for="maximize_conversions">
                                            Maximiser les conversions
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Advanced Media & Creative -->
                    <div class="form-section">
                        <h4><i class="fas fa-image"></i> Média et Créatifs Avancés</h4>
                        
                        <!-- Image Upload -->
                        <div class="mb-3">
                            <label for="image" class="form-label">Image Principale</label>
                            <div class="preview-section" onclick="document.getElementById('image').click();">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <p class="mb-2">Cliquez pour sélectionner une image</p>
                                <small class="text-muted">Formats acceptés: JPG, PNG, GIF, WebP (max 10MB)</small>
                            </div>
                            <input type="file" class="form-control d-none" id="image" name="image" accept="image/*">
                        </div>
                        
                        <!-- Multiple Images for Carousel -->
                        <div class="mb-3" id="carousel-images" style="display: none;">
                            <label class="form-label">Images Carrousel (jusqu'à 5 images)</label>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="preview-section" onclick="document.getElementById('image2').click();">
                                        <i class="fas fa-plus fa-2x text-muted mb-2"></i>
                                        <p class="mb-0">Image 2</p>
                                    </div>
                                    <input type="file" class="form-control d-none" id="image2" name="image2" accept="image/*">
                                </div>
                                <div class="col-md-3">
                                    <div class="preview-section" onclick="document.getElementById('image3').click();">
                                        <i class="fas fa-plus fa-2x text-muted mb-2"></i>
                                        <p class="mb-0">Image 3</p>
                                    </div>
                                    <input type="file" class="form-control d-none" id="image3" name="image3" accept="image/*">
                                </div>
                                <div class="col-md-3">
                                    <div class="preview-section" onclick="document.getElementById('image4').click();">
                                        <i class="fas fa-plus fa-2x text-muted mb-2"></i>
                                        <p class="mb-0">Image 4</p>
                                    </div>
                                    <input type="file" class="form-control d-none" id="image4" name="image4" accept="image/*">
                                </div>
                                <div class="col-md-3">
                                    <div class="preview-section" onclick="document.getElementById('image5').click();">
                                        <i class="fas fa-plus fa-2x text-muted mb-2"></i>
                                        <p class="mb-0">Image 5</p>
                                    </div>
                                    <input type="file" class="form-control d-none" id="image5" name="image5" accept="image/*">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Video Upload -->
                        <div class="mb-3" id="video-upload" style="display: none;">
                            <label for="video" class="form-label">Vidéo de la Publicité</label>
                            <div class="preview-section" onclick="document.getElementById('video').click();">
                                <i class="fas fa-video fa-3x text-muted mb-3"></i>
                                <p class="mb-2">Cliquez pour sélectionner une vidéo</p>
                                <small class="text-muted">Formats acceptés: MP4, MOV, AVI (max 50MB)</small>
                            </div>
                            <input type="file" class="form-control d-none" id="video" name="video" accept="video/*">
                        </div>
                        
                        <!-- Creative Options -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="template_id" class="form-label">Template (Optionnel)</label>
                                    <select class="form-select" id="template_id" name="template_id">
                                        <option value="">Aucun template</option>
                                        <?php foreach ($templates as $template): ?>
                                            <option value="<?php echo $template['id']; ?>" 
                                                    <?php echo ($_POST['template_id'] ?? '') == $template['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($template['name']); ?> (<?php echo $template['template_type']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="creative_style" class="form-label">Style Créatif</label>
                                    <select class="form-select" id="creative_style" name="creative_style">
                                        <option value="modern" <?php echo ($_POST['creative_style'] ?? 'modern') === 'modern' ? 'selected' : ''; ?>>Moderne</option>
                                        <option value="classic" <?php echo ($_POST['creative_style'] ?? '') === 'classic' ? 'selected' : ''; ?>>Classique</option>
                                        <option value="minimalist" <?php echo ($_POST['creative_style'] ?? '') === 'minimalist' ? 'selected' : ''; ?>>Minimaliste</option>
                                        <option value="bold" <?php echo ($_POST['creative_style'] ?? '') === 'bold' ? 'selected' : ''; ?>>Audacieux</option>
                                        <option value="elegant" <?php echo ($_POST['creative_style'] ?? '') === 'elegant' ? 'selected' : ''; ?>>Élégant</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Call-to-Action Options -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cta_text" class="form-label">Texte CTA (Call-to-Action)</label>
                                    <input type="text" class="form-control" id="cta_text" name="cta_text" 
                                           value="<?php echo htmlspecialchars($_POST['cta_text'] ?? 'En savoir plus'); ?>"
                                           placeholder="En savoir plus, Découvrir, Acheter maintenant...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cta_color" class="form-label">Couleur CTA</label>
                                    <input type="color" class="form-control form-control-color" id="cta_color" name="cta_color" 
                                           value="<?php echo $_POST['cta_color'] ?? '#1e40af'; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Animation Options -->
                        <div class="mb-3">
                            <label class="form-label">Options d'Animation</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="animations[]" value="fade_in" id="fade_in">
                                        <label class="form-check-label" for="fade_in">
                                            Fade In
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="animations[]" value="slide_up" id="slide_up">
                                        <label class="form-check-label" for="slide_up">
                                            Slide Up
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="animations[]" value="bounce" id="bounce">
                                        <label class="form-check-label" for="bounce">
                                            Bounce
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Créer la Publicité
                        </button>
                        <a href="dashboard.php" class="btn btn-outline-secondary btn-lg ms-3">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Enhanced Ad Creation Form JavaScript
        
        // Ad type change handler
        document.getElementById('ad_type').addEventListener('change', function() {
            const adType = this.value;
            const carouselImages = document.getElementById('carousel-images');
            const videoUpload = document.getElementById('video-upload');
            
            // Show/hide carousel images for carousel type
            if (adType === 'carousel') {
                carouselImages.style.display = 'block';
                videoUpload.style.display = 'none';
            } else if (adType === 'video') {
                carouselImages.style.display = 'none';
                videoUpload.style.display = 'block';
            } else {
                carouselImages.style.display = 'none';
                videoUpload.style.display = 'none';
            }
        });
        
        // File upload preview for main image
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewSection = document.querySelector('.preview-section');
                    previewSection.innerHTML = `
                        <img src="${e.target.result}" class="img-fluid" style="max-height: 200px;">
                        <p class="mt-2">${file.name}</p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // File upload preview for carousel images
        for (let i = 2; i <= 5; i++) {
            document.getElementById(`image${i}`).addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewSection = this.parentElement.querySelector('.preview-section');
                        previewSection.innerHTML = `
                            <img src="${e.target.result}" class="img-fluid" style="max-height: 100px;">
                            <p class="mt-1">${file.name}</p>
                        `;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Video upload preview
        document.getElementById('video').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewSection = document.querySelector('#video-upload .preview-section');
                    previewSection.innerHTML = `
                        <video src="${e.target.result}" class="img-fluid" style="max-height: 200px;" controls></video>
                        <p class="mt-2">${file.name}</p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Bidding strategy change handler
        document.querySelectorAll('input[name="bidding_strategy"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const bidAmountField = document.getElementById('bid_amount');
                if (this.value === 'auto') {
                    bidAmountField.disabled = true;
                    bidAmountField.value = '';
                } else {
                    bidAmountField.disabled = false;
                }
            });
        });
        
        // Budget calculation
        document.getElementById('budget').addEventListener('input', function() {
            const totalBudget = parseFloat(this.value) || 0;
            const dailyBudget = document.getElementById('daily_budget');
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
                
                if (days > 0) {
                    const suggestedDaily = totalBudget / days;
                    dailyBudget.placeholder = `Suggéré: ${suggestedDaily.toFixed(2)} DH`;
                }
            }
        });
        
        // Real-time form validation
        function validateForm() {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            const linkUrl = document.getElementById('link_url').value.trim();
            const budget = document.getElementById('budget').value;
            const dailyBudget = document.getElementById('daily_budget').value;
            
            let isValid = true;
            let errors = [];
            
            if (!title) {
                errors.push('Le titre est obligatoire');
                isValid = false;
            }
            
            if (!content) {
                errors.push('Le contenu est obligatoire');
                isValid = false;
            }
            
            if (!linkUrl) {
                errors.push('L\'URL de destination est obligatoire');
                isValid = false;
            } else if (!isValidUrl(linkUrl)) {
                errors.push('Format d\'URL invalide');
                isValid = false;
            }
            
            if (budget && dailyBudget && parseFloat(dailyBudget) > parseFloat(budget)) {
                errors.push('Le budget quotidien ne peut pas dépasser le budget total');
                isValid = false;
            }
            
            return { isValid, errors };
        }
        
        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
        
        // Form submission with validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const validation = validateForm();
            
            if (!validation.isValid) {
                e.preventDefault();
                alert('Erreurs de validation:\n' + validation.errors.join('\n'));
            }
        });
        
        // Real-time budget preview
        function updateBudgetPreview() {
            const budget = parseFloat(document.getElementById('budget').value) || 0;
            const dailyBudget = parseFloat(document.getElementById('daily_budget').value) || 0;
            const bidAmount = parseFloat(document.getElementById('bid_amount').value) || 0;
            const bidType = document.getElementById('bid_type').value;
            
            let preview = `Budget Total: ${budget.toFixed(2)} DH`;
            if (dailyBudget > 0) {
                preview += ` | Budget Quotidien: ${dailyBudget.toFixed(2)} DH`;
            }
            if (bidAmount > 0) {
                preview += ` | Surenchère: ${bidAmount.toFixed(2)} DH (${bidType.toUpperCase()})`;
            }
            
            // Update preview display (you can add a preview element to show this)
            console.log(preview);
        }
        
        // Add event listeners for budget preview
        ['budget', 'daily_budget', 'bid_amount', 'bid_type'].forEach(id => {
            document.getElementById(id).addEventListener('input', updateBudgetPreview);
        });
        
        // Auto-save draft functionality
        let autoSaveTimeout;
        function autoSaveDraft() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                const formData = new FormData(document.querySelector('form'));
                formData.append('action', 'save_draft');
                
                fetch('create_ad.php', {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    if (response.ok) {
                        console.log('Brouillon sauvegardé automatiquement');
                    }
                }).catch(error => {
                    console.error('Erreur lors de la sauvegarde automatique:', error);
                });
            }, 5000); // Auto-save every 5 seconds
        }
        
        // Add auto-save to form inputs
        document.querySelectorAll('input, textarea, select').forEach(input => {
            input.addEventListener('input', autoSaveDraft);
        });
        
        // Initialize form
        document.addEventListener('DOMContentLoaded', function() {
            // Set default dates
            const now = new Date();
            const future = new Date(now.getTime() + 30 * 24 * 60 * 60 * 1000); // 30 days from now
            
            document.getElementById('start_date').value = now.toISOString().slice(0, 16);
            document.getElementById('end_date').value = future.toISOString().slice(0, 16);
            
            // Initialize budget preview
            updateBudgetPreview();
        });
    </script>
</body>
</html>
