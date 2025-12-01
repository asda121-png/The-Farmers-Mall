-- Add username and address columns to users table
-- Run this in your Supabase SQL Editor

-- Add username column
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS username VARCHAR(100) UNIQUE;

-- Add address column
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS address TEXT;

-- Create index for username lookups
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);

-- Optional: Update existing users to have a username based on their email
-- (Only if you have existing users without usernames)
UPDATE users 
SET username = SPLIT_PART(email, '@', 1)
WHERE username IS NULL;

-- Make username NOT NULL after populating existing records (optional)
-- ALTER TABLE users ALTER COLUMN username SET NOT NULL;
