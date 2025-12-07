#!/bin/bash

###############################################################################
# ChiBank/QRPay Docker 构建和推送脚本
# Build and Push Docker Image Script
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

# 默认配置
IMAGE_NAME="chibank/qrpay"
VERSION=$(git describe --tags --always --dirty 2>/dev/null || echo "latest")
BUILD_DATE=$(date -u +'%Y-%m-%dT%H:%M:%SZ')

# 显示用法
usage() {
    echo "用法: $0 [选项]"
    echo ""
    echo "选项:"
    echo "  -n, --name NAME      镜像名称 (默认: $IMAGE_NAME)"
    echo "  -v, --version VER    版本标签 (默认: $VERSION)"
    echo "  -p, --push           构建后推送到镜像仓库"
    echo "  -h, --help           显示此帮助信息"
    echo ""
    echo "示例:"
    echo "  $0 --name myrepo/chibank --version v1.0.0 --push"
    exit 1
}

# 解析参数
PUSH=false
while [[ $# -gt 0 ]]; do
    case $1 in
        -n|--name)
            IMAGE_NAME="$2"
            shift 2
            ;;
        -v|--version)
            VERSION="$2"
            shift 2
            ;;
        -p|--push)
            PUSH=true
            shift
            ;;
        -h|--help)
            usage
            ;;
        *)
            log_error "未知参数: $1"
            usage
            ;;
    esac
done

log_info "Docker 镜像构建配置:"
log_info "  镜像名称: $IMAGE_NAME"
log_info "  版本标签: $VERSION"
log_info "  构建日期: $BUILD_DATE"
log_info "  推送镜像: $PUSH"

# 构建镜像
log_info "开始构建 Docker 镜像..."
docker build \
    --build-arg BUILD_DATE="$BUILD_DATE" \
    --build-arg VERSION="$VERSION" \
    -t "$IMAGE_NAME:$VERSION" \
    -t "$IMAGE_NAME:latest" \
    .

log_info "Docker 镜像构建成功！"

# 推送镜像
if [ "$PUSH" = true ]; then
    log_info "推送镜像到仓库..."
    docker push "$IMAGE_NAME:$VERSION"
    docker push "$IMAGE_NAME:latest"
    log_info "镜像推送成功！"
fi

log_info "=========================================="
log_info "完成！"
log_info "=========================================="
log_info "镜像标签:"
log_info "  - $IMAGE_NAME:$VERSION"
log_info "  - $IMAGE_NAME:latest"
log_info ""
log_info "运行镜像:"
log_info "  docker run -p 80:80 $IMAGE_NAME:$VERSION"
log_info ""
log_info "使用 Docker Compose:"
log_info "  docker-compose up -d"
