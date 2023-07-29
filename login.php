<?php include 'include/sess.php' ?>
<?php include 'include/connexion.php' ?>
<?php 
// if(isset($_POST['submit'])){
// 	$user = $_POST['user'];
// 	$pass = md5($_POST['pass']);
// 	$req = $bd->query("SELECT COUNT(*) FROM users WHERE USER='$user' AND pass='$pass'");
// 	$res = $req->fetchColumn();
// 	if($res == '1'){
// 		$_SESSION['user'] = $user;
// 		header('location: /emploiDB/home.php');
// 	}
// }

// if(isset($_POST['submit'])){
// 	$user = $_POST['user'];
// 	$pass = md5($_POST['pass']);
// 	$req = $bd->query("SELECT COUNT(*) FROM users WHERE user='$user' AND pass='$pass'");
//   $re1 = $bd->query("SELECT * FROM users WHERE user='$user' AND pass='$pass'");
  
// 	$res = $req->fetchColumn();
  
// 	if($res == '1' && $re1['role']=='admin'){
// 		$_SESSION['user'] = $user;
// 		header('location: /emploi/home.php');
// 	}
//   else($res == '1' && $re1['role']=='user'){
// 		$_SESSION['user'] = $user;
// 		header('location: /emploiDB/home.php');
// 	}
// }



if(isset($_POST['submit'])){
	$user = $_POST['user'];
	$pass = md5($_POST['pass']);
	$req = $bd->query("SELECT COUNT(*) FROM users WHERE user='$user' AND pass='$pass'");
  
	$res = $req->fetchColumn();
  
	if($res == '1'){
        $req = $bd->query("SELECT id,role FROM users WHERE user='$user' AND pass='$pass'");
        $data = $req->fetch();
		$_SESSION['user']=$data['user'];
		$_SESSION['role'] = $data['role'];
		$_SESSION['user_id'] = $data['id'];
		header('location: /emploiDB/home.php');
		echo $res;

	}
	// else{
	// 	echo $_SESSION['role'];
	// 	echo $_SESSION['user_id'];

	// 	echo 'vous pouver entre un valid Login';
	// 	// echo $res;
	// }

}


?>

<?php include 'include/header2.php'; ?>
<body class="signwrapper">

  <div class="sign-overlay"></div>
  <div class="signpanel"></div>

  <div class="panel signin">
    <div class="panel-heading">
      <h1>JobMaroc</h1>
      <h4 class="panel-title">Bonjour! Please signin.</h4>
    </div>
    <div class="panel-body">
      <!-- <button class="btn btn-primary btn-quirk btn-fb btn-block">Connect Avec Facebook</button> -->
      <div class="or">or</div>
      <form action="" method="post" >
        <div class="form-group mb10">
          <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
            <input name="user" type="text" class="form-control" placeholder="Entrer Username">
          </div>
        </div>
        <div class="form-group nomargin">
          <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
            <input name="pass" type="text" class="form-control" placeholder="Entrer Mot de pass">
          </div>
        </div>
        <!-- <div><a href="" class="forgot">Oblier Mot de pass?</a></div> -->
        <div class="form-group">
          <button name="submit" class="btn btn-success btn-quirk btn-block">Login</button>
        </div>
      </form>
      <hr class="invisible">
      <div class="form-group">
        <a href="signup.php" class="btn btn-default btn-quirk btn-stroke btn-stroke-thin btn-block btn-sign">Je ne suis pas un member? Register maintenet!</a>
      </div>
    </div>
  </div><!-- panel -->

</body>
</html>
