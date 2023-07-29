<?php include '../include/session.php'; ?>
<?php include '../include/connexion.php'; ?>
<?php
$id= $_GET['id'];
$reqa = $bd->prepare('select * from annonces where id=:id');
$reqa->execute(['id' => $id]);
$dataa = $reqa->fetch();

if(isset($_POST['submit'])){
  $titre = $_POST['titre'];
  //   $date_a = $_POST['date_a'];
  $date_a =date('Y-m-d', strtotime($_POST['date_a']));

//   $image = basename($_FILES['image']['name']);
//   $path= '../upload'.$image;
//   $file=$_FILES['image']['tmp_name'];
//   move_uploaded_file($file,$path);

  $image = basename($_FILES['image']['name']);
  $path= '../upload/'.$image;
  $file= $_FILES['image']['tmp_name'];
  move_uploaded_file($file,$path);

  $description = $_POST['description'];
  $telephone = $_POST['telephone'];
  $email = $_POST['email'];
  $entreprise = $_POST['entreprise'];
  $entreprise_detaile= $_POST['entreprise_detaile'];
  $siteweb = $_POST['siteweb'];
  $date_fin =date('Y-m-d', strtotime($_POST['date_fin']));

  $profile_id = $_POST['profile_id'];
  $contrat_id = $_POST['contrat_id'];
  $ville_id = $_POST['ville_id'];
  $domaine_id = $_POST['domaine_id'];

    $req = $bd->prepare("UPDATE annonces SET titre=?,date_a=?, image=?, description=?, telephone=?, email=?, entreprise=?, entreprise_detaile=?, siteweb=?, date_fin=?, profile_id=?, contrat_id=?, ville_id=?, domaine_id=? WHERE  id=?");
//   echo '<pre>';
//   var_dump($req);
//   echo '</pre>';
//   die();

    $req->execute([$titre,$date_a,  $image, $description, $telephone, $email, $entreprise,   $entreprise_detaile, $siteweb, $date_fin, $profile_id, $contrat_id, $ville_id, $domaine_id, $id]);
    header('location: /emploiDB/annonce/index.php?msg=updated');

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
            <h3>Modifier une Annonce</h3>
            <div class="card">
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group" >

                                <label for="titre">titre</label>
                            <input value="<?= $dataa['titre'] ?>" type="text" name="titre" id="titre" class="form-control" placeholder=""
                                aria-describedby="titre">
                        </div>
                        <div class="form-group">
                            <label for="date_a">date_a</label>
                            <input value="<?= $dataa['date_a'] ?>" type="date" name="date_a" id="date_a" class="form-control" placeholder=""
                                aria-describedby="date_a"> 
                        </div>
                        <div class="form-group ">
                            <label for="description">Description</label>
                            <textarea value="<?= $dataa['description']?>" type="text" name="description" id="description" class="form-control" placeholder=""
                                aria-describedby="description" rows="3" ><?= $dataa['description']?></textarea>
                        </div>


                        <div class="form-group">
                            <label for="image">image</label>
                            <input type="file"  value="<?=$dataa[3]?>"  name="image" id="image" class="form-control" placeholder=""
                                aria-describedby="image">
                        </div>
                        <div class="form-group">
                            <label for="telephone">telephone</label>
                            <input value="<?= $dataa['telephone'] ?>" type="text" name="telephone" id="telephone" class="form-control" placeholder=""
                                aria-describedby="telephone">
                        </div>
                        <div class="form-group">
                            <label for="email">email</label>
                            <input value="<?= $dataa['email'] ?>" type="text" name="email" id="email" class="form-control" placeholder=""
                                aria-describedby="email">
                        </div>
                        <div class="form-group">
                            <label for="entreprise">entreprise</label>
                            <input value="<?= $dataa['entreprise'] ?>" type="text" name="entreprise" id="entreprise" class="form-control" placeholder=""
                                aria-describedby="entreprise">    
                             </div>
                             <div class="form-group">
                            <label for="entreprise_detaile">detaile d'entreprise</label>
                            <textarea value="<?= $dataa['entreprise_detaile']?>" type="text" name="entreprise_detaile" id="entreprise_detaile" class="form-control" placeholder=""
                                aria-describedby="entreprise_detaile" rows="3" ><?= $dataa['entreprise_detaile'] ?></textarea>

                        </div>
                        <div class="form-group">
                            <label for="siteweb">site web</label>
                            <input value="<?= $dataa['siteweb'] ?>" type="text" name="siteweb" id="siteweb" class="form-control" placeholder=""
                                aria-describedby="siteweb">
                        </div>

                    
                        <div class="form-group">
                            <label for="profile_id">Profile ID</label>
                            <!-- <input value="<?= $dataa['profile_id'] ?>" type="text" name="profile_id" id="profile_id" class="form-control" placeholder=""
                                aria-describedby="profile_id"> -->
                                <select id="profile_id" name="profile_id" class="form-control">
                                <?php 
                                    $req =  $bd->query("select * from profiles");
                                    while($data = $req->fetch()):
                                ?>
                                    <option  <?= ($data['id']==$dataa['profile_id'])?'selected':'' ?> value="<?= $data['id'] ?>"><?= $data['nom'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="contrat_id">Contrat ID</label>
                            <!-- <input value="<?= $data['contrat_id'] ?>" type="text" name="contrat_id" id="contrat_id" class="form-control" placeholder=""
                                aria-describedby="contrat_id"> -->
                                <select id="contrat_id" name="contrat_id" class="form-control">
                                <?php 
                                    $req1 =  $bd->query("select * from contrats");
                                    while($data1 = $req1->fetch()):
                                ?>
                                    <option  <?= ($data1['id']==$dataa['contrat_id'])?'selected':'' ?> value="<?= $data1['id'] ?>"><?= $data1['nom'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ville_id">Ville ID</label>
                            <!-- <input value="<?= $data['ville_id'] ?>" type="text" name="ville_id" id="ville_id" class="form-control" placeholder=""
                                aria-describedby="ville_id"> -->
                                <select id="ville_id" name="ville_id" class="form-control">
                                <?php 
                                    $req2 =  $bd->query("select * from villes");
                                    while($data2 = $req2->fetch()):
                                ?>
                                    <option  <?= ($data2['id']==$dataa['ville_id'])?'selected':'' ?> value="<?= $data2['id'] ?>"><?= $data2['nom'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="domaine_id">Domaine ID</label>
                            <!-- <input value="<?= $data['domaine_id'] ?>" type="text" name="domaine_id" id="domaine_id" class="form-control" placeholder=""
                                aria-describedby="domaine_id"><br> -->
                                <select id="domaine_id" name="domaine_id" class="form-control">
                                <?php 
                                    $req3 =  $bd->query("select * from domaines");
                                    while($data3 = $req3->fetch()):
                                ?>
                                    <option  <?= ($data3['id']==$dataa['domaine_id'])?'selected':'' ?> value="<?= $data3['id'] ?>"><?= $data3['nom'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div> 
                        <div class="form-group">
                            <label for="date_fin">Date fin d'annonce</label>
                            <input value="<?= $dataa['date_fin'] ?>" type="date" name="date_fin" id="date_fin" class="form-control" placeholder=""
                                aria-describedby="date_fin">

                        </div>                         
                        </div>
                        <div class="form-group">
                        <button name="submit" class="btn btn-outline-warning"><a href="/emploidb/annonce/add.php">Ajouter une annance</a></button> <button name="submit" class="btn btn-outline-warning">Modifier</button>
                        </div>
                    </form>
                </div>
            </div>
        
      </div><!-- row -->
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->

</section>
<?php include '../include/footer.php'; ?>