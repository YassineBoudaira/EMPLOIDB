<?php
/**
 * Enhanced Email Service for EMPLOIDB
 * Handles email validation, sending, and template management
 */

class EmailService {
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $smtp_encryption;
    private $from_email;
    private $from_name;
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
        $this->loadEmailConfig();
    }
    
    /**
     * Load email configuration from environment or database
     */
    private function loadEmailConfig() {
        $this->smtp_host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $this->smtp_port = $_ENV['MAIL_PORT'] ?? 587;
        $this->smtp_username = $_ENV['MAIL_USERNAME'] ?? '';
        $this->smtp_password = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->smtp_encryption = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
        $this->from_email = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@emploidb.com';
        $this->from_name = $_ENV['MAIL_FROM_NAME'] ?? 'EMPLOIDB';
    }
    
    /**
     * Validate email address
     */
    public function validateEmail($email) {
        if (empty($email)) {
            return ['valid' => false, 'error' => 'Email address is required'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => 'Invalid email format'];
        }
        
        // Check for disposable email domains
        $disposableDomains = $this->getDisposableEmailDomains();
        $domain = substr(strrchr($email, "@"), 1);
        
        if (in_array($domain, $disposableDomains)) {
            return ['valid' => false, 'error' => 'Disposable email addresses are not allowed'];
        }
        
        // Check if email already exists
        $existing = $this->db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            return ['valid' => false, 'error' => 'Email address already registered'];
        }
        
        return ['valid' => true, 'error' => null];
    }
    
    /**
     * Send email using PHPMailer
     */
    public function sendEmail($to, $subject, $body, $isHTML = true, $attachments = []) {
        try {
            // Use PHPMailer if available, otherwise fallback to mail()
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return $this->sendWithPHPMailer($to, $subject, $body, $isHTML, $attachments);
            } else {
                return $this->sendWithMail($to, $subject, $body, $isHTML);
            }
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Send email using PHPMailer
     */
    private function sendWithPHPMailer($to, $subject, $body, $isHTML, $attachments) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = $this->smtp_encryption;
            $mail->Port = $this->smtp_port;
            
            // Recipients
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to);
            
            // Attachments
            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment['path'], $attachment['name']);
            }
            
            // Content
            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            $mail->send();
            
            // Log email
            $this->logEmail($to, $subject, 'sent');
            
            return ['success' => true, 'error' => null];
            
        } catch (Exception $e) {
            $this->logEmail($to, $subject, 'failed', $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Send email using PHP mail() function
     */
    private function sendWithMail($to, $subject, $body, $isHTML) {
        $headers = [
            'From: ' . $this->from_name . ' <' . $this->from_email . '>',
            'Reply-To: ' . $this->from_email,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        if ($isHTML) {
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=UTF-8';
        }
        
        $result = mail($to, $subject, $body, implode("\r\n", $headers));
        
        if ($result) {
            $this->logEmail($to, $subject, 'sent');
            return ['success' => true, 'error' => null];
        } else {
            $this->logEmail($to, $subject, 'failed', 'mail() function failed');
            return ['success' => false, 'error' => 'mail() function failed'];
        }
    }
    
    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail($user) {
        $subject = "Bienvenue sur EMPLOIDB - " . $user['nom'];
        $body = $this->getWelcomeEmailTemplate($user);
        
        return $this->sendEmail($user['email'], $subject, $body);
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($user, $resetToken) {
        $subject = "Réinitialisation de votre mot de passe - EMPLOIDB";
        $body = $this->getPasswordResetEmailTemplate($user, $resetToken);
        
        return $this->sendEmail($user['email'], $subject, $body);
    }
    
    /**
     * Send job application confirmation email
     */
    public function sendJobApplicationEmail($user, $job, $application) {
        $subject = "Confirmation de candidature - " . $job['titre'];
        $body = $this->getJobApplicationEmailTemplate($user, $job, $application);
        
        return $this->sendEmail($user['email'], $subject, $body);
    }
    
    /**
     * Send job alert email
     */
    public function sendJobAlertEmail($user, $jobs) {
        $subject = "Nouvelles offres d'emploi correspondant à vos critères";
        $body = $this->getJobAlertEmailTemplate($user, $jobs);
        
        return $this->sendEmail($user['email'], $subject, $body);
    }
    
    /**
     * Get welcome email template
     */
    private function getWelcomeEmailTemplate($user) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Bienvenue sur EMPLOIDB</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2563eb, #059669); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Bienvenue sur EMPLOIDB !</h1>
                    <p>Votre plateforme d'emploi au Maroc</p>
                </div>
                <div class='content'>
                    <h2>Bonjour " . htmlspecialchars($user['nom']) . ",</h2>
                    <p>Félicitations ! Votre compte EMPLOIDB a été créé avec succès.</p>
                    <p>Vous pouvez maintenant :</p>
                    <ul>
                        <li>Rechercher des emplois correspondant à votre profil</li>
                        <li>Postuler aux offres qui vous intéressent</li>
                        <li>Créer des alertes emploi personnalisées</li>
                        <li>Accéder à nos conseils carrière</li>
                    </ul>
                    <a href='" . ($_ENV['APP_URL'] ?? 'http://localhost') . "/login.php' class='button'>Se connecter</a>
                    <p>Si vous avez des questions, n'hésitez pas à nous contacter.</p>
                </div>
                <div class='footer'>
                    <p>© 2024 EMPLOIDB. Tous droits réservés.</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Get password reset email template
     */
    private function getPasswordResetEmailTemplate($user, $resetToken) {
        $resetUrl = ($_ENV['APP_URL'] ?? 'http://localhost') . "/reset_password.php?token=" . $resetToken;
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Réinitialisation de mot de passe</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2563eb, #059669); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Réinitialisation de mot de passe</h1>
                </div>
                <div class='content'>
                    <h2>Bonjour " . htmlspecialchars($user['nom']) . ",</h2>
                    <p>Vous avez demandé la réinitialisation de votre mot de passe EMPLOIDB.</p>
                    <p>Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
                    <a href='" . $resetUrl . "' class='button'>Réinitialiser mon mot de passe</a>
                    <div class='warning'>
                        <strong>Important :</strong> Ce lien est valide pendant 1 heure seulement. Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.
                    </div>
                    <p>Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :</p>
                    <p style='word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 5px;'>" . $resetUrl . "</p>
                </div>
                <div class='footer'>
                    <p>© 2024 EMPLOIDB. Tous droits réservés.</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Get job application email template
     */
    private function getJobApplicationEmailTemplate($user, $job, $application) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Confirmation de candidature</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2563eb, #059669); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .job-info { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #2563eb; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Candidature confirmée !</h1>
                </div>
                <div class='content'>
                    <h2>Bonjour " . htmlspecialchars($user['nom']) . ",</h2>
                    <p>Votre candidature a été envoyée avec succès.</p>
                    <div class='job-info'>
                        <h3>" . htmlspecialchars($job['titre']) . "</h3>
                        <p><strong>Entreprise :</strong> " . htmlspecialchars($job['company'] ?? 'Non spécifiée') . "</p>
                        <p><strong>Lieu :</strong> " . htmlspecialchars($job['location'] ?? 'Non spécifié') . "</p>
                        <p><strong>Date de candidature :</strong> " . date('d/m/Y H:i') . "</p>
                    </div>
                    <p>L'employeur recevra votre candidature et vous contactera si votre profil correspond à leurs attentes.</p>
                    <p>Vous pouvez suivre l'état de vos candidatures depuis votre espace personnel.</p>
                </div>
                <div class='footer'>
                    <p>© 2024 EMPLOIDB. Tous droits réservés.</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Get job alert email template
     */
    private function getJobAlertEmailTemplate($user, $jobs) {
        $jobList = '';
        foreach ($jobs as $job) {
            $jobList .= "
            <div style='background: white; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #059669;'>
                <h4>" . htmlspecialchars($job['titre']) . "</h4>
                <p><strong>Entreprise :</strong> " . htmlspecialchars($job['company'] ?? 'Non spécifiée') . "</p>
                <p><strong>Lieu :</strong> " . htmlspecialchars($job['location'] ?? 'Non spécifié') . "</p>
                <p><strong>Salaire :</strong> " . ($job['salary_min'] ? number_format($job['salary_min']) . ' - ' . number_format($job['salary_max']) . ' MAD' : 'Non spécifié') . "</p>
            </div>";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Nouvelles offres d'emploi</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2563eb, #059669); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Nouvelles offres d'emploi</h1>
                    <p>" . count($jobs) . " nouvelles offres correspondent à vos critères</p>
                </div>
                <div class='content'>
                    <h2>Bonjour " . htmlspecialchars($user['nom']) . ",</h2>
                    <p>Nous avons trouvé " . count($jobs) . " nouvelles offres d'emploi qui correspondent à vos critères de recherche :</p>
                    " . $jobList . "
                    <p>Connectez-vous à votre compte pour postuler à ces offres.</p>
                </div>
                <div class='footer'>
                    <p>© 2024 EMPLOIDB. Tous droits réservés.</p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Get disposable email domains list
     */
    private function getDisposableEmailDomains() {
        return [
            '10minutemail.com', 'tempmail.org', 'guerrillamail.com', 'mailinator.com',
            'yopmail.com', 'temp-mail.org', 'throwaway.email', 'getnada.com'
        ];
    }
    
    /**
     * Log email activity
     */
    private function logEmail($to, $subject, $status, $error = null) {
        try {
            $this->db->insert(
                "INSERT INTO email_logs (to_email, subject, status, error_message, created_at) VALUES (?, ?, ?, ?, NOW())",
                [$to, $subject, $status, $error]
            );
        } catch (Exception $e) {
            error_log("Failed to log email: " . $e->getMessage());
        }
    }
    
    /**
     * Test email configuration
     */
    public function testEmailConfiguration() {
        $testEmail = $this->smtp_username;
        $subject = "Test de configuration email - EMPLOIDB";
        $body = "<h1>Test de configuration</h1><p>Si vous recevez cet email, la configuration est correcte.</p>";
        
        return $this->sendEmail($testEmail, $subject, $body);
    }
}
?>
