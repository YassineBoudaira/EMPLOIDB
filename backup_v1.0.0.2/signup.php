

<?php 
include 'include/sess.php';
include 'include/connexion.php'; 

// Secure signup processing
if(isset($_POST['submit'])){
    // Verify CSRF token
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request";
    } else {
        $user = Security::sanitizeInput($_POST['user'] ?? '', 'string');
        $pass = $_POST['pass'] ?? '';
        $email = Security::sanitizeInput($_POST['email'] ?? '', 'email');
        $rolee = 'user';
        
        // Validate required fields
        if (empty($user) || empty($pass) || empty($email)) {
            $error = "Please fill all required fields";
        } else {
            // Validate email
            if (!Security::validateEmail($email)) {
                $error = "Please enter a valid email address";
            } else {
                // Validate password strength
                if (strlen($pass) < 8) {
                    $error = "Password must be at least 8 characters long";
                } else {
                    try {
                        // Check if username already exists
                        $existingUser = $db->fetch("SELECT id FROM users WHERE user = ?", [$user]);
                        if ($existingUser) {
                            $error = "Username already exists. Please choose a different username.";
                        } else {
                            // Check if email already exists
                            $existingEmail = $db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
                            if ($existingEmail) {
                                $error = "Email already registered. Please use a different email or login.";
                            } else {
                                // Hash password securely
                                $hashedPassword = Security::hashPassword($pass);
                                
                                // Insert new user
                                $userId = $db->insert("INSERT INTO users (user, pass, email, role) VALUES (?, ?, ?, ?)", 
                                                     [$user, $hashedPassword, $email, $rolee]);
                                
                                if ($userId) {
                                    Security::redirect('/login.php', 'Account created successfully! Please login.', 'success');
                                } else {
                                    $error = "Failed to create account. Please try again.";
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $error = "An error occurred. Please try again.";
                    }
                }
            }
        }
    }
}
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
            <h4 class="panel-title">Créer Un Compte</h4>
          </div>
          <div class="panel-body">
            <!-- <button class="btn btn-primary btn-quirk btn-fb btn-block">Sign Up Avec Facebook</button> -->
            <div class="or">or</div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form action="" method="post">    
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                
                <div class="form-group mb15">
                    <input name="user" type="text" class="form-control" placeholder="Enter Your Username" 
                           value="<?= htmlspecialchars($_POST['user'] ?? '') ?>" required>
                </div>
                <div class="form-group mb15">
                    <input name="pass" type="password" class="form-control" placeholder="Enter Your Password (min 8 characters)" required>
                </div>
                <div class="form-group mb15">
                    <input name="email" type="email" class="form-control" placeholder="Enter Your Email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <button type="submit" name="submit" class="btn btn-success btn-quirk btn-block">Registrer Moi</button>
                </div>
            </form>
          </div><!-- panel-body -->
        </div><!-- panel -->
      </div><!-- col-sm-5 -->
      <div class="col-sm-7">
        <div class="sign-sidebar">
          <h3 class="signtitle mb20">Comment créer un compte sur JobMaroc.ma ?</h3>
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

