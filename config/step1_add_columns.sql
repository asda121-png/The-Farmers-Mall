-- =====================================================
-- STEP 1: Add new columns to cart table
-- Run this FIRST before using the cart
-- =====================================================

-- Add customer_name column
ALTER TABLE cart ADD COLUMN IF NOT EXISTS customer_name VARCHAR(255);

-- Add product_name column
ALTER TABLE cart ADD COLUMN IF NOT EXISTS product_name VARCHAR(255);

-- Verify columns were added
SELECT column_name, data_type 
FROM information_schema.columns 
WHERE table_name = 'cart'
ORDER BY ordinal_position;
