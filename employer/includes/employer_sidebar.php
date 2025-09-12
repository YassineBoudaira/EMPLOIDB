<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Enhanced Professional Employer Sidebar -->
<nav class="enterprise-sidebar employer-sidebar" id="employerSidebar">
    <!-- Sidebar Brand Section -->
    <div class="sidebar-brand">
        <!-- Website Logo -->
        <div class="d-flex align-items-center justify-content-center mb-3">
            <div class="website-logo-container">
                <i class="fas fa-briefcase me-2" style="font-size: 1.8rem; color: white;"></i>
                <span style="font-size: 1.5rem; font-weight: 800; color: white;">EMPLOI</span><span style="color: #f59e0b; font-weight: 800;">DB</span>
            </div>
        </div>
        
        <!-- Company Brand Section -->
        <div class="company-brand-section">
            <div class="company-logo-container">
                <?php if (isset($employer['company_logo']) && !empty($employer['company_logo'])): ?>
                    <img src="<?= htmlspecialchars($employer['company_logo']) ?>" alt="Company Logo" class="company-logo">
                <?php else: ?>
                    <div class="company-logo-placeholder">
                        <i class="fas fa-building"></i>
                    </div>
                <?php endif; ?>
            </div>
            <h3 class="company-name"><?= htmlspecialchars($employer['company_name'] ?? 'Company Name') ?></h3>
            <p class="company-subtitle">Espace Employeur</p>
        </div>
        
        <!-- User Information -->
        <div class="user-info-section">
            <div class="user-avatar">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="user-details">
                <h6 class="user-name"><?= htmlspecialchars($_SESSION['username'] ?? 'Employer') ?></h6>
                <p class="user-role">Gestionnaire</p>
            </div>
        </div>
    </div>
    
    <!-- Enterprise Navigation -->
    <div class="enterprise-nav">
        <!-- Dashboard Section -->
        <div class="nav-section">
            <div class="nav-section-header">Dashboard</div>
            <a href="dashboard.php" class="enterprise-nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Jobs Management Section -->
        <div class="nav-section">
            <div class="nav-section-header">Gestion des Offres</div>
            <a href="post_job.php" class="enterprise-nav-link <?= $current_page == 'post_job.php' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i>
                <span>Publier une Offre</span>
            </a>
            <a href="manage_jobs.php" class="enterprise-nav-link <?= $current_page == 'manage_jobs.php' ? 'active' : '' ?>">
                <i class="fas fa-edit"></i>
                <span>Gérer les Offres</span>
            </a>
            <a href="applications.php" class="enterprise-nav-link <?= $current_page == 'applications.php' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i>
                <span>Candidatures Reçues</span>
                <span class="badge"><?= isset($new_applications) ? $new_applications : '0' ?></span>
            </a>
        </div>

        <!-- Company Management Section -->
        <div class="nav-section">
            <div class="nav-section-header">Entreprise</div>
            <a href="profile.php" class="enterprise-nav-link <?= $current_page == 'profile.php' ? 'active' : '' ?>">
                <i class="fas fa-user-tie"></i>
                <span>Profil Entreprise</span>
            </a>
            <a href="settings.php" class="enterprise-nav-link <?= $current_page == 'settings.php' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i>
                <span>Paramètres</span>
            </a>
        </div>

        <!-- Enhanced Features Section -->
        <div class="nav-section">
            <div class="nav-section-header">Fonctionnalités Avancées</div>
            <a href="ai_job_matching.php" class="enterprise-nav-link <?= $current_page == 'ai_job_matching.php' ? 'active' : '' ?>">
                <i class="fas fa-brain"></i>
                <span>Matching IA</span>
                <span class="badge bg-success">IA</span>
            </a>
            <a href="candidate_analytics.php" class="enterprise-nav-link <?= $current_page == 'candidate_analytics.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i>
                <span>Analytics Candidats</span>
            </a>
            <a href="salary_insights.php" class="enterprise-nav-link <?= $current_page == 'salary_insights.php' ? 'active' : '' ?>">
                <i class="fas fa-dollar-sign"></i>
                <span>Insights Salaires</span>
            </a>
            <a href="multilingual_jobs.php" class="enterprise-nav-link <?= $current_page == 'multilingual_jobs.php' ? 'active' : '' ?>">
                <i class="fas fa-globe"></i>
                <span>Offres Multilingues</span>
                <span class="badge bg-warning">i18n</span>
            </a>
        </div>

        <!-- News & Content Section -->
        <div class="nav-section">
            <div class="nav-section-header">Actualités</div>
            <a href="../news.php" class="enterprise-nav-link">
                <i class="fas fa-newspaper"></i>
                <span>Actualités</span>
                <span class="badge bg-primary">Nouveau</span>
            </a>
            <a href="company_news.php" class="enterprise-nav-link <?= $current_page == 'company_news.php' ? 'active' : '' ?>">
                <i class="fas fa-building"></i>
                <span>Actualités Entreprise</span>
            </a>
        </div>

        <!-- System Section -->
        <div class="nav-section">
            <div class="nav-section-header">Système</div>
            <a href="../index.php" class="enterprise-nav-link">
                <i class="fas fa-home"></i>
                <span>Retour au Site</span>
            </a>
            <a href="../logout.php" class="enterprise-nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </div>
</nav>
