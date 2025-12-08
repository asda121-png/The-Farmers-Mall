# Order Tracking Enhancement - Product Name Column

## Overview
This migration adds a `product_name` column to the `orders` table to make order tracking easier. It also updates the order status values to match a clearer workflow.

## What's Changed

### 1. New Column Added
- **`product_name`** - VARCHAR(255) column in the `orders` table
  - Stores the primary product name for quick reference
  - For orders with multiple items, shows the first product
  - Makes it easy to track what's being ordered/cancelled without joining tables

### 2. Updated Order Status Values
The order status now follows a clearer workflow:
- **`to_pay`** - Order created, waiting for payment (was: pending)
- **`to_ship`** - Payment confirmed, preparing to ship (was: confirmed, processing)
- **`to_receive`** - Order shipped, on the way to customer (was: shipped)
- **`completed`** - Order successfully delivered (was: delivered)
- **`cancelled`** - Order cancelled

### 3. New Order Tracking View
A new database view `order_tracking_view` provides:
- All order details in one place
- Human-readable status descriptions
- Total item count per order
- Easy querying for reports and dashboards

## How to Run the Migration

### Method 1: Using PHP Script (Recommended)
```bash
php config/run-order-tracking-migration.php
```

### Method 2: Using Supabase SQL Editor
1. Log in to your Supabase dashboard
2. Go to SQL Editor
3. Open the file `config/add_product_name_to_orders.sql`
4. Copy and paste the contents into the SQL Editor
5. Click "Run"

### Method 3: Using psql command line
```bash
psql -h [your-supabase-host] -U postgres -d postgres -f config/add_product_name_to_orders.sql
```

## Benefits

### For Order Management
```sql
-- Now you can easily see what product was ordered
SELECT id, customer_name, product_name, status, total_amount 
FROM orders 
WHERE status = 'cancelled';
```

### For Tracking Orders
```sql
-- Use the tracking view for comprehensive order information
SELECT * FROM order_tracking_view 
WHERE status IN ('to_ship', 'to_receive');
```

### For Reports
```sql
-- Track popular products being ordered
SELECT product_name, COUNT(*) as order_count, SUM(total_amount) as revenue
FROM orders
WHERE status = 'completed'
GROUP BY product_name
ORDER BY order_count DESC;
```

## Database Schema After Migration

### Orders Table (Key Columns)
| Column | Type | Description |
|--------|------|-------------|
| id | UUID | Order ID |
| customer_name | VARCHAR(255) | Customer's name |
| customer_email | VARCHAR(255) | Customer's email |
| **product_name** | **VARCHAR(255)** | **Primary product name (NEW)** |
| total_amount | DECIMAL(10,2) | Order total |
| status | VARCHAR(20) | Order status (to_pay, to_ship, to_receive, completed, cancelled) |
| payment_status | VARCHAR(20) | Payment status |
| delivery_address | TEXT | Delivery address |

## Updating Code to Use New Status Values

### In PHP (example)
```php
// Old way
if ($order['status'] == 'pending') {
    // handle pending order
}

// New way
if ($order['status'] == 'to_pay') {
    // handle payment pending
}
```

### Status Mapping
| Old Status | New Status |
|------------|------------|
| pending | to_pay |
| confirmed | to_ship |
| processing | to_ship |
| shipped | to_receive |
| delivered | completed |
| cancelled | cancelled |

## Rollback (if needed)

If you need to rollback this migration:

```sql
-- Remove the product_name column
ALTER TABLE orders DROP COLUMN IF EXISTS product_name;

-- Restore old status values
UPDATE orders SET status = 'pending' WHERE status = 'to_pay';
UPDATE orders SET status = 'confirmed' WHERE status = 'to_ship';
UPDATE orders SET status = 'shipped' WHERE status = 'to_receive';
UPDATE orders SET status = 'delivered' WHERE status = 'completed';

-- Restore old constraint
ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check;
ALTER TABLE orders ADD CONSTRAINT orders_status_check 
CHECK (status IN ('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'));

-- Drop the view
DROP VIEW IF EXISTS order_tracking_view;
```

## Notes

- The `order_items` table already has `product_name` for each item in an order
- This new column in `orders` table provides quick access without joining tables
- For orders with multiple products, you can modify the migration to show all products (see commented code in SQL file)
- Existing orders will be automatically updated with product names from their order items
- The migration is safe to run multiple times (uses `IF NOT EXISTS` and `IF NULL` checks)

## Support

If you encounter any issues:
1. Check the database connection in `config/database.php`
2. Ensure you have proper database permissions
3. Review the error messages from the migration script
4. Check Supabase logs for detailed error information
