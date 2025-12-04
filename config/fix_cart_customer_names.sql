-- Fix cart customer_name to use actual full_name instead of email
-- This updates cart items where customer_name is the user's email to use their actual full_name

UPDATE cart c
SET customer_name = u.full_name
FROM users u
WHERE c.customer_name = u.email
AND u.full_name IS NOT NULL
AND u.full_name != '';

-- For users without full_name, use their username
UPDATE cart c
SET customer_name = u.username
FROM users u
WHERE c.customer_name = u.email
AND (u.full_name IS NULL OR u.full_name = '')
AND u.username IS NOT NULL
AND u.username != '';
