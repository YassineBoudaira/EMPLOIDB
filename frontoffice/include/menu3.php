<!-- Enhanced Professional Navbar Start -->

<nav class="navbar navbar-expand-lg navbar-emploidb sticky-top p-0" id="emploidbNavbar">
    <div class="container-fluid px-4">
        <a href="index.php" class="navbar-brand-emploidb d-flex align-items-center py-0">
            <div class="d-flex align-items-center">
                <i class="fas fa-briefcase me-2" style="font-size: 1.5rem; color: var(--emploidb-primary);"></i>
                <span>EMPLOI</span><span style="color: var(--emploidb-secondary);">DB</span>
            </div>
        </a>
        <button type="button" class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav mx-auto p-4 p-lg-0">
                    <a href="index.php" class="nav-link-emploidb <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                        <i class="fas fa-home me-1"></i>Accueil
                    </a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link-emploidb dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-th-large me-1"></i>Offres par Domaines
                        </a>
                            <div class="dropdown-menu rounded-0 m-0">

                                <!-- <a name="dropdown-item" href="category.php" class="" value="" >Safi </a> -->
                                
                            <?php
                            // Use secure database queries with prepared statements
                            $domaines = $db->fetchAll("SELECT * FROM domaines");
                            foreach($domaines as $data):
                            ?>
                      
                            <a name="dropdown-item" href="category.php?iddo=<?= htmlspecialchars($data['id']) ?>" class="dropdown-item" value="<?= htmlspecialchars($data['id']) ?>" ><?= htmlspecialchars($data['nom']) ?></a>

                            <?php endforeach;?>
                            </div>

                    </div>
                    <a href="enhanced_search.php" class="nav-link-emploidb <?= basename($_SERVER['PHP_SELF']) == 'enhanced_search.php' ? 'active' : '' ?>">
                        <i class="fas fa-search-plus me-1"></i>Recherche Avancée
                    </a>
                    <a href="consiel.php" class="nav-link-emploidb <?= basename($_SERVER['PHP_SELF']) == 'consiel.php' ? 'active' : '' ?>">
                        <i class="fas fa-graduation-cap me-1"></i>Conseil Carrière
                    </a>
                    <a href="cabinets de recrutements.php" class="nav-link-emploidb <?= basename($_SERVER['PHP_SELF']) == 'cabinets de recrutements.php' ? 'active' : '' ?>">
                        <i class="fas fa-building me-1"></i>Cabinets de Recrutement
                    </a>
                    <a href="offers.php" class="nav-link-emploidb <?= basename($_SERVER['PHP_SELF']) == 'offers.php' ? 'active' : '' ?>">
                        <i class="fas fa-gift me-1"></i>Offres & Bonnes Affaires
                    </a>
                    <a href="about.php" class="nav-link-emploidb <?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>">
                        <i class="fas fa-info-circle me-1"></i>À Propos
                    </a>
                    <a href="contact.php" class="nav-link-emploidb <?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>">
                        <i class="fas fa-envelope me-1"></i>Contact
                    </a>

                    <?php if (!Security::isLoggedIn()): ?>
                    <!-- Enhanced Login/Signup Dropdown -->
                     
                    <div class="nav-item dropdown ms-lg-3">
                        <a href="#" class="emploidb-btn emploidb-btn-primary dropdown-toggle" data-bs-toggle="dropdown" style="border-radius: var(--emploidb-radius-lg);">
                            <i class="fas fa-user me-2"></i>Connexion
                        </a>
                        <div class="dropdown-menu dropdown-menu-end rounded-0 m-0" style="min-width: 280px;">
                            <div class="dropdown-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas  me-2"></i>Se connecter</h6>
                            </div>
                            <div class="p-3">
                                <form action="login.php" method="POST" class="mb-3">
                                    <button href="login.php" type="submit" class="btn btn-primary btn-sm w-100">
                                        <i class="fas fa-sign-in-alt me-1"></i>Se connecter
                                    </button>
                                </form>

                            </div>
                            <div class="dropdown-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-user-plus me-2"></i>S'inscrire</h6>
                            </div>
                            <div class="p-3">
                                <div class="d-grid gap-2">
                                    <a href="signup.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-user me-1"></i>Candidat
                                    </a>
                                    <a href="employer/register.php" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-building me-1"></i>Employeur
                                    </a>
                                    <a href="login.php" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-user_admin me-1"></i>Administartion
                                    </a>

                                </div>
                                <hr class="my-2">
                                <div class="text-center">
                                    <small class="text-muted">Pas encore de compte?</small>
                    </div>
                            </div> 
                            
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Enhanced User Menu Dropdown -->
                    <div class="nav-item dropdown ms-lg-3">
                        <a href="#" class="emploidb-btn emploidb-btn-secondary dropdown-toggle" data-bs-toggle="dropdown" style="border-radius: var(--emploidb-radius-lg);">
                            <i class="fas fa-user-circle me-2"></i><?= htmlspecialchars($_SESSION['username'] ?? 'Mon Compte') ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" style="border-radius: var(--emploidb-radius-xl); box-shadow: var(--emploidb-shadow-lg); border: none; min-width: 280px;">
                            <div class="dropdown-header" style="background: var(--emploidb-gradient-primary); color: white; padding: 1rem; margin-bottom: 0.5rem;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle me-2" style="font-size: 1.2rem;"></i>
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($_SESSION['username'] ?? 'Utilisateur') ?></h6>
                                        <small style="opacity: 0.8;"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></small>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (Security::isAdmin()): ?>
                            <a href="admin/dashboard.php" class="dropdown-item py-2">
                                <i class="fas fa-tachometer-alt me-2 text-primary"></i>Administration
                            </a>
                            <div class="dropdown-divider"></div>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'employer'): ?>
                            <a href="employer/dashboard.php" class="dropdown-item py-2">
                                <i class="fas fa-building me-2 text-success"></i>Espace Employeur
                            </a>
                            <div class="dropdown-divider"></div>
                            <?php endif; ?>
                            <a href="user_profile.php" class="dropdown-item py-2">
                                <i class="fas fa-user-edit me-2 text-info"></i>Mon Profil
                            </a>
                            <a href="saved_jobs.php" class="dropdown-item py-2">
                                <i class="fas fa-heart me-2 text-danger"></i>Emplois Sauvegardés
                            </a>
                            <a href="application_tracking.php" class="dropdown-item py-2">
                                <i class="fas fa-clipboard-list me-2 text-warning"></i>Mes Candidatures
                            </a>
                            <a href="job_alerts.php" class="dropdown-item py-2">
                                <i class="fas fa-bell me-2 text-info"></i>Alertes Emploi
                            </a>
                            <a href="notifications.php" class="dropdown-item py-2 d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-bell me-2 text-warning"></i>Notifications
                                </span>
                                <?php 
                                if (isset($_SESSION['user_id'])) {
                                    try {
                                        $unread_count = $db->fetch("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND (read_at IS NULL OR read_at = '')", [$_SESSION['user_id']])['count'];
                                        if ($unread_count > 0): 
                                    ?>
                                        <span class="badge" style="background: var(--emploidb-gradient-accent); color: white; border-radius: var(--emploidb-radius-full);"><?= $unread_count ?></span>
                                    <?php 
                                        endif;
                                    } catch (Exception $e) {
                                        // If notifications table doesn't exist or has different structure, just continue
                                    }
                                }
                                ?>
                            </a>
                            <a href="analytics_dashboard.php" class="dropdown-item py-2">
                                <i class="fas fa-chart-bar me-2 text-success"></i>Analytics & Statistiques
                            </a>
                            
                            <div class="dropdown-divider"></div>
                            <a href="change_password.php" class="dropdown-item py-2">
                                <i class="fas fa-key me-2 text-secondary"></i>Changer le Mot de Passe
                            </a>
                            
                            <div class="dropdown-divider"></div>
                            
                            <a href="logout.php" class="dropdown-item py-2" style="color: var(--emploidb-error);">
                                <i class="fas fa-sign-out-alt me-2"></i>Se Déconnecter
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Professional Navbar Enhancement Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const navbar = document.getElementById('emploidbNavbar');
            
            // Add scroll effect to navbar
            function updateNavbarOnScroll() {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }
            
            window.addEventListener('scroll', updateNavbarOnScroll);
            updateNavbarOnScroll(); // Call once to set initial state
            
            // Smooth dropdown animations
            const dropdowns = document.querySelectorAll('.dropdown-menu');
            dropdowns.forEach(dropdown => {
                dropdown.style.transition = 'all 0.3s ease';
            });
        });
    </script>
    <!-- Enhanced Professional Navbar End -->