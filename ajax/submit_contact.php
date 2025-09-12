<?php
// AJAX Handler: Submit Contact Form
header('Content-Type: application/json');
include '../include/config.php';
include '../include/sess.php';
include '../include/connexion.php';

// Check CSRF token
if (!isset($_POST['csrf_token']) || !Security::validateCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Token de sécurité invalide']);
    exit;
}

try {
    // Validate required fields
    $required_fields = ['name', 'email', 'subject', 'message'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "Le champ $field est requis"]);
            exit;
        }
    }
    
    // Validate email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Adresse email invalide']);
        exit;
    }
    
    // Insert contact message
    $contact_data = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'subject' => $_POST['subject'],
        'message' => $_POST['message'],
        'user_id' => $_SESSION['user_id'] ?? null,
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $contact_id = $db->insert("contact_messages", $contact_data);
    
    if ($contact_id) {
        // Send email notification to admin (optional)
        $to = "admin@jobmaroc.ma";
        $subject = "Nouveau message de contact: " . $_POST['subject'];
        $message = "
        Nouveau message de contact reçu:
        
        Nom: {$_POST['name']}
        Email: {$_POST['email']}
        Sujet: {$_POST['subject']}
        
        Message:
        {$_POST['message']}
        
        Date: " . date('Y-m-d H:i:s');
        
        $headers = "From: {$_POST['email']}\r\n";
        $headers .= "Reply-To: {$_POST['email']}\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // Uncomment to enable email sending
        // mail($to, $subject, $message, $headers);
        
        echo json_encode([
            'success' => true,
            'message' => 'Message envoyé avec succès'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de l\'envoi du message'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de l\'envoi du message'
    ]);
}
?>
