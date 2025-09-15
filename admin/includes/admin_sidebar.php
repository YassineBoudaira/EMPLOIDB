<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Enterprise Sidebar -->
<nav class="enterprise-sidebar" style="display: block !important; visibility: visible !important; transform: translateX(0) !important; position: fixed !important; left: 0 !important; top: 0 !important; width: 320px !important; height: 100vh !important; z-index: 1050 !important;">
    <div class="sidebar-brand">
        <h2><i class="fas fa-briefcase me-2"></i>EMPLOIDB</h2>
        <div class="subtitle">Enterprise Admin System</div>
    </div>
    
    <div class="enterprise-nav">
        <!-- Dashboard Section -->
       <div class="nav-section">
            <div class="nav-section-header">Dashboard</div>
            <a href="dashboard.php" class="enterprise-nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Analytics Section -->
        <div class="nav-section">
            <div class="nav-section-header">Analytics</div>
            <a href="user_analytics.php" class="enterprise-nav-link <?= $current_page == 'user_analytics.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>User Analytics</span>
                <span class="badge">Live</span>
            </a>
            <a href="real_time_monitoring.php" class="enterprise-nav-link <?= $current_page == 'real_time_monitoring.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i>
                <span>Real-time Monitoring</span>
            </a>
            <a href="performance_analytics.php" class="enterprise-nav-link <?= $current_page == 'performance_analytics.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Performance Analytics</span>
            </a>
            <a href="behavior_analytics.php" class="enterprise-nav-link <?= $current_page == 'behavior_analytics.php' ? 'active' : '' ?>">
                <i class="fas fa-brain"></i>
                <span>Behavior Analytics</span>
            </a>
            <a href="geographic_analytics.php" class="enterprise-nav-link <?= $current_page == 'geographic_analytics.php' ? 'active' : '' ?>">
                <i class="fas fa-globe"></i>
                <span>Geographic Analytics</span>
            </a>
            <a href="device_analytics.php" class="enterprise-nav-link <?= $current_page == 'device_analytics.php' ? 'active' : '' ?>">
                <i class="fas fa-mobile-alt"></i>
                <span>Device Analytics</span>
            </a>
        </div>

        <!-- Management Section -->
        <div class="nav-section">
            <div class="nav-section-header">Management</div>
            <a href="users.php" class="enterprise-nav-link <?= $current_page == 'users.php' ? 'active' : '' ?>">
                <i class="fas fa-user-friends"></i>
                <span>Users</span>
            </a>
            <a href="employers.php" class="enterprise-nav-link <?= $current_page == 'employers.php' ? 'active' : '' ?>">
                <i class="fas fa-building"></i>
                <span>Employers</span>
            </a>
            <a href="jobs.php" class="enterprise-nav-link <?= $current_page == 'jobs.php' ? 'active' : '' ?>">
                <i class="fas fa-briefcase"></i>
                <span>Jobs</span>
            </a>
            <a href="applications.php" class="enterprise-nav-link <?= $current_page == 'applications.php' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i>
                <span>Applications</span>
            </a>
            <a href="manage_profiles.php" class="enterprise-nav-link <?= $current_page == 'manage_profiles.php' ? 'active' : '' ?>">
                <i class="fas fa-id-card"></i>
                <span>Profiles</span>
            </a>
            <a href="manage_demands.php" class="enterprise-nav-link <?= $current_page == 'manage_demands.php' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list"></i>
                <span>Demands</span>
            </a>
        </div>

        <!-- Configuration Section -->
        <div class="nav-section">
            <div class="nav-section-header">Configuration</div>
            <a href="manage_contracts.php" class="enterprise-nav-link <?= $current_page == 'manage_contracts.php' ? 'active' : '' ?>">
                <i class="fas fa-file-contract"></i>
                <span>Contracts</span>
            </a>
            <a href="manage_domains.php" class="enterprise-nav-link <?= $current_page == 'manage_domains.php' ? 'active' : '' ?>">
                <i class="fas fa-sitemap"></i>
                <span>Domains</span>
            </a>
            <a href="manage_cities.php" class="enterprise-nav-link <?= $current_page == 'manage_cities.php' ? 'active' : '' ?>">
                <i class="fas fa-map-marker-alt"></i>
                <span>Cities</span>
            </a>
            <a href="offers.php" class="enterprise-nav-link <?= $current_page == 'offers.php' ? 'active' : '' ?>">
                <i class="fas fa-gift"></i>
                <span>Offers</span>
            </a>
        </div>

        <!-- Advertising Section -->
        <div class="nav-section">
            <div class="nav-section-header">Advertising</div>
            <a href="manage_advertisers.php" class="enterprise-nav-link <?= $current_page == 'manage_advertisers.php' ? 'active' : '' ?>">
                <i class="fas fa-ad"></i>
                <span>Advertisers</span>
            </a>
            <a href="manage_campaigns.php" class="enterprise-nav-link <?= $current_page == 'manage_campaigns.php' ? 'active' : '' ?>">
                <i class="fas fa-bullhorn"></i>
                <span>Campaigns</span>
            </a>
            <a href="manage_ads.php" class="enterprise-nav-link <?= $current_page == 'manage_ads.php' ? 'active' : '' ?>">
                <i class="fas fa-ad"></i>
                <span>Ads</span>
            </a>
            <a href="ads_analytics.php" class="enterprise-nav-link <?= $current_page == 'ads_analytics.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-pie"></i>
                <span>Ads Analytics</span>
            </a>
        </div>

        <!-- API & Automation Section -->
        <div class="nav-section">
            <div class="nav-section-header">API & Automation</div>
            <a href="job_aggregation.php" class="enterprise-nav-link <?= $current_page == 'job_aggregation.php' ? 'active' : '' ?>">
                <i class="fas fa-sync-alt"></i>
                <span>Job Aggregation</span>
                <span class="badge bg-success">Live</span>
            </a>
            <a href="job_aggregation_analytics.php" class="enterprise-nav-link <?= $current_page == 'job_aggregation_analytics.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i>
                <span>Aggregation Analytics</span>
            </a>
            <a href="job_aggregation_monitor.php" class="enterprise-nav-link <?= $current_page == 'job_aggregation_monitor.php' ? 'active' : '' ?>">
                <i class="fas fa-heartbeat"></i>
                <span>Aggregation Monitor</span>
            </a>
            <a href="api_management.php" class="enterprise-nav-link <?= $current_page == 'api_management.php' ? 'active' : '' ?>">
                <i class="fas fa-plug"></i>
                <span>API Management</span>
            </a>
            <a href="automation_settings.php" class="enterprise-nav-link <?= $current_page == 'automation_settings.php' ? 'active' : '' ?>">
                <i class="fas fa-robot"></i>
                <span>Automation Settings</span>
            </a>
            <a href="feature_settings.php" class="enterprise-nav-link <?= $current_page == 'feature_settings.php' ? 'active' : '' ?>">
                <i class="fas fa-sliders-h"></i>
                <span>Feature Settings</span>
            </a>
        </div>

        <!-- System Section -->
        <div class="nav-section">
            <div class="nav-section-header">System</div>
            <a href="reports.php" class="enterprise-nav-link <?= $current_page == 'reports.php' ? 'active' : '' ?>">
                <i class="fas fa-file-chart-line"></i>
                <span>Reports</span>
            </a>
            <a href="gdpr_compliance.php" class="enterprise-nav-link <?= $current_page == 'gdpr_compliance.php' ? 'active' : '' ?>">
                <i class="fas fa-user-shield"></i>
                <span>GDPR Compliance</span>
            </a>
            <a href="privacy_settings.php" class="enterprise-nav-link <?= $current_page == 'privacy_settings.php' ? 'active' : '' ?>">
                <i class="fas fa-user-lock"></i>
                <span>Privacy & Consents</span>
            </a>
            <a href="settings.php" class="enterprise-nav-link <?= $current_page == 'settings.php' ? 'active' : '' ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>

        <!-- Admin Tools Section -->
        <div class="nav-section">
            <div class="nav-section-header">Admin Tools</div>
            <a href="admin_database_manager.php" class="enterprise-nav-link <?= $current_page == 'admin_database_manager.php' ? 'active' : '' ?>">
                <i class="fas fa-database"></i>
                <span>Database Manager</span>
            </a>
            <a href="admin_direct.php" class="enterprise-nav-link <?= $current_page == 'admin_direct.php' ? 'active' : '' ?>">
                <i class="fas fa-crown"></i>
                <span>Direct Admin Access</span>
            </a>
            <a href="fix_ip_blocking.php" class="enterprise-nav-link <?= $current_page == 'fix_ip_blocking.php' ? 'active' : '' ?>">
                <i class="fas fa-shield-alt"></i>
                <span>IP Blocking Fix</span>
            </a>
            <a href="log.php" class="enterprise-nav-link <?= $current_page == 'log.php' ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i>
                <span>System Logs</span>
            </a>
            <a href="test_reports_enhancement.php" class="enterprise-nav-link <?= $current_page == 'test_reports_enhancement.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i>
                <span>Test Reports</span>
            </a>
            <a href="test_session.php" class="enterprise-nav-link <?= $current_page == 'test_session.php' ? 'active' : '' ?>">
                <i class="fas fa-search"></i>
                <span>Session Test</span>
            </a>
            <a href="update_passwords_by_role.php" class="enterprise-nav-link <?= $current_page == 'update_passwords_by_role.php' ? 'active' : '' ?>">
                <i class="fas fa-key"></i>
                <span>Password Manager</span>
            </a>
            <a href="show_login_credentials.php" class="enterprise-nav-link <?= $current_page == 'show_login_credentials.php' ? 'active' : '' ?>">
                <i class="fas fa-user-secret"></i>
                <span>Login Credentials</span>
            </a>
        </div>

        <!-- System Tools Section -->
        <div class="nav-section">
            <div class="nav-section-header">System Tools</div>
            <a href="admin_system_status.php" class="enterprise-nav-link <?= $current_page == 'admin_system_status.php' ? 'active' : '' ?>">
                <i class="fas fa-server"></i>
                <span>System Status</span>
            </a>
            <a href="emploidb_performance_optimizer.php" class="enterprise-nav-link <?= $current_page == 'emploidb_performance_optimizer.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Performance Optimizer</span>
            </a>
            <a href="emploidb_advanced_features.php" class="enterprise-nav-link <?= $current_page == 'emploidb_advanced_features.php' ? 'active' : '' ?>">
                <i class="fas fa-cogs"></i>
                <span>Advanced Features</span>
            </a>
            <a href="admin_debug.php" class="enterprise-nav-link <?= $current_page == 'admin_debug.php' ? 'active' : '' ?>">
                <i class="fas fa-bug"></i>
                <span>Debug Tools</span>
            </a>
        </div>

        <!-- Enhanced Features Section -->
        <div class="nav-section">
            <div class="nav-section-header">Enhanced Features</div>
            <a href="enhanced_features_manager.php" class="enterprise-nav-link <?= $current_page == 'enhanced_features_manager.php' ? 'active' : '' ?>">
                <i class="fas fa-rocket"></i>
                <span>Features Manager</span>
                <span class="badge bg-primary">New</span>
            </a>
            <a href="ai_features.php" class="enterprise-nav-link <?= $current_page == 'ai_features.php' ? 'active' : '' ?>">
                <i class="fas fa-brain"></i>
                <span>AI Features</span>
                <span class="badge bg-success">AI</span>
            </a>
            <a href="oauth_management.php" class="enterprise-nav-link <?= $current_page == 'oauth_management.php' ? 'active' : '' ?>">
                <i class="fas fa-key"></i>
                <span>OAuth/SSO</span>
                <span class="badge bg-info">SSO</span>
            </a>
            <a href="multilingual_management.php" class="enterprise-nav-link <?= $current_page == 'multilingual_management.php' ? 'active' : '' ?>">
                <i class="fas fa-globe"></i>
                <span>Multilingual</span>
                <span class="badge bg-warning">i18n</span>
            </a>
            <a href="seo_management.php" class="enterprise-nav-link <?= $current_page == 'seo_management.php' ? 'active' : '' ?>">
                <i class="fas fa-search"></i>
                <span>SEO Management</span>
                <span class="badge bg-secondary">SEO</span>
            </a>
            <a href="audit_logs.php" class="enterprise-nav-link <?= $current_page == 'audit_logs.php' ? 'active' : '' ?>">
                <i class="fas fa-clipboard-list"></i>
                <span>Audit Logs</span>
                <span class="badge bg-dark">Audit</span>
            </a>
        </div>

        <!-- News & Content Section -->
        <div class="nav-section">
            <div class="nav-section-header">News & Content</div>
            <a href="news_management.php" class="enterprise-nav-link <?= $current_page == 'news_management.php' ? 'active' : '' ?>">
                <i class="fas fa-newspaper"></i>
                <span>News Management</span>
                <span class="badge bg-primary">Content</span>
            </a>
            <a href="news_categories.php" class="enterprise-nav-link <?= $current_page == 'news_categories.php' ? 'active' : '' ?>">
                <i class="fas fa-folder"></i>
                <span>News Categories</span>
            </a>
            <a href="newsletter_management.php" class="enterprise-nav-link <?= $current_page == 'newsletter_management.php' ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i>
                <span>Newsletter</span>
            </a>
        </div>

        <!-- Navigation Section -->
        <div class="nav-section">
            <div class="nav-section-header">Navigation</div>
            <a href="../../index.php" class="enterprise-nav-link">
                <i class="fas fa-home"></i>
                <span>Back to Site</span>
            </a>
            <a href="../../logout.php" class="enterprise-nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
</nav>
