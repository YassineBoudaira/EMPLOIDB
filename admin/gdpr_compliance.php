<?php
$page_title = 'GDPR Compliance - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

$success_message = '';
$error_message = '';

?>

<div class="fade-in">
	<div class="enterprise-card mb-4">
		<div class="enterprise-card-header d-flex justify-content-between align-items-center">
			<h2 class="enterprise-card-title"><i class="fas fa-user-shield me-2"></i> GDPR Compliance</h2>
			<div>
				<a href="#" class="enterprise-btn enterprise-btn-outline" onclick="gdpr.exportSelected()"><i class="fas fa-download"></i> Export</a>
				<a href="#" class="enterprise-btn enterprise-btn-danger" onclick="gdpr.deleteSelected()"><i class="fas fa-user-times"></i> Anonymize/Delete</a>
			</div>
		</div>
		<div class="enterprise-card-body">
			<div class="row g-3 align-items-end">
				<div class="col-md-4">
					<label class="form-label fw-semibold">Search user (email or ID)</label>
					<input type="text" class="form-control" id="gdpr_query" placeholder="user@example.com or 123">
				</div>
				<div class="col-md-3">
					<label class="form-label fw-semibold">Action</label>
					<select class="form-select" id="gdpr_action">
						<option value="export">Export</option>
						<option value="delete">Anonymize/Delete</option>
					</select>
				</div>
				<div class="col-md-3">
					<label class="form-label fw-semibold">Reason</label>
					<input type="text" class="form-control" id="gdpr_reason" placeholder="User request, legal, ...">
				</div>
				<div class="col-md-2">
					<button class="enterprise-btn enterprise-btn-primary w-100" onclick="gdpr.submit()"><i class="fas fa-paper-plane"></i> Submit</button>
				</div>
			</div>
		</div>
	</div>

	<div class="enterprise-card">
		<div class="enterprise-card-header d-flex justify-content-between align-items-center">
			<h4 class="enterprise-card-title"><i class="fas fa-history me-2"></i> Recent GDPR Actions</h4>
			<a class="enterprise-btn enterprise-btn-outline enterprise-btn-sm" href="export_audit_logs.php?action=gdpr" target="_blank"><i class="fas fa-download"></i> Export Audit</a>
		</div>
		<div class="enterprise-card-body">
			<div id="gdpr_log"></div>
		</div>
	</div>
</div>

<script>
const gdpr = {
	submit() {
		const q = document.getElementById('gdpr_query').value.trim();
		const action = document.getElementById('gdpr_action').value;
		const reason = document.getElementById('gdpr_reason').value.trim();
		if (!q) { adminSystem.showNotification('Please enter a user email or ID', 'warning'); return; }
		fetch('ajax/gdpr_actions.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({ query: q, action, reason })
		}).then(r => r.json()).then(d => {
			if (d.success) { adminSystem.showNotification(d.message || 'Done', 'success'); this.refreshLog(); }
			else { adminSystem.showNotification(d.error || 'Failed', 'danger'); }
		}).catch(() => adminSystem.showNotification('Network error', 'danger'));
	},
	exportSelected() { document.getElementById('gdpr_action').value = 'export'; this.submit(); },
	deleteSelected() { document.getElementById('gdpr_action').value = 'delete'; this.submit(); },
	refreshLog() {
		fetch('ajax/gdpr_actions.php?recent=1').then(r => r.text()).then(html => { document.getElementById('gdpr_log').innerHTML = html; });
	}
};

document.addEventListener('DOMContentLoaded', () => gdpr.refreshLog());
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
