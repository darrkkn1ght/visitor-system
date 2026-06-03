# Visual Testing Reference - Browser Notifications

## What You're Testing

**Question:** Can admins get Desktop notifications even when they're not actively viewing the tab?

**Answer:** YES ✅ We just built it. Let's test it.

---

## Expected Browser Behavior

### Step 1: Permission Prompt (First Time Only)

**What You'll See:**
```
┌───────────────────────────────────────────┐
│  localhost wants to show notifications   │
│                                           │
│      [Block]          [Allow]             │
└───────────────────────────────────────────┘
```

**You Do:** Click `[Allow]`

---

### Step 2: Miniaturize Admin Dashboard

**What You'll Do:**
```
Admin Tab is OPEN
[===Dashboard===]  [===Check-in===]

Minimize/Hide the Admin Tab:
                        [===Check-in===]
   (Admin tab is no longer visible)
```

**Why:** This tests that notifications work even when tab is hidden

---

### Step 3: Submit Visitor Form

**What You'll Do:**
```
In Check-in Tab, fill:
  Name: John Smith
  Faculty: Engineering
  Phone: 555-1234
  Destination: Gaming Hub (admin manages this)

Click: [SUBMIT]
```

---

### Step 4: Notification Appears

**What You'll See (Windows):**
```
┌────────────────────────────────────────┐
│ 👤 New Visitor Arrived                  │
│                                         │
│ John Smith is checking in to            │
│ Gaming Hub.                             │
│                                         │
│ Visitor Type: Guest                     │
│ Phone: 555-1234                         │
│                                         │
│  [━━━━━━━━━━━━━━━━] 🕐 In 5 seconds    │
└────────────────────────────────────────┘
```

**What You'll See (Mac):**
```
Notification appears in top-right corner
Same content as above
```

**What You'll See (Mobile):**
```
Notification on lock screen or notification center
Even if phone is locked
```

**THIS IS SUCCESS!** ✅

---

## Console Output Reference

### When Everything Works ✅

**Browser Console (F12 → Console Tab):**

```javascript
// Initial Load
[NotificationHandler] Initializing...
[NotificationHandler] Service Worker registered: ServiceWorkerRegistration {scope: 'http://localhost/', ...}
[NotificationHandler] Requesting notification permission...

// User Grants Permission
[NotificationHandler] Notifications granted by user
[NotificationHandler] Setting up WebSocket listeners...

// WebSocket Connects
[RealtimeClient] Connecting... {clientType: 'admin', role: 'director', adminId: 1, destinationId: 3}
[RealtimeClient] Connected

// Visitor Submits Form
[RealtimeClient] Received [visitor_arrival] {id: 547, fullname: "John Smith", destination_id: 3, ...}
[NotificationHandler] Visitor arrival event: {id: 547, fullname: "John Smith", ...}
[NotificationHandler] Sending notification to Service Worker: 👤 New Visitor Arrived

// Service Worker Shows Notification
[Service Worker] Showing notification: 👤 New Visitor Arrived

// User Clicks Notification
[Service Worker] Notification clicked: 👤 New Visitor Arrived
```

**If you see all of this → EVERYTHING IS WORKING!** ✅

---

## Console Output Reference - Common Errors

### Error 1: Service Worker Won't Register ❌

```
[NotificationHandler] Initializing...
X [NotificationHandler] Service Worker registration failed:
  TypeError: Failed to execute 'register' on 'ServiceWorkerContainer':
  The HTTP scheme is not supported; HTTPS is required

```

**Problem:** Site is not HTTPS (required in production)
**Solution (Dev):** Using localhost is OK. If not on localhost, need HTTPS
**Solution (Prod):** Enable HTTPS on your server

---

### Error 2: Permission Denied ❌

```
[NotificationHandler] Initializing...
[NotificationHandler] Service Worker registered: ...
[NotificationHandler] Requesting notification permission...
[NotificationHandler] Notifications denied by user
```

**Problem:** User clicked "Block" in permission dialog
**Solution:**
1. Click 🔒 in address bar
2. Find "Notifications" setting
3. Change from "Blocked" to "Allow"
4. Reload page

---

### Error 3: WebSocket Not Connected ❌

```
[NotificationHandler] Setting up WebSocket listeners...

... nothing happens ...

(In Network tab, no connection to ws://localhost:3005)
```

**Problem:** Node.js server not running
**Solution:**
1. Open Command Prompt
2. Navigate: `cd c:\xampp\htdocs\visitor-system\realtime`
3. Run: `node server.js`
4. You should see: `Realtime server running on port 3005`
5. Reload admin dashboard page

---

### Error 4: RealtimeClient Not Found ❌

```
[NotificationHandler] Setting up WebSocket listeners...
[NotificationHandler] RealtimeClient not found. Retrying in 1s...
```

**Problem:** `realtime_client.js` didn't load before `notification_handler.js`
**Solution:** Check that `realtime_client.js` is loaded in the same page:
- Open DevTools → Sources
- Look for `realtime_client.js` in the file tree
- Check that loading completed

---

## Testing Checklist

### Before Starting Test

- [ ] Node.js WebSocket server is running
  ```
  Command Prompt: cd realtime && node server.js
  Should see: "Realtime server running on port 3005"
  ```

- [ ] Apache/XAMPP running
  - Test: Open `http://localhost/` in browser
  - Should see XAMPP dashboard or your site

- [ ] Two browser windows/tabs ready
  - Window 1: Admin Dashboard tab
  - Window 2: Visitor Check-in form

- [ ] Developer Console open
  - F12 → Console tab
  - Keep it visible while testing

### During Test

- [ ] Admin dashboard loads without errors
- [ ] Permission prompt appears
- [ ] User clicks "Allow"
- [ ] Console shows `[NotificationHandler] Setting up WebSocket listeners...`
- [ ] Visitor form submits successfully
- [ ] Console shows `[NotificationHandler] Visitor arrival event...`
- [ ] Notification popup appears on screen
- [ ] Notification can be clicked
- [ ] Clicking notification brings admin tab to focus

---

## Step-by-Step Testing Procedure

### 1. Start WebSocket Server
```
Windows:
  - Double-click: c:\xampp\htdocs\visitor-system\run_realtime_server.bat
  - A black command window opens and stays open
  - Look for message: "Realtime server running on port 3005"

Mac/Linux:
  - Terminal: cd <project>/realtime
  - bash: npm install (if first time)
  - bash: node server.js
  - Look for: "Realtime server running on port 3005"

Keep this window/terminal OPEN while testing
```

### 2. Open Admin Dashboard
```
Browser:
  - Go to: http://localhost/visitor-system/admin_dashboard.php
  - Log in with admin credentials
  - Press F12 to open Developer Console
  - Keep this window on-screen for now
```

### 3. Wait for Permission Prompt
```
Expected:
  🔔 Browser asks: "Allow notifications?"

Action:
  [Click Allow]

Expected in Console:
  [NotificationHandler] Notifications granted by user
  [NotificationHandler] Setting up WebSocket listeners...
  [RealtimeClient] Connected
```

### 4. Minimize Admin Tab
```
Action:
  - Minimize admin browser window, OR
  - Switch to another window/application, OR
  - Click another browser tab

Important:
  Admin tab should NOT be visible/focused anymore
```

### 5. Open Visitor Check-in in Second Window
```
Browser (New tab or new window):
  - Go to: http://localhost/visitor-system/
  - You see the public visitor check-in form
```

### 6. Fill & Submit Visitor Form
```
Form Fields:
  Full Name:        "Test Visitor #1"
  Visitor Type:     "Guest"
  Faculty/Org:      "Testing Department"
  Phone:            "1234567890"
  Purpose:          "System testing"
  Destination:      ⚠️ IMPORTANT: Select ADMIN'S DESTINATION
                    (If admin manages "Gaming Hub", select that)

Action:
  Click [Submit]
```

### 7. Watch for Notification
```
Expected on-screen:
  Notification popup appears (even though admin tab was minimized)

┌─────────────────────────────────────┐
│ 👤 New Visitor Arrived              │
│                                     │
│ Test Visitor #1 is checking in to   │
│ Gaming Hub.                         │
│ Visitor Type: Guest                 │
│ Phone: 1234567890                   │
└─────────────────────────────────────┘

Expected in Console:
  [NotificationHandler] Visitor arrival event: {...}
  [NotificationHandler] Sending notification to Service Worker
  [Service Worker] Showing notification: 👤 New Visitor Arrived
```

### 8. Click Notification
```
Action:
  Click on the notification popup

Expected:
  - Notification closes
  - Admin dashboard tab comes into focus
  - Visitor "Test Visitor #1" appears in the records table

Console:
  [Service Worker] Notification clicked: 👤 New Visitor Arrived
```

### 9. SUCCESS! ✅

```
🎉 Browser notifications are working!

The complete flow:
  Visitor checked in
  → WebSocket sent notification
  → Browser received event
  → Service Worker showed popup
  → Admin saw notification even though tab was hidden
  → Clicked notification brings tab to focus
```

---

## Quick Reference: What Files Changed?

```
NEW FILES:
  ✅ service-worker.js (61 lines)
  ✅ notification_handler.js (260 lines)
  ✅ NOTIFICATION_TESTING_GUIDE.md (detailed guide)
  ✅ NOTIFICATION_QUICK_START.md (quick guide)
  ✅ NOTIFICATIONS_SUMMARY.md (overview)
  ✅ VISUAL_TESTING_REFERENCE.md (this file)

MODIFIED FILES:
  ✅ admin_dashboard.php (added 1 line importing notification_handler.js)

UNCHANGED:
  ✅ WebSocket server (realtime/server.js)
  ✅ Database (no schema changes)
  ✅ Visitor check-in (index.php, submit.php)
  ✅ Authentication system
  ✅ Everything else
```

---

## Success Criteria

Your test is **SUCCESSFUL** ✅ if:

- [x] Admin dashboard loads normally
- [x] Permission prompt appears and is granted
- [x] Console shows `[NotificationHandler] Setting up WebSocket listeners...`
- [x] Visitor form submits without error
- [x] A notification popup appears on-screen
- [x] The notification disappears after a few seconds (auto-dismiss)
- [x] Notification contains visitor name and destination
- [x] Clicking notification brings admin tab to focus
- [x] Admin dashboard records table shows the new visitor

**If all of these worked → Congratulations! The feature is fully functional!** 🎉

---

## Need Help?

1. **Check console first** - Most errors will be logged there
2. **Is Node.js running?** - Check command window for "Realtime server running"
3. **Read NOTIFICATION_TESTING_GUIDE.md** - More detailed troubleshooting
4. **Check browser network tab** - Can you see WebSocket connection to :3005?
5. **Try different browser** - If one fails, test in Chrome/Firefox/Edge

---

**Ready to test?** Go to Step 1 above and follow it exactly!
