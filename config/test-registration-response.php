<?php
// Test what the registration endpoint returns

$testData = [
    'register_submitted' => '1',
    'firstname' => 'Test',
    'lastname' => 'User',
    'email' => 'testuser' . time() . '@example.com',
    'phone' => '09123456789',
    'username' => 'testuser' . time(),
    'password' => 'Test123!@#',
    'confirm' => 'Test123!@#',  // Changed from confirm_password to confirm
    'address' => '123 Test St, Test Barangay, Test City, Test Province, 1234',
    'terms' => '1',  // Terms checkbox
    'verification_code' => '123456'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/auth/register.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-Requested-With: XMLHttpRequest'
]);

echo "Sending test registration...\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Code: $httpCode\n";
echo "Response Length: " . strlen($response) . " bytes\n";
echo "First 200 chars: " . substr($response, 0, 200) . "\n\n";
echo "Full Response:\n";
echo $response . "\n\n";

// Try to parse as JSON
echo "Attempting JSON parse:\n";
$json = json_decode($response, true);
if ($json) {
    echo "✅ Valid JSON!\n";
    print_r($json);
} else {
    echo "❌ Invalid JSON: " . json_last_error_msg() . "\n";
}
?>
