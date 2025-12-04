<?php
/**
 * Alternative Supabase Connection using REST API
 * Use this if direct PostgreSQL connection doesn't work due to network issues
 */

// Guard against multiple includes
if (defined('SUPABASE_API_LOADED')) {
    return;
}
define('SUPABASE_API_LOADED', true);

require_once __DIR__ . '/env.php';

class SupabaseAPI {
    private static $instance = null;
    private $url;
    private $apiKey;
    
    private function __construct() {
        // Check if cURL extension is enabled
        if (!function_exists('curl_init')) {
            die('
                <div style="font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; border: 2px solid #dc3545; border-radius: 10px; background-color: #f8d7da;">
                    <h2 style="color: #721c24;">❌ cURL Extension Not Enabled</h2>
                    <p style="color: #721c24; font-size: 16px;">The PHP cURL extension is required but not enabled on your system.</p>
                    
                    <h3 style="color: #721c24;">To fix this in XAMPP:</h3>
                    <ol style="color: #721c24; font-size: 14px; line-height: 1.8;">
                        <li>Open <strong>php.ini</strong> file:
                            <ul>
                                <li>Click on XAMPP Control Panel</li>
                                <li>Click "Config" button next to Apache</li>
                                <li>Select "PHP (php.ini)"</li>
                            </ul>
                        </li>
                        <li>Find this line: <code>;extension=curl</code></li>
                        <li>Remove the semicolon to uncomment it: <code>extension=curl</code></li>
                        <li>Save the file</li>
                        <li>Restart Apache in XAMPP Control Panel</li>
                        <li>Refresh this page</li>
                    </ol>
                    
                    <h3 style="color: #721c24;">Alternative location (older XAMPP versions):</h3>
                    <p style="color: #721c24; font-size: 14px;">Look for: <code>;extension=php_curl.dll</code> and change to <code>extension=php_curl.dll</code></p>
                    
                    <p style="color: #721c24; font-size: 14px; margin-top: 20px;"><strong>File location:</strong> <code>C:\xampp\php\php.ini</code></p>
                </div>
            ');
        }
        
        $this->url = getenv('SUPABASE_URL');
        $this->apiKey = getenv('SUPABASE_ANON_KEY');
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Execute a query using Supabase REST API
     */
    public function query($table, $action = 'select', $data = [], $filters = []) {
        $url = $this->url . '/rest/v1/' . $table;
        
        $headers = [
            'apikey: ' . $this->apiKey,
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];
        
        $ch = curl_init();
        
        switch ($action) {
            case 'select':
                $url .= $this->buildFilters($filters);
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
                
            case 'insert':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
                
            case 'update':
                $url .= $this->buildFilters($filters);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
                
            case 'delete':
                $url .= $this->buildFilters($filters);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            throw new Exception("API Error: " . $error);
        }
        
        if ($httpCode >= 400) {
            throw new Exception("API Error: " . $response);
        }
        
        return json_decode($response, true);
    }
    
    private function buildFilters($filters) {
        if (empty($filters)) {
            return '';
        }
        
        $params = [];
        foreach ($filters as $key => $value) {
            if (is_array($value)) {
                // Handle operators like ['eq', 'value'] or ['gt', 5]
                $operator = $value[0];
                $val = $value[1];
                $params[] = $key . '=' . $operator . '.' . urlencode($val);
            } else {
                // Default to equals
                $params[] = $key . '=eq.' . urlencode($value);
            }
        }
        
        return '?' . implode('&', $params);
    }
    
    // Helper methods
    public function select($table, $filters = []) {
        return $this->query($table, 'select', [], $filters);
    }
    
    public function insert($table, $data) {
        return $this->query($table, 'insert', $data);
    }
    
    public function update($table, $data, $filters) {
        return $this->query($table, 'update', $data, $filters);
    }
    
    public function delete($table, $filters) {
        return $this->query($table, 'delete', [], $filters);
    }
}

// Helper function
function getSupabaseAPI() {
    return SupabaseAPI::getInstance();
}
