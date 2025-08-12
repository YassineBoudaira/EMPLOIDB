<?php include 'frontoffice/include/header2.php'; ?>

<?php 
// Secure input validation
$annonce_id = Security::sanitizeInput($_POST['annonce_id'] ?? '', 'int');
$nome = Security::sanitizeInput($_POST['nome'] ?? '', 'string');
$tele = Security::sanitizeInput($_POST['telephone'] ?? '', 'string');
$email = Security::sanitizeInput($_POST['email'] ?? '', 'email');
$lien = Security::sanitizeInput($_POST['lien'] ?? '', 'url');
$message = Security::sanitizeInput($_POST['message'] ?? '', 'string');

// Validate required fields
if (!$annonce_id || !$nome || !$tele || !$email || !$message) {
    Security::redirect('index.php', 'Please fill all required fields', 'error');
}

// Validate email
if (!Security::validateEmail($email)) {
    Security::redirect('index.php', 'Please enter a valid email address', 'error');
}

// Secure file upload validation
$cv = '';
if (isset($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
    if (Security::validateFileUpload($_FILES['cv'], ['pdf', 'doc', 'docx'], 10485760)) { // 10MB max
        $cv = Security::generateSecureFilename($_FILES['cv']['name']);
        $path = 'fichiers/' . $cv;
        
        if (!move_uploaded_file($_FILES['cv']['tmp_name'], $path)) {
            Security::redirect('index.php', 'File upload failed', 'error');
        }
    } else {
        Security::redirect('index.php', 'Invalid file type or size', 'error');
    }
}

// Insert data securely
try {
    $db->insert("INSERT INTO postulation (annonce_id, nom_prenom, telephone, email, lien, fichier, message) VALUES (?, ?, ?, ?, ?, ?, ?)", 
                [$annonce_id, $nome, $tele, $email, $lien, $cv, $message]);
    $success = true;
} catch (Exception $e) {
    Security::redirect('index.php', 'Application submission failed', 'error');
}


?>

<div class="container-fluid bg-white p-0">
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->


      <!-- Navbar End -->
      <?php include 'frontoffice/include/menu2.php'; ?>
    <!-- Header End -->


    <!-- Carousel Start -->
   
    <!-- Carousel End -->


    <!-- Search Start -->
    <center><div class="container-xxl bg-primary mb- wow fadeIn" data-wow-delay="0.1s" style="padding: 10px;">
    <h1 class="h1" >Bien Envoyer </h1>
    </div>

<div class="container"></br></br>


<h6><strong>vous avaier passe avec succes</strang> </h6><br></center>



</div>

</div>




<?php include 'frontoffice/include/footer2.php'; ?>