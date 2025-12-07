# ChiBank/QRPay Deployment Guide

This document provides comprehensive instructions for packaging, uploading, and deploying the ChiBank/QRPay payment gateway system.

## Table of Contents

1. [Quick Start](#quick-start)
2. [Deployment Methods](#deployment-methods)
3. [Docker Deployment](#docker-deployment)
4. [Traditional Deployment](#traditional-deployment)
5. [CI/CD Automated Deployment](#cicd-automated-deployment)
6. [Environment Configuration](#environment-configuration)
7. [Troubleshooting](#troubleshooting)

---

## Quick Start

### Prerequisites

- **Development Environment**:
  - PHP >= 8.0.2
  - Composer 2.x
  - Node.js >= 20.x
  - MySQL >= 8.0 or MariaDB >= 10.3
  
- **Production Environment** (Docker deployment):
  - Docker >= 20.x
  - Docker Compose >= 2.x

### One-Click Build

```bash
# Clone the repository
git clone https://github.com/LILIANSRL/chibank-.git
cd chibank-

# Run build script
./scripts/build.sh

# Or use production mode
./scripts/build.sh --prod
```

---

## Deployment Methods

ChiBank/QRPay supports three deployment methods:

1. **Docker Deployment** (Recommended) - Simplest and most reliable
2. **Traditional Deployment** - Suitable for traditional server environments
3. **Automated CI/CD** - Automated deployment using GitHub Actions

---

## Docker Deployment

### Method 1: Using Docker Compose (Recommended)

#### 1. Configure Environment Variables

```bash
# Copy environment example file
cp .env.example .env

# Edit .env file and configure key parameters:
# - APP_NAME=ChiBank
# - APP_ENV=production
# - APP_DEBUG=false
# - APP_URL=http://your-domain.com
# - DB_DATABASE=chibank
# - DB_USERNAME=chibank
# - DB_PASSWORD=your_secure_password
```

#### 2. Start Services

```bash
# Build and start all services
docker-compose up -d

# Check service status
docker-compose ps

# View logs
docker-compose logs -f app
```

#### 3. Initialize Database

```bash
# Enter application container
docker-compose exec app sh

# Run database migrations
php artisan migrate --force

# Run database seeding (optional)
php artisan db:seed

# Exit container
exit
```

#### 4. Access Application

Open browser and visit: `http://localhost` or your configured domain

### Method 2: Using Docker Only

#### 1. Build Docker Image

```bash
# Build using provided script
./scripts/docker-build.sh --name chibank/qrpay --version v1.0.0

# Or use Docker command directly
docker build -t chibank/qrpay:latest .
```

#### 2. Run Container

```bash
# Run application container (requires separate database configuration)
docker run -d \
  --name chibank-app \
  -p 80:80 \
  -e DB_HOST=your-db-host \
  -e DB_DATABASE=chibank \
  -e DB_USERNAME=chibank \
  -e DB_PASSWORD=your_password \
  chibank/qrpay:latest
```

#### 3. Push to Registry

```bash
# Login to Docker Hub
docker login

# Push image
./scripts/docker-build.sh --name your-username/chibank --version v1.0.0 --push
```

### Common Docker Commands

```bash
# Stop all services
docker-compose down

# Restart services
docker-compose restart

# View container logs
docker-compose logs -f [service_name]

# Enter container
docker-compose exec app sh

# Clean up unused images and containers
docker system prune -a
```

---

## Traditional Deployment

### 1. Server Preparation

#### Install Required Software

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install -y php8.1 php8.1-fpm php8.1-mysql php8.1-mbstring \
  php8.1-xml php8.1-bcmath php8.1-gd php8.1-curl \
  nginx mysql-server composer nodejs npm

# CentOS/RHEL
sudo yum install -y php81 php81-fpm php81-mysqlnd php81-mbstring \
  php81-xml php81-bcmath php81-gd php81-curl \
  nginx mysql-server composer nodejs npm
```

### 2. Download and Configure

```bash
# Clone code to server
cd /var/www
git clone https://github.com/LILIANSRL/chibank-.git chibank
cd chibank

# Set permissions
sudo chown -R www-data:www-data /var/www/chibank
sudo chmod -R 755 /var/www/chibank/storage
sudo chmod -R 755 /var/www/chibank/bootstrap/cache
```

### 3. Install Dependencies and Build

```bash
# Use build script
./scripts/build.sh --prod

# Or execute manually
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

### 4. Configure Environment

```bash
# Create environment file
cp .env.example .env
php artisan key:generate

# Edit .env file
nano .env
```

### 5. Database Setup

```bash
# Create database
mysql -u root -p
CREATE DATABASE chibank CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'chibank'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON chibank.* TO 'chibank'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force
php artisan db:seed
```

### 6. Configure Nginx

Create Nginx configuration file `/etc/nginx/sites-available/chibank`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/chibank/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/chibank /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 7. Performance Optimization

```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

---

## CI/CD Automated Deployment

The project includes pre-configured GitHub Actions workflows for automated builds and deployment.

### GitHub Actions Workflow

Workflow file located at `.github/workflows/deploy.yml` includes:

1. **Build and Test** - Runs on every push and pull request
2. **Docker Image Build** - Builds on main/master/production branches
3. **Auto Deploy** - Deploys on production branch

### Configure Automated Deployment

#### 1. Set GitHub Secrets

Add the following secrets in your GitHub repository settings:

- `DOCKER_USERNAME` - Docker Hub username (optional)
- `DOCKER_PASSWORD` - Docker Hub password/token (optional)
- `DEPLOY_SSH_KEY` - SSH private key for deployment server
- `DEPLOY_HOST` - Deployment server hostname or IP
- `DEPLOY_USER` - SSH username
- `DEPLOY_PATH` - Deployment path on target server

#### 2. Trigger Automated Deployment

```bash
# Push to production branch to trigger deployment
git checkout production
git merge main
git push origin production

# Or use manual trigger
# Click "Run workflow" on GitHub Actions page
```

### Manual Deployment Script

If not using automated deployment, use the provided deployment script:

```bash
# On the server
cd /var/www/chibank
./scripts/deploy.sh
```

The script performs the following:
1. Enter maintenance mode
2. Pull latest code
3. Update dependencies
4. Build frontend assets
5. Run database migrations (requires confirmation)
6. Clear and rebuild caches
7. Exit maintenance mode

---

## Environment Configuration

### Key Environment Variables

```env
# Application Configuration
APP_NAME=ChiBank
APP_ENV=production
APP_KEY=base64:xxxxx  # Generate using: php artisan key:generate
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chibank
DB_USERNAME=chibank
DB_PASSWORD=your_secure_password

# Cache Configuration
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_email@example.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Production Environment Optimization

1. **Enable OPcache**
   ```ini
   # php.ini
   opcache.enable=1
   opcache.memory_consumption=256
   opcache.max_accelerated_files=20000
   ```

2. **Configure Queue Worker**
   ```bash
   # Use Supervisor to manage queues
   sudo apt install supervisor
   
   # Create config file /etc/supervisor/conf.d/chibank-worker.conf
   [program:chibank-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /var/www/chibank/artisan queue:work --sleep=3 --tries=3
   autostart=true
   autorestart=true
   user=www-data
   numprocs=2
   redirect_stderr=true
   stdout_logfile=/var/www/chibank/storage/logs/worker.log
   ```

3. **Setup Cron Jobs**
   ```bash
   # Add to crontab
   * * * * * cd /var/www/chibank && php artisan schedule:run >> /dev/null 2>&1
   ```

4. **Configure SSL/HTTPS**
   ```bash
   # Using Let's Encrypt
   sudo apt install certbot python3-certbot-nginx
   sudo certbot --nginx -d your-domain.com
   ```

---

## Troubleshooting

### Common Issues

#### 1. 500 Server Error

```bash
# Check logs
tail -f storage/logs/laravel.log

# Check permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

#### 2. Frontend Assets Not Loading

```bash
# Rebuild frontend assets
npm run build

# Check if public/build directory exists
ls -la public/build/

# Verify APP_URL in .env is correct
```

#### 3. Database Connection Failed

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check MySQL service
sudo systemctl status mysql

# Check firewall
sudo ufw status
```

#### 4. Docker Container Won't Start

```bash
# View container logs
docker-compose logs app

# Check port usage
sudo netstat -tulpn | grep :80

# Rebuild image
docker-compose build --no-cache
docker-compose up -d
```

#### 5. File Upload Issues

```bash
# Check upload directory permissions
chmod 755 storage/app/public
php artisan storage:link

# Check PHP configuration
php -i | grep upload_max_filesize
php -i | grep post_max_size
```

### Performance Issues

#### Slow Application Response

```bash
# Enable caching
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize --classmap-authoritative

# Check database queries
# Enable query logging in .env
DB_LOG_QUERIES=true
```

#### Out of Memory

```bash
# Increase PHP memory limit
# In php.ini
memory_limit = 512M

# Optimize Composer
php -d memory_limit=-1 /usr/local/bin/composer install
```

### Getting Help

If you encounter issues you cannot resolve:

1. Check detailed logs: `storage/logs/laravel.log`
2. Review GitHub Issues: https://github.com/LILIANSRL/chibank-/issues
3. Consult full documentation: `docs/en/OPERATION-MANUAL.md`

---

## Summary

This deployment guide covers all deployment methods for ChiBank/QRPay:

- ✅ **Docker Deployment** (Recommended) - Fast, reliable, easy to maintain
- ✅ **Traditional Deployment** - Flexible, suitable for customization
- ✅ **Automated CI/CD** - Efficient, suitable for team collaboration

Choose the deployment method that best fits your needs and enjoy the convenient payment experience provided by ChiBank/QRPay!
