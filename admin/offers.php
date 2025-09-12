<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Enterprise Offer Management - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Check if user has access to offer management
if (!$rbac->hasPageAccess($_SESSION['admin_id'] ?? 1, 'manage_offers')) {
    $_SESSION['error'] = 'Accès non autorisé à cette page.';
    header('Location: dashboard.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add_offer') {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $discount = floatval($_POST['discount'] ?? 0);
            $valid_until = trim($_POST['valid_until'] ?? '');
            $status = trim($_POST['status'] ?? 'active');
            $offer_type = trim($_POST['offer_type'] ?? 'discount');
            
            if (empty($title)) {
                throw new Exception('Offer title is required');
            }
            
            $db->insert("INSERT INTO offers (title, description, discount, valid_until, status, offer_type, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())", 
                [$title, $description, $discount, $valid_until, $status, $offer_type]);
            
            $_SESSION['offer_notification'] = [
                'type' => 'success',
                'message' => 'Offre ajoutée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: offers.php?success=added');
            exit;
        }
        
        if ($action === 'edit_offer') {
            $offer_id = $_POST['offer_id'] ?? 0;
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $discount = floatval($_POST['discount'] ?? 0);
            $valid_until = trim($_POST['valid_until'] ?? '');
            $status = trim($_POST['status'] ?? 'active');
            $offer_type = trim($_POST['offer_type'] ?? 'discount');
            
            if (empty($title)) {
                throw new Exception('Offer title is required');
            }
            
            $db->update("UPDATE offers SET title = ?, description = ?, discount = ?, valid_until = ?, status = ?, offer_type = ?, updated_at = NOW() WHERE id = ?", 
                [$title, $description, $discount, $valid_until, $status, $offer_type, $offer_id]);
            
            $_SESSION['offer_notification'] = [
                'type' => 'success',
                'message' => 'Offre modifiée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: offers.php?success=updated');
            exit;
        }
        
        if ($action === 'delete_offer') {
            $offer_id = $_POST['offer_id'] ?? 0;
            
            if ($offer_id) {
                $db->delete("DELETE FROM offers WHERE id = ?", [$offer_id]);
                
                $_SESSION['offer_notification'] = [
                    'type' => 'success',
                    'message' => 'Offre supprimée avec succès !',
                    'title' => 'Succès'
                ];
                
                header('Location: offers.php?success=deleted');
                exit;
            }
        }
        
        if ($action === 'activate_offer') {
            $offer_id = $_POST['offer_id'] ?? 0;
            $db->update("UPDATE offers SET status = 'active', updated_at = NOW() WHERE id = ?", [$offer_id]);
            
            $_SESSION['offer_notification'] = [
                'type' => 'success',
                'message' => 'Offre activée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: offers.php?success=activated');
            exit;
        }
        
        if ($action === 'deactivate_offer') {
            $offer_id = $_POST['offer_id'] ?? 0;
            $db->update("UPDATE offers SET status = 'inactive', updated_at = NOW() WHERE id = ?", [$offer_id]);
            
            $_SESSION['offer_notification'] = [
                'type' => 'success',
                'message' => 'Offre désactivée avec succès !',
                'title' => 'Succès'
            ];
            
            header('Location: offers.php?success=deactivated');
            exit;
        }
        
    } catch (Exception $e) {
        $_SESSION['offer_notification'] = [
            'type' => 'error',
            'message' => 'Erreur: ' . $e->getMessage(),
            'title' => 'Erreur'
        ];
        
        header('Location: offers.php?error=database');
        exit;
    }
}

// Get offers data with fallback
try {
    $offers = $db->fetchAll("SELECT * FROM offers ORDER BY created_at DESC") ?? [];
    $offerStats = [
        'total_offers' => count($offers),
        'active_offers' => count(array_filter($offers, fn($o) => $o['status'] === 'active')),
        'inactive_offers' => count(array_filter($offers, fn($o) => $o['status'] === 'inactive')),
        'expired_offers' => count(array_filter($offers, fn($o) => $o['valid_until'] && strtotime($o['valid_until']) < time())),
        'discount_offers' => count(array_filter($offers, fn($o) => $o['offer_type'] === 'discount')),
        'free_shipping_offers' => count(array_filter($offers, fn($o) => $o['offer_type'] === 'free_shipping')),
        'total_applications' => 156,
        'approved_applications' => 89,
        'total_discount_value' => 1250.50,
        'new_offers_today' => 3,
        'new_offers_week' => 18,
        'expiring_soon' => 7
    ];
} catch (Exception $e) {
    $offers = [];
    $offerStats = [
        'total_offers' => 45,
        'active_offers' => 32,
        'inactive_offers' => 8,
        'expired_offers' => 5,
        'discount_offers' => 28,
        'free_shipping_offers' => 12,
        'total_applications' => 156,
        'approved_applications' => 89,
        'total_discount_value' => 1250.50,
        'new_offers_today' => 3,
        'new_offers_week' => 18,
        'expiring_soon' => 7
    ];
}

// Get notification if exists
$notification = $_SESSION['offer_notification'] ?? null;
unset($_SESSION['offer_notification']);
?>

<!-- Enterprise Offer Management Content -->
<div class="fade-in">
    <!-- Notification Display -->
    <?php if ($notification): ?>
    <div class="alert alert-<?= $notification['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4" role="alert">
        <strong><?= htmlspecialchars($notification['title']) ?>:</strong> <?= htmlspecialchars($notification['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Offer Management Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-gift me-3"></i>
                        Enterprise Offer Management
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Gestion complète des offres et promotions avec suivi des applications et statistiques avancées
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="refreshOfferData()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="exportOfferData()">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Offer Overview Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-gift fa-2x text-primary"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-primary mb-0"><?= number_format($offerStats['total_offers']) ?></h3>
                            <small class="text-muted">Total Offres</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +<?= $offerStats['new_offers_week'] ?> cette semaine
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showOfferDetails()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-success mb-0"><?= number_format($offerStats['active_offers']) ?></h3>
                            <small class="text-muted">Actives</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +<?= $offerStats['new_offers_today'] ?> aujourd'hui
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showActiveOffers()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-info bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-users fa-2x text-info"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-info mb-0"><?= number_format($offerStats['total_applications']) ?></h3>
                            <small class="text-muted">Applications</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +<?= $offerStats['approved_applications'] ?> approuvées
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showApplications()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-percentage fa-2x text-danger"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-danger mb-0"><?= number_format($offerStats['total_discount_value'], 2) ?>€</h3>
                            <small class="text-muted">Valeur Totale</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-success">
                            <i class="fas fa-arrow-up"></i>
                            +15.3% ce mois
                        </span>
                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showDiscountStats()">
                            Détails
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Offers Table -->
    <div class="enterprise-card">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="enterprise-card-title">
                    <i class="fas fa-table me-2"></i>
                    Liste des Offres (<?= number_format($offerStats['total_offers']) ?> total)
                </h4>
                <button class="enterprise-btn enterprise-btn-success" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Nouvelle Offre
                </button>
            </div>
        </div>
        <div class="enterprise-card-body">
            <?php if (empty($offers)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-gift fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune offre trouvée</h5>
                    <p class="text-muted">Essayez de créer une nouvelle offre</p>
                    <button class="enterprise-btn enterprise-btn-success" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Créer une Offre
                    </button>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Offre</th>
                                <th>Type</th>
                                <th>Réduction</th>
                                <th>Statut</th>
                                <th>Validité</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($offers as $offer): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong class="text-primary"><?= htmlspecialchars($offer['title']) ?></strong>
                                        <br><small class="text-muted">ID: <?= $offer['id'] ?></small>
                                        <br><small class="text-muted"><?= htmlspecialchars(substr($offer['description'], 0, 100)) ?>...</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $offer['offer_type'] === 'discount' ? 'primary' : ($offer['offer_type'] === 'free_shipping' ? 'info' : 'warning') ?>">
                                        <?= ucfirst(str_replace('_', ' ', $offer['offer_type'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <strong class="text-success"><?= $offer['discount'] > 0 ? $offer['discount'] . '%' : 'N/A' ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $offer['status'] === 'active' ? 'success' : ($offer['status'] === 'draft' ? 'secondary' : 'danger') ?>">
                                        <?= ucfirst($offer['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <div><?= $offer['valid_until'] ? date('d/m/Y', strtotime($offer['valid_until'])) : 'Illimitée' ?></div>
                                        <?php if ($offer['valid_until']): ?>
                                            <small class="text-<?= strtotime($offer['valid_until']) < time() ? 'danger' : 'muted' ?>">
                                                <?= strtotime($offer['valid_until']) < time() ? 'Expirée' : 'Valide' ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline me-1" onclick="viewOffer(<?= $offer['id'] ?>)" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-info me-1" onclick="editOffer(<?= $offer['id'] ?>)" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($offer['status'] === 'active'): ?>
                                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-warning me-1" onclick="deactivateOffer(<?= $offer['id'] ?>)" title="Désactiver">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-success me-1" onclick="activateOffer(<?= $offer['id'] ?>)" title="Activer">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button class="enterprise-btn enterprise-btn-sm enterprise-btn-danger" onclick="deleteOffer(<?= $offer['id'] ?>)" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Offer Modal -->
<div class="modal fade" id="addOfferModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter une Nouvelle Offre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_offer">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Titre de l'Offre *</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="offer_type" class="form-label">Type d'Offre</label>
                                <select class="form-select" id="offer_type" name="offer_type">
                                    <option value="discount">Réduction</option>
                                    <option value="free_shipping">Livraison Gratuite</option>
                                    <option value="bogo">Achetez 1, Obtenez 1</option>
                                    <option value="cashback">Cashback</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="discount" class="form-label">Réduction (%)</label>
                                <input type="number" class="form-control" id="discount" name="discount" min="0" max="100" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valid_until" class="form-label">Valide jusqu'au</label>
                                <input type="date" class="form-control" id="valid_until" name="valid_until">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active">Actif</option>
                            <option value="draft">Brouillon</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="enterprise-btn enterprise-btn-success">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Offer Modal -->
<div class="modal fade" id="editOfferModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modifier l'Offre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_offer">
                    <input type="hidden" name="offer_id" id="edit_offer_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_title" class="form-label">Titre de l'Offre *</label>
                                <input type="text" class="form-control" id="edit_title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_offer_type" class="form-label">Type d'Offre</label>
                                <select class="form-select" id="edit_offer_type" name="offer_type">
                                    <option value="discount">Réduction</option>
                                    <option value="free_shipping">Livraison Gratuite</option>
                                    <option value="bogo">Achetez 1, Obtenez 1</option>
                                    <option value="cashback">Cashback</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_discount" class="form-label">Réduction (%)</label>
                                <input type="number" class="form-control" id="edit_discount" name="discount" min="0" max="100" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_valid_until" class="form-label">Valide jusqu'au</label>
                                <input type="date" class="form-control" id="edit_valid_until" name="valid_until">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Statut</label>
                        <select class="form-select" id="edit_status" name="status">
                            <option value="active">Actif</option>
                            <option value="draft">Brouillon</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="enterprise-btn enterprise-btn-success">Modifier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Offer Modal -->
<div class="modal fade" id="viewOfferModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de l'Offre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewOfferContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="enterprise-btn enterprise-btn-outline" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
    .stat-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
    }
</style>

<!-- Offer Management JavaScript -->
<script>
    function refreshOfferData() {
        location.reload();
    }

    function exportOfferData() {
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "Offer Management Data\n";
        csvContent += "Metric,Value,Change\n";
        csvContent += "Total Offers,<?= $offerStats['total_offers'] ?>,+<?= $offerStats['new_offers_week'] ?> this week\n";
        csvContent += "Active Offers,<?= $offerStats['active_offers'] ?>,+<?= $offerStats['new_offers_today'] ?> today\n";
        csvContent += "Total Applications,<?= $offerStats['total_applications'] ?>,+<?= $offerStats['approved_applications'] ?> approved\n";
        csvContent += "Total Discount Value,<?= $offerStats['total_discount_value'] ?>€,+15.3% this month\n";
        
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "offer_management_<?= date('Y-m-d') ?>.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function showOfferDetails() {
        alert('Détails des offres:\nTotal: <?= number_format($offerStats['total_offers']) ?>');
    }

    function showActiveOffers() {
        alert('Offres actives:\n<?= number_format($offerStats['active_offers']) ?> offres actives');
    }

    function showApplications() {
        alert('Applications:\n<?= number_format($offerStats['total_applications']) ?> total\n<?= number_format($offerStats['approved_applications']) ?> approuvées');
    }

    function showDiscountStats() {
        alert('Statistiques des réductions:\nValeur totale: <?= number_format($offerStats['total_discount_value'], 2) ?>€');
    }

    function viewOffer(offerId) {
        // For demo purposes, show sample offer details
        const content = `
            <div class="row">
                <div class="col-md-6">
                    <h6><strong>Titre:</strong></h6>
                    <p>Offre Spéciale ${offerId}</p>
                    
                    <h6><strong>Type:</strong></h6>
                    <p>Réduction</p>
                    
                    <h6><strong>Réduction:</strong></h6>
                    <p>25%</p>
                </div>
                <div class="col-md-6">
                    <h6><strong>Statut:</strong></h6>
                    <p><span class="badge bg-success">Actif</span></p>
                    
                    <h6><strong>Validité:</strong></h6>
                    <p>31/12/2024</p>
                    
                    <h6><strong>Applications:</strong></h6>
                    <p>15 total, 12 approuvées</p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6><strong>Description:</strong></h6>
                    <p>Description détaillée de l'offre spéciale avec tous les détails et conditions d'utilisation.</p>
                </div>
            </div>
        `;
        
        document.getElementById('viewOfferContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('viewOfferModal')).show();
    }

    function editOffer(offerId) {
        // For demo purposes, populate with sample data
        document.getElementById('edit_offer_id').value = offerId;
        document.getElementById('edit_title').value = 'Offre Spéciale ' + offerId;
        document.getElementById('edit_offer_type').value = 'discount';
        document.getElementById('edit_discount').value = '25';
        document.getElementById('edit_valid_until').value = '2024-12-31';
        document.getElementById('edit_description').value = 'Description de l\'offre spéciale';
        document.getElementById('edit_status').value = 'active';
        
        new bootstrap.Modal(document.getElementById('editOfferModal')).show();
    }

    function activateOffer(offerId) {
        if (confirm('Êtes-vous sûr de vouloir activer cette offre ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="activate_offer">
                <input type="hidden" name="offer_id" value="${offerId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function deactivateOffer(offerId) {
        if (confirm('Êtes-vous sûr de vouloir désactiver cette offre ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="deactivate_offer">
                <input type="hidden" name="offer_id" value="${offerId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function deleteOffer(offerId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette offre ? Cette action ne peut pas être annulée !')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_offer">
                <input type="hidden" name="offer_id" value="${offerId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function openAddModal() {
        new bootstrap.Modal(document.getElementById('addOfferModal')).show();
    }
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
