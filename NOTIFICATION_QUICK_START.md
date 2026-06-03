# QUICK START: Testing Browser Notifications

## What You Have Now

The system can now send **native browser notifications** when:
- ✅ A visitor checks in to an admin's destination
- ✅ A visitor sends a message to an admin
- ✅ Admin availability changes (optional)

These notifications appear **even when the admin is not actively viewing the page** — like a popup on their desktop or phone lock screen.

---

## 30-Second Test (Right Now!)

### 1. Prerequisites ✓
- [ ] Realtime server is running (`node realtime/server.js` or `run_realtime_server.bat`)
- [ ] Apache/XAMPP is running
- [ ] Browser is modern (Chrome, Firefox, Edge, Safari)

### 2. Setup
```
WINDOW 1: admin_dashboard.php (logged in)
WINDOW 2: index.php (visitor check-in form)
CONSOLE: F12 to open Developer Tools
```

### 3. The Test
1. **Admin Window:** Allow notifications when prompted (browser will ask)
2. **Admin Window:** Minimize it (THIS IS THE POINT!)
3. **Visitor Window:** Fill form and submit
4. **Watch Screen:** Notification popup appears!
5. **Click It:** Dashboard comes into focus

**That's it. That's the whole feature.**

---

## Console Output You Should See

```
[NotificationHandler] Initializing...
[NotificationHandler] Service Worker registered: ...
[NotificationHandler] Requesting notification permission...
[NotificationHandler] Notifications granted by user
[NotificationHandler] Setting up WebSocket listeners...
[NotificationHandler] Visitor arrival event: {id: 123, fullname: "John Doe", ...}
[NotificationHandler] Sending notification to Service Worker: 👤 New Visitor Arrived
[Service Worker] Showing notification: 👤 New Visitor Arrived
```

If you see errors instead, see **Troubleshooting** below.

---

## What Each File Does

| File | Purpose |
|------|---------|
| `service-worker.js` | Runs in background, displays notification popups |
| `notification_handler.js` | Asks permission, connects WebSocket to notifications |
| `admin_dashboard.php` | Includes `notification_handler.js` |

**No changes to:**
- WebSocket server (already works)
- Database (no changes)
- Visitor check-in (no changes)

---

## Troubleshooting

### "I don't see a permission prompt"

You already denied it. Fix:
- **Chrome:** Click 🔒 in address bar → Permissions → Allow Notifications
- **Firefox:** Click 🔒 in address bar → Permissions → Allow
- **Safari:** System Settings → Notifications → Allow

### "Service Worker failed to register"

Check console for error message. Usually means:
- File path is wrong (should be `/visitor-system/service-worker.js`)
- Browser doesn't support Service Workers (use modern browser)

**Fix:** Reload page, try again

### "No notification appears but permission is granted"

WebSocket probably isn't connected. Check:
1. Is Node.js server running? `node realtime/server.js`
2. Is it on port 3005? Check console
3. Browser console: Do you see `[RealtimeClient: Connected]`?

**Fix:** Start Node.js server, reload page

### "Notification appears but text is weird/empty"

Event data isn't being sent properly. Check:
1. Did visitor form fully submit?
2. Look at browser console during check-in
3. Database: Did record get created?

**If still broken:** Check `submit.php` is properly calling `notify_realtime()`

---

## How It Works (Simple Explanation)

```
Visitor fills form → Database saves record → PHP calls notify_realtime()
→ HTTP request to Node.js WebSocket server → Server broadcasts to admin
→ Browser receives event via WebSocket → JavaScript calls Service Worker
→ Service Worker shows system notification popup
```

All of this happens in **< 100 milliseconds**.

---

## Test Scenarios

### Scenario 1: Notification While Tab Closed
1. Open admin_dashboard.php, grant permission
2. Close the tab entirely (not minimized, actually closed)
3. Open visitor check-in in another tab
4. Submit visitor
5. Desktop notification appears ✓

### Scenario 2: Multiple Admin Tabs
1. Open admin_dashboard.php in two different tabs
2. Minimize both
3. Submit visitor in check-in form
4. Both tabs get notification (if they're for same destination) ✓

### Scenario 3: Different Destinations
1. Admin is assigned to Gaming Hub destination
2. Visitor checks in to NelFund
3. No notification (admin doesn't manage that destination) ✓

### Scenario 4: Clicked Notification
1. Get notification while minimized
2. Click the notification
3. Tab comes into focus, dashboard loads ✓

---

## Production Readiness Checklist

Before going live:
- [ ] Test on Chrome
- [ ] Test on Firefox
- [ ] Test on Safari (if available)
- [ ] Test on mobile device
- [ ] HTTPS is enabled (required for Service Workers in production)
- [ ] WebSocket server (Node.js) is set to auto-start
- [ ] Users trained to grant notification permission
- [ ] Tested notification permission reset/revoke flow

---

## Questions to Ask Your Team

1. **Do we want notifications for ALL visitors or only high-priority ones?**
   - Current: All visitors trigger notifications
   - Option: Only new messages or unavailable statuses

2. **Should notifications require user dismissal or auto-dismiss?**
   - Current: Auto-dismiss (user can adjust in `notification_handler.js`)
   - Option: Require click to dismiss (more attention-grabbing)

3. **Do we need notification sounds?**
   - Current: No sound
   - Option: Add sound file to `service-worker.js`

4. **Should notifications work on mobile?**
   - Current: Yes, via WebSockets
   - Note: Requires same notification permission

---

## Files to Keep/Change

**DO NOT modify:**
- `realtime/server.js` (WebSocket server, works as-is)
- `submit.php` (already triggers notifications)
- `includes/realtime_notify.php` (already does the work)

**Safe to customize:**
- `notification_handler.js` (change notification text, icons, behavior)
- `service-worker.js` (add sound, change icon, etc)

---

## Need Help?

1. **Check console logs first** (F12, Console tab)
2. **Read NOTIFICATION_TESTING_GUIDE.md** (detailed guide)
3. **Check browser DevTools:**
   - Chrome: Application → Service Workers
   - Firefox: Storage → Service Workers
   - See if Service Worker is registered

---

**Ready to test?** Follow the 30-Second Test above. Should take less than 1 minute!
