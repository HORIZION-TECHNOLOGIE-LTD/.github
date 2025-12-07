# ChiBank v5.0.0 - 发行版制作和自动化部署指南
## Release Package Creation and Automated Deployment Guide

---

## 📦 软件包制作 (Release Package Creation)

ChiBank 提供了完整的发行版制作工具，可以创建包含所有必要文件和依赖的软件包（软包）。

### 方法 1: 使用脚本创建

```bash
# 创建指定版本的发行包
./scripts/create-release.sh 5.0.0

# 创建 latest 版本
./scripts/create-release.sh latest
```

### 方法 2: 使用 Makefile

```bash
# 创建发行包
make release VERSION=5.0.0
```

### 方法 3: 使用 NPM

```bash
# 创建发行包
npm run release 5.0.0
```

### 发行包内容

创建的软件包包含：

```
chibank-v5.0.0-{timestamp}.tar.gz
├── app/                          # 应用核心代码
├── config/                       # 配置文件
├── database/                     # 数据库迁移和种子
├── public/                       # 公共资源
│   └── build/                   # 编译后的前端资源
├── resources/                    # 资源文件
├── routes/                       # 路由文件
├── storage/                      # 存储目录
├── vendor/                       # Composer 依赖（生产）
├── .env.example                 # 环境配置示例
├── composer.json                # Composer 配置
├── docker-compose.yml           # Docker Compose 配置
├── Dockerfile                   # Docker 镜像配置
├── Makefile                     # Make 命令
├── VERSION.txt                  # 版本信息
└── README.md                    # 说明文档
```

### 校验和验证

每个发行包都包含校验和文件 (`.checksums`)，用于验证文件完整性：

```bash
# 验证 MD5
md5sum -c chibank-v5.0.0-*.tar.gz.checksums

# 验证 SHA256
sha256sum -c chibank-v5.0.0-*.tar.gz.checksums
```

---

## 🚀 自动化部署 (Automated Deployment)

ChiBank 支持多种自动化部署方式：

### 1. GitHub Actions 自动发布

当推送版本标签时，GitHub Actions 会自动创建发行包并发布到 GitHub Releases。

```bash
# 创建并推送版本标签
git tag v5.0.0
git push origin v5.0.0

# GitHub Actions 会自动:
# 1. 构建前端资源
# 2. 安装依赖
# 3. 创建发行包
# 4. 构建 Docker 镜像
# 5. 发布到 GitHub Releases
# 6. 推送到 Docker Hub
```

### 2. 手动触发发布

在 GitHub Actions 页面手动触发 "Automated Release and Package" 工作流：

1. 访问 `Actions` 标签
2. 选择 "Automated Release and Package"
3. 点击 "Run workflow"
4. 输入版本号（例如：5.0.0）
5. 点击 "Run workflow"

### 3. Docker 自动部署

#### 使用 Docker Compose

```bash
# 1. 下载软件包
wget https://github.com/hhongli1979-coder/chibank999/releases/download/v5.0.0/chibank-v5.0.0-*.tar.gz

# 2. 解压
tar -xzf chibank-v5.0.0-*.tar.gz
cd chibank-v5.0.0-*/

# 3. 配置环境
cp .env.example .env
# 编辑 .env 文件

# 4. 一键启动
docker-compose up -d

# 5. 初始化数据库
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed

# 6. 访问应用
# 浏览器打开: http://localhost
```

#### 使用预构建的 Docker 镜像

```bash
# 拉取最新镜像
docker pull chibank/qrpay:latest

# 或指定版本
docker pull chibank/qrpay:5.0.0

# 使用 docker-compose
docker-compose up -d
```

### 4. 传统服务器部署

#### 使用部署脚本

```bash
# 1. SSH 到服务器
ssh user@your-server.com

# 2. 下载并解压软件包
wget https://github.com/hhongli1979-coder/chibank999/releases/download/v5.0.0/chibank-v5.0.0-*.tar.gz
tar -xzf chibank-v5.0.0-*.tar.gz
cd chibank-v5.0.0-*/

# 3. 配置环境
cp .env.example .env
# 编辑 .env 文件
php artisan key:generate

# 4. 运行部署脚本
chmod +x scripts/deploy.sh
./scripts/deploy.sh
```

#### 手动部署步骤

```bash
# 1. 配置环境
cp .env.example .env
php artisan key:generate

# 2. 配置数据库
# 编辑 .env 文件中的数据库配置

# 3. 运行迁移
php artisan migrate --force
php artisan db:seed

# 4. 优化缓存
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. 设置权限
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 6. 配置 Web 服务器
# 参考 docs/zh-CN/部署文档.md
```

---

## 🔄 持续集成/持续部署 (CI/CD)

### GitHub Actions 工作流

ChiBank 包含以下自动化工作流：

1. **Build and Deploy** (`.github/workflows/deploy.yml`)
   - 触发: 每次推送到 main/master/production 分支
   - 功能:
     - 运行测试
     - 构建前端
     - 创建 Docker 镜像
     - 部署到生产环境

2. **Automated Release** (`.github/workflows/release.yml`)
   - 触发: 推送版本标签或手动触发
   - 功能:
     - 创建发行包
     - 构建 Docker 镜像
     - 发布到 GitHub Releases
     - 推送到 Docker Hub

3. **Docker Image CI** (`.github/workflows/docker-image.yml`)
   - 触发: 推送到 main 分支
   - 功能: 构建 Docker 镜像

### 配置 CI/CD 密钥

在 GitHub 仓库设置中配置以下密钥：

```
Settings > Secrets and variables > Actions > New repository secret
```

需要配置的密钥：

1. **DOCKER_USERNAME** - Docker Hub 用户名
2. **DOCKER_PASSWORD** - Docker Hub 密码或访问令牌
3. **DEPLOY_SSH_KEY** - SSH 私钥（用于部署到服务器）
4. **DEPLOY_HOST** - 部署服务器主机名或 IP
5. **DEPLOY_USER** - SSH 用户名
6. **DEPLOY_PATH** - 部署路径

---

## 📋 部署检查清单

### 部署前检查

- [ ] 服务器环境满足要求
  - [ ] PHP 8.0+
  - [ ] MySQL 8.0+ / MariaDB 10.5+
  - [ ] Nginx 1.18+ / Apache 2.4+
  - [ ] Redis 6.0+
  - [ ] Composer 2.x
  - [ ] Node.js 20.x
- [ ] 域名已配置
- [ ] SSL 证书已配置
- [ ] 数据库已创建
- [ ] .env 文件已配置
- [ ] 文件权限已设置

### 部署步骤

- [ ] 下载发行包
- [ ] 验证校验和
- [ ] 解压到部署目录
- [ ] 配置 .env 文件
- [ ] 生成应用密钥
- [ ] 运行数据库迁移
- [ ] 运行数据库种子（可选）
- [ ] 设置文件权限
- [ ] 配置 Web 服务器
- [ ] 配置队列工作者
- [ ] 配置定时任务
- [ ] 测试应用
- [ ] 配置监控和日志

### 部署后验证

- [ ] 首页可以访问
- [ ] 用户可以注册和登录
- [ ] 数据库连接正常
- [ ] Redis 连接正常
- [ ] 邮件发送正常
- [ ] 支付网关配置正常
- [ ] API 端点可以访问
- [ ] 静态资源加载正常
- [ ] 日志记录正常

---

## 🛠️ 常用命令

### Make 命令

```bash
make help           # 显示所有可用命令
make install        # 安装依赖
make build          # 构建前端
make build-prod     # 生产环境构建
make deploy         # 部署到生产
make release        # 创建发行包
make docker-build   # 构建 Docker 镜像
make docker-up      # 启动 Docker 容器
make docker-down    # 停止 Docker 容器
make test           # 运行测试
make lint           # 代码检查
make clean          # 清理缓存
```

### NPM 命令

```bash
npm run dev         # 开发模式
npm run build       # 构建前端
npm run build:prod  # 生产构建
npm run deploy      # 部署
npm run release     # 创建发行包
```

### Docker 命令

```bash
# 构建镜像
docker build -t chibank/qrpay:latest .

# 运行容器
docker-compose up -d

# 查看日志
docker-compose logs -f app

# 进入容器
docker-compose exec app sh

# 停止容器
docker-compose down

# 重启容器
docker-compose restart
```

### Artisan 命令

```bash
# 生成应用密钥
php artisan key:generate

# 运行迁移
php artisan migrate
php artisan migrate --force  # 生产环境

# 运行种子
php artisan db:seed

# 清除缓存
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 优化缓存
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 队列工作者
php artisan queue:work

# 定时任务
php artisan schedule:run
```

---

## 📞 技术支持

如有问题，请访问：

- **GitHub Issues**: https://github.com/hhongli1979-coder/chibank999/issues
- **文档中心**: `./docs/`
- **系统分析报告**: `./COMPREHENSIVE_ANALYSIS_REPORT_CN.md`
- **部署文档**: `./DEPLOYMENT.md`

---

## 📝 许可证

MIT License - 详见 LICENSE 文件

---

© 2024 ChiBank. All Rights Reserved.
