# Quick Test Guide for Ride Tracking

## 🚀 Quick Start (5 minutes)

### Prerequisites
✅ Reverb server running on port 8085  
✅ Frontend dev server running  
✅ noyaxi.test backend accessible  

### Test Steps

#### 1. Start Reverb Server (if not running)
```powershell
cd d:\Herd\noyaxi
php artisan reverb:start --host=0.0.0.0 --port=8085
```
You should see: `INFO  Starting server on 0.0.0.0:8085 (localhost)`

#### 2. Open Your Frontend
Navigate to your RidesTab page in the browser (dmsfrontend)

#### 3. Open Browser Console
Press F12 and go to the Console tab to see connection logs

#### 4. Request a Ride
1. Enter pickup: "Dhaka University"
2. Enter destination: "Gulshan 1"
3. Click "Request a Ride"
4. Click "Confirm Ride"
5. **Copy the Ride ID** from the success message (e.g., "ride-656f9a1b2c3d4")

#### 5. Open Driver Simulator
In a new browser tab, navigate to:
```
http://noyaxi.test/driver-simulator.html
```

#### 6. Simulate Driver Movement
1. Paste the Ride ID from step 4
2. Set starting position (defaults to Dhaka coordinates):
   - Latitude: 23.8103
   - Longitude: 90.4125
3. Click **"Start Auto Update"**

#### 7. Watch the Magic! ✨
Switch back to your RidesTab page and watch the driver position update in real-time on the map!

## 🔍 Debugging

### Check Console Logs
Look for these messages in browser console:

**Good signs:**
```
Initializing Echo with Reverb... {key: "ozfq...", host: "localhost", port: 8085}
Echo initialized successfully with Reverb
subscribed to ride.ride-xxxxx
PositionUpdated event received {rideId: "...", lat: 23.8103, lng: 90.4125}
```

**Problems:**
```
✗ Echo init failed
✗ WebSocket connection error
✗ Failed to create ride
```

### Check Reverb Server Logs
In the terminal where Reverb is running, you should see:
```
New connection accepted
Subscription succeeded for channel: private-ride.xxxxx
```

### Test API Manually

Test ride creation:
```powershell
$body = @{
    pickup = "Test Location A"
    destination = "Test Location B"
    vehicleType = "sedan"
    price = 100
    distance = 5
    duration = 10
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://noyaxi.test/api/rides" -Method POST -Body $body -ContentType "application/json"
```

Test position update:
```powershell
$body = @{
    lat = 23.8103
    lng = 90.4125
    bearing = 45
    speed = 40
    driverId = "driver-001"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://noyaxi.test/api/rides/test-ride-123/position" -Method POST -Body $body -ContentType "application/json"
```

## 📊 Expected Flow

```
User (RidesTab.vue)                Reverb Server              Driver Simulator
       |                                  |                           |
       |--1. Create Ride----------------→ [noyaxi API]               |
       |←-Return rideId-----------------  |                           |
       |                                  |                           |
       |--2. Subscribe to ride.{id}-----→ |                           |
       |←-Subscription confirmed---------  |                           |
       |                                  |                           |
       |                                  |  ←-3. Send Position------  |
       |                                  |      [noyaxi API]          |
       |                                  |                           |
       |                                  |--4. Broadcast Event----→  |
       |←-5. Receive PositionUpdated----  |                           |
       |                                  |                           |
    [Map Updates]                         |                           |
```

## 🎯 Success Criteria

✅ Reverb server starts without errors  
✅ Frontend connects to WebSocket (no console errors)  
✅ Ride creation returns valid rideId  
✅ Browser console shows "subscribed to ride.{id}"  
✅ Driver simulator shows successful API responses  
✅ Frontend receives PositionUpdated events  
✅ Map marker moves in real-time  

## 🛠️ Troubleshooting

| Problem | Solution |
|---------|----------|
| Port 8085 already in use | Kill process using port or change REVERB_PORT |
| WebSocket connection refused | Check Reverb server is running |
| 404 on API endpoints | Run `php artisan route:clear` |
| CORS errors | Add frontend URL to noyaxi CORS config |
| No real-time updates | Verify ride ID matches in both frontend and simulator |
| Auth errors on private channel | Channel temporarily allows all users for testing |

## 📝 Notes

- The channel authorization in `routes/channels.php` is currently open for testing
- In production, add proper authentication checks
- Consider storing rides in database for persistence
- Add error handling and retry logic for production use
