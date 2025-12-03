# Cart & Products Database Update Guide

## Overview
This update addresses two main requirements:
1. **Cart Table**: Show customer's full name instead of just customer_id
2. **Products Table**: Populate with all products from products.php

## Changes Made

### 1. SQL Migration File
**File**: `config/update_cart_with_customer_name.sql`

This SQL script will:
- ✅ Add `customer_name` column to cart table
- ✅ Populate `customer_name` with "FirstName LastName" from users table
- ✅ Insert all 11 products from products.php into the products table
- ✅ Create indexes for better performance

**Products Included**:
- **Vegetables** (5): Fresh Vegetable Bundle, Organic Lettuce, Baby Carrots, Fresh Vegetable Box, Tomato
- **Fruits** (3): Fresh Strawberries, Ripe Bananas, Banana
- **Dairy** (2): Farm Fresh Milk, Aged Cheddar
- **Bakery** (1): Artisan Bread

### 2. Cart API Updates
**File**: `api/cart.php`

**GET Request** - Now returns:
```json
{
  "success": true,
  "items": [
    {
      "cart_id": "...",
      "product_id": "...",
      "customer_name": "John Doe",     // ← NEW
      "product_name": "Fresh Lettuce",
      "name": "Fresh Lettuce",
      "price": 200.00,
      "quantity": 2,
      ...
    }
  ]
}
```

**POST Request** - Automatically:
- Fetches customer's first_name and last_name from users table
- Combines them as "FirstName LastName"
- Stores in cart.customer_name column
- Falls back to email if name is empty

## How to Apply Changes

### Step 1: Run SQL Migration
1. Open Supabase Dashboard → SQL Editor
2. Copy the entire content of `config/update_cart_with_customer_name.sql`
3. Paste and click **Run**

### Step 2: Assign Retailer to Products
1. Get a retailer ID:
```sql
SELECT id, email FROM users WHERE user_type = 'retailer' LIMIT 1;
```

2. Update products with retailer_id:
```sql
UPDATE products 
SET retailer_id = 'your-retailer-uuid-here' 
WHERE retailer_id IS NULL;
```

### Step 3: Verify Changes

**Check cart with customer names:**
```sql
SELECT 
    c.id, 
    c.customer_name,     -- Shows "FirstName LastName"
    c.product_name,      -- Shows product name
    c.quantity,
    c.created_at
FROM cart c 
ORDER BY c.created_at DESC;
```

**Check all products:**
```sql
SELECT 
    p.name, 
    p.category, 
    p.price, 
    p.stock_quantity,
    p.unit
FROM products p 
ORDER BY p.category, p.name;
```

**Count products by category:**
```sql
SELECT 
    category, 
    COUNT(*) as product_count 
FROM products 
GROUP BY category 
ORDER BY category;
```

Expected results:
- bakery: 1
- dairy: 2
- fruits: 3
- vegetables: 5
- **Total: 11 products**

## What Happens Now?

### When a user adds a product to cart:
1. API fetches customer's full name from users table
2. API fetches product name from products table
3. Both customer_name and product_name are stored in the cart table
4. You can easily see who added what in the database

### Database Query Example:
```sql
-- See cart items with customer names
SELECT 
    customer_name,
    product_name,
    quantity,
    created_at
FROM cart
ORDER BY created_at DESC;
```

Result will show:
```
customer_name    | product_name      | quantity | created_at
-----------------|-------------------|----------|------------
Jayson Bustamante| Fresh Strawberries|    2     | 2025-12-03
Jayson Bustamante| Organic Lettuce   |    1     | 2025-12-03
```

## Benefits

### ✅ Customer Name in Cart
- **Before**: Only see UUID (e.g., "abc123-def456-...")
- **After**: See full name (e.g., "Jayson Bustamante")
- **Fallback**: Uses email if name is not set

### ✅ Products Table Populated
- All 11 products from products.php are now in database
- Consistent pricing and descriptions
- Ready for product management features
- Can assign to retailers

### ✅ Better Data Management
- Easy to identify who added items
- Easy to see what products are in cart
- Better for reporting and analytics
- Improved debugging capability

## Testing

After running the SQL:

1. **Add a product to cart** from products.php
2. **Check the database**:
```sql
SELECT customer_name, product_name, quantity FROM cart;
```
3. **Verify** you see your full name and product name

## Notes

- The `customer_id` column is still there (needed for relationships)
- `customer_name` is a convenience column for easier reading
- Existing cart items will be updated with customer names automatically
- New cart items will always include customer_name and product_name
- If a customer has no first/last name, their email will be used instead

## Troubleshooting

**If customer_name is NULL:**
- User might not have first_name/last_name in users table
- Run the UPDATE query in the SQL file again
- Check if the user exists in users table

**If products don't appear:**
- Check for errors in SQL execution
- Verify ON CONFLICT clause worked (no duplicate names)
- Check retailer_id is assigned

**If API returns errors:**
- Check PHP error logs
- Verify session is active
- Ensure Supabase API connection is working
