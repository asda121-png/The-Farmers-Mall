-- Add sender_name and receiver_name columns to chat_messages table
-- Run this in Supabase SQL Editor if the columns are missing

-- Add columns if they don't exist
ALTER TABLE chat_messages 
ADD COLUMN IF NOT EXISTS sender_name VARCHAR(255),
ADD COLUMN IF NOT EXISTS receiver_name VARCHAR(255),
ADD COLUMN IF NOT EXISTS is_read BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Update existing records to populate names from users table
UPDATE chat_messages cm
SET 
    sender_name = COALESCE(u.full_name, u.username, 'Unknown'),
    receiver_name = COALESCE(r.full_name, r.username, 'Unknown')
FROM users u, users r
WHERE cm.sender_id = u.id 
  AND cm.receiver_id = r.id
  AND (cm.sender_name IS NULL OR cm.receiver_name IS NULL);
