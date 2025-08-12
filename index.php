
<?php 
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';
include 'frontoffice/include/header2.php'; 
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
        <div class="container-fluid p-0">
            <div class="owl-carousel header-carousel position-relative">
                <div class="owl-carousel-item position-relative">
                    <img class="img-fluid" src="frontoffice/assets/img/carousel-1.jpg" alt="">
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: rgba(43, 57, 64, .5);">
                        <div class="container">
                            <div class="row justify-content-start">
                                <div class="col-10 col-lg-8">
                                    <h1 class="display-3 text-white animated slideInDown mb-4">Trouver la meilleur Offre d'emploi, qui vous intéresse</h1>
                                    <p class="fs-5 fw-medium text-white mb-4 pb-2">"Le travail acharné bat le talent quand le talent ne travaille pas dur."</br> -Tim Notke, entraîneur.</p>
                                    <a href="" class="btn btn-primary py-md-3 px-md-5 me-3 animated slideInLeft">Trouver Des Offres</a>
                                    <a href="" class="btn btn-secondary py-md-3 px-md-5 animated slideInRight">Trouver Des Competances</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="owl-carousel-item position-relative">
                    <img class="img-fluid" src="frontoffice/assets/img/carousel-2.jpg" alt="">
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: rgba(43, 57, 64, .5);">
                        <div class="container">
                            <div class="row justify-content-start">
                                <div class="col-10 col-lg-8">
                                    <h1 class="display-3 text-white animated slideInDown mb-4">Find The Perfect Job That You Deserved</h1>
                                    <p class="fs-5 fw-medium text-white mb-4 pb-2">“Hard work beats talent when talent doesn't work hard.” -Tim Notke, coach.</p>
                                    <a href="" class="btn btn-primary py-md-3 px-md-5 me-3 animated slideInLeft">Search A Job</a>
                                    <a href="" class="btn btn-secondary py-md-3 px-md-5 animated slideInRight">Find A Talent</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Carousel End -->


        <!-- Search Start -->
        <div class="container-fluid bg-primary mb-5 wow fadeIn" data-wow-delay="0.1s" style="padding: 35px;">
            <div class="container">
            <form action="filtrage.php" method="GET">
                <div class="row g-2">
                    <div class="col-md-10">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="keyword" class="form-control border-0" placeholder="Mot Clé" />
                            </div>
                            <div class="col-md-4">
                                <select name="idd" class="form-select border-0">
                                    <option value="">Metiers et Domaines</option>
                                    <?php 
                                    // Use secure database queries with prepared statements
                                    $domaines = $db->fetchAll("SELECT * FROM domaines");
                                    foreach($domaines as $datad):
                                        ?>
                                    <option value="<?= htmlspecialchars($datad['id']) ?>"><?= htmlspecialchars($datad['nom']) ?></option>
                                    <?php endforeach;?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="idv" class="form-select border-0">
                                    <option value="">villes</option>
                                    <?php 
                                    // Use secure database queries with prepared statements
                                    $villes = $db->fetchAll("SELECT * FROM villes");
                                    foreach($villes as $datav):
                                    ?>
                                    <option value="<?= htmlspecialchars($datav['id']) ?>"><?= htmlspecialchars($datav['nom']) ?></option>
                                    <?php endforeach;?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-dark border-0 w-100">Recherche</button>
                    </div>
                </div>
           </form>
            </div>
        </div>
        <!-- Search End -->


        <!-- Category Start -->
      
        <!-- Category End -->


        <!-- About Start -->
        
        <!-- About End -->


        <!-- Jobs Start -->

        <div class="container-xxl py-5">
            <div class="container">
                <h1 class="text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">LISTE  D'OFFRES D'EMPLOI AU MAROC</h1>
                <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.3s">
                    
                    <div class="tab-content">
                            <div id="tab-1" class="tab-pane fade show p-0 active">
                                <!-- Start Annonce -->

                                <?php
                                    // Use secure database queries with prepared statements
                                    $annonces = $db->fetchAll("SELECT * FROM annonces ORDER BY id DESC");
                                    foreach($annonces as $data):
                                    
                                        $profile = $data['profile_id'];
                                        $data1 = $db->fetch("SELECT * FROM profiles WHERE id = ?", [$profile]);

                                        $contrat = $data['contrat_id'];
                                        $data2 = $db->fetch("SELECT * FROM contrats WHERE id = ?", [$contrat]);

                                        $ville = $data['ville_id'];
                                        $data3 = $db->fetch("SELECT * FROM villes WHERE id = ?", [$ville]);

                                        $domaine = $data['domaine_id'];
                                        $data4 = $db->fetch("SELECT * FROM domaines WHERE id = ?", [$domaine]);
                                ?>
                                    <div class="job-item p-4 mb-4">
                                        <div class="row g-4">
                                            <div class="col-sm-12 col-md-8 d-flex align-items-center">
                                                <img class="flex-shrink-0 img-fluid border rounded" src="upload/<?= $data['image'] ?>" alt="" style="width: 280px; height: 180px;">
                                                <div class="text-start ps-4">

                                            
                                                    <h5 name="ida" class="mb-3"><i class="fa fa-1x fa-user-tie text-primary mb-4 me-2"></i><?= $data['titre'] ?>  </h5>
                                                <P  > <?= substr($data['description'],0,300)?></P> 
                                                <!-- style="max-width:800px; position: relative;" -->

                                                    <span class="text-truncate me-3"><i class="fa fa-map-marker-alt text-primary me-2"></i> Location: <?=  $data3['nom'] ?></span>
                                                    
                                                    <span class="text-truncate me-3"><i class="far fa-clock text-primary me-2"></i> Contrat: <?= $data2['nom'] ?> </span>
                                                    <span class="text-truncate me-3"><i class="far fa-money-bill-alt text-primary me-2"></i> Salaire: $123 - $456</span>
                                                    <span class="text-truncate me-3"><i class="fa fa-1x fa-user-tie text-primary  me-2"></i> Domaine: <?= $data4['nom'] ?>  </span>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-4 d-flex flex-column align-items-start align-items-md-end justify-content-center">
                                                <div class="d-flex mb-3">
                                                    <a class="btn btn-light btn-square me-3" href=""><i class="far fa-heart text-primary"></i></a>
                                                    <a  href="annoncedetaile.php?ida=<?= $data['id'] ?>" class="btn btn-primary" ><button    class="btn border-none text-white">Afficher les detailes</button></a>
                                                </div>
                                                <small class="text-truncate"><i class="far fa-calendar-alt text-primary me-2"></i> Date Line: <?= $data['date_a'] ?></small>
                                                <small class="text-truncate"><i class="far fa-calendar-alt text-primary me-2"></i> Date Fin: <?= $data['date_fin'] ?></small>
                                            </div>
                                        </div>
                                    </div>
                            <?php
                                endforeach;
                            ?>

                            <!-- End Annonce -->
                            <a class="btn btn-primary py-3 px-5" href="/search.php">Trouver plus d'offres</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Jobs End -->


        <!-- Testimonial Start -->
        
        <!-- Testimonial End -->
        

       
    <?php include 'frontoffice/include/footer2.php'; ?>