<?php include 'include/sess.php' ?>
<?php include 'include/connexion.php' ?>

<?php 
// Check if user is already logged in
if (isLoggedIn()) {
    header('Location: /home.php');
    exit();
}

// Secure login processing
if(isset($_POST['submit'])){
    // Verify CSRF token
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request";
    } else {
        $user = Security::sanitizeInput($_POST['user'] ?? '', 'string');
        $pass = $_POST['pass'] ?? '';
        
        // Validate input
        if (empty($user) || empty($pass)) {
            $error = "Please enter both username and password";
        } else {
            // Get user data securely
            $data = $db->fetch("SELECT id, user, pass, role FROM users WHERE user = ?", [$user]);
            
            if ($data && Security::verifyPassword($pass, $data['pass'])) {
                // Set session variables
                $_SESSION['user'] = $data['user'];
                $_SESSION['role'] = $data['role'];
                $_SESSION['user_id'] = $data['id'];
                $_SESSION['login_time'] = time();
                
                // Redirect based on role
                if ($data['role'] === 'admin') {
                    header('Location: /home.php');
                } else {
                    header('Location: /home.php');
                }
                exit();
            } else {
                $error = "Invalid username or password";
            }
        }
    }
}

// Display success message from signup
$success_message = '';
if (isset($_SESSION['message'])) {
    $success_message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'] ?? 'success';
    unset($_SESSION['message'], $_SESSION['message_type']);
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
      
      <?php if (isset($error)): ?>
        <div class="alert alert-danger">
          <?= htmlspecialchars($error) ?>
          <br><small>Need help? Contact administrator for password reset.</small>
        </div>
      <?php endif; ?>
      
      <?php if ($success_message): ?>
        <div class="alert alert-<?= $message_type === 'error' ? 'danger' : 'success' ?>">
          <?= htmlspecialchars($success_message) ?>
        </div>
      <?php endif; ?>
      
      <!-- Login Help Information -->
      <div class="alert alert-info" style="font-size: 12px; margin-bottom: 20px;">
        <strong>Login Help:</strong><br>
        • Admin users: username = "admin", password = "admin123"<br>
        • Regular users: username = "user", password = "user123"<br>
        • Other users: password = "user123"<br>
        <small>Passwords were recently reset for security. Please change your password after login.</small>
      </div>
      
      <form action="" method="post" >
        <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
        <div class="form-group mb10">
          <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
            <input name="user" type="text" class="form-control" placeholder="Entrer Username" required>
          </div>
        </div>
        <div class="form-group nomargin">
          <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
            <input name="pass" type="password" class="form-control" placeholder="Entrer Mot de pass" required>
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
