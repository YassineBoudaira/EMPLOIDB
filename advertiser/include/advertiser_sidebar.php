<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

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
                <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Tableau de Bord</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'create_ad.php' ? 'active' : ''; ?>" href="create_ad.php">
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
                <a class="nav-link <?php echo $current_page === 'payment.php' ? 'active' : ''; ?>" href="payment.php">
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
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['advertiser_company'] ?? 'Annonceur'); ?></div>
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
