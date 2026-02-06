# Realtime Notifications & Polished UX

This document details the implementation of real-time WebSocket notifications and UX improvements for the Visitor System.

## Overview
The system now uses a hybrid approach:
1.  **WebSockets (Node.js)**: For instant updates (latency < 100ms).
2.  **Polling (Fallbacks)**: System continues to function via standard HTTP requests even if the Node server is offline.

## Setup & Running

### Requirements
- Node.js (v14+)
- PHP (with cURL enabled)

### Starting the Server
The realtime server is located in `/realtime`.

```bash
cd realtime
npm install  # Install dependencies (ws)
node server.js
```

The server runs on **Port 3005** by default (to avoid conflicts with standard 3000/3001/8080 ports).

To verify it's running:
1.  Open `http://localhost:3005/` in a browser (Should return 404, but server is listening).
2.  Check logs for "Realtime server running on port 3005".

## Features Implemented

### 1. Instant Admin Availability Updates
- **Trigger**: Admin changes status in Dashboard.
- **Effect**: All Visitors viewing that destination check-in page see the banner update immediately (color, text, message).
- **UX Polish**: "Last updated X minutes ago" text updates live.

### 2. Live Message Queue
- **Trigger**: Visitor submits a message (Away/Unavailable mode).
- **Effect**: Admin Dashboard sidebar badge increments instantly. Queue list updates if open.
- **UX Polish**: Red notification badge appears/animates.

### 3. Queue Actions Sync
- **Trigger**: Admin resolves or schedules a message.
- **Effect**: Removing an item in one tab removes it from other open admin tabs instantly.

### 4. Visual Enhancements
- **Status Dots**: Color-coded dots (Green/Orange/Red/Gray) added to status texts.
- **Timestamps**: "Last updated" text added to Admin Availability card and Visitor Status Banner.

## Files Modified

### Backend (Node.js)
- `realtime/package.json` (New)
- `realtime/server.js` (New)

### Backend (PHP)
- `includes/realtime_notify.php` (New - Helper function)
- `update_availability.php` (Added notification trigger)
- `submit.php` (Added notification trigger for messages)
- `resolve_message.php` (Added notification trigger for actions)

### Frontend
- `assets/js/realtime_client.js` (New - Shared WebSocket client)
- `admin_dashboard.php` (Included client script)
- `admin_dashboard.js` (Added event listeners & update logic)
- `admin_dashboard.css` (Added badge & timestamp styles)
- `index.php` (Included client script)
- `visitor_checkin.js` (Added availability listeners)
- `visitor_checkin_status.css` (Added dot & timestamp styles)

## Troubleshooting
**Q: What if the WebSocket server stops?**
A: The notification system is non-blocking. PHP sends will fail silently (fast timeout). The Frontend will fail to connect silently. The app reverts to standard behavior (page reloads required for updates, polling for queue counts).

**Q: Port 3005 is busy?**
A: Edit `realtime/server.js`, `includes/realtime_notify.php`, and `assets/js/realtime_client.js` to change the port.
