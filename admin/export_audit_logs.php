<?php
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename=audit_logs_export_' . date('Ymd_His') . '.csv');

require_once __DIR__ . '/../include/connexionbd.php';

$searchQuery = trim($_GET['q'] ?? '');
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$action = trim($_GET['action'] ?? '');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');

$where = [];
$params = [];

if ($searchQuery !== '') {
	$where[] = "(al.action LIKE ? OR al.resource LIKE ? OR al.details LIKE ? OR al.ip_address LIKE ?)";
	$like = '%' . $searchQuery . '%';
	$params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
}
if (!empty($userId)) {
	$where[] = "al.user_id = ?";
	$params[] = $userId;
}
if ($action !== '') {
	$where[] = "al.action = ?";
	$params[] = $action;
}
if ($dateFrom !== '') {
	$where[] = "al.created_at >= ?";
	$params[] = $dateFrom . ' 00:00:00';
}
if ($dateTo !== '') {
	$where[] = "al.created_at <= ?";
	$params[] = $dateTo . ' 23:59:59';
}

$whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

$out = fopen('php://output', 'w');
fputcsv($out, ['ID', 'When', 'User ID', 'User Email', 'Action', 'Resource', 'Details', 'IP', 'User Agent']);

try {
	$query = "
		SELECT al.id, al.user_id, al.action, al.resource, al.details, al.ip_address, al.user_agent, al.created_at,
		       u.email AS user_email
		FROM audit_logs al
		LEFT JOIN users u ON u.id = al.user_id
		$whereSql
		ORDER BY al.created_at DESC
		LIMIT 10000
	";
	$rows = $db->fetchAll($query, $params);
	foreach ($rows as $r) {
		fputcsv($out, [
			$r['id'],
			$r['created_at'],
			$r['user_id'],
			$r['user_email'],
			$r['action'],
			$r['resource'],
			$r['details'],
			$r['ip_address'],
			$r['user_agent'],
		]);
	}
} catch (Exception $e) {
	// Write an error row
	fputcsv($out, ['ERROR', $e->getMessage()]);
}

fclose($out);
