<?php 
include  'connexion.php'
?>
<div class="leftpanel">
    <div class="leftpanelinner">

      <!-- ################## LEFT PANEL PROFILE ################## -->
      <form action=""   >
        <div class="media leftpanel-profile">
          <div class="media-left">
            <a href="/emploiDB/home.php">
              <img src="assets/images/photos/admin.jpg" alt="" class="media-object img-circle">
            </a>
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
          <div class="media-body">
            <h4 class="media-heading"><?= $data['nom'] ?><a data-toggle="collapse" data-target="#loguserinfo" class="pull-right"><i class="fa fa-angle-down"></i></a></h4>
            <span><?=$data1['role']?></span>
          </div>
        </div><!-- leftpanel-profile -->

        <div class="leftpanel-userinfo collapse" id="loguserinfo">
          <h5 class="sidebar-title">Address</h5>
          <address>
          <?=$data['adresse']?>
          </address>
          <h5 class="sidebar-title">Contact</h5>
          <ul class="list-group">
            <li class="list-group-item">
              <label class="pull-left">Email</label>
              <span class="pull-right"><?=$data1['email']?></span>
            </li>
            <li class="list-group-item">
              <label class="pull-left">Ville</label>
              <span class="pull-right"><?=$data2['nom']?></span>
            </li>
            <li class="list-group-item">
              <label class="pull-left">Mobile</label>
              <span class="pull-right">+(212) <?=$data['telephone']?></span>
            </li>
            <li class="list-group-item">
              <label class="pull-left">Social</label>
              <div class="social-icons pull-right">
                <a href="#"><i class="fa fa-facebook-official"></i></a>
                <a href="#"><i class="fa fa-twitter"></i></a>
                <a href="#"><i class="fa fa-pinterest"></i></a>
              </div>
        <?php endwhile; ?>

            </li>
          </ul>

        </div><!-- leftpanel-userinfo -->

        <ul class="nav nav-tabs nav-justified nav-sidebar">
          <li class="tooltips active" data-toggle="tooltip" title="Main Menu"><a data-toggle="tab" data-target="#mainmenu"><i class="tooltips fa fa-ellipsis-h"></i></a></li>
        </ul>
        
      </form>
      <div class="tab-content">

        <!-- ################# MAIN MENU ################### -->
         <!-- ########################Adminstration##################### -->

         <?php if ($_SESSION['role']=='admin' ||  $_SESSION['role']=='user'):?>


        <div class="tab-pane active" id="mainmenu">
        <?php if ($_SESSION['role']=='admin'){?>

          <!-- <h5 class="sidebar-title">Actialités</h5> -->
          <ul class="nav nav-pills nav-stacked nav-quirk">
            <li class="active"><a href="/emploiDB/home.php"><i class="fa fa-home"></i> <span>Dashboard</span></a></li>

          <?php } ?>

          <ul class="nav nav-pills nav-stacked nav-quirk">
             <!-- ########################User##################### -->
           <!-- <h5 class="sidebar-title">---------------------------------------------</h5> -->
          <ul class="nav nav-pills nav-stacked nav-quirk">
            <!-- <li class=""><a href="/emploiDB/Profile/profile utilisateur.php"><i class="fa fa-home"></i> <span>Mon Profile</span></a></li> -->

            <li><a href="/emploiDB/domend/domandes.php"><span class="text text-md btn-success btn-danger  pull-right ">+++</span><i class="fa fa-cube"></i> <span>Domandes</span></a></li>
          </ul>
          <h5 class="sidebar-title">Paramétre Menu</h5>

          <?php if ($_SESSION['role']=='admin'){?>

            <li class="nav-parent">
              <a href="#"><i class="fa fa-check-square"></i> <span>profiles</span></a>
              <ul class="children">
                <li><a href="/emploiDB/profile/index.php">Liste des profiles</a></li>
                <li><a href="/emploiDB/profile/add.php">Ajouter une profile</a></li>
              </ul>
            </li>
            <?php } ?>

            <li class="nav-parent">
              <a href="#"><i class="fa fa-check-square"></i> <span>annonces</span></a>
              <ul class="children">
                <li><a href="/emploiDB/annonce/index.php">Liste des annonces</a></li>
                <li><a href="/emploiDB/annonce/add.php">Ajouter une annonce</a></li>
              </ul>
            </li> 
            
            <?php if ($_SESSION['role']=='admin' ):?>

            <li class="nav-parent">
              <a href="#"><i class="fa fa-check-square"></i> <span>domaines</span></a>
              <ul class="children">
                <li><a href="/emploiDB/domaine/index.php">Liste des domaines</a></li>
                <li><a href="/emploiDB/domaine/add.php">Ajouter une domaine</a></li>
              </ul>
            </li>
             <!-- ########################me##################### -->
            <li class="nav-parent">
              <a href="#"><i class="fa fa-check-square"></i> <span>villes</span></a>
              <ul class="children">
                <li><a href="/emploiDB/ville/index.php">Liste des villes</a></li>
                <li><a href="/emploiDB/ville/add.php">Ajouter une ville</a></li>
              </ul>
            </li>
            <li class="nav-parent">
              <a href="#"><i class="fa fa-check-square"></i> <span>contrats</span></a>
              <ul class="children">
                <li><a href="/emploiDB/contrat/index.php">Liste des contrats</a></li>
                <li><a href="/emploiDB/contrat/add.php">Ajouter une contrat</a></li>
              </ul>
            </li>
            <li class="nav-parent">
              <a href="#"><i class="fa fa-check-square"></i> <span>utilisateurs</span></a>
              <ul class="children">
                <li><a href="/emploiDB/user/index.php">Liste des utilisateurs</a></li>
                <li><a href="/emploiDB/user/add.php">Ajouter un utilisateur</a></li>
              </ul>
            </li>
            <?php  endif;?>

          </ul>

          
        </div><!-- tab-pane -->
        <?php endif; ?>
        <!-- ######################## EMAIL MENU ##################### -->

        <div class="tab-pane" id="emailmenu">
          <div class="sidebar-btn-wrapper">
            <a href="compose.html" class="btn btn-danger btn-block">Compose</a>
          </div>

          <h5 class="sidebar-title">Mailboxes</h5>
          <ul class="nav nav-pills nav-stacked nav-quirk nav-mail">
            <li><a href="email.html"><i class="fa fa-inbox"></i> <span>Inbox (3)</span></a></li>
            <li><a href="email.html"><i class="fa fa-pencil"></i> <span>Draft (2)</span></a></li>
            <li><a href="email.html"><i class="fa fa-paper-plane"></i> <span>Sent</span></a></li>
          </ul>

          <h5 class="sidebar-title">Tags</h5>
          <ul class="nav nav-pills nav-stacked nav-quirk nav-label">
            <li><a href="#"><i class="fa fa-tags primary"></i> <span>Communication</span></a></li>
            <li><a href="#"><i class="fa fa-tags success"></i> <span>Updates</span></a></li>
            <li><a href="#"><i class="fa fa-tags warning"></i> <span>Promotions</span></a></li>
            <li><a href="#"><i class="fa fa-tags danger"></i> <span>Social</span></a></li>
          </ul>
        </div><!-- tab-pane -->

        <!-- ################### CONTACT LIST ################### -->

        <div class="tab-pane" id="contactmenu">
          <div class="input-group input-search-contact">
            <input type="text" class="form-control" placeholder="Search contact">
            <span class="input-group-btn">
              <button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>
            </span>
          </div>
          <h5 class="sidebar-title">My Contacts</h5>
          <ul class="media-list media-list-contacts">
            <li class="media">
              <a href="#">
                <div class="media-left">
                    <img class="media-object img-circle" src="assets/images/photos/user1.png" alt="">
                </div>
                <div class="media-body">
                  <h4 class="media-heading">Christina R. Hill</h4>
                  <span><i class="fa fa-phone"></i> 386-752-1860</span>
                </div>
              </a>
            </li>
            <li class="media">
              <a href="#">
                <div class="media-left">
                  <img class="media-object img-circle" src="assets/images/photos/user2.png" alt="">
                </div>
                <div class="media-body">
                  <h4 class="media-heading">Floyd M. Romero</h4>
                  <span><i class="fa fa-mobile"></i> +1614-650-8281</span>
                </div>
              </a>
            </li>
            <li class="media">
              <a href="#">
                <div class="media-left">
                  <img class="media-object img-circle" src="assets/images/photos/user3.png" alt="">
                </div>
                <div class="media-body">
                  <h4 class="media-heading">Jennie S. Gray</h4>
                  <span><i class="fa fa-phone"></i> 310-757-8444</span>
                </div>
              </a>
            </li>
            <li class="media">
              <a href="#">
                <div class="media-left">
                  <img class="media-object img-circle" src="assets/images/photos/user4.png" alt="">
                </div>
                <div class="media-body">
                  <h4 class="media-heading">Alia J. Locher</h4>
                  <span><i class="fa fa-mobile"></i> +1517-386-0059</span>
                </div>
              </a>
            </li>
            <li class="media">
              <a href="#">
                <div class="media-left">
                  <img class="media-object img-circle" src="assets/images/photos/user5.png" alt="">
                </div>
                <div class="media-body">
                  <h4 class="media-heading">Nicholas T. Hinkle</h4>
                  <span><i class="fa fa-skype"></i> nicholas.hinkle</span>
                </div>
              </a>
            </li>
            <li class="media">
              <a href="#">
                <div class="media-left">
                  <img class="media-object img-circle" src="assets/images/photos/user6.png" alt="">
                </div>
                <div class="media-body">
                  <h4 class="media-heading">Jamie W. Bradford</h4>
                  <span><i class="fa fa-phone"></i> 225-270-2425</span>
                </div>
              </a>
            </li>
            <li class="media">
              <a href="#">
                <div class="media-left">
                  <img class="media-object img-circle" src="assets/images/photos/user7.png" alt="">
                </div>
                <div class="media-body">
                  <h4 class="media-heading">Pamela J. Stump</h4>
                  <span><i class="fa fa-mobile"></i> +1773-879-2491</span>
                </div>
              </a>
            </li>
            <li class="media">
              <a href="#">
                <div class="media-left">
                  <img class="media-object img-circle" src="assets/images/photos/user8.png" alt="">
                </div>
                <div class="media-body">
                  <h4 class="media-heading">Refugio C. Burgess</h4>
                  <span><i class="fa fa-mobile"></i> +1660-627-7184</span>
                </div>
              </a>
            </li>
            <li class="media">
              <a href="#">
                <div class="media-left">
                  <img class="media-object img-circle" src="assets/images/photos/user9.png" alt="">
                </div>
                <div class="media-body">
                  <h4 class="media-heading">Ashley T. Brewington</h4>
                  <span><i class="fa fa-skype"></i> ashley.brewington</span>
                </div>
              </a>
            </li>
            <li class="media">
              <a href="#">
                <div class="media-left">
                  <img class="media-object img-circle" src="assets/images/photos/user10.png" alt="">
                </div>
                <div class="media-body">
                  <h4 class="media-heading">Roberta F. Horn</h4>
                  <span><i class="fa fa-phone"></i> 716-630-0132</span>
                </div>
              </a>
            </li>
          </ul>
        </div><!-- tab-pane -->

        <!-- #################### SETTINGS ################### -->

        <div class="tab-pane" id="settings">
          <h5 class="sidebar-title">General Settings</h5>
          <ul class="list-group list-group-settings">
            <li class="list-group-item">
              <h5>Daily Newsletter</h5>
              <small>Get notified when someone else is trying to access your account.</small>
              <div class="toggle-wrapper">
                <div class="leftpanel-toggle toggle-light success"></div>
              </div>
            </li>
            <li class="list-group-item">
              <h5>Call Phones</h5>
              <small>Make calls to friends and family right from your account.</small>
              <div class="toggle-wrapper">
                <div class="leftpanel-toggle-off toggle-light success"></div>
              </div>
            </li>
          </ul>
          <h5 class="sidebar-title">Security Settings</h5>
          <ul class="list-group list-group-settings">
            <li class="list-group-item">
              <h5>Login Notifications</h5>
              <small>Get notified when someone else is trying to access your account.</small>
              <div class="toggle-wrapper">
                <div class="leftpanel-toggle toggle-light success"></div>
              </div>
            </li>
            <li class="list-group-item">
              <h5>Phone Approvals</h5>
              <small>Use your phone when login as an extra layer of security.</small>
              <div class="toggle-wrapper">
                <div class="leftpanel-toggle toggle-light success"></div>
              </div>
            </li>
          </ul>
        </div><!-- tab-pane -->


      </div><!-- tab-content -->

    </div><!-- leftpanelinner -->
  </div><!-- leftpanel -->