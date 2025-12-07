# Package.json 修复指南

## 问题
```
npm error code EJSONPARSE
npm error JSON.parse Invalid package.json
```

## 快速修复（3步）

### 1. 备份并删除旧文件
```bash
cd /www/wwwroot/chibank999
cp package.json package.json.backup
rm -f package-lock.json
rm -rf node_modules
```

### 2. 获取修复后的 package.json
```bash
# 方法1：从仓库下载最新版本
curl -fsSL https://raw.githubusercontent.com/hhongli1979-coder/chibank999/main/package.json -o package.json

# 方法2：手动替换整个文件内容（复制下面的完整内容）
```

### 3. 重新安装
```bash
npm install --legacy-peer-deps
npm run build
```

## 完整的正确 package.json

```json
{
    "private": true,
    "type": "module",
    "scripts": {
        "dev": "vite",
        "build": "vite build",
        "build:prod": "vite build --mode production",
        "typecheck": "tsc --noEmit",
        "deploy": "./scripts/deploy.sh",
        "docker:build": "./scripts/docker-build.sh",
        "docker:push": "./scripts/docker-build.sh --push",
        "release": "./scripts/create-release.sh"
    },
    "dependencies": {
        "react": "^18.2.0",
        "react-dom": "^18.2.0"
    },
    "devDependencies": {
        "@popperjs/core": "^2.11.6",
        "@types/react": "^18.2.0",
        "@types/react-dom": "^18.2.0",
        "@vitejs/plugin-react": "^4.3.0",
        "axios": "^1.6.0",
        "bootstrap": "^5.3.0",
        "laravel-vite-plugin": "^1.0.0",
        "lodash": "^4.17.21",
        "postcss": "^8.4.31",
        "sass": "^1.69.0",
        "typescript": "^5.3.0",
        "vite": "^5.4.0"
    }
}
```

## 常见错误原因

1. **重复的 JSON 对象** - 文件中有两个 `{` 开始
2. **多余的逗号** - 最后一项后面有逗号
3. **引号不匹配** - 使用了中文引号或缺少引号
4. **文件合并错误** - Git 合并时产生的冲突标记

## 验证 JSON 格式

```bash
# 使用 Node.js 验证
node -e "JSON.parse(require('fs').readFileSync('package.json'))"

# 使用 Python 验证  
python3 -m json.tool package.json

# 使用在线工具
# https://jsonlint.com/
```

## 如果还是不行

```bash
# 完全重置
rm -rf node_modules package-lock.json
npm cache clean --force
npm install --legacy-peer-deps
```

## Vite 5.4.0 新特性

✅ 更快的构建速度
✅ 更好的 HMR 性能
✅ 支持最新的 ES 特性
✅ 改进的错误提示
