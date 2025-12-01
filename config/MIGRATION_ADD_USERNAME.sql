-- IMPORTANT: Run this SQL script in Supabase SQL Editor
-- Go to: https://supabase.com/dashboard/project/YOUR_PROJECT/sql/new

-- Step 1: Add username column
ALTER TABLE users ADD COLUMN IF NOT EXISTS username VARCHAR(100);

-- Step 2: Generate usernames for existing users (from email prefix)
UPDATE users 
SET username = LOWER(SPLIT_PART(email, '@', 1))
WHERE username IS NULL OR username = '';

-- Step 3: Handle duplicate usernames by adding numbers
DO $$
DECLARE
    user_record RECORD;
    new_username VARCHAR(100);
    counter INTEGER;
BEGIN
    FOR user_record IN 
        SELECT id, username 
        FROM users 
        WHERE username IN (
            SELECT username 
            FROM users 
            GROUP BY username 
            HAVING COUNT(*) > 1
        )
        ORDER BY created_at
    LOOP
        counter := 1;
        new_username := user_record.username || counter;
        
        WHILE EXISTS (SELECT 1 FROM users WHERE username = new_username) LOOP
            counter := counter + 1;
            new_username := SPLIT_PART(user_record.username, '1', 1) || counter;
        END LOOP;
        
        UPDATE users SET username = new_username WHERE id = user_record.id;
    END LOOP;
END $$;

-- Step 4: Add unique constraint
ALTER TABLE users ADD CONSTRAINT users_username_unique UNIQUE (username);

-- Step 5: Make username NOT NULL
ALTER TABLE users ALTER COLUMN username SET NOT NULL;

-- Step 6: Insert admin user if not exists
INSERT INTO users (email, username, password_hash, full_name, phone, user_type, status)
SELECT 
    'Admin1234@gmail.com',
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Password: Admin123
    'Administrator',
    '09000000000',
    'admin',
    'active'
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'Admin1234@gmail.com'
);

-- Verify the changes
SELECT id, email, username, full_name, user_type FROM users ORDER BY created_at;
