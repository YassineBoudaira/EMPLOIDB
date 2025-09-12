<?php
// Include configuration first (before any session starts)
include '../include/config.php';
include '../include/sess.php';
include '../include/connexion.php';

// Set JSON content type
header('Content-Type: application/json');

// Check if user is logged in
if (!Security::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour sauvegarder des offres']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $offer_id = isset($_POST['offer_id']) ? (int)$_POST['offer_id'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : 'toggle';
    
    // Validate offer ID
    if (!$offer_id) {
        echo json_encode(['success' => false, 'message' => 'ID d\'offre invalide']);
        exit();
    }
    
    // Check if offer exists and is active
    $offer = $db->fetch("SELECT id FROM offers WHERE id = ? AND status = 'active' AND valid_until >= CURDATE()", [$offer_id]);
    if (!$offer) {
        echo json_encode(['success' => false, 'message' => 'Offre non trouvée ou expirée']);
        exit();
    }
    
    // Check if offer is already saved
    $saved_offer = $db->fetch("SELECT id FROM saved_offers WHERE user_id = ? AND offer_id = ?", [$user_id, $offer_id]);
    
    if ($action === 'toggle') {
        if ($saved_offer) {
            // Remove from saved offers
            $db->query("DELETE FROM saved_offers WHERE user_id = ? AND offer_id = ?", [$user_id, $offer_id]);
            echo json_encode(['success' => true, 'saved' => false, 'message' => 'Offre retirée des favoris']);
        } else {
            // Add to saved offers
            $db->query("INSERT INTO saved_offers (user_id, offer_id) VALUES (?, ?)", [$user_id, $offer_id]);
            echo json_encode(['success' => true, 'saved' => true, 'message' => 'Offre ajoutée aux favoris']);
        }
    } elseif ($action === 'save') {
        if (!$saved_offer) {
            $db->query("INSERT INTO saved_offers (user_id, offer_id) VALUES (?, ?)", [$user_id, $offer_id]);
        }
        echo json_encode(['success' => true, 'saved' => true, 'message' => 'Offre sauvegardée']);
    } elseif ($action === 'unsave') {
        if ($saved_offer) {
            $db->query("DELETE FROM saved_offers WHERE user_id = ? AND offer_id = ?", [$user_id, $offer_id]);
        }
        echo json_encode(['success' => true, 'saved' => false, 'message' => 'Offre retirée des favoris']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
    }
    
} catch (Exception $e) {
    error_log("Error in save_offer.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde']);
}
?>
