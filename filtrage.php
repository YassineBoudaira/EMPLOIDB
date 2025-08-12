<?php 
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';
include 'frontoffice/include/header2.php'; 
?>

<body>

<?php   
// Simple input validation
$keyword = trim($_GET['keyword'] ?? '');
$domaine_id = intval($_GET['idd'] ?? 0);
$ville_id = intval($_GET['idv'] ?? 0);

// Build search conditions
$conditions = [];
$params = [];
$search_title = "Résultats de recherche";

if (!empty($keyword)) {
    $conditions[] = "(titre LIKE ? OR description LIKE ? OR entreprise LIKE ?)";
    $keyword_param = "%$keyword%";
    $params[] = $keyword_param;
    $params[] = $keyword_param;
    $params[] = $keyword_param;
    $search_title .= " pour '$keyword'";
}

if ($domaine_id > 0) {
    $conditions[] = "domaine_id = ?";
    $params[] = $domaine_id;
    $domaine_data = $db->fetch("SELECT nom FROM domaines WHERE id = ?", [$domaine_id]);
    if ($domaine_data) {
        $search_title .= " dans le domaine '" . $domaine_data['nom'] . "'";
    }
}

if ($ville_id > 0) {
    $conditions[] = "ville_id = ?";
    $params[] = $ville_id;
    $ville_data = $db->fetch("SELECT nom FROM villes WHERE id = ?", [$ville_id]);
    if ($ville_data) {
        $search_title .= " à '" . $ville_data['nom'] . "'";
    }
}

// If no search criteria provided, show all results
if (empty($conditions)) {
    $sql = "SELECT * FROM annonces ORDER BY id DESC";
    $search_title = "Toutes les offres d'emploi";
} else {
    // Build the SQL query
    $sql = "SELECT * FROM annonces WHERE " . implode(' AND ', $conditions) . " ORDER BY id DESC";
}
?>

    <div class="container-fluid bg-white p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->

        
        <!-- Navbar End -->
        <?php include 'frontoffice/include/menu2.php'; ?>
        <!-- Header End -->


        <!-- Header End -->
        <div class="container-fluid py-5 bg-dark page-header mb-5">
            <div class="container my-5 pt-5 pb-4">
                <h1 class="display-3 text-white mb-3 animated slideInDown">Recherche d'emploi</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb text-uppercase">
                        <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">Recherche</li>
                    </ol>
                </nav>
            </div>
        </div>
        <!-- Header End -->


        <!-- Start search form -->
        <div class="container-fluid bg-primary mb-5 wow fadeIn" data-wow-delay="0.1s" style="padding: 35px;">
            <div class="container">
                <form action="filtrage.php" method="GET">
                    <div class="row g-2">
                        <div class="col-md-10">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" class="form-control border-0" placeholder="Mot Clé" />
                                </div>
                                <div class="col-md-4">
                                    <select name="idd" class="form-select border-0">
                                        <option value="">Metiers et Domaines</option>
                                        <?php 
                                        $domaines = $db->fetchAll("SELECT * FROM domaines");
                                        foreach($domaines as $datad):
                                            $selected = ($datad['id'] == $domaine_id) ? 'selected' : '';
                                        ?>
                                        <option value="<?= htmlspecialchars($datad['id']) ?>" <?= $selected ?>><?= htmlspecialchars($datad['nom']) ?></option>
                                        <?php endforeach;?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select name="idv" class="form-select border-0">
                                        <option value="">villes</option>
                                        <?php 
                                        $villes = $db->fetchAll("SELECT * FROM villes");
                                        foreach($villes as $datav):
                                            $selected = ($datav['id'] == $ville_id) ? 'selected' : '';
                                        ?>
                                        <option value="<?= htmlspecialchars($datav['id']) ?>" <?= $selected ?>><?= htmlspecialchars($datav['nom']) ?></option>
                                        <?php endforeach;?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-dark border-0 w-100">Rechercher</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- End search form -->
        
        <!-- Jobs Start -->
        <div class="container-xxl py-5">
            <div class="container">
                <h1 class="text-center mb-5 wow fadeInUp" data-wow-delay="0.1s"><?= htmlspecialchars($search_title) ?></h1>
            
                <div class="tab-class text-center wow fadeInUp" data-wow-delay="0.3s">
                    <div class="tab-content">
                        <div id="tab-1" class="tab-pane fade show p-0 active">
                            <!-- Start Annonce -->
                            <?php
                            try {
                                // Execute the search query
                                $annonces = $db->fetchAll($sql, $params);
                                
                                if (empty($annonces)) {
                                    echo '<div class="text-center py-5">';
                                    echo '<h3 class="text-muted">Aucun résultat trouvé</h3>';
                                    echo '<p class="text-muted">Essayez de modifier vos critères de recherche</p>';
                                    echo '<a href="index.php" class="btn btn-primary">Retour à l\'accueil</a>';
                                    echo '</div>';
                                } else {
                                    echo '<p class="text-center mb-4"><strong>' . count($annonces) . '</strong> offre(s) trouvée(s)</p>';
                                    
                                    foreach($annonces as $data):
                                        $profile = $data['profile_id'];
                                        $data1 = $db->fetch("SELECT * FROM profiles WHERE id = ?", [$profile]);

                                        $contrat = $data['contrat_id'];
                                        $data2 = $db->fetch("SELECT * FROM contrats WHERE id = ?", [$contrat]);

                                        $ville = $data['ville_id'];
                                        $data3 = $db->fetch("SELECT * FROM villes WHERE id = ?", [$ville]);

                                        $domaine = $data['domaine_id'];
                                        $data4 = $db->fetch("SELECT * FROM domaines WHERE id = ?", [$domaine]);
                            ?>
                            <div class="job-item p-4 mb-4">
                                <div class="row g-4">
                                    <div class="col-sm-12 col-md-8 d-flex align-items-center">
                                        <img class="flex-shrink-0 me-3" src="img/com-logo-1.jpg" alt="">
                                        <div class="text-start ps-4">
                                            <h5 class="mb-3"><?= htmlspecialchars($data['titre']) ?></h5>
                                            <span class="text-truncate me-3"><i class="fa fa-map-marker-alt text-primary me-2"></i><?= htmlspecialchars($data3['nom']) ?></span>
                                            <span class="text-truncate me-3"><i class="far fa-clock text-primary me-2"></i><?= htmlspecialchars($data1['nom']) ?></span>
                                            <span class="text-truncate me-0"><i class="far fa-money-bill-alt text-primary me-2"></i><?= htmlspecialchars($data2['nom']) ?></span>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-4 d-flex flex-column align-items-start align-items-md-end justify-content-center">
                                        <div class="d-flex mb-3">
                                            <a class="btn btn-light btn-square me-3" href=""><i class="far fa-heart text-primary"></i></a>
                                            <a class="btn btn-primary" href="annoncedetaile.php?ida=<?= $data['id'] ?>">Afficher les détails</a>
                                        </div>
                                        <small class="text-truncate"><i class="far fa-calendar-alt text-primary me-2"></i> Date: <?= htmlspecialchars($data['date_a']) ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php
                                    endforeach;
                                }
                            } catch (Exception $e) {
                                echo '<div class="text-center py-5">';
                                echo '<h3 class="text-danger">Erreur lors de la recherche</h3>';
                                echo '<p class="text-muted">Une erreur s\'est produite. Veuillez réessayer.</p>';
                                echo '<a href="index.php" class="btn btn-primary">Retour à l\'accueil</a>';
                                echo '</div>';
                            }
                            ?>
                            <!-- End Annonce -->
                            <a class="btn btn-primary py-3 px-5" href="index.php">Voir plus d'offres</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Jobs End -->

<?php include 'frontoffice/include/footer2.php'; ?>