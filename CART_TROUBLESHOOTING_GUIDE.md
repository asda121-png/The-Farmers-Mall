# Cart Troubleshooting Quick Reference

## 🚀 Quick Start

### If Cart Items Don't Show:
1. Open browser console (F12)
2. Navigate to cart page
3. Look for these logs:
   - 🚀 Initializing cart page...
   - 🔄 Loading cart from database...
   - ✅ Cart items loaded successfully: X items
   - 🛒 Cart contents: [...]

### If Badge Not Showing:
1. Check if items exist: Open `test-cart-api.php`
2. Click "Fetch Cart Items"
3. Verify API returns items
4. Check console for badge creation logs

## 📋 Console Log Guide

| Icon | Meaning | Action |
|------|---------|--------|
| 🚀 | Initialization | Normal startup |
| 🔄 | Loading data | Wait for response |
| ✅ | Success | All good! |
| ❌ | Error | Check error message |
| 📦 | Item details | Review item data |
| 🛒 | Cart contents | Full cart data |
| 📡 | Network | API response status |
| 💥 | Critical error | Check network/API |

## 🔧 Debug Tools

### 1. Cart API Debugger
**File:** `test-cart-api.php`
**URL:** `http://localhost:3000/test-cart-api.php`
**Use for:**
- Testing API connectivity
- Viewing session data
- Adding test items
- Checking raw responses

### 2. Badge Visual Tester
**File:** `test-cart-badge-visual.html`
**Use for:**
- Comparing old vs new badges
- Verifying uniform sizing
- Visual quality check

### 3. Browser Console
**Key Commands:**
```javascript
// Check cart API
fetch('../api/cart.php').then(r => r.json()).then(console.log)

// Check session
fetch('../api/cart.php').then(r => r.text()).then(console.log)

// Reload cart
location.reload()
```

## 🎯 Common Issues & Solutions

### Issue: "Cart is empty" but badge shows count
**Possible Causes:**
- Customer name mismatch
- API not returning items
- JavaScript rendering error

**Solutions:**
1. Check console for errors
2. Use test-cart-api.php to verify data
3. Check network tab for API response
4. Verify customer_name in database

### Issue: Badge size inconsistent
**Solution:** Already fixed! Badge should be uniform:
- Size: 18px x 18px (1.125rem)
- Color: Red-600
- Position: -top-2 -right-2

### Issue: Badge not updating
**Solutions:**
1. Hard refresh (Ctrl+F5)
2. Clear cache
3. Check console for update logs
4. Verify API is returning correct count

### Issue: Items added but don't appear
**Immediate Checks:**
1. Console errors?
2. Network tab shows 200 OK?
3. API response includes item?
4. Page auto-refreshed?

**Solutions:**
1. Wait 2 seconds (auto-refresh interval)
2. Manual refresh page
3. Check if user is logged in
4. Verify database entry created

## 📊 Expected Behavior

### Adding Item to Cart:
```
User clicks "Add to Cart" →
  API POST request →
  Database insert/update →
  Success response →
  Cart reloads →
  Badge updates →
  Notification shows
```

### Loading Cart Page:
```
Page loads →
  🚀 Initialize →
  🔄 Fetch from API →
  📡 Response received →
  ✅ Items parsed →
  🎨 Render items →
  Update totals →
  Update badge
```

### Auto-Refresh (every 2 seconds):
```
Interval fires →
  Fetch cart count →
  Compare with last count →
  If changed: reload cart →
  Update display
```

## 🔍 Verification Checklist

Before reporting issue as fixed:
- [ ] Cart items display correctly
- [ ] Badge shows correct count
- [ ] Badge is uniform size on all pages
- [ ] Adding items updates cart
- [ ] Removing items updates cart
- [ ] Quantities can be changed
- [ ] Totals calculate correctly
- [ ] Console shows proper logs
- [ ] No JavaScript errors
- [ ] API responds with 200 OK

## 📁 Files to Check

**If cart display broken:**
- `assets/js/cart.js` - Main cart logic
- `api/cart.php` - Backend API
- `user/cart.php` - Cart page

**If badge broken:**
- Check the page's inline `<script>` tag
- Or check corresponding JS file:
  - products.php → products.js
  - productdetails.php → productdetails.js
  - cart.php → cart.js

**If database issues:**
- Check `config/database.php`
- Check `config/supabase-api.php`
- Verify cart table exists
- Verify customer_name column exists

## 💾 Database Queries

**Check cart for user:**
```sql
SELECT * FROM cart WHERE customer_name = 'User Full Name';
```

**Check all cart items:**
```sql
SELECT c.*, p.name, p.price 
FROM cart c 
LEFT JOIN products p ON c.product_id = p.id;
```

**Clear test cart:**
```sql
DELETE FROM cart WHERE customer_name = 'Test User';
```

## 📞 Support Contact Points

1. **Browser Console** - First stop for any issue
2. **test-cart-api.php** - For API/database issues
3. **Network Tab** - For request/response issues
4. **Database** - For data verification

---

**Last Updated:** December 5, 2025
**Status:** All fixes applied and tested
