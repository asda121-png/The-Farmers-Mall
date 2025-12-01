# Supabase Database Setup Guide

## Why Supabase?

We're using Supabase instead of XAMPP/Workbench because:
- ✅ Everyone accesses the same cloud database
- ✅ No need to install MySQL locally
- ✅ Just `git pull` and you're ready to work
- ✅ Automatic backups and management
- ✅ Free tier with generous limits

## Step-by-Step Setup

### 1. Create Supabase Project (One person does this)

1. Go to [https://supabase.com](https://supabase.com)
2. Sign up for a free account
3. Click "New Project"
4. Fill in:
   - **Project Name**: farmers-mall (or any name)
   - **Database Password**: Create a strong password (SAVE THIS!)
   - **Region**: Choose closest to your team
5. Wait 2-3 minutes for project creation

### 2. Get Database Credentials

1. In your Supabase project dashboard
2. Go to **Project Settings** (gear icon) > **Database**
3. Scroll to **Connection String** section
4. Copy the following information:
   - Host (e.g., `db.xxxxxxxxxxxxx.supabase.co`)
   - Database name (usually `postgres`)
   - User (usually `postgres`)
   - Password (the one you created)
   - Port (usually `5432`)

### 3. Create Database Schema

1. In Supabase dashboard, go to **SQL Editor**
2. Click **New Query**
3. Open the file `config/schema.sql` from this project
4. Copy and paste the entire content into Supabase SQL Editor
5. Click **Run** to create all tables
6. You should see "Success" messages

### 4. Setup Environment File (Each team member)

1. Copy the example environment file:
   ```powershell
   Copy-Item config\.env.example config\.env
   ```

2. Open `config/.env` and fill in the credentials from Step 2:
   ```env
   SUPABASE_DB_HOST=db.xxxxxxxxxxxxx.supabase.co
   SUPABASE_DB_PORT=5432
   SUPABASE_DB_NAME=postgres
   SUPABASE_DB_USER=postgres
   SUPABASE_DB_PASSWORD=your-actual-password
   ```

3. **IMPORTANT**: Never commit `.env` to git (it's already in `.gitignore`)

### 5. Share Credentials with Team

**Option A - Secure (Recommended)**:
- Share credentials via private chat/email/Discord
- Each person creates their own `config/.env` file locally
- The `.env` file is never committed to git

**Option B - Less Secure but Convenient**:
- One person creates `config/.env` with real credentials
- Share the file directly with team members
- Each person places it in their local `config/` folder

### 6. Test Connection

Create a test file `config/test-connection.php`:

```php
<?php
require_once __DIR__ . '/database.php';

try {
    $db = getDB();
    echo "✅ Connected to Supabase successfully!\n";
    
    // Test query
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "📊 Users table exists. Count: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
```

Run it:
```powershell
php config/test-connection.php
```

## Using the Database in Your Code

### Example 1: Simple Query

```php
<?php
require_once __DIR__ . '/../config/database.php';

// Get database connection
$db = getDB();

// Select all users
$stmt = $db->query("SELECT * FROM users");
$users = $stmt->fetchAll();
```

### Example 2: Prepared Statement (Safer)

```php
<?php
require_once __DIR__ . '/../config/database.php';

$db = getDB();

// Insert new user
$stmt = $db->prepare("
    INSERT INTO users (email, password_hash, full_name, user_type) 
    VALUES (:email, :password, :name, :type)
");

$stmt->execute([
    ':email' => 'user@example.com',
    ':password' => password_hash('password123', PASSWORD_DEFAULT),
    ':name' => 'John Doe',
    ':type' => 'customer'
]);
```

### Example 3: Fetch Single Row

```php
<?php
require_once __DIR__ . '/../config/database.php';

$db = getDB();

$stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute([':email' => 'user@example.com']);
$user = $stmt->fetch();

if ($user) {
    echo "Welcome, " . $user['full_name'];
}
```

## Common Issues & Solutions

### Issue: "Database connection failed"
- ✅ Check your `.env` file has correct credentials
- ✅ Make sure `config/.env` exists (not just `.env.example`)
- ✅ Verify Supabase project is running (check dashboard)

### Issue: "Table doesn't exist"
- ✅ Run the `schema.sql` in Supabase SQL Editor
- ✅ Check you're connected to the right database

### Issue: "could not find driver"
- ✅ Install PHP PostgreSQL extension: `php-pgsql`
- ✅ For Windows: Enable `extension=pgsql` in `php.ini`

## Team Workflow

1. **One person** sets up Supabase project and runs schema
2. **One person** shares credentials with team
3. **Everyone** pulls code and creates their local `.env` file
4. **Everyone** can now work with the same database
5. When schema changes, run SQL in Supabase (everyone sees it instantly)

## Database Management

### View Data
- Go to Supabase dashboard > **Table Editor**
- Browse, edit, delete data visually

### Backup
- Supabase automatically backs up your database
- Manual backup: **Database** > **Backups**

### Monitor
- **Database** > **Query Performance**
- See slow queries and optimize

## Security Notes

- ⚠️ Never commit `.env` file to git
- ✅ Use prepared statements to prevent SQL injection
- ✅ Hash passwords with `password_hash()`
- ✅ Enable Row Level Security in Supabase for production

## Next Steps

1. ✅ Complete this setup guide
2. ✅ Update existing PHP files to use new database connection
3. ✅ Test all features with Supabase
4. ✅ Replace any old MySQL code with PostgreSQL syntax if needed

---

**Need Help?** Check Supabase docs: https://supabase.com/docs
