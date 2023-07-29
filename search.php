
<?php include 'frontoffice/include/header2.php'; ?>

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
        <div class="container-fluid bg-primary mb-5 wow fadeIn" data-wow-delay="0.1s" style="padding: 35px;">
            <div class="container">
            <form action="filtrage.php"   >
                <div class="row g-2">
                    <div class="col-md-10">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <input type="text" class="form-control border-0" placeholder="Mot Clé" />
                                <!-- <label for="cars" >Mot Clé:</label>
                                    <select name="cars" id="cars">
                                    <option value="volvo"  selected >voiture</option>
                                    <option value="volvo">Volvo</option>
                                    <option value="saab">Saab</option>
                                    <option value="mercedes">Mercedes</option>
                                    <option value="audi">Audi</option>
                                    </select> -->
                            </div>
                            

                                <?php 
                                // $id= $_GET['id'];
                                // $req = $bd->query("SELECT * from annonces where id=:id");
                                // $data = $req->fetch();
                                ?>
                                    <div class="col-md-4">
                                        <select name="idd"  class="form-select border-0">



                                        
                                            <option selected > Metiers et Domaines </option>
                                            <?php 
                                        
                                        $reqd =  $bd->query("SELECT * from domaines");
                                            while($datad = $reqd->fetch()):
                                                ?>
                                            <option  value="<?=  $vard= $datad['id'] ?>" ><?= $datad['nom'] ?></option>
                                            <!-- <option value="2">Category 2</option>
                                            <option value="3">Category 3</option> -->
                                            <?php endwhile;?>
                                        </select>
                                    </div>


                                    <div class="col-md-4">
                                        <select name="idv" class="form-select border-0">


                                        
                                            <option selected > villes </option>
                                            <?php 
                                            
                                            $reqv =  $bd->query("SELECT * from villes");
                                            while($datav = $reqv->fetch()):
                                            ?>
                                            <option value="<?= $varv= $datav['id'] ?>" ><?= $datav['nom'] ?></option>
                                            <!-- <option value="2">Category 2</option>
                                            <option value="3">Category 3</option> -->
                                            <?php endwhile;?>
                                        

                                            <!-- <option selected>Location</option>
                                            <option value="1">Location 1</option>
                                            <option value="2">Location 2</option>
                                            <option value="3">Location 3</option> -->
                                        </select>
                                    </div>

                        </div>
                        </form>
                    </div>
                    <!-- <div class="col-md-2">
                       <button  class="btn-search w-100" > Search</button>
                    </div> -->
                    <div class="col-md-2">
                        <button class="btn btn-dark border-0 w-100">Recherche</button>
                    </div>
                    
                </div>
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
                <h1 class="text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">Liste d'Offres</h1>
                <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.3s">
                    
                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane fade show p-0 active">
                            <!-- Start Annonce -->

                            <?php
					$req =  $bd->query("SELECT * from annonces ORDER BY id DESC ");
					while($data = $req->fetch()):
					

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
                            <div class="job-item p-4 mb-4">
                                <div class="row g-4">
                                    <div class="col-sm-12 col-md-8 d-flex align-items-center">
                                        <img class="flex-shrink-0 img-fluid border rounded" src="upload/<?= $data['image'] ?>" alt="" style="width: 280px; height: 180px;">
                                        <div class="text-start ps-4">

                                       
                                            <h5 class="mb-3"><i class="fa fa-1x fa-user-tie text-primary mb-4 me-2"></i><?= $data['titre'] ?>  </h5>
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
                                            <a class="btn btn-primary" href="annoncedetaile.php?ida=<?= $data['id'] ?>">Afficher les detailes</a>
                                        </div>
                                        <small class="text-truncate"><i class="far fa-calendar-alt text-primary me-2"></i> Date Line: <?= $data['date_a'] ?></small>
                                        <small class="text-truncate"><i class="far fa-calendar-alt text-primary me-2"></i> Date Fin: <?= $data['date_a'] ?></small>
                                    </div>

                                </div>
                            </div>
                            <?php
                                endwhile;
                            ?>
                            <!-- End Annonce -->
                            <a class="btn btn-primary py-3 px-5" href="/emploiDB/search.php">Browse More Jobs</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Testimonial Start -->
        
        <!-- Testimonial End -->
        

        
    </div>

    <?php include 'frontoffice/include/footer2.php'; ?>