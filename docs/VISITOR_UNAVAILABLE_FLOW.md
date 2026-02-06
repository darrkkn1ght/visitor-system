# Visitor Unavailable Flow Documentation

## Overview
The Visitor System now supports a "Message Mode" that allows visitors to leave a message when their destination admin is "Away", "Unavailable", or "Busy". These messages are queued in the admin dashboard for follow-up.

## Status Behavior

| Admin Status | Visitor Experience | Submission Mode | Keycard |
|--------------|--------------------|-----------------|---------|
| **Available** | Standard check-in form. | `checkin` | Assigned |
| **Busy** | Standard check-in form + option to "Leave a message". | `checkin` or `message` | Assigned (if checkin) / None (if message) |
| **Away** | Message form shown by default. Warning if "Proceed Anyway" selected. | `message` (default) or `checkin` | None (if message) / Assigned (if checkin) |
| **Unavailable**| Message form shown by default. Warning if "Proceed Anyway" selected. | `message` (default) or `checkin` | None (if message) / Assigned (if checkin) |

## Database Schema Changes
New columns added to `visitors` table:
- `visitor_message` (TEXT): Content of the message.
- `preferred_return` (DATETIME): Optional callback time.
- `admin_id` (INT): ID of the admin assigned at submission time.
- `availability_snapshot` (ENUM): Admin's status at submission.
- `status_message_snapshot` (VARCHAR): Admin's custom status message.
- `queue_status` (ENUM): 'pending', 'resolved', 'scheduled'.
- `admin_notes` (TEXT): Internal notes added by admin.
- `resolved_at` (DATETIME): Timestamp when resolved.

## API Endpoints

### 1. `get_message_queue.php`
Fetches pending messages for the logged-in admin.
- **Method**: GET
- **Access**: Admin only (Director/Super Admin see all; others filtered by destination/admin ID).
- **Response**: JSON array of messages.

### 2. `resolve_message.php`
Handles actions on queue items.
- **Method**: POST
- **Payload**: `visitor_id`, `action` ('resolve', 'schedule', 'add_note'), `notes` (optional), `csrf_token`.
- **Response**: JSON success/failure.

## Testing Steps

### Visitor Flow
1. **Set Admin Status**: specific admin status (e.g., "Away").
2. **Visit Check-in Page**: Select destination.
3. **Verify UI**: "Leave a Message" panel should appear.
4. **Submit Message**: Fill form, submit.
5. **Verify DB**: Check `visitors` table for `visitor_message` and `queue_status='pending'`.

### Admin Flow
1. **Login**: Login as the destination admin.
2. **Verify Queue**: Check "Messages / Queue" sidebar.
3. **View Message**: Ensure message appears with correct status tag.
4. **Resolve**: Click "Resolve" and confirm item disappears from list.
5. **Add Note**: Click "Note", enter text, verify DB update.

## Files Modified
- `visitor_unavailable_flow_migration.sql` (New)
- `submit.php`
- `index.php`
- `visitor_checkin.js`
- `visitor_checkin.css` (New/Used existing)
- `visitor_checkin_status.css` (New)
- `admin_dashboard.php`
- `admin_dashboard.js`
- `admin_dashboard.css`
- `get_message_queue.php` (New)
- `resolve_message.php` (New)
