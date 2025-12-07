# ChiBank Deployment Scripts

This directory contains deployment and bootstrap scripts for the ChiBank application.

## Scripts Overview

### 1. `bootstrap_and_deploy.sh`
Complete setup script for a fresh Ubuntu 22.04 LTS server. Installs all dependencies and deploys ChiBank.

**What it does:**
- Installs Nginx web server
- Installs PHP 8.1 (or specified version) with all required extensions
- Installs MySQL database server
- Installs Composer
- Installs Node.js
- Installs Certbot for SSL certificates
- Clones/updates the ChiBank repository
- Calls `deploy_server.sh` to complete deployment

**Usage:**

Non-interactive mode (recommended for automation):
```bash
export NONINTERACTIVE=yes
export DOMAIN=example.com
export DB_NAME=chibank
export DB_USER=chibankuser
export DB_PASS='your_secure_password'
export MYSQL_ROOT_PASS='your_mysql_root_password'
sudo bash scripts/bootstrap_and_deploy.sh
```

Interactive mode:
```bash
sudo bash scripts/bootstrap_and_deploy.sh
```

**Environment Variables:**
- `DOMAIN` - Your domain name (default: example.com)
- `DB_NAME` - Database name (default: chibank)
- `DB_USER` - Database username (default: chibankuser)
- `DB_PASS` - Database password (required in non-interactive mode)
- `MYSQL_ROOT_PASS` - MySQL root password (required in non-interactive mode)
- `NONINTERACTIVE` - Set to "yes" for non-interactive mode (default: no)
- `PHP_VERSION` - PHP version to install (default: 8.1)
- `CHOWN_USER` - User for file ownership (default: www-data)
- `GITHUB_REPO` - Git repository URL (default: https://github.com/hhongli1979-coder/chibank999.git)

### 2. `deploy_server.sh`
Deployment script for servers with existing runtime environment (PHP, Nginx, MySQL already installed).

**What it does:**
- Backs up existing .env file
- Configures environment variables
- Installs Composer dependencies
- Generates application key
- Runs database migrations
- Optimizes application (caches routes, config, views)
- Creates storage symlink
- Sets proper file permissions
- Configures Nginx
- Optionally sets up SSL with Let's Encrypt

**Usage:**

Non-interactive mode:
```bash
export NONINTERACTIVE=yes
export DOMAIN=example.com
export DB_DATABASE=chibank
export DB_USERNAME=chibankuser
export DB_PASSWORD='your_secure_password'
export CHOWN_USER=www-data
sudo bash scripts/deploy_server.sh
```

Interactive mode:
```bash
sudo bash scripts/deploy_server.sh
```

**Environment Variables:**
- `DOMAIN` - Your domain name (default: example.com)
- `DB_DATABASE` - Database name (default: chibank)
- `DB_USERNAME` - Database username (default: chibankuser)
- `DB_PASSWORD` - Database password (required)
- `CHOWN_USER` - User for file ownership (default: www-data)
- `NONINTERACTIVE` - Set to "yes" for non-interactive mode (default: no)
- `PHP_VERSION` - PHP version (default: 8.1)
- `SSL_EMAIL` - Email for SSL certificate registration (default: admin@DOMAIN)

## Requirements

- Ubuntu 22.04 LTS (recommended)
- Root or sudo privileges
- Internet connection

## Security Notes

1. **Password Handling**: Scripts use temporary configuration files for MySQL operations to avoid password exposure in process lists
2. **Backups**: .env files are automatically backed up with timestamps before modifications
3. **File Permissions**: Proper ownership and permissions are set for security
4. **SSL**: Optional SSL certificate setup with Let's Encrypt

## Rollback Instructions

If you need to rollback after deployment:

1. **Repository Level**: This PR only modifies repository files
   - On GitHub: Revert the merge commit
   - Or close/revert the PR

2. **Server Level**: Scripts create backups
   - Restore .env from `.env.backup.TIMESTAMP` files
   - Example: `cp .env.backup.20231206_120000 .env`

3. **Database**: Consider creating database backups before deployment
   ```bash
   mysqldump -u root -p chibank > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

## Testing

### Test on Fresh Ubuntu 22.04:
```bash
export NONINTERACTIVE=yes \
  DOMAIN=test.example.com \
  DB_NAME=chibank_test \
  DB_USER=testuser \
  DB_PASS='TestPass123!' \
  MYSQL_ROOT_PASS='RootPass123!'

sudo bash scripts/bootstrap_and_deploy.sh
```

### Test on Existing Runtime:
```bash
export NONINTERACTIVE=yes \
  DOMAIN=test.example.com \
  DB_DATABASE=chibank_test \
  DB_USERNAME=testuser \
  DB_PASSWORD='TestPass123!' \
  CHOWN_USER=www-data

sudo bash scripts/deploy_server.sh
```

## Troubleshooting

### Common Issues:

1. **Permission Denied**
   - Solution: Run with `sudo`

2. **Port 80/443 Already in Use**
   - Check: `sudo netstat -tlnp | grep :80`
   - Solution: Stop conflicting service or change Nginx config

3. **MySQL Connection Failed**
   - Verify database credentials
   - Check MySQL service: `sudo systemctl status mysql`

4. **Composer Install Fails**
   - Check PHP version compatibility
   - Ensure all PHP extensions are installed

5. **Storage Link Fails**
   - Usually safe to ignore if link already exists
   - Manually create: `ln -s ../storage/app/public public/storage`

## CI/CD Integration

The preflight workflow (`.github/workflows/preflight.yml`) runs automatically on pull requests to validate:
- Composer dependencies
- PHP syntax
- Shell script quality (shellcheck)
- Frontend build

## Support

For issues or questions:
1. Check the main repository documentation
2. Review error logs: `/var/log/nginx/error.log`, Laravel logs in `storage/logs/`
3. Open an issue on GitHub
