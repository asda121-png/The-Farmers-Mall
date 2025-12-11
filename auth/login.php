<?php
// PHP SCRIPT START - SUPABASE LOGIN

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submitted'])) {
// Load Supabase API
require_once __DIR__ . '/../config/supabase-api.php';

$login_status = '';
$login_message = '';
$redirect_url = '';

// Function to send JSON response and exit
function send_json_response($status, $message, $redirect_url = '') {
    // Ensure we're outputting JSON before we send anything
    header('Content-Type: application/json');

    echo json_encode([
        'status' => $status,
        'message' => $message,
        'redirect_url' => $redirect_url
    ]);
    exit();
}

    try {
        $api = getSupabaseAPI();
        
        $input_identifier = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($input_identifier) || empty($password)) {
            send_json_response('error', 'Please enter both email/username and password.');
        } else {
            // Check for hardcoded admin credentials first
            if ($input_identifier === 'Admin1234@gmail.com' && $password === 'Admin123') {
                $_SESSION['loggedin'] = true;
                $_SESSION['role'] = 'admin';
                $_SESSION['username'] = 'Administrator';
                $_SESSION['user_id'] = 'admin';
                $_SESSION['email'] = 'Admin1234@gmail.com';
                $_SESSION['full_name'] = 'Administrator';
                
                send_json_response('success', 'Admin login successful! Redirecting...', '../admin/admin-dashboard.php');
            } else {
                // Query Supabase for user by email or username
                $user = null;
                
                // Try to find by email first
                $users = $api->select('users', ['email' => $input_identifier]);
                
                if (!empty($users)) {
                    $user = $users[0];
                } else {
                    // Try to find by username
                    $users = $api->select('users', ['username' => $input_identifier]);
                    if (!empty($users)) {
                        $user = $users[0];
                    }
                }
                
                if ($user) {
                    
                    // Verify password
                    if (password_verify($password, $user['password_hash'])) {
                        // Check if account is active
                        if ($user['status'] !== 'active') {
                            send_json_response('error', 'Your account is not active. Please contact support.');
                        } else {
                            // Login successful
                            $_SESSION['loggedin'] = true;
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['email'] = $user['email'];
                            $_SESSION['username'] = $user['username'] ?? $user['full_name'];
                            $_SESSION['full_name'] = $user['full_name'];
                            $_SESSION['phone'] = $user['phone'] ?? '';
                            $_SESSION['address'] = $user['address'] ?? '';
                            $_SESSION['role'] = $user['user_type'];
                            $_SESSION['user_type'] = $user['user_type'];
                            
                            // Redirect based on user type
                            if ($user['user_type'] === 'admin') {
                                send_json_response('success', 'Admin login successful! Redirecting...', '../admin/admin-dashboard.php');
                            } elseif ($user['user_type'] === 'retailer') {
                                send_json_response('success', 'Retailer login successful! Redirecting...', '../retailer/retailer-dashboard2.php');
                            } else {
                                // Default to customer
                                send_json_response('success', 'Login successful! Redirecting...', '../user/user-homepage.php');
                            }
                        }
                    } else {
                        send_json_response('error', 'Invalid email/username or password.');
                    }
                } else {
                    send_json_response('error', 'Invalid email/username or password.');
                }
            }
        }
    } catch (Exception $e) {
        error_log("Login Error: " . $e->getMessage());
        send_json_response('error', 'A system error occurred. Please try again later.');
    }
    // After processing, we stop the script.
    exit();
}
// If it's not a POST request, the script will continue and render the HTML below.
?>
<!-- START: Login Modal -->
<div id="loginModal" class="login-modal hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
  <!-- Modal Content - Restored Original Layout -->
  <div class="modal-content w-full max-w-5xl bg-white rounded-2xl shadow-xl overflow-hidden relative lg:flex">
    <!-- Close Button -->
    <button id="closeLoginModal" class="absolute top-6 left-6 h-12 w-12 flex items-center justify-center bg-black bg-opacity-30 rounded-full text-white hover:bg-opacity-50 transition-all z-20">
      <i class="fas fa-arrow-left text-xl"></i>
    </button>

    <!-- Left Side - Branding with Image -->
    <div class="hidden lg:flex lg:w-1/2 p-16 flex-col justify-center items-center text-white text-center relative bg-cover bg-center" style="background-image: url('../images/img.png'); min-height: 680px;">
      <!-- Overlay -->
      <div class="absolute inset-0 bg-green-800 opacity-60"></div>
      <!-- Content -->
      <div class="relative z-10">
        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg" style="animation: float 3s ease-in-out infinite;">
          <i class="fas fa-leaf text-green-600 text-4xl"></i>
        </div>
        <h2 class="text-3xl font-bold">Farmer's Mall</h2>
        <p class="mt-2 text-green-100">Connecting farmers and consumers directly, offering fresh, local, and organic produce.</p>
      </div>
    </div>

    <!-- Right Side - Form -->
    <div class="w-full lg:w-1/2 p-8 sm:p-16 flex flex-col justify-center" style="min-height: 680px;">
      <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Welcome Back</h2>
        <p class="text-gray-600 mt-1">Sign in to your account</p>
      </div>
      
      <form id="loginForm" method="POST" class="space-y-6">
          <input type="hidden" name="login_submitted" value="1">
          <!-- Email Input -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email or Username</label>
            <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2 transition-all">
              <i class="far fa-envelope text-gray-400 mr-2"></i>
              <input id="login_email" name="email" type="text" placeholder="Enter your email or username" class="w-full outline-none text-gray-700 py-1" required>
            </div>
            <p id="loginEmailError" class="text-red-600 text-sm mt-1 hidden">Invalid email/username or password.</p>
          </div>

          <!-- Password Input -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2 transition-all">
              <i class="fas fa-lock text-gray-400 mr-2"></i>
              <input id="login_password" name="password" type="password" placeholder="Enter your password" class="w-full outline-none text-gray-700 py-1" required>
            </div>
            <p id="loginPasswordError" class="text-red-600 text-sm mt-1 hidden">Invalid email/username or password.</p>
          </div>

          <div class="flex items-center justify-between text-sm text-gray-600">
            <label class="flex items-center">
              <input type="checkbox" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded mr-2">
              Remember me
            </label>
            <a href="#" class="text-green-600 hover:underline">Forgot Password?</a>
          </div>
          
          <button id="loginSubmitBtn" type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition-colors shadow-md hover:shadow-lg">
            <span class="btn-text">Login</span>
            <i class="fas fa-sign-in-alt ml-2"></i>
          </button>

          <p class="text-center text-sm text-gray-600 mt-6">
            Don’t have an account?
            <a href="../auth/register.php" class="text-green-600 font-medium hover:underline">Create an Account</a>
          </p>

          <small class="block text-center text-xs text-gray-500 mt-4">
            By continuing, you agree to our
            <a href="#" class="text-green-600 font-medium hover:underline">Terms of Service</a> and
            <a href="#" class="text-green-600 font-medium hover:underline">Privacy Policy.</a>
          </small>
        </form>
    </div>
  </div>
</div>
<!-- END: Login Modal -->