# AVAILABILITY FLOW AUDIT REPORT

**Date:** 2026-02-06  
**Auditor:** Antigravity AI  
**Scope:** Admin Dashboard Availability ↔ Visitor Check-In Flow

---

## Executive Summary

| Feature | Status | Evidence |
|---------|--------|----------|
| Deterministic admin selection | ✅ IMPLEMENTED | Subquery with role priority |
| Fresh availability on selection | ✅ IMPLEMENTED | Async fetch to endpoint |
| CSRF protection | ✅ VERIFIED | `update_availability.php` L35 |
| Rate limiting | ✅ VERIFIED | `update_availability.php` L43-62 |

---

## 1. Deterministic Admin Selection Rule

When multiple admins are assigned to one destination, the system selects ONE using this priority:

| Priority | Criterion |
|----------|-----------|
| 1st | Role: `director` > `super_admin` > `destination_admin` |
| 2nd | Most recent `last_status_change` (DESC) |
| 3rd | Smallest `user.id` (ASC) |

**Implementation (SQL):**
```sql
SELECT u2.id 
FROM users u2 
WHERE u2.destination_id = d.id 
  AND u2.role IN ('destination_admin', 'super_admin', 'director')
ORDER BY 
    FIELD(u2.role, 'director', 'super_admin', 'destination_admin'),
    u2.last_status_change DESC,
    u2.id ASC
LIMIT 1
```

**Files using this rule:**
- `index.php` L16-36 (page load destinations)
- `get_destination_availability.php` L58-71 (fresh fetch)

---

## 2. Fresh Availability Fetch

### Endpoint

```
GET /get_destination_availability.php?destination_id=123
```

**Response:**
```json
{
  "ok": true,
  "destination_id": 123,
  "destination_name": "Directorate",
  "status": "busy",
  "message": "In a meeting until 3pm",
  "source_user": {
    "id": 5,
    "username": "director_user",
    "role": "director"
  }
}
```

**Error response:**
```json
{
  "ok": false,
  "error": "Destination not found"
}
```

### JavaScript Flow

1. Visitor selects destination from dropdown
2. JS shows loading spinner ("Checking availability...")
3. Fetch `get_destination_availability.php?destination_id=X`
4. On success: Update banner with fresh status
5. On error: Fallback to static `data-status` attribute
6. Show check-in form

**Code:** `visitor_checkin.js` L202-245

---

## 3. Data Model

**`users` table columns:**
```sql
availability_status ENUM('available', 'busy', 'unavailable', 'away') DEFAULT 'available'
status_message VARCHAR(255) DEFAULT NULL
last_status_change TIMESTAMP NULL
destination_id INT -- links user to destination
```

**Key constraint:** Availability is stored **PER USER**, mapped to destinations via `destination_id`.

---

## 4. Flow Diagram

```
ADMIN DASHBOARD                         VISITOR CHECK-IN
─────────────────                       ─────────────────
                                        
┌─────────────────┐                     ┌─────────────────┐
│ statusDropdown  │                     │ Destination     │
│ change event    │                     │ dropdown change │
└────────┬────────┘                     └────────┬────────┘
         │                                       │
         ▼                                       ▼
┌─────────────────┐                     ┌─────────────────┐
│ POST update_    │                     │ GET get_dest_   │
│ availability.php│                     │ availability.php│
└────────┬────────┘                     └────────┬────────┘
         │                                       │
         ▼                                       ▼
┌─────────────────┐                     ┌─────────────────┐
│ UPDATE users    │◀────────────────────│ SELECT FROM     │
│ SET status = ?  │                     │ users WHERE ... │
└─────────────────┘                     │ (deterministic) │
                                        └────────┬────────┘
                                                 │
                                                 ▼
                                        ┌─────────────────┐
                                        │ Update banner   │
                                        │ + show form     │
                                        └─────────────────┘
```

---

## 5. Business Rules

| Status | Check-In Allowed? | Form Shown? |
|--------|-------------------|-------------|
| Available | ✅ Yes | Full form |
| Busy | ✅ Yes (warning) | Full form |
| Unavailable | ✅ Yes (warning) | Full form |
| Away | ✅ Yes (warning) | Full form |

> [!NOTE]
> No status blocks check-in. All show full form with appropriate warning banner.

---

## 6. Security Features

| Feature | Status | Evidence |
|---------|--------|----------|
| CSRF validation | ✅ Implemented | `update_availability.php` L35 |
| Rate limiting | ✅ Implemented | 10 changes/hour, L43-62 |
| Input validation | ✅ Implemented | Status enum check L68-73 |
| Prepared statements | ✅ Implemented | All DB queries |
| Authentication | ✅ Implemented | Session check L21-25 |

---

## 7. Files Changed

| File | Change |
|------|--------|
| `index.php` | Deterministic admin selection query |
| `get_destination_availability.php` | **NEW** - Fresh availability endpoint |
| `visitor_checkin.js` | Async fetch on selection, loading state |
| `visitor_checkin_status.css` | Loading spinner animation |

---

## 8. Test Verification Steps

### Test 1: Deterministic Admin Selection
```
1. Create two admins for same destination (one director, one destination_admin)
2. Open visitor check-in page
3. Select that destination
4. Expected: Shows the director's status (higher priority)
```

### Test 2: Fresh Availability
```
1. Open visitor check-in page
2. In another tab, change admin status to "Busy"
3. WITHOUT refreshing, select that destination in check-in page
4. Expected: Shows "Busy" immediately (no page refresh needed)
```

### Test 3: Loading State
```
1. Open visitor check-in page
2. Select any destination
3. Expected: Brief "Checking availability..." spinner before status appears
```

### Test 4: Error Fallback
```
1. Temporarily rename get_destination_availability.php
2. Select a destination
3. Expected: Falls back to static data-status, check-in proceeds
4. Restore file
```
