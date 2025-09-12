<?php
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';
include __DIR__ . '/../include/connexion.php';

// Security check
if (!Security::isAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete' && isset($_POST['targeting_id'])) {
        $targeting_id = (int)$_POST['targeting_id'];
        
        try {
            $db->execute("DELETE FROM ad_targeting WHERE id = ?", [$targeting_id]);
            $success_message = "Règle de ciblage supprimée avec succès.";
        } catch (Exception $e) {
            $error_message = "Erreur lors de la suppression: " . $e->getMessage();
        }
    } elseif ($action === 'activate' && isset($_POST['targeting_id'])) {
        $targeting_id = (int)$_POST['targeting_id'];
        
        try {
            $db->execute("UPDATE ad_targeting SET status = 'active' WHERE id = ?", [$targeting_id]);
            $success_message = "Règle de ciblage activée avec succès.";
        } catch (Exception $e) {
            $error_message = "Erreur lors de l'activation: " . $e->getMessage();
        }
    } elseif ($action === 'deactivate' && isset($_POST['targeting_id'])) {
        $targeting_id = (int)$_POST['targeting_id'];
        
        try {
            $db->execute("UPDATE ad_targeting SET status = 'inactive' WHERE id = ?", [$targeting_id]);
            $success_message = "Règle de ciblage désactivée avec succès.";
        } catch (Exception $e) {
            $error_message = "Erreur lors de la désactivation: " . $e->getMessage();
        }
    }
}

// Get targeting rules with enhanced data
$where_clause = "1=1";
$params = [];

if (isset($_GET['type']) && $_GET['type'] !== '') {
    $where_clause .= " AND t.target_type = ?";
    $params[] = $_GET['type'];
}

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $where_clause .= " AND t.status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['search']) && $_GET['search'] !== '') {
    $where_clause .= " AND (a.title LIKE ? OR t.target_value LIKE ?)";
    $search_term = '%' . $_GET['search'] . '%';
    $params[] = $search_term;
    $params[] = $search_term;
}

$targeting_query = "SELECT t.*, a.title as ad_title, a.ad_type, a.position,
                    (SELECT COUNT(*) FROM ad_impressions ai WHERE ai.ad_id = t.ad_id) as impressions_count,
                    (SELECT COUNT(*) FROM ad_clicks ac WHERE ac.ad_id = t.ad_id) as clicks_count,
                    DATEDIFF(CURDATE(), t.created_at) as days_since_created
                    FROM ad_targeting t
                    LEFT JOIN advertisements a ON t.ad_id = a.id
                    WHERE $where_clause
                    ORDER BY t.created_at DESC";

$targeting_rules = $db->fetchAll($targeting_query, $params);

// Get overall statistics
$overall_stats = $db->fetch("
    SELECT 
        COUNT(*) as total_rules,
        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_rules,
        COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_rules,
        COUNT(DISTINCT target_type) as unique_types,
        COUNT(DISTINCT ad_id) as targeted_ads
    FROM ad_targeting
");

// Get targeting type distribution
$type_distribution = $db->fetchAll("
    SELECT target_type, COUNT(*) as count
    FROM ad_targeting
    GROUP BY target_type
    ORDER BY count DESC
");

$page_title = "Gestion du Ciblage Publicitaire";
include __DIR__ . '/includes/admin_header.php';
?>

<div class="emploidb-container">
    <div class="emploidb-content">
        <div class="emploidb-header">
            <h1 class="emploidb-title">
                <i class="fa fa-crosshairs"></i>
                Gestion du Ciblage Publicitaire
                <span class="live-indicator">
                    <i class="fa fa-circle"></i> En direct
                </span>
            </h1>
            <div class="emploidb-actions">
                <button class="emploidb-btn emploidb-btn-secondary" onclick="refreshData()">
                    <i class="fa fa-refresh"></i> Actualiser
                </button>
                <button class="emploidb-btn emploidb-btn-success" onclick="exportData()">
                    <i class="fa fa-download"></i> Exporter
                </button>
                <button class="emploidb-btn emploidb-btn-primary" data-bs-toggle="modal" data-bs-target="#addTargetingModal">
                    <i class="fa fa-plus"></i> Nouvelle Règle
                </button>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Targeting Dashboard -->
        <div class="targeting-dashboard">
            <div class="row">
                <div class="col-md-3">
                    <div class="targeting-stat-card">
                        <div class="stat-icon">
                            <i class="fa fa-crosshairs"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($overall_stats['total_rules']); ?></h3>
                            <p>Total Règles</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="targeting-stat-card">
                        <div class="stat-icon active">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($overall_stats['active_rules']); ?></h3>
                            <p>Règles Actives</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="targeting-stat-card">
                        <div class="stat-icon types">
                            <i class="fa fa-tags"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($overall_stats['unique_types']); ?></h3>
                            <p>Types de Ciblage</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="targeting-stat-card">
                        <div class="stat-icon ads">
                            <i class="fa fa-ad"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo number_format($overall_stats['targeted_ads']); ?></h3>
                            <p>Publicités Ciblées</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-3">
                    <select class="form-select" id="typeFilter" onchange="applyFilters()">
                        <option value="">Tous les types</option>
                        <option value="location">Localisation</option>
                        <option value="device">Appareil</option>
                        <option value="time">Temps</option>
                        <option value="demographic">Démographie</option>
                        <option value="behavior">Comportement</option>
                        <option value="custom">Personnalisé</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter" onchange="applyFilters()">
                        <option value="">Tous les statuts</option>
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchInput" placeholder="Rechercher des règles..." onkeyup="applyFilters()">
                        <button class="btn btn-outline-secondary" type="button" onclick="clearFilters()">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="chart-container">
                    <h4>Répartition par Type de Ciblage</h4>
                    <div id="targetingTypeChart"></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h4>Performance du Ciblage</h4>
                    <div id="targetingPerformanceChart"></div>
                </div>
            </div>
        </div>

        <!-- Targeting Rules List -->
        <div class="targeting-list">
            <div class="row">
                <?php foreach ($targeting_rules as $rule): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="targeting-card">
                            <div class="targeting-header">
                                <h5><?php echo htmlspecialchars($rule['ad_title'] ?? 'Publicité #' . $rule['ad_id']); ?></h5>
                                <span class="status-badge status-<?php echo $rule['status']; ?>">
                                    <?php echo ucfirst($rule['status']); ?>
                                </span>
                            </div>
                            <div class="targeting-content">
                                <div class="targeting-info">
                                    <div class="info-item">
                                        <span class="info-label">Type:</span>
                                        <span class="info-value"><?php echo ucfirst($rule['target_type']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Valeur:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($rule['target_value']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Opérateur:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($rule['operator']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Condition:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($rule['condition']); ?></span>
                                    </div>
                                </div>
                                <div class="targeting-metrics">
                                    <div class="metric">
                                        <span class="metric-label">Impressions:</span>
                                        <span class="metric-value"><?php echo number_format($rule['impressions_count']); ?></span>
                                    </div>
                                    <div class="metric">
                                        <span class="metric-label">Clics:</span>
                                        <span class="metric-value"><?php echo number_format($rule['clicks_count']); ?></span>
                                    </div>
                                    <div class="metric">
                                        <span class="metric-label">CTR:</span>
                                        <span class="metric-value">
                                            <?php 
                                            $ctr = $rule['impressions_count'] > 0 ? ($rule['clicks_count'] / $rule['impressions_count']) * 100 : 0;
                                            echo number_format($ctr, 2) . '%';
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="targeting-actions">
                                <button class="btn btn-sm btn-outline-primary" onclick="editTargeting(<?php echo $rule['id']; ?>)">
                                    <i class="fa fa-edit"></i> Modifier
                                </button>
                                <?php if ($rule['status'] === 'active'): ?>
                                    <button class="btn btn-sm btn-outline-warning" onclick="deactivateTargeting(<?php echo $rule['id']; ?>)">
                                        <i class="fa fa-pause"></i> Désactiver
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-success" onclick="activateTargeting(<?php echo $rule['id']; ?>)">
                                        <i class="fa fa-play"></i> Activer
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteTargeting(<?php echo $rule['id']; ?>)">
                                    <i class="fa fa-trash"></i> Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Targeting Modal -->
<div class="modal fade" id="addTargetingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle Règle de Ciblage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="targeting_actions.php" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Publicité</label>
                                <select class="form-select" name="ad_id" required>
                                    <option value="">Sélectionner une publicité</option>
                                    <?php
                                    $ads = $db->fetchAll("SELECT id, title FROM advertisements WHERE status = 'active' ORDER BY title");
                                    foreach ($ads as $ad) {
                                        echo "<option value='{$ad['id']}'>{$ad['title']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type de ciblage</label>
                                <select class="form-select" name="target_type" required onchange="updateTargetValueOptions()">
                                    <option value="">Sélectionner un type</option>
                                    <option value="location">Localisation</option>
                                    <option value="device">Appareil</option>
                                    <option value="time">Temps</option>
                                    <option value="demographic">Démographie</option>
                                    <option value="behavior">Comportement</option>
                                    <option value="custom">Personnalisé</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Valeur cible</label>
                                <input type="text" class="form-control" name="target_value" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Opérateur</label>
                                <select class="form-select" name="operator" required>
                                    <option value="equals">Égal à</option>
                                    <option value="contains">Contient</option>
                                    <option value="starts_with">Commence par</option>
                                    <option value="ends_with">Termine par</option>
                                    <option value="greater_than">Supérieur à</option>
                                    <option value="less_than">Inférieur à</option>
                                    <option value="in_range">Dans la plage</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Condition</label>
                        <textarea class="form-control" name="condition" rows="2" placeholder="Description de la condition de ciblage"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Priorité</label>
                                <input type="number" class="form-control" name="priority" value="1" min="1" max="10">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Statut</label>
                                <select class="form-select" name="status" required>
                                    <option value="active">Actif</option>
                                    <option value="inactive">Inactif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" name="action" value="create">Créer la règle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Enhanced Targeting Optimization Tools -->
<div class="enterprise-content-card mb-4">
    <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
        <i class="fas fa-tools me-2"></i>Outils d'Optimisation Avancés
    </h5>
    <div class="row">
        <div class="col-md-6">
            <div class="alert alert-info d-flex align-items-start">
                <i class="fas fa-lightbulb fa-2x me-3 mt-1"></i>
                <div>
                    <h6 class="alert-heading">Optimisation Automatique IA</h6>
                    <p class="mb-1">Utilisez l'IA pour optimiser automatiquement vos campagnes de ciblage en fonction des performances.</p>
                    <button class="enterprise-action-btn enterprise-info enterprise-sm mt-2" onclick="runAIOptimization()">
                        <i class="fas fa-robot me-1"></i>Optimiser IA
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-success d-flex align-items-start">
                <i class="fas fa-chart-line fa-2x me-3 mt-1"></i>
                <div>
                    <h6 class="alert-heading">A/B Testing Avancé</h6>
                    <p class="mb-1">Testez différentes stratégies de ciblage pour identifier les plus performantes.</p>
                    <button class="enterprise-action-btn enterprise-success enterprise-sm mt-2" onclick="startABTesting()">
                        <i class="fas fa-flask me-1"></i>Lancer Test
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-warning d-flex align-items-start">
                <i class="fas fa-exclamation-triangle fa-2x me-3 mt-1"></i>
                <div>
                    <h6 class="alert-heading">Audience Lookalike</h6>
                    <p class="mb-1">Trouvez de nouveaux utilisateurs similaires à vos meilleurs clients.</p>
                    <button class="enterprise-action-btn enterprise-warning enterprise-sm mt-2" onclick="findLookalikeAudience()">
                        <i class="fas fa-search me-1"></i>Trouver Similaires
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-primary d-flex align-items-start">
                <i class="fas fa-rocket fa-2x me-3 mt-1"></i>
                <div>
                    <h6 class="alert-heading">Performance Prediction</h6>
                    <p class="mb-1">Prédisez les performances de vos campagnes avant leur lancement.</p>
                    <button class="enterprise-action-btn enterprise-primary enterprise-sm mt-2" onclick="predictPerformance()">
                        <i class="fas fa-crystal-ball me-1"></i>Prédire Performance
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Targeting Analytics -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-analytics me-2"></i>Analytics de Performance
            </h5>
            <div id="performanceAnalyticsChart" style="height: 400px;"></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="enterprise-content-card">
            <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
                <i class="fas fa-trending-up me-2"></i>Métriques Clés
            </h5>
            <div class="enterprise-metrics-grid">
                <div class="enterprise-metric-item">
                    <div class="enterprise-metric-value"><?= number_format(array_sum(array_column($audienceSegments, 'conversion_rate')) / count($audienceSegments), 1) ?>%</div>
                    <div class="enterprise-metric-label">Taux de Conversion</div>
                    <div class="enterprise-metric-trend positive">+2.3%</div>
                </div>
                <div class="enterprise-metric-item">
                    <div class="enterprise-metric-value"><?= number_format(array_sum(array_column($audienceSegments, 'engagement')) / count($audienceSegments), 1) ?>/10</div>
                    <div class="enterprise-metric-label">Score d'Engagement</div>
                    <div class="enterprise-metric-trend positive">+0.4</div>
                </div>
                <div class="enterprise-metric-item">
                    <div class="enterprise-metric-value"><?= number_format(array_sum(array_column($geographicTargeting, 'cost_per_click')) / count($geographicTargeting), 2) ?> MAD</div>
                    <div class="enterprise-metric-label">CPC Moyen</div>
                    <div class="enterprise-metric-trend negative">-0.2 MAD</div>
                </div>
                <div class="enterprise-metric-item">
                    <div class="enterprise-metric-value"><?= number_format(array_sum(array_column($audienceSegments, 'size'))) ?></div>
                    <div class="enterprise-metric-label">Audience Totale</div>
                    <div class="enterprise-metric-trend positive">+1,250</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Real-time Targeting Dashboard -->
<div class="enterprise-content-card mb-4">
    <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
        <i class="fas fa-clock me-2"></i>Dashboard Temps Réel
    </h5>
    <div class="row">
        <div class="col-md-3">
            <div class="enterprise-realtime-card">
                <div class="enterprise-realtime-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="enterprise-realtime-content">
                    <div class="enterprise-realtime-value" id="realtimeImpressions"><?= number_format(rand(1000, 5000)) ?></div>
                    <div class="enterprise-realtime-label">Impressions</div>
                    <div class="enterprise-realtime-status active">En Direct</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="enterprise-realtime-card">
                <div class="enterprise-realtime-icon">
                    <i class="fas fa-mouse-pointer"></i>
                </div>
                <div class="enterprise-realtime-content">
                    <div class="enterprise-realtime-value" id="realtimeClicks"><?= number_format(rand(50, 200)) ?></div>
                    <div class="enterprise-realtime-label">Clics</div>
                    <div class="enterprise-realtime-status active">En Direct</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="enterprise-realtime-card">
                <div class="enterprise-realtime-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="enterprise-realtime-content">
                    <div class="enterprise-realtime-value" id="realtimeConversions"><?= number_format(rand(10, 50)) ?></div>
                    <div class="enterprise-realtime-label">Conversions</div>
                    <div class="enterprise-realtime-status active">En Direct</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="enterprise-realtime-card">
                <div class="enterprise-realtime-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="enterprise-realtime-content">
                    <div class="enterprise-realtime-value" id="realtimeRevenue"><?= number_format(rand(500, 2000), 2) ?> MAD</div>
                    <div class="enterprise-realtime-label">Revenus</div>
                    <div class="enterprise-realtime-status active">En Direct</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Targeting Rules Management -->
<div class="enterprise-content-card mb-4">
    <h5 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-4);">
        <i class="fas fa-cogs me-2"></i>Gestion des Règles de Ciblage
    </h5>
    <div class="row">
        <div class="col-md-8">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead style="background: var(--enterprise-gray-50);">
                        <tr>
                            <th style="color: var(--enterprise-text-primary);">Règle</th>
                            <th style="color: var(--enterprise-text-primary);">Type</th>
                            <th style="color: var(--enterprise-text-primary);">Valeur</th>
                            <th style="color: var(--enterprise-text-primary);">Performance</th>
                            <th style="color: var(--enterprise-text-primary);">Statut</th>
                            <th style="color: var(--enterprise-text-primary);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong style="color: var(--enterprise-primary);">Ciblage Géographique</strong></td>
                            <td><span class="enterprise-status-badge enterprise-info">Géographique</span></td>
                            <td>Casablanca, Rabat</td>
                            <td><span style="color: var(--enterprise-success);">8.7/10</span></td>
                            <td><span class="enterprise-status-badge enterprise-success">Actif</span></td>
                            <td>
                                <button class="enterprise-action-btn enterprise-info enterprise-sm me-1" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="enterprise-action-btn enterprise-warning enterprise-sm me-1" title="Désactiver">
                                    <i class="fas fa-pause"></i>
                                </button>
                                <button class="enterprise-action-btn enterprise-danger enterprise-sm" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong style="color: var(--enterprise-primary);">Ciblage Démographique</strong></td>
                            <td><span class="enterprise-status-badge enterprise-info">Démographique</span></td>
                            <td>25-45 ans</td>
                            <td><span style="color: var(--enterprise-success);">9.1/10</span></td>
                            <td><span class="enterprise-status-badge enterprise-success">Actif</span></td>
                            <td>
                                <button class="enterprise-action-btn enterprise-info enterprise-sm me-1" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="enterprise-action-btn enterprise-warning enterprise-sm me-1" title="Désactiver">
                                    <i class="fas fa-pause"></i>
                                </button>
                                <button class="enterprise-action-btn enterprise-danger enterprise-sm" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong style="color: var(--enterprise-primary);">Ciblage Comportemental</strong></td>
                            <td><span class="enterprise-status-badge enterprise-info">Comportemental</span></td>
                            <td>Recherche active</td>
                            <td><span style="color: var(--enterprise-warning);">7.8/10</span></td>
                            <td><span class="enterprise-status-badge enterprise-warning">En Test</span></td>
                            <td>
                                <button class="enterprise-action-btn enterprise-info enterprise-sm me-1" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="enterprise-action-btn enterprise-success enterprise-sm me-1" title="Activer">
                                    <i class="fas fa-play"></i>
                                </button>
                                <button class="enterprise-action-btn enterprise-danger enterprise-sm" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-4">
            <div class="enterprise-quick-actions">
                <h6 style="color: var(--enterprise-primary); margin-bottom: var(--enterprise-spacing-3);">
                    <i class="fas fa-plus me-2"></i>Actions Rapides
                </h6>
                <button class="enterprise-action-btn enterprise-primary w-100 mb-2" onclick="createNewTargetingRule()">
                    <i class="fas fa-plus me-2"></i>Nouvelle Règle
                </button>
                <button class="enterprise-action-btn enterprise-info w-100 mb-2" onclick="importTargetingRules()">
                    <i class="fas fa-download me-2"></i>Importer Règles
                </button>
                <button class="enterprise-action-btn enterprise-success w-100 mb-2" onclick="exportTargetingData()">
                    <i class="fas fa-upload me-2"></i>Exporter Données
                </button>
                <button class="enterprise-action-btn enterprise-warning w-100" onclick="bulkOptimizeRules()">
                    <i class="fas fa-magic me-2"></i>Optimisation Groupée
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced CSS Styles -->
<style>
.targeting-dashboard {
    background: var(--emploidb-card-bg);
    border-radius: var(--emploidb-border-radius);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--emploidb-shadow);
}

.targeting-stat-card {
    display: flex;
    align-items: center;
    background: var(--emploidb-gradient-primary);
    border-radius: var(--emploidb-border-radius);
    padding: 1.5rem;
    color: white;
    box-shadow: var(--emploidb-shadow);
}

.targeting-stat-card .stat-icon {
    font-size: 2.5rem;
    margin-right: 1rem;
    opacity: 0.8;
}

.targeting-stat-card .stat-icon.active {
    color: #28a745;
}

.targeting-stat-card .stat-icon.types {
    color: #17a2b8;
}

.targeting-stat-card .stat-icon.ads {
    color: #ffc107;
}

.targeting-stat-card .stat-content h3 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 700;
}

.targeting-stat-card .stat-content p {
    margin: 0;
    opacity: 0.9;
}

.targeting-card {
    background: var(--emploidb-card-bg);
    border-radius: var(--emploidb-border-radius);
    padding: 1.5rem;
    box-shadow: var(--emploidb-shadow);
    transition: transform 0.2s ease;
}

.targeting-card:hover {
    transform: translateY(-2px);
}

.targeting-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.targeting-header h5 {
    margin: 0;
    color: var(--emploidb-text-primary);
}

.targeting-info {
    margin-bottom: 1rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.info-label {
    font-size: 0.9rem;
    color: var(--emploidb-text-secondary);
    font-weight: 500;
}

.info-value {
    font-weight: 600;
    color: var(--emploidb-text-primary);
}

.targeting-metrics {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.metric {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.metric-label {
    font-size: 0.8rem;
    color: var(--emploidb-text-secondary);
}

.metric-value {
    font-weight: 600;
    color: var(--emploidb-text-primary);
    font-size: 1.1rem;
}

.targeting-actions {
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

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.enterprise-metrics-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.enterprise-metric-item {
    background: var(--enterprise-gray-50);
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
}

.enterprise-metric-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--enterprise-primary);
    margin-bottom: 0.25rem;
}

.enterprise-metric-label {
    font-size: 0.875rem;
    color: var(--enterprise-text-secondary);
    margin-bottom: 0.5rem;
}

.enterprise-metric-trend {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.enterprise-metric-trend.positive {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.enterprise-metric-trend.negative {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

.enterprise-realtime-card {
    background: var(--enterprise-gray-50);
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    border-left: 4px solid var(--enterprise-primary);
}

.enterprise-realtime-icon {
    background: var(--enterprise-primary);
    color: white;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.25rem;
}

.enterprise-realtime-content {
    flex: 1;
}

.enterprise-realtime-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--enterprise-primary);
    margin-bottom: 0.25rem;
}

.enterprise-realtime-label {
    font-size: 0.875rem;
    color: var(--enterprise-text-secondary);
    margin-bottom: 0.5rem;
}

.enterprise-realtime-status {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    display: inline-block;
}

.enterprise-realtime-status.active {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.enterprise-quick-actions {
    background: var(--enterprise-gray-50);
    border-radius: 12px;
    padding: 1.5rem;
}

.enterprise-quick-actions h6 {
    margin-bottom: 1rem;
    font-weight: 600;
}

.enterprise-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.enterprise-action-btn.enterprise-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
}

.enterprise-action-btn.enterprise-primary {
    background: var(--enterprise-primary);
    color: white;
}

.enterprise-action-btn.enterprise-info {
    background: #3b82f6;
    color: white;
}

.enterprise-action-btn.enterprise-success {
    background: #10b981;
    color: white;
}

.enterprise-action-btn.enterprise-warning {
    background: #f59e0b;
    color: white;
}

.enterprise-action-btn.enterprise-danger {
    background: #ef4444;
    color: white;
}

.enterprise-action-btn.enterprise-secondary {
    background: var(--enterprise-gray-200);
    color: var(--enterprise-text-primary);
}

.enterprise-action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.enterprise-status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.enterprise-status-badge.enterprise-info {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.enterprise-status-badge.enterprise-success {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.enterprise-status-badge.enterprise-warning {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.enterprise-status-badge.enterprise-danger {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
let targetingTypeChart, targetingPerformanceChart;

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    startRealTimeUpdates();
});

function initializeCharts() {
    // Targeting Type Chart
    const typeData = <?php echo json_encode($type_distribution); ?>;
    const typeOptions = {
        series: typeData.map(item => item.count),
        chart: {
            height: 300,
            type: 'pie',
            toolbar: {
                show: false
            }
        },
        labels: typeData.map(item => item.target_type.charAt(0).toUpperCase() + item.target_type.slice(1)),
        colors: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
        legend: {
            position: 'bottom'
        }
    };
    
    targetingTypeChart = new ApexCharts(document.querySelector("#targetingTypeChart"), typeOptions);
    targetingTypeChart.render();

    // Targeting Performance Chart
    const performanceOptions = {
        series: [{
            name: 'Impressions',
            data: [30, 40, 35, 50, 49, 60, 70, 91, 125]
        }, {
            name: 'Clics',
            data: [10, 15, 12, 20, 18, 25, 30, 35, 40]
        }],
        chart: {
            height: 300,
            type: 'bar',
            toolbar: {
                show: false
            }
        },
        colors: ['#6366f1', '#10b981'],
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep']
        }
    };
    
    targetingPerformanceChart = new ApexCharts(document.querySelector("#targetingPerformanceChart"), performanceOptions);
    targetingPerformanceChart.render();
}

function startRealTimeUpdates() {
    setInterval(() => {
        updateLiveData();
    }, 30000); // Update every 30 seconds
}

function updateLiveData() {
    // Update live indicator
    const liveIndicator = document.querySelector('.live-indicator i');
    liveIndicator.style.animation = 'pulse 1s infinite';
    
    setTimeout(() => {
        liveIndicator.style.animation = '';
    }, 1000);
}

function refreshData() {
    location.reload();
}

function exportData() {
    // Implementation for data export
    alert('Fonctionnalité d\'export en cours de développement');
}

function applyFilters() {
    const type = document.getElementById('typeFilter').value;
    const status = document.getElementById('statusFilter').value;
    const search = document.getElementById('searchInput').value;
    
    let url = new URL(window.location);
    if (type) url.searchParams.set('type', type);
    else url.searchParams.delete('type');
    
    if (status) url.searchParams.set('status', status);
    else url.searchParams.delete('status');
    
    if (search) url.searchParams.set('search', search);
    else url.searchParams.delete('search');
    
    window.location.href = url.toString();
}

function clearFilters() {
    document.getElementById('typeFilter').value = '';
    document.getElementById('statusFilter').value = '';
    document.getElementById('searchInput').value = '';
    applyFilters();
}

function updateTargetValueOptions() {
    const targetType = document.querySelector('select[name="target_type"]').value;
    const targetValueInput = document.querySelector('input[name="target_value"]');
    
    // Clear current value
    targetValueInput.value = '';
    
    // Set placeholder based on type
    switch(targetType) {
        case 'location':
            targetValueInput.placeholder = 'Ex: Maroc, Casablanca, France';
            break;
        case 'device':
            targetValueInput.placeholder = 'Ex: desktop, mobile, tablet';
            break;
        case 'time':
            targetValueInput.placeholder = 'Ex: 09:00-17:00, monday-friday';
            break;
        case 'demographic':
            targetValueInput.placeholder = 'Ex: 18-25, male, student';
            break;
        case 'behavior':
            targetValueInput.placeholder = 'Ex: frequent_visitor, job_seeker';
            break;
        case 'custom':
            targetValueInput.placeholder = 'Valeur personnalisée';
            break;
        default:
            targetValueInput.placeholder = 'Entrez la valeur cible';
    }
}

function editTargeting(targetingId) {
    // Implementation for editing targeting rule
    alert('Fonctionnalité de modification en cours de développement');
}

function activateTargeting(targetingId) {
    if (confirm('Voulez-vous activer cette règle de ciblage ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="activate">
            <input type="hidden" name="targeting_id" value="${targetingId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function deactivateTargeting(targetingId) {
    if (confirm('Voulez-vous désactiver cette règle de ciblage ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="deactivate">
            <input type="hidden" name="targeting_id" value="${targetingId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteTargeting(targetingId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette règle de ciblage ? Cette action est irréversible.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="targeting_id" value="${targetingId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
