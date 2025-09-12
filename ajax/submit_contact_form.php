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
    $name = Security::sanitizeInput($_POST['name'] ?? '');
    $email = Security::sanitizeInput($_POST['email'] ?? '');
    $subject = Security::sanitizeInput($_POST['subject'] ?? '');
    $message = Security::sanitizeInput($_POST['message'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        throw new Exception('Tous les champs sont obligatoires');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Adresse email invalide');
    }
    
    // Insert into database
    $query = "INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())";
    $result = $db->execute($query, [$name, $email, $subject, $message]);
    
    if ($result) {
        // Send email notification (optional)
        $to = "admin@jobmaroc.com";
        $email_subject = "Nouveau message de contact: " . $subject;
        $email_body = "Nom: $name\nEmail: $email\nSujet: $subject\n\nMessage:\n$message";
        
        mail($to, $email_subject, $email_body);
        
        echo json_encode([
            'success' => true,
            'message' => 'Message envoyé avec succès!'
        ]);
    } else {
        throw new Exception('Erreur lors de l\'envoi du message');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
