-- Migration: Add product_name column to orders table for easy tracking
-- Date: 2025-12-08
-- Purpose: Add product name directly to orders table for quick reference and tracking
-- Note: For orders with multiple items, this will show the first/primary product

-- Step 1: Add product_name column to orders table
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS product_name VARCHAR(255);

-- Step 2: Update order status values to match tracking workflow
-- Current status values: pending, confirmed, processing, shipped, delivered, cancelled
-- New status values: to_pay, to_ship, to_receive, completed, cancelled

-- First, drop the existing check constraint
ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check;

-- Add new check constraint with updated status values
ALTER TABLE orders 
ADD CONSTRAINT orders_status_check 
CHECK (status IN ('to_pay', 'to_ship', 'to_receive', 'completed', 'cancelled', 
                  'pending', 'confirmed', 'processing', 'shipped', 'delivered'));

-- Step 3: Migrate existing status values to new ones
UPDATE orders SET status = 'to_pay' WHERE status = 'pending';
UPDATE orders SET status = 'to_ship' WHERE status IN ('confirmed', 'processing');
UPDATE orders SET status = 'to_receive' WHERE status = 'shipped';
UPDATE orders SET status = 'completed' WHERE status = 'delivered';
-- 'cancelled' remains the same

-- Step 4: Populate product_name from order_items
-- For orders with multiple items, we'll take the first product name
-- You can modify this logic based on your business needs
UPDATE orders o
SET product_name = (
    SELECT oi.product_name
    FROM order_items oi
    WHERE oi.order_id = o.id
    ORDER BY oi.created_at ASC
    LIMIT 1
)
WHERE o.product_name IS NULL;

-- Alternative: Concatenate all product names for orders with multiple items
-- Uncomment below if you want to show all products in the order
/*
UPDATE orders o
SET product_name = (
    SELECT STRING_AGG(oi.product_name, ', ' ORDER BY oi.created_at)
    FROM order_items oi
    WHERE oi.order_id = o.id
    GROUP BY oi.order_id
)
WHERE o.product_name IS NULL;
*/

-- Step 5: Create index for better query performance on status
CREATE INDEX IF NOT EXISTS idx_orders_status_tracking ON orders(status);
CREATE INDEX IF NOT EXISTS idx_orders_product_name ON orders(product_name);

-- Step 6: Add comment for documentation
COMMENT ON COLUMN orders.product_name IS 'Primary product name for quick reference in order tracking';

-- Step 7: Create a view for easy order tracking
CREATE OR REPLACE VIEW order_tracking_view AS
SELECT 
    o.id,
    o.customer_name,
    o.customer_email,
    o.product_name,
    o.total_amount,
    o.status,
    o.payment_status,
    o.delivery_address,
    o.created_at,
    o.updated_at,
    CASE 
        WHEN o.status = 'to_pay' THEN 'Waiting for payment'
        WHEN o.status = 'to_ship' THEN 'Preparing to ship'
        WHEN o.status = 'to_receive' THEN 'Out for delivery'
        WHEN o.status = 'completed' THEN 'Order completed'
        WHEN o.status = 'cancelled' THEN 'Order cancelled'
        ELSE o.status
    END as status_description,
    (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as total_items
FROM orders o
ORDER BY o.created_at DESC;

-- Grant access to the view
GRANT SELECT ON order_tracking_view TO authenticated;

COMMENT ON VIEW order_tracking_view IS 'Simplified view for tracking orders with status descriptions';
