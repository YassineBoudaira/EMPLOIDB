<?php include '../include/session.php'; ?>
<?php include '../include/connexion.php'; ?>
<?php
if(isset($_POST['submit'])){
    $nom = $_POST['nom'];
    $req = $bd->prepare("insert into domaines(nom) values(?)");
    $req->execute([$nom]);
    header('location: /emploiDB/domaine/index.php?msg=added');
}
?>
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
            <h3>Ajouter une domaine</h3>
            <div class="card">
                <div class="card-body">
                    <form method="post" >
                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input type="text" name="nom" id="nom" class="form-control" placeholder=""
                                aria-describedby="nom">
                        </div>
                        <div class="form-group">
                            <label for="nom">Liste des domaine trouver</label>
                            <select id="contrats" name="contrats" class="form-control">
                                <?php 
                                    $req =  $bd->query("select * from domaines");
                                    while($data = $req->fetch()):
                                ?>
                                    <option value="<?= $data['id'] ?>"><?= $data['nom'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <button name="submit" class="btn btn-outline-primary">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        
      </div><!-- row -->
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->

</section>
<?php include '../include/footer.php'; ?>