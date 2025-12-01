# 🚀 Quick Setup for Team Members

## Automated Setup (Recommended)

### Method 1: Double-Click Setup (Easiest)
1. Pull the latest code: `git pull`
2. Double-click **`setup.bat`** in the project folder
3. Wait for it to complete - Done! ✅

### Method 2: PowerShell Command
1. Pull the latest code: `git pull`
2. Run: `.\setup.ps1`
3. Done! ✅

### Method 3: Manual Setup
```powershell
# 1. Pull latest code
git pull

# 2. Create .env file
Copy-Item config\.env.example config\.env

# 3. Test connection
php config/test-database.php
```

You should see:
```
🎉 ALL TESTS PASSED! 🎉
Your Supabase database is working perfectly!
```

## What the Setup Script Does

✅ Creates `.env` file automatically  
✅ Checks PHP installation  
✅ Enables CURL extension if needed  
✅ Downloads SSL certificates if needed  
✅ Tests database connection  
✅ Shows clear error messages if something fails

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

### Setup script fails
1. Make sure you ran `git pull` first
2. Run PowerShell as Administrator
3. Try: `Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy Bypass`
4. Run `.\setup.ps1` again

### "config/.env file not found"
Run the setup script: `.\setup.ps1` or double-click `setup.bat`

### "Call to undefined function curl_init()"
The setup script will fix this automatically. If not:
1. Run setup script as Administrator
2. Or manually enable in php.ini: `extension=curl`

### Connection test fails
- Check your internet connection
- Make sure you're not behind a firewall blocking Supabase
- Try running `.\setup.ps1` again
- Contact team lead if still failing

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
