# Real-Time Ride Tracking Debug Guide

## Current Status
- ✅ Reverb server running on port 8085 (PID: 5740)
- ✅ Broadcasts changed to `ShouldBroadcastNow` (synchronous)
- ✅ Test broadcast script works
- ❌ Driver dashboard not receiving ride requests

## Debug Steps

### 1. Start Reverb Server (if not running)
```powershell
cd D:\Herd\noyaxi
php artisan reverb:start --port=8085
```

### 2. Open Driver Dashboard
1. Navigate to: http://localhost:8080/driver/dashboard
2. Open browser console (F12)
3. Click the **Online** toggle
4. Watch for these logs:
   - `🔌 Initializing Echo with Reverb...`
   - `✅ Echo connected successfully!`
   - `Subscribing to drivers channel...`
   - `✅ Successfully subscribed to drivers channel`

### 3. Test Connection
Click the **"🧪 Test Broadcast Connection"** button in the debug panel

### 4. Create Test Ride from RidesTab
1. Open: http://localhost:8080 (or wherever RidesTab is)
2. Fill in pickup and destination
3. Click "Request a Ride"
4. Click "Confirm Ride"
5. Watch driver dashboard console for: `🚗 New ride request received:`

### 5. Manual Test via PHP Script
```powershell
cd D:\Herd\noyaxi
php test-broadcast.php
```

## Common Issues & Solutions

### Issue: Echo not connecting
**Symptoms:** No "✅ Echo connected successfully!" in console
**Solutions:**
- Verify Reverb is running: `netstat -ano | findstr "8085"`
- Check VITE env variables in `.env` file
- Try toggling offline then online again

### Issue: Not subscribed to channel
**Symptoms:** No "✅ Successfully subscribed to drivers channel"
**Solutions:**
- Make sure you toggled ONLINE first
- Check console for subscription errors
- Verify channel name is 'drivers' (not 'driver')

### Issue: Broadcast sent but not received
**Symptoms:** Test script succeeds but driver sees nothing
**Solutions:**
- Check Reverb server console for broadcast logs
- Verify event name matches: `RideRequest`
- Check if `ShouldBroadcastNow` interface is used (not `ShouldBroadcast`)

### Issue: CORS errors
**Symptoms:** Console shows CORS policy errors
**Solutions:**
- Already configured in `config/cors.php`
- Verify `SANCTUM_STATEFUL_DOMAINS` includes your frontend domain

## Environment Variables

Make sure these are set in `D:\Herd\noyaxi\.env`:
```
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=ozfq1oewbwd3vaduekjs
REVERB_APP_KEY=ozfq1oewbwd3vaduekjs
REVERB_APP_SECRET=your-secret-key
REVERB_HOST=localhost
REVERB_PORT=8085
REVERB_SCHEME=http
```

And in `D:\Vue\dmsfrontend\.env`:
```
VITE_REVERB_APP_KEY=ozfq1oewbwd3vaduekjs
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8085
VITE_REVERB_SCHEME=http
VITE_API_NOYAXI_URL=http://noyaxi.test
```

## Debug Panel Info
The driver dashboard now shows a debug panel with:
- Echo initialization status
- Online status
- Reverb connection details
- Test broadcast button

## Next Steps
1. Toggle driver online
2. Check console logs carefully
3. If connected but not receiving, check Reverb server logs
4. If not connecting at all, verify env variables
5. Try the test broadcast button
