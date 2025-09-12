<?php 
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';
include 'frontoffice/include/header2.php'; 
?>

<body>

<?php   
// Simple input validation
$keyword = trim($_GET['keyword'] ?? '');
$domaine_id = intval($_GET['idd'] ?? 0);
$ville_id = intval($_GET['idv'] ?? 0);

// Build search conditions for both local and aggregated jobs
$conditions = [];
$params = [];
$search_title = "Résultats de recherche";

if (!empty($keyword)) {
    $conditions[] = "(titre LIKE ? OR description LIKE ? OR entreprise LIKE ? OR company_name LIKE ?)";
    $keyword_param = "%$keyword%";
    $params[] = $keyword_param;
    $params[] = $keyword_param;
    $params[] = $keyword_param;
    $params[] = $keyword_param;
    $search_title .= " pour '$keyword'";
}

if ($domaine_id > 0) {
    $conditions[] = "domaine_id = ?";
    $params[] = $domaine_id;
    $domaine_data = $db->fetch("SELECT nom FROM domaines WHERE id = ?", [$domaine_id]);
    if ($domaine_data) {
        $search_title .= " dans le domaine '" . $domaine_data['nom'] . "'";
    }
}

if ($ville_id > 0) {
    $conditions[] = "(ville_id = ? OR location LIKE ?)";
    $params[] = $ville_id;
    $ville_data = $db->fetch("SELECT nom FROM villes WHERE id = ?", [$ville_id]);
    if ($ville_data) {
        $params[] = "%" . $ville_data['nom'] . "%";
        $search_title .= " à '" . $ville_data['nom'] . "'";
    }
}

// Build unified query for both local and aggregated jobs
if (empty($conditions)) {
    $sql = "
        SELECT 
            'local' as source_type,
            id, titre as title, description, entreprise as company_name, 
            ville_id, domaine_id, date_publication as posted_date,
            salary_min, salary_max, job_type, experience_level, 
            remote_work, featured, urgent, views_count, applications_count,
            NULL as external_url, NULL as source_name
        FROM annonces 
        WHERE status = 'active'
        
        UNION ALL
        
        SELECT 
            'aggregated' as source_type,
            id, title, description, company_name,
            NULL as ville_id, NULL as domaine_id, posted_date,
            salary_min, salary_max, job_type, experience_level,
            remote_work, 0 as featured, 0 as urgent, 0 as views_count, 0 as applications_count,
            external_url, js.name as source_name
        FROM aggregated_jobs aj
        JOIN job_sources js ON aj.source_id = js.id
        WHERE aj.status = 'active'
        
        ORDER BY featured DESC, urgent DESC, posted_date DESC
    ";
    $search_title = "Toutes les offres d'emploi";
} else {
    $where_clause = implode(' AND ', $conditions);
    $sql = "
        SELECT 
            'local' as source_type,
            id, titre as title, description, entreprise as company_name, 
            ville_id, domaine_id, date_publication as posted_date,
            salary_min, salary_max, job_type, experience_level, 
            remote_work, featured, urgent, views_count, applications_count,
            NULL as external_url, NULL as source_name
        FROM annonces 
        WHERE status = 'active' AND $where_clause
        
        UNION ALL
        
        SELECT 
            'aggregated' as source_type,
            id, title, description, company_name,
            NULL as ville_id, NULL as domaine_id, posted_date,
            salary_min, salary_max, job_type, experience_level,
            remote_work, 0 as featured, 0 as urgent, 0 as views_count, 0 as applications_count,
            external_url, js.name as source_name
        FROM aggregated_jobs aj
        JOIN job_sources js ON aj.source_id = js.id
        WHERE aj.status = 'active' AND $where_clause
        
        ORDER BY featured DESC, urgent DESC, posted_date DESC
    ";
}
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


        <!-- Header End -->
        <div class="container-fluid py-5 bg-dark page-header mb-5">
            <div class="container my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3 animated slideInDown">Recherche d'emploi</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb text-uppercase">
                        <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">Recherche</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Header End -->


        <!-- Start search form -->
        <div class="container-fluid bg-primary mb-5 wow fadeIn" data-wow-delay="0.1s" style="padding: 35px;">
            <div class="container">
                <form action="filtrage.php" method="GET">
                    <div class="row g-2">
                        <div class="col-md-10">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" class="form-control border-0" placeholder="Mot Clé" />
                                </div>
                                <div class="col-md-4">
                                    <select name="idd" class="form-select border-0">
                                        <option value="">Metiers et Domaines</option>
                                        <?php 
                                        $domaines = $db->fetchAll("SELECT * FROM domaines");
                                        foreach($domaines as $datad):
                                            $selected = ($datad['id'] == $domaine_id) ? 'selected' : '';
                                        ?>
                                        <option value="<?= htmlspecialchars($datad['id']) ?>" <?= $selected ?>><?= htmlspecialchars($datad['nom']) ?></option>
                                        <?php endforeach;?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select name="idv" class="form-select border-0">
                                        <option value="">villes</option>
                                        <?php 
                                        $villes = $db->fetchAll("SELECT * FROM villes");
                                        foreach($villes as $datav):
                                            $selected = ($datav['id'] == $ville_id) ? 'selected' : '';
                                        ?>
                                        <option value="<?= htmlspecialchars($datav['id']) ?>" <?= $selected ?>><?= htmlspecialchars($datav['nom']) ?></option>
                                        <?php endforeach;?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark border-0 w-100">Rechercher</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- End search form -->
        
        <!-- Jobs Start -->
        <div class="container-xxl py-5">
            <div class="container">
                <h1 class="text-center mb-5 wow fadeInUp" data-wow-delay="0.1s"><?= htmlspecialchars($search_title) ?></h1>
            
                <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.3s">
                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane fade show p-0 active">
                            <!-- Start Annonce -->
                            <?php
                            try {
                                // Execute the search query
                                $annonces = $db->fetchAll($sql, $params);
                                
                                if (empty($annonces)) {
                                    echo '<div class="text-center py-5">';
                                    echo '<h3 class="text-muted">Aucun résultat trouvé</h3>';
                                    echo '<p class="text-muted">Essayez de modifier vos critères de recherche</p>';
                                    echo '<a href="index.php" class="btn btn-primary">Retour à l\'accueil</a>';
                                    echo '</div>';
                                } else {
                                    echo '<p class="text-center mb-4"><strong>' . count($annonces) . '</strong> offre(s) trouvée(s)</p>';
                                    
                                    foreach($annonces as $data):
                                        // Handle both local and aggregated jobs
                                        if ($data['source_type'] === 'local') {
                                            $profile = $data['profile_id'] ?? null;
                                            $data1 = $profile ? $db->fetch("SELECT * FROM profiles WHERE id = ?", [$profile]) : null;

                                            $contrat = $data['contrat_id'] ?? null;
                                            $data2 = $contrat ? $db->fetch("SELECT * FROM contrats WHERE id = ?", [$contrat]) : null;

                                            $ville = $data['ville_id'];
                                            $data3 = $ville ? $db->fetch("SELECT * FROM villes WHERE id = ?", [$ville]) : null;

                                            $domaine = $data['domaine_id'];
                                            $data4 = $domaine ? $db->fetch("SELECT * FROM domaines WHERE id = ?", [$domaine]) : null;
                                        } else {
                                            // Aggregated job - use direct data
                                            $data1 = null;
                                            $data2 = null;
                                            $data3 = ['nom' => $data['location'] ?? 'Non spécifiée'];
                                            $data4 = ['nom' => 'Autre'];
                                        }
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
                                                    <?= htmlspecialchars($data['title']) ?>
                                                </h5>
                                                <?php if ($data['source_type'] === 'aggregated'): ?>
                                                <span class="badge bg-info">
                                                    <i class="fas fa-external-link-alt"></i> <?= htmlspecialchars($data['source_name']) ?>
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
                                                    Location: <?= htmlspecialchars($data3['nom'] ?: 'Non spécifiée') ?>
                                                </span>
                                                <span class="text-muted small">
                                                    <i class="fas fa-clock text-primary me-1"></i>
                                                    Contrat: <?= htmlspecialchars($data2['nom'] ?: 'Non spécifié') ?>
                                                </span>
                                                <span class="text-muted small">
                                                    <i class="fas fa-money-bill-alt text-primary me-1"></i>
                                                    Salaire: $123 - $456
                                                </span>
                                                <span class="text-muted small">
                                                    <i class="fas fa-user text-primary me-1"></i>
                                                    Domaine: <?= htmlspecialchars($data4['nom']) ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Action Buttons -->
                                        <div class="col-md-3 col-sm-12 text-end">
                                            <div class="d-flex flex-column gap-2">
                                                <!-- Save Button -->
                                                <a href="login.php" class="btn btn-outline-primary btn-sm">
                                                    <i class="far fa-heart"></i>
                                                    Se connecter
                                                </a>
                                                
                                                <!-- View Details Button -->
                                                <?php if ($data['source_type'] === 'local'): ?>
                                                <a href="annoncedetaile.php?ida=<?= $data['id'] ?>" 
                                                   class="btn btn-primary btn-sm">
                                                    Afficher les details
                                                </a>
                                                <?php else: ?>
                                                <a href="<?= htmlspecialchars($data['external_url']) ?>" 
                                                   class="btn btn-primary btn-sm" target="_blank">
                                                    <i class="fas fa-external-link-alt me-1"></i>
                                                    Voir sur <?= htmlspecialchars($data['source_name']) ?>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Date Information -->
                                            <div class="mt-3">
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-calendar text-primary me-1"></i>
                                                    Publié: <?= date('Y-m-d', strtotime($data['posted_date'])) ?>
                                                </small>
                                                <?php if ($data['source_type'] === 'local' && isset($data['date_fin']) && $data['date_fin']): ?>
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
                                echo '<h3 class="text-danger">Erreur lors de la recherche</h3>';
                                echo '<p class="text-muted">Une erreur s\'est produite. Veuillez réessayer.</p>';
                                echo '<a href="index.php" class="btn btn-primary">Retour à l\'accueil</a>';
                                echo '</div>';
                            }
                            ?>
                            <!-- End Annonce -->
                            <a class="btn btn-primary py-3 px-5" href="index.php">Voir plus d'offres</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Jobs End -->

<?php include 'frontoffice/include/footer2.php'; ?>

    <!-- Job Card Styles -->
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
    </style>