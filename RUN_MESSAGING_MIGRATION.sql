-- ========================================
-- MESSAGING SYSTEM MIGRATION
-- Run this to create/update the chat_messages table
-- ========================================

-- Drop existing table if exists (optional - comment out if you want to preserve data)
-- DROP TABLE IF EXISTS chat_messages CASCADE;

-- Create chat_messages table with sender/receiver names
CREATE TABLE IF NOT EXISTS chat_messages (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    sender_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    receiver_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    sender_name VARCHAR(255) NOT NULL,
    receiver_name VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_chat_messages_sender ON chat_messages(sender_id);
CREATE INDEX IF NOT EXISTS idx_chat_messages_receiver ON chat_messages(receiver_id);
CREATE INDEX IF NOT EXISTS idx_chat_messages_conversation ON chat_messages(sender_id, receiver_id);
CREATE INDEX IF NOT EXISTS idx_chat_messages_created_at ON chat_messages(created_at DESC);

-- Create trigger for updated_at
CREATE OR REPLACE FUNCTION update_chat_messages_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

DROP TRIGGER IF EXISTS update_chat_messages_timestamp ON chat_messages;
CREATE TRIGGER update_chat_messages_timestamp 
    BEFORE UPDATE ON chat_messages
    FOR EACH ROW 
    EXECUTE FUNCTION update_chat_messages_updated_at();

-- Insert some sample messages for testing (optional)
-- REPLACE these UUIDs with actual user IDs from your database
/*
INSERT INTO chat_messages (sender_id, receiver_id, sender_name, receiver_name, message) 
VALUES 
    ('your-user-uuid', 'retailer-uuid', 'Customer Name', 'Retailer Name', 'Hello, I have a question about your products!'),
    ('retailer-uuid', 'your-user-uuid', 'Retailer Name', 'Customer Name', 'Hello! How can I help you today?');
*/

-- Grant permissions (adjust as needed for your setup)
-- GRANT ALL PRIVILEGES ON chat_messages TO your_database_user;

SELECT 'Chat messages table created successfully!' as status;
