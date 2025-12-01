document.addEventListener('DOMContentLoaded', () => {
  // Dynamically load header.html
  fetch('header.html')
    .then(res => res.text())
    .then(data => {
      document.getElementById('header').innerHTML = data;

      // Highlight the notification icon once header is loaded
      const notifIcon = document.querySelector('a[href="notification.html"] i');
      if (notifIcon) {
        notifIcon.parentElement.classList.remove('text-gray-600');
        notifIcon.parentElement.classList.add('text-green-600');
      }

      // Add search functionality to the loaded header
      const headerSearchInput = document.querySelector('#header input[type="text"]');
      if (headerSearchInput) {
        const form = document.createElement('form');
        form.action = 'products.php';
        form.method = 'GET';
        headerSearchInput.name = 'search';
        headerSearchInput.parentElement.insertBefore(form, headerSearchInput);
        form.appendChild(headerSearchInput);
      }
    });

  // Script to handle notification actions
  const notificationList = document.getElementById('notificationList');
  const clearAllBtn = document.getElementById('clearAllBtn');
  let notifications = JSON.parse(localStorage.getItem('notifications')) || [];

  function renderNotifications() {
    notificationList.innerHTML = '';
    if (notifications.length === 0) {
      notificationList.innerHTML = '<p class="text-center text-gray-500 py-10">🎉 All caught up! No new notifications.</p>';
      if (clearAllBtn) clearAllBtn.style.display = 'none';
      return;
    }

    notifications.forEach(notif => {
      const notifDiv = document.createElement('div');
      notifDiv.className = 'p-5 flex items-start space-x-4 hover:bg-gray-50 transition';
      notifDiv.dataset.id = notif.id;

      let iconHtml = '';
      if (notif.type === 'order_placed') {
        iconHtml = `<div class="bg-green-100 text-green-700 p-3 rounded-full"><i class="fa-solid fa-box"></i></div>`;
      } else {
        iconHtml = `<div class="bg-blue-100 text-blue-700 p-3 rounded-full"><i class="fa-solid fa-info-circle"></i></div>`;
      }

      notifDiv.innerHTML = `
        ${iconHtml}
        <div class="flex-1">
          <p class="font-medium text-gray-800">${notif.title}</p>
          <p class="text-sm text-gray-500">${notif.message}</p>
          <span class="text-xs text-gray-400 block mt-1">${new Date(notif.timestamp).toLocaleString()}</span>
        </div>
        <button class="remove-notification text-gray-400 hover:text-red-500">
          <i class="fa-solid fa-xmark"></i>
        </button>
      `;
      notificationList.appendChild(notifDiv);
    });
  }

  function removeNotification(id) {
    notifications = notifications.filter(n => n.id != id);
    localStorage.setItem('notifications', JSON.stringify(notifications));
    renderNotifications();
  }

  function clearAllNotifications() {
    notifications = [];
    localStorage.setItem('notifications', JSON.stringify(notifications));
    renderNotifications();
  }

  // Event Listeners
  notificationList.addEventListener('click', (e) => {
    const removeBtn = e.target.closest('.remove-notification');
    if (removeBtn) {
      const notifDiv = removeBtn.closest('div[data-id]');
      if (notifDiv) {
        removeNotification(notifDiv.dataset.id);
      }
    }
  });

  if (clearAllBtn) {
    clearAllBtn.addEventListener('click', clearAllNotifications);
  }

  // Initial render
  renderNotifications();
});