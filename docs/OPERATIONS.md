## Operations Runbook

### Startup (local)
- Requirements: PHP 8.1+, MySQL 8, Composer, Node (optional), Docker (optional)
- Copy env: duplicate `env.example` to `.env` and fill values
- Start services (optional): `docker compose -f docker-compose.dev.yml up -d rabbitmq`
- Migrate DB: run your migration runner or import `database/migrations/*.sql`

### Job Aggregation
- Frequency: configured by `fetch_frequency_minutes` in `system_settings`
- Manual run (web): `cron/job_aggregation_cron_web.php`
- Cron: call `php cron/job_aggregation_cron.php` every N minutes
- Logs: `logs/cron.log`, `admin/logs/job_aggregation.log`

### Admin Panels
- Audit Logs: `admin/audit_logs.php` (search, export CSV)
- Aggregation Monitor: `admin/job_aggregation_monitor.php`
- Feature Settings: `admin/feature_settings.php`
- GDPR: `admin/gdpr_compliance.php`
- Privacy & Consents: `admin/privacy_settings.php`

### Email
- Configure SMTP in `admin/settings.php` (Email tab) or `.env`
- Test via settings page or `include/EmailService.php::testEmailConfiguration`

### Incident Response
- Check `admin/admin_system_status.php`
- Review `logs/` and `admin/logs/` files
- Use `admin/audit_logs.php` to trace who/when/what

### Backup/Restore
- DB backup: `mysqldump emploi > backup.sql`
- DB restore: `mysql emploi < backup.sql`

### Security
- Ensure HTTPS, secure cookies, updated dependencies
- Rotate API keys via `admin/feature_settings.php`

### Hostinger Deployment (high-level)
- Upload code via Git or FTP
- Configure PHP 8.1, MySQL, `.env`
- Point domain DNS and enable HTTPS
- Set cron via Hostinger panel to call `php cron/job_aggregation_cron.php`


