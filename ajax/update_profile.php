<?php
// AJAX Handler: Update User Profile
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
    $required_fields = ['nom', 'prenom'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Le champ $field est requis"]);
            exit;
        }
    }
    
    // Handle file upload
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../upload/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            echo json_encode(['success' => false, 'message' => 'Format de fichier non autorisé']);
            exit;
        }
        
        $file_size = $_FILES['photo']['size'];
        if ($file_size > 2 * 1024 * 1024) { // 2MB limit
            echo json_encode(['success' => false, 'message' => 'Fichier trop volumineux (max 2MB)']);
            exit;
        }
        
        $filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
        $photo_path = 'profiles/' . $filename;
        
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $filename)) {
            echo json_encode(['success' => false, 'message' => 'Erreur lors du téléchargement du fichier']);
            exit;
        }
    }
    
    // Prepare update data
    $update_data = [
        'nom' => $_POST['nom'],
        'prenom' => $_POST['prenom'],
        'telephone' => $_POST['telephone'] ?? null,
        'date_n' => $_POST['date_n'] ?? null,
        'adresse' => $_POST['adresse'] ?? null,
        'ville_id' => $_POST['ville_id'] ?? null,
        'domaine_id' => $_POST['domaine_id'] ?? null,
        'bio' => $_POST['bio'] ?? null,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    if ($photo_path) {
        $update_data['photo'] = $photo_path;
    }
    
    // Check if profile exists
    $existing_profile = $db->fetch("SELECT id FROM profiles WHERE user_id = ?", [$user_id]);
    
    if ($existing_profile) {
        // Update existing profile
        $db->update("profiles", $update_data, "user_id = ?", [$user_id]);
    } else {
        // Create new profile
        $update_data['user_id'] = $user_id;
        $update_data['created_at'] = date('Y-m-d H:i:s');
        $db->insert("profiles", $update_data);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Profil mis à jour avec succès'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la mise à jour du profil'
    ]);
}
?>
