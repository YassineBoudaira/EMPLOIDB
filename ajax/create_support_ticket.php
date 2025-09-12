<?php
// Include configuration
include '../include/config.php';
include '../include/sess.php';
include '../include/connexion.php';

header('Content-Type: application/json');

try {
    // Validate CSRF token
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Token de sécurité invalide');
    }
    
    // Get form data
    $subject = Security::sanitizeInput($_POST['subject'] ?? '');
    $issue_type = Security::sanitizeInput($_POST['issue_type'] ?? '');
    $priority = Security::sanitizeInput($_POST['priority'] ?? '');
    $description = Security::sanitizeInput($_POST['description'] ?? '');
    $user_id = $_SESSION['user_id'] ?? null;
    
    // Validation
    if (empty($subject) || empty($issue_type) || empty($priority) || empty($description)) {
        throw new Exception('Tous les champs sont obligatoires');
    }
    
    if (!$user_id) {
        throw new Exception('Utilisateur non connecté');
    }
    
    // Handle file upload
    $attachment_path = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_result = Security::validateFileUpload($_FILES['attachment'], ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'], 10 * 1024 * 1024);
        if ($upload_result['success']) {
            $filename = Security::generateSecureFilename($_FILES['attachment']['name'], 'ticket_');
            $upload_path = 'upload/tickets/' . $filename;
            
            if (!is_dir('upload/tickets/')) {
                mkdir('upload/tickets/', 0755, true);
            }
            
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path)) {
                $attachment_path = $upload_path;
            }
        }
    }
    
    // Insert into database
    $query = "INSERT INTO support_tickets (user_id, subject, issue_type, priority, description, attachment_path, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'open', NOW())";
    $result = $db->execute($query, [$user_id, $subject, $issue_type, $priority, $description, $attachment_path]);
    
    if ($result) {
        // Send email notification (optional)
        $to = "support@jobmaroc.com";
        $email_subject = "Nouveau ticket support: " . $subject;
        $email_body = "Nouveau ticket créé:\n\nSujet: $subject\nType: $issue_type\nPriorité: $priority\nDescription: $description";
        
        mail($to, $email_subject, $email_body);
        
        echo json_encode([
            'success' => true,
            'message' => 'Ticket de support créé avec succès!'
        ]);
    } else {
        throw new Exception('Erreur lors de la création du ticket');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
