<?php
require_once __DIR__ . '/supabase-api.php';

$api = getSupabaseAPI();

// Get all products
$products = $api->select('products', []);

echo "<h2>Current Products in Database</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Name</th><th>Image URL</th><th>Retailer ID</th></tr>";

foreach ($products as $product) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($product['id']) . "</td>";
    echo "<td>" . htmlspecialchars($product['name']) . "</td>";
    echo "<td>" . htmlspecialchars($product['image_url']) . "</td>";
    echo "<td>" . htmlspecialchars($product['retailer_id']) . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "<p>Total products: " . count($products) . "</p>";
?>
