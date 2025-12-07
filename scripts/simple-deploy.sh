#!/bin/bash
# ChiBank 极简部署脚本 / Ultra Simple Deploy Script

set -e
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${GREEN}ChiBank 自动部署开始...${NC}\n"

# 1. 检查 Docker
if ! command -v docker &> /dev/null; then
    echo -e "${RED}错误：Docker 未安装${NC}"
    echo "请先安装 Docker: curl -fsSL https://get.docker.com | sh"
    exit 1
fi
echo -e "${GREEN}✓${NC} Docker 已安装"

# 2. 创建目录
DEPLOY_DIR="$HOME/chibank-deploy"
mkdir -p "$DEPLOY_DIR" && cd "$DEPLOY_DIR"
echo -e "${GREEN}✓${NC} 目录创建: $DEPLOY_DIR"

# 3. 下载配置
curl -fsSL https://raw.githubusercontent.com/hhongli1979-coder/chibank999/main/docker-compose.prod.yml -o docker-compose.yml
curl -fsSL https://raw.githubusercontent.com/hhongli1979-coder/chibank999/main/.env.example -o .env.example
echo -e "${GREEN}✓${NC} 配置下载完成"

# 4. 配置环境
if [ ! -f .env ]; then
    cp .env.example .env
    DB_PASSWORD=$(openssl rand -base64 20 | tr -d "=+/" | cut -c1-16)
    APP_KEY="base64:$(openssl rand -base64 32)"
    
    if [[ "$OSTYPE" == "darwin"* ]]; then
        sed -i '' "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" .env
        sed -i '' "s|APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
    else
        sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASSWORD}/" .env
        sed -i "s|APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
    fi
    
    echo "数据库密码: ${DB_PASSWORD}" > credentials.txt
    chmod 600 credentials.txt
    echo -e "${GREEN}✓${NC} 环境配置完成 (密码保存在 credentials.txt)"
fi

# 5. 启动服务
echo -e "${YELLOW}正在拉取镜像并启动服务 (需要几分钟)...${NC}"
docker compose pull >/dev/null 2>&1 || docker-compose pull >/dev/null 2>&1
docker compose up -d >/dev/null 2>&1 || docker-compose up -d >/dev/null 2>&1
echo -e "${GREEN}✓${NC} 服务启动中..."

# 6. 等待并初始化
echo -e "${YELLOW}等待数据库启动...${NC}"
sleep 20

echo -e "${YELLOW}初始化数据库...${NC}"
docker compose exec -T app php artisan migrate --force >/dev/null 2>&1 || docker-compose exec -T app php artisan migrate --force >/dev/null 2>&1
echo -e "${GREEN}✓${NC} 数据库初始化完成"

# 完成
echo -e "\n${GREEN}╔═══════════════════════════════════╗${NC}"
echo -e "${GREEN}║      部署完成！/ Deploy Done!     ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════╝${NC}\n"

echo -e "${GREEN}访问地址:${NC} http://localhost"
echo -e "${GREEN}部署目录:${NC} $DEPLOY_DIR"
echo -e "${GREEN}数据库密码:${NC} 查看 $DEPLOY_DIR/credentials.txt\n"

echo -e "${YELLOW}常用命令:${NC}"
echo -e "  查看日志: cd $DEPLOY_DIR && docker-compose logs -f"
echo -e "  停止服务: cd $DEPLOY_DIR && docker-compose down"
echo -e "  重启服务: cd $DEPLOY_DIR && docker-compose restart\n"
