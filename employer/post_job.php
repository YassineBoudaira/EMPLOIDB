<?php 
// Include configuration first (before any session starts)
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';
include __DIR__ . '/../include/connexion.php';

// Check if user is logged in as employer
if (!Security::isLoggedIn() || !isset($_SESSION['role']) || $_SESSION['role'] !== 'employer') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get employer information
$employer = $db->fetch("SELECT * FROM employers WHERE user_id = ?", [$user_id]);
if (!$employer) {
    header('Location: register.php');
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
        $titre = Security::sanitizeInput($_POST['titre'] ?? '');
        $description = Security::sanitizeInput($_POST['description'] ?? '');
        $domaine_id = (int)($_POST['domaine_id'] ?? 0);
        $ville_id = (int)($_POST['ville_id'] ?? 0);
        $contrat_id = (int)($_POST['contrat_id'] ?? 0);
        $salary_min = (float)($_POST['salary_min'] ?? 0);
        $salary_max = (float)($_POST['salary_max'] ?? 0);
        $job_type = Security::sanitizeInput($_POST['job_type'] ?? 'full-time');
        $remote_work = Security::sanitizeInput($_POST['remote_work'] ?? 'on-site');
        $urgent = isset($_POST['urgent']) ? 1 : 0;
        $featured = isset($_POST['featured']) ? 1 : 0;
        $requirements = Security::sanitizeInput($_POST['requirements'] ?? '');
        $benefits = Security::sanitizeInput($_POST['benefits'] ?? '');
        $skills_required = Security::sanitizeInput($_POST['skills_required'] ?? '');
        $experience_level = Security::sanitizeInput($_POST['experience_level'] ?? 'mid');
        $education_level = Security::sanitizeInput($_POST['education_level'] ?? 'any');
        $application_deadline = Security::sanitizeInput($_POST['application_deadline'] ?? '');
        $telephone = Security::sanitizeInput($_POST['telephone'] ?? '');
        $email = Security::sanitizeInput($_POST['email'] ?? '');
        
        // Validation
        if (empty($titre)) {
            throw new Exception('Le titre du poste est requis');
        }
        
        if (empty($description)) {
            throw new Exception('La description du poste est requise');
        }
        
        if ($domaine_id <= 0) {
            throw new Exception('Veuillez sélectionner un domaine');
        }
        
        if ($ville_id <= 0) {
            throw new Exception('Veuillez sélectionner une ville');
        }
        
        if ($contrat_id <= 0) {
            throw new Exception('Veuillez sélectionner un type de contrat');
        }
        
        if ($salary_min > 0 && $salary_max > 0 && $salary_min > $salary_max) {
            throw new Exception('Le salaire minimum ne peut pas être supérieur au salaire maximum');
        }
        
        // Get employer profile ID
        $employer_profile = $db->fetch("SELECT id FROM profiles WHERE user_id = ?", [$user_id]);
        if (!$employer_profile) {
            throw new Exception('Profil employeur non trouvé');
        }
        
        // Insert job posting
        $job_id = $db->insert("INSERT INTO annonces (
            titre, description, domaine_id, ville_id, contrat_id, profile_id,
            salary_min, salary_max, job_type, remote_work, urgent, featured,
            requirements, benefits, skills_required, experience_level, education_level,
            application_deadline, telephone, email, company_name, company_description, 
            status, date_a
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())", [
            $titre, $description, $domaine_id, $ville_id, $contrat_id, $employer_profile['id'],
            $salary_min, $salary_max, $job_type, $remote_work, $urgent, $featured,
            $requirements, $benefits, $skills_required, $experience_level, $education_level,
            $application_deadline, $telephone, $email, $employer['company_name'],
            $employer['company_description']
        ]);
        
        // Handle job image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_result = Security::validateFileUpload($_FILES['image'], ['jpg', 'jpeg', 'png'], 5 * 1024 * 1024);
            if ($upload_result['valid']) {
                $filename = Security::generateSecureFilename($_FILES['image']['name'], 'job_');
                $upload_path = '../upload/' . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $db->query("UPDATE annonces SET image = ? WHERE id = ?", [$filename, $job_id]);
                }
            }
        }
        
        $success = true;
        $success_message = 'Offre d\'emploi publiée avec succès !';
        
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

// Get dropdown data
$domaines = $db->fetchAll("SELECT * FROM domaines ORDER BY nom");
$villes = $db->fetchAll("SELECT * FROM villes ORDER BY nom");
$contrats = $db->fetchAll("SELECT * FROM contrats ORDER BY nom");

// Generate CSRF token
$csrf_token = Security::generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier une Offre - <?= htmlspecialchars($employer['company_name']) ?> | EMPLOIDB</title>
    <!-- Favicon -->
    <link rel="icon" href="../frontoffice/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Quill Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

    <!-- EMPLOIDB Professional Design System -->
    <link href="../assets/css/emploidb-design-system.css" rel="stylesheet">
    
    
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
    <div class="employer-wrapper">
        <!-- Include Enhanced Employer Sidebar -->
        <?php include 'includes/employer_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="employer-content" id="employerContent">
            <!-- Top Bar -->
            <div class="employer-header">
                <div class="d-flex justify-content-between align-items-center"><div><h1 class="emploidb-text-3xl emploidb-font-black emploidb-text-primary mb-2">Publier une offre d'emploi</h1><p class="emploidb-text-secondary mb-0">Page description</p></div><div class="d-flex gap-3">
                    <a href="dashboard.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i>
                        Retour au tableau de bord
                    </a>
                </div>
            </div>

        <!-- Post Job Content -->\n            <div class="container-fluid section-spacing">\n                <div class="post-job-content">
            <div class="post-job-card">
                <div class="card-header">
                    <h3 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Créer une nouvelle offre d'emploi
                    </h3>
                    <p class="mb-0 mt-2 opacity-75">Remplissez les informations ci-dessous pour publier votre offre</p>
                </div>
                
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Erreurs :</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong><?= htmlspecialchars($success_message) ?></strong>
                            <p class="mb-0 mt-2">Votre offre d'emploi a été publiée et est maintenant visible par les candidats.</p>
                            <div class="d-flex gap-2 mt-3">
                                <a href="dashboard.php" class="btn btn-primary">Retour au tableau de bord</a>
                                <a href="applications.php" class="btn btn-outline-primary">Gérer les candidatures</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            
                            <h4 class="section-title">
                                <i class="fas fa-info-circle me-2"></i>
                                Informations du poste
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group element-spacing">
                                        <label for="titre" class="form-label">Titre du poste *</label>
                                        <input type="text" class="form-control" id="titre" name="titre" 
                                               value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required
                                               placeholder="Ex: Développeur Full Stack, Chef de Projet, etc.">
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group element-spacing">
                                        <label for="domaine_id" class="form-label">Domaine *</label>
                                        <select class="form-select" id="domaine_id" name="domaine_id" required>
                                            <option value="">Sélectionner un domaine</option>
                                            <?php foreach ($domaines as $domaine): ?>
                                                <option value="<?= $domaine['id'] ?>" 
                                                        <?= (isset($_POST['domaine_id']) && $_POST['domaine_id'] == $domaine['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($domaine['nom']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group element-spacing">
                                <label for="description" class="form-label">Description du poste *</label>
                                <textarea class="form-control" id="description" name="description" required
                                          placeholder="Décrivez en détail le poste, les responsabilités, les missions..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group element-spacing">
                                        <label for="ville_id" class="form-label">Ville *</label>
                                        <select class="form-select" id="ville_id" name="ville_id" required>
                                            <option value="">Sélectionner une ville</option>
                                            <?php foreach ($villes as $ville): ?>
                                                <option value="<?= $ville['id'] ?>" 
                                                        <?= (isset($_POST['ville_id']) && $_POST['ville_id'] == $ville['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($ville['nom']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group element-spacing">
                                        <label for="contrat_id" class="form-label">Type de contrat *</label>
                                        <select class="form-select" id="contrat_id" name="contrat_id" required>
                                            <option value="">Sélectionner un contrat</option>
                                            <?php foreach ($contrats as $contrat): ?>
                                                <option value="<?= $contrat['id'] ?>" 
                                                        <?= (isset($_POST['contrat_id']) && $_POST['contrat_id'] == $contrat['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($contrat['nom']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group element-spacing">
                                        <label for="job_type" class="form-label">Type d'emploi</label>
                                        <select class="form-select" id="job_type" name="job_type">
                                            <option value="full-time" <?= (isset($_POST['job_type']) && $_POST['job_type'] === 'full-time') ? 'selected' : '' ?>>Temps plein</option>
                                            <option value="part-time" <?= (isset($_POST['job_type']) && $_POST['job_type'] === 'part-time') ? 'selected' : '' ?>>Temps partiel</option>
                                            <option value="contract" <?= (isset($_POST['job_type']) && $_POST['job_type'] === 'contract') ? 'selected' : '' ?>>Contrat</option>
                                            <option value="internship" <?= (isset($_POST['job_type']) && $_POST['job_type'] === 'internship') ? 'selected' : '' ?>>Stage</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <h4 class="section-title">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Rémunération
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group element-spacing">
                                        <label for="salary_min" class="form-label">Salaire minimum (MAD)</label>
                                        <input type="number" class="form-control" id="salary_min" name="salary_min" 
                                               value="<?= htmlspecialchars($_POST['salary_min'] ?? '') ?>" 
                                               placeholder="Ex: 8000">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group element-spacing">
                                        <label for="salary_max" class="form-label">Salaire maximum (MAD)</label>
                                        <input type="number" class="form-control" id="salary_max" name="salary_max" 
                                               value="<?= htmlspecialchars($_POST['salary_max'] ?? '') ?>" 
                                               placeholder="Ex: 12000">
                                    </div>
                                </div>
                            </div>
                            
                            <h4 class="section-title">
                                <i class="fas fa-tasks me-2"></i>
                                Exigences et compétences
                            </h4>
                            
                            <div class="form-group element-spacing">
                                <label for="requirements" class="form-label">Exigences</label>
                                <textarea class="form-control" id="requirements" name="requirements" 
                                          placeholder="Listez les exigences pour ce poste..."><?= htmlspecialchars($_POST['requirements'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group element-spacing">
                                <label for="skills_required" class="form-label">Compétences requises</label>
                                <textarea class="form-control" id="skills_required" name="skills_required" 
                                          placeholder="Ex: PHP, MySQL, JavaScript, Git..."><?= htmlspecialchars($_POST['skills_required'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group element-spacing">
                                        <label for="experience_level" class="form-label">Niveau d'expérience</label>
                                        <select class="form-select" id="experience_level" name="experience_level">
                                            <option value="entry" <?= (isset($_POST['experience_level']) && $_POST['experience_level'] === 'entry') ? 'selected' : '' ?>>Débutant</option>
                                            <option value="mid" <?= (isset($_POST['experience_level']) && $_POST['experience_level'] === 'mid') ? 'selected' : '' ?>>Intermédiaire</option>
                                            <option value="senior" <?= (isset($_POST['experience_level']) && $_POST['experience_level'] === 'senior') ? 'selected' : '' ?>>Senior</option>
                                            <option value="expert" <?= (isset($_POST['experience_level']) && $_POST['experience_level'] === 'expert') ? 'selected' : '' ?>>Expert</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group element-spacing">
                                        <label for="education_level" class="form-label">Niveau d'éducation</label>
                                        <select class="form-select" id="education_level" name="education_level">
                                            <option value="any" <?= (isset($_POST['education_level']) && $_POST['education_level'] === 'any') ? 'selected' : '' ?>>Tous niveaux</option>
                                            <option value="bac" <?= (isset($_POST['education_level']) && $_POST['education_level'] === 'bac') ? 'selected' : '' ?>>Bac</option>
                                            <option value="bac+2" <?= (isset($_POST['education_level']) && $_POST['education_level'] === 'bac+2') ? 'selected' : '' ?>>Bac+2</option>
                                            <option value="bac+3" <?= (isset($_POST['education_level']) && $_POST['education_level'] === 'bac+3') ? 'selected' : '' ?>>Bac+3</option>
                                            <option value="bac+4" <?= (isset($_POST['education_level']) && $_POST['education_level'] === 'bac+4') ? 'selected' : '' ?>>Bac+4</option>
                                            <option value="bac+5" <?= (isset($_POST['education_level']) && $_POST['education_level'] === 'bac+5') ? 'selected' : '' ?>>Bac+5</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <h4 class="section-title">
                                <i class="fas fa-gift me-2"></i>
                                Avantages et conditions
                            </h4>
                            
                            <div class="form-group element-spacing">
                                <label for="benefits" class="form-label">Avantages</label>
                                <textarea class="form-control" id="benefits" name="benefits" 
                                          placeholder="Listez les avantages offerts (mutuelle, tickets restaurant, etc.)..."><?= htmlspecialchars($_POST['benefits'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group element-spacing">
                                        <label for="remote_work" class="form-label">Mode de travail</label>
                                        <select class="form-select" id="remote_work" name="remote_work">
                                            <option value="on-site" <?= (isset($_POST['remote_work']) && $_POST['remote_work'] === 'on-site') ? 'selected' : '' ?>>Sur site</option>
                                            <option value="hybrid" <?= (isset($_POST['remote_work']) && $_POST['remote_work'] === 'hybrid') ? 'selected' : '' ?>>Hybride</option>
                                            <option value="remote" <?= (isset($_POST['remote_work']) && $_POST['remote_work'] === 'remote') ? 'selected' : '' ?>>Télétravail</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group element-spacing">
                                        <label for="application_deadline" class="form-label">Date limite de candidature</label>
                                        <input type="date" class="form-control" id="application_deadline" name="application_deadline" 
                                               value="<?= htmlspecialchars($_POST['application_deadline'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group element-spacing">
                                        <label for="telephone" class="form-label">Téléphone de contact</label>
                                        <input type="tel" class="form-control" id="telephone" name="telephone" 
                                               value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>" 
                                               placeholder="Ex: +212 6 12 34 56 78">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group element-spacing">
                                        <label for="email" class="form-label">Email de contact</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                                               placeholder="Ex: recrutement@entreprise.com">
                                    </div>
                                </div>
                            </div>
                            
                            <h4 class="section-title">
                                <i class="fas fa-image me-2"></i>
                                Image de l'offre (optionnel)
                            </h4>
                            
                            <div class="form-group element-spacing">
                                <div class="file-upload-wrapper">
                                    <label for="image" class="file-upload-label">
                                        <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-muted"></i>
                                        <div class="text-muted">
                                            <strong>Cliquez pour sélectionner une image</strong><br>
                                            <small>Formats acceptés: JPG, PNG (Max: 5MB)</small>
                                        </div>
                                    </label>
                                    <input type="file" id="image" name="image" accept="image/jpeg,image/png">
                                </div>
                            </div>
                            
                            <h4 class="section-title">
                                <i class="fas fa-star me-2"></i>
                                Options d'affichage
                            </h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="urgent" name="urgent" 
                                               <?= (isset($_POST['urgent']) && $_POST['urgent']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="urgent">
                                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                            Offre urgente
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="featured" name="featured" 
                                               <?= (isset($_POST['featured']) && $_POST['featured']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="featured">
                                            <i class="fas fa-star text-warning me-2"></i>
                                            Offre en vedette
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end mt-4">
                                <a href="dashboard.php" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-times me-2"></i>
                                    Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Publier l'offre
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div></div></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
