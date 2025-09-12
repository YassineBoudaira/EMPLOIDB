<?php 
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';
include 'frontoffice/include/header2.php'; 

// Secure input validation
$ann = Security::sanitizeInput($_GET['ida'] ?? '', 'int');

// Validate that the parameter is provided and is an integer
if (!$ann || !Security::validateInt($ann)) {
    Security::redirect('index.php', 'Invalid job ID', 'error');
}

// Get job details with enhanced information
try {
    $job_query = "SELECT a.*, v.nom as ville_nom, d.nom as domaine_nom, c.nom as contrat_nom,
                  e.company_name, e.company_logo, e.company_description, e.website,
                  (SELECT COUNT(*) FROM postulation p WHERE p.annonce_id = a.id) as applications_count,
                  DATEDIFF(CURDATE(), a.date_a) as days_posted
                  FROM annonces a 
                  LEFT JOIN villes v ON a.ville_id = v.id 
                  LEFT JOIN domaines d ON a.domaine_id = d.id 
                  LEFT JOIN contrats c ON a.contrat_id = c.id
                  LEFT JOIN employers e ON a.employer_id = e.id
                  WHERE a.id = ? AND a.status = 'active'";
    $job = $db->fetch($job_query, [$ann]);
    
    if (!$job) {
        Security::redirect('index.php', 'Job not found', 'error');
    }
    
    // Update job views
    $db->query("UPDATE annonces SET views_count = views_count + 1 WHERE id = ?", [$ann]);
    
    // Get similar jobs
    $similar_jobs = $db->fetchAll("SELECT a.*, v.nom as ville_nom, e.company_name 
                                   FROM annonces a 
                                   LEFT JOIN villes v ON a.ville_id = v.id 
                                   LEFT JOIN employers e ON a.employer_id = e.id
                                   WHERE a.domaine_id = ? AND a.id != ? AND a.status = 'active' 
                                   ORDER BY a.date_a DESC LIMIT 3", 
                                   [$job['domaine_id'], $ann]);
    
} catch (Exception $e) {
    Security::redirect('index.php', 'Error loading job details', 'error');
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

    <!-- Navigation -->
    <?php include 'frontoffice/include/menu2.php'; ?>

    <!-- Professional Job Detail Header -->
    <div class="container-fluid py-5 mb-5" style="background: var(--emploidb-gradient-primary);">
        <div class="container my-5 pt-5 pb-4">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="emploidb-animate-fade-in-up text-center">
                        <!-- Company Logo (if available) -->
                        <?php if (!empty($job['company_logo'])): ?>
                            <div class="d-flex justify-content-center mb-4">
                                <div style="width: 100px; height: 100px; background: white; border-radius: var(--emploidb-radius-full); display: flex; align-items: center; justify-content: center; box-shadow: var(--emploidb-shadow-lg);">
                                    <img src="<?= htmlspecialchars($job['company_logo']) ?>" alt="Company Logo" style="max-width: 80px; max-height: 80px; border-radius: var(--emploidb-radius-lg);">
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="d-flex justify-content-center mb-4">
                                <div style="width: 100px; height: 100px; background: rgba(255, 255, 255, 0.2); border-radius: var(--emploidb-radius-full); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-briefcase" style="font-size: 3rem; color: white;"></i>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <h1 class="display-4 text-white mb-3 emploidb-font-black">
                            <?= htmlspecialchars($job['titre']) ?>
                        </h1>
                        
                        <div class="row justify-content-center mb-4">
                            <div class="col-md-8">
                                <div class="d-flex flex-wrap justify-content-center gap-3 text-white">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-building me-2"></i>
                                        <span><?= htmlspecialchars($job['company_name']) ?></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-map-marker-alt me-2"></i>
                                        <span><?= htmlspecialchars($job['ville_nom']) ?></span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar me-2"></i>
                                        <span>Publié il y a <?= $job['days_posted'] ?> jour<?= $job['days_posted'] > 1 ? 's' : '' ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Job Status Badges -->
                        <div class="d-flex justify-content-center gap-3 mb-4">
                            <?php if ($job['urgent']): ?>
                                <span class="badge" style="background: var(--emploidb-error); padding: var(--emploidb-spacing-2) var(--emploidb-spacing-4);">
                                    <i class="fas fa-exclamation-triangle me-1"></i>URGENT
                                </span>
                            <?php endif; ?>
                            <?php if ($job['featured']): ?>
                                <span class="badge" style="background: var(--emploidb-warning); color: var(--emploidb-text-primary); padding: var(--emploidb-spacing-2) var(--emploidb-spacing-4);">
                                    <i class="fas fa-star me-1"></i>EN VEDETTE
                                </span>
                            <?php endif; ?>
                            <span class="badge" style="background: var(--emploidb-success); padding: var(--emploidb-spacing-2) var(--emploidb-spacing-4);">
                                <i class="fas fa-briefcase me-1"></i><?= htmlspecialchars($job['contrat_nom']) ?>
                            </span>
                        </div>
                        
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb justify-content-center" style="background: rgba(255, 255, 255, 0.1); border-radius: var(--emploidb-radius-full); padding: var(--emploidb-spacing-3) var(--emploidb-spacing-6);">
                                <li class="breadcrumb-item">
                                    <a href="index.php" style="color: white; text-decoration: none;">
                                        <i class="fas fa-home me-1"></i>Accueil
                                    </a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="category.php?id=<?= $job['domaine_id'] ?>" style="color: white; text-decoration: none;">
                                        <?= htmlspecialchars($job['domaine_nom']) ?>
                                    </a>
                                </li>
                                <li class="breadcrumb-item text-white active" aria-current="page" style="opacity: 0.8;">
                                    Détail d'offre
                                </li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Professional Quick Stats -->
    <div class="container mb-5">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="emploidb-card text-center emploidb-hover-lift" style="background: var(--emploidb-gradient-secondary); color: white;">
                    <div class="card-body" style="padding: var(--emploidb-spacing-4);">
                        <i class="fas fa-users fa-2x mb-3" style="opacity: 0.8;"></i>
                        <h4 class="emploidb-font-black"><?= number_format($job['applications_count']) ?></h4>
                        <p class="mb-0 small">Candidature<?= $job['applications_count'] > 1 ? 's' : '' ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="emploidb-card text-center emploidb-hover-lift" style="background: var(--emploidb-gradient-accent); color: white;">
                    <div class="card-body" style="padding: var(--emploidb-spacing-4);">
                        <i class="fas fa-eye fa-2x mb-3" style="opacity: 0.8;"></i>
                        <h4 class="emploidb-font-black"><?= number_format($job['views_count'] ?? 0) ?></h4>
                        <p class="mb-0 small">Vue<?= ($job['views_count'] ?? 0) > 1 ? 's' : '' ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="emploidb-card text-center emploidb-hover-lift" style="background: var(--emploidb-gradient-primary); color: white;">
                    <div class="card-body" style="padding: var(--emploidb-spacing-4);">
                        <i class="fas fa-money-bill-wave fa-2x mb-3" style="opacity: 0.8;"></i>
                        <h4 class="emploidb-font-black">
                            <?php if ($job['salary_min'] && $job['salary_max']): ?>
                                <?= number_format($job['salary_min']) ?> - <?= number_format($job['salary_max']) ?>
                            <?php else: ?>
                                À négocier
                            <?php endif; ?>
                        </h4>
                        <p class="mb-0 small">Salaire (MAD)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="emploidb-card text-center emploidb-hover-lift" style="background: var(--emploidb-success); color: white;">
                    <div class="card-body" style="padding: var(--emploidb-spacing-4);">
                        <i class="fas fa-clock fa-2x mb-3" style="opacity: 0.8;"></i>
                        <h4 class="emploidb-font-black"><?= $job['days_posted'] ?></h4>
                        <p class="mb-0 small">Jour<?= $job['days_posted'] > 1 ? 's' : '' ?> depuis publication</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
                </div>
            </div>
        </div>
        <!-- Header End -->
        <?php 
        // Use secure database queries with prepared statements
        $data = $db->fetch("SELECT * FROM annonces WHERE id = ?", [$ann]);
        
        // Check if job exists
        if (!$data) {
            Security::redirect('index.php', 'Job not found', 'error');
        }

        $profile = $data['profile_id'];
        $data1 = $db->fetch("SELECT * FROM profiles WHERE id = ?", [$profile]);

        $contrat = $data['contrat_id'];
        $data2 = $db->fetch("SELECT * FROM contrats WHERE id = ?", [$contrat]);

        $ville = $data['ville_id'];
        $data3 = $db->fetch("SELECT * FROM villes WHERE id = ?", [$ville]);

        $domaine = $data['domaine_id'];
        $data4 = $db->fetch("SELECT * FROM domaines WHERE id = ?", [$domaine]);
        
        ?>

        <!-- Job Detail Start -->
        <div class="container-xxl py-5 wow fadeInUp" data-wow-delay="0.1s">
            <div class="container">
                <div class="row gy-5 gx-4">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center mb-5">
                            <img class="flex-shrink-0 img-fluid border rounded" src="upload/<?= $data['image'] ?>" alt="" style="width: 80px; height: 80px;">
                            <div class="text-start ps-4">
                                <h3 class="mb-3"><?= $data['titre'] ?> </h3>
                                <span class="text-truncate me-3"><i class="fa fa-map-marker-alt text-primary me-2"></i><?= $data3['nom'] ?></span>
                                <span class="text-truncate me-3"><i class="far fa-clock text-primary me-2"></i><?= $data2['nom'] ?></span>
                                <span class="text-truncate me-3"><i class="far fa-money-bill-alt text-primary me-2"></i>$123 - $456</span>
                                <span class="text-truncate me-3"><i class="fa fa-1x fa-user-tie text-primary  me-2"></i> <?= $data4['nom'] ?>   </span>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h4 class="mb-3">Offre description</h4>
                            <p><?= $data['description'] ?></p>
                            <h4 class="mb-3">Responsibilités</h4>
                            <p><?= $data['description'] ?></p>
                            <ul class="list-unstyled">
                                <li><i class="fa fa-angle-right text-primary me-2"></i><?= substr($data['description'],0,70) ?></li>
                                <li><i class="fa fa-angle-right text-primary me-2"></i><?= substr($data['description'],71,141) ?></li>
                                <li><i class="fa fa-angle-right text-primary me-2"></i><?= substr($data['description'],142,213) ?>r</li>
                                <li><i class="fa fa-angle-right text-primary me-2"></i><?= substr($data['description'],214,300) ?></li>
                                <li><i class="fa fa-angle-right text-primary me-2"></i><?= substr($data['description'],301,371) ?></li>
                            </ul>
                            <h4 class="mb-3">Qualifications</h4>
                            <p>Magna et elitr diam sed lorem. Diam diam stet erat no est est. Accusam sed lorem stet voluptua sit sit at stet consetetur, takimata at diam kasd gubergren elitr dolor</p>
                            <ul class="list-unstyled">
                                <li><i class="fa fa-angle-right text-primary me-2"></i>Dolor justo tempor duo ipsum accusam</li>
                                <li><i class="fa fa-angle-right text-primary me-2"></i>Elitr stet dolor vero clita labore gubergren</li>
                                <li><i class="fa fa-angle-right text-primary me-2"></i>Rebum vero dolores dolores elitr</li>
                                <li><i class="fa fa-angle-right text-primary me-2"></i>Est voluptua et sanctus at sanctus erat</li>
                                <li><i class="fa fa-angle-right text-primary me-2"></i>Diam diam stet erat no est est</li>
                            </ul>
                        </div>
        
                        <div class="">
                            <h4 class="mb-4">Postuler à ce poste</h4>
                            <form action="validation.php" method="post" enctype="multipart/form-data" >
                                <input type="hidden" name="annonce_id" value="<?= $ann ?>">
                                <div class="row g-3">
                                    <div class="col-12 col-sm-12">
                                        <input name="nome" type="text" class="form-control" placeholder="Votre Nome et Prenom">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <input name="telephone" type="text" class="form-control" placeholder="Votre Telephone">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <input name="email" type="email" class="form-control" placeholder="Votre Email">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <input name="lien" type="text" class="form-control" placeholder="Portfolio ou Linkden Profile">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <input name="cv" type="file" class="form-control bg-white" placeholder="CV">
                                    </div>
                                    <div class="col-12">
                                        <textarea name="message" class="form-control" rows="5" placeholder="Message"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button name="sub" class="btn btn-primary w-100" type="submit" ?>>Appliquer cette Offre</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
        
                    <div class="col-lg-4">
                        <div class="bg-light rounded p-5 mb-4 wow slideInUp" data-wow-delay="0.1s">
                            <h4 class="mb-4">Résumé du Post</h4>
                            <p><i class="fa fa-angle-right text-primary me-2"></i>Publié a: <?= $data['date_a'] ?></p>
                            <p><i class="fa fa-angle-right text-primary me-2"></i>poste vacant: 123 Position</p>
                            <p><i class="fa fa-angle-right text-primary me-2"></i>Post Nature: <?= $data2['nom'] ?></p>
                            <p><i class="fa fa-angle-right text-primary me-2"></i>Salaire: $123 - $456</p>
                            <p><i class="fa fa-angle-right text-primary me-2"></i>Emplacement: <?= $data3['nom'] ?></p>
                            <p class="m-0"><i class="fa fa-angle-right text-primary me-2"></i>Date de fin d'offre: <?= $data['date_a'] ?></p>
                        </div>
                        <div class="bg-light rounded p-5 wow slideInUp" data-wow-delay="0.1s">
                            <h4 class="mb-4">Détails de l'entreprise</h4>
                            <p class="m-0"> <?= $data['entreprise_detaile'] ?> </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Job Detail End -->


<?php include 'frontoffice/include/footer2.php'; ?>
 