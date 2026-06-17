// assets/js/theme.js - Theme Toggle Handler
(function () {
    'use strict';

    // ── 1. Apply saved theme immediately (before DOM loads) ──
    const savedTheme = localStorage.getItem('app-theme') || 'dark';
    if (savedTheme === 'light') {
        document.documentElement.classList.add('light-theme');
    }

    // ── 2. Initialize when DOM is ready ──
    function init() {
        console.log('[Theme] Initializing...');
        
        // Inject CSS
        injectThemeCSS();
        
        // Setup toggle button
        setupToggleButton();
    }

    function injectThemeCSS() {
        if (document.querySelector('link[href*="theme.css"]')) {
            console.log('[Theme] CSS already loaded');
            return;
        }
        
        const scripts = Array.from(document.getElementsByTagName('script'));
        const themeScript = scripts.find(s => s.src && s.src.includes('assets/js/theme.js'));
        
        if (themeScript) {
            const basePath = themeScript.src.replace('assets/js/theme.js', '');
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = basePath + 'assets/css/theme.css';
            document.head.appendChild(link);
            console.log('[Theme] CSS injected:', link.href);
        }
    }

    function setupToggleButton() {
        const button = document.getElementById('theme-toggle-btn');
        
        if (!button) {
            console.error('[Theme] Toggle button not found! Looking for #theme-toggle-btn');
            return;
        }
        
        console.log('[Theme] Toggle button found:', button);
        
        // Update button to match current theme
        updateButton(button);
        
        // Add click handler
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('[Theme] Button clicked!');
            
            // Toggle theme
            const isNowLight = document.documentElement.classList.toggle('light-theme');
            const newTheme = isNowLight ? 'light' : 'dark';
            
            // Save to localStorage
            localStorage.setItem('app-theme', newTheme);
            console.log('[Theme] Switched to:', newTheme);
            
            // Update button appearance
            updateButton(button);
            
            // Update charts if present
            updateCharts(isNowLight);
        });
        
        console.log('[Theme] Click handler attached');
    }

    function updateButton(button) {
        const isLight = document.documentElement.classList.contains('light-theme');
        const icon = button.querySelector('i');
        const text = button.querySelector('span');
        
        if (isLight) {
            // Light mode active - show moon icon (to switch to dark)
            if (icon) {
                icon.className = 'fas fa-moon';
                icon.style.color = '#6366f1'; // indigo
            }
            if (text) {
                text.textContent = 'Mode Gelap';
            }
            console.log('[Theme] Button updated for light mode');
        } else {
            // Dark mode active - show sun icon (to switch to light)
            if (icon) {
                icon.className = 'fas fa-sun';
                icon.style.color = '#fbbf24'; // yellow
            }
            if (text) {
                text.textContent = 'Mode Terang';
            }
            console.log('[Theme] Button updated for dark mode');
        }
    }

    function updateCharts(isLight) {
        if (typeof Chart === 'undefined') {
            console.log('[Theme] Chart.js not found, skipping chart update');
            return;
        }

        console.log('[Theme] Updating charts...');
        const textColor = isLight ? '#475569' : '#9ca3af';
        const gridColor = isLight ? 'rgba(0,0,0,0.08)' : 'rgba(255,255,255,0.08)';

        Chart.helpers.each(Chart.instances, function (chart) {
            if (chart.options.scales) {
                Object.values(chart.options.scales).forEach(scale => {
                    if (scale.ticks) scale.ticks.color = textColor;
                    if (scale.grid) scale.grid.color = gridColor;
                    if (scale.border) scale.border.color = gridColor;
                });
            }
            if (chart.options.plugins?.legend?.labels) {
                chart.options.plugins.legend.labels.color = textColor;
            }
            if (chart.options.plugins?.tooltip) {
                const tooltip = chart.options.plugins.tooltip;
                tooltip.backgroundColor = isLight ? 'rgba(255,255,255,0.95)' : 'rgba(15,15,20,0.92)';
                tooltip.titleColor = isLight ? '#0f172a' : '#f1f5f9';
                tooltip.bodyColor = isLight ? '#475569' : '#9ca3af';
            }
            chart.update('none');
        });
        
        console.log('[Theme] Charts updated');
    }

    // ── 3. Start initialization ──
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // DOM already loaded
        init();
    }
})();
