<?php
$page_title = 'Feature Settings - EMPLOIDB';
include __DIR__ . '/includes/admin_header.php';

function save_setting($db, $key, $value) {
	try {
		$affected = $db->query("UPDATE system_settings SET value = ? WHERE setting_key = ?", [$value, $key]);
		if ($affected === 0) {
			$db->query("INSERT INTO system_settings (setting_key, value) VALUES (?, ?)", [$key, $value]);
		}
	} catch (Exception $e) {
		error_log('Feature settings save error: ' . $e->getMessage());
	}
}

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$enabledSources = $_POST['sources'] ?? [];
	$fetchFrequency = (int)($_POST['fetch_frequency'] ?? 60);
	$aiRewriting = isset($_POST['ai_rewriting']) ? '1' : '0';
	$logoGeneration = isset($_POST['logo_generation']) ? '1' : '0';

	$apiKeys = [
		'OPENAI_API_KEY' => trim($_POST['OPENAI_API_KEY'] ?? ''),
		'HUGGINGFACE_API_KEY' => trim($_POST['HUGGINGFACE_API_KEY'] ?? ''),
		'ELASTICSEARCH_URL' => trim($_POST['ELASTICSEARCH_URL'] ?? ''),
		'STRIPE_SECRET' => trim($_POST['STRIPE_SECRET'] ?? ''),
		'RABBITMQ_HOST' => trim($_POST['RABBITMQ_HOST'] ?? ''),
		'RABBITMQ_PORT' => trim($_POST['RABBITMQ_PORT'] ?? ''),
		'RABBITMQ_USER' => trim($_POST['RABBITMQ_USER'] ?? ''),
		'RABBITMQ_PASS' => trim($_POST['RABBITMQ_PASS'] ?? ''),
	];

	try {
		// Save toggles
		save_setting($db, 'job_sources', json_encode($enabledSources));
		save_setting($db, 'fetch_frequency_minutes', (string)$fetchFrequency);
		save_setting($db, 'feature_ai_rewriting', $aiRewriting);
		save_setting($db, 'feature_logo_generation', $logoGeneration);

		// Save API keys
		foreach ($apiKeys as $k => $v) {
			if ($v !== '') {
				save_setting($db, $k, $v);
			}
		}

		$success_message = 'Settings saved successfully';
	} catch (Exception $e) {
		$error_message = 'Save failed: ' . $e->getMessage();
	}
}

// Load settings
$settings = [];
try {
	$rows = $db->fetchAll("SELECT setting_key, value FROM system_settings WHERE setting_key IN (
		'job_sources','fetch_frequency_minutes','feature_ai_rewriting','feature_logo_generation',
		'OPENAI_API_KEY','HUGGINGFACE_API_KEY','ELASTICSEARCH_URL','STRIPE_SECRET','RABBITMQ_HOST','RABBITMQ_PORT','RABBITMQ_USER','RABBITMQ_PASS'
	)");
	foreach ($rows as $r) {$settings[$r['setting_key']] = $r['value'];}
} catch (Exception $e) { error_log('Feature settings load error: ' . $e->getMessage()); }

$allSources = ['indeed','optioncarriere','reemploy','cvaden','emploi','marocannonces'];
$enabledSources = json_decode($settings['job_sources'] ?? '[]', true) ?: [];
$fetchFrequency = (int)($settings['fetch_frequency_minutes'] ?? 60);
$aiRewriting = ($settings['feature_ai_rewriting'] ?? '0') === '1';
$logoGeneration = ($settings['feature_logo_generation'] ?? '0') === '1';
?>

<div class="fade-in">
	<div class="enterprise-card mb-4">
		<div class="enterprise-card-header d-flex justify-content-between align-items-center">
			<h2 class="enterprise-card-title"><i class="fas fa-sliders-h me-2"></i> Feature Settings</h2>
			<a href="settings.php" class="enterprise-btn enterprise-btn-outline"><i class="fas fa-cog"></i> System Settings</a>
		</div>
		<div class="enterprise-card-body">
			<?php if ($success_message): ?><div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div><?php endif; ?>
			<?php if ($error_message): ?><div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div><?php endif; ?>

			<form method="post">
				<div class="row">
					<div class="col-lg-6 mb-4">
						<div class="enterprise-card h-100">
							<div class="enterprise-card-header"><h4 class="enterprise-card-title"><i class="fas fa-rss me-2"></i> Job Sources</h4></div>
							<div class="enterprise-card-body">
								<?php foreach ($allSources as $src): ?>
								<div class="form-check form-switch mb-2">
									<input class="form-check-input" type="checkbox" id="src_<?= $src ?>" name="sources[]" value="<?= $src ?>" <?= in_array($src, $enabledSources, true) ? 'checked' : '' ?>>
									<label class="form-check-label" for="src_<?= $src ?>"><?= ucfirst($src) ?></label>
								</div>
								<?php endforeach; ?>
								<div class="mt-3">
									<label class="form-label fw-semibold">Fetch Frequency (minutes)</label>
									<input type="number" class="form-control" name="fetch_frequency" min="5" max="1440" value="<?= $fetchFrequency ?>">
									<div class="form-text">How often to fetch jobs from enabled sources.</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-lg-6 mb-4">
						<div class="enterprise-card h-100">
							<div class="enterprise-card-header"><h4 class="enterprise-card-title"><i class="fas fa-magic me-2"></i> AI & Media</h4></div>
							<div class="enterprise-card-body">
								<div class="form-check form-switch mb-3">
									<input class="form-check-input" type="checkbox" id="ai_rewriting" name="ai_rewriting" <?= $aiRewriting ? 'checked' : '' ?>>
									<label class="form-check-label" for="ai_rewriting">Enable AI rewriting</label>
								</div>
								<div class="form-check form-switch mb-3">
									<input class="form-check-input" type="checkbox" id="logo_generation" name="logo_generation" <?= $logoGeneration ? 'checked' : '' ?>>
									<label class="form-check-label" for="logo_generation">Enable logo generation</label>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="enterprise-card mb-4">
					<div class="enterprise-card-header"><h4 class="enterprise-card-title"><i class="fas fa-key me-2"></i> API Keys & Integrations</h4></div>
					<div class="enterprise-card-body">
						<div class="row g-3">
							<div class="col-md-6">
								<label class="form-label fw-semibold">OpenAI API Key</label>
								<input type="password" class="form-control" name="OPENAI_API_KEY" value="" placeholder="sk-...">
							</div>
							<div class="col-md-6">
								<label class="form-label fw-semibold">HuggingFace API Key</label>
								<input type="password" class="form-control" name="HUGGINGFACE_API_KEY" value="" placeholder="hf_...">
							</div>
							<div class="col-md-6">
								<label class="form-label fw-semibold">Elasticsearch URL</label>
								<input type="text" class="form-control" name="ELASTICSEARCH_URL" value="<?= htmlspecialchars($settings['ELASTICSEARCH_URL'] ?? '') ?>" placeholder="http://localhost:9200">
							</div>
							<div class="col-md-6">
								<label class="form-label fw-semibold">Stripe Secret</label>
								<input type="password" class="form-control" name="STRIPE_SECRET" value="" placeholder="sk_live_...">
							</div>
							<div class="col-md-3">
								<label class="form-label fw-semibold">RabbitMQ Host</label>
								<input type="text" class="form-control" name="RABBITMQ_HOST" value="<?= htmlspecialchars($settings['RABBITMQ_HOST'] ?? '127.0.0.1') ?>">
							</div>
							<div class="col-md-3">
								<label class="form-label fw-semibold">RabbitMQ Port</label>
								<input type="number" class="form-control" name="RABBITMQ_PORT" value="<?= htmlspecialchars($settings['RABBITMQ_PORT'] ?? '5672') ?>">
							</div>
							<div class="col-md-3">
								<label class="form-label fw-semibold">RabbitMQ User</label>
								<input type="text" class="form-control" name="RABBITMQ_USER" value="<?= htmlspecialchars($settings['RABBITMQ_USER'] ?? 'guest') ?>">
							</div>
							<div class="col-md-3">
								<label class="form-label fw-semibold">RabbitMQ Pass</label>
								<input type="password" class="form-control" name="RABBITMQ_PASS" value="" placeholder="••••••••">
							</div>
						</div>
					</div>
				</div>

				<div class="d-flex justify-content-end gap-2">
					<button type="reset" class="enterprise-btn enterprise-btn-outline"><i class="fas fa-undo"></i> Reset</button>
					<button type="submit" class="enterprise-btn enterprise-btn-primary"><i class="fas fa-save"></i> Save Settings</button>
				</div>
			</form>
		</div>
	</div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
