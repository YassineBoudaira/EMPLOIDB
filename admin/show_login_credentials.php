<?php
// Show Login Credentials - Standard Admin Design
$page_title = 'Login Credentials - EMPLOIDB';
include 'includes/admin_header.php';

// Check if user is admin
if (!Security::isAdmin()) {
    echo '<div class="enterprise-card">
        <div class="card-header bg-gradient-danger text-white">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>Access Denied</h4>
        </div>
        <div class="card-body text-center">
            <p class="mb-0">Admin privileges are required to access login credentials.</p>
        </div>
    </div>';
    include 'includes/admin_footer.php';
    exit;
}

// Get user credentials
try {
    $users = $db->fetchAll("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!-- Login Credentials Content -->
<div class="enterprise-card">
    <div class="card-header bg-gradient-info text-white">
        <h4><i class="fas fa-user-secret me-2"></i>User Login Credentials</h4>
        <p class="mb-0">Display login information for all users</p>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($user['role']) ?></span></td>
                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
