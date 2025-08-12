<?php
include 'include/sess.php';
include 'include/connexion.php';

echo "<h2>Database Data Check</h2>";

// Check annonces table
echo "<h3>Annonces Table</h3>";
try {
    $annonces = $db->fetchAll("SELECT * FROM annonces LIMIT 5");
    echo "Total annonces: " . count($db->fetchAll("SELECT * FROM annonces")) . "<br>";
    if (!empty($annonces)) {
        echo "Sample annonces:<br>";
        foreach ($annonces as $annonce) {
            echo "- ID: " . $annonce['id'] . " | Titre: " . htmlspecialchars($annonce['titre']) . " | Domaine: " . $annonce['domaine_id'] . " | Ville: " . $annonce['ville_id'] . "<br>";
        }
    } else {
        echo "No annonces found in database<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<br>";

// Check domaines table
echo "<h3>Domaines Table</h3>";
try {
    $domaines = $db->fetchAll("SELECT * FROM domaines");
    echo "Total domaines: " . count($domaines) . "<br>";
    if (!empty($domaines)) {
        echo "All domaines:<br>";
        foreach ($domaines as $domaine) {
            echo "- ID: " . $domaine['id'] . " | Nom: " . htmlspecialchars($domaine['nom']) . "<br>";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<br>";

// Check villes table
echo "<h3>Villes Table</h3>";
try {
    $villes = $db->fetchAll("SELECT * FROM villes");
    echo "Total villes: " . count($villes) . "<br>";
    if (!empty($villes)) {
        echo "All villes:<br>";
        foreach ($villes as $ville) {
            echo "- ID: " . $ville['id'] . " | Nom: " . htmlspecialchars($ville['nom']) . "<br>";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<br>";
echo "<a href='index.php'>Return to homepage</a>";
?>
