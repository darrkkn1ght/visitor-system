<?php
/**
 * change_password.php - Password Change Handler
 * 
 * Features:
 * - Current password verification
 * - Strong password validation
 * - First-login enforcement
 * - Admin password reset handling
 */

require_once 'security_headers.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit;
}

include 'db.php';

// ============================================================
// PASSWORD VALIDATION FUNCTION
// ============================================================
function validate_password_strength($password) {
    $errors = [];
    
    // Minimum length: 12 characters
    if (strlen($password) < 12) {
        $errors[] = "Password must be at least 12 characters";
    }
    
    // Require uppercase
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter (A-Z)";
    }
    
    // Require lowercase
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter (a-z)";
    }
    
    // Require numbers
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number (0-9)";
    }
    
    // Require special characters
    if (!preg_match('/[!@#$%^&*()_\-+=\[\]{}:\'",.<>?\\\\\\/]/', $password)) {
        $errors[] = "Password must contain at least one special character (!@#$%^&*, etc.)";
    }
    
    // Check for common patterns (basic check)
    if (preg_match('/(.)\1{2,}/', $password)) {
        $errors[] = "Password cannot contain repeated characters (aaa, 111, etc.)";
    }
    
    return $errors;
}

$message = null;
$error = null;
$is_first_login = isset($_GET['reason']) && $_GET['reason'] === 'first_login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token validation
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        error_log("CSRF token validation failed in change_password.php from IP: " . $_SERVER['REMOTE_ADDR']);
        die("Request validation failed. Please try again.");
    }

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Get current user info
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect";
    } 
    // Verify new passwords match
    elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match";
    }
    // Validate new password strength
    else {
        $validation_errors = validate_password_strength($new_password);
        if (!empty($validation_errors)) {
            $error = implode("<br>", $validation_errors);
        } 
        // Check if new password is same as current password
        elseif (password_verify($new_password, $user['password'])) {
            $error = "New password cannot be the same as current password";
        }
        else {
            // All validation passed - update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update_stmt = $conn->prepare("UPDATE users SET password = ?, must_change_password = 0 WHERE id = ?");
            $update_stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            
            if ($update_stmt->execute()) {
                error_log("Password changed for user ID: " . $_SESSION['user_id'] . " from IP: " . $_SERVER['REMOTE_ADDR']);
                $message = "Password changed successfully!";
                $update_stmt->close();
                
                // Redirect to dashboard after 2 seconds
                header("Refresh: 2; url=admin_dashboard.php");
            } else {
                error_log("Database error changing password: " . $update_stmt->error);
                $error = "An error occurred while changing your password. Please try again.";
                $update_stmt->close();
            }
        }
    }

    $conn->close();
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="change_password.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="password-container">
        <div class="header">
            <h1>🔐 Change Password</h1>
            <p class="subtitle">Update your account password</p>
            <?php if ($is_first_login): ?>
                <span class="first-login-badge">REQUIRED: First Login</span>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success">✓ <?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">✗ <?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
                <p class="form-hint">Must be different from your current password</p>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="password-requirements">
                <h4>Password Requirements:</h4>
                <ul>
                    <li>✓ At least 12 characters long</li>
                    <li>✓ Uppercase letters (A-Z)</li>
                    <li>✓ Lowercase letters (a-z)</li>
                    <li>✓ Numbers (0-9)</li>
                    <li>✓ Special characters (!@#$%^&*)</li>
                </ul>
            </div>

            <button type="submit" class="btn">Change Password</button>
        </form>

        <?php if ($is_first_login): ?>
            <p class="required-message">
                You are required to change your password before continuing.
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
