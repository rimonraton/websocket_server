# 🔍 Real-Time System Diagnostic

## Step 1: Verify Reverb Server is Running
```powershell
netstat -ano | findstr "8085" | findstr "LISTENING"
```
**Expected:** TCP 0.0.0.0:8085 ... LISTENING

---

## Step 2: Test Backend Broadcast (Manual)
```powershell
cd D:\Herd\noyaxi
php test-counter.php
```
**Expected:** Broadcasts counter 1-5

---

## Step 3: Open Standalone Test Page
1. Open: http://noyaxi.test/test-counter.html
2. Check console (F12)
3. **Expected console logs:**
   - `Connecting to Reverb...`
   - `✅ Connected successfully!`
   - `Subscribing to test-counter channel...`
   - `✅ Subscribed to test-counter channel`
4. Click "Send Increment" button
5. **Expected:** Counter increments and broadcasts

---

## Step 4: Vue Dev Server Status
**CRITICAL:** Vue dev server must be restarted after .env changes!

```powershell
cd D:\Vue\dmsfrontend
# Stop current server (Ctrl+C if running in terminal)
npm run dev
```

---

## Step 5: Test Driver Dashboard
1. Open: http://localhost:8080/driver/dashboard
2. Open console (F12)
3. **Look for these logs on page load:**
   - `🔧 NewDashboard mounted`
   - `🔧 Environment variables: {key, host, port, ...}`
4. Toggle **Online** switch
5. **Expected console logs:**
   - `Toggle online status called, new state: true`
   - `Going online - starting location tracking and Echo...`
   - `🔌 Initializing Echo with Reverb...`
   - `Echo state changed: ... -> connected`
   - `✅ Echo connected successfully!`
   - `Subscribing to drivers channel...`
   - `Subscribing to test-counter channel...`
   - `✅ Successfully subscribed to test-counter channel`

---

## Step 6: Test Counter from Backend
While driver dashboard is open and online:
```powershell
cd D:\Herd\noyaxi
php test-counter.php
```

**Watch driver dashboard console for:**
```
📊 Counter updated event received: {counter: 1}
📊 testCounter.value set to: 1
📊 Counter updated event received: {counter: 2}
📊 testCounter.value set to: 2
...
```

**Watch debug panel:** Counter should increment from 0 → 5

---

## Step 7: Test from RidesTab
1. Open RidesTab page
2. Click "Increment Counter" button
3. **RidesTab console should show:**
   - `📤 Sending counter increment... 1`
   - `Counter broadcast response: {success: true, counter: 1}`
4. **Driver dashboard should show:**
   - `📊 Counter updated event received: {counter: 1}`
   - Counter in debug panel updates

---

## Common Issues & Solutions

### Issue: "Echo Status: Not Initialized ❌"
**Cause:** Driver not toggled online
**Solution:** Click the Online toggle switch

### Issue: No console logs after toggling online
**Cause:** JavaScript errors or Echo not imported
**Solution:** Check console for errors, refresh page

### Issue: "Cannot read properties of undefined (reading 'channel')"
**Cause:** Echo failed to initialize
**Solution:** 
1. Verify Reverb is running
2. Check .env variables
3. Restart Vue dev server

### Issue: Counter increments locally but not on driver dashboard
**Cause:** Backend broadcast not reaching Reverb
**Solution:**
1. Check Reverb server is running
2. Test with `php test-counter.php`
3. Check Reverb terminal for error messages

### Issue: Standalone HTML page works, but Vue doesn't
**Cause:** Vue dev server needs restart or env variables not loaded
**Solution:**
1. Stop Vue dev server
2. Run `npm run dev` again
3. Hard refresh browser (Ctrl+Shift+R)

---

## Success Checklist
- [ ] Reverb server running on port 8085
- [ ] Backend test broadcasts successfully
- [ ] Standalone HTML test page works
- [ ] Vue dev server restarted after .env changes
- [ ] Driver dashboard shows "Echo Status: Initialized ✅"
- [ ] Console shows subscription to test-counter channel
- [ ] Backend test updates counter on dashboard
- [ ] RidesTab button updates counter on dashboard

---

## If All Else Fails

1. **Kill all processes:**
   ```powershell
   # Find and kill Reverb
   Get-Process php | Where-Object {$_.StartTime -gt (Get-Date).AddHours(-1)} | Stop-Process
   ```

2. **Start fresh:**
   ```powershell
   # Terminal 1: Reverb
   cd D:\Herd\noyaxi
   php artisan reverb:start --port=8085
   
   # Terminal 2: Vue Dev Server
   cd D:\Vue\dmsfrontend
   npm run dev
   ```

3. **Open http://noyaxi.test/test-counter.html in TWO browser tabs**
   - Click increment in one tab
   - Should update in both tabs
   - If this doesn't work, the issue is with Reverb/backend, not Vue

4. **Check Reverb server terminal for errors**
   - Look for connection logs
   - Look for broadcast logs
   - Look for error messages
