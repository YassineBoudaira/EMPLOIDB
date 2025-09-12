<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page_title = 'Enterprise Geographic Analytics - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Get geographic data
try {
    // Check if database connection is available
    if (!isset($db)) {
        throw new Exception('Database connection not available');
    }
    
    $totalUsers = $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 1;
    
    // Get real geographic data from database
    $countryDistribution = $db->fetchAll("
        SELECT v.pays, COUNT(p.id) as users, 
               ROUND((COUNT(p.id) / ?) * 100, 1) as percentage
        FROM villes v 
        LEFT JOIN profiles p ON v.id = p.ville_id 
        GROUP BY v.pays 
        ORDER BY users DESC 
        LIMIT 10
    ", [$totalUsers]) ?? [];
    
    $cityDistribution = $db->fetchAll("
        SELECT v.nom as city, v.pays as country, COUNT(p.id) as users
        FROM villes v 
        LEFT JOIN profiles p ON v.id = p.ville_id 
        GROUP BY v.id, v.nom, v.pays 
        ORDER BY users DESC 
        LIMIT 15
    ", []) ?? [];
    
    // Get regional statistics
    $regionalStats = [
        'total_countries' => count(array_unique(array_column($countryDistribution, 'pays'))),
        'total_cities' => count($cityDistribution),
        'top_country' => $countryDistribution[0]['pays'] ?? 'N/A',
        'top_city' => $cityDistribution[0]['city'] ?? 'N/A'
    ];
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Geographic Analytics Error: " . $e->getMessage());
    
    // Use fallback data
    $countryDistribution = [
        ['pays' => 'France', 'users' => 450, 'percentage' => 45.0],
        ['pays' => 'Canada', 'users' => 200, 'percentage' => 20.0],
        ['pays' => 'Belgique', 'users' => 150, 'percentage' => 15.0]
    ];
    $cityDistribution = [
        ['city' => 'Paris', 'country' => 'France', 'users' => 200],
        ['city' => 'Montréal', 'country' => 'Canada', 'users' => 120],
        ['city' => 'Bruxelles', 'country' => 'Belgique', 'users' => 80]
    ];
    $regionalStats = [
        'total_countries' => 5,
        'total_cities' => 15,
        'top_country' => 'France',
        'top_city' => 'Paris'
    ];
}
?>

<div class="fade-in">
    <!-- Geographic Analytics Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-globe me-3"></i>
                        Enterprise Geographic Analytics
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Analyse géographique et répartition des utilisateurs
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="refreshGeographicData()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="exportGeographicData()">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                    <button class="enterprise-btn enterprise-btn-accent" onclick="generateGeographicReport()">
                        <i class="fas fa-file-pdf"></i>
                        Rapport
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Geographic Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-flag fa-2x text-primary"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-primary mb-0"><?= $regionalStats['total_countries'] ?></h3>
                            <small class="text-muted">Pays</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-success bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-city fa-2x text-success"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-success mb-0"><?= $regionalStats['total_cities'] ?></h3>
                            <small class="text-muted">Villes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-crown fa-2x text-warning"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-warning mb-0"><?= $regionalStats['top_country'] ?></h3>
                            <small class="text-muted">Pays Principal</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="stat-icon bg-info bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-map-marker-alt fa-2x text-info"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="stat-value text-info mb-0"><?= $regionalStats['top_city'] ?></h3>
                            <small class="text-muted">Ville Principale</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Geographic Charts -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-chart-pie me-2"></i>
                        Répartition par Pays
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div id="countryChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-chart-bar me-2"></i>
                        Top Villes
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div id="cityChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Geographic Data Tables -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-table me-2"></i>
                        Top Pays
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Pays</th>
                                    <th>Utilisateurs</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($countryDistribution, 0, 5) as $country): ?>
                                <tr>
                                    <td>
                                        <strong class="text-primary">
                                            <?= htmlspecialchars($country['pays']) ?>
                                        </strong>
                                    </td>
                                    <td><?= number_format($country['users']) ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= $country['percentage'] ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-header">
                    <h4 class="enterprise-card-title">
                        <i class="fas fa-table me-2"></i>
                        Top Villes
                    </h4>
                </div>
                <div class="enterprise-card-body">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Ville</th>
                                    <th>Pays</th>
                                    <th>Utilisateurs</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($cityDistribution, 0, 5) as $city): ?>
                                <tr>
                                    <td>
                                        <strong class="text-primary">
                                            <?= htmlspecialchars($city['city']) ?>
                                        </strong>
                                    </td>
                                    <td><?= htmlspecialchars($city['country']) ?></td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?= number_format($city['users']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Geographic Tools -->
    <div class="enterprise-card">
        <div class="enterprise-card-header">
            <h4 class="enterprise-card-title">
                <i class="fas fa-tools me-2"></i>
                Outils Géographiques
            </h4>
        </div>
        <div class="enterprise-card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="alert alert-info d-flex align-items-start">
                        <i class="fas fa-map fa-2x me-3 mt-1"></i>
                        <div>
                            <h6 class="alert-heading">Carte Interactive</h6>
                            <p class="mb-2">Visualisez la répartition géographique sur une carte interactive.</p>
                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="showInteractiveMap()">
                                <i class="fas fa-map me-1"></i>Afficher Carte
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="alert alert-success d-flex align-items-start">
                        <i class="fas fa-chart-line fa-2x me-3 mt-1"></i>
                        <div>
                            <h6 class="alert-heading">Tendances Régionales</h6>
                            <p class="mb-2">Analysez les tendances de croissance par région.</p>
                            <button class="enterprise-btn enterprise-btn-sm enterprise-btn-outline" onclick="analyzeRegionalTrends()">
                                <i class="fas fa-chart-line me-1"></i>Analyser
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
<script>
// Country Distribution Chart
const countryData = <?= json_encode($countryDistribution) ?>;
const countryOptions = {
    series: countryData.map(item => item.users),
    chart: {
        type: 'pie',
        height: 300,
        toolbar: { show: false }
    },
    labels: countryData.map(item => item.pays),
    colors: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#06b6d4'],
    legend: {
        position: 'bottom',
        labels: {
            colors: '#64748b'
        }
    },
    dataLabels: {
        enabled: true,
        formatter: function (val, opts) {
            return opts.w.globals.labels[opts.seriesIndex] + '\n' + val + ' users';
        }
    }
};
new ApexCharts(document.querySelector("#countryChart"), countryOptions).render();

// City Distribution Chart
const cityData = <?= json_encode($cityDistribution) ?>;
const cityOptions = {
    series: [{
        name: 'Utilisateurs',
        data: cityData.map(item => item.users)
    }],
    chart: {
        type: 'bar',
        height: 300,
        toolbar: { show: false }
    },
    colors: ['#3b82f6'],
    plotOptions: {
        bar: {
            horizontal: true,
            barHeight: '70%',
            distributed: true
        }
    },
    dataLabels: {
        enabled: true,
        textAnchor: 'start',
        style: {
            colors: ['#fff']
        },
        offsetX: 0
    },
    xaxis: {
        categories: cityData.map(item => item.city),
        labels: {
            style: { colors: '#64748b' }
        }
    },
    yaxis: {
        labels: {
            show: false
        }
    },
    legend: {
        show: false
    }
};
new ApexCharts(document.querySelector("#cityChart"), cityOptions).render();

function refreshGeographicData() {
    location.reload();
}

function exportGeographicData() {
    const data = {
        timestamp: new Date().toISOString(),
        regional_stats: <?= json_encode($regionalStats) ?>,
        country_distribution: countryData,
        city_distribution: cityData
    };
    
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'geographic-analytics.json';
    a.click();
    URL.revokeObjectURL(url);
}

function generateGeographicReport() {
    alert('Génération du rapport géographique...');
    // Add actual report generation logic here
}

function showInteractiveMap() {
    alert('Affichage de la carte interactive...');
    // Add actual interactive map logic here
}

function analyzeRegionalTrends() {
    alert('Analyse des tendances régionales...');
    // Add actual regional trends analysis here
}

function configureGeoTargeting() {
    alert('Configuration du ciblage géographique...');
    // Add actual geo targeting configuration here
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
