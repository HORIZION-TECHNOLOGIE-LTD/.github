# package.json 错误修复 / Fix package.json Error

## 错误信息 / Error Message
```
SyntaxError: Unexpected non-whitespace character after JSON at position 809 (line 32 column 2)
```

## 原因 / Cause
服务器上的 package.json 文件损坏或有多余内容。
The package.json file on the server is corrupted or has extra content.

---

## 🚀 快速修复 / Quick Fix

### 方法1：直接下载正确的文件（最简单）

```bash
cd /www/wwwroot/chibank999

# 备份旧文件
cp package.json package.json.broken

# 下载正确的文件
curl -fsSL https://raw.githubusercontent.com/hhongli1979-coder/chibank999/main/package.json -o package.json

# 验证
node -e "JSON.parse(require('fs').readFileSync('package.json'))" && echo "✓ 修复成功"
```

### 方法2：手动创建正确的文件

```bash
cd /www/wwwroot/chibank999

# 删除旧文件
rm package.json

# 创建新文件（复制下面全部内容）
cat > package.json << 'ENDOFJSON'
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
ENDOFJSON

# 验证
node -e "JSON.parse(require('fs').readFileSync('package.json'))" && echo "✓ 修复成功"
```

---

## 🔍 检查文件是否损坏

```bash
cd /www/wwwroot/chibank999

# 查看文件内容（看是否有多余内容）
cat package.json

# 查看文件大小（应该是 933 字节）
wc -c package.json

# 查看行数（应该是 32 行）
wc -l package.json

# 查看文件末尾（应该只有 } 和换行）
tail -3 package.json
```

---

## ⚠️ 常见问题

### 问题1：文件有两个 JSON 对象
**现象**: 看到两个 `{...}` 块  
**解决**: 用方法1或方法2重新创建文件

### 问题2：文件末尾有多余内容
**现象**: 最后一行不是 `}`  
**解决**: 删除多余内容，或用方法1重新下载

### 问题3：编辑器保存时添加了 BOM
**现象**: 文件看起来正常但报错  
**解决**: 用 UTF-8 无 BOM 格式保存，或用方法1重新下载

---

## ✅ 验证修复成功

```bash
# 验证 JSON 格式
node -e "JSON.parse(require('fs').readFileSync('package.json'))" && echo "✓ JSON 格式正确"

# 或用 Python
python3 -m json.tool package.json > /dev/null && echo "✓ JSON 格式正确"
```

---

## 📝 修复后重新安装

```bash
# 清理
rm -rf node_modules package-lock.json

# 重新安装
npm install --legacy-peer-deps

# 构建
npm run build
```

---

**如果还有问题，请运行 `cat package.json` 并把完整输出发给我。**
