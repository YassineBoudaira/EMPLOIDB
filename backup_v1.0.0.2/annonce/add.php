<?php include '../include/session.php'; ?>
<?php include '../include/connexion.php'; ?>

<?php
if(isset($_POST['submit'])){
    // Verify CSRF token
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request";
    } else {
        $titre = Security::sanitizeInput($_POST['titre'] ?? '', 'string');
        $date_a = Security::sanitizeInput($_POST['date_a'] ?? '', 'string');
        $description = Security::sanitizeInput($_POST['description'] ?? '', 'string');
        $telephone = Security::sanitizeInput($_POST['telephone'] ?? '', 'string');
        $email = Security::sanitizeInput($_POST['email'] ?? '', 'email');
        $entreprise = Security::sanitizeInput($_POST['entreprise'] ?? '', 'string');
        $entreprise_detaile = Security::sanitizeInput($_POST['entreprise_detaile'] ?? '', 'string');
        $siteweb = Security::sanitizeInput($_POST['siteweb'] ?? '', 'url');
        $date_fin = Security::sanitizeInput($_POST['date_fin'] ?? '', 'string');
        $profile_id = Security::sanitizeInput($_POST['profile_id'] ?? '', 'int');
        $contrat_id = Security::sanitizeInput($_POST['contrat_id'] ?? '', 'int');
        $ville_id = Security::sanitizeInput($_POST['ville_id'] ?? '', 'int');
        $domaine_id = Security::sanitizeInput($_POST['domaine_id'] ?? '', 'int');
        
        // Validate required fields
        if (empty($titre) || empty($description) || empty($email) || empty($entreprise)) {
            $error = "Please fill all required fields";
        } else {
            // Validate email
            if (!Security::validateEmail($email)) {
                $error = "Please enter a valid email address";
            } else {
                // Validate IDs
                if (!$profile_id || !$contrat_id || !$ville_id || !$domaine_id || 
                    !Security::validateInt($profile_id) || !Security::validateInt($contrat_id) || 
                    !Security::validateInt($ville_id) || !Security::validateInt($domaine_id)) {
                    $error = "Invalid selection for profile, contract, city, or domain";
                } else {
                    try {
                        $image = '';
                        
                        // Handle file upload
                        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                            if (Security::validateFileUpload($_FILES['image'], ['jpg', 'jpeg', 'png', 'gif'], 5242880)) { // 5MB max
                                $image = Security::generateSecureFilename($_FILES['image']['name']);
                                $path = '../upload/' . $image;
                                
                                if (!move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
                                    throw new Exception("File upload failed");
                                }
                            } else {
                                $error = "Invalid file type or size. Allowed: JPG, PNG, GIF up to 5MB";
                            }
                        }
                        
                        if (!isset($error)) {
                            $db->insert("INSERT INTO annonces (titre, date_a, description, image, telephone, email, entreprise, entreprise_detaile, siteweb, date_fin, profile_id, contrat_id, ville_id, domaine_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
                                       [$titre, $date_a, $description, $image, $telephone, $email, $entreprise, $entreprise_detaile, $siteweb, $date_fin, $profile_id, $contrat_id, $ville_id, $domaine_id]);
                            
                            Security::redirect('../annonce/index.php', 'Job added successfully', 'success');
                        }
                    } catch (Exception $e) {
                        $error = "Failed to add job";
                    }
                }
            }
        }
    }
}

// Get data for dropdowns
$profiles = $db->fetchAll("SELECT id, nom, prenom FROM profiles");
$contrats = $db->fetchAll("SELECT id, nom FROM contrats");
$villes = $db->fetchAll("SELECT id, nom FROM villes");
$domaines = $db->fetchAll("SELECT id, nom FROM domaines");
?>

<?php include '../include/header.php'; ?>
<header>
  <!-- Start menu -->
  <?php include '../include/menu.php'; ?>
  <!-- End menu -->
</header>
<section>
  <!-- Start Sidebar -->
  <?php include '../include/sidebar.php'; ?>
  <!-- End Sidebar -->
  <div class="mainpanel">
    <div class="contentpanel">
      <div class="row">
            <h3>Ajouter une offre d'emploi</h3>
            <div class="card">
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                        
                        <div class="form-group">
                            <label for="titre">Titre *</label>
                            <input type="text" name="titre" id="titre" class="form-control" placeholder="Titre du poste" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_a">Date de publication *</label>
                            <input type="date" name="date_a" id="date_a" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Image</label>
                            <input type="file" name="image" id="image" class="form-control" accept="image/*">
                            <small class="form-text text-muted">Allowed: JPG, PNG, GIF up to 5MB</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea name="description" id="description" class="form-control" rows="5" placeholder="Description du poste" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="text" name="telephone" id="telephone" class="form-control" placeholder="Téléphone">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="entreprise">Entreprise *</label>
                            <input type="text" name="entreprise" id="entreprise" class="form-control" placeholder="Nom de l'entreprise" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="entreprise_detaile">Détails de l'entreprise</label>
                            <textarea name="entreprise_detaile" id="entreprise_detaile" class="form-control" rows="3" placeholder="Détails de l'entreprise"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="siteweb">Site web</label>
                            <input type="url" name="siteweb" id="siteweb" class="form-control" placeholder="Site web">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_fin">Date de fin *</label>
                            <input type="date" name="date_fin" id="date_fin" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="profile_id">Profil *</label>
                            <select name="profile_id" id="profile_id" class="form-control" required>
                                <option value="">Sélectionner un profil</option>
                                <?php foreach($profiles as $profile): ?>
                                    <option value="<?= htmlspecialchars($profile['id']) ?>">
                                        <?= htmlspecialchars($profile['nom'] . ' ' . $profile['prenom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="contrat_id">Type de contrat *</label>
                            <select name="contrat_id" id="contrat_id" class="form-control" required>
                                <option value="">Sélectionner un type de contrat</option>
                                <?php foreach($contrats as $contrat): ?>
                                    <option value="<?= htmlspecialchars($contrat['id']) ?>">
                                        <?= htmlspecialchars($contrat['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="ville_id">Ville *</label>
                            <select name="ville_id" id="ville_id" class="form-control" required>
                                <option value="">Sélectionner une ville</option>
                                <?php foreach($villes as $ville): ?>
                                    <option value="<?= htmlspecialchars($ville['id']) ?>">
                                        <?= htmlspecialchars($ville['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="domaine_id">Domaine *</label>
                            <select name="domaine_id" id="domaine_id" class="form-control" required>
                                <option value="">Sélectionner un domaine</option>
                                <?php foreach($domaines as $domaine): ?>
                                    <option value="<?= htmlspecialchars($domaine['id']) ?>">
                                        <?= htmlspecialchars($domaine['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <button name="submit" type="submit" class="btn btn-outline-success">Ajouter</button>
                            <a href="../annonce/index.php" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        
      </div><!-- row -->
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->

</section>
<?php include '../include/footer.php'; ?>