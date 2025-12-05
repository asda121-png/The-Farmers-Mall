document.addEventListener('DOMContentLoaded', () => {
  // Script to handle notification actions
  const notificationList = document.getElementById('notificationList');
  const markAllReadBtn = document.getElementById('clearAllBtn'); // The button ID is clearAllBtn in the HTML
  const filterTabs = document.getElementById('filterTabs');
  let notifications = JSON.parse(localStorage.getItem('userNotifications')) || [];
  let currentFilter = 'all';


  const getIconForType = (type) => {
    switch (type) {
      case 'order_success': return { icon: 'fa-box-check', color: 'green' };
      case 'order_shipped': return { icon: 'fa-truck-fast', color: 'blue' };
      case 'low_stock': return { icon: 'fa-triangle-exclamation', color: 'orange' };
      case 'new_review': return { icon: 'fa-star', color: 'yellow' };
      default: return { icon: 'fa-info-circle', color: 'gray' };
    }
  };

  function renderNotifications() {
    notificationList.innerHTML = '';

    const filteredNotifications = notifications.filter(n => {
        if (currentFilter === 'unread') return !n.read;
        return true;
    });

    if (filteredNotifications.length === 0) {
      notificationList.innerHTML = '<p class="text-center text-gray-500 py-10">🎉 All caught up! No new notifications.</p>';
      if (markAllReadBtn) markAllReadBtn.style.display = 'none';
      updateBadge();
      return;
    }

    // Sort the filtered notifications by time, newest first
    filteredNotifications.sort((a, b) => new Date(b.time) - new Date(a.time));

    filteredNotifications.forEach(notif => {
      const { icon, color } = getIconForType(notif.type);
      const readClass = !notif.read ? 'bg-green-50 border-l-4 border-green-500' : '';

      const notifLink = document.createElement('a');
      notifLink.href = notif.link || '#';
      notifLink.className = `block p-5 flex items-start space-x-4 hover:bg-gray-100 transition ${readClass}`;
      notifLink.dataset.id = notif.id;

      notifLink.innerHTML = `
        <div class="bg-${color}-100 text-${color}-700 p-3 rounded-full"><i class="fa-solid ${icon}"></i></div>
        <div class="flex-1">
          <p class="font-medium text-gray-800">${notif.title}</p>
          <p class="text-sm text-gray-500">${notif.message}</p>
          <span class="text-xs text-gray-400 block mt-1">${new Date(notif.time).toLocaleString()}</span>
        </div>
        <button class="remove-notification text-gray-400 hover:text-red-500">
          <i class="fa-solid fa-xmark"></i>
        </button>
      `;
      notificationList.appendChild(notifLink);
    });
    updateBadge();
  }

  function saveAndRender() {
    localStorage.setItem('userNotifications', JSON.stringify(notifications));
    renderNotifications();
  }

  function updateBadge() {
    const unreadCount = notifications.filter(n => !n.read).length;
    const badge = document.getElementById('notificationBadge');
    if (badge) {
      if (unreadCount > 0) {
        badge.textContent = unreadCount;
        badge.classList.remove('hidden');
      } else {
        badge.classList.add('hidden');
      }
    }
  }

  // Event Listeners
  notificationList.addEventListener('click', (e) => {
    const removeBtn = e.target.closest('.remove-notification');
    const notifLink = e.target.closest('a[data-id]');

    if (removeBtn) {
      e.preventDefault(); // Prevent link navigation
      e.stopPropagation();
      const notifId = parseInt(notifLink.dataset.id, 10);
      notifications = notifications.filter(n => n.id !== notifId);
      saveAndRender();
    } else if (notifLink) {
      // Mark as read when clicked
      const notifId = parseInt(notifLink.dataset.id, 10);
      const notification = notifications.find(n => n.id === notifId);
      if (notification) {
        notification.read = true;
        saveAndRender();
      }
      // Allow navigation to proceed
    }
  });

  if (markAllReadBtn) {
    markAllReadBtn.addEventListener('click', () => {
      notifications.forEach(n => n.read = true);
      saveAndRender();
    });
  }

  // Filter Tabs Logic
  if (filterTabs) {
      filterTabs.addEventListener('click', (e) => {
          if (e.target.matches('.filter-tab')) {
              currentFilter = e.target.dataset.filter;
              document.querySelectorAll('.filter-tab').forEach(tab => {
                  tab.classList.remove('border-green-600', 'text-green-600', 'font-semibold');
                  tab.classList.add('border-transparent', 'text-gray-500');
              });
              e.target.classList.add('border-green-600', 'text-green-600', 'font-semibold');
              renderNotifications();
          }
      });
  }

  // When the page becomes visible, mark notifications as read
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
      notifications.forEach(n => n.read = true);
      saveAndRender();
    }
  });

  // Initial render
  renderNotifications();
});