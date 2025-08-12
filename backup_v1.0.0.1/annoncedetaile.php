<?php include 'frontoffice/include/header2.php'; ?>

<?php 

$ann = $_GET['ida'] ;






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


        <!-- Header End -->
        <div class="container-fluid py-5 bg-dark page-header mb-5">
            <div class="container my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3 animated slideInDown">Détail d'offre d'emploi</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb text-uppercase">
                        <li class="breadcrumb-item"><a href="index.php">Acueil</a></li>
                        <li class="breadcrumb-item"><a href="#">Domaines</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">Détail d'offre d'emploi</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Header End -->
        <?php 
        $req=$bd->query("SELECT * FROM annonces where id= $ann");
        $data= $req->fetch();

                                            

        $profile = $data['profile_id'];
        $req1=  $bd->query("select * from profiles where id=$profile");
        $data1 = $req1->fetch();

        $contrat = $data['contrat_id'];
        $req2 =  $bd->query("select * from contrats where id=$contrat");
        $data2 = $req2->fetch();

        $ville = $data['ville_id'];
        $req3 =  $bd->query("select * from villes where id=$ville");
        $data3 = $req3->fetch();

        $domaine = $data['domaine_id'];
        $req4 =  $bd->query("select * from domaines where id=$domaine");
        $data4 = $req4->fetch();
        
        ?>

        <!-- Job Detail Start -->
        <div class="container-xxl py-5 wow fadeInUp" data-wow-delay="0.1s">
            <div class="container">
                <div class="row gy-5 gx-4">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center mb-5">
                            <img class="flex-shrink-0 img-fluid border rounded" src="upload/<?= $data['image'] ?>" alt="" style="width: 80px; height: 80px;">
                            <div class="text-start ps-4">
                                <h3 class="mb-3"><?= $data['titre'] ?> </h3>
                                <span class="text-truncate me-3"><i class="fa fa-map-marker-alt text-primary me-2"></i><?= $data3['nom'] ?></span>
                                <span class="text-truncate me-3"><i class="far fa-clock text-primary me-2"></i><?= $data2['nom'] ?></span>
                                <span class="text-truncate me-3"><i class="far fa-money-bill-alt text-primary me-2"></i>$123 - $456</span>
                                <span class="text-truncate me-3"><i class="fa fa-1x fa-user-tie text-primary  me-2"></i> <?= $data4['nom'] ?>   </span>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h4 class="mb-3">Offre description</h4>
                            <p><?= $data['description'] ?></p>
                            <h4 class="mb-3">Responsibilités</h4>
                            <p><?= $data['description'] ?></p>
                            <ul class="list-unstyled">
                                <li><i class="fa fa-angle-right text-primary me-2"></i><?= substr($data['description'],0,70) ?></li>
                                <li><i class="fa fa-angle-right text-primary me-2"></i><?= substr($data['description'],71,141) ?></li>
                                <li><i class="fa fa-angle-right text-primary me-2"></i><?= substr($data['description'],142,213) ?>r</li>
                                <li><i class="fa fa-angle-right text-primary me-2"></i><?= substr($data['description'],214,300) ?></li>
                                <li><i class="fa fa-angle-right text-primary me-2"></i><?= substr($data['description'],301,371) ?></li>
                            </ul>
                            <h4 class="mb-3">Qualifications</h4>
                            <p>Magna et elitr diam sed lorem. Diam diam stet erat no est est. Accusam sed lorem stet voluptua sit sit at stet consetetur, takimata at diam kasd gubergren elitr dolor</p>
                            <ul class="list-unstyled">
                                <li><i class="fa fa-angle-right text-primary me-2"></i>Dolor justo tempor duo ipsum accusam</li>
                                <li><i class="fa fa-angle-right text-primary me-2"></i>Elitr stet dolor vero clita labore gubergren</li>
                                <li><i class="fa fa-angle-right text-primary me-2"></i>Rebum vero dolores dolores elitr</li>
                                <li><i class="fa fa-angle-right text-primary me-2"></i>Est voluptua et sanctus at sanctus erat</li>
                                <li><i class="fa fa-angle-right text-primary me-2"></i>Diam diam stet erat no est est</li>
                            </ul>
                        </div>
        
                        <div class="">
                            <h4 class="mb-4">Postuler à ce poste</h4>
                            <form action="validation.php" method="post" enctype="multipart/form-data" >
                                <input type="hidden" name="annonce_id" value="<?= $ann ?>">
                                <div class="row g-3">
                                    <div class="col-12 col-sm-12">
                                        <input name="nome" type="text" class="form-control" placeholder="Votre Nome et Prenom">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <input name="telephone" type="text" class="form-control" placeholder="Votre Telephone">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <input name="email" type="email" class="form-control" placeholder="Votre Email">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <input name="lien" type="text" class="form-control" placeholder="Portfolio ou Linkden Profile">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <input name="cv" type="file" class="form-control bg-white" placeholder="CV">
                                    </div>
                                    <div class="col-12">
                                        <textarea name="message" class="form-control" rows="5" placeholder="Message"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button name="sub" class="btn btn-primary w-100" type="submit" ?>>Appliquer cette Offre</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
        
                    <div class="col-lg-4">
                        <div class="bg-light rounded p-5 mb-4 wow slideInUp" data-wow-delay="0.1s">
                            <h4 class="mb-4">Résumé du Post</h4>
                            <p><i class="fa fa-angle-right text-primary me-2"></i>Publié a: <?= $data['date_a'] ?></p>
                            <p><i class="fa fa-angle-right text-primary me-2"></i>poste vacant: 123 Position</p>
                            <p><i class="fa fa-angle-right text-primary me-2"></i>Post Nature: <?= $data2['nom'] ?></p>
                            <p><i class="fa fa-angle-right text-primary me-2"></i>Salaire: $123 - $456</p>
                            <p><i class="fa fa-angle-right text-primary me-2"></i>Emplacement: <?= $data3['nom'] ?></p>
                            <p class="m-0"><i class="fa fa-angle-right text-primary me-2"></i>Date de fin d'offre: <?= $data['date_a'] ?></p>
                        </div>
                        <div class="bg-light rounded p-5 wow slideInUp" data-wow-delay="0.1s">
                            <h4 class="mb-4">Détails de l'entreprise</h4>
                            <p class="m-0"> <?= $data['entreprise_detaile'] ?> </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Job Detail End -->


<?php include 'frontoffice/include/footer2.php'; ?>
 