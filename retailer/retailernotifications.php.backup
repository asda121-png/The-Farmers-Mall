<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Seller Notifications – Farmers Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

  <!-- Header -->
     <?php
// Include the header
include '../retailer/retailerheader.php';
?>

  <!-- Main Content -->
  <main class="flex-grow">
    <div class="max-w-6xl mx-auto px-6 py-8 min-h-[92vh]">
      <!-- Title & Actions -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-3">
          <button onclick="window.location.href='retailerdashboard.php'" class="text-gray-600 hover:text-black">
            <i class="fa-solid fa-arrow-left text-lg"></i>
          </button>
          <h2 class="text-lg font-semibold">Notifications</h2>
        </div>
        <button id="markAllRead" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700">Mark all as read</button>
      </div>

      <!-- Filter Tabs -->
      <div id="filterTabs" class="flex items-center gap-4 mb-4 border-b">
        <button data-filter="all" class="filter-tab py-2 px-1 border-b-2 border-green-600 text-green-600 font-semibold text-sm">All</button>
        <button data-filter="unread" class="filter-tab py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-black text-sm">Unread</button>
      </div>

      <!-- Notifications List -->
      <div id="notificationList" class="bg-white rounded-lg shadow-sm divide-y">
        <!-- Notifications will be dynamically loaded here -->
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="text-white py-12 mt-auto" style="background-color: #1B5E20;">
    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">
      <div><h3 class="font-bold text-lg mb-3">Farmers Mall</h3><p class="text-gray-300 text-sm">Fresh, organic produce delivered straight to your home from local farmers.</p></div>
      <div><h3 class="font-bold text-lg mb-3">Quick Links</h3><ul class="space-y-2 text-sm text-gray-300"><li><a href="#" class="hover:underline">About Us</a></li><li><a href="#" class="hover:underline">Contact</a></li><li><a href="#" class="hover:underline">FAQ</a></li><li><a href="#" class="hover:underline">Support</a></li></ul></div>
      <div><h3 class="font-bold text-lg mb-3">Categories</h3><ul class="space-y-2 text-sm text-gray-300"><li><a href="#" class="hover:underline">Vegetables</a></li><li><a href="#" class="hover:underline">Fruits</a></li><li><a href="#" class="hover:underline">Dairy</a></li><li><a href="#" class="hover:underline">Meat</a></li></ul></div>
      <div><h3 class="font-bold text-lg mb-3">Follow Us</h3><div class="flex space-x-4 text-xl"><a href="#"><i class="fab fa-facebook"></i></a><a href="#"><i class="fab fa-twitter"></i></a><a href="#"><i class="fab fa-instagram"></i></a></div></div>
    </div>
    <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">© 2025 Farmers Mall. All rights reserved.</div>
  </footer>

  <!-- Delete Notification Confirmation Modal -->
  <div id="deleteNotificationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm text-center">
      <div class="text-red-500 text-4xl mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <h3 class="font-semibold text-lg mb-2">Confirm Deletion</h3>
      <p class="text-gray-600 text-sm mb-6">Are you sure you want to delete this notification?</p>
      <div class="flex justify-center gap-4">
        <button id="cancelDeleteNotification" class="px-6 py-2 border rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
        <button id="confirmDeleteNotification" class="px-6 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">Delete</button>
      </div>
    </div>
  </div>


  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const markAllReadBtn = document.getElementById('markAllRead');
      const notificationList = document.getElementById('notificationList');
      const filterTabs = document.getElementById('filterTabs');

      let currentFilter = 'all';
      
      // --- SIMULATED NOTIFICATION DATA ---
      // In a real application, you would fetch this from a PHP endpoint.
      let notifications = [
        {
          id: 1,
          type: 'order',
          title: 'New Order Received',
          message: 'You have a new order (#FM-1025) from Piodos De Blanco.',
          time: '2 hours ago',
          read: false,
          link: 'retailerorderdetails.php?orderId=FM-1025'
        },
        {
          id: 2,
          type: 'stock',
          title: 'Low Stock Warning',
          message: 'Your product "Red Onions" is running low on stock (5 left).',
          time: '1 day ago',
          read: false,
          link: 'retailerinventory.php'
        },
        {
          id: 3,
          type: 'review',
          title: 'New 5-Star Review',
          message: 'Jane Smith left a 5-star review for your product "Iceberg Lettuce".',
          time: '3 days ago',
          read: true,
          link: 'retailerproducts.php'
        },
        {
          id: 4,
          type: 'message',
          title: 'New Message',
          message: 'John Doe sent you a message regarding his order.',
          time: '4 days ago',
          read: true,
          link: 'retailermessage.php'
        },
        {
          id: 5,
          type: 'payment',
          title: 'Payment Received',
          message: 'Payment of ₱1,250.00 for order #FM-1024 has been confirmed.',
          time: '5 days ago',
          read: true,
          link: 'retailerproducts.php'
        }
      ];

      const getIconForType = (type) => {
        switch (type) {
          case 'order': return { icon: 'fa-box', color: 'green' };
          case 'stock': return { icon: 'fa-triangle-exclamation', color: 'orange' };
          case 'review': return { icon: 'fa-star', color: 'blue' };
          case 'message': return { icon: 'fa-comment-dots', color: 'purple' };
          case 'payment': return { icon: 'fa-credit-card', color: 'teal' };
          default: return { icon: 'fa-bell', color: 'gray' };
        }
      };

      const renderNotifications = () => {
        notificationList.innerHTML = '';

        const filteredNotifications = notifications.filter(n => {
          if (currentFilter === 'unread') return !n.read;
          return true;
        }).sort((a, b) => new Date(b.time) - new Date(a.time)); // Sort by time, newest first

        if (filteredNotifications.length === 0) {
          notificationList.innerHTML = '<p class="text-center text-gray-500 py-10">🎉 All caught up! No notifications left.</p>';
          updateNotificationBadge();
          return;
        }

        filteredNotifications.forEach(notif => {
          const { icon, color } = getIconForType(notif.type);
          const readClass = !notif.read ? 'bg-green-50 border-l-4 border-green-500' : '';
          const item = document.createElement('a');
          item.href = notif.link;
          item.className = `block p-5 flex items-start gap-4 hover:bg-gray-100 ${readClass}`;
          item.dataset.id = notif.id;

          item.innerHTML = `
            <div class="bg-${color}-100 text-${color}-600 p-3 rounded-full"><i class="fa-solid ${icon}"></i></div>
            <div class="flex-1">
                <p class="font-medium">${notif.title}</p>
                <p class="text-sm text-gray-600">${notif.message}</p>
                <p class="text-xs text-gray-400 mt-1">${notif.time}</p>
            </div>
            <button class="delete-notification text-gray-400 hover:text-red-500 text-xs z-10 relative"><i class="fa-solid fa-xmark"></i></button>
          `;
          notificationList.appendChild(item);
        });
        updateNotificationBadge();
      };

      // --- Centralized Notification Count Management ---
      const getUnreadCount = () => {
        return notifications.filter(n => !n.read).length;
      };

      const updateNotificationBadge = () => {
        const count = getUnreadCount();
        localStorage.setItem('unreadNotifications', count);

        // Update badge on all pages that might be open
        const badges = document.querySelectorAll('a[href="retailernotifications.php"] .absolute');
        badges.forEach(badge => {
          if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-flex';
          } else {
            badge.style.display = 'none';
          }
        });
      };

      markAllReadBtn.addEventListener('click', () => {
        notifications.forEach(n => n.read = true);
        renderNotifications();
      });

      // --- Mark as Read on Click ---
      notificationList.addEventListener('click', (e) => {
        const targetLink = e.target.closest('a');
        if (targetLink && !e.target.closest('.delete-notification')) {
          const notifId = parseInt(targetLink.dataset.id);
          const notification = notifications.find(n => n.id === notifId);
          if (notification && !notification.read) {
            notification.read = true;
            // No need to call renderNotifications() here, as the page will navigate away.
            // The badge will be correct on the next page load.
            updateNotificationBadge();
          }
        }
      });

      // --- Filter Logic ---
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

      // --- Delete Logic with Confirmation ---
      const deleteNotificationModal = document.getElementById('deleteNotificationModal');
      const cancelDeleteNotificationBtn = document.getElementById('cancelDeleteNotification');
      const confirmDeleteNotificationBtn = document.getElementById('confirmDeleteNotification');
      let notificationToDelete = null;

      notificationList.addEventListener('click', (e) => {
        const deleteBtn = e.target.closest('.delete-notification');
        if (deleteBtn) {
          // Prevent the link from being followed when deleting
          e.preventDefault();
          e.stopPropagation();

          notificationToDelete = e.target.closest('a');
          deleteNotificationModal.classList.remove('hidden');
        }
      });

      cancelDeleteNotificationBtn.addEventListener('click', () => {
        deleteNotificationModal.classList.add('hidden');
        notificationToDelete = null;
      });

      confirmDeleteNotificationBtn.addEventListener('click', () => {
        if (notificationToDelete) {
          const notifId = parseInt(notificationToDelete.dataset.id);
          notifications = notifications.filter(n => n.id !== notifId);
          renderNotifications();
        }
        deleteNotificationModal.classList.add('hidden');
        notificationToDelete = null;
      });

      // Initial count update on page load
      renderNotifications();
    });
  </script>

</body>

</html>
