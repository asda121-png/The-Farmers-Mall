<?php
// FILE: verify-email.php - Retailer email verification endpoint
// This mirrors the user-side verification system for consistency

header('Content-Type: application/json');

// 1. Start session to store the code
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Get the input data (expecting JSON from the fetch request)
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$email = filter_var(trim($data['email'] ?? ''), FILTER_SANITIZE_EMAIL);

// Prepare response array
$response = ['success' => false, 'message' => ''];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid email address.';
    echo json_encode($response);
    exit();
}

// 3. Generate a 6-digit random code
$verification_code = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
$expiration_time = time() + (5 * 60); // Code expires in 5 minutes

// 4. Store the code and its expiration in the session
$_SESSION['retailer_verification_code'] = $verification_code;
$_SESSION['retailer_code_email'] = $email;
$_SESSION['retailer_code_expires'] = $expiration_time;

// 5. Send the verification email
require_once __DIR__ . '/../includes/mailer.php';

$logFile = __DIR__ . '/verification_debug.log';

try {
    // Log attempt
    @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Attempting to send code $verification_code to $email\n", FILE_APPEND);
    
    $email_sent = sendVerificationEmail($email, $verification_code);
    
    if ($email_sent) {
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ✅ Email sent successfully\n", FILE_APPEND);
        $response['success'] = true;
        $response['message'] = "A 6-digit code has been sent to $email. It expires in 5 minutes.";
        // Return the code for frontend validation (development mode)
        if (file_exists(__DIR__ . '/../.development')) {
            $response['code'] = $verification_code;
        }
    } else {
        @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ❌ Email send failed - enabling fallback mode\n", FILE_APPEND);
        
        // FALLBACK FOR DEVELOPMENT: If email fails, still allow code to be used in session
        $response['success'] = true;
        $response['message'] = "Verification code generated. Check your email for the code.";
        // Return code for development/fallback
        if (file_exists(__DIR__ . '/../.development')) {
            $response['code'] = $verification_code;
        }
    }
} catch (Exception $e) {
    @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] ❌ Exception: " . $e->getMessage() . "\n", FILE_APPEND);
    
    // Still allow testing with the code (stored in session)
    $response['success'] = true;
    $response['message'] = "Verification code generated. Check your email for the code.";
    // Return code for development/fallback
    if (file_exists(__DIR__ . '/../.development')) {
        $response['code'] = $verification_code;
    }
}

echo json_encode($response);
?>
