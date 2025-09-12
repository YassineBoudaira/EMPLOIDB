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

<div class="container-fluid bg-light py-5">
    <div class="container">
        <!-- Page Header -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="h2 mb-2">
                            <i class="fas fa-bell text-primary me-3"></i>
                            Mes Notifications
                        </h1>
                        <p class="text-muted mb-0">Restez informé de vos candidatures et opportunités</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="user_profile.php" class="btn btn-outline-primary">
                            <i class="fas fa-user me-2"></i>Mon Profil
                        </a>
                        <a href="saved_jobs.php" class="btn btn-outline-success">
                            <i class="fas fa-heart me-2"></i>Offres Sauvegardées
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-chart-bar me-2"></i>Statistiques
                        </h6>
                        
                        <!-- Statistics Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="bg-primary bg-opacity-10 rounded p-3 text-center">
                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                        <i class="fas fa-bell text-primary"></i>
                                    </div>
                                    <h6 class="mb-0 text-primary"><?= $total_notifications ?></h6>
                                    <small class="text-muted">Total</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-danger bg-opacity-10 rounded p-3 text-center">
                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                        <i class="fas fa-exclamation-circle text-danger"></i>
                                    </div>
                                    <h6 class="mb-0 text-danger"><?= $unread_count ?></h6>
                                    <small class="text-muted">Non lues</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-success bg-opacity-10 rounded p-3 text-center">
                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                        <i class="fas fa-calendar-day text-success"></i>
                                    </div>
                                    <h6 class="mb-0 text-success"><?= $today_notifications ?></h6>
                                    <small class="text-muted">Aujourd'hui</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-info bg-opacity-10 rounded p-3 text-center">
                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                        <i class="fas fa-calendar-week text-info"></i>
                                    </div>
                                    <h6 class="mb-0 text-info"><?= $week_notifications ?></h6>
                                    <small class="text-muted">Cette semaine</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="d-grid gap-2">
                            <?php if ($unread_count > 0): ?>
                                <button class="btn btn-primary btn-sm" onclick="markAllAsRead()">
                                    <i class="fas fa-check-double me-2"></i>Tout marquer comme lu
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-outline-secondary btn-sm" onclick="refreshNotifications()">
                                <i class="fas fa-sync-alt me-2"></i>Actualiser
                            </button>
                            <a href="user_profile.php" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-cog me-2"></i>Paramètres
                            </a>
                        </div>
                        
                        <!-- Filter Options -->
                        <div class="mt-4">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-filter me-2"></i>Filtres
                            </h6>
                            <div class="d-flex flex-column gap-2">
                                <button class="btn btn-outline-primary btn-sm text-start" onclick="filterNotifications('all')">
                                    <i class="fas fa-list me-2"></i>Toutes
                                </button>
                                <button class="btn btn-outline-warning btn-sm text-start" onclick="filterNotifications('unread')">
                                    <i class="fas fa-exclamation-circle me-2"></i>Non lues
                                </button>
                                <button class="btn btn-outline-success btn-sm text-start" onclick="filterNotifications('job_alert')">
                                    <i class="fas fa-briefcase me-2"></i>Alertes d'emploi
                                </button>
                                <button class="btn btn-outline-info btn-sm text-start" onclick="filterNotifications('application_update')">
                                    <i class="fas fa-clipboard-check me-2"></i>Mises à jour
                                </button>
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
                                <h5 class="mb-1">Notifications</h5>
                                <p class="text-muted mb-0">
                                    <?= $total_notifications ?> notification<?= $total_notifications > 1 ? 's' : '' ?>
                                    <?php if ($unread_count > 0): ?>
                                        <span class="badge bg-danger ms-2"><?= $unread_count ?> non lue<?= $unread_count > 1 ? 's' : '' ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary btn-sm" onclick="refreshNotifications()">
                                    <i class="fas fa-sync-alt me-1"></i>Actualiser
                                </button>
                                <?php if ($unread_count > 0): ?>
                                    <button class="btn btn-primary btn-sm" onclick="markAllAsRead()">
                                        <i class="fas fa-check-double me-1"></i>Tout marquer comme lu
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications List -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <?php if (empty($notifications)): ?>
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-bell-slash fa-4x text-muted"></i>
                                </div>
                                <h5 class="text-muted mb-2">Aucune notification</h5>
                                <p class="text-muted mb-4">Vous n'avez pas encore de notifications.</p>
                                <a href="enhanced_search.php" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Rechercher des offres
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="notifications-container" id="notificationsList">
                                <?php foreach ($notifications as $notification): ?>
                                    <div class="notification-item <?= $notification['read_at'] ? 'read' : 'unread' ?>" 
                                         data-notification-id="<?= $notification['id'] ?>"
                                         data-type="<?= $notification['type'] ?>">
                                        <div class="notification-content">
                                            <div class="notification-icon">
                                                <?php
                                                $icon_class = 'fas fa-info-circle text-primary';
                                                $bg_class = 'bg-primary bg-opacity-10';
                                                switch ($notification['type']) {
                                                    case 'job_alert':
                                                        $icon_class = 'fas fa-briefcase text-success';
                                                        $bg_class = 'bg-success bg-opacity-10';
                                                        break;
                                                    case 'application_update':
                                                        $icon_class = 'fas fa-clipboard-check text-info';
                                                        $bg_class = 'bg-info bg-opacity-10';
                                                        break;
                                                    case 'new_job':
                                                        $icon_class = 'fas fa-plus-circle text-success';
                                                        $bg_class = 'bg-success bg-opacity-10';
                                                        break;
                                                    case 'offer':
                                                        $icon_class = 'fas fa-gift text-warning';
                                                        $bg_class = 'bg-warning bg-opacity-10';
                                                        break;
                                                    case 'system':
                                                        $icon_class = 'fas fa-cog text-secondary';
                                                        $bg_class = 'bg-secondary bg-opacity-10';
                                                        break;
                                                }
                                                ?>
                                                <div class="icon-container <?= $bg_class ?>">
                                                    <i class="<?= $icon_class ?>"></i>
                                                </div>
                                            </div>
                                            <div class="notification-details">
                                                <div class="notification-header">
                                                    <h6 class="notification-title"><?= htmlspecialchars($notification['title']) ?></h6>
                                                    <?php if (!$notification['read_at']): ?>
                                                        <span class="badge bg-danger">Nouveau</span>
                                                    <?php endif; ?>
                                                </div>
                                                <p class="notification-message"><?= htmlspecialchars($notification['message']) ?></p>
                                                <div class="notification-meta">
                                                    <small class="text-muted">
                                                        <i class="far fa-clock me-1"></i>
                                                        <?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?>
                                                    </small>
                                                    <?php if ($notification['action_url']): ?>
                                                        <a href="<?= htmlspecialchars($notification['action_url']) ?>" 
                                                           class="btn btn-sm btn-primary ms-2">
                                                            <?= htmlspecialchars($notification['action_text'] ?? 'Voir plus') ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="notification-actions">
                                                <?php if (!$notification['read_at']): ?>
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="markAsRead(<?= $notification['id'] ?>)"
                                                            title="Marquer comme lu">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteNotification(<?= $notification['id'] ?>)"
                                                        title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <nav aria-label="Notifications pagination">
                            <ul class="pagination">
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.notifications-container {
    max-height: 600px;
    overflow-y: auto;
}

.notification-item {
    border-bottom: 1px solid #e9ecef;
    transition: all 0.3s ease;
    padding: 1rem;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
}

.notification-item.read {
    background-color: #fff;
}

.notification-content {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.notification-icon {
    flex-shrink: 0;
}

.icon-container {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
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

.notification-title {
    margin: 0;
    font-weight: 600;
    color: #2c3e50;
}

.notification-message {
    margin: 0 0 0.5rem 0;
    color: #6c757d;
    line-height: 1.5;
}

.notification-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.notification-actions {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    flex-shrink: 0;
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

.pagination .page-link:hover {
    background-color: #e9ecef;
    color: #007bff;
}

.badge {
    border-radius: 6px;
    font-weight: 500;
}

@media (max-width: 768px) {
    .notification-content {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .notification-actions {
        flex-direction: row;
        width: 100%;
        justify-content: flex-end;
        margin-top: 0.5rem;
    }
    
    .notification-meta {
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
                notification.classList.remove('unread');
                notification.classList.add('read');
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
            const notifications = document.querySelectorAll('.notification-item.unread');
            notifications.forEach(notification => {
                notification.classList.remove('unread');
                notification.classList.add('read');
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
    const unreadNotifications = document.querySelectorAll('.notification-item.unread');
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
    const notifications = document.querySelectorAll('.notification-item');
    
    notifications.forEach(notification => {
        if (type === 'all') {
            notification.style.display = 'block';
        } else if (type === 'unread') {
            notification.style.display = notification.classList.contains('unread') ? 'block' : 'none';
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

// Show notification
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'times-circle'} me-2"></i>
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
