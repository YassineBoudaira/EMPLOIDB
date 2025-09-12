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

// Calculate CTR and CPM
$ctr = $overall_stats['total_impressions'] > 0 ? ($overall_stats['total_clicks'] / $overall_stats['total_impressions']) * 100 : 0;
$cpm = $overall_stats['total_impressions'] > 0 ? ($overall_stats['total_budget'] / $overall_stats['total_impressions']) * 1000 : 0;

// Get performance data for charts
try {
    $performance_data = $db->fetchAll("
        SELECT DATE(ap.date) as date, 
               SUM(ap.impressions) as impressions, 
               SUM(ap.clicks) as clicks,
               SUM(ap.revenue) as revenue
        FROM ad_performance ap
        JOIN advertisements a ON ap.ad_id = a.id
        WHERE a.created_by = ? AND ap.date >= ?
        GROUP BY DATE(ap.date)
        ORDER BY date
    ", [$advertiser_id, $start_date]);
} catch (Exception $e) {
    $performance_data = [];
}

// Get top performing ads
try {
    $top_ads = $db->fetchAll("
        SELECT a.title, 
               COALESCE(SUM(ap.impressions), 0) as impressions,
               COALESCE(SUM(ap.clicks), 0) as clicks,
               COALESCE(SUM(ap.revenue), 0) as revenue
        FROM advertisements a
        LEFT JOIN ad_performance ap ON a.id = ap.ad_id AND ap.date >= ?
        WHERE a.created_by = ?
        GROUP BY a.id, a.title
        ORDER BY impressions DESC
        LIMIT 10
    ", [$start_date, $advertiser_id]);
} catch (Exception $e) {
    $top_ads = [];
}

$page_title = "Analytics";
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
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
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
        
        /* Chart Container */
        .chart-container {
            background: var(--emploidb-white);
            border-radius: var(--emploidb-border-radius-lg);
            padding: 1.5rem;
            box-shadow: var(--emploidb-shadow-sm);
            border: 1px solid var(--emploidb-border-color);
            margin-bottom: 2rem;
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
                            <i class="fas fa-chart-line text-primary"></i>
                            Analytics
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
                        <div class="stat-number"><?php echo number_format($ctr, 2); ?>%</div>
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
                
                <!-- Performance Chart -->
                <div class="chart-container">
                    <h4 class="mb-3"><i class="fas fa-chart-area"></i> Évolution des Performances</h4>
                    <div id="performanceChart" style="height: 400px;"></div>
                </div>
                
                <!-- Top Performing Ads -->
                <div class="content-card">
                    <div class="content-header">
                        <h4><i class="fas fa-trophy"></i> Meilleures Publicités</h4>
                    </div>
                    <div class="content-body">
                        <?php if (empty($top_ads)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucune donnée disponible</h5>
                                <p class="text-muted">Créez vos premières publicités pour voir les analytics</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Publicité</th>
                                            <th>Impressions</th>
                                            <th>Clics</th>
                                            <th>CTR</th>
                                            <th>Revenus</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_ads as $ad): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($ad['title']); ?></td>
                                                <td><?php echo number_format($ad['impressions']); ?></td>
                                                <td><?php echo number_format($ad['clicks']); ?></td>
                                                <td>
                                                    <?php 
                                                    $ad_ctr = $ad['impressions'] > 0 ? ($ad['clicks'] / $ad['impressions']) * 100 : 0;
                                                    echo number_format($ad_ctr, 2) . '%';
                                                    ?>
                                                </td>
                                                <td><?php echo number_format($ad['revenue'], 2); ?> DH</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
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
            window.location.href = `analytics.php?range=${range}`;
        }
        
        // Performance Chart
        <?php if (!empty($performance_data)): ?>
        const performanceData = <?php echo json_encode($performance_data); ?>;
        
        const options = {
            series: [{
                name: 'Impressions',
                data: performanceData.map(item => item.impressions)
            }, {
                name: 'Clics',
                data: performanceData.map(item => item.clicks)
            }],
            chart: {
                type: 'area',
                height: 400,
                toolbar: {
                    show: false
                }
            },
            colors: ['#6366f1', '#10b981'],
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
                    opacityTo: 0.1
                }
            },
            xaxis: {
                categories: performanceData.map(item => item.date)
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return Math.floor(val);
                    }
                }
            },
            legend: {
                position: 'top'
            }
        };
        
        const chart = new ApexCharts(document.querySelector("#performanceChart"), options);
        chart.render();
        <?php endif; ?>
    </script>
</body>
</html>
