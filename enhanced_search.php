<?php 
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';
include 'frontoffice/include/header2.php'; 
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

    <!-- Enhanced Search Content -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- Professional Header Section -->
                    <div class="professional-section">
                        <h3><i class="fas fa-search"></i>Recherche Avancée d'Emplois</h3>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="enhanced-card">
                                    <div class="enhanced-card-header">
                                        <h4 class="enhanced-card-title">Recherche d'Emplois</h4>
                                        <div class="enhanced-card-actions">
                                            <?php if (Security::isLoggedIn()): ?>
                                            <button class="btn-action btn-action-primary" onclick="saveSearch()">
                                                <i class="fas fa-save"></i>Sauvegarder
                                            </button>
                                            <button class="btn-action btn-action-success" onclick="createJobAlert()">
                                                <i class="fas fa-bell"></i>Créer une Alerte
                                            </button>
                                            <?php else: ?>
                                            <button class="btn-action btn-action-warning" onclick="showLoginPrompt()">
                                                <i class="fas fa-sign-in-alt"></i>Se connecter pour sauvegarder
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Enhanced Search Form -->
                                    <form action="enhanced_search.php" method="GET" id="searchForm">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="form-label">Mot Clé, Compétences, Entreprise</label>
                                                    <input type="text" name="keyword" class="form-control" placeholder="Ex: Développeur PHP, Casablanca" value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="form-label">Domaine</label>
                                                    <select name="domaine_id" class="form-select">
                                                        <option value="">Tous les Domaines</option>
                                                        <?php 
                                                        $domaines = $db->fetchAll("SELECT * FROM domaines ORDER BY nom");
                                                        foreach($domaines as $domaine):
                                                        ?>
                                                        <option value="<?= htmlspecialchars($domaine['id']) ?>" <?= (isset($_GET['domaine_id']) && $_GET['domaine_id'] == $domaine['id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($domaine['nom']) ?>
                                                        </option>
                                                        <?php endforeach;?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="form-label">Ville</label>
                                                    <select name="ville_id" class="form-select">
                                                        <option value="">Toutes les Villes</option>
                                                        <?php 
                                                        $villes = $db->fetchAll("SELECT * FROM villes ORDER BY nom");
                                                        foreach($villes as $ville):
                                                        ?>
                                                        <option value="<?= htmlspecialchars($ville['id']) ?>" <?= (isset($_GET['ville_id']) && $_GET['ville_id'] == $ville['id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($ville['nom']) ?>
                                                        </option>
                                                        <?php endforeach;?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label class="form-label">Type de Contrat</label>
                                                    <select name="job_type" class="form-select">
                                                        <option value="">Tous les Types</option>
                                                        <option value="full-time" <?= (isset($_GET['job_type']) && $_GET['job_type'] == 'full-time') ? 'selected' : '' ?>>Temps Plein</option>
                                                        <option value="part-time" <?= (isset($_GET['job_type']) && $_GET['job_type'] == 'part-time') ? 'selected' : '' ?>>Temps Partiel</option>
                                                        <option value="internship" <?= (isset($_GET['job_type']) && $_GET['job_type'] == 'internship') ? 'selected' : '' ?>>Stage</option>
                                                        <option value="freelance" <?= (isset($_GET['job_type']) && $_GET['job_type'] == 'freelance') ? 'selected' : '' ?>>Freelance</option>
                                                        <option value="contract" <?= (isset($_GET['job_type']) && $_GET['job_type'] == 'contract') ? 'selected' : '' ?>>Contrat</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Advanced Search Options -->
                                        <div class="row mt-3" id="advancedSearch" style="display: none;">
                                            <div class="col-md-12">
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label class="form-label">Salaire Minimum (MAD)</label>
                                                            <input type="number" name="salary_min" class="form-control" placeholder="Min" value="<?= htmlspecialchars($_GET['salary_min'] ?? '') ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label class="form-label">Salaire Maximum (MAD)</label>
                                                            <input type="number" name="salary_max" class="form-control" placeholder="Max" value="<?= htmlspecialchars($_GET['salary_max'] ?? '') ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label class="form-label">Date de Publication</label>
                                                            <select name="date_posted" class="form-select">
                                                                <option value="">Toutes les Dates</option>
                                                                <option value="today" <?= (isset($_GET['date_posted']) && $_GET['date_posted'] == 'today') ? 'selected' : '' ?>>Aujourd'hui</option>
                                                                <option value="week" <?= (isset($_GET['date_posted']) && $_GET['date_posted'] == 'week') ? 'selected' : '' ?>>Cette Semaine</option>
                                                                <option value="month" <?= (isset($_GET['date_posted']) && $_GET['date_posted'] == 'month') ? 'selected' : '' ?>>Ce Mois</option>
                                                                <option value="3months" <?= (isset($_GET['date_posted']) && $_GET['date_posted'] == '3months') ? 'selected' : '' ?>>3 Derniers Mois</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label class="form-label">Niveau d'Expérience</label>
                                                            <select name="experience_level" class="form-select">
                                                                <option value="">Tous les Niveaux</option>
                                                                <option value="entry" <?= (isset($_GET['experience_level']) && $_GET['experience_level'] == 'entry') ? 'selected' : '' ?>>Débutant</option>
                                                                <option value="mid" <?= (isset($_GET['experience_level']) && $_GET['experience_level'] == 'mid') ? 'selected' : '' ?>>Intermédiaire</option>
                                                                <option value="senior" <?= (isset($_GET['experience_level']) && $_GET['experience_level'] == 'senior') ? 'selected' : '' ?>>Senior</option>
                                                                <option value="executive" <?= (isset($_GET['experience_level']) && $_GET['experience_level'] == 'executive') ? 'selected' : '' ?>>Direction</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-md-12">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" name="urgent" value="1" <?= (isset($_GET['urgent']) && $_GET['urgent'] == '1') ? 'checked' : '' ?>>
                                                            <label class="form-check-label">Emplois Urgents</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox" name="featured" value="1" <?= (isset($_GET['featured']) && $_GET['featured'] == '1') ? 'checked' : '' ?>>
                                                            <label class="form-check-label">Emplois Mis en Avant</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mt-3">
                                            <div class="col-md-12 text-center">
                                                <button type="submit" class="btn btn-primary btn-lg">
                                                    <i class="fas fa-search me-2"></i>Rechercher
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-lg ms-2" id="toggleAdvanced">
                                                    <i class="fas fa-cog me-2"></i>Recherche Avancée
                                                </button>
                                                <a href="enhanced_search.php" class="btn btn-outline-danger btn-lg ms-2">
                                                    <i class="fas fa-times me-2"></i>Effacer
                                                </a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="action-buttons">
                                    <button class="btn-action btn-action-info" onclick="window.location.href='user_profile.php'">
                                        <i class="fas fa-user"></i>Mon Profil
                                    </button>
                                    <button class="btn-action btn-action-warning" onclick="openProfileEdit()">
                                        <i class="fas fa-user-edit"></i>Modifier le Profil
                                    </button>
                                    <button class="btn-action btn-action-success" onclick="window.location.href='saved_jobs.php'">
                                        <i class="fas fa-heart"></i>Emplois Sauvegardés
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

    <!-- Jobs Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1 class="text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">
                        <?php if (!empty($_GET)): ?>
                            Résultats de Recherche
                        <?php else: ?>
                            Toutes les Offres d'Emploi
                        <?php endif; ?>
                    </h1>
                    
                    <!-- Filters Summary -->
                    <?php if (!empty($_GET)): ?>
                    <div class="alert alert-info mb-4">
                        <h6><i class="fas fa-filter"></i> Filtres Actifs:</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if (!empty($_GET['keyword'])): ?>
                                <span class="badge bg-primary">Mot-clé: <?= htmlspecialchars($_GET['keyword']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($_GET['domaine_id'])): ?>
                                <?php $domaine = $db->fetch("SELECT nom FROM domaines WHERE id = ?", [$_GET['domaine_id']]); ?>
                                <?php if ($domaine): ?>
                                    <span class="badge bg-primary">Domaine: <?= htmlspecialchars($domaine['nom']) ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (!empty($_GET['ville_id'])): ?>
                                <?php $ville = $db->fetch("SELECT nom FROM villes WHERE id = ?", [$_GET['ville_id']]); ?>
                                <?php if ($ville): ?>
                                    <span class="badge bg-primary">Ville: <?= htmlspecialchars($ville['nom']) ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (!empty($_GET['job_type'])): ?>
                                <span class="badge bg-primary">Type: <?= htmlspecialchars($_GET['job_type']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($_GET['remote_work'])): ?>
                                <span class="badge bg-primary">Mode: <?= htmlspecialchars($_GET['remote_work']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($_GET['salary_min']) || !empty($_GET['salary_max'])): ?>
                                <span class="badge bg-primary">Salaire: <?= htmlspecialchars($_GET['salary_min'] ?? '0') ?> - <?= htmlspecialchars($_GET['salary_max'] ?? '∞') ?> MAD</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Sort Options -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <select class="form-select" id="sortSelect">
                                <option value="date_desc">Plus Récent</option>
                                <option value="date_asc">Plus Ancien</option>
                                <option value="salary_desc">Salaire Élevé</option>
                                <option value="salary_asc">Salaire Faible</option>
                                <option value="urgent">Urgent en Premier</option>
                                <option value="featured">Mis en Avant</option>
                            </select>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="text-muted" id="resultsCount"></span>
                        </div>
                    </div>

                    <!-- Jobs List -->
                    <div id="jobsList">
                        <?php
                        // Build the search query
                        $where_conditions = ["a.status = 'active'"];
                        $params = [];
                        
                        // Keyword search
                        if (!empty($_GET['keyword'])) {
                            $keyword = Security::sanitizeInput($_GET['keyword']);
                            $where_conditions[] = "(a.titre LIKE ? OR a.description LIKE ? OR a.entreprise LIKE ? OR a.skills_required LIKE ?)";
                            $keyword_param = "%$keyword%";
                            $params = array_merge($params, [$keyword_param, $keyword_param, $keyword_param, $keyword_param]);
                        }
                        
                        // Domain filter
                        if (!empty($_GET['domaine_id'])) {
                            $where_conditions[] = "a.domaine_id = ?";
                            $params[] = Security::sanitizeInput($_GET['domaine_id']);
                        }
                        
                        // City filter
                        if (!empty($_GET['ville_id'])) {
                            $where_conditions[] = "a.ville_id = ?";
                            $params[] = Security::sanitizeInput($_GET['ville_id']);
                        }
                        
                        // Job type filter
                        if (!empty($_GET['job_type'])) {
                            $where_conditions[] = "a.job_type = ?";
                            $params[] = Security::sanitizeInput($_GET['job_type']);
                        }
                        
                        // Remote work filter
                        if (!empty($_GET['remote_work'])) {
                            $where_conditions[] = "a.remote_work = ?";
                            $params[] = Security::sanitizeInput($_GET['remote_work']);
                        }
                        
                        // Salary filters
                        if (!empty($_GET['salary_min'])) {
                            $where_conditions[] = "a.salary_max >= ?";
                            $params[] = Security::sanitizeInput($_GET['salary_min']);
                        }
                        
                        if (!empty($_GET['salary_max'])) {
                            $where_conditions[] = "a.salary_min <= ?";
                            $params[] = Security::sanitizeInput($_GET['salary_max']);
                        }
                        
                        // Date posted filter
                        if (!empty($_GET['date_posted'])) {
                            switch ($_GET['date_posted']) {
                                case 'today':
                                    $where_conditions[] = "DATE(a.date_a) = CURDATE()";
                                    break;
                                case 'week':
                                    $where_conditions[] = "a.date_a >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                                    break;
                                case 'month':
                                    $where_conditions[] = "a.date_a >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
                                    break;
                                case '3months':
                                    $where_conditions[] = "a.date_a >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
                                    break;
                            }
                        }
                        
                        // Experience level filter
                        if (!empty($_GET['experience_level'])) {
                            $where_conditions[] = "a.experience_level = ?";
                            $params[] = Security::sanitizeInput($_GET['experience_level']);
                        }
                        
                        // Urgent filter
                        if (!empty($_GET['urgent']) && $_GET['urgent'] == '1') {
                            $where_conditions[] = "a.urgent = TRUE";
                        }
                        
                        // Featured filter
                        if (!empty($_GET['featured']) && $_GET['featured'] == '1') {
                            $where_conditions[] = "a.featured = TRUE";
                        }
                        
                        // Build the complete query
                        $where_clause = implode(" AND ", $where_conditions);
                        
                        // Determine sort order
                        $sort_order = "ORDER BY a.date_a DESC";
                        if (!empty($_GET['sort'])) {
                            switch ($_GET['sort']) {
                                case 'date_asc':
                                    $sort_order = "ORDER BY a.date_a ASC";
                                    break;
                                case 'salary_desc':
                                    $sort_order = "ORDER BY a.salary_max DESC";
                                    break;
                                case 'salary_asc':
                                    $sort_order = "ORDER BY a.salary_min ASC";
                                    break;
                                case 'urgent':
                                    $sort_order = "ORDER BY a.urgent DESC, a.date_a DESC";
                                    break;
                                case 'featured':
                                    $sort_order = "ORDER BY a.featured DESC, a.date_a DESC";
                                    break;
                            }
                        }
                        
                        $query = "SELECT a.*, 
                                        d.nom as domaine_nom,
                                        v.nom as ville_nom,
                                        c.nom as contrat_nom,
                                        p.nom as profile_nom,
                                        p.prenom as profile_prenom
                                 FROM annonces a
                                 LEFT JOIN domaines d ON a.domaine_id = d.id
                                 LEFT JOIN villes v ON a.ville_id = v.id
                                 LEFT JOIN contrats c ON a.contrat_id = c.id
                                 LEFT JOIN profiles p ON a.profile_id = p.id
                                 WHERE $where_clause
                                 $sort_order";
                        
                        $annonces = $db->fetchAll($query, $params);
                        $total_results = count($annonces);
                        ?>
                        
                        <div id="resultsCount" class="mb-3">
                            <strong><?= $total_results ?></strong> offre(s) trouvée(s)
                        </div>
                        
                        <?php if ($total_results > 0): ?>
                            <?php foreach($annonces as $data): ?>
                                <div class="job-card bg-white rounded shadow-sm border-0 mb-4 wow fadeInUp" data-wow-delay="0.1s">
                                    <div class="card-body p-4">
                                        <div class="row align-items-center">
                                            <!-- Job Image -->
                                            <div class="col-md-2 col-sm-12 mb-3 mb-md-0">
                                                <div class="job-image-container">
                                                    <img class="img-fluid rounded" 
                                                         src="upload/<?= htmlspecialchars($data['image'] ?: 'default-job.jpg') ?>" 
                                                         alt="<?= htmlspecialchars($data['titre']) ?>"
                                                         style="width: 120px; height: 80px; object-fit: cover;">
                                                </div>
                                            </div>
                                            
                                            <!-- Job Content -->
                                            <div class="col-md-7 col-sm-12">
                                                <!-- Job Title -->
                                                <div class="d-flex align-items-center mb-2">
                                                    <h5 class="mb-0 me-3">
                                                        <i class="fas fa-user-tie text-primary me-2"></i>
                                                        <?= htmlspecialchars($data['titre']) ?>
                                                    </h5>
                                                    <?php if ($data['urgent']): ?>
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>Urgent
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Job Description -->
                                                <p class="text-muted mb-3">
                                                    <?= htmlspecialchars(substr($data['description'], 0, 150)) ?>...
                                                </p>
                                                
                                                <!-- Job Details -->
                                                <div class="d-flex flex-wrap gap-3">
                                                    <span class="text-muted small">
                                                        <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                                        Location: <?= htmlspecialchars($data['ville_nom'] ?: 'Non spécifiée') ?>
                                                    </span>
                                                    <span class="text-muted small">
                                                        <i class="fas fa-clock text-primary me-1"></i>
                                                        Contrat: <?= htmlspecialchars($data['contrat_nom'] ?: 'Non spécifié') ?>
                                                    </span>
                                                    <span class="text-muted small">
                                                        <i class="fas fa-money-bill-alt text-primary me-1"></i>
                                                        Salaire: <?php if ($data['salary_min'] && $data['salary_max']): ?>
                                                            $<?= number_format($data['salary_min']) ?> - $<?= number_format($data['salary_max']) ?>
                                                        <?php else: ?>
                                                            À négocier
                                                        <?php endif; ?>
                                                    </span>
                                                    <span class="text-muted small">
                                                        <i class="fas fa-user text-primary me-1"></i>
                                                        Domaine: <?= htmlspecialchars($data['domaine_nom'] ?: 'Non spécifié') ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <div class="col-md-3 col-sm-12 text-end">
                                                <div class="d-flex flex-column gap-2">
                                                    <!-- Save Button -->
                                                    <?php if (Security::isLoggedIn()): ?>
                                                        <?php 
                                                        $is_saved = $db->fetch("SELECT id FROM saved_jobs WHERE user_id = ? AND annonce_id = ?", [$_SESSION['user_id'], $data['id']]);
                                                        ?>
                                                        <button class="btn btn-outline-primary btn-sm" 
                                                                onclick="toggleSaveJob(<?= $data['id'] ?>)" 
                                                                id="saveBtn_<?= $data['id'] ?>">
                                                            <i class="<?= $is_saved ? 'fas' : 'far' ?> fa-heart" id="heartIcon_<?= $data['id'] ?>"></i>
                                                            <?= $is_saved ? 'Retirer' : 'Sauvegarder' ?>
                                                        </button>
                                                    <?php else: ?>
                                                        <a href="login.php" class="btn btn-outline-primary btn-sm">
                                                            <i class="far fa-heart"></i>
                                                            Se connecter
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <!-- View Details Button -->
                                                    <a href="enhanced_job_details.php?id=<?= $data['id'] ?>" 
                                                       class="btn btn-primary btn-sm">
                                                        Afficher les details
                                                    </a>
                                                </div>
                                                
                                                <!-- Date Information -->
                                                <div class="mt-3">
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-calendar text-primary me-1"></i>
                                                        Date Line: <?= date('Y-m-d', strtotime($data['date_a'])) ?>
                                                    </small>
                                                    <?php if ($data['date_fin']): ?>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-calendar text-primary me-1"></i>
                                                        Date Fin: <?= date('Y-m-d', strtotime($data['date_fin'])) ?>
                                                    </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">Aucune offre trouvée</h4>
                                <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                                <a href="enhanced_search.php" class="btn btn-primary">Effacer les Filtres</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Jobs End -->
</div>

<script>
// Toggle advanced search
document.getElementById('toggleAdvanced').addEventListener('click', function() {
    const advancedSearch = document.getElementById('advancedSearch');
    const isVisible = advancedSearch.style.display !== 'none';
    advancedSearch.style.display = isVisible ? 'none' : 'block';
    this.innerHTML = isVisible ? '<i class="fas fa-cog"></i> Recherche Avancée' : '<i class="fas fa-times"></i> Masquer';
});

// Sort functionality
document.getElementById('sortSelect').addEventListener('change', function() {
    const url = new URL(window.location);
    url.searchParams.set('sort', this.value);
    window.location.href = url.toString();
});

// Set current sort value
<?php if (!empty($_GET['sort'])): ?>
document.getElementById('sortSelect').value = '<?= htmlspecialchars($_GET['sort']) ?>';
<?php endif; ?>

// Save job functionality
function toggleSaveJob(jobId) {
    if (!<?= Security::isLoggedIn() ? 'true' : 'false' ?>) {
        window.location.href = 'login.php';
        return;
    }
    
    fetch('ajax/save_job.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'job_id=' + jobId + '&action=toggle'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const heartIcon = document.getElementById('heartIcon_' + jobId);
            if (data.saved) {
                heartIcon.className = 'fas fa-heart text-danger';
            } else {
                heartIcon.className = 'far fa-heart text-primary';
            }
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de la sauvegarde');
    });
}

// Check saved jobs on page load
<?php if (Security::isLoggedIn()): ?>
document.addEventListener('DOMContentLoaded', function() {
    // This would be populated by checking saved jobs for the current user
    // For now, we'll just show the default state
});
<?php endif; ?>
</script>

<?php include 'frontoffice/include/footer2.php'; ?>

<!-- Professional UX/UI Styles -->
<style>
/* Job Cards - Matching the Image Design */
.job-card {
    transition: all 0.3s ease;
    border: 1px solid #e0e0e0 !important;
    border-radius: 12px !important;
    overflow: hidden;
}

.job-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
}

.job-image-container {
    display: flex;
    justify-content: center;
    align-items: center;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 5px;
}

.job-image-container img {
    border-radius: 6px;
    border: 1px solid #e0e0e0;
}

/* Job Title Styling */
.job-card h5 {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
}

/* Job Details Styling */
.job-card .text-muted.small {
    font-size: 0.85rem;
    color: #6c757d !important;
}

.job-card .text-muted.small i {
    width: 16px;
    text-align: center;
}

/* Action Buttons Styling */
.job-card .btn-outline-primary {
    border-color: #007bff;
    color: #007bff;
    font-size: 0.85rem;
    padding: 0.375rem 0.75rem;
}

.job-card .btn-outline-primary:hover {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.job-card .btn-primary {
    background: linear-gradient(135deg, #259dab, #2b9bff);
    border: none;
    font-size: 0.85rem;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
}

.job-card .btn-primary:hover {
    background: linear-gradient(135deg, #1e7e8a, #1e7e8a);
    transform: translateY(-1px);
}

/* Date Information Styling */
.job-card small.text-muted {
    font-size: 0.75rem;
    color: #6c757d !important;
}

.job-card small.text-muted i {
    width: 14px;
    text-align: center;
}

/* Badge Styling */
.job-card .badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .job-card .col-md-2 {
        text-align: center;
        margin-bottom: 15px;
    }
    
    .job-card .col-md-3 {
        text-align: center !important;
        margin-top: 15px;
    }
    
    .job-card .d-flex.flex-column {
        flex-direction: row !important;
        justify-content: center;
        gap: 10px;
    }
}

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

/* Form Styling */
.form-group {
    margin-bottom: 1rem;
}

.form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
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

function createJobAlert() {
    // Pre-fill the form with current search criteria
    const form = document.getElementById('searchForm');
    const alertForm = document.getElementById('jobAlertForm');
    
    // Get current search values
    const keyword = form.querySelector('input[name="keyword"]').value;
    const domaineId = form.querySelector('select[name="domaine_id"]').value;
    const villeId = form.querySelector('select[name="ville_id"]').value;
    const jobType = form.querySelector('select[name="job_type"]').value;
    
    // Pre-fill alert form
    if (keyword) {
        alertForm.querySelector('input[name="alert_name"]').value = `Recherche: ${keyword}`;
        alertForm.querySelector('input[name="keywords"]').value = keyword;
    }
    if (domaineId) {
        alertForm.querySelector('select[name="domaine_id"]').value = domaineId;
    }
    if (villeId) {
        alertForm.querySelector('select[name="ville_id"]').value = villeId;
    }
    if (jobType) {
        alertForm.querySelector('select[name="contrat_id"]').value = jobType;
    }
    
    loadDomains();
    loadCities();
    loadContrats();
    openPopup('jobAlertPopup');
}

function saveSearch() {
    // Save current search criteria
    const form = document.getElementById('searchForm');
    const formData = new FormData(form);
    const searchParams = new URLSearchParams(formData);
    
    // Store in localStorage
    localStorage.setItem('savedSearch', searchParams.toString());
    alert('Recherche sauvegardée avec succès!');
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

// Toggle advanced search
document.getElementById('toggleAdvanced').addEventListener('click', function() {
    const advancedSearch = document.getElementById('advancedSearch');
    const isVisible = advancedSearch.style.display !== 'none';
    advancedSearch.style.display = isVisible ? 'none' : 'block';
    this.innerHTML = isVisible ? '<i class="fas fa-cog me-2"></i>Recherche Avancée' : '<i class="fas fa-times me-2"></i>Masquer';
});

// Sort functionality
document.getElementById('sortSelect').addEventListener('change', function() {
    const url = new URL(window.location);
    url.searchParams.set('sort', this.value);
    window.location.href = url.toString();
});

// Set current sort value
<?php if (!empty($_GET['sort'])): ?>
document.getElementById('sortSelect').value = '<?= htmlspecialchars($_GET['sort']) ?>';
<?php endif; ?>

// Save job functionality
function toggleSaveJob(jobId, buttonElement = null) {
    if (!<?= Security::isLoggedIn() ? 'true' : 'false' ?>) {
        showLoginPrompt();
        return;
    }
    
    fetch('ajax/save_job.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'job_id=' + jobId + '&action=toggle&csrf_token=<?= Security::generateCSRFToken() ?>'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (buttonElement) {
                if (data.saved) {
                    buttonElement.className = 'btn btn-sm btn-danger';
                    buttonElement.innerHTML = '<i class="fas fa-heart"></i> Retirer';
                } else {
                    buttonElement.className = 'btn btn-sm btn-outline-danger';
                    buttonElement.innerHTML = '<i class="fas fa-heart"></i> Sauvegarder';
                }
            } else {
                const heartIcon = document.getElementById('heartIcon_' + jobId);
                if (data.saved) {
                    heartIcon.className = 'fas fa-heart text-danger';
                } else {
                    heartIcon.className = 'far fa-heart text-primary';
                }
            }
            showNotification(data.message, 'success');
        } else {
            showNotification('Erreur: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erreur lors de la sauvegarde', 'error');
    });
}

// Save search functionality
function saveSearch() {
    if (!<?= Security::isLoggedIn() ? 'true' : 'false' ?>) {
        showLoginPrompt();
        return;
    }
    
    const searchParams = new URLSearchParams(window.location.search);
    const searchData = {
        keyword: searchParams.get('keyword') || '',
        domaine_id: searchParams.get('domaine_id') || '',
        ville_id: searchParams.get('ville_id') || '',
        job_type: searchParams.get('job_type') || '',
        remote_work: searchParams.get('remote_work') || '',
        salary_min: searchParams.get('salary_min') || '',
        salary_max: searchParams.get('salary_max') || '',
        date_posted: searchParams.get('date_posted') || '',
        experience_level: searchParams.get('experience_level') || ''
    };
    
    localStorage.setItem('savedSearch', JSON.stringify(searchData));
    showNotification('Recherche sauvegardée avec succès!', 'success');
}

// Create job alert functionality
function createJobAlert() {
    if (!<?= Security::isLoggedIn() ? 'true' : 'false' ?>) {
        showLoginPrompt();
        return;
    }
    
    openJobAlert();
}

// Show login prompt
function showLoginPrompt() {
    showNotification('Veuillez vous connecter pour utiliser cette fonctionnalité', 'warning');
    setTimeout(() => {
        if (confirm('Voulez-vous être redirigé vers la page de connexion?')) {
            window.location.href = 'login.php';
        }
    }, 2000);
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

// Check saved jobs on page load
<?php if (Security::isLoggedIn()): ?>
document.addEventListener('DOMContentLoaded', function() {
    // This would be populated by checking saved jobs for the current user
    // For now, we'll just show the default state
});
<?php endif; ?>
</script>

</body>
</html>
