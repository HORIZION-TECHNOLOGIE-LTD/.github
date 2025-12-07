#!/bin/bash

###############################################################################
# ChiBank Bootstrap and Deployment Script
# This script installs all required dependencies and deploys ChiBank
# Designed for Ubuntu 22.04 LTS
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
DB_NAME="${DB_NAME:-chibank}"
DB_USER="${DB_USER:-chibankuser}"
DB_PASS="${DB_PASS:-}"
MYSQL_ROOT_PASS="${MYSQL_ROOT_PASS:-}"
NONINTERACTIVE="${NONINTERACTIVE:-no}"
PHP_VERSION="${PHP_VERSION:-8.1}"
CHOWN_USER="${CHOWN_USER:-www-data}"
PROJECT_DIR="/var/www/${DOMAIN}"
GITHUB_REPO="${GITHUB_REPO:-https://github.com/hhongli1979-coder/chibank999.git}"

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
# Installation Functions
###############################################################################

check_root() {
    if [ "$EUID" -ne 0 ]; then 
        log_error "Please run this script as root or with sudo"
        exit 1
    fi
}

validate_requirements() {
    log_step "Validating requirements..."
    
    # Check if running in non-interactive mode with required variables
    if [ "${NONINTERACTIVE}" = "yes" ]; then
        if [ -z "${MYSQL_ROOT_PASS}" ]; then
            log_error "MYSQL_ROOT_PASS is required in non-interactive mode!"
            exit 1
        fi
        if [ -z "${DB_PASS}" ]; then
            log_error "DB_PASS is required in non-interactive mode!"
            exit 1
        fi
    fi
    
    log_info "Requirements validation passed"
}

install_system_updates() {
    log_step "Updating system packages..."
    apt-get update -qq
    apt-get upgrade -y -qq
    log_info "System packages updated"
}

install_basic_packages() {
    log_step "Installing basic packages..."
    DEBIAN_FRONTEND=noninteractive apt-get install -y -qq \
        software-properties-common \
        apt-transport-https \
        ca-certificates \
        curl \
        wget \
        git \
        unzip \
        gnupg \
        lsb-release
    log_info "Basic packages installed"
}

install_nginx() {
    log_step "Installing Nginx..."
    if ! command -v nginx &> /dev/null; then
        DEBIAN_FRONTEND=noninteractive apt-get install -y -qq nginx
        systemctl enable nginx
        systemctl start nginx
        log_info "Nginx installed successfully"
    else
        log_info "Nginx already installed"
    fi
}

install_php() {
    log_step "Installing PHP ${PHP_VERSION}..."
    
    # Add Ondřej's PHP PPA
    if ! grep -q "ondrej/php" /etc/apt/sources.list.d/*.list; then
        add-apt-repository ppa:ondrej/php -y
        apt-get update -qq
    fi
    
    # Install PHP and common extensions
    DEBIAN_FRONTEND=noninteractive apt-get install -y -qq \
        "php${PHP_VERSION}" \
        "php${PHP_VERSION}-fpm" \
        "php${PHP_VERSION}-cli" \
        "php${PHP_VERSION}-common" \
        "php${PHP_VERSION}-mysql" \
        "php${PHP_VERSION}-xml" \
        "php${PHP_VERSION}-curl" \
        "php${PHP_VERSION}-gd" \
        "php${PHP_VERSION}-mbstring" \
        "php${PHP_VERSION}-zip" \
        "php${PHP_VERSION}-bcmath" \
        "php${PHP_VERSION}-intl" \
        "php${PHP_VERSION}-soap" \
        "php${PHP_VERSION}-redis" \
        "php${PHP_VERSION}-imagick"
    
    # Configure PHP-FPM
    systemctl enable "php${PHP_VERSION}-fpm"
    systemctl start "php${PHP_VERSION}-fpm"
    
    log_info "PHP ${PHP_VERSION} installed successfully"
}

install_mysql() {
    log_step "Installing MySQL..."
    
    if ! command -v mysql &> /dev/null; then
        # Set MySQL root password before installation
        if [ "${NONINTERACTIVE}" = "yes" ]; then
            debconf-set-selections <<< "mysql-server mysql-server/root_password password ${MYSQL_ROOT_PASS}"
            debconf-set-selections <<< "mysql-server mysql-server/root_password_again password ${MYSQL_ROOT_PASS}"
        fi
        
        DEBIAN_FRONTEND=noninteractive apt-get install -y -qq mysql-server
        systemctl enable mysql
        systemctl start mysql
        
        log_info "MySQL installed successfully"
    else
        log_info "MySQL already installed"
    fi
    
    # Create database and user
    log_step "Creating database and user..."
    
    if [ "${NONINTERACTIVE}" = "yes" ]; then
        # Use mysql_config_editor for secure password storage (when possible)
        # or create a temporary .my.cnf file
        MYSQL_CNF=$(mktemp)
        chmod 600 "${MYSQL_CNF}"
        cat > "${MYSQL_CNF}" <<EOF
[client]
user=root
password=${MYSQL_ROOT_PASS}
EOF
        
        # Create database
        mysql --defaults-file="${MYSQL_CNF}" -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true
        
        # Create user and grant privileges
        mysql --defaults-file="${MYSQL_CNF}" -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';" 2>/dev/null || true
        mysql --defaults-file="${MYSQL_CNF}" -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';" 2>/dev/null || true
        mysql --defaults-file="${MYSQL_CNF}" -e "FLUSH PRIVILEGES;" 2>/dev/null || true
        
        # Clean up temporary file
        rm -f "${MYSQL_CNF}"
        
        log_info "Database and user created successfully"
    else
        log_warn "Please create database manually:"
        log_warn "  CREATE DATABASE ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
        log_warn "  CREATE USER '${DB_USER}'@'localhost' IDENTIFIED BY 'your_password';"
        log_warn "  GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
        log_warn "  FLUSH PRIVILEGES;"
    fi
}

install_composer() {
    log_step "Installing Composer..."
    
    if ! command -v composer &> /dev/null; then
        # Download and install Composer
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
        log_info "Composer installed successfully"
    else
        log_info "Composer already installed"
        # Update Composer
        composer self-update -q || true
    fi
}

install_nodejs() {
    log_step "Installing Node.js..."
    
    if ! command -v node &> /dev/null; then
        # Install Node.js 18.x LTS
        curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
        DEBIAN_FRONTEND=noninteractive apt-get install -y -qq nodejs
        log_info "Node.js installed successfully"
    else
        log_info "Node.js already installed"
    fi
}

install_certbot() {
    log_step "Installing Certbot..."
    
    if ! command -v certbot &> /dev/null; then
        DEBIAN_FRONTEND=noninteractive apt-get install -y -qq certbot python3-certbot-nginx
        log_info "Certbot installed successfully"
    else
        log_info "Certbot already installed"
    fi
}

clone_or_update_project() {
    log_step "Setting up project directory..."
    
    # Create web root if doesn't exist
    mkdir -p /var/www
    
    if [ -d "${PROJECT_DIR}" ]; then
        log_info "Project directory already exists"
        if confirm "Do you want to update the existing installation?"; then
            cd "${PROJECT_DIR}"
            git pull || log_warn "Failed to pull updates"
        fi
    else
        log_info "Cloning project repository..."
        git clone "${GITHUB_REPO}" "${PROJECT_DIR}" || {
            log_error "Failed to clone repository"
            exit 1
        }
    fi
    
    log_info "Project directory ready"
}

###############################################################################
# Main Bootstrap Process
###############################################################################

main() {
    log_info "========================================"
    log_info "ChiBank Bootstrap and Deployment Script"
    log_info "========================================"
    log_info ""
    log_info "Configuration:"
    log_info "  Domain: ${DOMAIN}"
    log_info "  Database: ${DB_NAME}"
    log_info "  DB User: ${DB_USER}"
    log_info "  PHP Version: ${PHP_VERSION}"
    log_info "  Project Directory: ${PROJECT_DIR}"
    log_info ""
    
    # Check root privileges
    check_root
    
    # Validate requirements
    validate_requirements
    
    if ! confirm "Continue with installation?"; then
        log_warn "Installation cancelled"
        exit 0
    fi
    
    # Install system components
    install_system_updates
    install_basic_packages
    install_nginx
    install_php
    install_mysql
    install_composer
    install_nodejs
    install_certbot
    
    # Setup project
    clone_or_update_project
    
    # Call deploy_server.sh with proper environment variables
    log_step "Running deployment script..."
    
    export DOMAIN="${DOMAIN}"
    export DB_DATABASE="${DB_NAME}"
    export DB_USERNAME="${DB_USER}"
    export DB_PASSWORD="${DB_PASS}"
    export CHOWN_USER="${CHOWN_USER}"
    export NONINTERACTIVE="${NONINTERACTIVE}"
    export PHP_VERSION="${PHP_VERSION}"
    
    # Ensure deploy script is executable
    chmod +x "${PROJECT_DIR}/scripts/deploy_server.sh"
    
    # Run deployment script
    bash "${PROJECT_DIR}/scripts/deploy_server.sh"
    
    log_info "========================================"
    log_info "Bootstrap completed successfully!"
    log_info "========================================"
}

# Run main function
main
