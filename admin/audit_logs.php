<?php
$page_title = 'Audit Logs - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

// Default filter values
$searchQuery = trim($_GET['q'] ?? '');
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$action = trim($_GET['action'] ?? '');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$pageSize = min(100, max(10, (int)($_GET['page_size'] ?? 25)));
$offset = ($page - 1) * $pageSize;

// Build WHERE conditions safely
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

// Fetch total count
$totalCount = 0;
try {
	$row = $db->fetch("SELECT COUNT(*) AS cnt FROM audit_logs al $whereSql", $params);
	$totalCount = (int)($row['cnt'] ?? 0);
} catch (Exception $e) {
	error_log('Audit logs count error: ' . $e->getMessage());
}

// Fetch paginated logs
$logs = [];
try {
	$query = "
		SELECT al.id, al.user_id, al.action, al.resource, al.details, al.ip_address, al.user_agent, al.created_at,
		       u.email AS user_email, u.nom AS user_name
		FROM audit_logs al
		LEFT JOIN users u ON u.id = al.user_id
		$whereSql
		ORDER BY al.created_at DESC
		LIMIT $pageSize OFFSET $offset
	";
	$logs = $db->fetchAll($query, $params);
} catch (Exception $e) {
	error_log('Audit logs fetch error: ' . $e->getMessage());
}

// Fetch distinct actions for filter
$actions = [];
try {
	$actions = $db->fetchAll("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC");
} catch (Exception $e) {
	error_log('Audit logs actions fetch error: ' . $e->getMessage());
}

$totalPages = max(1, (int)ceil($totalCount / $pageSize));
?>

<div class="fade-in">
	<div class="enterprise-card mb-4">
		<div class="enterprise-card-header d-flex justify-content-between align-items-center">
			<h2 class="enterprise-card-title">
				<i class="fas fa-clipboard-list me-2"></i>
				Audit Logs
			</h2>
			<div>
				<a href="?" class="enterprise-btn enterprise-btn-outline">
					<i class="fas fa-undo"></i> Reset
				</a>
			</div>
		</div>
		<div class="enterprise-card-body">
			<form class="row g-3" method="get">
				<div class="col-md-4">
					<label class="form-label fw-semibold">Search</label>
					<input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" class="form-control" placeholder="Action, resource, IP, details">
				</div>
				<div class="col-md-2">
					<label class="form-label fw-semibold">User ID</label>
					<input type="number" name="user_id" value="<?= htmlspecialchars((string)$userId) ?>" class="form-control" placeholder="e.g. 123">
				</div>
				<div class="col-md-3">
					<label class="form-label fw-semibold">Action</label>
					<select name="action" class="form-select">
						<option value="">All</option>
						<?php foreach ($actions as $row): $val = $row['action']; ?>
							<option value="<?= htmlspecialchars($val) ?>" <?= $action === $val ? 'selected' : '' ?>><?= htmlspecialchars($val) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="col-md-3">
					<label class="form-label fw-semibold">Date range</label>
					<div class="d-flex gap-2">
						<input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>" class="form-control">
						<input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>" class="form-control">
					</div>
				</div>
				<div class="col-12 d-flex justify-content-end">
					<button type="submit" class="enterprise-btn enterprise-btn-primary">
						<i class="fas fa-search"></i> Filter
					</button>
				</div>
			</form>
		</div>
	</div>

	<div class="enterprise-card">
		<div class="enterprise-card-header d-flex justify-content-between align-items-center">
			<h4 class="enterprise-card-title"><i class="fas fa-list me-2"></i> Results (<?= number_format($totalCount) ?>)</h4>
			<div class="d-flex align-items-center gap-2">
				<form method="get">
					<?php foreach ($_GET as $k => $v): if ($k === 'page_size') continue; ?>
						<input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
					<?php endforeach; ?>
					<select name="page_size" class="form-select form-select-sm" onchange="this.form.submit()">
						<?php foreach ([25,50,100] as $sz): ?>
							<option value="<?= $sz ?>" <?= $pageSize === $sz ? 'selected' : '' ?>><?= $sz ?>/page</option>
						<?php endforeach; ?>
					</select>
				</form>
				<a class="enterprise-btn enterprise-btn-outline enterprise-btn-sm" href="export_audit_logs.php?<?= http_build_query($_GET) ?>">
					<i class="fas fa-download"></i> Export CSV
				</a>
			</div>
		</div>
		<div class="enterprise-card-body">
			<div class="table-responsive">
				<table class="table table-hover align-middle">
					<thead>
						<tr>
							<th>ID</th>
							<th>When</th>
							<th>User</th>
							<th>Action</th>
							<th>Resource</th>
							<th>Details</th>
							<th>IP</th>
							<th>User Agent</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!$logs): ?>
							<tr><td colspan="8" class="text-center text-muted">No results</td></tr>
						<?php else: foreach ($logs as $log): ?>
							<tr>
								<td><?= (int)$log['id'] ?></td>
								<td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($log['created_at']))) ?></td>
								<td>
									<?php if ($log['user_id']): ?>
										<span class="enterprise-badge enterprise-badge-primary">#<?= (int)$log['user_id'] ?></span>
										<div class="small text-muted"><?= htmlspecialchars($log['user_email'] ?: $log['user_name'] ?: '') ?></div>
									<?php else: ?>
										<span class="text-muted">System</span>
									<?php endif; ?>
								</td>
								<td><span class="enterprise-badge enterprise-badge-info"><?= htmlspecialchars($log['action']) ?></span></td>
								<td><?= htmlspecialchars($log['resource']) ?></td>
								<td style="max-width: 360px;">
									<div class="text-truncate" title="<?= htmlspecialchars($log['details']) ?>">
										<?= htmlspecialchars($log['details']) ?>
									</div>
								</td>
								<td><?= htmlspecialchars($log['ip_address']) ?></td>
								<td>
									<div class="text-truncate" style="max-width: 260px;" title="<?= htmlspecialchars($log['user_agent']) ?>">
										<?= htmlspecialchars($log['user_agent']) ?>
									</div>
								</td>
							</tr>
						<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div>

			<?php if ($totalPages > 1): $qs = $_GET; ?>
			<nav class="mt-3">
				<ul class="pagination justify-content-center">
					<?php for ($p = 1; $p <= $totalPages; $p++): $qs['page'] = $p; ?>
					<li class="page-item <?= $p === $page ? 'active' : '' ?>">
						<a class="page-link" href="?<?= htmlspecialchars(http_build_query($qs)) ?>"><?= $p ?></a>
					</li>
					<?php endfor; ?>
				</ul>
			</nav>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
