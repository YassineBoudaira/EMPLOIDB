<?php include 'frontoffice/include/header2.php'; ?>
<body>
    <div class="container-fluid bg-white p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->


        <!-- Navbar Start -->
        <?php include 'frontoffice/include/menu2.php'; ?>
        <!-- Navbar End -->


        <!-- Header End -->
        <div class="container-fluid py-5 bg-dark page-header mb-5">
            <div class="container my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3 animated slideInDown">404 Error</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb text-uppercase">
                        <li class="breadcrumb-item"><a href="index.php">Acueil</a></li>
                        <li class="breadcrumb-item"><a href="#">Domaines</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">404 Error</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Header End -->


        <!-- 404 Start -->
        <div class="container-xxl py-5 wow fadeInUp" data-wow-delay="0.1s">
            <div class="container text-center">
                <div class="row justify-content-center">
                    <div class="col-lg-6">
                        <i class="bi bi-exclamation-triangle display-1 text-primary"></i>
                        <h1 class="display-1">404</h1>
                        <h1 class="mb-4"><strong>Page Not Found<br>Page non trouvée</strong> </h1>
                        <p class="mb-4">Nous sommes désolés, la page que vous avez recherchée n'existe pas sur notre site Web ! Allez peut-être sur notre page d'accueil ou essayez d'utiliser une recherche ?</p>
                        <a class="btn btn-primary py-3 px-5" href="index.php">Retour à L'accueil</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- 404 End -->

        
  


        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>
    <!-- <?php include 'frontoffice/include/footer2.php'; ?> -->