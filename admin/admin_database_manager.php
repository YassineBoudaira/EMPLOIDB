<?php
// Database Manager - Admin Panel (Standard Admin Design)
$page_title = 'Database Manager - EMPLOIDB';
include 'includes/admin_header.php';

// Check if user is admin
if (!Security::isAdmin()) {
    echo '<div class="enterprise-card">
        <div class="card-header bg-gradient-danger text-white">
            <h4><i class="fas fa-exclamation-triangle me-2"></i>Access Denied</h4>
        </div>
        <div class="card-body text-center">
            <p class="mb-0">You do not have sufficient privileges to access this page.</p>
        </div>
    </div>';
    include 'includes/admin_footer.php';
    exit;
}

$message = '';
$error = '';

// Handle database operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create_table':
                $table_name = $_POST['table_name'] ?? '';
                $table_sql = $_POST['table_sql'] ?? '';
                if ($table_name && $table_sql) {
                    $db->query($table_sql);
                    $message = "Table '$table_name' created successfully!";
                }
                break;
                
            case 'insert_data':
                $data_sql = $_POST['data_sql'] ?? '';
                if ($data_sql) {
                    $db->query($data_sql);
                    $message = "Data inserted successfully!";
                }
                break;
                
            case 'update_setting':
                $setting_key = $_POST['setting_key'] ?? '';
                $setting_value = $_POST['setting_value'] ?? '';
                if ($setting_key) {
                    $db->query("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?", [$setting_value, $setting_key]);
                    $message = "Setting updated successfully!";
                }
                break;
        }
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Get database information
try {
    $tables = $db->fetchAll("SHOW TABLES");
    $tables = array_map(function($row) { return array_values($row)[0]; }, $tables);
    
    $table_counts = [];
    foreach ($tables as $table) {
        $count = $db->fetch("SELECT COUNT(*) as count FROM `$table`")['count'];
        $table_counts[$table] = $count;
    }
    
    $system_settings = $db->fetchAll("SELECT * FROM system_settings ORDER BY setting_key");
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!-- Database Manager Content -->
<div class="enterprise-card">
    <div class="card-header bg-gradient-primary text-white">
        <h4><i class="fas fa-database me-2"></i>Database Manager</h4>
        <p class="mb-0">Manage database tables, settings, and operations</p>
    </div>
    <div class="card-body">
        <!-- Messages -->
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

        <!-- Database Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="enterprise-stat-card">
                    <div class="stat-icon bg-primary">
                        <i class="fas fa-table"></i>
                    </div>
                    <div class="stat-content">
                        <h5><?= count($tables) ?></h5>
                        <p>Total Tables</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="enterprise-stat-card">
                    <div class="stat-icon bg-success">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="stat-content">
                        <h5><?= array_sum($table_counts) ?></h5>
                        <p>Total Records</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="enterprise-stat-card">
                    <div class="stat-icon bg-info">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="stat-content">
                        <h5><?= count($system_settings) ?></h5>
                        <p>System Settings</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="enterprise-stat-card">
                    <div class="stat-icon bg-warning">
                        <i class="fas fa-server"></i>
                    </div>
                    <div class="stat-content">
                        <h5>Online</h5>
                        <p>Database Status</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" onclick="showCreateTableModal()">
                        <i class="fas fa-plus me-2"></i>Create Table
                    </button>
                    <button class="btn btn-success" onclick="showInsertDataModal()">
                        <i class="fas fa-plus me-2"></i>Insert Data
                    </button>
                    <button class="btn btn-info" onclick="exportDatabase()">
                        <i class="fas fa-download me-2"></i>Export Database
                    </button>
                </div>
            </div>
        </div>

        <!-- Database Tables -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="enterprise-card">
                    <div class="card-header">
                        <h5><i class="fas fa-table me-2"></i>Database Tables</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Table Name</th>
                                        <th>Records</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tables as $table): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-table me-2"></i>
                                                <strong><?= htmlspecialchars($table) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?= $table_counts[$table] ?? 0 ?></span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info me-1" onclick="viewTableStructure('<?= $table ?>')">
                                                    <i class="fas fa-eye"></i> Structure
                                                </button>
                                                <button class="btn btn-sm btn-success me-1" onclick="viewTableData('<?= $table ?>')">
                                                    <i class="fas fa-list"></i> Data
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Settings -->
        <div class="row">
            <div class="col-12">
                <div class="enterprise-card">
                    <div class="card-header">
                        <h5><i class="fas fa-cog me-2"></i>System Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Setting Key</th>
                                        <th>Setting Value</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($system_settings as $setting): ?>
                                        <tr>
                                            <td>
                                                <i class="fas fa-key me-2"></i>
                                                <strong><?= htmlspecialchars($setting['setting_key']) ?></strong>
                                            </td>
                                            <td>
                                                <code><?= htmlspecialchars($setting['setting_value']) ?></code>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" onclick="editSetting('<?= $setting['setting_key'] ?>', '<?= htmlspecialchars($setting['setting_value']) ?>')">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Forms -->
<form method="POST" style="display: none;" id="actionForm">
    <input type="hidden" name="action" id="actionInput">
    <input type="hidden" name="table_name" id="tableNameInput">
    <input type="hidden" name="table_sql" id="tableSqlInput">
    <input type="hidden" name="data_sql" id="dataSqlInput">
    <input type="hidden" name="setting_key" id="settingKeyInput">
    <input type="hidden" name="setting_value" id="settingValueInput">
</form>

<script>
function showCreateTableModal() {
    const tableName = prompt('Enter table name:');
    if (tableName) {
        const tableSql = prompt('Enter CREATE TABLE SQL:');
        if (tableSql) {
            document.getElementById('actionInput').value = 'create_table';
            document.getElementById('tableNameInput').value = tableName;
            document.getElementById('tableSqlInput').value = tableSql;
            document.getElementById('actionForm').submit();
        }
    }
}

function showInsertDataModal() {
    const dataSql = prompt('Enter INSERT SQL:');
    if (dataSql) {
        document.getElementById('actionInput').value = 'insert_data';
        document.getElementById('dataSqlInput').value = dataSql;
        document.getElementById('actionForm').submit();
    }
}

function exportDatabase() {
    alert('Export functionality would be implemented here');
}

function viewTableStructure(tableName) {
    alert('View structure for table: ' + tableName);
}

function viewTableData(tableName) {
    alert('View data for table: ' + tableName);
}

function editSetting(key, value) {
    const newValue = prompt('Enter new value for ' + key + ':', value);
    if (newValue !== null && newValue !== value) {
        document.getElementById('actionInput').value = 'update_setting';
        document.getElementById('settingKeyInput').value = key;
        document.getElementById('settingValueInput').value = newValue;
        document.getElementById('actionForm').submit();
    }
}
</script>

<?php include 'includes/admin_footer.php'; ?>
