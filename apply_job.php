<?php
require_once 'include/config.php';
require_once 'include/sess.php';
require_once 'include/connexion.php';

// Check if user is logged in
if (!Security::isLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$job_id) {
    header('Location: index.php');
    exit();
}

// Get job details
$job = $db->fetch("SELECT a.*, d.nom as domaine_nom, v.nom as ville_nom, c.nom as contrat_nom 
                   FROM annonces a 
                   LEFT JOIN domaines d ON a.domaine_id = d.id 
                   LEFT JOIN villes v ON a.ville_id = v.id 
                   LEFT JOIN contrats c ON a.contrat_id = c.id 
                   WHERE a.id = ? AND (a.status = 'active' OR a.status IS NULL)", [$job_id]);

if (!$job) {
    header('Location: index.php');
    exit();
}

// Get user profile
$user_id = $_SESSION['user_id'];
$profile = $db->fetch("SELECT * FROM profiles WHERE user_id = ?", [$user_id]);

// Check if already applied
$existing_application = $db->fetch("SELECT * FROM postulation WHERE user_id = ? AND annonce_id = ?", [$user_id, $job_id]);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!Security::verifyCSRFToken($csrf_token)) {
        $error = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        try {
            // Validate inputs
            $cover_letter = trim($_POST['cover_letter'] ?? '');
            $expected_salary = trim($_POST['expected_salary'] ?? '');
            $availability_date = trim($_POST['availability_date'] ?? '');
            
            if (empty($cover_letter)) {
                $error = "La lettre de motivation est requise.";
            } else {
                // Handle CV upload
                $cv_file_path = null;
                if (isset($_FILES['cv_file']) && $_FILES['cv_file']['error'] === UPLOAD_ERR_OK) {
                    $upload_result = Security::validateFileUpload($_FILES['cv_file'], ['pdf', 'doc', 'docx'], 5 * 1024 * 1024);
                    if ($upload_result['success']) {
                        $filename = Security::generateSecureFilename($_FILES['cv_file']['name'], 'cv_');
                        $upload_path = 'upload/cv/' . $filename;
                        
                        if (!is_dir('upload/cv/')) {
                            mkdir('upload/cv/', 0755, true);
                        }
                        
                        if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $upload_path)) {
                            $cv_file_path = $upload_path;
                        } else {
                            $error = "Erreur lors du téléchargement du CV.";
                        }
                    } else {
                        $error = $upload_result['message'];
                    }
                }
                
                if (!$error) {
                    // Insert application
                    $db->query("INSERT INTO postulation (user_id, annonce_id, cover_letter, expected_salary, availability_date, cv_file, status, applied_at) 
                               VALUES (?, ?, ?, ?, ?, ?, 'applied', NOW())", 
                               [$user_id, $job_id, $cover_letter, $expected_salary, $availability_date, $cv_file_path]);
                    
                    // Update job applications count
                    $db->query("UPDATE annonces SET applications_count = applications_count + 1 WHERE id = ?", [$job_id]);
                    
                    $success = "Votre candidature a été envoyée avec succès!";
                    
                    // Redirect to application tracking
                    header('Location: application_tracking.php?success=1');
                    exit();
                }
            }
        } catch (Exception $e) {
            error_log("Error in apply_job.php: " . $e->getMessage());
            $error = "Une erreur est survenue. Veuillez réessayer.";
        }
    }
}
?>

<?php 
// Set custom title for this page
$page_title = "Postuler - " . htmlspecialchars($job['titre']) . " | JobMaroc.ma";
include 'frontoffice/include/header2.php'; 
?>

        <!-- Enhanced Application Content -->
        <div class="container-xxl py-5">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <!-- Professional Header Section -->
                        <div class="professional-section">
                            <h3><i class="fas fa-paper-plane"></i>Postuler à l'Emploi</h3>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="enhanced-card">
                                        <div class="enhanced-card-header">
                                            <h4 class="enhanced-card-title">Candidature pour <?= htmlspecialchars($job['titre']) ?></h4>
                                            <div class="enhanced-card-actions">
                                                <button class="btn-action btn-action-primary" onclick="window.location.href='enhanced_search.php'">
                                                    <i class="fas fa-search"></i>Rechercher d'autres Emplois
                                                </button>
                                                <button class="btn-action btn-action-success" onclick="window.location.href='saved_jobs.php'">
                                                    <i class="fas fa-heart"></i>Emplois Sauvegardés
                                                </button>
                                            </div>
                                        </div>
                                        <p class="text-muted">Complétez votre candidature avec soin pour maximiser vos chances d'être sélectionné.</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="action-buttons">
                                        <button class="btn-action btn-action-info" onclick="window.location.href='user_profile.php'">
                                            <i class="fas fa-user"></i>Mon Profil
                                        </button>
                                        <button class="btn-action btn-action-warning" onclick="window.location.href='application_tracking.php'">
                                            <i class="fas fa-clipboard-list"></i>Mes Candidatures
                                        </button>
                                        <button class="btn-action btn-action-success" onclick="window.location.href='index.php'">
                                            <i class="fas fa-home"></i>Accueil
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

        <!-- Application Form Start -->
        <div class="container-xxl py-5">
            <div class="container">
                <div class="row">
                    <!-- Job Details -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Détails de l'Emploi</h5>
                            </div>
                            <div class="card-body">
                                <h6><?= htmlspecialchars($job['titre']) ?></h6>
                                <p class="text-muted mb-3"><?= htmlspecialchars($job['entreprise']) ?></p>
                                
                                <div class="mb-3">
                                    <strong><i class="fas fa-map-marker-alt text-primary me-2"></i>Lieu:</strong>
                                    <span><?= htmlspecialchars($job['ville_nom']) ?></span>
                                </div>
                                
                                <div class="mb-3">
                                    <strong><i class="fas fa-tag text-primary me-2"></i>Domaine:</strong>
                                    <span><?= htmlspecialchars($job['domaine_nom']) ?></span>
                                </div>
                                
                                <div class="mb-3">
                                    <strong><i class="fas fa-clock text-primary me-2"></i>Type:</strong>
                                    <span><?= htmlspecialchars(ucfirst($job['job_type'] ?? 'full-time')) ?></span>
                                </div>
                                
                                <?php if ($job['salary_min'] && $job['salary_max']): ?>
                                <div class="mb-3">
                                    <strong><i class="fas fa-money-bill text-primary me-2"></i>Salaire:</strong>
                                    <span><?= number_format($job['salary_min']) ?> - <?= number_format($job['salary_max']) ?> MAD</span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <strong><i class="fas fa-calendar text-primary me-2"></i>Publié le:</strong>
                                    <span><?= date('d/m/Y', strtotime($job['date_a'])) ?></span>
                                </div>
                                
                                <?php if ($existing_application): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Vous avez déjà postulé à cet emploi le <?= date('d/m/Y', strtotime($existing_application['applied_at'])) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Application Form -->
                    <div class="col-lg-8">
                        <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!$existing_application): ?>
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Formulaire de Candidature</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                                    
                                    <!-- Profile Information -->
                                    <div class="mb-4">
                                        <h6><i class="fas fa-user me-2"></i>Informations du Profil</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">Nom complet</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($profile['prenom'] . ' ' . $profile['nom']) ?>" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <label class="form-label">Téléphone</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($profile['telephone'] ?? '') ?>" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Ville</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($profile['ville'] ?? '') ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Cover Letter -->
                                    <div class="mb-4">
                                        <label class="form-label">Lettre de Motivation *</label>
                                        <textarea name="cover_letter" class="form-control" rows="6" placeholder="Présentez-vous et expliquez pourquoi vous êtes le candidat idéal pour ce poste..." required><?= htmlspecialchars($_POST['cover_letter'] ?? '') ?></textarea>
                                        <small class="text-muted">Minimum 100 caractères</small>
                                    </div>
                                    
                                    <!-- Expected Salary -->
                                    <div class="mb-4">
                                        <label class="form-label">Salaire Attendu (MAD)</label>
                                        <input type="number" name="expected_salary" class="form-control" placeholder="Ex: 8000" value="<?= htmlspecialchars($_POST['expected_salary'] ?? '') ?>">
                                        <small class="text-muted">Laissez vide si négociable</small>
                                    </div>
                                    
                                    <!-- Availability Date -->
                                    <div class="mb-4">
                                        <label class="form-label">Date de Disponibilité</label>
                                        <input type="date" name="availability_date" class="form-control" value="<?= htmlspecialchars($_POST['availability_date'] ?? '') ?>">
                                        <small class="text-muted">Quand pouvez-vous commencer ?</small>
                                    </div>
                                    
                                    <!-- CV Upload -->
                                    <div class="mb-4">
                                        <label class="form-label">CV (Optionnel)</label>
                                        <input type="file" name="cv_file" class="form-control" accept=".pdf,.doc,.docx">
                                        <small class="text-muted">Formats acceptés: PDF, DOC, DOCX (max 5MB)</small>
                                        <?php if ($profile['cv_file']): ?>
                                        <div class="mt-2">
                                            <small class="text-info">
                                                <i class="fas fa-info-circle me-1"></i>
                                                CV actuel: <?= htmlspecialchars(basename($profile['cv_file'])) ?>
                                            </small>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Submit Button -->
                                    <div class="text-end">
                                        <a href="enhanced_job_details.php?id=<?= $job_id ?>" class="btn btn-secondary me-2">
                                            <i class="fas fa-arrow-left me-2"></i>Retour
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-2"></i>Envoyer la Candidature
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                                <h4 class="mt-3">Candidature Déjà Envoyée</h4>
                                <p class="text-muted">Vous avez déjà postulé à cet emploi.</p>
                                <div class="mt-3">
                                    <a href="application_tracking.php" class="btn btn-primary">
                                        <i class="fas fa-clipboard-list me-2"></i>Suivre mes Candidatures
                                    </a>
                                    <a href="enhanced_job_details.php?id=<?= $job_id ?>" class="btn btn-outline-secondary ms-2">
                                        <i class="fas fa-arrow-left me-2"></i>Retour à l'Emploi
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Application Form End -->

        <!-- Footer Start -->
        <?php include 'frontoffice/include/footer.php'; ?>
        <!-- Footer End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="frontoffice/assets/lib/wow/wow.min.js"></script>
    <script src="frontoffice/assets/lib/easing/easing.min.js"></script>
    <script src="frontoffice/assets/lib/waypoints/waypoints.min.js"></script>
    <script src="frontoffice/assets/lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="frontoffice/assets/js/main.js"></script>
    
    <style>
        /* Professional Sections */
        .professional-section {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
        }
        
        .professional-section h3 {
            color: var(--dark-color);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .professional-section h3 i {
            color: var(--primary-color);
            margin-right: 15px;
        }
        
        .enhanced-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }
        
        .enhanced-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }
        
        .enhanced-card-title {
            color: var(--dark-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        
        .enhanced-card-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-action-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            color: white;
        }
        
        .btn-action-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
            color: white;
        }
        
        .btn-action-warning {
            background: linear-gradient(135deg, var(--warning-color) 0%, #d97706 100%);
            color: white;
        }
        
        .btn-action-info {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .text-muted {
            color: #64748b !important;
        }
    </style>
    
    <script>
    // Character counter for cover letter
    document.querySelector('textarea[name="cover_letter"]').addEventListener('input', function() {
        const length = this.value.length;
        const minLength = 100;
        
        if (length < minLength) {
            this.style.borderColor = '#dc3545';
        } else {
            this.style.borderColor = '#198754';
        }
    });
    </script>
</body>
</html>
