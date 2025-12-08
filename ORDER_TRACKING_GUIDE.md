# Order Tracking System - Implementation Guide

## Overview
The order tracking system allows customers to track their orders from placement to delivery, with visual progress indicators and product information.

## Files Created/Modified

### New Files
1. **`user/track-order.php`** - Main order tracking page with timeline visualization
2. **`config/add_product_name_simple.sql`** - Database migration to add product_name column

### Modified Files
1. **`user/ordersuccessfull.php`** - Updated "Track Order" button to redirect to "My Orders"
2. **`user/my-purchases.php`** - Added "Track Order" button for each order
3. **`api/update-order-status.php`** - Added support for new status values

## Database Setup

### Step 1: Run the Migration
Copy the contents of `config/add_product_name_simple.sql` and run it in your Supabase SQL Editor:

```sql
-- Add product_name column to orders table
ALTER TABLE orders ADD COLUMN IF NOT EXISTS product_name VARCHAR(255);

-- Populate with existing data
UPDATE orders o
SET product_name = (
    SELECT oi.product_name
    FROM order_items oi
    WHERE oi.order_id = o.id
    ORDER BY oi.created_at ASC
    LIMIT 1
)
WHERE o.product_name IS NULL;

-- Create index
CREATE INDEX IF NOT EXISTS idx_orders_product_name ON orders(product_name);
```

### Step 2: Verify the Column
```sql
SELECT column_name, data_type 
FROM information_schema.columns 
WHERE table_name = 'orders' AND column_name = 'product_name';
```

## Order Status Workflow

### Status Values
The system supports both old and new status values:

| Old Status | New Status | Display Name | Description |
|------------|------------|--------------|-------------|
| pending | to_pay | To Pay | Waiting for payment |
| confirmed | to_ship | To Ship | Payment confirmed, preparing to ship |
| processing | to_ship | To Ship | Order being processed |
| shipped | to_receive | To Receive | Out for delivery |
| delivered | completed | Completed | Successfully delivered |
| cancelled | cancelled | Cancelled | Order cancelled |

### Status Transitions
- **To Pay** → Can be cancelled
- **To Ship** → Processing by seller
- **To Receive** → Can mark as received/completed
- **Completed** → Final status, can reorder
- **Cancelled** → Final status

## Features

### 1. Order Tracking Page (`track-order.php`)
**URL:** `user/track-order.php?order_id={ORDER_ID}`

Features:
- Visual timeline showing order progress
- Product information with images
- Order summary with total amount
- Delivery address and payment method
- Action buttons (Cancel/Confirm Delivery)
- Help/support contact information

### 2. My Purchases Page (`my-purchases.php`)
Features:
- Tabbed interface for filtering orders
- "Track Order" button for each order
- Order cards with product images
- Status badges with color coding
- Action buttons based on order status

### 3. Order Successful Page (`ordersuccessfull.php`)
Features:
- Success confirmation
- "View My Orders" button (redirects to my-purchases.php)
- Order summary
- Continue shopping option

## User Workflow

```
1. Customer places order
   ↓
2. Order created with status "to_pay" or "pending"
   ↓
3. Customer clicks "View My Orders" from success page
   ↓
4. Customer sees all orders in "My Purchases"
   ↓
5. Customer clicks "Track Order" button
   ↓
6. Customer sees tracking timeline with current status
   ↓
7. Customer can:
   - Cancel order (if status is to_pay)
   - Mark as received (if status is to_receive)
   - View order details
   - Contact support
```

## API Endpoints

### Get Order Details
**Endpoint:** `api/get-order-details.php?order_id={ORDER_ID}`

**Response:**
```json
{
  "success": true,
  "order": {
    "id": "uuid",
    "customer_name": "John Doe",
    "product_name": "Fresh Tomatoes",
    "total_amount": 150.00,
    "status": "to_receive",
    "delivery_address": "123 Main St"
  },
  "items": [
    {
      "product_name": "Fresh Tomatoes",
      "quantity": 2,
      "price": 75.00,
      "subtotal": 150.00
    }
  ]
}
```

### Update Order Status
**Endpoint:** `api/update-order-status.php`

**Request:**
```json
{
  "order_id": "uuid",
  "status": "completed"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Order status updated successfully",
  "new_status": "completed"
}
```

## Customization

### Adding More Tracking Steps
Edit `track-order.php`, function `getTrackingSteps()`:

```php
$steps = [
    [
        'name' => 'Step Name',
        'icon' => 'fa-icon-name',
        'description' => 'Description text',
        'statuses' => ['status1', 'status2']
    ]
];
```

### Changing Status Colors
Edit `my-purchases.php`, function `getStatusColor()`:

```php
$colors = [
    'pending' => 'orange',
    'to_ship' => 'blue',
    // Add more...
];
```

### Modifying Timeline Colors
Edit CSS in `track-order.php`:

```css
.timeline-icon.active {
    border-color: #2E7D32; /* Green */
    background: #2E7D32;
}
```

## Testing Checklist

- [ ] Database migration completed
- [ ] Product name appears in orders table
- [ ] Track order button works from My Purchases
- [ ] Timeline displays correct current status
- [ ] Can cancel pending orders
- [ ] Can mark shipped orders as received
- [ ] Order items display with images
- [ ] Order summary shows correct totals
- [ ] Back button returns to My Purchases
- [ ] Status badges show correct colors
- [ ] Mobile responsive layout works

## Troubleshooting

### Product name is NULL
Run this query to populate:
```sql
UPDATE orders o
SET product_name = (
    SELECT oi.product_name
    FROM order_items oi
    WHERE oi.order_id = o.id
    LIMIT 1
)
WHERE o.product_name IS NULL;
```

### Timeline not showing progress
Check order status matches one of the valid values in `getTrackingSteps()` function.

### Images not loading
Verify image paths in order_items table have correct format (relative or absolute URLs).

### Cannot cancel/update order
Check:
1. User is logged in
2. Order belongs to the user
3. Status transition is valid (e.g., can't cancel shipped orders)

## Future Enhancements

1. **Email Notifications** - Send emails when status changes
2. **SMS Tracking** - SMS updates for order status
3. **Estimated Delivery Date** - Calculate and display delivery estimates
4. **Tracking Number** - Add courier tracking number integration
5. **Real-time Updates** - WebSocket or polling for live status updates
6. **Order History Export** - Download order history as PDF/CSV
7. **Rating System** - Allow customers to rate completed orders
8. **Reorder Functionality** - One-click reorder from completed orders

## Support

For issues or questions:
- Email: support@farmersmall.com
- Phone: (555) 123-4567
