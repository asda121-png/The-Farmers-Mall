<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
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
$retailerData = null;
$profilePicture = '../images/default-avatar.svg';

try {
    // Fetch user data
    $users = $api->select('users', ['id' => $userId]);
    if (!empty($users)) {
        $userData = $users[0];
        
        // Get profile picture
        if (!empty($userData['profile_picture'])) {
            $profilePath = '../' . ltrim($userData['profile_picture'], '/');
            if (file_exists($profilePath)) {
                $profilePicture = $profilePath;
            }
        }
        
        // Get retailer data if user is a retailer
        if ($userData['user_type'] === 'retailer') {
            $retailers = $api->select('retailers', ['user_id' => $userId]);
            if (!empty($retailers)) {
                $retailerData = $retailers[0];
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Seller Profile – The Farmer's Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">

  <!-- Header -->
  <?php include 'retailerheader.php'; ?>

  <!-- Main Content -->
  <main class="w-full">
    <div class="max-w-7xl mx-auto px-6 py-8 flex gap-6 pb-12" style="min-height: calc(100vh - 140px);">

    <!-- Sidebar -->
    <aside class="w-72">
      <div class="bg-white rounded-lg shadow p-6">
        <!-- Navigation: Profile-focused items -->
        <nav class="space-y-2 text-sm">
          <a href="#profile-overview" class="sidebar-link active flex items-center gap-2 px-4 py-2 rounded-lg text-gray-700">
            <i class="fas fa-user"></i> Profile Overview
          </a>
          <a href="#account-details" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg text-gray-700">
            <i class="fas fa-id-card"></i> Account Details
          </a>
          <a href="#shop-settings" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg text-gray-700">
            <i class="fas fa-cog"></i> Shop Settings
          </a>
          <a href="#business-permit-section" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg text-gray-700">
            <i class="fas fa-file-contract"></i> Business Permit
          </a>
          <a href="#payment-details" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg text-gray-700">
            <i class="fas fa-credit-card"></i> Payment Details
          </a>
          <a href="#change-password" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg text-gray-700">
            <i class="fas fa-key"></i> Change Password
          </a>
          
        </nav>
      </div>
    </aside>

    <!-- Main Profile Form -->
    <div class="flex-1">
      <section id="profile-overview" class="content-section bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
          <h2 class="font-semibold text-lg">My Profile</h2>
          <button id="editProfileBtn" class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
            <i class="fas fa-pen"></i> Edit Profile
          </button>
        </div>

        <div>
          <form class="space-y-6">

            <!-- Profile Picture -->
            <div class="flex items-center gap-6">
              <div class="relative w-32 h-32 flex-shrink-0">
                <img id="profileImage" src="<?php echo htmlspecialchars($profilePicture); ?>?v=<?php echo time(); ?>" class="w-32 h-32 rounded-full object-cover border-4 border-green-100" alt="Seller Profile" onerror="this.src='../images/default-avatar.svg'" />
                <label for="imageUpload" id="changePictureBtn" class="absolute bottom-0 right-0 w-10 h-10 bg-green-600 rounded-full flex items-center justify-center text-white border-4 border-white cursor-pointer hover:bg-green-700 transition">
                  <i class="fas fa-camera text-sm"></i>
                </label>
                <input type="file" id="imageUpload" class="hidden" accept="image/png, image/jpeg, image/gif, image/webp"/>
              </div>
              <div class="flex-1 space-y-2">
                <input type="text" id="shopName" placeholder="<?php echo htmlspecialchars($retailerData['shop_name'] ?? 'My Shop'); ?>" value="<?php echo htmlspecialchars($retailerData['shop_name'] ?? ''); ?>" class="text-2xl font-bold bg-transparent border-none p-0 focus:ring-0 w-full placeholder-black" disabled>
                <input type="text" id="shopAddress" placeholder="<?php echo htmlspecialchars($retailerData['business_address'] ?? 'Shop Location'); ?>" value="<?php echo htmlspecialchars($retailerData['business_address'] ?? ''); ?>" class="text-sm text-gray-500 bg-transparent border-none p-0 focus:ring-0 w-full placeholder-gray-400" disabled>
              </div>
            </div>

            <!-- Personal Info -->
            <div class="space-y-6 pt-6 border-t">
              <div class="grid md:grid-cols-2 gap-6">
                <!-- First Name -->
                <div class="info-block">
                  <div class="display-view bg-gray-50 p-4 rounded-lg"><label class="text-xs text-gray-500 uppercase tracking-wide">First Name</label><p id="displayFirstName" class="text-gray-800 font-medium mt-1"><?php echo htmlspecialchars(explode(' ', $userData['full_name'] ?? 'User')[0]); ?></p></div>
                  <div class="edit-view hidden"><label for="firstName" class="block text-sm font-medium text-gray-700">First Name</label><input type="text" id="firstName" value="<?php echo htmlspecialchars(explode(' ', $userData['full_name'] ?? '')[0]); ?>" class="mt-1 w-full border rounded-md px-3 py-2"></div>
                </div>
                <!-- Last Name -->
                <div class="info-block">
                  <div class="display-view bg-gray-50 p-4 rounded-lg"><label class="text-xs text-gray-500 uppercase tracking-wide">Last Name</label><p id="displayLastName" class="text-gray-800 font-medium mt-1"><?php $nameParts = explode(' ', $userData['full_name'] ?? 'User'); echo htmlspecialchars(isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : ''); ?></p></div>
                  <div class="edit-view hidden"><label for="lastName" class="block text-sm font-medium text-gray-700">Last Name</label><input type="text" id="lastName" value="<?php $nameParts = explode(' ', $userData['full_name'] ?? ''); echo htmlspecialchars(isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : ''); ?>" class="mt-1 w-full border rounded-md px-3 py-2"></div>
                </div>
                <!-- Mobile Number -->
                <div class="info-block">
                  <div class="display-view bg-gray-50 p-4 rounded-lg"><label class="text-xs text-gray-500 uppercase tracking-wide">Mobile Number</label><p id="displayMobileNumber" class="text-gray-800 font-medium mt-1"><?php echo htmlspecialchars($userData['phone'] ?? 'Not set'); ?></p></div>
                  <div class="edit-view hidden"><label for="mobileNumber" class="block text-sm font-medium text-gray-700">Mobile Number</label><input type="text" id="mobileNumber" value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>" class="mt-1 w-full border rounded-md px-3 py-2"></div>
                </div>
                <!-- Email Address -->
                <div class="info-block">
                  <div class="display-view bg-gray-50 p-4 rounded-lg"><label class="text-xs text-gray-500 uppercase tracking-wide">Email Address</label><p id="displayEmail" class="text-gray-800 font-medium mt-1"><?php echo htmlspecialchars($userData['email'] ?? 'Not set'); ?></p></div>
                  <div class="edit-view hidden"><label for="email" class="block text-sm font-medium text-gray-700">Email Address</label><input type="email" id="email" value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" class="mt-1 w-full border rounded-md px-3 py-2" readonly></div>
                </div>
              </div>
            </div>

            <!-- Business Permit Section -->
            <div class="space-y-6 pt-6 border-t">
              <h3 class="text-lg font-medium">Business Permit</h3>
              <div id="permitContainer">
                <button type="button" id="seePermitBtn" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed">See Business Permit</button>
              </div>
            </div>


            <!-- Actions -->
            <div class="border-t pt-6 flex justify-end">
              <button type="submit" id="saveChangesBtn" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 hidden">Save Changes</button>
            </div>

          </form>
        </div>

      </section>

      <!-- Other sections (hidden by default) -->
      <section id="account-details" class="content-section bg-white rounded-lg shadow p-6 hidden">
        <h2 class="font-semibold text-lg">Account Details</h2>
        <p class="mt-4 text-gray-600">This section will contain account details management.</p>
        <!-- Content from retaileraccount.php would go here -->
      </section>

      <section id="shop-settings" class="content-section bg-white rounded-lg shadow p-6 hidden">
        <h2 class="font-semibold text-lg">Shop Settings</h2>
        <p class="mt-4 text-gray-600">This section will contain shop settings.</p>
        <!-- Content from retailersettings.php would go here -->
      </section>

      <section id="business-permit-section" class="content-section bg-white rounded-lg shadow p-6 hidden">
        <h2 class="font-semibold text-lg">Business Permit</h2>
        <p class="mt-4 text-gray-600">This section will show business permit details.</p>
        <div class="mt-4">
            <button type="button" id="seePermitBtn2" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed">See Business Permit</button>
        </div>
      </section>

      <section id="payment-details" class="content-section bg-white rounded-lg shadow p-6 hidden">
        <h2 class="font-semibold text-lg">Payment Details</h2>
        <p class="mt-4 text-gray-600">This section will contain payment details management.</p>
        <!-- Content from retailerpayments.php would go here -->
      </section>

      <section id="change-password" class="content-section bg-white rounded-lg shadow p-6 hidden">
        <h2 class="font-semibold text-lg">Change Password</h2>
        <p class="mt-4 text-gray-600">This section will contain the change password form.</p>
        <!-- Content from retailerchangepassword.php would go here -->
      </section>


    </div>
  </main>


  <!-- Logout Modal -->
  <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm text-center">
      <div class="text-red-500 text-4xl mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
      <h3 class="font-semibold text-lg mb-2">Confirm Logout</h3>
      <p class="text-gray-600 text-sm mb-6">Are you sure you want to log out?</p>
      <div class="flex justify-center gap-4">
        <button id="cancelLogout" class="px-6 py-2 border rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
        <a href="../auth/login.php" id="confirmLogout" class="px-6 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">Logout</a>
      </div>
    </div>
  </div>

  <!-- Save Changes Modal -->
  <div id="saveChangesModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm text-center">
      <div class="text-green-500 text-4xl mb-4"><i class="fa-solid fa-circle-question"></i></div>
      <h3 class="font-semibold text-lg mb-2">Confirm Changes</h3>
      <p class="text-gray-600 text-sm mb-6">Are you sure?</p>
      <div class="flex justify-center gap-4">
        <button id="cancelSaveChanges" class="px-6 py-2 border rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100">Cancel</button>
        <button id="confirmSaveChanges" class="px-6 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">Save</button>
      </div>
    </div>
  </div>

  <!-- Business Permit Modal -->
  <div id="permitModal" class="fixed inset-0 bg-black bg-opacity-60 hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-lg p-4 w-full max-w-2xl relative">
      <div class="flex justify-between items-center border-b pb-3 mb-4">
        <h3 class="font-semibold text-lg">Business Permit</h3>
        <button id="closePermitModal" class="text-gray-500 hover:text-black text-2xl">&times;</button>
      </div>
      <div id="permitImageContainer" class="max-h-[70vh] overflow-auto">
        <!-- Image will be loaded here by JS -->
      </div>
    </div>
  </div>

  <script src="../assets/js/retailerprofile.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const sidebarLinks = document.querySelectorAll('.sidebar-link');
      const contentSections = document.querySelectorAll('.content-section');

      function updateContent(hash) {
        const targetHash = hash || '#profile-overview';

        // Hide all sections
        contentSections.forEach(section => {
          section.classList.add('hidden');
        });

        // Show the target section
        const targetSection = document.querySelector(targetHash);
        if (targetSection) {
          targetSection.classList.remove('hidden');
        }

        // Update active link style
        sidebarLinks.forEach(link => {
          if (link.getAttribute('href') === targetHash) {
            link.classList.add('bg-green-50', 'text-green-700');
            link.classList.remove('hover:bg-gray-100');
          } else {
            link.classList.remove('bg-green-50', 'text-green-700');
            link.classList.add('hover:bg-gray-100');
          }
        });
      }

      // Handle clicks on sidebar links
      sidebarLinks.forEach(link => {
        link.addEventListener('click', (e) => {
          e.preventDefault();
          const newHash = link.hash;
          history.pushState(null, '', newHash);
          updateContent(newHash);
        });
      });

      // Show content based on initial URL hash
      updateContent(window.location.hash);

      // Handle profile picture upload
      const imageUpload = document.getElementById('imageUpload');
      const profileImage = document.getElementById('profileImage');
      
      imageUpload.addEventListener('change', async function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
          alert('File size must be less than 5MB');
          return;
        }
        
        // Validate file type
        if (!['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(file.type)) {
          alert('Only JPG, PNG, GIF, and WEBP images are allowed');
          return;
        }
        
        // Show loading state
        const originalSrc = profileImage.src;
        profileImage.style.opacity = '0.5';
        
        try {
          const formData = new FormData();
          formData.append('profile_picture', file);
          
          const response = await fetch('../api/update-profile.php', {
            method: 'POST',
            body: formData
          });
          
          const result = await response.json();
          
          if (result.success) {
            // Update image with cache buster
            profileImage.src = result.profile_picture + '?t=' + new Date().getTime();
            profileImage.style.opacity = '1';
            alert('Profile picture updated successfully!');
            
            // Trigger update in dashboard if it's open
            window.dispatchEvent(new CustomEvent('profileUpdated'));
          } else {
            profileImage.style.opacity = '1';
            alert('Error: ' + result.message);
          }
        } catch (error) {
          console.error('Upload error:', error);
          profileImage.src = originalSrc;
          profileImage.style.opacity = '1';
          alert('Failed to upload profile picture. Please try again.');
        }
      });

      // Handle Edit Profile button
      const editProfileBtn = document.getElementById('editProfileBtn');
      const infoBlocks = document.querySelectorAll('.info-block');
      let isEditing = false;
      
      editProfileBtn.addEventListener('click', function() {
        isEditing = !isEditing;
        
        if (isEditing) {
          // Enable edit mode
          editProfileBtn.innerHTML = '<i class="fas fa-save"></i> Save Changes';
          editProfileBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
          editProfileBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
          
          infoBlocks.forEach(block => {
            block.querySelector('.display-view').classList.add('hidden');
            block.querySelector('.edit-view').classList.remove('hidden');
          });
          
          // Enable shop name and address editing
          document.getElementById('shopName').disabled = false;
          document.getElementById('shopAddress').disabled = false;
          document.getElementById('shopName').classList.add('border', 'border-gray-300', 'px-3', 'py-2', 'rounded');
          document.getElementById('shopAddress').classList.add('border', 'border-gray-300', 'px-3', 'py-2', 'rounded');
          
          // Enable image upload
          document.getElementById('imageUpload').disabled = false;
        } else {
          // Save changes
          saveProfileChanges();
        }
      });
      
      async function saveProfileChanges() {
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        const phone = document.getElementById('mobileNumber').value.trim();
        const shopName = document.getElementById('shopName').value.trim();
        const shopAddress = document.getElementById('shopAddress').value.trim();
        
        if (!firstName) {
          alert('First name is required');
          return;
        }
        
        try {
          const formData = new FormData();
          formData.append('full_name', firstName + ' ' + lastName);
          formData.append('phone', phone);
          formData.append('shop_name', shopName);
          formData.append('shop_description', shopAddress);
          
          const response = await fetch('../api/update-profile.php', {
            method: 'POST',
            body: formData
          });
          
          const result = await response.json();
          
          if (result.success) {
            // Update display values
            document.getElementById('displayFirstName').textContent = firstName;
            document.getElementById('displayLastName').textContent = lastName;
            document.getElementById('displayMobileNumber').textContent = phone || 'Not set';
            
            // Exit edit mode
            editProfileBtn.innerHTML = '<i class="fas fa-pen"></i> Edit Profile';
            editProfileBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            editProfileBtn.classList.add('bg-green-600', 'hover:bg-green-700');
            
            infoBlocks.forEach(block => {
              block.querySelector('.display-view').classList.remove('hidden');
              block.querySelector('.edit-view').classList.add('hidden');
            });
            
            document.getElementById('shopName').disabled = true;
            document.getElementById('shopAddress').disabled = true;
            document.getElementById('shopName').classList.remove('border', 'border-gray-300', 'px-3', 'py-2', 'rounded');
            document.getElementById('shopAddress').classList.remove('border', 'border-gray-300', 'px-3', 'py-2', 'rounded');
            document.getElementById('imageUpload').disabled = true;
            
            isEditing = false;
            alert('Profile updated successfully!');
            
            // Reload page to reflect all changes
            setTimeout(() => location.reload(), 1000);
          } else {
            alert('Error: ' + result.message);
          }
        } catch (error) {
          console.error('Save error:', error);
          alert('Failed to save changes. Please try again.');
        }
      }
    });
  </script>
  </body>
</html>
  