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
                    
                    <!-- Targeting & Positioning -->
                    <div class="form-section">
                        <h4><i class="fas fa-crosshairs"></i> Ciblage et Positionnement</h4>
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
                    
                    <!-- Budget & Scheduling -->
                    <div class="form-section">
                        <h4><i class="fas fa-dollar-sign"></i> Budget et Planification</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="budget" class="form-label">Budget (DH)</label>
                                    <input type="number" class="form-control" id="budget" name="budget" step="0.01" min="0"
                                           value="<?php echo htmlspecialchars($_POST['budget'] ?? ''); ?>">
                                </div>
                            </div>
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
                        </div>
                    </div>
                    
                    <!-- Media Upload -->
                    <div class="form-section">
                        <h4><i class="fas fa-image"></i> Média</h4>
                        <div class="mb-3">
                            <label for="image" class="form-label">Image de la Publicité</label>
                            <div class="preview-section" onclick="document.getElementById('image').click();">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <p class="mb-2">Cliquez pour sélectionner une image</p>
                                <small class="text-muted">Formats acceptés: JPG, PNG, GIF, WebP (max 5MB)</small>
                            </div>
                            <input type="file" class="form-control d-none" id="image" name="image" accept="image/*">
                        </div>
                        
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
        // File upload preview
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
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();
            const linkUrl = document.getElementById('link_url').value.trim();
            
            if (!title || !content || !linkUrl) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires.');
            }
        });
    </script>
</body>
</html>
