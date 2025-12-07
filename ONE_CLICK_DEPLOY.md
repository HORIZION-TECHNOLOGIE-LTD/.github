# ChiBank 一步部署指南 / One-Step Deployment Guide

## 🚀 一步部署 / One-Step Deployment

### 方式一：使用预构建镜像（推荐）/ Method 1: Use Pre-built Image (Recommended)

只需一行命令即可完成整个系统的部署！
Deploy the entire system with just one command!

```bash
curl -fsSL https://raw.githubusercontent.com/hhongli1979-coder/chibank999/main/scripts/one-click-deploy.sh | bash
```

### 方式二：手动部署 / Method 2: Manual Deployment

```bash
# 1. 下载部署脚本 / Download deployment script
curl -O https://raw.githubusercontent.com/hhongli1979-coder/chibank999/main/scripts/one-click-deploy.sh
chmod +x one-click-deploy.sh

# 2. 执行部署 / Execute deployment
./one-click-deploy.sh

# 3. 访问系统 / Access system
# 浏览器打开 http://localhost
# Open http://localhost in your browser
```

---

## 📋 系统要求 / System Requirements

### 最低配置 / Minimum Requirements
- **CPU**: 2 核心 / 2 cores
- **内存 / RAM**: 4GB
- **硬盘 / Disk**: 20GB
- **操作系统 / OS**: Linux, macOS, Windows (with WSL2)
- **软件 / Software**: Docker 20.10+, Docker Compose 2.0+

### 推荐配置 / Recommended Requirements
- **CPU**: 4 核心 / 4 cores
- **内存 / RAM**: 8GB
- **硬盘 / Disk**: 50GB SSD
- **网络 / Network**: 10Mbps+

---

## 🐳 Docker 镜像信息 / Docker Image Information

### 官方镜像 / Official Images

```bash
# GitHub Container Registry (推荐 / Recommended)
ghcr.io/hhongli1979-coder/chibank999:latest
ghcr.io/hhongli1979-coder/chibank999:main
ghcr.io/hhongli1979-coder/chibank999:v5.0.0

# Docker Hub (备用 / Alternative)
hhongli1979coder/chibank999:latest
```

### 镜像大小 / Image Size
- **压缩后 / Compressed**: ~300MB
- **解压后 / Uncompressed**: ~800MB

### 镜像包含 / Image Contains
- ✅ PHP 8.1 + Laravel 9
- ✅ Nginx Web Server
- ✅ 所有依赖已预装 / All dependencies pre-installed
- ✅ 生产环境优化 / Production optimized
- ✅ 前端资源已编译 / Frontend assets compiled

---

## 🎯 快速开始 / Quick Start

### 步骤 1：安装 Docker / Step 1: Install Docker

#### Linux
```bash
# Ubuntu/Debian
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
newgrp docker

# CentOS/RHEL
sudo yum install -y docker docker-compose
sudo systemctl start docker
sudo systemctl enable docker
```

#### macOS
```bash
# 使用 Homebrew / Using Homebrew
brew install --cask docker

# 或下载 Docker Desktop / Or download Docker Desktop
# https://www.docker.com/products/docker-desktop
```

#### Windows
```bash
# 安装 WSL2 / Install WSL2
wsl --install

# 下载并安装 Docker Desktop / Download and install Docker Desktop
# https://www.docker.com/products/docker-desktop
```

### 步骤 2：一键部署 / Step 2: One-Click Deploy

```bash
# 下载并运行一键部署脚本 / Download and run one-click deployment script
curl -fsSL https://raw.githubusercontent.com/hhongli1979-coder/chibank999/main/scripts/one-click-deploy.sh | bash
```

### 步骤 3：访问系统 / Step 3: Access System

```bash
# 系统将在以下地址运行 / System will run at:
http://localhost        # 主应用 / Main application
http://localhost:3306   # MySQL 数据库 / MySQL database
http://localhost:6379   # Redis 缓存 / Redis cache
```

---

## 🔧 高级配置 / Advanced Configuration

### 自定义环境变量 / Custom Environment Variables

创建 `.env` 文件并配置以下变量：
Create `.env` file and configure the following variables:

```bash
# 应用配置 / Application Configuration
APP_NAME="ChiBank"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_PORT=80

# 数据库配置 / Database Configuration
DB_DATABASE=chibank
DB_USERNAME=chibank
DB_PASSWORD=your_strong_password_here
DB_PORT=3306

# Redis 配置 / Redis Configuration
REDIS_PORT=6379

# 邮件配置 / Email Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls

# 支付网关配置 / Payment Gateway Configuration
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_SECRET=your_paypal_secret
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
```

### 自定义端口 / Custom Ports

```bash
# 使用自定义端口 / Use custom ports
APP_PORT=8080 docker-compose up -d
```

### 使用 SSL/HTTPS

```bash
# 1. 准备 SSL 证书 / Prepare SSL certificates
mkdir -p ./certs
cp your-domain.crt ./certs/
cp your-domain.key ./certs/

# 2. 使用 SSL 配置启动 / Start with SSL configuration
docker-compose -f docker-compose.prod.yml -f docker-compose.ssl.yml up -d
```

---

## 📝 详细部署步骤 / Detailed Deployment Steps

### 完整手动部署流程 / Complete Manual Deployment Process

```bash
# 1. 创建工作目录 / Create working directory
mkdir -p ~/chibank
cd ~/chibank

# 2. 下载 docker-compose 配置 / Download docker-compose configuration
curl -O https://raw.githubusercontent.com/hhongli1979-coder/chibank999/main/docker-compose.prod.yml

# 3. 下载环境变量模板 / Download environment template
curl -O https://raw.githubusercontent.com/hhongli1979-coder/chibank999/main/.env.example
cp .env.example .env

# 4. 编辑环境变量 / Edit environment variables
nano .env  # 或使用 vim、vi 等编辑器 / or use vim, vi, etc.

# 5. 拉取镜像 / Pull images
docker-compose -f docker-compose.prod.yml pull

# 6. 启动服务 / Start services
docker-compose -f docker-compose.prod.yml up -d

# 7. 等待服务启动 / Wait for services to start
echo "等待数据库启动... / Waiting for database to start..."
sleep 30

# 8. 运行数据库迁移 / Run database migrations
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# 9. 创建初始数据 / Seed initial data
docker-compose -f docker-compose.prod.yml exec app php artisan db:seed --force

# 10. 优化应用 / Optimize application
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec app php artisan view:cache

# 11. 检查服务状态 / Check service status
docker-compose -f docker-compose.prod.yml ps

# 12. 查看日志 / View logs
docker-compose -f docker-compose.prod.yml logs -f app
```

---

## 🎬 部署后操作 / Post-Deployment Operations

### 创建管理员账户 / Create Admin Account

```bash
docker-compose exec app php artisan admin:create
```

### 设置定时任务 / Setup Cron Jobs

```bash
# 在宿主机上添加定时任务 / Add cron job on host machine
crontab -e

# 添加以下行 / Add this line:
* * * * * cd /path/to/chibank && docker-compose exec -T app php artisan schedule:run >> /dev/null 2>&1
```

### 配置队列处理 / Configure Queue Processing

```bash
# 启动队列工作进程 / Start queue workers
docker-compose exec -d app php artisan queue:work --sleep=3 --tries=3
```

### 备份数据库 / Backup Database

```bash
# 手动备份 / Manual backup
docker-compose exec mysql mysqldump -u root -p chibank > backup_$(date +%Y%m%d).sql

# 自动定时备份 / Automatic scheduled backup
# 添加到 crontab / Add to crontab:
0 2 * * * cd /path/to/chibank && docker-compose exec mysql mysqldump -u root -p$DB_PASSWORD chibank | gzip > backup_$(date +\%Y\%m\%d).sql.gz
```

---

## 🔍 故障排查 / Troubleshooting

### 查看日志 / View Logs

```bash
# 查看应用日志 / View application logs
docker-compose logs -f app

# 查看数据库日志 / View database logs
docker-compose logs -f mysql

# 查看所有服务日志 / View all service logs
docker-compose logs -f
```

### 常见问题 / Common Issues

#### 1. 端口被占用 / Port Already in Use

```bash
# 检查端口占用 / Check port usage
sudo lsof -i :80
sudo lsof -i :3306

# 停止占用端口的服务 / Stop service using the port
sudo systemctl stop nginx  # 或其他占用端口的服务 / or other service

# 或使用自定义端口 / Or use custom port
APP_PORT=8080 docker-compose up -d
```

#### 2. 数据库连接失败 / Database Connection Failed

```bash
# 检查数据库容器状态 / Check database container status
docker-compose ps

# 重启数据库 / Restart database
docker-compose restart mysql

# 查看数据库日志 / View database logs
docker-compose logs mysql
```

#### 3. 权限问题 / Permission Issues

```bash
# 修复存储目录权限 / Fix storage directory permissions
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

#### 4. 清除缓存 / Clear Cache

```bash
# 清除所有缓存 / Clear all caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

---

## 🛠️ 维护操作 / Maintenance Operations

### 更新镜像 / Update Image

```bash
# 1. 拉取最新镜像 / Pull latest image
docker-compose pull

# 2. 停止服务 / Stop services
docker-compose down

# 3. 启动新版本 / Start new version
docker-compose up -d

# 4. 运行迁移 / Run migrations
docker-compose exec app php artisan migrate --force
```

### 扩展服务 / Scale Services

```bash
# 扩展应用实例 / Scale application instances
docker-compose up -d --scale app=3
```

### 监控资源使用 / Monitor Resource Usage

```bash
# 查看容器资源使用情况 / View container resource usage
docker stats

# 查看磁盘使用 / View disk usage
docker system df
```

---

## 📊 性能优化 / Performance Optimization

### 1. 启用 OPcache

已在 Docker 镜像中预配置 / Pre-configured in Docker image

### 2. 配置 Redis 缓存

```bash
# 在 .env 中配置 / Configure in .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 3. 数据库优化

```bash
# 优化数据库表 / Optimize database tables
docker-compose exec mysql mysqlcheck -u root -p --optimize --all-databases
```

### 4. 启用 CDN

```bash
# 配置 CDN URL / Configure CDN URL
ASSET_URL=https://cdn.your-domain.com
```

---

## 🔐 安全建议 / Security Recommendations

### 1. 修改默认密码

```bash
# 立即修改数据库密码 / Change database password immediately
DB_PASSWORD=your_very_strong_password_123456!@#
```

### 2. 启用 HTTPS

```bash
# 使用 Let's Encrypt 免费 SSL 证书 / Use Let's Encrypt free SSL
docker run -it --rm \
  -v /etc/letsencrypt:/etc/letsencrypt \
  -v /var/lib/letsencrypt:/var/lib/letsencrypt \
  certbot/certbot certonly --standalone \
  -d your-domain.com
```

### 3. 配置防火墙

```bash
# Ubuntu/Debian
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable

# CentOS/RHEL
sudo firewall-cmd --add-service=http --permanent
sudo firewall-cmd --add-service=https --permanent
sudo firewall-cmd --reload
```

### 4. 定期备份

设置自动备份脚本（见上文"备份数据库"部分）
Setup automatic backup script (see "Backup Database" section above)

---

## 📞 技术支持 / Technical Support

### 在线资源 / Online Resources

- **GitHub Repository**: https://github.com/hhongli1979-coder/chibank999
- **Issues**: https://github.com/hhongli1979-coder/chibank999/issues
- **文档 / Documentation**: 查看 `docs/` 目录 / See `docs/` directory

### 联系方式 / Contact

- **GitHub**: @hhongli1979-coder
- **Issues**: 在 GitHub 上创建 Issue / Create issue on GitHub

---

## 🎓 学习资源 / Learning Resources

### 官方文档 / Official Documentation

- **完整部署文档**: `DEPLOYMENT.md`
- **快速开始指南**: `QUICK_START_CN.md`
- **系统分析报告**: `COMPREHENSIVE_ANALYSIS_REPORT_CN.md`
- **发布指南**: `RELEASE_AND_DEPLOYMENT_GUIDE.md`

### 视频教程 / Video Tutorials

即将推出 / Coming soon

---

## ✅ 验证清单 / Verification Checklist

部署完成后，请验证以下内容：
After deployment, please verify the following:

- [ ] 所有容器正在运行 / All containers are running
  ```bash
  docker-compose ps
  ```

- [ ] 应用可以访问 / Application is accessible
  ```bash
  curl http://localhost
  ```

- [ ] 数据库连接正常 / Database connection is working
  ```bash
  docker-compose exec app php artisan migrate:status
  ```

- [ ] Redis 缓存可用 / Redis cache is available
  ```bash
  docker-compose exec app php artisan cache:clear
  ```

- [ ] 队列处理正常 / Queue processing is working
  ```bash
  docker-compose exec app php artisan queue:work --once
  ```

- [ ] 日志文件可写 / Log files are writable
  ```bash
  docker-compose exec app ls -la storage/logs/
  ```

---

## 📦 镜像更新日志 / Image Changelog

### v5.0.0 (Latest)
- ✅ 初始发布 / Initial release
- ✅ Laravel 9 + PHP 8.1
- ✅ 所有功能模块 / All feature modules
- ✅ 12 个支付网关 / 12 payment gateways
- ✅ 生产环境优化 / Production optimized

---

**文档版本 / Document Version**: 1.0  
**最后更新 / Last Updated**: 2025-12-05  
**维护者 / Maintainer**: @hhongli1979-coder  

© 2024-2025 ChiBank. All Rights Reserved.
