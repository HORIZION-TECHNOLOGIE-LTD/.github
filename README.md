# ChiBankv5.0.0

Enterprise-grade digital payment gateway solution built on Laravel.

## 📚 Documentation

### Chinese (中文)
- [操作文档 (Operation Manual)](docs/zh-CN/操作文档.md)
- [部署文档 (Deployment Guide)](docs/zh-CN/部署文档.md)
- [Docker 镜像管理 (Docker Image Management)](docs/zh-CN/Docker镜像管理.md)
- [白皮书 (White Paper)](docs/zh-CN/白皮书.md)
- [关于 GitHub Copilot CLI](docs/zh-CN/关于-GitHub-Copilot-CLI.md)

### English
- [Operation Manual](docs/en/OPERATION-MANUAL.md)
- [Deployment Guide](docs/en/DEPLOYMENT-GUIDE.md)
- [Docker Image Management](docs/en/DOCKER-IMAGE-MANAGEMENT.md)
- [White Paper](docs/en/WHITEPAPER.md)
- [About GitHub Copilot CLI](docs/en/ABOUT-GITHUB-COPILOT-CLI.md)
- [Vercel Deployment Guide](docs/VERCEL_DEPLOYMENT.md) ☁️ New!
- [Vercel AI Integration](docs/VERCEL_AI_INTEGRATION.md) 🤖 AI-Powered!

## 🚀 超简单部署 / Super Simple Deploy

**一步一步跟着做** → [安装步骤.md](安装步骤.md) ⭐️ 最简单！

其他方法：
- [快速部署.md](快速部署.md) - 复制粘贴一个脚本搞定
- [简易部署.md](简易部署.md) - 3步完成
- [ONE_CLICK_DEPLOY.md](ONE_CLICK_DEPLOY.md) - 完整详细文档
- [**数据库连接验证部署**](DEPLOYMENT_WITH_DB_CHECK.md) - 🆕 含数据库自动验证功能

## ✨ 新功能 / New Features

### 🔒 数据库连接自动验证 / Automatic Database Connection Validation

安装程序现在会在保存配置前自动验证数据库连接！
The installer now automatically validates database connections before saving configuration!

- ✅ 防止无效的数据库凭据 / Prevents invalid database credentials
- ✅ 自动检测连接问题 / Automatically detects connection issues  
- ✅ 支持自动创建数据库 / Supports automatic database creation
- ✅ 提供清晰的错误提示 / Provides clear error messages
- ✅ SQL注入防护 / SQL injection protection

详细信息请查看: [数据库连接验证部署指南](DEPLOYMENT_WITH_DB_CHECK.md)

---

## 🐳 Quick Start with Docker (Recommended) / 使用 Docker 快速开始（推荐）

### Pull Pre-built Image / 直接拉取预构建镜像

```bash
# Pull the latest image / 拉取最新镜像
docker pull ghcr.io/hhongli1979-coder/chibank999:main

# Clone repository to get configuration files / 克隆仓库获取配置文件
git clone --depth 1 https://github.com/hhongli1979-coder/chibank999.git
cd chibank999

# Configure environment / 配置环境
cp .env.example .env
# Edit .env and set DB_PASSWORD to a strong password / 编辑 .env 并设置强密码
# nano .env 或 vim .env

# Start all services with Docker Compose / 使用 Docker Compose 启动所有服务
docker-compose -f docker-compose.prod.yml up -d

# Initialize database / 初始化数据库
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Access application at http://localhost
# 访问 http://localhost
```

### Build from Source with Docker / 从源码构建 Docker

```bash
# Clone and build / 克隆并构建
git clone https://github.com/hhongli1979-coder/chibank999.git
cd chibank999
cp .env.example .env
docker-compose up -d --build
docker-compose exec app php artisan migrate --force
```

## 🚀 Quick Start (Development) / 开发环境快速开始

```bash
# Clone the repository
git clone https://github.com/hhongli1979-coder/chibank999.git
cd chibank999

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate
php artisan db:seed

# Start the application
php artisan serve
```

## ☁️ Deploy to Vercel (Serverless)

Deploy ChiBank to Vercel with AI integration in minutes:

```bash
# Install Vercel CLI
npm install -g vercel

# Deploy
vercel

# For production
vercel --prod
```

**Features with Vercel:**
- ☁️ Serverless deployment
- 🤖 AI model integration (OpenAI, Anthropic, Google)
- 🚀 Automatic scaling
- 🌍 Global CDN
- 📊 Analytics and monitoring

See [Vercel Deployment Guide](docs/VERCEL_DEPLOYMENT.md) for detailed instructions.

## 🔧 Features

- Multi-role support (Users, Agents, Merchants, Admin)
- Multiple payment gateways (Stripe, PayPal, Flutterwave, Fiat24, etc.)
- Payment links generation
- Mobile top-up service
- Two-factor authentication
- Mobile application (Flutter)
- Multi-language support
- Blockchain integration (Fiat24 - Swiss digital banking)
- **🤖 AI Integration** (OpenAI, Anthropic, Google) - New!
- **☁️ Vercel Deployment** - Serverless ready!

## 🐳 Docker Image Management / Docker 镜像管理

ChiBank uses GitHub Container Registry (GHCR) for centralized Docker image management with access control.

**Available Images / 可用镜像:**
```bash
ghcr.io/hhongli1979-coder/chibank999:main     # Latest stable version
ghcr.io/hhongli1979-coder/chibank999:v5.0.0   # Specific version
```

**Documentation / 文档:**
- [Docker Image Management Guide (English)](docs/en/DOCKER-IMAGE-MANAGEMENT.md) - Complete guide on managing images and access control
- [Docker 镜像管理指南 (中文)](docs/zh-CN/Docker镜像管理.md) - Docker 镜像集中管理和访问控制完整指南

**Key Features / 主要功能:**
- ✅ Centralized image storage / 集中镜像存储
- ✅ Fine-grained access control / 细粒度访问控制
- ✅ Automated CI/CD publishing / 自动化 CI/CD 发布
- ✅ Version management / 版本管理
- ✅ Security scanning / 安全扫描

## 📖 Additional Resources

- [API Documentation](docs/en/OPERATION-MANUAL.md#api-documentation)
- [Developer Portal](qrpay-documentations.html)
- [Fiat24 Integration Guide](docs/FIAT24_INTEGRATION.md)

## 📝 License

MIT License - see LICENSE file for details
