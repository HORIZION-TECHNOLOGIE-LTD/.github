# Docker 镜像管理和访问控制

本文档提供了关于在 GitHub Container Registry (GHCR) 中集中管理 Docker 镜像以及控制谁可以查看和访问这些镜像的全面指南。

## 目录

1. [概述](#概述)
2. [GitHub Container Registry (GHCR)](#github-container-registry-ghcr)
3. [发布 Docker 镜像](#发布-docker-镜像)
4. [访问控制](#访问控制)
5. [管理包可见性](#管理包可见性)
6. [认证和授权](#认证和授权)
7. [团队和组织访问](#团队和组织访问)
8. [最佳实践](#最佳实践)
9. [故障排除](#故障排除)

---

## 概述

ChiBank 使用 GitHub Container Registry (GHCR) 集中管理 Docker 镜像。GHCR 提供：

- **集中存储**：所有 Docker 镜像存储在一个位置
- **访问控制**：细粒度权限控制谁可以查看和拉取镜像
- **版本管理**：轻松跟踪和管理镜像版本
- **集成**：与 GitHub Actions 无缝集成实现 CI/CD
- **安全性**：内置安全扫描和漏洞检测

### 当前设置

ChiBank Docker 镜像发布到：
```
ghcr.io/hhongli1979-coder/chibank999:main
ghcr.io/hhongli1979-coder/chibank999:v*
ghcr.io/hhongli1979-coder/chibank999:sha-*
```

---

## GitHub Container Registry (GHCR)

### 什么是 GHCR？

GitHub Container Registry 是 GitHub 的容器镜像托管服务，允许您直接在 GitHub 账户或组织中存储和管理 Docker 和 OCI 镜像。

### 主要特性

1. **公共仓库免费**：公共镜像的存储和带宽无限制
2. **私有镜像**：根据您的 GitHub 计划，支持有使用限制的私有镜像
3. **细粒度访问**：在组织、团队和个人用户级别控制访问
4. **GitHub Actions 集成**：工作流中自动认证
5. **安全扫描**：自动扫描镜像漏洞

### 存储位置

- **个人账户**：`ghcr.io/username/image-name`
- **组织**：`ghcr.io/organization/image-name`

---

## 发布 Docker 镜像

### 使用 GitHub Actions 自动发布

ChiBank 使用 GitHub Actions 自动构建和发布 Docker 镜像。工作流在 `.github/workflows/docker-image.yml` 中定义。

#### 工作流触发器

镜像在以下情况下自动构建和发布：

1. **推送到 main/master 分支**：创建 `main` 或 `master` 标签
2. **创建版本标签**：推送 `v1.0.0` 等标签创建版本化镜像
3. **拉取请求**：为测试构建镜像（不发布）
4. **手动调度**：可以从 GitHub Actions UI 手动触发

#### 镜像标签

工作流自动创建多个标签：

- `main` - 来自 main 分支的最新版本
- `v1.0.0` - 语义版本标签
- `sha-abc123` - Git 提交 SHA 标签

### 手动发布

手动构建和推送镜像：

```bash
# 登录到 GHCR
echo $GITHUB_TOKEN | docker login ghcr.io -u USERNAME --password-stdin

# 构建镜像
docker build -t ghcr.io/hhongli1979-coder/chibank999:latest .

# 推送镜像
docker push ghcr.io/hhongli1979-coder/chibank999:latest
```

### 使用个人访问令牌 (PAT)

对于手动操作，创建具有适当权限的个人访问令牌：

1. 转到 GitHub 设置 → 开发者设置 → 个人访问令牌 → Tokens (classic)
2. 点击 "Generate new token (classic)"
3. 选择范围：
   - `write:packages` - 上传包到 GitHub Package Registry
   - `read:packages` - 从 GitHub Package Registry 下载包
   - `delete:packages` - 从 GitHub Package Registry 删除包
4. 点击 "Generate token" 并安全保存

```bash
# 使用 PAT 登录
echo $PAT | docker login ghcr.io -u USERNAME --password-stdin
```

---

## 访问控制

### 理解权限

GHCR 使用分层权限模型：

1. **仓库权限**：从关联的 GitHub 仓库继承
2. **包权限**：可以独立于仓库配置
3. **组织权限**：适用于组织的所有成员

### 权限级别

#### 公共包

- **Public（公开）**：任何人都可以拉取镜像（无需认证）
- **Internal（内部）**：仅组织成员可以拉取（需要认证）
- **Private（私有）**：仅指定的用户/团队可以拉取

#### 私有包

- **Read（读取）**：可以拉取和查看镜像
- **Write（写入）**：可以拉取和推送新版本
- **Admin（管理员）**：完全控制，包括删除版本和管理访问

### 默认访问

默认情况下，包从源仓库继承权限：

- 如果仓库是公开的 → 包是公开的
- 如果仓库是私有的 → 包是私有的
- 仓库协作者获得相应的包访问权限

---

## 管理包可见性

### 更改包可见性

#### 通过 GitHub Web 界面

1. 导航到仓库：`https://github.com/hhongli1979-coder/chibank999`
2. 点击右侧栏的 "Packages"
3. 选择包（例如 `chibank999`）
4. 点击右下角的 "Package settings"
5. 在 "Danger Zone" 下，选择可见性：
   - **Public（公开）**：任何人都可以无需认证拉取
   - **Private（私有）**：仅授权用户可以拉取
   - **Internal（内部）**：仅组织成员可以访问

#### 使包公开

使 ChiBank Docker 镜像公开访问：

1. 转到 `https://github.com/users/hhongli1979-coder/packages/container/chibank999/settings`
2. 滚动到 "Danger Zone"
3. 点击 "Change visibility"
4. 选择 "Public"
5. 输入包名称确认
6. 点击 "I understand, change package visibility"

**注意**：公开包允许任何人无需认证拉取镜像：

```bash
# 公开镜像无需登录
docker pull ghcr.io/hhongli1979-coder/chibank999:main
```

#### 保持包私有

私有包需要认证：

```bash
# 需要登录
echo $GITHUB_TOKEN | docker login ghcr.io -u USERNAME --password-stdin

# 然后拉取
docker pull ghcr.io/hhongli1979-coder/chibank999:main
```

---

## 认证和授权

### 认证到 GHCR

#### 方法 1：使用 GitHub Token（推荐用于 CI/CD）

```bash
echo $GITHUB_TOKEN | docker login ghcr.io -u USERNAME --password-stdin
```

#### 方法 2：使用个人访问令牌

```bash
echo $PAT | docker login ghcr.io -u USERNAME --password-stdin
```

#### 方法 3：使用 GitHub CLI

```bash
gh auth token | docker login ghcr.io -u USERNAME --password-stdin
```

### 授予用户访问权限

#### 添加个人用户

1. 转到包设置
2. 点击 "Manage Actions access" 或 "Manage package access"
3. 点击 "Add people or teams"
4. 搜索用户
5. 选择权限级别：
   - Read（读取）
   - Write（写入）
   - Admin（管理员）
6. 点击 "Add"

#### 对于组织包

组织所有者和具有适当权限的成员可以：

1. 导航到组织包
2. 选择包
3. 点击 "Package settings"
4. 在 "Manage Actions access" 下，添加团队或成员
5. 设置适当的权限级别

### 撤销访问

1. 转到包设置
2. 在访问列表中找到用户/团队
3. 点击其名称旁边的 "X" 或 "Remove" 按钮
4. 确认删除

---

## 团队和组织访问

### 组织级控制

#### 设置默认权限

对于组织，您可以设置默认包权限：

1. 转到组织设置
2. 在 "Code, planning, and automation" 下导航到 "Packages"
3. 设置包的默认权限：
   - 默认公开
   - 默认私有
   - 从仓库继承

#### 管理团队访问

授予整个团队访问权限：

1. 转到包设置
2. 点击 "Add people or teams"
3. 选择团队
4. 选择权限级别
5. 所有团队成员将获得指定的访问权限

### 组织的最佳实践

1. **使用团队**：授予团队访问权限而不是个人用户，以便于管理
2. **最小权限**：给予用户所需的最小权限
3. **定期审计**：定期审查谁可以访问包
4. **分离环境**：为开发/测试/生产使用不同的包或标签
5. **文档访问**：维护谁应该拥有访问权限以及原因的文档

### 仓库-包链接

将包链接到仓库以更好地组织：

1. 转到包设置
2. 在 "Danger Zone" 下，点击 "Connect repository"
3. 选择仓库
4. 包现在将出现在仓库的侧边栏中

链接的好处：
- 包默认继承仓库权限
- 显示在仓库 UI 中
- 协作者更容易发现

---

## 最佳实践

### 安全最佳实践

1. **使用最小范围的 PAT**：仅授予必要的权限
2. **定期轮换令牌**：定期更新 PAT 和 secrets
3. **启用 2FA**：要求组织成员使用双因素认证
4. **扫描镜像**：定期扫描镜像漏洞
5. **签名镜像**：使用 Docker Content Trust 或 sigstore 进行镜像签名
6. **敏感数据使用私有镜像**：如果镜像包含敏感信息，不要公开

### 版本管理

1. **使用语义版本控制**：用清晰的版本号标记镜像（v1.0.0, v1.0.1）
2. **谨慎使用 latest 标签**：保持 `latest` 标签用于稳定版本
3. **维护多个标签**：使用分支标签（main）、版本标签（v1.0.0）和 SHA 标签
4. **不可变标签**：不要覆盖现有版本标签
5. **清理旧镜像**：定期删除未使用的镜像以节省存储空间

### 文档

1. **记录拉取命令**：清楚地显示如何拉取镜像
2. **列出可用标签**：保持可用镜像标签的注册表
3. **访问要求**：记录谁需要访问以及如何请求
4. **更新说明**：保持此文档最新

### 监控和维护

1. **监控下载**：跟踪包下载指标
2. **审查访问日志**：定期审计访问模式
3. **更新依赖项**：保持基础镜像和依赖项更新
4. **自动化清理**：设置工作流以删除旧镜像
5. **监控存储**：跟踪存储使用情况（对私有包很重要）

---

## 故障排除

### 常见问题

#### 1. "unauthorized: authentication required"（未授权：需要认证）

**问题**：无法在没有认证的情况下拉取镜像

**解决方案**：
```bash
# 首先登录
echo $GITHUB_TOKEN | docker login ghcr.io -u USERNAME --password-stdin

# 然后拉取
docker pull ghcr.io/hhongli1979-coder/chibank999:main
```

#### 2. "denied: permission_denied"（拒绝：权限被拒）

**问题**：用户没有访问包的权限

**解决方案**：
- 检查包是否私有（使其公开或请求访问）
- 验证用户已被授予适当的权限
- 确认 PAT 具有 `read:packages` 范围
- 检查组织是否启用了 SSO（可能需要授权 PAT）

#### 3. 镜像未找到

**问题**：`Error: manifest unknown: manifest unknown`

**解决方案**：
- 验证镜像名称和标签是否正确
- 检查包是否存在于注册表中
- 确保镜像已成功发布
- 检查 GitHub Actions 工作流日志是否有构建失败

#### 4. GitHub Actions 构建失败

**问题**：工作流无法推送镜像

**解决方案**：
- 检查 `GITHUB_TOKEN` 是否具有 `packages: write` 权限（应该是自动的）
- 验证 Dockerfile 没有错误
- 查看工作流日志以获取特定错误消息
- 确保仓库设置允许 GitHub Actions 推送包

#### 5. 速率限制

**问题**：对 GHCR 的请求过多

**解决方案**：
- 在 CI/CD 管道中实施缓存
- 使用经过身份验证的请求（更高的速率限制）
- 考虑为经常访问的镜像使用镜像或代理

### 获取帮助

如果遇到问题：

1. **检查 GitHub 状态**：访问 [githubstatus.com](https://www.githubstatus.com)
2. **查看文档**：GitHub 的官方 GHCR 文档
3. **检查工作流日志**：查看 GitHub Actions 日志中的错误
4. **联系仓库维护者**：在仓库中开启 issue
5. **GitHub 支持**：联系 GitHub 支持以解决平台问题

---

## 其他资源

### 官方文档

- [GitHub Container Registry 文档](https://docs.github.com/zh/packages/working-with-a-github-packages-registry/working-with-the-container-registry)
- [认证到 GitHub Packages](https://docs.github.com/zh/packages/working-with-a-github-packages-registry/working-with-the-container-registry#authenticating-to-the-container-registry)
- [管理包访问](https://docs.github.com/zh/packages/learn-github-packages/configuring-a-packages-access-control-and-visibility)

### 相关文档

- [Docker 部署指南](部署文档.md)
- [GitHub Actions 工作流](../../.github/workflows/docker-image.yml)
- [主 README](../../README.md)

---

## 总结

本文档涵盖：

- ✅ 设置和使用 GitHub Container Registry
- ✅ 自动和手动发布 Docker 镜像
- ✅ 使用细粒度权限控制访问
- ✅ 管理包可见性（公开/私有）
- ✅ 认证和授权用户
- ✅ 管理团队和组织级访问
- ✅ 安全和管理的最佳实践
- ✅ 常见问题故障排除

如有疑问或改进此文档的建议，请在仓库中开启 issue。
