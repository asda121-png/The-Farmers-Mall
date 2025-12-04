# Email Verification System Setup for Collaborators

## ✅ What's Working Now

After `git pull`, the email verification system will:
1. ✅ Generate random 6-digit OTP codes
2. ✅ Store codes in PHP session (valid for 5 minutes)
3. ✅ Send real emails via Gmail SMTP (if configured)
4. ✅ **Fallback mode**: Show code on screen if email sending fails
5. ✅ Validate codes during registration
6. ✅ Work across all collaborators automatically

## 🚀 Quick Start for Collaborators

### Step 1: After Git Pull
```bash
git pull origin main
```

### Step 2: Verify Files Exist
Make sure these files are present:
- ✅ `config/.env` (has database credentials)
- ✅ `auth/register.php` (registration form)
- ✅ `auth/verify-email.php` (OTP endpoint)
- ✅ `includes/mailer.php` (email sender)

### Step 3: Start WAMP/PHP
- Start your WAMP server
- Open: `http://localhost/The-Farmers-Mall/auth/register.php`

### Step 4: Test Registration

1. **Fill in Steps 1-3:**
   - Step 1: Personal Info (First name, Last name)
   - Step 2: Address (Street, Barangay)
   - Step 3: Account Details (Username, Phone, Password)

2. **Step 4 - Verification:**
   - Enter your email
   - Click "Send Verification Code"
   - **You will see the code displayed on screen** (for development)
   - OR check your email inbox for the code
   - Enter the code in the form
   - Click "Next"

3. **Step 5 - Complete:**
   - Check the terms
   - Click "Sign Up"
   - Registration complete! ✅

## 🔍 How the System Works

### Flow Diagram
```
User enters email
    ↓
Clicks "Send Verification Code"
    ↓
PHP generates 6-digit code (e.g., 123456)
    ↓
Code stored in session ($_SESSION['verification_code'])
    ↓
Attempts to send via Gmail SMTP
    ├─ SUCCESS: Email sent ✅
    └─ FAILURE: Code shown on screen for testing
    ↓
User sees code (from email or screen)
    ↓
User enters code in form
    ↓
Clicks "Next"
    ↓
Server validates code matches session
    ├─ MATCH: Proceed to Step 5 ✓
    └─ NO MATCH: Show error, ask to try again
```

### Session Storage
When verification code is sent:
```php
$_SESSION['verification_code'] = '123456';      // The code
$_SESSION['code_email'] = 'user@email.com';     // Email used
$_SESSION['code_expires'] = time() + 300;       // Expires in 5 min
```

## 📧 Email Configuration

### Gmail SMTP Settings (Already Configured)
- **Host:** smtp.gmail.com
- **Port:** 587
- **Security:** TLS
- **From Email:** mati.farmersmall@gmail.com
- **Password:** (app password - configured in `includes/mailer.php`)

### Email Content
- **Subject:** "Farmers Mall - Email Verification Code"
- **HTML:** Green-themed with verification code displayed prominently
- **Includes:** 5-minute expiration notice and security warning

## 🧪 Testing & Debugging

### Option 1: Check Session (Development)
1. Go to: `http://localhost/The-Farmers-Mall/auth/verify-debug.php`
2. Shows current verification code in session
3. Useful if you didn't receive email

### Option 2: View Debug Logs
After clicking "Send Verification Code":
- **`debug_email.log`** - Shows SMTP connection and email sending
- **`debug_verification.log`** - Shows OTP generation and process flow

### Option 3: Test with Test Page
1. Edit: `test-email.php` (change `your-email@gmail.com` to your email)
2. Open: `http://localhost/The-Farmers-Mall/test-email.php`
3. Should receive test email immediately

## ⚙️ Configuration Files (Already Set Up)

### `.env` (Database Credentials)
```ini
SUPABASE_DB_HOST=db.spoawcnjvukrpjswclnn.supabase.co
SUPABASE_DB_PORT=6543
SUPABASE_DB_NAME=postgres
SUPABASE_DB_USER=postgres.spoawcnjvukrpjswclnn
SUPABASE_DB_PASSWORD=FArMeRs_Mall123
```

### `config/.env.example`
- Template file (for reference)
- `.env` is created from this
- `.env` is in `.gitignore` (never committed)

## 🔐 Security Features

✅ **Server-side validation** - Code verified on server, not client  
✅ **Session-based storage** - Code tied to user session  
✅ **5-minute expiration** - Codes auto-expire  
✅ **Email matching** - Code must match the email that requested it  
✅ **No exposed codes** - Actual code not shown in HTML (except dev mode)  
✅ **Password hashing** - User passwords hashed before storage  
✅ **Input sanitization** - All inputs sanitized before use  

## ❌ Troubleshooting

### Problem: "Next button doesn't work"
**Solution:**
1. Make sure you entered a valid 4-6 digit code
2. Check `verify-debug.php` to see what code is in session
3. Verify code hasn't expired (5 minutes limit)
4. Enter the exact code shown in `verify-debug.php`

### Problem: Email not received
**Solution:**
1. Check inbox (including spam/promotions folder)
2. Go to `verify-debug.php` to see code
3. Check `debug_email.log` for SMTP errors
4. Use code from `verify-debug.php` instead

### Problem: "Verification code does not match"
**Solution:**
1. Go to `verify-debug.php`
2. Copy the exact code shown
3. Paste it in the verification field
4. Make sure you enter it correctly (no extra spaces)

### Problem: "Email validation passed but code still won't work"
**Possible issues:**
1. Session not persisting - restart browser
2. Code expired - resend verification code
3. Using different email - make sure email matches

## 📝 For New Collaborators

When a new person joins:
1. They clone/pull the repo
2. `.env` file already exists (created by someone else)
3. They run `http://localhost/The-Farmers-Mall/auth/register.php`
4. System works automatically ✅
5. No additional setup needed!

## 🚨 Important Notes

⚠️ **Do NOT:**
- Modify `verify-email.php` without testing
- Change the email credentials
- Commit `.env` to git
- Share the Gmail password

✅ **DO:**
- Test registration before pushing
- Check debug logs if issues occur
- Report any verification errors
- Keep `.env` confidential

## 📞 Getting Help

**If verification fails:**
1. Visit: `http://localhost/The-Farmers-Mall/auth/verify-debug.php`
2. Check the session data shown
3. Look at debug log files
4. Verify email is correct
5. Contact team with error details

**Debug Files to Check:**
- `debug_email.log` - Email sending status
- `debug_verification.log` - Code generation status
- Browser console (F12) - JavaScript errors

---

**System Status:** ✅ READY TO USE  
**Last Updated:** December 5, 2025  
**For Team:** All collaborators
