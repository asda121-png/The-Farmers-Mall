# Cart Database Integration

## Overview
The shopping cart has been integrated with the Supabase database to persist cart data across sessions and sync between devices for logged-in users.

## Features Implemented

### 1. Database Cart Storage
- Cart items are now stored in the `cart` table in Supabase
- Each cart item is associated with a specific user (customer_id)
- Prevents duplicate entries (unique constraint on customer_id + product_id)
- Automatically updates quantity when adding the same product again

### 2. Cart API (`api/cart.php`)
The API supports full CRUD operations:

#### GET `/api/cart.php`
- Fetches all cart items for the logged-in user
- Returns product details along with cart information
- Response includes: product name, price, description, image, quantity, and stock

#### POST `/api/cart.php`
- Adds a new item to cart or updates quantity if item exists
- Required: `product_id`, `quantity`
- Automatically increments quantity for existing items

#### PUT `/api/cart.php`
- Updates the quantity of a cart item
- Required: `cart_id`, `quantity`
- Deletes item if quantity is set to 0 or less

#### DELETE `/api/cart.php`
- Removes specific item: provide `cart_id`
- Clears entire cart: provide `clear_all: true`

### 3. Updated JavaScript Files

#### `assets/js/cart.js`
- `loadCartFromDB()` - Loads cart items from database
- `addToCartDB()` - Adds items to database cart
- `updateCartItemDB()` - Updates item quantity
- `deleteCartItemDB()` - Removes single item
- `clearCartDB()` - Clears entire cart
- All operations update the UI automatically after database sync

#### `assets/js/products.js`
- Updated `addToCart()` to support both database products (with IDs) and fallback to localStorage
- Updated `updateCartIcon()` to fetch count from database with localStorage fallback
- Maintains backward compatibility with hardcoded products

#### `user/cart.php`
- Cart page now loads from database on page load
- Real-time sync with database for all cart operations

#### `user/user-homepage.php`
- Cart icon badge now pulls from database
- Updates in real-time when items are added

## Database Schema

### Cart Table
```sql
CREATE TABLE cart (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    customer_id UUID REFERENCES users(id) ON DELETE CASCADE,
    product_id UUID REFERENCES products(id) ON DELETE CASCADE,
    quantity INTEGER NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(customer_id, product_id)
);
```

## Setup Instructions

### 1. Ensure Database Tables Exist
Run the main schema if you haven't:
```bash
# In Supabase SQL Editor
Run: config/schema.sql
```

### 2. Add Sample Products (Optional)
```bash
# In Supabase SQL Editor
Run: config/sample_products.sql
```

Then update the retailer_id:
```sql
-- Get a retailer ID
SELECT id FROM users WHERE user_type = 'retailer' LIMIT 1;

-- Update products
UPDATE products SET retailer_id = 'YOUR-RETAILER-UUID' WHERE retailer_id IS NULL;
```

### 3. Test the Cart
1. Log in as a customer
2. Browse products and add items to cart
3. View cart page - items should persist
4. Log out and log back in - cart should still contain items
5. Try from different browser/device - cart syncs across devices

## How It Works

### Adding Items to Cart
1. User clicks "Add to Cart" button
2. JavaScript calls `addToCart()` function
3. If product has an ID, it sends POST request to `api/cart.php`
4. API checks if item already exists in cart
5. If exists: increments quantity
6. If new: creates new cart entry
7. Response triggers UI update

### Viewing Cart
1. Page loads and calls `loadCartFromDB()`
2. GET request to `api/cart.php`
3. API fetches cart items with product details
4. JavaScript renders cart items
5. Calculates totals (subtotal, tax, total)

### Updating Quantity
1. User clicks +/- buttons
2. JavaScript calls `updateCartItemDB(cart_id, new_quantity)`
3. PUT request to `api/cart.php`
4. Database updates quantity
5. Cart reloads from database

### Removing Items
1. User clicks trash icon
2. Confirmation modal appears
3. On confirm, calls `deleteCartItemDB(cart_id)`
4. DELETE request to `api/cart.php`
5. Item removed from database
6. Cart reloads

## Security Features
- All API endpoints require user authentication
- Session validation on every request
- User can only access/modify their own cart items
- SQL injection protection via prepared statements
- XSS protection via proper escaping

## Future Enhancements
1. Stock validation before adding to cart
2. Cart expiration (auto-clear old items)
3. Save for later functionality
4. Cart sharing between users
5. Price change notifications
6. Inventory updates when cart is modified

## Troubleshooting

### Cart Not Loading
- Check if user is logged in (session active)
- Verify database connection in `config/supabase-api.php`
- Check browser console for API errors

### Items Not Persisting
- Verify cart table exists in database
- Check user_id is set in session
- Ensure API endpoint is accessible (check server logs)

### Quantity Not Updating
- Check database permissions
- Verify cart_id is being passed correctly
- Look for JavaScript errors in console

## API Response Examples

### Successful GET Response
```json
{
  "success": true,
  "items": [
    {
      "cart_id": "uuid",
      "product_id": "uuid",
      "name": "Fresh Strawberries",
      "price": 89.99,
      "description": "Juicy and sweet...",
      "image": "../images/products/strawberry.png",
      "quantity": 2,
      "stock_quantity": 25
    }
  ]
}
```

### Successful POST Response
```json
{
  "success": true,
  "message": "Item added to cart"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Unauthorized"
}
```
