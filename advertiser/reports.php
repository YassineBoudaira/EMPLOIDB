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

// Get date range filter
$date_range = $_GET['range'] ?? '30';
$start_date = date('Y-m-d', strtotime("-$date_range days"));

// Get overall statistics
try {
    $overall_stats = $db->fetch("
        SELECT 
            COUNT(DISTINCT a.id) as total_ads,
            COUNT(DISTINCT c.id) as total_campaigns,
            0 as total_budget,
            COALESCE(SUM(ap.impressions), 0) as total_impressions,
            COALESCE(SUM(ap.clicks), 0) as total_clicks,
            COALESCE(SUM(ap.revenue), 0) as total_revenue
        FROM advertisements a
        LEFT JOIN ad_performance ap ON a.id = ap.ad_id AND ap.date >= ?
        LEFT JOIN ad_campaigns c ON c.created_by = ?
        WHERE a.created_by = ?
    ", [$start_date, $advertiser_id, $advertiser_id]);
} catch (Exception $e) {
    // If query fails, use default values
    $overall_stats = [
        'total_ads' => 0,
        'total_campaigns' => 0,
        'total_budget' => 0,
        'total_impressions' => 0,
        'total_clicks' => 0,
        'total_revenue' => 0
    ];
}

$page_title = "Rapports";
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
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--emploidb-shadow-md);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: var(--emploidb-white);
        }
        
        .stat-icon.impressions {
            background: var(--emploidb-gradient-primary);
        }
        
        .stat-icon.clicks {
            background: var(--emploidb-gradient-success);
        }
        
        .stat-icon.ctr {
            background: var(--emploidb-gradient-warning);
        }
        
        .stat-icon.revenue {
            background: var(--emploidb-gradient-info);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--emploidb-text-primary);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--emploidb-text-secondary);
            font-size: 0.9rem;
            font-weight: 600;
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
                            <i class="fas fa-chart-bar text-primary"></i>
                            Rapports
                        </h1>
                        <p class="text-muted mb-0">Analysez les performances de vos publicités</p>
                    </div>
                    <div class="d-flex gap-2">
                        <select id="dateRange" class="form-select" onchange="changeDateRange()">
                            <option value="7" <?= $date_range == '7' ? 'selected' : '' ?>>7 jours</option>
                            <option value="30" <?= $date_range == '30' ? 'selected' : '' ?>>30 jours</option>
                            <option value="90" <?= $date_range == '90' ? 'selected' : '' ?>>90 jours</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="content-body">
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon impressions">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($overall_stats['total_impressions']); ?></div>
                        <div class="stat-label">Impressions</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon clicks">
                            <i class="fas fa-mouse-pointer"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($overall_stats['total_clicks']); ?></div>
                        <div class="stat-label">Clics</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon ctr">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="stat-number">
                            <?php 
                            $ctr = $overall_stats['total_impressions'] > 0 ? 
                                ($overall_stats['total_clicks'] / $overall_stats['total_impressions']) * 100 : 0;
                            echo number_format($ctr, 2);
                            ?>%
                        </div>
                        <div class="stat-label">CTR</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-number"><?php echo number_format($overall_stats['total_revenue'], 2); ?> DH</div>
                        <div class="stat-label">Revenus</div>
                    </div>
                </div>
                
                <!-- Summary Report -->
                <div class="content-card">
                    <div class="content-header">
                        <h4><i class="fas fa-chart-bar"></i> Résumé des Performances</h4>
                    </div>
                    <div class="content-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Vue d'ensemble</h5>
                                <ul class="list-unstyled">
                                    <li><strong>Total Publicités:</strong> <?php echo number_format($overall_stats['total_ads']); ?></li>
                                    <li><strong>Total Campagnes:</strong> <?php echo number_format($overall_stats['total_campaigns']); ?></li>
                                    <li><strong>Budget Total:</strong> <?php echo number_format($overall_stats['total_budget'], 2); ?> DH</li>
                                    <li><strong>Période:</strong> <?php echo $date_range; ?> derniers jours</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Métriques Clés</h5>
                                <ul class="list-unstyled">
                                    <li><strong>CPM Moyen:</strong> 
                                        <?php 
                                        $cpm = $overall_stats['total_impressions'] > 0 ? 
                                            ($overall_stats['total_budget'] / $overall_stats['total_impressions']) * 1000 : 0;
                                        echo number_format($cpm, 2);
                                        ?> DH
                                    </li>
                                    <li><strong>CPC Moyen:</strong> 
                                        <?php 
                                        $cpc = $overall_stats['total_clicks'] > 0 ? 
                                            $overall_stats['total_budget'] / $overall_stats['total_clicks'] : 0;
                                        echo number_format($cpc, 2);
                                        ?> DH
                                    </li>
                                    <li><strong>ROI:</strong> 
                                        <?php 
                                        $roi = $overall_stats['total_budget'] > 0 ? 
                                            (($overall_stats['total_revenue'] - $overall_stats['total_budget']) / $overall_stats['total_budget']) * 100 : 0;
                                        echo number_format($roi, 2);
                                        ?>%
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recommendations -->
                <div class="content-card">
                    <div class="content-header">
                        <h4><i class="fas fa-lightbulb"></i> Recommandations</h4>
                    </div>
                    <div class="content-body">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Conseils pour améliorer vos performances:</h6>
                            <ul class="mb-0">
                                <li>Optimisez vos publicités avec des images de haute qualité</li>
                                <li>Testez différents titres et descriptions</li>
                                <li>Analysez les heures de pointe pour vos cibles</li>
                                <li>Réajustez vos budgets en fonction des performances</li>
                                <li>Créez des campagnes saisonnières</li>
                            </ul>
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
    
    <script>
        // Date range change
        function changeDateRange() {
            const range = document.getElementById('dateRange').value;
            window.location.href = `reports.php?range=${range}`;
        }
    </script>
</body>
</html>
