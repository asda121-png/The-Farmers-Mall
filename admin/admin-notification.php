<?php
// admin-notification.php

// In a real application, you would fetch these from a database.
// For consistency, we'll use the same mock data as the dashboard.
$notifications = [
    [
        "id" => "N001", "type" => "New User", "icon" => "fa-user-plus", "color" => "green",
        "title" => "New Customer Registered", "message" => "Alex Reyes has created an account.",
        "time" => "15m ago", "read" => false
    ],
    [
        "id" => "N002", "type" => "New Order", "icon" => "fa-receipt", "color" => "blue",
        "title" => "New Order #ORD-006", "message" => "An order amounting to ₱1,250 has been placed.",
        "time" => "1h ago", "read" => false
    ],
    [
        "id" => "N003", "type" => "Low Stock", "icon" => "fa-box-open", "color" => "yellow",
        "title" => "Low Stock Warning", "message" => "'Organic Apples' are running low.",
        "time" => "3h ago", "read" => true
    ],
    [
        "id" => "N004", "type" => "System Alert", "icon" => "fa-shield-halved", "color" => "red",
        "title" => "System Maintenance Scheduled", "message" => "A system-wide maintenance is scheduled for tonight.",
        "time" => "1d ago", "read" => true
    ],
    [
        "id" => "N005", "type" => "New Review", "icon" => "fa-star", "color" => "purple",
        "title" => "New Product Review", "message" => "A 5-star review was left for 'Fresh Carrots'.",
        "time" => "2d ago", "read" => true
    ],
    [
        "id" => "N006", "type" => "New Order", "icon" => "fa-receipt", "color" => "blue",
        "title" => "New Order #ORD-007", "message" => "An order amounting to ₱850 has been placed.",
        "time" => "2d ago", "read" => true
    ],
];

// When this page is loaded, we consider all notifications "seen".
// In a real app, you would run a DB query here: UPDATE notifications SET read = true;
// For our mock data, we will just render them without the 'unread' style.

$admin_name = "Admin User";
$admin_email = "admin@farmersmall.com";

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Notifications – Farmers Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="admin-theme.css">
  <style>
    .card-shadow { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05); }
    .bg-green-950 { background-color: #184D34; }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #4b5563; border-radius: 2px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
  </style>
</head>

<body class="bg-gray-50 text-gray-800 flex h-screen overflow-hidden">

  <!-- Sidebar -->
  <aside class="w-64 flex flex-col justify-between p-4 bg-green-950 text-gray-100 rounded-r-xl shadow-2xl transition-all duration-300 overflow-y-auto">
    <div>
      <div class="flex items-center gap-3 mb-8 px-2 py-2">
        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-white">
          <i class="fas fa-leaf text-green-600 text-2xl"></i>
        </div>
        <h1 class="text-xl font-bold">Farmers Mall</h1>
      </div>
      <p class="text-xs font-semibold text-green-300 uppercase tracking-widest mb-2 px-2">GENERAL</p>
      <nav class="space-y-1">
        <a href="admin-dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-tachometer-alt w-5"></i>
          <span>Dashboard</span>
        </a>
        <a href="admin-orders.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-receipt w-5"></i>
          <span>Orders</span>
        </a>
        <a href="admin-manage-users.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-user-gear w-5"></i>
          <span>Manage Users</span>
        </a>
      </nav>
      <p class="text-xs font-semibold text-green-300 uppercase tracking-widest my-4 px-2">ACCOUNT</p>
      <nav class="space-y-1">
        <a href="admin-settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-cog w-5"></i>
          <span>Settings</span>
        </a>
      </nav>
    </div>
    <div class="mt-8 pt-4 border-t border-green-800">
      <button id="logoutButton" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-red-600 hover:text-white transition-colors duration-200 text-gray-300">
        <i class="fa-solid fa-sign-out-alt w-5"></i>
        <span>Logout</span>
      </button>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="flex-1 custom-scrollbar overflow-y-auto">
    <!-- Header -->
    <div class="p-6">
      <header class="bg-white p-4 rounded-xl card-shadow flex justify-between items-center sticky top-0 z-10">
        <h2 class="text-2xl font-bold text-gray-900">All Notifications</h2>
        <div class="flex items-center gap-4 ml-auto">
          <a href="admin-settings.php" class="flex items-center gap-2 cursor-pointer">
            <img src="https://randomuser.me/api/portraits/men/40.jpg" class="w-9 h-9 rounded-full border-2 border-green-500" alt="Admin">
          </a>
        </div>
      </header>

      <!-- Content -->
      <main class="pt-6 space-y-6">
        <div class="bg-white rounded-xl card-shadow divide-y divide-gray-100">
          <?php if (empty($notifications)): ?>
            <div class="p-8 text-center text-gray-500">
              <i class="fa-solid fa-bell-slash text-4xl mb-4"></i>
              <p>No notifications yet.</p>
            </div>
          <?php else: ?>
            <?php foreach ($notifications as $notif): ?>
            <div class="notification-item p-5 flex items-start gap-4 hover:bg-green-50 transition-colors">
              <div class="w-10 h-10 rounded-full bg-<?php echo $notif['color']; ?>-100 flex-shrink-0 flex items-center justify-center text-<?php echo $notif['color']; ?>-600">
                  <i class="fa-solid <?php echo $notif['icon']; ?>"></i>
              </div>
              <a href="#" class="flex-1 cursor-pointer">
                <p class="font-semibold text-gray-800"><?php echo $notif['title']; ?></p>
                <p class="text-sm text-gray-600"><?php echo $notif['message']; ?></p>
                <p class="text-xs text-gray-400 mt-1"><?php echo $notif['time']; ?></p>
              </a>
              <button class="remove-notification-btn text-gray-400 hover:text-red-500 text-sm" title="Dismiss">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </main>
    </div>
  </div>

  <!-- Logout Modal (for consistency) -->
  <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl card-shadow p-8 w-full max-w-sm text-center">
      <div class="text-red-500 text-4xl mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <h3 class="font-bold text-xl mb-2 text-gray-900">Confirm Logout</h3>
      <p class="text-gray-600 text-sm mb-6">Are you sure you want to log out?</p>
      <div class="flex justify-center gap-4">
        <button id="cancelLogout" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
        <a href="../auth/login.php" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Logout</a>
      </div>
    </div>
  </div>
</body>
<script src="admin-theme.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const logoutButton = document.getElementById('logoutButton');
    const logoutModal = document.getElementById('logoutModal');
    const cancelLogout = document.getElementById('cancelLogout');
    logoutButton.addEventListener('click', () => logoutModal.classList.replace('hidden', 'flex'));
    cancelLogout.addEventListener('click', () => logoutModal.classList.replace('flex', 'hidden'));
    logoutModal.addEventListener('click', (e) => { 
      if (e.target === logoutModal) logoutModal.classList.replace('flex', 'hidden'); 
    });

    document.querySelector('main').addEventListener('click', (e) => {
      const removeBtn = e.target.closest('.remove-notification-btn');
      if (removeBtn) {
        const item = removeBtn.closest('.notification-item');
        item.style.transition = 'opacity 0.3s ease, max-height 0.3s ease, padding 0.3s ease, margin 0.3s ease';
        item.style.opacity = '0';
        setTimeout(() => item.remove(), 300);
      }
    });
  });
</script>
</html>
