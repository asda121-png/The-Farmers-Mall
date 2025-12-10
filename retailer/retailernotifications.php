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
        /* Force sidebar to be full height with logout at bottom */
        #sidebar {
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
        }
        #sidebar > *:last-child {
            margin-top: auto !important;
            padding-top: 1rem !important;
            border-top: 1px solid #e5e7eb !important;
        }
        /* Mobile menu toggle */
        #mobileMenuBtn {
            display: none;
        }
        @media (max-width: 768px) {
            #mobileMenuBtn {
                display: flex;
            }
            #sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                height: 100vh;
                z-index: 50;
                transition: left 0.3s ease;
            }
        #sidebar {
            min-height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
        }
        #sidebar > div:last-child {
            margin-top: auto !important;
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
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
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
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 4h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Dashboard
            </a>
            <a href="retailerinventory.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0V9m0 2v2m-4-2h1m-1 0h-2m2 0v2m-2-2h-1m-1 0H5m-2 4h18m-9-4v8m-7 4h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Products & Inventory
            </a>
            <a href="retailerfulfillment.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5h6"></path></svg>
                Order Fulfillment
            </a>
            <a href="retailerfinance.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2M9 14h6m-5 4h4m-4-8h4m-5-8h6a2 2 0 012 2v10a2 2 0 01-2 2h-6a2 2 0 01-2-2V6a2 2 0 012-2z"></path></svg>
                Financial Reports
            </a>
            <!-- Vouchers & Coupons -->
            <a href="retailercoupons.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11l4-4-4-4m0 16l4-4-4-4m-1-5a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Vouchers & Coupons
            </a>
            <a href="retailerreviews.php" class="nav-item flex items-center p-3 rounded-xl text-gray-700 hover:bg-green-100 transition duration-150">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.193a2.003 2.003 0 013.902 0l1.018 2.062 2.277.33a2.003 2.003 0 011.11 3.407l-1.652 1.61.39 2.269a2.003 2.003 0 01-2.906 2.108L12 15.698l-2.035 1.071a2.003 2.003 0 01-2.906-2.108l.39-2.269-1.652-1.61a2.003 2.003 0 011.11-3.407l2.277-.33 1.018-2.062z"></path></svg>
                Reviews & Customers
            </a>

            <div class="mt-auto pt-4 border-t border-gray-100">
                <a href="../auth/logout.php" class="w-full flex items-center justify-center p-2 rounded-xl text-red-600 bg-red-50 hover:bg-red-100 transition duration-150 font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </a>
            </div>
        </nav>

        <div class="flex-1 flex flex-col min-h-screen">
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-end">
                    <div class="flex items-center space-x-6">
                        <a href="retailer-dashboard2.php" class="text-gray-600 hover:text-green-600"><i class="fa-solid fa-house"></i></a>
                        <a href="retailermessage.php" class="text-gray-600 hover:text-green-600"><i class="fa-regular fa-comment"></i></a>
                        <a href="retailernotifications.php" class="text-green-600 relative">
                        <i class="fa-solid fa-bell"></i>
                        <!-- Notification badge can be added here if needed -->
                        </a>

                        <!-- Profile Dropdown -->
                        <div class="relative inline-block text-left">
                            <button id="profileDropdownBtn" class="flex items-center" title="<?php echo htmlspecialchars($userFullName); ?>">
                                <img id="headerProfilePic" src="<?php echo htmlspecialchars($profilePicture); ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($userFullName); ?>" class="w-8 h-8 rounded-full cursor-pointer object-cover border-2 border-gray-200" onerror="this.src='../images/default-avatar.svg'">
                            </button>
                            <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-40 bg-white rounded-md shadow-lg border z-50">
                                <a href="retailerprofile.php" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
                                <a href="retailerprofile.php#settings" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
                                <a href="../auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
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

    // Profile dropdown toggle
    document.getElementById('profileDropdownBtn')?.addEventListener('click', function(e) {
      e.stopPropagation();
      document.getElementById('profileDropdown').classList.toggle('hidden');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      const dropdown = document.getElementById('profileDropdown');
      const btn = document.getElementById('profileDropdownBtn');
      if (dropdown && !dropdown.contains(e.target) && !btn.contains(e.target)) {
        dropdown.classList.add('hidden');
      }
    });

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
