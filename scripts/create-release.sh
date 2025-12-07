#!/bin/bash

###############################################################################
# ChiBank/QRPay 发行版创建脚本 (Release Package Creation Script)
# 用于创建软件包 (软包)，包含所有必要文件和依赖
###############################################################################

set -e

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
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

log_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# 默认配置
VERSION=${1:-"latest"}
BUILD_DATE=$(date -u +'%Y%m%d%H%M%S')
RELEASE_NAME="chibank-v${VERSION}"
RELEASE_DIR="release/${RELEASE_NAME}"
PACKAGE_NAME="${RELEASE_NAME}-${BUILD_DATE}.tar.gz"

# 显示用法
usage() {
    echo "用法: $0 [版本号]"
    echo ""
    echo "示例:"
    echo "  $0 5.0.0      # 创建 v5.0.0 版本"
    echo "  $0 latest     # 创建 latest 版本"
    exit 1
}

# 检查是否在正确的目录
if [ ! -f "artisan" ]; then
    log_error "必须在 Laravel 项目根目录运行此脚本"
    exit 1
fi

log_info "=========================================="
log_info "ChiBank/QRPay 发行版创建工具"
log_info "=========================================="
log_info "版本: ${VERSION}"
log_info "构建日期: ${BUILD_DATE}"
log_info "发行包名: ${PACKAGE_NAME}"
log_info ""

# 步骤 1: 清理旧的发行版目录
log_step "步骤 1/10: 清理旧的发行版目录..."
rm -rf release/
mkdir -p ${RELEASE_DIR}
log_info "创建目录: ${RELEASE_DIR}"

# 步骤 2: 复制应用源代码
log_step "步骤 2/10: 复制应用源代码..."
rsync -av --progress \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.env' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='public/build' \
    --exclude='release' \
    --exclude='.phpunit.result.cache' \
    --exclude='tests' \
    --exclude='.github' \
    . ${RELEASE_DIR}/
log_info "源代码复制完成"

# 步骤 3: 安装 Composer 生产依赖
log_step "步骤 3/10: 安装 Composer 生产依赖..."
cd ${RELEASE_DIR}
composer install --no-dev --optimize-autoloader --no-interaction
log_info "Composer 依赖安装完成"

# 步骤 4: 安装 NPM 依赖
log_step "步骤 4/10: 安装 NPM 依赖..."
npm ci --production
log_info "NPM 依赖安装完成"

# 步骤 5: 构建前端资源
log_step "步骤 5/10: 构建前端资源..."
npm run build
log_info "前端资源构建完成"

# 步骤 6: 清理不需要的文件
log_step "步骤 6/10: 清理不需要的文件..."
rm -rf node_modules/
rm -rf .git/
rm -rf tests/
rm -rf .github/
rm -f .gitignore .gitattributes
rm -f .editorconfig
rm -f .phpunit.result.cache
log_info "清理完成"

# 步骤 7: 创建必要的目录
log_step "步骤 7/10: 创建必要的目录..."
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache
log_info "目录创建完成"

# 步骤 8: 设置权限
log_step "步骤 8/10: 设置文件权限..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache
log_info "权限设置完成"

# 步骤 9: 创建版本信息文件
log_step "步骤 9/10: 创建版本信息文件..."
cat > VERSION.txt << EOF
ChiBank v${VERSION}
构建日期: ${BUILD_DATE}
构建时间: $(date -u +'%Y-%m-%d %H:%M:%S UTC')
Git 提交: $(cd ../../ && git rev-parse --short HEAD 2>/dev/null || echo "N/A")
EOF
log_info "版本信息文件创建完成"

# 步骤 10: 打包发行版
log_step "步骤 10/10: 打包发行版..."
cd ..
tar -czf ${PACKAGE_NAME} ${RELEASE_NAME}/
PACKAGE_SIZE=$(du -h ${PACKAGE_NAME} | cut -f1)
log_info "打包完成: ${PACKAGE_NAME} (${PACKAGE_SIZE})"

# 计算校验和
log_info "计算校验和..."
MD5SUM=$(md5sum ${PACKAGE_NAME} | cut -d' ' -f1)
SHA256SUM=$(sha256sum ${PACKAGE_NAME} | cut -d' ' -f1)

# 创建校验和文件
cat > ${PACKAGE_NAME}.checksums << EOF
MD5: ${MD5SUM}
SHA256: ${SHA256SUM}
EOF

log_info "=========================================="
log_info "发行版创建成功！"
log_info "=========================================="
log_info ""
log_info "📦 软件包信息:"
log_info "  文件名: ${PACKAGE_NAME}"
log_info "  大小: ${PACKAGE_SIZE}"
log_info "  MD5: ${MD5SUM}"
log_info "  SHA256: ${SHA256SUM}"
log_info ""
log_info "📁 位置: $(pwd)/${PACKAGE_NAME}"
log_info ""
log_info "📋 部署说明:"
log_info "  1. 上传软件包到服务器"
log_info "  2. 解压: tar -xzf ${PACKAGE_NAME}"
log_info "  3. 配置: cd ${RELEASE_NAME} && cp .env.example .env"
log_info "  4. 生成密钥: php artisan key:generate"
log_info "  5. 迁移数据库: php artisan migrate --force"
log_info "  6. 启动应用: php artisan serve 或使用 Docker"
log_info ""
log_info "🐳 Docker 部署:"
log_info "  docker-compose up -d"
log_info ""

# 返回原目录
cd ../..

log_info "✅ 完成!"
