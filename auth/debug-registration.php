<?php
// Debug Registration Submissions
// Save this to check what data is being received

$logFile = __DIR__ . '/registration_debug.log';

$timestamp = date('Y-m-d H:i:s');
$method = $_SERVER['REQUEST_METHOD'];

$logEntry = "\n========================================\n";
$logEntry .= "Timestamp: $timestamp\n";
$logEntry .= "Method: $method\n";
$logEntry .= "POST Data:\n" . print_r($_POST, true) . "\n";
$logEntry .= "Files: " . print_r($_FILES, true) . "\n";

file_put_contents($logFile, $logEntry, FILE_APPEND);

echo "Debug info saved to registration_debug.log\n";
echo "POST vars: " . count($_POST) . "\n";
echo "Has register_submitted: " . (isset($_POST['register_submitted']) ? 'YES' : 'NO') . "\n";
?>
