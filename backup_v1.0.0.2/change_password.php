<?php 
include 'include/sess.php';
include 'include/connexion.php';

// Require authentication
requireAuth();

$success_message = '';
$error_message = '';

if(isset($_POST['submit'])){
    // Verify CSRF token
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = "Invalid request";
    } else {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate input
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = "Please fill all fields";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match";
        } elseif (strlen($new_password) < 8) {
            $error_message = "New password must be at least 8 characters long";
        } else {
            // Get current user data
            $user_id = $_SESSION['user_id'];
            $user_data = $db->fetch("SELECT pass FROM users WHERE id = ?", [$user_id]);
            
            if ($user_data && Security::verifyPassword($current_password, $user_data['pass'])) {
                // Update password
                $hashed_password = Security::hashPassword($new_password);
                $db->update("UPDATE users SET pass = ? WHERE id = ?", [$hashed_password, $user_id]);
                
                $success_message = "Password updated successfully!";
            } else {
                $error_message = "Current password is incorrect";
            }
        }
    }
}
?>

<?php include 'include/header2.php'; ?>

<body class="signwrapper">
  <div class="sign-overlay"></div>
  <div class="signpanel"></div>

  <div class="panel signin">
    <div class="panel-heading">
      <h1>JobMaroc</h1>
      <h4 class="panel-title">Change Password</h4>
    </div>
    <div class="panel-body">
      
      <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
      <?php endif; ?>
      
      <?php if ($success_message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
      <?php endif; ?>
      
      <form action="" method="post">
        <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
        
        <div class="form-group mb10">
          <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
            <input name="current_password" type="password" class="form-control" placeholder="Current Password" required>
          </div>
        </div>
        
        <div class="form-group mb10">
          <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
            <input name="new_password" type="password" class="form-control" placeholder="New Password (min 8 characters)" required>
          </div>
        </div>
        
        <div class="form-group nomargin">
          <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
            <input name="confirm_password" type="password" class="form-control" placeholder="Confirm New Password" required>
          </div>
        </div>
        
        <div class="form-group">
          <button name="submit" class="btn btn-success btn-quirk btn-block">Change Password</button>
        </div>
      </form>
      
      <hr class="invisible">
      <div class="form-group">
        <a href="/home.php" class="btn btn-default btn-quirk btn-stroke btn-stroke-thin btn-block btn-sign">Back to Dashboard</a>
      </div>
    </div>
  </div><!-- panel -->

</body>
</html>



