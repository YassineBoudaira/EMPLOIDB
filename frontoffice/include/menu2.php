<!-- Navbar Start -->

<nav class="navbar navbar-expand-lg bg-white navbar-light shadow sticky-top p-0">
            <a href="index.php" class="navbar-brand d-flex align-items-center text-center py-0 px-4 px-lg-5">
                <h1 class="m-0 text-primary">JobMaroc.ma</h1>
            </a>
            <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto p-4 p-lg-0">
                    <a href="/emploiDB/index.php" class="nav-item nav-link active">Accueil</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Offres par domaines</a>
                            <div class="dropdown-menu rounded-0 m-0">

                                <!-- <a name="dropdown-item" href="category.php" class="" value="" >Safi </a> -->
                                
                            <?php
                           
                            // $id = $_POST['id'];
                            // $req_id = $db->prepare('select * from domaines where id=:id');
                            // $req_id->execute(['id' => $id]);
                            // $data_id = $req->fetch();
                            

                            $req =  $bd->query("SELECT * from domaines ");
                            while($data = $req->fetch()):
                            ?>
                      
                            <a name="dropdown-item" href="category.php?iddo=<?= $data['id'] ?>" class="dropdown-item" value="<?= $data['id'] ?>" ><?= $data['nom'] ?></a>

                            <?php endwhile;?>
                            </div>

                    </div>
                    <a href="/emploiDB/consiel.php" class="nav-item nav-link active">Consiel Carriere</a>
                    <a href="/emploiDB/cabinets de recrutements.php" class="nav-item nav-link">Cabinet de Recrutement</a>
                    <a href="/emploiDB/about.php" class="nav-item nav-link">About</a>
                    <a href="/emploiDB/contact.php" class="nav-item nav-link">Contact</a>

                    <a href="/emploiDB/signup.php" class="btn btn-primary rounded-0 py-0 m-2 h-25 mt-4">S'inscrire<i class="fa fa-arrow-right ms-3"></i></a>

<a href="/emploiDB/login.php" class="btn btn-primary rounded-0 py-0 m-2 h-25 mt-4">Se connecter<i class="fa fa-arrow-right ms-3"></i></a>

                  <!-- <button type="button" class="btn btn-primary btn btn-success btn-quirk m-100">Se connecter</button>
                  <button type="button" class="btn btn-primary btn btn-success btn-quirk m-100">S'inscrire</button>               -->
                 </div>

            </div>
        </nav>
        <!-- Navbar End -->