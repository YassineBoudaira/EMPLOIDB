<?php
// Include session management
include 'include/sess.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - JobMaroc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            color: #667eea;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .search-box {
            margin: 2rem 0;
        }
        .search-input {
            border: 2px solid #eee;
            border-radius: 50px;
            padding: 12px 20px;
            width: 100%;
            max-width: 300px;
            margin-right: 10px;
        }
        .search-btn {
            background: #667eea;
            border: none;
            color: white;
            padding: 12px 20px;
            border-radius: 50px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <h1 class="error-message">Page Not Found</h1>
        <p class="error-description">
            Oops! The page you're looking for doesn't exist. It might have been moved, deleted, or you entered the wrong URL.
        </p>
        
        <!-- Search Box -->
        <div class="search-box">
            <form action="search.php" method="GET" class="d-flex justify-content-center">
                <input type="text" name="q" class="search-input" placeholder="Search for jobs...">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        
        <!-- Navigation Links -->
        <div class="mt-4">
            <a href="index.php" class="btn-home me-3">
                <i class="fas fa-home me-2"></i>Home
            </a>
            <a href="frontoffice/job-list.php" class="btn-home me-3">
                <i class="fas fa-briefcase me-2"></i>Jobs
            </a>
            <a href="contact.php" class="btn-home">
                <i class="fas fa-envelope me-2"></i>Contact
            </a>
        </div>
        
        <!-- Helpful Links -->
        <div class="mt-4">
            <small class="text-muted">
                Popular pages: 
                <a href="about.php" class="text-decoration-none">About</a> • 
                <a href="signup.php" class="text-decoration-none">Sign Up</a> • 
                <a href="login.php" class="text-decoration-none">Login</a>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>