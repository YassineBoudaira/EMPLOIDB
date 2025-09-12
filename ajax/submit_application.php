<?php
// AJAX Handler: Submit Job Application
header('Content-Type: application/json');
include '../include/config.php';
include '../include/sess.php';
include '../include/connexion.php';

// Check if user is logged in
if (!Security::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

// Check CSRF token
if (!isset($_POST['csrf_token']) || !Security::validateCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Token de sécurité invalide']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Validate required fields
    $required_fields = ['job_id', 'full_name', 'email', 'phone', 'cover_letter'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Le champ $field est requis"]);
            exit;
        }
    }
    
    // Check if already applied
    $existing_application = $db->fetch(
        "SELECT id FROM postulation WHERE user_id = ? AND annonce_id = ?", 
        [$user_id, $_POST['job_id']]
    );
    
    if ($existing_application) {
        echo json_encode(['success' => false, 'message' => 'Vous avez déjà postulé à cette offre']);
        exit;
    }
    
    // Handle CV file upload
    $cv_path = null;
    if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../upload/applications/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['cv']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['pdf', 'doc', 'docx'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            echo json_encode(['success' => false, 'message' => 'Format de CV non autorisé (PDF, DOC, DOCX uniquement)']);
            exit;
        }
        
        $file_size = $_FILES['cv']['size'];
        if ($file_size > 5 * 1024 * 1024) { // 5MB limit
            echo json_encode(['success' => false, 'message' => 'CV trop volumineux (max 5MB)']);
            exit;
        }
        
        $filename = 'cv_' . $user_id . '_' . time() . '.' . $file_extension;
        $cv_path = 'applications/' . $filename;
        
        if (!move_uploaded_file($_FILES['cv']['tmp_name'], $upload_dir . $filename)) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors du téléchargement du CV']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'CV requis']);
        exit;
    }
    
    // Handle portfolio file upload
    $portfolio_path = null;
    if (isset($_FILES['portfolio']) && $_FILES['portfolio']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../upload/portfolios/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['portfolio']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['pdf', 'zip'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            echo json_encode(['success' => false, 'message' => 'Format de portfolio non autorisé (PDF, ZIP uniquement)']);
            exit;
        }
        
        $file_size = $_FILES['portfolio']['size'];
        if ($file_size > 10 * 1024 * 1024) { // 10MB limit
            echo json_encode(['success' => false, 'message' => 'Portfolio trop volumineux (max 10MB)']);
            exit;
        }
        
        $filename = 'portfolio_' . $user_id . '_' . time() . '.' . $file_extension;
        $portfolio_path = 'portfolios/' . $filename;
        
        if (!move_uploaded_file($_FILES['portfolio']['tmp_name'], $upload_dir . $filename)) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors du téléchargement du portfolio']);
            exit;
        }
    }
    
    // Insert application
    $application_data = [
        'user_id' => $user_id,
        'annonce_id' => $_POST['job_id'],
        'full_name' => $_POST['full_name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'city' => $_POST['city'] ?? null,
        'cover_letter' => $_POST['cover_letter'],
        'cv_path' => $cv_path,
        'portfolio_path' => $portfolio_path,
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $application_id = $db->insert("postulation", $application_data);
    
    if ($application_id) {
        // Create notification for employer
        $job = $db->fetch("SELECT entreprise, titre FROM annonces WHERE id = ?", [$_POST['job_id']]);
        if ($job) {
            $notification_data = [
                'user_id' => $user_id,
                'type' => 'application_submitted',
                'title' => 'Candidature soumise',
                'message' => "Votre candidature pour '{$job['titre']}' chez {$job['entreprise']} a été soumise avec succès.",
                'data' => json_encode(['application_id' => $application_id, 'job_id' => $_POST['job_id']]),
                'created_at' => date('Y-m-d H:i:s')
            ];
            $db->insert("notifications", $notification_data);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Candidature soumise avec succès'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la soumission de la candidature'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la soumission de la candidature'
    ]);
}
?>
