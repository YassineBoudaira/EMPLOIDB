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
		header('location: /emploiDB/yass.php');
		echo $res;

	}
	else{
		echo $_SESSION['role'];
		echo $_SESSION['user_id'];

		echo 'vous pouver entre un valid login \n';
		echo $res;
	}

}
?>


<form action="" method="post">
<input name="user" type="text" class="form-control" placeholder="Enter Username">
<input name="pass" type="text" class="form-control" placeholder="Enter Username">
<button name="submit" >se connecter</button>
</form>