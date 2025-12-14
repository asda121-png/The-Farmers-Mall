<?php
require_once __DIR__ . '/config/supabase-api.php';

echo "<h1>Running Messaging System Migration</h1>";

try {
    $supabase = getSupabaseAPI();

    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/RUN_MESSAGING_MIGRATION.sql');

    // Split into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function ($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );

    echo "<h2>Executing " . count($statements) . " SQL statements...</h2><pre>";

    foreach ($statements as $i => $statement) {
        if (empty(trim($statement))) continue;

        echo "\n[" . ($i + 1) . "] Executing: " . substr($statement, 0, 100) . "...\n";

        try {
            // Execute via Supabase API (Note: This might need direct SQL execution)
            // For now, let's output what needs to be run
            echo "✓ Statement prepared\n";
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n";
        }
    }

    echo "\n</pre>";
    echo "<h2>⚠️ IMPORTANT: This script cannot execute CREATE TABLE statements via Supabase API</h2>";
    echo "<p>You MUST run the SQL manually in Supabase Dashboard:</p>";
    echo "<ol>";
    echo "<li>Go to <strong>Supabase Dashboard → SQL Editor</strong></li>";
    echo "<li>Copy all content from <code>RUN_MESSAGING_MIGRATION.sql</code></li>";
    echo "<li>Paste it in the SQL Editor</li>";
    echo "<li>Click <strong>Run</strong> button</li>";
    echo "<li>Refresh this messaging page</li>";
    echo "</ol>";

    echo "<h3>SQL to run:</h3>";
    echo "<textarea style='width:100%; height:300px; font-family:monospace;'>" . htmlspecialchars($sql) . "</textarea>";
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
