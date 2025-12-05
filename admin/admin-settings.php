<?php
// admin-settings.php
// Mock User Data for Pre-filling forms
$user_settings = [
    "name" => "Admin User",
    "email" => "admin@farmersmall.com",
    "phone" => "+63 917 000 0000",
    "role" => "Super Admin",
    "notifications" => [
        "email_alerts" => true,
        "order_updates" => true,
        "new_retailers" => false,
        "marketing" => false
    ]
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Farmers Mall Admin Panel - Settings</title>
  <script>
    // Force Tailwind to use class-based dark mode and prevent system preference from applying
    window.tailwind = window.tailwind || {};
    tailwind.config = { darkMode: 'class' };
    document.documentElement.classList.remove('dark');
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="admin-theme.css">
  <style>
    /* Global Styles */
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f7f9fc;
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

    /* Toggle Switch Styles */
    .toggle-checkbox:checked {
      right: 0;
      border-color: #15803d;
    }
    .toggle-checkbox:checked + .toggle-label {
      background-color: #15803d;
    }

    /* Tab Styles */
    .tab-content {
        display: none;
        animation: fadeIn 0.5s;
    }
    .tab-content.active {
        display: block;
    }
    .tab-btn.active {
        border-bottom-color: #16a34a; /* green-600 */
        color: #16a34a;
        font-weight: 600;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>

<body class="flex min-h-screen bg-gray-50 text-gray-800">

  <aside class="w-64 flex flex-col justify-between p-4 bg-green-950 text-gray-100 rounded-r-xl shadow-2xl transition-all duration-300">
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
      </nav>

      <!-- UPDATED: Removed 'bg-green-700 text-white' to remove permanent highlight. Added hover effects. -->
        <a href="admin-riders.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-green-800 text-gray-300">
          <i class="fa-solid fa-motorcycle w-5"></i>
          <span>Riders</span>
        </a>
      </nav>

      <p class="text-xs font-semibold text-green-300 uppercase tracking-widest my-4 px-2">ACCOUNT</p>
      <nav class="space-y-1">
        <a href="admin-settings.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-white bg-green-700 font-semibold card-shadow">
          <i class="fa-solid fa-cog w-5 text-green-200"></i>
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

  <div class="flex-1 p-6 space-y-6 custom-scrollbar">

    <div>
        <h2 class="text-3xl font-bold text-gray-900">Settings</h2>
        <p class="text-sm text-gray-500">Manage your profile and system preferences</p>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button onclick="switchTab('profile')" class="tab-btn active whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700">
                Profile
            </button>
            <button onclick="switchTab('security')" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700">
                Security
            </button>
            <button onclick="switchTab('notifications')" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700">
                Notifications
            </button>
            <button onclick="switchTab('appearance')" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700">
                Appearance
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="pt-6">
        <!-- Profile Tab -->
        <div id="tab-profile" class="tab-content active">
            <div class="bg-white p-6 rounded-xl card-shadow">                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-3 text-center">
                        <div class="relative w-32 h-32 rounded-full mx-auto border-4 border-gray-100 overflow-hidden bg-gray-200 group">
                            <img src="https://randomuser.me/api/portraits/men/40.jpg" class="w-full h-full object-cover" alt="Profile" id="profile-image">
                            <label for="profileUpload" class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                                <i class="fa-solid fa-camera text-white text-2xl"></i>
                            </label>
                            <input type="file" id="profileUpload" class="hidden">
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mt-4"><?php echo $user_settings['name']; ?></h3>
                        <p class="text-sm text-gray-500"><?php echo $user_settings['role']; ?></p>
                    </div>
                    <div class="lg:col-span-9">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-900">Personal Information</h3>
                            <div id="profile-actions" class="flex gap-2">
                                <button id="edit-profile-btn" class="px-4 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 transition-colors">Edit Profile</button>
                                <button id="save-profile-btn" class="px-4 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 transition-colors hidden">Save Changes</button>
                            </div>
                        </div>
                        <form id="profile-form" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                            <input type="text" value="<?php echo $user_settings['name']; ?>" class="profile-input w-full p-2 border bg-gray-100 border-gray-300 rounded-lg text-sm" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                            <input type="text" value="<?php echo $user_settings['role']; ?>" class="w-full p-2 border bg-gray-100 border-gray-300 rounded-lg text-sm" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" value="<?php echo $user_settings['email']; ?>" class="profile-input w-full p-2 border bg-gray-100 border-gray-300 rounded-lg text-sm" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="text" value="<?php echo $user_settings['phone']; ?>" class="profile-input w-full p-2 border bg-gray-100 border-gray-300 rounded-lg text-sm" readonly>
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                            <textarea rows="3" class="profile-input w-full p-2 border bg-gray-100 border-gray-300 rounded-lg text-sm" placeholder="Tell us a little about yourself..." readonly></textarea>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Tab -->
        <div id="tab-security" class="tab-content">
            <div class="space-y-6">
                <!-- Change Password Section -->
                <div class="bg-white p-6 rounded-xl card-shadow max-w-3xl mx-auto">
                  <h3 class="text-lg font-bold text-gray-900 mb-1">Change Password</h3>
                  <p class="text-sm text-gray-500 mb-6">For your security, we recommend choosing a strong password that you don't use elsewhere.</p>
                    <form id="security-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" id="current-password" placeholder="••••••••" class="password-input w-full p-2 border bg-gray-100 border-gray-300 rounded-lg text-sm" disabled>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" id="new-password" placeholder="Enter new password" class="password-input w-full p-2 border bg-gray-100 border-gray-300 rounded-lg text-sm" disabled>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <input type="password" id="confirm-new-password" placeholder="Confirm new password" class="password-input w-full p-2 border bg-gray-100 border-gray-300 rounded-lg text-sm" disabled>
                            </div>
                        </div>
                        <div id="password-mismatch-error" class="hidden">
                            <p class="text-xs text-red-500">The new passwords do not match. Please try again.</p>
                        </div>
                        <div class="pt-2">
                            <p class="text-xs text-gray-500">Password must be at least 8 characters long and include a mix of letters, numbers, and symbols.</p>
                        </div>
                        <div class="flex justify-end pt-2">
                            <div id="password-actions">
                                <button type="button" id="change-password-btn" class="px-5 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 transition-colors">Change Password</button>
                                <button type="button" id="update-password-btn" class="px-5 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 transition-colors hidden">Update Password</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Two-Factor Authentication Section -->
                <div class="bg-white p-6 rounded-xl card-shadow max-w-3xl mx-auto">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Two-Factor Authentication (2FA)</h3>
                    <p class="text-sm text-gray-500 mb-6">Add an extra layer of security to your account by requiring a second authentication step.</p>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex items-center gap-4">
                            <i class="fa-solid fa-shield-halved text-2xl text-green-600"></i>
                            <div>
                                <p class="font-semibold text-gray-800">2FA Status</p>
                                <p class="text-sm text-green-700 font-medium">Enabled</p>
                            </div>
                        </div>
                        <button class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">Manage 2FA</button>
                    </div>
                </div>

                <!-- Login Activity Section -->
                <div class="bg-white p-6 rounded-xl card-shadow max-w-3xl mx-auto">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Login Activity</h3>
                    <p class="text-sm text-gray-500 mb-6">This is a list of devices that have logged into your account. Revoke any sessions you do not recognize.</p>
                    <ul class="space-y-4">
                        <li class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-desktop text-xl text-gray-500"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Chrome on Windows <span class="text-green-600 font-semibold text-xs ml-1">(This Device)</span></p>
                                    <p class="text-xs text-gray-500">Makati City, PH · Last active now</p>
                                </div>
                            </div>
                            <button class="text-sm text-gray-500 font-medium hover:text-red-600 hover:underline">Log Out</button>
                        </li>
                        <li class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-50">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-mobile-screen-button text-xl text-gray-500"></i>
                                <div>
                                    <p class="text-sm font-medium text-gray-800">Safari on iPhone</p>
                                    <p class="text-xs text-gray-500">Cebu City, PH · Last active 2 days ago</p>
                                </div>
                            </div>
                            <button class="text-sm text-gray-500 font-medium hover:text-red-600 hover:underline">Log Out</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Notifications Tab -->
        <div id="tab-notifications" class="tab-content">
            <div class="bg-white p-6 rounded-xl card-shadow max-w-2xl mx-auto">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Notification Settings</h3>
                <p class="text-sm text-gray-500 mb-6">Choose how you want to be notified.</p>
                <div class="space-y-4 divide-y divide-gray-100">

                    <div class="flex items-center justify-between pt-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Email Alerts</p>
                            <p class="text-xs text-gray-500">Receive summaries and important updates via email.</p>
                        </div>
                        <div class="relative inline-block w-10 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" name="toggle" id="notifEmail" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" checked/>
                            <label for="notifEmail" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                        </div>

                    </div>
                    <div class="flex items-center justify-between pt-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Order Updates</p>
                            <p class="text-xs text-gray-500">Get notified when a new order is placed.</p>
                        </div>
                        <div class="relative inline-block w-10 align-middle select-none transition duration-200 ease-in">
                            <input type="checkbox" name="toggle" id="notifOrder" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" checked/>
                            <label for="notifOrder" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Appearance Tab -->
        <div id="tab-appearance" class="tab-content">
            <div class="space-y-8">
                <!-- Theme Selection -->
                <div class="bg-white p-6 rounded-xl card-shadow max-w-3xl mx-auto">
                  <h3 class="text-lg font-bold text-gray-900 mb-1">Theme</h3>
                  <p class="text-sm text-gray-500 mb-6">Select your preferred interface theme.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Light Mode -->
                        <label for="theme-light" class="theme-option border-2 border-green-500 rounded-lg p-4 cursor-pointer relative">
                            <input type="radio" name="theme" id="theme-light" value="light" class="absolute top-3 right-3">
                            <div class="w-full h-20 bg-gray-100 rounded-md flex items-center p-2 gap-2 border border-gray-200">
                                <div class="w-5 h-full bg-gray-300 rounded"></div>
                                <div class="flex-1 h-full bg-white rounded"></div>
                            </div>
                            <p class="text-sm font-semibold text-center mt-3 text-gray-800">Light</p>
                        </label>
                        <!-- Dark Mode -->
                        <label for="theme-dark" class="theme-option border-2 border-gray-200 hover:border-gray-400 rounded-lg p-4 cursor-pointer relative">
                            <input type="radio" name="theme" id="theme-dark" value="dark" class="absolute top-3 right-3">
                            <div class="w-full h-20 bg-gray-800 rounded-md flex items-center p-2 gap-2 border border-gray-700">
                                <div class="w-5 h-full bg-gray-700 rounded"></div>
                                <div class="flex-1 h-full bg-gray-900 rounded"></div>
                            </div>
                            <p class="text-sm font-semibold text-center mt-3 text-gray-800">Dark</p>
                        </label>
                    </div>
                </div>

                <!-- Layout Options -->
                <div class="bg-white p-6 rounded-xl card-shadow max-w-3xl mx-auto">
                  <h3 class="text-lg font-bold text-gray-900 mb-1">Layout</h3>
                  <p class="text-sm text-gray-500 mb-6">Customize the panel's layout.</p>
                  <div class="divide-y divide-gray-100">
                    <div class="flex items-center justify-between py-4">
                      <div>
                        <p class="text-sm font-medium text-gray-900">Compact Sidebar</p>
                        <p class="text-xs text-gray-500">Reduces the width of the sidebar for more content space.</p>
                            </div>
                            <div class="relative inline-block w-10 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" name="toggle" id="compactToggle" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
                                <label for="compactToggle" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Changes Confirmation Modal -->
    <div id="saveChangesModal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl card-shadow p-8 w-full max-w-sm text-center">
        <div class="text-green-500 text-4xl mb-4">
          <i class="fa-solid fa-circle-question"></i>
        </div>
        <h3 class="font-bold text-xl mb-2 text-gray-900">Confirm Changes</h3>
        <p class="text-gray-600 text-sm mb-6">Are you sure you want to save the changes to your profile?</p>
        <div class="flex justify-center gap-4">
          <button id="cancelSave" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
            Cancel
          </button>
          <button id="confirmSave" class="px-6 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 transition-colors">
            Save Changes
          </button>
        </div>
      </div>
    </div>

    <!-- Password Update Confirmation Modal -->
    <div id="updatePasswordModal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl card-shadow p-8 w-full max-w-sm text-center">
        <div class="text-green-500 text-4xl mb-4">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h3 class="font-bold text-xl mb-2 text-gray-900">Confirm Password Update</h3>
        <p class="text-gray-600 text-sm mb-6">Are you sure you want to update your password? This action cannot be undone.</p>
        <div class="flex justify-center gap-4">
          <button id="cancelPasswordUpdate" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
            Cancel
          </button>
          <button id="confirmPasswordUpdate" class="px-6 py-2 bg-green-700 text-white rounded-lg text-sm font-medium hover:bg-green-800 transition-colors">
            Update Password
          </button>
        </div>
      </div>
    </div>

    <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-30 hidden flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl card-shadow p-8 w-full max-w-sm text-center">
        <div class="text-red-500 text-4xl mb-4">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="font-bold text-xl mb-2 text-gray-900">Confirm Logout</h3>
        <p class="text-gray-600 text-sm mb-6">Are you sure you want to log out of the Farmers Mall Admin Panel?</p>
        <div class="flex justify-center gap-4">
          <button id="cancelLogout" class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
            Cancel
          </button>
          <a href="../auth/login.php" id="confirmLogout" class="px-6 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
            Logout
          </a>
        </div>
      </div>
    </div>

  </div> 
  <script src="admin-theme.js"></script>
  <script>
    function switchTab(tabName) {
        const activeTabClass = 'active';
        const activeBtnClasses = ['active'];
        const inactiveBtnClasses = [];

        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove(activeTabClass);
        });
        
        // Deactivate all tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove(...activeBtnClasses);
            btn.classList.add(...inactiveBtnClasses);
        });

        // Show the selected tab content and activate its button
        document.getElementById('tab-' + tabName).classList.add(activeTabClass);
        const activeBtn = document.querySelector(`.tab-btn[onclick="switchTab('${tabName}')"]`);
        activeBtn.classList.add(...activeBtnClasses);
        activeBtn.classList.remove(...inactiveBtnClasses);
    }

    document.addEventListener('DOMContentLoaded', function() {
      // --- Theme Switcher Logic ---
      const THEME_STORAGE_KEY = 'adminTheme';
      const themeRadios = document.querySelectorAll('input[name="theme"]');
      const themeOptions = document.querySelectorAll('.theme-option');

      const setThemePreference = (theme) => {
        if (typeof window.setAdminTheme === 'function') {
          window.setAdminTheme(theme);
        } else {
          localStorage.setItem(THEME_STORAGE_KEY, theme);
          const isDark = theme === 'dark';
          document.body.classList.toggle('dark-mode', isDark);
          document.documentElement.classList.toggle('dark', isDark);
        }
      };

      const updateRadioUI = (activeThemeParam) => {
        const activeTheme = activeThemeParam || localStorage.getItem(THEME_STORAGE_KEY) || 'light';
        const activeRadio = document.getElementById(`theme-${activeTheme}`);
        if (activeRadio) {
          activeRadio.checked = true;
        }

        themeOptions.forEach(opt => {
            const input = opt.querySelector('input[name="theme"]');
            if (!input) return;
            const isActive = input.value === activeTheme;
            opt.classList.toggle('border-green-500', isActive);
            opt.classList.toggle('border-gray-200', !isActive);
        });
      };

      const updateThemeSelection = (theme) => {
        setThemePreference(theme);
        updateRadioUI(theme);
      };

      themeRadios.forEach(radio => {
        radio.addEventListener('change', (e) => {
          updateThemeSelection(e.target.value);
        });
      });

      // Sync UI with saved theme on initial load (theme already applied globally)
      updateRadioUI(localStorage.getItem(THEME_STORAGE_KEY) || 'light');

      // Logout Modal Logic
      const logoutButton = document.getElementById('logoutButton');
      const logoutModal = document.getElementById('logoutModal');
      const cancelLogout = document.getElementById('cancelLogout');

      logoutButton.addEventListener('click', function() {
        logoutModal.classList.remove('hidden');
        logoutModal.classList.add('flex');
      });

      cancelLogout.addEventListener('click', function() {
        logoutModal.classList.add('hidden');
        logoutModal.classList.remove('flex');
      });

      logoutModal.addEventListener('click', function(e) {
          if (e.target === logoutModal) {
              logoutModal.classList.add('hidden');
              logoutModal.classList.remove('flex');
          }
      });

      // Set the default active tab on page load
      switchTab('profile');

      // --- Profile Edit/Save Logic ---
      const editBtn = document.getElementById('edit-profile-btn');
      const saveBtn = document.getElementById('save-profile-btn');
      const profileInputs = document.querySelectorAll('.profile-input');

      // Save Changes Modal Logic
      const saveChangesModal = document.getElementById('saveChangesModal');
      const cancelSave = document.getElementById('cancelSave');
      const confirmSave = document.getElementById('confirmSave');

      editBtn.addEventListener('click', function() {
        // Enable inputs
        profileInputs.forEach(input => {
            input.readOnly = false;
            input.classList.remove('bg-gray-100', 'border-gray-300');
            input.classList.add('bg-white', 'focus:ring-green-500', 'focus:border-green-500');
        });
 
        // Toggle buttons
        editBtn.classList.add('hidden');
        saveBtn.classList.remove('hidden');
      });

      saveBtn.addEventListener('click', function() {
        // Show the confirmation modal instead of saving directly
        saveChangesModal.classList.remove('hidden');
        saveChangesModal.classList.add('flex');
      });

      cancelSave.addEventListener('click', function() {
        saveChangesModal.classList.add('hidden');
        saveChangesModal.classList.remove('flex');
      });

      saveChangesModal.addEventListener('click', function(e) {
          if (e.target === saveChangesModal) {
              saveChangesModal.classList.add('hidden');
              saveChangesModal.classList.remove('flex');
          }
      });

      confirmSave.addEventListener('click', function() {
        // This is where the actual save logic would go (e.g., an AJAX call)
        console.log("Changes saved!");

        // Disable inputs
        profileInputs.forEach(input => {
            input.readOnly = true;
            input.classList.add('bg-gray-100', 'border-gray-300');
            input.classList.remove('bg-white', 'focus:ring-green-500', 'focus:border-green-500');
        });

        // Toggle buttons
        saveBtn.classList.add('hidden');
        editBtn.classList.remove('hidden');

        // Hide the modal
        saveChangesModal.classList.add('hidden');
        saveChangesModal.classList.remove('flex');
      });

      // --- Security Edit/Save Logic ---
      const changePassBtn = document.getElementById('change-password-btn');
      const updatePassBtn = document.getElementById('update-password-btn');
      const passwordInputs = document.querySelectorAll('.password-input');
      const updatePasswordModal = document.getElementById('updatePasswordModal');
      const currentPasswordInput = document.getElementById('current-password');
      const newPasswordInput = document.getElementById('new-password');
      const confirmNewPasswordInput = document.getElementById('confirm-new-password');
      const passwordMismatchError = document.getElementById('password-mismatch-error');

      const cancelPasswordUpdate = document.getElementById('cancelPasswordUpdate');
      const confirmPasswordUpdate = document.getElementById('confirmPasswordUpdate');

      const revertPasswordForm = () => {
        // Revert the state
        passwordInputs.forEach(input => {
            input.disabled = true;
            input.classList.add('bg-gray-100', 'border-gray-300');
            input.classList.remove('bg-white', 'focus:ring-green-500', 'focus:border-green-500');
            input.value = ''; // Clear fields

            // Hide error message and remove error styles
            passwordMismatchError.classList.add('hidden');
            newPasswordInput.classList.remove('border-red-500');
            confirmNewPasswordInput.classList.remove('border-red-500');

        });

        // Revert placeholder
        currentPasswordInput.placeholder = "••••••••";

        updatePassBtn.classList.add('hidden');
        changePassBtn.classList.remove('hidden');

        updatePasswordModal.classList.add('hidden');
        updatePasswordModal.classList.remove('flex');
      };
      changePassBtn.addEventListener('click', function() {
        // Enable inputs
        passwordInputs.forEach(input => {
            input.disabled = false;
            input.classList.remove('bg-gray-100', 'border-gray-300');
            input.classList.add('bg-white', 'focus:ring-green-500', 'focus:border-green-500');
        });

        // Update placeholder for current password
        currentPasswordInput.placeholder = "Enter your current password";

        // Toggle buttons
        changePassBtn.classList.add('hidden');
        updatePassBtn.classList.remove('hidden');
      });
      
      updatePassBtn.addEventListener('click', function() {
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmNewPasswordInput.value;

        // Validate if passwords match
        if (newPassword !== confirmPassword) {
            passwordMismatchError.classList.remove('hidden');
            newPasswordInput.classList.add('border-red-500');
            confirmNewPasswordInput.classList.add('border-red-500');
            newPasswordInput.focus();
        } else {
            // Clear any previous errors and show the modal
            passwordMismatchError.classList.add('hidden');
            newPasswordInput.classList.remove('border-red-500');
            confirmNewPasswordInput.classList.remove('border-red-500');

            // Show the confirmation modal
            updatePasswordModal.classList.remove('hidden');
            updatePasswordModal.classList.add('flex');
        }
      });

      cancelPasswordUpdate.addEventListener('click', function() {
        revertPasswordForm();
      });

      updatePasswordModal.addEventListener('click', function(e) {
          if (e.target === updatePasswordModal) {
              updatePasswordModal.classList.add('hidden');
              updatePasswordModal.classList.remove('flex');
          }
      });

      confirmPasswordUpdate.addEventListener('click', function() {
        // In a real app, you'd add validation here before proceeding.
        // For example, check if "New Password" and "Confirm New Password" match.
        // Also, an AJAX call would be made to a backend script to validate the current password and save the new one.
        console.log("Password update process initiated!");

        revertPasswordForm();
      });
    });
  </script>
</body>
</html> 