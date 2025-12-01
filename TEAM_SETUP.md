# 🚀 Quick Setup for Team Members

## Step 1: Clone/Pull the Project

```powershell
# If you don't have the project yet
git clone https://github.com/asda121-png/The-Farmers-Mall.git
cd The-Farmers-Mall

# If you already have it
git pull
```

## Step 2: Create Your .env File

```powershell
# Copy the example file to create your .env
Copy-Item config\.env.example config\.env
```

**That's it!** The `.env.example` already contains all the working credentials.

## Step 3: Test Your Connection

```powershell
php config/test-database.php
```

You should see:
```
🎉 ALL TESTS PASSED! 🎉
Your Supabase database is working perfectly!
```

## Step 4: Start Coding!

The database is already set up with all tables. You can start building features immediately.

---

## 📊 What's Already Done?

✅ Supabase project created  
✅ All database tables created (users, products, orders, retailers, etc.)  
✅ Connection configuration ready  
✅ No XAMPP or MySQL installation needed  

## 🔧 Important Files

- **`config/.env`** - Your local credentials (never commit this!)
- **`config/supabase-api.php`** - Database connection helper
- **`config/schema.sql`** - Database structure (already applied)

## 💻 How to Use the Database

### Example: Get All Users
```php
<?php
require_once __DIR__ . '/config/supabase-api.php';

$api = getSupabaseAPI();
$users = $api->select('users');
print_r($users);
```

### Example: Add New Product
```php
<?php
require_once __DIR__ . '/config/supabase-api.php';

$api = getSupabaseAPI();
$product = $api->insert('products', [
    'retailer_id' => 'some-uuid-here',
    'name' => 'Fresh Tomatoes',
    'description' => 'Organic tomatoes',
    'price' => 50.00,
    'stock_quantity' => 100,
    'category' => 'Vegetables'
]);
```

### Example: Update Product
```php
<?php
require_once __DIR__ . '/config/supabase-api.php';

$api = getSupabaseAPI();
$api->update('products', 
    ['stock_quantity' => 80],
    ['name' => 'Fresh Tomatoes']
);
```

### Example: Delete Product
```php
<?php
require_once __DIR__ . '/config/supabase-api.php';

$api = getSupabaseAPI();
$api->delete('products', ['name' => 'Fresh Tomatoes']);
```

## 🆘 Troubleshooting

### "config/.env file not found"
```powershell
Copy-Item config\.env.example config\.env
```

### "Call to undefined function curl_init()"
CURL extension needs to be enabled (already done on main setup, but if you get this error):
1. Find your `php.ini` file: `php --ini`
2. Edit it and uncomment: `extension=curl`
3. Add: `curl.cainfo = "C:\php\cacert.pem"`

### Connection test fails
- Check your internet connection
- Verify `.env` file exists in `config/` folder
- Make sure you copied it from `.env.example`

## 📚 Database Tables

All these tables are ready to use:

1. **users** - All user accounts (admin, retailer, customer)
2. **retailers** - Retailer/shop profiles
3. **products** - Product listings
4. **orders** - Customer orders
5. **order_items** - Order details
6. **reviews** - Product reviews
7. **messages** - User messaging
8. **notifications** - User notifications
9. **cart** - Shopping cart items

## 🔐 Security Notes

- ⚠️ **Never commit** the `config/.env` file to git (it's in `.gitignore`)
- ✅ The `.env.example` can be committed (credentials are for shared dev database)
- ✅ Use password hashing: `password_hash($password, PASSWORD_DEFAULT)`

## 🎯 Next Steps

1. Pull the latest code: `git pull`
2. Create `.env` file: `Copy-Item config\.env.example config\.env`
3. Test connection: `php config/test-database.php`
4. Start building your features!

---

**Questions?** Ask in the team chat or check `DATABASE_SETUP.md` for detailed information.
