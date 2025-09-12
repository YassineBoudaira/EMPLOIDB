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

// Get advertiser info
$advertiser = $db->fetch("SELECT * FROM advertisers WHERE id = ?", [$advertiser_id]);

// Get statistics
$stats = $db->fetch("
    SELECT 
        COUNT(*) as total_ads,
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_ads,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_ads,
        COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_ads
    FROM advertisements 
    WHERE created_by = ?
", [$advertiser_id]);

// Get campaign statistics
$campaign_stats = $db->fetch("
    SELECT 
        COUNT(*) as total_campaigns,
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_campaigns,
        COALESCE(SUM(budget), 0) as total_budget,
        COALESCE(SUM(spent), 0) as total_spent
    FROM ad_campaigns 
    WHERE created_by = ?
", [$advertiser_id]);

// Get recent ads
$recent_ads = $db->fetchAll("
    SELECT a.*, 
           (SELECT COUNT(*) FROM ad_impressions ai WHERE ai.ad_id = a.id) as impressions,
           (SELECT COUNT(*) FROM ad_clicks ac WHERE ac.ad_id = a.id) as clicks
    FROM advertisements a 
    WHERE a.created_by = ? 
    ORDER BY a.created_at DESC 
    LIMIT 5
", [$advertiser_id]);

// Get recent campaigns
$recent_campaigns = $db->fetchAll("
    SELECT c.*, 
           (SELECT COUNT(*) FROM campaign_ads ca WHERE ca.campaign_id = c.id) as ads_count
    FROM ad_campaigns c 
    WHERE c.created_by = ? 
    ORDER BY c.created_at DESC 
    LIMIT 5
", [$advertiser_id]);

// Get performance data for charts
$performance_data = $db->fetchAll("
    SELECT DATE(ap.date) as date, 
           SUM(ap.impressions) as impressions, 
           SUM(ap.clicks) as clicks,
           SUM(ap.revenue) as revenue
    FROM ad_performance ap
    JOIN advertisements a ON ap.ad_id = a.id
    WHERE a.created_by = ? AND ap.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(ap.date)
    ORDER BY date
", [$advertiser_id]);

$page_title = "Tableau de Bord Annonceur";
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
    <!-- ApexCharts CSS -->
    <link href="https://cdn.jsdelivr.net/npm/apexcharts@3.45.0/dist/apexcharts.css" rel="stylesheet">
    
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        
        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
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
        }
        
        .stat-icon.ads { background: var(--emploidb-gradient-primary); }
        .stat-icon.campaigns { background: var(--emploidb-gradient-success); }
        .stat-icon.budget { background: var(--emploidb-gradient-warning); }
        .stat-icon.performance { background: var(--emploidb-gradient-info); }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--emploidb-text-primary);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--emploidb-text-secondary);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-breakdown {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
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
        }
    </style>
</head>
<body>
    <div class="advertiser-wrapper">
        <!-- Sidebar -->
        <div class="emploidb-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <i class="fas fa-bullhorn"></i>
                    <span>Annonceur</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Tableau de Bord</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="create_ad.php">
                            <i class="fas fa-plus-circle"></i>
                            <span>Créer Publicité</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="my_ads.php">
                            <i class="fas fa-ad"></i>
                            <span>Mes Publicités</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="create_campaign.php">
                            <i class="fas fa-bullhorn"></i>
                            <span>Nouvelle Campagne</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="my_campaigns.php">
                            <i class="fas fa-list"></i>
                            <span>Mes Campagnes</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="payment.php">
                            <i class="fas fa-credit-card"></i>
                            <span>Paiements</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="analytics.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>Analytics</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-file-alt"></i>
                            <span>Rapports</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user"></i>
                            <span>Profil</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($advertiser['company_name']); ?></div>
                        <div class="user-role">Annonceur</div>
                    </div>
                </div>
                <div class="sidebar-actions">
                    <a href="logout.php" class="btn btn-outline-danger btn-sm" title="Déconnexion">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="advertiser-main">
            <!-- Page Header -->
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-tachometer-alt text-primary"></i>
                            Tableau de Bord
                        </h1>
                        <p class="text-muted mb-0">Bienvenue, <?php echo htmlspecialchars($advertiser['company_name']); ?>!</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshData()">
                            <i class="fas fa-sync-alt"></i> Actualiser
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="window.location.href='create_ad.php'">
                            <i class="fas fa-plus"></i> Nouvelle Publicité
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="content-body">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="welcome-title">
                        <i class="fa fa-bullhorn"></i>
                        Bienvenue, <?php echo htmlspecialchars($advertiser['company_name']); ?>!
                    </h1>
                    <p class="welcome-subtitle">
                        Gérez vos publicités et campagnes, suivez vos performances et maximisez votre ROI.
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="account-status">
                        <span class="status-badge status-<?php echo $advertiser['status']; ?>">
                            <?php echo ucfirst($advertiser['status']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="stats-section">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fa fa-ad"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($stats['total_ads']); ?></h3>
                            <p>Total Publicités</p>
                            <div class="stat-breakdown">
                                <span class="badge bg-success"><?php echo $stats['active_ads']; ?> Actives</span>
                                <span class="badge bg-warning"><?php echo $stats['pending_ads']; ?> En attente</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon campaigns">
                            <i class="fa fa-bullhorn"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($campaign_stats['total_campaigns']); ?></h3>
                            <p>Campagnes</p>
                            <div class="stat-breakdown">
                                <span class="badge bg-success"><?php echo $campaign_stats['active_campaigns']; ?> Actives</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon budget">
                            <i class="fa fa-dollar-sign"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($campaign_stats['total_budget'], 2); ?> DH</h3>
                            <p>Budget Total</p>
                            <div class="stat-breakdown">
                                <span class="badge bg-info"><?php echo number_format($campaign_stats['total_spent'], 2); ?> DH Dépensé</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon performance">
                            <i class="fa fa-chart-line"></i>
                        </div>
                        <div class="stat-content">
                            <h3 id="totalImpressions">0</h3>
                            <p>Impressions Total</p>
                            <div class="stat-breakdown">
                                <span class="badge bg-primary" id="totalClicks">0 Clics</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="row">
                <div class="col-md-12">
                    <div class="actions-card">
                        <h4><i class="fa fa-bolt"></i> Actions Rapides</h4>
                        <div class="actions-grid">
                            <a href="create_ad.php" class="action-item">
                                <i class="fa fa-plus-circle"></i>
                                <span>Créer une Publicité</span>
                            </a>
                            <a href="create_campaign.php" class="action-item">
                                <i class="fa fa-bullhorn"></i>
                                <span>Nouvelle Campagne</span>
                            </a>
                            <a href="payment.php" class="action-item">
                                <i class="fa fa-credit-card"></i>
                                <span>Payer</span>
                            </a>
                            <a href="analytics.php" class="action-item">
                                <i class="fa fa-chart-bar"></i>
                                <span>Analytics</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="row">
                <div class="col-md-8">
                    <div class="chart-card">
                        <h4><i class="fa fa-chart-area"></i> Performance (30 derniers jours)</h4>
                        <div id="performanceChart"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="chart-card">
                        <h4><i class="fa fa-pie-chart"></i> Répartition des Publicités</h4>
                        <div id="adsDistributionChart"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
            <div class="row">
                <div class="col-md-6">
                    <div class="activity-card">
                        <h4><i class="fa fa-ad"></i> Publicités Récentes</h4>
                        <div class="activity-list">
                            <?php if (empty($recent_ads)): ?>
                                <div class="empty-state">
                                    <i class="fa fa-inbox"></i>
                                    <p>Aucune publicité créée</p>
                                    <a href="create_ad.php" class="btn btn-primary btn-sm">Créer votre première publicité</a>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recent_ads as $ad): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fa fa-ad"></i>
                                        </div>
                                        <div class="activity-content">
                                            <h6><?php echo htmlspecialchars($ad['title']); ?></h6>
                                            <p class="activity-meta">
                                                <span class="badge status-<?php echo $ad['status']; ?>"><?php echo ucfirst($ad['status']); ?></span>
                                                <span class="activity-stats">
                                                    <i class="fa fa-eye"></i> <?php echo number_format($ad['impressions']); ?>
                                                    <i class="fa fa-mouse-pointer"></i> <?php echo number_format($ad['clicks']); ?>
                                                </span>
                                            </p>
                                            <small class="activity-time"><?php echo date('d/m/Y H:i', strtotime($ad['created_at'])); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="activity-card">
                        <h4><i class="fa fa-bullhorn"></i> Campagnes Récentes</h4>
                        <div class="activity-list">
                            <?php if (empty($recent_campaigns)): ?>
                                <div class="empty-state">
                                    <i class="fa fa-bullhorn"></i>
                                    <p>Aucune campagne créée</p>
                                    <a href="create_campaign.php" class="btn btn-primary btn-sm">Créer votre première campagne</a>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recent_campaigns as $campaign): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon campaigns">
                                            <i class="fa fa-bullhorn"></i>
                                        </div>
                                        <div class="activity-content">
                                            <h6><?php echo htmlspecialchars($campaign['name']); ?></h6>
                                            <p class="activity-meta">
                                                <span class="badge status-<?php echo $campaign['status']; ?>"><?php echo ucfirst($campaign['status']); ?></span>
                                                <span class="activity-stats">
                                                    <i class="fa fa-ad"></i> <?php echo $campaign['ads_count']; ?> publicités
                                                </span>
                                            </p>
                                            <small class="activity-time"><?php echo date('d/m/Y H:i', strtotime($campaign['created_at'])); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.welcome-section {
    background: var(--emploidb-gradient-primary);
    color: white;
    padding: 2rem;
    border-radius: var(--emploidb-border-radius);
    margin-bottom: 2rem;
    box-shadow: var(--emploidb-shadow);
}

.welcome-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.welcome-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 0;
}

.account-status .status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
}

.stats-section {
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--emploidb-card-bg);
    border-radius: var(--emploidb-border-radius);
    padding: 1.5rem;
    box-shadow: var(--emploidb-shadow);
    transition: transform 0.2s ease;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: var(--emploidb-gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.stat-icon.campaigns {
    background: linear-gradient(135deg, #10b981, #059669);
}

.stat-icon.budget {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.stat-icon.performance {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.stat-content h3 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--emploidb-text-primary);
    margin-bottom: 0.5rem;
}

.stat-content p {
    color: var(--emploidb-text-secondary);
    font-weight: 600;
    margin-bottom: 1rem;
}

.stat-breakdown .badge {
    margin-right: 0.5rem;
    font-size: 0.8rem;
}

.quick-actions {
    margin-bottom: 2rem;
}

.actions-card {
    background: var(--emploidb-card-bg);
    border-radius: var(--emploidb-border-radius);
    padding: 2rem;
    box-shadow: var(--emploidb-shadow);
}

.actions-card h4 {
    color: var(--emploidb-text-primary);
    margin-bottom: 1.5rem;
    font-weight: 600;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.action-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem;
    background: var(--emploidb-bg-secondary);
    border-radius: var(--emploidb-border-radius);
    text-decoration: none;
    color: var(--emploidb-text-primary);
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.action-item:hover {
    background: var(--emploidb-primary);
    color: white;
    transform: translateY(-2px);
    text-decoration: none;
}

.action-item i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.action-item span {
    font-weight: 600;
    text-align: center;
}

.charts-section {
    margin-bottom: 2rem;
}

.chart-card {
    background: var(--emploidb-card-bg);
    border-radius: var(--emploidb-border-radius);
    padding: 2rem;
    box-shadow: var(--emploidb-shadow);
    height: 100%;
}

.chart-card h4 {
    color: var(--emploidb-text-primary);
    margin-bottom: 1.5rem;
    font-weight: 600;
}

.recent-activity {
    margin-bottom: 2rem;
}

.activity-card {
    background: var(--emploidb-card-bg);
    border-radius: var(--emploidb-border-radius);
    padding: 2rem;
    box-shadow: var(--emploidb-shadow);
    height: 100%;
}

.activity-card h4 {
    color: var(--emploidb-text-primary);
    margin-bottom: 1.5rem;
    font-weight: 600;
}

.activity-list {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem 0;
    border-bottom: 1px solid var(--emploidb-border-color);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    background: var(--emploidb-gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    margin-right: 1rem;
    flex-shrink: 0;
}

.activity-icon.campaigns {
    background: linear-gradient(135deg, #10b981, #059669);
}

.activity-content {
    flex: 1;
}

.activity-content h6 {
    color: var(--emploidb-text-primary);
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.activity-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.activity-stats {
    color: var(--emploidb-text-secondary);
    font-size: 0.9rem;
}

.activity-stats i {
    margin-right: 0.25rem;
    margin-left: 0.5rem;
}

.activity-time {
    color: var(--emploidb-text-secondary);
    font-size: 0.8rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--emploidb-text-secondary);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state p {
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-rejected {
    background: #f8d7da;
    color: #721c24;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

@media (max-width: 768px) {
    .welcome-title {
        font-size: 1.5rem;
    }
    
    .actions-grid {
        grid-template-columns: 1fr;
    }
    
    .activity-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    initializeCharts();
    
    // Update performance stats
    updatePerformanceStats();
    
    // Start real-time updates
    setInterval(updatePerformanceStats, 30000);
});

function initializeCharts() {
    // Performance Chart
    const performanceData = <?php echo json_encode($performance_data); ?>;
    const dates = performanceData.map(item => item.date);
    const impressions = performanceData.map(item => parseInt(item.impressions) || 0);
    const clicks = performanceData.map(item => parseInt(item.clicks) || 0);
    const revenue = performanceData.map(item => parseFloat(item.revenue) || 0);
    
    const performanceOptions = {
        series: [{
            name: 'Impressions',
            data: impressions
        }, {
            name: 'Clics',
            data: clicks
        }],
        chart: {
            height: 300,
            type: 'area',
            toolbar: {
                show: false
            }
        },
        colors: ['#6366f1', '#10b981'],
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth'
        },
        fill: {
            type: 'gradient',
            gradient: {
                opacityFrom: 0.6,
                opacityTo: 0.1,
            }
        },
        xaxis: {
            categories: dates
        },
        yaxis: {
            labels: {
                formatter: function(val) {
                    return val.toFixed(0);
                }
            }
        }
    };
    
    new ApexCharts(document.querySelector("#performanceChart"), performanceOptions).render();
    
    // Ads Distribution Chart
    const adsDistributionOptions = {
        series: [
            <?php echo $stats['active_ads']; ?>, 
            <?php echo $stats['pending_ads']; ?>, 
            <?php echo $stats['rejected_ads']; ?>
        ],
        chart: {
            height: 300,
            type: 'pie',
            toolbar: {
                show: false
            }
        },
        labels: ['Actives', 'En attente', 'Rejetées'],
        colors: ['#10b981', '#f59e0b', '#ef4444'],
        legend: {
            position: 'bottom'
        }
    };
    
    new ApexCharts(document.querySelector("#adsDistributionChart"), adsDistributionOptions).render();
}

function updatePerformanceStats() {
    // Calculate total impressions and clicks from performance data
    const performanceData = <?php echo json_encode($performance_data); ?>;
    let totalImpressions = 0;
    let totalClicks = 0;
    
    performanceData.forEach(item => {
        totalImpressions += parseInt(item.impressions) || 0;
        totalClicks += parseInt(item.clicks) || 0;
    });
    
    // Update display
    document.getElementById('totalImpressions').textContent = totalImpressions.toLocaleString();
    document.getElementById('totalClicks').textContent = totalClicks.toLocaleString() + ' Clics';
}
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>
