# Chibank 企业级金融平台 v5.0.0 - 功能介绍文档

## 概述

本项目包含两个版本的Chibank平台功能介绍文档：

1. **静态版本** (`chibank-platform-documentation.html`) - 适合打印或生成PDF
2. **交互式版本** (`chibank-platform-documentation-interactive.html`) - 包含Mermaid.js交互式架构图，适合在线浏览

## 文档特性

### 设计风格
- **专业金融科技风格**：深蓝渐变背景配合金色AI粒子效果
- **企业级视觉设计**：采用Chibank品牌色系（深蓝 + 金色）
- **响应式布局**：支持多种设备尺寸浏览

### 包含内容

#### 一、平台概述
- 平台定位与核心优势
- 企业级架构特性
- 安全合规亮点
- AI智能化能力
- 全球化服务

#### 二、核心功能模块
- **SEPA支付中心**：即时支付、批量转账、定期付款
- **加密资产网关**：多链支持、冷热钱包管理、多签机制
- **企业级SaaS平台**：多级组织架构、多签钱包、审批工作流
- **AI风控大脑**：实时监控、异常检测、风险评估

#### 三、系统架构
- 整体架构图（含Mermaid.js交互式图表）
- 技术架构层次
- 微服务架构设计
- 数据流架构

#### 四、技术栈
- 后端技术：PHP 8.0+, Laravel 9.x, Redis等
- 前端技术：Vue.js 3, Bootstrap 5, Vite等
- 支付网关集成：Stripe, PayPal, Flutterwave等
- 安全技术：OAuth 2.0, 2FA, 端到端加密

#### 五、全球合规框架
- 欧盟EMI监管合规
- SEPA支付标准
- 数据保护合规（GDPR, PCI DSS, ISO 27001）
- 反洗钱与反恐融资措施

#### 六、AI赋能特性
- AI风控大脑工作流程
- 智能决策引擎
- 自动化运营成效
- 持续学习与优化

## 使用方法

### 在线浏览

#### 方法1：直接在浏览器中打开
```bash
# 启动本地服务器
php artisan serve

# 然后在浏览器中访问：
# 交互式版本（推荐）：
http://localhost:8000/chibank-platform-documentation-interactive.html

# 静态版本：
http://localhost:8000/chibank-platform-documentation.html
```

#### 方法2：使用任何HTTP服务器
```bash
# 使用PHP内置服务器
cd public
php -S localhost:8000

# 或使用Python
cd public
python3 -m http.server 8000

# 或使用Node.js http-server
cd public
npx http-server -p 8000
```

### 生成PDF

#### 方法1：使用浏览器打印功能
1. 在浏览器中打开静态版本文档
2. 按 `Ctrl+P` (Windows) 或 `Cmd+P` (Mac)
3. 选择"保存为PDF"
4. 调整打印设置：
   - 纸张大小：A4
   - 边距：默认
   - 背景图形：启用
5. 保存PDF文件

#### 方法2：使用Chrome无头模式
```bash
# 安装Chrome或Chromium
# 然后运行：
google-chrome --headless --disable-gpu --print-to-pdf=chibank-platform-v5.0.0.pdf http://localhost:8000/chibank-platform-documentation.html
```

#### 方法3：使用专业PDF转换工具
推荐工具：
- **wkhtmltopdf**：命令行工具，支持CSS打印样式
- **Puppeteer**：Node.js库，可编程生成PDF
- **Prince XML**：专业的HTML到PDF转换工具

示例（使用wkhtmltopdf）：
```bash
# 安装wkhtmltopdf
sudo apt-get install wkhtmltopdf  # Ubuntu/Debian
# 或
brew install wkhtmltopdf  # macOS

# 生成PDF
wkhtmltopdf --enable-local-file-access \
  --page-size A4 \
  --margin-top 0 \
  --margin-bottom 0 \
  --margin-left 0 \
  --margin-right 0 \
  --enable-javascript \
  http://localhost:8000/chibank-platform-documentation.html \
  chibank-platform-v5.0.0.pdf
```

## 文件说明

### chibank-platform-documentation.html
- **用途**：用于打印或生成PDF的静态版本
- **特点**：
  - 优化的打印样式
  - 静态架构图（无需外部依赖）
  - 页面分页符设置
  - 适合离线使用

### chibank-platform-documentation-interactive.html
- **用途**：在线浏览的交互式版本
- **特点**：
  - 使用Mermaid.js渲染交互式架构图
  - 导航菜单支持快速跳转
  - 响应式设计
  - 悬停效果和动画
  - 需要网络连接（加载Mermaid.js CDN）

## 自定义与扩展

### 修改样式
两个文档都使用内嵌CSS样式，可以直接在`<style>`标签中修改：

```css
/* 主色调修改 */
.cover-page {
    background: linear-gradient(135deg, #0a1929 0%, #1e3a5f 50%, #2d5a8c 100%);
}

/* 金色强调色 */
.cover-title {
    color: #ffd700;
}
```

### 添加新内容
在对应的`<div class="page content-page">`区块中添加新的章节：

```html
<div class="page content-page">
    <h1>七、新章节标题</h1>
    <h2>7.1 子章节</h2>
    <p>内容...</p>
</div>
```

### 添加新的Mermaid图表
在交互式版本中添加新的架构图：

```html
<div class="diagram-container">
    <div class="mermaid">
    graph TD
        A[节点A] --> B[节点B]
        B --> C[节点C]
    </div>
</div>
```

## 技术要求

### 浏览器兼容性
- Chrome/Edge 90+
- Firefox 88+
- Safari 14+

### 依赖项
- **交互式版本**：需要网络连接以加载Mermaid.js CDN
- **静态版本**：无外部依赖，可完全离线使用

## 品牌信息

- **产品名称**：Chibank 企业级金融平台
- **版本号**：v5.0.0
- **公司**：地平线AI智能科技
- **官方网站**：https://chibank.eu
- **定位**：AI赋能的全球金融基础设施

## 许可证

本文档遵循与主项目相同的许可证。

## 支持与反馈

如有问题或建议，请通过以下方式联系：
- 官方网站：https://chibank.eu
- 项目仓库：提交Issue或Pull Request

---

**地平线AI智能科技** | chibank.eu | © 2024
