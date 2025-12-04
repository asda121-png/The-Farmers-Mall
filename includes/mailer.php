<?php
// Verify PHPMailer files exist before including
$phpmailerPath = __DIR__ . '/../PHPMailer-master/src';

if (!file_exists($phpmailerPath . '/Exception.php')) {
    die('PHPMailer Exception.php not found at ' . $phpmailerPath);
}
if (!file_exists($phpmailerPath . '/PHPMailer.php')) {
    die('PHPMailer.php not found at ' . $phpmailerPath);
}
if (!file_exists($phpmailerPath . '/SMTP.php')) {
    die('SMTP.php not found at ' . $phpmailerPath);
}

require $phpmailerPath . '/Exception.php';
require $phpmailerPath . '/PHPMailer.php';
require $phpmailerPath . '/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($toEmail, $code) {
    $logFile = __DIR__ . '/../debug_email.log';
    
    try {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);
        
        // Log the attempt
        file_put_contents($logFile, "\n[" . date('Y-m-d H:i:s') . "] ========== SENDING EMAIL ==========\n", FILE_APPEND);
        file_put_contents($logFile, "To: $toEmail\n", FILE_APPEND);
        file_put_contents($logFile, "Code: $code\n", FILE_APPEND);

        // SMTP Configuration for Gmail
        $mail->isSMTP();
        file_put_contents($logFile, "✓ Set SMTP mode\n", FILE_APPEND);
        
        $mail->Host = 'smtp.gmail.com';
        file_put_contents($logFile, "✓ Set Host\n", FILE_APPEND);
        
        $mail->SMTPAuth = true;
        file_put_contents($logFile, "✓ Set SMTPAuth\n", FILE_APPEND);
        
        $mail->Username = 'mati.farmersmall@gmail.com';
        file_put_contents($logFile, "✓ Set Username\n", FILE_APPEND);
        
        $mail->Password = 'hiov goyk hblw nizf';
        file_put_contents($logFile, "✓ Set Password\n", FILE_APPEND);
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        file_put_contents($logFile, "✓ Set SMTPSecure\n", FILE_APPEND);
        
        $mail->Port = 587;
        file_put_contents($logFile, "✓ Set Port\n", FILE_APPEND);
        
        // SSL/TLS Configuration
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            )
        );
        file_put_contents($logFile, "✓ Set SMTPOptions\n", FILE_APPEND);

        // Email Headers
        $mail->setFrom('mati.farmersmall@gmail.com', 'Farmers Mall');
        file_put_contents($logFile, "✓ Set From\n", FILE_APPEND);
        
        $mail->addAddress($toEmail);
        file_put_contents($logFile, "✓ Added recipient\n", FILE_APPEND);
        
        $mail->addReplyTo('mati.farmersmall@gmail.com', 'Farmers Mall Support');
        file_put_contents($logFile, "✓ Set Reply-To\n", FILE_APPEND);

        // Email Content
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Farmers Mall - Email Verification Code';
        
        $mail->Body = "
            <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
                        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                        .header { color: #228B22; text-align: center; margin-bottom: 20px; }
                        .code-box { background-color: #f0f8f0; border: 2px solid #228B22; padding: 15px; text-align: center; border-radius: 5px; margin: 20px 0; }
                        .code-text { font-size: 32px; font-weight: bold; color: #15803d; letter-spacing: 5px; }
                        .footer { color: #666; font-size: 12px; text-align: center; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>🌾 Farmers Mall</h2>
                            <h3>Email Verification</h3>
                        </div>
                        
                        <p>Hello,</p>
                        
                        <p>Thank you for registering with Farmers Mall! To complete your registration, please use the verification code below:</p>
                        
                        <div class='code-box'>
                            <div class='code-text'>$code</div>
                        </div>
                        
                        <p><strong>Important:</strong></p>
                        <ul>
                            <li>This code expires in <strong>5 minutes</strong></li>
                            <li>Do not share this code with anyone</li>
                            <li>Farmers Mall staff will never ask for this code</li>
                        </ul>
                        
                        <p>If you did not request this verification code, please ignore this email.</p>
                        
                        <div class='footer'>
                            <p>&copy; 2025 Farmers Mall. All rights reserved.</p>
                            <p>Connecting farmers and consumers directly for fresh, local, and organic produce.</p>
                        </div>
                    </div>
                </body>
            </html>
        ";
        
        file_put_contents($logFile, "✓ Set email content\n", FILE_APPEND);

        // Send the email
        file_put_contents($logFile, "Attempting to send...\n", FILE_APPEND);
        $mail->send();
        
        file_put_contents($logFile, "✅ EMAIL SENT SUCCESSFULLY\n", FILE_APPEND);
        
        return true;
        
    } catch (Exception $e) {
        $errorMsg = "Exception: " . $e->getMessage();
        if (isset($mail)) {
            $errorMsg .= " | PHPMailer Error: " . $mail->ErrorInfo;
        }
        
        file_put_contents($logFile, "❌ SEND FAILED: $errorMsg\n", FILE_APPEND);
        error_log("sendVerificationEmail() Error: $errorMsg");
        
        return false;
        
    } catch (Throwable $t) {
        $errorMsg = "Throwable: " . $t->getMessage();
        file_put_contents($logFile, "❌ THROWABLE ERROR: $errorMsg\n", FILE_APPEND);
        error_log("sendVerificationEmail() Throwable: $errorMsg");
        
        return false;
    }
}

?>
