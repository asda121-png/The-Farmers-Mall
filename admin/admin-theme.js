// Shared dark mode logic for all admin pages

document.addEventListener('DOMContentLoaded', function () {
  const body = document.body;
  const THEME_KEY = 'adminTheme';

  // Apply saved theme on load
  const savedTheme = localStorage.getItem(THEME_KEY) || 'light';
  if (savedTheme === 'dark') {
    body.classList.add('dark-mode');
  } else {
    body.classList.remove('dark-mode');
  }

  // Wire up dark mode toggle if present on the page
  const darkToggle = document.getElementById('darkModeToggle');
  if (darkToggle) {
    // Sync initial state with current theme
    darkToggle.checked = body.classList.contains('dark-mode');

    darkToggle.addEventListener('change', function () {
      if (darkToggle.checked) {
        body.classList.add('dark-mode');
        localStorage.setItem(THEME_KEY, 'dark');
      } else {
        body.classList.remove('dark-mode');
        localStorage.setItem(THEME_KEY, 'light');
      }
    });
  }
});


