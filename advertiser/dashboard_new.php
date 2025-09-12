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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Annonceur | EMPLOIDB</title>
    
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
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon ads">
                                <i class="fas fa-ad"></i>
                            </div>
                        </div>
                        <div class="stat-number"><?php echo number_format($stats['total_ads']); ?></div>
                        <div class="stat-label">Total Publicités</div>
                        <div class="stat-breakdown">
                            <span class="badge bg-success"><?php echo $stats['active_ads']; ?> Actives</span>
                            <span class="badge bg-warning"><?php echo $stats['pending_ads']; ?> En attente</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon campaigns">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                        </div>
                        <div class="stat-number"><?php echo number_format($campaign_stats['total_campaigns']); ?></div>
                        <div class="stat-label">Campagnes</div>
                        <div class="stat-breakdown">
                            <span class="badge bg-success"><?php echo $campaign_stats['active_campaigns']; ?> Actives</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon budget">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="stat-number"><?php echo number_format($campaign_stats['total_budget'], 2); ?> DH</div>
                        <div class="stat-label">Budget Total</div>
                        <div class="stat-breakdown">
                            <span class="badge bg-info"><?php echo number_format($campaign_stats['total_spent'], 2); ?> DH Dépensé</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-icon performance">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div class="stat-number" id="totalImpressions">0</div>
                        <div class="stat-label">Impressions Total</div>
                        <div class="stat-breakdown">
                            <span class="badge bg-primary" id="totalClicks">0 Clics</span>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="content-card">
                    <div class="content-header">
                        <h4><i class="fas fa-bolt"></i> Actions Rapides</h4>
                    </div>
                    <div class="content-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="create_ad.php" class="btn btn-primary w-100 mb-3">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Créer une Publicité</span>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="create_campaign.php" class="btn btn-success w-100 mb-3">
                                    <i class="fas fa-bullhorn"></i>
                                    <span>Nouvelle Campagne</span>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="payment.php" class="btn btn-warning w-100 mb-3">
                                    <i class="fas fa-credit-card"></i>
                                    <span>Payer</span>
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="analytics.php" class="btn btn-info w-100 mb-3">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>Analytics</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Section -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="content-card">
                            <div class="content-header">
                                <h4><i class="fas fa-chart-area"></i> Performance (30 derniers jours)</h4>
                            </div>
                            <div class="content-body">
                                <div id="performanceChart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="content-card">
                            <div class="content-header">
                                <h4><i class="fas fa-chart-pie"></i> Répartition</h4>
                            </div>
                            <div class="content-body">
                                <div id="distributionChart" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="content-card">
                            <div class="content-header">
                                <h4><i class="fas fa-clock"></i> Publicités Récentes</h4>
                            </div>
                            <div class="content-body">
                                <?php if (empty($recent_ads)): ?>
                                    <p class="text-muted">Aucune publicité créée pour le moment.</p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($recent_ads as $ad): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($ad['title']); ?></h6>
                                                    <small class="text-muted">
                                                        <?php echo $ad['impressions']; ?> impressions • 
                                                        <?php echo $ad['clicks']; ?> clics
                                                    </small>
                                                </div>
                                                <span class="badge bg-<?php echo $ad['status'] === 'active' ? 'success' : ($ad['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                    <?php echo ucfirst($ad['status']); ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="content-card">
                            <div class="content-header">
                                <h4><i class="fas fa-bullhorn"></i> Campagnes Récentes</h4>
                            </div>
                            <div class="content-body">
                                <?php if (empty($recent_campaigns)): ?>
                                    <p class="text-muted">Aucune campagne créée pour le moment.</p>
                                <?php else: ?>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($recent_campaigns as $campaign): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($campaign['name']); ?></h6>
                                                    <small class="text-muted">
                                                        Budget: <?php echo number_format($campaign['budget'], 2); ?> DH • 
                                                        <?php echo $campaign['ads_count']; ?> publicités
                                                    </small>
                                                </div>
                                                <span class="badge bg-<?php echo $campaign['status'] === 'active' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($campaign['status']); ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.0/dist/apexcharts.min.js"></script>
    
    <script>
        // Performance Chart
        var performanceOptions = {
            series: [{
                name: 'Impressions',
                data: <?php echo json_encode(array_column($performance_data, 'impressions')); ?>
            }, {
                name: 'Clics',
                data: <?php echo json_encode(array_column($performance_data, 'clicks')); ?>
            }],
            chart: {
                type: 'area',
                height: 300,
                toolbar: {
                    show: false
                }
            },
            colors: ['#007bff', '#28a745'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            fill: {
                type: 'gradient',
                gradient: {
                    opacityFrom: 0.6,
                    opacityTo: 0.1,
                }
            },
            xaxis: {
                categories: <?php echo json_encode(array_column($performance_data, 'date')); ?>
            },
            tooltip: {
                x: {
                    format: 'dd/MM/yy'
                },
            },
        };
        
        var performanceChart = new ApexCharts(document.querySelector("#performanceChart"), performanceOptions);
        performanceChart.render();
        
        // Distribution Chart
        var distributionOptions = {
            series: [<?php echo $stats['active_ads']; ?>, <?php echo $stats['pending_ads']; ?>, <?php echo $stats['rejected_ads']; ?>],
            chart: {
                type: 'donut',
                height: 300
            },
            labels: ['Actives', 'En attente', 'Rejetées'],
            colors: ['#28a745', '#ffc107', '#dc3545'],
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };
        
        var distributionChart = new ApexCharts(document.querySelector("#distributionChart"), distributionOptions);
        distributionChart.render();
        
        // Update performance stats
        function updatePerformanceStats() {
            var totalImpressions = <?php echo array_sum(array_column($performance_data, 'impressions')); ?>;
            var totalClicks = <?php echo array_sum(array_column($performance_data, 'clicks')); ?>;
            
            document.getElementById('totalImpressions').textContent = totalImpressions.toLocaleString();
            document.getElementById('totalClicks').textContent = totalClicks.toLocaleString() + ' Clics';
        }
        
        // Refresh data
        function refreshData() {
            location.reload();
        }
        
        // Initialize
        updatePerformanceStats();
    </script>
</body>
</html>
