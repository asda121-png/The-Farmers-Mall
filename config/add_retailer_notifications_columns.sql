-- Add additional columns to notifications table for retailer notifications
-- Run this migration in Supabase SQL Editor

ALTER TABLE notifications 
ADD COLUMN IF NOT EXISTS link TEXT,
ADD COLUMN IF NOT EXISTS retailer_id UUID REFERENCES retailers(id) ON DELETE CASCADE,
ADD COLUMN IF NOT EXISTS order_id UUID REFERENCES orders(id) ON DELETE CASCADE,
ADD COLUMN IF NOT EXISTS related_data JSONB;

-- Add index for faster queries
CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications(user_id);
CREATE INDEX IF NOT EXISTS idx_notifications_retailer_id ON notifications(retailer_id);
CREATE INDEX IF NOT EXISTS idx_notifications_created_at ON notifications(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_notifications_is_read ON notifications(is_read);

-- Update existing notifications to have proper timestamps
UPDATE notifications SET created_at = CURRENT_TIMESTAMP WHERE created_at IS NULL;
