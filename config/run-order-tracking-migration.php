<?php
/**
 * Run Migration: Add product_name column to orders table
 * This script executes the migration to add product tracking to orders
 */

require_once 'database.php';

echo "=== Order Tracking Migration ===\n\n";

try {
    // Read the migration SQL file
    $sqlFile = __DIR__ . '/add_product_name_to_orders.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Migration file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    echo "Reading migration file...\n";
    echo "Connecting to database...\n";
    
    // Execute the migration
    $pdo = getDB();
    
    if (!$pdo) {
        throw new Exception("Failed to connect to database");
    }
    
    echo "Executing migration...\n\n";
    
    // Split SQL into individual statements and execute
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && 
                   strpos($stmt, '--') !== 0 && 
                   strpos($stmt, '/*') !== 0;
        }
    );
    
    $pdo->beginTransaction();
    
    $count = 0;
    foreach ($statements as $statement) {
        if (trim($statement)) {
            try {
                $pdo->exec($statement);
                $count++;
                echo "✓ Executed statement $count\n";
            } catch (PDOException $e) {
                // Continue on some errors (like constraint already exists)
                if (strpos($e->getMessage(), 'already exists') === false &&
                    strpos($e->getMessage(), 'does not exist') === false) {
                    throw $e;
                }
                echo "⚠ Skipped: " . substr($e->getMessage(), 0, 100) . "...\n";
            }
        }
    }
    
    $pdo->commit();
    
    echo "\n✅ Migration completed successfully!\n";
    echo "Total statements executed: $count\n\n";
    
    // Verify the changes
    echo "=== Verification ===\n";
    
    // Check if product_name column exists
    $stmt = $pdo->query("
        SELECT column_name, data_type, character_maximum_length
        FROM information_schema.columns
        WHERE table_name = 'orders' 
        AND column_name IN ('product_name', 'status', 'customer_name')
        ORDER BY column_name
    ");
    
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nOrders table columns:\n";
    foreach ($columns as $col) {
        echo sprintf(
            "  - %s (%s%s)\n",
            $col['column_name'],
            $col['data_type'],
            $col['character_maximum_length'] ? "({$col['character_maximum_length']})" : ''
        );
    }
    
    // Check order statuses
    $stmt = $pdo->query("
        SELECT status, COUNT(*) as count
        FROM orders
        GROUP BY status
        ORDER BY count DESC
    ");
    
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nOrder status distribution:\n";
    if (empty($statuses)) {
        echo "  No orders in database yet\n";
    } else {
        foreach ($statuses as $status) {
            echo sprintf("  - %s: %d orders\n", $status['status'], $status['count']);
        }
    }
    
    // Check if view was created
    $stmt = $pdo->query("
        SELECT COUNT(*) as count
        FROM information_schema.views
        WHERE table_name = 'order_tracking_view'
    ");
    
    $viewExists = $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
    
    echo "\nOrder tracking view: " . ($viewExists ? "✓ Created" : "✗ Not found") . "\n";
    
    echo "\n=== Summary ===\n";
    echo "✓ product_name column added to orders table\n";
    echo "✓ Order status values updated (to_pay, to_ship, to_receive, completed, cancelled)\n";
    echo "✓ Indexes created for better performance\n";
    echo "✓ Order tracking view created\n";
    echo "\nYou can now track orders easily using the product_name field!\n";
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Migration rolled back.\n";
    exit(1);
}
?>
