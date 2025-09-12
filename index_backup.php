
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

        <!-- Enhanced Professional Hero Section Start -->
        <div class="emploidb-hero">
            <div class="container-fluid p-0">
                <div class="owl-carousel header-carousel position-relative">
                    <div class="owl-carousel-item position-relative">
                        <img class="img-fluid" src="frontoffice/assets/img/carousel-1.jpg" alt="Opportunités d'emploi au Maroc">
                        <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: linear-gradient(135deg, rgba(37, 99, 235, 0.8) 0%, rgba(5, 150, 105, 0.6) 100%);">
                            <div class="container">
                                <div class="row justify-content-start">
                                    <div class="col-12 col-lg-10">
                                        <div class="emploidb-animate-fade-in-up">
                                            <h1 class="display-2 text-white mb-4 emploidb-font-black">
                                                Trouvez le Job de vos Rêves au 
                                                <span style="background: linear-gradient(45deg, #fbbf24, #f59e0b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Maroc</span>
                                            </h1>
                                            <p class="emploidb-text-xl text-white mb-5 emploidb-font-medium" style="opacity: 0.95; line-height: 1.6;">
                                                Découvrez des milliers d'opportunités professionnelles dans tout le Maroc. 
                                                Votre carrière idéale vous attend sur la plateforme leader de l'emploi.
                                            </p>
                                            
                                            <!-- Professional CTA Buttons -->
                                            <div class="d-flex flex-wrap gap-3 mb-5">
                                                <a href="#searchSection" class="emploidb-btn emploidb-btn-primary emploidb-btn-lg emploidb-hover-lift">
                                                    <i class="fas fa-search me-2"></i>Rechercher un Emploi
                                                </a>
                                                <a href="signup.php" class="emploidb-btn emploidb-btn-outline emploidb-btn-lg emploidb-hover-lift" style="border-color: white; color: white;">
                                                    <i class="fas fa-user-plus me-2"></i>Créer un Compte
                                                </a>
                                                <a href="employer/register.php" class="emploidb-btn emploidb-btn-secondary emploidb-btn-lg emploidb-hover-lift">
                                                    <i class="fas fa-building me-2"></i>Recruteurs
                                                </a>
                                            </div>
                                            
                                            <!-- Quick Stats -->
                                            <div class="row g-4 mt-4">
                                                <div class="col-6 col-md-3">
                                                    <div class="text-center">
                                                        <div class="emploidb-text-3xl emploidb-font-black text-white mb-1">
                                                            <?php 
                                                            try {
                                                                echo $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE status = 'active'")['count'] ?? '500+';
                                                            } catch (Exception $e) {
                                                                echo '500+';
                                                            }
                                                            ?>
                                                        </div>
                                                        <div class="emploidb-text-sm text-white" style="opacity: 0.8;">Emplois Actifs</div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <div class="text-center">
                                                        <div class="emploidb-text-3xl emploidb-font-black text-white mb-1">
                                                            <?php 
                                                            try {
                                                                echo $db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'candidate'")['count'] ?? '1000+';
                                                            } catch (Exception $e) {
                                                                echo '1000+';
                                                            }
                                                            ?>
                                                        </div>
                                                        <div class="emploidb-text-sm text-white" style="opacity: 0.8;">Candidats Inscrits</div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <div class="text-center">
                                                        <div class="emploidb-text-3xl emploidb-font-black text-white mb-1">
                                                            <?php 
                                                            try {
                                                                echo $db->fetch("SELECT COUNT(*) as count FROM employers WHERE verified = 1")['count'] ?? '50+';
                                                            } catch (Exception $e) {
                                                                echo '50+';
                                                            }
                                                            ?>
                                                        </div>
                                                        <div class="emploidb-text-sm text-white" style="opacity: 0.8;">Entreprises Partenaires</div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <div class="text-center">
                                                        <div class="emploidb-text-3xl emploidb-font-black text-white mb-1">
                                                            <?php 
                                                            try {
                                                                echo $db->fetch("SELECT COUNT(*) as count FROM postulation WHERE status = 'hired'")['count'] ?? '200+';
                                                            } catch (Exception $e) {
                                                                echo '200+';
                                                            }
                                                            ?>
                                                        </div>
                                                        <div class="emploidb-text-sm text-white" style="opacity: 0.8;">Recrutements Réussis</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <div class="owl-carousel-item position-relative">
                    <img class="img-fluid" src="frontoffice/assets/img/carousel-2.jpg" alt="">
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: rgba(43, 57, 64, .6);">
                        <div class="container">
                            <div class="row justify-content-start">
                                <div class="col-10 col-lg-8">
                                    <h1 class="display-3 text-white animated slideInDown mb-4 fw-bold">
                                        Des Opportunités Illimitées Vous Attendent
                                    </h1>
                                    <p class="fs-5 fw-medium text-white mb-4 pb-2">
                                        Rejoignez des milliers de professionnels qui ont trouvé leur voie grâce à EMPLOIDB.
                                    </p>
                                    <div class="d-flex flex-wrap gap-3">
                                        <a href="employer/register.php" class="btn btn-primary py-md-3 px-md-5 me-3 animated slideInLeft">
                                            <i class="fas fa-building me-2"></i>Recruter des Talents
                                        </a>
                                        <a href="offers.php" class="btn btn-secondary py-md-3 px-md-5 animated slideInRight">
                                            <i class="fas fa-gift me-2"></i>Voir les Offres
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Enhanced Carousel End -->

        <!-- Enhanced Professional Search Section Start -->
        <div class="container-fluid mb-5 wow fadeIn" data-wow-delay="0.1s" style="background: var(--emploidb-gradient-primary); padding: 60px 0;" id="searchSection">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="text-center mb-5">
                            <h2 class="text-white emploidb-font-black mb-3">
                                <i class="fas fa-search me-3"></i>Recherche Intelligente d'Emplois
                            </h2>
                            <p class="emploidb-text-lg text-white mb-0" style="opacity: 0.9;">
                                Utilisez nos filtres avancés pour trouver l'emploi qui correspond parfaitement à vos compétences et aspirations
                            </p>
                        </div>
                        
                        <div class="emploidb-card emploidb-shadow-2xl" style="border-radius: var(--emploidb-radius-3xl); border: none;">
                            <form action="enhanced_search.php" method="GET" id="searchForm" class="p-4">
                            <div class="row g-3">
                                <!-- Main Search Row -->
                                <div class="col-md-10">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="emploidb-form-group">
                                                <label class="emploidb-form-label">
                                                    <i class="fas fa-keyboard me-1" style="color: var(--emploidb-primary);"></i>Mot Clé
                                                </label>
                                                <input type="text" name="keyword" class="emploidb-form-control" 
                                                       placeholder="Poste, Compétences, Entreprise..." 
                                                       value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="emploidb-form-group">
                                                <label class="emploidb-form-label">
                                                    <i class="fas fa-briefcase me-1" style="color: var(--emploidb-secondary);"></i>Domaine
                                                </label>
                                                <select name="domaine_id" class="emploidb-form-control">
                                                    <option value="">Tous les Domaines</option>
                                                    <?php 
                                                    $domaines = $db->fetchAll("SELECT * FROM domaines ORDER BY nom");
                                                    foreach($domaines as $datad):
                                                    ?>
                                                    <option value="<?= htmlspecialchars($datad['id']) ?>" 
                                                            <?= (isset($_GET['domaine_id']) && $_GET['domaine_id'] == $datad['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($datad['nom']) ?>
                                                    </option>
                                                    <?php endforeach;?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label text-muted small mb-1">
                                                    <i class="fas fa-map-marker-alt me-1"></i>Ville
                                                </label>
                                                <select name="ville_id" class="form-select border-2 border-light">
                                                    <option value="">Toutes les Villes</option>
                                                    <?php 
                                                    $villes = $db->fetchAll("SELECT * FROM villes ORDER BY nom");
                                                    foreach($villes as $datav):
                                                    ?>
                                                    <option value="<?= htmlspecialchars($datav['nom']) ?>"
                                                            <?= (isset($_GET['ville_id']) && $_GET['ville_id'] == $datav['nom']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($datav['nom']) ?>
                                                    </option>
                                                    <?php endforeach;?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label text-muted small mb-1">
                                                    <i class="fas fa-clock me-1"></i>Type de Contrat
                                                </label>
                                                <select name="job_type" class="form-select border-2 border-light">
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
                                    
                                    <!-- Advanced Search Options (Collapsible) -->
                                    <div class="row g-3 mt-2" id="advancedSearchOptions" style="display: none;">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label text-muted small mb-1">
                                                    <i class="fas fa-home me-1"></i>Type de Travail
                                                </label>
                                                <select name="remote_work" class="form-select border-2 border-light">
                                                    <option value="">Tous les Types</option>
                                                    <option value="on-site" <?= (isset($_GET['remote_work']) && $_GET['remote_work'] == 'on-site') ? 'selected' : '' ?>>Sur Site</option>
                                                    <option value="remote" <?= (isset($_GET['remote_work']) && $_GET['remote_work'] == 'remote') ? 'selected' : '' ?>>Télétravail</option>
                                                    <option value="hybrid" <?= (isset($_GET['remote_work']) && $_GET['remote_work'] == 'hybrid') ? 'selected' : '' ?>>Hybride</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label text-muted small mb-1">
                                                    <i class="fas fa-money-bill-wave me-1"></i>Salaire
                                                </label>
                                                <select name="salary_range" class="form-select border-2 border-light">
                                                    <option value="">Tous les Salaires</option>
                                                    <option value="0-20000" <?= (isset($_GET['salary_range']) && $_GET['salary_range'] == '0-20000') ? 'selected' : '' ?>>0 - 20,000 MAD</option>
                                                    <option value="20000-40000" <?= (isset($_GET['salary_range']) && $_GET['salary_range'] == '20000-40000') ? 'selected' : '' ?>>20,000 - 40,000 MAD</option>
                                                    <option value="40000-60000" <?= (isset($_GET['salary_range']) && $_GET['salary_range'] == '40000-60000') ? 'selected' : '' ?>>40,000 - 60,000 MAD</option>
                                                    <option value="60000-80000" <?= (isset($_GET['salary_range']) && $_GET['salary_range'] == '60000-80000') ? 'selected' : '' ?>>60,000 - 80,000 MAD</option>
                                                    <option value="80000+" <?= (isset($_GET['salary_range']) && $_GET['salary_range'] == '80000+') ? 'selected' : '' ?>>80,000+ MAD</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label text-muted small mb-1">
                                                    <i class="fas fa-calendar me-1"></i>Date de Publication
                                                </label>
                                                <select name="date_posted" class="form-select border-2 border-light">
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
                                                <label class="form-label text-muted small mb-1">
                                                    <i class="fas fa-star me-1"></i>Niveau d'Expérience
                                                </label>
                                                <select name="experience_level" class="form-select border-2 border-light">
                                                    <option value="">Tous les Niveaux</option>
                                                    <option value="entry" <?= (isset($_GET['experience_level']) && $_GET['experience_level'] == 'entry') ? 'selected' : '' ?>>Débutant</option>
                                                    <option value="mid" <?= (isset($_GET['experience_level']) && $_GET['experience_level'] == 'mid') ? 'selected' : '' ?>>Intermédiaire</option>
                                                    <option value="senior" <?= (isset($_GET['experience_level']) && $_GET['experience_level'] == 'senior') ? 'selected' : '' ?>>Senior</option>
                                                    <option value="executive" <?= (isset($_GET['experience_level']) && $_GET['experience_level'] == 'executive') ? 'selected' : '' ?>>Cadre</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Professional Search Buttons -->
                                <div class="col-md-2">
                                    <div class="d-flex flex-column gap-3 h-100 justify-content-end">
                                        <button type="submit" class="emploidb-btn emploidb-btn-primary emploidb-btn-lg w-100 emploidb-hover-lift">
                                            <i class="fas fa-search me-2"></i>Rechercher
                                        </button>
                                        <button type="button" class="emploidb-btn emploidb-btn-outline w-100" id="advancedSearchBtn">
                                            <i class="fas fa-sliders-h me-2"></i>Filtres Avancés
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Professional Quick Actions -->
                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <div class="d-flex flex-wrap justify-content-center gap-3">
                                        <a href="enhanced_search.php" class="emploidb-btn emploidb-btn-ghost emploidb-btn-sm emploidb-hover-scale">
                                            <i class="fas fa-search-plus me-2"></i>Recherche Avancée
                                        </a>
                                        <a href="offers.php" class="emploidb-btn emploidb-btn-ghost emploidb-btn-sm emploidb-hover-scale">
                                            <i class="fas fa-tags me-2"></i>Offres Spéciales
                                        </a>
                                            <i class="fas fa-user-plus me-2"></i>Créer un Compte
                                        </a>
                                        <a href="employer/register.php" class="emploidb-btn emploidb-btn-ghost emploidb-btn-sm emploidb-hover-scale">
                                            <i class="fas fa-building me-2"></i>Espace Recruteur
                                        </a>
                                    </div>
                                    <p class="emploidb-text-sm emploidb-text-muted mt-3 mb-0">
                                        <i class="fas fa-lightbulb me-1" style="color: var(--emploidb-warning);"></i>
                                        Conseil : Utilisez des mots-clés spécifiques pour obtenir de meilleurs résultats
                                    </p>
                                </div>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <!-- Enhanced Professional Search Section End -->

        <!-- Statistics Section Start -->
        <div class="container-fluid py-5 bg-light">
            <div class="container">
                <div class="row g-4">
                    <?php
                    // Get statistics
                    $total_jobs = $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE status = 'active' OR status IS NULL")['count'];
                    $total_companies = $db->fetch("SELECT COUNT(DISTINCT entreprise) as count FROM annonces WHERE entreprise IS NOT NULL")['count'];
                    $total_cities = $db->fetch("SELECT COUNT(DISTINCT v.nom) as count FROM annonces a LEFT JOIN villes v ON a.ville_id = v.id WHERE v.nom IS NOT NULL")['count'];
                    $recent_jobs = $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE date_a >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'];
                    ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center bg-white rounded p-4 shadow-sm">
                            <div class="flex-shrink-0 btn btn-primary btn-square rounded-circle me-3">
                                <i class="fas fa-briefcase text-white"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-1"><?= number_format($total_jobs) ?></h6>
                                <span class="text-muted">Offres d'Emploi</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center bg-white rounded p-4 shadow-sm">
                            <div class="flex-shrink-0 btn btn-success btn-square rounded-circle me-3">
                                <i class="fas fa-building text-white"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-1"><?= number_format($total_companies) ?></h6>
                                <span class="text-muted">Entreprises</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center bg-white rounded p-4 shadow-sm">
                            <div class="flex-shrink-0 btn btn-warning btn-square rounded-circle me-3">
                                <i class="fas fa-map-marker-alt text-white"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-1"><?= number_format($total_cities) ?></h6>
                                <span class="text-muted">Villes</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center bg-white rounded p-4 shadow-sm">
                            <div class="flex-shrink-0 btn btn-info btn-square rounded-circle me-3">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-1"><?= number_format($recent_jobs) ?></h6>
                                <span class="text-muted">Nouvelles Offres</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Statistics Section End -->

        <!-- Enhanced Jobs Section Start -->
        <div class="container-xxl py-5">
            <div class="container">
                <div class="text-center mb-5">
                    <h1 class="mb-3 wow fadeInUp" data-wow-delay="0.1s">
                        <i class="fas fa-briefcase text-primary me-3"></i>
                        Offres d'Emploi au Maroc
                    </h1>
                    <p class="text-muted wow fadeInUp" data-wow-delay="0.2s">
                        Découvrez les meilleures opportunités de carrière dans tout le Maroc
                    </p>
                </div>
                
                <!-- View Toggle and Controls -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center gap-3">
                            <span class="text-muted fw-semibold">Affichage:</span>
                            <div class="btn-group" role="group" aria-label="View mode toggle">
                                <input type="radio" class="btn-check" name="viewMode" id="cardView" value="card" checked>
                                <label class="btn btn-outline-primary" for="cardView">
                                    <i class="fas fa-th-large me-2"></i>Cartes
                                </label>
                                
                                <input type="radio" class="btn-check" name="viewMode" id="listView" value="list">
                                <label class="btn btn-outline-primary" for="listView">
                                    <i class="fas fa-list me-2"></i>Liste
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 text-end">
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <span class="text-muted small">Trier par:</span>
                            <select class="form-select form-select-sm" style="width: auto;" id="sortSelect">
                                <option value="recent">Plus récentes</option>
                                <option value="urgent">Urgentes</option>
                                <option value="featured">Mis en avant</option>
                                <option value="applications">Plus de candidatures</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.3s">
                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane fade show p-0 active">
                            <!-- Enhanced Job Listings with Pagination -->
                            <?php
                            // Pagination settings
                            $items_per_page = 10; // For list view
                            $cards_per_page = 9; // 3x3 grid for card view
                            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                            $view_mode = isset($_GET['view']) ? $_GET['view'] : 'card';
                            
                            // Get total count for pagination
                            $total_count = $db->fetch("
                                SELECT COUNT(*) as count 
                                FROM annonces a 
                                WHERE a.status = 'active' OR a.status IS NULL
                            ")['count'];
                            
                            // Calculate pagination
                            $items_per_page_actual = ($view_mode === 'list') ? $items_per_page : $cards_per_page;
                            $total_pages = ceil($total_count / $items_per_page_actual);
                            $offset = ($page - 1) * $items_per_page_actual;
                            
                            // Enhanced query with pagination
                            $annonces = $db->fetchAll("
                                SELECT a.*, 
                                       d.nom as domaine_nom,
                                       v.nom as ville_nom,
                                       c.nom as contrat_nom,
                                       (SELECT COUNT(*) FROM postulation p WHERE p.annonce_id = a.id) as applications_count
                                FROM annonces a
                                LEFT JOIN domaines d ON a.domaine_id = d.id
                                LEFT JOIN villes v ON a.ville_id = v.id
                                LEFT JOIN contrats c ON a.contrat_id = c.id
                                WHERE a.status = 'active' OR a.status IS NULL
                                ORDER BY a.urgent DESC, a.featured DESC, a.date_a DESC
                                LIMIT ? OFFSET ?
                            ", [$items_per_page_actual, $offset]);
                            
                            if (empty($annonces)):
                            ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                    <h4 class="text-muted">Aucune offre d'emploi trouvée</h4>
                                    <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                                    <a href="enhanced_search.php" class="btn btn-primary">Voir toutes les offres</a>
                                </div>
                            <?php else: ?>
                                <!-- Card View -->
                                <div id="cardViewContainer" class="<?= $view_mode === 'card' ? '' : 'd-none' ?>">
                                    <div class="row g-4">
                                        <?php foreach($annonces as $data): ?>
                                            <div class="col-lg-4 col-md-6 col-sm-12">
                                                <div class="job-card h-100 wow fadeIn" data-wow-delay="0.1s">
                                                    <!-- Card Header with Image and Badges -->
                                                    <div class="card-header-section position-relative">
                                                        <img class="card-header-image" 
                                                             src="upload/<?= htmlspecialchars($data['image'] ?: 'default-job.jpg') ?>" 
                                                             alt="<?= htmlspecialchars($data['titre']) ?>" 
                                                             onerror="this.src='upload/default-job.jpg'">
                                                        
                                                        <!-- Status Badges -->
                                                        <div class="status-badges">
                                                            <?php if ($data['urgent']): ?>
                                                                <span class="badge bg-danger">
                                                                    <i class="fas fa-exclamation-triangle me-1"></i>Urgent
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php if ($data['featured']): ?>
                                                                <span class="badge bg-warning">
                                                                    <i class="fas fa-star me-1"></i>Mis en Avant
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <!-- Save Button -->
                                                        <?php if (Security::isLoggedIn()): ?>
                                                            <button class="save-btn" 
                                                                    onclick="toggleSaveJob(<?= $data['id'] ?>)" 
                                                                    id="saveBtn_<?= $data['id'] ?>"
                                                                    title="Ajouter aux favoris">
                                                                <i class="far fa-heart text-primary" id="heartIcon_<?= $data['id'] ?>"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <!-- Card Body -->
                                                    <div class="card-body-section">
                                                        <!-- Job Title -->
                                                        <h5 class="job-title">
                                                            <i class="fas fa-briefcase text-primary me-2"></i>
                                                            <?= htmlspecialchars($data['titre']) ?>
                                                        </h5>
                                                        
                                                        <!-- Company -->
                                                        <p class="company-name">
                                                            <i class="fas fa-building text-muted me-2"></i>
                                                            <?= htmlspecialchars($data['entreprise'] ?: 'Entreprise non précisée') ?>
                                                        </p>
                                                        
                                                        <!-- Description -->
                                                        <p class="job-description">
                                                            <?= htmlspecialchars(substr($data['description'] ?: 'Description non disponible', 0, 120)) ?><?= strlen($data['description'] ?: '') > 120 ? '...' : '' ?>
                                                        </p>
                                                        
                                                        <!-- Job Details -->
                                                        <div class="job-details">
                                                            <div class="detail-row">
                                                                <span class="detail-item">
                                                                    <i class="fas fa-map-marker-alt text-primary"></i>
                                                                    <?= htmlspecialchars($data['ville_nom'] ?: 'Lieu non précisé') ?>
                                                                </span>
                                                                <span class="detail-item">
                                                                    <i class="fas fa-clock text-primary"></i>
                                                                    <?= htmlspecialchars($data['contrat_nom'] ?: ucfirst($data['job_type'] ?? 'Temps plein')) ?>
                                                                </span>
                                                            </div>
                                                            <div class="detail-row">
                                                                <span class="detail-item">
                                                                    <i class="fas fa-money-bill-wave text-primary"></i>
                                                                    <?php if ($data['salary_min'] && $data['salary_max']): ?>
                                                                        <?= number_format($data['salary_min']) ?> - <?= number_format($data['salary_max']) ?> MAD
                                                                    <?php else: ?>
                                                                        Salaire à négocier
                                                                    <?php endif; ?>
                                                                </span>
                                                                <span class="detail-item">
                                                                    <i class="fas fa-tag text-primary"></i>
                                                                    <?= htmlspecialchars($data['domaine_nom'] ?: 'Domaine non précisé') ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Skills -->
                                                        <?php if ($data['skills_required']): ?>
                                                            <div class="skills-section">
                                                                <strong class="skills-label">Compétences:</strong>
                                                                <div class="skills-tags">
                                                                    <?php 
                                                                    $skills = explode(',', $data['skills_required']);
                                                                    foreach(array_slice($skills, 0, 3) as $skill): 
                                                                        $skill = trim($skill);
                                                                        if (!empty($skill)):
                                                                    ?>
                                                                        <span class="skill-tag"><?= htmlspecialchars($skill) ?></span>
                                                                    <?php 
                                                                        endif;
                                                                    endforeach; 
                                                                    ?>
                                                                    <?php if (count(array_filter(array_map('trim', $skills))) > 3): ?>
                                                                        <span class="skill-tag more-tag">+<?= count(array_filter(array_map('trim', $skills))) - 3 ?> autres</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="skills-section">
                                                                <strong class="skills-label">Compétences:</strong>
                                                                <span class="no-skills">Non précisées</span>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <!-- Card Footer -->
                                                        <div class="card-footer-section">
                                                            <div class="footer-info">
                                                                <small class="publish-date">
                                                                    <i class="far fa-calendar-alt text-muted me-1"></i> 
                                                                    Publié le <?= date('d/m/Y', strtotime($data['date_a'])) ?>
                                                                </small>
                                                                <?php if ($data['applications_count'] > 0): ?>
                                                                    <small class="applications-count">
                                                                        <i class="fas fa-users text-muted me-1"></i> 
                                                                        <?= $data['applications_count'] ?> candidat(s)
                                                                    </small>
                                                                <?php endif; ?>
                                                            </div>
                                                            <a href="enhanced_job_details.php?id=<?= $data['id'] ?>" 
                                                               class="btn btn-primary btn-sm view-details-btn">
                                                                <i class="fas fa-eye me-1"></i>Voir Détails
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Enhanced List View -->
                                <div id="listViewContainer" class="<?= $view_mode === 'list' ? '' : 'd-none' ?>">
                                    <div class="enhanced-list-container">
                                        <?php foreach($annonces as $data): ?>
                                            <div class="enhanced-job-item wow fadeIn" data-wow-delay="0.1s">
                                                <!-- Job Header Section -->
                                                <div class="job-header-section">
                                                    <div class="job-image-container">
                                                        <img class="job-list-image" 
                                                             src="upload/<?= htmlspecialchars($data['image'] ?: 'default-job.jpg') ?>" 
                                                             alt="<?= htmlspecialchars($data['titre']) ?>" 
                                                             onerror="this.src='upload/default-job.jpg'">
                                                        <div class="image-overlay">
                                                            <div class="status-badges">
                                                                <?php if ($data['urgent']): ?>
                                                                    <span class="badge urgent-badge">
                                                                        <i class="fas fa-exclamation-triangle me-1"></i>Urgent
                                                                    </span>
                                                                <?php endif; ?>
                                                                <?php if ($data['featured']): ?>
                                                                    <span class="badge featured-badge">
                                                                        <i class="fas fa-star me-1"></i>Mis en Avant
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="job-main-content">
                                                        <div class="job-title-section">
                                                            <h4 class="job-list-title">
                                                                <i class="fas fa-briefcase text-primary me-2"></i>
                                                                <?= htmlspecialchars($data['titre']) ?>
                                                            </h4>
                                                            <p class="company-name-large">
                                                                <i class="fas fa-building text-muted me-2"></i>
                                                                <?= htmlspecialchars($data['entreprise'] ?: 'Entreprise non précisée') ?>
                                                            </p>
                                                        </div>
                                                        
                                                        <div class="job-description-section">
                                                            <p class="job-description-text">
                                                                <?= htmlspecialchars(substr($data['description'] ?: 'Description non disponible', 0, 300)) ?><?= strlen($data['description'] ?: '') > 300 ? '...' : '' ?>
                                                            </p>
                                                        </div>
                                                        
                                                        <div class="job-details-grid">
                                                            <div class="detail-item">
                                                                <i class="fas fa-map-marker-alt text-primary"></i>
                                                                <span><?= htmlspecialchars($data['ville_nom'] ?: 'Lieu non précisé') ?></span>
                                                            </div>
                                                            <div class="detail-item">
                                                                <i class="fas fa-clock text-primary"></i>
                                                                <span><?= htmlspecialchars($data['contrat_nom'] ?: ucfirst($data['job_type'] ?? 'Temps plein')) ?></span>
                                                            </div>
                                                            <div class="detail-item">
                                                                <i class="fas fa-money-bill-wave text-primary"></i>
                                                                <span>
                                                                    <?php if ($data['salary_min'] && $data['salary_max']): ?>
                                                                        <?= number_format($data['salary_min']) ?> - <?= number_format($data['salary_max']) ?> MAD
                                                                    <?php else: ?>
                                                                        Salaire à négocier
                                                                    <?php endif; ?>
                                                                </span>
                                                            </div>
                                                            <div class="detail-item">
                                                                <i class="fas fa-tag text-primary"></i>
                                                                <span><?= htmlspecialchars($data['domaine_nom'] ?: 'Domaine non précisé') ?></span>
                                                            </div>
                                                        </div>
                                                        
                                                        <?php if ($data['skills_required']): ?>
                                                        <div class="skills-section-list">
                                                            <strong class="skills-label">Compétences requises:</strong>
                                                            <div class="skills-tags-list">
                                                                <?php 
                                                                $skills = explode(',', $data['skills_required']);
                                                                foreach(array_slice($skills, 0, 5) as $skill): 
                                                                    $skill = trim($skill);
                                                                    if (!empty($skill)):
                                                                ?>
                                                                    <span class="skill-tag-list"><?= htmlspecialchars($skill) ?></span>
                                                                <?php 
                                                                    endif;
                                                                endforeach; 
                                                                ?>
                                                                <?php if (count(array_filter(array_map('trim', $skills))) > 5): ?>
                                                                    <span class="skill-tag-list more-skills">+<?= count(array_filter(array_map('trim', $skills))) - 5 ?> autres</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="job-actions-section">
                                                        <div class="action-buttons">
                                                            <?php if (Security::isLoggedIn()): ?>
                                                                <button class="save-btn-list" 
                                                                        onclick="toggleSaveJob(<?= $data['id'] ?>)" 
                                                                        id="saveBtn_<?= $data['id'] ?>"
                                                                        title="Ajouter aux favoris">
                                                                    <i class="far fa-heart text-primary" id="heartIcon_<?= $data['id'] ?>"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                            <a href="enhanced_job_details.php?id=<?= $data['id'] ?>" 
                                                               class="btn btn-primary btn-lg view-details-btn-list">
                                                                <i class="fas fa-eye me-2"></i>Voir Détails
                                                            </a>
                                                        </div>
                                                        
                                                        <div class="job-meta-info">
                                                            <div class="meta-item">
                                                                <i class="far fa-calendar-alt text-muted"></i>
                                                                <span>Publié le <?= date('d/m/Y', strtotime($data['date_a'])) ?></span>
                                                            </div>
                                                            <?php if ($data['applications_count'] > 0): ?>
                                                            <div class="meta-item">
                                                                <i class="fas fa-users text-muted"></i>
                                                                <span><?= $data['applications_count'] ?> candidat(s)</span>
                                                            </div>
                                                            <?php endif; ?>
                                                            <?php if ($data['date_fin']): ?>
                                                            <div class="meta-item">
                                                                <i class="far fa-calendar-times text-danger"></i>
                                                                <span>Expire le <?= date('d/m/Y', strtotime($data['date_fin'])) ?></span>
                                                            </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Enhanced Pagination -->
                                <?php if ($total_pages > 1): ?>
                                <div class="row mt-5">
                                    <div class="col-12">
                                        <nav aria-label="Job listings pagination">
                                            <ul class="pagination justify-content-center">
                                                <!-- Previous Page -->
                                                <?php if ($page > 1): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="?page=<?= $page - 1 ?>&view=<?= $view_mode ?>" aria-label="Previous">
                                                            <i class="fas fa-chevron-left"></i>
                                                        </a>
                                                    </li>
                                                <?php else: ?>
                                                    <li class="page-item disabled">
                                                        <span class="page-link">
                                                            <i class="fas fa-chevron-left"></i>
                                                        </span>
                                                    </li>
                                                <?php endif; ?>

                                                <!-- Page Numbers -->
                                                <?php
                                                $start_page = max(1, $page - 2);
                                                $end_page = min($total_pages, $page + 2);
                                                
                                                if ($start_page > 1): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="?page=1&view=<?= $view_mode ?>">1</a>
                                                    </li>
                                                    <?php if ($start_page > 2): ?>
                                                        <li class="page-item disabled">
                                                            <span class="page-link">...</span>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                        <a class="page-link" href="?page=<?= $i ?>&view=<?= $view_mode ?>"><?= $i ?></a>
                                                    </li>
                                                <?php endfor; ?>

                                                <?php if ($end_page < $total_pages): ?>
                                                    <?php if ($end_page < $total_pages - 1): ?>
                                                        <li class="page-item disabled">
                                                            <span class="page-link">...</span>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="?page=<?= $total_pages ?>&view=<?= $view_mode ?>"><?= $total_pages ?></a>
                                                    </li>
                                                <?php endif; ?>

                                                <!-- Next Page -->
                                                <?php if ($page < $total_pages): ?>
                                                    <li class="page-item">
                                                        <a class="page-link" href="?page=<?= $page + 1 ?>&view=<?= $view_mode ?>" aria-label="Next">
                                                            <i class="fas fa-chevron-right"></i>
                                                        </a>
                                                    </li>
                                                <?php else: ?>
                                                    <li class="page-item disabled">
                                                        <span class="page-link">
                                                            <i class="fas fa-chevron-right"></i>
                                                        </span>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </nav>
                                        
                                        <!-- Page Info -->
                                        <div class="text-center mt-3">
                                            <p class="text-muted">
                                                Affichage de <?= $offset + 1 ?> à <?= min($offset + $items_per_page_actual, $total_count) ?> 
                                                sur <?= number_format($total_count) ?> offre(s) 
                                                (Page <?= $page ?> sur <?= $total_pages ?>)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <!-- View More Button -->
                                <div class="text-center mt-4">
                                    <a class="btn btn-primary py-3 px-5" href="enhanced_search.php">
                                        <i class="fas fa-search me-2"></i>Voir Plus d'Offres
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Enhanced Jobs Section End -->

        <!-- Enhanced JavaScript -->
        <script>
        // Enhanced Advanced Search Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const advancedSearchBtn = document.getElementById('advancedSearchBtn');
            const advancedSearchOptions = document.getElementById('advancedSearchOptions');
            
            if (advancedSearchBtn && advancedSearchOptions) {
                advancedSearchBtn.addEventListener('click', function() {
                    if (advancedSearchOptions.style.display === 'none') {
                        advancedSearchOptions.style.display = 'block';
                        advancedSearchBtn.innerHTML = '<i class="fas fa-times me-2"></i>Masquer Options';
                        advancedSearchBtn.classList.remove('btn-outline-primary');
                        advancedSearchBtn.classList.add('btn-primary');
                    } else {
                        advancedSearchOptions.style.display = 'none';
                        advancedSearchBtn.innerHTML = '<i class="fas fa-cog me-2"></i>Options Avancées';
                        advancedSearchBtn.classList.remove('btn-primary');
                        advancedSearchBtn.classList.add('btn-outline-primary');
                    }
                });
            }
            
            // Initialize saved jobs
            initializeSavedJobs();
            
            // Add smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // View Mode Toggle Functionality
            initializeViewToggle();
            
            // Sort Functionality
            initializeSorting();
        });
        
        // View Mode Toggle
        function initializeViewToggle() {
            const cardViewRadio = document.getElementById('cardView');
            const listViewRadio = document.getElementById('listView');
            const cardViewContainer = document.getElementById('cardViewContainer');
            const listViewContainer = document.getElementById('listViewContainer');
            
            if (cardViewRadio && listViewRadio) {
                cardViewRadio.addEventListener('change', function() {
                    if (this.checked) {
                        cardViewContainer.classList.remove('d-none');
                        listViewContainer.classList.add('d-none');
                        updateURL('card');
                    }
                });
                
                listViewRadio.addEventListener('change', function() {
                    if (this.checked) {
                        listViewContainer.classList.remove('d-none');
                        cardViewContainer.classList.add('d-none');
                        updateURL('list');
                    }
                });
            }
        }
        
        // Update URL with view mode
        function updateURL(viewMode) {
            const url = new URL(window.location);
            url.searchParams.set('view', viewMode);
            url.searchParams.delete('page'); // Reset to page 1 when changing view
            window.history.replaceState({}, '', url);
        }
        
        // Sorting Functionality
        function initializeSorting() {
            const sortSelect = document.getElementById('sortSelect');
            if (sortSelect) {
                sortSelect.addEventListener('change', function() {
                    const url = new URL(window.location);
                    url.searchParams.set('sort', this.value);
                    url.searchParams.delete('page'); // Reset to page 1 when sorting
                    window.location.href = url.toString();
                });
            }
        }
        
        // Enhanced Save job functionality
        function toggleSaveJob(jobId) {
            if (!<?= Security::isLoggedIn() ? 'true' : 'false' ?>) {
                showNotification('Veuillez vous connecter pour sauvegarder des offres', 'warning');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
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
                    const saveBtn = document.getElementById('saveBtn_' + jobId);
                    
                    if (data.saved) {
                        heartIcon.classList.remove('far');
                        heartIcon.classList.add('fas');
                        heartIcon.classList.add('text-danger');
                        saveBtn.title = 'Retirer des favoris';
                    } else {
                        heartIcon.classList.remove('fas');
                        heartIcon.classList.remove('text-danger');
                        heartIcon.classList.add('far');
                        saveBtn.title = 'Ajouter aux favoris';
                    }
                    
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Erreur lors de la sauvegarde', 'error');
            });
        }
        
        // Initialize saved jobs on page load
        function initializeSavedJobs() {
            if (!<?= Security::isLoggedIn() ? 'true' : 'false' ?>) return;
            
            fetch('ajax/save_job.php?action=get_saved')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.saved_jobs) {
                    data.saved_jobs.forEach(jobId => {
                        const heartIcon = document.getElementById('heartIcon_' + jobId);
                        const saveBtn = document.getElementById('saveBtn_' + jobId);
                        if (heartIcon && saveBtn) {
                            heartIcon.classList.remove('far');
                            heartIcon.classList.add('fas');
                            heartIcon.classList.add('text-danger');
                            saveBtn.title = 'Retirer des favoris';
                        }
                    });
                }
            })
            .catch(error => console.error('Error loading saved jobs:', error));
        }
        
        // Enhanced notification system
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger'} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
            notification.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'times-circle'} me-2"></i>
                    <span>${message}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(notification);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
        </script>

        <!-- Enhanced Card Styles -->
        <style>
        .job-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            max-height: 420px;
        }
        
        .job-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }
        
        /* Card Header Section */
        .card-header-section {
            position: relative;
            height: 120px;
            overflow: hidden;
        }
        
        .card-header-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .job-card:hover .card-header-image {
            transform: scale(1.02);
        }
        
        /* Status Badges */
        .status-badges {
            position: absolute;
            top: 8px;
            left: 8px;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        
        .status-badges .badge {
            font-size: 0.65rem;
            padding: 3px 6px;
            border-radius: 4px;
            font-weight: 600;
        }
        
        /* Save Button */
        .save-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(255,255,255,0.95);
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .save-btn:hover {
            background: rgba(255,255,255,1);
            transform: scale(1.05);
        }
        
        /* Card Body Section */
        .card-body-section {
            padding: 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        /* Job Title */
        .job-title {
            font-size: 1rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 6px;
            line-height: 1.2;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Company Name */
        .company-name {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        /* Job Description */
        .job-description {
            font-size: 0.8rem;
            color: #6c757d;
            line-height: 1.4;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Job Details */
        .job-details {
            margin-bottom: 12px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            gap: 8px;
        }
        
        .detail-item {
            font-size: 0.75rem;
            color: #495057;
            display: flex;
            align-items: center;
            gap: 4px;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .detail-item i {
            font-size: 0.8rem;
            width: 14px;
            text-align: center;
            flex-shrink: 0;
        }
        
        /* Skills Section */
        .skills-section {
            margin-bottom: 12px;
        }
        
        .skills-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 6px;
            display: block;
        }
        
        .skills-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 3px;
        }
        
        .skill-tag {
            background: #f8f9fa;
            color: #495057;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 500;
            border: 1px solid #e9ecef;
        }
        
        .skill-tag.more-tag {
            background: #e9ecef;
            color: #6c757d;
            font-style: italic;
        }
        
        .no-skills {
            font-size: 0.75rem;
            color: #adb5bd;
            font-style: italic;
        }
        
        /* Card Footer */
        .card-footer-section {
            margin-top: auto;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .footer-info {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }
        
        .publish-date, .applications-count {
            font-size: 0.7rem;
            color: #adb5bd;
        }
        
        .view-details-btn {
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .view-details-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0,123,255,0.25);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .detail-row {
                flex-direction: column;
                gap: 4px;
            }
            
            .card-footer-section {
                flex-direction: column;
                gap: 8px;
                align-items: stretch;
            }
            
            .view-details-btn {
                text-align: center;
            }
        }
        
        /* Additional compact styling */
        .job-card .card-body-section {
            min-height: 0;
        }
        
        .job-card .skills-tags {
            max-height: 40px;
            overflow: hidden;
        }
        
        .job-card .detail-item {
            min-width: 0;
        }
        
        /* Ensure consistent card heights */
        .col-lg-4, .col-md-6, .col-sm-12 {
            margin-bottom: 1rem;
        }
        
        /* Compact grid spacing */
        .row.g-4 {
            --bs-gutter-x: 1rem;
            --bs-gutter-y: 1rem;
        }
        
        /* Enhanced List View Styles */
        .enhanced-list-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .enhanced-job-item {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .enhanced-job-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .job-header-section {
            display: flex;
            gap: 1.5rem;
            padding: 1.5rem;
            align-items: flex-start;
        }
        
        /* Job Image Container */
        .job-image-container {
            position: relative;
            flex-shrink: 0;
            width: 180px;
            height: 140px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .job-list-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .enhanced-job-item:hover .job-list-image {
            transform: scale(1.05);
        }
        
        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0) 50%);
        }
        
        /* Status Badges */
        .status-badges {
            position: absolute;
            top: 8px;
            left: 8px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        
        .urgent-badge {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(220,53,69,0.3);
        }
        
        .featured-badge {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: #212529;
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(255,193,7,0.3);
        }
        
        /* Job Main Content */
        .job-main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .job-title-section {
            margin-bottom: 0.5rem;
        }
        
        .job-list-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }
        
        .company-name-large {
            font-size: 1.1rem;
            color: #6c757d;
            font-weight: 500;
            margin-bottom: 0;
        }
        
        .job-description-section {
            margin-bottom: 1rem;
        }
        
        .job-description-text {
            font-size: 0.95rem;
            color: #495057;
            line-height: 1.6;
            margin-bottom: 0;
        }
        
        /* Job Details Grid */
        .job-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #495057;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid #007bff;
        }
        
        .detail-item i {
            font-size: 1rem;
            color: #007bff;
            width: 20px;
            text-align: center;
        }
        
        /* Skills Section */
        .skills-section-list {
            margin-bottom: 1rem;
        }
        
        .skills-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .skills-tags-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .skill-tag-list {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #1976d2;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            border: 1px solid #90caf9;
        }
        
        .skill-tag-list.more-skills {
            background: linear-gradient(135deg, #f3e5f5, #e1bee7);
            color: #7b1fa2;
            border-color: #ba68c8;
        }
        
        /* Job Actions Section */
        .job-actions-section {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: flex-end;
            min-width: 200px;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            align-items: flex-end;
        }
        
        .save-btn-list {
            background: rgba(255,255,255,0.9);
            border: 2px solid #e9ecef;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .save-btn-list:hover {
            background: #fff;
            border-color: #007bff;
            transform: scale(1.05);
        }
        
        .view-details-btn-list {
            font-size: 0.9rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #007bff, #0056b3);
            border: none;
            box-shadow: 0 2px 8px rgba(0,123,255,0.3);
        }
        
        .view-details-btn-list:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,123,255,0.4);
            background: linear-gradient(135deg, #0056b3, #004085);
        }
        
        /* Job Meta Info */
        .job-meta-info {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            align-items: flex-end;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .meta-item i {
            font-size: 0.9rem;
        }
        
        /* Responsive Design for List View */
        @media (max-width: 992px) {
            .job-header-section {
                flex-direction: column;
                gap: 1rem;
            }
            
            .job-image-container {
                width: 100%;
                height: 200px;
            }
            
            .job-details-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 0.5rem;
            }
            
            .job-actions-section {
                align-items: stretch;
                min-width: auto;
            }
            
            .action-buttons {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }
        
        @media (max-width: 768px) {
            .job-header-section {
                padding: 1rem;
            }
            
            .job-list-title {
                font-size: 1.2rem;
            }
            
            .company-name-large {
                font-size: 1rem;
            }
            
            .job-description-text {
                font-size: 0.9rem;
            }
            
            .job-details-grid {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
            
            .detail-item {
                font-size: 0.85rem;
                padding: 0.4rem;
            }
            
            .skills-tags-list {
                gap: 0.3rem;
            }
            
            .skill-tag-list {
                font-size: 0.75rem;
                padding: 0.3rem 0.6rem;
            }
        }
        </style>

        <!-- Testimonial Start -->
        
        <!-- Testimonial End -->
        
    <?php include 'frontoffice/include/footer2.php'; ?>