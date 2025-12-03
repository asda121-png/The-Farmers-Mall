-- =====================================================
-- STEP 4: Insert all products from products.php
-- =====================================================

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

-- Verify products were inserted
SELECT name, category, price, stock_quantity
FROM products
ORDER BY category, name;

-- Get retailer ID and assign to products
-- First, get a retailer ID:
-- SELECT id, email FROM users WHERE user_type = 'retailer' LIMIT 1;

-- Then update products (replace YOUR-UUID with the actual retailer ID):
-- UPDATE products SET retailer_id = 'YOUR-UUID' WHERE retailer_id IS NULL;
