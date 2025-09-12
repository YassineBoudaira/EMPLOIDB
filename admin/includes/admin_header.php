<?php
// Include configuration first (before any session starts)
include __DIR__ . '/../../include/config.php';
include __DIR__ . '/../../include/sess.php';
include __DIR__ . '/../../include/connexion.php';

// Check if user is admin - prevent redirect loops
if (!Security::isLoggedIn()) {
    header('Location: ../../login.php');
    exit;
}

if (!Security::isAdmin()) {
    // If user is logged in but not admin, redirect to appropriate dashboard
    if (isset($_SESSION['role'])) {
        switch ($_SESSION['role']) {
            case 'employer':
                header('Location: ../../employer/dashboard.php');
                break;
            case 'advertiser':
                header('Location: ../../advertiser/dashboard.php');
                break;
            case 'user':
                header('Location: ../../index.php');
                break;
            default:
                header('Location: ../../login.php');
        }
    } else {
        header('Location: ../../login.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Enterprise Admin - EMPLOIDB' ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="../../frontoffice/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Enterprise CSS Framework -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Advanced Data Visualization -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/select/1.7.0/css/select.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Enterprise UI Components -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    
    <!-- EMPLOIDB Enterprise Design System -->
    <link href="../../assets/css/emploidb-design-system.css" rel="stylesheet">
    
    <style>
        /* EMPLOIDB Enterprise Admin System */
        :root {
            /* Enterprise Color Palette */
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

        /* Global Styles */
        * {
            box-sizing: border-box;
        }

        body {
            font-family: var(--enterprise-font-family);
            background: var(--enterprise-gray-50);
            color: var(--enterprise-gray-900);
            min-height: 100vh;
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* Enterprise Layout System */
        .enterprise-layout {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Advanced Sidebar - ULTRA AGGRESSIVE */
        .enterprise-sidebar {
            width: 320px;
            background: var(--enterprise-gradient-primary);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 9999;
            box-shadow: var(--enterprise-shadow-2xl);
            transition: var(--enterprise-transition-slow);
            backdrop-filter: blur(10px);
            transform: translateX(0);
            display: block;
            visibility: visible;
            opacity: 1;
            pointer-events: auto;
        }
        
        /* Override any conflicting styles */
        nav.enterprise-sidebar {
            width: 320px !important;
            background: var(--enterprise-gradient-primary) !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            height: 100vh !important;
            overflow-y: auto !important;
            z-index: 9999 !important;
            box-shadow: var(--enterprise-shadow-2xl) !important;
            transform: translateX(0) !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        .enterprise-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .enterprise-sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .enterprise-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: var(--enterprise-radius-full);
        }

        .enterprise-sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Sidebar Brand */
        .sidebar-brand {
            padding: var(--enterprise-spacing-8) var(--enterprise-spacing-6);
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }

        .sidebar-brand h2 {
            color: white;
            font-weight: 800;
            font-size: var(--enterprise-font-size-2xl);
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .sidebar-brand .subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: var(--enterprise-font-size-sm);
            font-weight: 500;
            margin-top: var(--enterprise-spacing-1);
        }

        /* Navigation System */
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

        /* Main Content Area */
        .enterprise-content {
            flex: 1;
            margin-left: 320px;
            min-height: 100vh;
            background: var(--enterprise-gray-50);
            transition: var(--enterprise-transition-slow);
        }
        
        /* Ensure content is visible */
        .enterprise-main {
            background: var(--enterprise-gray-50);
            color: var(--enterprise-gray-900);
            padding: var(--enterprise-spacing-8);
        }
        
        /* Fix for admin content wrapper */
        .emploidb-admin-content {
            background: var(--enterprise-gray-50);
            color: var(--enterprise-gray-900);
            padding: var(--enterprise-spacing-6);
            margin-left: 320px;
            min-height: 100vh;
        }

        /* Top Navigation Bar */
        .enterprise-topbar {
            background: white;
            border-bottom: 1px solid var(--enterprise-gray-200);
            padding: var(--enterprise-spacing-4) var(--enterprise-spacing-6);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--enterprise-shadow-sm);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: var(--enterprise-spacing-4);
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--enterprise-gray-600);
            font-size: var(--enterprise-font-size-xl);
            padding: var(--enterprise-spacing-2);
            border-radius: var(--enterprise-radius);
            transition: var(--enterprise-transition);
            cursor: pointer;
        }

        .sidebar-toggle:hover {
            background: var(--enterprise-gray-100);
            color: var(--enterprise-primary);
        }

        .page-title {
            font-size: var(--enterprise-font-size-xl);
            font-weight: 700;
            color: var(--enterprise-gray-900);
            margin: 0;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: var(--enterprise-spacing-4);
        }

        /* Search Bar */
        .enterprise-search {
            position: relative;
            width: 300px;
        }

        .enterprise-search input {
            width: 100%;
            padding: var(--enterprise-spacing-3) var(--enterprise-spacing-4) var(--enterprise-spacing-3) var(--enterprise-spacing-10);
            border: 1px solid var(--enterprise-gray-300);
            border-radius: var(--enterprise-radius-lg);
            background: var(--enterprise-gray-50);
            font-size: var(--enterprise-font-size-sm);
            transition: var(--enterprise-transition);
        }

        .enterprise-search input:focus {
            outline: none;
            border-color: var(--enterprise-primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .enterprise-search i {
            position: absolute;
            left: var(--enterprise-spacing-4);
            top: 50%;
            transform: translateY(-50%);
            color: var(--enterprise-gray-400);
        }

        /* Notification System */
        .notification-dropdown {
            position: relative;
        }

        .notification-btn {
            background: none;
            border: none;
            color: var(--enterprise-gray-600);
            font-size: var(--enterprise-font-size-xl);
            padding: var(--enterprise-spacing-2);
            border-radius: var(--enterprise-radius);
            transition: var(--enterprise-transition);
            cursor: pointer;
            position: relative;
        }

        .notification-btn:hover {
            background: var(--enterprise-gray-100);
            color: var(--enterprise-primary);
        }

        .notification-badge {
            position: absolute;
            top: 0;
            right: 0;
            background: var(--enterprise-danger);
            color: white;
            font-size: var(--enterprise-font-size-xs);
            padding: var(--enterprise-spacing-1) var(--enterprise-spacing-2);
            border-radius: var(--enterprise-radius-full);
            min-width: 18px;
            text-align: center;
        }

        /* User Profile */
        .user-profile {
            display: flex;
            align-items: center;
            gap: var(--enterprise-spacing-3);
            padding: var(--enterprise-spacing-2) var(--enterprise-spacing-3);
            border-radius: var(--enterprise-radius-lg);
            transition: var(--enterprise-transition);
            cursor: pointer;
        }

        .user-profile:hover {
            background: var(--enterprise-gray-100);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: var(--enterprise-radius-full);
            background: var(--enterprise-gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: var(--enterprise-font-size-lg);
        }

        .user-info {
            display: none;
        }

        .user-info .name {
            font-weight: 600;
            color: var(--enterprise-gray-900);
            font-size: var(--enterprise-font-size-sm);
        }

        .user-info .role {
            color: var(--enterprise-gray-500);
            font-size: var(--enterprise-font-size-xs);
        }

        /* Main Content Container */
        .enterprise-main {
            padding: var(--enterprise-spacing-8);
            background: var(--enterprise-gray-50);
            min-height: calc(100vh - 80px);
        }

        /* Enterprise Cards */
        .enterprise-card {
            background: white;
            border-radius: var(--enterprise-radius-xl);
            box-shadow: var(--enterprise-shadow);
            border: 1px solid var(--enterprise-gray-200);
            transition: var(--enterprise-transition);
            overflow: hidden;
        }

        .enterprise-card:hover {
            box-shadow: var(--enterprise-shadow-lg);
            transform: translateY(-2px);
        }

        .enterprise-card-header {
            padding: var(--enterprise-spacing-6);
            border-bottom: 1px solid var(--enterprise-gray-200);
            background: var(--enterprise-gradient-glass);
        }

        .enterprise-card-title {
            font-size: var(--enterprise-font-size-xl);
            font-weight: 700;
            color: var(--enterprise-gray-900);
            margin: 0;
        }

        .enterprise-card-subtitle {
            color: var(--enterprise-gray-500);
            font-size: var(--enterprise-font-size-sm);
            margin-top: var(--enterprise-spacing-1);
        }

        .enterprise-card-body {
            padding: var(--enterprise-spacing-6);
        }

        .enterprise-card-footer {
            padding: var(--enterprise-spacing-4) var(--enterprise-spacing-6);
            border-top: 1px solid var(--enterprise-gray-200);
            background: var(--enterprise-gray-50);
        }

        /* Enterprise Buttons */
        .enterprise-btn {
            display: inline-flex;
            align-items: center;
            gap: var(--enterprise-spacing-2);
            padding: var(--enterprise-spacing-3) var(--enterprise-spacing-6);
            border: none;
            border-radius: var(--enterprise-radius-lg);
            font-weight: 600;
            font-size: var(--enterprise-font-size-sm);
            text-decoration: none;
            transition: var(--enterprise-transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .enterprise-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .enterprise-btn:hover::before {
            left: 100%;
        }

        .enterprise-btn-primary {
            background: var(--enterprise-gradient-primary);
            color: white;
        }

        .enterprise-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--enterprise-shadow-lg);
        }

        .enterprise-btn-secondary {
            background: var(--enterprise-gradient-secondary);
            color: white;
        }

        .enterprise-btn-accent {
            background: var(--enterprise-gradient-accent);
            color: white;
        }

        .enterprise-btn-outline {
            background: transparent;
            border: 2px solid var(--enterprise-primary);
            color: var(--enterprise-primary);
        }

        .enterprise-btn-outline:hover {
            background: var(--enterprise-primary);
            color: white;
        }

        .enterprise-btn-sm {
            padding: var(--enterprise-spacing-2) var(--enterprise-spacing-4);
            font-size: var(--enterprise-font-size-xs);
        }

        .enterprise-btn-lg {
            padding: var(--enterprise-spacing-4) var(--enterprise-spacing-8);
            font-size: var(--enterprise-font-size-lg);
        }

        /* Status Badges */
        .enterprise-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--enterprise-spacing-1);
            padding: var(--enterprise-spacing-1) var(--enterprise-spacing-3);
            border-radius: var(--enterprise-radius-full);
            font-size: var(--enterprise-font-size-xs);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .enterprise-badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--enterprise-success);
        }

        .enterprise-badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--enterprise-warning);
        }

        .enterprise-badge-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--enterprise-danger);
        }

        .enterprise-badge-info {
            background: rgba(6, 182, 212, 0.1);
            color: var(--enterprise-info);
        }

        .enterprise-badge-purple {
            background: rgba(139, 92, 246, 0.1);
            color: var(--enterprise-purple);
        }

        /* Enterprise Stats */
        .enterprise-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--enterprise-spacing-6);
            margin-bottom: var(--enterprise-spacing-8);
        }

        .enterprise-stat-card {
            background: white;
            border-radius: var(--enterprise-radius-xl);
            padding: var(--enterprise-spacing-6);
            box-shadow: var(--enterprise-shadow);
            border: 1px solid var(--enterprise-gray-200);
            position: relative;
            overflow: hidden;
            transition: var(--enterprise-transition);
        }

        .enterprise-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--enterprise-shadow-lg);
        }

        .enterprise-stat-number {
            font-size: var(--enterprise-font-size-3xl);
            font-weight: 800;
            color: var(--enterprise-primary);
            margin-bottom: var(--enterprise-spacing-2);
        }

        .enterprise-stat-label {
            font-size: var(--enterprise-font-size-sm);
            color: var(--enterprise-gray-600);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-icon {
            position: absolute;
            top: var(--enterprise-spacing-4);
            right: var(--enterprise-spacing-4);
            width: 48px;
            height: 48px;
            border-radius: var(--enterprise-radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--enterprise-font-size-xl);
            opacity: 0.1;
        }

        /* Enterprise Content Cards */
        .enterprise-content-card {
            background: white;
            border-radius: var(--enterprise-radius-xl);
            box-shadow: var(--enterprise-shadow);
            border: 1px solid var(--enterprise-gray-200);
            margin-bottom: var(--enterprise-spacing-6);
            overflow: hidden;
        }

        .enterprise-content-card .card-header {
            padding: var(--enterprise-spacing-6);
            border-bottom: 1px solid var(--enterprise-gray-200);
            background: var(--enterprise-gray-50);
        }

        .enterprise-content-card .card-body {
            padding: var(--enterprise-spacing-6);
        }

        /* Enterprise Action Buttons */
        .enterprise-action-btn {
            display: inline-flex;
            align-items: center;
            gap: var(--enterprise-spacing-2);
            padding: var(--enterprise-spacing-2) var(--enterprise-spacing-4);
            border: none;
            border-radius: var(--enterprise-radius);
            font-weight: 500;
            font-size: var(--enterprise-font-size-sm);
            text-decoration: none;
            transition: var(--enterprise-transition);
            cursor: pointer;
        }

        .enterprise-action-btn.enterprise-primary {
            background: var(--enterprise-primary);
            color: white;
        }

        .enterprise-action-btn.enterprise-secondary {
            background: var(--enterprise-gray-100);
            color: var(--enterprise-gray-700);
        }

        .enterprise-action-btn.enterprise-success {
            background: var(--enterprise-success);
            color: white;
        }

        .enterprise-action-btn.enterprise-warning {
            background: var(--enterprise-warning);
            color: white;
        }

        .enterprise-action-btn.enterprise-danger {
            background: var(--enterprise-danger);
            color: white;
        }

        .enterprise-action-btn.enterprise-info {
            background: var(--enterprise-info);
            color: white;
        }

        .enterprise-action-btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--enterprise-shadow-md);
        }

        /* Enterprise Status Badges */
        .enterprise-status-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--enterprise-spacing-1);
            padding: var(--enterprise-spacing-1) var(--enterprise-spacing-3);
            border-radius: var(--enterprise-radius-full);
            font-size: var(--enterprise-font-size-xs);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .enterprise-status-badge.enterprise-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--enterprise-success);
        }

        .enterprise-status-badge.enterprise-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--enterprise-warning);
        }

        .enterprise-status-badge.enterprise-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--enterprise-danger);
        }

        .enterprise-status-badge.enterprise-info {
            background: rgba(6, 182, 212, 0.1);
            color: var(--enterprise-info);
        }

        .enterprise-status-badge.enterprise-primary {
            background: rgba(30, 64, 175, 0.1);
            color: var(--enterprise-primary);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .enterprise-sidebar {
                transform: translateX(-100%);
            }
            
            .enterprise-sidebar.show {
                transform: translateX(0);
            }
            
            .enterprise-content {
                margin-left: 0;
            }
            
            .enterprise-search {
                width: 250px;
            }
        }
        
        /* Force sidebar to be visible on larger screens - ULTRA AGGRESSIVE */
        @media (min-width: 1025px) {
            .enterprise-sidebar {
                transform: translateX(0) !important;
                display: block !important;
                visibility: visible !important;
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                width: 320px !important;
                height: 100vh !important;
                z-index: 9999 !important;
                opacity: 1 !important;
                pointer-events: auto !important;
            }
            
            nav.enterprise-sidebar {
                transform: translateX(0) !important;
                display: block !important;
                visibility: visible !important;
                position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                width: 320px !important;
                height: 100vh !important;
                z-index: 9999 !important;
                opacity: 1 !important;
                pointer-events: auto !important;
            }
            
            .enterprise-content {
                margin-left: 320px !important;
            }
        }

        @media (max-width: 768px) {
            .enterprise-sidebar {
                width: 280px;
            }
            
            .enterprise-search {
                display: none;
            }
            
            .user-info {
                display: none;
            }
            
            .enterprise-main {
                padding: var(--enterprise-spacing-4);
            }
        }

        /* Loading States */
        .enterprise-loading {
            position: relative;
            overflow: hidden;
        }

        .enterprise-loading::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .slide-in {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="enterprise-layout">
        <!-- Include Admin Sidebar -->
        <?php include 'includes/admin_sidebar.php'; ?>

        <!-- Main Content Area -->
        <div class="enterprise-content">
            <!-- Top Navigation Bar -->
            <header class="enterprise-topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title"><?= $page_title ?? 'Enterprise Admin' ?></h1>
                </div>
                
                <div class="topbar-right">
                    <div class="enterprise-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search anything..." id="globalSearch">
                    </div>
                    
                    <div class="notification-dropdown">
                        <button class="notification-btn" id="notificationBtn">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                    </div>
                    
                    <div class="user-profile" id="userProfile">
                        <div class="user-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="user-info">
                            <div class="name">Admin User</div>
                            <div class="role">System Administrator</div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Container -->
            <main class="enterprise-main">
                <div class="container-fluid">
