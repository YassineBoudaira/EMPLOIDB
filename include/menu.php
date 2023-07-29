<?php 
include 'connexion.php'; ?>
<div class="headerpanel">

    <div class="logopanel">
      <h2><a href="/emploiDB/home.php">JobMaroc</a></h2>
    </div><!-- logopanel -->

    <div class="headerbar">

      <a id="menuToggle" class="menutoggle"><i class="fa fa-bars"></i></a>

      <div class="searchpanel">
        <div class="input-group">
          <!-- <input type="text" class="form-control" placeholder="Search for...">
          <span class="input-group-btn"> -->
            <!-- <button class="btn btn-default" type="button"><i class="fa fa-search"></i></button> -->
          </span>
        </div><!-- input-group -->
      </div>

      <?php


$login=$_SESSION['user_id'];

$req =  $bd->query("select * from profiles where id=$login");
while($data = $req->fetch()):

// $user=$data['id'];
// $s='hhh';

$user = $data['user_id'];
$req1 =  $bd->query("select * from users where id=$user");
$data1 = $req1->fetch(); 

$ville = $data['ville_id'];
$req2 =  $bd->query("select * from villes where id=$ville");
$data2 = $req2->fetch();

?>
      <div class="header-right">
        <ul class="headermenu">
          <li>
            <div class="btn-group">
              <button type="button" class="btn btn-logged" data-toggle="dropdown">
                <img src="assets/images/photos/admin.jpg" alt="" />
              <?= $data['nom'].' '.$data['prenom'] ?>
                <span class="caret"></span>
              </button>
              <ul class="dropdown-menu pull-right">
                <!-- <li><a href="profile.html"><i class="glyphicon glyphicon-user"></i> Mon Profile</a></li>
                <li><a href="#"><i class="glyphicon glyphicon-cog"></i> Parametres de Compte</a></li>
                <li><a href="#"><i class="glyphicon glyphicon-question-sign"></i> Aide</a></li> -->
                <li><a href="/emploiDB/logout.php"><i class="glyphicon glyphicon-log-out"></i> Deconnexion</a></li>
              </ul>
            </div>
          </li>
        </ul>
      </div><!-- header-right -->
    </div><!-- headerbar -->
  </div><!-- header-->
  <?php endwhile; ?>