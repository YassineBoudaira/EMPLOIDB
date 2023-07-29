<?php include '../include/session.php'; ?>
<?php include '../include/connexion.php'; ?>
<?php
$id= $_GET['id'];
$reqd = $bd->prepare("select * from domaines where id=:id");
$reqd->execute(['id' => $id]);
$datad = $reqd->fetch();

if(isset($_POST['submit'])){
    $nom = $_POST['nom'];
    $req = $bd->prepare("UPDATE domaines SET nom=? WHERE  id=?");
    $req->execute([$nom,$id]);
    header('location: /emploiDB/domaine/index.php?msg=updated');
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
            <h3>List des domaines</h3>
            <div class="card">
                <div class="card-body">
                    <form method="post" >
                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input value="<?= $datad['nom'] ?>" type="text" name="nom" id="nom" class="form-control" placeholder=""
                                aria-describedby="nom">

                        </div>
                        <div class="form-group">
                            <label for="nom">List des domaines</label>
                            <select id="domaines" name="domaines" class="form-control">
                                <?php 
                                    $req1 =  $bd->query("select * from domaines");
                                    while($data1 = $req1->fetch()):
                                ?>
                                    <option value="<?= $data1['id'] ?>"><?= $data1['nom'] ?></option>
                                <?php endwhile; ?>
                            </select>

                        </div>
                        <div class="form-group">
                        <button name="submit" class="btn btn-outline-warning"><a href="/emploidb/domaine/add.php">Ajouter un domaine</a></button> <button name="submit" type="submit" class="btn btn-outline-warning" ><a href="/emploidb/domaine/index.php">Afichier Domaines</a></button>
                        </div>
                        </form>

                </div>
            </div>
        
      </div><!-- row -->
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->

</section>
<?php include '../include/footer.php'; ?>