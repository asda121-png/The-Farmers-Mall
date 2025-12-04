<?php
require __DIR__ . '/../PHPMailer-master/src/Exception.php';
require __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($toEmail, $code) {
    $logFile = __DIR__ . '/../debug_email.log';
    $timestamp = date('Y-m-d H:i:s');
    
    try {
        @file_put_contents($logFile, "[$timestamp] Starting email send to: $toEmail\n", FILE_APPEND);
        
        $mail = new PHPMailer(true);
        
        // SMTP Configuration for Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mati.farmersmall@gmail.com';
        $mail->Password   = 'hiov goyk hblw nizf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        @file_put_contents($logFile, "[$timestamp] SMTP configured for Gmail\n", FILE_APPEND);
        
        // SSL/TLS Configuration
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->setFrom('mati.farmersmall@gmail.com', 'Farmers Mall');
        $mail->addAddress($toEmail);
        $mail->addReplyTo('mati.farmersmall@gmail.com', 'Farmers Mall');
        
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Farmers Mall - Email Verification Code';
        
        $mail->Body = "
            <html>
                <body style='font-family: Arial, sans-serif;'>
                    <h3 style='color: #228B22;'>Email Verification</h3>
                    <p>Your verification code is:</p>
                    <h2 style='color: #15803d; font-size: 32px;'>$code</h2>
                    <p><strong>This code expires in 5 minutes.</strong></p>
                    <p>Do not share this code with anyone.</p>
                </body>
            </html>
        ";
        
        @file_put_contents($logFile, "[$timestamp] Email body prepared, attempting to send...\n", FILE_APPEND);
        
        $mail->send();
        
        @file_put_contents($logFile, "[$timestamp] ✅ SUCCESS - Email sent to: $toEmail\n", FILE_APPEND);
        return true;
        
    } catch (Exception $e) {
        $errorMsg = "PHPMailer Error: " . (isset($mail) ? $mail->ErrorInfo : $e->getMessage());
        @file_put_contents($logFile, "[$timestamp] ❌ FAILED - $errorMsg\n", FILE_APPEND);
        return false;
    }
}
