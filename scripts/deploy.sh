#!/bin/bash

###############################################################################
# ChiBank/QRPay 部署脚本 (Deployment Script)
# 用于将应用部署到生产环境
###############################################################################

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 日志函数
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 检查是否在正确的目录
if [ ! -f "artisan" ]; then
    log_error "必须在 Laravel 项目根目录运行此脚本"
    exit 1
fi

log_info "开始 ChiBank/QRPay 部署流程..."

# 步骤 1: 进入维护模式
log_info "步骤 1/8: 进入维护模式..."
php artisan down || log_warn "无法进入维护模式，继续..."

# 步骤 2: 拉取最新代码
log_info "步骤 2/8: 拉取最新代码..."
git pull origin $(git branch --show-current)

# 步骤 3: 安装/更新 Composer 依赖
log_info "步骤 3/8: 安装 Composer 依赖..."
composer install --no-dev --optimize-autoloader --no-interaction

# 步骤 4: 安装/更新 NPM 依赖
log_info "步骤 4/8: 安装 NPM 依赖..."
npm ci

# 步骤 5: 构建前端资源
log_info "步骤 5/8: 构建前端资源..."
npm run build

# 步骤 6: 运行数据库迁移
log_info "步骤 6/8: 运行数据库迁移..."
read -p "是否运行数据库迁移? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
else
    log_warn "跳过数据库迁移"
fi

# 步骤 7: 清除缓存
log_info "步骤 7/8: 清除并重建缓存..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 步骤 8: 退出维护模式
log_info "步骤 8/8: 退出维护模式..."
php artisan up

log_info "=========================================="
log_info "部署完成！"
log_info "=========================================="
