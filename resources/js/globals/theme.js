/**
* Sheaf Dark Mode Theme System
* Provides comprehensive theme management with Alpine.js integration
*/

import defineReactiveMagicProperty from '../utils.js';

export function registerTheme(Alpine) {
    return defineReactiveMagicProperty('theme', {
        currentTheme: null,
        storedTheme: null,

        init() {
            // Check localStorage for stored theme preference
            this.storedTheme = localStorage.getItem('theme') ?? 'system';

            // Resolve the configured theme to be only [light, dark]
            this.currentTheme = computeTheme(this.storedTheme);
            
            // Apply initial theme to DOM
            applyTheme(this.currentTheme);

            // Listen for system theme changes
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addEventListener('change', (event) => {
                if (this.storedTheme === 'system') {
                    this.currentTheme = event.matches ? 'dark' : 'light';
                    applyTheme(this.currentTheme);
                }
            });
        },

        /**
         * Set theme preference and persist to localStorage
         */
        setTheme(newTheme) {
            this.storedTheme = newTheme;
            localStorage.setItem('theme', newTheme);
            
            this.currentTheme = computeTheme(newTheme);
            applyTheme(this.currentTheme);
        },

        setLight() {
            this.setTheme('light');
        },

        setDark() {
            this.setTheme('dark');
        },

        setSystem() {
            this.setTheme('system');
        },

        toggle() {
            if (this.storedTheme === 'system') {
                this.setTheme(this.currentTheme === 'dark' ? 'light' : 'dark');
            } else {
                this.setTheme(this.storedTheme === 'dark' ? 'light' : 'dark');
            }
        },

        get() {
            return {
                stored: this.storedTheme,
                current: this.currentTheme,
                isLight: this.isLight,
                isDark: this.isDark,
                isSystem: this.isSystem
            };
        },

        get isLight() {
            return this.storedTheme === 'light';
        },

        get isDark() {
            return this.storedTheme === 'dark';
        },

        get isSystem() {
            return this.storedTheme === 'system';
        },

        get isResolvedToLight() {
            if (this.isSystem) {
                return getSystemTheme() === 'light';
            }
            return this.isLight;
        },

        get isResolvedToDark() {
            if (this.isSystem) {
                return getSystemTheme() === 'dark';
            }
            return this.isDark;
        }
    }, Alpine);
}

// Also listen to alpine:init just in case
document.addEventListener('alpine:init', () => {
    if (window.Alpine) {
        registerTheme(window.Alpine);
    }
});

function computeTheme(themePreference) {
    if (themePreference === 'system') {
        return getSystemTheme();
    }
    return themePreference;
}

function getSystemTheme() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches 
        ? 'dark' 
        : 'light';
}

function applyTheme(theme) {
    const documentElement = document.documentElement;
    
    if (theme === 'dark') {
        documentElement.classList.add('dark');
    } else {
        documentElement.classList.remove('dark');
    }
    
    document.dispatchEvent(new CustomEvent('theme-changed', {
        detail: { theme }
    }));
}