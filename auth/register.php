<?php
// PHP SCRIPT START - SERVER-SIDE REGISTRATION WITH SUPABASE INTEGRATION

// Start session for verification codes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load Supabase database connection
require_once __DIR__ . '/../config/supabase-api.php';

$registration_status = '';
$registration_message = '';

// Debug logging
$logFile = __DIR__ . '/registration_debug.log';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logEntry = "\n========================================\n";
    $logEntry .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
    $logEntry .= "Has register_submitted: " . (isset($_POST['register_submitted']) ? 'YES' : 'NO') . "\n";
    $logEntry .= "POST count: " . count($_POST) . "\n";
    $logEntry .= "POST data: " . print_r($_POST, true) . "\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submitted'])) { // Server-side logic for form submission
    try {
        // 1. Get Supabase API instance
        $api = getSupabaseAPI();
        
        // 2. Collect and Sanitize Data
        $firstName = trim($_POST['firstname'] ?? '');
        $lastName = trim($_POST['lastname'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $username = trim($_POST['username'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';
        $terms = isset($_POST['terms']);
        
        $errors = [];

        // 3. Server-Side Validation (Existing checks omitted for brevity)
        if (empty($firstName)) { $errors[] = "First name is required."; }
        if (empty($lastName)) { $errors[] = "Last name is required."; }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Invalid email format."; }
        if (empty($username)) { $errors[] = "Username is required."; }
        if (!preg_match('/^(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{6,}$/', $password)) { $errors[] = "Password must be 6+ characters with at least one number and symbol (!@#$%^&* etc)."; }
        if ($password !== $confirm) { $errors[] = "Passwords do not match."; }
        if (!preg_match('/^09\d{9}$/', $phone)) { $errors[] = "Invalid phone number format (must be 09XXXXXXXXX)."; }
        if (!$terms) { $errors[] = "You must agree to the Terms and Privacy Policy."; }
        
        // Check if email already exists (Re-enabling this check)
        if (empty($errors)) {
             try {
                 $existingUser = $api->select('users', ['email' => $email]);
                 if (!empty($existingUser)) {
                     $errors[] = "Email already registered. Please use a different email.";
                 }
             } catch (Exception $e) {
                 // Continue if table check fails
             }
        }

        // --- NEW: Server-Side OTP Validation (Inserted Here) ---
        $otp = trim($_POST['otp'] ?? '');
        
        // 1. Basic validation for the OTP field
        if (empty($otp)) {
            $errors[] = "Verification code is required.";
        } else if (!preg_match('/^\d{4,6}$/', $otp)) {
             $errors[] = "Verification code format is invalid (must be 4-6 digits).";
        }
        
        // 2. Check verification against session variables
        if (empty($errors)) {
            // Check if code was even generated and is in session
            if (!isset($_SESSION['verification_code']) || !isset($_SESSION['code_email']) || !isset($_SESSION['code_expires'])) {
                $errors[] = "No valid verification code found. Please request a new code.";
            } 
            // Check if the code has expired (5 minutes)
            else if ($_SESSION['code_expires'] < time()) {
                $errors[] = "Verification code has expired. Please request a new code.";
                // Clear expired session data
                unset($_SESSION['verification_code']);
                unset($_SESSION['code_email']);
                unset($_SESSION['code_expires']);
            }
            // Check if the email used for registration matches the email used for OTP
            else if ($_SESSION['code_email'] !== $email) {
                // Clear session data if email mismatch occurs
                unset($_SESSION['verification_code']);
                unset($_SESSION['code_email']);
                unset($_SESSION['code_expires']);
                $errors[] = "The submitted email does not match the email used for verification. Please request a new code.";
            }
            // Check if the submitted OTP matches the stored code
            else if ($_SESSION['verification_code'] !== $otp) {
                $errors[] = "Verification code does not match.";
            } else {
                // ✅ OTP SUCCESS: Clear session data immediately after successful use
                unset($_SESSION['verification_code']);
                unset($_SESSION['code_email']);
                unset($_SESSION['code_expires']);
            }
        }
        // -----------------------------------------------------------------
        
        // Continue to database insertion only if all checks, including OTP, passed.
        if (empty($errors)) {
            // 4. Set static role as customer
            $role = 'customer';
            
            // 5. Hash the password securely
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // 6. Prepare full name for database
            $fullName = $firstName . ' ' . $lastName;
            
            @file_put_contents($logFile, "📝 Attempting to insert user: $fullName ($email)\n", FILE_APPEND);
            
            // 7. Insert into Supabase database
            try {
                // Prepare insert data based on available columns
                $insertData = [
                    'email' => $email,
                    'username' => $username,
                    'password_hash' => $hashedPassword,
                    'full_name' => $fullName,
                    'phone' => $phone,
                    'user_type' => $role,
                    'status' => 'active',
                    // Note: 'is_verified' is not strictly needed here if the OTP check 
                    // is sufficient proof of verification, but you can set it if needed.
                    // 'is_verified' => 1 
                ];
                
                // Add username and address if provided (will be ignored if columns don't exist)
                if (!empty($username)) {
                    $insertData['username'] = $username;
                }
                if (!empty($address)) {
                    $insertData['address'] = $address;
                }
                
                $newUser = $api->insert('users', $insertData);
                
                @file_put_contents($logFile, "📊 Insert result: " . print_r($newUser, true) . "\n", FILE_APPEND);
            } catch (Exception $insertEx) {
                @file_put_contents($logFile, "❌ Insert Exception: " . $insertEx->getMessage() . "\n", FILE_APPEND);
                throw $insertEx;
            }
            
            if (!empty($newUser)) {
                // SUCCESS: Redirect to login page
                $registration_status = 'success';
                
                // Log success
                $logEntry = "✅ SUCCESS: User registered - " . $fullName . " (" . $email . ")\n";
                $logEntry .= "User ID: " . ($newUser[0]['id'] ?? 'unknown') . "\n";
                @file_put_contents($logFile, $logEntry, FILE_APPEND);
                
                // Return JSON for fetch requests - redirect to login
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Registration successful! Please log in to continue.',
                    'redirect' => '../public/index.php?registered=success'
                ]);
                exit();
            } else {
                $registration_status = 'error';
                $registration_message = "Registration failed. Please try again.";
                @file_put_contents($logFile, "❌ ERROR: Insert returned empty\n", FILE_APPEND);
                
                // Return JSON error
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Registration failed. Please try again.'
                ]);
                exit();
            }
            
        } else {
            // Validation errors
            $registration_status = 'error';
            $registration_message = implode(" | ", $errors);
            @file_put_contents($logFile, "❌ Validation errors: " . $registration_message . "\n", FILE_APPEND);
            
            // Return JSON error
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => $registration_message
            ]);
            exit();
        }
        
    } catch (Exception $e) {
        error_log("Registration Error: " . $e->getMessage());
        $registration_status = 'error';
        $registration_message = "Registration failed due to a system error. Please try again later.";
        @file_put_contents($logFile, "❌ Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
        
        // Return JSON error
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $registration_message
        ]);
        exit();
    }
}
// If it's not a POST request, the script will continue and render the HTML below.
?>
<!-- START: Register Modal -->
<div id="registerModal" class="register-modal hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4">
  <div class="modal-content w-full max-w-5xl bg-white rounded-2xl shadow-xl overflow-hidden relative lg:flex" style="min-height: 680px;">
    <!-- Left Side - Branding with Image -->
    <div class="hidden lg:flex lg:w-1/2 p-16 flex-col justify-center items-center text-white text-center relative bg-cover bg-center" style="background-image: url('../images/img.png');">
      <!-- Overlay -->
      <div class="absolute inset-0 bg-green-800 opacity-60"></div>
      
      <button id="closeRegisterModal" class="absolute top-6 left-6 h-12 w-12 flex items-center justify-center bg-black bg-opacity-30 rounded-full text-white hover:bg-opacity-50 transition-all z-20">
        <i class="fas fa-arrow-left text-xl"></i>
      </button>

      <!-- Content -->
      <div class="relative z-10">
        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg" style="animation: float 3s ease-in-out infinite;">
          <i class="fas fa-leaf text-green-600 text-4xl"></i>
        </div>
        <h2 class="text-3xl font-bold">Join The Community</h2>
        <p class="mt-2 text-green-100">Connecting farmers and consumers directly, offering fresh, local, and organic produce.</p>
      </div>
    </div>

    <!-- Right Side - Form -->
    <div class="w-full lg:w-1/2 p-8 md:p-16 flex flex-col">
      <!-- Initial Step: Choose Registration Method -->
      <div id="customer-registration-choice" class="flex flex-col justify-center flex-grow">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Create an Account</h2>
        <p class="text-sm text-gray-600 mb-6">Choose how you'd like to sign up</p>

        <button type="button" onclick="alert('Google Sign-In coming soon!')" class="w-full bg-white border-2 border-gray-300 text-gray-700 py-3 rounded-lg hover:bg-gray-50 transition duration-150 mb-3 flex items-center justify-center gap-2 font-medium">
          <svg class="w-5 h-5" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          Continue with Google
        </button>

        <div class="relative my-4">
          <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-300"></div></div>
          <div class="relative flex justify-center text-sm"><span class="px-2 bg-white text-gray-500">or</span></div>
        </div>

        <button type="button" onclick="showCustomerRegistrationForm()" class="w-full bg-green-700 text-white py-3 rounded-lg hover:bg-green-800 transition duration-150">
          Sign up with Email
        </button>

        <p class="text-center text-sm text-gray-600 mt-8">
          Already have an account? 
          <a href="login.php" class="text-green-600 font-medium hover:underline">Log In</a>
        </p>
      </div>

      <!-- Main Registration Form (Initially Hidden) -->
      <div id="customer-signup-form" class="hidden flex flex-col flex-grow">
        <div class="mb-6">
          <div class="flex items-center gap-2">
            <button type="button" onclick="showRegistrationChoice()" title="Go Back" class="h-10 w-10 flex items-center justify-center text-gray-500 hover:text-gray-800 rounded-full hover:bg-gray-100 transition-colors">
              <i class="fas fa-arrow-left text-lg"></i>
            </button>
            <h2 class="text-2xl font-bold text-gray-800">Create an Account</h2>
          </div>
          <p class="text-gray-600 mt-1">Join us and start shopping for fresh produce!</p>
        </div>

      <!-- Progress Bar -->
      <div class="mb-8">
        <div class="flex justify-between text-xs text-gray-500 mb-1">
          <span id="step-name">Personal Info</span>
          <span>Step <span id="step-current">1</span> of 5</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div id="progress-bar" class="bg-green-600 h-2 rounded-full progress-bar-fill" style="width: 25%;"></div>
        </div>
      </div>

      <div class="flex-grow flex flex-col">
        <form id="registerForm" method="POST" action="../auth/register.php" class="flex-grow flex flex-col" novalidate>
          <div class="flex-grow" style="min-height: 360px;">
            <div class="flex-grow" style="height: 320px; overflow-y: auto;">
            <!-- Step 1: Personal Info -->
            <div class="form-step active space-y-4 text-left">
              <div>
                <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="firstname" name="firstname" required placeholder="Enter your first name" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
                <span id="firstname-error" class="error-message hidden"></span>
              </div>
              <div>
                <label for="middlename" class="block text-sm font-medium text-gray-700 mb-1">Middle Name <span class="text-gray-400">(Optional)</span></label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="middlename" name="middlename" placeholder="Enter your middle name" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
                <span id="middlename-error" class="error-message hidden"></span>
              </div>
              <div>
                <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="lastname" name="lastname" required placeholder="Enter your last name" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
                <span id="lastname-error" class="error-message hidden"></span>
              </div>
              <div>
                <label for="suffix" class="block text-sm font-medium text-gray-700 mb-1">Suffix <span class="text-gray-400">(Optional)</span></label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="suffix" name="suffix" placeholder="e.g. Jr., Sr." class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
                <span id="suffix-error" class="error-message hidden"></span>
              </div>
            </div>
    
            <!-- Step 2: Account Details -->
            <div class="form-step hidden space-y-4 text-left">
              <div>
                <label for="street" class="block text-sm font-medium text-gray-700 mb-1">Street</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="street" name="street" required placeholder="Enter your street address" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
                <span id="street-error" class="error-message hidden"></span>
              </div>
              <div>
                <label for="barangay" class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3">
                  <select id="barangay" name="barangay" required class="w-full outline-none text-gray-700 text-sm bg-transparent py-2">
                    <option value="">Select Barangay</option>
                  </select>
                </div>
                <span id="barangay-error" class="error-message hidden"></span>
              </div>
              <div>
                <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2 bg-gray-100">
                  <input type="text" id="city" name="city" value="Mati City" readonly class="w-full outline-none text-gray-700 text-sm placeholder:text-sm bg-gray-100 cursor-not-allowed">
                </div>
              </div>
              <div>
                <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2 bg-gray-100">
                  <input type="text" id="province" name="province" value="Davao Oriental" readonly class="w-full outline-none text-gray-700 text-sm placeholder:text-sm bg-gray-100 cursor-not-allowed">
                </div>
              </div>
            </div>
     
            <input type="hidden" name="city" value="Mati City">
            <input type="hidden" name="province" value="Davao Oriental">

            <!-- Step 3: Contact Info -->
            <div class="form-step hidden space-y-4 text-left" id="step3">
              <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="username" name="username" required placeholder="Choose a username" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
                <span id="username-error" class="error-message hidden"></span>
              </div>
              <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="tel" id="phone" name="phone" required placeholder="09XXXXXXXXX" pattern="09[0-9]{9}" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
                <span id="phone-error" class="error-message hidden"></span>
                <p class="text-xs text-gray-500 mt-1">Format: 09XXXXXXXXX (11 digits)</p>
              </div>
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="password" id="password" name="password" required placeholder="Enter your password" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                  <button type="button" id="togglePassword" class="ml-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                    <i class="fas fa-eye text-lg"></i>
                  </button>
                </div>
                <span id="password-error" class="error-message hidden"></span>
                <div id="password-strength" class="mt-2 hidden">
                  <div class="flex items-center gap-2 mb-1">
                    <div class="flex-grow h-2 bg-gray-200 rounded-full overflow-hidden">
                      <div id="strength-bar" class="h-full w-0 rounded-full transition-all duration-300" style="background-color: #ef4444;"></div>
                    </div>
                    <span id="strength-text" class="text-xs font-semibold text-red-600">Weak</span>
                  </div>
                  <p class="text-xs text-gray-600">Requires: numbers, symbols, 6+ characters</p>
                </div>
              </div>
              <div>
                <label for="confirm" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="password" id="confirm" name="confirm" required placeholder="Confirm your password" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                  <button type="button" id="toggleConfirm" class="ml-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                    <i class="fas fa-eye text-lg"></i>
                  </button>
                </div>
                <span id="confirm-error" class="error-message hidden"></span>
              </div>
            </div>
     
            <!-- Step 4: Verification -->
            <div class="form-step hidden space-y-4 text-left" id="step4">
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email or Phone Number for Verification</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="email" name="email" required placeholder="Enter your email or phone" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
                <span id="email-error" class="error-message hidden"></span>
              </div>
              <button type="button" id="sendVerificationBtn" class="w-full text-center text-sm text-green-600 hover:underline font-medium py-2">Send Verification Code</button>
              <button type="button" id="resendVerificationBtn" class="w-full text-center text-sm text-blue-600 hover:underline font-medium hidden py-2">Resend Code</button>
              <div id="verificationMessage" class="text-sm text-center hidden"></div>
              <div>
                <label for="otp" class="block text-sm font-medium text-gray-700 mb-1">Verification Code</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="otp" name="otp" required placeholder="Enter the code you received" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
                <span id="otp-error" class="error-message hidden"></span>
              </div>
            </div>

            <!-- Step 5: Finalize (Terms & Conditions) -->
            <div class="form-step hidden space-y-4 text-left" id="step5">
              <h3 class="text-lg font-semibold text-gray-800">Final Agreement</h3>
              <p class="text-sm text-gray-600">Please review and agree to the following terms before creating your account. By proceeding, you acknowledge and accept:</p>
              <ul class="space-y-2 text-sm text-gray-600 list-disc list-inside bg-gray-50 p-4 rounded-lg">
                <li>You agree to our <a href="#" id="termsLink" class="text-green-600 font-medium hover:underline cursor-pointer">Terms of Service</a>, which govern your use of our platform.</li>
                <li>You have read and understood our <a href="#" id="privacyLink" class="text-green-600 font-medium hover:underline cursor-pointer">Privacy Policy</a>, which details how we handle your data.</li>
                <li>You consent to receive communications from us regarding your account and our services.</li>
                <li>You confirm that all information provided is accurate and that you are at least 18 years of age..</li>
              </ul>
            </div>
            </div>
  
            <div id="terms-container" class="flex items-start mt-4 hidden">
              <input type="checkbox" id="terms" name="terms" required class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded mt-1">
              <label for="terms" class="ml-3 block text-sm text-gray-700">
                I have read and agree to all the terms and conditions listed above.
              </label>
            </div>
          </div>

          <div id="navigation-buttons" class="mt-auto pt-6 flex gap-4">
            <button type="button" id="prevBtn" class="prev-btn w-32 justify-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-5 rounded-lg transition-colors text-sm hidden">Previous</button>
            <button type="button" id="nextBtn" class="next-btn w-32 justify-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg transition-colors text-sm ml-auto">Next</button>
            <button type="submit" id="submitBtn" form="registerForm" class="w-32 justify-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg transition-colors shadow-md hover:shadow-lg text-sm hidden ml-auto">
              Sign Up
            </button>
          </div>
        </form>
  
        <p class="text-center text-sm text-gray-600 mt-8">
          Already have an account? 
          <a href="login.php" class="text-green-600 font-medium hover:underline">Log In</a>
        </p>
      </div>
      </div>
    </div>
  </div>
</div>

<script>
  function showCustomerRegistrationForm() {
    document.getElementById('customer-registration-choice').classList.add('hidden');
    document.getElementById('customer-signup-form').classList.remove('hidden');
  }

  function showRegistrationChoice() {
    document.getElementById('customer-signup-form').classList.add('hidden');
    document.getElementById('customer-registration-choice').classList.remove('hidden');
  }
</script>

<!-- END: Register Modal -->