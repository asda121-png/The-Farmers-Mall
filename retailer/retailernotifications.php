<?php
session_start();

// Check if user is logged in and is a retailer
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'retailer' && $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Load database connection
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/supabase-api.php';

// Get user data from database
$api = getSupabaseAPI();
$userId = $_SESSION['user_id'];
$userData = null;
$profilePicture = '../images/default-avatar.svg'; // Default avatar
$userFullName = $_SESSION['full_name'] ?? 'Retailer';
$userEmail = $_SESSION['email'] ?? '';
$shopName = 'My Shop';

try {
    // Fetch user data from database
    $users = $api->select('users', ['id' => $userId]);
    if (!empty($users)) {
        $userData = $users[0];
        $userFullName = $userData['full_name'] ?? $userFullName;
        $userEmail = $userData['email'] ?? $userEmail;
        
        // Check if user has a profile picture
        if (!empty($userData['profile_picture'])) {
            $profilePath = '../' . ltrim($userData['profile_picture'], '/');
            if (file_exists($profilePath)) {
                $profilePicture = $profilePath;
            }
        }
        
        // Try to get retailer shop name
        if ($userData['user_type'] === 'retailer') {
            $retailers = $api->select('retailers', ['user_id' => $userId]);
            if (!empty($retailers)) {
                $shopName = $retailers[0]['shop_name'] ?? $shopName;
            }
        }
    }
} catch (Exception $e) {
    error_log("Error fetching user data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Notifications – The Farmer's Mall</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com">    
    function toggleMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
    
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                toggleMobileMenu();
            }
        });
    });
</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Load Inter font -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7fbf8;
        }
        /* Ensure main content area has minimum height */
        #content {
            min-height: calc(100vh - 200px);
        }
        /* Footer stays at bottom, visible only when scrolling */
        footer {
            margin-top: auto;
        }
        /* Sticky sidebar - fixed position */
        #sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            height: 100vh !important;
            overflow-y: auto !important;
            display: flex !important;
            flex-direction: column !important;
            z-index: 40 !important;
        }
        /* Add left margin to main content to account for fixed sidebar */
        #app {
            margin-left: 16rem !important;
        }
        /* Mobile menu toggle */
        #mobileMenuBtn {
            display: none;
        }
        @media (max-width: 768px) {
            #mobileMenuBtn {
                display: flex;
            }
            #app {
                margin-left: 0 !important;
            }
            #sidebar {
                left: -100%;
                z-index: 50;
                transition: left 0.3s ease;
            }
            #sidebar.active {
                left: 0;
            }
            #overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 40;
            }
            #overlay.active {
                display: block;
            }
        }
    </style>
</head>
<body>

<div class="flex flex-col min-h-screen">
    <!-- Mobile Menu Overlay -->
    <div id="overlay" onclick="toggleMobileMenu()"></div>
    
    <!-- Mobile Menu Button -->
    <button id="mobileMenuBtn" class="fixed top-4 left-4 z-50 bg-green-600 text-white p-3 rounded-lg shadow-lg md:hidden" onclick="toggleMobileMenu()">
        <i class="fas fa-bars text-xl"></i>
    </button>
    
    <!-- Main Application Container -->
    <div id="app" class="flex flex-1">
        
        <!-- Sidebar Navigation -->
        <nav id="sidebar" class="w-64 md:w-64 bg-white shadow-xl flex flex-col p-4 space-y-2 flex-shrink-0">
            <div class="flex items-center mb-6">
                <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center mr-2">
                    <i class="fas fa-leaf text-white text-lg"></i>
                </div>
                <h1 class="text-2xl font-bold text-green-700">Farmers Mall</h1>
            </div>
            <a href="retailer-dashboard2.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <i class="fas fa-tachometer-alt text-lg mr-3"></i>
                Dashboard
            </a>
            <a href="retailerinventory.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <i class="fas fa-boxes text-lg mr-3"></i>
                Products & Inventory
            </a>
            <a href="retailerfulfillment.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <i class="fas fa-clipboard-list text-lg mr-3"></i>
                Order Fulfillment
            </a>
            <a href="retailerfinance.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <i class="fas fa-chart-line text-lg mr-3"></i>
                Financial Reports
            </a>

            <a href="retailerreviews.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <i class="fas fa-star text-lg mr-3"></i>
                Reviews & Customers
            </a>
        </nav>

        <div class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm sticky top-0 z-30">
                <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-end">
                    <div class="flex items-center space-x-6">
                        <a href="retailer-dashboard2.php" class="text-gray-600 hover:text-green-600 transition" title="Home"><i class="fa-solid fa-house text-xl"></i></a>

                        <!-- Notifications Icon -->
                        <div class="relative" id="notificationPreviewContainer">
                            <a href="retailernotifications.php" class="text-green-600 transition relative" title="Notifications" id="notificationIcon">
                                <i class="fa-solid fa-bell text-xl"></i>
                                <span id="notificationBadge" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 min-w-[1.125rem] h-[1.125rem] flex items-center justify-center hidden">0</span>
                            </a>
                            <div id="notificationPreview" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                                <div class="p-4 border-b border-gray-100"><h3 class="font-semibold text-gray-800">Notifications</h3></div>
                                <div id="notificationPreviewItems" class="max-h-96 overflow-y-auto"><div class="p-8 text-center text-gray-500"><i class="fas fa-bell text-4xl mb-2 text-gray-300"></i><p class="text-sm">No notifications</p></div></div>
                                <div class="p-4 border-t border-gray-100 bg-gray-50"><a href="retailernotifications.php" class="block w-full bg-green-600 text-white text-center py-2 rounded-lg hover:bg-green-700 transition font-medium">View All Notifications</a></div>
                            </div>
                        </div>

                        <!-- Profile Dropdown -->
                        <div class="relative" id="profileDropdownContainer">
                            <button id="profileDropdownBtn" class="flex items-center focus:outline-none" title="<?php echo htmlspecialchars($userFullName); ?>">
                                <?php if (!empty($profilePicture) && $profilePicture !== '../images/default-avatar.svg' && file_exists(__DIR__ . '/' . $profilePicture)): ?>
                                    <img id="headerProfilePic" src="<?php echo htmlspecialchars($profilePicture); ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($userFullName); ?>" class="w-8 h-8 rounded-full cursor-pointer object-cover border-2 border-gray-200" onerror="this.src='../images/default-avatar.svg'">
                                <?php else: ?>
                                    <div class="w-8 h-8 rounded-full cursor-pointer bg-green-600 flex items-center justify-center">
                                        <i class="fas fa-user text-white text-sm"></i>
                                    </div>
                                <?php endif; ?>
                            </button>
                            <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-64 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                                <!-- Profile Header -->
                                <div class="p-4 border-b border-gray-200">
                                    <div class="flex items-center space-x-3">
                                        <?php if (!empty($profilePicture) && $profilePicture !== '../images/default-avatar.svg' && file_exists(__DIR__ . '/' . $profilePicture)): ?>
                                            <img src="<?php echo htmlspecialchars($profilePicture); ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($userFullName); ?>" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200" onerror="this.src='../images/default-avatar.svg'">
                                        <?php else: ?>
                                            <div class="w-12 h-12 rounded-full bg-green-600 flex items-center justify-center">
                                                <i class="fas fa-user text-white text-lg"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($userFullName); ?></p>
                                            <p class="text-xs text-gray-500 truncate"><?php echo htmlspecialchars($userEmail); ?></p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Menu Items -->
                                <div class="py-2">
                                    <a href="retailerprofile.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 transition">
                                        <i class="fas fa-user-circle text-gray-400 text-lg w-5"></i>
                                        <span class="ml-3 text-sm">Profile & Settings</span>
                                    </a>
                                    <a href="../auth/logout.php" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 transition">
                                        <i class="fas fa-sign-out-alt text-gray-400 text-lg w-5"></i>
                                        <span class="ml-3 text-sm">Logout</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- End Profile Dropdown -->
                    </div>
                </div>
            </header>
        
            <!-- Main Content Area -->
            <main id="content" class="p-8 transition-all duration-300 flex-1">
      <!-- Title & Actions -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-3">
          <h2 class="text-3xl font-bold text-gray-800">Notifications</h2>
        </div>
        <button id="markAllRead" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700">Mark all as read</button>
      </div>

      <!-- Filter Tabs -->
      <div id="filterTabs" class="flex items-center gap-4 mb-4 border-b">
        <button data-filter="all" class="filter-tab py-2 px-1 border-b-2 border-green-600 text-green-600 font-semibold text-sm">All</button>
        <button data-filter="unread" class="filter-tab py-2 px-1 border-b-2 border-transparent text-gray-500 hover:text-black text-sm">Unread</button>
      </div>

      <!-- Notifications List -->
      <div id="notificationList" class="bg-white rounded-lg shadow-sm hover:shadow-md hover:border-green-200 transition-all duration-300 divide-y">
        <!-- Notifications will be dynamically loaded here -->
      </div>
    </div>
            </main>
        </div>
    </div>
    
    </div>

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
      let notifications = [];
      
      // Fetch notifications from database
      function loadNotifications() {
        fetch('../api/get-retailer-notifications.php')
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              notifications = data.notifications;
              renderNotifications();
            } else {
              console.error('Error loading notifications:', data.error);
              notificationList.innerHTML = '<p class="text-center text-gray-500 py-10">Failed to load notifications</p>';
            }
          })
          .catch(error => {
            console.error('Error fetching notifications:', error);
            notificationList.innerHTML = '<p class="text-center text-gray-500 py-10">Failed to load notifications</p>';
          });
      }

      const getIconForType = (type) => {
        switch (type) {
          case 'order': return { icon: 'fa-box', color: 'green' };
          case 'order_cancelled': return { icon: 'fa-ban', color: 'red' };
          case 'stock': return { icon: 'fa-triangle-exclamation', color: 'orange' };
          case 'review': return { icon: 'fa-star', color: 'blue' };
          case 'message': return { icon: 'fa-comment-dots', color: 'purple' };
          case 'payment': return { icon: 'fa-credit-card', color: 'teal' };
          default: return { icon: 'fa-bell', color: 'gray' };
        }
      };
      
      function getTimeAgo(timestamp) {
        const date = new Date(timestamp);
        const seconds = Math.floor((new Date() - date) / 1000);
        if (seconds < 60) return 'Just now';
        if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
        if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
        return date.toLocaleDateString();
      }

      const renderNotifications = () => {
        notificationList.innerHTML = '';

        const filteredNotifications = notifications.filter(n => {
          if (currentFilter === 'unread') return !n.read;
          return true;
        });

        if (filteredNotifications.length === 0) {
          notificationList.innerHTML = '<p class="text-center text-gray-500 py-10">🎉 All caught up! No notifications left.</p>';
          updateNotificationBadge();
          return;
        }

        filteredNotifications.forEach(notif => {
          const { icon, color } = getIconForType(notif.type);
          const readClass = !notif.read ? 'bg-green-50 border-l-4 border-green-500' : '';
          const timeAgo = getTimeAgo(notif.timestamp);
          const item = document.createElement('a');
          item.href = notif.link;
          item.className = `block p-5 flex items-start gap-4 hover:bg-gray-100 ${readClass}`;
          item.dataset.id = notif.id;

          item.innerHTML = `
            <div class="bg-${color}-100 text-${color}-600 p-3 rounded-full"><i class="fa-solid ${icon}"></i></div>
            <div class="flex-1">
                <p class="font-medium">${escapeHtml(notif.title)}</p>
                <p class="text-sm text-gray-600">${escapeHtml(notif.message)}</p>
                <p class="text-xs text-gray-400 mt-1">${timeAgo}</p>
            </div>
            <button class="delete-notification text-gray-400 hover:text-red-500 text-xs z-10 relative"><i class="fa-solid fa-xmark"></i></button>
          `;
          notificationList.appendChild(item);
        });
        updateNotificationBadge();
      };
      
      function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
      }

      // --- Centralized Notification Count Management ---
      const getUnreadCount = () => {
        return notifications.filter(n => !n.read).length;
      };

      const updateNotificationBadge = () => {
        const count = getUnreadCount();
        localStorage.setItem('unreadNotifications', count);
        localStorage.setItem('notificationsUpdated', Date.now().toString());

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
        fetch('../api/mark-retailer-notification-read.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            notifications.forEach(n => n.read = true);
            renderNotifications();
            // Trigger notification badge refresh on all pages
            window.dispatchEvent(new CustomEvent('notificationsUpdated'));
          }
        })
        .catch(error => console.error('Error marking all as read:', error));
      });

      // --- Mark as Read on Click ---
      notificationList.addEventListener('click', (e) => {
        const targetLink = e.target.closest('a');
        if (targetLink && !e.target.closest('.delete-notification')) {
          const notifId = targetLink.dataset.id;
          const notification = notifications.find(n => n.id === notifId);
          if (notification && !notification.read) {
            // Mark as read in database
            fetch('../api/mark-retailer-notification-read.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ notification_id: notifId })
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                notification.read = true;
                updateNotificationBadge();
                // Trigger notification badge refresh on all pages
                window.dispatchEvent(new CustomEvent('notificationsUpdated'));
              }
            })
            .catch(error => console.error('Error marking as read:', error));
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
          const notifId = notificationToDelete.dataset.id;
          
          // Delete from database
          fetch('../api/update-notification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', notification_id: notifId })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              notifications = notifications.filter(n => n.id !== notifId);
              renderNotifications();
            }
          })
          .catch(error => console.error('Error deleting notification:', error));
        }
        deleteNotificationModal.classList.add('hidden');
        notificationToDelete = null;
      });

      // Initial load
      loadNotifications();
    });

    // Profile dropdown hover handlers (matching user header style)
    let profileDropdownTimeout = null;
    let notificationPreviewTimeout = null;
    const HOVER_DELAY = 200;
    
    const profileContainer = document.getElementById('profileDropdownContainer');
    const profileDropdown = document.getElementById('profileDropdown');
    const profileBtn = document.getElementById('profileDropdownBtn');
    
    if (profileContainer && profileDropdown && profileBtn) {
        profileContainer.addEventListener('mouseenter', function() {
            clearTimeout(profileDropdownTimeout);
            profileDropdown.classList.remove('hidden');
        });
        
        profileContainer.addEventListener('mouseleave', function() {
            profileDropdownTimeout = setTimeout(function() {
                profileDropdown.classList.add('hidden');
            }, HOVER_DELAY);
        });
        
        profileDropdown.addEventListener('mouseenter', function() {
            clearTimeout(profileDropdownTimeout);
        });
        
        profileDropdown.addEventListener('mouseleave', function() {
            profileDropdownTimeout = setTimeout(function() {
                profileDropdown.classList.add('hidden');
            }, HOVER_DELAY);
        });
        
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('hidden');
        });
        
        document.addEventListener('click', function() {
            if (!profileDropdown.classList.contains('hidden')) {
                profileDropdown.classList.add('hidden');
            }
        });
    }

    const notificationContainer = document.getElementById('notificationPreviewContainer');
    const notificationPreview = document.getElementById('notificationPreview');
    if (notificationContainer && notificationPreview) {
        notificationContainer.addEventListener('mouseenter', function() { clearTimeout(notificationPreviewTimeout); loadRetailerNotificationPreview(); notificationPreview.classList.remove('hidden'); });
        notificationContainer.addEventListener('mouseleave', function() { notificationPreviewTimeout = setTimeout(() => notificationPreview.classList.add('hidden'), HOVER_DELAY); });
        notificationPreview.addEventListener('mouseenter', () => clearTimeout(notificationPreviewTimeout));
        notificationPreview.addEventListener('mouseleave', () => { notificationPreviewTimeout = setTimeout(() => notificationPreview.classList.add('hidden'), HOVER_DELAY); });
    }
    function loadRetailerNotificationBadge() { const badge = document.getElementById('notificationBadge'); if (!badge) return; fetch('../api/get-retailer-notifications.php').then(r => r.json()).then(d => { if (d.success && d.notifications) { const c = d.unreadCount || 0; if (c > 0) { badge.textContent = c; badge.classList.remove('hidden'); } else { badge.classList.add('hidden'); }}}).catch(e => console.error(e)); }
    function loadRetailerNotificationPreview() { const items = document.getElementById('notificationPreviewItems'); if (!items) return; fetch('../api/get-retailer-notifications.php').then(r => r.json()).then(d => { if (d.success && d.notifications && d.notifications.length > 0) { items.innerHTML = d.notifications.slice(0, 5).map(n => { const unread = !n.read ? 'bg-green-50 border-l-4 border-green-500' : ''; const time = getTimeAgo(new Date(n.timestamp)); let icon = 'fa-info-circle', bg = 'bg-blue-100', tc = 'text-blue-700'; if (n.type === 'order') { icon = 'fa-box'; bg = 'bg-green-100'; tc = 'text-green-700'; } else if (n.type === 'stock') { icon = 'fa-exclamation-triangle'; bg = 'bg-yellow-100'; tc = 'text-yellow-700'; } else if (n.type === 'review') { icon = 'fa-star'; bg = 'bg-yellow-100'; tc = 'text-yellow-700'; } const t = escapeHtml(n.title || 'Notification'); const m = escapeHtml(n.message || ''); const l = n.link || 'retailernotifications.php'; return `<a href="${l}" class="block p-3 border-b border-gray-100 hover:bg-gray-50 transition ${unread}" data-notification-id="${n.id}" onclick="markNotificationAsRead(event, ${n.id})"><div class="flex items-start gap-3"><div class="${bg} ${tc} p-2 rounded-full flex-shrink-0"><i class="fas ${icon} text-sm"></i></div><div class="flex-1 min-w-0"><p class="font-medium text-gray-800 text-sm truncate">${t}</p><p class="text-xs text-gray-500 mt-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${m}</p><span class="text-xs text-gray-400 block mt-1">${time}</span></div>${!n.read ? '<div class="w-2 h-2 bg-green-500 rounded-full flex-shrink-0 mt-2"></div>' : ''}</div></a>`; }).join(''); } else { items.innerHTML = '<div class="p-8 text-center text-gray-500"><i class="fas fa-bell text-4xl mb-2 text-gray-300"></i><p class="text-sm">No notifications</p></div>'; }}).catch(e => console.error(e)); }
    function getTimeAgo(date) { const s = Math.floor((new Date() - date) / 1000); if (s < 60) return 'Just now'; if (s < 3600) return `${Math.floor(s / 60)}m ago`; if (s < 86400) return `${Math.floor(s / 3600)}h ago`; if (s < 604800) return `${Math.floor(s / 86400)}d ago`; return date.toLocaleDateString(); }
    function escapeHtml(text) { const div = document.createElement('div'); div.textContent = text; return div.innerHTML; }
    function markNotificationAsRead(event, notificationId) { fetch('../api/mark-retailer-notification-read.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ notification_id: notificationId }) }).then(response => response.json()).then(data => { if (data.success) { setTimeout(() => { loadRetailerNotificationBadge(); }, 100); } }).catch(error => console.error('Error marking notification as read:', error)); }
    // Load notifications immediately on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadRetailerNotificationBadge();
    });
    // Also call immediately in case DOM is already loaded
    if (document.readyState === 'loading') {
        // DOM is still loading, wait for DOMContentLoaded
    } else {
        // DOM is already loaded, execute immediately
        loadRetailerNotificationBadge();
    }
    setInterval(loadRetailerNotificationBadge, 5000);

    // Real-time profile picture updates
    let lastProfilePicture = document.getElementById('headerProfilePic')?.src || '';
        
    async function checkProfileUpdates() {
        try {
            const response = await fetch('../api/get-profile.php');
            if (response.ok) {
                const result = await response.json();
                const profilePicElement = document.getElementById('headerProfilePic');
                
                if (result.success && result.data && profilePicElement) {
                    const newProfilePic = result.data.profile_picture;
                    
                    // Only update if the picture has actually changed
                    if (newProfilePic && newProfilePic !== lastProfilePicture) {
                        profilePicElement.src = result.data.profile_picture + '?t=' + new Date().getTime();
                        lastProfilePicture = result.data.profile_picture;
                        console.log('Profile picture updated in real-time');
                    }
                    
                    // Update title attribute with user's name
                    if (result.data.full_name) {
                        profilePicElement.parentElement.setAttribute('title', result.data.full_name);
                    }
                }
            }
        } catch (error) {
            console.error('Error checking profile updates:', error);
        }
    }
    
    // Check for updates every 5 seconds
    setInterval(checkProfileUpdates, 5000);
    
    // Also check when user returns to the page
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            checkProfileUpdates();
        }
    });
    
    // Check when page loads
    window.addEventListener('load', () => {
        setTimeout(checkProfileUpdates, 1000);
    });
      
    function toggleMobileMenu() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
    
    document.querySelectorAll('#sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                toggleMobileMenu();
            }
        });
    });
</script>
</body>
</html>
