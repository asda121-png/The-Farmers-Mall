-- =====================================================
-- STEP 3: Remove customer_id column (OPTIONAL)
-- Only run this if you want to completely remove customer_id
-- =====================================================

-- Drop foreign key constraint if it exists
ALTER TABLE cart DROP CONSTRAINT IF EXISTS cart_customer_id_fkey;

-- Drop the customer_id column
ALTER TABLE cart DROP COLUMN IF EXISTS customer_id;

-- Create index for better performance
CREATE INDEX IF NOT EXISTS idx_cart_customer_name ON cart(customer_name);
CREATE INDEX IF NOT EXISTS idx_cart_product_name ON cart(product_name);

-- Verify customer_id is gone
SELECT column_name, data_type 
FROM information_schema.columns 
WHERE table_name = 'cart'
ORDER BY ordinal_position;
