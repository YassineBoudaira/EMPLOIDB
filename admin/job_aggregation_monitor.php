<?php
$page_title = 'Job Aggregation Monitoring - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

$sinceHours = (int)($_GET['hours'] ?? 24);
$sinceHours = max(1, min(168, $sinceHours));

$since = date('Y-m-d H:i:s', time() - $sinceHours * 3600);

$stats = [
	'source_counts' => [],
	'fetch_counts' => [],
	'duplicates' => 0,
	'total_raw_jobs' => 0,
	'errors' => [],
];

try {
	$row = $db->fetch("SELECT COUNT(*) AS c FROM raw_jobs WHERE created_at >= ?", [$since]);
	$stats['total_raw_jobs'] = (int)($row['c'] ?? 0);

	$stats['source_counts'] = $db->fetchAll("SELECT source, COUNT(*) AS c FROM raw_jobs WHERE created_at >= ? GROUP BY source ORDER BY c DESC", [$since]);

	$stats['fetch_counts'] = $db->fetchAll("SELECT DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') AS hour, COUNT(*) AS c FROM raw_jobs WHERE created_at >= ? GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') ORDER BY hour DESC LIMIT 48", [$since]);

	$dupRow = $db->fetch("SELECT COUNT(*) AS dups FROM (SELECT url, COUNT(*) AS c FROM raw_jobs WHERE created_at >= ? GROUP BY url HAVING COUNT(*) > 1) t", [$since]);
	$stats['duplicates'] = (int)($dupRow['dups'] ?? 0);
} catch (Exception $e) {
	error_log('Aggregation monitoring DB error: ' . $e->getMessage());
}

// Read recent errors from log file if exists
$logFile = __DIR__ . '/logs/job_aggregation.log';
if (file_exists($logFile)) {
	$lines = @file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
	$lines = array_slice(array_reverse($lines), 0, 100);
	foreach ($lines as $line) {
		if (stripos($line, 'error') !== false || stripos($line, 'fail') !== false) {
			$stats['errors'][] = $line;
		}
	}
}
?>

<div class="fade-in">
	<div class="enterprise-card mb-4">
		<div class="enterprise-card-header d-flex justify-content-between align-items-center">
			<h2 class="enterprise-card-title"><i class="fas fa-heartbeat me-2"></i> Aggregation Health</h2>
			<form class="d-flex align-items-center gap-2" method="get">
				<label class="form-label mb-0">Since</label>
				<select name="hours" class="form-select form-select-sm" onchange="this.form.submit()">
					<?php foreach ([6,12,24,48,72,168] as $h): ?>
						<option value="<?= $h ?>" <?= $sinceHours===$h?'selected':'' ?>><?= $h ?>h</option>
					<?php endforeach; ?>
				</select>
			</form>
		</div>
		<div class="enterprise-card-body">
			<div class="row text-center">
				<div class="col-md-3 mb-3">
					<div class="enterprise-card h-100">
						<div class="enterprise-card-body">
							<div class="stat-icon bg-success bg-opacity-10 rounded-circle p-3 mb-2"><i class="fas fa-database text-success"></i></div>
							<h3 class="stat-value text-success mb-0"><?= number_format($stats['total_raw_jobs']) ?></h3>
							<small class="text-muted">Raw jobs ingested</small>
						</div>
					</div>
				</div>
				<div class="col-md-3 mb-3">
					<div class="enterprise-card h-100">
						<div class="enterprise-card-body">
							<div class="stat-icon bg-warning bg-opacity-10 rounded-circle p-3 mb-2"><i class="fas fa-clone text-warning"></i></div>
							<h3 class="stat-value text-warning mb-0"><?= number_format($stats['duplicates']) ?></h3>
							<small class="text-muted">Duplicate URLs</small>
						</div>
					</div>
				</div>
				<div class="col-md-3 mb-3">
					<div class="enterprise-card h-100">
						<div class="enterprise-card-body">
							<div class="stat-icon bg-info bg-opacity-10 rounded-circle p-3 mb-2"><i class="fas fa-clock text-info"></i></div>
							<h3 class="stat-value text-info mb-0"><?= htmlspecialchars($sinceHours) ?>h</h3>
							<small class="text-muted">Window</small>
						</div>
					</div>
				</div>
				<div class="col-md-3 mb-3">
					<div class="enterprise-card h-100">
						<div class="enterprise-card-body">
							<div class="stat-icon bg-danger bg-opacity-10 rounded-circle p-3 mb-2"><i class="fas fa-exclamation-triangle text-danger"></i></div>
							<h3 class="stat-value text-danger mb-0"><?= number_format(count($stats['errors'])) ?></h3>
							<small class="text-muted">Recent errors (log)</small>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row mb-4">
		<div class="col-xl-6 mb-4">
			<div class="enterprise-card h-100">
				<div class="enterprise-card-header"><h4 class="enterprise-card-title"><i class="fas fa-stream me-2"></i> By Source</h4></div>
				<div class="enterprise-card-body">
					<div class="table-responsive">
						<table class="table table-hover">
							<thead><tr><th>Source</th><th class="text-end">Count</th></tr></thead>
							<tbody>
								<?php foreach ($stats['source_counts'] as $r): ?>
								<tr>
									<td><?= htmlspecialchars($r['source'] ?? 'unknown') ?></td>
									<td class="text-end"><span class="enterprise-badge enterprise-badge-primary"><?= number_format($r['c']) ?></span></td>
								</tr>
								<?php endforeach; if (!$stats['source_counts']): ?>
								<tr><td colspan="2" class="text-center text-muted">No data</td></tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xl-6 mb-4">
			<div class="enterprise-card h-100">
				<div class="enterprise-card-header"><h4 class="enterprise-card-title"><i class="fas fa-history me-2"></i> Last 48 hours</h4></div>
				<div class="enterprise-card-body">
					<div class="table-responsive">
						<table class="table table-hover">
							<thead><tr><th>Hour</th><th class="text-end">Count</th></tr></thead>
							<tbody>
								<?php foreach ($stats['fetch_counts'] as $r): ?>
								<tr>
									<td><?= htmlspecialchars($r['hour']) ?></td>
									<td class="text-end"><span class="enterprise-badge enterprise-badge-info"><?= number_format($r['c']) ?></span></td>
								</tr>
								<?php endforeach; if (!$stats['fetch_counts']): ?>
								<tr><td colspan="2" class="text-center text-muted">No data</td></tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="enterprise-card">
		<div class="enterprise-card-header d-flex justify-content-between align-items-center">
			<h4 class="enterprise-card-title"><i class="fas fa-bug me-2"></i> Recent Errors (from log)</h4>
			<a class="enterprise-btn enterprise-btn-outline enterprise-btn-sm" href="logs/job_aggregation.log" target="_blank"><i class="fas fa-external-link-alt"></i> Open log</a>
		</div>
		<div class="enterprise-card-body">
			<?php if (!$stats['errors']): ?>
				<div class="text-muted">No recent errors found.</div>
			<?php else: ?>
				<div class="list-group">
					<?php foreach ($stats['errors'] as $line): ?>
						<div class="list-group-item small">
							<code><?= htmlspecialchars($line) ?></code>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
