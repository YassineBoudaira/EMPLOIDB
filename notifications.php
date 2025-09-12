<?php
// Include configuration first (before any session starts)
include 'include/config.php';
include 'include/sess.php';
include 'include/connexion.php';

// Check if user is logged in
if (!Security::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle notification actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $notification_id = $_POST['notification_id'] ?? 0;
    
    if ($action === 'mark_read' && $notification_id) {
        $db->query("UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?", 
            [$notification_id, $user_id]);
        echo json_encode(['success' => true]);
        exit;
    }
    
    if ($action === 'mark_all_read') {
        $db->query("UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL", 
            [$user_id]);
        echo json_encode(['success' => true]);
        exit;
    }
    
    if ($action === 'delete' && $notification_id) {
        $db->query("DELETE FROM notifications WHERE id = ? AND user_id = ?", 
            [$notification_id, $user_id]);
        echo json_encode(['success' => true]);
        exit;
    }
}

// Get notifications with pagination
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$notifications = $db->fetchAll("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
", [$user_id, $per_page, $offset]);

$total_notifications = $db->fetch("
    SELECT COUNT(*) as count FROM notifications WHERE user_id = ?
", [$user_id])['count'];

$total_pages = ceil($total_notifications / $per_page);

// Get unread count
$unread_count = $db->fetch("
    SELECT COUNT(*) as count FROM notifications 
    WHERE user_id = ? AND read_at IS NULL
", [$user_id])['count'];

// Get statistics
$today_notifications = $db->fetch("
    SELECT COUNT(*) as count FROM notifications 
    WHERE user_id = ? AND DATE(created_at) = CURDATE()
", [$user_id])['count'];

$week_notifications = $db->fetch("
    SELECT COUNT(*) as count FROM notifications 
    WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
", [$user_id])['count'];

$month_notifications = $db->fetch("
    SELECT COUNT(*) as count FROM notifications 
    WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
", [$user_id])['count'];

// Set custom title for this page
$page_title = "Mes Notifications | JobMaroc.ma";

include 'frontoffice/include/header2.php';
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

    <!-- Notifications Content -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <!-- Professional Header Section -->
                    <div class="professional-section">
                        <h3><i class="fas fa-bell"></i>Gestion des Notifications</h3>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="enhanced-card">
                                    <div class="enhanced-card-header">
                                        <h4 class="enhanced-card-title">Mes Notifications</h4>
                                        <div class="enhanced-card-actions">
                                            <?php if ($unread_count > 0): ?>
                                                <button class="btn-action btn-action-primary" onclick="markAllAsRead()">
                                                    <i class="fas fa-check-double"></i>Tout marquer comme lu
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn-action btn-action-success" onclick="refreshNotifications()">
                                                <i class="fas fa-sync-alt"></i>Actualiser
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-muted"><?= $total_notifications ?> notifications au total</span>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary active" onclick="filterNotifications('all')">
                                                <i class="fas fa-list"></i> Toutes
                                            </button>
                                            <button type="button" class="btn btn-outline-warning" onclick="filterNotifications('unread')">
                                                <i class="fas fa-exclamation-circle"></i> Non lues
                                            </button>
                                            <button type="button" class="btn btn-outline-success" onclick="filterNotifications('job_alert')">
                                                <i class="fas fa-briefcase"></i> Alertes
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="action-buttons">
                                    <button class="btn-action btn-action-info" onclick="window.location.href='user_profile.php'">
                                        <i class="fas fa-cog"></i>Paramètres
                                    </button>
                                    <button class="btn-action btn-action-warning" onclick="openJobAlert()">
                                        <i class="fas fa-bell"></i>Créer une Alerte
                                    </button>
                                    <button class="btn-action btn-action-success" onclick="openProfileEdit()">
                                        <i class="fas fa-user-edit"></i>Modifier le Profil
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Statistics Section -->
                    <div class="professional-section">
                        <h3><i class="fas fa-chart-bar"></i>Statistiques des Notifications</h3>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-primary">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <div class="stats-number"><?= $total_notifications ?></div>
                                    <div class="stats-label">Total Notifications</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-danger">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>
                                    <div class="stats-number"><?= $unread_count ?></div>
                                    <div class="stats-label">Non Lues</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-success">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div class="stats-number"><?= $today_notifications ?></div>
                                    <div class="stats-label">Aujourd'hui</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon color-scheme-info">
                                        <i class="fas fa-calendar-week"></i>
                                    </div>
                                    <div class="stats-number"><?= $week_notifications ?></div>
                                    <div class="stats-label">Cette Semaine</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-9">
                    <!-- Notifications Header -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">Centre de Notifications</h5>
                                    <p class="text-muted mb-0">
                                        <?= $total_notifications ?> notification<?= $total_notifications > 1 ? 's' : '' ?>
                                        <?php if ($unread_count > 0): ?>
                                            • <span class="text-danger"><?= $unread_count ?> non lue<?= $unread_count > 1 ? 's' : '' ?></span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-secondary btn-sm" onclick="refreshPage()">
                                        <i class="fas fa-sync-alt me-1"></i>Actualiser
                                    </button>
                                    <button class="btn btn-primary btn-sm" onclick="markAllAsRead()">
                                        <i class="fas fa-check-double me-1"></i>Tout marquer comme lu
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications List -->
                    <?php if (empty($notifications)): ?>
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-bell fa-4x text-muted"></i>
                                </div>
                                <h5 class="text-muted mb-2">Aucune notification</h5>
                                <p class="text-muted mb-4">Vous n'avez pas encore de notifications.</p>
                                <a href="enhanced_search.php" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Rechercher des emplois
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="notifications-container">
                            <?php foreach ($notifications as $notification): ?>
                                <?php
                                // Determine notification type for filtering
                                $notification_type = 'general';
                                if (strpos(strtolower($notification['title']), 'candidature') !== false) {
                                    $notification_type = 'application_update';
                                } elseif (strpos(strtolower($notification['title']), 'alerte') !== false || strpos(strtolower($notification['title']), 'emploi') !== false) {
                                    $notification_type = 'job_alert';
                                }
                                ?>
                                <div class="notification-card mb-4" data-notification-id="<?= $notification['id'] ?>" data-type="<?= $notification_type ?>">
                                    <div class="card border-0 shadow-sm <?= empty($notification['read_at']) ? 'border-warning' : '' ?>">
                                        <div class="card-body p-4">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="d-flex align-items-start">
                                                        <div class="notification-icon me-3">
                                                            <?php
                                                            $icon_class = 'fas fa-info-circle';
                                                            $icon_color = 'text-primary';
                                                            
                                                            if (strpos(strtolower($notification['title']), 'candidature') !== false) {
                                                                $icon_class = 'fas fa-paper-plane';
                                                                $icon_color = 'text-success';
                                                            } elseif (strpos(strtolower($notification['title']), 'entretien') !== false) {
                                                                $icon_class = 'fas fa-handshake';
                                                                $icon_color = 'text-warning';
                                                            } elseif (strpos(strtolower($notification['title']), 'embauche') !== false) {
                                                                $icon_class = 'fas fa-trophy';
                                                                $icon_color = 'text-success';
                                                            } elseif (strpos(strtolower($notification['title']), 'refus') !== false) {
                                                                $icon_class = 'fas fa-times-circle';
                                                                $icon_color = 'text-danger';
                                                            }
                                                            ?>
                                                            <div class="status-icon <?= empty($notification['read_at']) ? 'bg-warning' : 'bg-secondary' ?>">
                                                                <i class="<?= $icon_class ?> text-white"></i>
                                                            </div>
                                                        </div>
                                                        <div class="notification-details flex-grow-1">
                                                            <h6 class="notification-title mb-2">
                                                                <?= htmlspecialchars($notification['title']) ?>
                                                                <?php if (empty($notification['read_at'])): ?>
                                                                    <span class="badge bg-warning ms-2">Nouveau</span>
                                                                <?php endif; ?>
                                                            </h6>
                                                            <p class="notification-message text-muted mb-2">
                                                                <?= htmlspecialchars($notification['message']) ?>
                                                            </p>
                                                            <div class="notification-meta">
                                                                <small class="text-muted">
                                                                    <i class="far fa-calendar-alt me-1"></i>
                                                                    <?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?>
                                                                </small>
                                                                <?php if (!empty($notification['read_at'])): ?>
                                                                    <small class="text-muted ms-3">
                                                                        <i class="fas fa-check me-1"></i>
                                                                        Lu le <?= date('d/m/Y H:i', strtotime($notification['read_at'])) ?>
                                                                    </small>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="d-flex flex-column align-items-end h-100">
                                                        <div class="notification-actions">
                                                            <?php if (empty($notification['read_at'])): ?>
                                                                <button class="btn btn-outline-primary btn-sm mb-2" onclick="markAsRead(<?= $notification['id'] ?>)">
                                                                    <i class="fas fa-check me-1"></i>Marquer comme lu
                                                                </button>
                                                            <?php endif; ?>
                                                            <button class="btn btn-outline-danger btn-sm" onclick="deleteNotification(<?= $notification['id'] ?>)">
                                                                <i class="fas fa-trash me-1"></i>Supprimer
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="card border-0 shadow-sm mt-4">
                                <div class="card-body p-4">
                                    <nav aria-label="Notifications pagination">
                                        <ul class="pagination justify-content-center mb-0">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?= $page - 1 ?>">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if ($page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?= $page + 1 ?>">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.notification-card {
    transition: all 0.3s ease;
}

.notification-card:hover {
    transform: translateY(-2px);
}

.status-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.notification-title {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.notification-message {
    line-height: 1.5;
}

.notification-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.notification-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.notification-details {
    flex: 1;
    min-width: 0;
}

.notification-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.notification-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.badge {
    border-radius: 6px;
    font-weight: 500;
}

.pagination .page-link {
    border-radius: 8px;
    margin: 0 2px;
    border: none;
    color: #007bff;
    transition: all 0.3s ease;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}

@media (max-width: 768px) {
    .notification-details {
        margin-top: 1rem;
    }

    .notification-actions {
        margin-top: 1rem;
        align-items: stretch;
        flex-direction: row;
        width: 100%;
        justify-content: flex-end;
    }

    .notification-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .notification-content {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<script>
// Mark notification as read
function markAsRead(notificationId) {
    fetch('notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_read&notification_id=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notification = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notification) {
                notification.classList.remove('border-warning');
                const badge = notification.querySelector('.badge');
                if (badge) badge.remove();
                const markReadBtn = notification.querySelector('.btn-outline-primary');
                if (markReadBtn) markReadBtn.remove();
            }
            updateUnreadCount();
            showNotification('Notification marquée comme lue', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erreur lors de la mise à jour', 'error');
    });
}

// Mark all notifications as read
function markAllAsRead() {
    fetch('notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_all_read'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notifications = document.querySelectorAll('.notification-card');
            notifications.forEach(notification => {
                notification.classList.remove('border-warning');
                const badge = notification.querySelector('.badge');
                if (badge) badge.remove();
                const markReadBtn = notification.querySelector('.btn-outline-primary');
                if (markReadBtn) markReadBtn.remove();
            });
            updateUnreadCount();
            showNotification('Toutes les notifications marquées comme lues', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erreur lors de la mise à jour', 'error');
    });
}

// Delete notification
function deleteNotification(notificationId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) {
        return;
    }
    
    fetch('notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=delete&notification_id=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notification = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notification) {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(-100%)';
                setTimeout(() => {
                    notification.remove();
                    updateUnreadCount();
                }, 300);
            }
            showNotification('Notification supprimée', 'success');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Erreur lors de la suppression', 'error');
    });
}

// Update unread count
function updateUnreadCount() {
    const unreadNotifications = document.querySelectorAll('.notification-card .border-warning');
    const unreadCount = unreadNotifications.length;
    
    const badge = document.querySelector('.badge.bg-danger');
    if (badge) {
        if (unreadCount === 0) {
            badge.remove();
        } else {
            badge.textContent = unreadCount + ' non lue' + (unreadCount > 1 ? 's' : '');
        }
    }
    
    const markAllBtn = document.querySelector('.btn-primary');
    if (markAllBtn && markAllBtn.textContent.includes('Tout marquer')) {
        if (unreadCount === 0) {
            markAllBtn.style.display = 'none';
        }
    }
}

// Filter notifications
function filterNotifications(type) {
    const notifications = document.querySelectorAll('.notification-card');
    
    notifications.forEach(notification => {
        if (type === 'all') {
            notification.style.display = 'block';
        } else if (type === 'unread') {
            notification.style.display = notification.classList.contains('border-warning') ? 'block' : 'none';
        } else {
            const notificationType = notification.getAttribute('data-type');
            notification.style.display = notificationType === type ? 'block' : 'none';
        }
    });
}

// Refresh notifications
function refreshNotifications() {
    location.reload();
}

// Refresh page
function refreshPage() {
    location.reload();
}

// Show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>

<?php include 'frontoffice/include/footer2.php'; ?>

<!-- Professional UX/UI Styles -->
<style>
/* Professional Sections */
.professional-section {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.professional-section h3 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.professional-section h3 i {
    color: #007bff;
    font-size: 1.2em;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 20px;
}

.btn-action {
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.btn-action-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.btn-action-success {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    color: white;
}

.btn-action-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: #212529;
}

.btn-action-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
    color: white;
}

/* Enhanced Cards */
.enhanced-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.enhanced-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.enhanced-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f8f9fa;
}

.enhanced-card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.enhanced-card-actions {
    display: flex;
    gap: 10px;
}

/* Statistics Cards */
.stats-card {
    background: linear-gradient(135deg, #ffffff, #f8f9fa);
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.5rem;
    color: white;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.stats-label {
    color: #6c757d;
    font-weight: 500;
}

/* Color Schemes */
.color-scheme-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
}

.color-scheme-success {
    background: linear-gradient(135deg, #28a745, #1e7e34);
}

.color-scheme-warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
}

.color-scheme-info {
    background: linear-gradient(135deg, #17a2b8, #138496);
}

.color-scheme-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
}

/* Popup Styles */
.popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
    z-index: 9999;
    display: none;
    animation: fadeIn 0.3s ease;
}

.popup-container {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 10000;
    animation: slideIn 0.3s ease;
}

.popup-header {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    padding: 25px 30px;
    border-radius: 20px 20px 0 0;
    position: relative;
}

.popup-header h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.popup-close {
    position: absolute;
    top: 20px;
    right: 25px;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
}

.popup-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

.popup-body {
    padding: 30px;
}

.popup-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    padding: 20px 30px;
    background: #f8f9fa;
    border-radius: 0 0 20px 20px;
}

.btn-popup {
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-popup-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
}

.btn-popup-primary:hover {
    background: linear-gradient(135deg, #0056b3, #004085);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
}

.btn-popup-secondary {
    background: #6c757d;
    color: white;
}

.btn-popup-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translate(-50%, -60%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

@media (max-width: 768px) {
    .professional-section {
        padding: 20px;
    }
    
    .enhanced-card {
        padding: 20px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- Popup Forms -->
<div id="profileEditPopup" class="popup-overlay">
    <div class="popup-container" style="width: 600px;">
        <div class="popup-header">
            <h3><i class="fas fa-user-edit me-2"></i>Modifier le Profil</h3>
            <button type="button" class="popup-close" onclick="closePopup('profileEditPopup')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="popup-body">
            <form id="profileEditForm" class="popup-form">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" name="nom" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Prénom *</label>
                            <input type="text" class="form-control" name="prenom" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" name="telephone">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Date de Naissance</label>
                            <input type="date" class="form-control" name="date_n">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Adresse</label>
                    <textarea class="form-control" name="adresse" rows="3"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Ville</label>
                            <select class="form-select" name="ville_id">
                                <option value="">Sélectionner une ville</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Domaine</label>
                            <select class="form-select" name="domaine_id">
                                <option value="">Sélectionner un domaine</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea class="form-control" name="bio" rows="4" placeholder="Parlez-nous de vous..."></textarea>
                </div>
            </form>
        </div>
        <div class="popup-actions">
            <button type="button" class="btn-popup btn-popup-secondary" onclick="closePopup('profileEditPopup')">
                <i class="fas fa-times me-2"></i>Annuler
            </button>
            <button type="button" class="btn-popup btn-popup-primary" onclick="saveProfile()">
                <i class="fas fa-save me-2"></i>Enregistrer
            </button>
        </div>
    </div>
</div>

<!-- Job Alert Popup -->
<div id="jobAlertPopup" class="popup-overlay">
    <div class="popup-container" style="width: 500px;">
        <div class="popup-header">
            <h3><i class="fas fa-bell me-2"></i>Créer une Alerte Emploi</h3>
            <button type="button" class="popup-close" onclick="closePopup('jobAlertPopup')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="popup-body">
            <form id="jobAlertForm" class="popup-form">
                <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
                <div class="form-group">
                    <label class="form-label">Nom de l'Alerte *</label>
                    <input type="text" class="form-control" name="alert_name" placeholder="Ex: Développeur Web Casablanca" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mots-clés</label>
                    <input type="text" class="form-control" name="keywords" placeholder="Ex: PHP, JavaScript, React">
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Domaine</label>
                            <select class="form-select" name="domaine_id">
                                <option value="">Tous les domaines</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Ville</label>
                            <select class="form-select" name="ville_id">
                                <option value="">Toutes les villes</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Type de Contrat</label>
                            <select class="form-select" name="contrat_id">
                                <option value="">Tous les contrats</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Fréquence</label>
                            <select class="form-select" name="frequency" required>
                                <option value="daily">Quotidienne</option>
                                <option value="weekly">Hebdomadaire</option>
                                <option value="monthly">Mensuelle</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="popup-actions">
            <button type="button" class="btn-popup btn-popup-secondary" onclick="closePopup('jobAlertPopup')">
                <i class="fas fa-times me-2"></i>Annuler
            </button>
            <button type="button" class="btn-popup btn-popup-primary" onclick="createJobAlert()">
                <i class="fas fa-bell me-2"></i>Créer l'Alerte
            </button>
        </div>
    </div>
</div>

<!-- Enhanced JavaScript Functions -->
<script>
function openPopup(popupId) {
    document.getElementById(popupId).style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closePopup(popupId) {
    document.getElementById(popupId).style.display = 'none';
    document.body.style.overflow = 'auto';
}

function openProfileEdit() {
    openPopup('profileEditPopup');
}

function openJobAlert() {
    loadDomains();
    loadCities();
    loadContrats();
    openPopup('jobAlertPopup');
}

function saveProfile() {
    const form = document.getElementById('profileEditForm');
    const formData = new FormData(form);
    
    fetch('ajax/update_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Profil mis à jour avec succès!');
            closePopup('profileEditPopup');
            location.reload();
        } else {
            alert(data.message || 'Erreur lors de la mise à jour');
        }
    })
    .catch(error => {
        alert('Erreur de connexion');
    });
}

function createJobAlert() {
    const form = document.getElementById('jobAlertForm');
    const formData = new FormData(form);
    
    fetch('ajax/create_job_alert.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Alerte créée avec succès!');
            closePopup('jobAlertPopup');
            form.reset();
        } else {
            alert(data.message || 'Erreur lors de la création');
        }
    })
    .catch(error => {
        alert('Erreur de connexion');
    });
}

function loadCities() {
    fetch('ajax/get_cities.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('select[name="ville_id"]');
                selects.forEach(select => {
                    select.innerHTML = '<option value="">Sélectionner une ville</option>';
                    data.cities.forEach(city => {
                        select.innerHTML += `<option value="${city.id}">${city.nom}</option>`;
                    });
                });
            }
        });
}

function loadDomains() {
    fetch('ajax/get_domains.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('select[name="domaine_id"]');
                selects.forEach(select => {
                    select.innerHTML = '<option value="">Sélectionner un domaine</option>';
                    data.domains.forEach(domain => {
                        select.innerHTML += `<option value="${domain.id}">${domain.nom}</option>`;
                    });
                });
            }
        });
}

function loadContrats() {
    fetch('ajax/get_contrats.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const selects = document.querySelectorAll('select[name="contrat_id"]');
                selects.forEach(select => {
                    select.innerHTML = '<option value="">Sélectionner un contrat</option>';
                    data.contrats.forEach(contrat => {
                        select.innerHTML += `<option value="${contrat.id}">${contrat.nom}</option>`;
                    });
                });
            }
        });
}

// Close popup when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('popup-overlay')) {
        e.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
});
</script>
