<?php
// Include configuration first (before any session starts)
include '../include/config.php';
include '../include/sess.php';
include '../include/connexion.php';

// Set JSON content type
header('Content-Type: application/json');

// Check if user is logged in
if (!Security::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté pour sauvegarder des emplois']);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : 'toggle';
    
    // Validate job ID
    if (!$job_id) {
        echo json_encode(['success' => false, 'message' => 'ID d\'emploi invalide']);
        exit();
    }
    
    // Check if job exists
    $job = $db->fetch("SELECT id FROM annonces WHERE id = ? AND status = 'active'", [$job_id]);
    if (!$job) {
        echo json_encode(['success' => false, 'message' => 'Emploi non trouvé']);
        exit();
    }
    
    // Check if job is already saved
    $saved_job = $db->fetch("SELECT id FROM saved_jobs WHERE user_id = ? AND annonce_id = ?", [$user_id, $job_id]);
    
    if ($action === 'toggle') {
        if ($saved_job) {
            // Remove from saved jobs
            $db->query("DELETE FROM saved_jobs WHERE user_id = ? AND annonce_id = ?", [$user_id, $job_id]);
            echo json_encode(['success' => true, 'saved' => false, 'message' => 'Emploi retiré des favoris']);
        } else {
            // Add to saved jobs
            $db->query("INSERT INTO saved_jobs (user_id, annonce_id) VALUES (?, ?)", [$user_id, $job_id]);
            echo json_encode(['success' => true, 'saved' => true, 'message' => 'Emploi ajouté aux favoris']);
        }
    } elseif ($action === 'save') {
        if (!$saved_job) {
            $db->query("INSERT INTO saved_jobs (user_id, annonce_id) VALUES (?, ?)", [$user_id, $job_id]);
        }
        echo json_encode(['success' => true, 'saved' => true, 'message' => 'Emploi sauvegardé']);
    } elseif ($action === 'unsave') {
        if ($saved_job) {
            $db->query("DELETE FROM saved_jobs WHERE user_id = ? AND annonce_id = ?", [$user_id, $job_id]);
        }
        echo json_encode(['success' => true, 'saved' => false, 'message' => 'Emploi retiré des favoris']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Action invalide']);
    }
    
} catch (Exception $e) {
    error_log("Error in save_job.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde']);
}
?>

