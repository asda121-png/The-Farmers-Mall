<?php
/**
 * Google OAuth Configuration
 * 
 * This file handles Google OAuth 2.0 authentication flow
 * Credentials are loaded from .env file for security
 */

require_once __DIR__ . '/env.php';

class GoogleOAuth {
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $tokenEndpoint = 'https://oauth2.googleapis.com/token';
    private $userInfoEndpoint = 'https://www.googleapis.com/oauth2/v2/userinfo';
    
    public function __construct() {
        // Load from environment variables
        $this->clientId = getenv('GOOGLE_CLIENT_ID');
        $this->clientSecret = getenv('GOOGLE_CLIENT_SECRET');
        
        // Redirect URI - callback URL
        $this->redirectUri = $this->getBaseUrl() . '/auth/google-callback.php';
        
        // Validate credentials
        if (empty($this->clientId) || empty($this->clientSecret)) {
            throw new Exception('Google OAuth credentials not configured in .env file');
        }
    }
    
    /**
     * Get the base URL of the application
     */
    private function getBaseUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        
        // ALWAYS return /The-Farmers-Mall as the base path
        // This ensures consistency regardless of which script is calling this function
        $basePath = '/The-Farmers-Mall';
        
        return $protocol . $host . $basePath;
    }
    
    /**
     * Generate the authorization URL for Google login
     */
    public function getAuthorizationUrl() {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];
        
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }
    
    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken($code) {
        $params = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code',
            'code' => $code
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->tokenEndpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false, // Disabled for development - enable in production
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Log debugging information
        error_log("Google OAuth Token Exchange Debug:");
        error_log("  HTTP Code: $httpCode");
        error_log("  Redirect URI: " . $this->redirectUri);
        error_log("  Response: " . substr($response, 0, 200)); // First 200 chars
        
        if (!empty($curlError)) {
            throw new Exception('cURL Error: ' . $curlError);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error_description'] ?? 'HTTP ' . $httpCode;
            throw new Exception('Google OAuth failed: ' . $errorMsg);
        }
        
        $data = json_decode($response, true);
        if (!isset($data['access_token'])) {
            throw new Exception('Invalid token response from Google: ' . json_encode($data));
        }
        
        return $data['access_token'];
    }
    
    /**
     * Get user information from Google using access token
     */
    public function getUserInfo($accessToken) {
        $url = $this->userInfoEndpoint . '?access_token=' . urlencode($accessToken);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false, // Disabled for development
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Log debugging information
        error_log("Google OAuth User Info Debug:");
        error_log("  URL: " . $url);
        error_log("  HTTP Code: $httpCode");
        error_log("  cURL Error: " . ($curlError ?: "None"));
        error_log("  Response: " . substr($response, 0, 300));
        
        if (!empty($curlError)) {
            throw new Exception('cURL Error getting user info: ' . $curlError);
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMsg = $errorData['error'] ?? ('HTTP ' . $httpCode);
            throw new Exception('Failed to get user info from Google: ' . $errorMsg);
        }
        
        $userInfo = json_decode($response, true);
        if (!isset($userInfo['email'])) {
            throw new Exception('No email in Google user info: ' . json_encode($userInfo));
        }
        
        return $userInfo;
    }
    
    /**
     * Get the redirect URI
     */
    public function getRedirectUri() {
        return $this->redirectUri;
    }
}
?>
