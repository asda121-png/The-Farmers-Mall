<?php
// Start output buffering
ob_start();

// Enable error logging
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../debug_retailer_profile_errors.log');

// Suppress display but keep logging when handling AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}

session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/login.php');
    exit;
}

// Load database connection
require_once __DIR__ . '/../config/supabase-api.php';

// --- Function Definitions ---

/**
 * Handles profile picture upload for retailers
 */
function handleRetailerProfilePictureUpload(array $file, string $userId, string $oldProfilePicture): ?string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    // Validate file size (5MB limit)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('File size must be less than 5MB');
    }

    // Validate MIME type
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if (function_exists('mime_content_type')) {
        $mime_type = mime_content_type($file['tmp_name']);
    } elseif (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
    } else {
        $mime_type = $file['type'];
    }
    
    if (!in_array($mime_type, $allowed_mime_types)) {
        throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.');
    }

    // Create uploads directory
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
 * Handles retailer profile update
 */
function handleRetailerProfileUpdate()
{
    header('Content-Type: application/json');
    $api = getSupabaseAPI();
    $user_id = $_SESSION['user_id'] ?? null;

    error_log("=== RETAILER PROFILE UPDATE DEBUG ===");
    error_log("User ID: " . $user_id);
    error_log("FILES received: " . print_r($_FILES, true));
    error_log("POST data: " . print_r($_POST, true));

    if (!$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'User session expired. Please log in again.']);
        exit();
    }

    try {
        // User data updates
        $updateData = [];
        
        if (!empty(trim($_POST['full_name'] ?? ''))) {
            $updateData['full_name'] = trim($_POST['full_name']);
        }
        if (!empty(trim($_POST['phone'] ?? ''))) {
            $updateData['phone'] = trim($_POST['phone']);
        }
        if (!empty(trim($_POST['email'] ?? ''))) {
            $updateData['email'] = trim($_POST['email']);
        }

        // Retailer-specific data
        $retailerData = [];
        
        if (!empty(trim($_POST['shop_name'] ?? ''))) {
            $retailerData['shop_name'] = trim($_POST['shop_name']);
        }
        if (!empty(trim($_POST['business_address'] ?? ''))) {
            $retailerData['business_address'] = trim($_POST['business_address']);
        }
        if (!empty(trim($_POST['contact_number'] ?? ''))) {
            $retailerData['contact_number'] = trim($_POST['contact_number']);
        }

        // Handle profile picture upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            error_log("Profile picture file detected: " . $_FILES['profile_picture']['name']);
            $oldProfilePicture = ($_SESSION['profile_picture'] ?? '');
            $newProfilePicPath = handleRetailerProfilePictureUpload($_FILES['profile_picture'], $user_id, $oldProfilePicture);
            if ($newProfilePicPath) {
                error_log("New profile picture saved to: " . $newProfilePicPath);
                $updateData['profile_picture'] = $newProfilePicPath;
            }
        }
        
        if (isset($_POST['profile_picture']) && $_POST['profile_picture'] === 'remove') {
            $updateData['profile_picture'] = null;
            if (!empty($_SESSION['profile_picture']) && file_exists(__DIR__ . '/../' . $_SESSION['profile_picture'])) {
                @unlink(__DIR__ . '/../' . $_SESSION['profile_picture']);
            }
        }

        // Update user database (only if there's data to update)
        if (!empty($updateData)) {
            error_log("Updating users table with: " . print_r($updateData, true));
            $result = $api->update('users', $updateData, ['id' => $user_id]);
            error_log("User update result: " . print_r($result, true));
        }

        // Update session with new values
        if (isset($updateData['full_name'])) {
            $_SESSION['full_name'] = $updateData['full_name'];
            $_SESSION['username'] = $updateData['full_name'];
        }
        if (isset($updateData['profile_picture'])) {
            $_SESSION['profile_picture'] = $updateData['profile_picture'];
        }

        // Update retailer table
        $retailers = $api->select('retailers', ['user_id' => $user_id]);
        if (!empty($retailers)) {
            if (!empty($retailerData)) {
                error_log("Updating retailers table with: " . print_r($retailerData, true));
                $result = $api->update('retailers', $retailerData, ['user_id' => $user_id]);
                error_log("Retailer update result: " . print_r($result, true));
            }
        } else {
            if (!empty($retailerData)) {
                $retailerData['user_id'] = $user_id;
                error_log("Inserting into retailers table: " . print_r($retailerData, true));
                $api->insert('retailers', $retailerData);
            }
        }

        // Fetch updated data
        $updatedUser = $api->select('users', ['id' => $user_id]);
        $updatedRetailer = $api->select('retailers', ['user_id' => $user_id]);
        
        $responseData = !empty($updatedUser) ? $updatedUser[0] : [];
        if (!empty($updatedRetailer)) {
            $responseData = array_merge($responseData, $updatedRetailer[0]);
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Profile updated successfully!',
            'data' => $responseData
        ]);
    } catch (Exception $e) {
        error_log("EXCEPTION during profile update: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()]);
    }
    exit();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    handleRetailerProfileUpdate();
}

// Handle remove profile picture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_profile_picture') {
    header('Content-Type: application/json');
    try {
        $user_id = $_SESSION['user_id'] ?? null;
        if ($user_id) {
            $api = getSupabaseAPI();
            $users = $api->select('users', ['id' => $user_id]);
            if (!empty($users)) {
                $currentPic = $users[0]['profile_picture'] ?? '';
                $full_name = $users[0]['full_name'] ?? 'User';
                
                if (!empty($currentPic) && file_exists(__DIR__ . '/../' . $currentPic)) {
                    @unlink(__DIR__ . '/../' . $currentPic);
                }
            } else {
                $full_name = 'User';
            }
            
            $api->update('users', ['profile_picture' => ''], ['id' => $user_id]);
            $_SESSION['profile_picture'] = '';
            $initials = strtoupper(substr($full_name, 0, 1));
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Profile picture removed successfully!',
                'data' => ['initials' => $initials]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to remove picture: ' . $e->getMessage()]);
    }
    exit();
}

// Get user and retailer data
$api = getSupabaseAPI();
$user_id = $_SESSION['user_id'];
$userData = [];
$retailerData = [];

try {
    $users = $api->select('users', ['id' => $user_id]);
    if (!empty($users)) {
        $userData = $users[0];
    }
    
    $retailers = $api->select('retailers', ['user_id' => $user_id]);
    if (!empty($retailers)) {
        $retailerData = $retailers[0];
    }
} catch (Exception $e) {
    error_log("Error fetching data: " . $e->getMessage());
}

// Set default values
$email = $userData['email'] ?? $_SESSION['email'] ?? 'retailer@email.com';
$full_name = $userData['full_name'] ?? $_SESSION['username'] ?? 'Guest Retailer';
$phone = $userData['phone'] ?? '';
$profile_picture = $userData['profile_picture'] ?? $_SESSION['profile_picture'] ?? '';
$created_at = $userData['created_at'] ?? '';

// Retailer-specific data
$shop_name = $retailerData['shop_name'] ?? 'My Shop';
$business_address = $retailerData['business_address'] ?? '';
$business_permit = $retailerData['business_permit'] ?? '';
$contact_number = $retailerData['contact_number'] ?? $phone;
$permit_status = !empty($business_permit) ? 'Verified' : 'Not Uploaded';

// Get shop statistics
$total_products = 0;
$total_orders = 0;
$total_revenue = 0;

try {
    // Count products
    $products = $api->select('products', ['retailer_id' => $user_id]);
    $total_products = count($products);
    
    // Count orders and calculate revenue
    $orders = $api->select('orders', ['retailer_id' => $user_id]);
    $total_orders = count($orders);
    foreach ($orders as $order) {
        $total_revenue += floatval($order['total_amount'] ?? 0);
    }
} catch (Exception $e) {
    // Silent fail
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Retailer Profile - Farmers Mall</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f7fbf8;
    }
  </style>
</head>
<body class="bg-gray-50 font-sans">

  <!-- Header -->
  <header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
      <div class="flex items-center">
        <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center mr-2">
          <i class="fas fa-leaf text-white text-lg"></i>
        </div>
        <h1 class="text-2xl font-bold text-green-700">Farmers Mall</h1>
      </div>
      <div class="flex items-center space-x-6">
        <a href="retailer-dashboard2.php" class="text-gray-600 hover:text-green-600"><i class="fa-solid fa-house"></i></a>
        <a href="retailermessage.php" class="text-gray-600 hover:text-green-600"><i class="fa-regular fa-comment"></i></a>
        <a href="retailernotifications.php" class="text-gray-600 hover:text-green-600 relative">
          <i class="fa-regular fa-bell"></i>
        </a>

        <!-- Profile Dropdown -->
        <div class="relative inline-block text-left">
          <button id="profileDropdownBtn" class="flex items-center" title="<?php echo htmlspecialchars($shop_name); ?>">
            <?php if (!empty($profile_picture) && file_exists(__DIR__ . '/../' . $profile_picture)): ?>
              <img id="headerProfilePic" src="<?php echo htmlspecialchars('../' . $profile_picture); ?>?v=<?php echo time(); ?>" alt="<?php echo htmlspecialchars($shop_name); ?>" class="w-8 h-8 rounded-full cursor-pointer object-cover border-2 border-gray-200" onerror="this.src='../images/default-avatar.svg'">
            <?php else: ?>
              <div class="w-8 h-8 rounded-full cursor-pointer bg-green-600 flex items-center justify-center border-2 border-gray-200">
                <i class="fas fa-user text-white text-sm"></i>
              </div>
            <?php endif; ?>
          </button>
          <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-40 bg-white rounded-md shadow-lg border z-50">
            <a href="retailerprofile.php" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
            <a href="retailerprofile.php#settings" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
            <a href="../auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Layout with Modern Design -->
  <main class="max-w-7xl mx-auto px-6 py-8 flex gap-6 mb-20">

    <!-- Sidebar -->
    <aside class="w-72 bg-white rounded-lg shadow p-6">
      <!-- Profile Info -->
      <div class="flex flex-col items-center text-center mb-6">
        <?php if (!empty($profile_picture) && file_exists(__DIR__ . '/../' . $profile_picture)): ?>
          <img id="sidebarProfilePic" src="<?php echo htmlspecialchars('../' . $profile_picture); ?>" alt="Profile" class="w-20 h-20 rounded-full mb-3 object-cover border-2 border-green-600">
        <?php else: ?>
          <div id="sidebarProfilePic" class="w-20 h-20 rounded-full mb-3 bg-green-600 flex items-center justify-center">
            <i class="fas fa-store text-white text-3xl"></i>
          </div>
        <?php endif; ?>
        <h2 class="font-semibold"><?php echo htmlspecialchars($shop_name); ?></h2>
        <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($email); ?></p>
      </div>

      <!-- Navigation -->
      <nav class="space-y-2">
        <a href="#my-profile" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg bg-green-50 text-green-700 font-medium">
          <i class="fas fa-user"></i> My Profile
        </a>
        <a href="#shop-details" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100">
          <i class="fas fa-store"></i> Shop Details
        </a>
        <a href="#business-permit" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100">
          <i class="fas fa-file-contract"></i> Business Permit
        </a>
        <a href="#payment-details" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100">
          <i class="fas fa-credit-card"></i> Payment Details
        </a>
        <a href="#settings" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100">
          <i class="fas fa-cog"></i> Settings
        </a>
        <a href="#help-support" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100">
          <i class="fas fa-question-circle"></i> Help & Support
        </a>
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
                  <i class="fas fa-store text-white text-4xl"></i>
                </div>
              <?php endif; ?>
            </div>
            <div class="flex-1">
              <h3 id="displayShopName" class="text-2xl font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($shop_name); ?></h3>
              <p id="displayEmail" class="text-gray-600 mb-3"><?php echo htmlspecialchars($email); ?></p>
              <?php if (!empty($full_name)): ?>
              <p class="text-gray-600 text-sm">
                <i class="fas fa-store text-green-600 mr-1"></i>
                <span id="displayShopName"><?php echo htmlspecialchars($shop_name); ?></span>
              </p>
              <?php endif; ?>
            </div>
          </div>

          <!-- Personal & Business Information Grid -->
          <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-gray-50 p-4 rounded-lg">
              <label class="text-xs text-gray-500 uppercase tracking-wide">Phone Number</label>
              <p id="displayPhone" class="text-gray-800 font-medium mt-1"><?php echo htmlspecialchars($phone ?: 'Not provided'); ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <label class="text-xs text-gray-500 uppercase tracking-wide">Contact Number</label>
              <p id="displayContactNumber" class="text-gray-800 font-medium mt-1"><?php echo htmlspecialchars($contact_number ?: 'Not provided'); ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <label class="text-xs text-gray-500 uppercase tracking-wide">Business Location</label>
              <p id="displayBusinessAddress" class="text-gray-800 font-medium mt-1"><?php echo htmlspecialchars($business_address ?: 'Not provided'); ?></p>
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
            <div class="bg-gray-50 p-4 rounded-lg">
              <label class="text-xs text-gray-500 uppercase tracking-wide">Business Permit Status</label>
              <p id="displayPermitStatus" class="text-gray-800 font-medium mt-1">
                <?php if ($permit_status === 'Verified'): ?>
                  <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Verified</span>
                <?php else: ?>
                  <span class="text-orange-500"><i class="fas fa-exclamation-circle mr-1"></i>Not Uploaded</span>
                <?php endif; ?>
              </p>
            </div>
          </div>

          <!-- Shop Statistics -->
          <div class="pt-6 border-t">
            <h4 class="font-semibold mb-4 text-gray-700">Shop Statistics</h4>
            <div class="grid grid-cols-3 gap-4">
              <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-2xl font-bold text-green-600"><?php echo $total_products; ?></p>
                <p class="text-sm text-gray-600 mt-1">Total Products</p>
              </div>
              <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-2xl font-bold text-blue-600"><?php echo $total_orders; ?></p>
                <p class="text-sm text-gray-600 mt-1">Total Orders</p>
              </div>
              <div class="text-center p-4 bg-purple-50 rounded-lg">
                <p class="text-2xl font-bold text-purple-600">₱<?php echo number_format($total_revenue, 2); ?></p>
                <p class="text-sm text-gray-600 mt-1">Total Revenue</p>
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

          <!-- Personal & Business Information -->
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
              <input type="tel" id="editPhone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none" placeholder="09XXXXXXXXX">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Shop Name *</label>
              <input type="text" id="editShopName" name="shop_name" value="<?php echo htmlspecialchars($shop_name); ?>" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
              <input type="tel" id="editContactNumber" name="contact_number" value="<?php echo htmlspecialchars($contact_number); ?>" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none" placeholder="09XXXXXXXXX">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Business Location</label>
              <input type="text" id="editBusinessAddress" name="business_address" value="<?php echo htmlspecialchars($business_address); ?>" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none" placeholder="Shop address">
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

      <!-- Shop Details Section -->
      <section id="shop-details" class="content-section bg-white rounded-lg shadow p-6 hidden">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Shop Details</h2>
        <div class="space-y-4">
          <div class="bg-gray-50 p-4 rounded-lg">
            <label class="text-xs text-gray-500 uppercase tracking-wide">Shop Name</label>
            <p class="text-gray-800 font-medium mt-1"><?php echo htmlspecialchars($shop_name); ?></p>
          </div>
          <div class="bg-gray-50 p-4 rounded-lg">
            <label class="text-xs text-gray-500 uppercase tracking-wide">Business Address</label>
            <p class="text-gray-800 font-medium mt-1"><?php echo htmlspecialchars($business_address ?: 'Not provided'); ?></p>
          </div>
          <div class="bg-gray-50 p-4 rounded-lg">
            <label class="text-xs text-gray-500 uppercase tracking-wide">Contact Number</label>
            <p class="text-gray-800 font-medium mt-1"><?php echo htmlspecialchars($contact_number ?: 'Not provided'); ?></p>
          </div>
        </div>
      </section>

      <!-- Business Permit Section -->
      <section id="business-permit" class="content-section bg-white rounded-lg shadow p-6 hidden">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Business Permit</h2>
        <div class="space-y-4">
          <div class="bg-gray-50 p-4 rounded-lg">
            <label class="text-xs text-gray-500 uppercase tracking-wide">Permit Status</label>
            <p class="text-gray-800 font-medium mt-1">
              <?php if ($permit_status === 'Verified'): ?>
                <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Verified</span>
              <?php else: ?>
                <span class="text-orange-500"><i class="fas fa-exclamation-circle mr-1"></i>Not Uploaded</span>
              <?php endif; ?>
            </p>
          </div>
          <?php if (!empty($business_permit)): ?>
            <div class="mt-4">
              <button type="button" id="viewPermitBtn" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
                <i class="fas fa-eye mr-1"></i> View Business Permit
              </button>
            </div>
          <?php else: ?>
            <div class="text-center py-10 border-2 border-dashed rounded-lg">
              <i class="fas fa-file-upload text-3xl text-gray-400 mb-3"></i>
              <p class="text-gray-500">No business permit uploaded yet</p>
              <button class="mt-4 bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
                <i class="fas fa-upload mr-1"></i> Upload Permit
              </button>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <!-- Payment Details Section -->
      <section id="payment-details" class="content-section bg-white rounded-lg shadow p-6 hidden">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold text-gray-800">Payment Details</h2>
          <button class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
            <i class="fas fa-plus mr-1"></i> Add Payment Method
          </button>
        </div>
        <div class="text-center text-gray-500 py-10 border-2 border-dashed rounded-lg">
          <i class="fas fa-credit-card text-3xl mb-3"></i>
          <p>No payment methods saved.</p>
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
                  <p class="font-medium text-sm">Order Notifications</p>
                  <p class="text-xs text-gray-500">Receive updates when you get new orders.</p>
                </div>
                <input type="checkbox" class="toggle-switch" checked>
              </label>
              <label class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                  <p class="font-medium text-sm">Product Updates</p>
                  <p class="text-xs text-gray-500">Get notified about product performance.</p>
                </div>
                <input type="checkbox" class="toggle-switch" checked>
              </label>
            </div>
          </div>
        </div>
      </section>

      <!-- Help & Support Section -->
      <section id="help-support" class="content-section bg-white rounded-lg shadow p-6 hidden">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Help & Support</h2>
        <div class="space-y-3">
          <div class="border rounded-lg">
            <button class="faq-question w-full text-left p-4 flex justify-between items-center font-medium">
              <span>How do I add products to my shop?</span>
              <i class="fas fa-chevron-down transition-transform"></i>
            </button>
            <div class="faq-answer hidden p-4 border-t text-sm text-gray-600">
              You can add products from the Products section in your dashboard. Click "Add New Product" and fill in the required details.
            </div>
          </div>
          <div class="border rounded-lg">
            <button class="faq-question w-full text-left p-4 flex justify-between items-center font-medium">
              <span>How do I manage my orders?</span>
              <i class="fas fa-chevron-down transition-transform"></i>
            </button>
            <div class="faq-answer hidden p-4 border-t text-sm text-gray-600">
              Go to the Orders section to view, process, and manage all your customer orders. You can update order status and communicate with customers.
            </div>
          </div>
        </div>
      </section>

    </div>
  </main>


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

  <!-- Business Permit Modal -->
  <div id="permitModal" class="fixed inset-0 bg-black bg-opacity-60 hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-lg p-4 w-full max-w-2xl relative">
      <div class="flex justify-between items-center border-b pb-3 mb-4">
        <h3 class="font-semibold text-lg">Business Permit</h3>
        <button id="closePermitModal" class="text-gray-500 hover:text-black text-2xl">&times;</button>
      </div>
      <div id="permitImageContainer" class="max-h-[70vh] overflow-auto">
        <?php if (!empty($business_permit)): ?>
          <img src="<?php echo htmlspecialchars('../' . $business_permit); ?>" alt="Business Permit" class="w-full">
        <?php else: ?>
          <p class="text-center text-gray-500">No permit uploaded</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Profile dropdown toggle
      const profileDropdownBtn = document.getElementById('profileDropdownBtn');
      const profileDropdown = document.getElementById('profileDropdown');
      
      if (profileDropdownBtn && profileDropdown) {
        profileDropdownBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          profileDropdown.classList.toggle('hidden');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
          if (!profileDropdownBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.add('hidden');
          }
        });
      }

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
        location.reload();
      }

      function closeErrorModal() {
        errorModal.classList.add('hidden');
      }

      document.getElementById('closeSuccessModal').addEventListener('click', closeSuccessModal);
      document.getElementById('closeErrorModal').addEventListener('click', closeErrorModal);

      // Close modals when clicking outside
      successModal.addEventListener('click', (e) => {
        if (e.target === successModal) closeSuccessModal();
      });
      errorModal.addEventListener('click', (e) => {
        if (e.target === errorModal) closeErrorModal();
      });

      // Sidebar navigation
      const sidebarLinks = document.querySelectorAll('.sidebar-link');
      const contentSections = document.querySelectorAll('.content-section');

      function updateContent(hash) {
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
          e.preventDefault();
          updateContent(link.hash);
        });
      });

      // Show content based on initial URL hash
      updateContent(window.location.hash);

      // Profile edit logic
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
                const newImg = document.createElement('img');
                newImg.id = 'editProfilePicPreview';
                newImg.src = event.target.result;
                newImg.alt = 'Profile';
                newImg.className = 'w-32 h-32 rounded-full border-4 border-green-100 object-cover';
                
                const oldEl = document.getElementById('editProfilePicPreview');
                previewContainer.replaceChild(newImg, oldEl);
                
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

            const response = await fetch('retailerprofile.php', {
              method: 'POST',
              body: formData
            });

            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseText = await response.text();
            console.log('Server response:', responseText);

            let result;
            try {
              result = JSON.parse(responseText);
            } catch (parseError) {
              console.error('JSON parse error:', parseError);
              throw new Error('Invalid server response');
            }

            if (result.status === 'success') {
              showSuccessModal('Profile picture removed successfully!');
              
              const defaultAvatar = `<div class="w-32 h-32 rounded-full border-4 border-green-100 bg-green-600 flex items-center justify-center">
                <i class="fas fa-user text-white text-4xl"></i>
              </div>`;
              
              const displayPic = document.getElementById('displayProfilePic');
              const sidebarPic = document.getElementById('sidebarProfilePic');
              if (displayPic) displayPic.outerHTML = defaultAvatar;
              if (sidebarPic) sidebarPic.outerHTML = defaultAvatar.replace('w-32 h-32', 'w-20 h-20');
              
              if (removeProfilePic) removeProfilePic.classList.add('hidden');
              
              setTimeout(() => {
                closeSuccessModal();
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
        
        showLoadingModal();
        
        const formData = new FormData();
        formData.append('action', 'update_profile');
        formData.append('full_name', document.getElementById('editFullName').value);
        formData.append('email', document.getElementById('editEmail').value);
        formData.append('phone', document.getElementById('editPhone').value);
        formData.append('shop_name', document.getElementById('editShopName').value);
        formData.append('business_address', document.getElementById('editBusinessAddress').value);
        formData.append('contact_number', document.getElementById('editContactNumber').value);
        
        const profilePicFile = profilePicInput.files[0];
        if (profilePicFile) {
          formData.append('profile_picture', profilePicFile);
          console.log('Uploading profile picture:', profilePicFile.name);
        }

        try {
          const response = await fetch('retailerprofile.php', {
            method: 'POST',
            body: formData
          });

          const responseText = await response.text();
          console.log('Server response:', responseText.substring(0, 1000));

          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }

          let result;
          try {
            result = JSON.parse(responseText);
          } catch (parseError) {
            console.error('JSON parse error:', parseError);
            throw new Error('Invalid server response');
          }

          if (result.status === 'success') {
            showSuccessModal(result.message || 'Profile updated successfully!');
            
            if (result.data) {
              const data = result.data;
              
              // Update display mode with shop name as primary
              if (data.shop_name) {
                document.getElementById('displayShopName').textContent = data.shop_name || 'My Shop';
              }
              if (data.full_name) {
                const fullNameEl = document.getElementById('displayFullName');
                if (fullNameEl) fullNameEl.textContent = data.full_name;
              }
              document.getElementById('displayEmail').textContent = data.email;
              document.getElementById('displayPhone').textContent = data.phone || 'Not provided';
              document.getElementById('displayBusinessAddress').textContent = data.business_address || 'Not provided';
              document.getElementById('displayContactNumber').textContent = data.contact_number || 'Not provided';
              
              if (data.created_at) {
                const createdDate = new Date(data.created_at);
                const formattedDate = createdDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
                document.getElementById('displayMemberSince').textContent = formattedDate;
              }
              
              // Update profile pictures
              if (data.profile_picture) {
                const picPath = '../' + data.profile_picture;
                
                const displayPicEl = document.getElementById('displayProfilePic');
                if (displayPicEl) {
                  displayPicEl.innerHTML = `<img src="${picPath}" alt="Profile" class="w-32 h-32 rounded-full border-4 border-green-100 object-cover">`;
                }
                
                const sidebarPicEl = document.getElementById('sidebarProfilePic');
                if (sidebarPicEl) {
                  sidebarPicEl.innerHTML = `<img src="${picPath}" alt="Profile" class="w-20 h-20 rounded-full mb-3 object-cover border-2 border-green-600">`;
                }
                
                const editPicEl = document.getElementById('editProfilePicPreview');
                if (editPicEl) {
                  editPicEl.outerHTML = `<img id="editProfilePicPreview" src="${picPath}" alt="Profile" class="w-32 h-32 rounded-full border-4 border-green-100 object-cover">`;
                }
                
                // Update header profile picture
                const headerPicEl = document.getElementById('headerProfilePic');
                if (headerPicEl) {
                  headerPicEl.src = picPath + '?v=' + new Date().getTime();
                }
                
                const removeBtn = document.getElementById('removeProfilePic');
                if (removeBtn) removeBtn.classList.remove('hidden');
              }
              
              // Update sidebar with shop name
              const sidebarTitle = document.querySelector('aside h2');
              if (sidebarTitle && data.shop_name) {
                sidebarTitle.textContent = data.shop_name;
              }
              document.querySelector('aside p').textContent = data.email;
            }
            
            profileDisplay.classList.remove('hidden');
            profileEditForm.classList.add('hidden');
            editProfileBtn.classList.remove('hidden');
            profilePicInput.value = '';
          } else {
            console.error('Update failed:', result.message);
            hideLoadingModal();
            showErrorModal(result.message || 'Failed to update profile. Please try again.');
          }
        } catch (error) {
          console.error('Profile update error:', error);
          hideLoadingModal();
          showErrorModal('An error occurred while updating your profile: ' + error.message);
        }
      });

      // Business permit modal
      const permitModal = document.getElementById('permitModal');
      const viewPermitBtn = document.getElementById('viewPermitBtn');
      const closePermitModal = document.getElementById('closePermitModal');

      if (viewPermitBtn) {
        viewPermitBtn.addEventListener('click', () => {
          permitModal.classList.remove('hidden');
        });
      }

      if (closePermitModal) {
        closePermitModal.addEventListener('click', () => {
          permitModal.classList.add('hidden');
        });
      }

      if (permitModal) {
        permitModal.addEventListener('click', (e) => {
          if (e.target === permitModal) {
            permitModal.classList.add('hidden');
          }
        });
      }

      // FAQ accordion
      const faqQuestions = document.querySelectorAll('.faq-question');
      faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
          const answer = question.nextElementSibling;
          const icon = question.querySelector('i');
          
          answer.classList.toggle('hidden');
          icon.classList.toggle('rotate-180');
        });
      });
    });
  </script>
</body>
</html>
  