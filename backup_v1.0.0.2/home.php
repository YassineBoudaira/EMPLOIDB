<?php include 'include/session.php'; ?>
<?php include 'include/connexion.php'; ?>
<?php include 'include/header2.php'; ?>
<header>
  <!-- Start menu -->
  <?php include 'include/menu.php'; ?>
  <!-- End menu -->
</header>
<section>
  <!-- Start Sidebar -->
  <?php include 'include/sidebar.php'; ?>
  <!-- End Sidebar -->
  <div class="mainpanel">
    <!--<div class="pageheader">
      <h2><i class="fa fa-home"></i> Dashboard</h2>
    </div>-->
    <div class="contentpanel">
      <div class="row">

      
     
   
        <!--  BEGIN CONTENT AREA  -->
        <div id="content" class="main-content">
            <div class="layout-px-spacing">

                <div class="row layout-top-spacing">

                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                        <div class="widget widget-table-two">

                            <div class="widget-heading">
                                <h5 class="">Recent Orders</h5>
                            </div>

                            <div class="widget-content">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th><div class="th-content">ID Postulation</div></th>
                                                <th><div class="th-content">Full Name</div></th>
                                                <th><div class="th-content">Date Publication</div></th>
                                                <th><div class="th-content">Telephone</div></th>
                                                <th><div class="th-content">Etreprise</div></th>
                                                <th><div class="th-content">Annance ID</div></th>
                                                
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            $req =  $bd->query("SELECT * from annonces");
                                            while( $data = $req->fetch()):


                                              $dataPost = $data['id'];
                                              $reqPost =  $bd->query("SELECT * from postulation where annonce_id=$dataPost");
                                              $dataPost = $reqPost->fetch();


                                              $profile = $data['profile_id'];
                                              $reqP=  $bd->query("select * from profiles where id=$profile");
                                              $dataP = $reqP->fetch();

                                              $contrat = $data['contrat_id'];
                                              $reqC =  $bd->query("select * from contrats where id=$contrat");
                                              $dataC = $reqC->fetch();

                                              $ville = $data['ville_id'];
                                              $reqV =  $bd->query("select * from villes where id=$ville");
                                              $dataV = $reqV->fetch();

                                              $domaine = $data['domaine_id'];
                                              $reqD =  $bd->query("select * from domaines where id=$domaine");
                                              $dataD = $reqD->fetch();
                                          ?>
                                        <tr>
                                                <td><div class="td-content customer-name"><span><?= $data['profile_id'] ?></span></div></td>
                                                <td><div class="td-content product-brand text-primary"><b><?= $dataP['nom'].' '.$dataP['prenom'] ?></b></div></td>
                                                <td><div class="td-content product-invoice"><?= $data['date_a'] ?></div></td>
                                                <td><div class="td-content pricing"><span class=""><?= $data['telephone'] ?></span></div></td>
                                                <td><div class="td-content"><span class="badge badge-success"><?= $data['entreprise'] ?></span></div></td>
                                                <td><div class="td-content pricing"><span class=""><?= $data['id'] ?></span></div></td>
                                            </tr>
                                            
                                            <!-- <tr>
                                                <td><div class="td-content customer-name">><span>Andy King</span></div></td>
                                                <td><div class="td-content product-brand text-warning">Nike Sport</div></td>
                                                <td><div class="td-content product-invoice">#76894</div></td>
                                                <td><div class="td-content pricing"><span class="">$88.00</span></div></td>
                                                <td><div class="td-content"><span class="badge badge-primary">Shipped</span></div></td>
                                            </tr>
                                            <tr>
                                                <td><div class="td-content customer-name">><span>Laurie Fox</span></div></td>
                                                <td><div class="td-content product-brand text-danger">Sunglasses</div></td>
                                                <td><div class="td-content product-invoice">#66894</div></td>
                                                <td><div class="td-content pricing"><span class="">$126.04</span></div></td>
                                                <td><div class="td-content"><span class="badge badge-success">Paid</span></div></td>
                                            </tr>                                            
                                            <tr>
                                                <td><div class="td-content customer-name">><span>Ryan Collins</span></div></td>
                                                <td><div class="td-content product-brand text-warning">Sport</div></td>
                                                <td><div class="td-content product-invoice">#89891</div></td>
                                                <td><div class="td-content pricing"><span class="">$108.09</span></div></td>
                                                <td><div class="td-content"><span class="badge badge-primary">Shipped</span></div></td>
                                            </tr>
                                            <tr>
                                                <td><div class="td-content customer-name">><span>Irene Collins</span></div></td>
                                                <td><div class="td-content product-brand text-primary">Speakers</div></td>
                                                <td><div class="td-content product-invoice">#75844</div></td>
                                                <td><div class="td-content pricing"><span class="">$84.00</span></div></td>
                                                <td><div class="td-content"><span class="badge badge-danger">Pending</span></div></td>
                                            </tr>
                                            <tr>
                                                <td><div class="td-content customer-name">><span>Sonia Shaw</span></div></td>
                                                <td><div class="td-content product-brand text-danger">Watch</div></td>
                                                <td><div class="td-content product-invoice">#76844</div></td>
                                                <td><div class="td-content pricing"><span class="">$110.00</span></div></td>
                                                <td><div class="td-content"><span class="badge badge-success">Paid</span></div></td>
                                            </tr> -->
                                        </tbody>
                                        <?php endwhile; ?>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
           
        </div>
        <!--  END CONTENT AREA  -->


    </div>
    <!-- END MAIN CONTAINER -->

    </div><!-- row -->
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->







</section>
<?php include 'include/footer2.php'; ?>