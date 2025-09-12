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
    $campaign_id = intval($_POST['campaign_id'] ?? 0);
    
    if ($action === 'delete' && $campaign_id > 0) {
        try {
            $db->beginTransaction();
            
            // Delete campaign ads relations
            $db->delete("DELETE FROM campaign_ads WHERE campaign_id = ?", [$campaign_id]);
            
            // Delete the campaign
            $db->delete("DELETE FROM ad_campaigns WHERE id = ? AND created_by = ?", [$campaign_id, $advertiser_id]);
            
            $db->commit();
            $success = "Campagne supprimée avec succès.";
            
        } catch (Exception $e) {
            $db->rollback();
            $errors[] = "Erreur lors de la suppression: " . $e->getMessage();
        }
    }
}

// Get filters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where_conditions = ["c.created_by = ?"];
$params = [$advertiser_id];

if ($status_filter) {
    $where_conditions[] = "c.status = ?";
    $params[] = $status_filter;
}

if ($search) {
    $where_conditions[] = "(c.name LIKE ? OR c.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = implode(" AND ", $where_conditions);

// Get campaigns with statistics
$campaigns = $db->fetchAll("
    SELECT c.*, 
           (SELECT COUNT(*) FROM campaign_ads ca WHERE ca.campaign_id = c.id) as ads_count,
           (SELECT SUM(a.budget) FROM advertisements a 
            JOIN campaign_ads ca ON a.id = ca.ad_id 
            WHERE ca.campaign_id = c.id) as total_ad_budget,
           (SELECT SUM(ap.impressions) FROM ad_performance ap 
            JOIN campaign_ads ca ON ap.ad_id = ca.ad_id 
            WHERE ca.campaign_id = c.id) as total_impressions,
           (SELECT SUM(ap.clicks) FROM ad_performance ap 
            JOIN campaign_ads ca ON ap.ad_id = ca.ad_id 
            WHERE ca.campaign_id = c.id) as total_clicks
    FROM ad_campaigns c 
    WHERE $where_clause
    ORDER BY c.created_at DESC
", $params);

// Get statistics
$stats = $db->fetch("
    SELECT 
        COUNT(*) as total_campaigns,
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_campaigns,
        COUNT(CASE WHEN status = 'paused' THEN 1 END) as paused_campaigns,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_campaigns,
        COALESCE(SUM(budget), 0) as total_budget,
        COALESCE(SUM(spent), 0) as total_spent
    FROM ad_campaigns 
    WHERE created_by = ?
", [$advertiser_id]);

$page_title = "Mes Campagnes";
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
        .stat-icon.paused { background: var(--emploidb-gradient-warning); }
        .stat-icon.completed { background: var(--emploidb-gradient-info); }
        
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
        
        /* Campaign Cards */
        .campaign-card {
            background: var(--emploidb-white);
            border-radius: var(--emploidb-border-radius);
            padding: 1.5rem;
            box-shadow: var(--emploidb-shadow-sm);
            border: 1px solid var(--emploidb-border-color);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .campaign-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--emploidb-shadow-md);
        }
        
        .campaign-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .campaign-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--emploidb-text-primary);
            margin-bottom: 0.5rem;
        }
        
        .campaign-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .campaign-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--emploidb-text-secondary);
            font-size: 0.9rem;
        }
        
        .campaign-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .campaign-stat {
            background: var(--emploidb-bg-light);
            padding: 0.5rem 1rem;
            border-radius: var(--emploidb-border-radius);
            text-align: center;
        }
        
        .campaign-stat-number {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--emploidb-primary);
        }
        
        .campaign-stat-label {
            font-size: 0.8rem;
            color: var(--emploidb-text-secondary);
        }
        
        .campaign-actions {
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
        .status-paused { background: #fff3cd; color: #856404; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        
        /* Progress Bar */
        .progress {
            height: 8px;
            border-radius: 4px;
            background: var(--emploidb-bg-light);
        }
        
        .progress-bar {
            background: var(--emploidb-gradient-primary);
        }
        
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
            
            .campaign-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .campaign-actions {
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
                            <i class="fas fa-bullhorn text-primary"></i>
                            Mes Campagnes
                        </h1>
                        <p class="text-muted mb-0">Gérez vos campagnes publicitaires et suivez leurs performances</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="create_campaign.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nouvelle Campagne
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
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($stats['total_campaigns']); ?></div>
                        <div class="stat-label">Total Campagnes</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon active">
                            <i class="fas fa-play-circle"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($stats['active_campaigns']); ?></div>
                        <div class="stat-label">Actives</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon paused">
                            <i class="fas fa-pause-circle"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($stats['paused_campaigns']); ?></div>
                        <div class="stat-label">En Pause</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon completed">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($stats['completed_campaigns']); ?></div>
                        <div class="stat-label">Terminées</div>
                    </div>
                </div>
                
                <!-- Budget Overview -->
                <div class="content-card">
                    <div class="content-header">
                        <h4><i class="fas fa-dollar-sign"></i> Aperçu du Budget</h4>
                    </div>
                    <div class="content-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Budget Total</span>
                                    <strong><?php echo number_format($stats['total_budget'], 2); ?> DH</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Dépensé</span>
                                    <strong><?php echo number_format($stats['total_spent'], 2); ?> DH</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Restant</span>
                                    <strong><?php echo number_format($stats['total_budget'] - $stats['total_spent'], 2); ?> DH</strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="progress mb-2">
                                    <div class="progress-bar" style="width: <?php echo $stats['total_budget'] > 0 ? ($stats['total_spent'] / $stats['total_budget']) * 100 : 0; ?>%"></div>
                                </div>
                                <small class="text-muted">Progression du budget</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="content-card">
                    <div class="content-header">
                        <h4><i class="fas fa-filter"></i> Filtres</h4>
                    </div>
                    <div class="content-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <label for="search" class="form-label">Rechercher</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Nom ou description de campagne...">
                            </div>
                            <div class="col-md-4">
                                <label for="status" class="form-label">Statut</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Tous les statuts</option>
                                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Actives</option>
                                    <option value="paused" <?php echo $status_filter === 'paused' ? 'selected' : ''; ?>>En pause</option>
                                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Terminées</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <a href="my_campaigns.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Campaigns List -->
                <div class="content-card">
                    <div class="content-header">
                        <h4><i class="fas fa-list"></i> Campagnes (<?php echo count($campaigns); ?>)</h4>
                    </div>
                    <div class="content-body">
                        <?php if (empty($campaigns)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucune campagne trouvée</h5>
                                <p class="text-muted">Commencez par créer votre première campagne</p>
                                <a href="create_campaign.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Créer une Campagne
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($campaigns as $campaign): ?>
                                <div class="campaign-card">
                                    <div class="campaign-header">
                                        <div class="flex-grow-1">
                                            <div class="campaign-title"><?php echo htmlspecialchars($campaign['name']); ?></div>
                                            <div class="campaign-meta">
                                                <div class="campaign-meta-item">
                                                    <i class="fas fa-calendar"></i>
                                                    <?php echo date('d/m/Y', strtotime($campaign['created_at'])); ?>
                                                </div>
                                                <div class="campaign-meta-item">
                                                    <i class="fas fa-ad"></i>
                                                    <?php echo $campaign['ads_count']; ?> publicité(s)
                                                </div>
                                                <div class="campaign-meta-item">
                                                    <i class="fas fa-dollar-sign"></i>
                                                    <?php echo number_format($campaign['budget'], 2); ?> DH
                                                </div>
                                                <?php if ($campaign['start_date']): ?>
                                                    <div class="campaign-meta-item">
                                                        <i class="fas fa-play"></i>
                                                        <?php echo date('d/m/Y', strtotime($campaign['start_date'])); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($campaign['end_date']): ?>
                                                    <div class="campaign-meta-item">
                                                        <i class="fas fa-stop"></i>
                                                        <?php echo date('d/m/Y', strtotime($campaign['end_date'])); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column align-items-end gap-2">
                                            <span class="status-badge status-<?php echo $campaign['status']; ?>">
                                                <?php echo ucfirst($campaign['status']); ?>
                                            </span>
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="edit_campaign.php?id=<?php echo $campaign['id']; ?>">
                                                        <i class="fas fa-edit"></i> Modifier
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="view_campaign.php?id=<?php echo $campaign['id']; ?>">
                                                        <i class="fas fa-eye"></i> Voir
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteCampaign(<?php echo $campaign['id']; ?>)">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if ($campaign['description']): ?>
                                        <p class="text-muted mb-2"><?php echo htmlspecialchars($campaign['description']); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="campaign-stats">
                                        <div class="campaign-stat">
                                            <div class="campaign-stat-number"><?php echo number_format($campaign['total_impressions'] ?? 0); ?></div>
                                            <div class="campaign-stat-label">Impressions</div>
                                        </div>
                                        <div class="campaign-stat">
                                            <div class="campaign-stat-number"><?php echo number_format($campaign['total_clicks'] ?? 0); ?></div>
                                            <div class="campaign-stat-label">Clics</div>
                                        </div>
                                        <div class="campaign-stat">
                                            <div class="campaign-stat-number">
                                                <?php 
                                                $ctr = ($campaign['total_impressions'] ?? 0) > 0 ? 
                                                    (($campaign['total_clicks'] ?? 0) / ($campaign['total_impressions'] ?? 1)) * 100 : 0;
                                                echo number_format($ctr, 2);
                                                ?>%
                                            </div>
                                            <div class="campaign-stat-label">CTR</div>
                                        </div>
                                        <div class="campaign-stat">
                                            <div class="campaign-stat-number"><?php echo number_format($campaign['total_ad_budget'] ?? 0, 2); ?> DH</div>
                                            <div class="campaign-stat-label">Budget Ads</div>
                                        </div>
                                    </div>
                                    
                                    <div class="campaign-actions">
                                        <a href="edit_campaign.php?id=<?php echo $campaign['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                        <a href="view_campaign.php?id=<?php echo $campaign['id']; ?>" class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-eye"></i> Voir
                                        </a>
                                        <a href="campaign_analytics.php?id=<?php echo $campaign['id']; ?>" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-chart-line"></i> Analytics
                                        </a>
                                        <button class="btn btn-outline-danger btn-sm" onclick="deleteCampaign(<?php echo $campaign['id']; ?>)">
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
                    <p>Êtes-vous sûr de vouloir supprimer cette campagne ? Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form method="POST" id="deleteForm">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="campaign_id" id="deleteCampaignId">
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
        function deleteCampaign(campaignId) {
            document.getElementById('deleteCampaignId').value = campaignId;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        // Auto-refresh stats every 30 seconds
        setInterval(function() {
            // You can add AJAX call here to refresh stats
        }, 30000);
    </script>
</body>
</html>
