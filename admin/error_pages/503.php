<?php
http_response_code(503);
$page_title = 'Service Indisponible - EMPLOIDB';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .error-container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }
        .error-code {
            font-size: 120px;
            font-weight: 900;
            color: #feca57;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        .error-message {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .error-description {
            color: #666;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        .maintenance-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #feca57;
        }
        .btn-home {
            background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%);
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s ease;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            color: white;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">503</div>
        <div class="error-message">Service Temporairement Indisponible</div>
        <div class="error-description">
            Nous effectuons actuellement une maintenance programmée pour améliorer 
            nos services. Le site sera de nouveau disponible sous peu.
        </div>
        <div class="maintenance-info">
            <h6><i class="fas fa-tools me-2"></i>Maintenance en Cours</h6>
            <p class="mb-0">Durée estimée: 30 minutes</p>
            <small class="text-muted">Dernière mise à jour: <?= date('H:i') ?></small>
        </div>
        <a href="/" class="btn-home">
            <i class="fas fa-home me-2"></i>
            Retour à l'Accueil
        </a>
    </div>
</body>
</html>
