<?php 
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';
include 'include/ads_display_system.php';

// Initialize ad display system
$user_id = $_SESSION['user_id'] ?? null;
$user_type = $_SESSION['user_type'] ?? 'guest';
$ad_system = new AdDisplaySystem($db, $user_id, $user_type, 'cabinets');

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

    <!-- Recruitment Agencies Header with Image -->
    <div class="container-fluid p-0">
        <div class="position-relative">
            <img class="img-fluid w-100" src="frontoffice/assets/img/recruitment-header.jpg" alt="Recruitment Agencies Header" style="height: 300px; object-fit: cover;">
            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: linear-gradient(135deg, rgba(37, 157, 171, 0.9), rgba(43, 155, 255, 0.8));">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 text-center">
                            <h1 class="display-4 text-white animated slideInDown mb-4">
                                <i class="fas fa-building me-3"></i>Cabinets de Recrutement
                            </h1>
                            <p class="fs-5 fw-medium text-white mb-4 pb-2">
                                Découvrez les meilleurs cabinets de recrutement au Maroc pour votre carrière
                            </p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="#agencies-list" class="btn btn-light py-md-3 px-md-5 me-3 animated slideInLeft">
                                    <i class="fas fa-handshake me-2"></i>Voir les Cabinets
                                </a>
                                <a href="enhanced_search.php" class="btn btn-outline-light py-md-3 px-md-5 animated slideInRight">
                                    <i class="fas fa-search me-2"></i>Rechercher des Emplois
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recruitment Agencies Statistics Section -->
    <div class="container-fluid py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="d-flex align-items-center bg-white rounded p-4 shadow-sm">
                        <div class="flex-shrink-0 btn btn-danger btn-square rounded-circle me-3">
                            <i class="fas fa-building text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1">25+</h6>
                            <span class="text-muted">Cabinets Partenaires</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="d-flex align-items-center bg-white rounded p-4 shadow-sm">
                        <div class="flex-shrink-0 btn btn-success btn-square rounded-circle me-3">
                            <i class="fas fa-users text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1">5K+</h6>
                            <span class="text-muted">Candidats Placés</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="d-flex align-items-center bg-white rounded p-4 shadow-sm">
                        <div class="flex-shrink-0 btn btn-primary btn-square rounded-circle me-3">
                            <i class="fas fa-star text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1">4.9</h6>
                            <span class="text-muted">Note Moyenne</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="d-flex align-items-center bg-white rounded p-4 shadow-sm">
                        <div class="flex-shrink-0 btn btn-info btn-square rounded-circle me-3">
                            <i class="fas fa-map-marker-alt text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1">15</h6>
                            <span class="text-muted">Villes Couvertes</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recruitment Agencies Content -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">Liste des Cabinets de Recrutement</h2>
                    
                    <!-- Content Top Ad -->
                    <?php echo displayContentTopAds(1); ?>
                    
                    <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.3s">
                        <div class="tab-content">
                            <div id="tab-1" class="tab-pane fade show p-0 active">
                                
                                <!-- Inline Ad -->
                                <?php echo displayInlineAds(1); ?>
                                
                                <!-- Adéquation -->
                                <div class="recruitment-agency-card mb-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body p-4">
                                            <div class="row g-4">
                                                <div class="col-sm-12 col-md-8">
                                                    <div class="agency-info">
                                                        <h5 class="agency-name mb-3">
                                                            <i class="fas fa-building text-primary me-2"></i>
                                                            Adéquation
                                                        </h5>
                                                        <p class="agency-description mb-3">
                                                            Cabinet de conseil en ressources humaines spécialisé dans le recrutement, la formation, le coaching et l'évaluation du personnel.
                                                        </p>
                                                        <div class="agency-details">
                                                            <div class="detail-item">
                                                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                                                <span class="label">Adresse :</span> 89, Bd d'Anfa - Résidence Ibn Zaidoune-8ème étage 20060 - Casablanca
                                                            </div>
                                                            <div class="detail-item">
                                                                <i class="fas fa-phone text-primary me-2"></i>
                                                                <span class="label">Tél :</span> 05 22 48 61 16
                                                            </div>
                                                            <div class="detail-item">
                                                                <i class="fas fa-fax text-primary me-2"></i>
                                                                <span class="label">Fax :</span> 05 22 48 56 97
                                                            </div>
                                                            <div class="detail-item">
                                                                <i class="fas fa-envelope text-primary me-2"></i>
                                                                <span class="label">E-mail :</span> <a href="mailto:info@adequation.ma">info@adequation.ma</a>
                                                            </div>
                                                            <div class="detail-item">
                                                                <i class="fas fa-globe text-primary me-2"></i>
                                                                <span class="label">Website :</span> 
                                                                <a href="http://www.adequation.ma" rel="nofollow" target="_blank">www.adequation.ma</a>, 
                                                                <a href="http://www.recruteonline.com" rel="nofollow" target="_blank">www.recruteonline.com</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-4">
                                                    <div class="agency-actions">
                                                        <a href="http://www.adequation.ma" target="_blank" class="btn btn-primary w-100 mb-2">
                                                            <i class="fas fa-external-link-alt me-2"></i>
                                                            Visiter le Site
                                                        </a>
                                                        <a href="mailto:info@adequation.ma" class="btn btn-outline-primary w-100 mb-2">
                                                            <i class="fas fa-envelope me-2"></i>
                                                            Contacter
                                                        </a>
                                                        <button class="btn btn-outline-secondary w-100" onclick="shareAgency('Adéquation')">
                                                            <i class="fas fa-share-alt me-2"></i>
                                                            Partager
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- IT Skills Services -->
                                <div class="recruitment-agency-card mb-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body p-4">
                                            <div class="row g-4">
                                                <div class="col-sm-12 col-md-8">
                                                    <div class="agency-info">
                                                        <h5 class="agency-name mb-3">
                                                            <i class="fas fa-laptop-code text-primary me-2"></i>
                                                            IT Skills Services
                                                        </h5>
                                                        <p class="agency-description mb-3">
                                                            Cabinet de recrutement spécialisé dans le domaine des métiers de l'informatique
                                                        </p>
                                                        <div class="agency-details">
                                                            <div class="detail-item">
                                                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                                                <span class="label">Adresse :</span> Technopark Route de Nouaceur Angle RC 11, 20000 - Casablanca
                                                            </div>
                                                            <div class="detail-item">
                                                                <i class="fas fa-phone text-primary me-2"></i>
                                                                <span class="label">Tél :</span> +212 522 21 91 98
                                                            </div>
                                                            <div class="detail-item">
                                                                <i class="fas fa-fax text-primary me-2"></i>
                                                                <span class="label">Fax :</span> +212 522 21 91 98
                                                            </div>
                                                            <div class="detail-item">
                                                                <i class="fas fa-envelope text-primary me-2"></i>
                                                                <span class="label">E-mail :</span> <a href="mailto:info@it-skills-services.com">info@it-skills-services.com</a>
                                                            </div>
                                                            <div class="detail-item">
                                                                <i class="fas fa-globe text-primary me-2"></i>
                                                                <span class="label">Website :</span> 
                                                                <a href="http://www.it-skills-services.com/" rel="nofollow" target="_blank">www.it-skills-services.com</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-4">
                                                    <div class="agency-actions">
                                                        <a href="http://www.it-skills-services.com/" target="_blank" class="btn btn-primary w-100 mb-2">
                                                            <i class="fas fa-external-link-alt me-2"></i>
                                                            Visiter le Site
                                                        </a>
                                                        <a href="mailto:info@it-skills-services.com" class="btn btn-outline-primary w-100 mb-2">
                                                            <i class="fas fa-envelope me-2"></i>
                                                            Contacter
                                                        </a>
                                                        <button class="btn btn-outline-secondary w-100" onclick="shareAgency('IT Skills Services')">
                                                            <i class="fas fa-share-alt me-2"></i>
                                                            Partager
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Multicibles -->
                                <div class="recruitment-agency-card mb-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body p-4">
                                            <div class="row g-4">
                                                <div class="col-sm-12 col-md-8">
                                                    <div class="agency-info">
                                                        <h5 class="agency-name mb-3">
                                                            <i class="fas fa-users text-primary me-2"></i>
                                                            Multicibles
                                                        </h5>
                                                        <p class="agency-description mb-3">
                                                            Cabinet de conseil en recrutement, spécialisé dans la recherche et la sélection de tous types de profils dans des secteurs d'activité très variés
                                                        </p>
                                                        <div class="agency-details">
                                                            <div class="detail-item">
                                                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                                                <span class="label">Adresse :</span> 51, rue Moussa Bnou Noussair, 1er étage - Casablanca
                                                            </div>
                                                            <div class="detail-item">
                                                                <i class="fas fa-phone text-primary me-2"></i>
                                                                <span class="label">Tél :</span> +212 522 48 15 11
                                                            </div>
                                                            <div class="detail-item">
                                                                <i class="fas fa-fax text-primary me-2"></i>
                                                                <span class="label">Fax :</span> +212 522 48 15 09
                                                            </div>
                                                            <div class="detail-item">
                                                                <i class="fas fa-envelope text-primary me-2"></i>
                                                                <span class="label">E-mail :</span> <a href="mailto:zineb.lahlou@multicibles.ma">zineb.lahlou@multicibles.ma</a>
                                                            </div>
                                                            <div class="detail-item">
                                                                <i class="fas fa-globe text-primary me-2"></i>
                                                                <span class="label">Website :</span> 
                                                                <a href="http://www.multicibles.com/" rel="nofollow" target="_blank">www.multicibles.com</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 col-md-4">
                                                    <div class="agency-actions">
                                                        <a href="http://www.multicibles.com/" target="_blank" class="btn btn-primary w-100 mb-2">
                                                            <i class="fas fa-external-link-alt me-2"></i>
                                                            Visiter le Site
                                                        </a>
                                                        <a href="mailto:zineb.lahlou@multicibles.ma" class="btn btn-outline-primary w-100 mb-2">
                                                            <i class="fas fa-envelope me-2"></i>
                                                            Contacter
                                                        </a>
                                                        <button class="btn btn-outline-secondary w-100" onclick="shareAgency('Multicibles')">
                                                            <i class="fas fa-share-alt me-2"></i>
                                                            Partager
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Inline Ad -->
                                <?php echo displayInlineAds(1); ?>
                                
                                <!-- Continue with other agencies... -->
                                <?php
                                // Add more agencies here following the same pattern
                                ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Bottom Ad -->
    <?php echo displayContentBottomAds(1); ?>
</div>

<!-- Popup Ads -->
<?php echo displayPopupAds(1); ?>

<!-- Sticky Ads -->
<?php echo displayStickyAds(1); ?>

<!-- Floating Ads -->
<?php echo displayFloatingAds(1); ?>

<style>
.recruitment-agency-card {
    transition: all 0.3s ease;
}

.recruitment-agency-card:hover {
    transform: translateY(-2px);
}

.agency-name {
    color: #2c3e50;
    font-weight: 600;
    font-size: 1.25rem;
}

.agency-description {
    color: #6c757d;
    line-height: 1.6;
}

.agency-details {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.detail-item {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.detail-item i {
    margin-top: 0.25rem;
    flex-shrink: 0;
}

.detail-item .label {
    font-weight: 600;
    color: #495057;
    min-width: 80px;
}

.detail-item a {
    color: #007bff;
    text-decoration: none;
}

.detail-item a:hover {
    text-decoration: underline;
}

.agency-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
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

@media (max-width: 768px) {
    .agency-actions {
        margin-top: 1rem;
    }
    
    .detail-item {
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .detail-item .label {
        min-width: auto;
    }
}
</style>

<script>
// Share agency
function shareAgency(agencyName) {
    const shareUrl = window.location.href;
    const shareTitle = `Découvrez ${agencyName} - Cabinet de recrutement sur JobMaroc.com`;
    
    if (navigator.share) {
        navigator.share({
            title: shareTitle,
            url: shareUrl
        });
    } else {
        // Fallback for browsers that don't support Web Share API
        const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
        window.open(facebookUrl, '_blank', 'width=600,height=400');
    }
}

// Show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>

        <!-- Professional Footer Section -->
        <div class="container-fluid bg-light py-5 mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="professional-section">
                            <h3><i class="fas fa-info-circle"></i>Services Complémentaires</h3>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="enhanced-card">
                                        <div class="enhanced-card-header">
                                            <h4 class="enhanced-card-title">
                                                <i class="fas fa-search me-2"></i>Recherche d'Emploi
                                            </h4>
                                        </div>
                                        <p class="text-muted">Trouvez l'emploi parfait avec nos outils de recherche avancés et partenariats avec les meilleurs cabinets.</p>
                                        <div class="mt-3">
                                            <a href="enhanced_search.php" class="btn btn-primary">
                                                <i class="fas fa-search me-2"></i>Rechercher
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="enhanced-card">
                                        <div class="enhanced-card-header">
                                            <h4 class="enhanced-card-title">
                                                <i class="fas fa-building me-2"></i>Services Entreprises
                                            </h4>
                                        </div>
                                        <p class="text-muted">Solutions sur mesure pour les entreprises. Diffusez vos offres et accédez à une base de données de candidats qualifiés.</p>
                                        <div class="mt-3">
                                            <a href="contact.php" class="btn btn-success">
                                                <i class="fas fa-handshake me-2"></i>Nos Services
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="enhanced-card">
                                        <div class="enhanced-card-header">
                                            <h4 class="enhanced-card-title">
                                                <i class="fas fa-users me-2"></i>Notre Communauté
                                            </h4>
                                        </div>
                                        <p class="text-muted">Rejoignez notre communauté de professionnels et candidats qualifiés. Trouvez l'emploi de vos rêves ou recrutez les meilleurs talents.</p>
                                        <div class="mt-3">
                                            <a href="signup.php" class="btn btn-info">
                                                <i class="fas fa-user-plus me-2"></i>Rejoindre
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

        @media (max-width: 768px) {
            .professional-section {
                padding: 20px;
            }

            .enhanced-card {
                padding: 20px;
            }
        }
        </style>

<!-- Ad Display System CSS -->
<link href="assets/css/ads-display.css" rel="stylesheet">

<!-- Ad Display System JavaScript -->
<script src="assets/js/ads-display.js"></script>

<?php include 'frontoffice/include/footer2.php'; ?>

