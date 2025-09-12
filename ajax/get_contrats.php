<?php
// AJAX Handler: Get Contracts Data
header('Content-Type: application/json');
include '../include/config.php';
include '../include/connexion.php';

try {
    $contrats = $db->fetchAll("SELECT id, nom FROM contrats ORDER BY nom ASC");
    
    echo json_encode([
        'success' => true,
        'contrats' => $contrats
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des contrats'
    ]);
}
?>
