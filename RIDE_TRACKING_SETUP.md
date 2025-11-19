# Real-Time Ride Tracking Setup Guide

## Overview
This setup connects the RidesTab.vue frontend (dmsfrontend) with the Laravel Reverb backend (noyaxi) for real-time rider position tracking.

## Architecture
- **Frontend**: Vue 3 application at `dmsfrontend.test`
- **Backend**: Laravel 11 with Reverb at `noyaxi.test`
- **WebSocket Server**: Reverb running on `localhost:8085`

## Setup Steps

### 1. Backend Setup (noyaxi)

#### Verify Environment Variables
Ensure these are in `d:\Herd\noyaxi\.env`:
```
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8085
REVERB_SCHEME=http
```

**Note**: Generate your own credentials with `php artisan reverb:install`

#### Start Reverb Server
```powershell
cd d:\Herd\noyaxi
php artisan reverb:start
```

The server should start on `http://localhost:8085`

### 2. Frontend Setup (dmsfrontend)

#### Verify Environment Variables
Check `d:\Vue\dmsfrontend\.env`:
```
VITE_REVERB_APP_KEY=your-app-key
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8085
VITE_REVERB_SCHEME=http
VITE_API_NOYAXI_URL=http://localhost:8000
```

**Note**: Use the same `REVERB_APP_KEY` from your backend `.env`

#### Start Frontend Dev Server
```powershell
cd d:\Vue\dmsfrontend
npm run dev
```

### 3. Test the Connection

#### Step 1: Open RidesTab
Navigate to your frontend URL where RidesTab.vue is rendered.

#### Step 2: Create a Ride
1. Enter pickup and destination locations
2. Click "Request a Ride"
3. Confirm the ride - this will create a ride in the noyaxi backend
4. Note the ride ID from the success notification

#### Step 3: Simulate Driver Position
1. Open the driver simulator: `http://localhost:8000/driver-simulator.html`
2. Enter the ride ID from step 2
3. Click "Send Position Update" or "Start Auto Update"

#### Step 4: Watch Real-Time Updates
The driver position should update in real-time on the map in RidesTab.vue!

## API Endpoints

### Create Ride
```
POST http://localhost:8000/api/rides
Content-Type: application/json

{
  "pickup": "Location A",
  "destination": "Location B",
  "vehicleType": "sedan",
  "price": 150.50,
  "distance": 5.2,
  "duration": 15
}
```

### Update Position
```
POST http://localhost:8000/api/rides/{rideId}/position
Content-Type: application/json

{
  "lat": 23.8103,
  "lng": 90.4125,
  "bearing": 45,
  "speed": 40,
  "driverId": "driver-001"
}
```

## WebSocket Events

### Channel
Private channel: `ride.{rideId}`

### Event
Event name: `PositionUpdated`

Payload:
```json
{
  "rideId": "ride-123",
  "lat": 23.8103,
  "lng": 90.4125,
  "bearing": 45,
  "speed": 40,
  "driverId": "driver-001",
  "ts": "2025-11-15T12:30:00Z"
}
```

## Troubleshooting

### Reverb Server Not Starting
- Check if port 8085 is already in use
- Verify .env variables are set correctly
- Run `php artisan config:clear`

### Frontend Not Connecting
- Check browser console for WebSocket errors
- Verify VITE_REVERB_* environment variables
- Restart the frontend dev server after changing .env

### No Real-Time Updates
- Check Reverb server logs for incoming connections
- Verify the ride ID matches between frontend and simulator
- Check browser console for Echo subscription errors
- Ensure broadcasting channel authorization in `routes/channels.php`

## Files Modified

### Backend (noyaxi)
- `app/Events/RidePositionUpdated.php` - Broadcast event
- `app/Http/Controllers/Api/RideController.php` - API endpoints
- `routes/api.php` - API routes
- `routes/channels.php` - WebSocket channel authorization
- `bootstrap/app.php` - API routes registration
- `public/driver-simulator.html` - Testing tool

### Frontend (dmsfrontend)
- `src/views/Site/Food/RidesTab.vue` - Real-time integration
- `.env` - Reverb configuration

## Next Steps

1. Add authentication to ride channels (currently open for testing)
2. Store rides in database
3. Add driver assignment logic
4. Implement ride status management (pending, active, completed)
5. Add ride history and tracking
