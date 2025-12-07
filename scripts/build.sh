#!/bin/bash

###############################################################################
# ChiBank/QRPay 构建脚本 (Build Script)
# 用于本地构建应用
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

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 检查是否在正确的目录
if [ ! -f "artisan" ]; then
    log_error "必须在 Laravel 项目根目录运行此脚本"
    exit 1
fi

log_info "开始 ChiBank/QRPay 构建流程..."

# 步骤 1: 安装 Composer 依赖
log_info "步骤 1/4: 安装 Composer 依赖..."
if [ "$1" == "--prod" ] || [ "$1" == "--production" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
else
    composer install --optimize-autoloader --no-interaction
fi

# 步骤 2: 安装 NPM 依赖
log_info "步骤 2/4: 安装 NPM 依赖..."
npm install

# 步骤 3: 构建前端资源
log_info "步骤 3/4: 构建前端资源..."
npm run build

# 步骤 4: 创建环境文件（如果不存在）
if [ ! -f ".env" ]; then
    log_info "步骤 4/4: 创建环境文件..."
    cp .env.example .env
    php artisan key:generate
else
    log_info "步骤 4/4: 环境文件已存在，跳过..."
fi

log_info "=========================================="
log_info "构建完成！"
log_info "=========================================="
log_info "下一步："
log_info "1. 配置 .env 文件"
log_info "2. 运行数据库迁移: php artisan migrate"
log_info "3. 启动应用: php artisan serve"
