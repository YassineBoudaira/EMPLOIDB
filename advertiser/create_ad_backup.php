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

$page_title = "Créer une Publicité";
?>

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
            $ad_id = $db->execute("
                INSERT INTO advertisements (
                    title, description, content, image_url, link_url, ad_type, position, 
                    budget, start_date, end_date, created_by, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ", [$title, $description, $content, $image_url, $link_url, $ad_type, $position, 
                 $budget, $start_date ?: null, $end_date ?: null, $advertiser_id]);
            
            // Link to campaign if selected
            if ($campaign_id > 0) {
                $db->execute("
                    INSERT INTO campaign_ads (campaign_id, ad_id) VALUES (?, ?)
                ", [$campaign_id, $ad_id]);
            }
            
            // Link categories
            if (!empty($categories)) {
                foreach ($categories as $category_id) {
                    $db->execute("
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
include __DIR__ . '/../include/header.php';
?>

<div class="emploidb-container">
    <div class="emploidb-content">
        <div class="row">
            <div class="col-md-12">
                <div class="page-header">
                    <h1><i class="fa fa-plus-circle"></i> Créer une Publicité</h1>
                    <p>Créez une nouvelle publicité pour promouvoir votre entreprise</p>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <i class="fa fa-check-circle"></i>
                <strong>Publicité créée avec succès!</strong><br>
                Votre publicité est en attente d'approbation par l'administrateur.
                <div class="mt-3">
                    <a href="dashboard.php" class="btn btn-primary">Retour au tableau de bord</a>
                    <a href="create_ad.php" class="btn btn-outline-primary">Créer une autre publicité</a>
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

            <div class="row">
                <div class="col-md-8">
                    <div class="create-ad-card">
                        <form method="POST" enctype="multipart/form-data" class="create-ad-form">
                            <!-- Basic Information -->
                            <div class="form-section">
                                <h4><i class="fa fa-info-circle"></i> Informations de base</h4>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="title">Titre de la publicité *</label>
                                            <input type="text" class="form-control" id="title" name="title" 
                                                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="ad_type">Type de publicité *</label>
                                            <select class="form-select" id="ad_type" name="ad_type" required>
                                                <option value="banner" <?php echo ($_POST['ad_type'] ?? '') === 'banner' ? 'selected' : ''; ?>>Bannière</option>
                                                <option value="popup" <?php echo ($_POST['ad_type'] ?? '') === 'popup' ? 'selected' : ''; ?>>Popup</option>
                                                <option value="sidebar" <?php echo ($_POST['ad_type'] ?? '') === 'sidebar' ? 'selected' : ''; ?>>Barre latérale</option>
                                                <option value="inline" <?php echo ($_POST['ad_type'] ?? '') === 'inline' ? 'selected' : ''; ?>>Intégrée</option>
                                                <option value="video" <?php echo ($_POST['ad_type'] ?? '') === 'video' ? 'selected' : ''; ?>>Vidéo</option>
                                                <option value="carousel" <?php echo ($_POST['ad_type'] ?? '') === 'carousel' ? 'selected' : ''; ?>>Carrousel</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description courte</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" 
                                              placeholder="Description courte de votre publicité..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="content">Contenu de la publicité *</label>
                                    <textarea class="form-control" id="content" name="content" rows="6" 
                                              placeholder="Contenu détaillé de votre publicité..." required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="link_url">URL de destination *</label>
                                    <input type="url" class="form-control" id="link_url" name="link_url" 
                                           value="<?php echo htmlspecialchars($_POST['link_url'] ?? ''); ?>" 
                                           placeholder="https://www.example.com" required>
                                </div>
                            </div>

                            <!-- Media & Design -->
                            <div class="form-section">
                                <h4><i class="fa fa-image"></i> Média et Design</h4>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image">Image de la publicité</label>
                                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                            <small class="form-text text-muted">Formats acceptés: JPG, PNG, GIF, WebP (max 5MB)</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="position">Position d'affichage *</label>
                                            <select class="form-select" id="position" name="position" required>
                                                <option value="header" <?php echo ($_POST['position'] ?? '') === 'header' ? 'selected' : ''; ?>>En-tête</option>
                                                <option value="footer" <?php echo ($_POST['position'] ?? '') === 'footer' ? 'selected' : ''; ?>>Pied de page</option>
                                                <option value="sidebar" <?php echo ($_POST['position'] ?? '') === 'sidebar' ? 'selected' : ''; ?>>Barre latérale</option>
                                                <option value="content_top" <?php echo ($_POST['position'] ?? '') === 'content_top' ? 'selected' : ''; ?>>Haut du contenu</option>
                                                <option value="content_bottom" <?php echo ($_POST['position'] ?? '') === 'content_bottom' ? 'selected' : ''; ?>>Bas du contenu</option>
                                                <option value="popup" <?php echo ($_POST['position'] ?? '') === 'popup' ? 'selected' : ''; ?>>Popup</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="template_id">Modèle de design</label>
                                    <select class="form-select" id="template_id" name="template_id">
                                        <option value="">Aucun modèle</option>
                                        <?php foreach ($templates as $template): ?>
                                            <option value="<?php echo $template['id']; ?>" 
                                                    <?php echo ($_POST['template_id'] ?? '') == $template['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($template['name']); ?> (<?php echo ucfirst($template['template_type']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Campaign & Budget -->
                            <div class="form-section">
                                <h4><i class="fa fa-bullhorn"></i> Campagne et Budget</h4>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="campaign_id">Campagne (optionnel)</label>
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
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="budget">Budget (DH)</label>
                                            <input type="number" class="form-control" id="budget" name="budget" 
                                                   value="<?php echo htmlspecialchars($_POST['budget'] ?? ''); ?>" 
                                                   min="0" step="0.01" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="start_date">Date de début</label>
                                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                                   value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="end_date">Date de fin</label>
                                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                                   value="<?php echo htmlspecialchars($_POST['end_date'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Categories -->
                            <div class="form-section">
                                <h4><i class="fa fa-tags"></i> Catégories</h4>
                                
                                <div class="form-group">
                                    <label>Sélectionner les catégories</label>
                                    <div class="categories-grid">
                                        <?php foreach ($categories as $category): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="categories[]" value="<?php echo $category['id']; ?>" 
                                                       id="category_<?php echo $category['id']; ?>"
                                                       <?php echo in_array($category['id'], $_POST['categories'] ?? []) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="category_<?php echo $category['id']; ?>">
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fa fa-save"></i> Créer la publicité
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fa fa-times"></i> Annuler
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="ad-preview-card">
                        <h4><i class="fa fa-eye"></i> Aperçu</h4>
                        <div id="adPreview" class="ad-preview">
                            <div class="preview-placeholder">
                                <i class="fa fa-image"></i>
                                <p>Aperçu de votre publicité</p>
                            </div>
                        </div>
                    </div>

                    <div class="help-card">
                        <h4><i class="fa fa-question-circle"></i> Conseils</h4>
                        <ul class="help-list">
                            <li>Utilisez des images de haute qualité</li>
                            <li>Rédigez un titre accrocheur</li>
                            <li>Incluez un appel à l'action clair</li>
                            <li>Testez différents formats</li>
                            <li>Surveillez vos performances</li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.page-header {
    background: var(--emploidb-gradient-primary);
    color: white;
    padding: 2rem;
    border-radius: var(--emploidb-border-radius);
    margin-bottom: 2rem;
    box-shadow: var(--emploidb-shadow);
}

.page-header h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.page-header p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 0;
}

.create-ad-card {
    background: var(--emploidb-card-bg);
    border-radius: var(--emploidb-border-radius);
    padding: 2rem;
    box-shadow: var(--emploidb-shadow);
    margin-bottom: 2rem;
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--emploidb-border-color);
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.form-section h4 {
    color: var(--emploidb-text-primary);
    font-weight: 600;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
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

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: var(--emploidb-bg-secondary);
    border-radius: var(--emploidb-border-radius);
    transition: all 0.3s ease;
}

.form-check:hover {
    background: var(--emploidb-primary);
    color: white;
}

.form-check-input:checked {
    background-color: var(--emploidb-primary);
    border-color: var(--emploidb-primary);
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--emploidb-border-color);
}

.btn {
    padding: 1rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary {
    background: var(--emploidb-gradient-primary);
    border: none;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
}

.ad-preview-card, .help-card {
    background: var(--emploidb-card-bg);
    border-radius: var(--emploidb-border-radius);
    padding: 1.5rem;
    box-shadow: var(--emploidb-shadow);
    margin-bottom: 2rem;
}

.ad-preview-card h4, .help-card h4 {
    color: var(--emploidb-text-primary);
    font-weight: 600;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ad-preview {
    min-height: 200px;
    border: 2px dashed var(--emploidb-border-color);
    border-radius: var(--emploidb-border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--emploidb-bg-secondary);
}

.preview-placeholder {
    text-align: center;
    color: var(--emploidb-text-secondary);
}

.preview-placeholder i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.help-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.help-list li {
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--emploidb-border-color);
    color: var(--emploidb-text-secondary);
}

.help-list li:last-child {
    border-bottom: none;
}

.help-list li:before {
    content: "✓";
    color: var(--emploidb-primary);
    font-weight: bold;
    margin-right: 0.5rem;
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

@media (max-width: 768px) {
    .form-actions {
        flex-direction: column;
    }
    
    .categories-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time preview update
    const titleInput = document.getElementById('title');
    const descriptionInput = document.getElementById('description');
    const contentInput = document.getElementById('content');
    const adTypeSelect = document.getElementById('ad_type');
    const previewDiv = document.getElementById('adPreview');
    
    function updatePreview() {
        const title = titleInput.value || 'Titre de la publicité';
        const description = descriptionInput.value || 'Description de la publicité';
        const content = contentInput.value || 'Contenu de la publicité';
        const adType = adTypeSelect.value;
        
        previewDiv.innerHTML = `
            <div class="ad-preview-content">
                <h3>${title}</h3>
                <p class="ad-description">${description}</p>
                <div class="ad-content">${content}</div>
                <div class="ad-type-badge">${adType.toUpperCase()}</div>
            </div>
        `;
    }
    
    titleInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    contentInput.addEventListener('input', updatePreview);
    adTypeSelect.addEventListener('change', updatePreview);
    
    // Form validation enhancement
    const form = document.querySelector('.create-ad-form');
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Création en cours...';
        submitBtn.disabled = true;
    });
    
    // Initialize preview
    updatePreview();
});
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>
