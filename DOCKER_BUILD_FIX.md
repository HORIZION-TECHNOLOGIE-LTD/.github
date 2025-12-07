# Docker Image Build Fix / Docker 镜像构建修复

## Problem / 问题

The Docker image build was failing with the error:
Docker 镜像构建失败，错误信息：

```
sh: vite: not found
```

## Root Cause / 根本原因

The Dockerfile was using `npm ci --only=production` which only installs production dependencies from package.json. However, `vite` and other build tools are listed as `devDependencies`, so they were not being installed. This caused the frontend build step to fail.

Dockerfile 使用了 `npm ci --only=production`，只安装生产环境依赖。但 `vite` 等构建工具在 package.json 中被列为 `devDependencies`，导致它们没有被安装，前端构建步骤失败。

##  Solution / 解决方案

### Changes Made / 所做的更改

1. **Updated npm install command** / **更新 npm 安装命令**
   - Changed from: `npm ci --only=production`  
   - Changed to: `npm ci` (without --only=production flag)
   - This ensures all dependencies, including devDependencies, are installed
   - Using `npm ci` maintains reproducible builds from package-lock.json

2. **Added missing source files** / **添加缺失的源文件**
   - Added `COPY src ./src` - Required for TypeScript sources
   - Added `COPY tsconfig.json ./` - Required for TypeScript configuration  
   - These files are needed by Vite to build the frontend properly

### Dockerfile Changes / Dockerfile 更改

```dockerfile
# Before / 之前
COPY package*.json ./
RUN npm ci --only=production
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

# After / 之后  
COPY package*.json ./
RUN npm ci  # Removed --only=production to include devDependencies
COPY resources ./resources
COPY src ./src
COPY vite.config.js ./
COPY tsconfig.json ./
RUN npm run build
```

## Testing / 测试

To build the Docker image:
构建 Docker 镜像：

```bash
docker build -t chibank999 .
```

To build with docker-compose:
使用 docker-compose 构建：

```bash
docker-compose build
```

## Notes / 注意事项

- The frontend build stage now correctly installs all necessary build tools using `npm ci`
- `npm ci` provides reproducible builds and better performance compared to `npm install`
- The multi-stage build ensures that devDependencies don't end up in the final production image

- 前端构建阶段现在使用 `npm ci` 正确安装所有必要的构建工具
- `npm ci` 比 `npm install` 提供更可靠和更快的构建
- 多阶段构建确保 devDependencies 不会出现在最终的生产镜像中

## Related Files / 相关文件

- `Dockerfile` - Main Docker configuration / 主要 Docker 配置
- `package.json` - Node.js dependencies / Node.js 依赖
- `vite.config.js` - Vite configuration / Vite 配置  
- `tsconfig.json` - TypeScript configuration / TypeScript 配置
