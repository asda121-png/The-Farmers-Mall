<?php
/**
 * GOOGLE OAUTH DIAGNOSTIC TOOL
 * 
 * This tool shows you exactly what redirect URI your application is generating
 * Use this to verify it matches what's configured in Google Cloud Console
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/google-oauth.php';
require_once __DIR__ . '/config/env.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Google OAuth Diagnostic Tool</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; }
        .section { background: #f5f5f5; padding: 20px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #4285F4; }
        .success { border-left-color: #34A853; background: #f0f9ff; }
        .error { border-left-color: #EA4335; background: #fff0f0; }
        .warning { border-left-color: #FBBC05; background: #fffbf0; }
        code { 
            background: #2d2d2d; 
            color: #f8f8f2; 
            padding: 15px; 
            display: block; 
            margin: 10px 0; 
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
        }
        h2 { color: #1f2937; margin-top: 30px; }
        .status { font-weight: bold; }
        .status.ok { color: #34A853; }
        .status.bad { color: #EA4335; }
        ul { line-height: 1.8; }
        .action { 
            background: #4285F4; 
            color: white; 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>🔍 Google OAuth Diagnostic Tool</h1>
    
    <div class="section success">
        <h2>✅ Your Redirect URI</h2>
        <p>This is the <strong>EXACT</strong> URL you must add to Google Cloud Console:</p>
        <?php
        try {
            $oauth = new GoogleOAuth();
            $redirectUri = $oauth->getRedirectUri();
            echo "<code>" . htmlspecialchars($redirectUri) . "</code>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>📋 Environment Variables Status</h2>
        <ul>
            <li><strong>GOOGLE_CLIENT_ID:</strong> 
                <span class="status <?php echo (getenv('GOOGLE_CLIENT_ID') ? 'ok' : 'bad'); ?>">
                    <?php echo getenv('GOOGLE_CLIENT_ID') ? '✅ Configured' : '❌ NOT SET'; ?>
                </span>
                <?php if (getenv('GOOGLE_CLIENT_ID')) echo '<code>' . htmlspecialchars(substr(getenv('GOOGLE_CLIENT_ID'), 0, 30)) . '...</code>'; ?>
            </li>
            <li><strong>GOOGLE_CLIENT_SECRET:</strong> 
                <span class="status <?php echo (getenv('GOOGLE_CLIENT_SECRET') ? 'ok' : 'bad'); ?>">
                    <?php echo getenv('GOOGLE_CLIENT_SECRET') ? '✅ Configured' : '❌ NOT SET'; ?>
                </span>
            </li>
        </ul>
    </div>

    <div class="section">
        <h2>🔧 Server Information</h2>
        <ul>
            <li><strong>HTTP_HOST:</strong> <code><?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?></code></li>
            <li><strong>SCRIPT_NAME:</strong> <code><?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?></code></li>
            <li><strong>Protocol:</strong> <code><?php echo (!empty($_SERVER['HTTPS']) ? 'HTTPS' : 'HTTP'); ?></code></li>
            <li><strong>Base Path:</strong> <code><?php echo htmlspecialchars('/The-Farmers-Mall'); ?></code></li>
        </ul>
    </div>

    <div class="action">
        <h3>📌 WHAT TO DO NEXT:</h3>
        <ol>
            <li><strong>Copy the redirect URI</strong> from the box above (the long URL)</li>
            <li><strong>Go to:</strong> <a href="https://console.cloud.google.com/apis/credentials" target="_blank" style="color: white; text-decoration: underline;">Google Cloud Console - Credentials</a></li>
            <li><strong>Click</strong> your OAuth 2.0 Client ID</li>
            <li><strong>Find</strong> "Authorized redirect URIs" section</li>
            <li><strong>Click "Add URI"</strong> button</li>
            <li><strong>Paste</strong> the redirect URI from above</li>
            <li><strong>Click "Save"**</strong></li>
            <li><strong>Wait 30 seconds</strong> for changes to propagate</li>
            <li><strong>Clear browser cache</strong> (Ctrl+Shift+Delete)</li>
            <li><strong>Try logging in again</strong></li>
        </ol>
    </div>

    <div class="section warning">
        <h2>⚠️ Common Issues</h2>
        <ul>
            <li><strong>Mismatch Error?</strong> Make sure the URL you add in Google Console is EXACTLY the same as shown above (case-sensitive)</li>
            <li><strong>Still Getting Error?</strong> Wait 30 seconds after saving in Google Console - changes take time to propagate</li>
            <li><strong>Multiple URIs?</strong> You may need to add multiple URIs for different environments:
                <ul>
                    <li><code>http://localhost/The-Farmers-Mall/auth/google-callback.php</code></li>
                    <li><code>http://127.0.0.1/The-Farmers-Mall/auth/google-callback.php</code></li>
                    <li><code>https://yourdomain.com/auth/google-callback.php</code> (for production)</li>
                </ul>
            </li>
        </ul>
    </div>

    <div class="section">
        <h2>🔍 Debug Information</h2>
        <details>
            <summary>Click to expand (for troubleshooting)</summary>
            <pre><?php 
            echo "Current URL: " . htmlspecialchars($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . "\n\n";
            echo "All SERVER variables:\n";
            foreach (['HTTP_HOST', 'SCRIPT_NAME', 'SCRIPT_FILENAME', 'REQUEST_SCHEME', 'HTTPS'] as $key) {
                if (isset($_SERVER[$key])) {
                    echo "$key = " . htmlspecialchars($_SERVER[$key]) . "\n";
                }
            }
            ?></pre>
        </details>
    </div>

    <hr>
    <p style="text-align: center; color: #666;">
        After you've added the redirect URI to Google Console and verified it matches above, delete this file (not needed anymore).
    </p>
</body>
</html>
