<?php
/**
 * Test Registration Functionality
 * Simulates a user registration to verify Supabase integration
 */

require_once __DIR__ . '/../config/supabase-api.php';

echo "===========================================\n";
echo "   Registration Test\n";
echo "===========================================\n\n";

try {
    $api = getSupabaseAPI();
    
    // Test data
    $testEmail = 'testuser_' . time() . '@farmersmail.test';
    $testData = [
        'email' => $testEmail,
        'password_hash' => password_hash('Test123!', PASSWORD_DEFAULT),
        'full_name' => 'Test User Registration',
        'phone' => '09123456789',
        'user_type' => 'customer',
        'status' => 'active'
    ];
    
    echo "Testing registration with:\n";
    echo "  Email: $testEmail\n";
    echo "  Name: Test User Registration\n";
    echo "  Phone: 09123456789\n";
    echo "  Type: customer\n\n";
    
    // Insert user
    echo "Inserting user into database...\n";
    $result = $api->insert('users', $testData);
    
    if (!empty($result)) {
        echo "✅ User registered successfully!\n";
        echo "  User ID: " . ($result[0]['id'] ?? 'N/A') . "\n";
        echo "  Email: " . ($result[0]['email'] ?? 'N/A') . "\n";
        echo "  Name: " . ($result[0]['full_name'] ?? 'N/A') . "\n\n";
        
        // Verify user exists
        echo "Verifying user in database...\n";
        $users = $api->select('users', ['email' => $testEmail]);
        
        if (!empty($users)) {
            echo "✅ User found in database\n";
            echo "  User count: " . count($users) . "\n\n";
            
            // Cleanup - delete test user
            echo "Cleaning up test data...\n";
            $api->delete('users', ['email' => $testEmail]);
            echo "✅ Test user removed\n\n";
            
            echo "===========================================\n";
            echo "✅ REGISTRATION TEST PASSED!\n";
            echo "===========================================\n";
            echo "\nYour registration form should work correctly.\n";
            echo "Try registering at: http://localhost:8000/auth/register.php\n";
        } else {
            echo "❌ User not found after insertion\n";
        }
    } else {
        echo "❌ Registration failed - no data returned\n";
    }
    
} catch (Exception $e) {
    echo "❌ Test failed with error:\n";
    echo "  " . $e->getMessage() . "\n";
}

echo "\n";
