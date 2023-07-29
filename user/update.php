<?php include '../include/session.php'; ?>
<?php include '../include/connexion.php'; ?>
<?php
// $id= $_GET['id'];
// $req = $bd->prepare('select * from users where id=:id');
// $req->execute(['id' => $id]);
// $dataa = $req->fetch();

$id= $_GET['id'];
$reqa = $bd->prepare('select * from users where id=:id');
$reqa->execute(['id' => $id]);
$dataa = $reqa->fetch();

if(isset($_POST['submit'])){
    $user = $_POST['user'];
    $pass= md5($_POST['pass']);
    $email= $_POST['email'];
    $role= $_POST['role'];
    $req = $bd->prepare("UPDATE users SET user=?,pass=?,email=?,role=? WHERE id=?");
    $req->execute([$user,$pass,$email,$role,$id]);
    header('location: /emploiDB/user/index.php?msg=updated');
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
                            <label for="user">Nom</label>
                            <input value="<?= $dataa['user'] ?>" type="text" name="user" id="user" class="form-control" placeholder=""
                                aria-describedby="user">
                        </div>
                        <div class="form-group">
                            <label for="pass">password</label>
                            <input value="<?= $dataa['pass'] ?>" type="text" name="pass" id="pass" class="form-control" placeholder=""
                                aria-describedby="pass">
                        </div>
                        <div class="form-group">
                            <label for="email">email</label>
                            <input value="<?= $dataa['email'] ?>" type="text" name="email" id="email" class="form-control" placeholder=""
                                aria-describedby="email">
                        </div>
                        <div class="form-group">

                        <label for="role">role</label>
                            <input value="<?= $dataa['role'] ?>" type="text" name="role" id="role" class="form-control" placeholder=""
                                aria-describedby="role">
                                </div>

                        <div class="form-group">
                            <label for="userss">Type d'utilisqteurs</label>
                            <select id="userss" name="userss" class="form-control">
                                <?php 
                                    $req2 =  $bd->query("select * from users");
                                    while($data2= $req2->fetch()):
                                ?>
                                    <option   value="<?= $data2['id'] ?>"><?= $data2['role'] ?></option>
                                   <?php endwhile; ?>
                            </select>

                        </div>
                        <div class="form-group">
                        <button name="submit" class="btn btn-outline-warning"><a href="/emploidb/user/add.php">Ajouter un type utilisateur</a></button> <button name="submit" class="btn btn-outline-warning"><a>Modifier</a></button>
                        </div>
                    </form>
                </div>
            </div>
        
      </div><!-- row -->
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->

</section>
<?php include '../include/footer.php'; ?>