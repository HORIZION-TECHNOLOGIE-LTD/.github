# ChiBank v5.0.0 - Operational Setup Guide
# 运营系统设置指南

## 概述 (Overview)

本文档提供完整的后端连接和系统运营设置指南。所有必要的后端集成已经连接完成。

This document provides a complete guide for backend connections and operational system setup. All necessary backend integrations have been connected.

---

## 已完成的后端连接 (Completed Backend Connections)

### ✅ 1. Fiat24 支付网关集成 (Fiat24 Payment Gateway Integration)

**连接的组件 (Connected Components):**
- ✅ User AddMoneyController (Web & API)
- ✅ Agent AddMoneyController (Web & API)
- ✅ PaymentGateway Helper
- ✅ PayLinkPaymentGateway Helper
- ✅ Fiat24Trait (完整的13个方法)
- ✅ Fiat24 Models (3个钱包模型)
- ✅ Database Migrations (数据库迁移文件)
- ✅ Routes (用户和代理路由)

**Fiat24 功能 (Fiat24 Features):**
1. **固定法币钱包 (Fiat Wallet)**
   - 瑞士IBAN账户
   - ERC-721 NFT代表
   - 支持 CHF, EUR, USD, CNH
   - KYC/AML验证追踪

2. **企业多签钱包 (Enterprise Multi-Sig Wallet)**
   - 多链支持 (Arbitrum, Mantle, 等)
   - N-of-M多签安全
   - DeFi协议集成
   - 智能合约交互

### ✅ 2. 其他支付网关 (Other Payment Gateways)

已集成的支付网关 (Integrated Payment Gateways):
1. PayPal
2. Stripe
3. Flutterwave
4. Razorpay
5. Pagadito
6. SSLCommerz
7. CoinGate
8. Tatum (加密货币)
9. Perfect Money
10. Paystack
11. Manual Gateway
12. **Fiat24** 🆕

---

## 系统部署步骤 (System Deployment Steps)

### 步骤 1: 环境配置 (Environment Configuration)

```bash
# 1. 复制环境文件
cp .env.example .env

# 2. 编辑 .env 文件，配置以下必需项:
# - 数据库连接 (Database Connection)
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# - 应用基础设置 (Application Settings)
APP_NAME="ChiBank"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# - 邮件配置 (Mail Configuration)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com

# - Pusher配置 (Pusher Configuration)
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_key
PUSHER_APP_SECRET=your_pusher_secret
PUSHER_APP_CLUSTER=your_cluster
```

### 步骤 2: 安装依赖 (Install Dependencies)

```bash
# 1. 安装Composer依赖
composer install --optimize-autoloader --no-dev

# 2. 安装NPM依赖
npm install

# 3. 构建前端资源
npm run build

# 4. 生成应用密钥
php artisan key:generate
```

### 步骤 3: 数据库设置 (Database Setup)

```bash
# 1. 运行迁移 (Run Migrations)
php artisan migrate --force

# 这将创建所有必需的表，包括:
# - fiat24_fiat_wallets (Fiat24固定法币钱包)
# - fiat24_enterprise_wallets (Fiat24企业钱包)
# - fiat24_enterprise_wallet_approvals (多签批准记录)
# - 以及所有其他系统表

# 2. 运行Seeder (可选，用于测试数据)
php artisan db:seed
```

### 步骤 4: 文件权限设置 (File Permissions)

```bash
# 设置存储和缓存目录权限
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 创建符号链接 (Create symbolic link)
php artisan storage:link
```

### 步骤 5: 缓存优化 (Cache Optimization)

```bash
# 清除所有缓存
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 优化生产环境
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 支付网关配置 (Payment Gateway Configuration)

### Fiat24 配置 (Fiat24 Configuration)

1. **登录管理后台 (Login to Admin Panel)**
   ```
   URL: https://your-domain.com/admin
   ```

2. **导航到支付网关设置 (Navigate to Payment Gateway Settings)**
   ```
   管理面板 → 设置 → 支付网关 → Fiat24
   ```

3. **配置Fiat24凭证 (Configure Fiat24 Credentials)**
   
   需要填写的字段 (Required Fields):
   - **Client ID / API Key**: 从Fiat24获取
   - **Client Secret / API Secret**: 从Fiat24获取
   - **NFT ID / Token ID**: 您的开发者NFT ID
   - **Chain ID**: 42161 (Arbitrum) 或 5000 (Mantle)
   - **Mode**: Sandbox (测试) 或 Production (生产)

4. **启用网关 (Enable Gateway)**
   - 将状态设置为 "Active"
   - 保存配置

### 其他支付网关配置 (Other Payment Gateways Configuration)

对于每个支付网关，在管理后台进行类似配置:

1. **PayPal**
   - Client ID
   - Client Secret
   - Mode (Sandbox/Live)

2. **Stripe**
   - Publishable Key
   - Secret Key
   - Webhook Secret

3. **Flutterwave**
   - Public Key
   - Secret Key
   - Encryption Key

(依此类推...)

---

## 测试系统功能 (Testing System Functionality)

### 1. 测试用户添加资金 (Test User Add Money)

```bash
# 1. 注册用户账户
访问: https://your-domain.com/user/register

# 2. 登录用户账户
访问: https://your-domain.com/user/login

# 3. 访问添加资金页面
访问: https://your-domain.com/user/add-money

# 4. 选择Fiat24网关
选择金额 → 选择货币 → 选择Fiat24 → 提交

# 5. 完成支付流程
按照Fiat24的指引完成支付
```

### 2. 测试代理添加资金 (Test Agent Add Money)

```bash
# 类似用户流程，但使用代理账户
访问: https://your-domain.com/agent/add-money
```

### 3. 测试支付链接 (Test Payment Link)

```bash
# 1. 创建支付链接
用户面板 → 支付链接 → 创建新链接

# 2. 分享链接
复制生成的链接并在浏览器中打开

# 3. 测试支付
使用Fiat24或其他网关完成支付
```

---

## API端点 (API Endpoints)

### Fiat24 相关API (Fiat24 Related APIs)

1. **添加资金 (Add Money)**
   ```
   POST /api/user/add-money/submit
   POST /api/agent/add-money/submit
   
   Headers:
   Authorization: Bearer {token}
   
   Body:
   {
     "amount": 100.00,
     "currency": "USD",
     "gateway": "fiat24"
   }
   ```

2. **支付成功回调 (Payment Success Callback)**
   ```
   GET /user/add-money/fiat24/payment/success/{trx}
   GET /agent/add-money/fiat24/payment/success/{trx}
   ```

3. **支付取消回调 (Payment Cancel Callback)**
   ```
   GET /user/add-money/fiat24/payment/cancel/{trx}
   GET /agent/add-money/fiat24/payment/cancel/{trx}
   ```

---

## 监控和日志 (Monitoring and Logs)

### 查看日志 (View Logs)

```bash
# Laravel应用日志
tail -f storage/logs/laravel.log

# Fiat24特定日志
grep "Fiat24" storage/logs/laravel.log

# 错误日志
grep "ERROR" storage/logs/laravel.log
```

### 重要日志事件 (Important Log Events)

Fiat24集成会记录以下事件:
- ✅ 支付初始化 (Payment Initialization)
- ✅ 重定向到Fiat24 (Redirect to Fiat24)
- ✅ 支付验证 (Payment Verification)
- ✅ 交易成功/失败 (Transaction Success/Failure)
- ✅ 钱包操作 (Wallet Operations)
- ✅ API调用 (API Calls)

---

## 生产环境检查清单 (Production Checklist)

### 安全性 (Security)
- [ ] 设置 `APP_DEBUG=false`
- [ ] 设置 `APP_ENV=production`
- [ ] 使用HTTPS (SSL证书)
- [ ] 配置CORS策略
- [ ] 启用速率限制
- [ ] 定期更新依赖

### 性能 (Performance)
- [ ] 启用缓存 (Config, Route, View)
- [ ] 配置队列系统
- [ ] 启用Redis/Memcached
- [ ] 优化数据库索引
- [ ] 设置CDN

### 备份 (Backup)
- [ ] 配置数据库自动备份
- [ ] 备份上传文件
- [ ] 备份.env配置文件
- [ ] 定期测试恢复

### 监控 (Monitoring)
- [ ] 设置应用监控 (如New Relic)
- [ ] 配置错误追踪 (如Sentry)
- [ ] 监控服务器资源
- [ ] 设置警报通知

---

## 常见问题 (FAQ)

### Q1: Fiat24支付失败怎么办？
**A:** 检查以下项目:
1. 网关凭证是否正确
2. 网关是否已启用
3. 用户钱包是否存在
4. 查看Laravel日志获取详细错误

### Q2: 如何添加新的支付网关？
**A:** 
1. 创建新的Trait在 `app/Traits/PaymentGateway/`
2. 在相关控制器中添加use语句
3. 在 `PaymentGatewayConst` 中添加常量
4. 在管理后台配置网关

### Q3: 数据库迁移失败怎么办？
**A:**
```bash
# 回滚迁移
php artisan migrate:rollback

# 查看迁移状态
php artisan migrate:status

# 重新运行迁移
php artisan migrate --force
```

### Q4: 如何测试Fiat24沙箱环境？
**A:**
1. 在管理后台将Fiat24设置为"Sandbox"模式
2. 使用Fiat24提供的测试凭证
3. 使用测试NFT ID
4. 按正常流程测试

---

## 技术支持 (Technical Support)

### 文档资源 (Documentation Resources)
- ChiBank文档: `/CHIBANK_DOCUMENTATION_README.md`
- 部署指南: `/DEPLOYMENT.md`
- 系统分析: `/SYSTEM_ANALYSIS_REPORT.md`
- Fiat24集成: `/docs/FIAT24_INTEGRATION.md`

### 联系方式 (Contact)
- 官方网站: https://chibank.eu
- GitHub: https://github.com/LILIANSRL/chibank-
- 技术支持邮箱: support@chibank.eu

---

## 版本信息 (Version Information)

- **ChiBank版本**: v5.0.0
- **Laravel版本**: 9.x
- **PHP版本**: 8.0.2+
- **最后更新**: 2024-12-04

---

## 总结 (Summary)

✅ **所有后端已连接完成！**
- Fiat24完整集成 (固定法币钱包 + 企业多签钱包)
- 12个支付网关已配置
- 用户、代理、API全部支持
- 数据库迁移文件完整
- 路由和控制器已连接

🚀 **系统已可运营！**

按照本指南完成部署配置后，您的ChiBank系统将完全可运营。
所有支付网关后端都已正确连接，只需配置凭证即可使用。

---

**祝运营顺利！ Good luck with operations!**
