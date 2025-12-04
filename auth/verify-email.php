<?php
// FILE: verify-email.php - Endpoint for generating and sending OTP

header('Content-Type: application/json');

// Enable error reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);

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
$logFile = __DIR__ . '/../debug_verification.log';

// Log the request
file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] ========== NEW VERIFICATION REQUEST ==========\n", FILE_APPEND);
file_put_contents($logFile, "Raw input: $input\n", FILE_APPEND);
file_put_contents($logFile, "Decoded email: $email\n", FILE_APPEND);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Invalid email address.';
    file_put_contents($logFile, "❌ Email validation failed\n", FILE_APPEND);
    echo json_encode($response);
    exit();
}

file_put_contents($logFile, "✅ Email validation passed\n", FILE_APPEND);

// 3. Generate a 6-digit random code
$verification_code = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
$expiration_time = time() + (5 * 60); // Code expires in 5 minutes

file_put_contents($logFile, "Generated code: $verification_code\n", FILE_APPEND);

// 4. Store the code and its expiration in the session
$_SESSION['verification_code'] = $verification_code;
$_SESSION['code_email'] = $email;
$_SESSION['code_expires'] = $expiration_time;

file_put_contents($logFile, "Session data stored\n", FILE_APPEND);
file_put_contents($logFile, "Session verification_code: " . $_SESSION['verification_code'] . "\n", FILE_APPEND);
file_put_contents($logFile, "Session code_email: " . $_SESSION['code_email'] . "\n", FILE_APPEND);

// 5. Send the verification email
require_once __DIR__ . '/../includes/mailer.php';

try {
    file_put_contents($logFile, "Calling sendVerificationEmail()...\n", FILE_APPEND);
    
    $email_sent = sendVerificationEmail($email, $verification_code);
    
    file_put_contents($logFile, "sendVerificationEmail returned: " . ($email_sent ? 'TRUE' : 'FALSE') . "\n", FILE_APPEND);
    
    if ($email_sent) {
        $response['success'] = true;
        $response['message'] = "A 6-digit code has been sent to $email. It expires in 5 minutes.";
        file_put_contents($logFile, "✅ RESPONSE: SUCCESS\n", FILE_APPEND);
    } else {
        $response['message'] = 'Error sending email. The email service may be temporarily unavailable. Please try again.';
        file_put_contents($logFile, "❌ RESPONSE: FAILED - sendVerificationEmail returned false\n", FILE_APPEND);
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    file_put_contents($logFile, "❌ EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
    file_put_contents($logFile, "Stack trace:\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
}

file_put_contents($logFile, "Final response: " . json_encode($response) . "\n", FILE_APPEND);

echo json_encode($response);
?>