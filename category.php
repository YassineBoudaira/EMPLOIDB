

<?php 
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';
include 'frontoffice/include/header2.php'; 

// Get domain ID from URL parameter
$id = isset($_GET['iddo']) ? (int)$_GET['iddo'] : 0;

// Validate domain ID
if ($id <= 0) {
    header('Location: index.php');
    exit();
}

// Get domain information
try {
    $reqd = $db->query("SELECT * FROM domaines WHERE id = ?", [$id]);
    $datad = $reqd->fetch();
    if (!$datad) {
        header('Location: index.php');
        exit();
    }
} catch (Exception $e) {
    header('Location: index.php');
    exit();
}

// Get statistics for this domain
try {
    $total_jobs = $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE domaine_id = ? AND status = 'active'", [$id])['count'];
    $companies_count = $db->fetch("SELECT COUNT(DISTINCT profile_id) as count FROM annonces WHERE domaine_id = ? AND status = 'active'", [$id])['count'];
    $cities_count = $db->fetch("SELECT COUNT(DISTINCT ville_id) as count FROM annonces WHERE domaine_id = ? AND status = 'active'", [$id])['count'];
    $recent_jobs_count = $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE domaine_id = ? AND status = 'active' AND date_a >= DATE_SUB(NOW(), INTERVAL 7 DAY)", [$id])['count'];
} catch (Exception $e) {
    $total_jobs = 0;
    $companies_count = 0;
    $cities_count = 0;
    $recent_jobs_count = 0;
}
?> 
<body>
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


        <!-- Header End -->
        
        <!-- Category Header with Image -->
        <div class="container-fluid p-0">
            <div class="position-relative">
                <img class="img-fluid w-100" src="frontoffice/assets/img/category-header.jpg" alt="Category Header" style="height: 300px; object-fit: cover;">
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: linear-gradient(135deg, rgba(37, 157, 171, 0.9), rgba(43, 155, 255, 0.8));">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-8 text-center">
                                <h1 class="display-4 text-white animated slideInDown mb-4">
                                    <i class="fas fa-briefcase me-3"></i>Offres d'Emploi - <?= htmlspecialchars($datad['nom']) ?>
                                </h1>
                                <p class="fs-5 fw-medium text-white mb-4 pb-2">
                                    Découvrez les meilleures opportunités de carrière dans le domaine <?= htmlspecialchars($datad['nom']) ?>
                                </p>
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="enhanced_search.php" class="btn btn-light py-md-3 px-md-5 me-3 animated slideInLeft">
                                        <i class="fas fa-search me-2"></i>Recherche Avancée
                                    </a>
                                    <a href="index.php" class="btn btn-outline-light py-md-3 px-md-5 animated slideInRight">
                                        <i class="fas fa-home me-2"></i>Retour à l'Accueil
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Statistics Section -->
        <div class="container-fluid py-5 bg-light">
            <div class="container">
                <div class="row g-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center bg-white rounded p-4 shadow-sm">
                            <div class="flex-shrink-0 btn btn-success btn-square rounded-circle me-3">
                                <i class="fas fa-briefcase text-white"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-1"><?= $total_jobs ?? 0 ?></h6>
                                <span class="text-muted">Offres Disponibles</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center bg-white rounded p-4 shadow-sm">
                            <div class="flex-shrink-0 btn btn-primary btn-square rounded-circle me-3">
                                <i class="fas fa-building text-white"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-1"><?= $companies_count ?? 0 ?></h6>
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
                                <h6 class="mb-1"><?= $cities_count ?? 0 ?></h6>
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
                                <h6 class="mb-1"><?= $recent_jobs_count ?? 0 ?></h6>
                                <span class="text-muted">Nouvelles Offres</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        
        <!-- Jobs Start -->

        <div class="container-xxl py-5">
            <div class="container">
                <h1 class="text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">Liste des Offres de <?= htmlspecialchars($datad['nom']) ?></h1>
            
                <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.3s">
                    
                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane fade show p-0 active">
                            <!-- Start Annonce -->


                            <?php
                            try {
                                // Get jobs for this domain with proper joins
                                $jobs_query = "
                                    SELECT 
                                        a.*,
                                        p.company_name,
                                        p.logo as company_logo,
                                        c.nom as contrat_nom,
                                        v.nom as ville_nom,
                                        d.nom as domaine_nom
                                    FROM annonces a
                                    LEFT JOIN profiles p ON a.profile_id = p.id
                                    LEFT JOIN contrats c ON a.contrat_id = c.id
                                    LEFT JOIN villes v ON a.ville_id = v.id
                                    LEFT JOIN domaines d ON a.domaine_id = d.id
                                    WHERE a.domaine_id = ? AND a.status = 'active'
                                    ORDER BY a.date_a DESC
                                ";
                                $jobs = $db->fetchAll($jobs_query, [$id]);
                                
                                if (empty($jobs)) {
                                    echo '<div class="text-center py-5">';
                                    echo '<i class="fas fa-briefcase fa-3x text-muted mb-3"></i>';
                                    echo '<h4 class="text-muted">Aucune offre trouvée</h4>';
                                    echo '<p class="text-muted">Il n\'y a actuellement aucune offre d\'emploi dans ce domaine.</p>';
                                    echo '<a href="enhanced_search.php" class="btn btn-primary">Rechercher d\'autres domaines</a>';
                                    echo '</div>';
                                } else {
                                    foreach($jobs as $data):
                            ?>
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
                                                <?php if (isset($data['urgent']) && $data['urgent']): ?>
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
                                                    Salaire: <?php if (isset($data['salary_min']) && isset($data['salary_max']) && $data['salary_min'] && $data['salary_max']): ?>
                                                        $<?= number_format($data['salary_min']) ?> - $<?= number_format($data['salary_max']) ?>
                                                    <?php else: ?>
                                                        À négocier
                                                    <?php endif; ?>
                                                </span>
                                                <span class="text-muted small">
                                                    <i class="fas fa-user text-primary me-1"></i>
                                                    Domaine: <?= htmlspecialchars($data['domaine_nom']) ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Action Buttons -->
                                        <div class="col-md-3 col-sm-12 text-end">
                                            <div class="d-flex flex-column gap-2">
                                                <!-- Save Button -->
                                                <?php if (isset($_SESSION['user_id'])): ?>
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
                                                <a href="annoncedetaile.php?ida=<?= $data['id'] ?>" 
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
                                                <?php if (isset($data['date_fin']) && $data['date_fin']): ?>
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
                                <?php
                                    endforeach;
                                }
                                } catch (Exception $e) {
                                    echo '<div class="text-center py-5">';
                                    echo '<i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>';
                                    echo '<h4 class="text-muted">Erreur de chargement</h4>';
                                    echo '<p class="text-muted">Impossible de charger les offres d\'emploi pour le moment.</p>';
                                    echo '<a href="enhanced_search.php" class="btn btn-primary">Rechercher d\'autres domaines</a>';
                                    echo '</div>';
                                }
                                ?>


                            <!-- End Annonce -->
                            <a class="btn btn-primary py-3 px-5" href="/search.php">Browse More Jobs</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Jobs End -->

        </div>
        <!-- Jobs End -->

        <!-- Professional Footer Section -->
        <div class="container-fluid bg-light py-5 mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="professional-section">
                            <h3><i class="fas fa-info-circle"></i>Informations Complémentaires</h3>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="enhanced-card">
                                        <div class="enhanced-card-header">
                                            <h4 class="enhanced-card-title">
                                                <i class="fas fa-search me-2"></i>Recherche Avancée
                                            </h4>
                                        </div>
                                        <p class="text-muted">Trouvez l'emploi parfait avec nos outils de recherche avancés et filtres personnalisés.</p>
                                        <div class="mt-3">
                                            <a href="enhanced_search.php" class="btn btn-primary">
                                                <i class="fas fa-search me-2"></i>Rechercher
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="enhanced-card">
                                        <div class="enhanced-card-header">
                                            <h4 class="enhanced-card-title">
                                                <i class="fas fa-briefcase me-2"></i>Services Entreprises
                                            </h4>
                                        </div>
                                        <p class="text-muted">Solutions sur mesure pour les entreprises. Diffusez vos offres et accédez à une base de données de candidats qualifiés.</p>
                                        <div class="mt-3">
                                            <a href="contact.php" class="btn btn-success">
                                                <i class="fas fa-building me-2"></i>Nos Services
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="enhanced-card">
                                        <div class="enhanced-card-header">
                                            <h4 class="enhanced-card-title">
                                                <i class="fas fa-users me-2"></i>Notre Communauté
                                            </h4>
                                        </div>
                                        <p class="text-muted">Rejoignez notre communauté de professionnels et candidats qualifiés. Trouvez l'emploi de vos rêves ou recrutez les meilleurs talents.</p>
                                        <div class="mt-3">
                                            <a href="signup.php" class="btn btn-info">
                                                <i class="fas fa-user-plus me-2"></i>Rejoindre
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information Section -->
        <div class="container-fluid bg-primary text-white py-5">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center">
                        <div class="professional-section bg-transparent">
                            <h3 class="text-white"><i class="fas fa-map-marker-alt"></i>Nos Coordonnées</h3>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="enhanced-card bg-white text-dark">
                                        <div class="enhanced-card-header">
                                            <h4 class="enhanced-card-title">
                                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>Adresse
                                            </h4>
                                        </div>
                                        <p class="mb-0">54 Qu Hrilla, Safi, MAROC</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="enhanced-card bg-white text-dark">
                                        <div class="enhanced-card-header">
                                            <h4 class="enhanced-card-title">
                                                <i class="fas fa-phone me-2 text-primary"></i>Téléphone
                                            </h4>
                                        </div>
                                        <p class="mb-0">+212 697825008</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="enhanced-card bg-white text-dark">
                                        <div class="enhanced-card-header">
                                            <h4 class="enhanced-card-title">
                                                <i class="fas fa-envelope me-2 text-primary"></i>Email
                                            </h4>
                                        </div>
                                        <p class="mb-0">yassineboudairaa@gmail.com</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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

        /* Job Item Enhancements */
        .job-item {
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .job-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            border-color: #007bff;
        }

        /* Professional Section Enhancements */
        .professional-section.bg-transparent {
            background: transparent !important;
            box-shadow: none;
        }

        .professional-section.bg-transparent h3 {
            color: #ffffff !important;
        }

        .professional-section.bg-transparent .enhanced-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Enhanced Card Improvements */
        .enhanced-card.bg-white {
            background: #ffffff !important;
        }

        .enhanced-card.bg-white .enhanced-card-header {
            border-bottom-color: #e2e8f0;
        }

        .enhanced-card.bg-white .enhanced-card-title {
            color: #1e293b;
        }

        @media (max-width: 768px) {
            .professional-section {
                padding: 20px;
            }

            .enhanced-card {
                padding: 20px;
            }

                    .job-item {
            margin-bottom: 15px;
        }

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
        }
        </style>

        <script>
        // Toggle save job functionality
        function toggleSaveJob(jobId, buttonElement) {
            if (!buttonElement) return;
            
            const icon = buttonElement.querySelector('i');
            const isSaved = buttonElement.classList.contains('btn-success');
            
            // Toggle visual state immediately for better UX
            if (isSaved) {
                buttonElement.classList.remove('btn-success');
                buttonElement.classList.add('btn-outline-primary');
                icon.classList.remove('fas');
                icon.classList.add('far');
                buttonElement.innerHTML = '<i class="far fa-heart me-2"></i>Sauvegarder';
            } else {
                buttonElement.classList.remove('btn-outline-primary');
                buttonElement.classList.add('btn-success');
                icon.classList.remove('far');
                icon.classList.add('fas');
                buttonElement.innerHTML = '<i class="fas fa-heart me-2"></i>Sauvegardé';
            }
            
            // Make AJAX call to save/unsave job
            fetch('ajax/save_job.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'job_id=' + jobId + '&action=' + (isSaved ? 'unsave' : 'save')
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                } else {
                    // Revert visual state if failed
                    if (isSaved) {
                        buttonElement.classList.remove('btn-outline-primary');
                        buttonElement.classList.add('btn-success');
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        buttonElement.innerHTML = '<i class="fas fa-heart me-2"></i>Sauvegardé';
                    } else {
                        buttonElement.classList.remove('btn-success');
                        buttonElement.classList.add('btn-outline-primary');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        buttonElement.innerHTML = '<i class="far fa-heart me-2"></i>Sauvegarder';
                    }
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Erreur lors de la sauvegarde', 'error');
            });
        }

        // Show notification function
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

        // Initialize WOW.js for animations
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof WOW !== 'undefined') {
                new WOW().init();
            }
        });
        </script>

<?php include 'frontoffice/include/footer2.php'; ?>