<?php
/**
 * PHP cURL and SSL/TLS Diagnostic
 * 
 * Check if your PHP has cURL and SSL/TLS support
 */

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>PHP cURL & SSL Diagnostic</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }";
echo "h2 { color: #333; }";
echo ".check { margin: 15px 0; padding: 10px; background: #f9f9f9; border-left: 4px solid #ddd; }";
echo ".check.ok { border-left-color: #4CAF50; }";
echo ".check.error { border-left-color: #f44336; }";
echo ".status { font-weight: bold; }";
echo ".status.ok { color: #4CAF50; }";
echo ".status.error { color: #f44336; }";
echo ".code { background: #f0f0f0; padding: 10px; border-radius: 4px; font-family: monospace; overflow-x: auto; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h2>🔍 PHP cURL & SSL/TLS Diagnostic</h2>";

// 1. PHP Version
echo "<div class='check ok'>";
echo "<strong>PHP Version:</strong><br>";
echo "<span class='status ok'>✅ " . phpversion() . "</span>";
echo "</div>";

// 2. Check cURL Extension
echo "<div class='check " . (extension_loaded('curl') ? 'ok' : 'error') . "'>";
echo "<strong>cURL Extension:</strong><br>";
if (extension_loaded('curl')) {
    echo "<span class='status ok'>✅ INSTALLED & ENABLED</span>";
    
    $curlVersion = curl_version();
    echo "<br><br>";
    echo "<strong>cURL Details:</strong><br>";
    echo "Version: " . $curlVersion['version'] . "<br>";
    echo "Host: " . $curlVersion['host'] . "<br>";
} else {
    echo "<span class='status error'>❌ NOT INSTALLED</span>";
    echo "<br><br>";
    echo "<strong>To fix:</strong><br>";
    echo "1. Open your php.ini file<br>";
    echo "2. Find the line: <code>;extension=curl</code><br>";
    echo "3. Remove the semicolon: <code>extension=curl</code><br>";
    echo "4. Restart Apache/WAMP<br>";
}
echo "</div>";

// 3. Check OpenSSL
echo "<div class='check " . (extension_loaded('openssl') ? 'ok' : 'error') . "'>";
echo "<strong>OpenSSL Extension:</strong><br>";
if (extension_loaded('openssl')) {
    echo "<span class='status ok'>✅ INSTALLED & ENABLED</span>";
    echo "<br><br>";
    echo "OpenSSL Version: " . OPENSSL_VERSION_TEXT . "<br>";
} else {
    echo "<span class='status error'>❌ NOT INSTALLED</span>";
    echo "<br><br>";
    echo "To fix:<br>";
    echo "1. Open php.ini<br>";
    echo "2. Find: <code>;extension=openssl</code><br>";
    echo "3. Remove semicolon: <code>extension=openssl</code><br>";
    echo "4. Restart Apache/WAMP<br>";
}
echo "</div>";

// 4. Check SSL/TLS Support in cURL
if (extension_loaded('curl')) {
    echo "<div class='check ok'>";
    echo "<strong>cURL SSL/TLS Support:</strong><br>";
    
    $curlVersion = curl_version();
    echo "Features: " . (isset($curlVersion['features']) ? $curlVersion['features'] : 'N/A') . "<br>";
    
    if (CURL_VERSION_SSL) {
        echo "<span class='status ok'>✅ SSL/TLS SUPPORTED</span>";
    } else {
        echo "<span class='status error'>❌ SSL/TLS NOT SUPPORTED</span>";
    }
    
    echo "<br><br>";
    echo "SSL Version: " . (isset($curlVersion['ssl_version']) ? $curlVersion['ssl_version'] : 'N/A') . "<br>";
    echo "</div>";
}

// 5. Test HTTPS Connection
echo "<div class='check'>";
echo "<strong>Test HTTPS Connection to Google:</strong><br>";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://www.google.com',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_NOBODY => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response !== false && $httpCode === 200) {
    echo "<div class='check ok'>";
    echo "<span class='status ok'>✅ HTTPS CONNECTION WORKS</span>";
    echo "</div>";
} else {
    echo "<div class='check error'>";
    echo "<span class='status error'>❌ HTTPS CONNECTION FAILED</span>";
    echo "<br>";
    echo "HTTP Code: " . $httpCode . "<br>";
    echo "Error: " . $curlError . "<br>";
    echo "<br>";
    echo "Try with SSL verification disabled:<br>";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://www.google.com',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_NOBODY => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($response !== false) {
        echo "<span class='status ok'>✅ Works without SSL verification</span>";
        echo "<br>Note: For development, this is OK. For production, fix SSL certificates.";
    } else {
        echo "<span class='status error'>❌ Still fails</span>";
        echo "<br>Error: " . $curlError;
    }
    echo "</div>";
}

echo "</div>";

// 6. Summary
echo "<div class='check'>";
echo "<h3>📋 Summary</h3>";

$allGood = extension_loaded('curl') && extension_loaded('openssl');

if ($allGood) {
    echo "<div class='check ok'>";
    echo "<span class='status ok'>✅ Your PHP is properly configured for Google OAuth</span>";
    echo "<br>";
    echo "You should be able to use Google OAuth without issues.";
    echo "</div>";
} else {
    echo "<div class='check error'>";
    echo "<span class='status error'>❌ Your PHP needs configuration</span>";
    echo "<br>";
    echo "Please enable the missing extensions and restart Apache/WAMP.";
    echo "</div>";
}

echo "</div>";

echo "</body>";
echo "</html>";
?>
