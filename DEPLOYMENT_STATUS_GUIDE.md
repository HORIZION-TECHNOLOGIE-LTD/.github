# 部署状态检查指南 / Deployment Status Check Guide

## 快速检查 / Quick Check

### 1. 查看 GitHub Actions 状态 / Check GitHub Actions Status

访问 / Visit:
```
https://github.com/hhongli1979-coder/chibank999/actions
```

### 2. 主要工作流 / Main Workflows

#### Build and Deploy (deploy.yml)
- **触发条件 / Triggers:** Push to main/master/production, Pull Requests
- **主要任务 / Main Tasks:**
  - 构建和测试 (Build and Test)
  - 构建 Docker 镜像 (Build Docker Image)
  - 部署到生产环境 (Deploy to Production - 仅 production 分支)

#### Docker Image CI (docker-image.yml)
- **触发条件 / Triggers:** Push to main/master, Tags
- **主要任务 / Main Tasks:**
  - 构建 Docker 镜像 (Build Docker Image)
  - 推送到 GitHub Container Registry (Push to GHCR)

#### Automated Release (release.yml)
- **触发条件 / Triggers:** Version tags (v*.*.*)
- **主要任务 / Main Tasks:**
  - 创建发布包 (Create Release Package)
  - 构建和推送 Docker 镜像 (Build and Push Docker Image)
  - 创建 GitHub Release (Create GitHub Release)

## 已修复的问题 / Fixed Issues

### ✅ 问题 1: 已弃用的 Actions / Deprecated Actions

**错误信息 / Error Message:**
```
This request has been automatically failed because it uses a deprecated version of `actions/upload-artifact: v3`
```

**修复 / Fix:**
- ✅ 已升级到 v4 / Upgraded to v4

### ✅ 问题 2: 缓存 Action 过时 / Outdated Cache Action

**修复 / Fix:**
- ✅ `actions/cache@v3` → `actions/cache@v4`

## 部署检查清单 / Deployment Checklist

### 开发环境 / Development

- [ ] 代码已提交到分支 / Code committed to branch
- [ ] 所有测试通过 / All tests passing
- [ ] GitHub Actions 工作流成功 / GitHub Actions workflows successful
- [ ] 代码审查通过 / Code review approved

### 生产环境 / Production

- [ ] 合并到 main/master 分支 / Merged to main/master
- [ ] Docker 镜像构建成功 / Docker image built successfully
- [ ] 镜像已推送到 Registry / Image pushed to registry
- [ ] 部署脚本已执行 / Deployment scripts executed
- [ ] 服务健康检查通过 / Service health checks passing

## 常见问题 / Common Issues

### 问题: 工作流失败 / Workflow Failure

**检查步骤 / Check Steps:**

1. 查看工作流日志 / View workflow logs
   ```bash
   # 访问 / Visit
   https://github.com/hhongli1979-coder/chibank999/actions
   ```

2. 检查环境变量 / Check environment variables
   ```bash
   # 确保 .env 文件配置正确 / Ensure .env is configured correctly
   cat .env
   ```

3. 验证 Docker 配置 / Verify Docker configuration
   ```bash
   # 测试 Docker 构建 / Test Docker build
   docker build -t chibank-test .
   ```

### 问题: 部署超时 / Deployment Timeout

**解决方案 / Solutions:**

1. 增加工作流超时时间 / Increase workflow timeout
2. 优化 Docker 镜像大小 / Optimize Docker image size
3. 使用多阶段构建 / Use multi-stage builds

### 问题: 数据库迁移失败 / Database Migration Failed

**检查步骤 / Check Steps:**

1. 验证数据库连接 / Verify database connection
   ```bash
   docker-compose exec app php artisan migrate:status
   ```

2. 手动运行迁移 / Manually run migrations
   ```bash
   docker-compose exec app php artisan migrate --force
   ```

3. 检查数据库权限 / Check database permissions

## 监控和日志 / Monitoring and Logs

### 查看应用日志 / View Application Logs

```bash
# Docker 日志 / Docker logs
docker-compose logs -f app

# Laravel 日志 / Laravel logs
docker-compose exec app tail -f storage/logs/laravel.log
```

### 查看工作流日志 / View Workflow Logs

1. 访问 GitHub Actions 页面 / Visit GitHub Actions page
2. 选择失败的工作流 / Select failed workflow
3. 查看详细日志 / View detailed logs
4. 下载日志文件 / Download log files (如需要 / if needed)

## 成功部署的标志 / Signs of Successful Deployment

✅ **GitHub Actions:**
- ✅ 所有工作流显示绿色对勾 / All workflows show green checkmarks
- ✅ 构建时间正常 / Build time is normal
- ✅ 没有警告或错误 / No warnings or errors

✅ **Docker:**
- ✅ 镜像成功推送 / Image successfully pushed
- ✅ 容器正常运行 / Containers running normally
- ✅ 健康检查通过 / Health checks passing

✅ **应用程序 / Application:**
- ✅ 网站可以访问 / Website accessible
- ✅ API 响应正常 / API responding normally
- ✅ 数据库连接成功 / Database connected successfully

## 获取帮助 / Get Help

如果遇到问题，请 / If you encounter issues:

1. **查看文档 / Check documentation:**
   - [DEPLOYMENT.md](./DEPLOYMENT.md)
   - [DEPLOYMENT_FIX_SUMMARY.md](./DEPLOYMENT_FIX_SUMMARY.md)
   - [QUICK_START_CN.md](./QUICK_START_CN.md)

2. **查看工作流 / Check workflows:**
   - [GitHub Actions](https://github.com/hhongli1979-coder/chibank999/actions)

3. **提交问题 / Create an issue:**
   - [GitHub Issues](https://github.com/hhongli1979-coder/chibank999/issues)

---

**最后更新 / Last Updated:** December 5, 2025  
**状态 / Status:** ✅ 部署系统已修复 / Deployment System Fixed
