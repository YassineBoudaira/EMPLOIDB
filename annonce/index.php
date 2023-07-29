<?php include '../include/session.php'; ?>
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
			<div class="col-md-12">
			<?php if(isset($_GET['msg']) && $_GET['msg']=='added'): ?>
                <div class="alert alert-success">Ajouter avec succes
                    <span data-dismiss="alert" class="close">&times;</span>
                </div>
                <?php endif; ?>
                <?php if(isset($_GET['msg']) && $_GET['msg']=='deleted'): ?>
                <div class="alert alert-danger">Supprimer avec succes
                    <span data-dismiss="alert" class="close">&times;</span>
                </div>
                <?php endif; ?>
                <?php if(isset($_GET['msg']) && $_GET['msg']=='updated'): ?>
                <div class="alert alert-warning">modifier avec succes
                    <span data-dismiss="alert" class="close">&times;</span>
                </div>
                <?php endif; ?>

			</div>
		</div>
		<div class="row">
		<h3>Liste des annonces</h3>
		<br />
		
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
				     <th>#</th>
					 <th>titre</th>
					 <th>date d'annonce</th>
					 <th>description</th>
					<th>image</th>
					<th>telephone</th>
					<th>email</th>
					<th>etreprise</th>
					<th>detaile d'entreprise</th>
					<th>site web</th>
					<th>profile</th>
					<th>contrat</th>
					<th>villes</th>
					<th>domaine</th>
					<th>date fin</th>

					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<?php

					// $req=$bd->query("SELECT a.*,p.nom,c.nom,v.nom,d.nom FROM annonces a,profiles p, contrats c,villes  v,domaines d
					// WHERE a.profile_id=p.id AND a.contrat_id=c.id AND a.ville_id=v.id AND a.domaine_id=d.id");
					// while($data=$req->fetch()):
						//  $data['id'] ;
						// $data['titre'] ;
					 	// $data['date_a'] ;
						// $data['description'] ;
						//  $data['telephone'] ;
						//  $data['email'] ;
						//  $data['nom'].' '.$data['prenom'];
						// $data['profile_id'] ;
						// $data['contrat_id'] ;
						// $data['ville_id'] ;
						// $data['domaine_id'] ;
						// <td><?= $data['nomp'].' '.$data['prenom'].' ID='.$data['id'] ;


					$req =  $bd->query("SELECT * from annonces");
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
			
				<tr class="gradeA">
					<td><?= $data['id'] ?></td>
					<td><?= $data['titre'] ?></td>
					<td><?= $data['date_a'] ?></td>
					<td><?= substr($data['description'],0,50) ?></td>
					<td><img width="80" src="../upload/<?= $data['image'] ?>" alt="pas image"></td>
					<td><?= $data['telephone'] ?></td>
					<td><?= $data['email'] ?></td>
					<td><?= $data['entreprise'] ?></td>
					<td><?= $data['entreprise_detaile'] ?></td>
					<td><?= $data['siteweb'] ?></td>
                  	<td><?=$data1['nom'].' '.$data1['prenom'] ?></td>
					<td><?= $data2['nom'] ?></td>
					<td><?= $data3['nom'] ?></td>
					<td><?= $data4['nom'] ?></td>
					<td><div class="td-content product-brand text-primary"><?= $data['date_fin'] ?></div></td>

					<td>
						<a href="/emploiDB/annonce/update.php?id=<?= $data['id'] ?>" class="btn btn-warning btn-sm">
							<i class="fa fa-edit"></i>
						</a>
						<a href="/emploiDB/annonce/delete.php?id=<?= $data['id'] ?>" class="btn btn-danger btn-sm">
							<i class="fa fa-trash"></i>
						</a>
					</td>
				</tr>
				<?php
					endwhile;
				?>
			</tbody>

		</table>
		</div><!-- row -->
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->

</section>
<?php include '../include/footer.php'; ?>