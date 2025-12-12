<?php
/**
 * Debug Google OAuth Token Exchange
 * 
 * This script helps diagnose token exchange failures
 */

require_once __DIR__ . '/config/env.php';

$clientId = getenv('GOOGLE_CLIENT_ID');
$clientSecret = getenv('GOOGLE_CLIENT_SECRET');
$redirectUri = 'http://localhost/The-Farmers-Mall/auth/google-callback.php';

echo "<h2>🔍 Google OAuth Token Exchange Diagnostic</h2>\n\n";

// 1. Check credentials
echo "<h3>1️⃣ Credentials Check</h3>\n";
echo "<pre>";
echo "Client ID: " . (substr($clientId, 0, 20) . "...") . "\n";
echo "Client Secret: " . (strlen($clientSecret) > 0 ? "✅ Present" : "❌ Missing") . "\n";
echo "Redirect URI: " . $redirectUri . "\n";
echo "</pre>\n\n";

// 2. Check cURL
echo "<h3>2️⃣ cURL Support</h3>\n";
echo "<pre>";
echo "cURL Extension: " . (extension_loaded('curl') ? "✅ Installed" : "❌ Not installed") . "\n";
if (extension_loaded('curl')) {
    $curlVersion = curl_version();
    echo "cURL Version: " . $curlVersion['version'] . "\n";
    echo "OpenSSL: " . $curlVersion['ssl_version'] . "\n";
}
echo "</pre>\n\n";

// 3. Test cURL connection
echo "<h3>3️⃣ Test Token Endpoint Connection</h3>\n";
echo "<pre>";

$testCode = 'test_code_12345'; // Dummy code for testing

$params = [
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'redirect_uri' => $redirectUri,
    'grant_type' => 'authorization_code',
    'code' => $testCode
];

echo "Testing connection to Google OAuth token endpoint...\n\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://oauth2.googleapis.com/token',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($params),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_VERBOSE => true,
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "cURL Error: " . ($curlError ?: "None") . "\n\n";
echo "Response:\n";
echo $response . "\n";

// Parse response
$data = json_decode($response, true);
if (isset($data['error'])) {
    echo "\n❌ Google Error: " . $data['error'] . "\n";
    echo "Description: " . ($data['error_description'] ?? 'N/A') . "\n\n";
    
    if ($data['error'] === 'invalid_grant') {
        echo "💡 This is normal - we're using a dummy code to test.\n";
        echo "   Real authorization codes are single-use.\n";
    } elseif ($data['error'] === 'invalid_client') {
        echo "⚠️ Client ID or Secret is invalid!\n";
        echo "   Check your credentials in config/.env\n";
    } elseif ($data['error'] === 'redirect_uri_mismatch') {
        echo "⚠️ Redirect URI doesn't match Google Console config!\n";
        echo "   Authorized URI: " . $redirectUri . "\n";
        echo "   Add this to Google Cloud Console > OAuth 2.0 Credentials\n";
    }
}

echo "</pre>\n\n";

// 4. SSL/TLS Check
echo "<h3>4️⃣ SSL/TLS Check</h3>\n";
echo "<pre>";

$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
]);

$fp = @fsockopen('ssl://oauth2.googleapis.com', 443, $errno, $errstr, 5);
if ($fp) {
    echo "✅ Can connect to Google OAuth endpoint (SSL)\n";
    fclose($fp);
} else {
    echo "❌ Cannot connect to Google OAuth endpoint\n";
    echo "   Error: " . $errstr . " ($errno)\n";
}

echo "</pre>\n\n";

// 5. What to do next
echo "<h3>5️⃣ Next Steps</h3>\n";
echo "<pre>";
echo "1. Check if credentials are correct in config/.env\n";
echo "2. Verify Redirect URI in Google Cloud Console:\n";
echo "   https://console.cloud.google.com/apis/credentials\n";
echo "3. Make sure these URIs are authorized:\n";
echo "   - http://localhost/The-Farmers-Mall/auth/google-callback.php\n";
echo "   - http://127.0.0.1/The-Farmers-Mall/auth/google-callback.php\n";
echo "4. Check the error logs for more details\n";
echo "5. Try testing again after updating credentials\n";
echo "</pre>\n";

// 6. Environment variables
echo "<h3>6️⃣ Environment Variables</h3>\n";
echo "<pre>";
echo "GOOGLE_CLIENT_ID is set: " . (getenv('GOOGLE_CLIENT_ID') ? "✅ Yes" : "❌ No") . "\n";
echo "GOOGLE_CLIENT_SECRET is set: " . (getenv('GOOGLE_CLIENT_SECRET') ? "✅ Yes" : "❌ No") . "\n";
echo "</pre>\n";
?>
