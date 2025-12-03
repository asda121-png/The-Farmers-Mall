-- =====================================================
-- STEP 2: Populate customer_name for existing cart items
-- Run this AFTER step 1
-- =====================================================

-- First, update users table to ensure they have proper names
-- Extract first name from email (part before @)
UPDATE users 
SET first_name = SPLIT_PART(email, '@', 1),
    last_name = 'User'
WHERE (first_name IS NULL OR first_name = '' OR last_name IS NULL OR last_name = '')
AND user_type = 'customer';

-- Now populate customer_name from users table with full names
UPDATE cart 
SET customer_name = TRIM(CONCAT(users.first_name, ' ', users.last_name))
FROM users
WHERE cart.customer_id = users.id
AND cart.customer_name IS NULL;

-- Populate product_name from products table
UPDATE cart 
SET product_name = products.name
FROM products
WHERE cart.product_id = products.id
AND cart.product_name IS NULL;

-- Check the results
SELECT id, customer_name, product_name, quantity
FROM cart
ORDER BY created_at DESC
LIMIT 5;
