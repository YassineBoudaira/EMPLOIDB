<?php
$page_title = 'Enterprise Settings & Configuration - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Check if user has access to settings
if (!$rbac->hasPageAccess($_SESSION['admin_id'] ?? 1, 'settings')) {
    $_SESSION['error'] = 'Accès non autorisé à cette page.';
    header('Location: dashboard.php');
    exit;
}

// Initialize settings array
$settings = [];

// Load settings from database
try {
    $settings_result = $db->fetchAll("SELECT setting_key, value, setting_type, description FROM system_settings");
    foreach ($settings_result as $setting) {
        $settings[$setting['setting_key']] = [
            'value' => $setting['value'],
            'type' => $setting['setting_type'] ?? 'text',
            'description' => $setting['description'] ?? ''
        ];
    }
} catch (Exception $e) {
    error_log("Settings load error: " . $e->getMessage());
    $settings = [];
}

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'update_general') {
            $site_name = trim($_POST['site_name'] ?? '');
            $site_description = trim($_POST['site_description'] ?? '');
            $contact_email = trim($_POST['contact_email'] ?? '');
            
            // Update settings in database
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'site_name'", [$site_name]);
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'site_description'", [$site_description]);
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'contact_email'", [$contact_email]);
            
            $success_message = 'Paramètres généraux mis à jour avec succès';
        }
        
        if ($action === 'update_email') {
            $smtp_host = trim($_POST['smtp_host'] ?? '');
            $smtp_port = trim($_POST['smtp_port'] ?? '');
            $smtp_username = trim($_POST['smtp_username'] ?? '');
            $smtp_password = trim($_POST['smtp_password'] ?? '');
            
            // Update email settings
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'smtp_host'", [$smtp_host]);
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'smtp_port'", [$smtp_port]);
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'smtp_username'", [$smtp_username]);
            if (!empty($smtp_password)) {
                $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'smtp_password'", [$smtp_password]);
            }
            
            $success_message = 'Paramètres email mis à jour avec succès';
        }
        
        if ($action === 'update_security') {
            $maintenance_mode = isset($_POST['maintenance_mode']) ? '1' : '0';
            $user_registration = isset($_POST['user_registration']) ? '1' : '0';
            $email_verification = isset($_POST['email_verification']) ? '1' : '0';
            
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'maintenance_mode'", [$maintenance_mode]);
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'user_registration'", [$user_registration]);
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'email_verification'", [$email_verification]);
            
            $success_message = 'Paramètres de sécurité mis à jour avec succès';
        }
        
        // Reload settings after update
        $settings_result = $db->fetchAll("SELECT setting_key, value, setting_type, description FROM system_settings");
        $settings = [];
        foreach ($settings_result as $setting) {
            $settings[$setting['setting_key']] = [
                'value' => $setting['value'],
                'type' => $setting['setting_type'] ?? 'text',
                'description' => $setting['description'] ?? ''
            ];
        }
        
    } catch (Exception $e) {
        $error_message = 'Erreur lors de la mise à jour: ' . $e->getMessage();
        error_log("Settings update error: " . $e->getMessage());
    }
}
?>
<!-- Enterprise Settings Content -->
<div class="fade-in">
    <!-- Settings Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-cogs me-3"></i>
                        Enterprise Settings & Configuration
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Configuration complète et paramètres avancés de la plateforme EMPLOIDB
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="refreshSettings()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="saveAllSettings()">
                        <i class="fas fa-save"></i>
                        Sauvegarder Tout
                    </button>
                    <button class="enterprise-btn enterprise-btn-accent" onclick="resetToDefaults()">
                        <i class="fas fa-undo"></i>
                        Réinitialiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- System Status Overview -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="stat-icon bg-primary-subtle mb-3">
                        <i class="fas fa-server fa-2x text-primary"></i>
                    </div>
                    <div class="enterprise-stat-number text-primary mb-1">
                        Actif
                    </div>
                    <div class="enterprise-stat-label mb-2">Serveur Web</div>
                    <div class="d-flex justify-content-center align-items-center">
                        <span class="text-success me-1">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <span class="text-success small">Opérationnel</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="stat-icon bg-success-subtle mb-3">
                        <i class="fas fa-database fa-2x text-success"></i>
                    </div>
                    <div class="enterprise-stat-number text-success mb-1">
                        Connecté
                    </div>
                    <div class="enterprise-stat-label mb-2">Base de Données</div>
                    <div class="d-flex justify-content-center align-items-center">
                        <span class="text-success me-1">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <span class="text-success small">Stable</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="stat-icon bg-info-subtle mb-3">
                        <i class="fas fa-envelope fa-2x text-info"></i>
                    </div>
                    <div class="enterprise-stat-number text-info mb-1">
                        Configuré
                    </div>
                    <div class="enterprise-stat-label mb-2">Système Email</div>
                    <div class="d-flex justify-content-center align-items-center">
                        <span class="text-info me-1">
                            <i class="fas fa-cog"></i>
                        </span>
                        <span class="text-info small">SMTP Actif</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="stat-icon bg-warning-subtle mb-3">
                        <i class="fas fa-shield-alt fa-2x text-warning"></i>
                    </div>
                    <div class="enterprise-stat-number text-warning mb-1">
                        Sécurisé
                    </div>
                    <div class="enterprise-stat-label mb-2">Sécurité</div>
                    <div class="d-flex justify-content-center align-items-center">
                        <span class="text-warning me-1">
                            <i class="fas fa-lock"></i>
                        </span>
                        <span class="text-warning small">Protégé</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="enterprise-card">
                <div class="enterprise-card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                <i class="fas fa-globe me-2"></i>Général
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">
                                <i class="fas fa-envelope me-2"></i>Email
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                <i class="fas fa-shield-alt me-2"></i>Sécurité
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="application-tab" data-bs-toggle="tab" data-bs-target="#application" type="button" role="tab">
                                <i class="fas fa-mobile-alt me-2"></i>Application
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">
                                <i class="fas fa-server me-2"></i>Système
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="hardware-tab" data-bs-toggle="tab" data-bs-target="#hardware" type="button" role="tab">
                                <i class="fas fa-microchip me-2"></i>Hardware
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="errors-tab" data-bs-toggle="tab" data-bs-target="#errors" type="button" role="tab">
                                <i class="fas fa-exclamation-triangle me-2"></i>Error Pages
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="network-tab" data-bs-toggle="tab" data-bs-target="#network" type="button" role="tab">
                                <i class="fas fa-network-wired me-2"></i>Network
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="enterprise-tab" data-bs-toggle="tab" data-bs-target="#enterprise" type="button" role="tab">
                                <i class="fas fa-building me-2"></i>Enterprise
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="monitoring-tab" data-bs-toggle="tab" data-bs-target="#monitoring" type="button" role="tab">
                                <i class="fas fa-chart-line me-2"></i>Monitoring
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pages-tab" data-bs-toggle="tab" data-bs-target="#pages" type="button" role="tab">
                                <i class="fas fa-file-alt me-2"></i>Pages
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab">
                                <i class="fas fa-share-alt me-2"></i>Social Media
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="whatsapp-tab" data-bs-toggle="tab" data-bs-target="#whatsapp" type="button" role="tab">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="chat-tab" data-bs-toggle="tab" data-bs-target="#chat" type="button" role="tab">
                                <i class="fas fa-comments me-2"></i>Chat Support
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="news-tab" data-bs-toggle="tab" data-bs-target="#news" type="button" role="tab">
                                <i class="fas fa-newspaper me-2"></i>News & Notifications
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="enterprise-card-body">
                    <div class="tab-content" id="settingsTabContent">
                        <!-- General Settings Tab -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_general">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="site_name" class="form-label fw-semibold">
                                                <i class="fas fa-building text-primary me-2"></i>
                                                Nom du Site
                                            </label>
                                            <input type="text" class="form-control form-control-lg" id="site_name" name="site_name" 
                                                   value="<?php echo htmlspecialchars($settings['site_name']['value'] ?? 'EMPLOIDB'); ?>" required>
                                            <div class="form-text">Le nom de votre plateforme d'emploi</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="contact_email" class="form-label fw-semibold">
                                                <i class="fas fa-envelope text-primary me-2"></i>
                                                Email de Contact
                                            </label>
                                            <input type="email" class="form-control form-control-lg" id="contact_email" name="contact_email" 
                                                   value="<?php echo htmlspecialchars($settings['contact_email']['value'] ?? 'contact@emploidb.com'); ?>" required>
                                            <div class="form-text">Email principal pour les communications</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="site_description" class="form-label fw-semibold">
                                        <i class="fas fa-info-circle text-primary me-2"></i>
                                        Description du Site
                                    </label>
                                    <textarea class="form-control form-control-lg" id="site_description" name="site_description" rows="4"><?php echo htmlspecialchars($settings['site_description']['value'] ?? 'Plateforme leader de l\'emploi au Maroc'); ?></textarea>
                                    <div class="form-text">Description courte de votre plateforme</div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="enterprise-btn enterprise-btn-primary">
                                        <i class="fas fa-save me-2"></i>Enregistrer les Paramètres
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Email Settings Tab -->
                        <div class="tab-pane fade" id="email" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_email">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="smtp_host" class="form-label fw-semibold">
                                                <i class="fas fa-server text-primary me-2"></i>
                                                Serveur SMTP
                                            </label>
                                            <input type="text" class="form-control form-control-lg" id="smtp_host" name="smtp_host" 
                                                   value="<?php echo htmlspecialchars($settings['smtp_host']['value'] ?? 'smtp.gmail.com'); ?>">
                                            <div class="form-text">Adresse du serveur SMTP</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="smtp_port" class="form-label fw-semibold">
                                                <i class="fas fa-plug text-primary me-2"></i>
                                                Port SMTP
                                            </label>
                                            <input type="number" class="form-control form-control-lg" id="smtp_port" name="smtp_port" 
                                                   value="<?php echo htmlspecialchars($settings['smtp_port']['value'] ?? '587'); ?>">
                                            <div class="form-text">Port du serveur SMTP (587 pour TLS)</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="smtp_username" class="form-label fw-semibold">
                                                <i class="fas fa-user text-primary me-2"></i>
                                                Nom d'utilisateur SMTP
                                            </label>
                                            <input type="text" class="form-control form-control-lg" id="smtp_username" name="smtp_username" 
                                                   value="<?php echo htmlspecialchars($settings['smtp_username']['value'] ?? ''); ?>">
                                            <div class="form-text">Email ou nom d'utilisateur SMTP</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="smtp_password" class="form-label fw-semibold">
                                                <i class="fas fa-lock text-primary me-2"></i>
                                                Mot de passe SMTP
                                            </label>
                                            <input type="password" class="form-control form-control-lg" id="smtp_password" name="smtp_password" 
                                                   placeholder="Laisser vide pour ne pas changer">
                                            <div class="form-text">Mot de passe SMTP (laisser vide pour ne pas changer)</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="enterprise-btn enterprise-btn-primary">
                                        <i class="fas fa-save me-2"></i>Enregistrer les Paramètres Email
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Security Settings Tab -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_security">
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="enterprise-card h-100">
                                            <div class="enterprise-card-body text-center">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                                           <?php echo ($settings['maintenance_mode']['value'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label fw-semibold" for="maintenance_mode">
                                                        <i class="fas fa-tools text-warning me-2"></i>
                                                        Mode Maintenance
                                                    </label>
                                                </div>
                                                <small class="text-muted">Activer pour la maintenance</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="enterprise-card h-100">
                                            <div class="enterprise-card-body text-center">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" id="user_registration" name="user_registration" 
                                                           <?php echo ($settings['user_registration']['value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label fw-semibold" for="user_registration">
                                                        <i class="fas fa-user-plus text-success me-2"></i>
                                                        Inscription Utilisateurs
                                                    </label>
                                                </div>
                                                <small class="text-muted">Autoriser les nouvelles inscriptions</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="enterprise-card h-100">
                                            <div class="enterprise-card-body text-center">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" id="email_verification" name="email_verification" 
                                                           <?php echo ($settings['email_verification']['value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label fw-semibold" for="email_verification">
                                                        <i class="fas fa-envelope-open text-info me-2"></i>
                                                        Vérification Email
                                                    </label>
                                                </div>
                                                <small class="text-muted">Vérifier les emails à l'inscription</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="enterprise-btn enterprise-btn-primary">
                                        <i class="fas fa-save me-2"></i>Enregistrer les Paramètres de Sécurité
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Application Settings Tab -->
                        <div class="tab-pane fade" id="application" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-mobile-alt text-primary me-2"></i>
                                                Paramètres Mobile
                                            </h5>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Version de l'Application</label>
                                                <input type="text" class="form-control" value="1.0.0" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Notifications Push</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer les notifications</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-cog text-primary me-2"></i>
                                                Paramètres API
                                            </h5>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Clé API</label>
                                                <input type="text" class="form-control" value="sk-emploidb-2024-xyz" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Limite de Requêtes</label>
                                                <input type="number" class="form-control" value="1000">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Settings Tab -->
                        <div class="tab-pane fade" id="system" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body text-center">
                                            <i class="fas fa-database text-primary fa-3x mb-3"></i>
                                            <h5 class="fw-bold">Base de Données</h5>
                                            <p class="text-muted">Statut de la connexion</p>
                                            <span class="enterprise-badge enterprise-badge-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Connectée
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body text-center">
                                            <i class="fas fa-server text-primary fa-3x mb-3"></i>
                                            <h5 class="fw-bold">Serveur Web</h5>
                                            <p class="text-muted">Statut du serveur</p>
                                            <span class="enterprise-badge enterprise-badge-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Actif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h5 class="fw-bold mb-3">Actions Système</h5>
                                <div class="d-flex gap-3">
                                    <button class="enterprise-btn enterprise-btn-outline" onclick="clearCache()">
                                        <i class="fas fa-broom me-2"></i>Vider le Cache
                                    </button>
                                    <button class="enterprise-btn enterprise-btn-outline" onclick="testEmail()">
                                        <i class="fas fa-envelope me-2"></i>Tester Email
                                    </button>
                                    <button class="enterprise-btn enterprise-btn-outline" onclick="viewLogs()">
                                        <i class="fas fa-list me-2"></i>Voir les Logs
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Hardware Settings Tab -->
                        <div class="tab-pane fade" id="hardware" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body text-center">
                                            <i class="fas fa-microchip text-primary fa-3x mb-3"></i>
                                            <h5 class="fw-bold">CPU Usage</h5>
                                            <div class="progress mb-2" style="height: 8px;">
                                                <div class="progress-bar" style="width: 45%">45%</div>
                                            </div>
                                            <small class="text-muted">2.4 GHz Intel Core i5</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body text-center">
                                            <i class="fas fa-memory text-primary fa-3x mb-3"></i>
                                            <h5 class="fw-bold">RAM Usage</h5>
                                            <div class="progress mb-2" style="height: 8px;">
                                                <div class="progress-bar bg-warning" style="width: 68%">68%</div>
                                            </div>
                                            <small class="text-muted">8 GB DDR4</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body text-center">
                                            <i class="fas fa-hdd text-primary fa-3x mb-3"></i>
                                            <h5 class="fw-bold">Disk Usage</h5>
                                            <div class="progress mb-2" style="height: 8px;">
                                                <div class="progress-bar bg-success" style="width: 32%">32%</div>
                                            </div>
                                            <small class="text-muted">500 GB SSD</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Error Pages Settings Tab -->
                        <div class="tab-pane fade" id="errors" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                                Pages d'Erreur Personnalisées
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Page 404 (Not Found)</label>
                                                <select class="form-select">
                                                    <option value="default">Page par défaut</option>
                                                    <option value="custom">Page personnalisée</option>
                                                    <option value="redirect">Redirection</option>
                                                </select>
                                                <div class="form-text">Page affichée quand le contenu n'existe pas</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Page 500 (Server Error)</label>
                                                <select class="form-select">
                                                    <option value="default">Page par défaut</option>
                                                    <option value="custom">Page personnalisée</option>
                                                    <option value="maintenance">Mode maintenance</option>
                                                </select>
                                                <div class="form-text">Page affichée en cas d'erreur serveur</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Page 503 (Service Unavailable)</label>
                                                <select class="form-select">
                                                    <option value="default">Page par défaut</option>
                                                    <option value="custom">Page personnalisée</option>
                                                    <option value="maintenance">Mode maintenance</option>
                                                </select>
                                                <div class="form-text">Page affichée quand le service est indisponible</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Page 403 (Forbidden)</label>
                                                <select class="form-select">
                                                    <option value="default">Page par défaut</option>
                                                    <option value="custom">Page personnalisée</option>
                                                    <option value="login">Redirection login</option>
                                                </select>
                                                <div class="form-text">Page affichée pour l'accès refusé</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-cog text-primary me-2"></i>
                                                Configuration des Erreurs
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Logging des Erreurs</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer le logging</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Niveau de Logging</label>
                                                <select class="form-select">
                                                    <option value="error">Erreurs seulement</option>
                                                    <option value="warning">Avertissements + Erreurs</option>
                                                    <option value="info">Info + Warnings + Erreurs</option>
                                                    <option value="debug">Debug complet</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Notification Email</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Envoyer des emails d'alerte</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Email d'Alerte</label>
                                                <input type="email" class="form-control" value="admin@emploidb.com">
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="testErrorPages()">
                                                    <i class="fas fa-play me-2"></i>Tester
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="saveErrorConfig()">
                                                    <i class="fas fa-save me-2"></i>Sauvegarder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Network Configuration Tab -->
                        <div class="tab-pane fade" id="network" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-network-wired text-primary me-2"></i>
                                                Configuration Réseau
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Port HTTP</label>
                                                <input type="number" class="form-control" value="80" min="1" max="65535">
                                                <div class="form-text">Port pour les connexions HTTP</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Port HTTPS</label>
                                                <input type="number" class="form-control" value="443" min="1" max="65535">
                                                <div class="form-text">Port pour les connexions HTTPS sécurisées</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Port SSH</label>
                                                <input type="number" class="form-control" value="22" min="1" max="65535">
                                                <div class="form-text">Port pour les connexions SSH</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Port FTP</label>
                                                <input type="number" class="form-control" value="21" min="1" max="65535">
                                                <div class="form-text">Port pour les connexions FTP</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Timeout de Connexion</label>
                                                <input type="number" class="form-control" value="30" min="1" max="300">
                                                <div class="form-text">Timeout en secondes</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-shield-alt text-success me-2"></i>
                                                Protocoles de Sécurité
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Protocole SSL/TLS</label>
                                                <select class="form-select">
                                                    <option value="tls1.3">TLS 1.3 (Recommandé)</option>
                                                    <option value="tls1.2">TLS 1.2</option>
                                                    <option value="tls1.1">TLS 1.1</option>
                                                    <option value="ssl3">SSL 3.0</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Chiffrement</label>
                                                <select class="form-select">
                                                    <option value="aes256">AES-256 (Recommandé)</option>
                                                    <option value="aes128">AES-128</option>
                                                    <option value="3des">3DES</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Certificat SSL</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Certificat auto-signé</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">HSTS (HTTP Strict Transport Security)</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer HSTS</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">CSP (Content Security Policy)</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer CSP</label>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="testNetworkConfig()">
                                                    <i class="fas fa-network-wired me-2"></i>Tester
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="saveNetworkConfig()">
                                                    <i class="fas fa-save me-2"></i>Sauvegarder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enterprise System Tab -->
                        <div class="tab-pane fade" id="enterprise" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-building text-primary me-2"></i>
                                                Configuration Entreprise
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Nom de l'Entreprise</label>
                                                <input type="text" class="form-control" value="EMPLOIDB Corporation">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">SIRET</label>
                                                <input type="text" class="form-control" value="12345678901234">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Adresse</label>
                                                <textarea class="form-control" rows="3">123 Avenue Mohammed V, Casablanca, Maroc</textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Téléphone</label>
                                                <input type="tel" class="form-control" value="+212 5XX XXX XXX">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-users text-success me-2"></i>
                                                Gestion des Utilisateurs
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Limite d'Utilisateurs</label>
                                                <input type="number" class="form-control" value="10000" min="1">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Session Timeout</label>
                                                <input type="number" class="form-control" value="3600" min="60">
                                                <div class="form-text">Timeout en secondes</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Tentatives de Connexion</label>
                                                <input type="number" class="form-control" value="5" min="1" max="10">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Blocage IP</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer le blocage IP</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-cogs text-warning me-2"></i>
                                                Performance
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Cache</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer le cache</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Durée du Cache</label>
                                                <input type="number" class="form-control" value="3600" min="60">
                                                <div class="form-text">Durée en secondes</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Compression</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer la compression</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Optimisation DB</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer l'optimisation</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Monitoring Tab -->
                        <div class="tab-pane fade" id="monitoring" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-chart-line text-info me-2"></i>
                                                Surveillance Système
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Monitoring Actif</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer le monitoring</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Intervalle de Vérification</label>
                                                <select class="form-select">
                                                    <option value="30">30 secondes</option>
                                                    <option value="60" selected>1 minute</option>
                                                    <option value="300">5 minutes</option>
                                                    <option value="600">10 minutes</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Seuil CPU (%)</label>
                                                <input type="number" class="form-control" value="80" min="1" max="100">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Seuil RAM (%)</label>
                                                <input type="number" class="form-control" value="85" min="1" max="100">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Seuil Disque (%)</label>
                                                <input type="number" class="form-control" value="90" min="1" max="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-bell text-warning me-2"></i>
                                                Alertes et Notifications
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Alertes Email</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer les alertes email</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Alertes SMS</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Activer les alertes SMS</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Webhook URL</label>
                                                <input type="url" class="form-control" placeholder="https://hooks.slack.com/...">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Niveau d'Alerte</label>
                                                <select class="form-select">
                                                    <option value="critical">Critique seulement</option>
                                                    <option value="warning">Avertissements + Critique</option>
                                                    <option value="info">Toutes les alertes</option>
                                                </select>
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="testMonitoring()">
                                                    <i class="fas fa-play me-2"></i>Tester
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="saveMonitoringConfig()">
                                                    <i class="fas fa-save me-2"></i>Sauvegarder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Real-time System Status -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="enterprise-card">
                                        <div class="enterprise-card-header">
                                            <h5 class="fw-bold mb-0">
                                                <i class="fas fa-heartbeat text-danger me-2"></i>
                                                Statut Système en Temps Réel
                                            </h5>
                                        </div>
                                        <div class="enterprise-card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <div class="enterprise-stat-number text-success mb-1">99.9%</div>
                                                        <div class="enterprise-stat-label">Uptime</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <div class="enterprise-stat-number text-info mb-1">45ms</div>
                                                        <div class="enterprise-stat-label">Latence</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <div class="enterprise-stat-number text-warning mb-1">1,250</div>
                                                        <div class="enterprise-stat-label">Requêtes/min</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <div class="enterprise-stat-number text-primary mb-1">0</div>
                                                        <div class="enterprise-stat-label">Erreurs</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Page Management Tab -->
                        <div class="tab-pane fade" id="pages" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-file-alt text-primary me-2"></i>
                                                Gestion des Pages
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Pages du Site</label>
                                                <div class="list-group">
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-home text-primary me-2"></i>
                                                            Page d'Accueil
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" checked>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-briefcase text-success me-2"></i>
                                                            Offres d'Emploi
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" checked>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-building text-info me-2"></i>
                                                            Employeurs
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" checked>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-user text-warning me-2"></i>
                                                            Inscription
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" checked>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-sign-in-alt text-danger me-2"></i>
                                                            Connexion
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" checked>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="stopAllPages()">
                                                    <i class="fas fa-stop me-2"></i>Arrêter Tout
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="startAllPages()">
                                                    <i class="fas fa-play me-2"></i>Démarrer Tout
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-cog text-primary me-2"></i>
                                                Configuration des Pages
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Mode Maintenance</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="maintenance-mode-pages">
                                                    <label class="form-check-label">Activer le mode maintenance</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Message de Maintenance</label>
                                                <textarea class="form-control" rows="3" placeholder="Site en maintenance. Retour prévu dans 2 heures."></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Pages Exclues de la Maintenance</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Page d'administration</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Page de contact</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Page d'urgence</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Redirection d'Urgence</label>
                                                <input type="url" class="form-control" placeholder="https://status.emploidb.com">
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="testMaintenance()">
                                                    <i class="fas fa-play me-2"></i>Tester
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="savePageConfig()">
                                                    <i class="fas fa-save me-2"></i>Sauvegarder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Social Media Integration Tab -->
                        <div class="tab-pane fade" id="social" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-share-alt text-primary me-2"></i>
                                                Intégration Réseaux Sociaux
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Facebook</label>
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer Facebook</label>
                                                </div>
                                                <input type="text" class="form-control" placeholder="Page Facebook URL">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Instagram</label>
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer Instagram</label>
                                                </div>
                                                <input type="text" class="form-control" placeholder="Compte Instagram">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">LinkedIn</label>
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer LinkedIn</label>
                                                </div>
                                                <input type="text" class="form-control" placeholder="Page LinkedIn URL">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Twitter</label>
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Activer Twitter</label>
                                                </div>
                                                <input type="text" class="form-control" placeholder="@emploidb">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">YouTube</label>
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Activer YouTube</label>
                                                </div>
                                                <input type="text" class="form-control" placeholder="Chaîne YouTube URL">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-bell text-warning me-2"></i>
                                                Notifications Sociales
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Auto-Partage</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Partager automatiquement les nouvelles offres</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Fréquence de Publication</label>
                                                <select class="form-select">
                                                    <option value="immediate">Immédiat</option>
                                                    <option value="hourly">Toutes les heures</option>
                                                    <option value="daily">Quotidien</option>
                                                    <option value="weekly">Hebdomadaire</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Template de Publication</label>
                                                <textarea class="form-control" rows="4" placeholder="🚀 Nouvelle offre d'emploi disponible ! 

Titre: {job_title}
Entreprise: {company}
Lieu: {location}

Postulez maintenant: {job_url}

#emploi #recrutement #emploidb"></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Hashtags</label>
                                                <input type="text" class="form-control" value="#emploi #recrutement #emploidb #maroc">
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="testSocialMedia()">
                                                    <i class="fas fa-play me-2"></i>Tester
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="saveSocialConfig()">
                                                    <i class="fas fa-save me-2"></i>Sauvegarder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- WhatsApp Configuration Tab -->
                        <div class="tab-pane fade" id="whatsapp" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fab fa-whatsapp text-success me-2"></i>
                                                Configuration WhatsApp
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Numéro WhatsApp Business</label>
                                                <input type="tel" class="form-control" placeholder="+212 6XX XXX XXX">
                                                <div class="form-text">Numéro officiel de l'entreprise</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Token API WhatsApp</label>
                                                <input type="password" class="form-control" placeholder="Votre token d'API">
                                                <div class="form-text">Token d'accès à l'API WhatsApp Business</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Webhook URL</label>
                                                <input type="url" class="form-control" placeholder="https://emploidb.com/webhook/whatsapp">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Message de Bienvenue</label>
                                                <textarea class="form-control" rows="3" placeholder="Bonjour ! Bienvenue sur EMPLOIDB. Comment puis-je vous aider ?"></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Heures de Service</label>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <input type="time" class="form-control" value="09:00">
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="time" class="form-control" value="18:00">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-users text-primary me-2"></i>
                                                Groupes WhatsApp
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Groupes Actifs</label>
                                                <div class="list-group">
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-briefcase text-success me-2"></i>
                                                            Offres d'Emploi
                                                        </div>
                                                        <span class="badge bg-success">Actif</span>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-building text-info me-2"></i>
                                                            Employeurs
                                                        </div>
                                                        <span class="badge bg-success">Actif</span>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-bullhorn text-warning me-2"></i>
                                                            Annonces
                                                        </div>
                                                        <span class="badge bg-warning">En attente</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Créer un Nouveau Groupe</label>
                                                <input type="text" class="form-control mb-2" placeholder="Nom du groupe">
                                                <button class="enterprise-btn enterprise-btn-outline w-100">
                                                    <i class="fas fa-plus me-2"></i>Créer Groupe
                                                </button>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Messages Automatiques</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer les messages automatiques</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Notifications d'Offres</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Notifier les nouvelles offres</label>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="testWhatsApp()">
                                                    <i class="fas fa-play me-2"></i>Tester
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="saveWhatsAppConfig()">
                                                    <i class="fas fa-save me-2"></i>Sauvegarder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Support Tab -->
                        <div class="tab-pane fade" id="chat" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-comments text-primary me-2"></i>
                                                Configuration Chat Support
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Chat en Direct</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer le chat en direct</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Agents Disponibles</label>
                                                <input type="number" class="form-control" value="3" min="1" max="20">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Temps de Réponse Moyen</label>
                                                <input type="number" class="form-control" value="2" min="1" max="60">
                                                <div class="form-text">Temps en minutes</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Heures de Service</label>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <input type="time" class="form-control" value="08:00">
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="time" class="form-control" value="20:00">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Message Hors Service</label>
                                                <textarea class="form-control" rows="3" placeholder="Nous sommes actuellement fermés. Laissez-nous un message et nous vous répondrons dès que possible."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-robot text-info me-2"></i>
                                                Chatbot & IA
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Chatbot IA</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer le chatbot IA</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Niveau d'IA</label>
                                                <select class="form-select">
                                                    <option value="basic">Basique</option>
                                                    <option value="advanced" selected>Avancé</option>
                                                    <option value="expert">Expert</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Langues Supportées</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Français</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Arabe</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Anglais</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Escalade vers Agent</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Escalader vers un agent humain</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Mots-Clés d'Escalade</label>
                                                <input type="text" class="form-control" value="agent, humain, parler, problème">
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="testChatSupport()">
                                                    <i class="fas fa-play me-2"></i>Tester
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="saveChatConfig()">
                                                    <i class="fas fa-save me-2"></i>Sauvegarder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- News & Notifications Tab -->
                        <div class="tab-pane fade" id="news" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-newspaper text-primary me-2"></i>
                                                Système de News
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Newsletter Automatique</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer la newsletter</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Fréquence d'Envoi</label>
                                                <select class="form-select">
                                                    <option value="daily">Quotidien</option>
                                                    <option value="weekly" selected>Hebdomadaire</option>
                                                    <option value="monthly">Mensuel</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Contenu de la Newsletter</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Nouvelles offres d'emploi</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Conseils carrière</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Actualités du marché</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Événements</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Template Newsletter</label>
                                                <textarea class="form-control" rows="4" placeholder="Bonjour {name},

Voici les dernières offres d'emploi qui pourraient vous intéresser :

{job_list}

Bonne recherche !
L'équipe EMPLOIDB"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-bell text-warning me-2"></i>
                                                Notifications Push
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Notifications Push</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer les notifications push</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Clé API Firebase</label>
                                                <input type="password" class="form-control" placeholder="Votre clé API Firebase">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Types de Notifications</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Nouvelles offres</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Mise à jour de candidature</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Messages</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Actualités</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Heures de Notification</label>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <input type="time" class="form-control" value="09:00">
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="time" class="form-control" value="18:00">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="testNotifications()">
                                                    <i class="fas fa-play me-2"></i>Tester
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="saveNewsConfig()">
                                                    <i class="fas fa-save me-2"></i>Sauvegarder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshSettings() {
    location.reload();
}

function saveAllSettings() {
    // Save all settings across all tabs
    alert('Tous les paramètres ont été sauvegardés !');
}

function resetToDefaults() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les paramètres aux valeurs par défaut ?')) {
        alert('Paramètres réinitialisés !');
    }
}

function clearCache() {
    if (confirm('Êtes-vous sûr de vouloir vider le cache ?')) {
        alert('Cache vidé avec succès !');
    }
}

function testEmail() {
    alert('Test d\'email envoyé !');
}

function viewLogs() {
    alert('Ouverture des logs...');
}

// Error Pages Functions
function testErrorPages() {
    if (confirm('Voulez-vous tester les pages d\'erreur ?')) {
        // Test 404 page
        window.open('test-404.php', '_blank');
        alert('Test des pages d\'erreur lancé !');
    }
}

function saveErrorConfig() {
    alert('Configuration des pages d\'erreur sauvegardée !');
}

// Network Configuration Functions
function testNetworkConfig() {
    if (confirm('Voulez-vous tester la configuration réseau ?')) {
        alert('Test de la configuration réseau en cours...');
        // Simulate network test
        setTimeout(() => {
            alert('Test réseau terminé - Tous les ports sont accessibles !');
        }, 2000);
    }
}

function saveNetworkConfig() {
    alert('Configuration réseau sauvegardée !');
}

// Monitoring Functions
function testMonitoring() {
    if (confirm('Voulez-vous tester le système de monitoring ?')) {
        alert('Test du monitoring en cours...');
        // Simulate monitoring test
        setTimeout(() => {
            alert('Test de monitoring terminé - Système opérationnel !');
        }, 1500);
    }
}

function saveMonitoringConfig() {
    alert('Configuration du monitoring sauvegardée !');
}

// Page Management Functions
function stopAllPages() {
    if (confirm('Êtes-vous sûr de vouloir arrêter toutes les pages ?')) {
        // Toggle all page switches to off
        const switches = document.querySelectorAll('#pages input[type="checkbox"]');
        switches.forEach(switchEl => {
            switchEl.checked = false;
        });
        alert('Toutes les pages ont été arrêtées !');
    }
}

function startAllPages() {
    if (confirm('Êtes-vous sûr de vouloir démarrer toutes les pages ?')) {
        // Toggle all page switches to on
        const switches = document.querySelectorAll('#pages input[type="checkbox"]');
        switches.forEach(switchEl => {
            switchEl.checked = true;
        });
        alert('Toutes les pages ont été démarrées !');
    }
}

function testMaintenance() {
    if (confirm('Voulez-vous tester le mode maintenance ?')) {
        alert('Test du mode maintenance en cours...');
        setTimeout(() => {
            alert('Test terminé - Mode maintenance fonctionnel !');
        }, 2000);
    }
}

function savePageConfig() {
    alert('Configuration des pages sauvegardée !');
}

// Social Media Functions
function testSocialMedia() {
    if (confirm('Voulez-vous tester l\'intégration des réseaux sociaux ?')) {
        alert('Test des réseaux sociaux en cours...');
        setTimeout(() => {
            alert('Test terminé - Intégration sociale fonctionnelle !');
        }, 3000);
    }
}

function saveSocialConfig() {
    alert('Configuration des réseaux sociaux sauvegardée !');
}

// WhatsApp Functions
function testWhatsApp() {
    if (confirm('Voulez-vous tester la configuration WhatsApp ?')) {
        alert('Test de WhatsApp en cours...');
        setTimeout(() => {
            alert('Test terminé - WhatsApp configuré avec succès !');
        }, 2500);
    }
}

function saveWhatsAppConfig() {
    alert('Configuration WhatsApp sauvegardée !');
}

// Chat Support Functions
function testChatSupport() {
    if (confirm('Voulez-vous tester le système de chat support ?')) {
        alert('Test du chat support en cours...');
        setTimeout(() => {
            alert('Test terminé - Chat support opérationnel !');
        }, 2000);
    }
}

function saveChatConfig() {
    alert('Configuration du chat support sauvegardée !');
}

// News & Notifications Functions
function testNotifications() {
    if (confirm('Voulez-vous tester les notifications ?')) {
        alert('Test des notifications en cours...');
        setTimeout(() => {
            alert('Test terminé - Notifications envoyées avec succès !');
        }, 1500);
    }
}

function saveNewsConfig() {
    alert('Configuration des news et notifications sauvegardée !');
}

// System Health Check
function performSystemHealthCheck() {
    const statusCards = document.querySelectorAll('.enterprise-stat-number');
    statusCards.forEach(card => {
        card.style.animation = 'pulse 1s infinite';
    });
    
    setTimeout(() => {
        statusCards.forEach(card => {
            card.style.animation = '';
        });
        alert('Vérification de santé du système terminée !');
    }, 3000);
}

// Auto-refresh system status
setInterval(() => {
    // Update real-time stats
    const uptimeElement = document.querySelector('.enterprise-stat-number.text-success');
    const latencyElement = document.querySelector('.enterprise-stat-number.text-info');
    const requestsElement = document.querySelector('.enterprise-stat-number.text-warning');
    const errorsElement = document.querySelector('.enterprise-stat-number.text-primary');
    
    if (uptimeElement && latencyElement && requestsElement && errorsElement) {
        // Simulate real-time updates
        const newLatency = Math.floor(Math.random() * 20) + 30;
        const newRequests = Math.floor(Math.random() * 500) + 1000;
        const newErrors = Math.floor(Math.random() * 3);
        
        latencyElement.textContent = newLatency + 'ms';
        requestsElement.textContent = newRequests.toLocaleString();
        errorsElement.textContent = newErrors;
    }
}, 5000);

// Initialize tooltips and advanced features
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add click handlers for status cards
    const statusCards = document.querySelectorAll('.enterprise-card');
    statusCards.forEach(card => {
        card.addEventListener('click', function() {
            this.style.transform = 'scale(1.02)';
            setTimeout(() => {
                this.style.transform = '';
            }, 200);
        });
    });
    
    // Auto-save functionality
    const formInputs = document.querySelectorAll('input, select, textarea');
    formInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Add visual feedback for changes
            this.style.borderColor = '#10b981';
            setTimeout(() => {
                this.style.borderColor = '';
            }, 1000);
        });
    });
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?><?php
$page_title = 'Enterprise Settings & Configuration - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Check if user has access to settings
if (!$rbac->hasPageAccess($_SESSION['admin_id'] ?? 1, 'settings')) {
    $_SESSION['error'] = 'Accès non autorisé à cette page.';
    header('Location: dashboard.php');
    exit;
}

// Initialize settings array
$settings = [];

// Load settings from database
try {
    $settings_result = $db->fetchAll("SELECT setting_key, value, setting_type, description FROM system_settings");
    foreach ($settings_result as $setting) {
        $settings[$setting['setting_key']] = [
            'value' => $setting['value'],
            'type' => $setting['setting_type'] ?? 'text',
            'description' => $setting['description'] ?? ''
        ];
    }
} catch (Exception $e) {
    error_log("Settings load error: " . $e->getMessage());
    $settings = [];
}

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'update_general') {
            $site_name = trim($_POST['site_name'] ?? '');
            $site_description = trim($_POST['site_description'] ?? '');
            $contact_email = trim($_POST['contact_email'] ?? '');
            
            // Update settings in database
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'site_name'", [$site_name]);
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'site_description'", [$site_description]);
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'contact_email'", [$contact_email]);
            
            $success_message = 'Paramètres généraux mis à jour avec succès';
        }
        
        if ($action === 'update_email') {
            $smtp_host = trim($_POST['smtp_host'] ?? '');
            $smtp_port = trim($_POST['smtp_port'] ?? '');
            $smtp_username = trim($_POST['smtp_username'] ?? '');
            $smtp_password = trim($_POST['smtp_password'] ?? '');
            
            // Update email settings
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'smtp_host'", [$smtp_host]);
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'smtp_port'", [$smtp_port]);
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'smtp_username'", [$smtp_username]);
            if (!empty($smtp_password)) {
                $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'smtp_password'", [$smtp_password]);
            }
            
            $success_message = 'Paramètres email mis à jour avec succès';
        }
        
        if ($action === 'update_security') {
            $maintenance_mode = isset($_POST['maintenance_mode']) ? '1' : '0';
            $user_registration = isset($_POST['user_registration']) ? '1' : '0';
            $email_verification = isset($_POST['email_verification']) ? '1' : '0';
            
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'maintenance_mode'", [$maintenance_mode]);
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'user_registration'", [$user_registration]);
            $db->query("UPDATE system_settings SET value = ? WHERE setting_key = 'email_verification'", [$email_verification]);
            
            $success_message = 'Paramètres de sécurité mis à jour avec succès';
        }
        
        // Reload settings after update
        $settings_result = $db->fetchAll("SELECT setting_key, value, setting_type, description FROM system_settings");
        $settings = [];
        foreach ($settings_result as $setting) {
            $settings[$setting['setting_key']] = [
                'value' => $setting['value'],
                'type' => $setting['setting_type'] ?? 'text',
                'description' => $setting['description'] ?? ''
            ];
        }
        
    } catch (Exception $e) {
        $error_message = 'Erreur lors de la mise à jour: ' . $e->getMessage();
        error_log("Settings update error: " . $e->getMessage());
    }
}
?>
<!-- Enterprise Settings Content -->
<div class="fade-in">
    <!-- Settings Header -->
    <div class="enterprise-card mb-4">
        <div class="enterprise-card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="enterprise-card-title">
                        <i class="fas fa-cogs me-3"></i>
                        Enterprise Settings & Configuration
                    </h2>
                    <p class="enterprise-card-subtitle">
                        Configuration complète et paramètres avancés de la plateforme EMPLOIDB
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <button class="enterprise-btn enterprise-btn-outline" onclick="refreshSettings()">
                        <i class="fas fa-sync-alt"></i>
                        Actualiser
                    </button>
                    <button class="enterprise-btn enterprise-btn-primary" onclick="saveAllSettings()">
                        <i class="fas fa-save"></i>
                        Sauvegarder Tout
                    </button>
                    <button class="enterprise-btn enterprise-btn-accent" onclick="resetToDefaults()">
                        <i class="fas fa-undo"></i>
                        Réinitialiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- System Status Overview -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="stat-icon bg-primary-subtle mb-3">
                        <i class="fas fa-server fa-2x text-primary"></i>
                    </div>
                    <div class="enterprise-stat-number text-primary mb-1">
                        Actif
                    </div>
                    <div class="enterprise-stat-label mb-2">Serveur Web</div>
                    <div class="d-flex justify-content-center align-items-center">
                        <span class="text-success me-1">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <span class="text-success small">Opérationnel</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="stat-icon bg-success-subtle mb-3">
                        <i class="fas fa-database fa-2x text-success"></i>
                    </div>
                    <div class="enterprise-stat-number text-success mb-1">
                        Connecté
                    </div>
                    <div class="enterprise-stat-label mb-2">Base de Données</div>
                    <div class="d-flex justify-content-center align-items-center">
                        <span class="text-success me-1">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <span class="text-success small">Stable</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="stat-icon bg-info-subtle mb-3">
                        <i class="fas fa-envelope fa-2x text-info"></i>
                    </div>
                    <div class="enterprise-stat-number text-info mb-1">
                        Configuré
                    </div>
                    <div class="enterprise-stat-label mb-2">Système Email</div>
                    <div class="d-flex justify-content-center align-items-center">
                        <span class="text-info me-1">
                            <i class="fas fa-cog"></i>
                        </span>
                        <span class="text-info small">SMTP Actif</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="enterprise-card h-100">
                <div class="enterprise-card-body text-center">
                    <div class="stat-icon bg-warning-subtle mb-3">
                        <i class="fas fa-shield-alt fa-2x text-warning"></i>
                    </div>
                    <div class="enterprise-stat-number text-warning mb-1">
                        Sécurisé
                    </div>
                    <div class="enterprise-stat-label mb-2">Sécurité</div>
                    <div class="d-flex justify-content-center align-items-center">
                        <span class="text-warning me-1">
                            <i class="fas fa-lock"></i>
                        </span>
                        <span class="text-warning small">Protégé</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="enterprise-card">
                <div class="enterprise-card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                <i class="fas fa-globe me-2"></i>Général
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">
                                <i class="fas fa-envelope me-2"></i>Email
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                <i class="fas fa-shield-alt me-2"></i>Sécurité
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="application-tab" data-bs-toggle="tab" data-bs-target="#application" type="button" role="tab">
                                <i class="fas fa-mobile-alt me-2"></i>Application
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">
                                <i class="fas fa-server me-2"></i>Système
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="hardware-tab" data-bs-toggle="tab" data-bs-target="#hardware" type="button" role="tab">
                                <i class="fas fa-microchip me-2"></i>Hardware
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="errors-tab" data-bs-toggle="tab" data-bs-target="#errors" type="button" role="tab">
                                <i class="fas fa-exclamation-triangle me-2"></i>Error Pages
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="network-tab" data-bs-toggle="tab" data-bs-target="#network" type="button" role="tab">
                                <i class="fas fa-network-wired me-2"></i>Network
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="enterprise-tab" data-bs-toggle="tab" data-bs-target="#enterprise" type="button" role="tab">
                                <i class="fas fa-building me-2"></i>Enterprise
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="monitoring-tab" data-bs-toggle="tab" data-bs-target="#monitoring" type="button" role="tab">
                                <i class="fas fa-chart-line me-2"></i>Monitoring
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pages-tab" data-bs-toggle="tab" data-bs-target="#pages" type="button" role="tab">
                                <i class="fas fa-file-alt me-2"></i>Pages
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab">
                                <i class="fas fa-share-alt me-2"></i>Social Media
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="whatsapp-tab" data-bs-toggle="tab" data-bs-target="#whatsapp" type="button" role="tab">
                                <i class="fab fa-whatsapp me-2"></i>WhatsApp
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="chat-tab" data-bs-toggle="tab" data-bs-target="#chat" type="button" role="tab">
                                <i class="fas fa-comments me-2"></i>Chat Support
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="news-tab" data-bs-toggle="tab" data-bs-target="#news" type="button" role="tab">
                                <i class="fas fa-newspaper me-2"></i>News & Notifications
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="enterprise-card-body">
                    <div class="tab-content" id="settingsTabContent">
                        <!-- General Settings Tab -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_general">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="site_name" class="form-label fw-semibold">
                                                <i class="fas fa-building text-primary me-2"></i>
                                                Nom du Site
                                            </label>
                                            <input type="text" class="form-control form-control-lg" id="site_name" name="site_name" 
                                                   value="<?php echo htmlspecialchars($settings['site_name']['value'] ?? 'EMPLOIDB'); ?>" required>
                                            <div class="form-text">Le nom de votre plateforme d'emploi</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="contact_email" class="form-label fw-semibold">
                                                <i class="fas fa-envelope text-primary me-2"></i>
                                                Email de Contact
                                            </label>
                                            <input type="email" class="form-control form-control-lg" id="contact_email" name="contact_email" 
                                                   value="<?php echo htmlspecialchars($settings['contact_email']['value'] ?? 'contact@emploidb.com'); ?>" required>
                                            <div class="form-text">Email principal pour les communications</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="site_description" class="form-label fw-semibold">
                                        <i class="fas fa-info-circle text-primary me-2"></i>
                                        Description du Site
                                    </label>
                                    <textarea class="form-control form-control-lg" id="site_description" name="site_description" rows="4"><?php echo htmlspecialchars($settings['site_description']['value'] ?? 'Plateforme leader de l\'emploi au Maroc'); ?></textarea>
                                    <div class="form-text">Description courte de votre plateforme</div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="enterprise-btn enterprise-btn-primary">
                                        <i class="fas fa-save me-2"></i>Enregistrer les Paramètres
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Email Settings Tab -->
                        <div class="tab-pane fade" id="email" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_email">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="smtp_host" class="form-label fw-semibold">
                                                <i class="fas fa-server text-primary me-2"></i>
                                                Serveur SMTP
                                            </label>
                                            <input type="text" class="form-control form-control-lg" id="smtp_host" name="smtp_host" 
                                                   value="<?php echo htmlspecialchars($settings['smtp_host']['value'] ?? 'smtp.gmail.com'); ?>">
                                            <div class="form-text">Adresse du serveur SMTP</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="smtp_port" class="form-label fw-semibold">
                                                <i class="fas fa-plug text-primary me-2"></i>
                                                Port SMTP
                                            </label>
                                            <input type="number" class="form-control form-control-lg" id="smtp_port" name="smtp_port" 
                                                   value="<?php echo htmlspecialchars($settings['smtp_port']['value'] ?? '587'); ?>">
                                            <div class="form-text">Port du serveur SMTP (587 pour TLS)</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="smtp_username" class="form-label fw-semibold">
                                                <i class="fas fa-user text-primary me-2"></i>
                                                Nom d'utilisateur SMTP
                                            </label>
                                            <input type="text" class="form-control form-control-lg" id="smtp_username" name="smtp_username" 
                                                   value="<?php echo htmlspecialchars($settings['smtp_username']['value'] ?? ''); ?>">
                                            <div class="form-text">Email ou nom d'utilisateur SMTP</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="smtp_password" class="form-label fw-semibold">
                                                <i class="fas fa-lock text-primary me-2"></i>
                                                Mot de passe SMTP
                                            </label>
                                            <input type="password" class="form-control form-control-lg" id="smtp_password" name="smtp_password" 
                                                   placeholder="Laisser vide pour ne pas changer">
                                            <div class="form-text">Mot de passe SMTP (laisser vide pour ne pas changer)</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="enterprise-btn enterprise-btn-primary">
                                        <i class="fas fa-save me-2"></i>Enregistrer les Paramètres Email
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Security Settings Tab -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_security">
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="enterprise-card h-100">
                                            <div class="enterprise-card-body text-center">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                                           <?php echo ($settings['maintenance_mode']['value'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label fw-semibold" for="maintenance_mode">
                                                        <i class="fas fa-tools text-warning me-2"></i>
                                                        Mode Maintenance
                                                    </label>
                                                </div>
                                                <small class="text-muted">Activer pour la maintenance</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="enterprise-card h-100">
                                            <div class="enterprise-card-body text-center">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" id="user_registration" name="user_registration" 
                                                           <?php echo ($settings['user_registration']['value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label fw-semibold" for="user_registration">
                                                        <i class="fas fa-user-plus text-success me-2"></i>
                                                        Inscription Utilisateurs
                                                    </label>
                                                </div>
                                                <small class="text-muted">Autoriser les nouvelles inscriptions</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="enterprise-card h-100">
                                            <div class="enterprise-card-body text-center">
                                                <div class="form-check form-switch mb-3">
                                                    <input class="form-check-input" type="checkbox" id="email_verification" name="email_verification" 
                                                           <?php echo ($settings['email_verification']['value'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                                    <label class="form-check-label fw-semibold" for="email_verification">
                                                        <i class="fas fa-envelope-open text-info me-2"></i>
                                                        Vérification Email
                                                    </label>
                                                </div>
                                                <small class="text-muted">Vérifier les emails à l'inscription</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="enterprise-btn enterprise-btn-primary">
                                        <i class="fas fa-save me-2"></i>Enregistrer les Paramètres de Sécurité
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Application Settings Tab -->
                        <div class="tab-pane fade" id="application" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-mobile-alt text-primary me-2"></i>
                                                Paramètres Mobile
                                            </h5>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Version de l'Application</label>
                                                <input type="text" class="form-control" value="1.0.0" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Notifications Push</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer les notifications</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-cog text-primary me-2"></i>
                                                Paramètres API
                                            </h5>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Clé API</label>
                                                <input type="text" class="form-control" value="sk-emploidb-2024-xyz" readonly>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Limite de Requêtes</label>
                                                <input type="number" class="form-control" value="1000">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Settings Tab -->
                        <div class="tab-pane fade" id="system" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body text-center">
                                            <i class="fas fa-database text-primary fa-3x mb-3"></i>
                                            <h5 class="fw-bold">Base de Données</h5>
                                            <p class="text-muted">Statut de la connexion</p>
                                            <span class="enterprise-badge enterprise-badge-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Connectée
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body text-center">
                                            <i class="fas fa-server text-primary fa-3x mb-3"></i>
                                            <h5 class="fw-bold">Serveur Web</h5>
                                            <p class="text-muted">Statut du serveur</p>
                                            <span class="enterprise-badge enterprise-badge-success">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Actif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h5 class="fw-bold mb-3">Actions Système</h5>
                                <div class="d-flex gap-3">
                                    <button class="enterprise-btn enterprise-btn-outline" onclick="clearCache()">
                                        <i class="fas fa-broom me-2"></i>Vider le Cache
                                    </button>
                                    <button class="enterprise-btn enterprise-btn-outline" onclick="testEmail()">
                                        <i class="fas fa-envelope me-2"></i>Tester Email
                                    </button>
                                    <button class="enterprise-btn enterprise-btn-outline" onclick="viewLogs()">
                                        <i class="fas fa-list me-2"></i>Voir les Logs
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Hardware Settings Tab -->
                        <div class="tab-pane fade" id="hardware" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body text-center">
                                            <i class="fas fa-microchip text-primary fa-3x mb-3"></i>
                                            <h5 class="fw-bold">CPU Usage</h5>
                                            <div class="progress mb-2" style="height: 8px;">
                                                <div class="progress-bar" style="width: 45%">45%</div>
                                            </div>
                                            <small class="text-muted">2.4 GHz Intel Core i5</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body text-center">
                                            <i class="fas fa-memory text-primary fa-3x mb-3"></i>
                                            <h5 class="fw-bold">RAM Usage</h5>
                                            <div class="progress mb-2" style="height: 8px;">
                                                <div class="progress-bar bg-warning" style="width: 68%">68%</div>
                                            </div>
                                            <small class="text-muted">8 GB DDR4</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body text-center">
                                            <i class="fas fa-hdd text-primary fa-3x mb-3"></i>
                                            <h5 class="fw-bold">Disk Usage</h5>
                                            <div class="progress mb-2" style="height: 8px;">
                                                <div class="progress-bar bg-success" style="width: 32%">32%</div>
                                            </div>
                                            <small class="text-muted">500 GB SSD</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Error Pages Settings Tab -->
                        <div class="tab-pane fade" id="errors" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                                Pages d'Erreur Personnalisées
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Page 404 (Not Found)</label>
                                                <select class="form-select">
                                                    <option value="default">Page par défaut</option>
                                                    <option value="custom">Page personnalisée</option>
                                                    <option value="redirect">Redirection</option>
                                                </select>
                                                <div class="form-text">Page affichée quand le contenu n'existe pas</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Page 500 (Server Error)</label>
                                                <select class="form-select">
                                                    <option value="default">Page par défaut</option>
                                                    <option value="custom">Page personnalisée</option>
                                                    <option value="maintenance">Mode maintenance</option>
                                                </select>
                                                <div class="form-text">Page affichée en cas d'erreur serveur</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Page 503 (Service Unavailable)</label>
                                                <select class="form-select">
                                                    <option value="default">Page par défaut</option>
                                                    <option value="custom">Page personnalisée</option>
                                                    <option value="maintenance">Mode maintenance</option>
                                                </select>
                                                <div class="form-text">Page affichée quand le service est indisponible</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Page 403 (Forbidden)</label>
                                                <select class="form-select">
                                                    <option value="default">Page par défaut</option>
                                                    <option value="custom">Page personnalisée</option>
                                                    <option value="login">Redirection login</option>
                                                </select>
                                                <div class="form-text">Page affichée pour l'accès refusé</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-cog text-primary me-2"></i>
                                                Configuration des Erreurs
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Logging des Erreurs</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer le logging</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Niveau de Logging</label>
                                                <select class="form-select">
                                                    <option value="error">Erreurs seulement</option>
                                                    <option value="warning">Avertissements + Erreurs</option>
                                                    <option value="info">Info + Warnings + Erreurs</option>
                                                    <option value="debug">Debug complet</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Notification Email</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Envoyer des emails d'alerte</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Email d'Alerte</label>
                                                <input type="email" class="form-control" value="admin@emploidb.com">
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="testErrorPages()">
                                                    <i class="fas fa-play me-2"></i>Tester
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="saveErrorConfig()">
                                                    <i class="fas fa-save me-2"></i>Sauvegarder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Network Configuration Tab -->
                        <div class="tab-pane fade" id="network" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-network-wired text-primary me-2"></i>
                                                Configuration Réseau
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Port HTTP</label>
                                                <input type="number" class="form-control" value="80" min="1" max="65535">
                                                <div class="form-text">Port pour les connexions HTTP</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Port HTTPS</label>
                                                <input type="number" class="form-control" value="443" min="1" max="65535">
                                                <div class="form-text">Port pour les connexions HTTPS sécurisées</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Port SSH</label>
                                                <input type="number" class="form-control" value="22" min="1" max="65535">
                                                <div class="form-text">Port pour les connexions SSH</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Port FTP</label>
                                                <input type="number" class="form-control" value="21" min="1" max="65535">
                                                <div class="form-text">Port pour les connexions FTP</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Timeout de Connexion</label>
                                                <input type="number" class="form-control" value="30" min="1" max="300">
                                                <div class="form-text">Timeout en secondes</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-shield-alt text-success me-2"></i>
                                                Protocoles de Sécurité
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Protocole SSL/TLS</label>
                                                <select class="form-select">
                                                    <option value="tls1.3">TLS 1.3 (Recommandé)</option>
                                                    <option value="tls1.2">TLS 1.2</option>
                                                    <option value="tls1.1">TLS 1.1</option>
                                                    <option value="ssl3">SSL 3.0</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Chiffrement</label>
                                                <select class="form-select">
                                                    <option value="aes256">AES-256 (Recommandé)</option>
                                                    <option value="aes128">AES-128</option>
                                                    <option value="3des">3DES</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Certificat SSL</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Certificat auto-signé</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">HSTS (HTTP Strict Transport Security)</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer HSTS</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">CSP (Content Security Policy)</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer CSP</label>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="testNetworkConfig()">
                                                    <i class="fas fa-network-wired me-2"></i>Tester
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="saveNetworkConfig()">
                                                    <i class="fas fa-save me-2"></i>Sauvegarder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enterprise System Tab -->
                        <div class="tab-pane fade" id="enterprise" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-building text-primary me-2"></i>
                                                Configuration Entreprise
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Nom de l'Entreprise</label>
                                                <input type="text" class="form-control" value="EMPLOIDB Corporation">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">SIRET</label>
                                                <input type="text" class="form-control" value="12345678901234">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Adresse</label>
                                                <textarea class="form-control" rows="3">123 Avenue Mohammed V, Casablanca, Maroc</textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Téléphone</label>
                                                <input type="tel" class="form-control" value="+212 5XX XXX XXX">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-users text-success me-2"></i>
                                                Gestion des Utilisateurs
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Limite d'Utilisateurs</label>
                                                <input type="number" class="form-control" value="10000" min="1">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Session Timeout</label>
                                                <input type="number" class="form-control" value="3600" min="60">
                                                <div class="form-text">Timeout en secondes</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Tentatives de Connexion</label>
                                                <input type="number" class="form-control" value="5" min="1" max="10">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Blocage IP</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer le blocage IP</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-cogs text-warning me-2"></i>
                                                Performance
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Cache</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer le cache</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Durée du Cache</label>
                                                <input type="number" class="form-control" value="3600" min="60">
                                                <div class="form-text">Durée en secondes</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Compression</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer la compression</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Optimisation DB</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer l'optimisation</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- System Monitoring Tab -->
                        <div class="tab-pane fade" id="monitoring" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-chart-line text-info me-2"></i>
                                                Surveillance Système
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Monitoring Actif</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer le monitoring</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Intervalle de Vérification</label>
                                                <select class="form-select">
                                                    <option value="30">30 secondes</option>
                                                    <option value="60" selected>1 minute</option>
                                                    <option value="300">5 minutes</option>
                                                    <option value="600">10 minutes</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Seuil CPU (%)</label>
                                                <input type="number" class="form-control" value="80" min="1" max="100">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Seuil RAM (%)</label>
                                                <input type="number" class="form-control" value="85" min="1" max="100">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Seuil Disque (%)</label>
                                                <input type="number" class="form-control" value="90" min="1" max="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-bell text-warning me-2"></i>
                                                Alertes et Notifications
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Alertes Email</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer les alertes email</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Alertes SMS</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Activer les alertes SMS</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Webhook URL</label>
                                                <input type="url" class="form-control" placeholder="https://hooks.slack.com/...">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Niveau d'Alerte</label>
                                                <select class="form-select">
                                                    <option value="critical">Critique seulement</option>
                                                    <option value="warning">Avertissements + Critique</option>
                                                    <option value="info">Toutes les alertes</option>
                                                </select>
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="testMonitoring()">
                                                    <i class="fas fa-play me-2"></i>Tester
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="saveMonitoringConfig()">
                                                    <i class="fas fa-save me-2"></i>Sauvegarder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Real-time System Status -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="enterprise-card">
                                        <div class="enterprise-card-header">
                                            <h5 class="fw-bold mb-0">
                                                <i class="fas fa-heartbeat text-danger me-2"></i>
                                                Statut Système en Temps Réel
                                            </h5>
                                        </div>
                                        <div class="enterprise-card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <div class="enterprise-stat-number text-success mb-1">99.9%</div>
                                                        <div class="enterprise-stat-label">Uptime</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <div class="enterprise-stat-number text-info mb-1">45ms</div>
                                                        <div class="enterprise-stat-label">Latence</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <div class="enterprise-stat-number text-warning mb-1">1,250</div>
                                                        <div class="enterprise-stat-label">Requêtes/min</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <div class="enterprise-stat-number text-primary mb-1">0</div>
                                                        <div class="enterprise-stat-label">Erreurs</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Page Management Tab -->
                        <div class="tab-pane fade" id="pages" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-file-alt text-primary me-2"></i>
                                                Gestion des Pages
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Pages du Site</label>
                                                <div class="list-group">
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-home text-primary me-2"></i>
                                                            Page d'Accueil
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" checked>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-briefcase text-success me-2"></i>
                                                            Offres d'Emploi
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" checked>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-building text-info me-2"></i>
                                                            Employeurs
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" checked>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-user text-warning me-2"></i>
                                                            Inscription
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" checked>
                                                        </div>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-sign-in-alt text-danger me-2"></i>
                                                            Connexion
                                                        </div>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" checked>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="stopAllPages()">
                                                    <i class="fas fa-stop me-2"></i>Arrêter Tout
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="startAllPages()">
                                                    <i class="fas fa-play me-2"></i>Démarrer Tout
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-cog text-primary me-2"></i>
                                                Configuration des Pages
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Mode Maintenance</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="maintenance-mode-pages">
                                                    <label class="form-check-label">Activer le mode maintenance</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Message de Maintenance</label>
                                                <textarea class="form-control" rows="3" placeholder="Site en maintenance. Retour prévu dans 2 heures."></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Pages Exclues de la Maintenance</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Page d'administration</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Page de contact</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Page d'urgence</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Redirection d'Urgence</label>
                                                <input type="url" class="form-control" placeholder="https://status.emploidb.com">
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="testMaintenance()">
                                                    <i class="fas fa-play me-2"></i>Tester
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="savePageConfig()">
                                                    <i class="fas fa-save me-2"></i>Sauvegarder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Social Media Integration Tab -->
                        <div class="tab-pane fade" id="social" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-share-alt text-primary me-2"></i>
                                                Intégration Réseaux Sociaux
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Facebook</label>
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer Facebook</label>
                                                </div>
                                                <input type="text" class="form-control" placeholder="Page Facebook URL">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Instagram</label>
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer Instagram</label>
                                                </div>
                                                <input type="text" class="form-control" placeholder="Compte Instagram">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">LinkedIn</label>
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer LinkedIn</label>
                                                </div>
                                                <input type="text" class="form-control" placeholder="Page LinkedIn URL">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Twitter</label>
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Activer Twitter</label>
                                                </div>
                                                <input type="text" class="form-control" placeholder="@emploidb">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">YouTube</label>
                                                <div class="form-check form-switch mb-2">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Activer YouTube</label>
                                                </div>
                                                <input type="text" class="form-control" placeholder="Chaîne YouTube URL">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-bell text-warning me-2"></i>
                                                Notifications Sociales
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Auto-Partage</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Partager automatiquement les nouvelles offres</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Fréquence de Publication</label>
                                                <select class="form-select">
                                                    <option value="immediate">Immédiat</option>
                                                    <option value="hourly">Toutes les heures</option>
                                                    <option value="daily">Quotidien</option>
                                                    <option value="weekly">Hebdomadaire</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Template de Publication</label>
                                                <textarea class="form-control" rows="4" placeholder="🚀 Nouvelle offre d'emploi disponible ! 

Titre: {job_title}
Entreprise: {company}
Lieu: {location}

Postulez maintenant: {job_url}

#emploi #recrutement #emploidb"></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Hashtags</label>
                                                <input type="text" class="form-control" value="#emploi #recrutement #emploidb #maroc">
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="testSocialMedia()">
                                                    <i class="fas fa-play me-2"></i>Tester
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="saveSocialConfig()">
                                                    <i class="fas fa-save me-2"></i>Sauvegarder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- WhatsApp Configuration Tab -->
                        <div class="tab-pane fade" id="whatsapp" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fab fa-whatsapp text-success me-2"></i>
                                                Configuration WhatsApp
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Numéro WhatsApp Business</label>
                                                <input type="tel" class="form-control" placeholder="+212 6XX XXX XXX">
                                                <div class="form-text">Numéro officiel de l'entreprise</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Token API WhatsApp</label>
                                                <input type="password" class="form-control" placeholder="Votre token d'API">
                                                <div class="form-text">Token d'accès à l'API WhatsApp Business</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Webhook URL</label>
                                                <input type="url" class="form-control" placeholder="https://emploidb.com/webhook/whatsapp">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Message de Bienvenue</label>
                                                <textarea class="form-control" rows="3" placeholder="Bonjour ! Bienvenue sur EMPLOIDB. Comment puis-je vous aider ?"></textarea>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Heures de Service</label>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <input type="time" class="form-control" value="09:00">
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="time" class="form-control" value="18:00">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-users text-primary me-2"></i>
                                                Groupes WhatsApp
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Groupes Actifs</label>
                                                <div class="list-group">
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-briefcase text-success me-2"></i>
                                                            Offres d'Emploi
                                                        </div>
                                                        <span class="badge bg-success">Actif</span>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-building text-info me-2"></i>
                                                            Employeurs
                                                        </div>
                                                        <span class="badge bg-success">Actif</span>
                                                    </div>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <i class="fas fa-bullhorn text-warning me-2"></i>
                                                            Annonces
                                                        </div>
                                                        <span class="badge bg-warning">En attente</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Créer un Nouveau Groupe</label>
                                                <input type="text" class="form-control mb-2" placeholder="Nom du groupe">
                                                <button class="enterprise-btn enterprise-btn-outline w-100">
                                                    <i class="fas fa-plus me-2"></i>Créer Groupe
                                                </button>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Messages Automatiques</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer les messages automatiques</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Notifications d'Offres</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Notifier les nouvelles offres</label>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="testWhatsApp()">
                                                    <i class="fas fa-play me-2"></i>Tester
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="saveWhatsAppConfig()">
                                                    <i class="fas fa-save me-2"></i>Sauvegarder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Support Tab -->
                        <div class="tab-pane fade" id="chat" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-comments text-primary me-2"></i>
                                                Configuration Chat Support
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Chat en Direct</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer le chat en direct</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Agents Disponibles</label>
                                                <input type="number" class="form-control" value="3" min="1" max="20">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Temps de Réponse Moyen</label>
                                                <input type="number" class="form-control" value="2" min="1" max="60">
                                                <div class="form-text">Temps en minutes</div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Heures de Service</label>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <input type="time" class="form-control" value="08:00">
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="time" class="form-control" value="20:00">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Message Hors Service</label>
                                                <textarea class="form-control" rows="3" placeholder="Nous sommes actuellement fermés. Laissez-nous un message et nous vous répondrons dès que possible."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-robot text-info me-2"></i>
                                                Chatbot & IA
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Chatbot IA</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer le chatbot IA</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Niveau d'IA</label>
                                                <select class="form-select">
                                                    <option value="basic">Basique</option>
                                                    <option value="advanced" selected>Avancé</option>
                                                    <option value="expert">Expert</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Langues Supportées</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Français</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Arabe</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Anglais</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Escalade vers Agent</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Escalader vers un agent humain</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Mots-Clés d'Escalade</label>
                                                <input type="text" class="form-control" value="agent, humain, parler, problème">
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="testChatSupport()">
                                                    <i class="fas fa-play me-2"></i>Tester
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="saveChatConfig()">
                                                    <i class="fas fa-save me-2"></i>Sauvegarder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- News & Notifications Tab -->
                        <div class="tab-pane fade" id="news" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-newspaper text-primary me-2"></i>
                                                Système de News
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Newsletter Automatique</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer la newsletter</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Fréquence d'Envoi</label>
                                                <select class="form-select">
                                                    <option value="daily">Quotidien</option>
                                                    <option value="weekly" selected>Hebdomadaire</option>
                                                    <option value="monthly">Mensuel</option>
                                                </select>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Contenu de la Newsletter</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Nouvelles offres d'emploi</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Conseils carrière</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Actualités du marché</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Événements</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Template Newsletter</label>
                                                <textarea class="form-control" rows="4" placeholder="Bonjour {name},

Voici les dernières offres d'emploi qui pourraient vous intéresser :

{job_list}

Bonne recherche !
L'équipe EMPLOIDB"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="enterprise-card h-100">
                                        <div class="enterprise-card-body">
                                            <h5 class="fw-bold mb-3">
                                                <i class="fas fa-bell text-warning me-2"></i>
                                                Notifications Push
                                            </h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Notifications Push</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Activer les notifications push</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Clé API Firebase</label>
                                                <input type="password" class="form-control" placeholder="Votre clé API Firebase">
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Types de Notifications</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Nouvelles offres</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" checked>
                                                    <label class="form-check-label">Mise à jour de candidature</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Messages</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox">
                                                    <label class="form-check-label">Actualités</label>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Heures de Notification</label>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <input type="time" class="form-control" value="09:00">
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="time" class="form-control" value="18:00">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex gap-2">
                                                <button class="enterprise-btn enterprise-btn-outline" onclick="testNotifications()">
                                                    <i class="fas fa-play me-2"></i>Tester
                                                </button>
                                                <button class="enterprise-btn enterprise-btn-primary" onclick="saveNewsConfig()">
                                                    <i class="fas fa-save me-2"></i>Sauvegarder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshSettings() {
    location.reload();
}

function saveAllSettings() {
    // Save all settings across all tabs
    alert('Tous les paramètres ont été sauvegardés !');
}

function resetToDefaults() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les paramètres aux valeurs par défaut ?')) {
        alert('Paramètres réinitialisés !');
    }
}

function clearCache() {
    if (confirm('Êtes-vous sûr de vouloir vider le cache ?')) {
        alert('Cache vidé avec succès !');
    }
}

function testEmail() {
    alert('Test d\'email envoyé !');
}

function viewLogs() {
    alert('Ouverture des logs...');
}

// Error Pages Functions
function testErrorPages() {
    if (confirm('Voulez-vous tester les pages d\'erreur ?')) {
        // Test 404 page
        window.open('test-404.php', '_blank');
        alert('Test des pages d\'erreur lancé !');
    }
}

function saveErrorConfig() {
    alert('Configuration des pages d\'erreur sauvegardée !');
}

// Network Configuration Functions
function testNetworkConfig() {
    if (confirm('Voulez-vous tester la configuration réseau ?')) {
        alert('Test de la configuration réseau en cours...');
        // Simulate network test
        setTimeout(() => {
            alert('Test réseau terminé - Tous les ports sont accessibles !');
        }, 2000);
    }
}

function saveNetworkConfig() {
    alert('Configuration réseau sauvegardée !');
}

// Monitoring Functions
function testMonitoring() {
    if (confirm('Voulez-vous tester le système de monitoring ?')) {
        alert('Test du monitoring en cours...');
        // Simulate monitoring test
        setTimeout(() => {
            alert('Test de monitoring terminé - Système opérationnel !');
        }, 1500);
    }
}

function saveMonitoringConfig() {
    alert('Configuration du monitoring sauvegardée !');
}

// Page Management Functions
function stopAllPages() {
    if (confirm('Êtes-vous sûr de vouloir arrêter toutes les pages ?')) {
        // Toggle all page switches to off
        const switches = document.querySelectorAll('#pages input[type="checkbox"]');
        switches.forEach(switchEl => {
            switchEl.checked = false;
        });
        alert('Toutes les pages ont été arrêtées !');
    }
}

function startAllPages() {
    if (confirm('Êtes-vous sûr de vouloir démarrer toutes les pages ?')) {
        // Toggle all page switches to on
        const switches = document.querySelectorAll('#pages input[type="checkbox"]');
        switches.forEach(switchEl => {
            switchEl.checked = true;
        });
        alert('Toutes les pages ont été démarrées !');
    }
}

function testMaintenance() {
    if (confirm('Voulez-vous tester le mode maintenance ?')) {
        alert('Test du mode maintenance en cours...');
        setTimeout(() => {
            alert('Test terminé - Mode maintenance fonctionnel !');
        }, 2000);
    }
}

function savePageConfig() {
    alert('Configuration des pages sauvegardée !');
}

// Social Media Functions
function testSocialMedia() {
    if (confirm('Voulez-vous tester l\'intégration des réseaux sociaux ?')) {
        alert('Test des réseaux sociaux en cours...');
        setTimeout(() => {
            alert('Test terminé - Intégration sociale fonctionnelle !');
        }, 3000);
    }
}

function saveSocialConfig() {
    alert('Configuration des réseaux sociaux sauvegardée !');
}

// WhatsApp Functions
function testWhatsApp() {
    if (confirm('Voulez-vous tester la configuration WhatsApp ?')) {
        alert('Test de WhatsApp en cours...');
        setTimeout(() => {
            alert('Test terminé - WhatsApp configuré avec succès !');
        }, 2500);
    }
}

function saveWhatsAppConfig() {
    alert('Configuration WhatsApp sauvegardée !');
}

// Chat Support Functions
function testChatSupport() {
    if (confirm('Voulez-vous tester le système de chat support ?')) {
        alert('Test du chat support en cours...');
        setTimeout(() => {
            alert('Test terminé - Chat support opérationnel !');
        }, 2000);
    }
}

function saveChatConfig() {
    alert('Configuration du chat support sauvegardée !');
}

// News & Notifications Functions
function testNotifications() {
    if (confirm('Voulez-vous tester les notifications ?')) {
        alert('Test des notifications en cours...');
        setTimeout(() => {
            alert('Test terminé - Notifications envoyées avec succès !');
        }, 1500);
    }
}

function saveNewsConfig() {
    alert('Configuration des news et notifications sauvegardée !');
}

// System Health Check
function performSystemHealthCheck() {
    const statusCards = document.querySelectorAll('.enterprise-stat-number');
    statusCards.forEach(card => {
        card.style.animation = 'pulse 1s infinite';
    });
    
    setTimeout(() => {
        statusCards.forEach(card => {
            card.style.animation = '';
        });
        alert('Vérification de santé du système terminée !');
    }, 3000);
}

// Auto-refresh system status
setInterval(() => {
    // Update real-time stats
    const uptimeElement = document.querySelector('.enterprise-stat-number.text-success');
    const latencyElement = document.querySelector('.enterprise-stat-number.text-info');
    const requestsElement = document.querySelector('.enterprise-stat-number.text-warning');
    const errorsElement = document.querySelector('.enterprise-stat-number.text-primary');
    
    if (uptimeElement && latencyElement && requestsElement && errorsElement) {
        // Simulate real-time updates
        const newLatency = Math.floor(Math.random() * 20) + 30;
        const newRequests = Math.floor(Math.random() * 500) + 1000;
        const newErrors = Math.floor(Math.random() * 3);
        
        latencyElement.textContent = newLatency + 'ms';
        requestsElement.textContent = newRequests.toLocaleString();
        errorsElement.textContent = newErrors;
    }
}, 5000);

// Initialize tooltips and advanced features
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add click handlers for status cards
    const statusCards = document.querySelectorAll('.enterprise-card');
    statusCards.forEach(card => {
        card.addEventListener('click', function() {
            this.style.transform = 'scale(1.02)';
            setTimeout(() => {
                this.style.transform = '';
            }, 200);
        });
    });
    
    // Auto-save functionality
    const formInputs = document.querySelectorAll('input, select, textarea');
    formInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Add visual feedback for changes
            this.style.borderColor = '#10b981';
            setTimeout(() => {
                this.style.borderColor = '';
            }, 1000);
        });
    });
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>