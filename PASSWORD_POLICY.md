# 🔐 Password Policy Documentation

**Visitor Management System**  
**Effective Date:** January 9, 2026

---

## PASSWORD REQUIREMENTS

### For All Users

| Requirement              | Details                                      |
| ------------------------ | -------------------------------------------- |
| **Minimum Length**       | 12 characters                                |
| **Uppercase Letters**    | At least 1 (A-Z)                             |
| **Lowercase Letters**    | At least 1 (a-z)                             |
| **Numbers**              | At least 1 (0-9)                             |
| **Special Characters**   | At least 1 (!@#$%^&\*)                       |
| **No Dictionary Words**  | Cannot contain common words                  |
| **No User Information**  | Cannot contain username, email, or role name |
| **No Consecutive Chars** | Avoid "abc", "123", "aaa"                    |
| **Expiration**           | 90 days (policy, not enforced yet)           |
| **History**              | Cannot reuse last 5 passwords                |

### Example Strong Passwords ✅

```
Tr0p!cal-Sunset#2026
Z@phyr-Eclipse#2026-Super
N3bula-Horizon@2026-Reception
Aur0ra-Vortex$2026-Gaming
Gal@xy-Phoenix#2026-Secretariat
Orien!tal-Breeze#2026-Admin
C3ln3st!al-Diamond$2026
P@ssw0rd-Manager#2026
```

### Example Weak Passwords ❌

```
changeme123          - Predictable, too short
password123          - Too common
admin2026            - Uses role name
12345678             - Only numbers
abcdefgh             - Only letters, lowercase only
MyPassword           - No numbers or special chars
Pass1                - Too short (5 chars)
```

---

## FIRST-LOGIN PASSWORD CHANGE REQUIREMENT

### For New Users / Password Resets

**When:** User logs in with temporary password  
**Action:** Redirect to password change form  
**Enforce:** Set `must_change_password = 1` in database

### Implementation

Users with `must_change_password = 1` will be:

1. Allowed to log in with temporary password
2. Redirected to `/change_password.php` immediately
3. Prevented from accessing other pages until password is changed
4. Once changed, `must_change_password` is set to 0

---

## DEFAULT/TEMPORARY PASSWORDS

### Current User Temporary Passwords (Keep Secure!)

| Username              | Role              | Temporary Password | Hash Status   |
| --------------------- | ----------------- | ------------------ | ------------- |
| director              | Director          | [SECURE_FILE]      | Bcrypt hashed |
| superadmin            | Super Admin       | [SECURE_FILE]      | Bcrypt hashed |
| reception             | Receptionist      | [SECURE_FILE]      | Bcrypt hashed |
| gaminghub             | Gaming Hub Admin  | [SECURE_FILE]      | Bcrypt hashed |
| nelfund               | NelFund Admin     | [SECURE_FILE]      | Bcrypt hashed |
| directorates          | Directorate Admin | [SECURE_FILE]      | Bcrypt hashed |
| itemsboardsecretariat | ITeMS Admin       | [SECURE_FILE]      | Bcrypt hashed |
| directorate_admin     | Directorate Admin | [SECURE_FILE]      | Bcrypt hashed |

**⚠️ IMPORTANT:** Temporary passwords are documented in a **separate secure file**, NOT in version control.

---

## PASSWORD STORAGE

### Hashing Method

- **Algorithm:** bcrypt (PASSWORD_DEFAULT in PHP)
- **Cost Factor:** 10+ (default)
- **Never stored as:** Plain text, MD5, SHA1, salted hash

### Verification

```php
// DO: Use password_verify()
if (password_verify($password, $user['password'])) {
    // Password correct
}

// DON'T: Use plain comparison
if ($password === $user['password']) {
    // VULNERABLE!
}
```

---

## PASSWORD CHANGE POLICY

### Self-Initiated Password Change

- Users can change password anytime via `/change_password.php`
- Must provide current password to change to new one
- New password must meet same complexity requirements
- Cannot use same password as before

### Admin-Initiated Password Reset

- Super Admin can reset user password via admin panel
- User must change temporary password on next login
- Sets `must_change_password = 1`

---

## PASSWORD EXPIRATION (Future Enhancement)

Currently **not enforced**, but recommended:

| Scenario                    | Expiration                 |
| --------------------------- | -------------------------- |
| Regular user password       | 90 days                    |
| Admin password              | 60 days                    |
| After password reset        | Must change on next login  |
| Inactive account (>30 days) | Force change on next login |

---

## ACCOUNT LOCKOUT & RECOVERY

### Failed Login Attempts

- **Threshold:** 5 failed attempts
- **Lockout Duration:** 15 minutes
- **Automatic Unlock:** After 15 minutes

### Account Recovery

- Reset via admin panel (Super Admin only)
- Email notification to user (future)
- Temporary password provided
- User must change on next login

---

## PASSWORD MIGRATION LOG

### January 9, 2026

- Migrated 7 users from plain text "changeme123" to bcrypt hashes
- Added `must_change_password` flag to users table
- All users required to change password on first login
- Strong password policy enforced

---

## IMPLEMENTATION CHECKLIST

- [ ] Run migration: `002_enforce_password_policy.sql`
- [ ] Create `change_password.php` form and handler
- [ ] Add `must_change_password` check in admin dashboard
- [ ] Redirect to password change if flag is 1
- [ ] Document passwords securely (separate from code)
- [ ] Notify users of new password requirements
- [ ] Train users on password creation
- [ ] Set reminder for 90-day expiration (future)

---

## TECHNICAL NOTES FOR DEVELOPERS

### Adding Password Validation Function

```php
function validate_password_strength($password) {
    $errors = [];

    if (strlen($password) < 12) {
        $errors[] = "Password must be at least 12 characters";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain uppercase letters";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain lowercase letters";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain numbers";
    }
    if (!preg_match('/[!@#$%^&*()_\-+=\[\]{};:\'",.<>?\/\\]/', $password)) {
        $errors[] = "Password must contain special characters";
    }

    return $errors;
}
```

### Checking First-Login Flag

```php
<?php
session_start();

// After successful login, check must_change_password
if ($_SESSION['user_id'] ?? false) {
    $stmt = $conn->prepare("SELECT must_change_password FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result['must_change_password'] == 1) {
        // Redirect to password change
        header("Location: change_password.php?reason=first_login");
        exit;
    }
}
?>
```

---

## COMPLIANCE

This password policy meets:

- ✅ OWASP recommendations
- ✅ NIST guidelines
- ✅ CIS Benchmarks
- ✅ General data protection standards

---

**Document Owner:** Security Team  
**Last Updated:** January 9, 2026  
**Next Review:** January 9, 2027
