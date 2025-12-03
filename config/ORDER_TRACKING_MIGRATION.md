# Database Migration: Enhanced Order Tracking

## What This Migration Does

This migration adds additional tracking columns to the `orders` and `order_items` tables to make it easier to track:
- **Who ordered**: Customer name and email saved directly in orders table
- **What was ordered**: Product name and image saved in order_items table

## Why These Changes?

### Benefits:
1. **Data Preservation**: Product names and images are saved even if products are deleted later
2. **Faster Queries**: No need to join multiple tables to get customer/product names
3. **Better Reporting**: Easy to generate order reports without complex joins
4. **Historical Accuracy**: Orders show exactly what was ordered at that time

### New Columns:

#### `orders` table:
- `customer_name` - Full name of the customer (cached from users table)
- `customer_email` - Email of the customer (cached from users table)

#### `order_items` table:
- `product_name` - Name of the product at time of order
- `product_image_url` - Product image URL at time of order

## How to Apply This Migration

### Option 1: Using Supabase Dashboard
1. Go to your Supabase project dashboard
2. Navigate to the SQL Editor
3. Copy the contents of `add_order_tracking_columns.sql`
4. Paste and run the SQL

### Option 2: Using psql Command Line
```bash
psql -h your-supabase-host -U postgres -d postgres -f add_order_tracking_columns.sql
```

### Option 3: Manual Steps in Supabase SQL Editor

Run these commands one by one:

```sql
-- Add columns to orders table
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS customer_name VARCHAR(255),
ADD COLUMN IF NOT EXISTS customer_email VARCHAR(255);

-- Add columns to order_items table
ALTER TABLE order_items 
ADD COLUMN IF NOT EXISTS product_name VARCHAR(255),
ADD COLUMN IF NOT EXISTS product_image_url TEXT;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_orders_customer_id ON orders(customer_id);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
CREATE INDEX IF NOT EXISTS idx_order_items_order_id ON order_items(order_id);
CREATE INDEX IF NOT EXISTS idx_order_items_product_id ON order_items(product_id);
```

## Verifying the Migration

After running the migration, verify the columns exist:

```sql
-- Check orders table structure
SELECT column_name, data_type 
FROM information_schema.columns 
WHERE table_name = 'orders';

-- Check order_items table structure
SELECT column_name, data_type 
FROM information_schema.columns 
WHERE table_name = 'order_items';
```

## Example Queries After Migration

### Get all orders with customer info (no join needed):
```sql
SELECT id, customer_name, customer_email, total_amount, status, created_at
FROM orders
ORDER BY created_at DESC;
```

### Get order details with product names (no product join needed):
```sql
SELECT 
    o.id as order_id,
    o.customer_name,
    oi.product_name,
    oi.quantity,
    oi.price,
    oi.subtotal
FROM orders o
JOIN order_items oi ON o.id = oi.order_id
WHERE o.customer_email = 'user@example.com';
```

### Get product sales summary:
```sql
SELECT 
    product_name,
    SUM(quantity) as total_sold,
    SUM(subtotal) as total_revenue
FROM order_items
GROUP BY product_name
ORDER BY total_revenue DESC;
```

## Rolling Back (If Needed)

If you need to remove these columns:

```sql
ALTER TABLE orders 
DROP COLUMN IF EXISTS customer_name,
DROP COLUMN IF EXISTS customer_email;

ALTER TABLE order_items 
DROP COLUMN IF EXISTS product_name,
DROP COLUMN IF EXISTS product_image_url;
```

## Notes

- The migration is **non-destructive** - it only adds columns
- Existing data is preserved
- The API (`api/order.php`) has been updated to populate these fields automatically
- Future orders will have all tracking information saved automatically
