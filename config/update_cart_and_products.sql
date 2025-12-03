-- =====================================================
-- FARMERS MALL DATABASE UPDATE
-- Add product_name to cart table & populate products
-- =====================================================

-- Step 1: Add product_name column to cart table for easier reference
ALTER TABLE cart ADD COLUMN IF NOT EXISTS product_name VARCHAR(255);

-- Step 2: Clear any existing test/duplicate products (optional - remove if you want to keep existing data)
-- DELETE FROM products WHERE retailer_id IS NULL;

-- Step 3: Insert all Farmers Mall products into the products table
-- Note: You'll need to update retailer_id after running this script

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
    status = EXCLUDED.status;

-- Step 4: Update the cart table to include product names
-- This will populate product_name for existing cart items
UPDATE cart 
SET product_name = products.name
FROM products
WHERE cart.product_id = products.id
AND cart.product_name IS NULL;

-- Step 5: Create an index on product_name in cart table for better query performance
CREATE INDEX IF NOT EXISTS idx_cart_product_name ON cart(product_name);

-- Step 6: Get a sample retailer ID to assign to products
-- Run this query separately and use the result in the next step:
-- SELECT id FROM users WHERE user_type = 'retailer' LIMIT 1;

-- Step 7: Update all products with a retailer_id
-- Replace 'YOUR-RETAILER-UUID-HERE' with an actual retailer ID from your users table
-- UPDATE products SET retailer_id = 'YOUR-RETAILER-UUID-HERE' WHERE retailer_id IS NULL;

-- Step 8: Verify the data
-- SELECT COUNT(*) as total_products FROM products;
-- SELECT p.name, p.price, p.category, p.stock_quantity FROM products p ORDER BY p.category, p.name;
-- SELECT c.id, c.product_name, c.quantity, u.email as customer_email 
-- FROM cart c 
-- JOIN users u ON c.customer_id = u.id 
-- ORDER BY c.created_at DESC;

-- =====================================================
-- NOTES:
-- 1. Make sure you have at least one user with user_type='retailer' before running step 7
-- 2. The product_name column in cart is for convenience - the product_id is still the primary reference
-- 3. All products have high stock quantities (50-200 units) to allow testing
-- 4. Prices are in Philippine Pesos (₱)
-- =====================================================
