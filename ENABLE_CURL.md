# How to Enable cURL Extension in XAMPP

If you encounter the error: `Fatal error: Call to undefined function curl_init()`, follow these steps:

## Method 1: Via XAMPP Control Panel (Easiest)

1. **Open XAMPP Control Panel**
2. **Click "Config" button** next to Apache
3. **Select "PHP (php.ini)"**
4. **Find this line** (use Ctrl+F to search):
   ```
   ;extension=curl
   ```
5. **Remove the semicolon** to uncomment it:
   ```
   extension=curl
   ```
6. **Save the file** (Ctrl+S)
7. **Stop Apache** in XAMPP Control Panel
8. **Start Apache** again
9. **Refresh your browser**

## Method 2: Manual Edit (If Method 1 doesn't work)

### For newer XAMPP versions:
1. Navigate to: `C:\xampp\php\php.ini`
2. Open with Notepad or any text editor
3. Search for: `;extension=curl`
4. Change to: `extension=curl`
5. Save and restart Apache

### For older XAMPP versions:
1. Navigate to: `C:\xampp\php\php.ini`
2. Search for: `;extension=php_curl.dll`
3. Change to: `extension=php_curl.dll`
4. Save and restart Apache

## Verify cURL is Enabled

Create a file called `check-curl.php` with this content:
```php
<?php
if (function_exists('curl_init')) {
    echo "✅ cURL is enabled!";
} else {
    echo "❌ cURL is NOT enabled!";
}
phpinfo();
?>
```

Access it via: `http://localhost:3000/check-curl.php`

## Troubleshooting

### Issue: Still not working after following steps
**Solution:** 
- Make sure you're editing the correct `php.ini` file
- Check which php.ini is loaded by running: `php --ini` in terminal
- Or check via `phpinfo()` to see "Loaded Configuration File"

### Issue: Can't find the line
**Solution:**
- Press Ctrl+F in your text editor
- Search for: `curl`
- Look for lines with `extension=curl` or `extension=php_curl.dll`

### Issue: Apache won't restart
**Solution:**
- Check Apache error logs in XAMPP Control Panel
- Make sure you didn't introduce syntax errors in php.ini
- Restore from backup if needed: `php.ini` has backups like `php.ini.bak`

## Why is cURL Required?

This project uses **Supabase API** which requires cURL to make HTTP requests to the database. Without cURL enabled, the application cannot:
- Connect to the database
- Login/Register users
- Fetch products
- Add items to cart
- Process orders

## Team Members: Quick Setup

**Share this with your team:**
1. Everyone needs to enable cURL in their XAMPP installation
2. Follow Method 1 above (it takes less than 2 minutes)
3. Restart Apache
4. Test the application

---

**Need help?** Contact the project maintainer or check XAMPP documentation at: https://www.apachefriends.org/
