# iOS Installation Guide - ChiBank QRPay User App

## 📱 System Requirements

- macOS 10.15 (Catalina) or later
- Xcode 15.0 or later
- CocoaPods 1.11.0 or later
- Flutter SDK 3.24.0
- iOS device or simulator with iOS 13.0 or later

---

## 🚀 Quick Installation (5 Steps)

### Step 1: Install Flutter SDK

```bash
# Download Flutter SDK
cd ~/development
git clone https://github.com/flutter/flutter.git -b stable

# Add Flutter to PATH
export PATH="$PATH:`pwd`/flutter/bin"

# Verify installation
flutter doctor
```

**Tip:** Add `export PATH="$PATH:~/development/flutter/bin"` to your `~/.zshrc` or `~/.bash_profile` to make it permanent.

---

### Step 2: Install Xcode and CocoaPods

1. **Install Xcode**
   - Install Xcode from the App Store
   - Open Xcode and accept the license agreement
   - Install command line tools:
     ```bash
     sudo xcode-select --switch /Applications/Xcode.app/Contents/Developer
     sudo xcodebuild -runFirstLaunch
     ```

2. **Install CocoaPods**
   ```bash
   sudo gem install cocoapods
   pod setup
   ```

---

### Step 3: Clone the Project

```bash
cd ~/Projects
git clone https://github.com/hhongli1979-coder/chibank999.git chibank
cd chibank/qrpay-user-app/qrpay-user-app-new-v5.0.0
```

---

### Step 4: Install Dependencies

```bash
# Install Flutter dependencies
flutter pub get

# Install iOS dependencies
cd ios
pod install
cd ..
```

---

### Step 5: Run the Application

#### Run on Simulator

```bash
# List available simulators
flutter emulators

# Launch simulator
flutter emulators --launch apple_ios_simulator

# Run the app
flutter run
```

#### Run on Physical Device

1. Connect your iPhone to Mac via USB
2. Trust the developer on iPhone (Settings > General > Device Management)
3. Run the command:
   ```bash
   flutter run
   ```

---

## 🔧 Detailed Configuration

### Configure API Endpoint

Edit the `lib/backend/utils/custom_api_url.dart` file to set your backend API address:

```dart
class CustomApiUrl {
  static const String mainUrl = "https://your-api-domain.com";
}
```

### Configure App Information

In `ios/Runner/Info.plist`, you can configure:

- App display name
- Permission descriptions (camera, photos, location, etc.)
- URL Schemes
- Other app settings

### Configure Firebase (if using)

1. Create a project in [Firebase Console](https://console.firebase.google.com/)
2. Add iOS app with Bundle ID: `net.appdevs.qrpayuser`
3. Download `GoogleService-Info.plist`
4. Place the file in `ios/Runner/` directory
5. Add file reference in Xcode

---

## 📦 Build Release Version

### Build IPA File

```bash
# Clean previous builds
flutter clean

# Get dependencies
flutter pub get

# Build iOS release version
flutter build ios --release
```

### Package and Upload with Xcode

1. Open project in Xcode:
   ```bash
   open ios/Runner.xcworkspace
   ```

2. Select Runner target

3. Configure signing and certificates:
   - Select your development team
   - Configure Provisioning Profile

4. Select Product > Archive

5. Upload to App Store Connect

---

## 🎨 Customization

### Change App Name

Use Flutter package to modify:

```bash
flutter pub run rename_app:main all="Your App Name"
```

### Change Bundle ID

Use Flutter package to modify:

```bash
flutter pub run change_app_package_name:main com.yourcompany.appname
```

### Change App Icon

1. Prepare a 1024x1024 icon file
2. Place it at `assets/logo/app_launcher.png`
3. Run command:
   ```bash
   flutter pub run flutter_launcher_icons:main
   ```

---

## 🔍 Troubleshooting

### Issue 1: Pod install fails

```bash
# Clean CocoaPods cache
cd ios
rm -rf Pods Podfile.lock
pod cache clean --all
pod install --repo-update
```

### Issue 2: Cannot run on physical device

- Check if developer certificate is configured correctly
- Trust developer in iPhone settings
- Ensure Bundle ID matches the certificate

### Issue 3: Flutter doctor errors

```bash
# Accept Android licenses
flutter doctor --android-licenses

# Install missing components
flutter doctor -v
```

### Issue 4: Xcode build fails

```bash
# Clean Xcode build cache
cd ios
rm -rf ~/Library/Developer/Xcode/DerivedData
xcodebuild clean
cd ..

# Rebuild
flutter clean
flutter pub get
cd ios && pod install && cd ..
flutter build ios
```

### Issue 5: App signing issues

1. Open `ios/Runner.xcworkspace` in Xcode
2. Select Runner target
3. In Signing & Capabilities:
   - Check "Automatically manage signing"
   - Select your Team
   - Modify Bundle Identifier if needed

---

## 📱 Testing the App

### Unit Tests

```bash
flutter test
```

### Integration Tests

```bash
flutter drive --target=test_driver/app.dart
```

---

## 🔄 Update the App

```bash
# Pull latest code
git pull origin main

# Clean and reinstall dependencies
flutter clean
flutter pub get
cd ios && pod install && cd ..

# Run the app
flutter run
```

---

## 📚 Related Documentation

- [Flutter Official Documentation](https://docs.flutter.dev/)
- [iOS Development Documentation](https://developer.apple.com/documentation/)
- [CocoaPods Documentation](https://guides.cocoapods.org/)
- [ChiBank Backend Deployment Documentation](../zh-CN/部署文档.md)

---

## 💡 Development Tips

### Hot Reload

While the app is running:
- Press `r`: Hot reload
- Press `R`: Full restart
- Press `q`: Quit

### Debug Mode

```bash
# Run in debug mode
flutter run --debug

# View logs
flutter logs
```

### Performance Profiling

```bash
# Run in profile mode
flutter run --profile
```

---

## 🆘 Get Help

- Check Flutter doctor: `flutter doctor -v`
- View detailed logs: `flutter run -v`
- Visit project homepage: [https://github.com/hhongli1979-coder/chibank999](https://github.com/hhongli1979-coder/chibank999)

---

**Happy coding! 🎉**
