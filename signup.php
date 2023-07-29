

<?php 
include 'include/sess.php';
include 'include/connexion.php'; ?>
<?php 
// try{
if(isset($_POST['submit'])){
	$user = $_POST['user'];
	$pass = md5($_POST['pass']);
  $email= $_POST['email'];
  $rolee= 'user';
  $reql = $bd->prepare("INSERT INTO users (user, pass, email, role) values(?,?,?,?)");
  $reql->execute([$user, $pass, $email, $rolee]);
  // echo '<pre>';
  // var_dump($req);
  // echo '</pre>';
  // die();
	header('location: /emploiDB/login.php');

}
// }
// catch(exeption $ex){



//   echo('message ERRORE || ::::::: || '.$ex->getMessage());

// }
?>
<?php include 'include/header2.php'  ?>

<body class="signwrapper">

  <div class="sign-overlay"></div>
  <div class="signpanel"></div>


  <div class="signup">

    <div class="row">
      <div class="col-sm-5">
        <div class="panel">
          <div class="panel-heading">
            <h1>JobMaroc</h1>
            <h4 class="panel-title">Creier Un Compte</h4>
          </div>
          <div class="panel-body">
            <!-- <button class="btn btn-primary btn-quirk btn-fb btn-block">Sign Up Avec Facebook</button> -->
            <div class="or">or</div>
            <form action="" method="post">    
              <div class="form-group mb15">
                <input name="user" type="text" class="form-control" placeholder="Enter Your Username">
              </div>
              <div class="form-group mb15">
                <input name="pass" type="text" class="form-control" placeholder="Enter Your Password">
              </div>
              <div class="form-group mb15">
                <input name="email" type="text" class="form-control" placeholder="Enter Your Email">
              </div>


              <!-- <div class="row mb15">
                <div class="col-xs-5">
                  <div class="form-group">
                    <select class="form-control" style="width: 100%" data-placeholder="Birth Month">
                      <option value="">&nbsp;</option>
                      <option value="January">January</option>
                      <option value="February">February</option>
                      <option value="March">March</option>
                      <option value="April">April</option>
                      <option value="May">May</option>
                      <option value="June">June</option>
                    </select>
                  </div>
                </div>
                <div class="col-xs-3">
                  <div class="form-group">
                    <select class="form-control" style="width: 100%" data-placeholder="Birth Day">
                      <option value="">&nbsp;</option>
                      <option value="01">01</option>
                      <option value="02">02</option>
                      <option value="03">03</option>
                      <option value="04">04</option>
                      <option value="05">05</option>
                      <option value="06">06</option>
                      <option value="01">07</option>
                      <option value="02">08</option>
                      <option value="03">09</option>
                      <option value="04">10</option>
                      <option value="05">11</option>
                      <option value="06">12</option>
                    </select>
                  </div>
                </div>
                <div class="col-xs-4">
                  <div class="form-group">
                    <select class="form-control" style="width: 100%" data-placeholder="Birth Year">
                      <option value="">&nbsp;</option>
                      <option value="1986">2024</option>
                      <option value="1987">2023</option>
                      <option value="1988">2022</option>
                      <option value="1986">2021</option>
                      <option value="1987">2020</option>
                      <option value="1988">2019</option>
                      <option value="1986">2018</option>
                      <option value="1987">2017</option>
                      <option value="1988">1988</option>
                      <option value="1986">1986</option>
                      <option value="1987">1987</option>
                      <option value="1988">1988</option>
                      <option value="1986">1986</option>
                      <option value="1987">1987</option>
                      <option value="1988">1988</option>
                    </select>
                  </div>
                </div>
              </div> -->

              <!-- <div class="form-group mb20">
                <label class="ckbox">
                  <input type="checkbox" name="checkbox">
                  <span>Accept terms and conditions</span>
                </label>
              </div> -->
              <div class="form-group">
                <button type="submit" name="submit" class="btn btn-success btn-quirk btn-block">Registrer Moi</button>
              </div>
            </form>
          </div><!-- panel-body -->
        </div><!-- panel -->
      </div><!-- col-sm-5 -->
      <div class="col-sm-7">
        <div class="sign-sidebar">
          <h3 class="signtitle mb20">Comment créer un compte sur JobMaroc.mq ?</h3>
          <p>Tout d'abord, un compte sur notre site vous permet de gérer toutes les annonces que vous publiez, d'une autre manière vous pouvez modifier et supprimez vos annonces quand vous souhaitez. Et pour créer votre compte, il suffit de cliquer sur « Registre Moi » qui figure en haut de la page du site, remplir le formulaire fourni et puis cliquer sur REGISTRER MOI.</p>

          <br>

          <h4 class="reason">Est-ce qu'il est possible de supprimer mon compte ?</h4>
          <p>Oui, c'est possible. Envoyez-nous votre demande via email sur contact@JobMaroc.ma ou bien contactez-nous par téléphone au : 05 97 82 50 08, et elle sera traitée le plus tôt possible..</p>

          <br>

        
          <hr class="invisible">

          <div class="form-group">
            <a href="login.php" class="btn btn-default btn-quirk btn-stroke btn-stroke-thin btn-block btn-sign">Déjà membre? Connectez vous maintenant!</a>
          </div>
        </div><!-- sign-sidebar -->
      </div>
    </div>
    </div><!-- signup -->
</div> 
<?php include 'include/footer2.php'  ?>

