<?php
require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/Database.php';

$db = new Database();
$action = $_GET['action'] ?? '';

if ($action === 'load') {
	header('Content-Type: application/json');
	$q = trim($_GET['query'] ?? '');
	if ($q === '') { echo json_encode(['success' => false, 'error' => 'Missing query']); exit; }
	try {
		$user = is_numeric($q)
			? $db->fetch("SELECT * FROM users WHERE id = ?", [(int)$q])
			: $db->fetch("SELECT * FROM users WHERE email = ?", [$q]);
		if (!$user) { echo json_encode(['success' => false, 'error' => 'User not found']); exit; }
		$consents = [];
		$rows = $db->fetchAll("SELECT consent_type, consent_value FROM user_consent WHERE user_id = ?", [$user['id']]);
		foreach ($rows as $r) { $consents[$r['consent_type']] = $r['consent_value']; }
		echo json_encode(['success' => true, 'user' => $user, 'consents' => $consents]);
	} catch (Exception $e) { echo json_encode(['success' => false, 'error' => $e->getMessage()]); }
	exit;
}

if ($action === 'save') {
	header('Content-Type: application/json');
	$input = json_decode(file_get_contents('php://input'), true) ?: [];
	$userId = (int)($input['user_id'] ?? 0);
	$consents = $input['consents'] ?? [];
	if (!$userId) { echo json_encode(['success' => false, 'error' => 'Missing user']); exit; }
	try {
		foreach ($consents as $k => $v) {
			$exists = $db->fetch("SELECT id FROM user_consent WHERE user_id = ? AND consent_type = ?", [$userId, $k]);
			if ($exists) {
				$db->query("UPDATE user_consent SET consent_value = ?, updated_at = NOW() WHERE id = ?", [$v, $exists['id']]);
			} else {
				$db->query("INSERT INTO user_consent (user_id, consent_type, consent_value, created_at) VALUES (?, ?, ?, NOW())", [$userId, $k, $v]);
			}
		}
		$db->insert("INSERT INTO audit_logs (user_id, action, resource, details, ip_address, user_agent, created_at) VALUES (?, 'consent_update', 'user', ?, ?, ?, NOW())",
			[$userId, json_encode($consents), $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']
		);
		echo json_encode(['success' => true]);
	} catch (Exception $e) { echo json_encode(['success' => false, 'error' => $e->getMessage()]); }
	exit;
}

if ($action === 'recent') {
	$userId = (int)($_GET['user_id'] ?? 0);
	try {
		$rows = $db->fetchAll("SELECT * FROM audit_logs WHERE action = 'consent_update' AND user_id = ? ORDER BY created_at DESC LIMIT 20", [$userId]);
		echo '<div class="table-responsive"><table class="table table-sm table-hover"><thead><tr><th>When</th><th>Details</th></tr></thead><tbody>';
		foreach ($rows as $r) {
			echo '<tr><td>' . htmlspecialchars($r['created_at']) . '</td><td><code>' . htmlspecialchars($r['details']) . '</code></td></tr>';
		}
		echo '</tbody></table></div>';
	} catch (Exception $e) { echo '<div class="text-muted">No history</div>'; }
	exit;
}

http_response_code(400);
echo 'Bad Request';
