<?php
/**
 * Complete Supabase Database Test
 * This will test all database operations to verify everything is working
 */

require_once __DIR__ . '/supabase-api.php';

echo "===========================================\n";
echo "   Farmers Mall - Supabase Test Suite\n";
echo "===========================================\n\n";

$api = getSupabaseAPI();
$allTestsPassed = true;

// Test 1: Connection
echo "Test 1: Connection Test\n";
echo "------------------------\n";
try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, getenv('SUPABASE_URL') . '/rest/v1/');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: ' . getenv('SUPABASE_ANON_KEY')]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200 || $httpCode == 404) {
        echo "✅ PASSED - Connected to Supabase successfully\n";
        echo "   URL: " . getenv('SUPABASE_URL') . "\n\n";
    } else {
        throw new Exception("HTTP Code: $httpCode");
    }
} catch (Exception $e) {
    echo "❌ FAILED - " . $e->getMessage() . "\n\n";
    $allTestsPassed = false;
}

// Test 2: Read Users Table
echo "Test 2: Read Users Table\n";
echo "------------------------\n";
try {
    $users = $api->select('users');
    echo "✅ PASSED - Users table is accessible\n";
    echo "   Found " . count($users) . " user(s) in database\n\n";
} catch (Exception $e) {
    echo "❌ FAILED - " . $e->getMessage() . "\n\n";
    $allTestsPassed = false;
}

// Test 3: Insert Test User
echo "Test 3: Insert Test User\n";
echo "------------------------\n";
$testEmail = 'test_' . time() . '@farmersmail.test';
try {
    $newUser = $api->insert('users', [
        'email' => $testEmail,
        'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
        'full_name' => 'Test User',
        'phone' => '1234567890',
        'user_type' => 'customer',
        'status' => 'active'
    ]);
    
    if (!empty($newUser)) {
        echo "✅ PASSED - Successfully inserted test user\n";
        echo "   Email: $testEmail\n";
        echo "   ID: " . ($newUser[0]['id'] ?? 'N/A') . "\n\n";
        $testUserId = $newUser[0]['id'] ?? null;
    } else {
        throw new Exception("Insert returned empty result");
    }
} catch (Exception $e) {
    echo "❌ FAILED - " . $e->getMessage() . "\n\n";
    $allTestsPassed = false;
    $testUserId = null;
}

// Test 4: Read Specific User
if ($testUserId) {
    echo "Test 4: Read Specific User\n";
    echo "------------------------\n";
    try {
        $user = $api->select('users', ['email' => $testEmail]);
        
        if (!empty($user) && $user[0]['email'] === $testEmail) {
            echo "✅ PASSED - Successfully retrieved user by email\n";
            echo "   Name: " . $user[0]['full_name'] . "\n";
            echo "   Type: " . $user[0]['user_type'] . "\n\n";
        } else {
            throw new Exception("User not found or data mismatch");
        }
    } catch (Exception $e) {
        echo "❌ FAILED - " . $e->getMessage() . "\n\n";
        $allTestsPassed = false;
    }
}

// Test 5: Update User
if ($testUserId) {
    echo "Test 5: Update User\n";
    echo "------------------------\n";
    try {
        $updated = $api->update('users', 
            ['full_name' => 'Updated Test User', 'phone' => '9876543210'],
            ['email' => $testEmail]
        );
        
        // Verify update
        $user = $api->select('users', ['email' => $testEmail]);
        if ($user[0]['full_name'] === 'Updated Test User') {
            echo "✅ PASSED - Successfully updated user\n";
            echo "   New name: " . $user[0]['full_name'] . "\n";
            echo "   New phone: " . $user[0]['phone'] . "\n\n";
        } else {
            throw new Exception("Update verification failed");
        }
    } catch (Exception $e) {
        echo "❌ FAILED - " . $e->getMessage() . "\n\n";
        $allTestsPassed = false;
    }
}

// Test 6: Check Other Tables
echo "Test 6: Check Other Tables\n";
echo "------------------------\n";
$tables = ['products', 'orders', 'retailers', 'reviews', 'messages', 'notifications', 'cart'];
$tablesExist = 0;

foreach ($tables as $table) {
    try {
        $result = $api->select($table);
        echo "✅ Table '$table' exists (" . count($result) . " records)\n";
        $tablesExist++;
    } catch (Exception $e) {
        echo "⚠️  Table '$table' - " . (strpos($e->getMessage(), 'does not exist') !== false ? 'not found' : 'error') . "\n";
    }
}
echo "\n";

// Test 7: Delete Test User (Cleanup)
if ($testUserId) {
    echo "Test 7: Delete Test User (Cleanup)\n";
    echo "------------------------\n";
    try {
        $api->delete('users', ['email' => $testEmail]);
        
        // Verify deletion
        $user = $api->select('users', ['email' => $testEmail]);
        if (empty($user)) {
            echo "✅ PASSED - Successfully deleted test user\n\n";
        } else {
            throw new Exception("User still exists after deletion");
        }
    } catch (Exception $e) {
        echo "❌ FAILED - " . $e->getMessage() . "\n\n";
        $allTestsPassed = false;
    }
}

// Summary
echo "===========================================\n";
echo "   Test Summary\n";
echo "===========================================\n\n";

if ($allTestsPassed) {
    echo "🎉 ALL TESTS PASSED! 🎉\n\n";
    echo "Your Supabase database is working perfectly!\n";
    echo "Tables found: $tablesExist/" . count($tables) . "\n\n";
    echo "You can now:\n";
    echo "1. Start building your application\n";
    echo "2. Share the .env file with your team\n";
    echo "3. Team members just need to git pull and add .env\n";
} else {
    echo "⚠️  SOME TESTS FAILED\n\n";
    echo "Check the errors above and:\n";
    echo "1. Verify your .env credentials\n";
    echo "2. Make sure schema.sql was run in Supabase\n";
    echo "3. Check your internet connection\n";
}

echo "\n===========================================\n";
