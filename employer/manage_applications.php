<?php
require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../include/sess.php';
require_once __DIR__ . '/../include/connexion.php';
include 'include/menu.php';

// Check if user is logged in and is an employer
if (!Security::isLoggedIn()) {
    header('Location: ../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$user_id = $_SESSION['user_id'];
$employer = $db->fetch("SELECT * FROM employers WHERE user_id = ?", [$user_id]);

if (!$employer) {
    header('Location: ../index.php');
    exit();
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!Security::verifyCSRFToken($csrf_token)) {
        $error = "Erreur de sécurité. Veuillez réessayer.";
    } else {
        try {
            $application_id = (int)$_POST['application_id'];
            $new_status = $_POST['new_status'];
            $employer_notes = trim($_POST['employer_notes'] ?? '');
            $interview_date = $_POST['interview_date'] ?? null;
            $interview_location = trim($_POST['interview_location'] ?? '');
            $salary_offered = $_POST['salary_offered'] ?? null;
            
            // Verify the application belongs to this employer's job
            $application = $db->fetch("SELECT p.*, a.titre as job_title, a.employer_id 
                                      FROM postulation p 
                                      JOIN annonces a ON p.annonce_id = a.id 
                                      WHERE p.id = ? AND a.employer_id = ?", 
                                      [$application_id, $employer['id']]);
            
            if ($application) {
                $update_data = [
                    'status' => $new_status,
                    'employer_notes' => $employer_notes,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                if ($interview_date) {
                    $update_data['interview_date'] = $interview_date;
                }
                if ($interview_location) {
                    $update_data['interview_location'] = $interview_location;
                }
                if ($salary_offered) {
                    $update_data['salary_offered'] = $salary_offered;
                }
                
                $db->query("UPDATE postulation SET " . implode(', ', array_map(function($key) { return "$key = ?"; }, array_keys($update_data))) . " WHERE id = ?", 
                           array_merge(array_values($update_data), [$application_id]));
                
                $success = "Statut de la candidature mis à jour avec succès.";
            } else {
                $error = "Candidature non trouvée.";
            }
        } catch (Exception $e) {
            error_log("Error in manage_applications.php: " . $e->getMessage());
            $error = "Une erreur est survenue. Veuillez réessayer.";
        }
    }
}

// Get filter parameters
$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where_conditions = ["a.employer_id = ?"];
$params = [$employer['id']];

if ($job_id) {
    $where_conditions[] = "a.id = ?";
    $params[] = $job_id;
}

if ($status_filter) {
    $where_conditions[] = "p.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(p.cover_letter LIKE ? OR u.username LIKE ? OR pr.prenom LIKE ? OR pr.nom LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$where_clause = implode(' AND ', $where_conditions);

// Get applications
$applications = $db->fetchAll("SELECT p.*, a.titre as job_title, a.entreprise, u.username, u.email,
                                      pr.prenom, pr.nom, pr.telephone, pr.ville, pr.cv_file
                               FROM postulation p
                               JOIN annonces a ON p.annonce_id = a.id
                               JOIN users u ON p.user_id = u.id
                               LEFT JOIN profiles pr ON u.id = pr.user_id
                               WHERE $where_clause
                               ORDER BY p.applied_at DESC", $params);

// Get employer's jobs for filter
$employer_jobs = $db->fetchAll("SELECT id, titre FROM annonces WHERE employer_id = ? ORDER BY titre", [$employer['id']]);

// Get statistics
$stats = $db->fetch("SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'applied' THEN 1 ELSE 0 END) as applied,
                        SUM(CASE WHEN status = 'viewed' THEN 1 ELSE 0 END) as viewed,
                        SUM(CASE WHEN status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted,
                        SUM(CASE WHEN status = 'interviewed' THEN 1 ELSE 0 END) as interviewed,
                        SUM(CASE WHEN status = 'hired' THEN 1 ELSE 0 END) as hired,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                     FROM postulation p
                     JOIN annonces a ON p.annonce_id = a.id
                     WHERE a.employer_id = ?", [$employer['id']]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gérer les Candidatures | JobMaroc.ma</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="../img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Inter:wght@700;800&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="../lib/animate/animate.min.css" rel="stylesheet">
    <link href="../lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="../css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container-xxl bg-white p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->

        <!-- Navbar Start -->
        <?php include '../frontoffice/include/menu2.php'; ?>
        <!-- Navbar End -->

        <!-- Header Start -->
        <div class="container-fluid bg-primary mb-5 wow fadeIn" data-wow-delay="0.1s" style="padding: 35px;">
            <div class="container">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h1 class="text-white mb-4">Gérer les Candidatures</h1>
                        <p class="text-white fs-5"><?= htmlspecialchars($employer['company_name']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Header End -->

        <!-- Applications Management Start -->
        <div class="container-xxl py-5">
            <div class="container">
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="card text-center bg-primary text-white">
                            <div class="card-body">
                                <h4><?= $stats['total'] ?></h4>
                                <small>Total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="card text-center bg-info text-white">
                            <div class="card-body">
                                <h4><?= $stats['applied'] ?></h4>
                                <small>Nouvelles</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="card text-center bg-warning text-white">
                            <div class="card-body">
                                <h4><?= $stats['shortlisted'] ?></h4>
                                <small>Sélectionnées</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="card text-center bg-success text-white">
                            <div class="card-body">
                                <h4><?= $stats['hired'] ?></h4>
                                <small>Embauchées</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="card text-center bg-danger text-white">
                            <div class="card-body">
                                <h4><?= $stats['rejected'] ?></h4>
                                <small>Refusées</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="card text-center bg-secondary text-white">
                            <div class="card-body">
                                <h4><?= $stats['interviewed'] ?></h4>
                                <small>Entretiens</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Emploi</label>
                                <select name="job_id" class="form-select">
                                    <option value="">Tous les emplois</option>
                                    <?php foreach($employer_jobs as $job): ?>
                                    <option value="<?= $job['id'] ?>" <?= $job_id == $job['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($job['titre']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Statut</label>
                                <select name="status" class="form-select">
                                    <option value="">Tous les statuts</option>
                                    <option value="applied" <?= $status_filter === 'applied' ? 'selected' : '' ?>>Nouvelle</option>
                                    <option value="viewed" <?= $status_filter === 'viewed' ? 'selected' : '' ?>>Vue</option>
                                    <option value="shortlisted" <?= $status_filter === 'shortlisted' ? 'selected' : '' ?>>Sélectionnée</option>
                                    <option value="interviewed" <?= $status_filter === 'interviewed' ? 'selected' : '' ?>>Entretien</option>
                                    <option value="hired" <?= $status_filter === 'hired' ? 'selected' : '' ?>>Embauchée</option>
                                    <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Refusée</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Rechercher</label>
                                <input type="text" name="search" class="form-control" placeholder="Nom, email, lettre de motivation..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filtrer
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Applications List -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Candidatures (<?= count($applications) ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($applications)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
                            <h5 class="mt-3">Aucune candidature trouvée</h5>
                            <p class="text-muted">Aucune candidature ne correspond à vos critères de recherche.</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Candidat</th>
                                        <th>Emploi</th>
                                        <th>Date de candidature</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($applications as $app): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-user-circle text-primary" style="font-size: 2rem;"></i>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0"><?= htmlspecialchars($app['prenom'] . ' ' . $app['nom']) ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($app['email']) ?></small>
                                                    <br>
                                                    <small class="text-muted"><?= htmlspecialchars($app['telephone']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($app['job_title']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($app['entreprise']) ?></small>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y H:i', strtotime($app['applied_at'])) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status_colors = [
                                                'applied' => 'secondary',
                                                'viewed' => 'info',
                                                'shortlisted' => 'warning',
                                                'interviewed' => 'primary',
                                                'hired' => 'success',
                                                'rejected' => 'danger'
                                            ];
                                            $status_labels = [
                                                'applied' => 'Nouvelle',
                                                'viewed' => 'Vue',
                                                'shortlisted' => 'Sélectionnée',
                                                'interviewed' => 'Entretien',
                                                'hired' => 'Embauchée',
                                                'rejected' => 'Refusée'
                                            ];
                                            $color = $status_colors[$app['status']] ?? 'secondary';
                                            $label = $status_labels[$app['status']] ?? $app['status'];
                                            ?>
                                            <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#applicationModal<?= $app['id'] ?>">
                                                    <i class="fas fa-eye"></i> Voir
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#updateModal<?= $app['id'] ?>">
                                                    <i class="fas fa-edit"></i> Mettre à jour
                                                </button>
                                                <?php if ($app['cv_file']): ?>
                                                <a href="../<?= htmlspecialchars($app['cv_file']) ?>" 
                                                   class="btn btn-sm btn-outline-info" target="_blank">
                                                    <i class="fas fa-download"></i> CV
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Applications Management End -->

        <!-- Application Detail Modals -->
        <?php foreach($applications as $app): ?>
        <div class="modal fade" id="applicationModal<?= $app['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Détails de la Candidature</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Informations du Candidat</h6>
                                <p><strong>Nom:</strong> <?= htmlspecialchars($app['prenom'] . ' ' . $app['nom']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($app['email']) ?></p>
                                <p><strong>Téléphone:</strong> <?= htmlspecialchars($app['telephone']) ?></p>
                                <p><strong>Ville:</strong> <?= htmlspecialchars($app['ville']) ?></p>
                                <?php if ($app['cv_file']): ?>
                                <p><strong>CV:</strong> <a href="../<?= htmlspecialchars($app['cv_file']) ?>" target="_blank">Télécharger</a></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6>Informations de Candidature</h6>
                                <p><strong>Emploi:</strong> <?= htmlspecialchars($app['job_title']) ?></p>
                                <p><strong>Date de candidature:</strong> <?= date('d/m/Y H:i', strtotime($app['applied_at'])) ?></p>
                                <p><strong>Statut:</strong> <span class="badge bg-<?= $status_colors[$app['status']] ?>"><?= $status_labels[$app['status']] ?></span></p>
                                <?php if ($app['expected_salary']): ?>
                                <p><strong>Salaire attendu:</strong> <?= number_format($app['expected_salary']) ?> MAD</p>
                                <?php endif; ?>
                                <?php if ($app['availability_date']): ?>
                                <p><strong>Disponibilité:</strong> <?= date('d/m/Y', strtotime($app['availability_date'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <hr>
                        <h6>Lettre de Motivation</h6>
                        <div class="border rounded p-3 bg-light">
                            <?= nl2br(htmlspecialchars($app['cover_letter'])) ?>
                        </div>
                        <?php if ($app['employer_notes']): ?>
                        <hr>
                        <h6>Notes de l'employeur</h6>
                        <div class="border rounded p-3 bg-light">
                            <?= nl2br(htmlspecialchars($app['employer_notes'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Status Modal -->
        <div class="modal fade" id="updateModal<?= $app['id'] ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mettre à jour le Statut</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Nouveau Statut</label>
                                <select name="new_status" class="form-select" required>
                                    <option value="applied" <?= $app['status'] === 'applied' ? 'selected' : '' ?>>Nouvelle</option>
                                    <option value="viewed" <?= $app['status'] === 'viewed' ? 'selected' : '' ?>>Vue</option>
                                    <option value="shortlisted" <?= $app['status'] === 'shortlisted' ? 'selected' : '' ?>>Sélectionnée</option>
                                    <option value="interviewed" <?= $app['status'] === 'interviewed' ? 'selected' : '' ?>>Entretien</option>
                                    <option value="hired" <?= $app['status'] === 'hired' ? 'selected' : '' ?>>Embauchée</option>
                                    <option value="rejected" <?= $app['status'] === 'rejected' ? 'selected' : '' ?>>Refusée</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Notes (optionnel)</label>
                                <textarea name="employer_notes" class="form-control" rows="3" placeholder="Ajoutez des notes sur cette candidature..."><?= htmlspecialchars($app['employer_notes'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Date d'entretien (optionnel)</label>
                                <input type="datetime-local" name="interview_date" class="form-control" value="<?= $app['interview_date'] ?? '' ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Lieu d'entretien (optionnel)</label>
                                <input type="text" name="interview_location" class="form-control" placeholder="Adresse ou plateforme" value="<?= htmlspecialchars($app['interview_location'] ?? '') ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Salaire proposé (optionnel)</label>
                                <input type="number" name="salary_offered" class="form-control" placeholder="MAD" value="<?= $app['salary_offered'] ?? '' ?>">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Footer Start -->
        <?php include '../frontoffice/include/footer2.php'; ?>
        <!-- Footer End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../lib/wow/wow.min.js"></script>
    <script src="../lib/easing/easing.min.js"></script>
    <script src="../lib/waypoints/waypoints.min.js"></script>
    <script src="../lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="../js/main.js"></script>
</body>
</html>
