<?php include '../include/session.php'; ?>
<?php include '../include/connexion.php'; ?>

<?php
// Secure input validation
$id = Security::sanitizeInput($_GET['id'] ?? '', 'int');

// Validate that the parameter is provided and is an integer
if (!$id || !Security::validateInt($id)) {
    Security::redirect('../user/index.php', 'Invalid user ID', 'error');
}

// Get user data securely
$dataa = $db->fetch("SELECT * FROM users WHERE id = ?", [$id]);

// Check if user exists
if (!$dataa) {
    Security::redirect('../user/index.php', 'User not found', 'error');
}

if(isset($_POST['submit'])){
    // Verify CSRF token
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request";
    } else {
        $user = Security::sanitizeInput($_POST['user'] ?? '', 'string');
        $pass = $_POST['pass'] ?? '';
        $email = Security::sanitizeInput($_POST['email'] ?? '', 'email');
        $role = Security::sanitizeInput($_POST['role'] ?? '', 'string');
        
        // Validate required fields
        if (empty($user) || empty($email) || empty($role)) {
            $error = "Please fill all required fields";
        } else {
            // Validate email
            if (!Security::validateEmail($email)) {
                $error = "Please enter a valid email address";
            } else {
                try {
                    // If password is provided, hash it securely
                    if (!empty($pass)) {
                        $hashedPassword = Security::hashPassword($pass);
                        $db->update("UPDATE users SET user = ?, pass = ?, email = ?, role = ? WHERE id = ?", 
                                   [$user, $hashedPassword, $email, $role, $id]);
                    } else {
                        // Update without changing password
                        $db->update("UPDATE users SET user = ?, email = ?, role = ? WHERE id = ?", 
                                   [$user, $email, $role, $id]);
                    }
                    
                    Security::redirect('../user/index.php', 'User updated successfully', 'success');
                } catch (Exception $e) {
                    $error = "Failed to update user";
                }
            }
        }
    }
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
    <div class="contentpanel">
      <div class="row">
            <h3>Modifier un utilisateur</h3>
            <div class="card">
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                        
                        <div class="form-group">
                            <label for="user">Nom</label>
                            <input value="<?= htmlspecialchars($dataa['user']) ?>" type="text" name="user" id="user" class="form-control" placeholder="Username" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="pass">Password (leave empty to keep current)</label>
                            <input type="password" name="pass" id="pass" class="form-control" placeholder="New password">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input value="<?= htmlspecialchars($dataa['email']) ?>" type="email" name="email" id="email" class="form-control" placeholder="Email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select name="role" id="role" class="form-control" required>
                                <option value="user" <?= $dataa['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="admin" <?= $dataa['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <button name="submit" type="submit" class="btn btn-outline-warning">Modifier</button>
                            <a href="../user/index.php" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        
      </div><!-- row -->
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->

</section>
<?php include '../include/footer.php'; ?>