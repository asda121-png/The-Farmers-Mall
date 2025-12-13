# Quick Fix Guide - Product Images Not Showing

## 🚀 Quick Test (Do this first!)

1. **Open diagnostic page:** `http://localhost:3000/test-upload-debug.html`
2. **Click:** "Run Diagnostics"
3. **Look for:**

   - ✅ `upload_dir_writable: true`
   - ✅ `upload_max_filesize: "2M"` or higher

4. **Click:** "Select Image" → Choose a product photo → "Test Upload"
5. **Look for:** `"upload_result": "SUCCESS"`

## 🔧 If Test Fails

### Error: "Upload directory is not writable"

```powershell
# Run PowerShell as Administrator
$path = "C:\Users\Jayson Bustamante\Downloads\The-Farmers-Mall\assets\product"
icacls $path /grant Everyone:(OI)(CI)F
```

### Error: "File exceeds upload_max_filesize"

→ Use a smaller image (compress it) or increase PHP upload limit

### Error: "Failed to move uploaded file"

→ Check temp directory permissions (shown in diagnostics)

## 📸 Adding Products with Images

1. Go to **Products & Inventory** → **Add New Product**
2. **Open Developer Console** (Press F12)
3. Fill in product details
4. **Select an image** (watch console for file info)
5. Click **Publish**
6. **Check console** for any errors or warnings

## 🔍 What the Console Shows

### ✅ Successful Upload:

```
Image selected: {name: "apple.jpg", size: 45678}
Uploading image: {name: "apple.jpg", type: "image/jpeg", size: 45678}
Response data: {success: true, message: "Product created successfully"}
```

### ❌ Failed Upload:

```
Response data: {
  success: false,
  message: "Failed to create product",
  upload_error: "Upload directory is not writable"
}
```

## 📋 Checklist

- [ ] Diagnostic test passes
- [ ] Console shows no errors
- [ ] Image file size < 5MB
- [ ] Image is JPG, PNG, GIF, or WebP
- [ ] Product appears in inventory with image (not just letter)

## 🆘 Still Not Working?

1. **Check PHP error logs:**

   - Windows/XAMPP: `C:\xampp\php\logs\php_error_log`
   - Look for recent errors with your user ID

2. **Try different browser** or incognito mode

3. **Check if file was created:**

   - Look in: `The-Farmers-Mall\assets\product\`
   - Should see `product_xxxxx.jpg` with today's date

4. **Verify in browser:**
   - After adding product, right-click the letter placeholder
   - Click "Inspect"
   - Look at `<img src="...">`
   - Copy the src URL and try opening it directly in browser

## 💡 Pro Tips

- Use images smaller than 500KB for faster uploads
- Avoid special characters in filenames
- Clear browser cache if you see old images (Ctrl + Shift + R)
- Check console after EVERY upload attempt

---

## Files Changed (for reference)

- ✏️ `api/save-product.php` - Enhanced error logging
- ✏️ `retailer/retaileraddnewproduct.php` - Added validation & console logging
- ✏️ `retailer/retailerinventory.php` - Added debug warnings
- ➕ `test-upload-debug.html` - Diagnostic interface
- ➕ `test-upload-debug.php` - Diagnostic backend
- 📄 `PRODUCT_IMAGE_UPLOAD_FIX.md` - Full documentation
