<?php
$page_title = 'Privacy & Consents - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

$consentTypes = ['marketing_emails','analytics_tracking','personalization','third_party_sharing'];

?>

<div class="fade-in">
	<div class="enterprise-card mb-4">
		<div class="enterprise-card-header d-flex justify-content-between align-items-center">
			<h2 class="enterprise-card-title"><i class="fas fa-user-lock me-2"></i> Privacy & Consents</h2>
			<div>
				<a href="#" class="enterprise-btn enterprise-btn-outline" onclick="privacy.loadUser()"><i class="fas fa-search"></i> Load User</a>
				<a href="#" class="enterprise-btn enterprise-btn-primary" onclick="privacy.save()"><i class="fas fa-save"></i> Save</a>
			</div>
		</div>
		<div class="enterprise-card-body">
			<div class="row g-3 align-items-end">
				<div class="col-md-4">
					<label class="form-label fw-semibold">User (email or ID)</label>
					<input type="text" class="form-control" id="ps_user" placeholder="user@example.com or 123">
				</div>
				<div class="col-md-8">
					<div class="row">
						<?php foreach ($consentTypes as $ct): ?>
						<div class="col-md-3">
							<div class="form-check form-switch">
								<input class="form-check-input" type="checkbox" id="consent_<?= $ct ?>">
								<label class="form-check-label" for="consent_<?= $ct ?>"><?= ucwords(str_replace('_',' ', $ct)) ?></label>
							</div>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="enterprise-card">
		<div class="enterprise-card-header"><h4 class="enterprise-card-title"><i class="fas fa-history me-2"></i> Latest Consents</h4></div>
		<div class="enterprise-card-body">
			<div id="ps_log"></div>
		</div>
	</div>
</div>

<script>
const privacy = {
	userId: null,
	loadUser() {
		const q = document.getElementById('ps_user').value.trim();
		if (!q) { adminSystem.showNotification('Enter user email or ID', 'warning'); return; }
		fetch('ajax/privacy_actions.php?action=load&query=' + encodeURIComponent(q))
			.then(r => r.json()).then(d => {
				if (!d.success) { adminSystem.showNotification(d.error || 'Failed', 'danger'); return; }
				this.userId = d.user.id;
				for (const k in d.consents) {
					const el = document.getElementById('consent_' + k);
					if (el) el.checked = d.consents[k] === 'granted';
				}
				this.refreshLog();
			});
	},
	save() {
		if (!this.userId) { adminSystem.showNotification('Load user first', 'warning'); return; }
		const payload = { user_id: this.userId, consents: {} };
		['marketing_emails','analytics_tracking','personalization','third_party_sharing'].forEach(k => {
			payload.consents[k] = document.getElementById('consent_' + k).checked ? 'granted' : 'denied';
		});
		fetch('ajax/privacy_actions.php?action=save', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
			.then(r => r.json()).then(d => {
				if (d.success) { adminSystem.showNotification('Saved', 'success'); this.refreshLog(); }
				else { adminSystem.showNotification(d.error || 'Failed', 'danger'); }
			});
	},
	refreshLog() {
		if (!this.userId) return;
		fetch('ajax/privacy_actions.php?action=recent&user_id=' + this.userId)
			.then(r => r.text()).then(html => document.getElementById('ps_log').innerHTML = html);
	}
};
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
