<?php 
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';
include 'frontoffice/include/header2.php'; 

// Check if user is logged in
if (!Security::isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle save/unsave job action
if (isset($_POST['action']) && isset($_POST['job_id'])) {
    $job_id = (int)$_POST['job_id'];
    $action = $_POST['action'];
    
    if ($action === 'save') {
        // Check if already saved
        $existing = $db->fetch("SELECT id FROM saved_jobs WHERE user_id = ? AND annonce_id = ?", 
                              [$user_id, $job_id]);
        if (!$existing) {
            $db->query("INSERT INTO saved_jobs (user_id, annonce_id, saved_at) VALUES (?, ?, NOW())", 
                      [$user_id, $job_id]);
        }
    } elseif ($action === 'unsave') {
        $db->query("DELETE FROM saved_jobs WHERE user_id = ? AND annonce_id = ?", 
                  [$user_id, $job_id]);
    }
    
    // Redirect to prevent form resubmission
    header('Location: saved_jobs.php');
    exit();
}

// Handle bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['selected_jobs'])) {
    $selected_jobs = $_POST['selected_jobs'];
    $bulk_action = $_POST['bulk_action'];
    
    if ($bulk_action === 'remove') {
        foreach ($selected_jobs as $job_id) {
            $db->query("DELETE FROM saved_jobs WHERE user_id = ? AND annonce_id = ?", 
                      [$user_id, (int)$job_id]);
        }
    } elseif ($bulk_action === 'apply') {
        // Redirect to apply to selected jobs
        $job_ids = implode(',', array_map('intval', $selected_jobs));
        header("Location: bulk_apply.php?jobs=$job_ids");
        exit();
    }
    
    header('Location: saved_jobs.php');
    exit();
}

// Get saved jobs with job details
$saved_jobs = $db->fetchAll("
    SELECT sj.*, a.*, d.nom as domaine_nom, v.nom as ville_nom, c.nom as contrat_nom,
           (SELECT COUNT(*) FROM postulation p WHERE p.annonce_id = a.id AND p.user_id = ?) as has_applied
    FROM saved_jobs sj
    JOIN annonces a ON sj.annonce_id = a.id
    LEFT JOIN domaines d ON a.domaine_id = d.id
    LEFT JOIN villes v ON a.ville_id = v.id
    LEFT JOIN contrats c ON a.contrat_id = c.id
    WHERE sj.user_id = ? AND a.status = 'active'
    ORDER BY sj.saved_at DESC
", [$user_id, $user_id]);

$total_saved = count($saved_jobs);

// Calculate statistics
$applied_jobs = array_filter($saved_jobs, function($job) {
    return $job['has_applied'] > 0;
});
$to_apply_jobs = array_filter($saved_jobs, function($job) {
    return $job['has_applied'] == 0;
});

$total_applied = count($applied_jobs);
$total_to_apply = count($to_apply_jobs);

// Get recent activity
$recent_activity = $db->fetchAll("
    SELECT sj.saved_at, a.titre, a.entreprise, 'saved' as action_type
    FROM saved_jobs sj
    JOIN annonces a ON sj.annonce_id = a.id
    WHERE sj.user_id = ?
    ORDER BY sj.saved_at DESC
    LIMIT 5
", [$user_id]);
?>

<div class="container-fluid bg-white p-0">
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Navbar End -->
    <?php include 'frontoffice/include/menu2.php'; ?>
    <!-- Header End -->

    <!-- Saved Jobs Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- Professional Header Section -->
                    <div class="professional-section">
                        <h3><i class="fas fa-bookmark"></i>Gestion des Emplois Sauvegardés</h3>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="enhanced-card">
                                    <div class="enhanced-card-header">
                                        <h4 class="enhanced-card-title">Mes Emplois Sauvegardés</h4>
                                        <div class="enhanced-card-actions">
                                            <button class="btn-action btn-action-primary" onclick="refreshSavedJobs()">
                                                <i class="fas fa-sync-alt"></i>Actualiser
                                            </button>
                                            <button class="btn-action btn-action-success" onclick="bulkApply()">
                                                <i class="fas fa-paper-plane"></i>Postuler en Lot
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted"><?= $total_saved ?> emplois sauvegardés</span>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary active" onclick="switchView('card')">
                                                <i class="fas fa-th-large"></i> Cartes
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" onclick="switchView('list')">
                                                <i class="fas fa-list"></i> Liste
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="action-buttons">
                                    <button class="btn-action btn-action-info" onclick="window.location.href='enhanced_search.php'">
                                        <i class="fas fa-search"></i>Rechercher des Emplois
                                    </button>
                                    <button class="btn-action btn-action-warning" onclick="openJobAlert()">
                                        <i class="fas fa-bell"></i>Créer une Alerte
                                    </button>
                                    <button class="btn-action btn-action-success" onclick="openProfileEdit()">
                                        <i class="fas fa-user-edit"></i>Modifier le Profil
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Statistics Section -->
                    <div class="professional-section">
                        <h3><i class="fas fa-chart-bar"></i>Statistiques</h3>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-primary">
                                        <i class="fas fa-bookmark"></i>
                                    </div>
                                    <div class="stats-number"><?= $total_saved ?></div>
                                    <div class="stats-label">Emplois Sauvegardés</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-success">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="stats-number"><?= $total_applied ?></div>
                                    <div class="stats-label">Postulés</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-warning">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="stats-number"><?= $total_to_apply ?></div>
                                    <div class="stats-label">À Postuler</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Section -->
                    <div class="professional-section">
                        <h3><i class="fas fa-history"></i>Activité Récente</h3>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="enhanced-card">
                                    <div class="enhanced-card-header">
                                        <h4 class="enhanced-card-title">Dernières Actions</h4>
                                        <div class="enhanced-card-actions">
                                            <button class="btn-action btn-action-primary" onclick="refreshActivity()">
                                                <i class="fas fa-sync-alt"></i>Actualiser
                                            </button>
                                        </div>
                                    </div>
                                    <?php if (!empty($recent_activity)): ?>
                                        <div class="timeline">
                                            <?php foreach($recent_activity as $activity): ?>
                                                <div class="timeline-item">
                                                    <div class="timeline-marker bg-primary">
                                                        <i class="fas fa-bookmark"></i>
                                                    </div>
                                                    <div class="timeline-content">
                                                        <h6 class="mb-1"><?= htmlspecialchars($activity['titre']) ?></h6>
                                                        <p class="text-muted mb-1"><?= htmlspecialchars($activity['entreprise']) ?></p>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>
                                                            <?= date('d/m/Y H:i', strtotime($activity['saved_at'])) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-history text-muted" style="font-size: 3rem;"></i>
                                            <p class="text-muted mt-2">Aucune activité récente</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Saved Jobs List -->
                    <?php if (empty($saved_jobs)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                            <h4>Aucun emploi sauvegardé</h4>
                            <p class="text-muted">Vous n'avez pas encore sauvegardé d'emplois.</p>
                            <a href="enhanced_search.php" class="btn btn-primary">Rechercher des emplois</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" id="bulkForm">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                    <label class="form-check-label" for="selectAll">
                                        Tout sélectionner
                                    </label>
                                </div>
                                <div class="d-flex gap-2">
                                    <select name="bulk_action" class="form-select" style="width: auto;">
                                        <option value="">Actions groupées</option>
                                        <option value="apply">Postuler à la sélection</option>
                                        <option value="remove">Supprimer la sélection</option>
                                    </select>
                                    <button type="submit" class="btn btn-outline-primary" id="bulkSubmit" disabled>
                                        Appliquer
                                    </button>
                                </div>
                            </div>
                            
                            <div class="row">
                                <?php foreach ($saved_jobs as $job): ?>
                                    <div class="col-lg-6 mb-4">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input job-checkbox" type="checkbox" 
                                                               name="selected_jobs[]" value="<?= $job['id'] ?>">
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <?php if ($job['urgent']): ?>
                                                            <span class="badge bg-danger">Urgent</span>
                                                        <?php endif; ?>
                                                        <?php if ($job['featured']): ?>
                                                            <span class="badge bg-warning">Mis en Avant</span>
                                                        <?php endif; ?>
                                                        <?php if ($job['has_applied']): ?>
                                                            <span class="badge bg-success">Postulé</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <h5 class="card-title">
                                                    <a href="enhanced_job_details.php?id=<?= $job['id'] ?>" 
                                                       class="text-decoration-none">
                                                        <?= htmlspecialchars($job['titre']) ?>
                                                    </a>
                                                </h5>
                                                
                                                <p class="text-muted mb-2">
                                                    <i class="fas fa-building me-2"></i>
                                                    <?= htmlspecialchars($job['company_name'] ?: $job['entreprise']) ?>
                                                </p>
                                                
                                                <p class="text-muted mb-2">
                                                    <i class="fas fa-map-marker-alt me-2"></i>
                                                    <?= htmlspecialchars($job['ville_nom']) ?>
                                                </p>
                                                
                                                <?php if ($job['salary_min'] || $job['salary_max']): ?>
                                                    <p class="text-muted mb-2">
                                                        <i class="fas fa-money-bill-wave me-2"></i>
                                                        <?= formatSalary($job['salary_min'], $job['salary_max']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <p class="text-muted mb-2">
                                                    <i class="fas fa-briefcase me-2"></i>
                                                    <?= htmlspecialchars($job['contrat_nom']) ?>
                                                </p>
                                                
                                                <p class="text-muted mb-3">
                                                    <i class="fas fa-calendar me-2"></i>
                                                    Sauvegardé le <?= date('d/m/Y', strtotime($job['saved_at'])) ?>
                                                </p>
                                                
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex gap-2">
                                                        <a href="enhanced_job_details.php?id=<?= $job['id'] ?>" 
                                                           class="btn btn-outline-primary btn-sm">
                                                            Voir détails
                                                        </a>
                                                        
                                                        <?php if (!$job['has_applied']): ?>
                                                            <a href="validation.php?id=<?= $job['id'] ?>" 
                                                               class="btn btn-primary btn-sm">
                                                                Postuler
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="application_tracking.php" 
                                                               class="btn btn-success btn-sm">
                                                                Voir candidature
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                                        <input type="hidden" name="action" value="unsave">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                                onclick="return confirm('Retirer cet emploi des sauvegardés ?')">
                                                            <i class="fas fa-heart-broken"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Saved Jobs End -->

    <?php include 'frontoffice/include/footer.php'; ?>
</div>

<!-- Professional UX/UI Styles -->
<style>
/* Professional Sections */
.professional-section {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.professional-section h3 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.professional-section h3 i {
    color: #007bff;
    font-size: 1.2em;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 20px;
}

.btn-action {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.btn-action-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.btn-action-success {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    color: white;
}

.btn-action-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: #212529;
}

.btn-action-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
}

/* Enhanced Cards */
.enhanced-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.enhanced-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.enhanced-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f8f9fa;
}

.enhanced-card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.enhanced-card-actions {
    display: flex;
    gap: 10px;
}

/* Statistics Cards */
.stats-card {
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.5rem;
    color: white;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.stats-label {
    color: #6c757d;
    font-weight: 500;
}

/* Color Schemes */
.color-scheme-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
}

.color-scheme-success {
    background: linear-gradient(135deg, #28a745, #1e7e34);
}

.color-scheme-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
}

.color-scheme-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
}

.color-scheme-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
}

/* Popup Styles */
.popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
    z-index: 9999;
    display: none;
    animation: fadeIn 0.3s ease;
}

.popup-container {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 10000;
    animation: slideIn 0.3s ease;
}

.popup-header {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    padding: 25px 30px;
    border-radius: 20px 20px 0 0;
    position: relative;
}

.popup-header h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.popup-close {
    position: absolute;
    top: 20px;
    right: 25px;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
}

.popup-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

.popup-body {
    padding: 30px;
}

.popup-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    padding: 20px 30px;
    background: #f8f9fa;
    border-radius: 0 0 20px 20px;
}

.btn-popup {
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-popup-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.btn-popup-primary:hover {
    background: linear-gradient(135deg, #0056b3, #004085);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
}

.btn-popup-secondary {
    background: #6c757d;
    color: white;
}

.btn-popup-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translate(-50%, -60%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

@media (max-width: 768px) {
    .professional-section {
        padding: 20px;
    }
    
    .enhanced-card {
        padding: 20px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- Popup Forms -->
<div id="profileEditPopup" class="popup-overlay">
    <div class="popup-container" style="width: 600px;">
        <div class="popup-header">
            <h3><i class="fas fa-user-edit me-2"></i>Modifier le Profil</h3>
            <button type="button" class="popup-close" onclick="closePopup('profileEditPopup')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="popup-body">
            <form id="profileEditForm" class="popup-form">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" name="nom" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Prénom *</label>
                            <input type="text" class="form-control" name="prenom" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" name="telephone">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Date de Naissance</label>
                            <input type="date" class="form-control" name="date_n">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Adresse</label>
                    <textarea class="form-control" name="adresse" rows="3"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Ville</label>
                            <select class="form-select" name="ville_id">
                                <option value="">Sélectionner une ville</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Domaine</label>
                            <select class="form-select" name="domaine_id">
                                <option value="">Sélectionner un domaine</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea class="form-control" name="bio" rows="4" placeholder="Parlez-nous de vous..."></textarea>
                </div>
            </form>
        </div>
        <div class="popup-actions">
            <button type="button" class="btn-popup btn-popup-secondary" onclick="closePopup('profileEditPopup')">
                <i class="fas fa-times me-2"></i>Annuler
            </button>
            <button type="button" class="btn-popup btn-popup-primary" onclick="saveProfile()">
                <i class="fas fa-save me-2"></i>Enregistrer
            </button>
        </div>
    </div>
</div>

<!-- Job Alert Popup -->
<div id="jobAlertPopup" class="popup-overlay">
    <div class="popup-container" style="width: 500px;">
        <div class="popup-header">
            <h3><i class="fas fa-bell me-2"></i>Créer une Alerte Emploi</h3>
            <button type="button" class="popup-close" onclick="closePopup('jobAlertPopup')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="popup-body">
            <form id="jobAlertForm" class="popup-form">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                <div class="form-group">
                    <label class="form-label">Nom de l'Alerte *</label>
                    <input type="text" class="form-control" name="alert_name" placeholder="Ex: Développeur Web Casablanca" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mots-clés</label>
                    <input type="text" class="form-control" name="keywords" placeholder="Ex: PHP, JavaScript, React">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Domaine</label>
                            <select class="form-select" name="domaine_id">
                                <option value="">Tous les domaines</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Ville</label>
                            <select class="form-select" name="ville_id">
                                <option value="">Toutes les villes</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Type de Contrat</label>
                            <select class="form-select" name="contrat_id">
                                <option value="">Tous les contrats</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Fréquence</label>
                            <select class="form-select" name="frequency" required>
                                <option value="daily">Quotidienne</option>
                                <option value="weekly">Hebdomadaire</option>
                                <option value="monthly">Mensuelle</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="popup-actions">
            <button type="button" class="btn-popup btn-popup-secondary" onclick="closePopup('jobAlertPopup')">
                <i class="fas fa-times me-2"></i>Annuler
            </button>
            <button type="button" class="btn-popup btn-popup-primary" onclick="createJobAlert()">
                <i class="fas fa-bell me-2"></i>Créer l'Alerte
            </button>
        </div>
    </div>
</div>

<script>
// Enhanced Popup Functions
function openPopup(popupId) {
    document.getElementById(popupId).style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closePopup(popupId) {
    document.getElementById(popupId).style.display = 'none';
    document.body.style.overflow = 'auto';
}

function openProfileEdit() {
    openPopup('profileEditPopup');
}

function openJobAlert() {
    loadDomains();
    loadCities();
    loadContrats();
    openPopup('jobAlertPopup');
}

function saveProfile() {
    const form = document.getElementById('profileEditForm');
    const formData = new FormData(form);
    
    fetch('ajax/update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Profil mis à jour avec succès!');
            closePopup('profileEditPopup');
            location.reload();
        } else {
            alert(data.message || 'Erreur lors de la mise à jour');
        }
    })
    .catch(error => {
        alert('Erreur de connexion');
    });
}

function createJobAlert() {
    const form = document.getElementById('jobAlertForm');
    const formData = new FormData(form);
    
    fetch('ajax/create_job_alert.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Alerte créée avec succès!');
            closePopup('jobAlertPopup');
            form.reset();
        } else {
            alert(data.message || 'Erreur lors de la création');
        }
    })
    .catch(error => {
        alert('Erreur de connexion');
    });
}

function refreshSavedJobs() {
    location.reload();
}

function bulkApply() {
    const selectedJobs = document.querySelectorAll('input[name="selected_jobs[]"]:checked');
    if (selectedJobs.length === 0) {
        alert('Veuillez sélectionner au moins un emploi');
        return;
    }
    
    if (confirm(`Voulez-vous postuler à ${selectedJobs.length} emploi(s) ?`)) {
        const form = document.getElementById('bulkForm');
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'bulk_action';
        actionInput.value = 'apply';
        form.appendChild(actionInput);
        form.submit();
    }
}

function switchView(view) {
    const cards = document.querySelectorAll('.job-card');
    const lists = document.querySelectorAll('.job-list');
    
    if (view === 'card') {
        cards.forEach(card => card.style.display = 'block');
        lists.forEach(list => list.style.display = 'none');
    } else {
        cards.forEach(card => card.style.display = 'none');
        lists.forEach(list => list.style.display = 'block');
    }
}

function loadCities() {
    fetch('ajax/get_cities.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('select[name="ville_id"]');
                selects.forEach(select => {
                    select.innerHTML = '<option value="">Sélectionner une ville</option>';
                    data.cities.forEach(city => {
                        select.innerHTML += `<option value="${city.id}">${city.nom}</option>`;
                    });
                });
            }
        });
}

function loadDomains() {
    fetch('ajax/get_domains.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('select[name="domaine_id"]');
                selects.forEach(select => {
                    select.innerHTML = '<option value="">Sélectionner un domaine</option>';
                    data.domains.forEach(domain => {
                        select.innerHTML += `<option value="${domain.id}">${domain.nom}</option>`;
                    });
                });
            }
        });
}

function loadContrats() {
    fetch('ajax/get_contrats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('select[name="contrat_id"]');
                selects.forEach(select => {
                    select.innerHTML = '<option value="">Sélectionner un contrat</option>';
                    data.contrats.forEach(contrat => {
                        select.innerHTML += `<option value="${contrat.id}">${contrat.nom}</option>`;
                    });
                });
            }
        });
}

// Close popup when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('popup-overlay')) {
        e.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
});

// Enhanced select all functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const jobCheckboxes = document.querySelectorAll('.job-checkbox');
    const bulkSubmit = document.getElementById('bulkSubmit');
    const bulkForm = document.getElementById('bulkForm');
    
    // Select all functionality
    selectAll.addEventListener('change', function() {
        jobCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkSubmit();
    });
    
    // Individual checkbox functionality
    jobCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkSubmit();
            
            // Update select all checkbox
            const checkedCount = document.querySelectorAll('.job-checkbox:checked').length;
            const totalCount = jobCheckboxes.length;
            
            if (checkedCount === 0) {
                selectAll.indeterminate = false;
                selectAll.checked = false;
            } else if (checkedCount === totalCount) {
                selectAll.indeterminate = false;
                selectAll.checked = true;
            } else {
                selectAll.indeterminate = true;
            }
        });
    });
    
    // Update bulk submit button
    function updateBulkSubmit() {
        const checkedCount = document.querySelectorAll('.job-checkbox:checked').length;
        const action = document.querySelector('select[name="bulk_action"]').value;
        
        bulkSubmit.disabled = checkedCount === 0 || !action;
    }
    
    // Bulk action change
    document.querySelector('select[name="bulk_action"]').addEventListener('change', updateBulkSubmit);
    
    // Form submission confirmation
    bulkForm.addEventListener('submit', function(e) {
        const action = document.querySelector('select[name="bulk_action"]').value;
        const checkedCount = document.querySelectorAll('.job-checkbox:checked').length;
        
        if (action === 'remove' && checkedCount > 0) {
            if (!confirm(`Êtes-vous sûr de vouloir supprimer ${checkedCount} emploi(s) de vos sauvegardés ?`)) {
                e.preventDefault();
            }
        }
    });
});

// Refresh saved jobs
function refreshSavedJobs() {
    location.reload();
}

// Refresh activity
function refreshActivity() {
    location.reload();
}

// Bulk apply functionality
function bulkApply() {
    const checkedJobs = document.querySelectorAll('.job-checkbox:checked');
    if (checkedJobs.length === 0) {
        showNotification('Veuillez sélectionner au moins un emploi', 'warning');
        return;
    }
    
    const jobIds = Array.from(checkedJobs).map(cb => cb.value);
    if (confirm(`Voulez-vous postuler à ${jobIds.length} emploi(s) ?`)) {
        // Redirect to bulk apply page
        window.location.href = `bulk_apply.php?jobs=${jobIds.join(',')}`;
    }
}

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Timeline CSS
const timelineCSS = `
<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e2e8f0;
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
    padding-left: 60px;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
}

.timeline-content {
    background: white;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content h6 {
    margin: 0;
    color: #1e293b;
    font-weight: 600;
}

.timeline-content p {
    margin: 5px 0;
    color: #64748b;
}

.timeline-content small {
    color: #94a3b8;
}
</style>
`;

// Inject timeline CSS
document.head.insertAdjacentHTML('beforeend', timelineCSS);
</script>

<?php
function formatSalary($min, $max) {
    if ($min && $max) {
        return number_format($min, 0, ',', ' ') . ' - ' . number_format($max, 0, ',', ' ') . ' MAD';
    } elseif ($min) {
        return 'À partir de ' . number_format($min, 0, ',', ' ') . ' MAD';
    } elseif ($max) {
        return 'Jusqu\'à ' . number_format($max, 0, ',', ' ') . ' MAD';
    }
    return 'Salaire non précisé';
}
?>
