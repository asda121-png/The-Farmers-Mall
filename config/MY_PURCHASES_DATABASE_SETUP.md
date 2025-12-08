# My Purchases Database Setup Guide

## Required Tables

Your Supabase database needs these two tables for the My Purchases feature to work:

### 1. **orders** table
Stores main order information for each purchase.

**Required Columns:**
- `id` (UUID, PRIMARY KEY) - Unique order identifier
- `customer_id` (UUID, FOREIGN KEY → users.id) - Links to the customer who placed the order
- `customer_name` (VARCHAR) - Customer's full name (cached for performance)
- `customer_email` (VARCHAR) - Customer's email (cached for performance)
- `retailer_id` (UUID, FOREIGN KEY → retailers.id) - Links to the retailer/seller
- `total_amount` (DECIMAL) - Total order amount
- `status` (VARCHAR) - Order status: 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'
- `payment_method` (VARCHAR) - Payment method used (e.g., 'Cash on Delivery', 'Credit Card')
- `payment_status` (VARCHAR) - Payment status: 'pending', 'paid', 'failed', 'refunded'
- `delivery_address` (TEXT) - Delivery address for the order
- `notes` (TEXT) - Additional order notes
- `created_at` (TIMESTAMP) - When order was created
- `updated_at` (TIMESTAMP) - When order was last updated

### 2. **order_items** table
Stores individual products within each order.

**Required Columns:**
- `id` (UUID, PRIMARY KEY) - Unique item identifier
- `order_id` (UUID, FOREIGN KEY → orders.id) - Links to parent order
- `product_id` (UUID, FOREIGN KEY → products.id) - Links to the product
- `product_name` (VARCHAR) - Product name (cached, preserved even if product deleted)
- `product_image_url` (TEXT) - Product image URL (cached)
- `quantity` (INTEGER) - Quantity ordered
- `price` (DECIMAL) - Price per unit at time of order
- `subtotal` (DECIMAL) - Total for this item (quantity × price)
- `created_at` (TIMESTAMP) - When item was added

## Setup Instructions

### Option 1: Using Supabase Dashboard (Recommended)

1. **Login to Supabase Dashboard**
   - Go to https://supabase.com
   - Navigate to your project

2. **Open SQL Editor**
   - Click "SQL Editor" in the left sidebar
   - Click "New Query"

3. **Run the Verification Script**
   - Copy the contents of `config/verify_orders_table.sql`
   - Paste into the SQL editor
   - Click "Run" or press Ctrl+Enter

4. **Check Results**
   - The script will create tables if they don't exist
   - Add missing columns if needed
   - Create indexes for better performance
   - Display current table structure

### Option 2: Using Schema File

If starting fresh, run the main schema file:

```sql
-- In Supabase SQL Editor, run:
-- config/schema.sql
```

Then run the migration for additional columns:

```sql
-- In Supabase SQL Editor, run:
-- config/add_order_tracking_columns.sql
```

## Verification

After running the setup, verify your tables exist:

```sql
-- Check orders table
SELECT * FROM orders LIMIT 5;

-- Check order_items table  
SELECT * FROM order_items LIMIT 5;

-- Check table structure
SELECT column_name, data_type 
FROM information_schema.columns 
WHERE table_name = 'orders';

SELECT column_name, data_type 
FROM information_schema.columns 
WHERE table_name = 'order_items';
```

## Required Permissions

Make sure Row Level Security (RLS) is disabled or properly configured:

```sql
-- Disable RLS (if needed)
ALTER TABLE orders DISABLE ROW LEVEL SECURITY;
ALTER TABLE order_items DISABLE ROW LEVEL SECURITY;
```

Or configure RLS policies:

```sql
-- Allow users to see their own orders
CREATE POLICY "Users can view own orders" ON orders
    FOR SELECT USING (customer_id = auth.uid());

-- Allow users to update their own pending orders
CREATE POLICY "Users can cancel own pending orders" ON orders
    FOR UPDATE USING (
        customer_id = auth.uid() 
        AND status = 'pending'
    );
```

## Testing with Sample Data

After setup, you can test with sample data:

```sql
-- Insert a test order
INSERT INTO orders (
    customer_id, 
    customer_name, 
    customer_email, 
    total_amount, 
    status, 
    payment_method,
    delivery_address
) VALUES (
    'your-user-id-here',
    'Test Customer',
    'test@example.com',
    150.00,
    'pending',
    'Cash on Delivery',
    '123 Test Street, Test City'
);

-- Insert test order items (use the order ID from above)
INSERT INTO order_items (
    order_id,
    product_id,
    product_name,
    product_image_url,
    quantity,
    price,
    subtotal
) VALUES (
    'order-id-from-above',
    'some-product-id',
    'Fresh Tomatoes',
    '../images/products/tomatoes.jpg',
    2,
    75.00,
    150.00
);
```

## Troubleshooting

### Issue: "relation 'orders' does not exist"
**Solution:** Run `config/schema.sql` to create the tables

### Issue: "column 'customer_name' does not exist"
**Solution:** Run `config/add_order_tracking_columns.sql` to add missing columns

### Issue: "permission denied for table orders"
**Solution:** Disable RLS or configure proper policies (see above)

### Issue: No orders showing in My Purchases page
**Solution:** 
1. Check if orders exist: `SELECT * FROM orders WHERE customer_id = 'your-user-id';`
2. Verify user is logged in and session has correct user_id
3. Check browser console for JavaScript errors

## Database Schema Diagram

```
users
  └── id (UUID)
       ↓
orders (customer_id references users.id)
  ├── id (UUID)
  ├── customer_id (UUID) → users.id
  ├── customer_name
  ├── customer_email
  ├── retailer_id → retailers.id
  ├── total_amount
  ├── status
  ├── payment_method
  ├── payment_status
  ├── delivery_address
  ├── notes
  ├── created_at
  └── updated_at
       ↓
order_items (order_id references orders.id)
  ├── id (UUID)
  ├── order_id (UUID) → orders.id
  ├── product_id → products.id
  ├── product_name
  ├── product_image_url
  ├── quantity
  ├── price
  ├── subtotal
  └── created_at
```

## Next Steps

After database setup is complete:

1. ✅ Tables created with correct structure
2. ✅ Indexes created for performance
3. ✅ Permissions configured
4. 🔄 Create test orders to verify functionality
5. 🔄 Test My Purchases page at `/user/my-purchases.php`
6. 🔄 Test order filtering by status
7. 🔄 Test order actions (cancel, confirm delivery, buy again)

## Support

If you encounter issues:
- Check Supabase logs in the dashboard
- Verify API credentials in `config/env.php`
- Check browser console for JavaScript errors
- Verify user is logged in with valid session
