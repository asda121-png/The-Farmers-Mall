<?php
// Temporary debug file to show your redirect URI
// Delete this file after checking

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/google-oauth.php';

try {
    $oauth = new GoogleOAuth();
    $redirectUri = $oauth->getRedirectUri();
    
    echo "<h2>Your Application's Redirect URI:</h2>";
    echo "<code style='font-size: 18px; background: #f0f0f0; padding: 15px; display: block; margin: 20px 0;'>";
    echo htmlspecialchars($redirectUri);
    echo "</code>";
    
    echo "<p><strong>Copy the above URL exactly</strong> and add it to your Google Cloud Console authorized redirect URIs.</p>";
    echo "<hr>";
    echo "<h3>What to do:</h3>";
    echo "<ol>";
    echo "<li>Copy the URL above</li>";
    echo "<li>Go to <a href='https://console.cloud.google.com/apis/credentials' target='_blank'>Google Cloud Console - Credentials</a></li>";
    echo "<li>Click on your OAuth 2.0 Client ID</li>";
    echo "<li>Find 'Authorized redirect URIs'</li>";
    echo "<li>Click 'Add URI'</li>";
    echo "<li>Paste the URL exactly as shown above</li>";
    echo "<li>Save the changes</li>";
    echo "<li>Wait 10 seconds, then try logging in again</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
