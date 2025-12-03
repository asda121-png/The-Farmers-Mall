-- =====================================================
-- FARMERS MALL DATABASE UPDATE - PART 2
-- Replace customer_id with customer_name in cart table
-- =====================================================

-- Step 1: Add customer_name column to cart table (temporary)
ALTER TABLE cart ADD COLUMN IF NOT EXISTS customer_name VARCHAR(255);

-- Step 2: Populate customer_name for existing cart items
-- This joins the cart with users table to get first_name and last_name
UPDATE cart 
SET customer_name = CONCAT(users.first_name, ' ', users.last_name)
FROM users
WHERE cart.customer_id = users.id
AND cart.customer_name IS NULL;

-- Step 3: Drop the foreign key constraint on customer_id (if exists)
-- Note: You may need to find the actual constraint name in your database
-- ALTER TABLE cart DROP CONSTRAINT IF EXISTS cart_customer_id_fkey;

-- Step 4: Drop the customer_id column
-- WARNING: This will remove the customer_id column permanently
-- Make sure customer_name is populated before running this!
ALTER TABLE cart DROP COLUMN IF EXISTS customer_id;

-- Step 5: Create an index on customer_name for better query performance
CREATE INDEX IF NOT EXISTS idx_cart_customer_name ON cart(customer_name);

-- Step 4: Clear existing products (optional - only if you want to start fresh)
-- Comment this out if you want to keep existing products
-- DELETE FROM products;

-- Step 5: Insert ALL products from products.php
-- These are the exact products shown on the products page
INSERT INTO products (name, description, category, price, stock_quantity, unit, image_url, status) 
VALUES
-- VEGETABLES
('Fresh Vegetable Bundle', 'A fresh assortment of seasonal vegetables including carrots, spinach, and broccoli, perfect for healthy meals.', 'vegetables', 24.99, 100, 'kg', '../images/products/Fresh Vegetable Box.png', 'active'),
('Organic Lettuce', 'Crisp and fresh organic lettuce, perfect for salads and sandwiches.', 'vegetables', 200.00, 80, 'kg', '../images/products/Organic Lettuce.png', 'active'),
('Baby Carrots', 'Sweet and crunchy baby carrots, perfect for snacking or adding to meals, grown locally for freshness.', 'vegetables', 32.75, 150, 'kg', '../images/products/carrots.png', 'active'),
('Fresh Vegetable Box', 'A curated box of fresh vegetables, including a variety of greens and roots, sourced directly from local farms for maximum freshness.', 'vegetables', 38.00, 60, 'bundle', '../images/products/Fresh Vegetable Box.png', 'active'),
('Tomato', 'Juicy and ripe tomatoes, perfect for salads, sauces, or cooking, grown in local greenhouses for optimal flavor.', 'vegetables', 28.00, 120, 'kg', '../images/products/Native tomato.jpg', 'active'),

-- FRUITS
('Fresh Strawberries', 'Juicy and sweet strawberries, handpicked at peak ripeness.', 'fruits', 89.99, 50, 'kg', '../images/products/strawberry.png', 'active'),
('Ripe Bananas', 'Perfectly ripe bananas, sweet and ready to eat, sourced from local plantations for optimal taste and nutrition.', 'fruits', 28.99, 200, 'kg', '../images/products/banana.png', 'active'),
('Banana', 'Fresh, ripe bananas, sweet and nutritious, ideal for a quick energy boost or baking, sourced from local plantations.', 'fruits', 150.00, 180, 'piece', '../images/products/banana.png', 'active'),

-- DAIRY
('Farm Fresh Milk', 'Pure and fresh milk straight from local farms, rich in nutrients and perfect for daily consumption.', 'dairy', 95.00, 75, 'liter', '../images/products/Fresh Milk.png', 'active'),
('Aged Cheddar', 'Rich and sharp aged cheddar cheese, matured to perfection for a bold flavor, ideal for cheese boards and cooking.', 'dairy', 120.00, 40, '250g', '../images/products/cheese.png', 'active'),

-- BAKERY
('Artisan Bread', 'Freshly baked artisan bread with a crispy crust and soft interior, made with traditional methods and high-quality ingredients.', 'bakery', 28.00, 90, 'loaf', '../images/products/bread.png', 'active')

ON CONFLICT (name) DO UPDATE SET
    description = EXCLUDED.description,
    category = EXCLUDED.category,
    price = EXCLUDED.price,
    stock_quantity = EXCLUDED.stock_quantity,
    unit = EXCLUDED.unit,
    image_url = EXCLUDED.image_url,
    status = EXCLUDED.status,
    updated_at = NOW();

-- Step 6: Verify the changes
-- Run these queries to check the results:

-- Check cart structure (should have customer_name, NOT customer_id)
-- SELECT column_name, data_type 
-- FROM information_schema.columns 
-- WHERE table_name = 'cart';

-- Check cart with customer names
-- SELECT 
--     c.id, 
--     c.customer_name,      -- Should show "FirstName LastName"
--     c.product_name,       -- Should show product name
--     c.quantity,
--     c.created_at
-- FROM cart c 
-- ORDER BY c.created_at DESC;

-- Check all products
-- SELECT 
--     p.id,
--     p.name, 
--     p.category, 
--     p.price, 
--     p.stock_quantity,
--     p.unit
-- FROM products p 
-- ORDER BY p.category, p.name;

-- Count products by category
-- SELECT 
--     category, 
--     COUNT(*) as product_count 
-- FROM products 
-- GROUP BY category 
-- ORDER BY category;

-- Step 7: Get a retailer ID to assign to products
-- SELECT id, email FROM users WHERE user_type = 'retailer' LIMIT 1;

-- Step 8: Update all products with a retailer_id
-- Replace 'YOUR-RETAILER-UUID-HERE' with an actual retailer ID
-- UPDATE products SET retailer_id = 'YOUR-RETAILER-UUID-HERE' WHERE retailer_id IS NULL;

-- =====================================================
-- SUMMARY OF CHANGES:
-- 1. Added customer_name column to cart table
-- 2. Populated customer_name with "FirstName LastName" from users table
-- 3. All 11 products from products.php are now in the products table
-- 4. Product details match exactly what's shown on the website
-- 
-- NEXT STEPS:
-- 1. Run this SQL in Supabase SQL Editor
-- 2. Get a retailer ID and update products (step 7-8)
-- 3. Update the cart API to include customer_name when inserting
-- =====================================================
