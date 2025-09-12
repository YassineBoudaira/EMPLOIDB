<?php 
// Include configuration first (before any session starts)
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';
include __DIR__ . '/../include/connexion.php';

// Check if user is logged in as employer
if (!Security::isLoggedIn() || !isset($_SESSION['role']) || $_SESSION['role'] !== 'employer') {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get employer information
$employer = $db->fetch("SELECT * FROM employers WHERE user_id = ?", [$user_id]);
if (!$employer) {
    header('Location: register.php');
    exit();
}

// Get employer profile ID
$employer_profile = $db->fetch("SELECT id FROM profiles WHERE user_id = ?", [$user_id]);
if (!$employer_profile) {
    header('Location: register.php');
    exit();
}

$employer_profile_id = $employer_profile['id'];

// Get company name from session or employer table
if (!isset($_SESSION['company_name'])) {
    $_SESSION['company_name'] = $employer['company_name'];
}

// Get statistics
$total_jobs = $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE profile_id = ?", [$employer_profile_id])['count'];
$active_jobs = $db->fetch("SELECT COUNT(*) as count FROM annonces WHERE profile_id = ? AND status = 'active'", [$employer_profile_id])['count'];
$total_applications = $db->fetch("SELECT COUNT(*) as count FROM postulation p JOIN annonces a ON p.annonce_id = a.id WHERE a.profile_id = ?", [$employer_profile_id])['count'];
$new_applications = $db->fetch("SELECT COUNT(*) as count FROM postulation p JOIN annonces a ON p.annonce_id = a.id WHERE a.profile_id = ? AND p.status = 'applied'", [$employer_profile_id])['count'];

// Get recent job postings
$recent_jobs = $db->fetchAll("
    SELECT a.*, d.nom as domaine_nom, v.nom as ville_nom,
           (SELECT COUNT(*) FROM postulation p WHERE p.annonce_id = a.id) as applications_count
    FROM annonces a
    LEFT JOIN domaines d ON a.domaine_id = d.id
    LEFT JOIN villes v ON a.ville_id = v.id
    WHERE a.profile_id = ?
    ORDER BY a.date_a DESC
    LIMIT 5
", [$employer_profile_id]);

// Get recent applications
$recent_applications = $db->fetchAll("
    SELECT p.*, a.titre, pr.nom, pr.prenom, pr.telephone, u.email
    FROM postulation p
    JOIN annonces a ON p.annonce_id = a.id
    JOIN profiles pr ON p.user_id = pr.user_id
    JOIN users u ON p.user_id = u.id
    WHERE a.profile_id = ?
    ORDER BY p.applied_at DESC
    LIMIT 10
", [$employer_profile_id]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Employeur - <?= htmlspecialchars($employer['company_name']) ?> | EMPLOIDB</title>
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
    <!-- ApexCharts CSS -->
    <link href="https://cdn.jsdelivr.net/npm/apexcharts@3.45.0/dist/apexcharts.css" rel="stylesheet">

    <!-- EMPLOIDB Professional Design System -->
    <link href="../assets/css/emploidb-design-system.css" rel="stylesheet">
    
    <style>
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
    </style>
</head>
<body>
    <div class="employer-wrapper">
        <!-- Include Enhanced Employer Sidebar -->
        <?php include 'includes/employer_sidebar.php'; ?>
        
        <!-- Professional Employer Content -->
        <div class="employer-content" id="employerContent">
            <!-- Professional Header -->
            <div class="employer-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="emploidb-text-3xl emploidb-font-black emploidb-text-primary mb-2">
                            <i class="fas fa-chart-line me-3"></i>Dashboard Employeur
                        </h1>
                        <p class="emploidb-text-secondary mb-0">
                            Gérez vos offres d'emploi et candidatures - <?= htmlspecialchars($employer['company_name']) ?>
                        </p>
                    </div>
                    <div class="d-flex gap-3">
                        <button class="emploidb-btn emploidb-btn-outline" id="toggleSidebar">
                            <i class="fas fa-bars"></i>
                        </button>
                        <a href="post_job.php" class="emploidb-btn emploidb-btn-primary">
                            <i class="fas fa-plus-circle me-2"></i>Nouvelle Offre
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Professional Analytics Dashboard -->
            <div class="row g-4 mb-6 section-spacing">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="analytics-card primary-gradient">
                        <div class="analytics-header">
                            <div class="analytics-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="analytics-trend positive">
                                <i class="fas fa-arrow-up"></i>
                                <span>+12%</span>
                            </div>
                        </div>
                        <div class="analytics-content">
                            <div class="analytics-number"><?= number_format($total_jobs) ?></div>
                            <div class="analytics-label">Offres Publiées</div>
                            <div class="analytics-subtitle">
                                <span class="badge bg-success-light"><?= number_format($active_jobs) ?> actives</span>
                            </div>
                        </div>
                        <div class="analytics-footer">
                            <a href="manage_jobs.php" class="analytics-link">
                                <i class="fas fa-arrow-right"></i> Gérer les offres
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="analytics-card success-gradient">
                        <div class="analytics-header">
                            <div class="analytics-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="analytics-trend positive">
                                <i class="fas fa-arrow-up"></i>
                                <span>+8%</span>
                            </div>
                        </div>
                        <div class="analytics-content">
                            <div class="analytics-number"><?= number_format($total_applications) ?></div>
                            <div class="analytics-label">Candidatures</div>
                            <div class="analytics-subtitle">
                                <span class="badge bg-warning-light"><?= number_format($new_applications) ?> nouvelles</span>
                            </div>
                        </div>
                        <div class="analytics-footer">
                            <a href="applications.php" class="analytics-link">
                                <i class="fas fa-arrow-right"></i> Voir candidatures
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="analytics-card warning-gradient">
                        <div class="analytics-header">
                            <div class="analytics-icon">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div class="analytics-trend positive">
                                <i class="fas fa-arrow-up"></i>
                                <span>+5%</span>
                            </div>
                        </div>
                        <div class="analytics-content">
                            <div class="analytics-number"><?= number_format(($total_applications > 0 ? ($new_applications / $total_applications) * 100 : 0), 1) ?>%</div>
                            <div class="analytics-label">Taux de Réponse</div>
                            <div class="analytics-subtitle">
                                <span class="badge bg-info-light">Ce mois-ci</span>
                            </div>
                        </div>
                        <div class="analytics-footer">
                            <a href="applications.php" class="analytics-link">
                                <i class="fas fa-arrow-right"></i> Analyser
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="analytics-card info-gradient">
                        <div class="analytics-header">
                            <div class="analytics-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="analytics-trend positive">
                                <i class="fas fa-arrow-up"></i>
                                <span>+3</span>
                            </div>
                        </div>
                        <div class="analytics-content">
                            <div class="analytics-number"><?= count($recent_jobs) ?></div>
                            <div class="analytics-label">Offres Récentes</div>
                            <div class="analytics-subtitle">
                                <span class="badge bg-secondary-light">Cette semaine</span>
                            </div>
                        </div>
                        <div class="analytics-footer">
                            <a href="manage_jobs.php" class="analytics-link">
                                <i class="fas fa-arrow-right"></i> Voir toutes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Jobs and Applications -->
            <div class="row g-4 mb-5 section-spacing">
                <div class="col-xl-8">
                    <div class="chart-container">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="emploidb-font-semibold emploidb-text-lg mb-0">
                                <i class="fas fa-briefcase me-2 text-primary"></i>Offres d'Emploi Récentes
                            </h3>
                            <a href="manage_jobs.php" class="emploidb-btn emploidb-btn-outline emploidb-btn-sm">
                                <i class="fas fa-eye me-1"></i>Voir Tout
                            </a>
                        </div>
                        
                        <?php if (empty($recent_jobs)): ?>
                            <div class="empty-state">
                                <i class="fas fa-briefcase"></i>
                                <h5>Aucune offre d'emploi</h5>
                                <p>Commencez par publier votre première offre d'emploi</p>
                                <a href="post_job.php" class="emploidb-btn emploidb-btn-primary">
                                    <i class="fas fa-plus me-2"></i>Publier une Offre
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="space-y-3">
                                <?php foreach($recent_jobs as $job): ?>
                                <div class="job-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h5 class="emploidb-font-semibold mb-2">
                                                <a href="job_details.php?id=<?= $job['id'] ?>" class="text-decoration-none">
                                                    <?= htmlspecialchars($job['titre']) ?>
                                                </a>
                                            </h5>
                                            <div class="d-flex flex-wrap gap-3 emploidb-text-sm emploidb-text-secondary">
                                                <span><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($job['ville_nom'] ?? 'Non spécifié') ?></span>
                                                <span><i class="fas fa-briefcase me-1"></i><?= htmlspecialchars($job['domaine_nom'] ?? 'Non spécifié') ?></span>
                                                <span><i class="fas fa-calendar me-1"></i><?= date('d/m/Y', strtotime($job['date_a'])) ?></span>
                                                <span><i class="fas fa-users me-1"></i><?= $job['applications_count'] ?? 0 ?> candidatures</span>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <span class="status-badge <?= $job['status'] === 'active' ? 'active' : 'pending' ?>">
                                                <?= $job['status'] === 'active' ? 'Actif' : 'Inactif' ?>
                                            </span>
                                            <div class="dropdown">
                                                <button class="emploidb-btn emploidb-btn-outline emploidb-btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-h"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="edit_job.php?id=<?= $job['id'] ?>"><i class="fas fa-edit me-2"></i>Modifier</a></li>
                                                    <li><a class="dropdown-item" href="view_applications.php?job_id=<?= $job['id'] ?>"><i class="fas fa-users me-2"></i>Candidatures</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="delete_job.php?id=<?= $job['id'] ?>"><i class="fas fa-trash me-2"></i>Supprimer</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-xl-4">
                    <div class="chart-container">
                        <div class="section-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="section-title">
                                    <i class="fas fa-file-alt me-2 text-secondary"></i>Candidatures Récentes
                                </h3>
                                <a href="applications.php" class="emploidb-btn emploidb-btn-outline emploidb-btn-sm">
                                    <i class="fas fa-eye me-1"></i>Voir Tout
                                </a>
                            </div>
                        </div>
                        
                        <?php if (empty($recent_applications)): ?>
                            <div class="empty-state">
                                <i class="fas fa-file-alt"></i>
                                <h5>Aucune candidature récente</h5>
                                <p>Les candidatures apparaîtront ici une fois que vous aurez publié des offres</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-2">
                                <?php foreach(array_slice($recent_applications, 0, 5) as $application): ?>
                                <div class="application-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="emploidb-font-semibold mb-1">
                                                <?= htmlspecialchars($application['nom'] . ' ' . $application['prenom']) ?>
                                            </h6>
                                            <p class="emploidb-text-sm emploidb-text-secondary mb-1">
                                                <?= htmlspecialchars($application['titre']) ?>
                                            </p>
                                            <div class="emploidb-text-xs emploidb-text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($application['applied_at'])) ?>
                                            </div>
                                        </div>
                                        <span class="status-badge pending">
                                            Nouvelle
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Professional Quick Actions Panel -->
            <div class="row g-4 mb-5 section-spacing">
                <div class="col-12">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-bolt me-2 text-warning"></i>Actions Rapides
                        </h3>
                        <p class="section-subtitle">Accédez rapidement aux fonctionnalités principales</p>
                    </div>
                </div>
                
                <div class="col-xl-8">
                    <div class="row g-4">
                        <div class="col-lg-6 col-md-6">
                            <a href="post_job.php" class="quick-action-btn primary-action w-100 text-decoration-none">
                                <div class="quick-action-icon">
                                    <i class="fas fa-plus-circle"></i>
                                </div>
                                <h5 class="emploidb-font-semibold mb-2">Publier une Offre</h5>
                                <p class="emploidb-text-sm emploidb-text-muted mb-3">Créer une nouvelle annonce d'emploi</p>
                                <div class="quick-action-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-6 col-md-6">
                            <a href="manage_jobs.php" class="quick-action-btn success-action w-100 text-decoration-none">
                                <div class="quick-action-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <h5 class="emploidb-font-semibold mb-2">Gérer les Offres</h5>
                                <p class="emploidb-text-sm emploidb-text-muted mb-3">Modifier et organiser vos annonces</p>
                                <div class="quick-action-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-6 col-md-6">
                            <a href="applications.php" class="quick-action-btn warning-action w-100 text-decoration-none">
                                <div class="quick-action-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h5 class="emploidb-font-semibold mb-2">Candidatures</h5>
                                <p class="emploidb-text-sm emploidb-text-muted mb-3">Examiner les CV et profils reçus</p>
                                <div class="quick-action-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-6 col-md-6">
                            <a href="profile.php" class="quick-action-btn info-action w-100 text-decoration-none">
                                <div class="quick-action-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <h5 class="emploidb-font-semibold mb-2">Profil Entreprise</h5>
                                <p class="emploidb-text-sm emploidb-text-muted mb-3">Mettre à jour les informations</p>
                                <div class="quick-action-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-4">
                    <div class="performance-widget">
                        <div class="widget-header">
                            <h4 class="widget-title">
                                <i class="fas fa-trophy me-2"></i>Performance
                            </h4>
                        </div>
                        <div class="performance-metrics">
                            <div class="metric-item">
                                <div class="metric-label">Temps de Réponse Moyen</div>
                                <div class="metric-value">2.3 jours</div>
                                <div class="metric-progress">
                                    <div class="progress-bar" style="width: 85%"></div>
                                </div>
                            </div>
                            
                            <div class="metric-item">
                                <div class="metric-label">Taux de Conversion</div>
                                <div class="metric-value">12.5%</div>
                                <div class="metric-progress">
                                    <div class="progress-bar" style="width: 65%"></div>
                                </div>
                            </div>
                            
                            <div class="metric-item">
                                <div class="metric-label">Satisfaction Candidats</div>
                                <div class="metric-value">4.8/5</div>
                                <div class="metric-progress">
                                    <div class="progress-bar" style="width: 96%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="widget-footer">
                            <button class="btn btn-outline-primary btn-sm" onclick="showPerformanceDetails()">
                                <i class="fas fa-chart-bar me-1"></i>Voir Détails
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Professional Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.0/dist/apexcharts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle functionality
            const sidebar = document.getElementById('employerSidebar');
            const content = document.getElementById('employerContent');
            const toggleBtn = document.getElementById('toggleSidebar');
            
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    content.classList.toggle('expanded');
                });
            }
            
            // Remove loading animation
            setTimeout(function() {
                const loading = document.getElementById('loading');
                if (loading) {
                    loading.style.display = 'none';
                }
            }, 500);
            
            // Add active class to current nav link
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.employer-nav-link');
            navLinks.forEach(function(link) {
                if (link.href.includes(currentPath.split('/').pop())) {
                    link.classList.add('active');
                }
            });
            
            // Initialize analytics cards animations
            initializeAnalyticsCards();
            
            // Initialize quick action buttons
            initializeQuickActions();
        });
        
        // Analytics Cards Animation
        function initializeAnalyticsCards() {
            const analyticsCards = document.querySelectorAll('.analytics-card');
            analyticsCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }
        
        // Quick Actions Enhancement
        function initializeQuickActions() {
            const quickActionBtns = document.querySelectorAll('.quick-action-btn');
            quickActionBtns.forEach((btn, index) => {
                btn.style.opacity = '0';
                btn.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    btn.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    btn.style.opacity = '1';
                    btn.style.transform = 'translateY(0)';
                }, (index * 100) + 400);
                
                // Add click animation
                btn.addEventListener('click', function(e) {
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });
        }
        
        // Performance Details Modal
        function showPerformanceDetails() {
            // Create and show performance details modal
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-chart-line me-2"></i>Détails de Performance
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Temps de Réponse</h6>
                                    <p>Votre temps de réponse moyen est excellent à 2.3 jours, ce qui place votre entreprise dans le top 15% des recruteurs.</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Taux de Conversion</h6>
                                    <p>Votre taux de conversion de 12.5% est au-dessus de la moyenne du secteur (8-10%).</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Satisfaction Candidats</h6>
                                    <p>Les candidats évaluent votre processus de recrutement à 4.8/5, ce qui est exceptionnel.</p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Recommandations</h6>
                                    <p>Continuez à maintenir ces excellentes performances. Considérez l'automatisation pour améliorer encore plus.</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            <button type="button" class="btn btn-primary">Voir Rapport Complet</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            modal.addEventListener('hidden.bs.modal', function() {
                document.body.removeChild(modal);
            });
        }
        
        // Quick Action Functions
        function quickAction(action) {
            switch(action) {
                case 'post_job':
                    window.location.href = 'post_job.php';
                    break;
                case 'manage_jobs':
                    window.location.href = 'manage_jobs.php';
                    break;
                case 'applications':
                    window.location.href = 'applications.php';
                    break;
                case 'profile':
                    window.location.href = 'profile.php';
                    break;
                default:
                    console.log('Action not defined:', action);
            }
        }
        
        // Enhanced Interactive Features
        function initializeEnhancedInteractions() {
            // Add smooth page transitions
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.3s ease-in-out';
            
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
            
            // Add loading states for buttons
            const buttons = document.querySelectorAll('.emploidb-btn, .quick-action-btn');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (this.href && !this.href.includes('#')) {
                        this.style.opacity = '0.7';
                        this.style.pointerEvents = 'none';
                        
                        // Add loading spinner
                        const originalContent = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Chargement...';
                        
                        // Reset after navigation (this won't execute if page changes)
                        setTimeout(() => {
                            this.innerHTML = originalContent;
                            this.style.opacity = '1';
                            this.style.pointerEvents = 'auto';
                        }, 2000);
                    }
                });
            });
            
            // Add hover effects for cards
            const cards = document.querySelectorAll('.analytics-card, .job-card, .application-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            
            // Add ripple effect for buttons
            const actionButtons = document.querySelectorAll('.quick-action-btn');
            actionButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.add('ripple');
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        }
        
        // Initialize all enhancements
        document.addEventListener('DOMContentLoaded', function() {
            initializeEnhancedInteractions();
        });
        
        // Add ripple effect styles
        const style = document.createElement('style');
        style.textContent = `
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
