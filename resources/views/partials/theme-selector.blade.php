{{-- ChiBank v5.0.0 主题选择器组件 --}}
{{-- 适用于：管理端、商户端、用户端 --}}

<div class="chibank-theme-selector">
    <!-- 主题切换按钮 -->
    <button class="theme-toggle-btn" id="chiBankThemeToggle" title="切换主题" type="button">
        <i class="bi bi-palette-fill"></i>
        <span class="theme-toggle-text d-none d-md-inline ms-2">主题</span>
    </button>
    
    <!-- 主题选择下拉菜单 -->
    <div class="theme-dropdown" id="chiBankThemeDropdown">
        <!-- 头部 -->
        <div class="theme-dropdown-header">
            <div class="theme-dropdown-title">
                <i class="bi bi-palette me-2"></i>
                <h6>选择主题风格</h6>
            </div>
            <button class="theme-close-btn" onclick="chiBankCloseThemeDropdown()" type="button">
                <i class="bi bi-x"></i>
            </button>
        </div>
        
        <!-- 主题选项 -->
        <div class="theme-options">
            <!-- 明亮模式 -->
            <button class="theme-option" data-theme="light" onclick="chiBankSwitchTheme('light')" type="button">
                <div class="theme-preview light-preview">
                    <div class="preview-sidebar"></div>
                    <div class="preview-main">
                        <div class="preview-header"></div>
                        <div class="preview-content">
                            <div class="preview-card"></div>
                            <div class="preview-card"></div>
                        </div>
                    </div>
                </div>
                <div class="theme-info">
                    <div class="theme-icon light-icon">
                        <i class="bi bi-sun-fill"></i>
                    </div>
                    <div class="theme-details">
                        <h6 class="theme-name">明亮模式</h6>
                        <p class="theme-description">清新明亮，适合白天使用</p>
                        <div class="theme-colors">
                            <span class="color-dot" style="background: #0a1929;"></span>
                            <span class="color-dot" style="background: #ffd700;"></span>
                            <span class="color-dot" style="background: #ffffff;"></span>
                        </div>
                    </div>
                    <div class="theme-check">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </button>
            
            <!-- 黑暗模式 -->
            <button class="theme-option" data-theme="dark" onclick="chiBankSwitchTheme('dark')" type="button">
                <div class="theme-preview dark-preview">
                    <div class="preview-sidebar"></div>
                    <div class="preview-main">
                        <div class="preview-header"></div>
                        <div class="preview-content">
                            <div class="preview-card"></div>
                            <div class="preview-card"></div>
                        </div>
                    </div>
                </div>
                <div class="theme-info">
                    <div class="theme-icon dark-icon">
                        <i class="bi bi-moon-fill"></i>
                    </div>
                    <div class="theme-details">
                        <h6 class="theme-name">黑暗模式</h6>
                        <p class="theme-description">护眼舒适，适合夜间使用</p>
                        <div class="theme-colors">
                            <span class="color-dot" style="background: #3b82f6;"></span>
                            <span class="color-dot" style="background: #fbbf24;"></span>
                            <span class="color-dot" style="background: #0f172a;"></span>
                        </div>
                    </div>
                    <div class="theme-check">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </button>
            
            <!-- 豪华模式 -->
            <button class="theme-option" data-theme="luxury" onclick="chiBankSwitchTheme('luxury')" type="button">
                <div class="theme-preview luxury-preview">
                    <div class="preview-sidebar"></div>
                    <div class="preview-main">
                        <div class="preview-header"></div>
                        <div class="preview-content">
                            <div class="preview-card"></div>
                            <div class="preview-card"></div>
                        </div>
                    </div>
                </div>
                <div class="theme-info">
                    <div class="theme-icon luxury-icon">
                        <i class="bi bi-gem"></i>
                    </div>
                    <div class="theme-details">
                        <h6 class="theme-name">豪华模式</h6>
                        <p class="theme-description">奢华尊贵，VIP 专享体验</p>
                        <div class="theme-colors">
                            <span class="color-dot" style="background: linear-gradient(135deg, #ffd700, #ffed4e);"></span>
                            <span class="color-dot" style="background: linear-gradient(135deg, #9333ea, #c084fc);"></span>
                            <span class="color-dot" style="background: #1a1a2e;"></span>
                        </div>
                    </div>
                    <div class="theme-check">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </button>
        </div>
        
        <!-- 底部选项 -->
        <div class="theme-dropdown-footer">
            <label class="theme-auto-switch">
                <input type="checkbox" id="chiBankThemeAuto" onchange="chiBankToggleAutoTheme(this.checked)">
                <span class="theme-auto-label">
                    <i class="bi bi-arrow-repeat me-2"></i>
                    自动跟随系统
                </span>
            </label>
        </div>
    </div>
</div>

<style>
/* ============================================
   主题选择器样式
============================================ */

.chibank-theme-selector {
    position: relative;
    z-index: 1000;
}

/* 切换按钮 */
.theme-toggle-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.65rem 1rem;
    border: none;
    background: var(--theme-surface);
    border-radius: var(--theme-border-radius);
    color: var(--theme-text-primary);
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: var(--theme-shadow-sm);
}

.theme-toggle-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--theme-shadow);
    background: var(--theme-surface-alt);
}

.theme-toggle-btn i {
    font-size: 1.15rem;
}

/* 下拉菜单 */
.theme-dropdown {
    position: absolute;
    top: calc(100% + 0.75rem);
    right: 0;
    width: 420px;
    max-width: 95vw;
    background: var(--theme-surface);
    border: 1px solid var(--theme-border);
    border-radius: var(--theme-border-radius-lg);
    box-shadow: var(--theme-shadow-lg);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1001;
}

.theme-dropdown.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

/* 下拉菜单头部 */
.theme-dropdown-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--theme-border);
}

.theme-dropdown-title {
    display: flex;
    align-items: center;
}

.theme-dropdown-title h6 {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--theme-text-primary);
}

.theme-close-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    color: var(--theme-text-muted);
    font-size: 1.35rem;
    cursor: pointer;
    border-radius: 0.35rem;
    transition: all 0.2s ease;
}

.theme-close-btn:hover {
    background: var(--theme-surface-alt);
    color: var(--theme-text-primary);
}

/* 主题选项容器 */
.theme-options {
    padding: 1.25rem;
    max-height: 500px;
    overflow-y: auto;
}

.theme-options::-webkit-scrollbar {
    width: 6px;
}

.theme-options::-webkit-scrollbar-track {
    background: var(--theme-surface-alt);
    border-radius: 3px;
}

.theme-options::-webkit-scrollbar-thumb {
    background: var(--theme-border);
    border-radius: 3px;
}

.theme-options::-webkit-scrollbar-thumb:hover {
    background: var(--theme-text-muted);
}

/* 主题选项按钮 */
.theme-option {
    width: 100%;
    padding: 1.25rem;
    margin-bottom: 1rem;
    border: 2px solid var(--theme-border);
    background: var(--theme-surface);
    border-radius: var(--theme-border-radius-lg);
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: left;
}

.theme-option:hover {
    border-color: var(--theme-primary);
    transform: translateX(4px);
    box-shadow: var(--theme-shadow);
}

.theme-option.active {
    border-color: var(--theme-primary);
    background: var(--theme-surface-alt);
}

.theme-option:last-child {
    margin-bottom: 0;
}

/* 主题预览 */
.theme-preview {
    width: 100%;
    height: 80px;
    border-radius: var(--theme-border-radius);
    overflow: hidden;
    margin-bottom: 1rem;
    position: relative;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.preview-sidebar {
    position: absolute;
    left: 0;
    top: 0;
    width: 25%;
    height: 100%;
    background: #1a2942;
}

.preview-main {
    position: absolute;
    left: 25%;
    top: 0;
    width: 75%;
    height: 100%;
}

.preview-header {
    height: 15px;
    background: rgba(0, 0, 0, 0.05);
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

.preview-content {
    padding: 8px;
    height: calc(100% - 15px);
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.preview-card {
    flex: 1;
    background: rgba(255, 255, 255, 0.8);
    border-radius: 4px;
}

/* 明亮主题预览 */
.light-preview .preview-main {
    background: #ffffff;
}

.light-preview .preview-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
}

/* 黑暗主题预览 */
.dark-preview .preview-sidebar {
    background: #1e293b;
}

.dark-preview .preview-main {
    background: #0f172a;
}

.dark-preview .preview-header {
    background: #1e293b;
    border-color: #334155;
}

.dark-preview .preview-card {
    background: #1e293b;
    border: 1px solid #334155;
}

/* 豪华主题预览 */
.luxury-preview .preview-sidebar {
    background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
}

.luxury-preview .preview-main {
    background: #1a1a2e;
}

.luxury-preview .preview-header {
    background: linear-gradient(90deg, #ffd700 0%, #9333ea 100%);
    border: none;
}

.luxury-preview .preview-card {
    background: #16213e;
    border: 1px solid #2d3561;
}

/* 主题信息 */
.theme-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.theme-icon {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--theme-border-radius);
    font-size: 1.35rem;
    color: white;
}

.light-icon {
    background: linear-gradient(135deg, #0a1929 0%, #1a2942 100%);
}

.dark-icon {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.luxury-icon {
    background: linear-gradient(135deg, #ffd700 0%, #9333ea 100%);
}

.theme-details {
    flex: 1;
}

.theme-name {
    margin: 0 0 0.25rem;
    font-size: 1rem;
    font-weight: 700;
    color: var(--theme-text-primary);
}

.theme-description {
    margin: 0 0 0.5rem;
    font-size: 0.85rem;
    color: var(--theme-text-muted);
    line-height: 1.4;
}

.theme-colors {
    display: flex;
    gap: 0.35rem;
}

.color-dot {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid var(--theme-surface);
    box-shadow: 0 0 0 1px var(--theme-border);
}

.theme-check {
    flex-shrink: 0;
    font-size: 1.5rem;
    color: var(--theme-primary);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.theme-option.active .theme-check {
    opacity: 1;
}

/* 底部 */
.theme-dropdown-footer {
    padding: 1.25rem 1.5rem;
    border-top: 1px solid var(--theme-border);
    background: var(--theme-surface-alt);
}

.theme-auto-switch {
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
}

.theme-auto-switch input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-right: 0.75rem;
    cursor: pointer;
}

.theme-auto-label {
    font-size: 0.95rem;
    font-weight: 500;
    color: var(--theme-text-secondary);
}

/* 响应式 */
@media (max-width: 576px) {
    .theme-dropdown {
        width: 100%;
        left: 0;
        right: 0;
    }
    
    .theme-preview {
        height: 60px;
    }
    
    .theme-info {
        flex-wrap: wrap;
    }
    
    .theme-check {
        width: 100%;
        text-align: center;
        margin-top: 0.5rem;
    }
}
</style>

<script>
/**
 * ChiBank 主题选择器功能
 */

// 切换主题下拉菜单
document.getElementById('chiBankThemeToggle')?.addEventListener('click', function(e) {
    e.stopPropagation();
    const dropdown = document.getElementById('chiBankThemeDropdown');
    dropdown?.classList.toggle('show');
});

// 关闭下拉菜单
function chiBankCloseThemeDropdown() {
    const dropdown = document.getElementById('chiBankThemeDropdown');
    dropdown?.classList.remove('show');
}

// 切换主题
function chiBankSwitchTheme(themeName) {
    if (window.themeManager) {
        window.themeManager.switchTheme(themeName);
        chiBankUpdateActiveTheme(themeName);
        chiBankCloseThemeDropdown();
    }
}

// 更新激活状态
function chiBankUpdateActiveTheme(themeName) {
    document.querySelectorAll('.theme-option').forEach(option => {
        option.classList.remove('active');
    });
    document.querySelector(`[data-theme="${themeName}"]`)?.classList.add('active');
}

// 切换自动跟随系统
function chiBankToggleAutoTheme(enabled) {
    if (window.themeManager) {
        window.themeManager.setAutoTheme(enabled);
    }
}

// 点击外部关闭
document.addEventListener('click', function(event) {
    const selector = document.querySelector('.chibank-theme-selector');
    if (selector && !selector.contains(event.target)) {
        chiBankCloseThemeDropdown();
    }
});

// 监听主题变化
window.addEventListener('themeChanged', function(e) {
    chiBankUpdateActiveTheme(e.detail.theme);
});

// 初始化
document.addEventListener('DOMContentLoaded', function() {
    if (window.themeManager) {
        const currentTheme = window.themeManager.currentTheme;
        chiBankUpdateActiveTheme(currentTheme);
        
        // 检查是否启用自动跟随
        const autoTheme = window.themeManager.isAutoTheme();
        const autoCheckbox = document.getElementById('chiBankThemeAuto');
        if (autoCheckbox) {
            autoCheckbox.checked = autoTheme;
        }
    }
});
</script>
