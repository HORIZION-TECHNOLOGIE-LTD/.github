# Quick Deployment Reference / 快速部署参考

## 🐳 Pull Pre-built Image (Fastest) / 直接拉取预构建镜像（最快）

```bash
# Pull the latest pre-built image / 拉取最新预构建镜像
docker pull ghcr.io/hhongli1979-coder/chibank999:main

# Clone repository to get configuration files / 克隆仓库获取配置文件
git clone --depth 1 https://github.com/hhongli1979-coder/chibank999.git
cd chibank999

# Configure environment / 配置环境
cp .env.example .env
# Edit .env and set DB_PASSWORD to a strong password / 编辑 .env 并设置强密码

# Start with pre-built image / 使用预构建镜像启动
docker-compose -f docker-compose.prod.yml up -d
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Access at http://localhost / 访问 http://localhost
```

## 🚀 Quick Docker Deployment (Build from Source) / 从源码构建 Docker 部署

```bash
# 1. Clone repository / 克隆仓库
git clone https://github.com/hhongli1979-coder/chibank999.git
cd chibank999

# 2. Configure environment / 配置环境
cp .env.example .env
# Edit .env file with your settings / 编辑 .env 文件

# 3. Start with Docker Compose / 使用 Docker Compose 启动
docker-compose up -d

# 4. Initialize database / 初始化数据库
docker-compose exec app php artisan migrate --force

# 5. Access application / 访问应用
# Open http://localhost in your browser
# 在浏览器中打开 http://localhost
```

## 📦 Build Scripts / 构建脚本

```bash
# Build application / 构建应用
./scripts/build.sh

# Build for production / 生产环境构建
./scripts/build.sh --prod

# Build Docker image / 构建 Docker 镜像
./scripts/docker-build.sh --name chibank/qrpay --version v1.0.0

# Build and push Docker image / 构建并推送 Docker 镜像
./scripts/docker-build.sh --name chibank/qrpay --version v1.0.0 --push

# Deploy to server / 部署到服务器
./scripts/deploy.sh
```

## 📋 NPM Scripts / NPM 脚本

```bash
# Development build / 开发环境构建
npm run dev

# Production build / 生产环境构建
npm run build

# Production optimized build / 优化的生产构建
npm run build:prod

# Deploy (runs deploy.sh) / 部署（运行 deploy.sh）
npm run deploy

# Build Docker image / 构建 Docker 镜像
npm run docker:build

# Build and push Docker image / 构建并推送 Docker 镜像
npm run docker:push
```

## 🔧 Manual Deployment / 手动部署

### Using Makefile (Recommended) / 使用 Makefile（推荐）

```bash
# View all available commands / 查看所有可用命令
make help

# Install dependencies / 安装依赖
make install

# Build for production / 生产环境构建
make build-prod

# Deploy to server / 部署到服务器
make deploy

# Start with Docker / 使用 Docker 启动
make docker-up

# View Docker logs / 查看 Docker 日志
make docker-logs
```

### Using Scripts Directly / 直接使用脚本

```bash
# 1. Install dependencies / 安装依赖
composer install --no-dev --optimize-autoloader
npm ci

# 2. Build frontend / 构建前端
npm run build

# 3. Configure environment / 配置环境
cp .env.example .env
php artisan key:generate

# 4. Run migrations / 运行迁移
php artisan migrate --force

# 5. Optimize / 优化
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📖 Full Documentation / 完整文档

- **Chinese / 中文**: [docs/zh-CN/部署文档.md](docs/zh-CN/部署文档.md)
- **English**: [docs/en/DEPLOYMENT-GUIDE.md](docs/en/DEPLOYMENT-GUIDE.md)

## ⚡ CI/CD

The project includes automated GitHub Actions workflows:
本项目包含自动化的 GitHub Actions 工作流：

- **Build & Test**: On every push and PR / 每次推送和 PR 时
- **Docker Build & Push**: Automatically builds and pushes to GitHub Container Registry (ghcr.io) on main/master branches
  自动构建并推送到 GitHub Container Registry (ghcr.io) - 在 main/master 分支
- **Auto Deploy**: On production branch / 在 production 分支

See `.github/workflows/docker-image.yml` and `.github/workflows/deploy.yml` for details.
详见 `.github/workflows/docker-image.yml` 和 `.github/workflows/deploy.yml`。

## 🐳 Docker Commands / Docker 命令

```bash
# Start services / 启动服务
docker-compose up -d

# Stop services / 停止服务
docker-compose down

# View logs / 查看日志
docker-compose logs -f app

# Restart services / 重启服务
docker-compose restart

# Enter container / 进入容器
docker-compose exec app sh

# Rebuild containers / 重建容器
docker-compose up -d --build
```

## 📞 Support / 支持

For issues and questions:
如有问题：

- GitHub Issues: https://github.com/hhongli1979-coder/chibank999/issues
- Documentation: See docs folder / 查看 docs 文件夹
