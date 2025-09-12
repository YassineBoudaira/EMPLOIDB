<?php 
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';
include 'frontoffice/include/header2.php'; 
?>

<div class="container-fluid bg-white p-0">
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Navbar End -->
    <?php include 'frontoffice/include/menu2.php'; ?>
    <!-- Header End -->

    <!-- Enhanced About Content -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- Professional Header Section -->
                    <div class="professional-section">
                        <h3><i class="fas fa-building"></i>À Propos de JobMaroc.com</h3>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="enhanced-card">
                                    <div class="enhanced-card-header">
                                        <h4 class="enhanced-card-title">Votre partenaire de confiance pour l'emploi au Maroc</h4>
                                        <div class="enhanced-card-actions">
                                            <button class="btn-action btn-action-primary" onclick="window.location.href='contact.php'">
                                                <i class="fas fa-envelope"></i>Nous Contacter
                                            </button>
                                            <button class="btn-action btn-action-success" onclick="window.location.href='enhanced_search.php'">
                                                <i class="fas fa-search"></i>Rechercher des Emplois
                                            </button>
                                        </div>
                                    </div>
                                    <p class="text-muted">Le site de petites annonces gratuites de particuliers et professionnels au MAROC. Nous aidons nos visiteurs et nos membres à obtenir le meilleur emploi et à trouver des talents compétents pour leurs entreprises.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="action-buttons">
                                    <button class="btn-action btn-action-success" onclick="window.location.href='signup.php'">
                                        <i class="fas fa-user-plus"></i>S'inscrire
                                    </button>
                                    <button class="btn-action btn-action-primary" onclick="window.location.href='enhanced_search.php'">
                                        <i class="fas fa-search"></i>Rechercher des Emplois
                                    </button>
                                    <button class="btn-action btn-action-info" onclick="window.location.href='contact.php'">
                                        <i class="fas fa-envelope"></i>Nous Contacter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Statistics Section -->
                    <div class="professional-section">
                        <h3><i class="fas fa-chart-bar"></i>Nos Statistiques</h3>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-primary">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="stats-number"><?= $total_users ?? 10000 ?>+</div>
                                    <div class="stats-label">Utilisateurs Inscrits</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-success">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <div class="stats-number"><?= $total_jobs ?? 5000 ?>+</div>
                                    <div class="stats-label">Offres d'Emploi</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-warning">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="stats-number"><?= $total_companies ?? 500 ?>+</div>
                                    <div class="stats-label">Entreprises Partenaires</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-info">
                                        <i class="fas fa-handshake"></i>
                                    </div>
                                    <div class="stats-number"><?= $successful_placements ?? 2000 ?>+</div>
                                    <div class="stats-label">Placements Réussis</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- About Content -->
                    <div class="row g-5 align-items-center">
                <div class="col-lg-6 wow fadeIn" data-wow-delay="0.1s">
                    <div class="about-image-container">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <div class="row g-0 about-bg rounded overflow-hidden">
                                    <div class="col-6 text-start">
                                        <img class="img-fluid w-100" src="frontoffice/assets/img/about-1.jpg" alt="À propos de JobMaroc">
                                    </div>
                                    <div class="col-6 text-start">
                                        <img class="img-fluid" src="frontoffice/assets/img/about-2.jpg" style="width: 85%; margin-top: 15%;" alt="Notre équipe">
                                    </div>
                                    <div class="col-6 text-end">
                                        <img class="img-fluid" src="frontoffice/assets/img/about-3.jpg" style="width: 85%;" alt="Nos services">
                                    </div>
                                    <div class="col-6 text-end">
                                        <img class="img-fluid w-100" src="frontoffice/assets/img/about-4.jpg" alt="Notre mission">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 wow fadeIn" data-wow-delay="0.5s">
                    <div class="about-content">
                        <h2 class="mb-4">Bienvenue sur JobMaroc.com</h2>
                        <p class="lead mb-4">Le site de petites annonces gratuites de particuliers et professionnels au MAROC</p>
                        <p class="mb-4">Nous aidons nos visiteurs et nos membres à obtenir le meilleur emploi et à trouver des talents compétents pour leurs entreprises.</p>
                        
                        <div class="features-list mb-4">
                            <div class="feature-item">
                                <i class="fas fa-check-circle text-primary me-3"></i>
                                <span>Vous pouvez vous inscrire et déposer autant de petites annonces que vous le souhaitez dans différents domaines</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle text-primary me-3"></i>
                                <span>Nous continuerons sans réserve à nous consacrer à améliorer l'efficacité et à faire tous les efforts possibles pour répondre à votre confiance</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check-circle text-primary me-3"></i>
                                <span>Contribution à l'élaboration et au développement de ce secteur d'activité</span>
                            </div>
                        </div>
                        
                        <a class="btn btn-primary py-3 px-5" href="contact.php">
                            <i class="fas fa-envelope me-2"></i>
                            Nous Contacter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="container-xxl py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-5">
                            <h3 class="mb-4">
                                <i class="fas fa-bullhorn text-primary me-2"></i>
                                Diffusez vos Offres d'Emploi
                            </h3>
                            <p class="lead mb-4">
                                Diffusez vos offres d'emploi sur JobMaroc.com et sur notre réseau de sites Emploi et réseaux sociaux.
                            </p>
                            <div class="contact-info">
                                <p class="mb-0">
                                    <i class="fas fa-phone text-primary me-2"></i>
                                    Pour plus d'informations : <strong>0661 700 614</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="stat-card">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body p-4">
                                <div class="stat-icon mb-3">
                                    <i class="fas fa-users text-primary"></i>
                                </div>
                                <h4 class="text-primary mb-2">10,000+</h4>
                                <p class="text-muted mb-0">Utilisateurs Actifs</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                    <div class="stat-card">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body p-4">
                                <div class="stat-icon mb-3">
                                    <i class="fas fa-briefcase text-primary"></i>
                                </div>
                                <h4 class="text-primary mb-2">5,000+</h4>
                                <p class="text-muted mb-0">Offres d'Emploi</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                    <div class="stat-card">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body p-4">
                                <div class="stat-icon mb-3">
                                    <i class="fas fa-building text-primary"></i>
                                </div>
                                <h4 class="text-primary mb-2">1,000+</h4>
                                <p class="text-muted mb-0">Entreprises</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="0.7s">
                    <div class="stat-card">
                        <div class="card border-0 shadow-sm text-center">
                            <div class="card-body p-4">
                                <div class="stat-icon mb-3">
                                    <i class="fas fa-handshake text-primary"></i>
                                </div>
                                <h4 class="text-primary mb-2">95%</h4>
                                <p class="text-muted mb-0">Taux de Satisfaction</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Professional Footer Section -->
<div class="container-fluid bg-light py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="professional-section">
                    <h3><i class="fas fa-info-circle"></i>Informations Complémentaires</h3>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="enhanced-card">
                                <div class="enhanced-card-header">
                                    <h4 class="enhanced-card-title">
                                        <i class="fas fa-users me-2"></i>Notre Communauté
                                    </h4>
                                </div>
                                <p class="text-muted">Rejoignez notre communauté de professionnels et candidats qualifiés. Trouvez l'emploi de vos rêves ou recrutez les meilleurs talents.</p>
                                <div class="mt-3">
                                    <a href="signup.php" class="btn btn-primary">
                                        <i class="fas fa-user-plus me-2"></i>Rejoindre
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="enhanced-card">
                                <div class="enhanced-card-header">
                                    <h4 class="enhanced-card-title">
                                        <i class="fas fa-briefcase me-2"></i>Services Entreprises
                                    </h4>
                                </div>
                                <p class="text-muted">Diffusez vos offres d'emploi et accédez à une base de données de candidats qualifiés. Solutions sur mesure pour les entreprises.</p>
                                <div class="mt-3">
                                    <a href="contact.php" class="btn btn-success">
                                        <i class="fas fa-building me-2"></i>Nos Services
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="enhanced-card">
                                <div class="enhanced-card-header">
                                    <h4 class="enhanced-card-title">
                                        <i class="fas fa-headset me-2"></i>Support Client
                                    </h4>
                                </div>
                                <p class="text-muted">Notre équipe est disponible pour vous accompagner dans votre recherche d'emploi ou vos besoins de recrutement.</p>
                                <div class="mt-3">
                                    <a href="contact.php" class="btn btn-info">
                                        <i class="fas fa-phone me-2"></i>Nous Contacter
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Information Section -->
<div class="container-fluid bg-primary text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <div class="professional-section bg-transparent">
                    <h3 class="text-white"><i class="fas fa-map-marker-alt"></i>Nos Coordonnées</h3>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="enhanced-card bg-white text-dark">
                                <div class="enhanced-card-header">
                                    <h4 class="enhanced-card-title">
                                        <i class="fas fa-map-marker-alt me-2 text-primary"></i>Adresse
                                    </h4>
                                </div>
                                <p class="mb-0">54 Qu Hrilla, Safi, MAROC</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="enhanced-card bg-white text-dark">
                                <div class="enhanced-card-header">
                                    <h4 class="enhanced-card-title">
                                        <i class="fas fa-phone me-2 text-primary"></i>Téléphone
                                    </h4>
                                </div>
                                <p class="mb-0">+212 697825008</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="enhanced-card bg-white text-dark">
                                <div class="enhanced-card-header">
                                    <h4 class="enhanced-card-title">
                                        <i class="fas fa-envelope me-2 text-primary"></i>Email
                                    </h4>
                                </div>
                                <p class="mb-0">yassineboudairaa@gmail.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.about-image-container {
    transition: all 0.3s ease;
}

.about-image-container:hover {
    transform: translateY(-5px);
}

.about-content h2 {
    color: #2c3e50;
    font-weight: 600;
}

.features-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.feature-item i {
    margin-top: 0.25rem;
    flex-shrink: 0;
}

.stat-card {
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #007bff, #0056b3);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 1.5rem;
    color: white;
}

.card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.contact-info {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    padding: 1.5rem;
    border-radius: 8px;
    margin-top: 1rem;
}

@media (max-width: 768px) {
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
    
    .feature-item {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<?php include 'frontoffice/include/footer2.php'; ?>

<!-- Professional UX/UI Styles -->
<style>
/* Professional Sections */
.professional-section {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.professional-section h3 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.professional-section h3 i {
    color: #007bff;
    font-size: 1.2em;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 20px;
}

.btn-action {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.btn-action-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.btn-action-success {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    color: white;
}

.btn-action-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: #212529;
}

.btn-action-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
}

/* Enhanced Cards */
.enhanced-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.enhanced-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.enhanced-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f8f9fa;
}

.enhanced-card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.enhanced-card-actions {
    display: flex;
    gap: 10px;
}

/* Stats Cards */
.stats-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    margin-bottom: 20px;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.5rem;
    color: white;
}

.color-scheme-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
}

.color-scheme-success {
    background: linear-gradient(135deg, #28a745, #1e7e34);
}

.color-scheme-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
}

.color-scheme-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.stats-label {
    color: #6c757d;
    font-weight: 500;
}

/* Form Styling */
.form-group {
    margin-bottom: 1rem;
}

.form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}



@media (max-width: 768px) {
    .professional-section {
        padding: 20px;
    }
    
    .enhanced-card {
        padding: 20px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
}

/* Footer Enhancements */
.footer {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important;
    border-top: 3px solid #007bff;
    position: relative;
    overflow: hidden;
}

.footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(135deg, #007bff, #0056b3, #004085);
}

.footer h5 {
    color: #ffffff !important;
    font-weight: 600;
    margin-bottom: 20px;
    position: relative;
}

.footer h5::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 40px;
    height: 3px;
    background: linear-gradient(135deg, #007bff, #0056b3);
    border-radius: 2px;
}

.footer .btn-link {
    color: #cbd5e1 !important;
    text-decoration: none;
    transition: all 0.3s ease;
    padding: 10px 0;
    display: block;
    border: none;
    background: none;
    position: relative;
    font-weight: 500;
}

.footer .btn-link::before {
    content: '→';
    position: absolute;
    left: -15px;
    opacity: 0;
    transition: all 0.3s ease;
    color: #007bff;
}

.footer .btn-link:hover {
    color: #ffffff !important;
    transform: translateX(8px);
    text-decoration: none;
    padding-left: 15px;
}

.footer .btn-link:hover::before {
    opacity: 1;
    left: 0;
}

.footer .btn-social {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    transition: all 0.3s ease;
    border: 2px solid #cbd5e1;
    color: #cbd5e1;
    background: transparent;
    font-size: 1.1rem;
    position: relative;
    overflow: hidden;
}

.footer .btn-social::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #007bff, #0056b3);
    transition: left 0.3s ease;
    z-index: -1;
}

.footer .btn-social:hover {
    border-color: #007bff;
    color: #ffffff;
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 8px 20px rgba(0, 123, 255, 0.4);
}

.footer .btn-social:hover::before {
    left: 0;
}

.copyright {
    border-top: 1px solid #475569;
    padding-top: 20px;
    margin-top: 30px;
}

.copyright a {
    color: #007bff;
    text-decoration: none;
    transition: color 0.3s ease;
}

.copyright a:hover {
    color: #0056b3;
}

.footer-menu a {
    color: #cbd5e1;
    text-decoration: none;
    margin-left: 20px;
    transition: color 0.3s ease;
}

.footer-menu a:hover {
    color: #007bff;
}

.back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #007bff, #0056b3);
    border: none;
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
}

.back-to-top.show {
    opacity: 1;
    visibility: visible;
}

.back-to-top:hover {
    background: linear-gradient(135deg, #0056b3, #004085);
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 123, 255, 0.4);
}

/* Professional Section Enhancements */
.professional-section.bg-transparent {
    background: transparent !important;
    box-shadow: none;
}

.professional-section.bg-transparent h3 {
    color: #ffffff !important;
}

.professional-section.bg-transparent .enhanced-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Enhanced Card Improvements */
.enhanced-card.bg-white {
    background: #ffffff !important;
}

.enhanced-card.bg-white .enhanced-card-header {
    border-bottom-color: #e2e8f0;
}

.enhanced-card.bg-white .enhanced-card-title {
    color: #1e293b;
}

/* Responsive Footer */
@media (max-width: 768px) {
    .footer .btn-social {
        width: 35px;
        height: 35px;
        margin-right: 8px;
    }
    
    .footer-menu a {
        margin-left: 10px;
        font-size: 0.9rem;
    }
    
    .back-to-top {
        bottom: 20px;
        right: 20px;
        width: 45px;
        height: 45px;
    }
}
</style>



<!-- Enhanced JavaScript Functions -->
<script>
// Show notification function for any future use
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Smooth scrolling for anchor links
document.addEventListener('DOMContentLoaded', function() {
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Back to top button functionality
    const backToTopButton = document.querySelector('.back-to-top');
    if (backToTopButton) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });
        
        backToTopButton.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Initialize WOW.js for animations
    if (typeof WOW !== 'undefined') {
        new WOW().init();
    }
});
</script>