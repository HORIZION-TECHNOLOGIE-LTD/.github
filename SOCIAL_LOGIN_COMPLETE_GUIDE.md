# 🚀 ChiBank v5.0.0 - 社交登录系统完整指南

## 📋 目录

1. [系统概述](#系统概述)
2. [支持的平台](#支持的平台)
3. [安装配置](#安装配置)
4. [使用教程](#使用教程)
5. [API文档](#api文档)
6. [定制开发](#定制开发)

---

## 🌟 系统概述

ChiBank v5.0.0 集成了完整的**第三方社交登录系统**，支持用户通过以下平台一键登录/注册：

- ✅ 支付宝 (Alipay)
- ✅ 微信 (WeChat/WeCom)
- ✅ QQ
- ✅ 微博 (Weibo)
- ✅ Google
- ✅ Facebook
- ✅ GitHub
- ✅ Twitter/X
- ✅ LinkedIn
- ✅ Apple ID

### 核心特性

- 🎯 **一键登录/注册** - 无需填写表单
- 🔒 **OAuth 2.0 安全认证** - 符合国际标准
- 🌐 **自动账户绑定** - 邮箱/手机号智能匹配
- 📱 **移动端优化** - 完美适配所有设备
- 🎨 **品牌化设计** - 符合各平台VI规范
- ⚡ **极速响应** - 毫秒级登录体验

---

## 🎯 支持的平台

### 🇨🇳 中国平台

#### 1. 支付宝 (Alipay)
```php
// 配置文件：config/services.php
'alipay' => [
    'app_id' => env('ALIPAY_APP_ID'),
    'public_key' => env('ALIPAY_PUBLIC_KEY'),
    'private_key' => env('ALIPAY_PRIVATE_KEY'),
    'redirect' => env('ALIPAY_REDIRECT_URL'),
],
```

**获取配置**：
1. 访问 [支付宝开放平台](https://open.alipay.com/)
2. 创建应用 → 配置OAuth 2.0
3. 获取 APP_ID 和密钥

#### 2. 微信 (WeChat)
```php
'wechat' => [
    'app_id' => env('WECHAT_APP_ID'),
    'app_secret' => env('WECHAT_APP_SECRET'),
    'redirect' => env('WECHAT_REDIRECT_URL'),
],
```

**获取配置**：
1. 访问 [微信开放平台](https://open.weixin.qq.com/)
2. 注册网站应用
3. 获取 AppID 和 AppSecret

#### 3. QQ
```php
'qq' => [
    'app_id' => env('QQ_APP_ID'),
    'app_key' => env('QQ_APP_KEY'),
    'redirect' => env('QQ_REDIRECT_URL'),
],
```

#### 4. 微博 (Weibo)
```php
'weibo' => [
    'client_id' => env('WEIBO_CLIENT_ID'),
    'client_secret' => env('WEIBO_CLIENT_SECRET'),
    'redirect' => env('WEIBO_REDIRECT_URL'),
],
```

### 🌍 国际平台

#### 5. Google
```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URL'),
],
```

**获取配置**：
1. 访问 [Google Cloud Console](https://console.cloud.google.com/)
2. 创建OAuth 2.0凭据
3. 配置授权回调URL

#### 6. Facebook
```php
'facebook' => [
    'client_id' => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect' => env('FACEBOOK_REDIRECT_URL'),
],
```

#### 7. GitHub
```php
'github' => [
    'client_id' => env('GITHUB_CLIENT_ID'),
    'client_secret' => env('GITHUB_CLIENT_SECRET'),
    'redirect' => env('GITHUB_REDIRECT_URL'),
],
```

---

## ⚙️ 安装配置

### 步骤 1：安装 Laravel Socialite

```bash
composer require laravel/socialite
composer require socialiteproviders/manager
```

### 步骤 2：配置环境变量

编辑 `.env` 文件：

```env
# 支付宝
ALIPAY_APP_ID=your_app_id
ALIPAY_PUBLIC_KEY=your_public_key
ALIPAY_PRIVATE_KEY=your_private_key
ALIPAY_REDIRECT_URL=https://chibank.eu/callback/alipay

# 微信
WECHAT_APP_ID=your_app_id
WECHAT_APP_SECRET=your_app_secret
WECHAT_REDIRECT_URL=https://chibank.eu/callback/wechat

# Google
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URL=https://chibank.eu/callback/google

# Facebook
FACEBOOK_CLIENT_ID=your_client_id
FACEBOOK_CLIENT_SECRET=your_client_secret
FACEBOOK_REDIRECT_URL=https://chibank.eu/callback/facebook

# GitHub
GITHUB_CLIENT_ID=your_client_id
GITHUB_CLIENT_SECRET=your_client_secret
GITHUB_REDIRECT_URL=https://chibank.eu/callback/github
```

### 步骤 3：注册服务提供者

在 `config/app.php` 中添加：

```php
'providers' => [
    // ...
    \SocialiteProviders\Manager\ServiceProvider::class,
],
```

### 步骤 4：配置事件监听器

在 `app/Providers/EventServiceProvider.php` 中：

```php
protected $listen = [
    \SocialiteProviders\Manager\SocialiteWasCalled::class => [
        'SocialiteProviders\\Alipay\\AlipayExtendSocialite@handle',
        'SocialiteProviders\\Weixin\\WeixinExtendSocialite@handle',
        'SocialiteProviders\\QQ\\QQExtendSocialite@handle',
        'SocialiteProviders\\Weibo\\WeiboExtendSocialite@handle',
    ],
];
```

### 步骤 5：创建数据库迁移

```bash
php artisan make:migration add_social_login_to_users_table
```

```php
// database/migrations/xxxx_add_social_login_to_users_table.php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('provider')->nullable()->after('email');
        $table->string('provider_id')->nullable()->after('provider');
        $table->string('avatar')->nullable()->after('provider_id');
    });
}
```

运行迁移：
```bash
php artisan migrate
```

---

## 🔧 使用教程

### 前端集成

在登录页面中使用社交登录组件：

```blade
@extends('user.layouts.user_auth')

@section('content')
<div class="login-container">
    <!-- 传统登录表单 -->
    <form>
        <!-- ... -->
    </form>
    
    <!-- 社交登录组件 -->
    @include('partials.social-login')
</div>
@endsection
```

### 后端路由配置

```php
// routes/web.php
Route::prefix('auth')->group(function () {
    // 重定向到社交平台
    Route::get('{provider}', [SocialLoginController::class, 'redirect'])
        ->name('social.login');
    
    // 回调处理
    Route::get('{provider}/callback', [SocialLoginController::class, 'callback'])
        ->name('social.callback');
});
```

### 控制器实现

创建 `App\Http\Controllers\SocialLoginController.php`：

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SocialLoginController extends Controller
{
    /**
     * 重定向到社交平台授权页面
     */
    public function redirect($provider)
    {
        $this->validateProvider($provider);
        
        return Socialite::driver($provider)->redirect();
    }
    
    /**
     * 处理社交平台回调
     */
    public function callback($provider)
    {
        $this->validateProvider($provider);
        
        try {
            $socialUser = Socialite::driver($provider)->user();
            
            // 查找或创建用户
            $user = $this->findOrCreateUser($socialUser, $provider);
            
            // 登录用户
            Auth::login($user, true);
            
            return redirect()->route('user.dashboard')
                ->with('success', __('Login successful!'));
                
        } catch (\Exception $e) {
            return redirect()->route('user.login')
                ->with('error', __('Login failed. Please try again.'));
        }
    }
    
    /**
     * 验证社交平台
     */
    protected function validateProvider($provider)
    {
        $allowedProviders = [
            'alipay', 'wechat', 'qq', 'weibo',
            'google', 'facebook', 'github', 'twitter', 'linkedin', 'apple'
        ];
        
        if (!in_array($provider, $allowedProviders)) {
            abort(404);
        }
    }
    
    /**
     * 查找或创建用户
     */
    protected function findOrCreateUser($socialUser, $provider)
    {
        // 1. 尝试通过 provider_id 查找
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();
            
        if ($user) {
            return $user;
        }
        
        // 2. 尝试通过 email 查找并绑定
        if ($socialUser->getEmail()) {
            $user = User::where('email', $socialUser->getEmail())->first();
            
            if ($user) {
                $user->update([
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                ]);
                return $user;
            }
        }
        
        // 3. 创建新用户
        return User::create([
            'username' => $this->generateUsername($socialUser),
            'email' => $socialUser->getEmail() ?? $this->generateEmail($socialUser),
            'firstname' => $this->getFirstName($socialUser),
            'lastname' => $this->getLastName($socialUser),
            'password' => bcrypt(Str::random(24)),
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'email_verified' => true,
            'status' => 1,
        ]);
    }
    
    /**
     * 生成用户名
     */
    protected function generateUsername($socialUser)
    {
        $username = $socialUser->getNickname() ?? 
                   $socialUser->getName() ?? 
                   'user_' . time();
        
        // 确保用户名唯一
        $originalUsername = $username;
        $counter = 1;
        
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }
        
        return $username;
    }
    
    /**
     * 生成邮箱
     */
    protected function generateEmail($socialUser)
    {
        return $socialUser->getId() . '@social.chibank.eu';
    }
    
    /**
     * 获取名字
     */
    protected function getFirstName($socialUser)
    {
        $name = $socialUser->getName();
        $parts = explode(' ', $name);
        return $parts[0] ?? $name;
    }
    
    /**
     * 获取姓氏
     */
    protected function getLastName($socialUser)
    {
        $name = $socialUser->getName();
        $parts = explode(' ', $name);
        return count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
    }
}
```

---

## 📱 前端组件

### social-login.blade.php

```blade
{{-- 社交登录组件 --}}
<div class="social-login-wrapper">
    <div class="social-login-title">
        {{ __("Or continue with") }}
    </div>
    
    <div class="social-login-buttons">
        {{-- 支付宝 --}}
        @if(config('services.alipay.app_id'))
        <a href="{{ route('social.login', 'alipay') }}" 
           class="social-btn alipay" 
           data-tooltip="{{ __('Login with Alipay') }}">
            <i class="bi bi-alipay"></i>
        </a>
        @endif
        
        {{-- 微信 --}}
        @if(config('services.wechat.app_id'))
        <a href="{{ route('social.login', 'wechat') }}" 
           class="social-btn wechat" 
           data-tooltip="{{ __('Login with WeChat') }}">
            <i class="bi bi-wechat"></i>
        </a>
        @endif
        
        {{-- Google --}}
        @if(config('services.google.client_id'))
        <a href="{{ route('social.login', 'google') }}" 
           class="social-btn google" 
           data-tooltip="{{ __('Login with Google') }}">
            <i class="bi bi-google"></i>
        </a>
        @endif
        
        {{-- Facebook --}}
        @if(config('services.facebook.client_id'))
        <a href="{{ route('social.login', 'facebook') }}" 
           class="social-btn facebook" 
           data-tooltip="{{ __('Login with Facebook') }}">
            <i class="bi bi-facebook"></i>
        </a>
        @endif
        
        {{-- GitHub --}}
        @if(config('services.github.client_id'))
        <a href="{{ route('social.login', 'github') }}" 
           class="social-btn github" 
           data-tooltip="{{ __('Login with GitHub') }}">
            <i class="bi bi-github"></i>
        </a>
        @endif
    </div>
</div>
```

---

## 🎨 CSS 样式

完整的 CSS 已包含在 `public/backend/css/social-login.css` 中，主要特性：

- ✅ 响应式网格布局
- ✅ 平滑过渡动画
- ✅ 品牌色渐变背景
- ✅ 悬停效果
- ✅ 暗色模式支持
- ✅ 加载状态
- ✅ 错误提示

---

## 🔒 安全考虑

### 1. CSRF 保护

所有社交登录路由自动受 Laravel 的 CSRF 保护。

### 2. 状态验证

```php
// 在redirect方法中
return Socialite::driver($provider)
    ->stateless()
    ->with(['state' => csrf_token()])
    ->redirect();
```

### 3. 邮箱验证

```php
// 社交登录用户自动标记为已验证
'email_verified' => true,
```

### 4. 敏感信息保护

```php
// 隐藏社交登录字段
protected $hidden = [
    'password',
    'provider',
    'provider_id',
];
```

---

## 📊 数据库架构

### Users 表扩展

```sql
ALTER TABLE `users` 
ADD COLUMN `provider` VARCHAR(50) NULL AFTER `email`,
ADD COLUMN `provider_id` VARCHAR(255) NULL AFTER `provider`,
ADD COLUMN `avatar` VARCHAR(255) NULL AFTER `provider_id`,
ADD INDEX `idx_provider` (`provider`, `provider_id`);
```

---

## 🌐 多语言支持

在 `resources/lang/` 目录中添加翻译：

```php
// zh_CN/auth.php
'social_login' => [
    'alipay' => '支付宝登录',
    'wechat' => '微信登录',
    'google' => 'Google登录',
    'facebook' => 'Facebook登录',
    'github' => 'GitHub登录',
    'or_continue_with' => '或使用以下方式继续',
    'login_success' => '登录成功！',
    'login_failed' => '登录失败，请重试。',
],
```

---

## 🧪 测试

### 单元测试

```php
// tests/Feature/SocialLoginTest.php
public function test_alipay_redirect()
{
    $response = $this->get(route('social.login', 'alipay'));
    $response->assertRedirect();
}

public function test_social_callback_creates_user()
{
    // Mock Socialite
    $abstractUser = Mockery::mock('Laravel\Socialite\Two\User');
    $abstractUser->shouldReceive('getId')->andReturn('123456');
    $abstractUser->shouldReceive('getEmail')->andReturn('test@example.com');
    $abstractUser->shouldReceive('getName')->andReturn('Test User');
    
    // Test callback
    $response = $this->get(route('social.callback', 'google'));
    $this->assertDatabaseHas('users', [
        'provider' => 'google',
        'provider_id' => '123456',
    ]);
}
```

---

## 📈 性能优化

### 1. 缓存配置

```php
// 缓存社交平台配置
Cache::remember('social_providers', 3600, function () {
    return config('services');
});
```

### 2. 异步处理

```php
// 异步获取用户头像
dispatch(new DownloadAvatarJob($user, $socialUser->getAvatar()));
```

### 3. CDN 加速

使用 CDN 加载社交平台的 SDK 和图标。

---

## 🔍 故障排查

### 常见问题

1. **回调URL不匹配**
   ```
   解决：确保.env中的REDIRECT_URL与平台配置一致
   ```

2. **CSRF Token验证失败**
   ```
   解决：确保使用stateless()方法
   ```

3. **用户信息获取失败**
   ```
   解决：检查scope权限配置
   ```

### 调试模式

```php
// 启用详细错误日志
'log' => [
    'level' => 'debug',
],
```

---

## 📚 参考资源

- [Laravel Socialite 官方文档](https://laravel.com/docs/socialite)
- [Socialite Providers](https://socialiteproviders.com/)
- [支付宝开放平台](https://open.alipay.com/)
- [微信开放平台](https://open.weixin.qq.com/)
- [Google Cloud Console](https://console.cloud.google.com/)

---

## 🎯 下一步

- [ ] 添加更多社交平台支持
- [ ] 实现账户解绑功能
- [ ] 添加社交分享功能
- [ ] 完善错误处理机制
- [ ] 优化用户体验

---

**🚀 ChiBank v5.0.0 社交登录系统 - 让登录更简单！**

**地平线AI智能科技** | chibank.eu | © 2024 ⚡
