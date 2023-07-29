


<?php include '../include/sess.php'; ?>
<?php include '../include/connexion.php'; ?>
<?php include '../include/header.php'; ?>
<header>
  <!-- Start menu -->
  <?php include '../include/menu.php'; ?>
  <!-- End menu -->
</header>
<section>
  <!-- Start Sidebar -->
  <?php include '../include/sidebar.php'; ?>
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

                                <script type="text/javascript">
		jQuery( document ).ready( function( $ ) {
			var $table3 = jQuery("#table-3");
			
			var table3 = $table3.DataTable( {
				"aLengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]]
			} );
			
			// Initalize Select Dropdown after DataTables is created
			$table3.closest( '.dataTables_wrapper' ).find( 'select' ).select2( {
				minimumResultsForSearch: -1
			});
			
			// Setup - add a text input to each footer cell
			$( '#table-3 tfoot th' ).each( function () {
				var title = $('#table-3 thead th').eq( $(this).index() ).text();
				$(this).html( '<input type="text" class="form-control" placeholder="Search ' + title + '" />' );
			} );
			
			// Apply the search
			table3.columns().every( function () {
				var that = this;
			
				$( 'input', this.footer() ).on( 'keyup change', function () {
					if ( that.search() !== this.value ) {
						that
							.search( this.value )
							.draw();
					}
				} );
			} );
		} );
		</script>

                                    <table class="table table-bordered datatable" id="table-3">
                                        <thead>
                                            <tr class="replace-inputs">
                                                <th><div class="th-content">ID Postulation</div></th>
                                                <th><div class="th-content">Name et Prenom</div></th>
                                                <th><div class="th-content">Telephone personnel</div></th>
                                                <th><div class="th-content">Etreprise</div></th>
                                                <th><div class="th-content">Telephone Entreprise</div></th>
                                                <th><div class="th-content">Annance ID</div></th>
                                                <th><div class="th-content">Date Publication</div></th>
                                                <th><div class="th-content">Action</div></th>

                                                
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                            $req =  $bd->query("SELECT * from postulation");
                                            while( $dataPost = $req->fetch()):


                                              $dataP = $dataPost['annonce_id'];
                                              $reqa =  $bd->query("SELECT * from annonces where id=$dataP");
                                              $dataa = $reqa->fetch();


                                              $profile = $dataa['profile_id'];
                                              $reqP=  $bd->query("select * from profiles where id=$profile");
                                              $dataP = $reqP->fetch();

                                              $contrat = $dataa['contrat_id'];
                                              $reqC =  $bd->query("select * from contrats where id=$contrat");
                                              $dataC = $reqC->fetch();

                                              $ville = $dataa['ville_id'];
                                              $reqV =  $bd->query("select * from villes where id=$ville");
                                              $dataV = $reqV->fetch();

                                              $domaine = $dataa['domaine_id'];
                                              $reqD =  $bd->query("select * from domaines where id=$domaine");
                                              $dataD = $reqD->fetch();
                                          ?>
                                        <tr class="gradeA">
                                                <td><div class="td-content customer-name"><span><?= $dataPost['id'] ?></span></div></td>
                                                <td><div class="td-content product-brand text-primary"><b><?= $dataPost['nom_prenom']?></b></span></div></td>
                                                <td><div class="td-content pricing"><span class=""><?= $dataPost['telephone'] ?></span></div></td>
                                                <td><div class="td-content"><span class="badge badge-success"><?= $dataa['entreprise'] ?></span></div></td>
                                                <td><div class="td-content pricing"><span class=""><?= $dataa['telephone'] ?></span></div></td>
                                                <td><center><div class="td-content pricing"><span class="badge "><?= $dataPost['annonce_id'] ?></span></div></center></td>
                                                <td><div class="td-content product-invoice text-danger"><span class="" ><?= $dataa['date_a'] ?></span></div></td>
                                                <td><a href="/emploiDB/domand/delate.php?id=<?= $data['id'] ?>" class="btn btn-danger btn-sm">
							                        <i class="fa fa-trash"></i></a></td>
                                            </tr>
 
                                        <!-- <tr class="gradeA">
                                                <td><div class="td-content customer-name"><span><?= $data['profile_id'] ?></span></div></td>
                                                <td><div class="td-content product-brand text-primary"><b><?= $dataP['nom'].' '.$dataP['prenom'] ?></b></div></td>
                                                <td><div class="td-content product-invoice"><?= $data['date_a'] ?></div></td>
                                                <td><div class="td-content pricing"><span class=""><?= $data['telephone'] ?></span></div></td>
                                                <td><div class="td-content pricing"><span class=""><?= $dataPost['telephone'] ?></span></div></td>

                                                <td><div class="td-content"><span class="badge badge-success"><?= $data['entreprise'] ?></span></div></td>
                                                <td><div class="td-content pricing"><span class=""><?= $dataPost['annonce_id'] ?></span></div></td>
                                            </tr> -->
                                            
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
<?php include '../include/footer.php'; ?>








