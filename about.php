

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
        
        <!-- Navbar End -->
        <?php include 'frontoffice/include/menu2.php'; ?>
        <!-- Header End -->
        <div class="container-fluid py-5 bg-dark page-header mb-5">
            <div class="container my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3 animated slideInDown">About Us</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb text-uppercase">
                        <li class="breadcrumb-item"><a href="#">Accueil</a></li>
                        <li class="breadcrumb-item"><a href="#">Domaines</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">About</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Header End -->


        <!-- About Start -->
        <div class="container-xxl py-5">
            <div class="container">
                <div class="row g-5 align-items-center">
                    <div class="col-lg-6 wow fadeIn" data-wow-delay="0.1s">
                        <div class="row g-0 about-bg rounded overflow-hidden">
                            <div class="col-6 text-start">
                                <img class="img-fluid w-100" src="frontoffice/assets/img/about-1.jpg">
                            </div>
                            <div class="col-6 text-start">
                                <img class="img-fluid" src="frontoffice/assets/img/about-2.jpg" style="width: 85%; margin-top: 15%;">
                            </div>
                            <div class="col-6 text-end">
                                <img class="img-fluid" src="frontoffice/assets/img/about-3.jpg" style="width: 85%;">
                            </div>
                            <div class="col-6 text-end">
                                <img class="img-fluid w-100" src="frontoffice/assets/img/about-4.jpg">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s">
                        <h1 class="mb-4">Bienvenue sur JobMaroc.com le site de petites annonces gratuites de particuliers et professionnels au MAROC</h1>
                        <p class="mb-4">Nous aidons notre visiteurs et notre membres à obtenir le meilleur emploi et à trouver un talent et compétant</p>
                        <p><i class="fa fa-check text-primary me-3"></i>Vous pouvez s'inscrire et déposer autant de petites annonces que vous le souhaitez dans différentes domaines</p>
                        <p><i class="fa fa-check text-primary me-3"></i>Nous continuerons sans réserve à nous consacrer à améliorer l'efficacité et à faire tous les efforts possibles pour répondre à votre confiance et contribuer à l'élaboration de ce secteur.</p>
                        <a class="btn btn-primary py-3 px-5 mt-3" href="/emploidb/contact.php">Contacter Nous</a>
                    </div>
                </div>
            </div>

        </div>
        <!-- About End -->

<center><p>Diffuser vos offres d'emploi sur JobMaroc.com et sur notre réseau des sites Emploi, réseaux sociaux. Pour plus d'informations 0661 700 614</p></center>

    <?php include 'frontoffice/include/footer2.php'; ?>