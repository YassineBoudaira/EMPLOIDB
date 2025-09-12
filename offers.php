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

    <!-- Offers Header with Image -->
    <div class="container-fluid p-0">
        <div class="position-relative">
            <img class="img-fluid w-100" src="frontoffice/assets/img/offers-header.jpg" alt="Offers Header" style="height: 300px; object-fit: cover;">
            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: linear-gradient(135deg, rgba(37, 157, 171, 0.9), rgba(43, 155, 255, 0.8));">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-8 text-center">
                            <h1 class="display-4 text-white animated slideInDown mb-4">
                                <i class="fas fa-gift me-3"></i>Offres et Bonnes Affaires
                            </h1>
                            <p class="fs-5 fw-medium text-white mb-4 pb-2">
                                Découvrez les meilleures offres et réductions pour votre carrière professionnelle
                            </p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="#offers-content" class="btn btn-light py-md-3 px-md-5 me-3 animated slideInLeft">
                                    <i class="fas fa-tags me-2"></i>Voir les Offres
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

    <!-- Offers Statistics Section -->
    <div class="container-fluid py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="d-flex align-items-center bg-white rounded p-4 shadow-sm">
                        <div class="flex-shrink-0 btn btn-primary btn-square rounded-circle me-3">
                            <i class="fas fa-gift text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1"><?= $total_offers ?? 50 ?>+</h6>
                            <span class="text-muted">Offres Disponibles</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="d-flex align-items-center bg-white rounded p-4 shadow-sm">
                        <div class="flex-shrink-0 btn btn-success btn-square rounded-circle me-3">
                            <i class="fas fa-percentage text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1"><?= $avg_discount ?? 25 ?>%</h6>
                            <span class="text-muted">Réduction Moyenne</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="d-flex align-items-center bg-white rounded p-4 shadow-sm">
                        <div class="flex-shrink-0 btn btn-warning btn-square rounded-circle me-3">
                            <i class="fas fa-star text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1"><?= $featured_offers ?? 10 ?>+</h6>
                            <span class="text-muted">Offres en Vedette</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="d-flex align-items-center bg-white rounded p-4 shadow-sm">
                        <div class="flex-shrink-0 btn btn-info btn-square rounded-circle me-3">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-1"><?= $expiring_soon ?? 5 ?>+</h6>
                            <span class="text-muted">Expirent Bientôt</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Offers Content -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- Professional Header Section -->
                    <div class="professional-section">
                        <h3><i class="fas fa-gift"></i>Offres et Bonnes Affaires</h3>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="enhanced-card">
                                    <div class="enhanced-card-header">
                                        <h4 class="enhanced-card-title">Découvrez les Meilleures Offres</h4>
                                        <div class="enhanced-card-actions">
                                            <button class="btn-action btn-action-primary" onclick="refreshOffers()">
                                                <i class="fas fa-sync-alt"></i>Actualiser
                                            </button>
                                            <button class="btn-action btn-action-success" onclick="saveOffers()">
                                                <i class="fas fa-bookmark"></i>Sauvegarder
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Enhanced Search Form -->
                                    <form action="offers.php" method="GET" id="offersForm">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="form-label">Rechercher une offre</label>
                                                    <input type="text" name="keyword" class="form-control" placeholder="Ex: Formation, Services, Outils..." value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="form-label">Catégorie</label>
                                                    <select name="category" class="form-select">
                                                        <option value="">Toutes les Catégories</option>
                                                        <option value="Formation" <?= (isset($_GET['category']) && $_GET['category'] == 'Formation') ? 'selected' : '' ?>>Formation</option>
                                                        <option value="Services" <?= (isset($_GET['category']) && $_GET['category'] == 'Services') ? 'selected' : '' ?>>Services</option>
                                                        <option value="Outils" <?= (isset($_GET['category']) && $_GET['category'] == 'Outils') ? 'selected' : '' ?>>Outils</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="form-label">Trier par</label>
                                                    <select name="sort" class="form-select">
                                                        <option value="">Trier par</option>
                                                        <option value="discount_desc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'discount_desc') ? 'selected' : '' ?>>Réduction la plus élevée</option>
                                                        <option value="discount_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'discount_asc') ? 'selected' : '' ?>>Réduction la plus faible</option>
                                                        <option value="expiry_asc" <?= (isset($_GET['sort']) && $_GET['sort'] == 'expiry_asc') ? 'selected' : '' ?>>Expire bientôt</option>
                                                        <option value="featured" <?= (isset($_GET['sort']) && $_GET['sort'] == 'featured') ? 'selected' : '' ?>>Mis en avant</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label class="form-label">&nbsp;</label>
                                                    <button type="submit" class="btn btn-primary w-100">
                                                        <i class="fas fa-search me-2"></i>Rechercher
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
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
                        <h3><i class="fas fa-chart-bar"></i>Statistiques des Offres</h3>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-primary">
                                        <i class="fas fa-gift"></i>
                                    </div>
                                    <div class="stats-number"><?= $total_offers ?? 0 ?></div>
                                    <div class="stats-label">Offres Disponibles</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-success">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                    <div class="stats-number"><?= $avg_discount ?? 0 ?>%</div>
                                    <div class="stats-label">Réduction Moyenne</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-warning">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <div class="stats-number"><?= $featured_offers ?? 0 ?></div>
                                    <div class="stats-label">Offres en Vedette</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-info">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="stats-number"><?= $expiring_soon ?? 0 ?></div>
                                    <div class="stats-label">Expirent Bientôt</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    // Get statistics for offers
                    try {
                        $total_offers = $db->fetch("SELECT COUNT(*) as count FROM offers WHERE status = 'active' AND valid_until >= CURDATE()")['count'];
                        $avg_discount = $db->fetch("SELECT AVG(discount_percentage) as avg FROM offers WHERE status = 'active' AND valid_until >= CURDATE() AND discount_percentage > 0")['avg'];
                        $featured_offers = $db->fetch("SELECT COUNT(*) as count FROM offers WHERE status = 'active' AND valid_until >= CURDATE() AND featured = 1")['count'];
                        $expiring_soon = $db->fetch("SELECT COUNT(*) as count FROM offers WHERE status = 'active' AND valid_until BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")['count'];
                    } catch (Exception $e) {
                        $total_offers = 50;
                        $avg_discount = 25;
                        $featured_offers = 10;
                        $expiring_soon = 5;
                    }

                    // Build the search query
                    $where_conditions = ["o.status = 'active'", "o.valid_until >= CURDATE()"];
                    $params = [];
                    
                    // Keyword search
                    if (!empty($_GET['keyword'])) {
                        $keyword = Security::sanitizeInput($_GET['keyword']);
                        $where_conditions[] = "(o.title LIKE ? OR o.description LIKE ? OR o.merchant_name LIKE ?)";
                        $keyword_param = "%$keyword%";
                        $params = array_merge($params, [$keyword_param, $keyword_param, $keyword_param]);
                    }
                    
                    // Category filter
                    if (!empty($_GET['category'])) {
                        $where_conditions[] = "o.category = ?";
                        $params[] = Security::sanitizeInput($_GET['category']);
                    }
                    
                    // Build the complete query
                    $where_clause = implode(" AND ", $where_conditions);
                    
                    // Determine sort order
                    $sort_order = "ORDER BY o.featured DESC, o.created_at DESC";
                    if (!empty($_GET['sort'])) {
                        switch ($_GET['sort']) {
                            case 'discount_desc':
                                $sort_order = "ORDER BY o.discount_percentage DESC";
                                break;
                            case 'discount_asc':
                                $sort_order = "ORDER BY o.discount_percentage ASC";
                                break;
                            case 'expiry_asc':
                                $sort_order = "ORDER BY o.valid_until ASC";
                                break;
                            case 'featured':
                                $sort_order = "ORDER BY o.featured DESC, o.created_at DESC";
                                break;
                        }
                    }
                    
                    $query = "SELECT * FROM offers WHERE $where_clause $sort_order";
                    $offers = $db->fetchAll($query, $params);
                    $total_offers = count($offers);
                    ?>
                    
                    <div id="offers-content" class="mb-4">
                        <strong><?= $total_offers ?></strong> offre(s) trouvée(s)
                    </div>
                    
                    <?php if ($total_offers > 0): ?>
                        <div class="row">
                            <?php foreach($offers as $offer): ?>
                                <div class="col-lg-4 col-md-6 mb-4 wow fadeInUp" data-wow-delay="0.1s">
                                    <div class="card h-100 offer-card">
                                        <!-- Offer Image -->
                                        <div class="position-relative">
                                            <img src="upload/<?= htmlspecialchars($offer['image'] ?: 'default-offer.jpg') ?>" 
                                                 class="card-img-top" alt="<?= htmlspecialchars($offer['title']) ?>"
                                                 style="height: 200px; object-fit: cover;">
                                            
                                            <!-- Discount Badge -->
                                            <?php if ($offer['discount_percentage']): ?>
                                                <div class="position-absolute top-0 start-0 m-3">
                                                    <span class="badge bg-danger fs-6">
                                                        -<?= number_format($offer['discount_percentage']) ?>%
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Featured Badge -->
                                            <?php if ($offer['featured']): ?>
                                                <div class="position-absolute top-0 end-0 m-3">
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-star"></i> Mis en Avant
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Expiry Warning -->
                                            <?php 
                                            $days_until_expiry = (strtotime($offer['valid_until']) - time()) / (60 * 60 * 24);
                                            if ($days_until_expiry <= 7): ?>
                                                <div class="position-absolute bottom-0 start-0 w-100 p-2" style="background: rgba(220, 53, 69, 0.9);">
                                                    <span class="text-white small">
                                                        <i class="fas fa-clock"></i> Expire dans <?= round($days_until_expiry) ?> jour(s)
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="card-body d-flex flex-column">
                                            <h5 class="card-title"><?= htmlspecialchars($offer['title']) ?></h5>
                                            <p class="card-text text-muted"><?= htmlspecialchars(substr($offer['description'], 0, 100)) ?>...</p>
                                            
                                            <!-- Merchant Info -->
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center">
                                                    <?php if ($offer['merchant_logo']): ?>
                                                        <img src="upload/<?= htmlspecialchars($offer['merchant_logo']) ?>" 
                                                             alt="<?= htmlspecialchars($offer['merchant_name']) ?>" 
                                                             class="me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($offer['merchant_name']) ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?= htmlspecialchars($offer['category']) ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Coupon Code -->
                                            <?php if ($offer['coupon_code']): ?>
                                                <div class="mb-3">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" value="<?= htmlspecialchars($offer['coupon_code']) ?>" readonly>
                                                        <button class="btn btn-outline-secondary" type="button" onclick="copyCoupon('<?= htmlspecialchars($offer['coupon_code']) ?>')">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Usage Info -->
                                            <div class="mb-3">
                                                <small class="text-muted">
                                                    <?php if ($offer['max_uses']): ?>
                                                        <?= $offer['current_uses'] ?>/<?= $offer['max_uses'] ?> utilisations
                                                    <?php else: ?>
                                                        Utilisations illimitées
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <div class="mt-auto">
                                                <div class="d-grid gap-2">
                                                    <a href="<?= htmlspecialchars($offer['merchant_website']) ?>" 
                                                       target="_blank" 
                                                       class="btn btn-primary">
                                                        <i class="fas fa-external-link-alt me-2"></i>Obtenir l'Offre
                                                    </a>
                                                    
                                                    <?php if (Security::isLoggedIn()): ?>
                                                        <button class="btn btn-outline-primary" onclick="toggleSaveOffer(<?= $offer['id'] ?>)" id="saveOfferBtn_<?= $offer['id'] ?>">
                                                            <i class="far fa-heart" id="heartIcon_<?= $offer['id'] ?>"></i>
                                                            <span id="saveText_<?= $offer['id'] ?>">Sauvegarder</span>
                                                        </button>
                                                    <?php else: ?>
                                                        <a href="login.php" class="btn btn-outline-primary">
                                                            <i class="fas fa-sign-in-alt me-2"></i>Se Connecter
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="card-footer text-muted">
                                            <small>
                                                <i class="fas fa-calendar me-1"></i>
                                                Valide jusqu'au <?= date('d/m/Y', strtotime($offer['valid_until'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-gift fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Aucune offre trouvée</h4>
                            <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                            <a href="offers.php" class="btn btn-primary">Voir Toutes les Offres</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
// Copy coupon code to clipboard
function copyCoupon(code) {
    navigator.clipboard.writeText(code).then(function() {
        alert('Code copié dans le presse-papiers: ' + code);
    }).catch(function() {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = code;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Code copié dans le presse-papiers: ' + code);
    });
}

// Save offer functionality
function toggleSaveOffer(offerId) {
    if (!<?= Security::isLoggedIn() ? 'true' : 'false' ?>) {
        window.location.href = 'login.php';
        return;
    }
    
    fetch('ajax/save_offer.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'offer_id=' + offerId + '&action=toggle'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const heartIcon = document.getElementById('heartIcon_' + offerId);
            const saveText = document.getElementById('saveText_' + offerId);
            
            if (data.saved) {
                heartIcon.className = 'fas fa-heart text-danger';
                saveText.textContent = 'Retirer';
            } else {
                heartIcon.className = 'far fa-heart';
                saveText.textContent = 'Sauvegarder';
            }
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de la sauvegarde');
    });
}

// Refresh offers functionality
function refreshOffers() {
    location.reload();
}

// Save offers functionality
function saveOffers() {
    if (!<?= Security::isLoggedIn() ? 'true' : 'false' ?>) {
        showNotification('Veuillez vous connecter pour sauvegarder des offres', 'warning');
        return;
    }
    showNotification('Fonctionnalité de sauvegarde en cours de développement', 'info');
}

// Show notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Check saved offers on page load
<?php if (Security::isLoggedIn()): ?>
document.addEventListener('DOMContentLoaded', function() {
    // This would be populated by checking saved offers for the current user
    // For now, we'll just show the default state
});
<?php endif; ?>
</script>

<style>
.offer-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid #e0e0e0;
}

.offer-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.card-img-top {
    border-bottom: 1px solid #e0e0e0;
}

.badge {
    font-size: 0.9rem;
}

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
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
}
</style>

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
                                                <i class="fas fa-gift me-2"></i>Offres Spéciales
                                            </h4>
                                        </div>
                                        <p class="text-muted">Découvrez nos offres exclusives et réductions spéciales pour maximiser vos économies sur vos achats professionnels.</p>
                                        <div class="mt-3">
                                            <a href="offers.php" class="btn btn-primary">
                                                <i class="fas fa-tags me-2"></i>Voir les Offres
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
                                        <p class="text-muted">Solutions sur mesure pour les entreprises. Diffusez vos offres et accédez à une base de données de candidats qualifiés.</p>
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

<?php include 'frontoffice/include/footer2.php'; ?>
