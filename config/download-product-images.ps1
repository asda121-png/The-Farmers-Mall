# Download actual product images from a free image service
$products = @{
    # Mesa Farm - Vegetables & Herbs
    "tomatoes.jpg" = "https://images.unsplash.com/photo-1546470427-227e925a5df7?w=400&h=300&fit=crop"
    "lettuce.jpg" = "https://images.unsplash.com/photo-1622206151226-18ca2c9ab4a1?w=400&h=300&fit=crop"
    "carrots.jpg" = "https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=400&h=300&fit=crop"
    "basil.jpg" = "https://images.unsplash.com/photo-1618375569909-3c8616cf7733?w=400&h=300&fit=crop"
    "spinach.jpg" = "https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=400&h=300&fit=crop"
    "bell-peppers.jpg" = "https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?w=400&h=300&fit=crop"
    "rosemary.jpg" = "https://images.unsplash.com/photo-1584638431214-8d4c6b0c2e6d?w=400&h=300&fit=crop"
    "cucumbers.jpg" = "https://images.unsplash.com/photo-1589927986089-35812388d1f4?w=400&h=300&fit=crop"
    
    # Taco Bell - Mexican Ingredients
    "jalapenos.jpg" = "https://images.unsplash.com/photo-1599003176311-a3c8c6d16b4d?w=400&h=300&fit=crop"
    "red-onions.jpg" = "https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=400&h=300&fit=crop"
    "cilantro.jpg" = "https://images.unsplash.com/photo-1556901786-4e5a0c3e2f6f?w=400&h=300&fit=crop"
    "avocados.jpg" = "https://images.unsplash.com/photo-1523049673857-eb18f1d7b578?w=400&h=300&fit=crop"
    "limes.jpg" = "https://images.unsplash.com/photo-1582169296194-e4d644c48063?w=400&h=300&fit=crop"
    "chili-peppers.jpg" = "https://images.unsplash.com/photo-1583663818404-6c8c86fcdfeb?w=400&h=300&fit=crop"
    "tomatillos.jpg" = "https://images.unsplash.com/photo-1629116994889-53bbb8e44133?w=400&h=300&fit=crop"
    "corn.jpg" = "https://images.unsplash.com/photo-1551754655-cd27e38d2076?w=400&h=300&fit=crop"
    
    # Jay's Artisan - Coffee & Bread
    "coffee-beans.jpg" = "https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=400&h=300&fit=crop"
    "sourdough.jpg" = "https://images.unsplash.com/photo-1549931319-a545dcf3bc73?w=400&h=300&fit=crop"
    "baguette.jpg" = "https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop"
    "espresso.jpg" = "https://images.unsplash.com/photo-1610889556528-9a770e32642f?w=400&h=300&fit=crop"
    "wheat-bread.jpg" = "https://images.unsplash.com/photo-1585478259715-876acc6ab7f2?w=400&h=300&fit=crop"
    "croissants.jpg" = "https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=400&h=300&fit=crop"
    "cold-brew.jpg" = "https://images.unsplash.com/photo-1517487881594-2787fef5ebf7?w=400&h=300&fit=crop"
    "multigrain.jpg" = "https://images.unsplash.com/photo-1586444248902-2f64eddc13df?w=400&h=300&fit=crop"
    "colombian-coffee.jpg" = "https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=400&h=300&fit=crop"
    
    # Ocean Fresh - Seafood
    "salmon.jpg" = "https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=400&h=300&fit=crop"
    "prawns.jpg" = "https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?w=400&h=300&fit=crop"
    "tuna.jpg" = "https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=400&h=300&fit=crop"
    "sea-bass.jpg" = "https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=400&h=300&fit=crop"
    "squid.jpg" = "https://images.unsplash.com/photo-1608208597396-51511689e43e?w=400&h=300&fit=crop"
    "mussels.jpg" = "https://images.unsplash.com/photo-1567031972589-1b7ce3a0e8b7?w=400&h=300&fit=crop"
    "crab.jpg" = "https://images.unsplash.com/photo-1608533922687-3c8d95d9f4e7?w=400&h=300&fit=crop"
    "red-snapper.jpg" = "https://images.unsplash.com/photo-1544943910-4c1dc44aab44?w=400&h=300&fit=crop"
    "oysters.jpg" = "https://images.unsplash.com/photo-1626201047821-0c46b9c4c1c6?w=400&h=300&fit=crop"
}

$destPath = "C:\Users\Jayson Bustamante\Downloads\The-Farmers-Mall\images\products"
$successCount = 0
$failCount = 0

Write-Host "Starting download of product images..." -ForegroundColor Green
Write-Host "Destination: $destPath" -ForegroundColor Cyan
Write-Host ""

foreach ($product in $products.GetEnumerator()) {
    $fileName = $product.Key
    $url = $product.Value
    $filePath = Join-Path $destPath $fileName
    
    try {
        Write-Host "Downloading $fileName..." -NoNewline
        Invoke-WebRequest -Uri $url -OutFile $filePath -UseBasicParsing
        Write-Host " ✓ Success" -ForegroundColor Green
        $successCount++
    }
    catch {
        Write-Host " ✗ Failed: $($_.Exception.Message)" -ForegroundColor Red
        $failCount++
    }
    
    Start-Sleep -Milliseconds 500  # Small delay to avoid rate limiting
}

Write-Host ""
Write-Host "Download complete!" -ForegroundColor Green
Write-Host "Success: $successCount | Failed: $failCount" -ForegroundColor Cyan
