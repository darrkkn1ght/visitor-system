# 🔒 COMPREHENSIVE SECURITY AUDIT REPORT

**Visitor Management System - PHP Application**
**Audit Date:** March 18, 2026
**Scope:** Complete READ-ONLY code review and security analysis
**Assessment Level:** Pre-Production Security Assessment

---

## EXECUTIVE SUMMARY

**Overall Security Posture:** 🟡 **MEDIUM-HIGH RISK**

| Category | Status | Details |
|---|---|---|
| **Critical Issues** | 🔴 4 | Sensitive file exposure, credentials in git, empty DB password, missing CSRF |
| **High Issues** | 🟠 3 | WebSocket auth, rate limiting gaps, file accessibility |
| **Medium Issues** | 🟡 2 | Information leakage, query optimization |
| **Low Issues** | 🔵 0 | None |

**Production Ready:** ⚠️ **NOT READY** - Immediate remediation required for file exposure and credentials

---

## CODEBASE SUMMARY

### File Statistics
- **Total PHP Files:** 65+
- **JavaScript Files:** 14
- **CSS Files:** 10
- **Database Files:** 7 SQL (+migrations)
- **Documentation:** 13 markdown files
- **Node.js Server:** 1 (realtime WebSocket)

### Database Schema (8 Tables)
| Table | Purpose |
|---|---|
| `users` | Admin accounts with bcrypt passwords and role assignments |
| `visitors` | Visitor check-in/checkout records with tokens |
| `notifications` | User notifications with read status |
| `events` | Calendar events with date ranges and time slots |
| `destinations` | Building locations/departments with keycard ranges |
| `keycards` | Physical access cards with assignment status |
| `audit_log` | Immutable security event log with JSON diffs |
| `login_attempts` | Brute-force tracking and lockout management |

### Role-Based Access Control (4 Roles)

```
super_admin
├─ Create/Read/Update/Delete: users, visitors, events, destinations
├─ Download: reports, exports
├─ View: audit logs
└─ Manage: settings

director
├─ Create/Read/Update: visitors, events (all destinations)
├─ Download: reports, exports
├─ View: audit logs
└─ Cannot: manage users, settings

destination_admin
├─ Create/Read/Update: visitors (own destination only)
├─ Create/Read/Update: events (own destination only)
├─ Download: reports, exports (own destination only)
└─ Cannot: view audit logs, manage users

receptionist
├─ Create/Read: visitors (own destination only)
├─ Check-in/Check-out: visitors only
├─ Read: events
└─ Cannot: export, view logs, manage anything
```

### AJAX Endpoints (20+)

| Endpoint | Method | Purpose | Auth | CSRF |
|---|---|---|---|---|
| `/admin_auth.php` | POST | Login authentication | Session | ✅ |
| `/submit.php` | POST | Visitor check-in | Session | ✅ |
| `/checkout.php` | POST | Visitor check-out | Session | ✅ |
| `/create_event_ajax.php` | POST | Create calendar event | Session | ❌ **MISSING** |
| `/update_event.php` | POST | Update event | Session | ✅ |
| `/delete_event.php` | POST | Delete event | Session | ✅ |
| `/fetch_notifications.php` | GET | Get notifications | Session | N/A |
| `/mark_notification_read.php` | POST | Mark notification read | Session | ✅ |
| `/export_csv.php` | GET | Export visitor records | Session | ✅ |
| `/export_data.php` | GET | Export with filters | Session | ✅ |
| `/backup_records.php` | POST | Create backup | Session | Implied |
| `/get_available_destinations.php` | GET | List destinations | Public | N/A |
| `/get_events.php` | GET | Fetch calendar events | Session | N/A |
| `/update_availability.php` | POST | Update admin status | Session | ✅ |
| `/resolve_message.php` | POST | Resolve message | Session | ✅ |

### WebSocket Events

```
Client -> Server:
├─ "hello" (handshake, sends admin_id, role, destination_id)
└─ (no other events defined)

Server -> Client:
├─ "availability_updated" - admin status changed
├─ "new_message" - visitor sent message
├─ "visitor_arrival" - new visitor checked in
└─ (custom events via broadcastEvent)
```

---

## FUNCTIONAL STATUS (Per Module)

| **Module** | **Status** | **Key Assessment** |
|---|---|---|
| **Authentication** | ✅ WORKING | password_verify() used, session_regenerate_id() called, 5/15min brute-force protection |
| **Dashboard** | ✅ WORKING | Proper session checks, CSRF tokens in forms, first-login password change enforced |
| **Visitor Check-in** | ✅ WORKING | Form validation, CSRF protection, keycard assignment, token generation |
| **Visitor Checkout** | ✅ WORKING | Database transaction, keycard release, CSV backup to file |
| **Destinations** | ✅ WORKING | RBAC enforced, admin availability tracking, destination filtering |
| **Events/Calendar** | ⚠️ PARTIAL | Most endpoints secured; `create_event_ajax.php` missing CSRF |
| **Records/Export** | ✅ WORKING | Rate limiting (5/hour), RBAC checks, audit logging, prepared statements |
| **Notifications** | ✅ WORKING | Real-time WebSocket, database persistence, read status tracking |
| **Admin Profile** | ✅ WORKING | First-login password change enforced, form validation, session check |
| **Audit Logs** | ✅ WORKING | Immutable logging, IP tracking, JSON diffs, comprehensive event coverage |
| **Backup** | ✅ WORKING | Rate-limited (3/hour), RBAC enforced, dated files, admin bypass |
| **RBAC** | ✅ WORKING | Well-defined permission matrix, checked on sensitive operations, role helpers |

---

## 🔴 CRITICAL SECURITY FINDINGS

### Finding #1: Sensitive Files Web-Accessible

**Severity:** 🔴 **CRITICAL**
**Files:** Root directory
**Details:**

The following sensitive files are stored in the web-accessible root directory with NO access controls:

1. **TEMP_PASSWORDS.txt** (6,053 bytes)
   - Contains 8 plaintext temporary passwords for user accounts
   - Accessible via: `http://localhost/TEMP_PASSWORDS.txt`
   - Risk: Attacker can download all credentials

2. **visitor_db.sql** (23,213 bytes)
   - Complete database schema with sample data
   - Accessible via: `http://localhost/visitor_db.sql`
   - Risk: Attacker learns table structures, column names, data types

3. **.env** (224 bytes)
   - Database credentials (host, user, name)
   - Accessible via: `http://localhost/.env` (if guessed)
   - Risk: Database connection parameters exposed

4. **checked_out.csv** (63 bytes)
   - Visitor records with fullname, phone, schedule
   - Accessible via: `http://localhost/checked_out.csv`
   - Risk: Personal information disclosure (PII)

5. **presentation_notes.txt** (3,257 bytes)
   - Potentially contains operational details
   - Risk: Information leakage

**Current Protection:** NONE (no .htaccess, no nginx rules, no access controls)

**Immediate Fix:**

Create `.htaccess` in root with:
```apache
# Block access to sensitive files
<FilesMatch "(\.env|TEMP_PASSWORDS\.txt|visitor_db\.sql|checked_out\.csv|presentation_notes\.txt)$">
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
    <IfModule !mod_authz_core.c>
        Order allow,deny
        Deny from all
    </IfModule>
</FilesMatch>

# Block access to backup directories
<Directory /backup>
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
    <IfModule !mod_authz_core.c>
        Order allow,deny
        Deny from all
    </IfModule>
</Directory>

<Directory /backups>
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
    <IfModule !mod_authz_core.c>
        Order allow,deny
        Deny from all
    </IfModule>
</Directory>
```

Or for nginx:
```nginx
location ~ /\. {
    deny all;
}
location ~ \.(env|sql|txt|csv)$ {
    deny all;
}
location /backup { deny all; }
location /backups { deny all; }
```

---

### Finding #2: Credentials & Schema in Git History

**Severity:** 🔴 **CRITICAL**
**Details:**

Files are tracked in git repository:
```bash
$ git log --name-status --all | grep -E "TEMP_PASSWORDS|visitor_db"
A	TEMP_PASSWORDS.txt
A	visitor_db.sql
```

**Impact:** If repository is cloned, forked, or leaked, attacker gains immediate access to:
- All 8 user passwords (plaintext and bcrypt hashes)
- Complete database schema
- Sample visitor data

**Fix:**

1. Remove from git history:
```bash
# Remove files from git history
git filter-branch --tree-filter 'rm -f TEMP_PASSWORDS.txt visitor_db.sql' -- --all

# Force push to origin (if collaborative)
git push origin --force --all
git push origin --force --tags

# Clean reflog
git reflog expire --all --expire=now
git gc --prune=now --aggressive
```

2. Update `.gitignore`:
```
# Credentials and seeds
TEMP_PASSWORDS.txt
TEMP_PASSWORDS*.txt
*.sql
checked_out.csv

# Sensitive environment files
.env
.env.local
.env.*.local
```

3. Notify team to re-clone repository

---

### Finding #3: Empty Database Password

**Severity:** 🔴 **CRITICAL**
**File:** `.env`
**Line:** 5
**Details:**

```
DB_HOST=localhost
DB_PORT=3308
DB_USER=root
DB_PASS=""          # ❌ EMPTY PASSWORD!
DB_NAME=visitor_db
```

**Impact:**
- Any user with network access to MySQL can connect as root
- No password protection on database containing visitor PII
- Violates OWASP and basic security standards

**Fix:**

1. Generate strong password (minimum 16 characters):
```bash
openssl rand -base64 12
# Example output: "aB3xK9mL2pQ7wR4t"
```

2. Set MySQL password:
```sql
ALTER USER 'root'@'localhost' IDENTIFIED BY 'aB3xK9mL2pQ7wR4t';
FLUSH PRIVILEGES;
```

3. Update `.env`:
```
DB_PASS="aB3xK9mL2pQ7wR4t"
```

---

### Finding #4: Missing CSRF Protection on Event Creation

**Severity:** 🔴 **CRITICAL**
**File:** `create_event_ajax.php`
**Lines:** 1-35 (entire CSRF validation section missing)
**Details:**

The endpoint accepts POST requests but never validates CSRF tokens:

```php
<?php
require_once 'security_headers.php';
session_start();

// ❌ NO CSRF VALIDATION - should check $_POST['csrf_token']

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // ... only checks login, not CSRF
}

// Immediately processes POST data without token check
$title = trim($_POST['event_title'] ?? '');
// ...
```

**Attack Scenario:**
1. Attacker creates malicious HTML page:
```html
<form action="http://admin-site/create_event_ajax.php" method="POST" style="display:none;">
    <input name="event_title" value="Fake Event">
    <input name="organizer_name" value="Attacker">
    <input name="venue" value="Fake Location">
    <input name="start_date" value="2026-04-01">
    <input name="end_date" value="2026-04-01">
    <input name="time_slot" value="morning">
</form>
<script>document.forms[0].submit();</script>
```

2. Tricks admin into visiting page while logged in
3. Admin's browser automatically creates fake event
4. Calendar is polluted with false information

**Fix:**

Add CSRF validation at line 18 (after session_start):

```php
// Validate CSRF token
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    http_response_code(403);
    error_log("CSRF validation failed for create_event from IP: " . $_SERVER['REMOTE_ADDR']);
    echo json_encode(['success' => false, 'message' => 'CSRF validation failed']);
    exit;
}
```

Ensure token is passed from form:
```html
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
```

---

## 🟠 HIGH SEVERITY FINDINGS

### Finding #5: WebSocket No Session Validation

**Severity:** 🟠 **HIGH**
**File:** `realtime/server.js`
**Lines:** 52-75
**Details:**

When client connects via WebSocket, server accepts any admin_id and role:

```javascript
ws.on('message', (message) => {
    try {
        const data = JSON.parse(message);

        if (data.type === 'hello') {
            const metadata = {
                clientType: data.client || 'unknown',
                role: data.role || 'guest',
                adminId: parseInt(data.admin_id) || 0,      // ❌ TRUSTS CLIENT VALUE
                destinationId: parseInt(data.destination_id) || 0  // ❌ NO VALIDATION
            };
            clients.set(ws, metadata);
        }
    } catch (e) {
        console.error('WS Error:', e);
    }
});
```

**Attack Scenario (Requires Local Network):**

1. Attacker on same network opens browser to `http://localhost:3005/`
2. Connects WebSocket with:
```javascript
ws = new WebSocket('ws://localhost:3005/ws');
ws.send(JSON.stringify({
    type: 'hello',
    admin_id: 1,  // Claim to be admin_id=1 (director)
    role: 'super_admin'  // Claim to be super admin
}));
```

3. Receives all notifications intended for admin_id=1
4. Can see real-time visitor arrivals, admin status changes

**Impact:** LOW-MEDIUM (requires same network, but no auth mechanism)

**Fix:**

Implement PHP session token exchange:

1. PHP endpoint to get session token:
```php
<?php
// get_ws_token.php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    die('Unauthorized');
}
echo json_encode([
    'token' => hash('sha256', session_id() . $_SESSION['user_id']),
    'admin_id' => $_SESSION['user_id'],
    'role' => $_SESSION['role']
]);
```

2. Server validates token:
```javascript
// server.js
const validTokens = new Map(); // Map<token, {adminId, role, exp}>

// PHP can POST to http://127.0.0.1:3001/register
if (req.url === '/register') {
    // Receive {token, admin_id, role}
    // Store in validTokens with expiration
}

wss.on('connection', (ws) => {
    let authenticated = false;

    ws.on('message', (message) => {
        const data = JSON.parse(message);

        if (data.type === 'hello') {
            // Validate token against stored sessions
            if (validTokens.has(data.token)) {
                const session = validTokens.get(data.token);
                authenticated = true;
                clients.set(ws, {
                    adminId: session.admin_id,
                    role: session.role,
                    // ... other data
                });
            } else {
                ws.close(1008, 'Invalid token');
            }
        } else if (!authenticated) {
            ws.close(1008, 'Not authenticated');
        }
    });
});
```

---

### Finding #6: Visitor Records File Accessibility

**Severity:** 🟠 **HIGH**
**File:** `checked_out.csv`
**Details:**

CSV file containing visitor records is in web-accessible root:
- Location: `/checked_out.csv`
- Accessible: Yes (HTTP download)
- Contents: Fullname, Faculty/Organization, Purpose, Time In, Time Out
- Privacy Level: Contains PII

**Fix:** Move to protected directory and add .htaccess (same as Finding #1)

---

### Finding #7: Backup Directory Contents

**Severity:** 🟠 **HIGH**
**Directories:** `/backup/`, `/backups/`
**Details:**

Contains 14 timestamped CSV files with visitor data:
- `backups/visitors_20250722_1300.csv`
- `backups/visitors_20250723_1600.csv`
- ... (12 more files)

**Accessibility:** Not blocked; if attacker knows directory name, can enumerate and download

**Fix:** Add .htaccess protection to both directories (see Finding #1)

---

## 🟡 MEDIUM SEVERITY FINDINGS

### Finding #8: No Rate Limiting on Event Operations

**Severity:** 🟡 **MEDIUM**
**Files:** `create_event_ajax.php`, `update_event.php`, `delete_event.php`
**Details:**

Rate limiting is only applied to:
- CSV exports: 5/hour
- Backup records: 3/hour

Missing rate limits on:
- Event creation (can spam calendar)
- Event deletion (can wipe calendar)
- Event updates (can deface calendar)
- Notification operations

**Attack Impact:** Calendar DoS (denial of service)

**Fix:**

Add rate limiting to event endpoints:

```php
// In create_event_ajax.php, after session_start():
require_once 'rate_limiting.php';

// Add new rate limit type in rate_limiting.php:
$RATE_LIMITS['create_event'] = [
    'limit' => 10,      // 10 events per hour
    'window' => 3600,
    'name' => 'Event Creation'
];

// Then enforce in endpoint:
$rate_limit = enforce_rate_limit(
    'create_event',
    $_SESSION['user_id'],
    $_SESSION['username'],
    $_SERVER['REMOTE_ADDR']
);
```

---

### Finding #9: Complex Destination Query

**Severity:** 🟡 **MEDIUM**
**File:** `index.php`
**Lines:** 16-38
**Details:**

Complex LEFT JOIN with subquery for each destination:

```php
$sql = "
    SELECT d.id, d.name, u.availability_status, ...
    FROM destinations d
    LEFT JOIN users u ON u.id = (
        SELECT u2.id
        FROM users u2
        WHERE u2.destination_id = d.id
          AND u2.role IN ('destination_admin', 'super_admin', 'director')
        ORDER BY FIELD(u2.role, 'director', 'super_admin', 'destination_admin'),
                 u2.last_status_change DESC, u2.id ASC
        LIMIT 1
    )
    ORDER BY d.name ASC
";
```

**Issue:** Subquery runs for each destination (N+1 query problem)

**Fix (Optional):**

Cache destination list in session or use JOIN:

```php
// Option 1: Cache (simple)
if (empty($_SESSION['destinations_cache']) || time() - $_SESSION['destinations_cache_time'] > 300) {
    // ... fetch fresh
    $_SESSION['destinations_cache'] = $destinations;
    $_SESSION['destinations_cache_time'] = time();
}

// Option 2: Better SQL
SELECT d.id, d.name, u.availability_status, u.status_message
FROM destinations d
LEFT JOIN users u ON d.id = (
    SELECT destination_id FROM users u2
    WHERE u2.destination_id = d.id ...
);
```

---

## SECURITY CONTROLS ASSESSMENT

### A. SQL Injection ✅ PROTECTED

**Status:** All queries use prepared statements

**Evidence:**
- ✅ `submit.php`: Lines 138-161 uses bind_param
- ✅ `checkout.php`: Lines 23-48 uses bind_param
- ✅ `export_csv.php`: Lines 64-76 uses bind_param
- ✅ `admin_auth.php`: Lines 11-22 uses bind_param
- ✅ `create_event_ajax.php`: Lines 60-61 uses bind_param

**Verdict:** **SECURE** - No SQL injection vulnerabilities found

---

### B. Cross-Site Scripting (XSS) ⚠️ MOSTLY PROTECTED

**Status:** Output escaping implemented; some consistency needed

**Protected Examples:**
```php
// records.php lines 94-98
<?= htmlspecialchars($row['fullname']) ?>
<?= htmlspecialchars($row['faculty_organization']) ?>

// admin_login.php line 124
<?= htmlspecialchars($error) ?>
```

**Escaping Helper:** `output_escaping.php` provides:
- `esc_html()` - Escape for HTML content
- `esc_attr()` - Escape for HTML attributes
- `esc_js()` - Escape for JavaScript
- `esc_json()` - Escape for JSON
- `esc_url_param()` - URL encode

**Recommendation:** Consistently use `output_escaping.php` functions instead of mixing approaches

**Verdict:** **MOSTLY SECURE** - No XSS vulnerabilities found, but could standardize escaping

---

### C. CSRF Protection ⚠️ PARTIALLY IMPLEMENTED

**Status:** CSRF tokens on most endpoints; one critical gap

**Protected Endpoints:**
- ✅ `submit.php` - lines 54-58
- ✅ `checkout.php` - lines 7-11
- ✅ `index.php` - lines 8-10 (token generation)
- ✅ `change_password.php` - validated
- ✅ `update_event.php` - lines 15-19
- ✅ `delete_event.php` - lines 15-19
- ✅ `admin_dashboard.php` - lines 20-22
- ✅ `update_availability.php` - validated
- ✅ `mark_notification_read.php` - validated
- ✅ `resolve_message.php` - validated

**Unprotected Endpoints:**
- ❌ `create_event_ajax.php` - **NO CSRF VALIDATION** (CRITICAL)

**Implementation:**
```php
// Standard CSRF check pattern
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    http_response_code(403);
    die("CSRF validation failed");
}
```

**Verdict:** **MEDIUM RISK** - One critical endpoint missing CSRF protection

---

### D. Authentication & Session Security ✅ SECURE

**Password Hashing:**
- ✅ Uses `password_hash()` with PASSWORD_DEFAULT (bcrypt)
- ✅ Uses `password_verify()` for comparison
- ✅ All passwords in database are bcrypt hashes (cost 10)

**Session Management:**
- ✅ `session_regenerate_id(true)` called immediately after login (admin_auth.php:51)
- ✅ Secure cookie flags set: HttpOnly, Secure, SameSite=Strict
- ✅ 30-minute session timeout (session.gc_maxlifetime=1800)
- ✅ Uses session_start() on all protected pages

**Brute-Force Protection:**
- ✅ 5 failed attempts trigger 15-minute lockout (admin_login.php:15-26)
- ✅ Lockout tracked in `login_attempts` table
- ✅ Clear on successful login
- ✅ Logged to audit_log table

**First-Login Password Change:**
- ✅ `must_change_password` flag in users table
- ✅ Enforced in admin_dashboard.php (lines 33-36)
- ✅ Redirects to change_password.php
- ✅ Password policy validated: 12+ chars, mixed case, numbers, special chars

**Audit Logging:**
- ✅ All logins logged (successful & failed)
- ✅ Lockouts logged
- ✅ User role changes logged
- ✅ IP addresses tracked

**Verdict:** **SECURE** - Excellent authentication implementation

---

### E. HTTP Security Headers ✅ COMPREHENSIVE

**File:** `security_headers.php`

**Implemented Headers:**
```php
// HTTPS Enforcement
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload

// Clickjacking Protection
X-Frame-Options: DENY

// MIME Sniffing Protection
X-Content-Type-Options: nosniff

// Referrer Policy
Referrer-Policy: strict-origin-when-cross-origin

// Content Security Policy
Content-Security-Policy: default-src 'none';
    script-src 'self' 'unsafe-inline';
    style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
    img-src 'self' data:;
    font-src 'self' https://fonts.gstatic.com data:;
    connect-src 'self' ws: wss:;
    form-action 'self';
    base-uri 'self';
    frame-ancestors 'none';
    manifest-src 'self';

// Permissions Policy
Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()
```

**Session Configuration:**
```php
session.cookie_secure = 1          // HTTPS only
session.cookie_httponly = 1        // No JavaScript access
session.use_strict_mode = 1        // Reject uninitialized SIDs
session.gc_maxlifetime = 1800      // 30-minute timeout
session.cookie_samesite = Strict   // CSRF mitigation
```

**Note:** CSP uses `'unsafe-inline'` for scripts (not ideal, but acceptable for PHP templates)

**Verdict:** **GOOD** - Comprehensive header implementation

---

### F. RBAC Enforcement ✅ STRONG

**System:** Role-Based Access Control in `rbac.php`

**Permission Matrix:**
```php
$PERMISSIONS = [
    'super_admin' => ['users' => ['create','read','update','delete','role_change'], ...],
    'director' => ['users' => ['read'], 'visitors' => ['create','read','update','checkin','checkout'], ...],
    'destination_admin' => ['users' => ['read'], 'visitors' => [...] // own destination only],
    'receptionist' => ['users' => [], 'visitors' => ['create','read','checkin','checkout']] // own destination
];
```

**Enforcement Examples:**
- ✅ `create_event_ajax.php` line 12: `if ($_SESSION['role'] !== 'super_admin')`
- ✅ `export_csv.php` line 17: `if (!can_export_reports($_SESSION['destination_id']))`
- ✅ `audit_logs_viewer.php`: Checks `can_view_audit_logs()`

**Destination Isolation:**
- ✅ destination_admin can only see/manage own destination
- ✅ receptionist limited to own destination
- ✅ super_admin and director see all destinations

**Verdict:** **WELL-IMPLEMENTED** - Strong RBAC system

---

### G. File Upload Security ✅ N/A

**Status:** No file upload functionality in application

**Note:** Backup system uses CSV export only, no file upload

**Verdict:** **NOT APPLICABLE**

---

### H. Rate Limiting ✅ PARTIAL

**Implemented in `rate_limiting.php`:**

| Operation | Limit | Window |
|---|---|---|
| export_csv | 5 | 1 hour |
| export_data | 5 | 1 hour |
| backup_records | 3 | 1 hour |

**Admin Bypass:** Admins (super_admin, director) bypass all limits

**Database Tracking:** `rate_limit_log` table with indexes

**Missing Rate Limits:**
- Event creation (should have limit)
- Event deletion (should have limit)
- Notification operations (should have limit)

**Verdict:** **PARTIAL COVERAGE** - Covers sensitive operations; add to event operations

---

### I. Audit Logging ✅ EXCELLENT

**System:** Comprehensive audit logging in `audit_logging.php`

**Tracked Events:**
- ✅ LOGIN / LOGIN_FAILED
- ✅ User creation, modification, deletion
- ✅ Role changes (with before/after tracking)
- ✅ Visitor check-in / check-out
- ✅ Data exports (with record count)
- ✅ Security events (permission denied, rate limit exceeded, etc.)

**Data Captured:**
- User ID, username, role
- IP address (handles proxies)
- User agent
- Session ID
- Action timestamp
- Old/new values (JSON)
- Error messages (on failure)

**Storage:** Immutable `audit_log` table (InnoDB)

**Example Query:**
```sql
SELECT * FROM audit_log
WHERE created_at >= NOW() - INTERVAL 7 DAY
  AND user_role = 'super_admin'
ORDER BY created_at DESC;
```

**Verdict:** **EXCELLENT** - Comprehensive audit trail

---

### J. Information Leakage ⚠️ MEDIUM RISK

**Error Handling:**
- ✅ DB errors logged server-side only (error_log)
- ✅ User sees generic messages: "An error occurred"
- ✅ No stack traces exposed
- ✅ No table names in error messages

**File Exposure:**
- ❌ TEMP_PASSWORDS.txt accessible
- ❌ visitor_db.sql accessible
- ❌ .env accessible (if guessed)
- ❌ checked_out.csv accessible
- ❌ /backup/ and /backups/ directories listable

**Verbose Output:**
- ✅ Admin login page shows generic "Invalid credentials"
- ✅ No username enumeration
- ✅ Failed login doesn't reveal if user exists

**HTTP Headers:**
- ✅ X-Powered-By likely suppressed
- ✅ Server header likely hidden (PHP 8.2 default)

**Verdict:** **MEDIUM RISK** - Error handling good, but file exposure critical

---

## TEMP_PASSWORDS.txt VERDICT

### 🔴 CRITICAL - MUST BE DELETED IMMEDIATELY

**File Status:**
- Location: Root directory (`/TEMP_PASSWORDS.txt`)
- Size: 6,053 bytes
- Permissions: `-rw-r--r--` (world-readable)
- Web-Accessible: **YES**
- In Git History: **YES** (commit A, not yet pushed if local)

**Contents:**

```
================================================================================
User: director
Temporary Password: UGyR0ly&eo@&KFf#
Bcrypt Hash: $2y$10$xJvvXrzAFyviXDzWwsglFuZJ5cgOXsADqI57zEZW/CFIfcuizHGHS
================================================================================

User: superadmin
Temporary Password: GP!N2n$j!G1tCcCz
Bcrypt Hash: $2y$10$oDL4B..8fQ/STSKIFDpwrO/q5v7pZdEpY/W1mbcdADsXw/Ii9IxoS
================================================================================

User: reception
Temporary Password: 2d%aD$kw2ZoYU@fp
Bcrypt Hash: $2y$10$.NuJqHFbLl4eXBJHmUXLuedcUjt59fRxKMaCZbY3qU8fAvrmRbivK
================================================================================

User: gaminghub
Temporary Password: *dG4Zcqa&#c1qv8S
Bcrypt Hash: $2y$10$EuGlMc90zj2H5ZrH34HARuxQK2zW9jbt1aHAVO/KDnx1KjkhhX/Ia
================================================================================

User: nelfund
Temporary Password: otPz4f9*rhc7&!X4
Bcrypt Hash: $2y$10$MsFFqFCcwVHxXgr9hqg2LeMDpLwxehSbIBwvtPHaZ.AYiKQWYr/tC
================================================================================

User: directorates
Temporary Password: Rw7mY1o%gP$u9EWF
Bcrypt Hash: $2y$10$Z52oJ6nzJu7nv/Evp/NUCuR3nHyIv3SoCfSlGbzlIcYRUmK.hmGpW
================================================================================

User: itemsboardsecretariat
Temporary Password: M#3v3dhmbnMM3*mM
Bcrypt Hash: $2y$10$nBlefCaqt29xiV1LTay9nOjpBxWWbC61b3sG0qLYGWD9Gnf5qtJaG
================================================================================

User: directorate_admin
Temporary Password: 0C8n#XO9CGdjt*3U
Bcrypt Hash: $2y$10$hd63uO0EcNHje4LL5lD1AOXABXa90tQ7ujUMsE6tWoxEBPbs9LVCK
================================================================================
```

### Why It's Critical:

1. **Web-Accessible:** Attacker downloads via `GET /TEMP_PASSWORDS.txt`
2. **Plaintext Passwords:** All credential pairs exposed
3. **In Git:** Repository clone gives attacker credentials
4. **Compliance Violation:** GDPR, OWASP, PCI-DSS all forbid plaintext creds
5. **Active Threats:** Passwords may still be valid if users haven't completed first login

### Immediate Response:

**Step 1:** Delete the file
```bash
rm TEMP_PASSWORDS.txt
```

**Step 2:** Remove from git history
```bash
git filter-branch --tree-filter 'rm -f TEMP_PASSWORDS.txt' -- --all
git push origin --force --all
git reflog expire --all --expire=now
git gc --prune=now --aggressive
```

**Step 3:** Verify users changed passwords
```sql
SELECT username, must_change_password FROM users;
-- Should all show 0 (must_change_password = 0)
```

**Step 4:** Check for unauthorized access
```sql
SELECT * FROM audit_log
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
  AND (action = 'LOGIN_FAILED' OR error_message LIKE '%password%');
```

**Step 5:** Force password reset if compromised
```sql
UPDATE users SET must_change_password = 1 WHERE id IN (1, 5, 7, 9, 10, 11, 17, 21);
```

---

## TOP 10 PRIORITY FIXES

| **#** | **Fix** | **Priority** | **Effort** | **Risk** |
|---|---|---|---|---|
| **1** | Delete TEMP_PASSWORDS.txt and remove from git history | 🔴 **NOW** | 5 min | CRITICAL |
| **2** | Create .htaccess to block sensitive file access | 🔴 **NOW** | 10 min | CRITICAL |
| **3** | Set strong DB password in .env (currently blank) | 🔴 **NOW** | 5 min | CRITICAL |
| **4** | Add CSRF token validation to create_event_ajax.php | 🔴 **TODAY** | 15 min | CRITICAL |
| **5** | Move checked_out.csv outside webroot or protect with .htaccess | 🔴 **TODAY** | 20 min | HIGH |
| **6** | Implement session token validation in WebSocket (server.js) | 🟠 **WEEK** | 1-2 hours | HIGH |
| **7** | Add rate limiting to event creation/deletion endpoints | 🟠 **WEEK** | 30 min | MEDIUM |
| **8** | Add .gitignore entries for all SQL, CSV, temp files | 🟠 **WEEK** | 5 min | MEDIUM |
| **9** | Review and standardize all output escaping (use output_escaping.php) | 🟡 **MONTH** | 2 hours | LOW |
| **10** | Optimize destination query to avoid N+1 problem | 🟡 **MONTH** | 1 hour | LOW |

---

## DOCUMENTATION vs CODE DISCREPANCIES

### Previous Issues (January 2026)

**docs/SECURITY_AUDIT_REPORT.md** claimed these issues:

| Issue | Status | Resolution |
|---|---|---|
| Plain text password storage | ❌ FIXED | Now using bcrypt password_verify() |
| SQL injection in queries | ❌ FIXED | All using prepared statements |
| No session regeneration | ❌ FIXED | session_regenerate_id() called on login |
| No CSRF protection | ❌ MOSTLY FIXED | Most endpoints protected, except create_event_ajax.php |
| Missing output escaping | ❌ FIXED | htmlspecialchars() and output_escaping.php available |
| Weak access control | ❌ FIXED | RBAC system well-implemented |

**Verdict:** Most critical security issues have been remediated since January. However, new issues (sensitive file exposure, empty DB password) were not addressed.

---

## SUMMARY & RECOMMENDATIONS

### Current Security Posture: 🟡 **MEDIUM-HIGH RISK**

**Strengths:**
- ✅ Proper password hashing (bcrypt)
- ✅ Session regeneration on login
- ✅ CSRF protection on most endpoints
- ✅ No SQL injection vulnerabilities
- ✅ Strong RBAC system
- ✅ Comprehensive audit logging
- ✅ Proper security headers
- ✅ Rate limiting on sensitive operations
- ✅ Brute-force protection with lockouts

**Critical Weaknesses:**
- 🔴 Sensitive files accessible via web (credentials, schema, visitor data)
- 🔴 Credentials in git history
- 🔴 Empty database password
- 🟠 One unprotected CSRF endpoint (event creation)
- 🟠 WebSocket lacks session validation
- 🟠 No rate limiting on event operations

### Production Readiness: ⚠️ **NOT READY**

**Must Fix Before Production:**
1. Delete TEMP_PASSWORDS.txt and remove from git
2. Protect sensitive files with .htaccess
3. Set database password
4. Add CSRF to create_event_ajax.php
5. Move checked_out.csv outside webroot

**Should Fix Before Production:**
1. Add session validation to WebSocket
2. Add rate limiting to event endpoints
3. Update .gitignore for future commits

**Nice to Have:**
1. Standardize output escaping
2. Optimize database queries
3. Enhanced logging and monitoring

---

## Audit Methodology

**Approach:** READ-ONLY code review without executing any code

**Files Reviewed:**
- All PHP endpoints (65+ files)
- All JavaScript files (14 files)
- All CSS files (10 files)
- Database schema (visitor_db.sql)
- Configuration files (.env, db.php)
- Security utilities (rbac.php, audit_logging.php, rate_limiting.php, etc.)
- Documentation (13 markdown files)
- WebSocket server (realtime/server.js)

**Tools Used:**
- File reading and content analysis
- Pattern matching and grep searches
- Git history inspection
- File system inspection

**Out of Scope:**
- Penetration testing
- Automated vulnerability scanning
- Database modification
- Code execution
- Network testing

---

**Report Generated:** March 18, 2026
**Audit Status:** Complete
**Data Protection:** READ-ONLY analysis - no files modified

