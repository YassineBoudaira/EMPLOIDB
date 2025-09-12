<?php
// AJAX Handler: Create Job Alert
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
    if (empty($_POST['alert_name'])) {
        echo json_encode(['success' => false, 'message' => 'Le nom de l\'alerte est requis']);
        exit;
    }
    
    if (empty($_POST['frequency'])) {
        echo json_encode(['success' => false, 'message' => 'La fréquence est requise']);
        exit;
    }
    
    // Check if alert with same name already exists
    $existing_alert = $db->fetch(
        "SELECT id FROM job_alerts WHERE user_id = ? AND alert_name = ?", 
        [$user_id, $_POST['alert_name']]
    );
    
    if ($existing_alert) {
        echo json_encode(['success' => false, 'message' => 'Une alerte avec ce nom existe déjà']);
        exit;
    }
    
    // Insert job alert
    $alert_data = [
        'user_id' => $user_id,
        'alert_name' => $_POST['alert_name'],
        'keywords' => $_POST['keywords'] ?? null,
        'domaine_id' => $_POST['domaine_id'] ?? null,
        'ville_id' => $_POST['ville_id'] ?? null,
        'contrat_id' => $_POST['contrat_id'] ?? null,
        'frequency' => $_POST['frequency'],
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $alert_id = $db->insert("job_alerts", $alert_data);
    
    if ($alert_id) {
        // Create notification
        $notification_data = [
            'user_id' => $user_id,
            'type' => 'job_alert_created',
            'title' => 'Alerte Emploi Créée',
            'message' => "Votre alerte '{$_POST['alert_name']}' a été créée avec succès.",
            'data' => json_encode(['alert_id' => $alert_id]),
            'created_at' => date('Y-m-d H:i:s')
        ];
        $db->insert("notifications", $notification_data);
        
        echo json_encode([
            'success' => true,
            'message' => 'Alerte créée avec succès'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la création de l\'alerte'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la création de l\'alerte'
    ]);
}
?>
