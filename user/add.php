<?php include '../include/session.php'; ?>
<?php include '../include/connexion.php'; ?>
<?php
if(isset($_POST['submit'])){
    $user= $_POST['user'];
    $pass= md5($_POST['pass']);
    $email= $_POST['email'];
    $role= $_POST['role'];
    $req = $bd->prepare("insert into users(user,pass,email,role) values(?,?,?,?)");
    $req->execute([$user,$pass,$email,$role]);
    header('location: /emploiDB/user/index.php?msg=added');
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
            <h3>Ajouter un utilisateur</h3>
            <div class="card">
                <div class="card-body">
                    <form method="post" >
                        <div class="form-group">
                            <label for="user">utilisateur</label>
                            <input type="text" name="user" id="user" class="form-control" placeholder=""
                                aria-describedby="user">
                        </div>
                        <div class="form-group">
                            <label for="pass">password</label>
                            <input type="text" name="pass" id="pass" class="form-control" placeholder=""
                                aria-describedby="pass">
                        </div>
                        <div class="form-group">
                            <label for="email">email</label>
                            <input type="text" name="email" id="email" class="form-control" placeholder=""
                                aria-describedby="email">
                        </div>
                        <div class="form-group">
                            <label for="newrole">role</label>
                            <input type="text" name="newrole" id="newrole" class="form-control" placeholder=""
                                aria-describedby="newrole">
                        </div>
                        <div class="form-group">
                            <label for="roles">List des types d'utilisateurs</label>
                            <!-- <input type="text" name="nom" id="nom" class="form-control" placeholder=""
                                aria-describedby="nom"> -->
                                <select id="roles" name="roles" class="form-control">
                                <?php 
                                    $req1 =  $bd->query("select * from users");
                                    while($data1 = $req1->fetch()):
                                ?>
                                    <option  <?=($data1['id']) ?>  value="<?= $data1['id'] ?>"><?= $data1['user'] ?></option>
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