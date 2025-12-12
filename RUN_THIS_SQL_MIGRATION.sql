-- IMPORTANT: Run this SQL in your Supabase SQL Editor
-- This adds the required columns for retailer notifications

-- Step 1: Add new columns to notifications table
ALTER TABLE notifications 
ADD COLUMN IF NOT EXISTS link TEXT,
ADD COLUMN IF NOT EXISTS retailer_id UUID REFERENCES retailers(id) ON DELETE CASCADE,
ADD COLUMN IF NOT EXISTS order_id UUID REFERENCES orders(id) ON DELETE CASCADE,
ADD COLUMN IF NOT EXISTS related_data JSONB;

-- Step 2: Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications(user_id);
CREATE INDEX IF NOT EXISTS idx_notifications_retailer_id ON notifications(retailer_id);
CREATE INDEX IF NOT EXISTS idx_notifications_created_at ON notifications(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_notifications_is_read ON notifications(is_read);

-- Step 3: Update any existing notifications with proper timestamps
UPDATE notifications SET created_at = CURRENT_TIMESTAMP WHERE created_at IS NULL;

-- Step 4: Verify the migration
SELECT 
    column_name, 
    data_type, 
    is_nullable
FROM information_schema.columns 
WHERE table_name = 'notifications'
ORDER BY ordinal_position;

-- Expected output should include these columns:
-- id, user_id, retailer_id, order_id, title, message, type, link, is_read, related_data, created_at

-- If you see all these columns, the migration was successful! ✅
