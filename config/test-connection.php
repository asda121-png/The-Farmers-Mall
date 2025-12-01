<?php
/**
 * Test Supabase Database Connection
 * Run this file to verify your Supabase setup is working correctly
 */

require_once __DIR__ . '/database.php';

echo "=================================\n";
echo "Supabase Connection Test\n";
echo "=================================\n\n";

try {
    // Test connection
    $db = getDB();
    echo "✅ Successfully connected to Supabase!\n\n";
    
    // Test database version
    $stmt = $db->query("SELECT version()");
    $version = $stmt->fetch();
    echo "📊 PostgreSQL Version:\n";
    echo "   " . substr($version['version'], 0, 50) . "...\n\n";
    
    // List all tables
    echo "📋 Tables in database:\n";
    $stmt = $db->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    $tables = $stmt->fetchAll();
    
    if (empty($tables)) {
        echo "   ⚠️  No tables found. Run schema.sql in Supabase SQL Editor!\n\n";
    } else {
        foreach ($tables as $table) {
            echo "   ✓ " . $table['table_name'] . "\n";
        }
        echo "\n";
    }
    
    // Test a simple query on users table if it exists
    if (!empty($tables)) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM users");
            $result = $stmt->fetch();
            echo "👥 Users table: " . $result['count'] . " records\n";
        } catch (Exception $e) {
            echo "⚠️  Could not query users table: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=================================\n";
    echo "✅ All tests passed!\n";
    echo "=================================\n";
    
} catch (Exception $e) {
    echo "\n=================================\n";
    echo "❌ Connection failed!\n";
    echo "=================================\n\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Troubleshooting:\n";
    echo "1. Make sure config/.env file exists (copy from .env.example)\n";
    echo "2. Check your Supabase credentials in .env file\n";
    echo "3. Verify your Supabase project is running\n";
    echo "4. Ensure PHP PostgreSQL extension is installed\n";
}
