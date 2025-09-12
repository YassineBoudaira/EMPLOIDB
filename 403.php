<?php
// Include session management
include 'include/sess.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - JobMaroc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 90%;
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            color: #ff6b6b;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        .error-message {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 1rem;
        }
        .error-description {
            color: #666;
            margin-bottom: 2rem;
        }
        .btn-home {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s ease;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }
        .lock-icon {
            font-size: 4rem;
            color: #ff6b6b;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="lock-icon">
            <i class="fas fa-lock"></i>
        </div>
        <div class="error-code">403</div>
        <h1 class="error-message">Access Denied</h1>
        <p class="error-description">
            Sorry! You don't have permission to access this page. Please make sure you're logged in with the correct account type.
        </p>
        
        <!-- Navigation Links -->
        <div class="mt-4">
            <a href="index.php" class="btn-home me-3">
                <i class="fas fa-home me-2"></i>Home
            </a>
            <a href="login.php" class="btn-home me-3">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </a>
            <a href="contact.php" class="btn-home">
                <i class="fas fa-envelope me-2"></i>Contact
            </a>
        </div>
        
        <!-- Helpful Links -->
        <div class="mt-4">
            <small class="text-muted">
                Need help? 
                <a href="contact.php" class="text-decoration-none">Contact Support</a> • 
                <a href="signup.php" class="text-decoration-none">Create Account</a>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
