<<<<<<< HEAD
﻿<?php
// Start output buffering at the very beginning
ob_start();

// Suppress all warnings and errors when handling AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    error_reporting(0);
    ini_set('display_errors', '0');
}

=======

<?php
>>>>>>> 26349651fc647432abab65c84a72b3e99720c97c
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

    // More secure MIME type validation
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
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

    if (!$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'User session expired. Please log in again.']);
        exit();
    }

    try {
        // Combine first and last name to full_name
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $full_name = trim("$first_name $last_name");

        $updateData = [
            'full_name' => $full_name,
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'date_of_birth' => trim($_POST['date_of_birth'] ?? null),
            'gender' => trim($_POST['gender'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
            'address' => trim($_POST['address'] ?? '')
        ];

        // If date is empty, it should be set to null in the DB
        if (empty($updateData['date_of_birth'])) {
            $updateData['date_of_birth'] = null;
        }

        // Handle profile picture upload
        if (isset($_FILES['profile_picture'])) {
            $oldProfilePicture = ($_SESSION['profile_picture'] ?? '');
            $newProfilePicPath = handleProfilePictureUpload($_FILES['profile_picture'], $user_id, $oldProfilePicture);
            if ($newProfilePicPath) {
                $updateData['profile_picture'] = $newProfilePicPath;
            }
        } elseif (isset($_POST['profile_picture']) && $_POST['profile_picture'] === 'remove') {
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



// Handle AJAX requests FIRST before any output

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
$userData = [];
if ($user_id) {
    $users = $api->select('users', ['id' => $user_id]);
    if (!empty($users)) {
        $userData = $users[0];
    }
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
$bio = $userData['bio'] ?? '';
$address = $userData['address'] ?? '';
<<<<<<< HEAD
=======
$city = 'Mati City'; // As per registration form
$province = 'Davao Oriental'; // As per registration form
>>>>>>> 26349651fc647432abab65c84a72b3e99720c97c
$created_at = $userData['created_at'] ?? '';



<<<<<<< HEAD
// Handle profile update via AJAX

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {

    // Debug logging - use absolute path

    $log_file = 'C:\tools_\The-Farmers-Mall\profile_upload.log';

    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . "Profile Update Request\n", FILE_APPEND);

    

    if (ob_get_level()) ob_end_clean(); // End and clean any output buffer

    header('Content-Type: application/json');

    // Fetch current user data for defaults
    $userData = [];
    $profile_picture = '';
    if ($user_id) {
        $users = $api->select('users', ['id' => $user_id]);
        if (!empty($users)) {
            $userData = $users[0];
            $profile_picture = $userData['profile_picture'] ?? '';
        }
    }

    

    file_put_contents($log_file, "POST: " . json_encode($_POST) . "\n", FILE_APPEND);

    

    $files_info = [];

    if (!empty($_FILES)) {

        foreach ($_FILES as $key => $file) {

            if (is_array($file['name'])) {

                $files_info[$key] = 'multiple files';

            } else {

                $files_info[$key] = ['name' => $file['name'] ?? 'none', 'size' => $file['size'] ?? 0, 'error' => $file['error'] ?? 4];

            }

        }

    }

    file_put_contents($log_file, "FILES: " . json_encode($files_info) . "\n", FILE_APPEND);

    

    try {

        $updateData = [

            'full_name' => trim($_POST['full_name'] ?? ''),

            'phone' => trim($_POST['phone'] ?? ''),

            'email' => trim($_POST['email'] ?? ''),

            'date_of_birth' => trim($_POST['date_of_birth'] ?? ''),

            'gender' => trim($_POST['gender'] ?? ''),

            'bio' => trim($_POST['bio'] ?? ''),

            'address' => trim($_POST['address'] ?? '')

        ];

        

        // Remove empty date_of_birth

        if (empty($updateData['date_of_birth'])) {

            unset($updateData['date_of_birth']);

        }

        

        // Handle profile picture upload if file is provided

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {

            file_put_contents($log_file, "Processing file upload...\n", FILE_APPEND);

            $file = $_FILES['profile_picture'];

            

            // Validate file - use simple extension check

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', ];

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            

            file_put_contents($log_file, "File extension: " . $extension . "\n", FILE_APPEND);

            

            if (!in_array($extension, $allowed_extensions)) {

                file_put_contents($log_file, "Invalid file type\n", FILE_APPEND);

                echo json_encode(['status' => 'error', 'message' => 'Only image files (JPG, PNG, GIF) are allowed']);

                exit();

            }

            

            if ($file['size'] > 5 * 1024 * 1024) { // 5MB

                echo json_encode(['status' => 'error', 'message' => 'File size must be less than 5MB']);

                exit();

            }

            

            // Create uploads/profiles directory if it doesn't exist

            $upload_dir = __DIR__ . '/../assets/profiles/';

            if (!is_dir($upload_dir)) {

                mkdir($upload_dir, 0755, true);

            }

            

            // Generate unique filename

            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

            $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;

            $filepath = $upload_dir . $filename;

            

            file_put_contents($log_file, "Attempting to save to: " . $filepath . "\n", FILE_APPEND);

            

            // Move uploaded file

            if (move_uploaded_file($file['tmp_name'], $filepath)) {

                file_put_contents($log_file, "File saved successfully\n", FILE_APPEND);

                // Delete old profile picture if exists

                if (!empty($profile_picture) && file_exists(__DIR__ . '/../' . $profile_picture)) {

                    @unlink(__DIR__ . '/../' . $profile_picture);

                }

                

                // Add to update data

                $updateData['profile_picture'] = 'assets/profiles/' . $filename;

                file_put_contents($log_file, "Profile picture path: " . $updateData['profile_picture'] . "\n", FILE_APPEND);

            } else {

                file_put_contents($log_file, "Failed to move uploaded file\n", FILE_APPEND);

            }

        } else {

            if (isset($_FILES['profile_picture'])) {

                file_put_contents($log_file, "File error: " . $_FILES['profile_picture']['error'] . "\n", FILE_APPEND);

            } else {

                file_put_contents($log_file, "No file uploaded\n", FILE_APPEND);

            }

        }

        

        // Update in database

        if ($user_id) {

            file_put_contents($log_file, "Updating database with data: " . json_encode($updateData) . "\n", FILE_APPEND);

            file_put_contents($log_file, "User ID: " . $user_id . "\n", FILE_APPEND);

            

            // Correct parameter order: update($table, $data, $filters)

            try {

                $api->update('users', $updateData, ['id' => $user_id]);

                file_put_contents($log_file, "Database update successful\n", FILE_APPEND);

            } catch (Exception $e) {

                file_put_contents($log_file, "Database update error: " . $e->getMessage() . "\n", FILE_APPEND);

                throw $e;

            }

            

            // Update session

            $_SESSION['full_name'] = $updateData['full_name'];

            $_SESSION['phone'] = $updateData['phone'];

            $_SESSION['email'] = $updateData['email'];

            $_SESSION['username'] = $updateData['full_name'];

            

            // Fetch updated data from database to return to client

            $updatedUser = $api->select('users', ['id' => $user_id]);

            $userData = !empty($updatedUser) ? $updatedUser[0] : [];

            

            echo json_encode([

                'status' => 'success', 

                'message' => 'Profile updated successfully!',

                'data' => [

                    'full_name' => $userData['full_name'] ?? '',

                    'email' => $userData['email'] ?? '',

                    'phone' => $userData['phone'] ?? '',

                    'profile_picture' => $userData['profile_picture'] ?? '',

                    'date_of_birth' => $userData['date_of_birth'] ?? '',

                    'gender' => $userData['gender'] ?? '',

                    'bio' => $userData['bio'] ?? '',

                    'address' => $userData['address'] ?? '',

                    'created_at' => $userData['created_at'] ?? ''

                ]

            ]);

        } else {

            file_put_contents($log_file, "ERROR: User ID not found\n", FILE_APPEND);

            echo json_encode(['status' => 'error', 'message' => 'User ID not found', 'debug' => 'no_user_id']);

        }

    } catch (Exception $e) {

        echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()]);

    }

    exit();

}



=======
>>>>>>> 26349651fc647432abab65c84a72b3e99720c97c
// Get order statistics
$total_orders = 0;
$total_spent = 0;
try {
<<<<<<< HEAD

    $orders = $api->select('orders', ['customer_id' => $user_id]);

=======
    $orders = $api->select('orders', ['user_id' => $user_id]);
>>>>>>> 26349651fc647432abab65c84a72b3e99720c97c
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
</head>
<body class="bg-gray-50 font-sans">



  <!-- Navbar -->
<header class="bg-white shadow-sm">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <!-- Logo -->
        <a href="user-homepage.php" class="flex items-center gap-2">
            <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                <i class="fas fa-leaf text-white text-lg"></i>
            </div>
            <span class="text-xl font-bold" style="color: #2E7D32;">Farmers Mall</span>
        </a>

        <!-- Search -->
        <div class="flex-1 mx-6">
            <form action="products.php" method="GET">
                <input 
                    type="text" 
                    name="search"
                    placeholder="Search for fresh produce, dairy, and more..."
                    class="w-full px-4 py-2 border rounded-full focus:ring-2 focus:ring-green-500 focus:outline-none"
                />
            </form>
        </div>

        <!-- Icons & Profile Dropdown -->
        <div class="flex items-center space-x-6">
            <a href="user-homepage.php" class="text-gray-600 hover:text-green-600"><i class="fa-solid fa-house"></i></a>
            <a href="message.php" class="text-gray-600"><i class="fa-regular fa-comment"></i></a>
            <a href="notification.php" class="text-gray-600"><i class="fa-regular fa-bell"></i></a>
            <a href="cart.php" class="text-gray-600 relative">
                <i class="fa-solid fa-cart-shopping"></i>
                <span id="cartBadge" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-semibold rounded-full px-1.5 min-w-[1.125rem] h-[1.125rem] flex items-center justify-center hidden">0</span>
            </a>

            <!-- Profile Dropdown -->
            <div class="relative inline-block text-left">
                <button id="profileDropdownBtn" class="flex items-center" data-userid="<?php echo htmlspecialchars($user_id ?? ''); ?>">
                    <?php if (!empty($profile_picture) && file_exists(__DIR__ . '/../' . $profile_picture)): ?>
                        <img src="<?php echo htmlspecialchars('../' . $profile_picture); ?>" 
                             alt="Profile" 
                             class="w-8 h-8 rounded-full cursor-pointer object-cover">
                    <?php else: ?>
                        <div class="w-8 h-8 rounded-full cursor-pointer bg-green-600 flex items-center justify-center">
                            <i class="fas fa-user text-white text-sm"></i>
                        </div>
                    <?php endif; ?>
                </button>

                <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-40 bg-white rounded-md shadow-lg border z-50">
                    <a href="profile.php" class="block px-4 py-2 hover:bg-gray-100">Profile</a>
                    <a href="profile.php#settings" class="block px-4 py-2 hover:bg-gray-100">Settings</a>
                    <a href="../auth/login.php" class="block px-4 py-2 text-red-600 hover:bg-gray-100">Logout</a>
                </div>
            </div>
            <!-- End Profile Dropdown -->

        </div>
    </div>
</header>
<!-- Dropdown JS -->
<script>
    const btn = document.getElementById('profileDropdownBtn');
    const menu = document.getElementById('profileDropdown');

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('hidden');
    });

    document.addEventListener('click', () => {
        menu.classList.add('hidden');
    });
</script>


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

          <i class="fas fa-box"></i> Order History

        </a>

        <a href="#my-address" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100">

          <i class="fas fa-map-marker-alt"></i> My Address

        </a>

        <a href="#payment-methods" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100">

          <i class="fas fa-credit-card"></i> Payment Methods

        </a>

        <a href="#settings" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100">

          <i class="fas fa-cog"></i> Settings

        </a>

        <a href="#help-support" class="sidebar-link flex items-center gap-2 px-4 py-2 rounded-lg hover:bg-gray-100">

          <i class="fas fa-question-circle"></i> Help & Support

        </a>

        <!-- Logout Button -->

        <div class="border-t pt-2 mt-2">

          <a href="#" id="logoutButton" class="flex items-center gap-2 px-4 py-2 rounded-lg text-red-600 hover:bg-red-50 font-medium">

            <i class="fas fa-sign-out-alt"></i> Logout

          </a>

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
                <p id="displayBio" class="text-gray-600 text-sm italic"><?php echo htmlspecialchars($bio ?: 'Welcome to Farmers Mall!'); ?></p>
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

          <!-- Bio -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
            <textarea id="editBio" name="bio" rows="3" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:outline-none" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($bio); ?></textarea>
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
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold text-gray-800">Order History</h2>
          <div class="flex items-center gap-3">
            <select class="border rounded-lg px-3 py-2 text-sm">
              <option>Last 3 months</option>
              <option>Last 6 months</option>
              <option>Last year</option>
            </select>
            <button class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
              <i class="fas fa-download"></i> Export
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
          <button id="editAddressBtn" class="border px-4 py-2 rounded-lg text-sm hover:bg-gray-100">
            <i class="fas fa-pen mr-1"></i> Edit
          </button>
        </div>
        <div id="addressDisplay" class="text-gray-600 leading-relaxed">
          <div id="noAddressPlaceholder" class="text-center text-gray-500 py-10 border-2 border-dashed rounded-lg">
            <i class="fas fa-map-marker-alt text-3xl mb-3"></i>
            <p>No saved addresses yet.</p>
          </div>
        </div>
      </section>

      <!-- Payment Methods Section -->
      <section id="payment-methods" class="content-section bg-white rounded-lg shadow p-6 hidden">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-xl font-semibold text-gray-800">Payment Methods</h2>
          <button id="addPaymentBtn" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700">
            <i class="fas fa-plus mr-1"></i> Add New Card
          </button>
        </div>
        <div id="paymentList" class="space-y-4">
          <div id="noPaymentPlaceholder" class="text-center text-gray-500 py-10">
            <i class="fas fa-credit-card text-3xl mb-3"></i>
            <p>No payment methods saved.</p>
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
        <div class="space-y-3" id="faqAccordion">
          <div class="border rounded-lg">
            <button class="faq-question w-full text-left p-4 flex justify-between items-center font-medium">
              <span>How do I track my order?</span>
              <i class="fas fa-chevron-down transition-transform"></i>
            </button>
            <div class="faq-answer hidden p-4 border-t text-sm text-gray-600">
              You can track your order from the "Order History" section. Click on "Track Order" for any item that is in transit.
            </div>
          </div>
          <div class="border rounded-lg">
            <button class="faq-question w-full text-left p-4 flex justify-between items-center font-medium">
              <span>What is your return policy?</span>
              <i class="fas fa-chevron-down transition-transform"></i>
            </button>
            <div class="faq-answer hidden p-4 border-t text-sm text-gray-600">
              Due to the nature of fresh produce, we only accept returns for damaged or incorrect items reported within 24 hours of delivery. Please contact our support team with a photo of the issue.
            </div>
          </div>
          <div class="border rounded-lg">
            <button class="faq-question w-full text-left p-4 flex justify-between items-center font-medium">
              <span>How do I change my delivery address?</span>
              <i class="fas fa-chevron-down transition-transform"></i>
            </button>
            <div class="faq-answer hidden p-4 border-t text-sm text-gray-600">
              You can manage your delivery locations in the "Saved Addresses" section. You can add, edit, or delete addresses there.
            </div>
          </div>
        </div>

      </section>



    </div>

  </main>



  <!-- Footer -->

  <footer class="text-white py-12" style="background-color: #1B5E20;">

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

  <div id="addressModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">

    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">

      <h3 class="font-semibold text-lg mb-4">Add New Address</h3>

      <form id="addressForm" class="space-y-4">

        <div>

          <label class="block text-sm font-medium text-gray-700">Full Name</label>

          <input type="text" id="address-name" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">

        </div>

        <div>

          <label class="block text-sm font-medium text-gray-700">Street Address</label>

          <input type="text" id="address-street" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">

        </div>

        <div class="grid grid-cols-2 gap-4">

          <div><label class="block text-sm font-medium text-gray-700">City</label><input type="text" id="address-city" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none"></div>

          <div><label class="block text-sm font-medium text-gray-700">Province</label><input type="text" id="address-province" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none"></div>

        </div>

        <div class="mt-6 flex justify-end gap-3">

          <button type="button" class="modal-close px-4 py-2 border rounded-md text-sm">Cancel</button>

          <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm">Save Address</button>

        </div>

      </form>

    </div>

  </div>



  <!-- Add Payment Modal -->

  <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">

    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">

      <h3 class="font-semibold text-lg mb-4">Add New Card</h3>

      <form id="paymentForm" class="space-y-4">

        <div>

          <label class="block text-sm font-medium text-gray-700">Card Number</label>

          <input type="text" id="card-number" placeholder="€¢€¢€¢€¢ €¢€¢€¢€¢ €¢€¢€¢€¢ €¢€¢€¢€¢" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">

        </div>

        <div>

          <label class="block text-sm font-medium text-gray-700">Cardholder Name</label>

          <input type="text" id="card-name" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none">

        </div>

        <div class="grid grid-cols-2 gap-4">

          <div><label class="block text-sm font-medium text-gray-700">Expiry (MM/YY)</label><input type="text" id="card-expiry" placeholder="MM/YY" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none"></div>

          <div><label class="block text-sm font-medium text-gray-700">CVV</label><input type="text" id="card-cvv" placeholder="€¢€¢€¢" required class="mt-1 w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-green-500 outline-none"></div>

        </div>

        <div class="mt-6 flex justify-end gap-3">

          <button type="button" class="modal-close px-4 py-2 border rounded-md text-sm">Cancel</button>

          <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm">Save Card</button>

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
      const addressEdit = document.getElementById('addressEdit');

      const closeModalBtns = document.querySelectorAll('.modal-close');



      const openModal = (modal) => modal.classList.remove('hidden');

      const closeModal = (modal) => modal.classList.add('hidden');
      
      editAddressBtn.addEventListener('click', () => {
        addressDisplay.classList.add('hidden');
        addressEdit.classList.remove('hidden');
      });

      closeModalBtns.forEach(btn => {

        btn.addEventListener('click', () => {

          closeModal(addressModal);
        });

      });

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

      // --- Order History Logic ---

      const orderList = document.getElementById('orderList');

      const noOrdersPlaceholder = document.getElementById('noOrdersPlaceholder');

      let userOrders = JSON.parse(localStorage.getItem('userOrders')) || [];



      function getStatusInfo(status) {

        switch (status) {

          case 'Delivered': return { text: 'Delivered', class: 'text-green-600' };

          case 'Shipped': return { text: 'In Transit', class: 'text-blue-600' };

          case 'Cancelled': return { text: 'Cancelled', class: 'text-red-600' };

          default: return { text: 'Pending', class: 'text-orange-500' };

        }

      }



      function renderOrders() {

        orderList.innerHTML = '';

        if (userOrders.length === 0) {

          orderList.appendChild(noOrdersPlaceholder);

          noOrdersPlaceholder.style.display = 'block';

        } else {

          noOrdersPlaceholder.style.display = 'none';

          // Sort by most recent first

          userOrders.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));

          

          userOrders.forEach(order => {

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

              const editPicEl = document.getElementById('editProfilePicPreview');

              if (editPicEl) {

                editPicEl.innerHTML = `<img src="${event.target.result}" alt="Profile" class="w-full h-full rounded-full object-cover">`;

                editPicEl.className = 'w-32 h-32 rounded-full border-4 border-green-100';

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

          

          // Check if response is ok

          if (!response.ok) {

            throw new Error(`HTTP error! status: ${response.status}`);

          }

          

          const responseText = await response.text();

          console.log('📦 Raw server response:', responseText.substring(0, 500)); // Log first 500 chars

          

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

                displayPicEl.innerHTML = `<img src="${picPath}" alt="Profile" class="w-32 h-32 rounded-full border-4 border-green-100 object-cover">`;

                

                // Sidebar profile picture

                const sidebarPicEl = document.getElementById('sidebarProfilePic');

                sidebarPicEl.innerHTML = `<img src="${picPath}" alt="Profile" class="w-20 h-20 rounded-full mb-3 object-cover border-2 border-green-600">`;

                

                // Navbar profile picture

                const navPicEl = document.getElementById('navProfilePic');

                navPicEl.innerHTML = `<img src="${picPath}" alt="Profile" class="w-8 h-8 rounded-full cursor-pointer ring-2 ring-green-600 object-cover">`;

                

                // Edit mode preview

                const editPicEl = document.getElementById('editProfilePicPreview');

                editPicEl.innerHTML = `<img src="${picPath}" alt="Profile" class="w-32 h-32 rounded-full border-4 border-green-100 object-cover">`;

                

                document.getElementById('removeProfilePic').classList.remove('hidden');

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

          showErrorModal('An error occurred while updating your profile. Please try again.');

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
<<<<<<< HEAD

      }



      async function updateCartIcon() {

        const cartBadge = document.getElementById('cartBadge');

        if (!cartBadge) return;

        try {
          // Fetch from database
          const response = await fetch('../api/cart.php');
          const data = await response.json();
          
          if (data.success && data.items) {
            const totalItems = data.items.reduce((sum, item) => sum + (item.quantity || 1), 0);
            cartBadge.textContent = totalItems;
            cartBadge.classList.toggle('hidden', totalItems === 0);
            return;
          }
        } catch (error) {
          console.log('Error loading cart count:', error);
        }

        // Fallback to localStorage
        const cart = JSON.parse(localStorage.getItem('cart')) || [];

        const totalItems = cart.reduce((sum, item) => sum + (item.quantity || 1), 0);

        cartBadge.textContent = totalItems;

        cartBadge.classList.toggle('hidden', totalItems === 0);

      }



      // Sync cart from database on load, then update icon

      syncCartFromDatabase();



      // Listen for cart updates from other pages/tabs

      window.addEventListener('storage', (e) => {

        if (e.key === 'cart') {

          updateCartIcon();

        }

=======
>>>>>>> 26349651fc647432abab65c84a72b3e99720c97c
      });



      // Listen for cart updates within the same page

      window.addEventListener('cartUpdated', updateCartIcon);

    });

  </script>
  <script src="../assets/js/profile-sync.js"></script>

</body>

</html>