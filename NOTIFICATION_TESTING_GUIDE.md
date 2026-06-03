# Browser Push Notification Testing Guide

## What We Just Built

We've added **native browser push notifications** to the Visitor Management System. This means:

✅ **Admins get popup notifications even when they're not actively viewing the page**
✅ **Notifications appear even if the tab is minimized or in the background**
✅ **Clicking the notification brings the dashboard into focus**
✅ **All notifications are powered by the real-time WebSocket system**

## Technical Architecture

```
Visitor Checks In → PHP Creates Notification → WebSocket Server Broadcasts Event
→ JavaScript Receives Event → Service Worker → Native Browser Notification Popup
```

Three components working together:
1. **service-worker.js** — Runs in background, displays notifications
2. **notification_handler.js** — Manages permissions, connects WebSocket to Service Worker
3. **admin_dashboard.php** — Includes both scripts

---

## Testing Instructions

### Step 1: Grant Notification Permissions

1. **Open admin_dashboard.php** (make sure you're logged in)
2. **Look for a browser prompt** saying "visitor-system wants to send you notifications"
3. **Click "Allow"** (or your browser's equivalent)
   - Chrome/Edge: "Allow" / "Block"
   - Firefox: "Always Receive Notifications" / "Never for This Site" / "Not Now"
   - Safari: Might ask in System Preferences

**Console Output to Watch For:**
```
[NotificationHandler] Initializing...
[NotificationHandler] Service Worker registered:
[NotificationHandler] Requesting notification permission...
[NotificationHandler] Notifications granted by user
[NotificationHandler] Setting up WebSocket listeners...
```

### Step 2: Test with a Visitor Check-In

**Setup:**
- Keep admin_dashboard.php open in one browser window
- **Minimize it or move it to the background** (this is the whole point!)
- Navigate to `http://localhost/visitor-system/` in another browser tab or window
- Open your browser's **Developer Console** (F12) to watch logs

**Action:**
1. Fill out the visitor check-in form:
   - Name: "Test Visitor"
   - Faculty: "Testing"
   - Phone: "1234567890"
   - Destination: Select the admin's destination
   - Click Submit

**Expected Result:**
A **popup notification** appears on your screen, even though the admin dashboard tab was in the background:

```
[Logo]
👤 New Visitor Arrived

Test Visitor is checking in to Gaming Hub.
Visitor Type: Guest
Phone: 1234567890
```

**Console Output:**
```
[NotificationHandler] Visitor arrival event: {id: 123, fullname: "Test Visitor", ...}
[NotificationHandler] Sending notification to Service Worker: 👤 New Visitor Arrived
[Service Worker] Showing notification: 👤 New Visitor Arrived
```

### Step 3: Test Notification Click

**Action:**
1. From Step 2, a notification popup appeared
2. **Click on the notification popup**

**Expected Result:**
- The notification closes
- The admin_dashboard.php tab comes into focus
- The visitor record appears in the table

**Console Output:**
```
[Service Worker] Notification clicked: 👤 New Visitor Arrived
```

### Step 4: Test Background Focus Behavior

**Setup:**
- Open admin_dashboard.php
- Grant permissions
- Minimize or switch to another window entirely (Windows Key + D to show desktop)

**Action:**
1. Have another admin or colleague check in a visitor
2. Watch your screen

**Expected Result:**
- Even though the dashboard is not visible, a **system notification** appears
- On Windows: Notification appears in bottom-right corner
- On Mac: Notification appears in top-right corner
- On mobile: System notification on lock screen (if accessing from phone)

### Step 5: Test Message Notifications (Optional)

If your system has an "away mode" or "unavailable" feature for visitors to message:

**Setup:**
1. Have admin mark destination as "Unavailable"
2. Visitor attempts check-in, but destination is unavailable
3. System offers "Send Message" option instead

**Action:**
1. Visitor fills form and sends message
2. Admin is away from dashboard

**Expected Result:**
Popup notification appears:
```
💬 New Message from Visitor

Test Visitor sent a message.

"I'll come back later"
```

---

## Troubleshooting

### "I don't see a notification permission prompt"

**Possible Causes:**
- You already denied permissions. Notifications are blocked for this site.
- Browser doesn't support notifications (very old browser)

**Solution:**
1. **Chrome/Edge**: Click lock icon in address bar → Site Settings → Notifications → Allow
2. **Firefox**: Click lock icon → Permissions → Unblock notifications
3. **Safari**: System Preferences → Notifications → Find "visitor-system" and allow

---

### "I allowed notifications but nothing happens"

**Debug Steps:**
1. Open **Developer Console** (F12)
2. Look for error messages starting with `[NotificationHandler]` or `[Service Worker]`
3. Check if Service Worker is registered:
   - Chrome Dev Tools → Application → Service Workers
   - Firefox Dev Tools → Storage → Service Workers
4. Verify WebSocket is connected:
   - Check for message: `[NotificationHandler] Setting up WebSocket listeners...`
   - Check Network tab for connection to port 3005

**Common Issues:**
- **Service Worker registration failed** → Check browser console for errors, ensure `/visitor-system/service-worker.js` exists
- **WebSocket connection failed** → Ensure Node.js server is running on port 3005
- **Notification permission is "Denied"** → Reset site permissions (see above)

---

### "Notifications work sometimes but not always"

**Likely Cause:** WebSocket server (Node.js) is not running

**Solution:**
1. Start the Node.js server: `node c:\xampp\htdocs\visitor-system\realtime\server.js`
2. You should see: `Realtime server running on port 3005`
3. Reload admin_dashboard.php

---

### "I see notification but text is wrong"

**Possible Issue:** Event data is missing or not properly formatted

**Solution:**
Look at console output to see what data is being received:
```
[NotificationHandler] Visitor arrival event: {id: 123, fullname: "", destination_name: ...}
```

If `fullname` is empty, the visitor check-in form didn't send proper data.

---

## Advanced: Customizing Notifications

### Change Notification Icon

Edit `service-worker.js`, line with `icon:`:
```javascript
icon: '/visitor-system/ui_logo-removebg-preview.png', // Change this path
```

### Change Notification Duration (Auto-dismiss)

Edit `notification_handler.js`, in `showNotification()`:
```javascript
const notificationOptions = {
    requireInteraction: true,  // true = user must dismiss, false = auto-dismiss
    // ...
};
```

### Add Sound to Notification

Edit `service-worker.js`:
```javascript
self.registration.showNotification(title, {
    icon: '/visitor-system/ui_logo-removebg-preview.png',
    sound: '/visitor-system/notification.mp3',  // Add sound file
    ...options
});
```

---

## Browser Compatibility

| Browser | Support | Notes |
|---------|---------|-------|
| Chrome/Chromium | ✅ Full | Works great, all features |
| Firefox | ✅ Full | Works great, all features |
| Edge | ✅ Full | Windows only, all features |
| Safari | ⚠️ Partial | macOS/iOS only, might ask for extra permission |
| IE 11 | ❌ None | Too old, notifications not supported |
| Mobile Chrome | ✅ Full | Notifications on lock screen |
| Mobile Firefox | ✅ Full | Notifications on lock screen |

---

## Disable Notifications (If Needed)

In browser console:
```javascript
window.NotificationHandler.disable();
```

Re-enable:
```javascript
window.NotificationHandler.requestPermission();
```

---

## Production Deployment Notes

1. **Ensure HTTPS** — Service Workers only work on HTTPS (or localhost)
2. **Test on all devices** — Desktop, laptop, tablet, phone
3. **Consider notification fatigue** — Don't notify on every single event or users will turn off
4. **Mobile considerations** — Notifications appear on lock screen, so keep content brief
5. **User choice** — Users can disable notifications per site in browser settings

---

## Files Changed/Added

**New Files:**
- `service-worker.js` — Service Worker handling notifications
- `notification_handler.js` — Notification permission & WebSocket integration

**Modified Files:**
- `admin_dashboard.php` — Added `<script src="notification_handler.js"></script>`

**Unchanged:**
- `assets/js/realtime_client.js` — Already handles WebSocket
- `realtime/server.js` — Already broadcasts events
- `submit.php` — Already sends visitor notifications via WebSocket

---

## Next Steps / Ideas

✨ **Potential Enhancements:**
- Add sound notifications (requires MP3 file)
- Group notifications by destination
- Allow users to configure notification preferences (which events to notify on)
- Add notification action buttons ("View Visitor" / "Mark Unavailable")
- Track notification click-through rate
- Send notifications via SMS or email as fallback

---

**Questions?** Check browser console logs — they're very detailed!

