#!/bin/bash

###############################################################################
# ChiBank 一键部署脚本 / One-Click Deployment Script
# 
# 这个脚本会自动完成以下任务：
# This script will automatically complete the following tasks:
# 1. 检查系统要求 / Check system requirements
# 2. 安装 Docker（如果需要）/ Install Docker (if needed)
# 3. 下载配置文件 / Download configuration files
# 4. 配置环境变量 / Configure environment variables
# 5. 启动所有服务 / Start all services
# 6. 初始化数据库 / Initialize database
# 7. 验证部署 / Verify deployment
#
# 使用方法 / Usage:
#   curl -fsSL https://raw.githubusercontent.com/hhongli1979-coder/chibank999/main/scripts/one-click-deploy.sh | bash
#   或 / or
#   ./one-click-deploy.sh
###############################################################################

set -e

# 颜色定义 / Color definitions
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 配置 / Configuration
DEPLOY_DIR="${HOME}/chibank-deploy"
GITHUB_REPO="hhongli1979-coder/chibank999"
GITHUB_RAW_URL="https://raw.githubusercontent.com/${GITHUB_REPO}/main"

###############################################################################
# 日志函数 / Logging functions
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
    echo -e "\n${BLUE}===> $1${NC}\n"
}

###############################################################################
# 检查命令是否存在 / Check if command exists
###############################################################################

command_exists() {
    command -v "$1" >/dev/null 2>&1
}

###############################################################################
# 检查系统要求 / Check system requirements
###############################################################################

check_system_requirements() {
    log_step "检查系统要求 / Checking system requirements"
    
    # 检查操作系统 / Check OS
    if [[ "$OSTYPE" == "linux-gnu"* ]]; then
        log_info "操作系统: Linux"
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        log_info "操作系统: macOS"
    elif [[ "$OSTYPE" == "msys" || "$OSTYPE" == "cygwin" ]]; then
        log_info "操作系统: Windows (WSL)"
    else
        log_warn "未知操作系统: $OSTYPE"
    fi
    
    # 检查内存 / Check memory
    if command_exists free; then
        TOTAL_MEM=$(free -g | awk '/^Mem:/{print $2}')
        if [ "$TOTAL_MEM" -lt 4 ]; then
            log_warn "内存不足 4GB，推荐至少 4GB / Memory less than 4GB, recommend at least 4GB"
        else
            log_info "内存: ${TOTAL_MEM}GB ✓"
        fi
    fi
    
    # 检查磁盘空间 / Check disk space
    AVAILABLE_SPACE=$(df -BG "$HOME" | awk 'NR==2 {print $4}' | sed 's/G//')
    if [ "$AVAILABLE_SPACE" -lt 20 ]; then
        log_error "磁盘空间不足 20GB / Disk space less than 20GB"
        exit 1
    else
        log_info "可用磁盘空间: ${AVAILABLE_SPACE}GB ✓"
    fi
}

###############################################################################
# 安装 Docker / Install Docker
###############################################################################

install_docker() {
    log_step "检查 Docker 安装 / Checking Docker installation"
    
    if command_exists docker; then
        DOCKER_VERSION=$(docker --version | awk '{print $3}' | sed 's/,//')
        log_info "Docker 已安装: $DOCKER_VERSION ✓"
    else
        log_warn "Docker 未安装，正在安装... / Docker not installed, installing..."
        
        if [[ "$OSTYPE" == "linux-gnu"* ]]; then
            # Linux 安装 / Linux installation
            curl -fsSL https://get.docker.com | sh
            sudo usermod -aG docker "$USER"
            log_info "Docker 安装完成 / Docker installed successfully"
            log_warn "请注销并重新登录以使 Docker 权限生效 / Please logout and login again for Docker permissions"
        elif [[ "$OSTYPE" == "darwin"* ]]; then
            # macOS 安装提示 / macOS installation hint
            log_error "请访问 https://www.docker.com/products/docker-desktop 下载 Docker Desktop for Mac"
            log_error "Please visit https://www.docker.com/products/docker-desktop to download Docker Desktop for Mac"
            exit 1
        else
            log_error "请手动安装 Docker / Please install Docker manually"
            exit 1
        fi
    fi
    
    # 检查 Docker Compose / Check Docker Compose
    if command_exists docker-compose || docker compose version >/dev/null 2>&1; then
        log_info "Docker Compose 已安装 ✓"
    else
        log_error "Docker Compose 未安装 / Docker Compose not installed"
        log_error "请访问 https://docs.docker.com/compose/install/"
        exit 1
    fi
    
    # 检查 Docker 服务状态 / Check Docker service status
    if docker ps >/dev/null 2>&1; then
        log_info "Docker 服务运行正常 ✓"
    else
        log_error "Docker 服务未运行，请启动 Docker / Docker service not running, please start Docker"
        if [[ "$OSTYPE" == "linux-gnu"* ]]; then
            log_info "尝试启动 Docker 服务 / Attempting to start Docker service"
            sudo systemctl start docker
        fi
        exit 1
    fi
}

###############################################################################
# 创建部署目录 / Create deployment directory
###############################################################################

create_deploy_directory() {
    log_step "创建部署目录 / Creating deployment directory"
    
    if [ -d "$DEPLOY_DIR" ]; then
        log_warn "目录已存在: $DEPLOY_DIR"
        read -p "是否删除并重新创建？(y/N) / Delete and recreate? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            rm -rf "$DEPLOY_DIR"
            mkdir -p "$DEPLOY_DIR"
            log_info "目录已重新创建 / Directory recreated"
        fi
    else
        mkdir -p "$DEPLOY_DIR"
        log_info "目录创建成功: $DEPLOY_DIR"
    fi
    
    cd "$DEPLOY_DIR"
}

###############################################################################
# 下载配置文件 / Download configuration files
###############################################################################

download_configuration() {
    log_step "下载配置文件 / Downloading configuration files"
    
    # 下载 docker-compose.prod.yml
    log_info "下载 docker-compose.prod.yml..."
    curl -fsSL "${GITHUB_RAW_URL}/docker-compose.prod.yml" -o docker-compose.yml
    
    # 下载 .env.example
    log_info "下载 .env.example..."
    curl -fsSL "${GITHUB_RAW_URL}/.env.example" -o .env.example
    
    log_info "配置文件下载完成 ✓"
}

###############################################################################
# 配置环境变量 / Configure environment variables
###############################################################################

configure_environment() {
    log_step "配置环境变量 / Configuring environment variables"
    
    if [ -f .env ]; then
        log_warn ".env 文件已存在，跳过配置 / .env file exists, skipping configuration"
        return
    fi
    
    # 复制模板 / Copy template
    cp .env.example .env
    
    # 生成随机密码 / Generate random passwords
    DB_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
    APP_KEY="base64:$(openssl rand -base64 32)"
    
    # 更新 .env 文件 / Update .env file
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS
        sed -i '' "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" .env
        sed -i '' "s|APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
        sed -i '' "s/APP_DEBUG=.*/APP_DEBUG=false/" .env
        sed -i '' "s/APP_ENV=.*/APP_ENV=production/" .env
    else
        # Linux
        sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" .env
        sed -i "s|APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
        sed -i "s/APP_DEBUG=.*/APP_DEBUG=false/" .env
        sed -i "s/APP_ENV=.*/APP_ENV=production/" .env
    fi
    
    log_info "环境变量配置完成 ✓"
    log_info "数据库密码: ${DB_PASSWORD}"
    log_warn "请妥善保存以上密码信息 / Please save the password information safely"
    
    # 保存密码到文件 / Save password to file
    echo "数据库密码 / Database Password: ${DB_PASSWORD}" > credentials.txt
    echo "请妥善保存此文件 / Please save this file safely" >> credentials.txt
    chmod 600 credentials.txt
    log_info "密码已保存到: ${DEPLOY_DIR}/credentials.txt"
}

###############################################################################
# 拉取 Docker 镜像 / Pull Docker images
###############################################################################

pull_images() {
    log_step "拉取 Docker 镜像 / Pulling Docker images"
    
    log_info "这可能需要几分钟时间... / This may take a few minutes..."
    
    if command_exists docker-compose; then
        docker-compose pull
    else
        docker compose pull
    fi
    
    log_info "镜像拉取完成 ✓"
}

###############################################################################
# 启动服务 / Start services
###############################################################################

start_services() {
    log_step "启动服务 / Starting services"
    
    log_info "启动所有容器... / Starting all containers..."
    
    if command_exists docker-compose; then
        docker-compose up -d
    else
        docker compose up -d
    fi
    
    log_info "等待服务启动... / Waiting for services to start..."
    sleep 30
    
    # 检查服务状态 / Check service status
    if command_exists docker-compose; then
        docker-compose ps
    else
        docker compose ps
    fi
    
    log_info "服务启动完成 ✓"
}

###############################################################################
# 初始化数据库 / Initialize database
###############################################################################

initialize_database() {
    log_step "初始化数据库 / Initializing database"
    
    log_info "运行数据库迁移... / Running database migrations..."
    
    if command_exists docker-compose; then
        docker-compose exec -T app php artisan migrate --force
    else
        docker compose exec -T app php artisan migrate --force
    fi
    
    log_info "数据库迁移完成 ✓"
    
    # 询问是否导入示例数据 / Ask if import sample data
    read -p "是否导入示例数据？(y/N) / Import sample data? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        log_info "导入示例数据... / Importing sample data..."
        if command_exists docker-compose; then
            docker-compose exec -T app php artisan db:seed --force
        else
            docker compose exec -T app php artisan db:seed --force
        fi
        log_info "示例数据导入完成 ✓"
    fi
}

###############################################################################
# 优化应用 / Optimize application
###############################################################################

optimize_application() {
    log_step "优化应用 / Optimizing application"
    
    log_info "缓存配置... / Caching configuration..."
    
    if command_exists docker-compose; then
        docker-compose exec -T app php artisan config:cache
        docker-compose exec -T app php artisan route:cache
        docker-compose exec -T app php artisan view:cache
    else
        docker compose exec -T app php artisan config:cache
        docker compose exec -T app php artisan route:cache
        docker compose exec -T app php artisan view:cache
    fi
    
    log_info "应用优化完成 ✓"
}

###############################################################################
# 验证部署 / Verify deployment
###############################################################################

verify_deployment() {
    log_step "验证部署 / Verifying deployment"
    
    # 检查容器状态 / Check container status
    log_info "检查容器状态... / Checking container status..."
    
    if command_exists docker-compose; then
        RUNNING_CONTAINERS=$(docker-compose ps | grep -c "Up")
    else
        RUNNING_CONTAINERS=$(docker compose ps | grep -c "Up")
    fi
    
    if [ "$RUNNING_CONTAINERS" -ge 3 ]; then
        log_info "所有容器运行正常 ✓"
    else
        log_warn "部分容器可能未正常运行 / Some containers may not be running properly"
    fi
    
    # 检查应用是否可访问 / Check if application is accessible
    log_info "检查应用访问... / Checking application access..."
    sleep 5
    
    if curl -f -s -o /dev/null http://localhost; then
        log_info "应用可以访问 ✓"
    else
        log_warn "应用暂时无法访问，请稍后再试 / Application temporarily inaccessible, please try again later"
    fi
}

###############################################################################
# 显示部署信息 / Display deployment information
###############################################################################

display_info() {
    log_step "部署完成 / Deployment Complete"
    
    echo -e "${GREEN}╔════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║                   ChiBank 部署成功！                           ║${NC}"
    echo -e "${GREEN}║              ChiBank Deployed Successfully!                    ║${NC}"
    echo -e "${GREEN}╚════════════════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "${BLUE}📍 访问信息 / Access Information:${NC}"
    echo -e "   应用地址 / Application URL: ${GREEN}http://localhost${NC}"
    echo -e "   数据库端口 / Database Port: ${GREEN}3306${NC}"
    echo -e "   Redis 端口 / Redis Port: ${GREEN}6379${NC}"
    echo ""
    echo -e "${BLUE}📂 部署目录 / Deployment Directory:${NC}"
    echo -e "   ${GREEN}${DEPLOY_DIR}${NC}"
    echo ""
    echo -e "${BLUE}🔑 凭证文件 / Credentials File:${NC}"
    echo -e "   ${GREEN}${DEPLOY_DIR}/credentials.txt${NC}"
    echo ""
    echo -e "${BLUE}📝 常用命令 / Common Commands:${NC}"
    echo -e "   查看日志 / View logs:"
    echo -e "   ${YELLOW}cd ${DEPLOY_DIR} && docker-compose logs -f app${NC}"
    echo ""
    echo -e "   停止服务 / Stop services:"
    echo -e "   ${YELLOW}cd ${DEPLOY_DIR} && docker-compose down${NC}"
    echo ""
    echo -e "   重启服务 / Restart services:"
    echo -e "   ${YELLOW}cd ${DEPLOY_DIR} && docker-compose restart${NC}"
    echo ""
    echo -e "   进入容器 / Enter container:"
    echo -e "   ${YELLOW}cd ${DEPLOY_DIR} && docker-compose exec app sh${NC}"
    echo ""
    echo -e "${BLUE}📖 文档 / Documentation:${NC}"
    echo -e "   ${GREEN}https://github.com/${GITHUB_REPO}${NC}"
    echo ""
    echo -e "${GREEN}🎉 祝您使用愉快！ / Enjoy using ChiBank!${NC}"
    echo ""
}

###############################################################################
# 主函数 / Main function
###############################################################################

main() {
    clear
    
    echo -e "${BLUE}"
    echo "╔════════════════════════════════════════════════════════════════╗"
    echo "║                                                                ║"
    echo "║              ChiBank 一键部署脚本                              ║"
    echo "║         ChiBank One-Click Deployment Script                   ║"
    echo "║                                                                ║"
    echo "║                      Version 1.0.0                             ║"
    echo "║                                                                ║"
    echo "╚════════════════════════════════════════════════════════════════╝"
    echo -e "${NC}\n"
    
    # 执行部署步骤 / Execute deployment steps
    check_system_requirements
    install_docker
    create_deploy_directory
    download_configuration
    configure_environment
    pull_images
    start_services
    initialize_database
    optimize_application
    verify_deployment
    display_info
}

# 运行主函数 / Run main function
main "$@"
