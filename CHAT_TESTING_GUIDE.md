# Simple Chat with Laravel Reverb - Testing Guide

A minimal real-time chat application built with Laravel Reverb, Inertia.js, and Vue 3.

## Setup Instructions

### 1. Configure Environment Variables

Update your `.env` file with these Reverb settings:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

If you don't have Reverb credentials, generate them:
```bash
php artisan reverb:install
```

### 2. Start Required Services

Open **3 separate terminal windows** and run:

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 - Reverb WebSocket Server:**
```bash
php artisan reverb:start
```

**Terminal 3 - Vite Dev Server:**
```bash
npm run dev
```

Or use the convenient composer script (requires concurrently):
```bash
composer dev
```

### 3. Test the Chat

1. Open your browser and navigate to: `http://localhost:8000/chat`
2. Open the same URL in multiple browser tabs or windows
3. Change the username in each tab if desired
4. Type a message and click "Send" or press Enter
5. Watch the message appear instantly in all open tabs!

## How It Works

### Backend Components

- **Event**: `App\Events\MessageSent` - Broadcasts chat messages via Reverb
- **Controller**: `App\Http\Controllers\ChatController` - Handles message sending
- **Channel**: `chat` (public channel) - Defined in `routes/channels.php`
- **Route**: `POST /chat/send` - Endpoint to send messages

### Frontend Components

- **Page**: `resources/js/pages/Chat.vue` - Main chat interface
- **Echo**: Configured in `resources/js/app.ts` with Reverb broadcaster
- **Composable**: `useEcho` from `@laravel/echo-vue` - Manages real-time subscriptions

### Broadcasting Flow

1. User types message and clicks "Send"
2. Frontend sends POST request to `/chat/send`
3. Controller validates and broadcasts `MessageSent` event
4. Reverb server pushes event to all connected clients on `chat` channel
5. All clients receive the event via Echo and update their UI

## Features

- ✅ Real-time message broadcasting
- ✅ Multiple concurrent users
- ✅ Custom usernames
- ✅ Timestamps for each message
- ✅ Auto-scroll to latest message
- ✅ Dark mode support
- ✅ Enter to send, Shift+Enter for new line
- ✅ Message validation (max 500 chars)

## Troubleshooting

### Messages not appearing?

1. Check that Reverb server is running: `php artisan reverb:start`
2. Verify `BROADCAST_CONNECTION=reverb` in `.env`
3. Check browser console for connection errors
4. Ensure Vite is running: `npm run dev`

### Connection refused?

Make sure your Reverb credentials in `.env` match and are properly set. Run:
```bash
php artisan config:clear
php artisan cache:clear
```

### Still having issues?

Check the Reverb server output for connection attempts and the browser console for JavaScript errors.

## Next Steps

This is a minimal implementation for testing. To enhance it:

- Add authentication to track real users
- Store messages in database
- Add typing indicators
- Implement message editing/deletion
- Add file uploads
- Create multiple chat rooms
- Add user presence (online/offline)
- Implement read receipts

## Learn More

- [Laravel Broadcasting Docs](https://laravel.com/docs/broadcasting)
- [Laravel Reverb Docs](https://laravel.com/docs/reverb)
- [Inertia.js Docs](https://inertiajs.com/)
- [Laravel Echo-Vue](https://github.com/laravel/echo-vue)
