-- Populate Shops and Products for The Farmers Mall
-- This script creates retailers and their products

-- First, delete existing data if you want to start fresh (optional - comment out if not needed)
-- DELETE FROM products WHERE retailer_id IN (SELECT id FROM retailers WHERE shop_name IN ('Mesa Farm', 'Taco Bell', 'Jay''s Artisan', 'Ocean Fresh'));
-- DELETE FROM retailers WHERE shop_name IN ('Mesa Farm', 'Taco Bell', 'Jay''s Artisan', 'Ocean Fresh');

-- Insert retailers with their shop information (only if they don't exist)
INSERT INTO retailers (id, user_id, shop_name, shop_description, business_address, verification_status, rating, total_sales)
SELECT gen_random_uuid(), NULL, 'Mesa Farm', 'Organic vegetables & herbs grown with care and sustainability in mind. Fresh from our farm to your table.', '123 Farm Road, Mesa Valley', 'verified', 4.80, 15000.00
WHERE NOT EXISTS (SELECT 1 FROM retailers WHERE shop_name = 'Mesa Farm');

INSERT INTO retailers (id, user_id, shop_name, shop_description, business_address, verification_status, rating, total_sales)
SELECT gen_random_uuid(), NULL, 'Taco Bell', 'Fresh Mexican ingredients and authentic spices. Quality produce for authentic Mexican cuisine.', '456 Market Street, Downtown', 'verified', 4.50, 12000.00
WHERE NOT EXISTS (SELECT 1 FROM retailers WHERE shop_name = 'Taco Bell');

INSERT INTO retailers (id, user_id, shop_name, shop_description, business_address, verification_status, rating, total_sales)
SELECT gen_random_uuid(), NULL, 'Jay''s Artisan', 'Premium coffees and freshly baked bread. Handcrafted with passion and expertise.', '789 Baker Avenue, City Center', 'verified', 4.90, 20000.00
WHERE NOT EXISTS (SELECT 1 FROM retailers WHERE shop_name = 'Jay''s Artisan');

INSERT INTO retailers (id, user_id, shop_name, shop_description, business_address, verification_status, rating, total_sales)
SELECT gen_random_uuid(), NULL, 'Ocean Fresh', 'Daily catch seafood delivered fresh from the ocean. Sustainable and high-quality seafood selection.', '321 Harbor Drive, Coastal Area', 'verified', 4.70, 18000.00
WHERE NOT EXISTS (SELECT 1 FROM retailers WHERE shop_name = 'Ocean Fresh');

-- Get retailer IDs for inserting products
DO $$
DECLARE
  mesa_farm_id UUID;
  taco_bell_id UUID;
  jays_artisan_id UUID;
  ocean_fresh_id UUID;
BEGIN
  -- Get retailer IDs
  SELECT id INTO mesa_farm_id FROM retailers WHERE shop_name = 'Mesa Farm';
  SELECT id INTO taco_bell_id FROM retailers WHERE shop_name = 'Taco Bell';
  SELECT id INTO jays_artisan_id FROM retailers WHERE shop_name = 'Jay''s Artisan';
  SELECT id INTO ocean_fresh_id FROM retailers WHERE shop_name = 'Ocean Fresh';

  -- Insert products for Mesa Farm (Organic vegetables & herbs)
  INSERT INTO products (retailer_id, name, description, category, price, stock_quantity, unit, image_url, status)
  VALUES
    (mesa_farm_id, 'Organic Tomatoes', 'Fresh, juicy organic tomatoes grown without pesticides', 'Vegetables', 120.00, 50, 'kg', 'https://images.unsplash.com/photo-1546470427-227e1e9c0b70?w=500', 'active'),
    (mesa_farm_id, 'Fresh Lettuce', 'Crisp organic lettuce perfect for salads', 'Vegetables', 80.00, 40, 'piece', 'https://images.unsplash.com/photo-1622206151226-18ca2c9ab4a1?w=500', 'active'),
    (mesa_farm_id, 'Organic Carrots', 'Sweet and crunchy organic carrots', 'Vegetables', 90.00, 60, 'kg', 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=500', 'active'),
    (mesa_farm_id, 'Fresh Basil', 'Aromatic fresh basil herbs', 'Herbs', 50.00, 30, 'bunch', 'https://images.unsplash.com/photo-1618375569909-3c8616cf7733?w=500', 'active'),
    (mesa_farm_id, 'Organic Spinach', 'Nutrient-rich organic spinach leaves', 'Vegetables', 70.00, 45, 'bunch', 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=500', 'active'),
    (mesa_farm_id, 'Bell Peppers', 'Colorful organic bell peppers', 'Vegetables', 150.00, 35, 'kg', 'https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?w=500', 'active'),
    (mesa_farm_id, 'Fresh Rosemary', 'Fragrant rosemary herbs', 'Herbs', 45.00, 25, 'bunch', 'https://images.unsplash.com/photo-1599024869862-9588091ae161?w=500', 'active'),
    (mesa_farm_id, 'Organic Cucumbers', 'Fresh and crispy organic cucumbers', 'Vegetables', 100.00, 55, 'kg', 'https://images.unsplash.com/photo-1568584711271-1098a6c5b0d6?w=500', 'active');

  -- Insert products for Taco Bell (Fresh Mexican ingredients)
  INSERT INTO products (retailer_id, name, description, category, price, stock_quantity, unit, image_url, status)
  VALUES
    (taco_bell_id, 'Fresh Jalapeños', 'Spicy jalapeño peppers for authentic Mexican dishes', 'Vegetables', 180.00, 40, 'kg', 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=500', 'active'),
    (taco_bell_id, 'Red Onions', 'Fresh red onions perfect for salsas', 'Vegetables', 110.00, 50, 'kg', 'https://images.unsplash.com/photo-1508747703725-719777637510?w=500', 'active'),
    (taco_bell_id, 'Cilantro Bundle', 'Fresh cilantro for Mexican cuisine', 'Herbs', 60.00, 35, 'bunch', 'https://images.unsplash.com/photo-1599547275228-fdc5448c0dfd?w=500', 'active'),
    (taco_bell_id, 'Avocados', 'Ripe avocados for guacamole', 'Fruits', 250.00, 30, 'kg', 'https://images.unsplash.com/photo-1523049673857-eb18f1d7b578?w=500', 'active'),
    (taco_bell_id, 'Fresh Limes', 'Juicy limes for authentic flavor', 'Fruits', 140.00, 45, 'kg', 'https://images.unsplash.com/photo-1582979512210-99b6a53386f9?w=500', 'active'),
    (taco_bell_id, 'Mexican Chili Peppers', 'Authentic Mexican chili peppers', 'Vegetables', 200.00, 25, 'kg', 'https://images.unsplash.com/photo-1583663848850-46af132dc08e?w=500', 'active'),
    (taco_bell_id, 'Tomatillos', 'Fresh tomatillos for salsa verde', 'Vegetables', 170.00, 30, 'kg', 'https://images.unsplash.com/photo-1570368294249-6cc0f0e9f8cb?w=500', 'active'),
    (taco_bell_id, 'Corn Kernels', 'Sweet corn kernels for Mexican dishes', 'Vegetables', 130.00, 40, 'kg', 'https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=500', 'active');

  -- Insert products for Jay's Artisan (Coffees and bread)
  INSERT INTO products (retailer_id, name, description, category, price, stock_quantity, unit, image_url, status)
  VALUES
    (jays_artisan_id, 'Arabica Coffee Beans', 'Premium Arabica coffee beans, freshly roasted', 'Coffee', 450.00, 50, 'kg', 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=500', 'active'),
    (jays_artisan_id, 'Sourdough Bread', 'Artisan sourdough bread baked fresh daily', 'Bread', 180.00, 30, 'loaf', 'https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=500', 'active'),
    (jays_artisan_id, 'French Baguette', 'Classic French baguette with crispy crust', 'Bread', 120.00, 40, 'piece', 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=500', 'active'),
    (jays_artisan_id, 'Espresso Blend', 'Rich espresso blend for perfect coffee', 'Coffee', 500.00, 35, 'kg', 'https://images.unsplash.com/photo-1610889556528-9a770e32642f?w=500', 'active'),
    (jays_artisan_id, 'Whole Wheat Bread', 'Healthy whole wheat bread', 'Bread', 150.00, 35, 'loaf', 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=500', 'active'),
    (jays_artisan_id, 'Croissants', 'Buttery, flaky croissants', 'Bread', 200.00, 25, 'pack of 6', 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=500', 'active'),
    (jays_artisan_id, 'Cold Brew Coffee', 'Smooth cold brew concentrate', 'Coffee', 350.00, 30, 'liter', 'https://images.unsplash.com/photo-1517487881594-2787fef5ebf7?w=500', 'active'),
    (jays_artisan_id, 'Multigrain Bread', 'Nutritious multigrain bread', 'Bread', 170.00, 28, 'loaf', 'https://images.unsplash.com/photo-1547638385-7a7e4ab2b0b4?w=500', 'active'),
    (jays_artisan_id, 'Colombian Coffee', 'Premium Colombian coffee beans', 'Coffee', 480.00, 40, 'kg', 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=500', 'active');

  -- Insert products for Ocean Fresh (Daily catch seafood)
  INSERT INTO products (retailer_id, name, description, category, price, stock_quantity, unit, image_url, status)
  VALUES
    (ocean_fresh_id, 'Fresh Salmon', 'Wild-caught fresh salmon fillets', 'Seafood', 650.00, 25, 'kg', 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=500', 'active'),
    (ocean_fresh_id, 'Tiger Prawns', 'Large tiger prawns, fresh from the ocean', 'Seafood', 800.00, 20, 'kg', 'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?w=500', 'active'),
    (ocean_fresh_id, 'Fresh Tuna', 'Premium tuna steaks', 'Seafood', 700.00, 18, 'kg', 'https://images.unsplash.com/photo-1580959375944-0c5e8a3e3b5f?w=500', 'active'),
    (ocean_fresh_id, 'Sea Bass', 'Fresh sea bass, whole or filleted', 'Seafood', 600.00, 22, 'kg', 'https://images.unsplash.com/photo-1544943910-4c1dc44aab44?w=500', 'active'),
    (ocean_fresh_id, 'Squid', 'Fresh squid, cleaned and ready to cook', 'Seafood', 450.00, 30, 'kg', 'https://images.unsplash.com/photo-1599084993091-1cb5c0721cc6?w=500', 'active'),
    (ocean_fresh_id, 'Mussels', 'Fresh mussels in shell', 'Seafood', 350.00, 35, 'kg', 'https://images.unsplash.com/photo-1615141982883-c7ad0e69fd62?w=500', 'active'),
    (ocean_fresh_id, 'Crab Meat', 'Fresh crab meat, hand-picked', 'Seafood', 900.00, 15, 'kg', 'https://images.unsplash.com/photo-1585237672869-459cb4e0d6c0?w=500', 'active'),
    (ocean_fresh_id, 'Red Snapper', 'Fresh red snapper, daily catch', 'Seafood', 550.00, 24, 'kg', 'https://images.unsplash.com/photo-1534043464124-3be32fe000c9?w=500', 'active'),
    (ocean_fresh_id, 'Oysters', 'Fresh oysters on the half shell', 'Seafood', 750.00, 20, 'dozen', 'https://images.unsplash.com/photo-1626201735555-7cfe6c872135?w=500', 'active');

END $$;
