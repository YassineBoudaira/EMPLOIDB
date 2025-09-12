<?php
// AJAX Handler: Get User Profile Data
header('Content-Type: application/json');
include '../include/config.php';
include '../include/sess.php';
include '../include/connexion.php';

// Check if user is logged in
if (!Security::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Get profile data
    $profile = $db->fetch("SELECT * FROM profiles WHERE user_id = ?", [$user_id]);
    
    if ($profile) {
        echo json_encode([
            'success' => true,
            'profile' => $profile
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Profil non trouvé'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération du profil'
    ]);
}
?>
