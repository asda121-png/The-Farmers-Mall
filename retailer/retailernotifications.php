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
  <header class="bg-white border-b shadow-sm sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-6 py-3 flex items-center justify-between">
      <!-- Left: Logo -->
      <div class="flex items-center space-x-3 cursor-pointer" onclick="window.location.href='retailerdashboard.php'">
        <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
          <i class="fas fa-leaf text-white text-lg"></i>
        </div>
        <h1 class="text-xl font-bold text-green-700">Farmers Mall</h1>
      </div>

      <!-- Center: Search -->
      <div class="flex-1 mx-8 max-w-xl">
        <form action="retailersearchresults.php" method="GET" class="relative">
          <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          <input type="search" name="q" placeholder="Search orders, products, customers..."
            class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-md focus:outline-none focus:ring-1 focus:ring-green-600 focus:border-green-600 text-sm">
        </form>
      </div>

      <!-- Right: Icons & Profile -->
      <div class="flex items-center space-x-6">
        <a href="retailermessage.php" class="relative cursor-pointer">
          <i class="fa-regular fa-comment text-xl text-gray-600"></i>
          <span class="absolute -top-2 -right-2 bg-green-700 text-white text-xs font-semibold rounded-full px-1.5">3</span>
        </a>
        <a href="retailernotifications.php" class="relative cursor-pointer">
          <i class="fa-regular fa-bell text-xl text-green-600"></i>
          <span class="absolute -top-2 -right-2 bg-green-700 text-white text-xs font-semibold rounded-full px-1.5">5</span>
        </a>
        <a href="retailerprofile.php" class="flex items-center space-x-2 cursor-pointer">
          <img src="https://randomuser.me/api/portraits/men/32.jpg" class="w-8 h-8 rounded-full" alt="Seller Profile">
          <div class="profile-info">
            <p class="text-sm font-medium text-gray-800">Mesa Farm</p>
            <p class="text-xs text-gray-500">Seller</p>
          </div>
        </a>
      </div>
    </div>
  </header>

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

      <!-- Notifications List -->
      <div id="notificationList" class="bg-white rounded-lg shadow-sm divide-y">
        <!-- Unread Notification - New Order -->
        <a href="retailerorderdetails.php?orderId=FM-1025" class="block p-5 flex items-start gap-4 hover:bg-green-200 bg-green-100 border-l-4 border-green-500">
            <div class="bg-green-100 text-green-600 p-3 rounded-full"><i class="fa-solid fa-box"></i></div>
            <div class="flex-1">
                <p class="font-medium">New Order Received</p>
                <p class="text-sm text-gray-600">You have a new order (#FM-1025) from Piodos De Blanco.</p>
                <p class="text-xs text-gray-400 mt-1">2 hours ago</p>
            </div>
            <button class="delete-notification text-gray-400 hover:text-red-500 text-xs z-10 relative"><i class="fa-solid fa-xmark"></i></button>
        </a>

        <!-- Unread Notification - Low Stock -->
        <a href="retailerinventory.php" class="block p-5 flex items-start gap-4 hover:bg-green-200 bg-green-50 border-l-4 border-green-500">
            <div class="bg-orange-100 text-orange-600 p-3 rounded-full"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="flex-1">
                <p class="font-medium">Low Stock Warning</p>
                <p class="text-sm text-gray-600">Your product "Red Onions" is running low on stock (5 left).</p>
                <p class="text-xs text-gray-400 mt-1">1 day ago</p>
            </div>
            <button class="delete-notification text-gray-400 hover:text-red-500 text-xs z-10 relative"><i class="fa-solid fa-xmark"></i></button>
        </a>

        <!-- Read Notification - New Review -->
        <a href="retailerproducts.php" class="block p-5 flex items-start gap-4 hover:bg-green-200">
            <div class="bg-blue-100 text-blue-600 p-3 rounded-full"><i class="fa-solid fa-star"></i></div>
            <div class="flex-1">
                <p class="font-medium">New 5-Star Review</p>
                <p class="text-sm text-gray-600">Jane Smith left a 5-star review for your product "Iceberg Lettuce".</p>
                <p class="text-xs text-gray-400 mt-1">3 days ago</p>
            </div>
            <button class="delete-notification text-gray-400 hover:text-red-500 text-xs z-10 relative"><i class="fa-solid fa-xmark"></i></button>
        </a>
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

      // --- Centralized Notification Count Management ---
      const getUnreadCount = () => {
        const unreadItems = document.querySelectorAll('#notificationList .bg-green-50');
        return unreadItems.length;
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
        const unreadNotifications = notificationList.querySelectorAll('.bg-green-50');
        unreadNotifications.forEach(notification => {
          notification.classList.remove('bg-green-50', 'border-l-4', 'border-green-500');
        });
        updateNotificationBadge();
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

          notificationToDelete = deleteBtn.closest('a');
          deleteNotificationModal.classList.remove('hidden');
        }
      });

      cancelDeleteNotificationBtn.addEventListener('click', () => {
        deleteNotificationModal.classList.add('hidden');
        notificationToDelete = null;
      });

      confirmDeleteNotificationBtn.addEventListener('click', () => {
        if (notificationToDelete) {
          notificationToDelete.remove();
          updateNotificationBadge();

          const remainingItems = notificationList.querySelectorAll('a');
          if (remainingItems.length === 0) {
            notificationList.innerHTML = '<p class="text-center text-gray-500 py-10">🎉 All caught up! No notifications left.</p>';
          }
        }
        deleteNotificationModal.classList.add('hidden');
        notificationToDelete = null;
      });


      // Initial count update on page load
      updateNotificationBadge();
    });
  </script>

</body>

</html>
