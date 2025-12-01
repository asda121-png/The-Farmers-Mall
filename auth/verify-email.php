<?php
/**
 * Email Verification Handler
 * Sends verification codes via email
 */

// Start session for storing verification codes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
    
    if (!$email) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    // Generate 6-digit verification code
    $verificationCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Store in session with expiry (5 minutes)
    $_SESSION['verification_code'] = $verificationCode;
    $_SESSION['verification_email'] = $email;
    $_SESSION['verification_expiry'] = time() + 300; // 5 minutes
    
    // Try to send email using mail() function
    // Note: This requires a mail server configured on the system
    $subject = "Farmers Mall - Email Verification Code";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #228B22; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .code { font-size: 32px; font-weight: bold; color: #228B22; letter-spacing: 5px; text-align: center; padding: 20px; background: white; border-radius: 10px; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🌾 Farmers Mall</h1>
            </div>
            <div class='content'>
                <h2>Email Verification</h2>
                <p>Thank you for registering with Farmers Mall!</p>
                <p>Your verification code is:</p>
                <div class='code'>$verificationCode</div>
                <p>This code will expire in 5 minutes.</p>
                <p>If you didn't request this code, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>&copy; 2025 Farmers Mall. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@farmersmall.com" . "\r\n";
    
    // Attempt to send email
    $emailSent = @mail($email, $subject, $message, $headers);
    
    if ($emailSent) {
        echo json_encode([
            'success' => true,
            'message' => 'Verification code sent to your email'
        ]);
    } else {
        // For development: return the code in response (remove in production)
        echo json_encode([
            'success' => true,
            'message' => 'Email server not configured. Your verification code is: ' . $verificationCode,
            'dev_code' => $verificationCode // Only for development
        ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['verify'])) {
    // Verify the code
    $code = $_GET['code'] ?? '';
    
    if (!isset($_SESSION['verification_code'])) {
        echo json_encode(['success' => false, 'message' => 'No verification code found']);
        exit;
    }
    
    if (time() > $_SESSION['verification_expiry']) {
        unset($_SESSION['verification_code']);
        unset($_SESSION['verification_expiry']);
        echo json_encode(['success' => false, 'message' => 'Verification code expired']);
        exit;
    }
    
    if ($code === $_SESSION['verification_code']) {
        echo json_encode(['success' => true, 'message' => 'Email verified successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid verification code']);
    }
}
?>
