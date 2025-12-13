<?php
// Start output buffering at the very beginning
ob_start();

// Enable error logging for debugging
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../debug_profile_errors.log');

// Suppress display but keep logging when handling AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit();

}



// Load Supabase API
require_once __DIR__ . '/../config/supabase-api.php';

// --- Function Definitions ---

/**
 * Handles profile picture upload, validation, and storage.
 *
 * @param array $file The $_FILES['profile_picture'] array.
 * @param string $userId The ID of the current user.
 * @param string $oldProfilePicture The path to the old profile picture to be deleted.
 * @return string|null The new profile picture path on success, null on failure.
 * @throws Exception If validation or file move fails.
 */
function handleProfilePictureUpload(array $file, string $userId, string $oldProfilePicture): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        // No file uploaded or an error occurred, but not a fatal one for the whole process.
        return null;
    }

    // Validate file size (5MB limit)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File size must be less than 5MB');
    }

    // Validate MIME type - use fileinfo if available, fallback to browser-provided type
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (function_exists('mime_content_type')) {
        $mime_type = mime_content_type($file['tmp_name']);
    } elseif (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        // finfo_close is deprecated in PHP 8.0+ and no longer needed
    } else {
        // Fallback to browser-provided MIME type
        $mime_type = $file['type'];
    }
    
    if (!in_array($mime_type, $allowed_mime_types)) {
        throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
    }

    // Create uploads/profiles directory if it doesn't exist
    $upload_dir = __DIR__ . '/../assets/profiles/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception('Failed to create upload directory.');
        }
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Delete old profile picture if it exists
        if (!empty($oldProfilePicture) && file_exists(__DIR__ . '/../' . $oldProfilePicture)) {
            @unlink(__DIR__ . '/../' . $oldProfilePicture);
        }
        return 'assets/profiles/' . $filename;
    } else {
        throw new Exception('Failed to move uploaded file.');
    }
}

/**
 * Handles the entire profile update process.
 */
function handleProfileUpdate()
{
    header('Content-Type: application/json');
    $api = getSupabaseAPI();
    $user_id = $_SESSION['user_id'] ?? null;

    // DEBUG: Log what we received
    error_log("=== PROFILE UPDATE DEBUG ===");
    error_log("User ID: " . $user_id);
    error_log("FILES received: " . print_r($_FILES, true));
    error_log("POST data: " . print_r($_POST, true));

    if (!$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'User session expired. Please log in again.']);
        exit();
    }

    try {
        // Get full_name from POST data
        $full_name = trim($_POST['full_name'] ?? '');

        $updateData = [
            'full_name' => $full_name,
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'date_of_birth' => trim($_POST['date_of_birth'] ?? null),
            'gender' => trim($_POST['gender'] ?? ''),
            'address' => trim($_POST['address'] ?? '')
        ];

        // If date is empty, it should be set to null in the DB
        if (empty($updateData['date_of_birth'])) {
            $updateData['date_of_birth'] = null;
        }

        // Handle profile picture upload
        if (isset($_FILES['profile_picture'])) {
            error_log("Profile picture file detected: " . $_FILES['profile_picture']['name']);
            $oldProfilePicture = ($_SESSION['profile_picture'] ?? '');
            $newProfilePicPath = handleProfilePictureUpload($_FILES['profile_picture'], $user_id, $oldProfilePicture);
            if ($newProfilePicPath) {
                error_log("New profile picture saved to: " . $newProfilePicPath);
                $updateData['profile_picture'] = $newProfilePicPath;
            } else {
                error_log("Profile picture upload returned null (file error or no file selected)");
            }
        } else {
            error_log("No profile_picture in FILES array");
        }
        
        if (isset($_POST['profile_picture']) && $_POST['profile_picture'] === 'remove') {
            // Handle picture removal request from the client
            $updateData['profile_picture'] = null;
            if (!empty($_SESSION['profile_picture']) && file_exists(__DIR__ . '/../' . $_SESSION['profile_picture'])) {
                @unlink(__DIR__ . '/../' . $_SESSION['profile_picture']);
            }
        }

        // Update database
        $api->update('users', $updateData, ['id' => $user_id]);

        // Update session with new data
        $_SESSION['full_name'] = $updateData['full_name'];
        $_SESSION['username'] = $updateData['full_name']; // Assuming username is full_name
        if (isset($updateData['profile_picture'])) {
            $_SESSION['profile_picture'] = $updateData['profile_picture'];
        }

        // Fetch updated data to return to client
        $updatedUser = $api->select('users', ['id' => $user_id]);
        $userData = !empty($updatedUser) ? $updatedUser[0] : [];

        echo json_encode([
            'status' => 'success',
            'message' => 'Profile updated successfully!',
            'data' => $userData
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()]);
    }
    exit();
}

// --- Main Logic ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    handleProfileUpdate();
}

$user_id = $_SESSION['user_id'] ?? null;
$api = getSupabaseAPI();

// Handle remove profile picture via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_profile_picture') {

    header('Content-Type: application/json');

    

    try {

        if ($user_id) {

            // Get current profile picture

            $users = $api->select('users', ['id' => $user_id]);

            if (!empty($users)) {

                $currentPic = $users[0]['profile_picture'] ?? '';

                $full_name = $users[0]['full_name'] ?? 'User';

                

                // Delete physical file if exists

                if (!empty($currentPic) && file_exists(__DIR__ . '/../' . $currentPic)) {

                    @unlink(__DIR__ . '/../' . $currentPic);

                }

            } else {

                $full_name = 'User';

            }

            

            // Update database to remove profile picture

            $api->update('users', ['profile_picture' => ''], ['id' => $user_id]);

            

            // Update session

            $_SESSION['profile_picture'] = '';

            

            // Get user initials for response

            $initials = strtoupper(substr($full_name, 0, 1));

            

            echo json_encode([

                'status' => 'success',

                'message' => 'Profile picture removed successfully!',

                'data' => [

                    'initials' => $initials

                ]

            ]);

        } else {

            echo json_encode(['status' => 'error', 'message' => 'User not found']);

        }

    } catch (Exception $e) {

        echo json_encode(['status' => 'error', 'message' => 'Failed to remove picture: ' . $e->getMessage()]);

    }

    exit();

}

// Fetch user data from Supabase
require_once __DIR__ . '/../config/uuid-helper.php';
$userData = [];
if ($user_id) {
    $userData = safeGetUser($user_id, $api) ?: [];
}

// Set default values
$email = $userData['email'] ?? $_SESSION['email'] ?? 'user@email.com';
$full_name = $userData['full_name'] ?? $_SESSION['username'] ?? 'Guest User';
$name_parts = explode(' ', $full_name, 2);
$first_name = $name_parts[0];
$last_name = $name_parts[1] ?? '';

$address_parts = explode(', ', $userData['address'] ?? '');
$street = $address_parts[0] ?? '';
$barangay = $address_parts[1] ?? '';
$phone = $userData['phone'] ?? '';
$username = $userData['username'] ?? '';
$profile_picture = $userData['profile_picture'] ?? $_SESSION['profile_picture'] ?? '';
$date_of_birth = $userData['date_of_birth'] ?? '';
$gender = $userData['gender'] ?? '';
$address = $userData['address'] ?? '';
$created_at = $userData['created_at'] ?? '';

// Get order statistics
$total_orders = 0;
$total_spent = 0;
try {
    $orders = $api->select('orders', ['customer_id' => $user_id]);
    $total_orders = count($orders);
    foreach ($orders as $order) {
        $total_spent += floatval($order['total_amount'] ?? 0);
    }
} catch (Exception $e) {
    // Silent fail
}



// Get saved items count (cart items)
$saved_items = 0;
try {
    $cart_items = $api->select('cart', ['user_id' => $user_id]);
    $saved_items = count($cart_items);
} catch (Exception $e) {
    // Silent fail
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - Farmers Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .notification-dropdown { position: absolute; top: 100%; right: 0; margin-top: 8px; width: 320px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); z-index: 50; max-height: 400px; overflow-y: auto; }
    .notification-item { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; transition: all 0.2s ease; cursor: pointer; }
    .notification-item:hover { background-color: #f9f9f9; }
    .notification-item.unread { background-color: #f0f9f5; border-left: 4px solid #4CAF50; }
    .notification-item-title { font-weight: 600; color: #333; font-size: 14px; margin-bottom: 4px; }
    .notification-item-message { font-size: 12px; color: #666; margin-bottom: 4px; }
    .notification-item-time { font-size: 11px; color: #999; }
    .notification-empty { padding: 24px 16px; text-align: center; color: #999; font-size: 14px; }
    .notification-header { padding: 12px 16px; border-bottom: 1px solid #e0e0e0; font-weight: 600; display: flex; justify-content: space-between; align-items: center; }
    .notification-clear-btn { font-size: 12px; color: #2E7D32; cursor: pointer; background: none; border: none; }
    .notification-clear-btn:hover { color: #1B5E20; }

    /* Ensure select dropdown doesn't overflow modal */
    select { 
      max-height: 140px;
      overflow-y: auto;
    }
    
    /* FAQ Accordion Styles from support.php */
    .faq-item .faq-answer { display: grid; grid-template-rows: 0fr; transition: grid-template-rows 0.3s ease-out; }
    .faq-item.active .faq-answer { grid-template-rows: 1fr; }
    .faq-item.active .faq-toggle .fa-chevron-down { transform: rotate(180deg); }

    /* Custom style for a focused select element to act as a scrollable dropdown */
    #address-barangay[size] {
      position: absolute;
      z-index: 99;
      height: 400px; /* Adjust height as needed */
      border-width: 2px;
    }
  </style>
</head>
<body class="bg-gray-50 font-sans">



<?php include __DIR__ . '/../includes/user-header.php'; ?>


  <!-- Main Layout with Modern Design -->
  <main class="max-w-7xl mx-auto px-6 py-8 flex gap-6 mb-32 min-h-screen">

    <!-- Sidebar -->

    <aside class="w-72 bg-white rounded-lg shadow p-6 h-fit sticky top-24">

      <!-- Profile Info -->

      <div class="flex flex-col items-center text-center mb-6">

        <?php if (!empty($profile_picture) && file_exists(__DIR__ . '/../' . $profile_picture)): ?>

          <img id="sidebarProfilePic" src="<?php echo htmlspecialchars('../' . $profile_picture); ?>" alt="Profile" class="w-20 h-20 rounded-full mb-3 object-cover border-2 border-green-600">

        <?php else: ?>

          <div id="sidebarProfilePic" class="w-20 h-20 rounded-full mb-3 bg-green-600 flex items-center justify-center">

            <i class="fas fa-user text-white text-3xl"></i>

          </div>

        <?php endif; ?>

        <h2 class="font-semibold"><?php echo htmlspecialchars($full_name); ?></h2>

        <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($email); ?></p>

      </div>



      <!-- Navigation -->

      <nav class="space-y-2">

        <a href="#my-profile" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg bg-green-50 text-green-700 font-medium">

          <i class="fas fa-user"></i> My Profile

        </a>

        <a href="#order-history" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100">

          <i class="fas fa-box"></i> My Purchases

        </a>

        <a href="#my-address" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100">

          <i class="fas fa-map-marker-alt"></i> My Address

        </a>

        <a href="#settings" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100">

          <i class="fas fa-cog"></i> Settings

        </a>

        <a href="#help-support" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100">

          <i class="fas fa-question-circle"></i> Help & Support

        </a>

        <!-- Logout Button -->

        <div class="border-t pt-2 mt-2">

        

        </div>

      </nav>

    </aside>


    <!-- Main Content -->
    <div class="flex-1">

      <!-- My Profile Section -->
      <section id="my-profile" class="content-section bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold text-gray-800">My Profile</h2>
          <button id="editProfileBtn" class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
            <i class="fas fa-pen"></i> Edit Profile
          </button>
        </div>

          <!-- Profile Display Mode -->
          <div id="profileDisplay" class="space-y-6">
            <!-- Profile Picture & Basic Info -->
            <div class="flex items-start gap-6 pb-6 border-b">
              <div class="relative">
                <?php if (!empty($profile_picture) && file_exists(__DIR__ . '/../' . $profile_picture)): ?>
                  <img id="displayProfilePic" src="<?php echo htmlspecialchars('../' . $profile_picture); ?>" alt="Profile" class="w-32 h-32 rounded-full border-4 border-green-100 object-cover">
                <?php else: ?>
                  <div id="displayProfilePic" class="w-32 h-32 rounded-full border-4 border-green-100 bg-green-600 flex items-center justify-center">
                    <i class="fas fa-user text-white text-4xl"></i>
                  </div>
                <?php endif; ?>
              </div>
              <div class="flex-1">
                <h3 id="displayFullName" class="text-2xl font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($full_name); ?></h3>
                <p id="displayEmail" class="text-gray-600 mb-3"><?php echo htmlspecialchars($email); ?></p>
              </div>
            </div>



          <!-- Personal Information Grid -->

          <div class="grid md:grid-cols-2 gap-6">

            <div class="bg-gray-50 p-4 rounded-lg">

              <label class="text-xs text-gray-500 uppercase tracking-wide">Phone Number</label>

              <p id="displayPhone" class="text-gray-800 font-medium mt-1"><?php echo htmlspecialchars($phone ?: 'Not provided'); ?></p>

            </div>

            <div class="bg-gray-50 p-4 rounded-lg">

              <label class="text-xs text-gray-500 uppercase tracking-wide">Date of Birth</label>

              <p id="displayDob" class="text-gray-800 font-medium mt-1">

                <?php 

                  if (!empty($date_of_birth)) {

                    echo date('F d, Y', strtotime($date_of_birth));

                  } else {

                    echo 'Not provided';

                  }

                ?>

              </p>

            </div>

            <div class="bg-gray-50 p-4 rounded-lg">

              <label class="text-xs text-gray-500 uppercase tracking-wide">Gender</label>

              <p id="displayGender" class="text-gray-800 font-medium mt-1"><?php echo htmlspecialchars($gender ?: 'Not specified'); ?></p>

            </div>

            <div class="bg-gray-50 p-4 rounded-lg">

              <label class="text-xs text-gray-500 uppercase tracking-wide">Member Since</label>

              <p id="displayMemberSince" class="text-gray-800 font-medium mt-1">

                <?php 

                  if (!empty($created_at)) {

                    echo date('F Y', strtotime($created_at));

                  } else {

                    echo 'Recently';

                  }

                ?>

              </p>

            </div>

            <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">

              <label class="text-xs text-gray-500 uppercase tracking-wide">Address</label>

              <p id="displayAddress" class="text-gray-800 font-medium mt-1"><?php echo htmlspecialchars($address ?: 'Not provided'); ?></p>

            </div>

          </div>



          <!-- Account Statistics -->

          <div class="pt-6 border-t">

            <h4 class="font-semibold mb-4 text-gray-700">Account Statistics</h4>

            <div class="grid grid-cols-3 gap-4">

              <div class="text-center p-4 bg-green-50 rounded-lg">

                <p class="text-2xl font-bold text-green-600"><?php echo $total_orders; ?></p>

                <p class="text-sm text-gray-600 mt-1">Total Orders</p>

              </div>

              <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-2xl font-bold text-blue-600">₱<?php echo number_format($total_spent, 2); ?></p>
                <p class="text-sm text-gray-600 mt-1">Total Spent</p>
              </div>

              <div class="text-center p-4 bg-purple-50 rounded-lg">

                <p class="text-2xl font-bold text-purple-600"><?php echo $saved_items; ?></p>

                <p class="text-sm text-gray-600 mt-1">Saved Items</p>

              </div>

            </div>

          </div>

        </div>



        <!-- Profile Edit Mode (Hidden by default) -->

        <form id="profileEditForm" class="space-y-6 hidden">

          <!-- Profile Picture Upload -->

          <div class="flex items-start gap-6 pb-6 border-b">

            <div class="relative">

              <?php if (!empty($profile_picture) && file_exists(__DIR__ . '/../' . $profile_picture)): ?>

                <img id="editProfilePicPreview" src="<?php echo htmlspecialchars('../' . $profile_picture); ?>" alt="Profile" class="w-32 h-32 rounded-full border-4 border-green-100 object-cover">

              <?php else: ?>

                <div id="editProfilePicPreview" class="w-32 h-32 rounded-full border-4 border-green-100 bg-green-600 flex items-center justify-center">

                  <i class="fas fa-user text-white text-5xl"></i>

                </div>

              <?php endif; ?>

              <label for="profilePicInput" class="absolute bottom-0 right-0 w-10 h-10 bg-green-600 rounded-full flex items-center justify-center text-white border-4 border-white cursor-pointer hover:bg-green-700 transition">

                <i class="fas fa-camera text-sm"></i>

              </label>

              <input type="file" id="profilePicInput" accept="image/*" class="hidden">

            </div>

            <div class="flex-1">

              <label class="block text-sm font-medium text-gray-700 mb-2">Profile Picture</label>

              <p class="text-sm text-gray-500 mb-2">Click the camera icon to upload a new profile picture</p>

              <button type="button" id="removeProfilePic" class="text-sm text-red-600 hover:text-red-700 <?php echo empty($profile_picture) ? 'hidden' : ''; ?>">

                <i class="fas fa-trash-alt mr-1"></i> Remove Picture

              </button>

            </div>

          </div>



          <!-- Personal Information -->

          <div class="grid md:grid-cols-2 gap-4">

            <div>

              <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>

              <input type="text" id="editFullName" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none">

            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
              <input type="email" id="editEmail" name="email" value="<?php echo htmlspecialchars($email); ?>" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
              <input type="tel" id="editPhone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none" placeholder="09XXXXXXXXX" pattern="09[0-9]{9}">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
              <input type="date" id="editDob" name="date_of_birth" value="<?php echo htmlspecialchars($date_of_birth); ?>" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
              <select id="editGender" name="gender" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none">
                <option value="">Select Gender</option>
                <option value="Male" <?php echo ($gender === 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo ($gender === 'Female') ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo ($gender === 'Other') ? 'selected' : ''; ?>>Other</option>
                <option value="Prefer not to say" <?php echo ($gender === 'Prefer not to say') ? 'selected' : ''; ?>>Prefer not to say</option>
              </select>

            </div>

            <div>

              <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>

              <input type="text" id="editAddress" name="address" value="<?php echo htmlspecialchars($address); ?>" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none" placeholder="Full address">

            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" id="cancelEditProfile" class="px-6 py-2 border rounded-lg text-sm font-medium hover:bg-gray-50">
              Cancel
            </button>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
              <i class="fas fa-save mr-1"></i> Save Changes
            </button>
          </div>

        </form>

      </section>



      <section id="order-history" class="content-section bg-white rounded-lg shadow p-6 hidden">
        <div class="mb-6">
          <h2 class="text-xl font-semibold text-gray-800 mb-4">My Purchases</h2>
          
          <!-- Filter Tabs -->
          <div class="flex gap-4 overflow-x-auto border-b">
            <button class="order-filter-btn pb-3 px-4 font-medium text-gray-600 border-b-2 border-transparent hover:border-green-600 whitespace-nowrap transition" data-filter="all">
              All
            </button>
            <button class="order-filter-btn pb-3 px-4 font-medium text-gray-600 border-b-2 border-transparent hover:border-green-600 whitespace-nowrap transition" data-filter="to-pay">
              To Pay
            </button>
            <button class="order-filter-btn pb-3 px-4 font-medium text-gray-600 border-b-2 border-transparent hover:border-green-600 whitespace-nowrap transition" data-filter="to-ship">
              To Ship
            </button>
            <button class="order-filter-btn pb-3 px-4 font-medium text-gray-600 border-b-2 border-transparent hover:border-green-600 whitespace-nowrap transition" data-filter="to-receive">
              To Receive
            </button>
            <button class="order-filter-btn pb-3 px-4 font-medium text-gray-600 border-b-2 border-transparent hover:border-green-600 whitespace-nowrap transition" data-filter="completed">
              Completed
            </button>
            <button class="order-filter-btn pb-3 px-4 font-medium text-gray-600 border-b-2 border-transparent hover:border-green-600 whitespace-nowrap transition" data-filter="cancelled">
              Cancelled
            </button>
            <button class="order-filter-btn pb-3 px-4 font-medium text-gray-600 border-b-2 border-transparent hover:border-green-600 whitespace-nowrap transition" data-filter="return-refund">
              Return Refund
            </button>
          </div>
        </div>

        <div id="orderList" class="space-y-4">
          <div id="noOrdersPlaceholder" class="text-center text-gray-500 py-10 border-2 border-dashed rounded-lg">
            <i class="fas fa-receipt text-3xl mb-3"></i>
            <p>You haven't placed any orders yet.</p>
            <a href="products.php" class="mt-4 inline-block bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
              Start Shopping
            </a>
          </div>

        </div>
      </section>

      <!-- Saved Addresses Section -->
      <section id="my-address" class="content-section bg-white rounded-lg shadow p-6 hidden">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold text-gray-800">My Delivery Address</h2>
          <button id="editAddressBtn" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
            <i class="fas fa-plus mr-1"></i> Add New Address
          </button>
        </div>
        <div id="addressDisplay" class="text-gray-600 leading-relaxed">
          <div id="noAddressPlaceholder" class="text-center text-gray-500 py-10 border-2 border-dashed rounded-lg">
            <i class="fas fa-map-marker-alt text-3xl mb-3"></i>
            <p>No saved addresses yet.</p>
          </div>
        </div>
      </section>

      <!-- Settings Section -->
      <section id="settings" class="content-section bg-white rounded-lg shadow p-6 hidden">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Settings</h2>
        <div class="space-y-6">
          <div>
            <h3 class="font-medium text-md mb-3">Email Notifications</h3>
            <div class="space-y-3">
              <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                  <p class="font-medium text-sm">Order Updates</p>
                  <p class="text-xs text-gray-500">Receive updates on your order status.</p>
                </div>
                <input type="checkbox" class="toggle-switch" data-setting="orderUpdates" checked>
              </label>
              <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                  <p class="font-medium text-sm">Promotions & News</p>
                  <p class="text-xs text-gray-500">Get notified about sales and new products.</p>
                </div>
                <input type="checkbox" class="toggle-switch" data-setting="promotions" checked>
              </label>
            </div>
          </div>
        </div>
      </section>

      <!-- Help & Support Section -->
      <section id="help-support" class="content-section bg-white rounded-lg shadow p-6 hidden">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Help & Support</h2>
        <div class="space-y-4" id="faqAccordion">
          <div class="faq-item border rounded-lg overflow-hidden">
            <button class="faq-toggle w-full text-left p-4 flex justify-between items-center font-medium hover:bg-gray-50">
              <span>How do I track my order?</span>
              <i class="fas fa-chevron-down transition-transform duration-200"></i>
            </button>
            <div class="faq-answer">
              <div class="overflow-hidden">
                <div class="p-4 pt-0 text-sm text-gray-600">
                  You can track your order from the "Order History" section. Click on "View Details" for any order, and if it has shipped, a "Track Order" link will be available.
                </div>
              </div>
            </div>
          </div>
          <div class="faq-item border rounded-lg overflow-hidden">
            <button class="faq-toggle w-full text-left p-4 flex justify-between items-center font-medium hover:bg-gray-50">
              <span>What is your return policy?</span>
              <i class="fas fa-chevron-down transition-transform duration-200"></i>
            </button>
            <div class="faq-answer">
              <div class="overflow-hidden">
                <div class="p-4 pt-0 text-sm text-gray-600">
                  Due to the nature of fresh produce, we only accept returns for damaged or incorrect items reported within 24 hours of delivery. Please contact our support team with a photo of the issue.
                </div>
              </div>
            </div>
          </div>
          <div class="faq-item border rounded-lg overflow-hidden">
            <button class="faq-toggle w-full text-left p-4 flex justify-between items-center font-medium hover:bg-gray-50">
              <span>How do I change my delivery address?</span>
              <i class="fas fa-chevron-down transition-transform duration-200"></i>
            </button>
            <div class="faq-answer">
              <div class="overflow-hidden">
                <div class="p-4 pt-0 text-sm text-gray-600">
                  You can update your primary address in the "My Profile" section by clicking "Edit Profile". For managing multiple delivery addresses, please visit the "My Address" section.
                </div>
              </div>
            </div>
          </div>
          <div class="faq-item border rounded-lg overflow-hidden">
            <button class="faq-toggle w-full text-left p-4 flex justify-between items-center font-medium hover:bg-gray-50">
              <span>What payment methods are accepted?</span>
              <i class="fas fa-chevron-down transition-transform duration-200"></i>
            </button>
            <div class="faq-answer">
              <div class="overflow-hidden">
                <div class="p-4 pt-0 text-sm text-gray-600">
                  We accept all major credit cards, GCash, and Cash on Delivery (COD). You can manage your saved payment methods in the "Payment Methods" section.
                </div>
              </div>
            </div>
          </div>
          <div class="faq-item border rounded-lg overflow-hidden">
            <button class="faq-toggle w-full text-left p-4 flex justify-between items-center font-medium hover:bg-gray-50">
              <span>What should I do if an item is missing or damaged?</span>
              <i class="fas fa-chevron-down transition-transform duration-200"></i>
            </button>
            <div class="faq-answer">
              <div class="overflow-hidden">
                <div class="p-4 pt-0 text-sm text-gray-600">
                  We're sorry for the inconvenience! Please contact our support team within 24 hours of delivery with your order number and a photo of the damaged item. We will arrange for a refund or a replacement.
                </div>
              </div>
            </div>
          </div>
          <div class="faq-item border rounded-lg overflow-hidden">
            <button class="faq-toggle w-full text-left p-4 flex justify-between items-center font-medium hover:bg-gray-50">
              <span>Which areas do you deliver to?</span>
              <i class="fas fa-chevron-down transition-transform duration-200"></i>
            </button>
            <div class="faq-answer">
              <div class="overflow-hidden">
                <div class="p-4 pt-0 text-sm text-gray-600">
                  We currently deliver to most areas within the City of Mati. For a detailed list of covered barangays, please visit our main <a href="../public/support.php" class="text-green-600 hover:underline">Support Page</a>. We are always expanding our delivery zones!
                </div>
              </div>
            </div>
          </div>
        </div>

      </section>



    </div>

  </main>



  <!-- Footer -->

  <footer class="text-white py-12 mt-40" style="background-color: #1B5E20;">

    <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-4 gap-8">

      

      <!-- Logo/About -->

      <div>

        <h3 class="font-bold text-lg mb-3">Farmers Mall</h3>

        <p class="text-gray-300 text-sm">

          Fresh, organic produce delivered straight to your home from local farmers.

        </p>

      </div>

      

      <!-- Quick Links -->

      <div>

        <h3 class="font-bold text-lg mb-3">Quick Links</h3>

        <ul class="space-y-2 text-sm text-gray-300">

          <li><a href="#" class="hover:underline">About Us</a></li>

          <li><a href="#" class="hover:underline">Contact</a></li>

          <li><a href="#" class="hover:underline">FAQ</a></li>

          <li><a href="#" class="hover:underline">Support</a></li>

        </ul>

      </div>



      <!-- Categories -->

      <div>

        <h3 class="font-bold text-lg mb-3">Categories</h3>

        <ul class="space-y-2 text-sm text-gray-300">

          <li><a href="#" class="hover:underline">Vegetables</a></li>

          <li><a href="#" class="hover:underline">Fruits</a></li>

          <li><a href="#" class="hover:underline">Dairy</a></li>

          <li><a href="#" class="hover:underline">Meat</a></li>

        </ul>

      </div>



      <!-- Social -->

      <div>

        <h3 class="font-bold text-lg mb-3">Follow Us</h3>

        <div class="flex space-x-4 text-xl">

          <a href="#"><i class="fab fa-facebook"></i></a>

          <a href="#"><i class="fab fa-twitter"></i></a>

          <a href="#"><i class="fab fa-instagram"></i></a>

        </div>

      </div>

    </div>



    <!-- Divider -->

    <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">

      © 2025 Farmers Mall. All rights reserved.

    </div>

  </footer>



  <!-- Add Address Modal -->

  <div id="addressModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-4">

    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">

      <h3 class="font-semibold text-lg p-6 pb-4 border-b">New Address</h3>
      
      <!-- The form itself should not scroll, but its content should. -->
      <form id="addressForm" class="flex flex-col flex-1">
        <div class="p-6 space-y-4 overflow-y-auto">

        <!-- Full Name and Phone Number Row -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
            <input type="text" id="address-name" required placeholder="Full Name" class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
            <input type="tel" id="address-phone" required placeholder="Phone Number" class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
          </div>
        </div>

        <!-- Region, Province, City, Barangay -->
        <div class="space-y-3">
          <div class="grid grid-cols-4 gap-3">
            <!-- Region Static Display -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Region</label>
              <div class="w-full border rounded-md px-3 py-2 bg-gray-100 text-gray-700 text-sm">
                Davao
              </div>
              <input type="hidden" id="address-region" value="REGION_XI">
            </div>

            <!-- Province Static Display -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
              <div class="w-full border rounded-md px-3 py-2 bg-gray-100 text-gray-700 text-sm">
                Davao Oriental
              </div>
              <input type="hidden" id="address-province" value="Davao Oriental">
            </div>

            <!-- City Static Display -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
              <div class="w-full border rounded-md px-3 py-2 bg-gray-100 text-gray-700 text-sm">
                Mati
              </div>
              <input type="hidden" id="address-city" value="Mati">
            </div>

            <!-- Barangay Dropdown -->
            <div class="relative z-20">
            <div class="relative">
              <label class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
              <select id="address-barangay" class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none text-sm bg-white">
                <option value="">Barangay</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Postal Code Static Display -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
          <div class="w-full border rounded-md px-3 py-2 bg-gray-100 text-gray-700 text-sm">
            8200
          </div>
          <input type="hidden" id="address-postal" value="8200">
        </div>

        <!-- Street Address with Add Location -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Street Name, Building, House No.</label>
          <input type="text" id="address-street" required placeholder="Street Name, Building, House No." class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">
        </div>

        </div>

        <!-- Buttons -->
        <div class="p-6 pt-4 border-t bg-gray-50 flex justify-end gap-3">
          <button type="button" class="modal-close px-6 py-2 border rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
          <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">Submit</button>
        </div>

      </form>

    </div>

  </div>

  <!-- Success Notification Modal -->

  <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">

    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md text-center transform transition-all">

      <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">

        <i class="fas fa-check text-green-600 text-3xl"></i>

      </div>

      <h3 class="font-semibold text-xl mb-2 text-gray-900">Success!</h3>

      <p id="successMessage" class="text-gray-600 text-sm mb-6">Your profile has been updated successfully.</p>

      <button id="closeSuccessModal" class="px-8 py-2.5 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">

        Great!

      </button>

    </div>

  </div>



  <!-- Loading Modal -->

  <div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">

    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md text-center">

      <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">

        <i class="fas fa-spinner fa-spin text-green-600 text-3xl"></i>

      </div>

      <h3 class="font-semibold text-xl mb-2 text-gray-900">Saving Changes...</h3>

      <p class="text-gray-600 text-sm">Please wait while we update your profile.</p>

    </div>

  </div>



  <!-- Error Notification Modal -->

  <div id="errorModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">

    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md text-center transform transition-all">

      <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">

        <i class="fas fa-exclamation-circle text-red-600 text-3xl"></i>

      </div>

      <h3 class="font-semibold text-xl mb-2 text-gray-900">Oops!</h3>

      <p id="errorMessage" class="text-gray-600 text-sm mb-6">Something went wrong. Please try again.</p>

      <button id="closeErrorModal" class="px-8 py-2.5 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">

        Close

      </button>

    </div>

  </div>



  <!-- Remove Picture Confirmation Modal -->

  <div id="removePictureModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">

    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md text-center transform transition-all">

      <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">

        <i class="fas fa-trash-alt text-red-600 text-3xl"></i>

      </div>

      <h3 class="font-semibold text-xl mb-2 text-gray-900">Remove Profile Picture</h3>

      <p class="text-gray-600 text-sm mb-6">Are you sure you want to remove your profile picture? This action cannot be undone.</p>

      <div class="flex justify-center gap-3">

        <button id="cancelRemovePicture" class="px-6 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">

          Cancel

        </button>

        <button id="confirmRemovePicture" class="px-6 py-2.5 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">

          <i class="fas fa-trash-alt mr-1"></i> Remove

        </button>

      </div>

    </div>

  </div>



  <!-- Logout Confirmation Modal -->

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



  <script>

    document.addEventListener('DOMContentLoaded', function () {

      // Modal helper functions

      const successModal = document.getElementById('successModal');

      const errorModal = document.getElementById('errorModal');

      const loadingModal = document.getElementById('loadingModal');

      const successMessage = document.getElementById('successMessage');

      const errorMessage = document.getElementById('errorMessage');

      

      function showSuccessModal(message) {

        successMessage.textContent = message;

        loadingModal.classList.add('hidden');

        successModal.classList.remove('hidden');

      }

      

      function showErrorModal(message) {

        errorMessage.textContent = message;

        loadingModal.classList.add('hidden');

        errorModal.classList.remove('hidden');

      }

      

      function showLoadingModal() {

        loadingModal.classList.remove('hidden');

      }

      

      function hideLoadingModal() {

        loadingModal.classList.add('hidden');

      }

      

      function closeSuccessModal() {

        successModal.classList.add('hidden');

        // Reload page after modal closes to show updated profile

        location.reload();

      }

      

      function closeErrorModal() {

        errorModal.classList.add('hidden');

      }

      

      document.getElementById('closeSuccessModal').addEventListener('click', closeSuccessModal);

      document.getElementById('closeErrorModal').addEventListener('click', closeErrorModal);
      
      // --- Tab Switching Logic for Personal Info / Account Details ---
      const infoTabBtns = document.querySelectorAll('.info-tab-btn');
      const infoTabContents = document.querySelectorAll('.info-tab-content');

      infoTabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
          const target = document.querySelector(btn.dataset.tabTarget);

          infoTabContents.forEach(content => content.classList.add('hidden'));
          target.classList.remove('hidden');

          infoTabBtns.forEach(b => {
            b.classList.remove('active-tab', 'text-green-600', 'border-green-600');
            b.classList.add('inactive-tab', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
          });
          btn.classList.add('active-tab', 'text-green-600', 'border-green-600');
          btn.classList.remove('inactive-tab', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        });
      });

      

      // Close modals when clicking outside

      successModal.addEventListener('click', (e) => {

        if (e.target === successModal) closeSuccessModal();

      });

      errorModal.addEventListener('click', (e) => {

        if (e.target === errorModal) closeErrorModal();

      });



      const sidebarLinks = document.querySelectorAll('.sidebar-link');

      const contentSections = document.querySelectorAll('.content-section');



      function updateContent(hash) {

        // Default to my-profile if hash is empty or invalid

        const targetHash = hash || '#my-profile';

        

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

            link.classList.add('bg-green-50', 'text-green-700', 'font-medium');

            link.classList.remove('hover:bg-gray-100');

          } else {

            link.classList.remove('bg-green-50', 'text-green-700', 'font-medium');

            link.classList.add('hover:bg-gray-100');

          }

        });

      }



      // Handle clicks on sidebar links

      sidebarLinks.forEach(link => {

        link.addEventListener('click', (e) => {

          e.preventDefault(); // Prevent the page from jumping

          updateContent(link.hash);

        });

      });



      // Show content based on initial URL hash, or default to the first one

      updateContent(window.location.hash);



      // --- Modal Management ---

      const addressModal = document.getElementById('addressModal');
      const editAddressBtn = document.getElementById('editAddressBtn');
      const addressDisplay = document.getElementById('addressDisplay');
      const addressForm = document.getElementById('addressForm');

      const closeModalBtns = document.querySelectorAll('.modal-close');

      const openModal = (modal, callback) => {
        modal.classList.remove('hidden');
      };

      const closeModal = (modal) => modal.classList.add('hidden');
      
      // Open address modal when Add New Address button is clicked
      editAddressBtn.addEventListener('click', () => {
        // Reset to "New Address" mode
        document.querySelector('#addressModal h3').textContent = 'New Address';
        const existingIndexInput = document.getElementById('editingAddressIndex');
        if (existingIndexInput) {
          existingIndexInput.remove();
        }
        addressForm.reset();
        populateBarangays(); // Repopulate and set default

        openModal(addressModal);
      });

      closeModalBtns.forEach(btn => {

        btn.addEventListener('click', () => {

          closeModal(addressModal);
          addressForm.reset();
          const existingIndexInput = document.getElementById('editingAddressIndex');
          if (existingIndexInput) {
            existingIndexInput.remove();
          }
        });

      });

      // --- Address Form Submission ---
      let userAddresses = JSON.parse(localStorage.getItem('userAddresses')) || [];

      // Comprehensive region, province, city mapping
      const regionData = {
        'NCR': {
          name: 'Metro Manila',
          provinces: {
            'Metro Manila': ['Manila', 'Quezon City', 'Caloocan', 'Las Piñas', 'Makati', 'Marikina', 'Muntinlupa', 'Navotas', 'Pasay', 'Pasig', 'Pateros', 'San Juan', 'Taguig', 'Valenzuela', 'Malabon']
          }
        },
        'REGION_I': {
          name: 'North Luzon',
          provinces: {
            'Ilocos Norte': ['Laoag', 'Batac', 'Paoay', 'Pagudpud'],
            'Ilocos Sur': ['Vigan', 'Candon', 'Santa Cruz'],
            'La Union': ['San Fernando', 'Dagupan', 'Agoo', 'Bauang']
          }
        },
        'REGION_II': {
          name: 'Cagayan Valley',
          provinces: {
            'Cagayan': ['Tuguegarao', 'Ilagan', 'Cabanatuan', 'Sanchez Mira'],
            'Isabela': ['Isabela City', 'Cabagan', 'Cauayan']
          }
        },
        'REGION_III': {
          name: 'Central Luzon',
          provinces: {
            'Bulacan': ['Malolos', 'San Fernando', 'Meycauayan', 'Valenzuela'],
            'Nueva Ecija': ['Cabanatuan', 'San Fernando', 'Palayan'],
            'Pampanga': ['Angeles City', 'San Fernando', 'Mabalacat', 'Porac'],
            'Quezon': ['Lucena', 'Pagbilao', 'Tayabas']
          }
        },
        'CALABARZON': {
          name: 'Calabarzon',
          provinces: {
            'Batangas': ['Batangas City', 'Lipa City', 'Nasugbu', 'Tagaytay'],
            'Cavite': ['Dasmariñas', 'Kawit', 'Bacoor', 'Imus'],
            'Laguna': ['Biñan', 'Santa Rosa', 'Calamba', 'Laguna'],
            'Quezon': ['Lucena', 'Pagbilao', 'Tayabas'],
            'Rizal': ['Rizal Province', 'Antipolo', 'Cainta']
          }
        },
        'MIMAROPA': {
          name: 'MIMAROPA',
          provinces: {
            'Marinduque': ['Boac', 'Mogpog'],
            'Mindoro Occidental': ['Mamburao', 'San Jose'],
            'Mindoro Oriental': ['Calapan', 'Pola'],
            'Palawan': ['Puerto Princesa', 'Coron', 'Naic']
          }
        },
        'REGION_VI': {
          name: 'Western Visayas',
          provinces: {
            'Aklan': ['Kalibo', 'Boracay'],
            'Antique': ['San Jose de Buenavista'],
            'Capiz': ['Roxas', 'Panay'],
            'Iloilo': ['Iloilo City', 'Dungarvan', 'Molo'],
            'Negros Occidental': ['Bacolod', 'Talisay', 'Victorias']
          }
        },
        'REGION_VII': {
          name: 'Central Visayas',
          provinces: {
            'Bohol': ['Tagbilaran', 'Panglao', 'Loon'],
            'Cebu': ['Cebu City', 'Lapu-Lapu', 'Mandaue', 'Talisay', 'Bais'],
            'Negros Oriental': ['Dumaguete', 'Sibulan', 'Mabinay'],
            'Siquijor': ['Larena', 'Lazi']
          }
        },
        'REGION_VIII': {
          name: 'Eastern Visayas',
          provinces: {
            'Biliran': ['Naval', 'Almeria'],
            'Eastern Samar': ['Borongan', 'Guiuan'],
            'Leyte': ['Tacloban', 'Ormoc', 'Albuera'],
            'Northern Samar': ['Catarman', 'San Jose'],
            'Southern Leyte': ['Maasin', 'San Ricardo']
          }
        },
        'REGION_IX': {
          name: 'Zamboanga Peninsula',
          provinces: {
            'Zamboanga del Norte': ['Dipolog', 'Dapitan'],
            'Zamboanga del Sur': ['Pagadian', 'Zamboanga City'],
            'Zamboanga Sibugay': ['Ipil', 'Malangas']
          }
        },
        'REGION_X': {
          name: 'Northern Mindanao',
          provinces: {
            'Bukidnon': ['Butuan', 'Valencia'],
            'Camiguin': ['Mambajao', 'Catarman'],
            'Lanao del Norte': ['Iligan', 'Marawi'],
            'Misamis Occidental': ['Oroquieta', 'Ozamis City'],
            'Misamis Oriental': ['Cagayan de Oro', 'Gingoog', 'Surigao City']
          }
        },
        'REGION_XI': {
          name: 'Davao',
          provinces: {
            'Davao del Norte': ['Davao City', 'Tagum', 'Panabo'],
            'Davao del Sur': ['Digos', 'Matanao'],
            'Davao Oriental': ['Mati', 'Manay'],
            'Davao Occidental': ['Malita', 'Don Marcelino']
          }
        },
        'REGION_XII': {
          name: 'Soccsksargen',
          provinces: {
            'Cotabato': ['Kidapawan', 'Matalam', 'Arakan'],
            'Sarangani': ['Alabel', 'Malungon'],
            'South Cotabato': ['Koronadal', 'General Santos', 'Tupi'],
            'Sultan Kudarat': ['Isulan', 'Tacurong']
          }
        },
        'CARAGA': {
          name: 'Caraga',
          provinces: {
            'Agusan del Norte': ['Butuan', 'Cabadbaran'],
            'Agusan del Sur': ['Proserpina', 'San Luis'],
            'Surigao del Norte': ['Surigao City', 'Tandag'],
            'Surigao del Sur': ['Bislig', 'Tandag']
          }
        },
        'BARMM': {
          name: 'Bangsamoro',
          provinces: {
            'Basilan': ['Isabela City', 'Lamitan'],
            'Lanao del Sur': ['Marawi', 'Koronadal'],
            'Maguindanao': ['Cotabato City', 'Parang'],
            'Sulu': ['Jolo', 'Zamboanga']
          }
        }
      };

      // Element references
      const regionSelect = document.getElementById('address-region');
      const provinceSelect = document.getElementById('address-province');
      const citySelect = document.getElementById('address-city');
      const barangaySelect = document.getElementById('address-barangay');

      // All barangays in Mati city
      const matiBarangays = [
        'Badas', 'Bobon', 'Buso', 'Cabuaya', 'Central', 'Culian', 'Dahican', 'Danao', 'Dawan',
        'Don Enrique Lopez', 'Don Martin Marundan', 'Don Salvador Lopez, Sr.', 'Langka', 'Lawigan',
        'Libudon', 'Luban', 'Macambol', 'Mamali', 'Matiao', 'Mayo', 'Sainz', 'Sanghay',
        'Tagabakid', 'Tagbinonga', 'Taguibo', 'Tamisan'
      ];

      // Populate barangay dropdown on page load
      function populateBarangays() {
        barangaySelect.innerHTML = ''; // Remove the repeating "Barangay" placeholder
        matiBarangays.forEach(barangay => {
          const option = document.createElement('option');
          option.value = barangay;
          option.textContent = barangay;
          barangaySelect.appendChild(option);
        });
      }

      // Call on page load
      populateBarangays();


      // --- Custom Dropdown Behavior for Barangay ---
      // This keeps the dropdown inside the scrolling modal.
      if (barangaySelect) {
        barangaySelect.addEventListener('focus', () => {
          // When the select box is focused, give it a size to expand it.
          barangaySelect.setAttribute('size', '20'); 
        });

        barangaySelect.addEventListener('blur', () => {
          // When it loses focus, remove the size to collapse it.
          barangaySelect.removeAttribute('size');
        });

        barangaySelect.addEventListener('change', () => {
          // When an option is selected, manually trigger the blur to collapse it.
          barangaySelect.blur();
        });
      }


      function displayAddresses() {
        const noAddressPlaceholder = document.getElementById('noAddressPlaceholder');
        
        if (userAddresses.length === 0) {
          addressDisplay.innerHTML = '';
          addressDisplay.appendChild(noAddressPlaceholder);
        } else {
          addressDisplay.innerHTML = '';
          userAddresses.forEach((address, index) => {
            const addressDiv = document.createElement('div');
            addressDiv.className = 'border rounded-lg p-4 mb-4 flex justify-between items-start';
            addressDiv.innerHTML = `
              <div>
                <h3 class="font-semibold text-gray-800">${address.name || ''}</h3>
                <p class="text-sm text-gray-600">Phone: ${address.phone || ''}</p>
                <p class="text-sm text-gray-600">${address.street || ''}</p>
                <p class="text-sm text-gray-600">${address.barangay || ''}, ${address.city || ''}, ${address.province || ''}</p>
                ${address.postal ? `<p class="text-sm text-gray-600">Postal: ${address.postal}</p>` : ''}
              </div>
              <div class="flex gap-3">
                <button onclick="editAddress(${index})" class="text-green-600 hover:text-green-700 text-sm">
                  <i class="fas fa-pen"></i> Edit
                </button>
                <button onclick="deleteAddress(${index})" class="text-red-600 hover:text-red-700 text-sm">
                  <i class="fas fa-trash"></i> Delete
                </button>
              </div>
            `;
            addressDisplay.appendChild(addressDiv);
          });
        }
      }

      window.deleteAddress = function(index) {
        userAddresses.splice(index, 1);
        localStorage.setItem('userAddresses', JSON.stringify(userAddresses));
        displayAddresses();
      };

      window.editAddress = function(index) {
        const address = userAddresses[index];
        if (!address) return;

        // Populate the modal form
        document.querySelector('#addressModal h3').textContent = 'Edit Address';
        const existingIndexInput = document.getElementById('editingAddressIndex');
        if (existingIndexInput) {
          existingIndexInput.remove();
        }
        addressForm.reset();

        document.getElementById('address-name').value = address.name || '';
        document.getElementById('address-phone').value = address.phone || '';
        document.getElementById('address-street').value = address.street || '';
        document.getElementById('address-barangay').value = address.barangay || '';
        
        // Add a hidden input to store the index of the address being edited
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.id = 'editingAddressIndex';
        hiddenInput.value = index;
        addressForm.appendChild(hiddenInput);

        openModal(addressModal);
      };

      if (addressForm) {
        addressForm.addEventListener('submit', (e) => {
          e.preventDefault();
          
          const newAddress = {
            name: document.getElementById('address-name').value,
            phone: document.getElementById('address-phone').value,
            street: document.getElementById('address-street').value,
            postal: document.getElementById('address-postal').value,
            region: document.getElementById('address-region').value,
            province: document.getElementById('address-province').value,
            city: document.getElementById('address-city').value,
            barangay: document.getElementById('address-barangay').value
          };
          
          const editingIndexInput = document.getElementById('editingAddressIndex');
          if (editingIndexInput) {
            // We are editing an existing address
            const index = parseInt(editingIndexInput.value, 10);
            userAddresses[index] = newAddress;
            editingIndexInput.remove(); // Clean up the hidden input
          } else {
            // We are adding a new address
            userAddresses.push(newAddress);
          }

          localStorage.setItem('userAddresses', JSON.stringify(userAddresses));
          
          displayAddresses();
          closeModal(addressModal);
          addressForm.reset();
        });
      }

      // Reset form when address modal is closed
      if (addressModal) {
        closeModalBtns.forEach(btn => {
          btn.addEventListener('click', () => {
            addressForm.reset();
            const existingIndexInput = document.getElementById('editingAddressIndex');
            if (existingIndexInput) {
              existingIndexInput.remove();
            }
            document.querySelector('#addressModal h3').textContent = 'New Address';
          });
        });
      }

      // Display addresses on load
      displayAddresses();

      // --- Logout Modal Logic ---
      const logoutLink = document.getElementById('logoutLink');
      const logoutModal = document.getElementById('logoutModal');
      const cancelLogout = document.getElementById('cancelLogout');

      if (logoutLink) {
        logoutLink.addEventListener('click', (e) => {
          e.preventDefault();
          logoutModal.classList.remove('hidden');
        });
      }

      if (cancelLogout) {
        cancelLogout.addEventListener('click', () => {
          logoutModal.classList.add('hidden');
        });
      }

      // --- Settings Logic ---

      const toggleSwitches = document.querySelectorAll('.toggle-switch');

      let userSettings = JSON.parse(localStorage.getItem('userSettings')) || { orderUpdates: true, promotions: true };



      function applySettings() {

        toggleSwitches.forEach(toggle => {

          const settingName = toggle.dataset.setting;

          toggle.checked = userSettings[settingName] !== false; // Default to true if not set

        });

      }



      toggleSwitches.forEach(toggle => {

        toggle.addEventListener('change', () => {

          const settingName = toggle.dataset.setting;

          userSettings[settingName] = toggle.checked;

          localStorage.setItem('userSettings', JSON.stringify(userSettings));

        });

      });

      // --- FAQ Accordion Logic ---
      const faqItems = document.querySelectorAll('.faq-item');
      if (faqItems) {
        faqItems.forEach(item => {
          const toggle = item.querySelector('.faq-toggle');
          if (toggle) {
            toggle.addEventListener('click', () => {
              item.classList.toggle('active');
            });
          }
        });
      }

      // --- Order History Logic ---

      const orderList = document.getElementById('orderList');

      const noOrdersPlaceholder = document.getElementById('noOrdersPlaceholder');

      let userOrders = JSON.parse(localStorage.getItem('userOrders')) || [];

      let currentFilter = 'all';



      function getStatusInfo(status) {

        switch (status) {

          case 'Delivered': return { text: 'Delivered', class: 'text-green-600' };

          case 'Shipped': return { text: 'In Transit', class: 'text-blue-600' };

          case 'Cancelled': return { text: 'Cancelled', class: 'text-red-600' };

          default: return { text: 'Pending', class: 'text-orange-500' };

        }

      }



      function getOrderStatus(order) {

        if (order.status === 'Delivered') return 'completed';

        if (order.status === 'Cancelled') return 'cancelled';

        if (order.status === 'Shipped') return 'to-receive';

        if (order.payment_status === 'pending') return 'to-pay';

        if (order.status === 'Processing') return 'to-ship';

        return 'all';

      }



      function filterOrders(filter) {

        if (filter === 'all') return userOrders;

        return userOrders.filter(order => getOrderStatus(order) === filter);

      }



      function renderOrders() {

        orderList.innerHTML = '';

        const filteredOrders = filterOrders(currentFilter);

        

        if (filteredOrders.length === 0) {

          orderList.appendChild(noOrdersPlaceholder);

          noOrdersPlaceholder.style.display = 'block';

        } else {

          noOrdersPlaceholder.style.display = 'none';

          // Sort by most recent first

          filteredOrders.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));

          

          filteredOrders.forEach(order => {

            const statusInfo = getStatusInfo(order.status);

            const firstItem = order.items[0];

            const additionalItems = order.items.length - 1;

            const itemSummary = `${firstItem.name}${additionalItems > 0 ? ` + ${additionalItems} more item(s)` : ''}`;



            const div = document.createElement('div');

            div.className = 'flex items-center justify-between border-b pb-4';

            div.innerHTML = `

              <div class="flex items-center gap-4">

                <img src="${firstItem.image}" alt="${firstItem.name}" class="rounded-lg w-16 h-16 object-cover">

                <div>

                  <h3 class="font-medium">Order ${order.id}</h3>

                  <p class="text-sm text-gray-500">Placed on ${new Date(order.timestamp).toLocaleDateString()}</p>

                  <p class="text-sm">${itemSummary}</p>

                </div>

              </div>

              <div class="text-right">

                <p class="font-semibold">‚±${order.total.toFixed(2)}</p>

                <span class="${statusInfo.class} text-xs font-medium">${statusInfo.text}</span>

                <div class="text-sm mt-1">

                  <a href="#" class="text-green-600 hover:underline">View Details</a>

                  ${order.status === 'Delivered' ? `· <a href="#" class="text-green-600 hover:underline">Reorder</a>` : ''}

                  ${order.status === 'Shipped' ? `· <a href="#" class="text-green-600 hover:underline">Track</a>` : ''}

                </div>

              </div>

            `;

            orderList.appendChild(div);

          });

        }

      }



      // --- Filter Button Handlers ---

      document.querySelectorAll('.order-filter-btn').forEach(btn => {

        btn.addEventListener('click', () => {

          // Remove active class from all buttons

          document.querySelectorAll('.order-filter-btn').forEach(b => {

            b.classList.remove('text-green-600', 'border-green-600');

            b.classList.add('text-gray-600', 'border-transparent');

          });

          // Add active class to clicked button

          btn.classList.remove('text-gray-600', 'border-transparent');

          btn.classList.add('text-green-600', 'border-green-600');

          

          // Update filter and render

          currentFilter = btn.dataset.filter;

          renderOrders();

        });

      });

      

      // Set "All" as active by default

      document.querySelector('[data-filter="all"]').classList.add('text-green-600', 'border-green-600');

      document.querySelector('[data-filter="all"]').classList.remove('text-gray-600', 'border-transparent');



      // --- Initial Render on Load ---

      renderOrders();

      applySettings();



      // --- Profile Edit Logic ---

      const editProfileBtn = document.getElementById('editProfileBtn');

      const cancelEditProfile = document.getElementById('cancelEditProfile');

      const profileDisplay = document.getElementById('profileDisplay');

      const profileEditForm = document.getElementById('profileEditForm');

      const profilePicInput = document.getElementById('profilePicInput');

      const editProfilePicPreview = document.getElementById('editProfilePicPreview');

      const removeProfilePic = document.getElementById('removeProfilePic');



      // Switch to edit mode

      if (editProfileBtn) {

        editProfileBtn.addEventListener('click', () => {

          profileDisplay.classList.add('hidden');

          profileEditForm.classList.remove('hidden');

          editProfileBtn.classList.add('hidden');

        });

      }



      // Cancel editing

      if (cancelEditProfile) {

        cancelEditProfile.addEventListener('click', () => {

          profileDisplay.classList.remove('hidden');

          profileEditForm.classList.add('hidden');

          editProfileBtn.classList.remove('hidden');

          // Reset form to original values from page load

          location.reload();

        });

      }



      // Handle profile picture upload

      if (profilePicInput) {

        profilePicInput.addEventListener('change', (e) => {

          const file = e.target.files[0];

          if (file) {

            // Show preview only - don't upload yet

            const reader = new FileReader();

            reader.onload = (event) => {

              const previewContainer = document.getElementById('editProfilePicPreview').parentElement;

              if (previewContainer) {

                // Replace whatever is there with a new img tag

                const newImg = document.createElement('img');

                newImg.id = 'editProfilePicPreview';

                newImg.src = event.target.result;

                newImg.alt = 'Profile';

                newImg.className = 'w-32 h-32 rounded-full border-4 border-green-100 object-cover';

                

                // Remove the old element and add the new one

                const oldEl = document.getElementById('editProfilePicPreview');

                previewContainer.replaceChild(newImg, oldEl);

                

                // Show the remove button

                const removeBtn = document.getElementById('removeProfilePic');

                if (removeBtn) removeBtn.classList.remove('hidden');

              }

            };

            reader.readAsDataURL(file);

          }

        });

      }



      // Remove profile picture modal handling

      const removePictureModal = document.getElementById('removePictureModal');

      const cancelRemovePicture = document.getElementById('cancelRemovePicture');

      const confirmRemovePicture = document.getElementById('confirmRemovePicture');



      if (removeProfilePic && removePictureModal) {

        removeProfilePic.addEventListener('click', () => {

          removePictureModal.classList.remove('hidden');

        });

      }



      if (cancelRemovePicture && removePictureModal) {

        cancelRemovePicture.addEventListener('click', () => {

          removePictureModal.classList.add('hidden');

        });

      }



      // Close modal when clicking outside

      if (removePictureModal) {

        removePictureModal.addEventListener('click', (e) => {

          if (e.target === removePictureModal) {

            removePictureModal.classList.add('hidden');

          }

        });

      }



      // Confirm remove profile picture

      if (confirmRemovePicture && removePictureModal) {

        confirmRemovePicture.addEventListener('click', async () => {

          removePictureModal.classList.add('hidden');

          showLoadingModal();



          try {

          const formData = new FormData();

          formData.append('action', 'remove_profile_picture');



            const response = await fetch('profile.php', {

              method: 'POST',

              body: formData

            });

            

            // Check if response is ok

            if (!response.ok) {

              throw new Error(`HTTP error! status: ${response.status}`);

            }

            

            const responseText = await response.text();

            console.log('Server response:', responseText);

            

            // Try to parse JSON with better error handling

            let result;

            try {

              result = JSON.parse(responseText);

            } catch (parseError) {

              console.error('JSON parse error:', parseError);

              console.error('Response text:', responseText);

              throw new Error('Invalid server response');

            }



            if (result.status === 'success') {

              showSuccessModal('Profile picture removed successfully!');

              

              // Update UI immediately

              const defaultAvatar = `<div class="w-32 h-32 mx-auto bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center text-white text-4xl font-bold shadow-lg">

                ${result.data.initials || '<?php echo strtoupper(substr($full_name, 0, 1)); ?>'}

              </div>`;

              

              const displayPic = document.getElementById('displayProfilePic');

              const editPic = document.getElementById('editProfilePic');

              if (displayPic) displayPic.innerHTML = defaultAvatar;

              if (editPic) editPic.innerHTML = defaultAvatar;

              

              // Hide remove button

              if (removeProfilePic) removeProfilePic.classList.add('hidden');

              

              // Update all profile pictures in header

              document.querySelectorAll('img[alt="Profile"]').forEach(img => {

                const parent = img.parentElement;

                img.remove();

                parent.innerHTML = `<div class="w-8 h-8 rounded-full cursor-pointer bg-green-600 flex items-center justify-center">

                  <i class="fas fa-user text-white text-sm"></i>

                </div>`;

              });

              

              setTimeout(() => {

                closeSuccessModal();

                location.reload();

              }, 1500);

            } else {

              showErrorModal(result.message || 'Failed to remove profile picture');

            }

          } catch (error) {

            console.error('Remove error:', error);

            showErrorModal('An error occurred. Please try again.');

          }

        });

      }



      // Save profile changes

      profileEditForm.addEventListener('submit', async (e) => {

        e.preventDefault();

        

        // Show loading modal

        showLoadingModal();

        

        // Get form data

        const formData = new FormData();

        formData.append('action', 'update_profile');

        formData.append('full_name', document.getElementById('editFullName').value);

        formData.append('email', document.getElementById('editEmail').value);

        formData.append('phone', document.getElementById('editPhone').value);

        formData.append('date_of_birth', document.getElementById('editDob').value);

        formData.append('gender', document.getElementById('editGender').value);

        formData.append('bio', document.getElementById('editBio').value);

        formData.append('address', document.getElementById('editAddress').value);

        

        // Include profile picture if one was selected

        const profilePicFile = profilePicInput.files[0];

        if (profilePicFile) {

          formData.append('profile_picture', profilePicFile);

          console.log('📸 Uploading profile picture:', profilePicFile.name, 'Size:', profilePicFile.size, 'bytes');

        }



        // Save to database via AJAX

        try {

          console.log('Submitting form with file:', profilePicFile ? profilePicFile.name : 'no file');

          

          const response = await fetch('profile.php', {

            method: 'POST',

            body: formData

          });

          

          // Get response text first for debugging

          const responseText = await response.text();

          console.log('📦 Server response status:', response.status);

          console.log('📦 Server response headers:', [...response.headers.entries()]);

          console.log('📦 Raw server response:', responseText.substring(0, 1000));

          

          // Check if response is ok

          if (!response.ok) {

            console.error('❌ HTTP Error Response:', responseText);

            throw new Error(`HTTP error! status: ${response.status}. Response: ${responseText.substring(0, 200)}`);

          }

          

          // Try to parse JSON with better error handling

          let result;

          try {

            result = JSON.parse(responseText);

          } catch (parseError) {

            console.error('JSON parse error:', parseError);

            console.error('Response text:', responseText);

            throw new Error('Invalid server response');

          }

          

          if (result.status === 'success') {

            // Show success modal instead of alert

            showSuccessModal(result.message || 'Profile updated successfully!');

            

            // Update all display elements with fresh data from database

            if (result.data) {

              const data = result.data;

              

              // Update display mode

              document.getElementById('displayFullName').textContent = data.full_name;

              document.getElementById('displayEmail').textContent = data.email;

              document.getElementById('displayPhone').textContent = data.phone || 'Not provided';

              document.getElementById('displayBio').textContent = data.bio || 'Welcome to Farmers Mall!';

              document.getElementById('displayAddress').textContent = data.address || 'Not provided';

              

              // Format and display date of birth

              if (data.date_of_birth) {

                const dobDate = new Date(data.date_of_birth);

                const formattedDob = dobDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

                document.getElementById('displayDob').textContent = formattedDob;

              } else {

                document.getElementById('displayDob').textContent = 'Not provided';

              }

              

              document.getElementById('displayGender').textContent = data.gender || 'Not specified';

              

              // Format member since

              if (data.created_at) {

                const createdDate = new Date(data.created_at);

                const formattedDate = createdDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });

                document.getElementById('displayMemberSince').textContent = formattedDate;

              }

              

              // Update profile pictures in all locations

              if (data.profile_picture) {

                const picPath = '../' + data.profile_picture;

                

                // Display mode profile picture

                const displayPicEl = document.getElementById('displayProfilePic');

                if (displayPicEl) {

                  displayPicEl.innerHTML = `<img src="${picPath}" alt="Profile" class="w-32 h-32 rounded-full border-4 border-green-100 object-cover">`;

                }

                

                // Sidebar profile picture

                const sidebarPicEl = document.getElementById('sidebarProfilePic');

                if (sidebarPicEl) {

                  sidebarPicEl.innerHTML = `<img src="${picPath}" alt="Profile" class="w-20 h-20 rounded-full mb-3 object-cover border-2 border-green-600">`;

                }

                

                // Edit mode preview

                const editPicEl = document.getElementById('editProfilePicPreview');

                if (editPicEl) {

                  editPicEl.innerHTML = `<img src="${picPath}" alt="Profile" class="w-32 h-32 rounded-full border-4 border-green-100 object-cover">`;

                }

                

                // Update navbar profile picture (find by button with profile image)

                const navProfileBtn = document.getElementById('profileDropdownBtn');

                if (navProfileBtn) {

                  navProfileBtn.innerHTML = `<img src="${picPath}" alt="Profile" class="w-8 h-8 rounded-full cursor-pointer object-cover">`;

                }

                

                // Show remove button

                const removeBtn = document.getElementById('removeProfilePic');

                if (removeBtn) removeBtn.classList.remove('hidden');

              }

              

              // Update sidebar name and email

              document.querySelector('aside h2').textContent = data.full_name;

              document.querySelector('aside p').textContent = data.email;

              

              // Update edit form with fresh data

              document.getElementById('editFullName').value = data.full_name;

              document.getElementById('editEmail').value = data.email;

              document.getElementById('editPhone').value = data.phone || '';

              document.getElementById('editDob').value = data.date_of_birth || '';

              document.getElementById('editGender').value = data.gender || '';

              document.getElementById('editBio').value = data.bio || '';

              document.getElementById('editAddress').value = data.address || '';

            }

            

            // Switch back to display mode

            profileDisplay.classList.remove('hidden');

            profileEditForm.classList.add('hidden');

            editProfileBtn.classList.remove('hidden');

            

            // Clear the file input

            profilePicInput.value = '';

          } else {

            console.error('❌ Update failed:', result.message);

            hideLoadingModal();

            showErrorModal(result.message || 'Failed to update profile. Please try again.');

          }

        } catch (error) { 

          console.error('💥 Profile update error:', error);

          hideLoadingModal();

          showErrorModal('An error occurred while updating your profile: ' + error.message);

         

        }
      });

      // --- My Address Edit Logic ---
      const cancelAddressEdit = document.getElementById('cancelAddressEdit');
      const saveAddress = document.getElementById('saveAddress');

      cancelAddressEdit.addEventListener('click', () => {
        addressDisplay.classList.remove('hidden');
        addressEdit.classList.add('hidden');
      });

      saveAddress.addEventListener('click', async () => {
        const newAddress = document.getElementById('editAddress').value;
        const formData = new FormData();
        formData.append('action', 'update_profile');
        formData.append('address', newAddress);
        // Include names to prevent them from being wiped out
        formData.append('first_name', '<?php echo addslashes($first_name); ?>');
        formData.append('last_name', '<?php echo addslashes($last_name); ?>');

        showLoadingModal();

        try {
          const response = await fetch('profile.php', {
            method: 'POST',
            body: formData
          });
          const result = await response.json();

          if (result.status === 'success') {
            showSuccessModal('Address updated successfully!');
            // Use a brief timeout to allow the user to see the success message
            setTimeout(() => location.reload(), 1500);
          } else {
            showErrorModal(result.message || 'Failed to update address.');
          }
        } catch (error) {
          console.error('Address update error:', error);
          showErrorModal('An error occurred while updating your address.');
        }
      });



      // Listen for cart updates within the same page

      window.addEventListener('cartUpdated', updateCartIcon);

      // Listen for storage events to sync orders across tabs
      window.addEventListener('storage', (e) => {
        if (e.key === 'userOrders') {
          console.log('Order history updated from another tab. Refreshing...');
          userOrders = JSON.parse(e.newValue) || [];
          renderOrders();
        }
      });

    });

  </script>
  <script src="../assets/js/profile-sync.js"></script>

</body>

</html>