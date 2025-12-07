# iOS 安装指南 - ChiBank QRPay 用户端

## 📱 系统要求

- macOS 10.15 (Catalina) 或更高版本
- Xcode 15.0 或更高版本
- CocoaPods 1.11.0 或更高版本
- Flutter SDK 3.24.0
- iOS 13.0 或更高版本的设备或模拟器

---

## 🚀 快速安装（5步搞定）

### 第1步：安装 Flutter SDK

```bash
# 下载 Flutter SDK
cd ~/development
git clone https://github.com/flutter/flutter.git -b stable

# 添加 Flutter 到环境变量
export PATH="$PATH:`pwd`/flutter/bin"

# 验证安装
flutter doctor
```

**提示：** 把 `export PATH="$PATH:~/development/flutter/bin"` 添加到 `~/.zshrc` 或 `~/.bash_profile` 中永久生效。

---

### 第2步：安装 Xcode 和 CocoaPods

1. **安装 Xcode**
   - 从 App Store 安装 Xcode
   - 打开 Xcode 并同意许可协议
   - 安装命令行工具：
     ```bash
     sudo xcode-select --switch /Applications/Xcode.app/Contents/Developer
     sudo xcodebuild -runFirstLaunch
     ```

2. **安装 CocoaPods**
   ```bash
   sudo gem install cocoapods
   pod setup
   ```

---

### 第3步：克隆项目

```bash
cd ~/Projects
git clone https://github.com/HORIZION-TECHNOLOGIE-LTD/.github.git chibank
cd chibank/qrpay-user-app/qrpay-user-app-new-v5.0.0
```

---

### 第4步：安装依赖

```bash
# 安装 Flutter 依赖
flutter pub get

# 安装 iOS 依赖
cd ios
pod install
cd ..
```

---

### 第5步：运行应用

#### 使用模拟器运行

```bash
# 列出可用的模拟器
flutter emulators

# 启动模拟器
flutter emulators --launch apple_ios_simulator

# 运行应用
flutter run
```

#### 使用真机运行

1. 用 USB 连接 iPhone 到 Mac
2. 在 iPhone 上信任开发者（设置 > 通用 > 设备管理）
3. 运行命令：
   ```bash
   flutter run
   ```

---

## 🔧 详细配置

### 配置 API 端点

编辑 `lib/backend/utils/custom_api_url.dart` 文件，设置你的后端 API 地址：

```dart
class CustomApiUrl {
  static const String mainUrl = "https://your-api-domain.com";
}
```

### 配置应用信息

在 `ios/Runner/Info.plist` 中可以配置：

- 应用显示名称
- 权限说明（相机、相册、位置等）
- URL Schemes
- 其他应用设置

### 配置 Firebase（如果使用）

1. 在 [Firebase Console](https://console.firebase.google.com/) 创建项目
2. 添加 iOS 应用，Bundle ID: `net.appdevs.qrpayuser`
3. 下载 `GoogleService-Info.plist`
4. 将文件放到 `ios/Runner/` 目录
5. 在 Xcode 中添加文件引用

---

## 📦 构建发布版本

### 构建 IPA 文件

```bash
# 清理之前的构建
flutter clean

# 获取依赖
flutter pub get

# 构建 iOS release 版本
flutter build ios --release
```

### 使用 Xcode 打包上传

1. 在 Xcode 中打开项目：
   ```bash
   open ios/Runner.xcworkspace
   ```

2. 选择 Runner target

3. 配置签名和证书：
   - 选择你的开发团队
   - 配置 Provisioning Profile

4. 选择 Product > Archive

5. 上传到 App Store Connect

---

## 🎨 自定义配置

### 修改应用名称

使用 Flutter 包修改：

```bash
flutter pub run rename_app:main all="你的应用名称"
```

### 修改 Bundle ID

使用 Flutter 包修改：

```bash
flutter pub run change_app_package_name:main com.yourcompany.appname
```

### 修改应用图标

1. 准备 1024x1024 的图标文件
2. 放到 `assets/logo/app_launcher.png`
3. 运行命令：
   ```bash
   flutter pub run flutter_launcher_icons:main
   ```

---

## 🔍 常见问题

### 问题 1: Pod install 失败

```bash
# 清理 CocoaPods 缓存
cd ios
rm -rf Pods Podfile.lock
pod cache clean --all
pod install --repo-update
```

### 问题 2: 无法在真机运行

- 检查开发者证书是否配置正确
- 在 iPhone 设置中信任开发者
- 确保 Bundle ID 与证书匹配

### 问题 3: Flutter doctor 报错

```bash
# 接受 Android 许可
flutter doctor --android-licenses

# 安装缺失的组件
flutter doctor -v
```

### 问题 4: Xcode 构建失败

```bash
# 清理 Xcode 构建缓存
cd ios
rm -rf ~/Library/Developer/Xcode/DerivedData
xcodebuild clean
cd ..

# 重新构建
flutter clean
flutter pub get
cd ios && pod install && cd ..
flutter build ios
```

### 问题 5: 应用签名问题

1. 在 Xcode 中打开 `ios/Runner.xcworkspace`
2. 选择 Runner target
3. 在 Signing & Capabilities 中：
   - 勾选 "Automatically manage signing"
   - 选择你的 Team
   - 如果需要，修改 Bundle Identifier

---

## 📱 测试应用

### 单元测试

```bash
flutter test
```

### 集成测试

```bash
flutter drive --target=test_driver/app.dart
```

---

## 🔄 更新应用

```bash
# 拉取最新代码
git pull origin main

# 清理并重新安装依赖
flutter clean
flutter pub get
cd ios && pod install && cd ..

# 运行应用
flutter run
```

---

## 📚 相关文档

- [Flutter 官方文档](https://docs.flutter.dev/)
- [iOS 开发文档](https://developer.apple.com/documentation/)
- [CocoaPods 文档](https://guides.cocoapods.org/)
- [ChiBank 后端部署文档](../docs/zh-CN/部署文档.md)

---

## 💡 开发技巧

### 热重载

在应用运行时：
- 按 `r` 键：热重载
- 按 `R` 键：完全重启
- 按 `q` 键：退出

### 调试模式

```bash
# 以调试模式运行
flutter run --debug

# 查看日志
flutter logs
```

### 性能分析

```bash
# 以性能分析模式运行
flutter run --profile
```

---

## 🆘 获取帮助

- 查看 Flutter doctor: `flutter doctor -v`
- 查看详细日志: `flutter run -v`
- 访问项目主页: [https://github.com/HORIZION-TECHNOLOGIE-LTD/.github](https://github.com/HORIZION-TECHNOLOGIE-LTD/.github)

---

**祝你使用愉快！🎉**
