# Secrets Management

This directory contains template files for secrets and sensitive configuration. **NEVER commit actual secrets to version control.**

## Files

- `database.env.template` - Database connection secrets
- `payment.env.template` - Payment gateway API keys
- `ai.env.template` - AI service API keys
- `oauth.env.template` - OAuth provider credentials
- `monitoring.env.template` - Monitoring service credentials

## Usage

1. Copy the template files to `.env` files
2. Fill in your actual secret values
3. Add `.env` files to `.gitignore`
4. Use environment variables in your application

## Security Best Practices

- Use strong, unique passwords
- Rotate secrets regularly
- Use different secrets for different environments
- Never log or expose secrets in error messages
- Use secret management services in production (AWS Secrets Manager, Azure Key Vault, etc.)

## Example

```bash
# Copy template
cp secrets/database.env.template .env.database

# Edit with actual values
nano .env.database

# Source in your application
source .env.database
```
