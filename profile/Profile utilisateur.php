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
		<h3>Liste des profiles</h3>
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
		
		<table class="table table-bordered datatable" id="table-10">
            <thead>
		     	<tr class="replace-inputs">
				     <th>#</th>
					 <th>Nom</th>
					 <th>Prenom</th>
				 	<th>telephone</th>
					<th>date naissance</th>
					<th>adresse</th>
					<th>user id</th>
					<th>ville id</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$req =  $bd->query("select * from profiles");
					while($data = $req->fetch()):

						$user = $data['user_id'];
						$req1 =  $bd->query("select * from users where id=$user");
						$data1 = $req1->fetch(); 

						$ville = $data['ville_id'];
						$req2 =  $bd->query("select * from villes where id=$ville");
						$data2 = $req2->fetch();
				?>
			
				<tr class="gradeA">
				    <td><?= $data['id'] ?></td>
					<td><?= $data['nom'] ?></td>
					<td><?= $data['prenom'] ?></td>
					<td><?= $data['telephone'] ?></td>
					<td><?= $data['date_n'] ?></td>
					<td><?= $data['adresse'] ?></td>
					<td><?= $data1['user'] ?></td>
					<td><?= $data2['nom'] ?></td>
					<td>
						<a href="/emploiDB/profile/update.php?id=<?= $data['id'] ?>" class="btn btn-warning btn-sm">
							<i class="fa fa-edit"></i>
						</a>
						<a href="/emploiDB/profile/delete.php?id=<?= $data['id'] ?>" class="btn btn-danger btn-sm">
							<i class="fa fa-trash"></i>
						</a>
					</td>
				</tr>
				<?php
					endwhile;
				?>
			</tbody>
			<tfoot>
				<tr>
				<th>#</th>
					 <th>Nom</th>
					 <th>Prenom</th>
				 	<th>telephone</th>
					<th>date naissance</th>
					<th>adresse</th>
					<th>user id</th>
					<th>ville id</th>
					<th>Action</th>
				</tr>
			</tfoot>
		</table>
		</div><!-- row -->
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->

</section>
<?php include '../include/footer.php'; ?>