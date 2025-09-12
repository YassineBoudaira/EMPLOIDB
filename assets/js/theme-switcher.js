/**
 * Theme Switcher JavaScript
 * Handles dark/light mode toggle and color scheme switching
 */

class ThemeSwitcher {
    constructor() {
        this.themes = {
            'default': {
                name: 'Default',
                primary: '#2563eb',
                secondary: '#059669',
                accent: '#f59e0b'
            },
            'ocean': {
                name: 'Ocean',
                primary: '#0ea5e9',
                secondary: '#06b6d4',
                accent: '#8b5cf6'
            },
            'forest': {
                name: 'Forest',
                primary: '#16a34a',
                secondary: '#22c55e',
                accent: '#f59e0b'
            },
            'sunset': {
                name: 'Sunset',
                primary: '#dc2626',
                secondary: '#ea580c',
                accent: '#f59e0b'
            },
            'purple': {
                name: 'Purple',
                primary: '#7c3aed',
                secondary: '#a855f7',
                accent: '#ec4899'
            }
        };
        
        this.currentTheme = localStorage.getItem('theme') || 'default';
        this.isDarkMode = localStorage.getItem('darkMode') === 'true';
        
        this.init();
    }
    
    init() {
        this.applyTheme();
        this.createThemeSwitcher();
        this.bindEvents();
    }
    
    applyTheme() {
        const root = document.documentElement;
        const theme = this.themes[this.currentTheme];
        
        // Apply color scheme
        root.style.setProperty('--emploidb-brand-primary', theme.primary);
        root.style.setProperty('--emploidb-brand-secondary', theme.secondary);
        root.style.setProperty('--emploidb-brand-accent', theme.accent);
        
        // Apply dark mode
        if (this.isDarkMode) {
            root.classList.add('dark-mode');
        } else {
            root.classList.remove('dark-mode');
        }
        
        // Update theme indicator
        this.updateThemeIndicator();
    }
    
    createThemeSwitcher() {
        // Create theme switcher HTML
        const themeSwitcher = document.createElement('div');
        themeSwitcher.className = 'theme-switcher';
        themeSwitcher.innerHTML = `
            <div class="theme-switcher-container">
                <button class="theme-toggle-btn" id="themeToggle" title="Toggle Dark Mode">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>
                <div class="theme-dropdown" id="themeDropdown">
                    <div class="theme-dropdown-header">
                        <h6>Color Schemes</h6>
                        <button class="close-btn" id="closeThemeDropdown">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="theme-options">
                        ${Object.entries(this.themes).map(([key, theme]) => `
                            <div class="theme-option ${key === this.currentTheme ? 'active' : ''}" 
                                 data-theme="${key}">
                                <div class="theme-preview">
                                    <div class="color-swatch primary" style="background-color: ${theme.primary}"></div>
                                    <div class="color-swatch secondary" style="background-color: ${theme.secondary}"></div>
                                    <div class="color-swatch accent" style="background-color: ${theme.accent}"></div>
                                </div>
                                <span class="theme-name">${theme.name}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;
        
        // Add CSS styles
        const style = document.createElement('style');
        style.textContent = `
            .theme-switcher {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
            }
            
            .theme-switcher-container {
                position: relative;
            }
            
            .theme-toggle-btn {
                background: var(--emploidb-primary);
                color: white;
                border: none;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transition: all 0.3s ease;
            }
            
            .theme-toggle-btn:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(0,0,0,0.2);
            }
            
            .theme-dropdown {
                position: absolute;
                top: 60px;
                right: 0;
                background: white;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.15);
                padding: 20px;
                min-width: 250px;
                display: none;
                border: 1px solid #e2e8f0;
            }
            
            .theme-dropdown.show {
                display: block;
                animation: slideDown 0.3s ease;
            }
            
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .theme-dropdown-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
                padding-bottom: 10px;
                border-bottom: 1px solid #e2e8f0;
            }
            
            .theme-dropdown-header h6 {
                margin: 0;
                color: var(--emploidb-text-primary);
                font-weight: 600;
            }
            
            .close-btn {
                background: none;
                border: none;
                color: var(--emploidb-text-muted);
                cursor: pointer;
                padding: 5px;
                border-radius: 4px;
            }
            
            .close-btn:hover {
                background: #f1f5f9;
            }
            
            .theme-options {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            .theme-option {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 10px;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.2s ease;
                border: 2px solid transparent;
            }
            
            .theme-option:hover {
                background: #f8fafc;
            }
            
            .theme-option.active {
                background: var(--emploidb-primary-light);
                border-color: var(--emploidb-primary);
            }
            
            .theme-preview {
                display: flex;
                gap: 4px;
            }
            
            .color-swatch {
                width: 16px;
                height: 16px;
                border-radius: 50%;
                border: 2px solid white;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            
            .theme-name {
                font-weight: 500;
                color: var(--emploidb-text-primary);
            }
            
            /* Dark mode styles */
            .dark-mode {
                --emploidb-bg-primary: #1e293b;
                --emploidb-bg-secondary: #334155;
                --emploidb-bg-accent: #475569;
                --emploidb-text-primary: #f1f5f9;
                --emploidb-text-secondary: #cbd5e1;
                --emploidb-text-muted: #94a3b8;
                --emploidb-text-inverse: #0f172a;
            }
            
            .dark-mode .theme-dropdown {
                background: var(--emploidb-bg-secondary);
                border-color: var(--emploidb-bg-accent);
            }
            
            .dark-mode .theme-dropdown-header {
                border-color: var(--emploidb-bg-accent);
            }
            
            .dark-mode .theme-dropdown-header h6 {
                color: var(--emploidb-text-primary);
            }
            
            .dark-mode .close-btn {
                color: var(--emploidb-text-muted);
            }
            
            .dark-mode .close-btn:hover {
                background: var(--emploidb-bg-accent);
            }
            
            .dark-mode .theme-option:hover {
                background: var(--emploidb-bg-accent);
            }
            
            .dark-mode .theme-option.active {
                background: var(--emploidb-primary-light);
            }
            
            .dark-mode .theme-name {
                color: var(--emploidb-text-primary);
            }
            
            /* Responsive */
            @media (max-width: 768px) {
                .theme-switcher {
                    top: 10px;
                    right: 10px;
                }
                
                .theme-toggle-btn {
                    width: 45px;
                    height: 45px;
                }
                
                .theme-dropdown {
                    right: -50px;
                    min-width: 200px;
                }
            }
        `;
        
        document.head.appendChild(style);
        document.body.appendChild(themeSwitcher);
    }
    
    bindEvents() {
        // Theme toggle button
        document.getElementById('themeToggle').addEventListener('click', () => {
            this.toggleDarkMode();
        });
        
        // Theme dropdown toggle
        document.getElementById('themeToggle').addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleDropdown();
        });
        
        // Close dropdown
        document.getElementById('closeThemeDropdown').addEventListener('click', () => {
            this.closeDropdown();
        });
        
        // Theme selection
        document.querySelectorAll('.theme-option').forEach(option => {
            option.addEventListener('click', () => {
                const theme = option.dataset.theme;
                this.setTheme(theme);
                this.closeDropdown();
            });
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.theme-switcher')) {
                this.closeDropdown();
            }
        });
    }
    
    toggleDarkMode() {
        this.isDarkMode = !this.isDarkMode;
        localStorage.setItem('darkMode', this.isDarkMode);
        this.applyTheme();
    }
    
    setTheme(theme) {
        this.currentTheme = theme;
        localStorage.setItem('theme', theme);
        this.applyTheme();
        
        // Update active theme in dropdown
        document.querySelectorAll('.theme-option').forEach(option => {
            option.classList.toggle('active', option.dataset.theme === theme);
        });
    }
    
    toggleDropdown() {
        const dropdown = document.getElementById('themeDropdown');
        dropdown.classList.toggle('show');
    }
    
    closeDropdown() {
        const dropdown = document.getElementById('themeDropdown');
        dropdown.classList.remove('show');
    }
    
    updateThemeIndicator() {
        const icon = document.getElementById('themeIcon');
        if (this.isDarkMode) {
            icon.className = 'fas fa-sun';
        } else {
            icon.className = 'fas fa-moon';
        }
    }
    
    // Public methods
    getCurrentTheme() {
        return {
            theme: this.currentTheme,
            isDarkMode: this.isDarkMode
        };
    }
    
    setCustomTheme(primary, secondary, accent) {
        const root = document.documentElement;
        root.style.setProperty('--emploidb-brand-primary', primary);
        root.style.setProperty('--emploidb-brand-secondary', secondary);
        root.style.setProperty('--emploidb-brand-accent', accent);
    }
}

// Initialize theme switcher when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.themeSwitcher = new ThemeSwitcher();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeSwitcher;
}
