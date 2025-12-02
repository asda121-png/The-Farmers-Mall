/**
 * Farmers Mall Admin Theme Initializer
 * This script should be included at the end of the body tag.
 * It handles shared UI logic like dark mode.
 */

function initializeAdminTheme() {
    const body = document.body;
    const root = document.documentElement;
    const THEME_KEY = 'adminTheme';
    const darkToggle = document.getElementById('darkModeToggle');

    const applyTheme = (theme) => {
        const isDark = theme === 'dark';
        body.classList.toggle('dark-mode', isDark);
        root.classList.toggle('dark', isDark);
        if (darkToggle) {
            darkToggle.checked = isDark;
        }
    };

    const setTheme = (theme) => {
        localStorage.setItem(THEME_KEY, theme);
        applyTheme(theme);
    };

    // expose helper so other scripts (e.g. settings) can reuse
    window.setAdminTheme = setTheme;

    // Apply saved theme on load
    const savedTheme = localStorage.getItem(THEME_KEY) || 'light';
    applyTheme(savedTheme);

    // Wire up legacy dark mode toggle if it exists
    if (darkToggle) {
        darkToggle.addEventListener('change', function () {
            setTheme(this.checked ? 'dark' : 'light');
        });
    }
}

// Run the theme initializer as soon as the script is loaded.
initializeAdminTheme();
