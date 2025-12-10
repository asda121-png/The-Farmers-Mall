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
// PHP SCRIPT END
?>
        });