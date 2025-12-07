/**
 * ChiBank v5.0.0 主题管理系统
 * 支持明亮、黑暗、豪华三套主题
 * 实时切换 + 本地存储
 */

class ChiBankThemeManager {
    constructor() {
        // 主题配置
        this.themes = {
            light: {
                name: '明亮模式',
                icon: 'bi-sun-fill',
                description: '清新明亮，适合白天使用',
                colors: {
                    // 主色调
                    primary: '#0a1929',
                    primaryLight: '#1a2942',
                    primaryDark: '#050c14',
                    
                    // 辅助色
                    secondary: '#ffd700',
                    secondaryLight: '#ffe54c',
                    secondaryDark: '#ccac00',
                    
                    // 背景色
                    background: '#ffffff',
                    backgroundAlt: '#f9fafb',
                    surface: '#ffffff',
                    surfaceAlt: '#f3f4f6',
                    
                    // 文字色
                    textPrimary: '#111827',
                    textSecondary: '#374151',
                    textMuted: '#6b7280',
                    textDisabled: '#9ca3af',
                    
                    // 边框
                    border: '#e5e7eb',
                    borderLight: '#f3f4f6',
                    
                    // 语义色
                    success: '#17c653',
                    successLight: '#c9f7f5',
                    danger: '#f1416c',
                    dangerLight: '#fff5f8',
                    warning: '#ffc700',
                    warningLight: '#fff8dd',
                    info: '#7239ea',
                    infoLight: '#f1eeff',
                    
                    // 阴影
                    shadowColor: 'rgba(0, 0, 0, 0.08)',
                    shadowColorDark: 'rgba(0, 0, 0, 0.15)',
                    
                    // 卡片
                    cardBg: '#ffffff',
                    cardBorder: '#e5e7eb'
                }
            },
            
            dark: {
                name: '黑暗模式',
                icon: 'bi-moon-fill',
                description: '护眼舒适，适合夜间使用',
                colors: {
                    // 主色调
                    primary: '#3b82f6',
                    primaryLight: '#60a5fa',
                    primaryDark: '#2563eb',
                    
                    // 辅助色
                    secondary: '#fbbf24',
                    secondaryLight: '#fcd34d',
                    secondaryDark: '#f59e0b',
                    
                    // 背景色
                    background: '#0f172a',
                    backgroundAlt: '#1e293b',
                    surface: '#1e293b',
                    surfaceAlt: '#334155',
                    
                    // 文字色
                    textPrimary: '#f1f5f9',
                    textSecondary: '#e2e8f0',
                    textMuted: '#94a3b8',
                    textDisabled: '#64748b',
                    
                    // 边框
                    border: '#334155',
                    borderLight: '#475569',
                    
                    // 语义色
                    success: '#22c55e',
                    successLight: '#166534',
                    danger: '#ef4444',
                    dangerLight: '#7f1d1d',
                    warning: '#f59e0b',
                    warningLight: '#78350f',
                    info: '#8b5cf6',
                    infoLight: '#4c1d95',
                    
                    // 阴影
                    shadowColor: 'rgba(0, 0, 0, 0.3)',
                    shadowColorDark: 'rgba(0, 0, 0, 0.5)',
                    
                    // 卡片
                    cardBg: '#1e293b',
                    cardBorder: '#334155'
                }
            },
            
            luxury: {
                name: '豪华模式',
                icon: 'bi-gem',
                description: '奢华尊贵，VIP 专享体验',
                colors: {
                    // 主色调（金色渐变）
                    primary: '#ffd700',
                    primaryLight: '#ffed4e',
                    primaryDark: '#ccac00',
                    primaryGradient: 'linear-gradient(135deg, #ffd700 0%, #ffed4e 100%)',
                    
                    // 辅助色（紫色渐变）
                    secondary: '#9333ea',
                    secondaryLight: '#c084fc',
                    secondaryDark: '#7e22ce',
                    secondaryGradient: 'linear-gradient(135deg, #9333ea 0%, #c084fc 100%)',
                    
                    // 背景色
                    background: '#1a1a2e',
                    backgroundAlt: '#16213e',
                    surface: '#16213e',
                    surfaceAlt: '#2d3561',
                    
                    // 文字色
                    textPrimary: '#f5f5dc',
                    textSecondary: '#fff8dc',
                    textMuted: '#d4c5a9',
                    textDisabled: '#b8a588',
                    
                    // 边框
                    border: '#2d3561',
                    borderLight: '#3d4574',
                    
                    // 语义色
                    success: '#10b981',
                    successLight: '#064e3b',
                    danger: '#f43f5e',
                    dangerLight: '#881337',
                    warning: '#f59e0b',
                    warningLight: '#78350f',
                    info: '#8b5cf6',
                    infoLight: '#4c1d95',
                    
                    // 阴影（带金色光晕）
                    shadowColor: 'rgba(255, 215, 0, 0.15)',
                    shadowColorDark: 'rgba(147, 51, 234, 0.25)',
                    
                    // 卡片
                    cardBg: '#16213e',
                    cardBorder: '#2d3561',
                    
                    // 特效
                    glowGold: '0 0 20px rgba(255, 215, 0, 0.5)',
                    glowPurple: '0 0 20px rgba(147, 51, 234, 0.5)'
                }
            }
        };
        
        // 动画配置
        this.animationConfig = {
            duration: 300,
            easing: 'cubic-bezier(0.4, 0, 0.2, 1)'
        };
        
        // 初始化
        this.currentTheme = this.loadTheme();
        this.init();
    }
    
    /**
     * 初始化主题系统
     */
    init() {
        // 应用保存的主题
        this.applyTheme(this.currentTheme, false);
        
        // 监听系统主题变化
        this.watchSystemTheme();
        
        // 添加主题变化监听
        this.setupThemeListeners();
        
        console.log(`%c🎨 ChiBank 主题系统已初始化`, 'color: #ffd700; font-weight: bold;');
        console.log(`当前主题: ${this.themes[this.currentTheme].name}`);
    }
    
    /**
     * 切换主题
     * @param {string} themeName - 主题名称
     * @param {boolean} animate - 是否显示动画
     */
    switchTheme(themeName, animate = true) {
        if (!this.themes[themeName]) {
            console.error(`主题 "${themeName}" 不存在`);
            return;
        }
        
        // 如果是当前主题，不执行切换
        if (themeName === this.currentTheme) {
            return;
        }
        
        // 显示加载动画
        if (animate) {
            this.showThemeTransition();
        }
        
        // 应用新主题
        this.applyTheme(themeName, animate);
        
        // 保存主题设置
        this.currentTheme = themeName;
        this.saveTheme(themeName);
        
        // 触发主题切换事件
        this.notifyThemeChange(themeName);
        
        // 显示成功提示
        if (animate) {
            this.showToast(`已切换到${this.themes[themeName].name}`);
        }
    }
    
    /**
     * 应用主题
     * @param {string} themeName - 主题名称
     * @param {boolean} animate - 是否显示动画
     */
    applyTheme(themeName, animate = true) {
        const theme = this.themes[themeName];
        const root = document.documentElement;
        
        // 添加过渡动画
        if (animate) {
            root.style.transition = `all ${this.animationConfig.duration}ms ${this.animationConfig.easing}`;
        }
        
        // 设置主题属性
        root.setAttribute('data-theme', themeName);
        root.setAttribute('data-theme-name', theme.name);
        
        // 应用主题颜色变量
        Object.entries(theme.colors).forEach(([key, value]) => {
            const varName = `--theme-${this.camelToKebab(key)}`;
            root.style.setProperty(varName, value);
        });
        
        // 应用动画配置
        root.style.setProperty('--theme-transition-duration', `${this.animationConfig.duration}ms`);
        root.style.setProperty('--theme-transition-easing', this.animationConfig.easing);
        
        // 更新 meta 主题颜色（移动端）
        this.updateMetaThemeColor(theme.colors.primary);
        
        // 移除过渡动画
        if (animate) {
            setTimeout(() => {
                root.style.transition = '';
            }, this.animationConfig.duration);
        }
    }
    
    /**
     * 显示主题切换动画
     */
    showThemeTransition() {
        const overlay = document.createElement('div');
        overlay.className = 'theme-transition-overlay';
        overlay.innerHTML = `
            <div class="theme-transition-content">
                <div class="theme-transition-spinner"></div>
                <div class="theme-transition-text">切换主题中...</div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        
        // 添加动画样式
        setTimeout(() => {
            overlay.classList.add('show');
        }, 10);
        
        // 移除动画
        setTimeout(() => {
            overlay.classList.remove('show');
            setTimeout(() => {
                overlay.remove();
            }, 300);
        }, this.animationConfig.duration);
    }
    
    /**
     * 加载保存的主题
     * @returns {string} 主题名称
     */
    loadTheme() {
        try {
            const saved = localStorage.getItem('chibank_theme');
            if (saved && this.themes[saved]) {
                return saved;
            }
        } catch (e) {
            console.warn('无法从 localStorage 加载主题:', e);
        }
        
        // 默认返回明亮主题
        return 'light';
    }
    
    /**
     * 保存主题设置
     * @param {string} themeName - 主题名称
     */
    saveTheme(themeName) {
        try {
            localStorage.setItem('chibank_theme', themeName);
            
            // 同步到服务器（如果用户已登录）
            if (window.isUserLoggedIn) {
                this.syncThemeToServer(themeName);
            }
        } catch (e) {
            console.warn('无法保存主题到 localStorage:', e);
        }
    }
    
    /**
     * 同步主题到服务器
     * @param {string} themeName - 主题名称
     */
    async syncThemeToServer(themeName) {
        try {
            await fetch('/api/user/theme', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ theme: themeName })
            });
        } catch (e) {
            console.warn('无法同步主题到服务器:', e);
        }
    }
    
    /**
     * 监听系统主题变化
     */
    watchSystemTheme() {
        if (window.matchMedia) {
            const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
            
            darkModeQuery.addEventListener('change', (e) => {
                // 如果用户设置了自动跟随系统
                if (this.isAutoTheme()) {
                    const newTheme = e.matches ? 'dark' : 'light';
                    this.switchTheme(newTheme, true);
                }
            });
        }
    }
    
    /**
     * 检查是否自动跟随系统主题
     * @returns {boolean}
     */
    isAutoTheme() {
        try {
            return localStorage.getItem('chibank_theme_auto') === 'true';
        } catch (e) {
            return false;
        }
    }
    
    /**
     * 设置自动跟随系统主题
     * @param {boolean} auto - 是否自动
     */
    setAutoTheme(auto) {
        try {
            localStorage.setItem('chibank_theme_auto', auto.toString());
            
            if (auto) {
                const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                this.switchTheme(isDark ? 'dark' : 'light');
            }
        } catch (e) {
            console.warn('无法设置自动主题:', e);
        }
    }
    
    /**
     * 设置主题监听器
     */
    setupThemeListeners() {
        // 监听主题切换事件
        window.addEventListener('themeChanged', (e) => {
            console.log('主题已切换:', e.detail.theme);
        });
    }
    
    /**
     * 触发主题切换事件
     * @param {string} themeName - 主题名称
     */
    notifyThemeChange(themeName) {
        const event = new CustomEvent('themeChanged', {
            detail: {
                theme: themeName,
                themeData: this.themes[themeName]
            }
        });
        
        window.dispatchEvent(event);
    }
    
    /**
     * 更新 meta 主题颜色
     * @param {string} color - 颜色值
     */
    updateMetaThemeColor(color) {
        let metaTheme = document.querySelector('meta[name="theme-color"]');
        
        if (!metaTheme) {
            metaTheme = document.createElement('meta');
            metaTheme.name = 'theme-color';
            document.head.appendChild(metaTheme);
        }
        
        metaTheme.content = color;
    }
    
    /**
     * 显示 Toast 提示
     * @param {string} message - 提示消息
     */
    showToast(message) {
        if (typeof toastr !== 'undefined') {
            toastr.success(message);
        } else {
            console.log(message);
        }
    }
    
    /**
     * 驼峰转短横线
     * @param {string} str - 驼峰字符串
     * @returns {string} 短横线字符串
     */
    camelToKebab(str) {
        return str.replace(/([a-z0-9])([A-Z])/g, '$1-$2').toLowerCase();
    }
    
    /**
     * 获取当前主题
     * @returns {Object} 主题对象
     */
    getCurrentTheme() {
        return {
            name: this.currentTheme,
            data: this.themes[this.currentTheme]
        };
    }
    
    /**
     * 获取所有主题
     * @returns {Object} 所有主题
     */
    getAllThemes() {
        return this.themes;
    }
}

// 初始化主题管理器
const themeManager = new ChiBankThemeManager();

// 暴露到全局
window.themeManager = themeManager;

// 便捷方法
window.switchTheme = (themeName) => themeManager.switchTheme(themeName);

console.log('%c🎨 ChiBank 主题系统已加载', 'color: #ffd700; font-size: 14px; font-weight: bold;');
