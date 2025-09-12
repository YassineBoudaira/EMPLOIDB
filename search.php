
<?php 
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';
include 'frontoffice/include/header2.php'; 

// Get search parameters
$keyword = $_GET['keyword'] ?? '';
$location = $_GET['location'] ?? '';
$domain = $_GET['domain'] ?? '';
$contract = $_GET['contract'] ?? '';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Build search query
$where_conditions = [];
$params = [];

if (!empty($keyword)) {
    $where_conditions[] = "(a.titre LIKE ? OR a.description LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}

if (!empty($location)) {
    $where_conditions[] = "v.nom LIKE ?";
    $params[] = "%$location%";
}

if (!empty($domain)) {
    $where_conditions[] = "a.domaine_id = ?";
    $params[] = $domain;
}

if (!empty($contract)) {
    $where_conditions[] = "a.contrat_id = ?";
    $params[] = $contract;
}

$where_clause = !empty($where_conditions) ? 'WHERE a.status = "active" AND ' . implode(' AND ', $where_conditions) : 'WHERE a.status = "active"';

// Get total count
$count_query = "SELECT COUNT(*) as total FROM annonces a 
                LEFT JOIN villes v ON a.ville_id = v.id 
                LEFT JOIN domaines d ON a.domaine_id = d.id 
                LEFT JOIN contrats c ON a.contrat_id = c.id 
                $where_clause";
$total_jobs = $db->fetch($count_query, $params)['total'];
$total_pages = ceil($total_jobs / $limit);

// Get jobs
$jobs_query = "SELECT a.*, v.nom as ville_nom, d.nom as domaine_nom, c.nom as contrat_nom,
               (SELECT COUNT(*) FROM postulation p WHERE p.annonce_id = a.id) as applications_count
               FROM annonces a 
               LEFT JOIN villes v ON a.ville_id = v.id 
               LEFT JOIN domaines d ON a.domaine_id = d.id 
               LEFT JOIN contrats c ON a.contrat_id = c.id 
               $where_clause
               ORDER BY a.date_a DESC 
               LIMIT $limit OFFSET $offset";
$jobs = $db->fetchAll($jobs_query, $params);

// Get filter options
$domaines = $db->fetchAll("SELECT * FROM domaines ORDER BY nom");
$villes = $db->fetchAll("SELECT * FROM villes ORDER BY nom");
$contrats = $db->fetchAll("SELECT * FROM contrats ORDER BY nom");
?>

<div class="container-fluid bg-white p-0">
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Navigation -->
    <?php include 'frontoffice/include/menu2.php'; ?>

    <!-- Professional Search Header -->
    <div class="container-fluid py-5 mb-5" style="background: var(--emploidb-gradient-secondary);">
        <div class="container my-5 pt-5 pb-4">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <div class="emploidb-animate-fade-in-up">
                        <div class="d-flex justify-content-center mb-4">
                            <div style="width: 80px; height: 80px; background: rgba(255, 255, 255, 0.2); border-radius: var(--emploidb-radius-full); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-search" style="font-size: 2.5rem; color: white;"></i>
                            </div>
                        </div>
                        <h1 class="display-3 text-white mb-4 emploidb-font-black">
                            Recherche d'Emplois
                        </h1>
                        <p class="emploidb-text-xl text-white mb-5" style="opacity: 0.9; line-height: 1.6;">
                            <?php if (!empty($keyword) || !empty($location) || !empty($domain)): ?>
                                Résultats de recherche - <?= number_format($total_jobs) ?> emploi<?= $total_jobs > 1 ? 's' : '' ?> trouvé<?= $total_jobs > 1 ? 's' : '' ?>
                            <?php else: ?>
                                Découvrez toutes les opportunités professionnelles disponibles sur EMPLOIDB
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Search Form -->
    <div class="container mb-5">
        <div class="emploidb-card emploidb-shadow-lg">
            <div class="card-body" style="padding: var(--emploidb-spacing-6);">
                <form method="GET" action="search.php" class="row g-3">
                    <div class="col-md-3">
                        <label class="emploidb-form-label">
                            <i class="fas fa-search me-2" style="color: var(--emploidb-primary);"></i>Mot-clé
                        </label>
                        <input type="text" name="keyword" class="emploidb-form-control" 
                               placeholder="Titre du poste, compétences..." 
                               value="<?= htmlspecialchars($keyword) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="emploidb-form-label">
                            <i class="fas fa-map-marker-alt me-2" style="color: var(--emploidb-secondary);"></i>Localisation
                        </label>
                        <select name="location" class="emploidb-form-control">
                            <option value="">Toutes les villes</option>
                            <?php foreach($villes as $ville): ?>
                                <option value="<?= $ville['nom'] ?>" <?= $location === $ville['nom'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ville['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="emploidb-form-label">
                            <i class="fas fa-briefcase me-2" style="color: var(--emploidb-accent);"></i>Domaine
                        </label>
                        <select name="domain" class="emploidb-form-control">
                            <option value="">Tous domaines</option>
                            <?php foreach($domaines as $dom): ?>
                                <option value="<?= $dom['id'] ?>" <?= $domain == $dom['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dom['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="emploidb-form-label">
                            <i class="fas fa-file-contract me-2" style="color: var(--emploidb-warning);"></i>Contrat
                        </label>
                        <select name="contract" class="emploidb-form-control">
                            <option value="">Tous types</option>
                            <?php foreach($contrats as $cont): ?>
                                <option value="<?= $cont['id'] ?>" <?= $contract == $cont['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cont['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="emploidb-form-label" style="opacity: 0;">Action</label>
                        <button type="submit" class="emploidb-btn emploidb-btn-primary w-100 emploidb-hover-lift">
                            <i class="fas fa-search me-2"></i>Rechercher
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
        <div class="container-fluid bg-primary mb-5 wow fadeIn" data-wow-delay="0.1s" style="padding: 35px;">
            <div class="container">
            <form action="filtrage.php" method="GET">
                <div class="row g-2">
                    <div class="col-md-10">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="keyword" class="form-control border-0" placeholder="Mot Clé" />
                            </div>
                            <div class="col-md-4">
                                <select name="idd" class="form-select border-0">
                                    <option value="">Metiers et Domaines</option>
                                    <?php 
                                    // Use secure database queries with prepared statements
                                    $domaines = $db->fetchAll("SELECT * FROM domaines");
                                    foreach($domaines as $datad):
                                        ?>
                                    <option value="<?= htmlspecialchars($datad['id']) ?>"><?= htmlspecialchars($datad['nom']) ?></option>
                                    <?php endforeach;?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="idv" class="form-select border-0">
                                    <option value="">villes</option>
                                    <?php 
                                    // Use secure database queries with prepared statements
                                    $villes = $db->fetchAll("SELECT * FROM villes");
                                    foreach($villes as $datav):
                                    ?>
                                    <option value="<?= htmlspecialchars($datav['id']) ?>"><?= htmlspecialchars($datav['nom']) ?></option>
                                    <?php endforeach;?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-dark border-0 w-100">Recherche</button>
                    </div>
                </div>
           </form>
            </div>
        </div>
        <!-- Search End -->


        <!-- Category Start -->
      
        <!-- Category End -->


        <!-- About Start -->
        
        <!-- About End -->


        <!-- Jobs Start -->
         <div class="container-xxl py-5">
            <div class="container">
                <h1 class="text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">Liste d'Offres</h1>
                <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.3s">
                    
                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane fade show p-0 active">
                            <!-- Start Annonce -->

                            <?php
					$req =  $bd->query("SELECT * from annonces ORDER BY id DESC ");
					while($data = $req->fetch()):
					

						$profile = $data['profile_id'];
						$req1=  $bd->query("select * from profiles where id=$profile");
						$data1 = $req1->fetch();

						$contrat = $data['contrat_id'];
						$req2 =  $bd->query("select * from contrats where id=$contrat");
						$data2 = $req2->fetch();

						$ville = $data['ville_id'];
						$req3 =  $bd->query("select * from villes where id=$ville");
						$data3 = $req3->fetch();

						$domaine = $data['domaine_id'];
						$req4 =  $bd->query("select * from domaines where id=$domaine");
						$data4 = $req4->fetch();

                        
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
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-calendar text-primary me-1"></i>
                                                    Date Fin: <?= date('Y-m-d', strtotime($data['date_a'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                                endwhile;
                            ?>
                            <!-- End Annonce -->
                            <a class="btn btn-primary py-3 px-5" href="/search.php">Browse More Jobs</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Testimonial Start -->
        
        <!-- Testimonial End -->
        

        
    </div>

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