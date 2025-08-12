<?php include 'include/sess.php' ?>
<?php include 'include/connexion.php' ?>

<?php 
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
                $_SESSION['user'] = $data['user'];
                $_SESSION['role'] = $data['role'];
                $_SESSION['user_id'] = $data['id'];
                Security::redirect('/yass.php', 'Login successful');
            } else {
                $error = "Invalid username or password";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JobMaroc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="text-center mb-4">JobMaroc Login</h3>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        
                        <form action="" method="post">
                            <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                            
                            <div class="mb-3">
                                <label for="user" class="form-label">Username</label>
                                <input type="text" name="user" id="user" class="form-control" placeholder="Enter Username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="pass" class="form-label">Password</label>
                                <input type="password" name="pass" id="pass" class="form-control" placeholder="Enter Password" required>
                            </div>
                            
                            <div class="d-grid">
                                <button name="submit" type="submit" class="btn btn-primary">Se connecter</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="login.php" class="text-decoration-none">Back to main login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>