<?php include '../include/session.php'; ?>
<?php include '../include/connexion.php'; ?>

<?php
// Secure input validation
$id = Security::sanitizeInput($_GET['id'] ?? '', 'int');

// Validate that the parameter is provided and is an integer
if (!$id || !Security::validateInt($id)) {
    Security::redirect('../profile/index.php', 'Invalid profile ID', 'error');
}

// Get profile data securely
$datap = $db->fetch("SELECT * FROM profiles WHERE id = ?", [$id]);

// Check if profile exists
if (!$datap) {
    Security::redirect('../profile/index.php', 'Profile not found', 'error');
}

if(isset($_POST['submit'])){
    // Verify CSRF token
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid request";
    } else {
        $nom = Security::sanitizeInput($_POST['nom'] ?? '', 'string');
        $prenom = Security::sanitizeInput($_POST['prenom'] ?? '', 'string');
        $telephone = Security::sanitizeInput($_POST['telephone'] ?? '', 'string');
        $adresse = Security::sanitizeInput($_POST['adresse'] ?? '', 'string');
        $date_n = Security::sanitizeInput($_POST['date_n'] ?? '', 'string');
        $user_id = Security::sanitizeInput($_POST['user_id'] ?? '', 'int');
        $ville_id = Security::sanitizeInput($_POST['ville_id'] ?? '', 'int');
        
        // Validate required fields
        if (empty($nom) || empty($prenom) || empty($telephone) || empty($adresse) || empty($date_n)) {
            $error = "Please fill all required fields";
        } else {
            // Validate IDs
            if (!$user_id || !$ville_id || !Security::validateInt($user_id) || !Security::validateInt($ville_id)) {
                $error = "Invalid user or city selection";
            } else {
                try {
                    $db->update("UPDATE profiles SET nom = ?, prenom = ?, telephone = ?, adresse = ?, date_n = ?, user_id = ?, ville_id = ? WHERE id = ?", 
                               [$nom, $prenom, $telephone, $adresse, $date_n, $user_id, $ville_id, $id]);
                    
                    Security::redirect('../profile/index.php', 'Profile updated successfully', 'success');
                } catch (Exception $e) {
                    $error = "Failed to update profile";
                }
            }
        }
    }
}

// Get users and cities for dropdowns
$users = $db->fetchAll("SELECT id, user FROM users");
$villes = $db->fetchAll("SELECT id, nom FROM villes");
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
            <h3>Modifier un profil</h3>
            <div class="card">
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                        
                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input value="<?= htmlspecialchars($datap['nom']) ?>" type="text" name="nom" id="nom" class="form-control" placeholder="Nom" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="prenom">Prénom</label>
                            <input value="<?= htmlspecialchars($datap['prenom']) ?>" type="text" name="prenom" id="prenom" class="form-control" placeholder="Prénom" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input value="<?= htmlspecialchars($datap['telephone']) ?>" type="text" name="telephone" id="telephone" class="form-control" placeholder="Téléphone" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="adresse">Adresse</label>
                            <input value="<?= htmlspecialchars($datap['adresse']) ?>" type="text" name="adresse" id="adresse" class="form-control" placeholder="Adresse" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="date_n">Date de naissance</label>
                            <input value="<?= htmlspecialchars($datap['date_n']) ?>" type="date" name="date_n" id="date_n" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="user_id">Utilisateur</label>
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">Sélectionner un utilisateur</option>
                                <?php foreach($users as $user): ?>
                                    <option value="<?= htmlspecialchars($user['id']) ?>" <?= $datap['user_id'] == $user['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['user']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="ville_id">Ville</label>
                            <select name="ville_id" id="ville_id" class="form-control" required>
                                <option value="">Sélectionner une ville</option>
                                <?php foreach($villes as $ville): ?>
                                    <option value="<?= htmlspecialchars($ville['id']) ?>" <?= $datap['ville_id'] == $ville['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ville['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <button name="submit" type="submit" class="btn btn-outline-warning">Modifier</button>
                            <a href="../profile/index.php" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        
      </div><!-- row -->
    </div><!-- contentpanel -->
  </div><!-- mainpanel -->

</section>
<?php include '../include/footer.php'; ?>