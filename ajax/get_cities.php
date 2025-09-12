<?php
// AJAX Handler: Get Cities Data
header('Content-Type: application/json');
include '../include/config.php';
include '../include/connexion.php';

try {
    $cities = $db->fetchAll("SELECT id, nom FROM villes ORDER BY nom ASC");
    
    echo json_encode([
        'success' => true,
        'cities' => $cities
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la récupération des villes'
    ]);
}
?>
