<?php 
// Check if we're being called from root or frontoffice directory
if (file_exists('../../include/sess.php')) {
    // Called from root directory (index.php, about.php, etc.)
    // header2.php is in frontoffice/include/, so we go up two levels to reach root
    include '../../include/sess.php';
    include '../../include/connexion.php';
} elseif (file_exists('../include/sess.php')) {
    // Called from frontoffice subdirectory
    include '../include/sess.php';
    include '../include/connexion.php';
} else {
    // Called from other directories, try to find the files
    $possible_paths = [
        'include/sess.php',
        '../include/sess.php',
        '../../include/sess.php',
        '../../../include/sess.php',
        '../../../../include/sess.php'
    ];
    
    $sess_found = false;
    $connexion_found = false;
    
    foreach ($possible_paths as $path) {
        if (!$sess_found && file_exists($path)) {
            include $path;
            $sess_found = true;
        }
        if (!$connexion_found && file_exists(str_replace('sess.php', 'connexion.php', $path))) {
            include str_replace('sess.php', 'connexion.php', $path);
            $connexion_found = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= isset($page_title) ? $page_title : 'JobMaroc' ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="frontoffice/assets/img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Inter:wght@700;800&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="frontoffice/assets/lib/animate/animate.min.css" rel="stylesheet">
    <link href="frontoffice/assets/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="frontoffice/assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- EMPLOIDB Professional Design System -->
    <link href="assets/css/emploidb-design-system.css" rel="stylesheet">
    
    <!-- Template Stylesheet -->
    <link href="frontoffice/assets/css/style.css" rel="stylesheet">
    
    <!-- Enhanced Professional Styles -->
    <style>
        /* Professional Header Enhancements */
        .navbar-emploidb {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--emploidb-neutral-200);
            box-shadow: var(--emploidb-shadow-sm);
            transition: var(--emploidb-transition-all);
            padding: 0.75rem 0;
        }
        
        .navbar-emploidb.scrolled {
            background: rgba(255, 255, 255, 0.98) !important;
            box-shadow: var(--emploidb-shadow-md);
        }
        
        .navbar-brand-emploidb {
            font-family: var(--emploidb-font-display);
            font-size: var(--emploidb-text-2xl);
            font-weight: var(--emploidb-font-weight-black);
            background: var(--emploidb-gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            transition: var(--emploidb-transition-all);
        }
        
        .navbar-brand-emploidb:hover {
            transform: scale(1.05);
            text-decoration: none;
        }
        
        .nav-link-emploidb {
            font-weight: var(--emploidb-font-weight-medium);
            color: var(--emploidb-text-primary) !important;
            padding: var(--emploidb-spacing-3) var(--emploidb-spacing-4) !important;
            border-radius: var(--emploidb-radius-lg);
            transition: var(--emploidb-transition-all);
            margin: 0 0.25rem;
            position: relative;
            overflow: hidden;
        }
        
        .nav-link-emploidb::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--emploidb-gradient-primary);
            opacity: 0.1;
            transition: var(--emploidb-transition-all);
            z-index: -1;
        }
        
        .nav-link-emploidb:hover::before {
            left: 0;
        }
        
        .nav-link-emploidb:hover {
            color: var(--emploidb-primary) !important;
            background: var(--emploidb-primary-50);
            text-decoration: none;
            transform: translateY(-1px);
        }
        
        .nav-link-emploidb.active {
            color: var(--emploidb-primary) !important;
            background: var(--emploidb-primary-50);
            box-shadow: var(--emploidb-shadow-sm);
        }
        
        /* Enhanced Mobile Menu */
        @media (max-width: 991.98px) {
            .navbar-emploidb {
                background: rgba(255, 255, 255, 0.98) !important;
            }
            
            .navbar-collapse {
                background: white;
                border-radius: var(--emploidb-radius-xl);
                box-shadow: var(--emploidb-shadow-lg);
                margin-top: 1rem;
                padding: 1rem;
            }
            
            .nav-link-emploidb {
                margin: 0.25rem 0;
                padding: var(--emploidb-spacing-3) var(--emploidb-spacing-4) !important;
            }
        }
        
        /* Professional Spinner */
        #spinner {
            background: linear-gradient(135deg, var(--emploidb-primary-50) 0%, var(--emploidb-secondary-50) 100%);
        }
        
        .spinner-border {
            border-color: var(--emploidb-primary) transparent var(--emploidb-primary) transparent;
        }
    </style>
    
    <!-- Spinner fallback CSS -->
    <style>
        /* Hide spinner after 3 seconds as fallback */
        #spinner {
            transition: opacity 0.5s ease-out;
        }
        #spinner.hide-fallback {
            opacity: 0;
            pointer-events: none;
        }
        @media (max-width: 768px) {
            #spinner {
                display: none !important;
            }
        }
    </style>
    <script>
        // Fallback: Hide spinner after 3 seconds
        setTimeout(function() {
            var spinner = document.getElementById('spinner');
            if (spinner) {
                spinner.classList.add('hide-fallback');
            }
        }, 3000);
    </script>
</head>

<body>