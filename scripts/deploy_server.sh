#!/bin/bash

###############################################################################
# ChiBank Server Deployment Script
# This script deploys the ChiBank application on an existing server environment
###############################################################################

set -e

# Color definitions
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration variables
DOMAIN="${DOMAIN:-example.com}"
DB_DATABASE="${DB_DATABASE:-chibank}"
DB_USERNAME="${DB_USERNAME:-chibankuser}"
DB_PASSWORD="${DB_PASSWORD:-}"
CHOWN_USER="${CHOWN_USER:-www-data}"
NONINTERACTIVE="${NONINTERACTIVE:-no}"
PROJECT_DIR="/var/www/${DOMAIN}"
PHP_VERSION="${PHP_VERSION:-8.1}"
SSL_EMAIL="${SSL_EMAIL:-admin@${DOMAIN}}"

###############################################################################
# Helper Functions
###############################################################################

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

log_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# Safe .env value update using awk
update_env_value() {
    local env_file="$1"
    local key="$2"
    local value="$3"
    
    # Create temp file
    local temp_file="${env_file}.tmp"
    
    # Use awk to safely replace or add the key-value pair
    awk -v key="${key}" -v value="${value}" '
    BEGIN { found=0; }
    {
        if ($0 ~ "^" key "=") {
            print key "=" value;
            found=1;
        } else {
            print $0;
        }
    }
    END {
        if (!found) {
            print key "=" value;
        }
    }
    ' "${env_file}" > "${temp_file}"
    
    # Replace original file
    mv "${temp_file}" "${env_file}"
}

confirm() {
    if [ "${NONINTERACTIVE}" = "yes" ]; then
        return 0
    fi
    
    local message="$1"
    read -p "${message} (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        return 0
    else
        return 1
    fi
}

###############################################################################
# Main Deployment Steps
###############################################################################

main() {
    log_info "Starting ChiBank deployment..."
    log_info "Domain: ${DOMAIN}"
    log_info "Project Directory: ${PROJECT_DIR}"
    
    # Validate required variables
    if [ -z "${DB_PASSWORD}" ]; then
        log_error "DB_PASSWORD is required!"
        exit 1
    fi
    
    # Step 1: Backup current .env if exists
    log_step "Backing up current .env file..."
    if [ -f "${PROJECT_DIR}/.env" ]; then
        cp "${PROJECT_DIR}/.env" "${PROJECT_DIR}/.env.backup.$(date +%Y%m%d_%H%M%S)"
        log_info ".env backed up successfully"
    fi
    
    # Step 2: Setup or update .env file
    log_step "Configuring environment variables..."
    if [ ! -f "${PROJECT_DIR}/.env" ]; then
        if [ -f "${PROJECT_DIR}/.env.example" ]; then
            cp "${PROJECT_DIR}/.env.example" "${PROJECT_DIR}/.env"
        else
            touch "${PROJECT_DIR}/.env"
        fi
    fi
    
    # Update .env values safely
    update_env_value "${PROJECT_DIR}/.env" "APP_NAME" "ChiBank"
    update_env_value "${PROJECT_DIR}/.env" "APP_ENV" "production"
    update_env_value "${PROJECT_DIR}/.env" "APP_DEBUG" "false"
    update_env_value "${PROJECT_DIR}/.env" "APP_URL" "https://${DOMAIN}"
    update_env_value "${PROJECT_DIR}/.env" "DB_CONNECTION" "mysql"
    update_env_value "${PROJECT_DIR}/.env" "DB_HOST" "127.0.0.1"
    update_env_value "${PROJECT_DIR}/.env" "DB_PORT" "3306"
    update_env_value "${PROJECT_DIR}/.env" "DB_DATABASE" "${DB_DATABASE}"
    update_env_value "${PROJECT_DIR}/.env" "DB_USERNAME" "${DB_USERNAME}"
    update_env_value "${PROJECT_DIR}/.env" "DB_PASSWORD" "\"${DB_PASSWORD}\""
    
    log_info "Environment variables configured"
    
    # Step 3: Install/Update Composer dependencies
    log_step "Installing Composer dependencies..."
    cd "${PROJECT_DIR}"
    composer install --no-dev --optimize-autoloader --no-interaction
    
    # Step 4: Generate application key if not set
    log_step "Generating application key..."
    php artisan key:generate --force
    
    # Step 5: Run migrations
    log_step "Running database migrations..."
    php artisan migrate --force
    
    # Step 6: Clear and cache configuration
    log_step "Optimizing application..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Step 7: Create storage link
    log_step "Creating storage link..."
    php artisan storage:link || true
    
    # Step 8: Set proper permissions
    log_step "Setting proper permissions..."
    chown -R "${CHOWN_USER}":"${CHOWN_USER}" "${PROJECT_DIR}"
    chmod -R 755 "${PROJECT_DIR}/storage"
    chmod -R 755 "${PROJECT_DIR}/bootstrap/cache"
    
    # Step 9: Setup Nginx configuration
    log_step "Configuring Nginx..."
    
    cat > "/etc/nginx/sites-available/${DOMAIN}" <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN} www.${DOMAIN};
    root ${PROJECT_DIR}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php"${PHP_VERSION}"-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
    
    # Enable site
    ln -sf "/etc/nginx/sites-available/${DOMAIN}" "/etc/nginx/sites-enabled/"
    
    # Test Nginx configuration
    nginx -t
    
    # Reload Nginx
    systemctl reload nginx
    
    log_info "Nginx configured successfully"
    
    # Step 10: Restart PHP-FPM
    log_step "Restarting PHP-FPM..."
    systemctl restart "php${PHP_VERSION}-fpm"
    
    log_info "PHP-FPM restarted successfully"
    
    # Step 11: Setup SSL with Certbot (optional)
    if confirm "Do you want to setup SSL certificate with Let's Encrypt?"; then
        log_step "Setting up SSL certificate..."
        certbot --nginx -d "${DOMAIN}" -d "www.${DOMAIN}" --non-interactive --agree-tos --email "${SSL_EMAIL}" || {
            log_warn "SSL setup failed. You can run 'certbot --nginx -d ${DOMAIN}' manually later."
        }
    fi
    
    log_info "========================================"
    log_info "Deployment completed successfully!"
    log_info "========================================"
    log_info "Your site is available at: https://${DOMAIN}"
    log_info ""
    log_info "Next steps:"
    log_info "1. Visit your site and complete the installation wizard"
    log_info "2. Configure your payment gateways"
    log_info "3. Setup your email settings"
    log_info ""
    log_info "Backup files are stored in: ${PROJECT_DIR}/.env.backup.*"
}

# Run main function
main
