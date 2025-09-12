<?php
$page_title = 'Enterprise Ads Analytics - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Get analytics data
$period = $_GET['period'] ?? '30d';
$ad_id = $_GET['ad_id'] ?? '';

// Calculate date range
switch ($period) {
    case '7d':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        break;
    case '30d':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        break;
    case '90d':
        $start_date = date('Y-m-d', strtotime('-90 days'));
        break;
    default:
        $start_date = date('Y-m-d', strtotime('-30 days'));
}

$end_date = date('Y-m-d');

try {
    // Get overall statistics
    $overall_stats = $db->fetch("
        SELECT 
            COUNT(DISTINCT a.id) as total_ads,
            COUNT(DISTINCT CASE WHEN a.status = 'active' THEN a.id END) as active_ads,
            COALESCE(SUM(ap.impressions), 0) as total_impressions,
            COALESCE(SUM(ap.clicks), 0) as total_clicks,
            COALESCE(SUM(ap.conversions), 0) as total_conversions,
            COALESCE(SUM(ap.revenue), 0) as total_revenue,
            COALESCE(SUM(ap.cost), 0) as total_cost,
            CASE 
                WHEN COALESCE(SUM(ap.impressions), 0) > 0 
                THEN ROUND((COALESCE(SUM(ap.clicks), 0) / COALESCE(SUM(ap.impressions), 0)) * 100, 2)
                ELSE 0 
            END as overall_ctr,
            CASE 
                WHEN COALESCE(SUM(ap.clicks), 0) > 0 
                THEN ROUND(COALESCE(SUM(ap.cost), 0) / COALESCE(SUM(ap.clicks), 0), 2)
                ELSE 0 
            END as overall_cpc,
            CASE 
                WHEN COALESCE(SUM(ap.impressions), 0) > 0 
                THEN ROUND((COALESCE(SUM(ap.cost), 0) / COALESCE(SUM(ap.impressions), 0)) * 1000, 2)
                ELSE 0 
            END as overall_cpm
        FROM advertisements a
        LEFT JOIN ad_performance ap ON a.id = ap.ad_id AND ap.date >= ? AND ap.date <= ?
    ", [$start_date, $end_date]) ?? [];

    // Get top performing ads
    $top_ads = $db->fetchAll("
        SELECT 
            a.id,
            a.title,
            a.ad_type,
            a.position,
            a.status,
            COALESCE(SUM(ap.impressions), 0) as impressions,
            COALESCE(SUM(ap.clicks), 0) as clicks,
            COALESCE(SUM(ap.conversions), 0) as conversions,
            COALESCE(SUM(ap.revenue), 0) as revenue,
            COALESCE(SUM(ap.cost), 0) as cost,
            CASE 
                WHEN COALESCE(SUM(ap.impressions), 0) > 0 
                THEN ROUND((COALESCE(SUM(ap.clicks), 0) / COALESCE(SUM(ap.impressions), 0)) * 100, 2)
                ELSE 0 
            END as ctr,
            CASE 
                WHEN COALESCE(SUM(ap.clicks), 0) > 0 
                THEN ROUND(COALESCE(SUM(ap.cost), 0) / COALESCE(SUM(ap.clicks), 0), 2)
                ELSE 0 
            END as cpc
        FROM advertisements a
        LEFT JOIN ad_performance ap ON a.id = ap.ad_id AND ap.date >= ? AND ap.date <= ?
        GROUP BY a.id
        ORDER BY impressions DESC
        LIMIT 10
    ", [$start_date, $end_date]) ?? [];

    // Get performance by ad type
    $performance_by_type = $db->fetchAll("
        SELECT 
            a.ad_type,
            COUNT(DISTINCT a.id) as ad_count,
            COALESCE(SUM(ap.impressions), 0) as impressions,
            COALESCE(SUM(ap.clicks), 0) as clicks,
            COALESCE(SUM(ap.conversions), 0) as conversions,
            COALESCE(SUM(ap.revenue), 0) as revenue,
            CASE 
                WHEN COALESCE(SUM(ap.impressions), 0) > 0 
                THEN ROUND((COALESCE(SUM(ap.clicks), 0) / COALESCE(SUM(ap.impressions), 0)) * 100, 2)
                ELSE 0 
            END as ctr
        FROM advertisements a
        LEFT JOIN ad_performance ap ON a.id = ap.ad_id AND ap.date >= ? AND ap.date <= ?
        GROUP BY a.ad_type
        ORDER BY impressions DESC
    ", [$start_date, $end_date]) ?? [];

    // Get daily performance data for charts
    $daily_performance = $db->fetchAll("
        SELECT 
            DATE(ap.date) as date,
            SUM(ap.impressions) as impressions,
            SUM(ap.clicks) as clicks,
            SUM(ap.conversions) as conversions,
            SUM(ap.revenue) as revenue,
            SUM(ap.cost) as cost
        FROM ad_performance ap
        WHERE ap.date >= ? AND ap.date <= ?
        GROUP BY DATE(ap.date)
        ORDER BY date
    ", [$start_date, $end_date]) ?? [];

    // Get all ads for filter dropdown
    $all_ads = $db->fetchAll("
        SELECT id, title, ad_type, status 
        FROM advertisements 
        ORDER BY title
    ", []) ?? [];

} catch (Exception $e) {
    $overall_stats = [];
    $top_ads = [];
    $performance_by_type = [];
    $daily_performance = [];
    $all_ads = [];
    error_log("Database error in ads_analytics.php: " . $e->getMessage());
}
?>

<!-- Period Filter -->
<div class="enterprise-content-card mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h5 style="color: var(--enterprise-primary); margin: 0;">
            <i class="fas fa-chart-line me-2"></i>Analytics des Publicités
        </h5>
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" onchange="changePeriod(this.value)">
                <option value="7d" <?= $period === '7d' ? 'selected' : '' ?>>7 derniers jours</option>
                <option value="30d" <?= $period === '30d' ? 'selected' : '' ?>>30 derniers jours</option>
                <option value="90d" <?= $period === '90d' ? 'selected' : '' ?>>90 derniers jours</option>
            </select>
            <a href="manage_ads.php" class="enterprise-action-btn enterprise-secondary">
                <i class="fas fa-arrow-left me-1"></i>Retour
            </a>
        </div>
    </div>
</div>

<!-- Overall Statistics -->
<div class="enterprise-stats-grid">
    <div class="enterprise-stat-card">
        <div class="enterprise-stat-number"><?= number_format($overall_stats['total_ads'] ?? 0) ?></div>
        <div class="enterprise-stat-label">Total Publicités</div>
        <i class="fas fa-ad stat-icon"></i>
    </div>
    <div class="enterprise-stat-card">
        <div class="enterprise-stat-number"><?= number_format($overall_stats['active_ads'] ?? 0) ?></div>
        <div class="enterprise-stat-label">Publicités Actives</div>
        <i class="fas fa-check-circle stat-icon"></i>
    </div>
    <div class="enterprise-stat-card">
        <div class="enterprise-stat-number"><?= number_format($overall_stats['total_impressions'] ?? 0) ?></div>
        <div class="enterprise-stat-label">Impressions Total</div>
        <i class="fas fa-eye stat-icon"></i>
    </div>
    <div class="enterprise-stat-card">
        <div class="enterprise-stat-number"><?= number_format($overall_stats['total_clicks'] ?? 0) ?></div>
        <div class="enterprise-stat-label">Clics Total</div>
        <i class="fas fa-mouse-pointer stat-icon"></i>
    </div>
    <div class="enterprise-stat-card">
        <div class="enterprise-stat-number"><?= number_format($overall_stats['total_revenue'] ?? 0, 2) ?> MAD</div>
        <div class="enterprise-stat-label">Revenus Total</div>
        <i class="fas fa-dollar-sign stat-icon"></i>
    </div>
    <div class="enterprise-stat-card">
        <div class="enterprise-stat-number"><?= number_format($overall_stats['overall_ctr'] ?? 0, 2) ?>%</div>
        <div class="enterprise-stat-label">CTR Global</div>
        <i class="fas fa-percentage stat-icon"></i>
    </div>
</div>

<!-- Performance Charts -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-chart-area me-2"></i>Performance Quotidienne
            </h5>
            <div id="dailyPerformanceChart" style="height: 400px;"></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-chart-pie me-2"></i>Performance par Type
            </h5>
            <div id="typePerformanceChart" style="height: 400px;"></div>
        </div>
    </div>
</div>

<!-- Top Performing Ads -->
<div class="enterprise-content-card">
    <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
        <i class="fas fa-trophy me-2"></i>Top 10 Publicités Performantes
    </h5>
    
    <?php if (empty($top_ads)): ?>
    <div class="text-center py-5">
        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Aucune donnée disponible</h5>
        <p class="text-muted">Aucune performance enregistrée pour cette période.</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead style="background: var(--enterprise-gray-50);">
                <tr>
                    <th style="color: var(--enterprise-text-primary);">Publicité</th>
                    <th style="color: var(--enterprise-text-primary);">Type</th>
                    <th style="color: var(--enterprise-text-primary);">Impressions</th>
                    <th style="color: var(--enterprise-text-primary);">Clics</th>
                    <th style="color: var(--enterprise-text-primary);">CTR</th>
                    <th style="color: var(--enterprise-text-primary);">Revenus</th>
                    <th style="color: var(--enterprise-text-primary);">Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($top_ads as $ad): ?>
                <tr>
                    <td>
                        <strong style="color: var(--enterprise-primary);">
                            <?= htmlspecialchars($ad['title']) ?>
                        </strong>
                    </td>
                    <td>
                        <span class="badge bg-primary"><?= ucfirst($ad['ad_type']) ?></span>
                    </td>
                    <td>
                        <span style="color: var(--enterprise-text-secondary);">
                            <?= number_format($ad['impressions']) ?>
                        </span>
                    </td>
                    <td>
                        <span style="color: var(--enterprise-text-secondary);">
                            <?= number_format($ad['clicks']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="enterprise-status-badge enterprise-info">
                            <?= number_format($ad['ctr'], 2) ?>%
                        </span>
                    </td>
                    <td>
                        <span style="color: var(--enterprise-success); font-weight: 600;">
                            <?= number_format($ad['revenue'], 2) ?> MAD
                        </span>
                    </td>
                    <td>
                        <?php if ($ad['status'] === 'active'): ?>
                        <span class="enterprise-status-badge enterprise-success">
                            <i class="fas fa-check me-1"></i>Active
                        </span>
                        <?php else: ?>
                        <span class="enterprise-status-badge enterprise-warning">
                            <i class="fas fa-pause me-1"></i>Inactive
                        </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Performance by Type -->
<div class="enterprise-content-card">
    <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
        <i class="fas fa-chart-bar me-2"></i>Performance par Type de Publicité
    </h5>
    
    <?php if (empty($performance_by_type)): ?>
    <div class="text-center py-5">
        <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Aucune donnée disponible</h5>
        <p class="text-muted">Aucune performance par type pour cette période.</p>
    </div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($performance_by_type as $type): ?>
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="enterprise-content-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 style="color: var(--enterprise-primary);">
                        <?= ucfirst($type['ad_type']) ?>
                    </h6>
                    <span class="badge bg-info"><?= $type['ad_count'] ?> ads</span>
                </div>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="enterprise-stat-number" style="font-size: 1.5rem;">
                            <?= number_format($type['impressions']) ?>
                        </div>
                        <div class="enterprise-stat-label" style="font-size: 0.8rem;">Impressions</div>
                    </div>
                    <div class="col-6">
                        <div class="enterprise-stat-number" style="font-size: 1.5rem;">
                            <?= number_format($type['clicks']) ?>
                        </div>
                        <div class="enterprise-stat-label" style="font-size: 0.8rem;">Clics</div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between">
                        <span style="color: var(--enterprise-text-secondary); font-size: 0.9rem;">
                            CTR: <?= number_format($type['ctr'], 2) ?>%
                        </span>
                        <span style="color: var(--enterprise-success); font-weight: 600; font-size: 0.9rem;">
                            <?= number_format($type['revenue'], 2) ?> MAD
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
<script>
function changePeriod(period) {
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('period', period);
    window.location.href = currentUrl.toString();
}

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Daily Performance Chart
    const dailyData = <?= json_encode($daily_performance) ?>;
    if (dailyData.length > 0) {
        const dailyChartOptions = {
            series: [{
                name: 'Impressions',
                data: dailyData.map(item => item.impressions)
            }, {
                name: 'Clics',
                data: dailyData.map(item => item.clicks)
            }, {
                name: 'Conversions',
                data: dailyData.map(item => item.conversions)
            }],
            chart: {
                type: 'area',
                height: 400,
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            colors: ['#3b82f6', '#10b981', '#f59e0b'],
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                type: 'gradient',
                gradient: {
                    opacityFrom: 0.6,
                    opacityTo: 0.1
                }
            },
            xaxis: {
                categories: dailyData.map(item => new Date(item.date).toLocaleDateString('fr-FR')),
                labels: {
                    style: {
                        colors: '#64748b'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#64748b'
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right'
            },
            tooltip: {
                theme: 'dark'
            }
        };
        
        new ApexCharts(document.querySelector("#dailyPerformanceChart"), dailyChartOptions).render();
    }

    // Type Performance Chart
    const typeData = <?= json_encode($performance_by_type) ?>;
    if (typeData.length > 0) {
        const typeChartOptions = {
            series: typeData.map(item => item.impressions),
            chart: {
                type: 'donut',
                height: 400
            },
            labels: typeData.map(item => ucfirst(item.ad_type)),
            colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
            legend: {
                position: 'bottom'
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '60%'
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return number_format(value) + ' impressions';
                    }
                }
            }
        };
        
        new ApexCharts(document.querySelector("#typePerformanceChart"), typeChartOptions).render();
    }
});

function ucfirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function number_format(number) {
    return new Intl.NumberFormat('fr-FR').format(number);
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
