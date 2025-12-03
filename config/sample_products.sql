-- Sample Products Migration
-- Run this in Supabase SQL Editor to add sample products

-- First, we need a retailer. Let's create a sample retailer if one doesn't exist
-- You'll need to replace the user_id with an actual user ID from your users table

-- Insert sample products (you'll need to update retailer_id with actual value)
INSERT INTO products (retailer_id, name, description, category, price, stock_quantity, unit, image_url, status) VALUES
-- Vegetables
(NULL, 'Fresh Vegetable Bundle', 'A fresh assortment of seasonal vegetables including carrots, spinach, and broccoli, perfect for healthy meals.', 'vegetables', 24.99, 50, 'kg', '../images/products/Fresh Vegetable Box.png', 'active'),
(NULL, 'Organic Lettuce', 'Crisp and fresh organic lettuce, perfect for salads and sandwiches.', 'vegetables', 200.00, 30, 'kg', '../images/products/Organic Lettuce.png', 'active'),
(NULL, 'Baby Carrots', 'Sweet and crunchy baby carrots, perfect for snacking or adding to meals, grown locally for freshness.', 'vegetables', 32.75, 40, 'kg', '../images/products/carrots.png', 'active'),
(NULL, 'Native Tomato', 'Fresh and juicy native tomatoes, perfect for cooking and salads.', 'vegetables', 45.00, 60, 'kg', '../images/products/Native tomato.jpg', 'active'),
(NULL, 'Bell Pepper Mix', 'Colorful mix of red, yellow, and green bell peppers, rich in vitamins.', 'vegetables', 55.00, 35, 'kg', '../images/products/bell pepper mix.png', 'active'),

-- Fruits
(NULL, 'Fresh Strawberries', 'Juicy and sweet strawberries, handpicked at peak ripeness.', 'fruits', 89.99, 25, 'kg', '../images/products/strawberry.png', 'active'),
(NULL, 'Organic Bananas', 'Sweet and creamy organic bananas, rich in potassium and natural energy.', 'fruits', 45.00, 100, 'kg', '../images/products/banana.png', 'active'),
(NULL, 'Fresh Apples', 'Crisp and juicy apples, perfect for snacking or baking.', 'fruits', 120.00, 50, 'kg', '../images/products/apple.png', 'active'),

-- Dairy
(NULL, 'Farm Fresh Milk', 'Pure and fresh milk straight from local farms, rich in nutrients and perfect for daily consumption.', 'dairy', 95.00, 40, 'liter', '../images/products/Fresh Milk.png', 'active'),
(NULL, 'Organic Yogurt', 'Creamy and delicious organic yogurt made from fresh milk.', 'dairy', 85.00, 30, 'container', '../images/products/yogurt.png', 'active'),
(NULL, 'Farm Cheese', 'Artisan cheese made from locally sourced milk.', 'dairy', 150.00, 20, 'kg', '../images/products/cheese.png', 'active'),

-- Bakery
(NULL, 'Whole Wheat Bread', 'Freshly baked whole wheat bread, healthy and delicious.', 'bakery', 55.00, 25, 'loaf', '../images/products/bread.png', 'active'),
(NULL, 'Sourdough Bread', 'Traditional sourdough bread with a perfect crust and tangy flavor.', 'bakery', 75.00, 15, 'loaf', '../images/products/sourdough.png', 'active'),

-- Meat
(NULL, 'Free Range Chicken', 'Fresh chicken from free-range farms, antibiotic-free and naturally raised.', 'meat', 180.00, 30, 'kg', '../images/products/chicken.png', 'active'),
(NULL, 'Grass-Fed Beef', 'Premium grass-fed beef, tender and flavorful.', 'meat', 350.00, 20, 'kg', '../images/products/beef.png', 'active'),

-- Seafood
(NULL, 'Fresh Tilapia', 'Fresh tilapia fish, perfect for grilling or frying.', 'seafood', 120.00, 40, 'kg', '../images/products/tilapia.png', 'active'),
(NULL, 'Wild Caught Shrimp', 'Fresh wild caught shrimp, ready to cook.', 'seafood', 280.00, 15, 'kg', '../images/products/shrimp.png', 'active');

-- Note: After running this, you'll need to update the retailer_id
-- Run this query to see your user IDs:
-- SELECT id, email, user_type FROM users WHERE user_type = 'retailer' LIMIT 1;

-- Then update all products with:
-- UPDATE products SET retailer_id = 'YOUR-RETAILER-UUID-HERE' WHERE retailer_id IS NULL;
