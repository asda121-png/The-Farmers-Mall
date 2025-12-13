<?php
// Quick diagnostic for Google OAuth redirect URI
session_start();
require_once __DIR__ . '/config/google-oauth.php';

try {
    $oauth = new GoogleOAuth();
    $authUrl = $oauth->getAuthorizationUrl();
    
    // Extract just the redirect_uri from the auth URL
    preg_match('/redirect_uri=([^&]+)/', $authUrl, $matches);
    $redirectUri = urldecode($matches[1] ?? 'Not found');
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Google OAuth Redirect URI Diagnostic</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen p-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-2xl p-8 border-l-4 border-blue-600">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">🔐 Google OAuth Diagnostic</h1>
                <p class="text-gray-600 mb-8">Your redirect URI must match exactly what's in Google Cloud Console</p>
                
                <div class="space-y-6">
                    <!-- Redirect URI Box -->
                    <div class="p-6 bg-blue-50 border-2 border-blue-200 rounded-lg">
                        <h2 class="text-lg font-semibold text-blue-900 mb-3">📍 Your Redirect URI:</h2>
                        <div class="bg-white p-4 rounded border border-blue-300 font-mono text-sm break-all">
                            <?php echo htmlspecialchars($redirectUri); ?>
                        </div>
                        <button onclick="copyRedirectURI()" class="mt-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                            📋 Copy This URI
                        </button>
                    </div>
                    
                    <!-- Instructions -->
                    <div class="p-6 bg-yellow-50 border-2 border-yellow-200 rounded-lg">
                        <h2 class="text-lg font-semibold text-yellow-900 mb-3">⚙️ What to do in Google Cloud Console:</h2>
                        <ol class="list-decimal list-inside space-y-2 text-gray-800">
                            <li>Go to <a href="https://console.cloud.google.com/" target="_blank" class="text-blue-600 underline">Google Cloud Console</a></li>
                            <li>Select your OAuth app project</li>
                            <li>Go to <strong>APIs & Services</strong> → <strong>Credentials</strong></li>
                            <li>Click on your OAuth 2.0 Client ID</li>
                            <li>In <strong>Authorized redirect URIs</strong>, add the URI above</li>
                            <li>Click <strong>Save</strong></li>
                        </ol>
                    </div>
                    
                    <!-- Current System Info -->
                    <div class="p-6 bg-gray-50 border-2 border-gray-200 rounded-lg">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">📊 System Information:</h2>
                        <table class="w-full text-sm">
                            <tr class="border-b">
                                <td class="font-semibold text-gray-700 py-2">HTTP Host:</td>
                                <td class="text-gray-600 font-mono"><?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?></td>
                            </tr>
                            <tr class="border-b">
                                <td class="font-semibold text-gray-700 py-2">Protocol:</td>
                                <td class="text-gray-600 font-mono"><?php echo (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'; ?></td>
                            </tr>
                            <tr class="border-b">
                                <td class="font-semibold text-gray-700 py-2">Script Path:</td>
                                <td class="text-gray-600 font-mono"><?php echo htmlspecialchars($_SERVER['SCRIPT_NAME']); ?></td>
                            </tr>
                            <tr>
                                <td class="font-semibold text-gray-700 py-2">Credentials Loaded:</td>
                                <td class="text-green-600 font-mono">✅ Yes (from .env.local or .env)</td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Error Help -->
                    <div class="p-6 bg-red-50 border-2 border-red-200 rounded-lg">
                        <h2 class="text-lg font-semibold text-red-900 mb-3">❌ If you still get "redirect_uri_mismatch":</h2>
                        <ul class="list-disc list-inside space-y-2 text-gray-800">
                            <li>Make sure your <strong>entire redirect URI</strong> matches exactly (including protocol and port)</li>
                            <li>Check if you're using <code>http</code> vs <code>https</code></li>
                            <li>Verify the port number (8080 is common for local development)</li>
                            <li>Double-check spelling and paths</li>
                            <li>If you added the URI, wait a minute for Google to update</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            function copyRedirectURI() {
                const uri = document.querySelector('.font-mono').textContent;
                navigator.clipboard.writeText(uri).then(() => {
                    alert('✅ Redirect URI copied to clipboard!');
                }).catch(() => {
                    alert('Failed to copy. Please copy manually.');
                });
            }
        </script>
    </body>
    </html>
    <?php
} catch (Exception $e) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Google OAuth Error</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-red-50 min-h-screen p-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-8 border-l-4 border-red-600">
            <h1 class="text-3xl font-bold text-red-800 mb-4">❌ OAuth Configuration Error</h1>
            <p class="text-gray-700 mb-4"><?php echo htmlspecialchars($e->getMessage()); ?></p>
            <p class="text-gray-600 text-sm">Make sure your <code>.env.local</code> file has valid Google OAuth credentials.</p>
        </div>
    </body>
    </html>
    <?php
}
?>
