<?php
/**
 * Product Population Script
 * This script populates the products table with all products from the website
 */

require_once __DIR__ . '/supabase-api.php';

$api = getSupabaseAPI();

// Product data extracted from products.php
$products = [
    [
        'name' => 'Fresh Vegetable Bundle',
        'description' => 'A fresh assortment of seasonal vegetables including carrots, spinach, and broccoli, perfect for healthy meals.',
        'category' => 'vegetables',
        'price' => 24.99,
        'unit' => 'Per kg',
        'image_url' => 'images/products/Fresh Vegetable Box.png',
        'stock_quantity' => 100,
        'status' => 'active'
    ],
    [
        'name' => 'Organic Lettuce',
        'description' => 'Crisp and fresh organic lettuce, perfect for salads and sandwiches.',
        'category' => 'vegetables',
        'price' => 5.99,
        'unit' => 'Per kg',
        'image_url' => 'images/products/Organic Lettuce.png',
        'stock_quantity' => 150,
        'status' => 'active'
    ],
    [
        'name' => 'Fresh Strawberries',
        'description' => 'Juicy and sweet strawberries, handpicked at peak ripeness.',
        'category' => 'fruits',
        'price' => 8.99,
        'unit' => 'Per kg',
        'image_url' => 'images/products/strawberry.png',
        'stock_quantity' => 80,
        'status' => 'active'
    ],
    [
        'name' => 'Farm Fresh Milk',
        'description' => 'Pure and fresh milk straight from local farms, rich in nutrients and perfect for daily consumption.',
        'category' => 'dairy',
        'price' => 65.00,
        'unit' => 'Per liter',
        'image_url' => 'images/products/Fresh Milk.png',
        'stock_quantity' => 200,
        'status' => 'active'
    ],
    [
        'name' => 'Baby Carrots',
        'description' => 'Sweet and crunchy baby carrots, perfect for snacking or adding to meals, grown locally for freshness.',
        'category' => 'vegetables',
        'price' => 32.75,
        'unit' => 'Per kg',
        'image_url' => 'images/products/carrots.png',
        'stock_quantity' => 120,
        'status' => 'active'
    ],
    [
        'name' => 'Artisan Bread',
        'description' => 'Freshly baked artisan bread with a crispy crust and soft interior, made with traditional methods and high-quality ingredients.',
        'category' => 'bakery',
        'price' => 45.00,
        'unit' => 'Per loaf',
        'image_url' => 'images/products/bread.png',
        'stock_quantity' => 50,
        'status' => 'active'
    ],
    [
        'name' => 'Ripe Bananas',
        'description' => 'Perfectly ripe bananas, sweet and ready to eat, sourced from local plantations for optimal taste and nutrition.',
        'category' => 'fruits',
        'price' => 28.99,
        'unit' => 'Per kg',
        'image_url' => 'images/products/banana.png',
        'stock_quantity' => 180,
        'status' => 'active'
    ],
    [
        'name' => 'Aged Cheddar',
        'description' => 'Rich and sharp aged cheddar cheese, matured to perfection for a bold flavor, ideal for cheese boards and cooking.',
        'category' => 'dairy',
        'price' => 125.50,
        'unit' => 'Per 250g',
        'image_url' => 'images/products/cheese.png',
        'stock_quantity' => 60,
        'status' => 'active'
    ],
    [
        'name' => 'Fresh Vegetable Box',
        'description' => 'A curated box of fresh vegetables, including a variety of greens and roots, sourced directly from local farms for maximum freshness.',
        'category' => 'vegetables',
        'price' => 45.00,
        'unit' => 'Bundle',
        'image_url' => 'images/products/Fresh Vegetable Box.png',
        'stock_quantity' => 40,
        'status' => 'active'
    ],
    [
        'name' => 'Banana',
        'description' => 'Fresh, ripe bananas, sweet and nutritious, ideal for a quick energy boost or baking, sourced from local plantations.',
        'category' => 'fruits',
        'price' => 15.00,
        'unit' => 'Per piece',
        'image_url' => 'images/products/banana.png',
        'stock_quantity' => 300,
        'status' => 'active'
    ],
    [
        'name' => 'Tomato',
        'description' => 'Juicy and ripe tomatoes, perfect for salads, sauces, or cooking, grown in local greenhouses for optimal flavor.',
        'category' => 'vegetables',
        'price' => 28.00,
        'unit' => 'Per kg',
        'image_url' => 'images/products/Native tomato.jpg',
        'stock_quantity' => 150,
        'status' => 'active'
    ]
];

echo "<!DOCTYPE html>
<html>
<head>
    <title>Product Population</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #2E7D32; }
        .success { color: #2E7D32; padding: 10px; background: #E8F5E9; border-left: 4px solid #2E7D32; margin: 10px 0; }
        .error { color: #C62828; padding: 10px; background: #FFEBEE; border-left: 4px solid #C62828; margin: 10px 0; }
        .info { color: #1976D2; padding: 10px; background: #E3F2FD; border-left: 4px solid #1976D2; margin: 10px 0; }
        .product { padding: 15px; margin: 10px 0; background: #f9f9f9; border-radius: 4px; }
        .product h3 { margin: 0 0 10px 0; color: #333; }
        .product p { margin: 5px 0; color: #666; }
        button { background: #2E7D32; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #1B5E20; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🌱 Farmers Mall - Product Population</h1>
";

// Check if we should proceed with insertion
if (!isset($_POST['confirm'])) {
    echo "<div class='info'>
            <strong>Preview:</strong> Found " . count($products) . " products to insert into the database.
          </div>";
    
    echo "<h2>Products to be Added:</h2>";
    foreach ($products as $index => $product) {
        echo "<div class='product'>
                <h3>" . ($index + 1) . ". " . htmlspecialchars($product['name']) . "</h3>
                <p><strong>Category:</strong> " . htmlspecialchars($product['category']) . "</p>
                <p><strong>Price:</strong> ₱" . number_format($product['price'], 2) . " " . htmlspecialchars($product['unit']) . "</p>
                <p><strong>Description:</strong> " . htmlspecialchars($product['description']) . "</p>
                <p><strong>Stock:</strong> " . $product['stock_quantity'] . " units</p>
                <p><strong>Image:</strong> " . htmlspecialchars($product['image_url']) . "</p>
              </div>";
    }
    
    echo "<form method='POST'>
            <input type='hidden' name='confirm' value='1'>
            <button type='submit'>✓ Confirm and Insert Products</button>
          </form>";
    
} else {
    echo "<h2>Inserting Products...</h2>";
    
    $successCount = 0;
    $errorCount = 0;
    $errors = [];
    
    foreach ($products as $product) {
        try {
            // Check if product already exists
            $existing = $api->select('products', ['name' => $product['name']]);
            
            if (!empty($existing)) {
                echo "<div class='info'>⚠️ Product already exists: <strong>" . htmlspecialchars($product['name']) . "</strong> (Skipped)</div>";
                continue;
            }
            
            // Insert the product
            $result = $api->insert('products', $product);
            
            if (!empty($result)) {
                $successCount++;
                echo "<div class='success'>✓ Successfully added: <strong>" . htmlspecialchars($product['name']) . "</strong></div>";
            } else {
                $errorCount++;
                $errors[] = $product['name'];
                echo "<div class='error'>✗ Failed to add: <strong>" . htmlspecialchars($product['name']) . "</strong></div>";
            }
            
        } catch (Exception $e) {
            $errorCount++;
            $errors[] = $product['name'] . ' - ' . $e->getMessage();
            echo "<div class='error'>✗ Error adding <strong>" . htmlspecialchars($product['name']) . "</strong>: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
    
    echo "<hr>";
    echo "<h2>Summary</h2>";
    echo "<div class='success'><strong>✓ Successfully Inserted:</strong> $successCount products</div>";
    
    if ($errorCount > 0) {
        echo "<div class='error'><strong>✗ Failed:</strong> $errorCount products</div>";
        if (!empty($errors)) {
            echo "<div class='error'><strong>Errors:</strong><ul>";
            foreach ($errors as $error) {
                echo "<li>" . htmlspecialchars($error) . "</li>";
            }
            echo "</ul></div>";
        }
    }
    
    echo "<p><a href='../user/products.php'><button>→ View Products Page</button></a></p>";
}

echo "    </div>
</body>
</html>";
?>
