# Product Image Upload Fix - Troubleshooting Guide

## Problem Description

When you add products with images in the retailer dashboard, only letter placeholders appear instead of the actual product images, while other team members' uploads work correctly.

## Root Cause Analysis

The issue could be caused by several factors:

1. File upload errors (permissions, size limits, file types)
2. Image path not being saved to the database
3. Browser caching issues
4. Directory permission problems

## Fixes Applied

### 1. Enhanced Error Logging (`api/save-product.php`)

✅ **Added comprehensive upload debugging**

- Logs all file upload attempts with user ID
- Captures and reports specific upload errors
- Validates file types (jpg, jpeg, png, gif, webp)
- Checks directory existence and permissions
- Verifies file was actually saved after upload
- Returns upload errors/warnings to the frontend

### 2. Frontend Validation (`retailer/retaileraddnewproduct.php`)

✅ **Added client-side validation**

- File type validation (images only)
- File size validation (max 5MB)
- Console logging of file details
- Better error messages shown to users
- Warning display for upload issues

### 3. Product List Debugging (`retailer/retailerinventory.php`)

✅ **Added image display debugging**

- Console warnings for products missing images
- Cache-busting parameters to prevent stale images
- Better fallback to letter placeholders

### 4. Diagnostic Tools Created

#### Test Upload Page (`test-upload-debug.html`)

- Simple interface to test file uploads
- No complex form, just upload testing
- Shows detailed diagnostics

#### Upload Debug Script (`test-upload-debug.php`)

- Shows PHP configuration (upload_max_filesize, etc.)
- Tests directory permissions
- Verifies upload process step-by-step
- Returns detailed JSON diagnostic data

## How to Use the Diagnostic Tools

### Step 1: Check Your Upload Settings

1. Open your browser and go to: `http://localhost:3000/test-upload-debug.html`
2. Click "Run Diagnostics (No Upload)"
3. Review the output for:
   - `upload_dir_exists`: Should be `true`
   - `upload_dir_writable`: Should be `true`
   - `upload_max_filesize`: Should be at least `2M`
   - `post_max_size`: Should be at least `8M`

### Step 2: Test an Actual Upload

1. On the same page, click "Select Image to Test Upload"
2. Choose a product image (JPG, PNG, etc.)
3. Click "Test Upload"
4. Review the results:
   - Look for `"upload_result": "SUCCESS"`
   - Check `"file_exists_after_upload": true`
   - Verify `"file_size_after_upload"` is greater than 0

### Step 3: Try Adding a Product (with Enhanced Logging)

1. Go to the retailer dashboard: Products & Inventory → Add New Product
2. Open your browser's Developer Console (F12)
3. Fill in the product details
4. Select a product image
5. **Watch the console** - you'll see:

   ```
   Image selected: { name: "...", type: "...", size: ... }
   Form submission data:
     product_name: ...
     product_image: filename.jpg (image/jpeg, 12345 bytes)
   Sending request to save-product.php...
   Response status: 200
   Response data: { success: true, ... }
   ```

6. If there's an upload error, you'll see:
   - Error message in the toast notification
   - Console warning with details
   - `upload_warning` or `upload_error` in response

### Step 4: Check the Product List

1. After adding the product, go to Products & Inventory
2. Open Developer Console (F12)
3. Look for any console warnings:
   ```
   Product missing image_url: {id: "...", name: "...", image_url: null}
   ```
4. This tells you which products don't have images saved

## Common Issues and Solutions

### Issue 1: "File exceeds upload_max_filesize"

**Solution:** Your image is too large. Either:

- Compress the image before uploading
- Or ask your server admin to increase `upload_max_filesize` in php.ini

### Issue 2: "Upload directory is not writable"

**Solution:** Directory permissions issue

```bash
# On Windows (run as administrator)
icacls "c:\path\to\The-Farmers-Mall\assets\product" /grant Everyone:(OI)(CI)F

# Or manually: Right-click folder → Properties → Security → Edit → Add write permissions
```

### Issue 3: "Failed to move uploaded file"

**Solution:** Temporary directory issue

1. Check `upload_tmp_dir` in diagnostic results
2. Make sure that directory exists and is writable
3. May need to set `upload_tmp_dir` in php.ini

### Issue 4: Images upload but don't display

**Solution:** Path or cache issue

1. Check browser console for image load errors
2. Right-click the letter placeholder → Inspect
3. Look at the `src` attribute of the `<img>` tag
4. Verify the path is correct: `../assets/product/product_xxx.jpg`
5. Try accessing the image directly in browser: `http://localhost:3000/assets/product/product_xxx.jpg`

### Issue 5: Other users' uploads work but yours don't

**Possible causes:**

1. **Browser issue:** Try a different browser or incognito mode
2. **User permissions:** Your user account may have restrictions
3. **Session issue:** Try logging out and back in
4. **Antivirus/Security software:** May be blocking uploads

## Checking PHP Error Logs

The enhanced code now logs detailed information. To view PHP error logs:

**On Windows with XAMPP:**

- Check: `C:\xampp\php\logs\php_error_log`
- Or: `C:\xampp\apache\logs\error.log`

**Look for entries like:**

```
[13-Dec-2025 10:30:00] Upload attempt - Files: {...}
[13-Dec-2025 10:30:00] Image uploaded successfully for user 123: assets/product/product_xxx.jpg
[13-Dec-2025 10:30:00] Setting image_url in product data: assets/product/product_xxx.jpg
```

**If upload failed:**

```
[13-Dec-2025 10:30:00] Upload error for user 123: Failed to write file to disk
[13-Dec-2025 10:30:00] Failed to move uploaded file for user 123 from C:\tmp\php1234.tmp to ...
```

## Testing Your Fix

1. **Add a new product with image**

   - Use a small test image (< 1MB)
   - Fill in all required fields
   - Upload the image
   - Submit the form

2. **Verify the image displays**

   - Go to Products & Inventory
   - Find your new product
   - Confirm the actual image shows (not just a letter)

3. **Check the file was saved**
   - Open: `The-Farmers-Mall\assets\product\`
   - Look for the newest file (starts with `product_`)
   - Verify it's your image

## Additional Tips

1. **Clear browser cache** if you see old images:

   - Press `Ctrl + Shift + R` to force refresh
   - Or clear browser cache completely

2. **Use smaller images**:

   - Recommended: Under 500KB
   - Maximum: 5MB (enforced by new validation)

3. **Use supported formats**:

   - JPG/JPEG ✅
   - PNG ✅
   - GIF ✅
   - WebP ✅
   - Other formats ❌

4. **Check image file names**:
   - Avoid special characters
   - Spaces are OK but simple names are better

## Need More Help?

If the issue persists:

1. Run the diagnostic tool and save the output
2. Check PHP error logs
3. Try uploading with the test page first
4. Check browser console for any JavaScript errors
5. Verify your product in the database has `image_url` filled in

## Database Check

If you want to verify the database directly, you can check if the `image_url` is being saved:

```sql
-- Check your latest products
SELECT id, name, image_url, created_at, retailer_id
FROM products
WHERE retailer_id = YOUR_RETAILER_ID
ORDER BY created_at DESC
LIMIT 10;
```

The `image_url` column should contain values like: `assets/product/product_675b1234abcd5.jpg`

If it's `NULL` or empty, then the upload is failing.

---

## Summary

The fixes include:
✅ Comprehensive error logging
✅ File validation (type and size)
✅ Better user feedback
✅ Diagnostic tools
✅ Console logging for debugging
✅ Cache-busting for images

These changes will help identify exactly why your uploads aren't working while others' are.
