# ChiBank 一键部署指南（含数据库连接验证）
# One-Click Deployment Guide (with Database Connection Validation)

## 🎯 新功能 / New Feature

**数据库连接自动验证 / Automatic Database Connection Validation**

部署系统现在会在保存配置前自动验证数据库连接，确保：
The deployment system now automatically validates database connections before saving configuration, ensuring:

✅ 数据库服务器可访问 / Database server is accessible
✅ 用户名和密码正确 / Username and password are correct
✅ 数据库存在或可创建 / Database exists or can be created
✅ 用户具有足够权限 / User has sufficient permissions

这避免了配置错误导致的部署失败！
This prevents deployment failures caused by configuration errors!

---

## 🚀 快速部署 / Quick Deployment

### 方式一：使用 Docker Compose（推荐）/ Method 1: Using Docker Compose (Recommended)

```bash
# 1. 克隆仓库 / Clone repository
git clone https://github.com/hhongli1979-coder/chibank999.git
cd chibank999

# 2. 配置环境 / Configure environment
cp .env.example .env
nano .env  # 编辑配置文件 / Edit configuration file

# 3. 启动服务 / Start services
docker-compose -f docker-compose.prod.yml up -d

# 4. 初始化数据库（系统会自动验证连接）/ Initialize database (system will auto-validate connection)
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# 5. 访问系统 / Access system
# 浏览器打开 http://localhost
# Open http://localhost in browser
```

### 方式二：一键部署脚本 / Method 2: One-Click Deployment Script

```bash
# 自动完成所有步骤，包括数据库连接验证
# Automatically completes all steps, including database connection validation
curl -fsSL https://raw.githubusercontent.com/hhongli1979-coder/chibank999/main/scripts/one-click-deploy.sh | bash
```

---

## 🔧 数据库配置 / Database Configuration

### 环境变量配置 / Environment Variables

在 `.env` 文件中配置以下参数：
Configure the following parameters in `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1          # 数据库主机 / Database host
DB_PORT=3306               # 数据库端口 / Database port
DB_DATABASE=chibank        # 数据库名称 / Database name
DB_USERNAME=root           # 数据库用户名 / Database username
DB_PASSWORD=your_password  # 数据库密码 / Database password
```

### 数据库名称要求 / Database Name Requirements

**重要**: 数据库名称必须符合以下规则：
**Important**: Database name must follow these rules:

- ✅ 1-64 个字符 / 1-64 characters
- ✅ 以字母、下划线或美元符号开头 / Start with letter, underscore, or dollar sign
- ✅ 只包含字母、数字、下划线和美元符号 / Contain only letters, numbers, underscores, and dollar signs
- ❌ 不能以数字开头 / Cannot start with number

**示例 / Examples:**
- ✅ `chibank`
- ✅ `chibank_db`
- ✅ `chibank_prod`
- ✅ `_chibank`
- ❌ `123chibank` (不能以数字开头 / Cannot start with number)
- ❌ `chibank-db` (不能包含连字符 / Cannot contain hyphen)

---

## 🛡️ 数据库连接验证详情 / Database Connection Validation Details

### 验证流程 / Validation Process

系统会按以下顺序验证数据库连接：
System validates database connection in the following order:

1. **验证数据库名称格式** / Validate database name format
   - 检查是否符合命名规则 / Check naming rules compliance
   - 防止 SQL 注入攻击 / Prevent SQL injection attacks

2. **测试服务器连接** / Test server connection
   - 连接到 MySQL 服务器 / Connect to MySQL server
   - 5秒超时设置 / 5-second timeout

3. **检查数据库存在性** / Check database existence
   - 使用预处理语句查询 / Query using prepared statement
   - 如果不存在则尝试创建 / Try to create if not exists

4. **验证数据库访问权限** / Verify database access
   - 连接到指定数据库 / Connect to specific database
   - 执行测试查询 / Execute test query

### 错误处理 / Error Handling

系统提供详细的错误信息：
System provides detailed error messages:

| 错误代码 / Error Code | 描述 / Description | 解决方法 / Solution |
|---|---|---|
| 1045 | 用户名或密码错误 / Invalid username or password | 检查 DB_USERNAME 和 DB_PASSWORD |
| 2002-2006 | 无法连接到服务器 / Cannot connect to server | 检查 DB_HOST 和 DB_PORT，确保 MySQL 服务运行 |
| 1049 | 数据库不存在且无法创建 / Database does not exist and cannot be created | 检查用户权限或手动创建数据库 |
| 1044/1142 | 权限不足 / Insufficient privileges | 授予用户足够的数据库权限 |

---

## 📦 Docker 镜像部署 / Docker Image Deployment

### 使用预构建镜像 / Using Pre-built Image

```bash
# 拉取最新镜像 / Pull latest image
docker pull ghcr.io/hhongli1979-coder/chibank999:main

# 使用 docker-compose.prod.yml 启动 / Start with docker-compose.prod.yml
docker-compose -f docker-compose.prod.yml up -d
```

### 构建自定义镜像 / Build Custom Image

```bash
# 构建包含数据库连接验证的镜像 / Build image with DB connection validation
docker build -t chibank999:custom .

# 标记和推送（可选）/ Tag and push (optional)
docker tag chibank999:custom ghcr.io/your-username/chibank999:latest
docker push ghcr.io/your-username/chibank999:latest
```

---

## 🔍 故障排除 / Troubleshooting

### 数据库连接失败 / Database Connection Failed

**问题**: 提示 "Cannot connect to database server"
**Problem**: Message "Cannot connect to database server"

**解决方法 / Solutions**:
1. 确认 MySQL 服务正在运行 / Confirm MySQL service is running
   ```bash
   docker ps --filter name=mysql
   # 或 / or
   systemctl status mysql
   ```

2. 检查防火墙设置 / Check firewall settings
   ```bash
   sudo ufw allow 3306/tcp
   ```

3. 验证主机和端口 / Verify host and port
   ```bash
   telnet localhost 3306
   ```

### 数据库权限问题 / Database Permission Issues

**问题**: 提示 "Insufficient privileges"
**Problem**: Message "Insufficient privileges"

**解决方法 / Solutions**:
1. 授予用户完整权限 / Grant full privileges
   ```sql
   GRANT ALL PRIVILEGES ON chibank.* TO 'chibank'@'%';
   FLUSH PRIVILEGES;
   ```

2. 或使用 root 用户 / Or use root user
   ```env
   DB_USERNAME=root
   DB_PASSWORD=root_password
   ```

### 数据库名称无效 / Invalid Database Name

**问题**: 提示 "Invalid database name"
**Problem**: Message "Invalid database name"

**解决方法 / Solutions**:
- 确保数据库名称符合命名规则（见上文）
- Ensure database name follows naming rules (see above)
- 移除特殊字符如连字符 / Remove special characters like hyphens
- 不要以数字开头 / Don't start with numbers

---

## 📝 部署检查清单 / Deployment Checklist

部署前请确认：
Before deployment, confirm:

- [ ] Docker 和 Docker Compose 已安装 / Docker and Docker Compose installed
- [ ] 至少 4GB 内存和 20GB 磁盘空间 / At least 4GB RAM and 20GB disk
- [ ] `.env` 文件已正确配置 / `.env` file properly configured
- [ ] 数据库名称符合命名规则 / Database name follows naming rules
- [ ] MySQL 服务可访问（如使用外部数据库）/ MySQL service accessible (if using external DB)
- [ ] 数据库用户具有足够权限 / Database user has sufficient permissions
- [ ] 端口 80 和 3306 未被占用 / Ports 80 and 3306 are available

---

## 🎉 部署成功验证 / Verify Successful Deployment

部署完成后，系统应该：
After deployment, system should:

1. ✅ 所有容器正在运行 / All containers running
   ```bash
   docker-compose -f docker-compose.prod.yml ps
   ```

2. ✅ 数据库连接成功 / Database connection successful
   ```bash
   docker-compose -f docker-compose.prod.yml exec app php artisan db:show
   ```

3. ✅ Web 界面可访问 / Web interface accessible
   - 打开浏览器访问 / Open browser to: http://localhost

4. ✅ 可以登录安装程序 / Can access installer
   - 导航到 / Navigate to: http://localhost/install

---

## 📚 相关文档 / Related Documentation

- [完整部署指南 / Full Deployment Guide](./DEPLOYMENT.md)
- [快速开始 / Quick Start](./QUICK_START_CN.md)
- [故障排除 / Troubleshooting](./OPERATIONAL_SETUP_GUIDE.md)
- [Docker 配置 / Docker Configuration](./docker-compose.prod.yml)

---

## 🤝 获取帮助 / Get Help

遇到问题？/ Having issues?

1. 查看日志 / Check logs:
   ```bash
   docker-compose -f docker-compose.prod.yml logs -f app
   ```

2. 提交 Issue / Submit an issue:
   https://github.com/hhongli1979-coder/chibank999/issues

3. 查看文档 / Check documentation:
   - [操作文档](./docs/zh-CN/操作文档.md)
   - [部署文档](./docs/zh-CN/部署文档.md)

---

**版本 / Version**: 5.0.0 with Database Connection Validation
**更新日期 / Last Updated**: 2025-12-05
