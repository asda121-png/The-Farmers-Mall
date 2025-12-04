<?php
// Simple test to verify email sending works

// Start session
session_start();

// Test email address - change this to your email
$testEmail = 'your-email@gmail.com'; // CHANGE THIS TO YOUR EMAIL
$testCode = '123456';

echo "<h2>Testing Email Verification System</h2>";

// Include the mailer
require_once __DIR__ . '/includes/mailer.php';

echo "<p>Sending test email to: <strong>$testEmail</strong></p>";
echo "<p>Test Code: <strong>$testCode</strong></p>";

// Send the test email
$result = sendVerificationEmail($testEmail, $testCode);

if ($result) {
    echo "<p style='color: green; font-weight: bold;'>✅ Email sent successfully!</p>";
    echo "<p>Check your inbox (and spam folder) for the verification email.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Email failed to send.</p>";
    echo "<p>Check the debug log at: <code>debug_email.log</code></p>";
}

// Show debug logs if they exist
if (file_exists(__DIR__ . '/debug_email.log')) {
    echo "<hr>";
    echo "<h3>Debug Log:</h3>";
    echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
    echo htmlspecialchars(file_get_contents(__DIR__ . '/debug_email.log'));
    echo "</pre>";
}

if (file_exists(__DIR__ . '/debug_verification.log')) {
    echo "<hr>";
    echo "<h3>Verification Log:</h3>";
    echo "<pre style='background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
    echo htmlspecialchars(file_get_contents(__DIR__ . '/debug_verification.log'));
    echo "</pre>";
}
?>
