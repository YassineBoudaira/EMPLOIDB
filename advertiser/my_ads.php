<?php
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';
include __DIR__ . '/../include/connexion.php';

// Check if advertiser is logged in
if (!isset($_SESSION['advertiser_id'])) {
    header('Location: login.php');
    exit;
}

$advertiser_id = $_SESSION['advertiser_id'];
$success = false;
$errors = [];

// Get advertiser info
$advertiser = $db->fetch("SELECT * FROM advertisers WHERE id = ?", [$advertiser_id]);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $ad_id = intval($_POST['ad_id'] ?? 0);
    
    if ($action === 'delete' && $ad_id > 0) {
        try {
            $db->beginTransaction();
            
            // Delete ad category relations
            $db->delete("DELETE FROM ad_category_relations WHERE ad_id = ?", [$ad_id]);
            
            // Delete campaign ads relations
            $db->delete("DELETE FROM campaign_ads WHERE ad_id = ?", [$ad_id]);
            
            // Delete performance data
            $db->delete("DELETE FROM ad_performance WHERE ad_id = ?", [$ad_id]);
            
            // Delete impressions and clicks
            $db->delete("DELETE FROM ad_impressions WHERE ad_id = ?", [$ad_id]);
            $db->delete("DELETE FROM ad_clicks WHERE ad_id = ?", [$ad_id]);
            
            // Delete the advertisement
            $db->delete("DELETE FROM advertisements WHERE id = ? AND created_by = ?", [$ad_id, $advertiser_id]);
            
            $db->commit();
            $success = "Publicité supprimée avec succès.";
            
        } catch (Exception $e) {
            $db->rollback();
            $errors[] = "Erreur lors de la suppression: " . $e->getMessage();
        }
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where_conditions = ["a.created_by = ?"];
$params = [$advertiser_id];

if ($status_filter) {
    $where_conditions[] = "a.status = ?";
    $params[] = $status_filter;
}

if ($type_filter) {
    $where_conditions[] = "a.ad_type = ?";
    $params[] = $type_filter;
}

if ($search) {
    $where_conditions[] = "(a.title LIKE ? OR a.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(" AND ", $where_conditions);

// Get advertisements with statistics
$ads = $db->fetchAll("
    SELECT a.*, 
           (SELECT COUNT(*) FROM ad_impressions ai WHERE ai.ad_id = a.id) as impressions,
           (SELECT COUNT(*) FROM ad_clicks ac WHERE ac.ad_id = a.id) as clicks,
           (SELECT COUNT(*) FROM campaign_ads ca WHERE ca.ad_id = a.id) as campaign_count
    FROM advertisements a 
    WHERE $where_clause
    ORDER BY a.created_at DESC
", $params);

// Get statistics
$stats = $db->fetch("
    SELECT 
        COUNT(*) as total_ads,
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_ads,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_ads,
        COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_ads,
        COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_ads
    FROM advertisements 
    WHERE created_by = ?
", [$advertiser_id]);

$page_title = "Mes Publicités";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | EMPLOIDB</title>
    
    <!-- Favicon -->
    <link rel="icon" href="../frontoffice/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    
    <!-- EMPLOIDB Professional Design System -->
    <link href="../assets/css/emploidb-design-system.css" rel="stylesheet">
    
    <style>
        /* Advertiser Panel Professional Styles */
        body {
            background: var(--emploidb-bg-secondary);
            font-family: var(--emploidb-font-primary);
            color: var(--emploidb-text-primary);
        }
        
        /* Advertiser Layout */
        .advertiser-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .emploidb-sidebar {
            width: 280px;
            background: var(--emploidb-bg-primary);
            border-right: 1px solid var(--emploidb-border-color);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--emploidb-border-color);
            background: var(--emploidb-gradient-primary);
        }
        
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--emploidb-white);
            font-weight: 700;
            font-size: 1.25rem;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .sidebar-nav .nav-link {
            color: var(--emploidb-text-secondary);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            border: none;
            background: transparent;
        }
        
        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            color: var(--emploidb-primary);
            background: var(--emploidb-bg-hover);
            border-left: 3px solid var(--emploidb-primary);
        }
        
        .sidebar-nav .nav-link i {
            width: 20px;
            text-align: center;
        }
        
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--emploidb-border-color);
            background: var(--emploidb-bg-primary);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--emploidb-gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--emploidb-white);
        }
        
        .user-details {
            flex: 1;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--emploidb-text-primary);
        }
        
        .user-role {
            font-size: 0.8rem;
            color: var(--emploidb-text-secondary);
        }
        
        /* Main Content */
        .advertiser-main {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            background: var(--emploidb-bg-secondary);
        }
        
        /* Content Cards */
        .content-card {
            background: var(--emploidb-white);
            border-radius: var(--emploidb-border-radius-lg);
            box-shadow: var(--emploidb-shadow-sm);
            border: 1px solid var(--emploidb-border-color);
            margin-bottom: 2rem;
        }
        
        .content-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--emploidb-border-color);
            background: var(--emploidb-bg-light);
            border-radius: var(--emploidb-border-radius-lg) var(--emploidb-border-radius-lg) 0 0;
        }
        
        .content-body {
            padding: 1.5rem;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--emploidb-white);
            border-radius: var(--emploidb-border-radius-lg);
            padding: 1.5rem;
            box-shadow: var(--emploidb-shadow-sm);
            border: 1px solid var(--emploidb-border-color);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--emploidb-shadow-md);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--emploidb-border-radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--emploidb-white);
            margin-bottom: 1rem;
        }
        
        .stat-icon.total { background: var(--emploidb-gradient-primary); }
        .stat-icon.active { background: var(--emploidb-gradient-success); }
        .stat-icon.pending { background: var(--emploidb-gradient-warning); }
        .stat-icon.rejected { background: var(--emploidb-gradient-danger); }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--emploidb-text-primary);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--emploidb-text-secondary);
            font-size: 0.9rem;
        }
        
        /* Ad Cards */
        .ad-card {
            background: var(--emploidb-white);
            border-radius: var(--emploidb-border-radius);
            padding: 1.5rem;
            box-shadow: var(--emploidb-shadow-sm);
            border: 1px solid var(--emploidb-border-color);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .ad-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--emploidb-shadow-md);
        }
        
        .ad-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .ad-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--emploidb-text-primary);
            margin-bottom: 0.5rem;
        }
        
        .ad-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .ad-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--emploidb-text-secondary);
            font-size: 0.9rem;
        }
        
        .ad-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .ad-stat {
            background: var(--emploidb-bg-light);
            padding: 0.5rem 1rem;
            border-radius: var(--emploidb-border-radius);
            text-align: center;
        }
        
        .ad-stat-number {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--emploidb-primary);
        }
        
        .ad-stat-label {
            font-size: 0.8rem;
            color: var(--emploidb-text-secondary);
        }
        
        .ad-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-active { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-inactive { background: #e2e3e5; color: #383d41; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .emploidb-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .emploidb-sidebar.show {
                transform: translateX(0);
            }
            
            .advertiser-main {
                margin-left: 0;
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .ad-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .ad-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="advertiser-wrapper">
        <!-- Sidebar -->
        <?php include 'include/advertiser_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="advertiser-main">
            <!-- Page Header -->
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-ad text-primary"></i>
                            Mes Publicités
                        </h1>
                        <p class="text-muted mb-0">Gérez vos publicités et suivez leurs performances</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="create_ad.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nouvelle Publicité
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="content-body">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon total">
                            <i class="fas fa-ad"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($stats['total_ads']); ?></div>
                        <div class="stat-label">Total Publicités</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon active">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($stats['active_ads']); ?></div>
                        <div class="stat-label">Actives</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($stats['pending_ads']); ?></div>
                        <div class="stat-label">En Attente</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon rejected">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($stats['rejected_ads']); ?></div>
                        <div class="stat-label">Rejetées</div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="content-card">
                    <div class="content-header">
                        <h4><i class="fas fa-filter"></i> Filtres</h4>
                    </div>
                    <div class="content-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Rechercher</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Titre ou description...">
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Statut</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Actives</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejetées</option>
                                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactives</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="">Tous les types</option>
                                    <option value="banner" <?php echo $type_filter === 'banner' ? 'selected' : ''; ?>>Bannière</option>
                                    <option value="popup" <?php echo $type_filter === 'popup' ? 'selected' : ''; ?>>Popup</option>
                                    <option value="sidebar" <?php echo $type_filter === 'sidebar' ? 'selected' : ''; ?>>Sidebar</option>
                                    <option value="inline" <?php echo $type_filter === 'inline' ? 'selected' : ''; ?>>Inline</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <a href="my_ads.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Advertisements List -->
                <div class="content-card">
                    <div class="content-header">
                        <h4><i class="fas fa-list"></i> Publicités (<?php echo count($ads); ?>)</h4>
                    </div>
                    <div class="content-body">
                        <?php if (empty($ads)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-ad fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucune publicité trouvée</h5>
                                <p class="text-muted">Commencez par créer votre première publicité</p>
                                <a href="create_ad.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Créer une Publicité
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($ads as $ad): ?>
                                <div class="ad-card">
                                    <div class="ad-header">
                                        <div class="flex-grow-1">
                                            <div class="ad-title"><?php echo htmlspecialchars($ad['title']); ?></div>
                                            <div class="ad-meta">
                                                <div class="ad-meta-item">
                                                    <i class="fas fa-tag"></i>
                                                    <?php echo ucfirst($ad['ad_type']); ?>
                                                </div>
                                                <div class="ad-meta-item">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?php echo ucfirst($ad['position']); ?>
                                                </div>
                                                <div class="ad-meta-item">
                                                    <i class="fas fa-calendar"></i>
                                                    <?php echo date('d/m/Y', strtotime($ad['created_at'])); ?>
                                                </div>
                                                <div class="ad-meta-item">
                                                    <i class="fas fa-bullhorn"></i>
                                                    <?php echo $ad['campaign_count']; ?> campagne(s)
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column align-items-end gap-2">
                                            <span class="status-badge status-<?php echo $ad['status']; ?>">
                                                <?php echo ucfirst($ad['status']); ?>
                                            </span>
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="edit_ad.php?id=<?php echo $ad['id']; ?>">
                                                        <i class="fas fa-edit"></i> Modifier
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="view_ad.php?id=<?php echo $ad['id']; ?>">
                                                        <i class="fas fa-eye"></i> Voir
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteAd(<?php echo $ad['id']; ?>)">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if ($ad['description']): ?>
                                        <p class="text-muted mb-2"><?php echo htmlspecialchars($ad['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="ad-stats">
                                        <div class="ad-stat">
                                            <div class="ad-stat-number"><?php echo number_format($ad['impressions']); ?></div>
                                            <div class="ad-stat-label">Impressions</div>
                                        </div>
                                        <div class="ad-stat">
                                            <div class="ad-stat-number"><?php echo number_format($ad['clicks']); ?></div>
                                            <div class="ad-stat-label">Clics</div>
                                        </div>
                                        <div class="ad-stat">
                                            <div class="ad-stat-number">
                                                <?php echo $ad['impressions'] > 0 ? number_format(($ad['clicks'] / $ad['impressions']) * 100, 2) : '0'; ?>%
                                            </div>
                                            <div class="ad-stat-label">CTR</div>
                                        </div>
                                        <div class="ad-stat">
                                            <div class="ad-stat-number"><?php echo number_format($ad['budget'], 2); ?> DH</div>
                                            <div class="ad-stat-label">Budget</div>
                                        </div>
                                    </div>
                                    
                                    <div class="ad-actions">
                                        <a href="edit_ad.php?id=<?php echo $ad['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                        <a href="view_ad.php?id=<?php echo $ad['id']; ?>" class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-eye"></i> Voir
                                        </a>
                                        <a href="ad_analytics.php?id=<?php echo $ad['id']; ?>" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-chart-line"></i> Analytics
                                        </a>
                                        <button class="btn btn-outline-danger btn-sm" onclick="deleteAd(<?php echo $ad['id']; ?>)">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cette publicité ? Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form method="POST" id="deleteForm">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="ad_id" id="deleteAdId">
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        function deleteAd(adId) {
            document.getElementById('deleteAdId').value = adId;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        // Auto-refresh stats every 30 seconds
        setInterval(function() {
            // You can add AJAX call here to refresh stats
        }, 30000);
    </script>
</body>
</html>
