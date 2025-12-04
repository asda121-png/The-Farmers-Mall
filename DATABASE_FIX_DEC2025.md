# 🔧 Database Connection Fix - December 2025

## ⚠️ CRITICAL: All Team Members Must Update

Supabase changed the database pooler endpoint. The old host no longer works.

---

## 🚨 Problem

**Old Host (BROKEN):**
```
db.spoawcnjvukrpjswclnn.supabase.co
```

**Error Message:**
```
SQLSTATE[08006] [7] could not translate host name to address: Name or service not known
```

---

## ✅ Solution

**New Host (WORKING):**
```
aws-1-ap-southeast-2.pooler.supabase.com
```

---

## 📋 Steps for All Team Members

### Step 1: Pull Latest Changes

```powershell
git pull
```

This will update:
- ✅ `config/.env` (actual credentials)
- ✅ `config/.env.example` (template)
- ✅ `config/supabase-api.php` (now uses service key for better reliability)

### Step 2: Verify Connection

Test your database connection:

```powershell
php config\test-connection.php
```

**Expected Output:**
```
✅ Successfully connected to Supabase!
📊 PostgreSQL Version: PostgreSQL 17.6...
📋 Tables in database: [list of 9 tables]
👥 Users table: 27 records
✅ All tests passed!
```

### Step 3: Verify Users

```powershell
php config\view-users.php
```

You should see all 27 registered users.

---

## 🔍 What Changed

### 1. Database Host Updated
- **File:** `config/.env` and `config/.env.example`
- **Change:** Host changed from `db.spoawcnjvukrpjswclnn.supabase.co` to `aws-1-ap-southeast-2.pooler.supabase.com`

### 2. Enhanced API Client
- **File:** `config/supabase-api.php`
- **Change:** Now automatically uses `SUPABASE_SERVICE_KEY` when available (bypasses RLS restrictions)
- **Benefit:** More reliable for server-side operations like registration, updates, deletes

---

## 📊 Connection Details

### Direct PostgreSQL Connection (Primary)
```
Host:     aws-1-ap-southeast-2.pooler.supabase.com
Port:     6543
Database: postgres
User:     postgres.spoawcnjvukrpjswclnn
Password: FArMeRs_Mall123
```

**Connection String:**
```
postgresql://postgres.spoawcnjvukrpjswclnn:FArMeRs_Mall123@aws-1-ap-southeast-2.pooler.supabase.com:6543/postgres
```

### REST API (Fallback)
```
URL:          https://spoawcnjvukrpjswclnn.supabase.co
Anon Key:     eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
Service Key:  eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9... (for server-side only)
```

---

## 🛠️ Troubleshooting

### Problem: "could not translate host name"

**Solution:** You're using the old host. Run `git pull` to get the updated files.

### Problem: "Row Level Security" or "permission denied"

**Solution:** The updated `config/supabase-api.php` now uses the service key automatically. Make sure you pulled the latest code.

### Problem: Still can't connect

**Check these:**

1. **PHP Extensions:** Ensure PostgreSQL extensions are installed
   ```powershell
   php -m | Select-String -Pattern "pgsql|pdo_pgsql"
   ```
   Should show: `pdo_pgsql` and `pgsql`

2. **Internet Connection:** Test connectivity
   ```powershell
   Test-NetConnection aws-1-ap-southeast-2.pooler.supabase.com -Port 6543
   ```

3. **Firewall:** Ensure port 6543 is not blocked

4. **DNS:** Try using Google DNS (8.8.8.8) if your ISP DNS is blocking Supabase

---

## 🎯 Testing Registration & Login

### Test User Registration
1. Start PHP server:
   ```powershell
   php -S localhost:8000
   ```

2. Open browser: `http://localhost:8000/auth/register.php`

3. Register a test account

4. Verify in database:
   ```powershell
   php config\view-users.php
   ```

### Test Login
1. Open: `http://localhost:8000/auth/login.php`
2. Login with your credentials
3. Should redirect to user homepage

---

## 📝 For Server Deployment (Production)

When deploying to a production server:

1. **Copy `.env.example` to `.env`**
2. **Update credentials** (same as above, or create new Supabase project)
3. **Never commit `.env`** to git (it's in `.gitignore`)
4. **Run migrations** in Supabase SQL Editor:
   - `config/schema.sql` (if fresh database)
   - `config/MIGRATION_ADD_USERNAME.sql`
   - `config/DISABLE_RLS.sql` (for development) or create proper RLS policies (for production)

---

## ✅ Verification Checklist

- [ ] Ran `git pull`
- [ ] Tested `php config\test-connection.php` (success)
- [ ] Tested `php config\view-users.php` (shows 27 users)
- [ ] Tested registration on local server (works)
- [ ] Tested login (works)

---

## 🆘 Still Having Issues?

1. Check the debug log: `auth/registration_debug.log`
2. Check PHP error log: Look for any PHP warnings/errors
3. Verify your `.env` file matches `.env.example` structure
4. Share error messages with the team

---

## 📅 Updated: December 5, 2025

**Status:** ✅ Fixed and Tested
**Action:** All team members must run `git pull` to get the fix
