-- Migration: Add additional tracking columns to orders and order_items tables
-- Date: 2025-12-04
-- Purpose: Enhance order tracking with customer name and product name for better reporting

-- Add customer_name to orders table (for quick reference without joining users table)
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS customer_name VARCHAR(255),
ADD COLUMN IF NOT EXISTS customer_email VARCHAR(255);

-- Add product_name to order_items table (preserve product name even if product is deleted)
ALTER TABLE order_items 
ADD COLUMN IF NOT EXISTS product_name VARCHAR(255),
ADD COLUMN IF NOT EXISTS product_image_url TEXT;

-- Update existing records with customer names
UPDATE orders o
SET customer_name = CONCAT(u.first_name, ' ', u.last_name),
    customer_email = u.email
FROM users u
WHERE o.customer_id = u.id
AND o.customer_name IS NULL;

-- Update existing order_items with product names
UPDATE order_items oi
SET product_name = p.name,
    product_image_url = p.image_url
FROM products p
WHERE oi.product_id = p.id
AND oi.product_name IS NULL;

-- Create index for better query performance
CREATE INDEX IF NOT EXISTS idx_orders_customer_id ON orders(customer_id);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
CREATE INDEX IF NOT EXISTS idx_order_items_order_id ON order_items(order_id);
CREATE INDEX IF NOT EXISTS idx_order_items_product_id ON order_items(product_id);

-- Add comments for documentation
COMMENT ON COLUMN orders.customer_name IS 'Cached customer name for quick reference';
COMMENT ON COLUMN orders.customer_email IS 'Cached customer email for quick reference';
COMMENT ON COLUMN order_items.product_name IS 'Product name at time of order (preserved even if product deleted)';
COMMENT ON COLUMN order_items.product_image_url IS 'Product image at time of order (preserved even if product deleted)';
