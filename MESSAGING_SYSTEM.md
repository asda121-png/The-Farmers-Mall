# Messaging System Documentation

## Overview
The messaging system allows customers to chat with retailers in real-time. Messages are stored in Supabase and can optionally use RabbitMQ for real-time delivery.

## Features Implemented

### 1. Retailer List Display
- Shows all retailers in the left sidebar
- Displays online/offline status with colored indicators:
  - **Green circle** = Online
  - **White/Gray circle** = Offline
- Shows unread message count badges
- Displays last message preview and timestamp
- Search functionality to filter retailers

### 2. Chat Functionality
- Click a retailer to open chat
- Display all messages between customer and retailer
- Send new messages
- Real-time message polling (every 3 seconds)
- Auto-scroll to latest message

### 3. Unread Message Indicators
- Red badge with count on retailers with unread messages
- Messages automatically marked as read when chat is opened
- Badge removed after reading

### 4. Online Status Tracking
- Tracks user activity for online/offline status
- Updates every 2 minutes when page is active
- Requires `last_activity` column in users table (optional)

## API Endpoints

### `api/retailers.php` (GET)
Returns list of all retailers with:
- Shop name
- Profile picture
- Online status
- Unread message count
- Last message preview

### `api/messages.php`

#### GET `?retailer_id={id}`
Returns all messages between current user and specified retailer.

#### POST
Send a new message.
```json
{
  "receiver_id": "uuid",
  "message": "Message text"
}
```

#### PUT
Mark messages as read.
```json
{
  "retailer_id": "uuid"
}
```

### `api/update-activity.php` (GET)
Updates user's last activity timestamp for online status tracking.

## Database Schema

### Messages Table
- `id` - UUID primary key
- `sender_id` - UUID (references users.id)
- `receiver_id` - UUID (references users.id)
- `message` - TEXT
- `is_read` - BOOLEAN (default: false)
- `created_at` - TIMESTAMP

### Users Table (Optional Enhancement)
Add `last_activity` column for online status:
```sql
ALTER TABLE users ADD COLUMN last_activity TIMESTAMP;
```

See `config/add_last_activity.sql` for complete migration.

## RabbitMQ Integration (Optional)

RabbitMQ is integrated but optional. The system works without it using database polling.

### Setup RabbitMQ (Optional)

1. Install RabbitMQ server
2. Install PHP AMQP extension:
   ```bash
   pecl install amqp
   ```
3. Add to `config/.env`:
   ```
   RABBITMQ_HOST=localhost
   RABBITMQ_PORT=5672
   RABBITMQ_USER=guest
   RABBITMQ_PASSWORD=guest
   RABBITMQ_VHOST=/
   ```

If RabbitMQ is not configured, messages are still saved to the database and delivered via polling.

## File Structure

```
api/
  ├── messages.php          # Message CRUD operations
  ├── retailers.php         # Get retailers list
  └── update-activity.php   # Update online status

config/
  ├── rabbitmq.php         # RabbitMQ client (optional)
  └── add_last_activity.sql # Migration for online status

user/
  └── message.php          # Main messaging interface
```

## Usage

1. **View Retailers**: Open `user/message.php` to see all retailers
2. **Start Chat**: Click on a retailer to open chat
3. **Send Message**: Type message and press Enter or click send button
4. **View Unread**: Retailers with unread messages show a red badge
5. **Online Status**: Green dot = online, gray dot = offline

## Notes

- Messages are polled every 3 seconds for new messages
- Retailers list refreshes every 10 seconds
- Activity status updates every 2 minutes
- Online status requires `last_activity` column (see migration file)
- RabbitMQ is optional - system works with database polling only




