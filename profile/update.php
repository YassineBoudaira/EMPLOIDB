<?php include '../include/session.php'; ?>
<?php include '../include/connexion.php'; ?>
<?php
// $id= $_GET['id'];
// $req = $bd->prepare('select * from profiles where id=:id');
// $req->execute(['id' => $id]);
// $data = $req->fetch();

$id= $_GET['id'];
$reqa = $bd->prepare('select * from profiles where id=:id');
$reqa->execute(['id' => $id]);
$datap = $reqa->fetch();

if(isset($_POST['submit'])){
  $nom = $_POST['nom'];
  $prenom= $_POST['prenom'];
  $telephone= $_POST['telephone'];
  $adresse= $_POST['adresse'];
  $date_n = $_POST['date_n']; 
  $user_id = $_POST['user_id'];
  $ville_id = $_POST['ville_id'];
  $req = $bd->prepare(" UPDATE profiles SET nom=?,Prenom=?,telephone=?,adresse=?,date_n=?,user_id=?,ville_id=? WHERE id=?");
  $req->execute([$nom,$prenom,$telephone,$adresse,$date_n,$user_id,$ville_id,$id]);
    header('location: /emploiDB/profile/index.php?msg=updated');
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
            <h3>modifier un utilisateur</h3>
            <div class="card">
                <div class="card-body">
                    <form method="post" >
                    <div class="form-group">
                            <label for="nom">nom</label>
                            <input  value="<?= $datap['nom'] ?>" type="text" name="nom" id="nom" class="form-control" placeholder=""
                                aria-describedby="nom">
                        </div>
                        <div class="form-group">
                            <label for="prenom">prenom</label>
                            <input value="<?= $datap['prenom']?>" type="text" name="prenom" id="prenom" class="form-control" placeholder=""
                                aria-describedby="prenom">
                        </div>
                        <div class="form-group">
                            <label for="telephone">telephone</label>
                            <input value="<?= $datap['telephone'] ?>" type="text" name="telephone" id="telephone" class="form-control" placeholder=""
                                aria-describedby="telephone">
                        </div>
                        <div class="form-group">
                            <label for="adresse">adresse</label>
                            <input value="<?= $datap['adresse'] ?>" type="text" name="adresse" id="adresse" class="form-control" placeholder=""
                                aria-describedby="adresse">
                        </div>
                        <div class="form-group">
                            <label for="date_n">date_n</label>
                            <input value="<?= $datap['date_n'] ?>" type="text" name="date_n" id="date_n" class="form-control" placeholder=""
                                aria-describedby="date_n">
                        </div>
                        <div class="form-group">
                            <label for="user_id">type d'utilisation</label>
                            <select id="user_id" name="user_id" class="form-control">
                                <?php 
                                    $req1 =  $bd->query("select * from users");
                                    while($data1 = $req1->fetch()):
                                ?>
                                    <option  <?=($data1['id'] == $id)?'selected':'' ?>  value="<?= $data1['id'] ?>"><?= $data1['user'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ville_id">ville</label>
                            <select id="ville_id" name="ville_id" class="form-control">
                                <?php 
                                    $req2 =  $bd->query("select * from villes");
                                    while($data2= $req2->fetch()):
                                ?>
                                    <option  <?=($data2['id'] == $id)?'selected':'' ?>  value="<?= $data2['id'] ?>"><?= $data2['nom'] ?></option>
                                   <?php endwhile; ?>
                            </select>
    
                        </div>
                        <div class="form-group">
                        <button name="submit" class="btn btn-outline-warning"><a href="/emploidb/profile/add.php">Ajouter un profile</a></button><button name="submit" class="btn btn-outline-primary">Modifier</button>
                       </div>
                    </form> 
                </div>
            </div>
        
      </div><!-- row -->
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->

</section>
<?php include '../include/footer.php'; ?>