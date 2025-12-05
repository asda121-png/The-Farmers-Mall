# Product Import & Verification System

This system automatically extracts product information from all user-facing pages and stores them in the database.

## Files Created

1. **`config/import-all-products.php`** - Main import script
2. **`config/verify-products.php`** - Database verification tool
3. **`config/product_import.log`** - Import log file (auto-generated)

## What Gets Imported

The system extracts and stores product information from:

### 1. **user-homepage.php**
- Top Products section (5 products)
- Other Products section (12 products)

### 2. **products.php**
- All product cards with details (12 products)

### 3. **productdetails.php**
- Related products section (4 products)

### 4. **shop-products.php**
- Products from specific retailers

## Product Information Captured

Each product includes:
- **Name** - Product title
- **Description** - Detailed product description
- **Category** - vegetables, fruits, dairy, meat, seafood, bakery
- **Price** - Product price in PHP (₱)
- **Stock Quantity** - Available inventory
- **Unit** - kg, piece, liter, dozen, etc.
- **Image URL** - Path to product image
- **Status** - active, inactive, or out_of_stock
- **Retailer ID** - Associated retailer/shop

## How to Use

### Step 1: Run the Import Script

Open your browser and navigate to:
```
http://localhost/The-Farmers-Mall/config/import-all-products.php
```

**What happens:**
1. Creates "Farmers Mall" retailer if it doesn't exist
2. Extracts all unique products from the pages
3. Checks for duplicates (by product name)
4. Inserts new products into the database
5. Displays a summary with:
   - ✅ Successfully imported products
   - ⚠️ Skipped products (already exist)
   - ❌ Failed imports

### Step 2: Verify the Import

Navigate to:
```
http://localhost/The-Farmers-Mall/config/verify-products.php
```

**You'll see:**
- Total product count
- Products grouped by category
- Full product details table with:
  - Product images
  - Names and descriptions
  - Prices and stock levels
  - Retailer information
  - Status indicators
- Product statistics:
  - Total products
  - Active products
  - Total stock quantity
  - Total inventory value

## Product Categories

The system imports products in these categories:

| Category | Example Products |
|----------|-----------------|
| **Vegetables** | Fresh Vegetable Box, Organic Lettuce, Native Tomatoes, Fresh Okra, Baby Carrots |
| **Fruits** | Fresh Strawberries, Fresh Avocado, Ripe Bananas |
| **Dairy** | Fresh Milk, Farm Eggs, Aged Cheddar, Chocolate Milk, Butter Spread |
| **Meat** | Fresh Pork Liempo, Native Chicken, Pork Ribs, Free-Range Chicken |
| **Seafood** | Tilapia, Bangus, Shrimp |
| **Bakery** | Emsaymada, Artisan Bread, Ube Cheese Pandesal |

## Complete Product List

### From user-homepage.php (Top Products):
1. Fresh Vegetable Box - ₱45.00/box
2. Organic Lettuce - ₱30.00/kg
3. Fresh Milk - ₱50.00/liter
4. Tilapia - ₱80.00/kg
5. Farm Eggs - ₱60.00/dozen

### From user-homepage.php (Other Products):
6. Emsaymada - ₱25.00/piece
7. Butter Spread - ₱70.00/250g
8. Bangus - ₱140.00/kg
9. Fresh Pork Liempo - ₱180.00/kg
10. Fresh Avocado - ₱50.00/kg
11. Native Tomatoes - ₱30.00/kg
12. Fresh Okra - ₱25.00/kg
13. Native Chicken - ₱260.00/kg
14. Pork Ribs - ₱310.00/kg
15. Shrimp - ₱400.00/kg
16. Chocolate Milk - ₱55.00/250ml
17. Ube Cheese Pandesal - ₱50.00/5 pcs

### From products.php:
18. Fresh Vegetable Bundle - ₱24.99/kg
19. Fresh Strawberries - ₱89.99/kg
20. Farm Fresh Milk - ₱95.00/liter
21. Baby Carrots - ₱32.75/kg
22. Artisan Bread - ₱28.00/loaf
23. Ripe Bananas - ₱28.99/kg
24. Aged Cheddar - ₱120.00/250g
25. Tomato - ₱28.00/kg

### From productdetails.php:
26. Organic Broccoli - ₱45.50/kg
27. Free-Range Chicken - ₱280.00/kg

## Features

### Duplicate Prevention
- Checks existing products by name and retailer
- Skips products that already exist
- Prevents duplicate entries

### Error Handling
- Logs all operations to `product_import.log`
- Continues processing even if individual products fail
- Provides detailed error messages

### Automatic Retailer Management
- Creates "Farmers Mall" retailer if needed
- Links all products to the correct retailer
- Maintains retailer relationships

## Database Schema

Products are stored with this structure:

```sql
CREATE TABLE products (
    id UUID PRIMARY KEY,
    retailer_id UUID REFERENCES retailers(id),
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INTEGER DEFAULT 0,
    unit VARCHAR(50),
    image_url VARCHAR(500),
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Troubleshooting

### Problem: Products not appearing on website
**Solution:** Check that:
- Products have `status = 'active'`
- `stock_quantity > 0`
- Image URLs are correct

### Problem: Import fails
**Solution:** 
1. Check `config/product_import.log` for errors
2. Verify database connection in `config/supabase-api.php`
3. Ensure retailer exists

### Problem: Duplicate products
**Solution:**
- The script automatically skips duplicates
- Manually remove duplicates from database if needed
- Re-run verification script

## Re-running the Import

You can safely re-run the import script:
- Existing products will be skipped
- Only new products will be added
- No data will be lost

## Maintenance

### Updating Product Information
To update products:
1. Modify the `$products` array in `import-all-products.php`
2. Re-run the import script
3. Or update directly in the database

### Adding New Products
Add new product entries to the `$products` array:
```php
[
    'name' => 'Product Name',
    'description' => 'Product description',
    'category' => 'category_name',
    'price' => 99.99,
    'stock_quantity' => 50,
    'unit' => 'kg',
    'image_url' => 'images/products/image.jpg',
    'status' => 'active'
]
```

## Support

For issues or questions:
1. Check the log file: `config/product_import.log`
2. Run verification: `verify-products.php`
3. Review database directly

---

**Last Updated:** December 5, 2025
**Total Products:** 27 unique products
**Categories:** 6 (vegetables, fruits, dairy, meat, seafood, bakery)
