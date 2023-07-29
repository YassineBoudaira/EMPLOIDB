<?php include 'frontoffice/include/header2.php'; ?>
<?php 
$annonce_id=$_POST['annonce_id'];
$nome=$_POST['nome'];
$tele=$_POST['telephone'];
$email=$_POST['email'];
$lien=$_POST['lien'];

$cv = basename($_FILES['cv']['name']);
$path= 'fichiers/'.$cv;
$file=$_FILES['cv']['tmp_name'];
move_uploaded_file($file,$path);




$message=$_POST['message'];
$reqva=$bd->prepare("INSERT INTO postulation (annonce_id, nom_prenom, telephone, email, lien, fichier, message ) values(?,?,?,?,?,?,?)");
$reqva->execute([$annonce_id, $nome, $tele, $email, $lien, $cv, $message]);


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