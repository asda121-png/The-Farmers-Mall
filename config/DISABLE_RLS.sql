-- IMPORTANT: Run this in Supabase SQL Editor to fix Row Level Security
-- This allows the API to insert/update/delete data

-- Disable RLS on all tables (for development/testing)
-- For production, you should create proper policies instead

ALTER TABLE users DISABLE ROW LEVEL SECURITY;
ALTER TABLE retailers DISABLE ROW LEVEL SECURITY;
ALTER TABLE products DISABLE ROW LEVEL SECURITY;
ALTER TABLE orders DISABLE ROW LEVEL SECURITY;
ALTER TABLE order_items DISABLE ROW LEVEL SECURITY;
ALTER TABLE reviews DISABLE ROW LEVEL SECURITY;
ALTER TABLE messages DISABLE ROW LEVEL SECURITY;
ALTER TABLE notifications DISABLE ROW LEVEL SECURITY;
ALTER TABLE cart DISABLE ROW LEVEL SECURITY;

-- Verify RLS is disabled
SELECT 
    schemaname, 
    tablename, 
    rowsecurity 
FROM pg_tables 
WHERE schemaname = 'public' 
ORDER BY tablename;
