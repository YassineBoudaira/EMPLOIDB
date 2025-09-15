<?php
require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/Database.php';

$db = new Database();

// Recent log HTML fragment
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['recent'])) {
	try {
		$rows = $db->fetchAll("SELECT * FROM audit_logs WHERE action LIKE 'gdpr_%' ORDER BY created_at DESC LIMIT 20");
		echo '<div class="table-responsive"><table class="table table-sm table-hover"><thead><tr><th>When</th><th>Action</th><th>User</th><th>Resource</th><th>Details</th></tr></thead><tbody>';
		foreach ($rows as $r) {
			echo '<tr>';
			echo '<td>' . htmlspecialchars($r['created_at']) . '</td>';
			echo '<td><span class="badge bg-dark">' . htmlspecialchars($r['action']) . '</span></td>';
			echo '<td>#' . (int)$r['user_id'] . '</td>';
			echo '<td>' . htmlspecialchars($r['resource']) . '</td>';
			echo '<td class="small">' . htmlspecialchars($r['details']) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table></div>';
	} catch (Exception $e) { echo '<div class="text-muted">No recent actions</div>'; }
	exit;
}

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$query = trim($input['query'] ?? '');
action:
$action = trim($input['action'] ?? 'export');
$reason = trim($input['reason'] ?? '');

if ($query === '') { echo json_encode(['success' => false, 'error' => 'Missing query']); exit; }

try {
	// Find user by email or ID
	$user = null;
	if (is_numeric($query)) {
		$user = $db->fetch("SELECT * FROM users WHERE id = ?", [(int)$query]);
	} else {
		$user = $db->fetch("SELECT * FROM users WHERE email = ?", [$query]);
	}
	if (!$user) { echo json_encode(['success' => false, 'error' => 'User not found']); exit; }

	if ($action === 'export') {
		$data = [
			'user' => $user,
			'applications' => $db->fetchAll("SELECT * FROM postulation WHERE user_id = ?", [$user['id']]),
			'alerts' => $db->fetchAll("SELECT * FROM job_alerts WHERE user_id = ?", [$user['id']])
		];
		$payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$path = __DIR__ . '/../../exports';
		if (!is_dir($path)) { @mkdir($path, 0775, true); }
		$file = $path . '/gdpr_export_user_' . $user['id'] . '_' . date('Ymd_His') . '.json';
		file_put_contents($file, $payload);
		
		$db->insert("INSERT INTO audit_logs (user_id, action, resource, details, ip_address, user_agent, created_at) VALUES (?, 'gdpr_export', 'user', ?, ?, ?, NOW())",
			[$user['id'], 'export file: ' . basename($file), $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']
		);
		
echo json_encode(['success' => true, 'message' => 'Export generated', 'file' => basename($file)]); exit;
	}
	
	if ($action === 'delete') {
		// Anonymize PII and mark records
		$db->query("UPDATE users SET email = CONCAT('deleted_', id, '@example.com'), nom = 'Deleted', prenom = 'User', phone = NULL WHERE id = ?", [$user['id']]);
		$db->query("UPDATE postulation SET personal_data = NULL WHERE user_id = ?", [$user['id']]);
		
		$db->insert("INSERT INTO audit_logs (user_id, action, resource, details, ip_address, user_agent, created_at) VALUES (?, 'gdpr_delete', 'user', ?, ?, ?, NOW())",
			[$user['id'], 'reason: ' . $reason, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']
		);
		
echo json_encode(['success' => true, 'message' => 'User anonymized']); exit;
	}
	
	echo json_encode(['success' => false, 'error' => 'Unknown action']);
} catch (Exception $e) {
	echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
