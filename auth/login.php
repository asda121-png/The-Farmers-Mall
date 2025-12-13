
<?php
// PHP SCRIPT START - SUPABASE LOGIN

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load Google OAuth configuration
require_once __DIR__ . '/../config/google-oauth.php';
$googleAuthUrl = '';
try {
    $oauth = new GoogleOAuth();
    $googleAuthUrl = $oauth->getAuthorizationUrl();
} catch (Exception $e) {
    error_log("Google OAuth initialization error: " . $e->getMessage());
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
                
                send_json_response('success', 'Admin login successful! Redirecting...', '../public/loading.php?redirect_to=../admin/admin-dashboard.php');
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
                                send_json_response('success', 'Admin login successful! Redirecting...', '../public/loading.php?redirect_to=../admin/admin-dashboard.php');
                            } elseif ($user['user_type'] === 'retailer') {
                                send_json_response('success', 'Retailer login successful! Redirecting...', '../public/loading.php?redirect_to=../retailer/retailer-dashboard2.php');
                            } else {
                                // Default to customer
                                send_json_response('success', 'Login successful! Redirecting...', '../public/loading.php?redirect_to=../user/user-homepage.php');
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
            <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2 transition-all relative">
              <i class="fas fa-lock text-gray-400 mr-2"></i>
              <input id="login_password" name="password" type="password" placeholder="Enter your password" class="w-full outline-none text-gray-700 py-1" required>
              <button type="button" id="toggleLoginPassword" class="absolute right-3 text-gray-500 hover:text-gray-700">
                <i class="fas fa-eye"></i>
              </button>
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
        </form>

      <!-- Divider -->
      <div class="relative my-6">
        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-300"></div></div>
        <div class="relative flex justify-center text-sm"><span class="px-2 bg-white text-gray-500">or</span></div>
      </div>

      <!-- Google Sign-In Button -->
      <button type="button" id="googleLoginBtn" class="w-full bg-white border-2 border-gray-300 text-gray-700 py-3 rounded-lg hover:bg-gray-50 transition duration-150 flex items-center justify-center gap-2 font-medium">
        <svg class="w-5 h-5" viewBox="0 0 24 24">
          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
          <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
        </svg>
        <span id="googleLoginBtnText">Continue with Google</span>
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
    </div>
  </div>
</div>
<!-- END: Login Modal -->

<script>
  // Google OAuth Login Handler
  document.addEventListener('DOMContentLoaded', function() {
    const googleLoginBtn = document.getElementById('googleLoginBtn');
    if (googleLoginBtn) {
      googleLoginBtn.addEventListener('click', function() {
        const googleAuthUrl = '<?php echo $googleAuthUrl; ?>';
        if (googleAuthUrl) {
          // Redirect to Google OAuth
          window.location.href = googleAuthUrl;
        } else {
          alert('Google authentication is not configured. Please try regular login.');
        }
      });
    }
  });
  
  // Regular login form submission
  document.getElementById('loginForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const form = e.target;
    const submitBtn = document.getElementById('loginSubmitBtn');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
    
    try {
      const response = await fetch(form.action || window.location.href, {
        method: 'POST',
        body: new FormData(form)
      });
      
      const data = await response.json();
      
      if (data.status === 'success') {
        window.location.href = data.redirect_url;
      } else {
        document.getElementById('loginEmailError').classList.remove('hidden');
        document.getElementById('loginPasswordError').classList.remove('hidden');
        alert(data.message);
      }
    } catch (error) {
      console.error('Error:', error);
      alert('An error occurred. Please try again.');
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
    }
  });
  
  // Password visibility toggle
  document.getElementById('toggleLoginPassword')?.addEventListener('click', function(e) {
    e.preventDefault();
    const passwordInput = document.getElementById('login_password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
      passwordInput.type = 'text';
      icon.classList.remove('fa-eye');
      icon.classList.add('fa-eye-slash');
    } else {
      passwordInput.type = 'password';
      icon.classList.remove('fa-eye-slash');
      icon.classList.add('fa-eye');
    }
  });
</script>
