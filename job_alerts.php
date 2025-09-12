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

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de sécurité invalide');
        }
        
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create' || $action === 'update') {
            // Get form data
            $alert_name = Security::sanitizeInput($_POST['alert_name'] ?? '');
            $keywords = Security::sanitizeInput($_POST['keywords'] ?? '');
            $domaine_id = (int)($_POST['domaine_id'] ?? 0);
            $ville_id = (int)($_POST['ville_id'] ?? 0);
            $job_type = Security::sanitizeInput($_POST['job_type'] ?? '');
            $remote_work = Security::sanitizeInput($_POST['remote_work'] ?? '');
            $salary_min = (float)($_POST['salary_min'] ?? 0);
            $salary_max = (float)($_POST['salary_max'] ?? 0);
            $experience_level = Security::sanitizeInput($_POST['experience_level'] ?? '');
            $frequency = Security::sanitizeInput($_POST['frequency'] ?? 'daily');
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Validation
            if (empty($alert_name)) {
                throw new Exception('Le nom de l\'alerte est requis');
            }
            
            if (empty($keywords) && $domaine_id <= 0 && $ville_id <= 0) {
                throw new Exception('Veuillez spécifier au moins un critère de recherche');
            }
            
            if ($action === 'create') {
                // Create new alert
                $db->insert("INSERT INTO job_alerts (
                    user_id, alert_name, keywords, domaine_id, ville_id, job_type, remote_work,
                    salary_min, salary_max, experience_level, frequency, is_active, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())", [
                    $user_id, $alert_name, $keywords, $domaine_id, $ville_id, $job_type, $remote_work,
                    $salary_min, $salary_max, $experience_level, $frequency, $is_active
                ]);
                $success_message = "Alerte créée avec succès !";
            } else {
                // Update existing alert
                $alert_id = (int)($_POST['alert_id'] ?? 0);
                $db->query("UPDATE job_alerts SET 
                    alert_name = ?, keywords = ?, domaine_id = ?, ville_id = ?, job_type = ?, 
                    remote_work = ?, salary_min = ?, salary_max = ?, experience_level = ?, 
                    frequency = ?, is_active = ?, updated_at = NOW()
                    WHERE id = ? AND user_id = ?", [
                    $alert_name, $keywords, $domaine_id, $ville_id, $job_type, $remote_work,
                    $salary_min, $salary_max, $experience_level, $frequency, $is_active,
                    $alert_id, $user_id
                ]);
                $success_message = "Alerte mise à jour avec succès !";
            }
            
            $success = true;
            
        } elseif ($action === 'delete') {
            $alert_id = (int)($_POST['alert_id'] ?? 0);
            $db->query("DELETE FROM job_alerts WHERE id = ? AND user_id = ?", [$alert_id, $user_id]);
            $success_message = "Alerte supprimée avec succès !";
            $success = true;
            
        } elseif ($action === 'toggle') {
            $alert_id = (int)($_POST['alert_id'] ?? 0);
            $is_active = (int)($_POST['is_active'] ?? 0);
            $db->query("UPDATE job_alerts SET is_active = ? WHERE id = ? AND user_id = ?", 
                      [$is_active, $alert_id, $user_id]);
            $success = true;
        }
        
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

// Get user's alerts
$alerts = $db->fetchAll("
    SELECT ja.*, d.nom as domaine_nom, v.nom as ville_nom
    FROM job_alerts ja
    LEFT JOIN domaines d ON ja.domaine_id = d.id
    LEFT JOIN villes v ON ja.ville_id = v.id
    WHERE ja.user_id = ?
    ORDER BY ja.created_at DESC
", [$user_id]);

// Get statistics
$total_alerts = count($alerts);
$active_alerts = count(array_filter($alerts, function($alert) { return $alert['is_active']; }));
$inactive_alerts = $total_alerts - $active_alerts;

// Get domains and cities for form
$domains = $db->fetchAll("SELECT * FROM domaines ORDER BY nom");
$cities = $db->fetchAll("SELECT * FROM villes ORDER BY nom");
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

    <!-- Job Alerts Content -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- Professional Header Section -->
                    <div class="professional-section">
                        <h3><i class="fas fa-bell"></i>Gestion des Alertes Emploi</h3>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="enhanced-card">
                                    <div class="enhanced-card-header">
                                        <h4 class="enhanced-card-title">Mes Alertes Emploi</h4>
                                        <div class="enhanced-card-actions">
                                            <button class="btn-action btn-action-primary" onclick="openJobAlert()">
                                                <i class="fas fa-plus"></i>Nouvelle Alerte
                                            </button>
                                            <button class="btn-action btn-action-success" onclick="refreshAlerts()">
                                                <i class="fas fa-sync-alt"></i>Actualiser
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted"><?= $total_alerts ?> alertes configurées</span>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary active" onclick="filterAlerts('all')">
                                                <i class="fas fa-list"></i> Toutes
                                            </button>
                                            <button type="button" class="btn btn-outline-success" onclick="filterAlerts('active')">
                                                <i class="fas fa-check-circle"></i> Actives
                                            </button>
                                            <button type="button" class="btn btn-outline-warning" onclick="filterAlerts('inactive')">
                                                <i class="fas fa-pause"></i> Inactives
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
                                    <button class="btn-action btn-action-warning" onclick="openProfileEdit()">
                                        <i class="fas fa-user-edit"></i>Modifier le Profil
                                    </button>
                                    <button class="btn-action btn-action-success" onclick="window.location.href='application_tracking.php'">
                                        <i class="fas fa-tasks"></i>Suivi Candidatures
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Statistics Section -->
                    <div class="professional-section">
                        <h3><i class="fas fa-chart-bar"></i>Statistiques des Alertes</h3>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-primary">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <div class="stats-number"><?= $total_alerts ?></div>
                                    <div class="stats-label">Total Alertes</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-success">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="stats-number"><?= $active_alerts ?></div>
                                    <div class="stats-label">Alertes Actives</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-warning">
                                        <i class="fas fa-pause"></i>
                                    </div>
                                    <div class="stats-number"><?= $inactive_alerts ?></div>
                                    <div class="stats-label">Alertes Inactives</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-info">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                    <div class="stats-number"><?= $total_alerts > 0 ? round(($active_alerts / $total_alerts) * 100) : 0 ?>%</div>
                                    <div class="stats-label">Taux d'Activité</div>
                                </div>
                            </div>
                        </div>
                    </div>
                                    <i class="fas fa-search me-2"></i>Rechercher des Emplois
                                </a>
                                <a href="saved_jobs.php" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-heart me-2"></i>Emplois Sauvegardés
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-9">
                    <!-- Success/Error Messages -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars(implode(', ', $errors)) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($success_message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Alerts Header -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">Mes Alertes Emploi</h5>
                                    <p class="text-muted mb-0">
                                        <?= $total_alerts ?> alerte<?= $total_alerts > 1 ? 's' : '' ?> configurée<?= $total_alerts > 1 ? 's' : '' ?>
                                    </p>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="refreshPage()">
                                        <i class="fas fa-sync-alt me-1"></i>Actualiser
                                    </button>
                                    <button class="btn btn-primary btn-sm" onclick="showCreateAlertModal()">
                                        <i class="fas fa-plus me-1"></i>Nouvelle Alerte
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alerts List -->
                    <?php if (empty($alerts)): ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-bell fa-4x text-muted"></i>
                                </div>
                                <h5 class="text-muted mb-2">Aucune alerte configurée</h5>
                                <p class="text-muted mb-4">Créez votre première alerte pour être notifié des nouvelles offres d'emploi.</p>
                                <button class="btn btn-primary" onclick="showCreateAlertModal()">
                                    <i class="fas fa-plus me-2"></i>Créer une Alerte
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alerts-container">
                            <?php foreach ($alerts as $alert): ?>
                                <div class="alert-card mb-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body p-4">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="d-flex align-items-start">
                                                        <div class="alert-icon me-3">
                                                            <div class="status-icon <?= $alert['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                                <i class="fas fa-bell text-white"></i>
                                                            </div>
                                                        </div>
                                                        <div class="alert-details flex-grow-1">
                                                            <h5 class="alert-title mb-2">
                                                                <?= htmlspecialchars($alert['alert_name']) ?>
                                                            </h5>
                                                            <div class="alert-criteria mb-3">
                                                                <?php if (!empty($alert['keywords'])): ?>
                                                                    <span class="badge bg-light text-dark me-2">
                                                                        <i class="fas fa-search text-primary me-1"></i>
                                                                        <?= htmlspecialchars($alert['keywords']) ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                                <?php if (!empty($alert['domaine_nom'])): ?>
                                                                    <span class="badge bg-light text-dark me-2">
                                                                        <i class="fas fa-briefcase text-primary me-1"></i>
                                                                        <?= htmlspecialchars($alert['domaine_nom']) ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                                <?php if (!empty($alert['ville_nom'])): ?>
                                                                    <span class="badge bg-light text-dark me-2">
                                                                        <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                                                        <?= htmlspecialchars($alert['ville_nom']) ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                                <?php if (!empty($alert['job_type'])): ?>
                                                                    <span class="badge bg-light text-dark me-2">
                                                                        <i class="fas fa-clock text-primary me-1"></i>
                                                                        <?= htmlspecialchars($alert['job_type']) ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="alert-meta">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-calendar me-1"></i>
                                                                    Créée le <?= date('d/m/Y H:i', strtotime($alert['created_at'])) ?>
                                                                </small>
                                                                <small class="text-muted ms-3">
                                                                    <i class="fas fa-sync-alt me-1"></i>
                                                                    <?= ucfirst($alert['frequency']) ?>
                                                                </small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="d-flex flex-column align-items-end h-100">
                                                        <div class="status-badge mb-3">
                                                            <span class="badge <?= $alert['is_active'] ? 'bg-success' : 'bg-secondary' ?> fs-6">
                                                                <?= $alert['is_active'] ? 'Active' : 'Inactive' ?>
                                                            </span>
                                                        </div>
                                                        <div class="alert-actions">
                                                            <button class="btn btn-outline-primary btn-sm mb-2" onclick="editAlert(<?= $alert['id'] ?>)">
                                                                <i class="fas fa-edit me-1"></i>Modifier
                                                            </button>
                                                            <button class="btn btn-outline-success btn-sm mb-2" onclick="toggleAlert(<?= $alert['id'] ?>, <?= $alert['is_active'] ? 0 : 1 ?>)">
                                                                <i class="fas fa-<?= $alert['is_active'] ? 'pause' : 'play' ?> me-1"></i>
                                                                <?= $alert['is_active'] ? 'Désactiver' : 'Activer' ?>
                                                            </button>
                                                            <button class="btn btn-outline-danger btn-sm" onclick="deleteAlert(<?= $alert['id'] ?>)">
                                                                <i class="fas fa-trash me-1"></i>Supprimer
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="alertModalLabel">
                    <i class="fas fa-bell me-2"></i>
                    <span id="modalTitle">Nouvelle Alerte Emploi</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="alertForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="alert_id" id="alertId" value="">
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="alert_name" class="form-label">Nom de l'Alerte *</label>
                            <input type="text" class="form-control" id="alert_name" name="alert_name" required>
                        </div>
                        <div class="col-12">
                            <label for="keywords" class="form-label">Mots-clés</label>
                            <input type="text" class="form-control" id="keywords" name="keywords" placeholder="Ex: développeur, PHP, JavaScript...">
                        </div>
                        <div class="col-md-6">
                            <label for="domaine_id" class="form-label">Domaine</label>
                            <select class="form-select" id="domaine_id" name="domaine_id">
                                <option value="">Tous les domaines</option>
                                <?php foreach ($domains as $domain): ?>
                                    <option value="<?= $domain['id'] ?>"><?= htmlspecialchars($domain['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="ville_id" class="form-label">Ville</label>
                            <select class="form-select" id="ville_id" name="ville_id">
                                <option value="">Toutes les villes</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= $city['id'] ?>"><?= htmlspecialchars($city['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="job_type" class="form-label">Type d'Emploi</label>
                            <select class="form-select" id="job_type" name="job_type">
                                <option value="">Tous les types</option>
                                <option value="CDI">CDI</option>
                                <option value="CDD">CDD</option>
                                <option value="Freelance">Freelance</option>
                                <option value="Stage">Stage</option>
                                <option value="Alternance">Alternance</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="remote_work" class="form-label">Télétravail</label>
                            <select class="form-select" id="remote_work" name="remote_work">
                                <option value="">Tous</option>
                                <option value="full_remote">Télétravail complet</option>
                                <option value="hybrid">Hybride</option>
                                <option value="on_site">Sur site</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="salary_min" class="form-label">Salaire Minimum (MAD)</label>
                            <input type="number" class="form-control" id="salary_min" name="salary_min" min="0">
                        </div>
                        <div class="col-md-6">
                            <label for="salary_max" class="form-label">Salaire Maximum (MAD)</label>
                            <input type="number" class="form-control" id="salary_max" name="salary_max" min="0">
                        </div>
                        <div class="col-md-6">
                            <label for="experience_level" class="form-label">Niveau d'Expérience</label>
                            <select class="form-select" id="experience_level" name="experience_level">
                                <option value="">Tous les niveaux</option>
                                <option value="junior">Junior (0-2 ans)</option>
                                <option value="mid">Intermédiaire (3-5 ans)</option>
                                <option value="senior">Senior (5+ ans)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="frequency" class="form-label">Fréquence</label>
                            <select class="form-select" id="frequency" name="frequency">
                                <option value="daily">Quotidienne</option>
                                <option value="weekly">Hebdomadaire</option>
                                <option value="monthly">Mensuelle</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    Activer cette alerte
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>
                        <span id="submitText">Créer l'Alerte</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.alert-card {
    transition: all 0.3s ease;
}

.alert-card:hover {
    transform: translateY(-2px);
}

.status-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.alert-title {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.alert-criteria {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.alert-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.status-badge .badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}

.alert-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.badge {
    border-radius: 6px;
    font-weight: 500;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.modal-content {
    border-radius: 12px;
    border: none;
}

.modal-header {
    border-radius: 12px 12px 0 0;
}

@media (max-width: 768px) {
    .alert-details {
        margin-top: 1rem;
    }

    .alert-actions {
        margin-top: 1rem;
        align-items: stretch;
    }

    .alert-criteria {
        flex-direction: column;
        gap: 0.25rem;
    }

    .alert-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<script>
// Show create alert modal
function showCreateAlertModal() {
    document.getElementById('modalTitle').textContent = 'Nouvelle Alerte Emploi';
    document.getElementById('formAction').value = 'create';
    document.getElementById('submitText').textContent = 'Créer l\'Alerte';
    document.getElementById('alertForm').reset();
    document.getElementById('alertId').value = '';
    new bootstrap.Modal(document.getElementById('alertModal')).show();
}

// Edit alert
function editAlert(alertId) {
    // This would populate the form with alert data
    // For now, we'll just show the modal
    document.getElementById('modalTitle').textContent = 'Modifier l\'Alerte';
    document.getElementById('formAction').value = 'update';
    document.getElementById('submitText').textContent = 'Mettre à Jour';
    document.getElementById('alertId').value = alertId;
    new bootstrap.Modal(document.getElementById('alertModal')).show();
}

// Toggle alert status
function toggleAlert(alertId, newStatus) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
        <input type="hidden" name="action" value="toggle">
        <input type="hidden" name="alert_id" value="${alertId}">
        <input type="hidden" name="is_active" value="${newStatus}">
    `;
    document.body.appendChild(form);
    form.submit();
}

// Delete alert
function deleteAlert(alertId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette alerte ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="alert_id" value="${alertId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Refresh page
function refreshPage() {
    location.reload();
}

// Show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>

<?php include 'frontoffice/include/footer2.php'; ?>

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

<!-- Enhanced JavaScript Functions -->
<script>
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
    showCreateAlertModal();
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

function refreshAlerts() {
    location.reload();
}

function filterAlerts(filter) {
    const alerts = document.querySelectorAll('.alert-card');
    const buttons = document.querySelectorAll('.btn-group .btn');
    
    // Update active button
    buttons.forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    alerts.forEach(alert => {
        const isActive = alert.querySelector('.badge-success') !== null;
        
        if (filter === 'all') {
            alert.style.display = 'block';
        } else if (filter === 'active' && isActive) {
            alert.style.display = 'block';
        } else if (filter === 'inactive' && !isActive) {
            alert.style.display = 'block';
        } else {
            alert.style.display = 'none';
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
</script>
