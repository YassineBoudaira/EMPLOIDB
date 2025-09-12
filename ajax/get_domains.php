<?php
// AJAX Handler: Get Domains Data
header('Content-Type: application/json');
include '../include/config.php';
include '../include/connexion.php';

try {
    $domains = $db->fetchAll("SELECT id, nom FROM domaines ORDER BY nom ASC");
    
    echo json_encode([
        'success' => true,
        'domains' => $domains
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des domaines'
    ]);
}
?>
