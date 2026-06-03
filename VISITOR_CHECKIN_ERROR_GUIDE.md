# Visitor Check-In Form - Complete Error Guide

## Error You're Getting

```json
{
  "success": false,
  "errors": {
    "fullname": "Full name is required",
    "faculty": "Faculty/Organization is required",
    "phone": "Phone number is required",
    "visitor_type": "Invalid visitor type"
  }
}
```

This means the form is submitting, but **the data isn't being sent properly** to the server.

---

## 🔍 Complete List of ALL Possible Errors & What Causes Them

### 1. "Full name is required"

**Error Message:**
```
"fullname": "Full name is required"
```

**What Causes It:**
- [ ] Field is blank/empty
- [ ] JavaScript didn't capture the value
- [ ] Form field name is wrong
- [ ] CSRF token is missing

**How to Fix:**
1. Open F12 (Developer Tools)
2. Go to **Console** tab
3. Type: `document.querySelector('[name="fullname"]').value`
4. Press Enter
5. **Does it show text?** YES → Problem is elsewhere
           NO → Means field value wasn't captured
6. **If NO:** Clear the field and retype slowly, ensuring text appears

---

### 2. "Faculty/Organization is required"

**Error Message:**
```
"faculty": "Faculty/Organization is required"
```

**What Causes It:**
- [ ] Autocomplete field is not capturing value properly
- [ ] Field is still focused (dropdown might be interfering)
- [ ] Selecting from dropdown but field value isn't set
- [ ] Auto-complete JavaScript issue

**How to Fix:**
1. **If clicking dropdown:** Select an option from list (don't just type)
2. **If typing manually:** Type faculty name, wait for dropdown to appear, click the suggestion
3. **In Console (F12):** Type: `document.querySelector('[name="faculty"]').value`
4. Should show the faculty name you selected

---

### 3. "Phone number is required"

**Error Message:**
```
"phone": "Phone number is required"
```

**What Causes It:**
- [ ] Phone field is empty
- [ ] Format validation is rejecting it (needs 7-15 digits)
- [ ] Special characters in phone (only numbers allowed)
- [ ] Field name is `phone_number` not `phone`

**How to Fix:**
1. **Enter only numbers:** 1234567890 (NO dashes, spaces, parentheses)
2. **Length:** Must be 7-15 digits
3. **Example valid numbers:**
   ```
   1234567       (7 digits)
   08012345678   (Nigerian number)
   1-555-123-4567 (NO - will be stripped to 15558901234 which is 11 digits)
   ```
4. **In Console:** `document.querySelector('[name="phone_number"]').value`

---

### 4. "Invalid visitor type"

**Error Message:**
```
"visitor_type": "Invalid visitor type"
```

**What Causes It:**
- [ ] No visitor type selected (no radio button checked)
- [ ] JavaScript issue with radio buttons
- [ ] Radio button not properly captured

**Valid Values Only:**
```
staff
student
guest
vendor
family
other
```

**How to Fix:**
1. **Check the form:** Is one of these selected?
   - ☐ Staff
   - ☐ Student
   - ☐ Guest
2. **In Console:** `document.querySelector('[name="visitor_type"]:checked')`
3. If it shows `null` → Nothing is selected!
4. Click one of the radio buttons and try again

---

### 5. "Purpose contains invalid characters" OR "Purpose is required"

**Error Message:**
```
"purpose": "Purpose contains invalid characters"
   OR
"purpose": "Purpose is required"
```

**What Causes It:**
- [ ] Textarea is empty
- [ ] Contains blocked characters: `<`, `>`, `javascript:`, `onerror=`, `onclick=`
- [ ] Value not being captured

**Valid Examples:**
```
✅ GOOD:
  I need to visit the engineering lab
  Meeting with Dr. Smith about research
  Library study session
  Testing system access

❌ BAD:
  <script>alert('test')</script>
  javascript:alert('xss')
  <img src=x onerror=alert('xss')>
```

**How to Fix:**
1. Type a simple message like: "Meeting with admin"
2. Don't include HTML tags or JavaScript
3. **In Console:** `document.querySelector('[name="purpose"]').value`

---

### 6. "Invalid destination selected"

**Error Message:**
```
"destination": "Invalid destination selected"
```

**What Causes It:**
- [ ] No destination selected
- [ ] Destination select is blank/empty
- [ ] Database has no destinations configured
- [ ] Destination ID is not a number

**How to Fix:**
1. **Click the dropdown:** "Select Destination"
2. **Choose one:** "Gaming Hub", "Director's Office", etc.
3. **Wait:** Page will load admin availability status
4. **Then proceed** to fill other fields
5. **In Console:** `document.querySelector('[name="destination"]').value`
   - Should show a number like: `3` or `5`

---

### 7. "CSRF token validation failed"

**Error Message:**
```
"Request validation failed. Please try again."
```

**What Causes It:**
- [ ] CSRF token is missing from form
- [ ] CSRF token doesn't match session token
- [ ] Session expired
- [ ] Form was loaded in one tab, submitted in another

**How to Fix:**
1. **Reload the entire page:** F5
2. **Fill form fresh** (all fields)
3. **Submit immediately** (don't wait long)
4. **In Console:** `document.querySelector('[name="csrf_token"]')`
   - Should show a hidden input field with a long token
5. If it shows `null` → CSRF field is missing!

---

### 8. "Destination doesn't have available keycards"

**Error Message:**
```html
<message>Number of visitor is exceeded for your destination</message>
```

**What Causes It:**
- [ ] All keycards for that destination are assigned
- [ ] No free keycards available
- [ ] Database hasn't been set up with keycards

**How to Fix:**
1. **Try a different destination**
2. **Check checkout records:** Make sure visitors actually checked out
3. **Database check:** Ask admin to verify keycards exist
4. **Contact admin** to either checkout visitors or add more keycards

---

### 9. "Database connection error"

**Error Message:**
```
"An error occurred. Please try again later."
```

**What Causes It:**
- [ ] Database server is down
- [ ] `db.php` can't connect to MySQL
- [ ] Credentials in `.env` are wrong
- [ ] MySQL service stopped

**How to Fix:**
1. **Check Apache/XAMPP:** Make sure MySQL is running (green indicator)
2. **Check .env file:** Credentials are correct
3. **Try restarting XAMPP:** Stop → Start all services
4. **In browser console:**  Look for specific error message
5. **Server logs:** Check `error_log` file for details

---

### 10. "Validation error: input too short/long"

**Various messages:**
```
"fullname": "Name too short"
"phone": "Phone number invalid format"
"purpose": "Purpose too short"
```

**What Causes It:**
- [ ] Fullname less than 1 character
- [ ] Phone less than 7 or more than 15 digits
- [ ] Purpose less than... (check validation)
- [ ] Faculty less than 5 characters

**Valid Ranges:**
```
Fullname:  1-100 characters
Faculty:   5-150 characters
Phone:     7-15 digits (numbers only)
Purpose:   Minimum ~10 characters probably
```

**How to Fix:**
1. Enter full/complete information
2. Don't abbreviate
3. Use full phone number

---

## 🧪 Complete Testing Checklist

### Before Submitting the Form

- [ ] **Destination:** Selected from dropdown (shows status banner)
- [ ] **Full Name:** Filled with actual name (min 1 char)
  ```
  ✅ John Smith
  ✅ Maria Rodriguez
  ❌ (empty)
  ```

- [ ] **Faculty/Organization:** Selected from autocomplete (5+ chars)
  ```
  ✅ Faculty of Engineering
  ✅ Faculty of Computing
  ❌ (empty)
  ❌ Eng (too short)
  ```

- [ ] **Phone Number:** Numbers only, 7-15 digits
  ```
  ✅ 1234567890
  ✅ 08901234567
  ❌ 123 (too short)
  ❌ 1-800-CALL-ME (letters not allowed)
  ❌ (empty)
  ```

- [ ] **Purpose:** Clear statement, no HTML
  ```
  ✅ Meeting with department head
  ✅ Library access
  ❌ <script>alert('test')</script>
  ❌ (empty)
  ```

- [ ] **Visitor Type:** One selected
  ```
  ✅ ☑ Staff
  ✅ ☑ Student
  ✅ ☑ Guest
  ❌ (none selected)
  ```

---

## 🛠️ Debug Steps (If Still Failing)

### Step 1: Check Console (F12)

```
Press: F12
Go to: Console tab
Look for RED errors or YELLOW warnings
```

**Common console warnings (usually OK):**
```
[Deprecation] Synchronous XMLHttpRequest...
[Violation] Long task blocked...
```

**Critical errors (PROBLEM):**
```
Uncaught TypeError: document.querySelector is null
Uncaught ReferenceError: visitor_checkin is not defined
SecurityError: cross-origin...
```

### Step 2: Check Network Tab (F12)

```
Press: F12
Go to: Network tab
Fill & submit form
Look for POST request to submit.php
```

**Good response:**
```
Status: 302 (redirect)
Response: (empty, means redirect to confirmation.php)
```

**Bad response:**
```
Status: 400
Response: { "success": false, "errors": {...} }
```

### Step 3: Check Form Fields in Console

```javascript
// Check each field:

// Destination
document.querySelector('[name="destination"]').value

// Full Name
document.querySelector('[name="fullname"]').value

// Faculty
document.querySelector('[name="faculty"]').value

// Phone
document.querySelector('[name="phone_number"]').value

// Purpose
document.querySelector('[name="purpose"]').value

// Visitor Type
document.querySelector('[name="visitor_type"]:checked').value

// CSRF Token
document.querySelector('[name="csrf_token"]').value
```

**All should show values, none should be empty**

---

## ✅ How the Form SHOULD Work

### Correct Flow:

```
1. Load index.php
   ↓
2. Select Destination (dropdown)
   ↓ (page shows availability status)
3. Details fields appear
   ↓
4. Fill: Name, Phone, Faculty, Purpose, Type
   ↓
5. Click "Check In" button
   ↓
6. Form submits to submit.php
   ↓
7. Server validates all fields
   ↓
8. SUCCESS: Redirects to confirmation.php
   ↓
9. Shows: Keycard number
```

### What NOT to Do:

```
❌ Fill all fields BEFORE selecting destination
❌ Close browser tab during submission
❌ Click submit multiple times quickly
❌ Reload page mid-submission
❌ Use different browser for form submit
```

---

## 🆘 If Nothing Works

### Nuclear Option: Start Fresh

1. **Clear browser cache:** Ctrl+Shift+Delete
2. **Close all tabs** with the system
3. **Open NEW browser window**
4. **Go to:** `http://192.168.3.65/visitor-system/`
5. **Wait for page to fully load** (check bottom of browser)
6. **Fill form once more**
7. **Submit**

### Check Logs

1. Look at error logs:
   ```
   c:\xampp\apache\logs\error.log
   c:\xampp\mysql\data\error.log (if available)
   ```

2. Check error_log:
   ```
   Open: c:\xampp\htdocs\visitor-system\error_log
   Look for: submit.php or validation errors
   ```

---

## 📊 Error Reference Table

| Error | Cause | Fix |
|-------|-------|-----|
| "Full name is required" | Field empty or not sent | Retype name, reload page |
| "Faculty is required" | Select from dropdown | Click suggestion, don't just type |
| "Phone is required" | Numbers only, 7-15 digits | Enter: 1234567890 |
| "Visitor type is required" | No radio button selected | Click Staff/Student/Guest |
| "Invalid destination" | Nothing selected | Pick destination from dropdown first |
| "CSRF token failed" | Session expired | Reload entire page, resubmit |
| "Purpose invalid" | HTML tags in purpose | Remove <, >, javascript: |
| "Keycards exceeded" | No available keycards | Try different destination |
| "Database error" | Server error | Check MySQL is running |

---

## 🎯 Right Now - Do This:

1. **Reload the page:**
   ```
   Press: F5 or Ctrl+R
   ```

2. **Fill the form EXACTLY like this:**
   ```
   Destination:    Gaming Hub
   Full Name:      John Smith
   Phone:          1234567890
   Faculty:        Faculty of Engineering
   Purpose:        Testing the system
   Visitor Type:   ☑ Guest
   ```

3. **Click:** [Check In]

4. **Did it work?**
   - ✅ YES → Great! You're done testing
   - ❌ NO → Come back to this guide, find your specific error, follow the fix

---

**Good luck! You've got this!** 🚀
