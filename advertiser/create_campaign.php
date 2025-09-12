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

// Get available ads for this advertiser
$available_ads = $db->fetchAll("SELECT id, title FROM advertisements WHERE created_by = ? AND status = 'active'", [$advertiser_id]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $budget = floatval($_POST['budget'] ?? 0);
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $target_audience = $_POST['target_audience'] ?? '';
    $selected_ads = $_POST['selected_ads'] ?? [];
    
    // Validation
    if (empty($name)) {
        $errors[] = "Le nom de la campagne est requis.";
    }
    
    if (empty($description)) {
        $errors[] = "La description de la campagne est requise.";
    }
    
    if ($budget <= 0) {
        $errors[] = "Le budget doit être supérieur à 0.";
    }
    
    if (!empty($start_date) && !empty($end_date)) {
        if (strtotime($start_date) >= strtotime($end_date)) {
            $errors[] = "La date de fin doit être postérieure à la date de début.";
        }
    }
    
    if (empty($selected_ads)) {
        $errors[] = "Veuillez sélectionner au moins une publicité.";
    }
    
    // If no errors, create campaign
    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            // Insert campaign
            $campaign_id = $db->insert("
                INSERT INTO ad_campaigns (
                    name, description, budget, start_date, end_date, target_audience, 
                    created_by, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
            ", [$name, $description, $budget, $start_date ?: null, $end_date ?: null, $target_audience, $advertiser_id]);
            
            // Link selected ads to campaign
            foreach ($selected_ads as $ad_id) {
                $db->insert("
                    INSERT INTO campaign_ads (campaign_id, ad_id) VALUES (?, ?)
                ", [$campaign_id, $ad_id]);
            }
            
            $db->commit();
            $success = true;
            
        } catch (Exception $e) {
            $db->rollback();
            $errors[] = "Erreur lors de la création de la campagne: " . $e->getMessage();
        }
    }
}

$page_title = "Créer une Campagne";
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
        
        .ad-selection {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid var(--emploidb-border-color);
            border-radius: var(--emploidb-border-radius);
            padding: 1rem;
        }
        
        .ad-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border: 1px solid var(--emploidb-border-color);
            border-radius: var(--emploidb-border-radius);
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .ad-item:hover {
            background: var(--emploidb-bg-hover);
        }
        
        .ad-item.selected {
            background: var(--emploidb-primary);
            color: white;
            border-color: var(--emploidb-primary);
        }
        
        .ad-item input[type="checkbox"] {
            margin-right: 1rem;
        }
        
        .ad-item-info {
            flex: 1;
        }
        
        .ad-item-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .ad-item-meta {
            font-size: 0.9rem;
            opacity: 0.8;
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
                            <i class="fas fa-bullhorn text-primary"></i>
                            Créer une Campagne
                        </h1>
                        <p class="text-muted mb-0">Créez une nouvelle campagne publicitaire</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="my_campaigns.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="content-body">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        Votre campagne a été créée avec succès !
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
                
                <form method="POST">
                    <!-- Campaign Information -->
                    <div class="form-section">
                        <h4><i class="fas fa-info-circle"></i> Informations de la Campagne</h4>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom de la Campagne *</label>
                                    <input type="text" class="form-control" id="name" name="name" required 
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                           placeholder="Ex: Campagne Été 2024">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="budget" class="form-label">Budget (DH) *</label>
                                    <input type="number" class="form-control" id="budget" name="budget" required 
                                           step="0.01" min="0"
                                           value="<?php echo htmlspecialchars($_POST['budget'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required
                                      placeholder="Décrivez votre campagne publicitaire..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="target_audience" class="form-label">Audience Cible</label>
                            <textarea class="form-control" id="target_audience" name="target_audience" rows="3"
                                      placeholder="Décrivez votre audience cible..."><?php echo htmlspecialchars($_POST['target_audience'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <!-- Campaign Schedule -->
                    <div class="form-section">
                        <h4><i class="fas fa-calendar"></i> Planification</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Date de Début</label>
                                    <input type="datetime-local" class="form-control" id="start_date" name="start_date"
                                           value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">Date de Fin</label>
                                    <input type="datetime-local" class="form-control" id="end_date" name="end_date"
                                           value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ad Selection -->
                    <div class="form-section">
                        <h4><i class="fas fa-ad"></i> Sélection des Publicités *</h4>
                        <?php if (empty($available_ads)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Vous n'avez aucune publicité active. 
                                <a href="create_ad.php" class="alert-link">Créez d'abord une publicité</a>.
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-3">Sélectionnez les publicités à inclure dans cette campagne :</p>
                            <div class="ad-selection">
                                <?php foreach ($available_ads as $ad): ?>
                                    <div class="ad-item">
                                        <input type="checkbox" name="selected_ads[]" value="<?php echo $ad['id']; ?>" 
                                               id="ad_<?php echo $ad['id']; ?>"
                                               <?php echo in_array($ad['id'], $_POST['selected_ads'] ?? []) ? 'checked' : ''; ?>>
                                        <div class="ad-item-info">
                                            <div class="ad-item-title"><?php echo htmlspecialchars($ad['title']); ?></div>
                                            <div class="ad-item-meta">ID: <?php echo $ad['id']; ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <small class="text-muted">Sélectionnez au moins une publicité pour créer la campagne.</small>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg" <?php echo empty($available_ads) ? 'disabled' : ''; ?>>
                            <i class="fas fa-save"></i> Créer la Campagne
                        </button>
                        <a href="my_campaigns.php" class="btn btn-outline-secondary btn-lg ms-3">
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
        // Ad selection styling
        document.querySelectorAll('input[name="selected_ads[]"]').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                const adItem = this.closest('.ad-item');
                if (this.checked) {
                    adItem.classList.add('selected');
                } else {
                    adItem.classList.remove('selected');
                }
            });
            
            // Initialize selected state
            if (checkbox.checked) {
                checkbox.closest('.ad-item').classList.add('selected');
            }
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const description = document.getElementById('description').value.trim();
            const budget = parseFloat(document.getElementById('budget').value);
            const selectedAds = document.querySelectorAll('input[name="selected_ads[]"]:checked');
            
            if (!name || !description || budget <= 0 || selectedAds.length === 0) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires et sélectionner au moins une publicité.');
            }
        });
        
        // Budget calculation preview
        document.getElementById('budget').addEventListener('input', function() {
            const budget = parseFloat(this.value) || 0;
            const selectedAds = document.querySelectorAll('input[name="selected_ads[]"]:checked').length;
            
            if (selectedAds > 0) {
                const budgetPerAd = budget / selectedAds;
                // You can add a preview element here to show budget per ad
            }
        });
    </script>
</body>
</html>
