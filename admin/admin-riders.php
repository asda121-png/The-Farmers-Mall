<?php
require_once __DIR__ . '/../config/supabase-api.php';

// Initialize Supabase API
$api = getSupabaseAPI();

$riders = [];

// Mock Data for Riders
$riders = [
    [
        "id" => "RDR-001",
        "name" => "Michael Jordan",
        "email" => "mike.j@delivery.com",
        "phone" => "0917-123-4567",
        "vehicle" => "Motorcycle",
        "plate_number" => "XYZ-123",
        "area" => "Downtown",
        "status" => "Available",
        "rating" => 4.8,
        "joined" => "2023-01-15",
        "avatar" => "https://randomuser.me/api/portraits/men/32.jpg"
    ],
    [
        "id" => "RDR-002",
        "name" => "Sarah Connor",
        "email" => "sarah.c@delivery.com",
        "phone" => "0918-987-6543",
        "vehicle" => "Van",
        "plate_number" => "ABC-987",
        "area" => "North District",
        "status" => "Busy",
        "rating" => 4.9,
        "joined" => "2023-03-10",
        "avatar" => "https://randomuser.me/api/portraits/women/44.jpg"
    ],
    [
        "id" => "RDR-003",
        "name" => "Bruce Wayne",
        "email" => "bruce@wayne.com",
        "phone" => "0922-555-0000",
        "vehicle" => "Motorcycle",
        "plate_number" => "BAT-001",
        "area" => "Gotham Suburb",
        "status" => "Offline",
        "rating" => 5.0,
        "joined" => "2022-11-05",
        "avatar" => "https://randomuser.me/api/portraits/men/85.jpg"
    ],
    [
        "id" => "RDR-004",
        "name" => "Clark Kent",
        "email" => "clark@daily.com",
        "phone" => "0917-777-7777",
        "vehicle" => "Bike",
        "plate_number" => "N/A",
        "area" => "Uptown",
        "status" => "Available",
        "rating" => 4.7,
        "joined" => "2023-05-20",
        "avatar" => "https://randomuser.me/api/portraits/men/12.jpg"
    ],
    [
        "id" => "RDR-005",
        "name" => "Diana Prince",
        "email" => "diana@amazon.com",
        "phone" => "0919-888-9999",
        "vehicle" => "Motorcycle",
        "plate_number" => "WW-84",
        "area" => "Westside",
        "status" => "Pending",
        "rating" => 0.0,
        "joined" => "2023-10-01",
        "avatar" => "https://randomuser.me/api/portraits/women/65.jpg"
    ]
];

// Mock Notifications
$notifications = [
    ["icon" => "fa-user-plus", "color" => "green", "title" => "New Rider Application", "message" => "Diana Prince applied to be a rider.", "time" => "2h ago", "read" => false],
    ["icon" => "fa-triangle-exclamation", "color" => "red", "title" => "Rider Report", "message" => "Rider #002 reported a breakdown.", "time" => "5h ago", "read" => true],
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farmers Mall Admin Panel - Riders</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="admin-theme.css">
  <style>
    /* Global Styles */
    body {
      font-family: 'Inter', sans-serif;
      background-color: #ffffff;
    }

    .custom-scrollbar::-webkit-scrollbar {
      width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
      background-color: #4b5563;
      border-radius: 2px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
      background: transparent;
    }

    .card-shadow {
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
    }
    
    .bg-green-950 {
        background-color: #184D34;
    }

    /* Modal Animation */
    .modal-enter {
        opacity: 0;
        transform: scale(0.95);
    }
    .modal-enter-active {
        opacity: 1;
        transform: scale(1);
        transition: opacity 300ms, transform 300ms;
    }
    .modal-exit {
        opacity: 1;
        transform: scale(1);
    }
    .modal-exit-active {
        opacity: 0;
        transform: scale(0.95);
        transition: opacity 200ms, transform 200ms;
    }

    .pagination-btn {
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        background-color: white;
        color: #374151;
        font-size: 0.875rem;
        border-radius: 0.375rem;
        transition: all 0.2s;
    }
    .pagination-btn:hover {
        background-color: #f3f4f6;
    }
    .pagination-btn.active {
        background-color: #15803d;
        color: white;
        border-color: #15803d;
    }
    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
  </style>
</head>

<body class="flex min-h-screen bg-white text-gray-800 dark:bg-gray-900 dark:text-gray-200">

  <!-- SIDEBAR: Sticky and Fixed -->
  <aside class="w-64 flex flex-col justify-between p-4 bg-green-950 text-gray-100 rounded-r-xl shadow-2xl transition-all duration-300 sticky top-0 h-screen overflow-y-auto">
    <div>
      <div class="flex items-center gap-3 mb-8 px-2 py-2">
        <div class="w-8 h-8 flex items-center justify-center rounded-full bg-white">
          <i class="fas fa-leaf text-green-700 text-lg"></i>
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
        <!-- UPDATED: Added 'bg-green-700 text-white font-semibold card-shadow' to make it Active -->
        <a href="admin-riders.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white bg-green-700 font-semibold card-shadow">
          <i class="fa-solid fa-motorcycle w-5 text-green-200"></i>
          <span>Riders</span>
        </a>
      </nav>

      <p class="text-xs font-semibold text-green-300 uppercase tracking-widest my-4 px-2">ACCOUNT</p>
      <nav class="space-y-1">
        <a href="admin-settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-cog w-5"></i>
          <span>Settings</span>
        </a>
      
        <a href="admin-manage-users.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-user-gear w-5"></i>
          <span>Manage Users</span>
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

  <!-- MAIN CONTENT -->
  <div class="flex-1 p-6 space-y-6 custom-scrollbar">

    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 p-4 rounded-xl card-shadow flex justify-between items-center sticky top-6 z-10 w-full">
      <div class="relative w-full max-w-lg hidden md:block">
        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        <input type="text" id="search-input" placeholder="Search rider name, ID, or plate number..."
          class="w-full py-2 pl-10 pr-4 border border-gray-200 rounded-lg focus:ring-green-500 focus:border-green-500 transition-colors">
      </div>

      <div class="flex items-center gap-4 ml-auto">
        <!-- Notification Dropdown -->
        <div class="relative">
            <button id="notification-btn" class="relative" title="View Notifications">
                <i class="fa-regular fa-bell text-xl text-gray-500 hover:text-green-600 cursor-pointer"></i>
                <span class="absolute -top-1 -right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
            </button>
            <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 z-20">
                <div class="p-4 border-b">
                    <h4 class="font-bold text-gray-800">Notifications</h4>
                </div>
                <div id="notification-list" class="max-h-80 overflow-y-auto custom-scrollbar transition-all duration-300">
                    <?php foreach($notifications as $notif): ?>
                    <div class="notification-item flex items-start gap-3 p-4 hover:bg-green-50 <?php echo !$notif['read'] ? 'bg-green-50' : ''; ?>" data-read="<?php echo $notif['read'] ? 'true' : 'false'; ?>">
                        <div class="w-8 h-8 rounded-full bg-<?php echo $notif['color']; ?>-100 flex-shrink-0 flex items-center justify-center text-<?php echo $notif['color']; ?>-600">
                            <i class="fa-solid <?php echo $notif['icon']; ?> text-sm"></i>
                        </div>
                        <a href="#" class="flex-1 cursor-pointer">
                            <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($notif['title']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($notif['message']); ?></p>
                            <p class="text-xs text-gray-400 mt-1"><?php echo htmlspecialchars($notif['time']); ?></p>
                        </a>
                        <button class="remove-notification-btn text-gray-400 hover:text-red-500 transition-colors" title="Remove notification"><i class="fa-solid fa-times text-xs"></i></button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="p-2 border-t"><a href="#" id="view-all-notifications-btn" class="block w-full text-center text-sm font-medium text-green-600 hover:bg-gray-100 rounded-lg py-2">View all notifications</a></div>
            </div>
        </div>
        <div class="w-px h-6 bg-gray-200 mx-2 hidden sm:block"></div>
        <a href="admin-settings.php" class="flex items-center gap-2 cursor-pointer">
          <img src="https://randomuser.me/api/portraits/men/40.jpg" class="w-9 h-9 rounded-full border-2 border-green-500" alt="Admin">
        </a>
      </div>
    </header>

    <!-- Page Title & Actions -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Riders Management</h2>
            <p class="text-sm text-gray-500">Manage delivery personnel, track status, and verify applicants</p>
        </div>
        <div class="flex gap-3">
            <button id="add-rider-btn" class="flex items-center gap-2 px-4 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 shadow-lg shadow-green-700/30 transition-colors">
                <i class="fa-solid fa-plus"></i> Add New Rider
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl card-shadow flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Riders</p>
                <h4 class="text-2xl font-bold text-gray-800">128</h4>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl card-shadow flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Active Now</p>
                <h4 class="text-2xl font-bold text-green-600">45</h4>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                <i class="fa-solid fa-motorcycle"></i>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl card-shadow flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">On Delivery</p>
                <h4 class="text-2xl font-bold text-blue-600">12</h4>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                <i class="fa-solid fa-box"></i>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl card-shadow flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Pending Approval</p>
                <h4 class="text-2xl font-bold text-orange-500">8</h4>
            </div>
            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-orange-600">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white rounded-xl card-shadow overflow-hidden">

        <!-- Filter Bar -->
        <div class="p-4 border-b border-gray-200 flex flex-wrap gap-4 items-center justify-between bg-gray-50">
             <div class="flex space-x-2">
                <button class="filter-tab active px-4 py-2 text-sm font-semibold rounded-lg bg-green-700 text-white transition-colors" data-status="All">All Riders</button>
                <button class="filter-tab px-4 py-2 text-sm font-semibold rounded-lg bg-white text-gray-600 hover:bg-gray-200 transition-colors" data-status="Available">Available</button>
                <button class="filter-tab px-4 py-2 text-sm font-semibold rounded-lg bg-white text-gray-600 hover:bg-gray-200 transition-colors" data-status="Busy">On Duty</button>
                <button class="filter-tab px-4 py-2 text-sm font-semibold rounded-lg bg-white text-gray-600 hover:bg-gray-200 transition-colors" data-status="Pending">Pending</button>
             </div>

             <div class="flex items-center gap-2">
                <select id="vehicle-filter" class="text-sm border-gray-300 border rounded-lg p-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">All Vehicles</option>
                    <option value="Motorcycle">Motorcycle</option>
                    <option value="Van">Van</option>
                    <option value="Bike">Bike</option>
                </select>
             </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rider Profile</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Vehicle Info</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Service Area</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rating</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="riders-table-body" class="bg-white divide-y divide-gray-200">
                    <!-- JS will populate this -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 bg-gray-50 border-t border-gray-200 flex justify-end">
            <nav class="pagination flex gap-2" id="riders-pagination"></nav>
        </div>
    </div>

  </div> 

  <!-- ================= MODALS ================= -->

  <!-- 1. Add Rider Modal -->
  <div id="add-rider-modal" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-xl card-shadow w-full max-w-2xl transform transition-all scale-95 opacity-0 modal-content">
      <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-xl">
          <h3 class="font-bold text-xl text-gray-800">Register New Rider</h3>
          <button class="modal-close-btn text-gray-400 hover:text-gray-600 transition-colors"><i class="fa-solid fa-xmark text-xl"></i></button>
      </div>
      <form id="add-rider-form" class="p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <input type="tel" name="phone" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Area / Zone</label>
                <input type="text" name="area" placeholder="e.g., Downtown" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Type</label>
                <select name="vehicle" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                    <option value="Motorcycle">Motorcycle</option>
                    <option value="Van">Van</option>
                    <option value="Bike">Bicycle</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">License Plate</label>
                <input type="text" name="plate" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
        </div>
        <div class="pt-4 flex justify-end gap-3">
            <button type="button" class="modal-close-btn px-5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit" class="px-5 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 shadow-md">Add Rider</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 2. Edit Rider Modal -->
  <div id="edit-rider-modal" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
    <div class="bg-white rounded-xl card-shadow w-full max-w-2xl transform transition-all scale-95 opacity-0 modal-content">
      <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50 rounded-t-xl">
          <h3 class="font-bold text-xl text-gray-800">Edit Rider Details</h3>
          <button class="modal-close-btn text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
      </div>
      <form id="edit-rider-form" class="p-6 space-y-4">
        <input type="hidden" id="edit-rider-id">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" id="edit-name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="edit-status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                    <option value="Available">Available</option>
                    <option value="Busy">Busy (On Duty)</option>
                    <option value="Offline">Offline</option>
                    <option value="Pending">Pending Approval</option>
                    <option value="Suspended">Suspended</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="tel" id="edit-phone" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vehicle Info</label>
                <input type="text" id="edit-vehicle" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
        </div>
        <div class="pt-4 flex justify-end gap-3">
            <button type="button" class="modal-close-btn px-5 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
            <button type="submit" class="px-5 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 shadow-md">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- 3. Confirmation Notification Modal (The "Interface" you requested) -->
  <div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
      <div class="bg-white rounded-2xl card-shadow w-full max-w-sm transform transition-all scale-95 opacity-0 modal-content text-center p-8">
          <!-- Icon -->
          <div id="confirm-icon-bg" class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
              <i id="confirm-icon" class="fa-solid fa-trash-can text-3xl text-red-600"></i>
          </div>
          
          <!-- Text -->
          <h3 id="confirm-title" class="text-xl font-bold text-gray-900 mb-2">Delete Rider?</h3>
          <p id="confirm-message" class="text-sm text-gray-500 mb-8">Are you sure you want to delete this rider? This action cannot be undone.</p>
          
          <!-- Actions -->
          <div class="flex flex-col gap-3">
              <button id="confirm-btn-yes" class="w-full py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                  Yes, Delete it
              </button>
              <button id="confirm-btn-no" class="w-full py-2.5 px-4 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                  Cancel
              </button>
          </div>
      </div>
  </div>

  <!-- 4. View Rider Profile Modal -->
  <div id="view-rider-modal" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center z-50 p-4 backdrop-blur-sm">
      <div class="bg-white rounded-xl card-shadow w-full max-w-lg transform transition-all scale-95 opacity-0 modal-content overflow-hidden">
          <div class="h-24 bg-gradient-to-r from-green-800 to-green-600"></div>
          <div class="px-8 pb-8">
              <div class="relative flex justify-between items-end -mt-12 mb-6">
                  <img id="view-avatar" src="" alt="Rider" class="w-24 h-24 rounded-full border-4 border-white shadow-md bg-white">
                  <span id="view-status-badge" class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 uppercase tracking-wide">Available</span>
              </div>
              <h2 id="view-name" class="text-2xl font-bold text-gray-900">Rider Name</h2>
              <p id="view-id" class="text-sm text-gray-500 mb-6">ID: RDR-000</p>
              
              <div class="space-y-4">
                  <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                      <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm text-green-600"><i class="fa-solid fa-phone"></i></div>
                      <div>
                          <p class="text-xs text-gray-500">Contact Number</p>
                          <p id="view-phone" class="font-semibold text-gray-800">--</p>
                      </div>
                  </div>
                  <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                      <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm text-blue-600"><i class="fa-solid fa-truck"></i></div>
                      <div>
                          <p class="text-xs text-gray-500">Vehicle & Plate</p>
                          <p id="view-vehicle" class="font-semibold text-gray-800">--</p>
                      </div>
                  </div>
                  <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                      <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm text-orange-500"><i class="fa-solid fa-map-location-dot"></i></div>
                      <div>
                          <p class="text-xs text-gray-500">Assigned Area</p>
                          <p id="view-area" class="font-semibold text-gray-800">--</p>
                      </div>
                  </div>
              </div>

              <div class="mt-8 flex justify-end">
                  <button class="modal-close-btn px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 dark:bg-gray-900 dark:hover:bg-gray-800 transition-colors">Close</button>
              </div>
          </div>
      </div>
  </div>

  <!-- JavaScript Application Logic -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 1. State Management ---
        const ridersData = <?php echo json_encode($riders); ?>;
        
        const state = {
            riders: ridersData,
            filterStatus: 'All',
            filterVehicle: '',
            searchQuery: '',
            currentPage: 1,
            itemsPerPage: 10,
            
            // For Confirmation Modal
            pendingAction: null, 
            pendingId: null
        };

        // --- 2. DOM Elements ---
        const tableBody = document.getElementById('riders-table-body');
        const paginationContainer = document.getElementById('riders-pagination');
        const searchInput = document.getElementById('search-input');
        const vehicleFilter = document.getElementById('vehicle-filter');
        const statusTabs = document.querySelectorAll('.filter-tab');
        
        // Modals
        const addModal = document.getElementById('add-rider-modal');
        const editModal = document.getElementById('edit-rider-modal');
        const confirmModal = document.getElementById('confirm-modal');
        const viewModal = document.getElementById('view-rider-modal');

        // --- 3. Render Functions ---
        
        function renderTable() {
            // Filter Data
            let filtered = state.riders.filter(rider => {
                const matchesStatus = state.filterStatus === 'All' || rider.status === state.filterStatus;
                const matchesVehicle = state.filterVehicle === '' || rider.vehicle === state.filterVehicle;
                const matchesSearch = rider.name.toLowerCase().includes(state.searchQuery) || 
                                      rider.id.toLowerCase().includes(state.searchQuery) || 
                                      rider.plate_number.toLowerCase().includes(state.searchQuery);
                return matchesStatus && matchesVehicle && matchesSearch;
            });

            // Pagination Logic
            const totalPages = Math.ceil(filtered.length / state.itemsPerPage);
            if (state.currentPage > totalPages) state.currentPage = 1; // Reset if out of bounds
            
            const start = (state.currentPage - 1) * state.itemsPerPage;
            const end = start + state.itemsPerPage;
            const pageData = filtered.slice(start, end);

            // Generate HTML
            tableBody.innerHTML = pageData.map(rider => {
                // Status Styling
                let statusClass = 'bg-gray-100 text-gray-800';
                if(rider.status === 'Available') statusClass = 'bg-green-100 text-green-800 ring-1 ring-green-600/20';
                if(rider.status === 'Busy') statusClass = 'bg-blue-100 text-blue-800 ring-1 ring-blue-600/20';
                if(rider.status === 'Offline') statusClass = 'bg-gray-100 text-gray-500 ring-1 ring-gray-600/20';
                if(rider.status === 'Pending') statusClass = 'bg-yellow-100 text-yellow-800 ring-1 ring-yellow-600/20';
                if(rider.status === 'Suspended') statusClass = 'bg-red-100 text-red-800 ring-1 ring-red-600/20';

                // Rating Stars
                const stars = Array(5).fill(0).map((_, i) => 
                    `<i class="fa-solid fa-star text-[10px] ${i < Math.round(rider.rating) ? 'text-yellow-400' : 'text-gray-300'}"></i>`
                ).join('');

                return `
                <tr class="hover:bg-gray-50 transition-colors group">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <img class="h-10 w-10 rounded-full object-cover border-2 border-white shadow-sm" src="${rider.avatar}" alt="">
                            <div class="ml-4">
                                <div class="text-sm font-bold text-gray-900">${rider.name}</div>
                                <div class="text-xs text-gray-500">${rider.id}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 font-medium">${rider.vehicle}</div>
                        <div class="text-xs text-gray-500 bg-gray-100 inline-block px-1.5 rounded border border-gray-200">${rider.plate_number}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-700"><i class="fa-solid fa-location-dot text-gray-400 mr-1"></i> ${rider.area}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full ${statusClass}">
                            ${rider.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-gray-800">${rider.rating > 0 ? rider.rating : 'New'}</span>
                            <div class="flex">${stars}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                            <button class="action-btn-view p-2 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-full transition-colors" data-id="${rider.id}" title="View Details">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button class="action-btn-edit p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-full transition-colors" data-id="${rider.id}" title="Edit Rider">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="action-btn-delete p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-colors" data-id="${rider.id}" title="Delete Rider">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                `;
            }).join('');
            
            if(filtered.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500 italic">No riders found matching your criteria.</td></tr>`;
            }

            renderPagination(totalPages);
        }

        function renderPagination(totalPages) {
            let html = '';
            if (totalPages > 1) {
                // Prev
                html += `<button class="pagination-btn" onclick="app.setPage(${state.currentPage - 1})" ${state.currentPage === 1 ? 'disabled' : ''}><i class="fa-solid fa-chevron-left"></i></button>`;
                
                for (let i = 1; i <= totalPages; i++) {
                     if (i === 1 || i === totalPages || (i >= state.currentPage - 1 && i <= state.currentPage + 1)) {
                        html += `<button class="pagination-btn ${i === state.currentPage ? 'active' : ''}" onclick="app.setPage(${i})">${i}</button>`;
                    } else if (i === state.currentPage - 2 || i === state.currentPage + 2) {
                        html += `<span class="px-2 text-gray-400">...</span>`;
                    }
                }
                
                // Next
                html += `<button class="pagination-btn" onclick="app.setPage(${state.currentPage + 1})" ${state.currentPage === totalPages ? 'disabled' : ''}><i class="fa-solid fa-chevron-right"></i></button>`;
            }
            paginationContainer.innerHTML = html;
        }

        // --- 4. Modal Functions (Transitions & Logic) ---

        function openModal(modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            // Small delay to allow display:flex to apply before adding opacity
            setTimeout(() => {
                const content = modal.querySelector('.modal-content');
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeModal(modal) {
            const content = modal.querySelector('.modal-content');
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 200); // Match CSS transition duration
        }

        // --- 5. Event Listeners ---

        // Filters
        searchInput.addEventListener('input', (e) => { state.searchQuery = e.target.value.toLowerCase(); state.currentPage = 1; renderTable(); });
        vehicleFilter.addEventListener('change', (e) => { state.filterVehicle = e.target.value; state.currentPage = 1; renderTable(); });
        
        statusTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                statusTabs.forEach(t => { t.classList.remove('bg-green-700', 'text-white'); t.classList.add('bg-white', 'text-gray-600', 'hover:bg-gray-200'); });
                tab.classList.remove('bg-white', 'text-gray-600', 'hover:bg-gray-200');
                tab.classList.add('bg-green-700', 'text-white');
                state.filterStatus = tab.dataset.status;
                state.currentPage = 1;
                renderTable();
            });
        });

        // Global Click for Modal Closing
        document.querySelectorAll('.modal-close-btn, #confirm-btn-no').forEach(btn => {
            btn.addEventListener('click', (e) => {
                closeModal(e.target.closest('.fixed'));
            });
        });

        // Add Rider
        document.getElementById('add-rider-btn').addEventListener('click', () => openModal(addModal));
        
        document.getElementById('add-rider-form').addEventListener('submit', (e) => {
            e.preventDefault();
            // In a real app, you'd gather FormData and send to API
            alert("Rider Added Successfully! (Simulation)");
            closeModal(addModal);
            e.target.reset();
            renderTable(); // Refresh
        });

        // Table Actions (Delegation)
        tableBody.addEventListener('click', (e) => {
            const btn = e.target.closest('button');
            if(!btn) return;
            const id = btn.dataset.id;
            const rider = state.riders.find(r => r.id === id);

            if(btn.classList.contains('action-btn-delete')) {
                // Setup Confirmation Interface
                state.pendingAction = 'delete';
                state.pendingId = id;
                document.getElementById('confirm-title').innerText = "Delete Rider?";
                document.getElementById('confirm-message').innerText = `Are you sure you want to remove ${rider.name}? This action is permanent.`;
                document.getElementById('confirm-btn-yes').innerText = "Yes, Delete";
                document.getElementById('confirm-btn-yes').className = "w-full py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-md";
                document.getElementById('confirm-icon').className = "fa-solid fa-trash-can text-3xl text-red-600";
                document.getElementById('confirm-icon-bg').className = "mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6";
                openModal(confirmModal);
            } 
            else if (btn.classList.contains('action-btn-edit')) {
                // Populate Edit Modal
                document.getElementById('edit-rider-id').value = rider.id;
                document.getElementById('edit-name').value = rider.name;
                document.getElementById('edit-phone').value = rider.phone;
                document.getElementById('edit-status').value = rider.status;
                document.getElementById('edit-vehicle').value = `${rider.vehicle} - ${rider.plate_number}`;
                openModal(editModal);
            }
            else if (btn.classList.contains('action-btn-view')) {
                // Populate View Modal
                document.getElementById('view-name').innerText = rider.name;
                document.getElementById('view-id').innerText = `ID: ${rider.id}`;
                document.getElementById('view-phone').innerText = rider.phone;
                document.getElementById('view-vehicle').innerText = `${rider.vehicle} (${rider.plate_number})`;
                document.getElementById('view-area').innerText = rider.area;
                document.getElementById('view-avatar').src = rider.avatar;
                
                const badge = document.getElementById('view-status-badge');
                badge.innerText = rider.status;
                // Simplified badge color logic for view modal
                badge.className = `px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide ${rider.status === 'Available' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`;
                
                openModal(viewModal);
            }
        });

        // Edit Form Submit
        document.getElementById('edit-rider-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const id = document.getElementById('edit-rider-id').value;
            const idx = state.riders.findIndex(r => r.id === id);
            if(idx !== -1) {
                // Simulate update
                state.riders[idx].name = document.getElementById('edit-name').value;
                state.riders[idx].status = document.getElementById('edit-status').value;
                state.riders[idx].phone = document.getElementById('edit-phone').value;
            }
            closeModal(editModal);
            renderTable();
        });

        // Confirmation "Yes" Button Logic
        document.getElementById('confirm-btn-yes').addEventListener('click', () => {
            if(state.pendingAction === 'delete') {
                state.riders = state.riders.filter(r => r.id !== state.pendingId);
                renderTable(); // Re-render to show row gone
            }
            closeModal(confirmModal);
            state.pendingAction = null;
            state.pendingId = null;
        });

        // Global functions for pagination onclick
        window.app = {
            setPage: (p) => { state.currentPage = p; renderTable(); }
        };

        // Initialize
        renderTable();
        
        // --- Sidebar Logout Logic ---
        const logoutBtn = document.getElementById('logoutButton');
        // We can reuse the same modal style for logout if we want, or create a specific one. 
        // For now, I'll hook it to a standard redirect or alert as per previous files
         logoutBtn.addEventListener('click', () => {
            if(confirm("Are you sure you want to logout?")) {
                window.location.href = '../auth/login.php';
            }
        });
        
        // --- Notification Logic (Copied from your snippets) ---
        const notifBtn = document.getElementById('notification-btn');
        const notifDrop = document.getElementById('notification-dropdown');
        const notifList = document.getElementById('notification-list');
        const viewAllNotif = document.getElementById('view-all-notifications-btn');

        notifList.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.remove-notification-btn');
            if (removeBtn) {
                e.stopPropagation();
                const notificationItem = removeBtn.closest('.notification-item');
                if (notificationItem) {
                    notificationItem.style.transition = 'opacity 0.3s ease';
                    notificationItem.style.opacity = '0';
                    setTimeout(() => notificationItem.remove(), 300);
                }
            }
            // Add "mark as read" logic here if needed
        });

        notifBtn.addEventListener('click', (e) => { e.stopPropagation(); notifDrop.classList.toggle('hidden'); });
        window.addEventListener('click', (e) => {
             if (!notifDrop.classList.contains('hidden') && !notifBtn.contains(e.target) && !notifDrop.contains(e.target)) {
                notifDrop.classList.add('hidden');
            }
        });
        viewAllNotif.addEventListener('click', (e) => {
             e.preventDefault();
             notifList.classList.replace('max-h-80', 'max-h-[60vh]');
             e.target.style.display = 'none';
        });
    });
  </script>
  <script src="admin-theme.js"></script>
</body>
</html>
