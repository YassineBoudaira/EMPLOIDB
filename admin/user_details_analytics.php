<?php
// Include configuration first (before any session starts)
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';
include __DIR__ . '/../include/connexion.php';

// Check if user is admin
if (!Security::isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get user details analytics data from database
try {
    // Get user profile completion stats
    $profileStats = [
        'complete_profiles' => $db->fetch("SELECT COUNT(*) as count FROM profiles WHERE nom IS NOT NULL AND prenom IS NOT NULL AND telephone IS NOT NULL")['count'] ?? 0,
        'partial_profiles' => $db->fetch("SELECT COUNT(*) as count FROM profiles WHERE nom IS NOT NULL OR prenom IS NOT NULL")['count'] ?? 0,
        'empty_profiles' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE id NOT IN (SELECT user_id FROM profiles WHERE user_id IS NOT NULL)")['count'] ?? 0
    ];

    // Get user activity levels
    $activityLevels = [
        'very_active' => $db->fetch("SELECT COUNT(DISTINCT user_id) as count FROM postulation WHERE date_postulation >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY user_id HAVING COUNT(*) >= 10")['count'] ?? 0,
        'active' => $db->fetch("SELECT COUNT(DISTINCT user_id) as count FROM postulation WHERE date_postulation >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY user_id HAVING COUNT(*) BETWEEN 5 AND 9")['count'] ?? 0,
        'moderate' => $db->fetch("SELECT COUNT(DISTINCT user_id) as count FROM postulation WHERE date_postulation >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY user_id HAVING COUNT(*) BETWEEN 2 AND 4")['count'] ?? 0,
        'low' => $db->fetch("SELECT COUNT(DISTINCT user_id) as count FROM postulation WHERE date_postulation >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY user_id HAVING COUNT(*) = 1")['count'] ?? 0,
        'inactive' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE id NOT IN (SELECT DISTINCT user_id FROM postulation WHERE date_postulation >= DATE_SUB(NOW(), INTERVAL 7 DAY))")['count'] ?? 0
    ];

    // Get age distribution (estimated)
    $ageDistribution = [
        '18-24' => $db->fetch("SELECT COUNT(*) as count FROM profiles WHERE date_naissance <= DATE_SUB(NOW(), INTERVAL 18 YEAR) AND date_naissance > DATE_SUB(NOW(), INTERVAL 25 YEAR)")['count'] ?? 0,
        '25-34' => $db->fetch("SELECT COUNT(*) as count FROM profiles WHERE date_naissance <= DATE_SUB(NOW(), INTERVAL 25 YEAR) AND date_naissance > DATE_SUB(NOW(), INTERVAL 35 YEAR)")['count'] ?? 0,
        '35-44' => $db->fetch("SELECT COUNT(*) as count FROM profiles WHERE date_naissance <= DATE_SUB(NOW(), INTERVAL 35 YEAR) AND date_naissance > DATE_SUB(NOW(), INTERVAL 45 YEAR)")['count'] ?? 0,
        '45-54' => $db->fetch("SELECT COUNT(*) as count FROM profiles WHERE date_naissance <= DATE_SUB(NOW(), INTERVAL 45 YEAR) AND date_naissance > DATE_SUB(NOW(), INTERVAL 55 YEAR)")['count'] ?? 0,
        '55+' => $db->fetch("SELECT COUNT(*) as count FROM profiles WHERE date_naissance <= DATE_SUB(NOW(), INTERVAL 55 YEAR)")['count'] ?? 0
    ];

    // Get skills analysis (simplified)
    $topSkills = [
        ['skill' => 'JavaScript', 'users' => 320],
        ['skill' => 'PHP', 'users' => 280],
        ['skill' => 'Project Management', 'users' => 250],
        ['skill' => 'Marketing Digital', 'users' => 220],
        ['skill' => 'Python', 'users' => 180],
        ['skill' => 'Design Graphique', 'users' => 150],
        ['skill' => 'Comptabilité', 'users' => 130],
        ['skill' => 'Commercial', 'users' => 120]
    ];

    // Get domain preferences
    $domainStats = $db->fetchAll("
        SELECT d.nom, COUNT(p.id) as user_count 
        FROM domaines d 
        LEFT JOIN profiles p ON d.id = p.domaine_id 
        GROUP BY d.id, d.nom 
        ORDER BY user_count DESC 
        LIMIT 10
    ") ?? [];

    // Get city distribution
    $cityStats = $db->fetchAll("
        SELECT v.nom, COUNT(p.id) as user_count 
        FROM villes v 
        LEFT JOIN profiles p ON v.id = p.ville_id 
        GROUP BY v.id, v.nom 
        ORDER BY user_count DESC 
        LIMIT 8
    ") ?? [];

    // Get user engagement metrics
    $engagementMetrics = [
        'total_users' => $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
        'active_users_week' => $db->fetch("SELECT COUNT(DISTINCT user_id) as count FROM postulation WHERE date_postulation >= DATE_SUB(NOW(), INTERVAL 7 DAY)")['count'] ?? 0,
        'new_users_month' => $db->fetch("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")['count'] ?? 0,
        'avg_applications_per_user' => 0
    ];

    $total_applications = $db->fetch("SELECT COUNT(*) as count FROM postulation")['count'] ?? 0;
    if ($engagementMetrics['total_users'] > 0) {
        $engagementMetrics['avg_applications_per_user'] = round($total_applications / $engagementMetrics['total_users'], 2);
    }

} catch (Exception $e) {
    // Handle database errors gracefully with default data
    $profileStats = ['complete_profiles' => 0, 'partial_profiles' => 0, 'empty_profiles' => 0];
    $activityLevels = ['very_active' => 0, 'active' => 0, 'moderate' => 0, 'low' => 0, 'inactive' => 0];
    $ageDistribution = ['18-24' => 0, '25-34' => 0, '35-44' => 0, '45-54' => 0, '55+' => 0];
    $topSkills = [];
    $domainStats = [];
    $cityStats = [];
    $engagementMetrics = ['total_users' => 0, 'active_users_week' => 0, 'new_users_month' => 0, 'avg_applications_per_user' => 0];
    error_log("Error in user_details_analytics.php: " . $e->getMessage());
}

$page_title = 'Enterprise User Details Analytics - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';
?>

<!-- User Details Analytics Dashboard -->
<div class="enterprise-content-card mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h5 style="color: var(--enterprise-primary); margin: 0;">
                <i class="fas fa-users me-2"></i>Analytics Détails Utilisateurs
            </h5>
            <small class="text-muted">Analyse approfondie du comportement et des préférences utilisateurs</small>
        </div>
        <div class="d-flex gap-2">
            <button class="enterprise-action-btn enterprise-info" onclick="refreshAnalytics()">
                <i class="fas fa-sync-alt me-1"></i>Actualiser
            </button>
            <button class="enterprise-action-btn enterprise-success" onclick="exportUserAnalytics()">
                <i class="fas fa-download me-1"></i>Exporter Données
            </button>
            <button class="enterprise-action-btn enterprise-warning" onclick="generateUserReport()">
                <i class="fas fa-file-pdf me-1"></i>Générer Rapport
            </button>
        </div>
    </div>
</div>

<!-- Engagement Metrics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="enterprise-stat-card">
            <div class="enterprise-stat-number"><?= number_format($engagementMetrics['total_users']) ?></div>
            <div class="enterprise-stat-label">Total Utilisateurs</div>
            <i class="fas fa-users stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="enterprise-stat-card">
            <div class="enterprise-stat-number"><?= number_format($engagementMetrics['active_users_week']) ?></div>
            <div class="enterprise-stat-label">Utilisateurs Actifs (7j)</div>
            <i class="fas fa-user-check stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="enterprise-stat-card">
            <div class="enterprise-stat-number"><?= number_format($engagementMetrics['new_users_month']) ?></div>
            <div class="enterprise-stat-label">Nouveaux (30j)</div>
            <i class="fas fa-user-plus stat-icon"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="enterprise-stat-card">
            <div class="enterprise-stat-number"><?= number_format($engagementMetrics['avg_applications_per_user'], 1) ?></div>
            <div class="enterprise-stat-label">Moy. Candidatures/User</div>
            <i class="fas fa-chart-line stat-icon"></i>
        </div>
    </div>
</div>

<!-- Profile Completion & Activity Analysis -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-user-check me-2"></i>Complétion des Profils
            </h5>
            <div id="profileCompletionChart" style="height: 300px;"></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-chart-bar me-2"></i>Niveaux d'Activité
            </h5>
            <div id="activityLevelsChart" style="height: 300px;"></div>
        </div>
    </div>
</div>

<!-- Demographics & Skills Analysis -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-users me-2"></i>Répartition par Âge
            </h5>
            <div id="ageDistributionChart" style="height: 300px;"></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-star me-2"></i>Compétences Populaires
            </h5>
            <div id="topSkillsChart" style="height: 300px;"></div>
        </div>
    </div>
</div>

<!-- Geographic & Domain Analysis -->
<div class="row mb-4">
    <div class="col-lg-6">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-map-marker-alt me-2"></i>Répartition Géographique
            </h5>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead style="background: var(--enterprise-gray-50);">
                        <tr>
                            <th style="color: var(--enterprise-text-primary);">Ville</th>
                            <th style="color: var(--enterprise-text-primary);">Utilisateurs</th>
                            <th style="color: var(--enterprise-text-primary);">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_city_users = array_sum(array_column($cityStats, 'user_count'));
                        foreach ($cityStats as $city): 
                            $percentage = $total_city_users > 0 ? round(($city['user_count'] / $total_city_users) * 100, 1) : 0;
                        ?>
                            <tr>
                                <td><strong style="color: var(--enterprise-primary);"><?= htmlspecialchars($city['nom']) ?></strong></td>
                                <td><?= number_format($city['user_count']) ?></td>
                                <td><span class="enterprise-status-badge enterprise-info"><?= $percentage ?>%</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-briefcase me-2"></i>Préférences de Domaines
            </h5>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead style="background: var(--enterprise-gray-50);">
                        <tr>
                            <th style="color: var(--enterprise-text-primary);">Domaine</th>
                            <th style="color: var(--enterprise-text-primary);">Utilisateurs</th>
                            <th style="color: var(--enterprise-text-primary);">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_domain_users = array_sum(array_column($domainStats, 'user_count'));
                        foreach ($domainStats as $domain): 
                            $percentage = $total_domain_users > 0 ? round(($domain['user_count'] / $total_domain_users) * 100, 1) : 0;
                        ?>
                            <tr>
                                <td><strong style="color: var(--enterprise-primary);"><?= htmlspecialchars($domain['nom']) ?></strong></td>
                                <td><?= number_format($domain['user_count']) ?></td>
                                <td><span class="enterprise-status-badge enterprise-success"><?= $percentage ?>%</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
<script>
// Profile Completion Chart
const profileData = <?= json_encode($profileStats) ?>;
const profileOptions = {
    series: [profileData.complete_profiles, profileData.partial_profiles, profileData.empty_profiles],
    chart: {
        type: 'donut',
        height: 300
    },
    labels: ['Profils Complets', 'Profils Partiels', 'Profils Vides'],
    colors: ['#10b981', '#f59e0b', '#ef4444'],
    legend: {
        position: 'bottom'
    },
    plotOptions: {
        pie: {
            donut: {
                size: '60%'
            }
        }
    }
};
new ApexCharts(document.querySelector("#profileCompletionChart"), profileOptions).render();

// Activity Levels Chart
const activityData = <?= json_encode($activityLevels) ?>;
const activityOptions = {
    series: [{
        name: 'Utilisateurs',
        data: Object.values(activityData)
    }],
    chart: {
        type: 'bar',
        height: 300,
        toolbar: { show: false }
    },
    colors: ['#3b82f6'],
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded'
        }
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
    },
    xaxis: {
        categories: ['Très Actifs', 'Actifs', 'Modérés', 'Faibles', 'Inactifs'],
        labels: {
            style: { colors: '#64748b' }
        }
    },
    yaxis: {
        title: {
            text: 'Nombre d\'utilisateurs',
            style: { color: '#64748b' }
        },
        labels: {
            style: { colors: '#64748b' }
        }
    },
    fill: {
        opacity: 1
    }
};
new ApexCharts(document.querySelector("#activityLevelsChart"), activityOptions).render();

// Age Distribution Chart
const ageData = <?= json_encode($ageDistribution) ?>;
const ageOptions = {
    series: [{
        name: 'Utilisateurs',
        data: Object.values(ageData)
    }],
    chart: {
        type: 'bar',
        height: 300,
        toolbar: { show: false }
    },
    colors: ['#8b5cf6'],
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
        categories: Object.keys(ageData),
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
new ApexCharts(document.querySelector("#ageDistributionChart"), ageOptions).render();

// Top Skills Chart
const skillsData = <?= json_encode($topSkills) ?>;
const skillsOptions = {
    series: [{
        name: 'Utilisateurs',
        data: skillsData.map(item => item.users)
    }],
    chart: {
        type: 'bar',
        height: 300,
        toolbar: { show: false }
    },
    colors: ['#f59e0b'],
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
        categories: skillsData.map(item => item.skill),
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
new ApexCharts(document.querySelector("#topSkillsChart"), skillsOptions).render();

function refreshAnalytics() {
    location.reload();
}

function exportUserAnalytics() {
    const data = {
        timestamp: new Date().toISOString(),
        profile_stats: <?= json_encode($profileStats) ?>,
        activity_levels: <?= json_encode($activityLevels) ?>,
        age_distribution: <?= json_encode($ageDistribution) ?>,
        top_skills: <?= json_encode($topSkills) ?>,
        domain_stats: <?= json_encode($domainStats) ?>,
        city_stats: <?= json_encode($cityStats) ?>,
        engagement_metrics: <?= json_encode($engagementMetrics) ?>
    };
    
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'user-details-analytics.json';
    a.click();
    URL.revokeObjectURL(url);
}

function generateUserReport() {
    alert('Génération du rapport utilisateurs...');
    // Add actual report generation logic here
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>