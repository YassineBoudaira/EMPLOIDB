<?php include '../include/session.php'; ?>
<?php include '../include/connexion.php'; ?>
<?php
$id= $_GET['id'];
$reqv = $bd->prepare('select * from villes where id=:id');
$reqv->execute(['id' => $id]);
$datav = $reqv->fetch();

if(isset($_POST['submit'])){
    $nom = $_POST['nom'];
    $req = $bd->prepare("UPDATE villes SET nom=? WHERE  id=?");
    $req->execute([$nom,$id]);
    header('location: /emploiDB/ville/index.php?msg=updated');
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
            <h3>Modifie une la ville</h3>
            <div class="card">
                <div class="card-body">
                    <form method="post" >
                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input value="<?= $datav['nom'] ?>" type="text" name="nom" id="nom" class="form-control" placeholder=""
                                aria-describedby="nom">

                        </div> 
                       <div class="form-group">
                            <label for="ville">List des villes</label>
                            <select id="ville" name="ville" class="form-control">
                                <?php 
                                    $req3 =  $bd->query("select * from villes");
                                    while($data3 = $req3->fetch()):
                                ?>
                                    <option  value="<?= $data3['id'] ?>"><?= $data3['nom'] ?></option>
                                <?php endwhile; ?>
                            </select>

                        </div>
                        <div class="form-group">
                        <button name="submit" class="btn btn-outline-warning"><a href="/emploidb/ville/add.php">Ajouter un ville</a></button> <button name="submit" class="btn btn-outline-warning">Modifier</button>
                        </div>
                    </form>
                </div>
            </div>
        
      </div><!-- row -->
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->

</section>
<?php include '../include/footer.php'; ?>