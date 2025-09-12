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

    <!-- Enhanced Contact Content -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- Professional Header Section -->
                    <div class="professional-section">
                        <h3><i class="fas fa-phone"></i>Contactez-Nous</h3>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="enhanced-card">
                                    <div class="enhanced-card-header">
                                        <h4 class="enhanced-card-title">Nous sommes là pour vous aider</h4>
                                        <div class="enhanced-card-actions">
                                            <button class="btn-action btn-action-primary" onclick="refreshContact()">
                                                <i class="fas fa-sync-alt"></i>Actualiser
                                            </button>
                                            <button class="btn-action btn-action-success" onclick="openSupportTicket()">
                                                <i class="fas fa-ticket-alt"></i>Ticket Support
                                            </button>
                                        </div>
                                    </div>
                                    <p class="text-muted">Contactez-nous pour toute question, suggestion ou assistance technique. Notre équipe est disponible pour vous aider.</p>
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
                    
                    <!-- Contact Information -->
                    <div class="row g-4 mb-5">
                        <div class="col-md-4 wow fadeIn" data-wow-delay="0.1s">
                            <div class="contact-info-card">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="contact-icon mb-3">
                                            <i class="fas fa-map-marker-alt text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Adresse</h5>
                                        <p class="card-text text-muted">54 Qu Hrilla, Safi, MAROC</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 wow fadeIn" data-wow-delay="0.3s">
                            <div class="contact-info-card">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="contact-icon mb-3">
                                            <i class="fas fa-envelope text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Email</h5>
                                        <p class="card-text text-muted">Info@JobMaroc.com</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 wow fadeIn" data-wow-delay="0.5s">
                            <div class="contact-info-card">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body text-center p-4">
                                        <div class="contact-icon mb-3">
                                            <i class="fas fa-phone text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Téléphone</h5>
                                        <p class="card-text text-muted">+212 697 825 008</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form and Map -->
                    <div class="row g-4">
                        <div class="col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <h4 class="mb-4">
                                        <i class="fas fa-map-marked-alt text-primary me-2"></i>
                                        Notre Localisation
                                    </h4>
                                    <iframe class="position-relative rounded w-100"
                                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3001156.4288297426!2d-78.01371936852176!3d42.72876761954724!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x4ccc4bf0f123a5a9%3A0xddcfc6c1de189567!2sNew%20York%2C%20USA!5e0!3m2!1sen!2sbd!4v1603794290143!5m2!1sen!2sbd"
                                        frameborder="0" style="height: 400px; border:0;" allowfullscreen="" aria-hidden="false"
                                        tabindex="0"></iframe>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="wow fadeInUp" data-wow-delay="0.5s">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-4">
                                        <h4 class="mb-4">
                                            <i class="fas fa-envelope text-primary me-2"></i>
                                            Envoyez-nous un Message
                                        </h4>
                                        <form id="contactForm">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="name" placeholder="Votre Nom" required>
                                                        <label for="name">Votre Nom</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="email" class="form-control" id="email" placeholder="Votre Email" required>
                                                        <label for="email">Votre Email</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" id="subject" placeholder="Sujet" required>
                                                        <label for="subject">Sujet</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <textarea class="form-control" placeholder="Votre message" id="message" style="height: 150px" required></textarea>
                                                        <label for="message">Message</label>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <button class="btn btn-primary w-100 py-3" type="submit">
                                                        <i class="fas fa-paper-plane me-2"></i>
                                                        Envoyer Message
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5 class="text-primary mb-3">
                                                <i class="fas fa-clock me-2"></i>
                                                Heures d'Ouverture
                                            </h5>
                                            <ul class="list-unstyled">
                                                <li class="mb-2">
                                                    <strong>Lundi - Vendredi:</strong> 9:00 - 18:00
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Samedi:</strong> 9:00 - 13:00
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Dimanche:</strong> Fermé
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h5 class="text-primary mb-3">
                                                <i class="fas fa-info-circle me-2"></i>
                                                Informations Supplémentaires
                                            </h5>
                                            <ul class="list-unstyled">
                                                <li class="mb-2">
                                                    <i class="fas fa-check text-success me-2"></i>
                                                    Support technique 24/7
                                                </li>
                                                <li class="mb-2">
                                                    <i class="fas fa-check text-success me-2"></i>
                                                    Réponse sous 24h
                                                </li>
                                                <li class="mb-2">
                                                    <i class="fas fa-check text-success me-2"></i>
                                                    Consultation gratuite
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
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
                                        <i class="fas fa-headset me-2"></i>Support Client
                                    </h4>
                                </div>
                                <p class="text-muted">Notre équipe de support est disponible pour vous accompagner dans tous vos besoins. Contactez-nous pour une assistance personnalisée.</p>
                                <div class="mt-3">
                                    <a href="contact.php" class="btn btn-primary">
                                        <i class="fas fa-phone me-2"></i>Nous Contacter
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
.contact-info-card {
    transition: all 0.3s ease;
}

.contact-info-card:hover {
    transform: translateY(-5px);
}

.contact-icon {
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

.form-control {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

@media (max-width: 768px) {
    .contact-icon {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
    }
}
</style>

<script>
// Contact form submission
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(this);
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const subject = document.getElementById('subject').value;
    const message = document.getElementById('message').value;
    
    // Basic validation
    if (!name || !email || !subject || !message) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'warning');
        return;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showNotification('Veuillez entrer une adresse email valide', 'warning');
        return;
    }
    
    // Simulate form submission
    showNotification('Message envoyé avec succès! Nous vous répondrons dans les plus brefs délais.', 'success');
    
    // Reset form
    this.reset();
});

// Show notification
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
</script>

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
// Contact form submission
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(this);
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const subject = document.getElementById('subject').value;
    const message = document.getElementById('message').value;
    
    // Basic validation
    if (!name || !email || !subject || !message) {
        showNotification('Veuillez remplir tous les champs obligatoires', 'warning');
        return;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showNotification('Veuillez entrer une adresse email valide', 'warning');
        return;
    }
    
    // Submit form via AJAX
    fetch('ajax/submit_contact_form.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Message envoyé avec succès! Nous vous répondrons dans les plus brefs délais.', 'success');
            this.reset();
        } else {
            showNotification(data.message || 'Erreur lors de l\'envoi du message', 'warning');
        }
    })
    .catch(error => {
        showNotification('Erreur de connexion', 'warning');
    });
});

// Show notification function
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
    
    // Initialize WOW.js for animations
    if (typeof WOW !== 'undefined') {
        new WOW().init();
    }
});
</script>
 

