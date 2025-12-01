# Database Migration: Add Username Column

## ⚠️ IMPORTANT: Run This Migration First!

Before the updated registration and login will work, you need to add the `username` column to the Supabase database.

## Steps to Run Migration:

### 1. Go to Supabase SQL Editor
1. Open your browser and go to: https://supabase.com/dashboard
2. Select your project: **spoawcnjvukrpjswclnn**
3. Click on **SQL Editor** in the left sidebar
4. Click **New Query**

### 2. Copy and Run the SQL
1. Open the file: `config/MIGRATION_ADD_USERNAME.sql`
2. Copy ALL the SQL code
3. Paste it into the Supabase SQL Editor
4. Click **Run** (or press Ctrl+Enter)

### 3. Verify Results
You should see a success message and a table showing all users with their new usernames.

The migration will:
- ✅ Add `username` column to users table
- ✅ Generate usernames for existing users (from email)
- ✅ Handle duplicate usernames
- ✅ Create admin user (email: Admin1234@gmail.com, username: admin, password: Admin123)
- ✅ Add unique constraint on username

### 4. Test After Migration
After running the SQL migration, you can test:

```powershell
# View all users with usernames
php config/view-users.php

# Test registration with username
# Go to: http://localhost:8000/auth/register.php

# Test login with username or email
# Go to: http://localhost:8000/auth/login.php
```

## Troubleshooting

If you get an error about "column already exists", it means the migration was already run. You can skip it.

If you need to re-run the migration:
1. The script is safe to run multiple times
2. It uses `IF NOT EXISTS` and checks before inserting

## After Migration

All team members need to:
1. Run `git pull` to get the updated code
2. The database changes are already done (no local migration needed)
3. New registrations will include username automatically
