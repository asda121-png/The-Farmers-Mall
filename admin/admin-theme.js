/**
 * Farmers Mall Admin Theme Initializer
 * This script should be included at the end of the body tag.
 * It handles shared UI logic like dark mode.
 */

function initializeAdminTheme() {
    const body = document.body;
    const THEME_KEY = 'adminTheme';

    // 1. Apply saved theme on load
    const savedTheme = localStorage.getItem(THEME_KEY) || 'light';
    if (savedTheme === 'dark') {
        body.classList.add('dark-mode');
    } else {
        body.classList.remove('dark-mode');
    }

    // 2. Wire up dark mode toggle if it exists on the page
    const darkToggle = document.getElementById('darkModeToggle');
    if (darkToggle) {
        darkToggle.checked = body.classList.contains('dark-mode');

        darkToggle.addEventListener('change', function () {
            if (this.checked) {
                body.classList.add('dark-mode');
                localStorage.setItem(THEME_KEY, 'dark');
            } else {
                body.classList.remove('dark-mode');
                localStorage.setItem(THEME_KEY, 'light');
            }
        });
    }
}

// Run the theme initializer as soon as the script is loaded.
initializeAdminTheme();
