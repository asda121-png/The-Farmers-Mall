<?php
// PHP SCRIPT START - RETAILER REGISTRATION WITH SUPABASE
session_start();

// Load Google OAuth configuration
require_once __DIR__ . '/../config/google-oauth.php';
$googleAuthUrl = '';
try {
    $oauth = new GoogleOAuth();
    $baseAuthUrl = $oauth->getAuthorizationUrl();
    // Add user_type parameter for retailer signup
    $googleAuthUrl = $baseAuthUrl . '&state=retailer';
} catch (Exception $e) {
    error_log("Google OAuth initialization error: " . $e->getMessage());
}

// Load Supabase API
require_once __DIR__ . '/../config/supabase-api.php';

$registration_status = '';
$registration_message = '';

// Clear any previous error messages on fresh page load (not POST submission)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Clear session variables
    unset($_SESSION['registration_error']);
    unset($_SESSION['registration_status']);
    unset($_SESSION['registration_message']);
    
    // Ensure local variables are empty
    $registration_status = '';
    $registration_message = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['retailer_signup'])) {
    try {
        $api = getSupabaseAPI();
        
        // Collect form data
        $phone = trim($_POST['phone'] ?? '');
        $shopName = trim($_POST['shop_name'] ?? '');
        $street = trim($_POST['street'] ?? '');
        $barangay = trim($_POST['barangay'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $province = trim($_POST['province'] ?? '');
        $shopCategory = trim($_POST['shop_category'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $verificationCode = trim($_POST['verification_code'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $terms = isset($_POST['terms']);
        
        // Combine address fields
        $shopAddress = $street . ', ' . $barangay . ', ' . $city . ', ' . $province;
        
        $errors = [];
        
        // Validation
        if (empty($shopName)) $errors[] = "Shop name is required.";
        if (!preg_match('/^\+63 9\d{9}$/', $phone)) $errors[] = "Invalid phone number (must be +63 9XXXXXXXXX).";
        if (empty($shopCategory)) $errors[] = "Shop category is required.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
        if (empty($shopAddress)) $errors[] = "Shop address is required.";
        if (empty($verificationCode)) $errors[] = "Verification code is required.";
        
        // Verify the email verification code
        if (empty($errors) && !empty($verificationCode)) {
            // Check if code exists in session and matches
            if (!isset($_SESSION['retailer_verification_code']) || !isset($_SESSION['retailer_code_email']) || !isset($_SESSION['retailer_code_expires'])) {
                $errors[] = "No verification code found. Please send a new code.";
            } else if ($_SESSION['retailer_code_email'] !== $email) {
                $errors[] = "Verification code does not match the email address.";
            } else if (time() > $_SESSION['retailer_code_expires']) {
                $errors[] = "Verification code has expired. Please send a new code.";
                unset($_SESSION['retailer_verification_code']);
                unset($_SESSION['retailer_code_email']);
                unset($_SESSION['retailer_code_expires']);
            } else if ($_SESSION['retailer_verification_code'] !== $verificationCode) {
                $errors[] = "Verification code is incorrect.";
            } else {
                // Code is valid - clear the session variables
                unset($_SESSION['retailer_verification_code']);
                unset($_SESSION['retailer_code_email']);
                unset($_SESSION['retailer_code_expires']);
            }
        }
        
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) $errors[] = "Password must be at least 8 characters with uppercase, lowercase, and number.";
        if ($password !== $confirmPassword) $errors[] = "Passwords do not match.";
        if (!$terms) $errors[] = "You must agree to the Terms and Conditions.";
        
        // Check if email already exists
        if (empty($errors)) {
            $existingUser = $api->select('users', ['email' => $email]);
            if (!empty($existingUser)) {
                $errors[] = "Email already registered.";
            }
        }
        
        if (empty($errors)) {
            // Create user account
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $newUser = $api->insert('users', [
                'email' => $email,
                'password_hash' => $hashedPassword,
                'full_name' => $shopName,
                'phone' => $phone,
                'user_type' => 'retailer',
                'status' => 'active'
            ]);
            
            if (!empty($newUser)) {
                $userId = $newUser[0]['id'];
                
                // Create retailer profile
                $retailerData = [
                    'user_id' => $userId,
                    'shop_name' => $shopName,
                    'business_address' => $shopAddress,
                    'shop_category' => $shopCategory,
                    'verification_status' => 'pending',
                    'rating' => 0.00,
                    'total_sales' => 0.00
                ];
                
                $newRetailer = $api->insert('retailers', $retailerData);
                
                if (!empty($newRetailer)) {
                    // Success - redirect to login with success message
                    $registration_status = 'success';
                    header('Location: ../auth/login.php?registered=success&type=retailer');
                    exit();
                } else {
                    $errors[] = "Failed to create retailer profile.";
                }
            } else {
                $errors[] = "Failed to create user account.";
            }
        }
        
        if (!empty($errors)) {
            $registration_status = 'error';
            $registration_message = implode(" | ", $errors);
        }
        
    } catch (Exception $e) {
        error_log("Retailer Registration Error: " . $e->getMessage());
        $registration_status = 'error';
        $registration_message = "A system error occurred. Please try again.";
    }
}
// PHP SCRIPT END
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Farmer's Market</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>

<style>
        /* Import Google Fonts - Match customer registration */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* How To Start Selling Section */
        .how-to-sell {
            padding: 80px 0;
            justify-content: center;
            align-content: center;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            text-align: center;
        }
   .step-card {
            padding: 20px;
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
        }

        .step-card:nth-child(1) { animation-delay: 0.1s; }
        .step-card:nth-child(2) { animation-delay: 0.2s; }
        .step-card:nth-child(3) { animation-delay: 0.3s; }
        .step-card:nth-child(4) { animation-delay: 0.4s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .step-icon-wrapper {
            background-color: var(--color-secondary);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px;
            border: 1px solid var(--color-accent-light);
        }

        .step-icon-wrapper i {
            font-size: 24px;
            color: white;
        }

        .step-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .step-description {
            font-size: 13px;
            color: #666;
        }

        /* Why Sell With Us Section */
        .benefit-card {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
        }

        .benefit-card:nth-child(1) { animation-delay: 0.1s; }
        .benefit-card:nth-child(2) { animation-delay: 0.2s; }
        .benefit-card:nth-child(3) { animation-delay: 0.3s; }
        .benefit-card:nth-child(4) { animation-delay: 0.4s; }
        .benefit-card:nth-child(5) { animation-delay: 0.5s; }
        .benefit-card:nth-child(6) { animation-delay: 0.6s; }

        /* Styles for multi-step form - Match customer registration */
        .form-step { display: none; }
        .form-step.active { display: flex; flex-direction: column; flex-grow: 1; }
        .step-content { flex-grow: 1; }
        
        /* Match customer registration progress bar */
        .progress-bar-fill {
            transition: width 0.4s ease-in-out;
        }
        
        /* Match customer registration input styles */
        .input-focus {
            transition: border-color 0.2s;
        }
        .input-focus:focus-within {
            border-color: #16a34a;
        }
        
        .input-error {
            border-color: #dc2626 !important;
        }
        .error-message {
            color: #dc2626;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            display: block;
            min-height: 1.25rem;
        }
        .error-message.hidden {
            display: none;
        }
        
        /* Terms box styling */
        .terms-box { 
            border: 1px solid #d1d5db; 
            padding: 1rem; 
            border-radius: 0.5rem; 
            font-size: 0.875rem; 
            color: #4b5563; 
            background-color: #f9fafb; 
            margin-bottom: 1rem; 
            max-height: 200px;
            overflow-y: auto;
        }
        
        /* Password toggle button */
        .password-toggle-btn {
          position: absolute;
          right: 0.75rem;
          top: 50%;
          transform: translateY(-50%);
          background: none;
          border: none;
          cursor: pointer;
        }
        
        .password-field-container {
          position: relative;
        }
        
        /* Others category field */
        #shop_category_other_container {
            margin-top: 1rem;
        }
        
        /* Toast notification animation */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translate(-50%, -20px);
            }
            to {
                opacity: 1;
                transform: translate(-50%, 0);
            }
        }
        
        .animate-slideDown {
            animation: slideDown 0.3s ease-out forwards;
        }
        
        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translate(-50%, 0);
            }
            to {
                opacity: 0;
                transform: translate(-50%, -20px);
            }
        }
        
        .animate-slideUp {
            animation: slideUp 0.3s ease-out forwards;
        }
</style>

<body class="bg-gray-50 font-sans">

  <!-- Header -->
<?php
// Include the header
include '../includes/header.php';
?>

  <!-- Toast Notification for Errors -->
  <div id="error-toast" class="hidden fixed top-4 left-1/2 transform -translate-x-1/2 z-50 animate-slideDown">
    <div class="bg-red-500 text-white px-6 py-4 rounded-lg shadow-2xl flex items-center gap-3 min-w-[320px] max-w-md">
      <div class="flex-shrink-0">
        <i class="fas fa-exclamation-circle text-xl"></i>
      </div>
      <div class="flex-1">
        <p id="error-toast-text" class="text-sm font-medium">Please fix the highlighted errors.</p>
      </div>
      <button onclick="hideErrorToast()" class="flex-shrink-0 ml-2 text-white hover:text-red-100">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </div>

  <!-- Hero / Sign Up Section -->
  <section class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between py-16 px-6">
    <div class="md:w-1/2 space-y-6">
      <h2 class="text-3xl font-bold text-gray-900">Farmer's Market</h2>
      <h3 class="text-2xl font-semibold text-green-700">Grow Your Harvest and Sell More</h3>
      <p class="text-gray-600">Join our community of local growers and producers. Reach more customers and cultivate your business with us.</p>
      <ul class="space-y-2">
        <li class="flex items-center"><span class="text-green-700 mr-2">&#10003;</span>Leading platform for local, fresh produce.</li>
        <li class="flex items-center"><span class="text-green-700 mr-2">&#10003;</span>Connecting you with a wider customer base.</li>
        <li class="flex items-center"><span class="text-green-700 mr-2">&#10003;</span>Easy management via web and mobile app.</li>
      </ul>
    </div>
    <div class="md:w-2/5 bg-white p-8 rounded-2xl shadow-xl mt-10 md:mt-0 w-full">
      <!-- Initial Step: Choose Registration Method -->
      <div id="registration-choice" class="flex flex-col">
        <h4 class="text-2xl font-bold text-gray-800 mb-2">Become a Seller</h4>
        <p class="text-sm text-gray-600 mb-6">Choose how you'd like to sign up</p>
        
        <button type="button" onclick="continueWithGoogle()" class="w-full bg-white border-2 border-gray-300 text-gray-700 py-3 rounded-lg hover:bg-gray-50 transition duration-150 mb-3 flex items-center justify-center gap-2">
          <svg class="w-5 h-5" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          Continue with Google
        </button>
        
        <div class="relative my-4">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white text-gray-500">or</span>
          </div>
        </div>
        
        <button type="button" onclick="continueWithEmail()" class="w-full bg-green-700 text-white py-3 rounded-lg hover:bg-green-800 transition duration-150">
          Sign up with Email
        </button>
        
        <p class="text-xs text-gray-500 mt-4 text-center">
          Already have an account? <a href="../auth/login.php" class="text-green-700 hover:underline">Sign in</a>
        </p>
      </div>
      
      <!-- UPDATED: Added flex classes and a fixed height -->
      <form id="retailer-signup-form" method="POST" class="flex flex-col h-auto hidden">
        <input type="hidden" name="retailer_signup" value="1">
        <div class="mb-6">
          <h4 class="text-2xl font-bold text-gray-800">Become a Seller</h4>
          <p class="text-gray-600 mt-1">Join us and start selling your products!</p>
        </div>

        <!-- Progress Bar matching customer registration -->
        <div class="mb-8">
          <div class="flex justify-between text-xs text-gray-500 mb-1">
            <span id="step-name-text">Shop Info</span>
            <span>Step <span id="step-current">1</span> of 5</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div id="progress-bar" class="bg-green-600 h-2 rounded-full progress-bar-fill" style="width: 20%;"></div>
          </div>
        </div>

        <div class="flex-grow">
          <!-- Step 1: Shop Info -->
          <div class="form-step active" data-step="1">
            <div class="step-content space-y-4">
              <div>
                <label for="shop_name" class="block text-sm font-medium text-gray-700 mb-1">Shop / Farm Name</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" name="shop_name" id="shop_name" placeholder="Enter your shop or farm name" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm" required>
                </div>
                <span id="shop_name-error" class="error-message hidden"></span>
              </div>
              <div>
                <label for="shop_category" class="block text-sm font-medium text-gray-700 mb-1">Shop Category</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3">
                  <select name="shop_category" id="shop_category" class="w-full outline-none text-gray-700 text-sm bg-transparent py-2" required>
                    <option value="">Select Shop Category</option>
                    <option value="vegetables">Vegetables</option>
                    <option value="fruits">Fruits</option>
                    <option value="dairy">Dairy Products</option>
                    <option value="meat">Meat & Poultry</option>
                    <option value="grains">Grains & Cereals</option>
                    <option value="processed">Processed Foods</option>
                    <option value="organic">Organic Products</option>
                    <option value="mixed">Mixed Farm Products</option>
                    <option value="others">Others</option>
                  </select>
                </div>
                <span id="shop_category-error" class="error-message hidden"></span>
              </div>
              <div id="shop_category_other_container" class="hidden">
                <label for="shop_category_other" class="block text-sm font-medium text-gray-700 mb-1">Specify Category</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" name="shop_category_other" id="shop_category_other" placeholder="Please specify your shop category" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
                <span id="shop_category_other-error" class="error-message hidden"></span>
              </div>
              <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="tel" name="phone" id="phone" placeholder="+63 9XXXXXXXXX" value="+63 " class="w-full outline-none text-gray-700 text-sm placeholder:text-sm" required>
                </div>
                <span id="phone-error" class="error-message hidden"></span>
                <p class="text-xs text-gray-500 mt-1">Format: +63 9XXXXXXXXX (Philippine mobile number)</p>
              </div>
            </div>
            <div class="flex gap-4 mt-6">
              <button type="button" onclick="nextStep()" class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition duration-150 font-medium text-sm">Next</button>
            </div>
          </div>

          <!-- Step 2: Shop Details -->
          <div class="form-step" data-step="2">
            <div class="step-content space-y-4">
              <div>
                <label for="street" class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" name="street" id="street" placeholder="Enter your street address" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm" required>
                </div>
                <span id="street-error" class="error-message hidden"></span>
              </div>
              <div>
                <label for="barangay" class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3">
                  <select name="barangay" id="barangay" class="w-full outline-none text-gray-700 text-sm bg-transparent py-2" required>
                    <option value="">Select Barangay</option>
                    <!-- Options will be populated by JavaScript -->
                  </select>
                </div>
                <span id="barangay-error" class="error-message hidden"></span>
              </div>
              <div>
                <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2 bg-gray-100">
                  <input type="text" name="city" id="city" value="Mati City" readonly class="w-full outline-none text-gray-700 text-sm placeholder:text-sm bg-gray-100 cursor-not-allowed">
                </div>
              </div>
              <div>
                <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2 bg-gray-100">
                  <input type="text" name="province" id="province" value="Davao Oriental" readonly class="w-full outline-none text-gray-700 text-sm placeholder:text-sm bg-gray-100 cursor-not-allowed">
                </div>
              </div>
            </div>
            <div class="flex gap-4 mt-6">
              <button type="button" onclick="prevStep()" class="w-1/2 bg-gray-200 text-gray-700 py-3 rounded-lg hover:bg-gray-300 transition duration-150 font-medium text-sm">Previous</button>
              <button type="button" onclick="nextStep()" class="w-1/2 bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition duration-150 font-medium text-sm">Next</button>
            </div>
          </div>

          <!-- Step 3: Email Verification -->
          <div class="form-step" data-step="3">
            <div class="step-content space-y-4">
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="email" name="email" id="email" placeholder="Enter your email address" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm" required>
                </div>
                <span id="email-error" class="error-message hidden"></span>
              </div>
              <button type="button" class="w-full text-center text-sm text-green-600 hover:underline font-medium">Send Verification Code</button>
              <div>
                <label for="verification_code" class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" name="verification_code" id="verification_code" placeholder="Enter 6-digit code" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm" required>
                </div>
                <span id="verification_code-error" class="error-message hidden"></span>
              </div>
            </div>
            <div class="flex gap-4 mt-6">
              <button type="button" onclick="prevStep()" class="w-1/2 bg-gray-200 text-gray-700 py-3 rounded-lg hover:bg-gray-300 transition duration-150 font-medium text-sm">Previous</button>
              <button type="button" onclick="nextStep()" class="w-1/2 bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition duration-150 font-medium text-sm">Next</button>
            </div>
          </div>

          <!-- Step 4: Create Account -->
          <div class="form-step" data-step="4">
            <div class="step-content space-y-4">
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="password-field-container">
                  <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                    <input type="password" name="password" id="password" placeholder="Create your password" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm" minlength="8" required>
                    <button type="button" onclick="togglePassword('password')" class="ml-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                      <i class="fas fa-eye text-lg" id="password-toggle"></i>
                    </button>
                  </div>
                </div>
                <span id="password-error" class="error-message hidden"></span>
                <!-- Password Strength Meter -->
                <div id="password-strength" class="mt-2 hidden">
                  <div class="flex items-center gap-2 mb-1">
                    <div class="flex-grow h-2 bg-gray-200 rounded-full overflow-hidden">
                      <div id="strength-bar" class="h-full w-0 rounded-full transition-all duration-300" style="background-color: #ef4444;"></div>
                    </div>
                    <span id="strength-text" class="text-xs font-semibold text-red-600">Weak</span>
                  </div>
                  <p class="text-xs text-gray-600">Requires: uppercase, lowercase, numbers, 8+ characters</p>
                </div>
              </div>
              <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <div class="password-field-container">
                  <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm your password" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm" minlength="8" required>
                    <button type="button" onclick="togglePassword('confirm_password')" class="ml-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                      <i class="fas fa-eye text-lg" id="confirm-password-toggle"></i>
                    </button>
                  </div>
                </div>
                <span id="confirm_password-error" class="error-message hidden"></span>
              </div>
            </div>
            <div class="flex gap-4 mt-6">
              <button type="button" onclick="prevStep()" class="w-1/2 bg-gray-200 text-gray-700 py-3 rounded-lg hover:bg-gray-300 transition duration-150 font-medium text-sm">Previous</button>
              <button type="button" onclick="nextStep()" class="w-1/2 bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition duration-150 font-medium text-sm">Next</button>
            </div>
          </div>

          <!-- Step 5: Terms & Conditions -->
          <div class="form-step" data-step="5">
            <div class="step-content space-y-4">
              <div class="terms-box">
                <p class="mb-3 text-gray-700">Welcome to Farmers Mall. By creating a seller account and using our platform, you agree to comply with these terms and conditions. These terms govern your relationship with Farmers Mall and outline your responsibilities as a seller on our marketplace.</p>
                
                <p class="mb-3 text-gray-700">As a seller, you are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account. You must provide accurate, current, and complete information during registration and keep this information updated. Any false or misleading information may result in immediate account suspension.</p>
                
                <p class="mb-3 text-gray-700">All product listings must contain accurate descriptions, current pricing, and high-quality images. Products must be fresh, safe for consumption, and comply with all applicable local food safety regulations and standards. Misleading product information or poor quality goods may result in listing removal and potential account termination.</p>
                
                <p class="mb-3 text-gray-700">Farmers Mall operates on a commission-based model. While listing products on our platform is free, we charge a small commission fee on each completed sale. This fee structure allows us to maintain and improve our platform while keeping it accessible to all sellers. Commission rates and payment schedules will be communicated clearly and are subject to change with prior notice.</p>
                
                <p class="text-gray-700">Farmers Mall reserves the right to suspend or terminate seller accounts that violate these terms, engage in fraudulent activities, receive excessive customer complaints, or conduct business in a manner deemed harmful to our community. We are committed to maintaining a fair and trustworthy marketplace for both sellers and customers.</p>
              </div>
              <div>
                <label class="flex items-start">
                  <input type="checkbox" name="terms" id="terms-checkbox" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded mt-1 mr-2" required>
                  <span class="text-sm text-gray-700">I have read and agree to the Terms and Conditions</span>
                </label>
                <span id="terms-error" class="error-message hidden"></span>
              </div>
            </div>
            <div class="flex gap-4 mt-6">
              <button type="button" onclick="prevStep()" class="w-1/2 bg-gray-200 text-gray-700 py-3 rounded-lg hover:bg-gray-300 transition duration-150 font-medium text-sm">Previous</button>
              <button type="submit" class="w-1/2 bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition duration-150 font-medium text-sm">Create Account</button>
            </div>
          </div>
        </div>

        <p class="text-xs text-gray-500 mt-6 text-center">Have an account? <a href="../auth/login.php" class="text-green-600 hover:underline font-medium">Log in</a>.</p>
      </form>
    </div>
  </section>

  <!-- Why Sell With Us Section -->
  <section class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-6">
      <h2 class="text-3xl font-bold text-center mb-12 text-gray-900">WHY SELL WITH US</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div class="benefit-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300 text-center">
          <div class="bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
            <i class="fas fa-dollar-sign text-white text-2xl"></i>
          </div>
          <h4 class="text-lg font-semibold text-gray-800 mb-2">0% No Listing Fees</h4>
          <p class="text-gray-600 text-sm">List your fresh produce and artisan goods for free. We only succeed when you do.</p>
        </div>
        <div class="benefit-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300 text-center">
          <div class="bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
            <i class="fas fa-bullhorn text-white text-2xl"></i>
          </div>
          <h4 class="text-lg font-semibold text-gray-800 mb-2">In-app Marketing Tools</h4>
          <p class="text-gray-600 text-sm">Utilize our built-in tools to promote your products and reach a targeted local audience.</p>
        </div>
        <div class="benefit-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300 text-center">
          <div class="bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
            <i class="fas fa-truck text-white text-2xl"></i>
          </div>
          <h4 class="text-lg font-semibold text-gray-800 mb-2">Hassle-free Logistics</h4>
          <p class="text-gray-600 text-sm">We provide streamlined options for local delivery and customer pickup to simplify your operations.</p>
        </div>
        <div class="benefit-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300 text-center">
          <div class="bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
            <i class="fas fa-rocket text-white text-2xl"></i>
          </div>
          <h4 class="text-lg font-semibold text-gray-800 mb-2">High-Impact Campaigns</h4>
          <p class="text-gray-600 text-sm">Participate in seasonal campaigns and promotions to boost your visibility and sales.</p>
        </div>
        <div class="benefit-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300 text-center">
          <div class="bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
            <i class="fas fa-headset text-white text-2xl"></i>
          </div>
          <h4 class="text-lg font-semibold text-gray-800 mb-2">Extensive Seller Support</h4>
          <p class="text-gray-600 text-sm">Our dedicated team is here to help you get started and grow your online farm stand.</p>
        </div>
        <div class="benefit-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300 text-center">
          <div class="bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
            <i class="fas fa-users text-white text-2xl"></i>
          </div>
          <h4 class="text-lg font-semibold text-gray-800 mb-2">Robust Seller Community</h4>
          <p class="text-gray-600 text-sm">Connect with other local producers, share insights, and grow together in our network.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- How to Start Selling Section -->
<section class="how-to-sell">
        <div class="container mx-auto px-6">
            <h2 class="section-title text-3xl font-bold text-center mb-12 text-gray-900">HOW TO START SELLING</h2>
            <div class="steps-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="step-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300">
                    <div class="step-icon-wrapper bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-clipboard-list text-white text-2xl"></i>
                    </div>
                    <h3 class="step-title text-lg font-semibold mb-2 text-gray-800">Register Your Farm/Shop</h3>
                    <p class="step-description text-gray-600 text-sm">
                        Create your seller account and verify your farm or business details.
                    </p>
                </div>
                <div class="step-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300">
                    <div class="step-icon-wrapper bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-box-open text-white text-2xl"></i>
                    </div>
                    <h3 class="step-title text-lg font-semibold mb-2 text-gray-800">List Your Products</h3>
                    <p class="step-description text-gray-600 text-sm">
                        Upload high-quality photos and descriptions of your fresh produce or artisan goods.
                    </p>
                </div>
                <div class="step-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300">
                    <div class="step-icon-wrapper bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-shopping-cart text-white text-2xl"></i>
                    </div>
                    <h3 class="step-title text-lg font-semibold mb-2 text-gray-800">Receive Orders & Fulfill</h3>
                    <p class="step-description text-gray-600 text-sm">
                        Get notified of new orders and handle shipping or independent pickup or local delivery.
                    </p>
                </div>
                <div class="step-card bg-white p-6 rounded-lg shadow-md hover:shadow-xl hover:scale-105 transition-all duration-300">
                    <div class="step-icon-wrapper bg-gradient-to-r from-green-400 to-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-4 mx-auto">
                        <i class="fas fa-hand-holding-usd text-white text-2xl"></i>
                    </div>
                    <h3 class="step-title text-lg font-semibold mb-2 text-gray-800">Get Paid & Grow</h3>
                    <p class="step-description text-gray-600 text-sm">
                        Receive payments securely and track your sales performance to grow your business.
                    </p>
                </div>
            </div>
        </div>
    </section>
  <!-- Support Section -->
  <section class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-6 text-center">
      <h2 class="text-3xl font-bold mb-4">SUPPORT WHEN YOU NEED IT</h2>
      <p class="mb-6 text-gray-600">We’re committed to your success. Our Farmer’s Market platform provides a wealth of resources to help you every step of the way. From onboarding and setting up your shop to managing orders and marketing your products, we’ve got you covered. Access our comprehensive <a href="#" class="text-green-700">Seller Center</a>, browse the FAQ, join educational webinars, or reach out to our friendly <a href="#" class="text-green-700">Customer Service team</a>. We’re here to help you thrive.</p>
    </div>
  </section>

  <!-- Footer -->
  <footer class="text-white py-12 mt-16" style="background-color: #1B5E20;">
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
          <a href="#" class="hover:text-gray-300"><i class="fab fa-facebook"></i></a>
          <a href="#" class="hover:text-gray-300"><i class="fab fa-twitter"></i></a>
          <a href="#" class="hover:text-gray-300"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
    </div>

    <!-- Divider -->
    <div class="border-t border-green-800 text-center text-gray-400 text-sm mt-10 pt-6">
      © 2025 Farmers Mall. All rights reserved.
    </div>
  </footer>

  <script>
    // ====== REGISTRATION METHOD CHOICE ======
    function continueWithGoogle() {
      // Redirect to Google OAuth
      const googleAuthUrl = '<?php echo $googleAuthUrl; ?>';
      if (googleAuthUrl) {
        window.location.href = googleAuthUrl;
      } else {
        alert('Google authentication is not configured. Please try email sign-up instead.');
      }
    }

    function continueWithEmail() {
      // Hide the choice screen
      document.getElementById('registration-choice').classList.add('hidden');
      // Show the registration form
      document.getElementById('retailer-signup-form').classList.remove('hidden');
    }
    
    // Handle "Others" category option
    document.addEventListener('DOMContentLoaded', function() {
      const shopCategory = document.getElementById('shop_category');
      const shopCategoryOtherContainer = document.getElementById('shop_category_other_container');
      
      if (shopCategory && shopCategoryOtherContainer) {
        shopCategory.addEventListener('change', function() {
          if (this.value === 'others') {
            shopCategoryOtherContainer.classList.remove('hidden');
            document.getElementById('shop_category_other').required = true;
          } else {
            shopCategoryOtherContainer.classList.add('hidden');
            document.getElementById('shop_category_other').required = false;
          }
        });
      }
      
      // Handle phone number +63 prefix
      const phoneInput = document.getElementById('phone');
      if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
          let value = e.target.value;
          // Ensure it always starts with +63
          if (!value.startsWith('+63 ')) {
            value = '+63 ';
          }
          e.target.value = value;
        });
        
        phoneInput.addEventListener('keydown', function(e) {
          // Prevent deleting the +63 prefix
          if (e.target.selectionStart < 4 && (e.key === 'Backspace' || e.key === 'Delete')) {
            e.preventDefault();
          }
        });
      }
    });

    // Mati City Barangays
    const matiBarangays = [
      "Badas", "Bobon", "Buso", "Cawayanan", "Central", "Dahican", "Danao", "Dawan", "Don Enrique Lopez",
      "Don Martin Marundan", "Don Salvador Lopez Sr.", "Langka", "Lantawan", "Lawigan", "Libudon", "Luban",
      "Macambol", "Magsaysay", "Manay", "Matiao", "New Bataan", "New Libudon", "Old Macambol", "Poblacion",
      "Sainz", "San Isidro", "San Roque", "Tagabakid", "Tagbinonga", "Taguibo", "Tamisan"
    ];

    function populateBarangays() {
      const barangaySelect = document.getElementById('barangay');
      if (barangaySelect) {
        barangaySelect.innerHTML = '<option value="">Select Barangay</option>'; // Clear existing options
        matiBarangays.sort().forEach(barangay => { // Sort alphabetically
          const option = document.createElement('option');
          option.value = barangay;
          option.textContent = barangay;
          barangaySelect.appendChild(option);
        });
      }
    }

    // ====== VALIDATION PATTERNS AND ERROR MESSAGES ======
    const validationPatterns = {
      email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
      phone: /^\+63 9\d{9}$/,
      password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/
    };

    const errorMessages = {
      shop_name: 'Shop name is required',
      shop_category: 'Shop category is required',
      shop_category_other: 'Please specify your shop category',
      phone: 'Invalid phone number. Must be +63 9XXXXXXXXX',
      street: 'Street address is required',
      barangay: 'Barangay is required',
      email: 'Invalid email format',
      verification_code: 'Verification code is required',
      password: 'Password must be at least 8 characters with uppercase, lowercase, and number',
      confirm_password: 'Passwords do not match',
      terms: 'You must agree to the Terms and Conditions'
    };

    // ====== FIELD ERROR FUNCTIONS ======
    function setFieldError(fieldId, message) {
      const field = document.getElementById(fieldId);
      const errorElement = document.getElementById(`${fieldId}-error`);
      
      if (field) {
        field.classList.add('input-error');
        // Also add red border to parent input-focus div
        const parent = field.closest('.input-focus');
        if (parent) {
          parent.style.borderColor = '#dc2626';
        }
      }
      if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
      }
    }

    function clearFieldError(fieldId) {
      const field = document.getElementById(fieldId);
      const errorElement = document.getElementById(`${fieldId}-error`);
      
      if (field) {
        field.classList.remove('input-error');
        // Also remove red border from parent input-focus div
        const parent = field.closest('.input-focus');
        if (parent) {
          parent.style.borderColor = '';
        }
      }
      if (errorElement) {
        errorElement.textContent = '';
        errorElement.classList.add('hidden');
      }
    }

    // ====== ERROR TOAST FUNCTIONS ======
    function showErrorBanner(message) {
      const toast = document.getElementById('error-toast');
      const toastText = document.getElementById('error-toast-text');
      if (toast && toastText) {
        toastText.textContent = message;
        toast.classList.remove('hidden', 'animate-slideUp');
        toast.classList.add('animate-slideDown');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
          hideErrorToast();
        }, 5000);
      }
    }

    function hideErrorBanner() {
      hideErrorToast();
    }
    
    function hideErrorToast() {
      const toast = document.getElementById('error-toast');
      if (toast && !toast.classList.contains('hidden')) {
        toast.classList.remove('animate-slideDown');
        toast.classList.add('animate-slideUp');
        setTimeout(() => {
          toast.classList.add('hidden');
        }, 300);
      }
    }

    // ====== PASSWORD STRENGTH METER ======
    function calculatePasswordStrength(password) {
      let strength = 0;
      let feedbackText = '';
      let color = '';
      
      if (!password) {
        return { strength: 0, text: 'Weak', color: '#ef4444', feedback: [] };
      }

      const feedback = [];
      
      // Check length
      if (password.length >= 8 && password.length < 12) strength += 1;
      else if (password.length >= 12 && password.length < 16) strength += 2;
      else if (password.length >= 16) strength += 3;
      
      // Check for numbers
      if (/\d/.test(password)) {
        strength += 1;
      } else {
        feedback.push('Add numbers');
      }
      
      // Check for lowercase letters
      if (/[a-z]/.test(password)) {
        strength += 1;
      } else {
        feedback.push('Add lowercase letters');
      }
      
      // Check for uppercase letters
      if (/[A-Z]/.test(password)) {
        strength += 1;
      } else {
        feedback.push('Add uppercase letters');
      }
      
      // Calculate percentage and set text/color
      const maxStrength = 6;
      const percentage = (strength / maxStrength) * 100;
      
      if (percentage <= 33) {
        feedbackText = 'Weak';
        color = '#ef4444'; // red-500
      } else if (percentage <= 66) {
        feedbackText = 'Medium';
        color = '#f59e0b'; // amber-500
      } else {
        feedbackText = 'Strong';
        color = '#10b981'; // emerald-500
      }
      
      return {
        strength: percentage,
        text: feedbackText,
        color: color,
        feedback: feedback
      };
    }

    function updatePasswordStrength(password) {
      const strengthDiv = document.getElementById('password-strength');
      const strengthBar = document.getElementById('strength-bar');
      const strengthText = document.getElementById('strength-text');
      
      if (!strengthDiv) return;
      
      const strength = calculatePasswordStrength(password);
      
      if (!password) {
        strengthDiv.classList.add('hidden');
        return;
      }
      
      strengthDiv.classList.remove('hidden');
      strengthBar.style.width = strength.strength + '%';
      strengthBar.style.backgroundColor = strength.color;
      strengthText.textContent = strength.text;
      strengthText.style.color = strength.color;
    }

    // ====== REAL-TIME VALIDATION FUNCTIONS ======
    function validateField(fieldId) {
      const field = document.getElementById(fieldId);
      if (!field) return true;
      
      const value = field.value.trim();
      
      // Check if empty
      if (!value && field.hasAttribute('required')) {
        setFieldError(fieldId, errorMessages[fieldId] || 'This field is required');
        return false;
      }
      
      // If field is empty and not required, clear errors and return true
      if (!value && !field.hasAttribute('required')) {
        clearFieldError(fieldId);
        return true;
      }
      
      // Validate specific fields
      if (fieldId === 'phone' && value) {
        if (!validationPatterns.phone.test(value)) {
          setFieldError(fieldId, errorMessages.phone);
          return false;
        }
      }
      
      if (fieldId === 'email' && value) {
        if (!validationPatterns.email.test(value)) {
          setFieldError(fieldId, errorMessages.email);
          return false;
        }
      }
      
      if (fieldId === 'password' && value) {
        if (!validationPatterns.password.test(value)) {
          setFieldError(fieldId, errorMessages.password);
          return false;
        }
      }
      
      if (fieldId === 'confirm_password' && value) {
        const password = document.getElementById('password').value;
        if (value !== password) {
          setFieldError(fieldId, errorMessages.confirm_password);
          return false;
        }
      }
      
      if (fieldId === 'shop_category' && !value) {
        setFieldError(fieldId, errorMessages.shop_category);
        return false;
      }
      
      if (fieldId === 'shop_category_other' && value) {
        const category = document.getElementById('shop_category');
        if (category && category.value === 'others' && !value) {
          setFieldError(fieldId, errorMessages.shop_category_other);
          return false;
        }
      }
      
      if (fieldId === 'barangay' && !value) {
        setFieldError(fieldId, errorMessages.barangay);
        return false;
      }
      
      clearFieldError(fieldId);
      return true;
    }

    // ====== ATTACH INPUT LISTENERS ======
    function initializeValidation() {
      const fieldsToValidate = [
        'shop_name', 'shop_category', 'shop_category_other', 'phone', 'street', 'barangay',
        'email', 'verification_code', 'password', 'confirm_password'
      ];
      
      fieldsToValidate.forEach(fieldId => {
        const element = document.getElementById(fieldId);
        if (element) {
          // Clear error on input
          element.addEventListener('input', () => {
            clearFieldError(fieldId);
            hideErrorBanner();
            
            // Update password strength
            if (fieldId === 'password') {
              updatePasswordStrength(element.value);
              // Validate confirm password if it has a value
              const confirmField = document.getElementById('confirm_password');
              if (confirmField && confirmField.value) {
                validateField('confirm_password');
              }
            }
            
            // Validate confirm password on input
            if (fieldId === 'confirm_password') {
              validateField('confirm_password');
            }
          });
          
          // Validate on blur
          element.addEventListener('blur', () => {
            if (element.value.trim()) {
              validateField(fieldId);
            }
          });
        }
      });
      
      // Special handling for barangay select
      const barangaySelect = document.getElementById('barangay');
      if (barangaySelect) {
        barangaySelect.addEventListener('change', () => {
          if (barangaySelect.value) {
            clearFieldError('barangay');
          }
        });
      }
    }

    // Initialize barangays and validation when page loads
    document.addEventListener('DOMContentLoaded', function() {
      populateBarangays();
      initializeValidation();
    });

    // ====== MULTI-STEP FORM NAVIGATION ======
    let currentStep = 1;
    const totalSteps = 5;
    
    const stepInfo = [
      { name: "Shop Info", percentage: 20 },
      { name: "Shop Details", percentage: 40 },
      { name: "Email Verification", percentage: 60 },
      { name: "Create Password", percentage: 80 },
      { name: "Terms & Conditions", percentage: 100 }
    ];

    function updateProgressBar(step) {
      const stepNameText = document.getElementById('step-name-text');
      const stepCurrent = document.getElementById('step-current');
      const progressBar = document.getElementById('progress-bar');
      
      if (stepNameText) stepNameText.textContent = stepInfo[step - 1].name;
      if (stepCurrent) stepCurrent.textContent = step;
      if (progressBar) progressBar.style.width = stepInfo[step - 1].percentage + '%';
    }

    function showStep(step) {
      document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
      const nextStepElement = document.querySelector(`.form-step[data-step="${step}"]`);
      if (nextStepElement) {
        nextStepElement.classList.add('active');
        updateProgressBar(step);
      }
    }

    function validateStep(step) {
      const currentStepElement = document.querySelector(`.form-step[data-step="${step}"]`);
      let isValid = true;
      const errors = [];

      // Hide error banner initially
      hideErrorBanner();

      // Get all inputs within the current step
      const inputs = currentStepElement.querySelectorAll('input[type="text"], input[type="tel"], input[type="email"], input[type="password"], select');
      
      inputs.forEach(input => {
        if (input.hasAttribute('required')) {
          if (!validateField(input.id)) {
            isValid = false;
            const fieldLabel = input.closest('div').previousElementSibling?.textContent || input.placeholder || 'This field';
            errors.push(fieldLabel.replace('*', '').trim());
          }
        }
      });
      
      // Special validation for "Others" category
      if (step === 1) {
        const category = document.getElementById('shop_category');
        const categoryOther = document.getElementById('shop_category_other');
        if (category && category.value === 'others' && categoryOther) {
          if (!categoryOther.value.trim()) {
            setFieldError('shop_category_other', errorMessages.shop_category_other);
            isValid = false;
            errors.push('Specify Category');
          }
        }
      }

      // Show error banner if there are errors
      if (!isValid && errors.length > 0) {
        showErrorBanner('Please fix the highlighted errors.');
      }

      return isValid;
    }

    function nextStep() {
      if (validateStep(currentStep)) {
        hideErrorBanner();
        if (currentStep < totalSteps) {
          currentStep++;
          showStep(currentStep);
        }
      }
    }

    function prevStep() {
      if (currentStep > 1) {
        currentStep--;
        showStep(currentStep);
      }
    }

    // Password toggle function
    function togglePassword(fieldId) {
      const field = document.getElementById(fieldId);
      const toggle = document.getElementById(fieldId + '-toggle');
      
      if (field.type === 'password') {
        field.type = 'text';
        toggle.classList.remove('fa-eye');
        toggle.classList.add('fa-eye-slash');
      } else {
        field.type = 'password';
        toggle.classList.remove('fa-eye-slash');
        toggle.classList.add('fa-eye');
      }
    }

    // ====== EMAIL VERIFICATION HANDLER ======
    let sentVerificationCode = null; // Stores the OTP for client-side validation (dev only)
    let verificationCodeSent = false; // Flag to indicate if code was sent
    
    const sendVerificationBtn = document.querySelector('button[type="button"]');
    const emailInput = document.getElementById('email');
    const verificationCodeInput = document.getElementById('verification_code');
    const verificationMessage = document.createElement('div');
    verificationMessage.id = 'verification_message';
    verificationMessage.className = 'text-sm text-center hidden mt-2';
    
    // Insert the message div after the send button
    if (sendVerificationBtn) {
      sendVerificationBtn.parentNode.insertBefore(verificationMessage, sendVerificationBtn.nextSibling);
    }
    
    // Find the actual Send Verification Code button by checking text content
    let sendCodeBtn = null;
    const allButtons = document.querySelectorAll('button[type="button"]');
    allButtons.forEach(btn => {
      if (btn.textContent.includes('Send Verification Code')) {
        sendCodeBtn = btn;
      }
    });
    
    if (sendCodeBtn && emailInput) {
      sendCodeBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const email = emailInput.value.trim();
        
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          verificationMessage.innerHTML = '<span class="text-red-600">Please enter a valid email address.</span>';
          verificationMessage.classList.remove('hidden');
          return;
        }
        
        sendCodeBtn.disabled = true;
        sendCodeBtn.textContent = 'Sending...';
        verificationMessage.classList.add('hidden');
        
        fetch('./verify-email.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            email: email
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            sentVerificationCode = data.code || null;
            verificationMessage.innerHTML = '<span class="text-green-600">✅ ' + data.message + '</span>';
            verificationMessage.classList.remove('hidden');
            sendCodeBtn.textContent = 'Code Sent!';
            sendCodeBtn.classList.add('opacity-50', 'cursor-not-allowed');
            verificationCodeSent = true;
            verificationCodeInput.focus();
          } else {
            sendCodeBtn.disabled = false;
            sendCodeBtn.textContent = 'Send Verification Code';
            verificationMessage.innerHTML = '<span class="text-red-600">❌ ' + data.message + '</span>';
            verificationMessage.classList.remove('hidden');
          }
        })
        .catch(error => {
          sendCodeBtn.disabled = false;
          sendCodeBtn.textContent = 'Send Verification Code';
          verificationMessage.innerHTML = '<span class="text-red-600">❌ Error: ' + error.message + '</span>';
          verificationMessage.classList.remove('hidden');
        });
      });
    }

    // Final form submission handler
    document.getElementById('retailer-signup-form').addEventListener('submit', function(event) {
      if (currentStep === totalSteps) {
        const termsCheckbox = document.getElementById('terms-checkbox');
        if (!termsCheckbox.checked) {
          event.preventDefault();
          setFieldError('terms', errorMessages.terms);
          showErrorBanner('Please agree to the Terms and Conditions to continue.');
          return false;
        }
        clearFieldError('terms');
        hideErrorBanner();
        // Form will submit normally to PHP
      } else {
        event.preventDefault();
        showErrorBanner('Please complete all steps before submitting.');
      }
    });
  </script>

  <?php
  // Include the login modal HTML. This should come before the script that handles it.
  include '../auth/login.php';
  ?>

  <!-- Use the centralized modal handler script -->
  <script src="../assets/js/modal-handler.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const registrationChoice = document.getElementById('registration-choice');
      const retailerSignupForm = document.getElementById('retailer-signup-form');
      const loginModal = document.getElementById('loginModal');

      // This function shows the inline registration form on this page.
      function showRetailerRegistrationForm(event) {
        if (event) event.preventDefault();

        // If the login modal is open, close it first.
        if (loginModal && !loginModal.classList.contains('hidden')) {
          // Use the function from modal-handler.js if available
          if (typeof closeLoginModal === 'function') {
            closeLoginModal();
          } else {
            loginModal.classList.add('hidden');
          }
        }

        // Show the registration form and scroll to it.
        registrationChoice.classList.add('hidden');
        retailerSignupForm.classList.remove('hidden');
        retailerSignupForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }

      // Find the "Create an Account" link inside the login modal.
      const createAccountLink = document.querySelector('#loginModal a[href*="register.php"]');
      if (createAccountLink) {
        // Override the default modal-handler behavior for this specific link.
        createAccountLink.addEventListener('click', showRetailerRegistrationForm, true); // Use capture to run this first
      }
    });
  </script>

</html>
