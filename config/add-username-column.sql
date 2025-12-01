-- Migration: Add username column to users table
-- Run this in Supabase SQL Editor

-- Step 1: Add username column (nullable first)
ALTER TABLE users ADD COLUMN IF NOT EXISTS username VARCHAR(100);

-- Step 2: Generate usernames for existing users (from email)
UPDATE users 
SET username = LOWER(SPLIT_PART(email, '@', 1)) 
WHERE username IS NULL;

-- Step 3: Make username unique and not null
ALTER TABLE users ALTER COLUMN username SET NOT NULL;
CREATE UNIQUE INDEX IF NOT EXISTS users_username_unique ON users(username);

-- Step 4: Add admin user if not exists
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM users WHERE email = 'Admin1234@gmail.com') THEN
        INSERT INTO users (email, username, password_hash, full_name, phone, user_type, status)
        VALUES (
            'Admin1234@gmail.com',
            'admin',
            '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Password: Admin123
            'Administrator',
            '09000000000',
            'admin',
            'active'
        );
    END IF;
END $$;
