<?php
/**
 * Test Supabase API Connection (Alternative method)
 * This uses REST API instead of direct PostgreSQL connection
 */

require_once __DIR__ . '/supabase-api.php';

echo "=================================\n";
echo "Supabase API Connection Test\n";
echo "=================================\n\n";

try {
    $api = getSupabaseAPI();
    echo "✅ Supabase API client initialized!\n\n";
    
    // Test by trying to query a table (this will fail if table doesn't exist, but connection will work)
    echo "📡 Testing API connection...\n";
    
    // Simple health check - just try to connect
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, getenv('SUPABASE_URL') . '/rest/v1/');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . getenv('SUPABASE_ANON_KEY')
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 || $httpCode == 404) {
        echo "✅ API connection successful!\n";
        echo "🌐 URL: " . getenv('SUPABASE_URL') . "\n";
        
        // Now try to see if tables exist
        try {
            $result = $api->select('users');
            echo "📊 Users table exists with " . count($result) . " records\n";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'does not exist') !== false) {
                echo "⚠️  Tables don't exist yet. Run schema.sql in Supabase SQL Editor.\n";
            }
        }
    } else {
        throw new Exception("Failed to connect to API. HTTP Code: " . $httpCode);
    }
    
    echo "\n=================================\n";
    echo "✅ API connection is working!\n";
    echo "=================================\n\n";
    echo "Note: Use this API method if direct PostgreSQL connection fails.\n";
    echo "See DATABASE_SETUP.md for usage examples.\n";
    
} catch (Exception $e) {
    echo "\n=================================\n";
    echo "❌ API Connection failed!\n";
    echo "=================================\n\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Troubleshooting:\n";
    echo "1. Check your SUPABASE_URL in .env file\n";
    echo "2. Check your SUPABASE_ANON_KEY in .env file\n";
    echo "3. Verify your Supabase project is running\n";
    echo "4. Check your internet connection\n";
}
