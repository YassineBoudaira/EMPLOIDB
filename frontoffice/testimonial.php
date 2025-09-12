<?php 
// Include configuration and database
include '../include/config.php';
include '../include/sess.php';
include '../include/connexion.php';
include 'include/header2.php'; 

// Get testimonials from database (if testimonials table exists, otherwise use sample data)
try {
    $testimonials = $db->fetchAll("SELECT * FROM testimonials WHERE status = 'active' ORDER BY created_at DESC LIMIT 10");
} catch (Exception $e) {
    // If testimonials table doesn't exist, use sample data
    $testimonials = [
        [
            'id' => 1,
            'name' => 'Sara Benali',
            'position' => 'Développeuse Web',
            'company' => 'TechCorp',
            'content' => 'Grâce à EMPLOIDB, j\'ai trouvé le poste de mes rêves en développement web. La plateforme est intuitive et les offres sont de qualité.',
            'rating' => 5,
            'photo' => 'frontoffice/assets/img/testimonial-1.jpg'
        ],
        [
            'id' => 2,
            'name' => 'Ahmed Mansouri',
            'position' => 'Chef de Projet',
            'company' => 'InnovateGroup',
            'content' => 'Une expérience exceptionnelle ! L\'interface est professionnelle et j\'ai reçu plusieurs propositions d\'emploi en moins d\'une semaine.',
            'rating' => 5,
            'photo' => 'frontoffice/assets/img/testimonial-2.jpg'
        ],
        [
            'id' => 3,
            'name' => 'Fatima Alaoui',
            'position' => 'Responsable Marketing',
            'company' => 'MarketPro',
            'content' => 'EMPLOIDB m\'a permis de connecter avec des employeurs de qualité. Le processus de candidature est simple et efficace.',
            'rating' => 5,
            'photo' => 'frontoffice/assets/img/testimonial-3.jpg'
        ],
        [
            'id' => 4,
            'name' => 'Youssef Tazi',
            'position' => 'Ingénieur Logiciel',
            'company' => 'SoftDev Solutions',
            'content' => 'La meilleure plateforme d\'emploi au Maroc ! Design moderne, fonctionnalités avancées et excellent support client.',
            'rating' => 5,
            'photo' => 'frontoffice/assets/img/testimonial-4.jpg'
        ]
    ];
}

// Get platform statistics for impact section
try {
    $total_users = $db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'candidate'")['count'];
    $total_jobs = $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE status = 'active'")['count'];
    $successful_placements = $db->fetch("SELECT COUNT(*) as count FROM postulation WHERE status = 'hired'")['count'];
} catch (Exception $e) {
    $total_users = 5000;
    $total_jobs = 1200;
    $successful_placements = 850;
}
?>

<body>
    <div class="container-xxl bg-white p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->

        <!-- Navigation -->
        <?php include 'include/menu2.php'; ?>

        <!-- Professional Testimonials Header -->
        <div class="container-fluid py-5 mb-5" style="background: var(--emploidb-gradient-secondary);">
            <div class="container my-5 pt-5 pb-4">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-8">
                        <div class="emploidb-animate-fade-in-up">
                            <div class="d-flex justify-content-center mb-4">
                                <div style="width: 80px; height: 80px; background: rgba(255, 255, 255, 0.2); border-radius: var(--emploidb-radius-full); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-quote-left" style="font-size: 2.5rem; color: white;"></i>
                                </div>
                            </div>
                            <h1 class="display-3 text-white mb-4 emploidb-font-black">
                                Témoignages Clients
                            </h1>
                            <p class="emploidb-text-xl text-white mb-5" style="opacity: 0.9; line-height: 1.6;">
                                Découvrez les expériences authentiques de nos utilisateurs qui ont trouvé leur emploi idéal grâce à EMPLOIDB
                            </p>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center" style="background: rgba(255, 255, 255, 0.1); border-radius: var(--emploidb-radius-full); padding: var(--emploidb-spacing-3) var(--emploidb-spacing-6);">
                                    <li class="breadcrumb-item">
                                        <a href="../index.php" style="color: white; text-decoration: none;">
                                            <i class="fas fa-home me-1"></i>Accueil
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item text-white active" aria-current="page" style="opacity: 0.8;">
                                        Témoignages
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Impact Statistics -->
        <div class="container mb-5">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="emploidb-card text-center emploidb-hover-lift" style="background: var(--emploidb-gradient-primary); color: white;">
                        <div class="card-body" style="padding: var(--emploidb-spacing-6);">
                            <i class="fas fa-users fa-3x mb-3" style="opacity: 0.8;"></i>
                            <h3 class="emploidb-font-black"><?= number_format($total_users) ?>+</h3>
                            <p class="mb-0">Candidats Satisfaits</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="emploidb-card text-center emploidb-hover-lift" style="background: var(--emploidb-gradient-secondary); color: white;">
                        <div class="card-body" style="padding: var(--emploidb-spacing-6);">
                            <i class="fas fa-briefcase fa-3x mb-3" style="opacity: 0.8;"></i>
                            <h3 class="emploidb-font-black"><?= number_format($total_jobs) ?>+</h3>
                            <p class="mb-0">Offres d'Emploi</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="emploidb-card text-center emploidb-hover-lift" style="background: var(--emploidb-gradient-accent); color: white;">
                        <div class="card-body" style="padding: var(--emploidb-spacing-6);">
                            <i class="fas fa-handshake fa-3x mb-3" style="opacity: 0.8;"></i>
                            <h3 class="emploidb-font-black"><?= number_format($successful_placements) ?>+</h3>
                            <p class="mb-0">Placements Réussis</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Testimonial Start -->
        <div class="container-xxl py-5 wow fadeInUp" data-wow-delay="0.1s">
            <div class="container">
                <h1 class="text-center mb-5">Our Clients Say!!!</h1>
                <div class="owl-carousel testimonial-carousel">
                    <div class="testimonial-item bg-light rounded p-4">
                        <i class="fa fa-quote-left fa-2x text-primary mb-3"></i>
                        <p>Dolor et eos labore, stet justo sed est sed. Diam sed sed dolor stet amet eirmod eos labore diam</p>
                        <div class="d-flex align-items-center">
                            <img class="img-fluid flex-shrink-0 rounded" src="img/testimonial-1.jpg" style="width: 50px; height: 50px;">
                            <div class="ps-3">
                                <h5 class="mb-1">Client Name</h5>
                                <small>Profession</small>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-item bg-light rounded p-4">
                        <i class="fa fa-quote-left fa-2x text-primary mb-3"></i>
                        <p>Dolor et eos labore, stet justo sed est sed. Diam sed sed dolor stet amet eirmod eos labore diam</p>
                        <div class="d-flex align-items-center">
                            <img class="img-fluid flex-shrink-0 rounded" src="img/testimonial-2.jpg" style="width: 50px; height: 50px;">
                            <div class="ps-3">
                                <h5 class="mb-1">Client Name</h5>
                                <small>Profession</small>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-item bg-light rounded p-4">
                        <i class="fa fa-quote-left fa-2x text-primary mb-3"></i>
                        <p>Dolor et eos labore, stet justo sed est sed. Diam sed sed dolor stet amet eirmod eos labore diam</p>
                        <div class="d-flex align-items-center">
                            <img class="img-fluid flex-shrink-0 rounded" src="img/testimonial-3.jpg" style="width: 50px; height: 50px;">
                            <div class="ps-3">
                                <h5 class="mb-1">Client Name</h5>
                                <small>Profession</small>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-item bg-light rounded p-4">
                        <i class="fa fa-quote-left fa-2x text-primary mb-3"></i>
                        <p>Dolor et eos labore, stet justo sed est sed. Diam sed sed dolor stet amet eirmod eos labore diam</p>
                        <div class="d-flex align-items-center">
                            <img class="img-fluid flex-shrink-0 rounded" src="img/testimonial-4.jpg" style="width: 50px; height: 50px;">
                            <div class="ps-3">
                                <h5 class="mb-1">Client Name</h5>
                                <small>Profession</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Testimonial End -->



<?php include 'include/footer2.php'; ?>