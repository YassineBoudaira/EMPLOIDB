<?php 
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';
include 'frontoffice/include/header2.php'; 

// Get job ID from URL
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$job_id) {
    header('Location: enhanced_search.php');
    exit();
}

// Fetch job details with all related information
$job = $db->fetch("SELECT a.*, 
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
                   WHERE a.id = ? AND a.status = 'active'", [$job_id]);

if (!$job) {
    header('Location: enhanced_search.php');
    exit();
}

// Track job view
$user_id = Security::isLoggedIn() ? $_SESSION['user_id'] : null;
$ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

try {
    $db->query("INSERT INTO job_views (annonce_id, user_id, ip_address, user_agent) VALUES (?, ?, ?, ?)", 
               [$job_id, $user_id, $ip_address, $user_agent]);
    
    // Update view count
    $db->query("UPDATE annonces SET views_count = views_count + 1 WHERE id = ?", [$job_id]);
} catch (Exception $e) {
    // Ignore view tracking errors
}

// Check if job is saved by current user
$is_saved = false;
if (Security::isLoggedIn()) {
    $saved_job = $db->fetch("SELECT id FROM saved_jobs WHERE user_id = ? AND annonce_id = ?", 
                           [$_SESSION['user_id'], $job_id]);
    $is_saved = $saved_job ? true : false;
}

// Get similar jobs
$similar_jobs = $db->fetchAll("SELECT a.*, d.nom as domaine_nom, v.nom as ville_nom
                               FROM annonces a
                               LEFT JOIN domaines d ON a.domaine_id = d.id
                               LEFT JOIN villes v ON a.ville_id = v.id
                               WHERE a.id != ? AND a.status = 'active' 
                               AND (a.domaine_id = ? OR a.ville_id = ?)
                               ORDER BY a.date_a DESC LIMIT 3", 
                               [$job_id, $job['domaine_id'], $job['ville_id']]);
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

    <!-- Job Details Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Job Header -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h1 class="mb-3"><?= htmlspecialchars($job['titre']) ?></h1>
                                    
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <?php if ($job['urgent']): ?>
                                            <span class="badge bg-danger">Urgent</span>
                                        <?php endif; ?>
                                        <?php if ($job['featured']): ?>
                                            <span class="badge bg-warning">Mis en Avant</span>
                                        <?php endif; ?>
                                        <span class="badge bg-primary"><?= htmlspecialchars($job['domaine_nom']) ?></span>
                                        <span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($job['job_type'] ?? 'full-time')) ?></span>
                                        <span class="badge bg-info"><?= htmlspecialchars(ucfirst($job['remote_work'] ?? 'on-site')) ?></span>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p><i class="fas fa-building text-primary me-2"></i> <strong>Entreprise:</strong> <?= htmlspecialchars($job['entreprise']) ?></p>
                                            <p><i class="fas fa-map-marker-alt text-primary me-2"></i> <strong>Localisation:</strong> <?= htmlspecialchars($job['ville_nom']) ?></p>
                                            <p><i class="far fa-clock text-primary me-2"></i> <strong>Type de Contrat:</strong> <?= htmlspecialchars($job['contrat_nom']) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><i class="far fa-money-bill-alt text-primary me-2"></i> <strong>Salaire:</strong> 
                                                <?php if ($job['salary_min'] && $job['salary_max']): ?>
                                                    <?= number_format($job['salary_min']) ?> - <?= number_format($job['salary_max']) ?> MAD
                                                <?php else: ?>
                                                    À négocier
                                                <?php endif; ?>
                                            </p>
                                            <p><i class="fas fa-calendar-alt text-primary me-2"></i> <strong>Publié le:</strong> <?= date('d/m/Y', strtotime($job['date_a'])) ?></p>
                                            <?php if ($job['date_fin']): ?>
                                                <p><i class="fas fa-calendar-times text-danger me-2"></i> <strong>Expire le:</strong> <?= date('d/m/Y', strtotime($job['date_fin'])) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <img src="upload/<?= htmlspecialchars($job['image']) ?>" alt="Job Image" class="img-fluid rounded" style="max-width: 200px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Description -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4><i class="fas fa-file-alt text-primary me-2"></i>Description du Poste</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <?= nl2br(htmlspecialchars($job['description'])) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Requirements -->
                    <?php if ($job['requirements']): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4><i class="fas fa-list-check text-primary me-2"></i>Exigences et Qualifications</h4>
                        </div>
                        <div class="card-body">
                            <?= nl2br(htmlspecialchars($job['requirements'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Benefits -->
                    <?php if ($job['benefits']): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4><i class="fas fa-gift text-primary me-2"></i>Avantages et Bénéfices</h4>
                        </div>
                        <div class="card-body">
                            <?= nl2br(htmlspecialchars($job['benefits'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Skills Required -->
                    <?php if ($job['skills_required']): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4><i class="fas fa-tools text-primary me-2"></i>Compétences Requises</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-2">
                                <?php 
                                $skills = explode(',', $job['skills_required']);
                                foreach($skills as $skill): 
                                ?>
                                    <span class="badge bg-primary"><?= htmlspecialchars(trim($skill)) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Company Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4><i class="fas fa-building text-primary me-2"></i>À Propos de l'Entreprise</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5><?= htmlspecialchars($job['entreprise']) ?></h5>
                                    <?php if ($job['entreprise_detaile']): ?>
                                        <p><?= htmlspecialchars($job['entreprise_detaile']) ?></p>
                                    <?php endif; ?>
                                    <?php if ($job['siteweb']): ?>
                                        <p><i class="fas fa-globe text-primary me-2"></i> <a href="<?= htmlspecialchars($job['siteweb']) ?>" target="_blank">Site Web</a></p>
                                    <?php endif; ?>
                                    <?php if ($job['email']): ?>
                                        <p><i class="fas fa-envelope text-primary me-2"></i> <a href="mailto:<?= htmlspecialchars($job['email']) ?>"><?= htmlspecialchars($job['email']) ?></a></p>
                                    <?php endif; ?>
                                    <?php if ($job['telephone']): ?>
                                        <p><i class="fas fa-phone text-primary me-2"></i> <a href="tel:<?= htmlspecialchars($job['telephone']) ?>"><?= htmlspecialchars($job['telephone']) ?></a></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 text-end">
                                    <a href="#" class="btn btn-outline-primary">
                                        <i class="fas fa-building me-2"></i>Voir le Profil
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Action Buttons -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <?php if (Security::isLoggedIn()): ?>
                                    <a href="apply_job.php?id=<?= $job_id ?>" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Postuler Maintenant
                                    </a>
                                    <button class="btn btn-outline-primary" onclick="toggleSaveJob(<?= $job_id ?>)" id="saveBtn_<?= $job_id ?>">
                                        <i class="<?= $is_saved ? 'fas fa-heart text-danger' : 'far fa-heart' ?>" id="heartIcon_<?= $job_id ?>"></i>
                                        <?= $is_saved ? 'Retirer des Favoris' : 'Ajouter aux Favoris' ?>
                                    </button>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-sign-in-alt me-2"></i>Se Connecter pour Postuler
                                    </a>
                                    <a href="signup.php" class="btn btn-outline-primary">
                                        <i class="fas fa-user-plus me-2"></i>Créer un Compte
                                    </a>
                                <?php endif; ?>
                                
                                <button class="btn btn-outline-secondary" onclick="shareJob()">
                                    <i class="fas fa-share me-2"></i>Partager
                                </button>
                                
                                <button class="btn btn-outline-danger" onclick="reportJob()">
                                    <i class="fas fa-flag me-2"></i>Signaler
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Job Summary -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle text-primary me-2"></i>Résumé du Poste</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-briefcase text-primary me-2"></i> <strong>Type:</strong> <?= htmlspecialchars(ucfirst($job['job_type'] ?? 'full-time')) ?></li>
                                <li class="mb-2"><i class="fas fa-map-marker-alt text-primary me-2"></i> <strong>Localisation:</strong> <?= htmlspecialchars($job['ville_nom']) ?></li>
                                <li class="mb-2"><i class="fas fa-laptop text-primary me-2"></i> <strong>Mode:</strong> <?= htmlspecialchars(ucfirst($job['remote_work'] ?? 'on-site')) ?></li>
                                <li class="mb-2"><i class="fas fa-user-tie text-primary me-2"></i> <strong>Niveau:</strong> <?= htmlspecialchars(ucfirst($job['experience_level'] ?? 'mid')) ?></li>
                                <li class="mb-2"><i class="fas fa-graduation-cap text-primary me-2"></i> <strong>Éducation:</strong> <?= htmlspecialchars(ucfirst($job['education_level'] ?? 'any')) ?></li>
                                <li class="mb-2"><i class="fas fa-eye text-primary me-2"></i> <strong>Vues:</strong> <?= number_format($job['views_count'] ?? 0) ?></li>
                                <li class="mb-2"><i class="fas fa-users text-primary me-2"></i> <strong>Candidatures:</strong> <?= number_format($job['applications_count'] ?? 0) ?></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Similar Jobs -->
                    <?php if (!empty($similar_jobs)): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-thumbs-up text-primary me-2"></i>Emplois Similaires</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach($similar_jobs as $similar): ?>
                                <div class="border-bottom pb-3 mb-3">
                                    <h6><a href="enhanced_job_details.php?id=<?= $similar['id'] ?>" class="text-decoration-none"><?= htmlspecialchars($similar['titre']) ?></a></h6>
                                    <p class="text-muted small mb-1"><?= htmlspecialchars($similar['entreprise']) ?></p>
                                    <p class="text-muted small mb-1">
                                        <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($similar['ville_nom']) ?> • 
                                        <i class="fas fa-tag me-1"></i><?= htmlspecialchars($similar['domaine_nom']) ?>
                                    </p>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i><?= date('d/m/Y', strtotime($similar['date_a'])) ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- Job Details End -->
</div>

<script>
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
            const saveBtn = document.getElementById('saveBtn_' + jobId);
            
            if (data.saved) {
                heartIcon.className = 'fas fa-heart text-danger';
                saveBtn.innerHTML = '<i class="fas fa-heart text-danger" id="heartIcon_' + jobId + '"></i>Retirer des Favoris';
            } else {
                heartIcon.className = 'far fa-heart';
                saveBtn.innerHTML = '<i class="far fa-heart" id="heartIcon_' + jobId + '"></i>Ajouter aux Favoris';
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

// Share job functionality
function shareJob() {
    if (navigator.share) {
        navigator.share({
            title: '<?= htmlspecialchars($job['titre']) ?>',
            text: 'Découvrez cette offre d\'emploi intéressante',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(function() {
            alert('Lien copié dans le presse-papiers!');
        });
    }
}

// Report job functionality
function reportJob() {
    const reason = prompt('Raison du signalement (spam, contenu inapproprié, etc.):');
    if (reason) {
        // Here you would send the report to the server
        alert('Merci pour votre signalement. Nous l\'examinerons rapidement.');
    }
}

// Print job details
function printJob() {
    window.print();
}
</script>

<?php include 'frontoffice/include/footer2.php'; ?>
