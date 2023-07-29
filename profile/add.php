<?php include '../include/session.php'; ?>

<?php include '../include/connexion.php'; ?>
<?php //if($_SESSION['role']='admin'){ ?>
<?php
if(isset($_POST['submit'])){
  $nom = $_POST['nom'];
  $prenom= $_POST['prenom'];
  $telephone= $_POST['telephone'];
  $adresse= $_POST['adresse'];
//   $date_n = $_POST['date_n']; 
$date_n =date('Y-m-d', strtotime($_POST['date_n']));
  $user_id = $_POST['user_id'];
  $ville_id = $_POST['ville_id'];
  $req = $bd->prepare("insert into profiles (nom,prenom,telephone,adresse,date_n,user_id,ville_id) values(?,?,?,?,?,?,?)");
  $req->execute([$nom,$prenom,$telephone,$adresse,$date_n,$user_id,$ville_id]);
   header('location: /emploiDB/profile/index.php?msg=added');
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
            <h3>Ajouter un profile</h3>
            <div class="card">
                <div class="card-body">
                    <form method="post" >
                    <div class="form-group">
                            <label for="nom">nom</label>
                            <input   type="text" name="nom" id="nom" class="form-control" placeholder=""
                                aria-describedby="nom">
                        </div>
                        <div class="form-group">
                            <label for="prenom">prenom</label>
                            <input  type="text" name="prenom" id="prenom" class="form-control" placeholder=""
                                aria-describedby="prenom">
                        </div>
                        <div class="form-group">
                            <label for="telephone">telephone</label>
                            <input  type="text" name="telephone" id="telephone" class="form-control" placeholder=""
                                aria-describedby="telephone">
                        </div>
                        <div class="form-group">
                            <label for="adresse">adresse</label>
                            <input  type="text" name="adresse" id="adresse" class="form-control" placeholder=""
                                aria-describedby="adresse">
                        </div>
                        <div class="form-group">
                            <label for="date_n">date_n</label>
                            <input  type="date" name="date_n" id="date_n" class="form-control" placeholder=""
                                aria-describedby="date_n">
                        </div>
                        <div class="form-group">
                            <label for="user_id">type d'utilisation</label>
                            <select id="user_id" name="user_id" class="form-control">
                                <?php 
                                    $req1 =  $bd->query("select * from users");
                                    while($data = $req1->fetch()):
                                ?>
                                    <option value="<?= $data['id'] ?>"><?= $data['role'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ville_id">ville</label>
                            <select id="ville_id" name="ville_id" class="form-control">
                                <?php 
                                    $req2 =  $bd->query("select * from villes");
                                    while($data = $req2->fetch()):
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
<?php //} ?>