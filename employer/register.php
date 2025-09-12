<?php 
// Include configuration first (before any session starts)
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';
include __DIR__ . '/../include/connexion.php';

// Check if user is already logged in as employer
if (Security::isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'employer') {
    header('Location: dashboard.php');
    exit();
}

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            throw new Exception('Token de sécurité invalide');
        }
        
        // Get form data
        $company_name = Security::sanitizeInput($_POST['company_name'] ?? '');
        $email = Security::sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $phone = Security::sanitizeInput($_POST['phone'] ?? '');
        $website = Security::sanitizeInput($_POST['website'] ?? '');
        $industry = Security::sanitizeInput($_POST['industry'] ?? '');
        $company_size = Security::sanitizeInput($_POST['company_size'] ?? '');
        $founded_year = (int)($_POST['founded_year'] ?? 0);
        $company_description = Security::sanitizeInput($_POST['company_description'] ?? '');
        
        // Validation
        if (empty($company_name)) {
            throw new Exception('Le nom de l\'entreprise est requis');
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Une adresse email valide est requise');
        }
        
        if (strlen($password) < 8) {
            throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
        }
        
        if ($password !== $confirm_password) {
            throw new Exception('Les mots de passe ne correspondent pas');
        }
        
        // Check if email already exists
        $existing_user = $db->fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing_user) {
            throw new Exception('Cette adresse email est déjà utilisée');
        }
        
        // Check if company name already exists
        $existing_company = $db->fetch("SELECT id FROM employers WHERE company_name = ?", [$company_name]);
        if ($existing_company) {
            throw new Exception('Une entreprise avec ce nom existe déjà');
        }
        
        // Start transaction
        $db->query("START TRANSACTION");
        
        // Create user account
        $hashed_password = Security::hashPassword($password);
        $user_id = $db->insert("INSERT INTO users (email, pass, role, email_verified, created_at) VALUES (?, ?, 'employer', FALSE, NOW())", 
                              [$email, $hashed_password]);
        
        // Create employer profile
        $employer_id = $db->insert("INSERT INTO employers (
            user_id, company_name, company_description, company_website, industry, company_size, founded_year,
            verified, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, FALSE, NOW())", [
            $user_id, $company_name, $company_description, $website, $industry, $company_size, $founded_year
        ]);
        
        // Create profile entry for employer
        $db->insert("INSERT INTO profiles (user_id, nom, prenom, telephone, adresse, ville_id, bio) VALUES (?, ?, ?, ?, ?, ?, ?)", [
            $user_id, $company_name, '', $phone, '', 5, $company_description
        ]);
        
        // Handle company logo upload
        if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
            $upload_result = Security::validateFileUpload($_FILES['company_logo'], ['jpg', 'jpeg', 'png'], 5 * 1024 * 1024);
            if ($upload_result['valid']) {
                $filename = Security::generateSecureFilename($_FILES['company_logo']['name'], 'logo_');
                $upload_path = '../upload/' . $filename;
                
                if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $upload_path)) {
                    $db->query("UPDATE employers SET company_logo = ? WHERE id = ?", [$filename, $employer_id]);
                }
            }
        }
        
        // Commit transaction
        $db->query("COMMIT");
        
        $success = true;
        $success_message = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
        
    } catch (Exception $e) {
        // Rollback transaction
        $db->query("ROLLBACK");
        $errors[] = $e->getMessage();
        error_log("Employer registration failed: " . $e->getMessage());
    }
}

// Generate CSRF token
$csrf_token = Security::generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription Employeur - JobMaroc</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Content Visibility Fixes */
        .employer-content {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }
        
        .container-fluid {
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        .card, .form-group, .table-responsive, .alert {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }
        
        .section-spacing {
            margin-bottom: 2rem;
        }
        
        .component-spacing {
            margin-bottom: 1.5rem;
        }
        
        .element-spacing {
            margin-bottom: 1rem;
        }
        
        /* Ensure all content is visible */
        * {
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        
        /* EMPLOIDB Enterprise Design System - Inherited from Admin Panel */
        :root {
            /* Enterprise Color Palette - Inherited from Admin Panel */
            --enterprise-primary: #1e40af;
            --enterprise-primary-dark: #1e3a8a;
            --enterprise-primary-light: #3b82f6;
            --enterprise-secondary: #059669;
            --enterprise-secondary-dark: #047857;
            --enterprise-accent: #f59e0b;
            --enterprise-accent-dark: #d97706;
            
            /* Status Colors */
            --enterprise-success: #10b981;
            --enterprise-warning: #f59e0b;
            --enterprise-danger: #ef4444;
            --enterprise-info: #06b6d4;
            --enterprise-purple: #8b5cf6;
            --enterprise-pink: #ec4899;
            
            /* Neutral Colors */
            --enterprise-dark: #0f172a;
            --enterprise-dark-light: #1e293b;
            --enterprise-gray-50: #f8fafc;
            --enterprise-gray-100: #f1f5f9;
            --enterprise-gray-200: #e2e8f0;
            --enterprise-gray-300: #cbd5e1;
            --enterprise-gray-400: #94a3b8;
            --enterprise-gray-500: #64748b;
            --enterprise-gray-600: #475569;
            --enterprise-gray-700: #334155;
            --enterprise-gray-800: #1e293b;
            --enterprise-gray-900: #0f172a;
            
            /* Shadows & Effects */
            --enterprise-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --enterprise-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --enterprise-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --enterprise-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --enterprise-shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --enterprise-shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            
            /* Gradients */
            --enterprise-gradient-primary: linear-gradient(135deg, var(--enterprise-primary) 0%, var(--enterprise-primary-dark) 100%);
            --enterprise-gradient-secondary: linear-gradient(135deg, var(--enterprise-secondary) 0%, var(--enterprise-secondary-dark) 100%);
            --enterprise-gradient-accent: linear-gradient(135deg, var(--enterprise-accent) 0%, var(--enterprise-accent-dark) 100%);
            --enterprise-gradient-dark: linear-gradient(135deg, var(--enterprise-dark) 0%, var(--enterprise-dark-light) 100%);
            --enterprise-gradient-glass: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            
            /* Typography */
            --enterprise-font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --enterprise-font-size-xs: 0.75rem;
            --enterprise-font-size-sm: 0.875rem;
            --enterprise-font-size-base: 1rem;
            --enterprise-font-size-lg: 1.125rem;
            --enterprise-font-size-xl: 1.25rem;
            --enterprise-font-size-2xl: 1.5rem;
            --enterprise-font-size-3xl: 1.875rem;
            --enterprise-font-size-4xl: 2.25rem;
            
            /* Spacing */
            --enterprise-spacing-1: 0.25rem;
            --enterprise-spacing-2: 0.5rem;
            --enterprise-spacing-3: 0.75rem;
            --enterprise-spacing-4: 1rem;
            --enterprise-spacing-5: 1.25rem;
            --enterprise-spacing-6: 1.5rem;
            --enterprise-spacing-8: 2rem;
            --enterprise-spacing-10: 2.5rem;
            --enterprise-spacing-12: 3rem;
            --enterprise-spacing-16: 4rem;
            --enterprise-spacing-20: 5rem;
            
            /* Border Radius */
            --enterprise-radius-sm: 0.25rem;
            --enterprise-radius: 0.375rem;
            --enterprise-radius-md: 0.5rem;
            --enterprise-radius-lg: 0.75rem;
            --enterprise-radius-xl: 1rem;
            --enterprise-radius-2xl: 1.5rem;
            --enterprise-radius-full: 9999px;
            
            /* Transitions */
            --enterprise-transition: all 0.15s ease-in-out;
            --enterprise-transition-fast: all 0.1s ease-in-out;
            --enterprise-transition-slow: all 0.3s ease-in-out;
        }

        /* EMPLOIDB Employer Panel Professional Styles */
        body {
            background: var(--emploidb-bg-secondary);
            font-family: var(--emploidb-font-primary);
            color: var(--emploidb-text-primary);
        }
        
        /* Employer Layout */
        .employer-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .employer-sidebar {
            width: 280px;
            background: var(--emploidb-gradient-secondary);
            color: var(--emploidb-text-inverse);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: var(--emploidb-transition-all);
        }
        
        .employer-sidebar.collapsed {
            width: 80px;
        }
        
        .employer-content {
            flex: 1;
            margin-left: 320px;
            padding: var(--emploidb-spacing-8);
            transition: var(--emploidb-transition-all);
            background: var(--emploidb-bg-secondary);
            min-height: 100vh;
        }
        
        .employer-content.expanded {
            margin-left: 80px;
        }
        
        /* Professional Header */
        .employer-header {
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            padding: var(--emploidb-spacing-6);
            border-radius: var(--emploidb-radius-xl);
            box-shadow: var(--emploidb-shadow-lg);
            margin-bottom: var(--emploidb-spacing-6);
            border: 1px solid var(--emploidb-neutral-200);
            position: relative;
            overflow: hidden;
        }
        
        .employer-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2563eb, #059669, #d97706, #0891b2);
        }
        
        .company-brand {
            font-family: var(--emploidb-font-display);
            font-size: var(--emploidb-text-2xl);
            font-weight: var(--emploidb-font-weight-black);
            background: var(--emploidb-gradient-secondary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }
        
        /* Navigation Styles */
        .employer-nav-link {
            display: flex;
            align-items: center;
            padding: var(--emploidb-spacing-4) var(--emploidb-spacing-6);
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--emploidb-transition-all);
            border-radius: var(--emploidb-radius-lg);
            margin: var(--emploidb-spacing-1) var(--emploidb-spacing-4);
        }
        
        .employer-nav-link:hover,
        .employer-nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: var(--emploidb-text-inverse);
            transform: translateX(5px);
        }
        
        .employer-nav-link i {
            margin-right: var(--emploidb-spacing-3);
            font-size: var(--emploidb-text-lg);
        }
        
        /* Dashboard Cards */
        .employer-stat-card {
            background: var(--emploidb-bg-primary);
            border: 1px solid var(--emploidb-neutral-200);
            border-radius: var(--emploidb-radius-xl);
            padding: var(--emploidb-spacing-6);
            box-shadow: var(--emploidb-shadow-sm);
            transition: var(--emploidb-transition-all);
            position: relative;
            overflow: hidden;
        }
        
        .employer-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--emploidb-shadow-lg);
        }
        
        .employer-stat-card.primary {
            border-left: 5px solid var(--emploidb-primary);
        }
        
        .employer-stat-card.secondary {
            border-left: 5px solid var(--emploidb-secondary);
        }
        
        .employer-stat-card.success {
            border-left: 5px solid var(--emploidb-success);
        }
        
        .employer-stat-card.warning {
            border-left: 5px solid var(--emploidb-warning);
        }
        
        /* Company Logo */
        .company-logo {
            width: 60px;
            height: 60px;
            border-radius: var(--emploidb-radius-full);
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Job Card Styles */
        .job-card {
            background: var(--emploidb-bg-primary);
            border: 1px solid var(--emploidb-neutral-200);
            border-radius: var(--emploidb-radius-lg);
            padding: var(--emploidb-spacing-4);
            transition: var(--emploidb-transition-all);
        }
        
        .job-card:hover {
            box-shadow: var(--emploidb-shadow-md);
            border-color: var(--emploidb-primary-200);
        }
        
        /* Application Card */
        .application-card {
            background: var(--emploidb-bg-primary);
            border: 1px solid var(--emploidb-neutral-200);
            border-radius: var(--emploidb-radius-lg);
            padding: var(--emploidb-spacing-4);
            transition: var(--emploidb-transition-all);
            margin-bottom: var(--emploidb-spacing-3);
        }
        
        .application-card:hover {
            box-shadow: var(--emploidb-shadow-sm);
            transform: translateX(5px);
        }
        
        /* Status Badges */
        .status-badge {
            padding: var(--emploidb-spacing-1) var(--emploidb-spacing-3);
            border-radius: var(--emploidb-radius-full);
            font-size: var(--emploidb-text-xs);
            font-weight: var(--emploidb-font-weight-semibold);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-badge.active {
            background: var(--emploidb-success-100);
            color: var(--emploidb-success);
        }
        
        .status-badge.pending {
            background: var(--emploidb-warning-100);
            color: var(--emploidb-warning);
        }
        
        .status-badge.applied {
            background: var(--emploidb-info-100);
            color: var(--emploidb-info);
        }
        
        /* Responsive Design */
        @media (max-width: 991.98px) {
            .employer-sidebar {
                transform: translateX(-100%);
            }
            
            .employer-sidebar.show {
                transform: translateX(0);
            }
            
            .employer-content {
                margin-left: 0;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #334155;
            line-height: 1.6;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            color: white;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-brand i {
            font-size: 2rem;
            color: #fbbf24;
        }

        .sidebar-nav {
            padding: 1.5rem 0;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: #fbbf24;
        }

        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .user-details h6 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .user-details small {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.8rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .top-bar {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-color);
        }

        .top-bar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        /* Dashboard Content */
        .dashboard-content {
            padding: 2rem;
        }

        .welcome-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.2);
        }

        .welcome-section h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .welcome-section p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }

        /* Statistics Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-card.info {
            border-left: 4px solid var(--info-color);
        }

        .stat-card.success {
            border-left: 4px solid var(--success-color);
        }

        .stat-card.warning {
            border-left: 4px solid var(--warning-color);
        }

        .stat-card.danger {
            border-left: 4px solid var(--danger-color);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-icon.info {
            background: rgba(6, 182, 212, 0.1);
            color: var(--info-color);
        }

        .stat-icon.success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .stat-icon.warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }

        .stat-icon.danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--secondary-color);
            font-weight: 500;
        }

        /* Content Cards */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .content-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h5 {
            margin: 0;
            font-weight: 600;
            color: var(--dark-color);
        }

        .card-body {
            padding: 1.5rem;
        }

        .list-item {
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .list-item:last-child {
            border-bottom: none;
        }

        .list-item-content h6 {
            margin: 0 0 0.25rem 0;
            font-weight: 600;
        }

        .list-item-content p {
            margin: 0;
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge.applied { background: rgba(6, 182, 212, 0.1); color: var(--info-color); }
        .badge.viewed { background: rgba(245, 158, 11, 0.1); color: var(--warning-color); }
        .badge.shortlisted { background: rgba(100, 116, 139, 0.1); color: var(--secondary-color); }
        .badge.interviewed { background: rgba(30, 41, 59, 0.1); color: var(--dark-color); }
        .badge.hired { background: rgba(16, 185, 129, 0.1); color: var(--success-color); }
        .badge.rejected { background: rgba(239, 68, 68, 0.1); color: var(--danger-color); }

        /* Quick Actions */
        .quick-actions {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            padding: 1.5rem;
        }

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem;
            border-radius: 0.75rem;
            text-decoration: none;
            color: var(--dark-color);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            color: var(--dark-color);
        }

        .action-btn.primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1d4ed8 100%);
            color: white;
            border: none;
        }

        .action-btn.primary:hover {
            color: white;
        }

        .action-icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
        }

        .action-btn.primary .action-icon {
            color: white;
        }

        /* Professional Analytics Cards */
        .analytics-card {
            background: var(--emploidb-bg-primary);
            border-radius: var(--emploidb-radius-xl);
            padding: var(--emploidb-spacing-6);
            box-shadow: var(--emploidb-shadow-lg);
            border: 1px solid var(--emploidb-neutral-200);
            transition: var(--emploidb-transition-all);
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .analytics-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--emploidb-shadow-xl);
        }
        
        .analytics-card.primary-gradient {


            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
        }
        
        .analytics-card.success-gradient {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
        }
        
        .analytics-card.warning-gradient {
            background: linear-gradient(135deg, #d97706, #b45309);
            color: white;
        }
        
        .analytics-card.info-gradient {
            background: linear-gradient(135deg, #0891b2, #0e7490);
            color: white;
        }
        
        .analytics-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--emploidb-spacing-4);
        }
        
        .analytics-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--emploidb-radius-full);
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .analytics-trend {
            display: flex;
            align-items: center;
            gap: var(--emploidb-spacing-1);
            font-size: 0.875rem;
            font-weight: var(--emploidb-font-weight-semibold);
            background: rgba(255, 255, 255, 0.2);
            padding: var(--emploidb-spacing-2) var(--emploidb-spacing-3);
            border-radius: var(--emploidb-radius-full);
        }
        
        .analytics-trend.positive {
            color: #10b981;
        }
        
        .analytics-trend.negative {
            color: #ef4444;
        }
        
        .analytics-content {
            margin-bottom: var(--emploidb-spacing-4);
        }
        
        .analytics-number {
            font-size: 2.5rem;
            font-weight: var(--emploidb-font-weight-black);
            line-height: 1;
            margin-bottom: var(--emploidb-spacing-2);
        }
        
        .analytics-label {
            font-size: 1rem;
            font-weight: var(--emploidb-font-weight-medium);
            opacity: 0.9;
            margin-bottom: var(--emploidb-spacing-3);
        }
        
        .analytics-subtitle {
            margin-bottom: var(--emploidb-spacing-3);
        }
        
        .analytics-footer {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: var(--emploidb-spacing-4);
        }
        
        .analytics-link {
            color: inherit;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: var(--emploidb-font-weight-medium);
            display: flex;
            align-items: center;
            gap: var(--emploidb-spacing-2);
            transition: var(--emploidb-transition-all);
        }
        
        .analytics-link:hover {
            opacity: 0.8;
            transform: translateX(4px);
        }
        
        .badge {
            padding: 4px 12px;
            border-radius: var(--emploidb-radius-full);
            font-size: 0.75rem;
            font-weight: var(--emploidb-font-weight-semibold);
        }
        
        .bg-success-light {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }
        
        .bg-warning-light {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
        }
        
        .bg-info-light {
            background: rgba(6, 182, 212, 0.2);
            color: #06b6d4;
        }
        
        .bg-secondary-light {
            background: rgba(100, 116, 139, 0.2);
            color: #64748b;
        }

        /* Quick action buttons */
        .quick-action-btn {
            background: var(--emploidb-bg-secondary);
            border: 2px solid var(--emploidb-neutral-200);
            border-radius: var(--emploidb-radius-xl);
            padding: var(--emploidb-spacing-6);
            text-align: center;
            transition: var(--emploidb-transition-all);
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 140px;
        }
        
        .quick-action-btn:hover {
            border-color: var(--emploidb-primary);
            box-shadow: var(--emploidb-shadow-lg);
            transform: translateY(-2px);
            background: var(--emploidb-bg-primary);
        }
        
        /* Enhanced Quick Action Buttons - Professional Color Scheme */
        .quick-action-btn.primary-action {
            border-color: #2563eb;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(29, 78, 216, 0.05));
        }
        
        .quick-action-btn.primary-action:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
        }
        
        .quick-action-btn.success-action {
            border-color: #059669;
            background: linear-gradient(135deg, rgba(5, 150, 105, 0.1), rgba(4, 120, 87, 0.05));
        }
        
        .quick-action-btn.success-action:hover {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
        }
        
        .quick-action-btn.warning-action {
            border-color: #d97706;
            background: linear-gradient(135deg, rgba(217, 119, 6, 0.1), rgba(180, 83, 9, 0.05));
        }
        
        .quick-action-btn.warning-action:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
            color: white;
        }
        
        .quick-action-btn.info-action {
            border-color: #0891b2;
            background: linear-gradient(135deg, rgba(8, 145, 178, 0.1), rgba(14, 116, 144, 0.05));
        }
        
        .quick-action-btn.info-action:hover {
            background: linear-gradient(135deg, #0891b2, #0e7490);
            color: white;
        }
        
        .quick-action-arrow {
            position: absolute;
            top: var(--emploidb-spacing-4);
            right: var(--emploidb-spacing-4);
            opacity: 0;
            transition: var(--emploidb-transition-all);
        }
        
        .quick-action-btn:hover .quick-action-arrow {
            opacity: 1;
            transform: translateX(4px);
        }
        
        .quick-action-btn:hover .quick-action-icon {
            transform: scale(1.1);
        }
        
        .quick-action-btn:hover h5,
        .quick-action-btn:hover p {
            color: inherit;
        }
        
        .quick-action-icon {
            width: 60px;
            height: 60px;
            border-radius: var(--emploidb-radius-full);
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: var(--emploidb-spacing-4);
            transition: var(--emploidb-transition-all);
        }
        
        .quick-action-btn:hover .quick-action-icon {
            transform: scale(1.1);
            box-shadow: var(--emploidb-shadow-md);
        }
        
        .quick-action-icon i {
            font-size: 1.5rem;
            color: white;
        }
        
        .quick-action-btn h5 {
            color: var(--emploidb-text-primary);
            margin-bottom: var(--emploidb-spacing-2);
            font-weight: var(--emploidb-font-weight-semibold);
        }
        
        .quick-action-btn p {
            color: var(--emploidb-text-secondary);
            margin: 0;
            font-size: 0.875rem;
        }
        
        /* Performance Widget */
        .performance-widget {
            background: var(--emploidb-bg-primary);
            border-radius: var(--emploidb-radius-xl);
            padding: var(--emploidb-spacing-6);
            box-shadow: var(--emploidb-shadow-lg);
            border: 1px solid var(--emploidb-neutral-200);
            height: 100%;
        }
        
        .widget-header {
            margin-bottom: var(--emploidb-spacing-5);
            padding-bottom: var(--emploidb-spacing-4);
            border-bottom: 2px solid var(--emploidb-neutral-200);
        }
        
        .widget-title {
            font-size: 1.25rem;
            font-weight: var(--emploidb-font-weight-bold);
            color: var(--emploidb-text-primary);
            margin: 0;
        }
        
        .performance-metrics {
            margin-bottom: var(--emploidb-spacing-5);
        }
        
        .metric-item {
            margin-bottom: var(--emploidb-spacing-4);
        }
        
        .metric-item:last-child {
            margin-bottom: 0;
        }
        
        .metric-label {
            font-size: 0.875rem;
            color: var(--emploidb-text-secondary);
            margin-bottom: var(--emploidb-spacing-1);
        }
        
        .metric-value {
            font-size: 1.5rem;
            font-weight: var(--emploidb-font-weight-bold);
            color: var(--emploidb-text-primary);
            margin-bottom: var(--emploidb-spacing-2);
        }
        
        .metric-progress {
            height: 6px;
            background: var(--emploidb-neutral-200);
            border-radius: 3px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #2563eb, #059669);
            border-radius: 3px;
            transition: width 0.3s ease;
        }
        
        .widget-footer {
            border-top: 1px solid var(--emploidb-neutral-200);
            padding-top: var(--emploidb-spacing-4);
        }
        
        /* Section Spacing and Color Schema */
        .section-spacing {
            margin-bottom: var(--emploidb-spacing-8) !important;
        }
        
        .section-spacing:last-child {
            margin-bottom: 0 !important;
        }
        
        /* Enhanced Chart Container */
        .chart-container {
            background: var(--emploidb-bg-primary);
            border-radius: var(--emploidb-radius-xl);
            padding: var(--emploidb-spacing-6);
            box-shadow: var(--emploidb-shadow-lg);
            border: 1px solid var(--emploidb-neutral-200);
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #2563eb, #0891b2);
        }
        
        /* Enhanced Job Cards */
        .job-card {
            background: var(--emploidb-bg-primary);
            border-radius: var(--emploidb-radius-lg);
            padding: var(--emploidb-spacing-5);
            border: 1px solid var(--emploidb-neutral-200);
            margin-bottom: var(--emploidb-spacing-4);
            transition: var(--emploidb-transition-all);
            position: relative;
            overflow: hidden;
        }
        
        .job-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #2563eb;
            transform: scaleY(0);
            transition: var(--emploidb-transition-all);
        }
        
        .job-card:hover::before {
            transform: scaleY(1);
        }
        
        .job-card:hover {
            box-shadow: var(--emploidb-shadow-lg);
            border-color: #2563eb;
            transform: translateY(-2px);
        }
        
        /* Enhanced Application Cards */
        .application-card {
            background: var(--emploidb-bg-primary);
            border-radius: var(--emploidb-radius-lg);
            padding: var(--emploidb-spacing-4);
            border: 1px solid var(--emploidb-neutral-200);
            margin-bottom: var(--emploidb-spacing-3);
            transition: var(--emploidb-transition-all);
            position: relative;
            overflow: hidden;
        }
        
        .application-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #059669;
            transform: scaleY(0);
            transition: var(--emploidb-transition-all);
        }
        
        .application-card:hover::before {
            transform: scaleY(1);
        }
        
        .application-card:hover {
            box-shadow: var(--emploidb-shadow-md);
            border-color: #059669;
            transform: translateY(-1px);
        }
        
        /* Enhanced Status Badges */
        .status-badge {
            padding: 6px 14px;
            border-radius: var(--emploidb-radius-full);
            font-size: 0.75rem;
            font-weight: var(--emploidb-font-weight-semibold);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid transparent;
            transition: var(--emploidb-transition-all);
        }
        
        .status-badge.active {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            box-shadow: 0 2px 4px rgba(5, 150, 105, 0.3);
        }
        
        .status-badge.pending {
            background: linear-gradient(135deg, #d97706, #b45309);
            color: white;
            box-shadow: 0 2px 4px rgba(217, 119, 6, 0.3);
        }
        
        .status-badge.inactive {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
            box-shadow: 0 2px 4px rgba(107, 114, 128, 0.3);
        }
        
        /* Enhanced Button Styles */
        .emploidb-btn {
            padding: var(--emploidb-spacing-3) var(--emploidb-spacing-5);
            border-radius: var(--emploidb-radius-lg);
            font-weight: var(--emploidb-font-weight-semibold);
            transition: var(--emploidb-transition-all);
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: var(--emploidb-spacing-2);
            position: relative;
            overflow: hidden;
        }
        
        .emploidb-btn-sm {
            padding: var(--emploidb-spacing-2) var(--emploidb-spacing-4);
            font-size: 0.875rem;
        }
        
        .emploidb-btn-primary {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        
        .emploidb-btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
            color: white;
        }
        
        .emploidb-btn-outline {
            background: transparent;
            color: #2563eb;
            border: 2px solid #2563eb;
            position: relative;
            overflow: hidden;
        }
        
        .emploidb-btn-outline::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            transition: var(--emploidb-transition-all);
            z-index: -1;
        }
        
        .emploidb-btn-outline:hover::before {
            left: 0;
        }
        
        .emploidb-btn-outline:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }

        /* Enhanced Employer Sidebar Styles - Enterprise Design */
        .employer-sidebar {
            background: var(--enterprise-gradient-primary);
            box-shadow: var(--enterprise-shadow-2xl);
            width: 320px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 1050;
            transition: var(--enterprise-transition-slow);
            backdrop-filter: blur(10px);
        }

        .employer-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .employer-sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .employer-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: var(--enterprise-radius-full);
        }

        .employer-sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Enterprise Navigation Styles */
        .enterprise-nav {
            padding: var(--enterprise-spacing-6) 0;
        }

        .nav-section {
            margin-bottom: var(--enterprise-spacing-8);
        }

        .nav-section-header {
            padding: 0 var(--enterprise-spacing-6) var(--enterprise-spacing-3);
            color: rgba(255, 255, 255, 0.6);
            font-size: var(--enterprise-font-size-xs);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .enterprise-nav-link {
            display: flex;
            align-items: center;
            padding: var(--enterprise-spacing-4) var(--enterprise-spacing-6);
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--enterprise-transition);
            position: relative;
            font-weight: 500;
            border-left: 3px solid transparent;
        }

        .enterprise-nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: var(--enterprise-accent);
            transform: translateX(4px);
        }

        .enterprise-nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.15);
            border-left-color: var(--enterprise-accent);
            box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.1);
        }

        .enterprise-nav-link i {
            width: 20px;
            margin-right: var(--enterprise-spacing-4);
            font-size: var(--enterprise-font-size-lg);
        }

        .enterprise-nav-link .badge {
            margin-left: auto;
            background: var(--enterprise-accent);
            color: white;
            font-size: var(--enterprise-font-size-xs);
            padding: var(--enterprise-spacing-1) var(--enterprise-spacing-2);
            border-radius: var(--enterprise-radius-full);
        }
        
        .company-brand-section {
            text-align: center;
            margin-bottom: var(--enterprise-spacing-6);
        }
        
        .company-logo-container {
            margin-bottom: var(--enterprise-spacing-4);
        }
        
        .company-logo {
            width: 60px;
            height: 60px;
            border-radius: var(--enterprise-radius-lg);
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--enterprise-shadow-md);
        }
        
        .company-logo-placeholder {
            width: 60px;
            height: 60px;
            border-radius: var(--enterprise-radius-lg);
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            border: 3px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--enterprise-shadow-md);
        }
        
        .company-logo-placeholder i {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .company-name {
            color: white;
            font-size: var(--enterprise-font-size-lg);
            font-weight: 700;
            margin: 0 0 var(--enterprise-spacing-1) 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .company-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: var(--enterprise-font-size-sm);
            font-weight: 500;
            margin: 0;
        }
        
        .user-info-section {
            display: flex;
            align-items: center;
            gap: var(--enterprise-spacing-3);
            padding: var(--enterprise-spacing-4);
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--enterprise-radius-lg);
            margin-top: var(--enterprise-spacing-4);
            backdrop-filter: blur(10px);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: var(--enterprise-radius-full);
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: var(--enterprise-font-size-lg);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .user-details {
            flex: 1;
        }
        
        .user-name {
            color: white;
            font-size: var(--enterprise-font-size-sm);
            font-weight: 600;
            margin: 0 0 2px 0;
        }
        
        .user-role {
            color: rgba(255, 255, 255, 0.7);
            font-size: var(--enterprise-font-size-xs);
            margin: 0;
        }
        
        .website-logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--enterprise-spacing-2);
            background: rgba(255, 255, 255, 0.1);
            border-radius: var(--enterprise-radius-lg);
            backdrop-filter: blur(10px);
        }
        
        /* Enhanced Animations & Interactions - Staggered Loading */
        .analytics-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .analytics-card:nth-child(1) { animation-delay: 0.1s; }
        .analytics-card:nth-child(2) { animation-delay: 0.2s; }
        .analytics-card:nth-child(3) { animation-delay: 0.3s; }
        .analytics-card:nth-child(4) { animation-delay: 0.4s; }
        
        .quick-action-btn {
            animation: fadeInUp 0.6s ease-out;
        }
        
        .quick-action-btn:nth-child(1) { animation-delay: 0.5s; }
        .quick-action-btn:nth-child(2) { animation-delay: 0.6s; }
        .quick-action-btn:nth-child(3) { animation-delay: 0.7s; }
        .quick-action-btn:nth-child(4) { animation-delay: 0.8s; }
        
        .performance-widget {
            animation: fadeInUp 0.6s ease-out 0.9s both;
        }
        
        .chart-container {
            animation: fadeInUp 0.6s ease-out 1.0s both;
        }
        
        .job-card,
        .application-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .employer-sidebar {
                transform: translateX(-100%);
            }
            
            .employer-sidebar.show {
                transform: translateX(0);
            }
            
            .employer-content {
                margin-left: 0;
            }
        }
        
        @media (max-width: 768px) {
            .employer-sidebar {
                width: 280px;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .employer-content {
                padding: var(--emploidb-spacing-4);
                margin-left: 0;
            }
            
            .employer-header {
                padding: var(--emploidb-spacing-4);
                margin-bottom: var(--emploidb-spacing-4);
            }
            
            .analytics-card {
                padding: var(--emploidb-spacing-4);
                margin-bottom: var(--emploidb-spacing-4);
            }
            
            .chart-container {
                padding: var(--emploidb-spacing-4);
            }
            
            .section-spacing {
                margin-bottom: var(--emploidb-spacing-6) !important;
            }
            
            .row.g-4 {
                margin-bottom: var(--emploidb-spacing-4) !important;
            }
            
            .quick-action-btn {
                min-height: 120px;
                padding: var(--emploidb-spacing-4);
            }
            
            .quick-action-icon {
                width: 50px;
                height: 50px;
                margin-bottom: var(--emploidb-spacing-3);
            }
            
            .quick-action-icon i {
                font-size: 1.25rem;
            }
            
            .job-card,
            .application-card {
                padding: var(--emploidb-spacing-4);
                margin-bottom: var(--emploidb-spacing-3);
            }
            
            .performance-widget {
                padding: var(--emploidb-spacing-4);
                margin-top: var(--emploidb-spacing-4);
            }
        }
        
        @media (max-width: 576px) {
            .employer-content {
                padding: var(--emploidb-spacing-3);
            }
            
            .employer-header {
                padding: var(--emploidb-spacing-3);
                text-align: center;
            }
            
            .employer-header .d-flex {
                flex-direction: column;
                gap: var(--emploidb-spacing-3);
            }
            
            .analytics-card {
                padding: var(--emploidb-spacing-3);
            }
            
            .chart-container {
                padding: var(--emploidb-spacing-3);
            }
            
            .section-spacing {
                margin-bottom: var(--emploidb-spacing-4) !important;
            }
        }

        /* Loading Animation */
        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    
        /* Content Visibility Fixes */
        .employer-content {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }
        
        .container-fluid {
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        .card, .form-group, .table-responsive, .alert, .btn {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }
        
        .section-spacing {
            margin-bottom: 2rem;
        }
        
        .component-spacing {
            margin-bottom: 1.5rem;
        }
        
        .element-spacing {
            margin-bottom: 1rem;
        }
        
        /* Ensure all content is visible */
        * {
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        /* Fix for specific elements */
        .post-job-content, .jobs-content, .applications-content, .profile-content, .settings-content {
            opacity: 1 !important;
            visibility: visible !important;
            display: block !important;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <!-- Header -->
        <div class="register-header">
            <div class="header-content">
                <div class="brand-logo">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h1 class="header-title">Inscription Employeur</h1>
                <p class="header-subtitle">Rejoignez JobMaroc et trouvez les meilleurs talents pour votre entreprise</p>
            </div>
        </div>

        <!-- Registration Form -->
        <div class="register-form">
            <h2 class="form-title">Créer votre compte employeur</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($errors[0]) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success_message) ?>
                    <br>
                    <a href="login.php" class="btn btn-success btn-sm mt-2">Se connecter maintenant</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                
                <!-- Company Information -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-building me-2"></i>
                        Informations de l'entreprise
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group element-spacing">
                            <label for="company_name" class="form-label">Nom de l'entreprise *</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" 
                                   placeholder="Nom de votre entreprise" required 
                                   value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group element-spacing">
                            <label for="email" class="form-label">Email professionnel *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="contact@entreprise.com" required 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group element-spacing">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   placeholder="+212 6 12 34 56 78" 
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group element-spacing">
                            <label for="website" class="form-label">Site web</label>
                            <input type="url" class="form-control" id="website" name="website" 
                                   placeholder="https://www.entreprise.com" 
                                   value="<?= htmlspecialchars($_POST['website'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group element-spacing">
                            <label for="industry" class="form-label">Secteur d'activité</label>
                            <select class="form-select" id="industry" name="industry">
                                <option value="">Sélectionnez un secteur</option>
                                <option value="Technology" <?= ($_POST['industry'] ?? '') === 'Technology' ? 'selected' : '' ?>>Technologie</option>
                                <option value="Finance" <?= ($_POST['industry'] ?? '') === 'Finance' ? 'selected' : '' ?>>Finance</option>
                                <option value="Healthcare" <?= ($_POST['industry'] ?? '') === 'Healthcare' ? 'selected' : '' ?>>Santé</option>
                                <option value="Education" <?= ($_POST['industry'] ?? '') === 'Education' ? 'selected' : '' ?>>Éducation</option>
                                <option value="Manufacturing" <?= ($_POST['industry'] ?? '') === 'Manufacturing' ? 'selected' : '' ?>>Manufacture</option>
                                <option value="Retail" <?= ($_POST['industry'] ?? '') === 'Retail' ? 'selected' : '' ?>>Commerce</option>
                                <option value="Consulting" <?= ($_POST['industry'] ?? '') === 'Consulting' ? 'selected' : '' ?>>Conseil</option>
                                <option value="Other" <?= ($_POST['industry'] ?? '') === 'Other' ? 'selected' : '' ?>>Autre</option>
                            </select>
                        </div>
                        
                        <div class="form-group element-spacing">
                            <label for="company_size" class="form-label">Taille de l'entreprise</label>
                            <select class="form-select" id="company_size" name="company_size">
                                <option value="">Sélectionnez la taille</option>
                                <option value="1-10" <?= ($_POST['company_size'] ?? '') === '1-10' ? 'selected' : '' ?>>1-10 employés</option>
                                <option value="11-50" <?= ($_POST['company_size'] ?? '') === '11-50' ? 'selected' : '' ?>>11-50 employés</option>
                                <option value="51-200" <?= ($_POST['company_size'] ?? '') === '51-200' ? 'selected' : '' ?>>51-200 employés</option>
                                <option value="201-500" <?= ($_POST['company_size'] ?? '') === '201-500' ? 'selected' : '' ?>>201-500 employés</option>
                                <option value="501-1000" <?= ($_POST['company_size'] ?? '') === '501-1000' ? 'selected' : '' ?>>501-1000 employés</option>
                                <option value="1000+" <?= ($_POST['company_size'] ?? '') === '1000+' ? 'selected' : '' ?>>1000+ employés</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group element-spacing">
                        <label for="founded_year" class="form-label">Année de création</label>
                        <input type="number" class="form-control" id="founded_year" name="founded_year" 
                               placeholder="2020" min="1900" max="<?= date('Y') ?>" 
                               value="<?= htmlspecialchars($_POST['founded_year'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group element-spacing">
                        <label for="company_description" class="form-label">Description de l'entreprise</label>
                        <textarea class="form-control" id="company_description" name="company_description" 
                                  placeholder="Décrivez votre entreprise, sa mission, ses valeurs..."><?= htmlspecialchars($_POST['company_description'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Account Security -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-shield-alt me-2"></i>
                        Sécurité du compte
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group element-spacing">
                            <label for="password" class="form-label">Mot de passe *</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Minimum 8 caractères" required>
                        </div>
                        
                        <div class="form-group element-spacing">
                            <label for="confirm_password" class="form-label">Confirmer le mot de passe *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                   placeholder="Répétez votre mot de passe" required>
                        </div>
                    </div>
                </div>

                <!-- Company Logo -->
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-image me-2"></i>
                        Logo de l'entreprise
                    </h3>
                    
                    <div class="form-group element-spacing">
                        <div class="file-upload">
                            <input type="file" id="company_logo" name="company_logo" accept="image/*">
                            <label for="company_logo" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <div>
                                    <strong>Cliquez pour télécharger</strong>
                                    <br>
                                    <small>PNG, JPG jusqu'à 5MB</small>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus me-2"></i>
                    Créer mon compte employeur
                </button>
            </form>

            <div class="form-links">
                <p class="mb-0">
                    Vous avez déjà un compte ? 
                    <a href="login.php" class="fw-bold">Se connecter</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

