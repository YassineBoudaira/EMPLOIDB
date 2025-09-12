<?php
include __DIR__ . '/../include/config.php';
include __DIR__ . '/../include/sess.php';
include __DIR__ . '/../include/connexion.php';

// Check if advertiser is logged in
if (!isset($_SESSION['advertiser_id'])) {
    header('Location: login.php');
    exit;
}

$advertiser_id = $_SESSION['advertiser_id'];
$success = false;
$errors = [];

// Get advertiser info
$advertiser = $db->fetch("SELECT * FROM advertisers WHERE id = ?", [$advertiser_id]);

// Get payment history
$payments = $db->fetchAll("
    SELECT * FROM advertiser_payments 
    WHERE advertiser_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
", [$advertiser_id]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? '';
    
    if ($amount <= 0) {
        $errors[] = "Le montant doit être supérieur à 0.";
    }
    
    if (empty($payment_method)) {
        $errors[] = "Veuillez sélectionner une méthode de paiement.";
    }
    
    if (empty($errors)) {
        try {
            $transaction_id = 'TXN_' . time() . '_' . $advertiser_id;
            
            $db->insert("
                INSERT INTO advertiser_payments (
                    advertiser_id, amount, payment_method, transaction_id, status, created_at
                ) VALUES (?, ?, ?, ?, 'pending', NOW())
            ", [$advertiser_id, $amount, $payment_method, $transaction_id]);
            
            $success = "Paiement initié avec succès! Vous recevrez une confirmation par email.";
        } catch (Exception $e) {
            $errors[] = "Erreur lors du traitement du paiement: " . $e->getMessage();
        }
    }
}

$page_title = "Paiements";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | EMPLOIDB</title>
    
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
    
    <!-- EMPLOIDB Professional Design System -->
    <link href="../assets/css/emploidb-design-system.css" rel="stylesheet">
    
    <style>
        /* Advertiser Panel Professional Styles */
        body {
            background: var(--emploidb-bg-secondary);
            font-family: var(--emploidb-font-primary);
            color: var(--emploidb-text-primary);
        }
        
        /* Advertiser Layout */
        .advertiser-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .emploidb-sidebar {
            width: 280px;
            background: var(--emploidb-bg-primary);
            border-right: 1px solid var(--emploidb-border-color);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--emploidb-border-color);
            background: var(--emploidb-gradient-primary);
        }
        
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--emploidb-white);
            font-weight: 700;
            font-size: 1.25rem;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .sidebar-nav .nav-link {
            color: var(--emploidb-text-secondary);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            border: none;
            background: transparent;
        }
        
        .sidebar-nav .nav-link:hover,
        .sidebar-nav .nav-link.active {
            color: var(--emploidb-primary);
            background: var(--emploidb-bg-hover);
            border-left: 3px solid var(--emploidb-primary);
        }
        
        .sidebar-nav .nav-link i {
            width: 20px;
            text-align: center;
        }
        
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--emploidb-border-color);
            background: var(--emploidb-bg-primary);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.5rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--emploidb-gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--emploidb-white);
        }
        
        .user-details {
            flex: 1;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--emploidb-text-primary);
        }
        
        .user-role {
            font-size: 0.8rem;
            color: var(--emploidb-text-secondary);
        }
        
        /* Main Content */
        .advertiser-main {
            flex: 1;
            margin-left: 280px;
            padding: 2rem;
            background: var(--emploidb-bg-secondary);
        }
        
        /* Content Cards */
        .content-card {
            background: var(--emploidb-white);
            border-radius: var(--emploidb-border-radius-lg);
            box-shadow: var(--emploidb-shadow-sm);
            border: 1px solid var(--emploidb-border-color);
            margin-bottom: 2rem;
        }
        
        .content-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--emploidb-border-color);
            background: var(--emploidb-bg-light);
            border-radius: var(--emploidb-border-radius-lg) var(--emploidb-border-radius-lg) 0 0;
        }
        
        .content-body {
            padding: 1.5rem;
        }
        
        /* Payment Cards */
        .payment-card {
            background: var(--emploidb-white);
            border-radius: var(--emploidb-border-radius-lg);
            padding: 2rem;
            box-shadow: var(--emploidb-shadow-sm);
            border: 1px solid var(--emploidb-border-color);
            margin-bottom: 2rem;
        }
        
        .balance-card {
            background: var(--emploidb-gradient-primary);
            color: var(--emploidb-white);
            border-radius: var(--emploidb-border-radius-lg);
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .balance-amount {
            font-size: 3rem;
            font-weight: 700;
            margin: 1rem 0;
        }
        
        .history-card {
            background: var(--emploidb-white);
            border-radius: var(--emploidb-border-radius-lg);
            padding: 1.5rem;
            box-shadow: var(--emploidb-shadow-sm);
            border: 1px solid var(--emploidb-border-color);
        }
        
        .payment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--emploidb-border-color);
        }
        
        .payment-item:last-child {
            border-bottom: none;
        }
        
        .payment-amount {
            font-weight: 600;
            color: var(--emploidb-text-primary);
            font-size: 1.1rem;
        }
        
        .payment-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .payment-date {
            color: var(--emploidb-text-secondary);
            font-size: 0.9rem;
        }
        
        .payment-method {
            color: var(--emploidb-text-secondary);
            font-size: 0.9rem;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            font-weight: 600;
            color: var(--emploidb-text-primary);
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .form-control, .form-select {
            border: 2px solid var(--emploidb-border-color);
            border-radius: var(--emploidb-border-radius);
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--emploidb-primary);
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
        }
        
        .btn-primary {
            background: var(--emploidb-gradient-primary);
            border: none;
            padding: 1rem 2rem;
            font-weight: 600;
            border-radius: var(--emploidb-border-radius);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--emploidb-shadow-md);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .emploidb-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .emploidb-sidebar.show {
                transform: translateX(0);
            }
            
            .advertiser-main {
                margin-left: 0;
                padding: 1rem;
            }
            
            .balance-amount {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="advertiser-wrapper">
        <!-- Sidebar -->
        <?php include 'include/advertiser_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="advertiser-main">
            <!-- Page Header -->
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-credit-card text-primary"></i>
                            Paiements
                        </h1>
                        <p class="text-muted mb-0">Gérez vos paiements et consultez votre solde</p>
                    </div>
                </div>
            </div>
            
            <div class="content-body">
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Payment Form -->
                        <div class="payment-card">
                            <h3 class="mb-4">
                                <i class="fas fa-plus-circle text-primary"></i>
                                Effectuer un Paiement
                            </h3>
                            
                            <form method="POST" class="payment-form">
                                <div class="form-group">
                                    <label for="amount">Montant (DH)</label>
                                    <input type="number" 
                                           id="amount" 
                                           name="amount" 
                                           class="form-control" 
                                           min="10" 
                                           step="0.01" 
                                           required 
                                           placeholder="Entrez le montant">
                                </div>
                                
                                <div class="form-group">
                                    <label for="payment_method">Méthode de paiement</label>
                                    <select id="payment_method" name="payment_method" class="form-select" required>
                                        <option value="">Sélectionner une méthode</option>
                                        <option value="card">Carte bancaire</option>
                                        <option value="paypal">PayPal</option>
                                        <option value="bank">Virement bancaire</option>
                                        <option value="mobile">Paiement mobile</option>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-lock me-2"></i>
                                    Payer en toute sécurité
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <!-- Balance Card -->
                        <div class="balance-card">
                            <h4><i class="fas fa-wallet"></i> Solde actuel</h4>
                            <div class="balance-amount">
                                <?php echo number_format($advertiser['balance'], 2); ?> DH
                            </div>
                            <p class="mb-0">Disponible pour vos campagnes</p>
                        </div>
                        
                        <!-- Payment History -->
                        <div class="history-card">
                            <h5 class="mb-3">
                                <i class="fas fa-history text-primary"></i>
                                Historique des paiements
                            </h5>
                            
                            <?php if (empty($payments)): ?>
                                <div class="text-center py-3">
                                    <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Aucun paiement effectué</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($payments as $payment): ?>
                                    <div class="payment-item">
                                        <div class="d-flex flex-column">
                                            <div class="payment-amount"><?php echo number_format($payment['amount'], 2); ?> DH</div>
                                            <div class="payment-method">
                                                <i class="fas fa-credit-card me-1"></i>
                                                <?php echo ucfirst($payment['payment_method']); ?>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column align-items-end">
                                            <span class="payment-status status-<?php echo $payment['status']; ?>">
                                                <?php echo ucfirst($payment['status']); ?>
                                            </span>
                                            <div class="payment-date">
                                                <?php echo date('d/m/Y', strtotime($payment['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="text-center mt-3">
                                    <a href="#" class="btn btn-outline-primary btn-sm">
                                        Voir tout l'historique
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Form validation
        document.querySelector('.payment-form').addEventListener('submit', function(e) {
            const amount = document.getElementById('amount').value;
            const method = document.getElementById('payment_method').value;
            
            if (!amount || amount <= 0) {
                e.preventDefault();
                alert('Veuillez entrer un montant valide.');
                return false;
            }
            
            if (!method) {
                e.preventDefault();
                alert('Veuillez sélectionner une méthode de paiement.');
                return false;
            }
        });
        
        // Auto-format amount input
        document.getElementById('amount').addEventListener('input', function(e) {
            let value = e.target.value;
            if (value && !isNaN(value)) {
                e.target.value = parseFloat(value).toFixed(2);
            }
        });
    </script>
</body>
</html>
