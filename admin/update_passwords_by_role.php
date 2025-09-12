<?php
// Password Update by Role - Standard Admin Design
$page_title = 'Password Manager - EMPLOIDB';
include 'includes/admin_header.php';

// Check if user is admin
if (!Security::isAdmin()) {
    echo '<div class="enterprise-card">
        <div class="card-header bg-gradient-danger text-white">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>Access Denied</h4>
        </div>
        <div class="card-body text-center">
            <p class="mb-0">Admin privileges are required to access password management.</p>
        </div>
    </div>';
    include 'includes/admin_footer.php';
    exit;
}

$message = '';
$error = '';

// Handle password updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $role = $_POST['role'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        
        if ($role && $new_password) {
            $hashed_password = password_hash($new_password, PASSWORD_ARGON2ID);
            $db->query("UPDATE users SET password = ? WHERE role = ?", [$hashed_password, $role]);
            $message = "Passwords updated successfully for role: $role";
        }
    } catch (Exception $e) {
        $error = "Error updating passwords: " . $e->getMessage();
    }
}

// Get users by role
try {
    $roles = $db->fetchAll("SELECT DISTINCT role FROM users ORDER BY role");
    $users_by_role = [];
    foreach ($roles as $role) {
        $users_by_role[$role['role']] = $db->fetchAll("SELECT id, username, email FROM users WHERE role = ?", [$role['role']]);
    }
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!-- Password Manager Content -->
<div class="enterprise-card">
    <div class="card-header bg-gradient-primary text-white">
        <h4><i class="fas fa-key me-2"></i>Password Manager</h4>
        <p class="mb-0">Update user passwords based on their roles</p>
    </div>
    <div class="card-body">
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Password Update Form -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Update Passwords by Role</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label for="role" class="form-label">Select Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Choose a role...</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= htmlspecialchars($role['role']) ?>"><?= htmlspecialchars($role['role']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key me-2"></i>Update Passwords
                    </button>
                </form>
            </div>
            <div class="col-md-6">
                <h5>Users by Role</h5>
                <?php foreach ($users_by_role as $role => $users): ?>
                    <div class="mb-3">
                        <h6><?= htmlspecialchars($role) ?> (<?= count($users) ?> users)</h6>
                        <ul class="list-unstyled">
                            <?php foreach (array_slice($users, 0, 5) as $user): ?>
                                <li><i class="fas fa-user me-2"></i><?= htmlspecialchars($user['username']) ?> - <?= htmlspecialchars($user['email']) ?></li>
                            <?php endforeach; ?>
                            <?php if (count($users) > 5): ?>
                                <li><em>... and <?= count($users) - 5 ?> more</em></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin_footer.php'; ?>
