-- ===================================================================
-- SIMPLE MIGRATION: Add product_name to orders table
-- ===================================================================
-- Instructions: Copy this entire script and paste it into your 
-- Supabase SQL Editor, then click "RUN"
-- ===================================================================

-- Step 1: Add product_name column
ALTER TABLE orders ADD COLUMN IF NOT EXISTS product_name VARCHAR(255);

-- Step 2: Update existing orders with product names from order_items
-- (Takes the first product name for orders with multiple items)
UPDATE orders o
SET product_name = (
    SELECT oi.product_name
    FROM order_items oi
    WHERE oi.order_id = o.id
    ORDER BY oi.created_at ASC
    LIMIT 1
)
WHERE o.product_name IS NULL;

-- Step 3: Create index for better performance
CREATE INDEX IF NOT EXISTS idx_orders_product_name ON orders(product_name);

-- Step 4: Add comment for documentation
COMMENT ON COLUMN orders.product_name IS 'Primary product name for quick reference in order tracking';

-- ===================================================================
-- VERIFICATION QUERY - Run this after the migration to check results
-- ===================================================================
-- SELECT id, customer_name, product_name, status, total_amount 
-- FROM orders 
-- ORDER BY created_at DESC 
-- LIMIT 10;
