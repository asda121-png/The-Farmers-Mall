<?php
// PHP SCRIPT START - SERVER-SIDE LOGIN WITH MYSQL INTEGRATION
session_start();

// --- MySQL Connection Details ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', ''); 
define('DB_NAME', 'farmers');

$login_status = '';
$login_message = '';
$redirect_url = ''; // New variable to store the destination URL

/**
 * Establishes a connection to the MySQL database.
 * @return mysqli|null The database connection object or null on failure.
 */
function connectToDatabase() {
    $conn = @new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        error_log("Login Connection failed: " . $conn->connect_error);
        return null;
    }
    return $conn;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submitted'])) {
    $input_identifier = trim($_POST['email'] ?? ''); // Can be email or username
    $password = $_POST['password'] ?? '';
    $login_success = false;

    // =========================================================
    // 1. ADMIN CREDENTIAL CHECK (Development/Testing ONLY)
    // 
    // WARNING: Hardcoding credentials and password checks like this is 
    // highly insecure for a real application. This is done to satisfy 
    // the prompt's request for a simple admin check.
    // =========================================================
    
    // Define the specific admin credentials (in a real app, this would be a hashed password)
    $ADMIN_EMAIL = 'Admin1234@gmail.com';
    $ADMIN_PASS = 'Admin123'; 
    
    // Check for hardcoded admin credentials
    if ($input_identifier === $ADMIN_EMAIL && $password === $ADMIN_PASS) {
        // SUCCESS: Admin Login
        $_SESSION['loggedin'] = true;
        $_SESSION['role'] = 'admin'; // Set session role
        $_SESSION['username'] = 'Administrator';
        
        $login_status = 'success';
        $login_message = 'Admin login successful! Redirecting to dashboard...';
        $redirect_url = 'admin-dashboard.php'; // Set admin redirect
        $login_success = true;
    } 
    
    // =========================================================
    // 2. GENERIC USER DATABASE CHECK
    // =========================================================
    if (!$login_success) {
        $conn = connectToDatabase();
        
        if (!$conn) {
            $login_status = 'error';
            $login_message = "Login failed: Database connection failed. Please ensure MySQL is running.";
        } else {
            // Check database for user by email OR username
            $sql = "SELECT id, username, password_hash FROM users WHERE email = ? OR username = ?";
            
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ss", $input_identifier, $input_identifier);
                
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();
                        $hashed_password = $user['password_hash'];
                        
                        // Verify Password using secure hashing function
                        if (password_verify($password, $hashed_password)) {
                            // SUCCESS: Regular User Login
                            $_SESSION['loggedin'] = true;
                            $_SESSION['id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            // Assume default 'user' role if not explicitly set in the database
                            $_SESSION['role'] = $user['role'] ?? 'user'; 
                            
                            $login_status = 'success';
                            $login_message = 'Login successful! Redirecting to homepage...';
                            $redirect_url = 'homepage.php'; // Set user redirect
                            $login_success = true;
                            
                        } else {
                            // Incorrect password
                            $login_status = 'error';
                            $login_message = 'Invalid email/username or password.';
                        }
                    } else {
                        // User not found
                        $login_status = 'error';
                        $login_message = 'Invalid email/username or password.';
                    }
                } else {
                    error_log("MySQL Execution Error: " . $stmt->error);
                    $login_status = 'error';
                    $login_message = "Login failed due to a server error. (Code: 500)";
                }
                $stmt->close();
            } else {
                error_log("MySQL Prepare Error: " . $conn->error);
                $login_status = 'error';
                $login_message = "Login failed due to a server error. (Code: 501)";
            }
            
            $conn->close();
        }
    }
}
// PHP SCRIPT END
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login – Farmers Mall</title>

  <!-- Tailwind + Icons -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0px); }
    }

    .floating-icon {
      animation: float 3s ease-in-out infinite;
    }

    .input-focus:focus-within {
      box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.2);
      border-color: #15803d;
    }

    /* Background Circles */
    body {
      background: #228B22;
      min-height: 100vh;
      font-family: 'Inter', sans-serif;
      position: relative;
      overflow: hidden;
    }

    .bg-circle {
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      animation: float 6s ease-in-out infinite;
    }

    .circle1 { width: 150px; height: 150px; top: 10%; left: 5%; }
    .circle2 { width: 100px; height: 100px; bottom: 20%; left: 15%; }
    .circle3 { width: 120px; height: 120px; top: 30%; right: 10%; }
    .circle4 { width: 80px; height: 80px; bottom: 15%; right: 20%; }
    .circle5 { width: 200px; height: 200px; bottom: 5%; left: 50%; transform: translateX(-50%); }

    .error-message {
      color: #dc2626;
      font-size: 0.875rem;
      margin-top: 4px;
      display: none;
    }
    
    /* Toast Notification */
    .toast {
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%) translateY(-50px);
      color: white;
      padding: 14px 22px;
      border-radius: 10px;
      font-weight: 500;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
      opacity: 0;
      transition: all 0.4s ease;
      z-index: 9999;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .toast.show {
      transform: translateX(-50%) translateY(0);
      opacity: 1;
    }
  </style>
</head>

<body class="flex items-center justify-center p-4">

  <!-- Background Circles -->
  <div class="bg-circle circle1"></div>
  <div class="bg-circle circle2"></div>
  <div class="bg-circle circle3"></div>
  <div class="bg-circle circle4"></div>
  <div class="bg-circle circle5"></div>

  <!-- Toast Notification -->
  <div id="toast" class="toast hidden">
    <i class="fa-solid fa-circle-exclamation"></i>
    <span id="toastMsg"></span>
  </div>

  <!-- Login Card -->
  <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden relative z-10">
    <div class="bg-green-600 py-6 px-8 text-center relative">
      <a href="landing.php" class="absolute left-6 top-6 text-white hover:text-green-200 transition-colors">
        <i class="fas fa-arrow-left"></i>
      </a>
      <div class="floating-icon w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
        <i class="fas fa-leaf text-green-600 text-3xl"></i>
      </div>
      <h2 class="text-2xl font-bold text-white">Welcome Back</h2>
      <p class="text-green-100 mt-1">Sign in to your account</p>
    </div>

    <div class="p-8">
      <!-- Form points to this file (login.php) for processing -->
      <form id="loginForm" method="POST">
        <input type="hidden" name="login_submitted" value="1">
        
        <!-- Email/Username Input -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Email or Username</label>
          <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2 transition-all">
            <i class="far fa-envelope text-gray-400 mr-2"></i>
            <input id="email" name="email" type="text" placeholder="Enter your email or username" class="w-full outline-none text-gray-700" required>
          </div>
          <p id="emailError" class="error-message">Invalid email/username or password.</p>
        </div>

        <!-- Password Input -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2 transition-all">
            <i class="fas fa-lock text-gray-400 mr-2"></i>
            <input id="password" name="password" type="password" placeholder="Enter your password" class="w-full outline-none text-gray-700" required>
          </div>
          <p id="passwordError" class="error-message">Invalid email/username or password.</p>
        </div>

        <div class="flex items-center justify-between text-sm text-gray-600 mt-4 mb-6">
          <label class="flex items-center">
            <input type="checkbox" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded mr-2">
            Remember me
          </label>
          <a href="#" class="text-green-600 hover:underline">Forgot Password?</a>
        </div>

        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition-colors shadow-md hover:shadow-lg">
          Login <i class="fas fa-sign-in-alt ml-2"></i>
        </button>

        <div class="relative my-6">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">Or continue with</span>
          </div>
        </div>

        <div class="flex justify-center">
          <button type="button" class="flex items-center justify-center gap-2 bg-white border border-gray-300 rounded-lg py-2 px-6 text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
            <i class="fab fa-google text-red-500"></i>
            <span>Google</span>
          </button>
        </div>

        <p class="text-center text-sm text-gray-600 mt-6">
          Don’t have an account?
          <!-- LINK TO REGISTRATION -->
          <a href="account.php" class="text-green-600 font-medium hover:underline">Create an Account</a>
        </p>

        <small class="block text-center text-xs text-gray-500 mt-4">
          By continuing, you agree to our
          <a href="#" class="text-green-600 font-medium hover:underline">Terms of Service</a> and
          <a href="#" class="text-green-600 font-medium hover:underline">Privacy Policy</a>.
        </small>
      </form>
    </div>
  </div>

  <script>
    feather.replace();

    /**
     * Shows a colored toast notification.
     * @param {string} message The message to display.
     * @param {string} type 'success' or 'error'.
     */
    function showToast(message, type = "error") {
      const toast = document.getElementById("toast");
      const msg = document.getElementById("toastMsg");

      msg.textContent = message;
      toast.classList.remove("hidden");
      toast.classList.add("show");
      
      toast.style.backgroundColor = type === "success" ? "#16a34a" : "#dc2626";
      const icon = toast.querySelector('.fa-solid');
      icon.className = type === "success" ? "fa-solid fa-check-circle" : "fa-solid fa-circle-exclamation";


      setTimeout(() => {
        toast.classList.remove("show");
        setTimeout(() => toast.classList.add("hidden"), 400);
      }, 3500); 
    }
    
    function resetErrors() {
      document.getElementById("emailError").style.display = "none";
      document.getElementById("passwordError").style.display = "none";
    }

    // Inject PHP status check and redirect logic after page load
    window.onload = function() {
        const status = '<?= $login_status ?>';
        // PHP data to JS: Escape single quotes for use in JS string
        const message = '<?= addslashes($login_message) ?>'; 
        const redirectUrl = '<?= $redirect_url ?>';

        resetErrors(); // Hide any initial errors

        if (status === 'success') {
            showToast(message, 'success');
            
            // Redirect based on the determined URL (admin or user)
            if (redirectUrl) {
                setTimeout(() => window.location.href = redirectUrl, 2500);
            }
        } else if (status === 'error') {
            showToast(message, 'error');
            // Specifically show form error messages if login failed due to credentials
            if (message === 'Invalid email/username or password.') {
                document.getElementById("emailError").style.display = "block";
                document.getElementById("passwordError").style.display = "block";
            }
        }
        feather.replace();
    };
  </script>
</body>
</html>