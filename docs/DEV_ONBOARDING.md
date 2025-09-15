## Developer Onboarding

### Prerequisites
- PHP 8.1+, Composer
- Python 3.10+ (for services)
- MySQL 8
- Docker (optional for RabbitMQ)

### Setup
1. Clone repo and checkout `nextgen/saas-refactor`
2. Copy `env.example` to `.env` and fill DB/SMTP/API keys
3. Composer install (if needed for libs): `composer install`
4. Start RabbitMQ (optional): `docker compose -f docker-compose.dev.yml up -d`
5. Create DB and import `database/migrations/*.sql`

### Running
- PHP app: via local server (Laragon/Apache/Nginx)
- Services (optional): see `services/`
- Cron: `php cron/job_aggregation_cron.php`

### Useful Admin Pages
- Dashboard: `admin/dashboard.php`
- Jobs: `admin/jobs.php`
- Audit Logs: `admin/audit_logs.php`
- Aggregation Monitor: `admin/job_aggregation_monitor.php`
- Feature Settings: `admin/feature_settings.php`
- GDPR: `admin/gdpr_compliance.php`
- Privacy & Consents: `admin/privacy_settings.php`

### Testing
- Add PHPUnit config in `phpunit.xml` and run `vendor/bin/phpunit`
- Stubs in `tests/` (to be expanded)

### Coding Standards
- PHP: PSR-12, clear naming, handle errors, avoid deep nesting
- Keep secrets in env


