# How to Check Out Old Visitors (Not Checked Out in 1+ Month)

## The Problem

You have visitors who were checked in 1+ month ago and never checked out:

```
Time In: 2025-02-15 09:30
Today: 2025-03-18
Status: Still marked as CHECKED IN ❌
```

The system still has them as active when they should be gone.

---

## ✅ Solution: 4 Simple Steps

### Step 1: Go to Admin Dashboard

```
URL: http://192.168.3.65/visitor-system/admin_dashboard.php
Log in with: Admin or Receptionist account
```

### Step 2: Filter to Show Only Checked-In Visitors

```
Look at the TOP of the page.
Find filter buttons:

[ All Visitors ] [ Checked In ] [ Checked Out ]

Click: [Checked In]
↓
Now only shows visitors who are STILL checked in
(time_out is blank/empty)
```

### Step 3: Look for Old Records

```
Look at the table columns:

| Name | Destination | Time In | Time Out |
|------|-------------|---------|----------|
| John | Gaming Hub  | Feb 15  | — (blank!)
| Mary | IT Center   | Feb 10  | — (blank!)
| Bob  | Director    | Mar 17  | — (blank!)

^ These are the ones you need to checkout

The ones from February are clearly not still here!
```

### Step 4: Check Them Out

```
For EACH old visitor:

1. Find the visitor row in the table
2. Look at the RIGHT side of the row
3. Click the blue button: [CHECK OUT]

System will:
  ✅ Record current time as checkout time
  ✅ Release their keycard
  ✅ Add to backup CSV
  ✅ Log the action

The row will now show:
| John | Gaming Hub  | Feb 15  | Mar 18 14:30 |
                                  ^ Checkout time added!
```

---

## 🎯 SPECIFIC EXAMPLES

### Example 1: Checkout 1 Old Visitor

```
Dashboard shows:

John Smith | Gaming Hub | 2025-02-15 09:30 | — (empty)

Action:
1. Find the [CHECK OUT] button on John's row
2. Click it
3. System asks for confirmation
4. Click [Yes, Check Out]
5. Page refreshes
6. John's time_out now shows: 2025-03-18 14:35
```

### Example 2: Checkout Multiple Old Visitors

```
Dashboard shows:

Mary Johnson | IT Center | 2025-02-10 10:00 | — (empty)
Bob Harris   | Director  | 2025-02-05 11:15 | — (empty)
Alice Brown  | Gaming    | 2025-01-30 14:20 | — (empty)

Action:
Repeat for each:
1. Click [CHECK OUT] on Mary's row
2. Confirm
3. Click [CHECK OUT] on Bob's row
4. Confirm
5. Click [CHECK OUT] on Alice's row
6. Confirm
```

---

## 📊 Column Meanings

| Column | What It Means |
|--------|--------------|
| **Name** | Visitor's full name |
| **Destination** | Where they were going (Gaming Hub, etc) |
| **Time In** | When they checked in |
| **Time Out** | When they left (empty = still here!) |
| **Visitor Type** | staff, student, guest, etc |
| **Keycard** | Physical card number assigned |

---

## 🔍 How to Identify Old Visitors

### Visitors That Need Checkout:
```
Time In Column shows: Old date (1+ month ago)
Time Out Column shows: — (dash or empty)

Examples:
✅ Time In: 2025-01-15 | Time Out: —      (Need to checkout!)
✅ Time In: 2025-02-01 | Time Out: —      (Need to checkout!)
✅ Time In: 2025-02-28 | Time Out: —      (Need to checkout!)
```

### Visitors Already Checked Out:
```
Time Out Column has a DATE & TIME

Examples:
❌ Time In: 2025-02-15 | Time Out: 2025-02-15 16:45  (Already done)
❌ Time In: 2025-03-17 | Time Out: 2025-03-17 17:20  (Already done)
```

---

## ⚡ Quick Steps Summary

```
1. Go to admin_dashboard.php
2. Click filter: [Checked In]
3. Look at Time In column
4. Find visitors from 1+ month ago with empty Time Out
5. Click [CHECK OUT] on each
6. Done!
```

---

## 💡 Why This Matters

**If you DON'T checkout old visitors:**
- ❌ System shows them as still in building
- ❌ Their keycards stay marked as "assigned"
- ❌ New visitors can't get keycards (system thinks all are in use)
- ❌ Audit trail shows they're still here (wrong data)
- ❌ Reports are inaccurate

**If you DO checkout old visitors:**
- ✅ Records are accurate
- ✅ Keycards become available for new visitors
- ✅ System knows true occupancy
- ✅ Audit logs are correct
- ✅ No more "no keycards available" errors

---

## 🗓️ When to Do This

**Recommendation:**
- **Daily:** Check for visitors from yesterday who didn't checkout
- **Weekly:** Check for visitors from last week
- **Monthly:** Check for visitors from 30+ days ago
- **Quarterly:** Bulk cleanup of old records

---

## 🚨 What If Checkout Button Doesn't Work?

### Try This:

**Step 1: Refresh Page**
```
Press: F5
Try checkout again
```

**Step 2: Check Console**
```
Press: F12 (open dev tools)
Go to: Console tab
Look for error messages
```

**Step 3: Check CSRF Token**
```
F12 → Console →
Type: document.querySelector('[name="csrf_token"]').value
Should show a long string
If it shows: null → Reload page
```

**Step 4: Contact Admin**
```
If still broken, contact your system admin
Share error message from console
```

---

## 📝 Example Checkout Session

```
TIME: 2:00 PM

Admin opens dashboard
Sees this filtered to "Checked In":

| John Smith     | Gaming Hub | Feb 15 09:30 | —        | [CHECK OUT]
| Mary Johnson   | IT Center  | Feb 10 10:00 | —        | [CHECK OUT]
| Bob Harris     | Director   | Feb 05 11:15 | —        | [CHECK OUT]

Admin clicks all three [CHECK OUT] buttons

System confirms each one, records time as: Mar 18 14:02

Results in dashboard:

| John Smith     | Gaming Hub | Feb 15 09:30 | Mar 18 14:02 |
| Mary Johnson   | IT Center  | Feb 10 10:00 | Mar 18 14:02 |
| Bob Harris     | Director   | Feb 05 11:15 | Mar 18 14:02 |

All now show checkout time!
System marked keycards as "released"
Available for new visitors!
```

---

## ✨ Going Forward

**To Prevent This Problem:**
1. **Daily checkout reminder** — Check dashboard at end of day
2. **Make checkout policy clear** — Visitors know they must checkout before leaving
3. **Use announcements** — "Please remember to checkout before leaving"
4. **Receptionist training** — Remind visitors during check-in to checkout
5. **Automated emails** — Could send reminder after 7 days checked in (future feature)

---

## 🎯 RIGHT NOW - Do This:

1. **Open:** `http://192.168.3.65/visitor-system/admin_dashboard.php`
2. **Click:** `[Checked In]` filter
3. **Look for:** Visitors with old "Time In" dates and blank "Time Out"
4. **Click:** `[CHECK OUT]` on each one
5. **Done!** ✅

---

**That's it! Now they're checked out and keycards are released.** 🚀
