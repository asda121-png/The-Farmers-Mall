# 🌾 The Farmers Mall

An e-commerce platform connecting farmers/retailers with customers.

## 🚀 Quick Start for Team Members

### ⚠️ Getting an Error? Check Your System First!
Visit: **`http://localhost:3000/system-check.php`** to diagnose issues.

Common error: `Call to undefined function curl_init()` → See [ENABLE_CURL.md](ENABLE_CURL.md)

---

### Option 1: Automated Setup (Recommended) 🎯
```powershell
git pull
.\setup.ps1
```
Or just double-click **`setup.bat`** ✨

### Option 2: Manual Setup
```powershell
git pull
Copy-Item config\.env.example config\.env
php config/test-database.php
```

✅ You should see "ALL TESTS PASSED!" - you're ready to code!

## 📖 Documentation

- **[TEAM_SETUP.md](TEAM_SETUP.md)** - Quick setup guide with code examples
- **[ENABLE_CURL.md](ENABLE_CURL.md)** - How to enable cURL extension (REQUIRED!)
- **[DATABASE_SETUP.md](DATABASE_SETUP.md)** - Detailed database documentation
- **[CONVERSION_SUMMARY.md](CONVERSION_SUMMARY.md)** - Project conversion notes

## 🗄️ Database

We use **Supabase** (cloud PostgreSQL) - no XAMPP or local MySQL needed!

**Why?** Everyone accesses the same database instantly. Just `git pull` and you're synced.

### Available Tables
- `users` - User accounts (admin, retailer, customer)
- `retailers` - Shop/seller profiles  
- `products` - Product catalog
- `orders` & `order_items` - Order management
- `reviews` - Product ratings
- `messages` - User messaging
- `notifications` - Alerts
- `cart` - Shopping cart

## 💻 Using the Database

```php
<?php
require_once __DIR__ . '/config/supabase-api.php';
$api = getSupabaseAPI();

// Select
$users = $api->select('users');

// Insert
$api->insert('products', ['name' => 'Tomatoes', 'price' => 50.00]);

// Update
$api->update('products', ['price' => 45.00], ['name' => 'Tomatoes']);

// Delete
$api->delete('products', ['name' => 'Tomatoes']);
```

See **[TEAM_SETUP.md](TEAM_SETUP.md)** for more examples.

## 📁 Project Structure

```
├── admin/          # Admin dashboard pages
├── retailer/       # Retailer/seller pages
├── user/           # Customer pages
├── auth/           # Login/register
├── assets/         # CSS, JS
├── config/         # Database configuration
│   ├── .env        # Your credentials (don't commit!)
│   ├── supabase-api.php
│   └── test-database.php
├── includes/       # Headers, footers
└── public/         # Landing pages
```

## 🛠️ Tech Stack

- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP
- **Database**: Supabase (PostgreSQL)
- **Version Control**: Git

## 🤝 Team Workflow

1. `git pull` - Get latest changes
2. Create your feature/fix
3. Test locally (everyone uses same database)
4. `git add` + `git commit` + `git push`
5. Done! ✨

## 🔐 Security

- ❌ Never commit `config/.env` (it's in `.gitignore`)
- ✅ Always hash passwords: `password_hash($pass, PASSWORD_DEFAULT)`
- ✅ Use prepared statements (handled by `supabase-api.php`)

## 💡 Need Help?

1. Check **[TEAM_SETUP.md](TEAM_SETUP.md)** for common issues
2. Run `php config/test-database.php` to verify your setup
3. Ask in the team chat

---

**Ready to contribute?** Just pull, copy `.env.example` to `.env`, and start coding! 🚀
