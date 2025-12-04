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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submitted'])) {
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

        // 3. Server-Side Validation
        if (empty($firstName)) {
            $errors[] = "First name is required.";
        }
        if (empty($lastName)) {
            $errors[] = "Last name is required.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }
        if (empty($username)) {
            $errors[] = "Username is required.";
        }
        // Password must be 6+ characters with at least one number and one symbol
        if (!preg_match('/^(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{6,}$/', $password)) {
            $errors[] = "Password must be 6+ characters with at least one number and symbol (!@#$%^&* etc).";
        }
        if ($password !== $confirm) {
            $errors[] = "Passwords do not match.";
        }
        if (!preg_match('/^09\d{9}$/', $phone)) {
            $errors[] = "Invalid phone number format (must be 09XXXXXXXXX).";
        }
        if (!$terms) {
            $errors[] = "You must agree to the Terms and Privacy Policy.";
        }
        
        // Check if email already exists
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
                    'status' => 'active'
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
                    'message' => 'Registration successful! Please login to continue.',
                    'redirect' => 'login.php?registered=success'
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
// PHP SCRIPT END
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register – Farmers Mall</title>

    <!-- Tailwind + Icons -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
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
        body {
            background: #228B22;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            position: relative;
            overflow: hidden;
        }
        /* Background Circles */
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

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        /* Toast Notification */
        .toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-50px);
            background-color: #dc2626;
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
        /* Field Error Styles */
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
    </style>
</head>
<body class="flex items-center justify-center p-4" style="background: #228B22;">

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

    <!-- Register Card -->
  <div class="w-full max-w-5xl bg-white rounded-2xl shadow-xl overflow-hidden relative z-10 lg:flex" style="min-height: 680px;">
    <!-- Left Side - Branding with Image -->
    <div class="hidden lg:flex lg:w-1/2 p-16 flex-col justify-center items-center text-white text-center relative bg-cover bg-center" style="background-image: url('../images/img.png');">
      <!-- Overlay -->
      <div class="absolute inset-0 bg-green-800 opacity-60"></div>
      
      <a href="../public/index.php" class="absolute top-6 left-6 h-12 w-12 flex items-center justify-center bg-black bg-opacity-30 rounded-full text-white hover:bg-opacity-50 transition-all z-20">
        <i class="fas fa-arrow-left text-xl"></i>
      </a>

      <!-- Content -->
      <div class="relative z-10">
        <div class="floating-icon w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
          <i class="fas fa-leaf text-green-600 text-4xl"></i>
        </div>
        <h2 class="text-3xl font-bold">Join The Community</h2>
        <p class="mt-2 text-green-100">Connecting farmers and consumers directly, offering fresh, local, and organic produce.</p>
      </div>
    </div>

    <!-- Right Side - Form -->
    <div class="w-full lg:w-1/2 p-8 md:p-16 flex flex-col">
      <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-800">Create an Account</h2>
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
        <!-- The form action is currently pointing to a sample script, adjust as needed -->
        <form id="registerForm" method="POST" action="register.php" class="flex-grow flex flex-col">
          <div class="flex-grow" style="min-height: 360px;"> <!-- Parent container for static height -->
            <div class="flex-grow" style="height: 320px; overflow-y: auto;"> <!-- Scrollable content area -->
            <!-- Step 1: Personal Info -->
            <div class="form-step active space-y-4">
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
            <div class="form-step hidden space-y-4"> <!-- This is now the Address step -->
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
                    <!-- Options will be populated by JavaScript -->
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
     
            <!-- Hidden inputs for fixed City and Province values -->
            <input type="hidden" name="city" value="Mati City">
            <input type="hidden" name="province" value="Davao Oriental">

            <!-- Step 3: Contact Info -->
            <div class="form-step hidden space-y-4"> <!-- This is now the Account Details step -->
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
                  <p class="text-xs text-gray-600">Requires: numbers, symbols, 6+ characters</p>
                </div>
              </div>
              <div>
                <label for="confirm" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="password" id="confirm" name="confirm" required placeholder="Confirm your password" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
                <span id="confirm-error" class="error-message hidden"></span>
              </div>
            </div>
     
             <!-- Step 4: Finalize -->
            <div class="form-step hidden space-y-4"> <!-- This is now the Verification step -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email or Phone Number for Verification</label>
                <div class="input-focus flex items-center border border-gray-300 rounded-lg px-3 py-2">
                  <input type="text" id="email" name="email" required placeholder="Enter your email or phone" class="w-full outline-none text-gray-700 text-sm placeholder:text-sm">
                </div>
                <span id="email-error" class="error-message hidden"></span>
              </div>
              <button type="button" id="sendVerificationBtn" class="w-full text-center text-sm text-green-600 hover:underline font-medium">Send Verification Code</button>
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
            <div class="form-step hidden space-y-4">
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
  
            <!-- Terms Checkbox (outside scrollable area, shown on last step) -->
            <div id="terms-container" class="flex items-start mt-4 hidden">
              <input type="checkbox" id="terms" name="terms" required class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded mt-1">
              <label for="terms" class="ml-3 block text-sm text-gray-700">
                I have read and agree to all the terms and conditions listed above.
              </label>
            </div>
          </div>

          <!-- Navigation Buttons (now inside the form) -->
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

  <!-- Terms of Service Modal -->
  <div id="termsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
      <!-- Modal Header -->
      <div class="bg-green-600 text-white p-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold">Terms of Service</h2>
        <button id="closeTermsModal" class="text-white hover:bg-green-700 rounded-full w-10 h-10 flex items-center justify-center transition-colors">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>
      
      <!-- Modal Content -->
      <div class="overflow-y-auto flex-grow p-6">
        <div class="space-y-4 text-gray-700 text-sm leading-relaxed">
          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">1. Acceptance of Terms</h3>
            <p>By accessing and using The Farmers Mall platform, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>
          </section>

          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">2. Use License</h3>
            <p>Permission is granted to temporarily download one copy of the materials (information or software) on The Farmers Mall for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
            <ul class="list-disc list-inside ml-2 space-y-1 mt-2">
              <li>Modifying or copying the materials</li>
              <li>Using the materials for any commercial purpose or for any public display</li>
              <li>Attempting to decompile or reverse engineer any software contained on The Farmers Mall</li>
              <li>Removing any copyright or other proprietary notations from the materials</li>
              <li>Transferring the materials to another person or "mirroring" the materials on any other server</li>
            </ul>
          </section>

          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">3. Disclaimer</h3>
            <p>The materials on The Farmers Mall are provided "as is". The Farmers Mall makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</p>
          </section>

          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">4. Limitations</h3>
            <p>In no event shall The Farmers Mall or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on The Farmers Mall.</p>
          </section>

          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">5. Accuracy of Materials</h3>
            <p>The materials appearing on The Farmers Mall could include technical, typographical, or photographic errors. The Farmers Mall does not warrant that any of the materials on its website are accurate, complete, or current. The Farmers Mall may make changes to the materials contained on its website at any time without notice.</p>
          </section>

          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">6. Links</h3>
            <p>The Farmers Mall has not reviewed all of the sites linked to its website and is not responsible for the contents of any such linked site. The inclusion of any link does not imply endorsement by The Farmers Mall of the site. Use of any such linked website is at the user's own risk.</p>
          </section>

          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">7. Modifications</h3>
            <p>The Farmers Mall may revise these terms of service for its website at any time without notice. By using this website, you are agreeing to be bound by the then current version of these terms of service.</p>
          </section>

          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">8. Governing Law</h3>
            <p>These terms and conditions are governed by and construed in accordance with the laws of the Philippines, and you irrevocably submit to the exclusive jurisdiction of the courts located in Davao Oriental.</p>
          </section>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="bg-gray-100 p-4 flex justify-end gap-3">
        <button id="closeTermsBtn" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition-colors font-medium">Close</button>
      </div>
    </div>
  </div>

  <!-- Privacy Policy Modal -->
  <div id="privacyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
      <!-- Modal Header -->
      <div class="bg-green-600 text-white p-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold">Privacy Policy</h2>
        <button id="closePrivacyModal" class="text-white hover:bg-green-700 rounded-full w-10 h-10 flex items-center justify-center transition-colors">
          <i class="fas fa-times text-xl"></i>
        </button>
      </div>
      
      <!-- Modal Content -->
      <div class="overflow-y-auto flex-grow p-6">
        <div class="space-y-4 text-gray-700 text-sm leading-relaxed">
          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">1. Information We Collect</h3>
            <p>We collect information you provide directly to us, such as when you create an account, place an order, or contact us. This information may include your name, email address, phone number, address, payment information, and any other information you choose to provide.</p>
          </section>

          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">2. How We Use Your Information</h3>
            <p>We use the information we collect to:</p>
            <ul class="list-disc list-inside ml-2 space-y-1 mt-2">
              <li>Process and fulfill your orders</li>
              <li>Send you transactional and promotional communications</li>
              <li>Improve and personalize your experience</li>
              <li>Respond to your inquiries and requests</li>
              <li>Comply with legal obligations</li>
              <li>Prevent fraudulent transactions</li>
            </ul>
          </section>

          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">3. Information Sharing</h3>
            <p>We do not share, sell, or rent your personal information to third parties without your explicit consent, except as required by law or to service providers who assist us in operating our website and conducting our business, subject to confidentiality agreements.</p>
          </section>

          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">4. Data Security</h3>
            <p>We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the internet or electronic storage is 100% secure.</p>
          </section>

          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">5. Cookies</h3>
            <p>We use cookies and similar tracking technologies to enhance your experience on our platform. You can control cookie settings through your browser preferences.</p>
          </section>

          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">6. Your Rights</h3>
            <p>You have the right to access, correct, or delete your personal information. You may also opt out of receiving promotional communications. Contact us at support@farmersmall.com to exercise these rights.</p>
          </section>

          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">7. Third-Party Links</h3>
            <p>Our website may contain links to third-party websites. We are not responsible for the privacy practices of these external sites. Please review their privacy policies before providing your information.</p>
          </section>

          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">8. Policy Changes</h3>
            <p>We may update this Privacy Policy from time to time. We will notify you of any changes by updating the "Last Updated" date at the bottom of this policy. Your continued use of The Farmers Mall after changes constitutes your acceptance of the updated policy.</p>
          </section>

          <section>
            <h3 class="font-bold text-lg text-gray-800 mb-2">9. Contact Us</h3>
            <p>If you have questions about this Privacy Policy or our privacy practices, please contact us at support@farmersmall.com or call +63 (XXX) XXX-XXXX.</p>
          </section>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="bg-gray-100 p-4 flex justify-end gap-3">
        <button id="closePrivacyBtn" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition-colors font-medium">Close</button>
      </div>
    </div>
  </div>
  
  <script>
    feather.replace();

    // ====== VALIDATION REGEX PATTERNS ======
    const validationPatterns = {
      // Names: Allow only letters, spaces, hyphens, apostrophes (2-50 characters)
      name: /^[a-zA-Z\s\-']{2,50}$/,
      
      // Username: Alphanumeric and underscores/hyphens (3-20 characters)
      username: /^[a-zA-Z0-9_-]{3,20}$/,
      
      // Email: Standard email format
      email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
      
      // Phone: Philippine phone format 09XXXXXXXXX (11 digits)
      phone: /^09\d{9}$/,
      
      // Password: At least 6 characters, must contain at least one number and one symbol
      password: /^(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]).{6,}$/,
      
      // Street: Allow letters, numbers, spaces, commas, dots, hyphens
      street: /^[a-zA-Z0-9\s,.\-]{3,100}$/,
      
      // OTP: Numeric only, typically 4-6 digits
      otp: /^\d{4,6}$/
    };

    // ====== VALIDATION ERROR MESSAGES ======
    const errorMessages = {
      firstname: "First name must contain only letters and be 2-50 characters",
      lastname: "Last name must contain only letters and be 2-50 characters",
      middlename: "Middle name must contain only letters and be 2-50 characters",
      suffix: "Suffix must contain only letters and be 2-50 characters",
      username: "Username must be 3-20 characters (letters, numbers, _, -)",
      email: "Please enter a valid email address",
      phone: "Phone must be in format 09XXXXXXXXX (11 digits)",
      password: "Password must be 6+ characters with at least one number and symbol (!@#$%^&* etc)",
      confirm: "Passwords do not match",
      street: "Street address must be 3-100 characters",
      barangay: "Please select a barangay",
      otp: "Verification code must be 4-6 digits"
    };

    // ====== VALIDATION FUNCTIONS ======
    function validateField(fieldId, fieldType) {
      const field = document.getElementById(fieldId);
      const errorElement = document.getElementById(`${fieldId}-error`);
      if (!field) return true;

      const value = field.value.trim();
      const isRequired = field.hasAttribute('required');
      
      // Special case: optional fields that are empty are valid
      if (!isRequired && !value) {
        clearFieldError(fieldId);
        return true;
      }

      // Required fields must not be empty
      if (isRequired && !value) {
        setFieldError(fieldId, `${field.name || fieldId} is required`);
        return false;
      }

      // Validate against regex pattern if pattern exists
      if (validationPatterns[fieldType] && value) {
        if (!validationPatterns[fieldType].test(value)) {
          setFieldError(fieldId, errorMessages[fieldId] || "Invalid format");
          return false;
        }
      }

      // Special validation: password confirmation
      if (fieldId === 'confirm') {
        const passwordValue = document.getElementById('password').value;
        if (value !== passwordValue) {
          setFieldError(fieldId, errorMessages.confirm);
          return false;
        }
      }

      clearFieldError(fieldId);
      return true;
    }

    function setFieldError(fieldId, message) {
      const field = document.getElementById(fieldId);
      const errorElement = document.getElementById(`${fieldId}-error`);
      
      if (field) {
        field.parentElement.classList.add('input-error');
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
        field.parentElement.classList.remove('input-error');
      }
      if (errorElement) {
        errorElement.textContent = '';
        errorElement.classList.add('hidden');
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
      if (password.length >= 6 && password.length < 8) strength += 1;
      else if (password.length >= 8 && password.length < 12) strength += 2;
      else if (password.length >= 12) strength += 3;
      
      // Check for numbers
      if (/\d/.test(password)) {
        strength += 1;
      } else {
        feedback.push('Add numbers');
      }
      
      // Check for lowercase letters
      if (/[a-z]/.test(password)) {
        strength += 1;
      }
      
      // Check for uppercase letters
      if (/[A-Z]/.test(password)) {
        strength += 1;
      }
      
      // Check for symbols
      if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
        strength += 1;
      } else {
        feedback.push('Add symbols');
      }
      
      // Determine strength level
      let strengthLevel = 'Weak';
      if (strength <= 2) {
        strengthLevel = 'Weak';
        color = '#ef4444'; // Red
      } else if (strength <= 4) {
        strengthLevel = 'Medium';
        color = '#f59e0b'; // Amber
      } else if (strength <= 6) {
        strengthLevel = 'Strong';
        color = '#10b981'; // Green
      } else {
        strengthLevel = 'Very Strong';
        color = '#059669'; // Dark Green
      }
      
      return {
        strength: Math.min((strength / 8) * 100, 100),
        text: strengthLevel,
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

    // ====== ATTACH REAL-TIME VALIDATION ======
    const fieldsToValidate = [
      { id: 'firstname', type: 'name' },
      { id: 'lastname', type: 'name' },
      { id: 'middlename', type: 'name' },
      { id: 'suffix', type: 'name' },
      { id: 'username', type: 'username' },
      { id: 'email', type: 'email' },
      { id: 'phone', type: 'phone' },
      { id: 'password', type: 'password' },
      { id: 'confirm', type: 'password' },
      { id: 'street', type: 'street' },
      { id: 'otp', type: 'otp' }
    ];

    // ====== FORM INITIALIZATION (after DOM is ready) ======
    function initializeValidation() {
      // Attach input and blur listeners for real-time validation
      fieldsToValidate.forEach(field => {
        const element = document.getElementById(field.id);
        if (element) {
          element.addEventListener('input', () => validateField(field.id, field.type));
          element.addEventListener('blur', () => validateField(field.id, field.type));
        }
      });

      // Special handling for barangay select
      const barangaySelect = document.getElementById('barangay');
      if (barangaySelect) {
        barangaySelect.addEventListener('change', () => {
          if (!barangaySelect.value) {
            setFieldError('barangay', errorMessages.barangay);
          } else {
            clearFieldError('barangay');
          }
        });
      }

      // Special handling for password confirmation
      const passwordField = document.getElementById('password');
      const confirmField = document.getElementById('confirm');
      if (passwordField && confirmField) {
        passwordField.addEventListener('input', () => {
          // Update password strength meter
          updatePasswordStrength(passwordField.value);
          // Validate confirm field if it has a value
          if (confirmField.value) {
            validateField('confirm', 'password');
          }
        });
        confirmField.addEventListener('input', () => {
          validateField('confirm', 'password');
        });
      }
    }

    // ====== EXISTING FORM LOGIC ======
    const steps = Array.from(document.querySelectorAll('.form-step'));
    const form = document.getElementById('registerForm');
    const progressBar = document.getElementById('progress-bar');
    const stepNameEl = document.getElementById('step-name');
    const stepCurrentEl = document.getElementById('step-current');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const countrySelect = document.getElementById('country');
    const termsContainer = document.getElementById('terms-container');
    const barangaySelect = document.getElementById('barangay');
    let currentStep = 0;
    const stepNames = ["Personal Info", "Address", "Account Details", "Verification", "Finalize"];

    const provinces = {
      "PH": ["Davao Oriental", "Davao de Oro", "Davao del Norte", "Metro Manila"],
      "US": ["California", "Texas", "Florida", "New York"]
    };

    // Mati City Barangays
    const matiBarangays = [
      "Badas", "Bobon", "Buso", "Cawayanan", "Central", "Dahican", "Danao", "Dawan", "Don Enrique Lopez",
      "Don Martin Marundan", "Don Salvador Lopez Sr.", "Langka", "Lantawan", "Lawigan", "Libudon", "Luban",
      "Macambol", "Magsaysay", "Manay", "Matiao", "New Bataan", "New Libudon", "Old Macambol", "Poblacion",
      "Sainz", "San Isidro", "San Roque", "Tagabakid", "Tagbinonga", "Taguibo", "Tamisan"
    ];

    function populateBarangays() {
      barangaySelect.innerHTML = '<option value="">Select Barangay</option>'; // Clear existing options
      matiBarangays.sort().forEach(barangay => { // Sort alphabetically
        const option = document.createElement('option');
        option.value = barangay;
        option.textContent = barangay;
        barangaySelect.appendChild(option);
      });
    }


    function updateProgress() {
      // Hide all steps first to handle logic cleanly
      steps.forEach(step => step.classList.add('hidden'));
      // Then show the current one
      steps[currentStep].classList.remove('hidden');
      progressBar.style.width = `${((currentStep + 1) / steps.length) * 100}%`;
      stepNameEl.textContent = stepNames[currentStep];
      stepCurrentEl.textContent = currentStep + 1;
    }

    function goToStep(stepIndex) {
      steps[currentStep].classList.add('hidden');
      currentStep = stepIndex;
      updateProgress();
      updateButtons();
    }

    function updateButtons() {
      const navContainer = document.getElementById('navigation-buttons');
      const isFirstStep = currentStep === 0;
      const isLastStep = currentStep === steps.length - 1;

      // Show/hide the terms checkbox container based on the step
      termsContainer.classList.toggle('hidden', !isLastStep);

      prevBtn.classList.toggle('hidden', currentStep === 0);
      nextBtn.classList.toggle('hidden', isLastStep);
      submitBtn.classList.toggle('hidden', !isLastStep);
    }

    function validateStep(stepIndex) {
      const inputs = steps[stepIndex].querySelectorAll('input[required], select[required]');
      let isValid = true;
      let firstInvalidField = null;

      inputs.forEach(input => {
        const fieldId = input.id;
        const fieldType = getFieldValidationType(fieldId);
        
        // For required fields, first check if empty
        if (!input.value.trim()) {
          isValid = false;
          setFieldError(fieldId, `${input.name || fieldId} is required`);
          if (!firstInvalidField) firstInvalidField = fieldId;
        } else {
          // Then validate against regex
          if (validationPatterns[fieldType] && !validationPatterns[fieldType].test(input.value.trim())) {
            isValid = false;
            setFieldError(fieldId, errorMessages[fieldId] || "Invalid format");
            if (!firstInvalidField) firstInvalidField = fieldId;
          } else {
            clearFieldError(fieldId);
          }
        }
      });

      if (!isValid) {
        showToast("Please fix the highlighted errors before proceeding.", "error");
        return false;
      }

      // Step-specific validation
      if (stepIndex === 1) { // Address
        if (!document.getElementById('barangay').value) {
          setFieldError('barangay', errorMessages.barangay);
          showToast("Please select a barangay.", "error");
          return false;
        }
        clearFieldError('barangay');
      }

      if (stepIndex === 2) { // Account Details
        const pass = document.getElementById('password').value.trim();
        const confirm = document.getElementById('confirm').value.trim();
        
        // Validate password strength
        if (!validationPatterns.password.test(pass)) {
          setFieldError('password', errorMessages.password);
          showToast("Password must be 6+ characters with at least one number and symbol.", "error");
          return false;
        }
        clearFieldError('password');
        
        // Validate password match
        if (pass !== confirm) {
          setFieldError('confirm', errorMessages.confirm);
          showToast("Passwords do not match.", "error");
          return false;
        }
        clearFieldError('confirm');
      }

      if (stepIndex === 3) { // Verification
        const otpValue = document.getElementById('otp').value.trim();
        if (!otpValue) {
          setFieldError('otp', "Verification code is required");
          showToast("Please enter the verification code.", "error");
          return false;
        }
        if (!validationPatterns.otp.test(otpValue)) {
          setFieldError('otp', errorMessages.otp);
          showToast("Verification code must be 4-6 digits.", "error");
          return false;
        }
        clearFieldError('otp');
      }

      if (stepIndex === 4) { // Finalize
        if (!document.getElementById('terms').checked) {
          showToast("You must agree to the terms and conditions.", "error");
          return false;
        }
      }

      return true;
    }

    // Helper function to determine validation type based on field ID
    function getFieldValidationType(fieldId) {
      const typeMap = {
        'firstname': 'name',
        'lastname': 'name',
        'middlename': 'name',
        'suffix': 'name',
        'username': 'username',
        'email': 'email',
        'phone': 'phone',
        'password': 'password',
        'confirm': 'password',
        'street': 'street',
        'otp': 'otp',
        'barangay': 'barangay'
      };
      return typeMap[fieldId] || 'text';
    }

    // Comprehensive validation for all required fields across all steps
    function validateAllFields() {
      let isValid = true;
      
      // Get all required fields in the entire form
      const allRequiredFields = form.querySelectorAll('input[required], select[required]');
      
      allRequiredFields.forEach(field => {
        const fieldId = field.id;
        const fieldType = getFieldValidationType(fieldId);
        const value = field.value.trim();
        
        // Check if empty
        if (!value) {
          setFieldError(fieldId, `${field.name || fieldId} is required`);
          isValid = false;
          return;
        }
        
        // Validate against regex
        if (validationPatterns[fieldType] && !validationPatterns[fieldType].test(value)) {
          setFieldError(fieldId, errorMessages[fieldId] || "Invalid format");
          isValid = false;
          return;
        }
        
        clearFieldError(fieldId);
      });
      
      // Special check: password match
      const password = document.getElementById('password').value;
      const confirm = document.getElementById('confirm').value;
      if (password && confirm && password !== confirm) {
        setFieldError('confirm', errorMessages.confirm);
        isValid = false;
      }
      
      // Special check: terms checkbox
      if (!document.getElementById('terms').checked) {
        isValid = false;
      }
      
      return isValid;
    }

    nextBtn.addEventListener('click', () => {
      if (validateStep(currentStep)) {
        if (currentStep < steps.length - 1) {
          goToStep(currentStep + 1);
        }
      }
    });

    prevBtn.addEventListener('click', () => {
      if (currentStep > 0) {
        goToStep(currentStep - 1);
      }
    });

    populateBarangays(); // Populate barangays on load
    initializeValidation(); // Initialize real-time validation listeners


    function showToast(message, type = "error") {
      const toast = document.getElementById("toast");
      const msg = document.getElementById("toastMsg");

      msg.textContent = message;
      toast.classList.remove("hidden");
      toast.classList.add("show");
      toast.style.backgroundColor = type === "success" ? "#16a34a" : "#dc2626";

      setTimeout(() => {
        toast.classList.remove("show");
        setTimeout(() => toast.classList.add("hidden"), 400);
      }, 3500); // Give user time to read the message
    }

    // Handle form submission with AJAX to the PHP script
    form.addEventListener('submit', function(e) {
      e.preventDefault(); // Prevent the default form submission

      if (validateStep(currentStep)) { // Final validation on the last step
        const formData = new FormData(this);
        formData.append('register_submitted', '1');

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.textContent = 'Registering...';

        fetch('register.php', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData
        })
        .then(response => response.text())
        .then(text => {
          console.log('Raw response:', text);
          
          // Check if response is HTML (page reload) or JSON
          try {
            const data = JSON.parse(text);
            console.log('Parsed JSON:', data);
            
            if (data.status === 'success') {
              showToast(data.message || 'Registration successful!', 'success');
              const redirectUrl = data.redirect || '../user/user-homepage.php';
              setTimeout(() => window.location.href = redirectUrl, 2000);
            } else {
              showToast(data.message || "Registration failed. Please try again.", 'error');
              console.error('Registration error:', data);
              submitBtn.disabled = false;
              submitBtn.textContent = 'Complete Registration';
            }
          } catch (e) {
            console.error('JSON parse error:', e);
            console.log('Response text:', text);
            
            // HTML response means redirect happened or success
            if (text.includes('user-homepage.php') || text === '') {
              showToast('Registration successful!', 'success');
              setTimeout(() => window.location.href = "../user/user-homepage.php", 1500);
            } else {
              showToast("Registration failed. Please try again.", 'error');
              submitBtn.disabled = false;
              submitBtn.textContent = 'Complete Registration';
            }
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast("A network error occurred. Please try again.", 'error');
          submitBtn.disabled = false;
          submitBtn.textContent = 'Complete Registration';
        });
      }
    });

    // Email Verification Button Handler
    const sendVerificationBtn = document.getElementById('sendVerificationBtn');
    const verificationMessage = document.getElementById('verificationMessage');
    let verificationCodeSent = false;
    let actualVerificationCode = '';
    let verificationAttempts = 0;

    if (sendVerificationBtn) {
      sendVerificationBtn.addEventListener('click', function() {
        const emailInput = document.getElementById('email');
        const email = emailInput.value.trim();

        if (!email) {
          setFieldError('email', 'Email is required');
          showToast('Please enter your email address', 'error');
          return;
        }

        if (!validationPatterns.email.test(email)) {
          setFieldError('email', errorMessages.email);
          showToast('Please enter a valid email address', 'error');
          return;
        }

        clearFieldError('email');

        // Disable button and show loading
        sendVerificationBtn.disabled = true;
        sendVerificationBtn.textContent = 'Sending...';
        verificationAttempts = 0; // Reset attempts when sending new code

        fetch('verify-email.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            verificationCodeSent = true;
            if (data.dev_code) {
              actualVerificationCode = data.dev_code;
              verificationMessage.textContent = '✅ ' + data.message;
              verificationMessage.className = 'text-sm text-center text-green-600 font-medium';
            } else {
              verificationMessage.textContent = '✅ Code sent! Check your email.';
              verificationMessage.className = 'text-sm text-center text-green-600 font-medium';
            }
            verificationMessage.classList.remove('hidden');
            sendVerificationBtn.textContent = 'Code Sent ✓';
            sendVerificationBtn.classList.add('opacity-50', 'cursor-not-allowed');
            showToast(data.message, 'success');
            
            // Clear OTP field and error for new code entry
            const otpInput = document.getElementById('otp');
            if (otpInput) {
              otpInput.value = '';
              clearFieldError('otp');
            }
          } else {
            showToast(data.message || 'Failed to send verification code', 'error');
            sendVerificationBtn.disabled = false;
            sendVerificationBtn.textContent = 'Send Verification Code';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showToast('Failed to send verification code. Please try again.', 'error');
          sendVerificationBtn.disabled = false;
          sendVerificationBtn.textContent = 'Send Verification Code';
        });
      });
    }

    // Verify OTP when moving to next step
    const originalValidateStep = validateStep;
    validateStep = function(stepIndex) {
      if (stepIndex === 3) { // Verification step
        const otpInput = document.getElementById('otp');
        const otp = otpInput.value.trim();

        if (!otp) {
          setFieldError('otp', 'Verification code is required');
          showToast("Please enter the verification code.", "error");
          return false;
        }

        if (!validationPatterns.otp.test(otp)) {
          setFieldError('otp', errorMessages.otp);
          showToast("Verification code must be 4-6 digits.", "error");
          return false;
        }

        if (!verificationCodeSent) {
          setFieldError('otp', 'No verification code has been sent');
          showToast("Please request a verification code first.", "error");
          return false;
        }

        // Strict check: Code must match exactly
        if (!actualVerificationCode) {
          setFieldError('otp', 'Unable to verify - no code on file');
          showToast("Verification code not found. Please request a new code.", "error");
          return false;
        }

        // Case-sensitive exact match
        if (otp !== actualVerificationCode) {
          verificationAttempts++;
          const remaining = Math.max(0, 3 - verificationAttempts);
          let errorMsg = 'Verification code does not match. Please check and try again.';
          
          if (remaining > 0) {
            errorMsg += ` (${remaining} attempts remaining)`;
          }
          
          setFieldError('otp', 'Invalid verification code');
          showToast(errorMsg, "error");
          return false;
        }

        // Code matches - verification successful!
        clearFieldError('otp');
        showToast("Email verified successfully!", "success");
        return true;
      }

      return originalValidateStep(stepIndex);
    };

    // Add real-time OTP validation listener with immediate feedback
    const otpInputField = document.getElementById('otp');
    if (otpInputField) {
      otpInputField.addEventListener('input', () => {
        const otp = otpInputField.value.trim();
        
        // Real-time format validation
        if (otp && !validationPatterns.otp.test(otp)) {
          setFieldError('otp', 'Code must be 4-6 digits');
          return;
        }

        // If code is correct length and matches, show success immediately
        if (otp && validationPatterns.otp.test(otp) && verificationCodeSent && actualVerificationCode) {
          if (otp === actualVerificationCode) {
            clearFieldError('otp');
            // Show inline success indicator
            const errorSpan = document.getElementById('otp-error');
            if (errorSpan) {
              errorSpan.textContent = 'Code verified!';
              errorSpan.className = 'error-message text-green-600';
              errorSpan.classList.remove('hidden');
            }
          } else {
            // Code doesn't match but is valid format
            setFieldError('otp', 'Code does not match');
          }
        } else if (!otp) {
          clearFieldError('otp');
        }
      });

      // Blur event for final validation
      otpInputField.addEventListener('blur', () => {
        const otp = otpInputField.value.trim();
        if (otp) {
          if (!validationPatterns.otp.test(otp)) {
            setFieldError('otp', 'Code must be 4-6 digits');
          } else if (verificationCodeSent && actualVerificationCode && otp !== actualVerificationCode) {
            setFieldError('otp', 'Code does not match');
          }
        }
      });
    }

    // ====== MODAL HANDLERS ======
    function setupModalHandlers() {
      // Get modal elements
      const termsModal = document.getElementById('termsModal');
      const privacyModal = document.getElementById('privacyModal');
      
      // Get trigger links
      const termsLink = document.getElementById('termsLink');
      const privacyLink = document.getElementById('privacyLink');
      
      // Get close buttons
      const closeTermsModal = document.getElementById('closeTermsModal');
      const closeTermsBtn = document.getElementById('closeTermsBtn');
      const closePrivacyModal = document.getElementById('closePrivacyModal');
      const closePrivacyBtn = document.getElementById('closePrivacyBtn');

      // Function to open modal
      function openModal(modal) {
        if (modal) {
          modal.classList.remove('hidden');
          document.body.style.overflow = 'hidden'; // Prevent body scroll
        }
      }

      // Function to close modal
      function closeModal(modal) {
        if (modal) {
          modal.classList.add('hidden');
          document.body.style.overflow = 'auto'; // Re-enable body scroll
        }
      }

      // Terms Modal Events
      if (termsLink) {
        termsLink.addEventListener('click', (e) => {
          e.preventDefault();
          openModal(termsModal);
        });
      }

      if (closeTermsModal) {
        closeTermsModal.addEventListener('click', () => {
          closeModal(termsModal);
        });
      }

      if (closeTermsBtn) {
        closeTermsBtn.addEventListener('click', () => {
          closeModal(termsModal);
        });
      }

      // Privacy Modal Events
      if (privacyLink) {
        privacyLink.addEventListener('click', (e) => {
          e.preventDefault();
          openModal(privacyModal);
        });
      }

      if (closePrivacyModal) {
        closePrivacyModal.addEventListener('click', () => {
          closeModal(privacyModal);
        });
      }

      if (closePrivacyBtn) {
        closePrivacyBtn.addEventListener('click', () => {
          closeModal(privacyModal);
        });
      }

      // Close modal when clicking outside of it (on backdrop)
      if (termsModal) {
        termsModal.addEventListener('click', (e) => {
          if (e.target === termsModal) {
            closeModal(termsModal);
          }
        });
      }

      if (privacyModal) {
        privacyModal.addEventListener('click', (e) => {
          if (e.target === privacyModal) {
            closeModal(privacyModal);
          }
        });
      }
    }

    // Initial setup
    updateProgress();
    setupModalHandlers();
  </script>
</body>

</html>