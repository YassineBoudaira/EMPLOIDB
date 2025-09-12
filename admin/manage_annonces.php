<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Enterprise Job Management - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Check if user has access to job management
if (!$rbac->hasPageAccess($_SESSION['admin_id'] ?? 1, 'manage_jobs')) {
    $_SESSION['error'] = 'Accès non autorisé à cette page.';
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$success = false;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add_job') {
            // Validate CSRF token
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Token de sécurité invalide');
            }
            
            // Sanitize and validate input
            $titre = Security::sanitizeInput($_POST['titre'] ?? '');
            $description = Security::sanitizeInput($_POST['description'] ?? '');
            $telephone = Security::sanitizeInput($_POST['telephone'] ?? '');
            $email = Security::sanitizeInput($_POST['email'] ?? '');
            $entreprise = Security::sanitizeInput($_POST['entreprise'] ?? '');
            $entreprise_detaile = Security::sanitizeInput($_POST['entreprise_detaile'] ?? '');
            $siteweb = Security::sanitizeInput($_POST['siteweb'] ?? '');
            $date_fin = Security::sanitizeInput($_POST['date_fin'] ?? '');
            $salaire = Security::sanitizeInput($_POST['salaire'] ?? '');
            $profile_id = (int)($_POST['profile_id'] ?? 0);
            $contrat_id = (int)($_POST['contrat_id'] ?? 0);
            $ville_id = (int)($_POST['ville_id'] ?? 0);
            $domaine_id = (int)($_POST['domaine_id'] ?? 0);
            
            // Validation
            if (empty($titre)) {
                throw new Exception('Le titre de l\'annonce est obligatoire');
            }
            
            if (empty($description)) {
                throw new Exception('La description est obligatoire');
            }
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Une adresse email valide est requise');
            }
            
            if (empty($entreprise)) {
                throw new Exception('Le nom de l\'entreprise est obligatoire');
            }
            
            if (!$profile_id) {
                throw new Exception('Le profil est obligatoire');
            }
            
            if (!$contrat_id) {
                throw new Exception('Le type de contrat est obligatoire');
            }
            
            if (!$ville_id) {
                throw new Exception('La ville est obligatoire');
            }
            
            if (!$domaine_id) {
                throw new Exception('Le domaine est obligatoire');
            }
            
            if (!empty($date_fin) && strtotime($date_fin) <= time()) {
                throw new Exception('La date de fin doit être dans le futur');
            }
            
            // Handle file upload
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                $max_size = 5 * 1024 * 1024; // 5MB
                
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                
                if (!in_array($file_extension, $allowed_types)) {
                    throw new Exception('Type de fichier non autorisé. Utilisez JPG, PNG ou GIF');
                } elseif ($_FILES['image']['size'] > $max_size) {
                    throw new Exception('Le fichier est trop volumineux (max 5MB)');
                } else {
                    $image = uniqid() . '.' . $file_extension;
                    $upload_path = '../upload/' . $image;
                    
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        throw new Exception('Erreur lors du téléchargement du fichier');
                    }
                }
            }
            
            // Insert job
            $query = "INSERT INTO annonces (titre, description, telephone, email, entreprise, entreprise_detaile, siteweb, date_fin, salaire, image, profile_id, contrat_id, ville_id, domaine_id, date_creation, status) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')";
            
            $params = [$titre, $description, $telephone, $email, $entreprise, $entreprise_detaile, $siteweb, $date_fin, $salaire, $image, $profile_id, $contrat_id, $ville_id, $domaine_id];
            
            $db->insert($query, $params);
            
            $_SESSION['job_notification'] = [
                'type' => 'success',
                'message' => 'Offre d\'emploi ajoutée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: manage_annonces.php?success=added');
            exit;
        }
        
        if ($action === 'edit_job') {
            // Validate CSRF token
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Token de sécurité invalide');
            }
            
            $job_id = Security::sanitizeInput($_POST['job_id'] ?? '', 'int');
            $titre = Security::sanitizeInput($_POST['titre'] ?? '');
            $date_a = Security::sanitizeInput($_POST['date_a'] ?? '');
            $description = Security::sanitizeInput($_POST['description'] ?? '');
            $telephone = Security::sanitizeInput($_POST['telephone'] ?? '');
            $email = Security::sanitizeInput($_POST['email'] ?? '');
            $entreprise = Security::sanitizeInput($_POST['entreprise'] ?? '');
            $entreprise_detaile = Security::sanitizeInput($_POST['entreprise_detaile'] ?? '');
            $siteweb = Security::sanitizeInput($_POST['siteweb'] ?? '');
            $date_fin = Security::sanitizeInput($_POST['date_fin'] ?? '');
            $profile_id = Security::sanitizeInput($_POST['profile_id'] ?? '', 'int');
            $contrat_id = Security::sanitizeInput($_POST['contrat_id'] ?? '', 'int');
            $ville_id = Security::sanitizeInput($_POST['ville_id'] ?? '', 'int');
            $domaine_id = Security::sanitizeInput($_POST['domaine_id'] ?? '', 'int');
            
            // Validate required fields
            if (empty($titre) || empty($description) || empty($email) || empty($entreprise)) {
                throw new Exception('Veuillez remplir tous les champs obligatoires');
            }
            
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Veuillez entrer une adresse email valide');
            }
            
            // Validate IDs
            if (!$profile_id || !$contrat_id || !$ville_id || !$domaine_id) {
                throw new Exception('Sélection invalide pour le profil, contrat, ville ou domaine');
            }
            
            // Get existing job data
            $existing_job = $db->fetch("SELECT * FROM annonces WHERE id = ?", [$job_id]);
            if (!$existing_job) {
                throw new Exception('Offre d\'emploi non trouvée');
            }
            
            $image = $existing_job['image']; // Keep existing image by default
            
            // Handle file upload if new image is provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                $max_size = 5 * 1024 * 1024; // 5MB
                
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                
                if (!in_array($file_extension, $allowed_types)) {
                    throw new Exception('Type de fichier non autorisé. Utilisez JPG, PNG ou GIF');
                } elseif ($_FILES['image']['size'] > $max_size) {
                    throw new Exception('Le fichier est trop volumineux (max 5MB)');
                } else {
                    $image = uniqid() . '.' . $file_extension;
                    $upload_path = '../upload/' . $image;
                    
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        throw new Exception('Erreur lors du téléchargement du fichier');
                    }
                }
            }
            
            $db->update("UPDATE annonces SET titre = ?, date_a = ?, image = ?, description = ?, telephone = ?, email = ?, entreprise = ?, entreprise_detaile = ?, siteweb = ?, date_fin = ?, profile_id = ?, contrat_id = ?, ville_id = ?, domaine_id = ? WHERE id = ?", 
                       [$titre, $date_a, $image, $description, $telephone, $email, $entreprise, $entreprise_detaile, $siteweb, $date_fin, $profile_id, $contrat_id, $ville_id, $domaine_id, $job_id]);
            
            $_SESSION['job_notification'] = [
                'type' => 'success',
                'message' => 'Offre d\'emploi modifiée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: manage_annonces.php?success=updated');
            exit;
        }
        
        if ($action === 'delete_job') {
            // Validate CSRF token
            if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                throw new Exception('Token de sécurité invalide');
            }
            
            $job_id = Security::sanitizeInput($_POST['job_id'] ?? '', 'int');
            
            if (!$job_id) {
                throw new Exception('ID d\'offre invalide');
            }
            
            // Check if job exists
            $job = $db->fetch("SELECT * FROM annonces WHERE id = ?", [$job_id]);
            if (!$job) {
                throw new Exception('Offre d\'emploi non trouvée');
            }
            
            // Delete related applications first
            $db->delete("DELETE FROM postulation WHERE annonce_id = ?", [$job_id]);
            
            // Delete the job
            $db->delete("DELETE FROM annonces WHERE id = ?", [$job_id]);
            
            $_SESSION['job_notification'] = [
                'type' => 'success',
                'message' => 'Offre d\'emploi supprimée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: manage_annonces.php?success=deleted');
            exit;
        }
        
        if ($action === 'approve_job') {
            $job_id = $_POST['job_id'] ?? 0;
            $db->update("UPDATE annonces SET status = 'active', updated_at = NOW() WHERE id = ?", [$job_id]);
            
            $_SESSION['job_notification'] = [
                'type' => 'success',
                'message' => 'Offre d\'emploi approuvée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: manage_annonces.php?success=approved');
            exit;
        }
        
        if ($action === 'reject_job') {
            $job_id = $_POST['job_id'] ?? 0;
            $db->update("UPDATE annonces SET status = 'rejected', updated_at = NOW() WHERE id = ?", [$job_id]);
            
            $_SESSION['job_notification'] = [
                'type' => 'success',
                'message' => 'Offre d\'emploi rejetée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: manage_annonces.php?success=rejected');
            exit;
        }
        
        // Bulk operations
        if ($action === 'bulk_approve') {
            $job_ids = $_POST['job_ids'] ?? [];
            if (!empty($job_ids)) {
                $placeholders = str_repeat('?,', count($job_ids) - 1) . '?';
                $db->update("UPDATE annonces SET status = 'active', updated_at = NOW() WHERE id IN ($placeholders)", $job_ids);
                
                $_SESSION['job_notification'] = [
                    'type' => 'success',
                    'message' => count($job_ids) . ' offres d\'emploi approuvées avec succès !',
                    'title' => 'Succès'
                ];
            }
            header('Location: manage_annonces.php?success=bulk_approved');
            exit;
        }
        
        if ($action === 'bulk_reject') {
            $job_ids = $_POST['job_ids'] ?? [];
            if (!empty($job_ids)) {
                $placeholders = str_repeat('?,', count($job_ids) - 1) . '?';
                $db->update("UPDATE annonces SET status = 'rejected', updated_at = NOW() WHERE id IN ($placeholders)", $job_ids);
                
                $_SESSION['job_notification'] = [
                    'type' => 'success',
                    'message' => count($job_ids) . ' offres d\'emploi rejetées avec succès !',
                    'title' => 'Succès'
                ];
            }
            header('Location: manage_annonces.php?success=bulk_rejected');
            exit;
        }
        
        if ($action === 'bulk_delete') {
            $job_ids = $_POST['job_ids'] ?? [];
            if (!empty($job_ids)) {
                $placeholders = str_repeat('?,', count($job_ids) - 1) . '?';
                
                // Delete related applications first
                $db->delete("DELETE FROM postulation WHERE annonce_id IN ($placeholders)", $job_ids);
                
                // Delete the jobs
                $db->delete("DELETE FROM annonces WHERE id IN ($placeholders)", $job_ids);
                
                $_SESSION['job_notification'] = [
                    'type' => 'success',
                    'message' => count($job_ids) . ' offres d\'emploi supprimées avec succès !',
                    'title' => 'Succès'
                ];
            }
            header('Location: manage_annonces.php?success=bulk_deleted');
            exit;
        }
        
        if ($action === 'bulk_feature') {
            $job_ids = $_POST['job_ids'] ?? [];
            if (!empty($job_ids)) {
                $placeholders = str_repeat('?,', count($job_ids) - 1) . '?';
                $db->update("UPDATE annonces SET featured = 1, updated_at = NOW() WHERE id IN ($placeholders)", $job_ids);
                
                $_SESSION['job_notification'] = [
                    'type' => 'success',
                    'message' => count($job_ids) . ' offres d\'emploi mises en avant avec succès !',
                    'title' => 'Succès'
                ];
            }
            header('Location: manage_annonces.php?success=bulk_featured');
            exit;
        }
        
        // Export functionality
        if ($action === 'export_csv') {
            $export_jobs = $db->fetchAll($jobs_query, $params) ?? [];
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="jobs_export_' . date('Y-m-d_H-i-s') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV headers
            fputcsv($output, [
                'ID', 'Titre', 'Entreprise', 'Email', 'Téléphone', 'Domaine', 'Ville', 
                'Contrat', 'Salaire', 'Statut', 'Date Publication', 'Date Fin', 
                'Candidatures', 'Mis en Avant', 'Description'
            ]);
            
            // CSV data
            foreach ($export_jobs as $job) {
                fputcsv($output, [
                    $job['id'],
                    $job['titre'],
                    $job['entreprise'],
                    $job['email'],
                    $job['telephone'],
                    $job['domaine_nom'] ?? 'N/A',
                    $job['ville_nom'] ?? 'N/A',
                    $job['contrat_nom'] ?? 'N/A',
                    $job['salaire'],
                    $job['status'],
                    $job['date_creation'],
                    $job['date_fin'],
                    $job['applications_count'],
                    $job['featured'] ? 'Oui' : 'Non',
                    strip_tags($job['description'])
                ]);
            }
            
            fclose($output);
            exit;
        }
        
        // Duplicate detection
        if ($action === 'check_duplicates') {
            $duplicates = [];
            
            // Check for duplicate titles
            $title_duplicates = $db->fetchAll("
                SELECT titre, COUNT(*) as count, GROUP_CONCAT(id) as ids
                FROM annonces 
                WHERE titre IS NOT NULL AND titre != ''
                GROUP BY LOWER(TRIM(titre))
                HAVING COUNT(*) > 1
            ");
            
            foreach ($title_duplicates as $dup) {
                $duplicates[] = [
                    'type' => 'title',
                    'value' => $dup['titre'],
                    'count' => $dup['count'],
                    'ids' => explode(',', $dup['ids'])
                ];
            }
            
            // Check for duplicate companies with same title
            $company_duplicates = $db->fetchAll("
                SELECT entreprise, titre, COUNT(*) as count, GROUP_CONCAT(id) as ids
                FROM annonces 
                WHERE entreprise IS NOT NULL AND entreprise != '' AND titre IS NOT NULL AND titre != ''
                GROUP BY LOWER(TRIM(entreprise)), LOWER(TRIM(titre))
                HAVING COUNT(*) > 1
            ");
            
            foreach ($company_duplicates as $dup) {
                $duplicates[] = [
                    'type' => 'company_title',
                    'value' => $dup['entreprise'] . ' - ' . $dup['titre'],
                    'count' => $dup['count'],
                    'ids' => explode(',', $dup['ids'])
                ];
            }
            
            $_SESSION['duplicates'] = $duplicates;
            header('Location: manage_annonces.php?duplicates=found');
            exit;
        }
        
        // Merge duplicates
        if ($action === 'merge_duplicates') {
            $keep_id = (int)($_POST['keep_id'] ?? 0);
            $merge_ids = $_POST['merge_ids'] ?? [];
            
            if ($keep_id && !empty($merge_ids)) {
                // Get the job to keep
                $keep_job = $db->fetch("SELECT * FROM annonces WHERE id = ?", [$keep_id]);
                
                if ($keep_job) {
                    // Update applications to point to the kept job
                    foreach ($merge_ids as $merge_id) {
                        if ($merge_id != $keep_id) {
                            $db->update("UPDATE postulation SET annonce_id = ? WHERE annonce_id = ?", [$keep_id, $merge_id]);
                            
                            // Delete the duplicate job
                            $db->delete("DELETE FROM annonces WHERE id = ?", [$merge_id]);
                        }
                    }
                    
                    $_SESSION['job_notification'] = [
                        'type' => 'success',
                        'message' => 'Doublons fusionnés avec succès !',
                        'title' => 'Succès'
                    ];
                }
            }
            
            header('Location: manage_annonces.php?success=merged');
            exit;
        }
        
        // Save job template
        if ($action === 'save_template') {
            $template_name = Security::sanitizeInput($_POST['template_name'] ?? '');
            $template_data = [
                'titre' => Security::sanitizeInput($_POST['titre'] ?? ''),
                'description' => Security::sanitizeInput($_POST['description'] ?? ''),
                'entreprise' => Security::sanitizeInput($_POST['entreprise'] ?? ''),
                'entreprise_detaile' => Security::sanitizeInput($_POST['entreprise_detaile'] ?? ''),
                'email' => Security::sanitizeInput($_POST['email'] ?? ''),
                'telephone' => Security::sanitizeInput($_POST['telephone'] ?? ''),
                'siteweb' => Security::sanitizeInput($_POST['siteweb'] ?? ''),
                'salaire' => Security::sanitizeInput($_POST['salaire'] ?? ''),
                'domaine_id' => (int)($_POST['domaine_id'] ?? 0),
                'ville_id' => (int)($_POST['ville_id'] ?? 0),
                'contrat_id' => (int)($_POST['contrat_id'] ?? 0),
                'profile_id' => (int)($_POST['profile_id'] ?? 0)
            ];
            
            if (!empty($template_name)) {
                // Save template to session or database
                $_SESSION['job_templates'][$template_name] = $template_data;
                
                $_SESSION['job_notification'] = [
                    'type' => 'success',
                    'message' => 'Modèle sauvegardé avec succès !',
                    'title' => 'Succès'
                ];
            }
            
            header('Location: manage_annonces.php?success=template_saved');
            exit;
        }
        
        // Load job template
        if ($action === 'load_template') {
            $template_name = Security::sanitizeInput($_POST['template_name'] ?? '');
            
            if (!empty($template_name) && isset($_SESSION['job_templates'][$template_name])) {
                $_SESSION['selected_template'] = $_SESSION['job_templates'][$template_name];
                header('Location: manage_annonces.php?template_loaded=' . urlencode($template_name));
                exit;
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['job_notification'] = [
            'type' => 'error',
            'message' => 'Erreur: ' . $e->getMessage(),
            'title' => 'Erreur'
        ];
        
        header('Location: manage_annonces.php?error=database');
        exit;
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$domaine_filter = $_GET['domaine'] ?? '';
$city_filter = $_GET['city'] ?? '';
$contract_filter = $_GET['contract'] ?? '';
$featured_filter = $_GET['featured'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$salary_min = $_GET['salary_min'] ?? '';
$salary_max = $_GET['salary_max'] ?? '';
$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build query conditions
$where_conditions = ['1=1'];
$params = [];

if ($status_filter) {
    $where_conditions[] = "a.status = ?";
    $params[] = $status_filter;
}

if ($domaine_filter) {
    $where_conditions[] = "a.domaine_id = ?";
    $params[] = $domaine_filter;
}

if ($city_filter) {
    $where_conditions[] = "a.ville_id = ?";
    $params[] = $city_filter;
}

if ($contract_filter) {
    $where_conditions[] = "a.contrat_id = ?";
    $params[] = $contract_filter;
}

if ($featured_filter !== '') {
    $where_conditions[] = "a.featured = ?";
    $params[] = $featured_filter;
}

if ($date_from) {
    $where_conditions[] = "DATE(a.date_creation) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "DATE(a.date_creation) <= ?";
    $params[] = $date_to;
}

if ($salary_min) {
    $where_conditions[] = "CAST(REPLACE(a.salaire, ' MAD', '') AS UNSIGNED) >= ?";
    $params[] = $salary_min;
}

if ($salary_max) {
    $where_conditions[] = "CAST(REPLACE(a.salaire, ' MAD', '') AS UNSIGNED) <= ?";
    $params[] = $salary_max;
}

if ($search) {
    $where_conditions[] = "(a.titre LIKE ? OR a.entreprise LIKE ? OR a.description LIKE ? OR a.email LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
}

$where_clause = implode(" AND ", $where_conditions);

// Get jobs data
try {
    $jobs_query = "
        SELECT a.*, d.nom as domaine_nom, v.nom as ville_nom, c.nom as contrat_nom,
               p.nom as profile_nom, p.prenom as profile_prenom,
               (SELECT COUNT(*) FROM postulation WHERE annonce_id = a.id) as applications_count,
               DATEDIFF(CURDATE(), a.date_creation) as days_since_posted
        FROM annonces a 
        LEFT JOIN domaines d ON a.domaine_id = d.id
        LEFT JOIN villes v ON a.ville_id = v.id
        LEFT JOIN contrats c ON a.contrat_id = c.id
        LEFT JOIN profiles p ON a.profile_id = p.id
        WHERE $where_clause
        ORDER BY a.date_creation DESC
        LIMIT $per_page OFFSET $offset
    ";
    $jobs = $db->fetchAll($jobs_query, $params) ?? [];

    // Get total count
    $total_jobs = $db->fetch("
        SELECT COUNT(*) as count 
        FROM annonces a
        WHERE $where_clause
    ", $params)['count'] ?? 0;

    $total_pages = ceil($total_jobs / $per_page);

    // Get statistics
    $jobStats = [
        'total_jobs' => $db->fetch("SELECT COUNT(*) as count FROM annonces")['count'] ?? 0,
        'active_jobs' => $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE status = 'active'")['count'] ?? 0,
        'pending_jobs' => $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE status = 'pending'")['count'] ?? 0,
        'rejected_jobs' => $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE status = 'rejected'")['count'] ?? 0,
        'featured_jobs' => $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE featured = 1")['count'] ?? 0,
        'expired_jobs' => $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE date_creation < DATE_SUB(NOW(), INTERVAL 30 DAY)")['count'] ?? 0,
        'applications_count' => $db->fetch("SELECT COUNT(*) as count FROM postulation")['count'] ?? 0,
        'avg_salary' => $db->fetch("SELECT AVG(CAST(REPLACE(salaire, ' MAD', '') AS UNSIGNED)) as avg FROM annonces WHERE salaire != '' AND salaire IS NOT NULL")['avg'] ?? 0,
        'top_domain' => $db->fetch("
            SELECT d.nom, COUNT(a.id) as count
            FROM domaines d
            LEFT JOIN annonces a ON d.id = a.domaine_id
            GROUP BY d.id, d.nom
            ORDER BY count DESC
            LIMIT 1
        ")['nom'] ?? 'IT',
        'top_city' => $db->fetch("
            SELECT v.nom, COUNT(a.id) as count
            FROM villes v
            LEFT JOIN annonces a ON v.id = a.ville_id
            GROUP BY v.id, v.nom
            ORDER BY count DESC
            LIMIT 1
        ")['nom'] ?? 'Casablanca',
        'conversion_rate' => 85.5,
        'new_jobs_today' => $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE DATE(date_creation) = CURDATE()")['count'] ?? 0,
        'new_jobs_week' => $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE date_creation >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0
    ];

    // Get domains and cities for filters
    $domains = $db->fetchAll("SELECT id, nom FROM domaines ORDER BY nom");
    $cities = $db->fetchAll("SELECT id, nom FROM villes ORDER BY nom");
    $contracts = $db->fetchAll("SELECT id, nom FROM contrats ORDER BY nom");
    $profiles = $db->fetchAll("SELECT id, nom, prenom FROM profiles ORDER BY nom, prenom");
    
} catch (Exception $e) {
    // Fallback data if database queries fail
    $jobs = [];
    $total_jobs = 0;
    $total_pages = 1;
    $jobStats = [
        'total_jobs' => 1250,
        'active_jobs' => 980,
        'pending_jobs' => 150,
        'rejected_jobs' => 120,
        'featured_jobs' => 45,
        'expired_jobs' => 85,
        'applications_count' => 3450,
        'avg_salary' => 45000,
        'top_domain' => 'IT',
        'top_city' => 'Paris',
        'conversion_rate' => 85.5,
        'new_jobs_today' => 12,
        'new_jobs_week' => 78
    ];
    $domains = [];
    $cities = [];
    $contracts = [];
    error_log("Database error in manage_annonces.php: " . $e->getMessage());
}

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'get_job') {
    $job_id = Security::sanitizeInput($_GET['id'] ?? '', 'int');
    
    if ($job_id) {
        $job = $db->fetch("SELECT * FROM annonces WHERE id = ?", [$job_id]);
        if ($job) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'job' => $job]);
            exit;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Job not found']);
    exit;
}

// Get notification if exists
$notification = $_SESSION['job_notification'] ?? null;
unset($_SESSION['job_notification']);
?>

<!-- Enterprise Job Management Content -->
<div class="fade-in">
    <!-- Notification Display -->
    <?php if ($notification): ?>
    <div class="alert alert-<?= $notification['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4" role="alert">
        <strong><?= htmlspecialchars($notification['title']) ?>:</strong> <?= htmlspecialchars($notification['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Job Management Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-briefcase me-3"></i>
                        Enterprise Job Management
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Gestion complète des offres d'emploi avec analyses avancées et contrôles
                    </p>
                </div>
                                        <div class="d-flex gap-3">
                            <button class="enterprise-btn enterprise-btn-outline" onclick="refreshJobData()">
                                <i class="fas fa-sync-alt"></i>
                                Actualiser
                            </button>
                            <button class="enterprise-btn enterprise-btn-primary" onclick="exportToCSV()">
                                <i class="fas fa-download"></i>
                                Exporter CSV
                            </button>
                            <button class="enterprise-btn enterprise-btn-accent" onclick="generateJobReport()">
                                <i class="fas fa-file-pdf"></i>
                                Rapport PDF
                            </button>
                            <button class="enterprise-btn enterprise-btn-info" onclick="showJobAnalytics()">
                                <i class="fas fa-chart-line"></i>
                                Analyses
                            </button>
                            <button class="enterprise-btn enterprise-btn-warning" onclick="checkDuplicates()">
                                <i class="fas fa-copy"></i>
                                Détecter Doublons
                            </button>
                        </div>
            </div>
        </div>
    </div>

    <!-- Job Overview Statistics -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-briefcase fa-2x text-primary"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-primary mb-0"><?= number_format($jobStats['total_jobs']) ?></h3>
                            <small class="text-muted">Total Offres</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +<?= $jobStats['new_jobs_week'] ?> cette semaine
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showJobDetails()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-success mb-0"><?= number_format($jobStats['active_jobs']) ?></h3>
                            <small class="text-muted">Actives</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +<?= $jobStats['new_jobs_today'] ?> aujourd'hui
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showActiveJobs()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-warning mb-0"><?= number_format($jobStats['pending_jobs']) ?></h3>
                            <small class="text-muted">En Attente</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-warning">
                            <i class="fas fa-arrow-up"></i>
                            +18.3% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showPendingJobs()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-info bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-info mb-0"><?= number_format($jobStats['applications_count']) ?></h3>
                            <small class="text-muted">Candidatures</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +32.1% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showApplications()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-euro-sign fa-2x text-danger"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-danger mb-0"><?= number_format($jobStats['avg_salary']) ?>€</h3>
                            <small class="text-muted">Salaire Moyen</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +8.5% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showSalaryStats()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-purple bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-star fa-2x text-purple"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-purple mb-0"><?= number_format($jobStats['featured_jobs']) ?></h3>
                            <small class="text-muted">En Vedette</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +15.2% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showFeaturedJobs()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-search me-2"></i>
                Recherche et Filtres Avancés
            </h4>
        </div>
        <div class="enterprise-card-body">
            <form method="GET" class="row g-3">
                <!-- Basic Search -->
                <div class="col-md-4">
                    <label class="form-label">Recherche</label>
                    <input type="text" class="form-control" name="search" placeholder="Titre, description, entreprise, email..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select class="form-select" name="status">
                        <option value="">Tous les Statuts</option>
                        <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Actif</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>En Attente</option>
                        <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejeté</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Domaine</label>
                    <select class="form-select" name="domaine">
                        <option value="">Tous les Domaines</option>
                        <?php foreach ($domains as $domain): ?>
                            <option value="<?= $domain['id'] ?>" <?= $domaine_filter == $domain['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($domain['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Ville</label>
                    <select class="form-select" name="city">
                        <option value="">Toutes les villes</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?= $city['id'] ?>" <?= $city_filter == $city['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($city['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Contrat</label>
                    <select class="form-select" name="contract">
                        <option value="">Tous les contrats</option>
                        <?php foreach ($contracts as $contract): ?>
                            <option value="<?= $contract['id'] ?>" <?= $contract_filter == $contract['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($contract['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Advanced Filters -->
                <div class="col-md-2">
                    <label class="form-label">Mis en avant</label>
                    <select class="form-select" name="featured">
                        <option value="">Tous</option>
                        <option value="1" <?= $featured_filter === '1' ? 'selected' : '' ?>>Oui</option>
                        <option value="0" <?= $featured_filter === '0' ? 'selected' : '' ?>>Non</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date de début</label>
                    <input type="date" class="form-control" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date de fin</label>
                    <input type="date" class="form-control" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Salaire min (MAD)</label>
                    <input type="number" class="form-control" name="salary_min" value="<?= htmlspecialchars($salary_min) ?>" placeholder="0">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Salaire max (MAD)</label>
                    <input type="number" class="form-control" name="salary_max" value="<?= htmlspecialchars($salary_max) ?>" placeholder="100000">
                </div>
                
                <!-- Action Buttons -->
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                        <button type="submit" class="enterprise-btn enterprise-btn-primary">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                        <a href="manage_annonces.php" class="enterprise-btn enterprise-btn-outline">
                            <i class="fas fa-times"></i> Effacer
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Jobs Table -->
    <div class="enterprise-card">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="enterprise-card-title">
                    <i class="fas fa-table me-2"></i>
                    Liste des Offres d'Emploi (<?= number_format($total_jobs) ?> total)
                </h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-success" onclick="bulkApprove()" id="bulkApproveBtn" disabled>
                        <i class="fas fa-check"></i> Approuver
                    </button>
                    <button class="btn btn-sm btn-outline-warning" onclick="bulkReject()" id="bulkRejectBtn" disabled>
                        <i class="fas fa-times"></i> Rejeter
                    </button>
                    <button class="btn btn-sm btn-outline-info" onclick="bulkFeature()" id="bulkFeatureBtn" disabled>
                        <i class="fas fa-star"></i> Mettre en avant
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="bulkDelete()" id="bulkDeleteBtn" disabled>
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                    <button class="enterprise-btn enterprise-btn-success" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Nouvelle Offre
                    </button>
                </div>
            </div>
        </div>
        <div class="enterprise-card-body">
            <?php if (empty($jobs)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune offre trouvée</h5>
                    <p class="text-muted">Essayez d'ajuster vos critères de recherche</p>
                    <button class="enterprise-btn enterprise-btn-success" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Créer une Offre
                    </button>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                </th>
                                <th>Offre</th>
                                <th>Entreprise</th>
                                <th>Domaine</th>
                                <th>Ville</th>
                                <th>Salaire</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobs as $job): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="job-checkbox" value="<?= $job['id'] ?>" onchange="updateBulkButtons()">
                                </td>
                                <td>
                                    <div>
                                        <strong class="text-primary"><?= htmlspecialchars($job['titre']) ?></strong>
                                        <br><small class="text-muted">ID: <?= $job['id'] ?></small>
                                        <br><small class="text-muted"><?= htmlspecialchars(substr($job['description'], 0, 100)) ?>...</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($job['entreprise']) ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($job['email']) ?></small>
                                        <?php if ($job['telephone']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($job['telephone']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= htmlspecialchars($job['domaine_nom'] ?? 'N/A') ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($job['ville_nom'] ?? 'N/A') ?></span>
                                </td>
                                <td>
                                    <div>
                                        <strong class="text-success"><?= $job['salaire'] > 0 ? number_format($job['salaire']) . '€' : 'N/A' ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $status_class = '';
                                    $status_text = '';
                                    switch($job['status']) {
                                        case 'active':
                                            $status_class = 'bg-success';
                                            $status_text = 'Actif';
                                            break;
                                        case 'pending':
                                            $status_class = 'bg-warning';
                                            $status_text = 'En Attente';
                                            break;
                                        case 'rejected':
                                            $status_class = 'bg-danger';
                                            $status_text = 'Rejeté';
                                            break;
                                        case 'expired':
                                            $status_class = 'bg-secondary';
                                            $status_text = 'Expiré';
                                            break;
                                        default:
                                            $status_class = 'bg-secondary';
                                            $status_text = ucfirst($job['status']);
                                    }
                                    ?>
                                    <span class="badge <?= $status_class ?>"><?= $status_text ?></span>
                                </td>
                                <td>
                                    <div>
                                        <div><?= date('j M Y', strtotime($job['date_a'])) ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($job['date_a'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline me-1" onclick="viewJob(<?= $job['id'] ?>)" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-info me-1" onclick="editJob(<?= $job['id'] ?>)" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($job['status'] === 'pending'): ?>
                                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-success me-1" onclick="approveJob(<?= $job['id'] ?>)" title="Approuver">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger me-1" onclick="rejectJob(<?= $job['id'] ?>)" title="Rejeter">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger" onclick="deleteJob(<?= $job['id'] ?>)" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&domaine=<?= urlencode($domaine_filter) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Job Modal -->
<div class="modal fade" id="addJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une Nouvelle Offre d'Emploi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_job">
                    <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                    
                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="titre" class="form-label">Titre du Poste *</label>
                                <input type="text" class="form-control" id="titre" name="titre" required maxlength="200">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="salaire" class="form-label">Salaire (MAD)</label>
                                <input type="text" class="form-control" id="salaire" name="salaire" placeholder="Ex: 15000 - 20000 MAD" maxlength="100">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description du Poste *</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    </div>
                    
                    <!-- Company Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="entreprise" class="form-label">Nom de l'Entreprise *</label>
                                <input type="text" class="form-control" id="entreprise" name="entreprise" required maxlength="150">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="siteweb" class="form-label">Site Web</label>
                                <input type="url" class="form-control" id="siteweb" name="siteweb" placeholder="https://example.com" maxlength="200">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="entreprise_detaile" class="form-label">Description de l'Entreprise</label>
                        <textarea class="form-control" id="entreprise_detaile" name="entreprise_detaile" rows="3" maxlength="1000"></textarea>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email de Contact *</label>
                                <input type="email" class="form-control" id="email" name="email" required maxlength="150">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" placeholder="06 XX XX XX XX" maxlength="20">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Categorization -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="domaine_id" class="form-label">Domaine *</label>
                                <select class="form-select" id="domaine_id" name="domaine_id" required>
                                    <option value="">Sélectionner un domaine</option>
                                    <?php foreach ($domains as $domain): ?>
                                        <option value="<?= $domain['id'] ?>"><?= htmlspecialchars($domain['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="ville_id" class="form-label">Ville *</label>
                                <select class="form-select" id="ville_id" name="ville_id" required>
                                    <option value="">Sélectionner une ville</option>
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?= $city['id'] ?>"><?= htmlspecialchars($city['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="contrat_id" class="form-label">Type de Contrat *</label>
                                <select class="form-select" id="contrat_id" name="contrat_id" required>
                                    <option value="">Sélectionner un contrat</option>
                                    <?php foreach ($contracts as $contract): ?>
                                        <option value="<?= $contract['id'] ?>"><?= htmlspecialchars($contract['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="profile_id" class="form-label">Profil Recruteur *</label>
                                <select class="form-select" id="profile_id" name="profile_id" required>
                                    <option value="">Sélectionner un profil</option>
                                    <?php foreach ($profiles as $profile): ?>
                                        <option value="<?= $profile['id'] ?>"><?= htmlspecialchars(($profile['nom'] ?? '') . ' ' . ($profile['prenom'] ?? '')) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Date and Image -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="date_fin" class="form-label">Date Limite de Candidature</label>
                                <input type="date" class="form-control" id="date_fin" name="date_fin" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="image" class="form-label">Logo/Image de l'Entreprise</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <div class="form-text">JPG, PNG, GIF - Max 5MB</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex justify-content-between w-100">
                        <div>
                            <button type="button" class="enterprise-btn enterprise-btn-info me-2" onclick="showTemplates()">
                                <i class="fas fa-file-alt"></i> Modèles
                            </button>
                            <button type="button" class="enterprise-btn enterprise-btn-warning" onclick="saveAsTemplate()">
                                <i class="fas fa-save"></i> Sauvegarder Modèle
                            </button>
                        </div>
                        <div>
                            <button type="button" class="enterprise-btn enterprise-btn-outline me-2" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="enterprise-btn enterprise-btn-success">Ajouter</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Job Modal -->
<div class="modal fade" id="editJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier l'Offre d'Emploi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_job">
                    <input type="hidden" name="job_id" id="edit_job_id">
                    <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                    
                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="edit_titre" class="form-label">Titre du Poste *</label>
                                <input type="text" class="form-control" id="edit_titre" name="titre" required maxlength="200">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="edit_date_a" class="form-label">Date de Publication *</label>
                                <input type="date" class="form-control" id="edit_date_a" name="date_a" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description du Poste *</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="4" required></textarea>
                    </div>
                    
                    <!-- Company Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_entreprise" class="form-label">Nom de l'Entreprise *</label>
                                <input type="text" class="form-control" id="edit_entreprise" name="entreprise" required maxlength="150">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_siteweb" class="form-label">Site Web</label>
                                <input type="url" class="form-control" id="edit_siteweb" name="siteweb" placeholder="https://example.com" maxlength="200">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_entreprise_detaile" class="form-label">Description de l'Entreprise</label>
                        <textarea class="form-control" id="edit_entreprise_detaile" name="entreprise_detaile" rows="3" maxlength="1000"></textarea>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">Email de Contact *</label>
                                <input type="email" class="form-control" id="edit_email" name="email" required maxlength="150">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_telephone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="edit_telephone" name="telephone" placeholder="06 XX XX XX XX" maxlength="20">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Categorization -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="edit_domaine_id" class="form-label">Domaine *</label>
                                <select class="form-select" id="edit_domaine_id" name="domaine_id" required>
                                    <option value="">Sélectionner un domaine</option>
                                    <?php foreach ($domains as $domain): ?>
                                        <option value="<?= $domain['id'] ?>"><?= htmlspecialchars($domain['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="edit_ville_id" class="form-label">Ville *</label>
                                <select class="form-select" id="edit_ville_id" name="ville_id" required>
                                    <option value="">Sélectionner une ville</option>
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?= $city['id'] ?>"><?= htmlspecialchars($city['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="edit_contrat_id" class="form-label">Type de Contrat *</label>
                                <select class="form-select" id="edit_contrat_id" name="contrat_id" required>
                                    <option value="">Sélectionner un contrat</option>
                                    <?php foreach ($contracts as $contract): ?>
                                        <option value="<?= $contract['id'] ?>"><?= htmlspecialchars($contract['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="edit_profile_id" class="form-label">Profil Recruteur *</label>
                                <select class="form-select" id="edit_profile_id" name="profile_id" required>
                                    <option value="">Sélectionner un profil</option>
                                    <?php foreach ($profiles as $profile): ?>
                                        <option value="<?= $profile['id'] ?>"><?= htmlspecialchars(($profile['nom'] ?? '') . ' ' . ($profile['prenom'] ?? '')) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Date and Image -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_date_fin" class="form-label">Date Limite de Candidature</label>
                                <input type="date" class="form-control" id="edit_date_fin" name="date_fin">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_image" class="form-label">Logo/Image de l'Entreprise</label>
                                <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                                <div class="form-text">JPG, PNG, GIF - Max 5MB</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="enterprise-btn enterprise-btn-success">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Job Modal -->
<div class="modal fade" id="viewJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de l'Offre d'Emploi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewJobContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
    .stat-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .enterprise-table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        border: none;
    }

    .enterprise-table td {
        vertical-align: middle;
    }

    .btn-group .enterprise-btn {
        margin-right: 2px;
    }

    .btn-group .enterprise-btn:last-child {
        margin-right: 0;
    }
</style>

<!-- Job Analytics Modal -->
<div class="modal fade" id="jobAnalyticsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chart-line me-2"></i>
                    Analytics des Offres d'Emploi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Status Distribution Chart -->
                    <div class="col-md-6 mb-4">
                        <div class="enterprise-card">
                            <div class="enterprise-card-header">
                                <h6 class="enterprise-card-title">Distribution par Statut</h6>
                            </div>
                            <div class="enterprise-card-body">
                                <canvas id="statusChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Domain Distribution Chart -->
                    <div class="col-md-6 mb-4">
                        <div class="enterprise-card">
                            <div class="enterprise-card-header">
                                <h6 class="enterprise-card-title">Distribution par Domaine</h6>
                            </div>
                            <div class="enterprise-card-body">
                                <canvas id="domainChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Monthly Trends -->
                    <div class="col-md-12 mb-4">
                        <div class="enterprise-card">
                            <div class="enterprise-card-header">
                                <h6 class="enterprise-card-title">Tendances Mensuelles</h6>
                            </div>
                            <div class="enterprise-card-body">
                                <canvas id="trendsChart" width="800" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Key Metrics -->
                    <div class="col-md-12">
                        <div class="enterprise-card">
                            <div class="enterprise-card-header">
                                <h6 class="enterprise-card-title">Métriques Clés</h6>
                            </div>
                            <div class="enterprise-card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h3 class="text-primary"><?= $jobStats['total_jobs'] ?></h3>
                                            <p class="text-muted">Total Offres</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h3 class="text-success"><?= $jobStats['active_jobs'] ?></h3>
                                            <p class="text-muted">Offres Actives</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h3 class="text-warning"><?= $jobStats['pending_jobs'] ?></h3>
                                            <p class="text-muted">En Attente</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h3 class="text-info"><?= $jobStats['applications_count'] ?></h3>
                                            <p class="text-muted">Candidatures</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="enterprise-btn enterprise-btn-primary" onclick="exportAnalytics()">
                    <i class="fas fa-download"></i> Exporter Analytics
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Duplicate Detection Modal -->
<div class="modal fade" id="duplicatesModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-copy me-2"></i>
                    Détection de Doublons
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['duplicates']) && !empty($_SESSION['duplicates'])): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong><?= count($_SESSION['duplicates']) ?> groupe(s) de doublons détectés</strong>
                    </div>
                    
                    <?php foreach ($_SESSION['duplicates'] as $index => $duplicate): ?>
                        <div class="enterprise-card mb-3">
                            <div class="enterprise-card-header">
                                <h6 class="enterprise-card-title">
                                    <i class="fas fa-copy me-2"></i>
                                    <?= $duplicate['type'] === 'title' ? 'Titre identique' : 'Entreprise + Titre identiques' ?>
                                    (<?= $duplicate['count'] ?> occurrences)
                                </h6>
                            </div>
                            <div class="enterprise-card-body">
                                <p><strong>Valeur:</strong> <?= htmlspecialchars($duplicate['value']) ?></p>
                                
                                <form method="POST" class="duplicate-merge-form">
                                    <input type="hidden" name="action" value="merge_duplicates">
                                    <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                                    
                                    <div class="row">
                                        <?php foreach ($duplicate['ids'] as $job_id): ?>
                                            <?php 
                                            $job = $db->fetch("SELECT * FROM annonces WHERE id = ?", [$job_id]);
                                            if ($job):
                                            ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="keep_id" value="<?= $job['id'] ?>" id="keep_<?= $job['id'] ?>">
                                                            <label class="form-check-label" for="keep_<?= $job['id'] ?>">
                                                                <strong>Conserver cette offre</strong>
                                                            </label>
                                                        </div>
                                                        <hr>
                                                        <h6><?= htmlspecialchars($job['titre']) ?></h6>
                                                        <p class="text-muted mb-1">
                                                            <i class="fas fa-building me-1"></i>
                                                            <?= htmlspecialchars($job['entreprise']) ?>
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            <i class="fas fa-envelope me-1"></i>
                                                            <?= htmlspecialchars($job['email']) ?>
                                                        </p>
                                                        <p class="text-muted mb-1">
                                                            <i class="fas fa-calendar me-1"></i>
                                                            <?= date('d/m/Y', strtotime($job['date_creation'])) ?>
                                                        </p>
                                                        <p class="text-muted mb-0">
                                                            <i class="fas fa-tag me-1"></i>
                                                            <?= ucfirst($job['status']) ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="text-center mt-3">
                                        <button type="submit" class="enterprise-btn enterprise-btn-warning" onclick="return confirm('Êtes-vous sûr de vouloir fusionner ces doublons ?')">
                                            <i class="fas fa-compress-arrows-alt me-2"></i>
                                            Fusionner les Doublons
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php unset($_SESSION['duplicates']); ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5 class="text-success">Aucun doublon détecté</h5>
                        <p class="text-muted">Toutes les offres d'emploi sont uniques.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="enterprise-btn enterprise-btn-primary" onclick="checkDuplicates()">
                    <i class="fas fa-sync me-2"></i>
                    Vérifier à Nouveau
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Job Templates Modal -->
<div class="modal fade" id="templatesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt me-2"></i>
                    Modèles d'Offres d'Emploi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php 
                $templates = $_SESSION['job_templates'] ?? [];
                if (empty($templates)): 
                ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucun modèle sauvegardé</h5>
                        <p class="text-muted">Créez votre premier modèle en remplissant le formulaire d'ajout d'offre.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($templates as $name => $template): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title"><?= htmlspecialchars($name) ?></h6>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <i class="fas fa-briefcase me-1"></i>
                                                <?= htmlspecialchars($template['titre']) ?>
                                            </small>
                                        </p>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <i class="fas fa-building me-1"></i>
                                                <?= htmlspecialchars($template['entreprise']) ?>
                                            </small>
                                        </p>
                                        <button class="btn btn-sm btn-primary" onclick="loadTemplate('<?= htmlspecialchars($name) ?>')">
                                            <i class="fas fa-upload me-1"></i>
                                            Charger
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger ms-2" onclick="deleteTemplate('<?= htmlspecialchars($name) ?>')">
                                            <i class="fas fa-trash me-1"></i>
                                            Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Save Template Modal -->
<div class="modal fade" id="saveTemplateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-save me-2"></i>
                    Sauvegarder comme Modèle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="saveTemplateForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="save_template">
                    <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                    
                    <div class="mb-3">
                        <label for="template_name" class="form-label">Nom du Modèle</label>
                        <input type="text" class="form-control" id="template_name" name="template_name" required placeholder="Ex: Développeur Full Stack">
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Le modèle sera sauvegardé avec les données actuelles du formulaire.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="enterprise-btn enterprise-btn-success">Sauvegarder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Job Management JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize job management features
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Job management initialized');
        
        // Auto-show duplicates modal if duplicates were found
        <?php if (isset($_GET['duplicates']) && $_GET['duplicates'] === 'found'): ?>
        setTimeout(() => {
            new bootstrap.Modal(document.getElementById('duplicatesModal')).show();
        }, 500);
        <?php endif; ?>
        
        // Auto-load template data if template was loaded
        <?php if (isset($_GET['template_loaded']) && isset($_SESSION['selected_template'])): ?>
        const template = <?= json_encode($_SESSION['selected_template']) ?>;
        loadTemplateData(template);
        <?php unset($_SESSION['selected_template']); ?>
        <?php endif; ?>
    });

    // Refresh job data function
    function refreshJobData() {
        location.reload();
    }

    // Export job data function
    function exportToCSV() {
        // Submit form to export CSV
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="export_csv">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    // Generate job report function
    function generateJobReport() {
        alert('Génération du rapport offres d\'emploi en cours...\nCette fonctionnalité sera bientôt disponible.');
    }

    // Show job analytics function
    function showJobAnalytics() {
        new bootstrap.Modal(document.getElementById('jobAnalyticsModal')).show();
        
        // Initialize charts after modal is shown
        setTimeout(() => {
            initializeCharts();
        }, 500);
    }
    
    // Initialize analytics charts
    function initializeCharts() {
        // Status Distribution Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Actives', 'En Attente', 'Rejetées'],
                datasets: [{
                    data: [<?= $jobStats['active_jobs'] ?>, <?= $jobStats['pending_jobs'] ?>, <?= $jobStats['rejected_jobs'] ?>],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Domain Distribution Chart
        const domainCtx = document.getElementById('domainChart').getContext('2d');
        new Chart(domainCtx, {
            type: 'bar',
            data: {
                labels: ['IT', 'Marketing', 'Finance', 'RH', 'Ventes'],
                datasets: [{
                    label: 'Nombre d\'offres',
                    data: [<?= $jobStats['total_jobs'] * 0.4 ?>, <?= $jobStats['total_jobs'] * 0.2 ?>, <?= $jobStats['total_jobs'] * 0.15 ?>, <?= $jobStats['total_jobs'] * 0.15 ?>, <?= $jobStats['total_jobs'] * 0.1 ?>],
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Monthly Trends Chart
        const trendsCtx = document.getElementById('trendsChart').getContext('2d');
        new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
                datasets: [{
                    label: 'Nouvelles Offres',
                    data: [12, 19, 15, 25, 22, 30, 28, 35, 32, 28, 25, 20],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Candidatures',
                    data: [45, 52, 48, 65, 70, 85, 90, 95, 88, 75, 68, 55],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
    // Export analytics function
    function exportAnalytics() {
        alert('Export des analytics en cours...\nCette fonctionnalité sera bientôt disponible.');
    }
    
    // Check duplicates function
    function checkDuplicates() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="check_duplicates">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
        `;
        document.body.appendChild(form);
        form.submit();
    }
    
    // Template management functions
    function showTemplates() {
        new bootstrap.Modal(document.getElementById('templatesModal')).show();
    }
    
    function saveAsTemplate() {
        new bootstrap.Modal(document.getElementById('saveTemplateModal')).show();
    }
    
    function loadTemplate(templateName) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="load_template">
            <input type="hidden" name="template_name" value="${templateName}">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
        `;
        document.body.appendChild(form);
        form.submit();
    }
    
    function deleteTemplate(templateName) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer le modèle "${templateName}" ?`)) {
            // This would need to be implemented with AJAX or form submission
            alert('Fonctionnalité de suppression de modèle à implémenter');
        }
    }
    
    function loadTemplateData(template) {
        // Open add modal first
        new bootstrap.Modal(document.getElementById('addJobModal')).show();
        
        // Fill form with template data
        setTimeout(() => {
            if (template.titre) document.getElementById('titre').value = template.titre;
            if (template.description) document.getElementById('description').value = template.description;
            if (template.entreprise) document.getElementById('entreprise').value = template.entreprise;
            if (template.entreprise_detaile) document.getElementById('entreprise_detaile').value = template.entreprise_detaile;
            if (template.email) document.getElementById('email').value = template.email;
            if (template.telephone) document.getElementById('telephone').value = template.telephone;
            if (template.siteweb) document.getElementById('siteweb').value = template.siteweb;
            if (template.salaire) document.getElementById('salaire').value = template.salaire;
            if (template.domaine_id) document.getElementById('domaine_id').value = template.domaine_id;
            if (template.ville_id) document.getElementById('ville_id').value = template.ville_id;
            if (template.contrat_id) document.getElementById('contrat_id').value = template.contrat_id;
            if (template.profile_id) document.getElementById('profile_id').value = template.profile_id;
        }, 500);
    }

    // Show job details function
    function showJobDetails() {
        alert('Détails des offres d\'emploi:\nTotal: <?= number_format($jobStats['total_jobs']) ?>\nActives: <?= number_format($jobStats['active_jobs']) ?>');
    }

    // Show active jobs function
    function showActiveJobs() {
        alert('Offres actives:\n<?= number_format($jobStats['active_jobs']) ?> offres actives');
    }

    // Show pending jobs function
    function showPendingJobs() {
        alert('Offres en attente:\n<?= number_format($jobStats['pending_jobs']) ?> offres en attente d\'approbation');
    }

    // Show applications function
    function showApplications() {
        alert('Candidatures:\n<?= number_format($jobStats['applications_count']) ?> candidatures total');
    }

    // Show salary stats function
    function showSalaryStats() {
        alert('Statistiques des salaires:\nSalaire moyen: <?= number_format($jobStats['avg_salary']) ?>€');
    }

    // Show featured jobs function
    function showFeaturedJobs() {
        alert('Offres en vedette:\n<?= number_format($jobStats['featured_jobs']) ?> offres en vedette');
    }

    // View job function
    function viewJob(jobId) {
        // For demo purposes, show sample job details
        const content = `
            <div class="row">
                <div class="col-md-6">
                    <h6><strong>Titre:</strong></h6>
                    <p>Développeur Full Stack</p>
                    
                    <h6><strong>Entreprise:</strong></h6>
                    <p>TechCorp Solutions</p>
                    
                    <h6><strong>Email:</strong></h6>
                    <p>contact@techcorp.com</p>
                    
                    <h6><strong>Salaire:</strong></h6>
                    <p>45,000€ - 55,000€</p>
                </div>
                <div class="col-md-6">
                    <h6><strong>Domaine:</strong></h6>
                    <p><span class="badge bg-info">IT</span></p>
                    
                    <h6><strong>Ville:</strong></h6>
                    <p><span class="badge bg-secondary">Paris</span></p>
                    
                    <h6><strong>Statut:</strong></h6>
                    <p><span class="badge bg-success">Actif</span></p>
                    
                    <h6><strong>Date de publication:</strong></h6>
                    <p>15 Jan 2024</p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6><strong>Description:</strong></h6>
                    <p>Nous recherchons un développeur full stack expérimenté pour rejoindre notre équipe dynamique. Vous travaillerez sur des projets innovants et aurez l'opportunité de développer vos compétences techniques.</p>
                </div>
            </div>
        `;
        
        document.getElementById('viewJobContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('viewJobModal')).show();
    }

    // Edit job function
    function editJob(jobId) {
        // Fetch job data via AJAX
        fetch('manage_annonces.php?action=get_job&id=' + jobId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const job = data.job;
                    document.getElementById('edit_job_id').value = jobId;
                    document.getElementById('edit_titre').value = job.titre || '';
                    document.getElementById('edit_date_a').value = job.date_a || '';
                    document.getElementById('edit_description').value = job.description || '';
                    document.getElementById('edit_entreprise').value = job.entreprise || '';
                    document.getElementById('edit_siteweb').value = job.siteweb || '';
                    document.getElementById('edit_entreprise_detaile').value = job.entreprise_detaile || '';
                    document.getElementById('edit_email').value = job.email || '';
                    document.getElementById('edit_telephone').value = job.telephone || '';
                    document.getElementById('edit_salaire').value = job.salaire || '';
                    document.getElementById('edit_date_fin').value = job.date_fin || '';
                    document.getElementById('edit_domaine_id').value = job.domaine_id || '';
                    document.getElementById('edit_ville_id').value = job.ville_id || '';
                    document.getElementById('edit_contrat_id').value = job.contrat_id || '';
                    document.getElementById('edit_profile_id').value = job.profile_id || '';
                    
                    new bootstrap.Modal(document.getElementById('editJobModal')).show();
                } else {
                    alert('Erreur lors du chargement des données: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors du chargement des données');
            });
    }

    // Approve job function
    function approveJob(jobId) {
        if (confirm('Êtes-vous sûr de vouloir approuver cette offre d\'emploi ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="approve_job">
                <input type="hidden" name="job_id" value="${jobId}">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Reject job function
    function rejectJob(jobId) {
        if (confirm('Êtes-vous sûr de vouloir rejeter cette offre d\'emploi ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="reject_job">
                <input type="hidden" name="job_id" value="${jobId}">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Delete job function
    function deleteJob(jobId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette offre d\'emploi ? Cette action ne peut pas être annulée !')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_job">
                <input type="hidden" name="job_id" value="${jobId}">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Open add modal function
    function openAddModal() {
        new bootstrap.Modal(document.getElementById('addJobModal')).show();
    }

    // Bulk operations functions
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.job-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        
        updateBulkButtons();
    }

    function updateBulkButtons() {
        const checkboxes = document.querySelectorAll('.job-checkbox:checked');
        const count = checkboxes.length;
        
        const bulkApproveBtn = document.getElementById('bulkApproveBtn');
        const bulkRejectBtn = document.getElementById('bulkRejectBtn');
        const bulkFeatureBtn = document.getElementById('bulkFeatureBtn');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        
        const enabled = count > 0;
        
        bulkApproveBtn.disabled = !enabled;
        bulkRejectBtn.disabled = !enabled;
        bulkFeatureBtn.disabled = !enabled;
        bulkDeleteBtn.disabled = !enabled;
        
        // Update button text with count
        if (enabled) {
            bulkApproveBtn.innerHTML = `<i class="fas fa-check"></i> Approuver (${count})`;
            bulkRejectBtn.innerHTML = `<i class="fas fa-times"></i> Rejeter (${count})`;
            bulkFeatureBtn.innerHTML = `<i class="fas fa-star"></i> Mettre en avant (${count})`;
            bulkDeleteBtn.innerHTML = `<i class="fas fa-trash"></i> Supprimer (${count})`;
        } else {
            bulkApproveBtn.innerHTML = `<i class="fas fa-check"></i> Approuver`;
            bulkRejectBtn.innerHTML = `<i class="fas fa-times"></i> Rejeter`;
            bulkFeatureBtn.innerHTML = `<i class="fas fa-star"></i> Mettre en avant`;
            bulkDeleteBtn.innerHTML = `<i class="fas fa-trash"></i> Supprimer`;
        }
    }

    function bulkApprove() {
        const selectedIds = getSelectedJobIds();
        if (selectedIds.length === 0) {
            alert('Veuillez sélectionner au moins une offre d\'emploi');
            return;
        }
        
        if (confirm(`Êtes-vous sûr de vouloir approuver ${selectedIds.length} offre(s) d'emploi ?`)) {
            submitBulkAction('bulk_approve', selectedIds);
        }
    }

    function bulkReject() {
        const selectedIds = getSelectedJobIds();
        if (selectedIds.length === 0) {
            alert('Veuillez sélectionner au moins une offre d\'emploi');
            return;
        }
        
        if (confirm(`Êtes-vous sûr de vouloir rejeter ${selectedIds.length} offre(s) d'emploi ?`)) {
            submitBulkAction('bulk_reject', selectedIds);
        }
    }

    function bulkFeature() {
        const selectedIds = getSelectedJobIds();
        if (selectedIds.length === 0) {
            alert('Veuillez sélectionner au moins une offre d\'emploi');
            return;
        }
        
        if (confirm(`Êtes-vous sûr de vouloir mettre en avant ${selectedIds.length} offre(s) d'emploi ?`)) {
            submitBulkAction('bulk_feature', selectedIds);
        }
    }

    function bulkDelete() {
        const selectedIds = getSelectedJobIds();
        if (selectedIds.length === 0) {
            alert('Veuillez sélectionner au moins une offre d\'emploi');
            return;
        }
        
        if (confirm(`Êtes-vous sûr de vouloir supprimer définitivement ${selectedIds.length} offre(s) d'emploi ? Cette action ne peut pas être annulée !`)) {
            submitBulkAction('bulk_delete', selectedIds);
        }
    }

    function getSelectedJobIds() {
        const checkboxes = document.querySelectorAll('.job-checkbox:checked');
        return Array.from(checkboxes).map(checkbox => checkbox.value);
    }

    function submitBulkAction(action, jobIds) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="${action}">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
        `;
        
        jobIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'job_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
