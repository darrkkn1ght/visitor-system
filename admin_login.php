<?php
require_once 'security_headers.php';
session_start();
include 'db.php'; // connect to database
require_once 'audit_logging.php'; // include audit logging

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Check for brute-force lockout (5 attempts in 15 minutes)
    $lockout_threshold = 5;
    $lockout_duration = 15 * 60; // 15 minutes in seconds

    $stmt = $conn->prepare("SELECT COUNT(*) as attempt_count FROM login_attempts 
                           WHERE username = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $attempt_row = $result->fetch_assoc();
    $stmt->close();

    if ($attempt_row['attempt_count'] >= $lockout_threshold) {
        $error = "Too many login attempts. Please try again in 15 minutes.";
        // Log the lockout attempt
        error_log("Login lockout triggered for username: $username from IP: $ip_address");
        // Audit log the lockout
        log_login_attempt($username, false, "Account locked due to multiple failed attempts");
    } else {
        // Fetch user from users table
        $stmt = $conn->prepare("SELECT id, username, password, role, destination_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // ✅ Verify password using password_verify() for secure hashed passwords
            if (password_verify($password, $user['password'])) {
                // Clear any previous failed attempts for this user on successful login
                $clear_stmt = $conn->prepare("DELETE FROM login_attempts WHERE username = ?");
                $clear_stmt->bind_param("s", $username);
                $clear_stmt->execute();
                $clear_stmt->close();

                // Regenerate session ID to prevent session fixation attacks
                session_regenerate_id(true);
                
                // Save session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['destination_id'] = $user['destination_id'];

                // Log successful login
                log_login_attempt($username, true);

                // Redirect to dashboard
                header("Location: admin_dashboard.php");
                exit;
            } else {
                // Log failed login attempt
                $log_stmt = $conn->prepare("INSERT INTO login_attempts (username, ip_address, attempt_time) VALUES (?, ?, NOW())");
                $log_stmt->bind_param("ss", $username, $ip_address);
                $log_stmt->execute();
                $log_stmt->close();
                
                // Audit log failed attempt
                log_login_attempt($username, false, "Invalid password");
                
                $error = "Invalid credentials";
                error_log("Failed login attempt for username: $username from IP: $ip_address");
            }
        } else {
            // Log failed attempt even if user doesn't exist (prevent username enumeration)
            $log_stmt = $conn->prepare("INSERT INTO login_attempts (username, ip_address, attempt_time) VALUES (?, ?, NOW())");
            $log_stmt->bind_param("ss", $username, $ip_address);
            $log_stmt->execute();
            $log_stmt->close();
            
            // Audit log failed attempt
            log_login_attempt($username, false, "User not found");
            
            $error = "Invalid credentials";
            error_log("Failed login attempt for non-existent username: $username from IP: $ip_address");
        }

        $stmt->close();
    }
    
    $conn->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <link rel="stylesheet" href="admin_login.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <!-- Logo -->
  <div class="logo-container">
    <img src="ui_logo-removebg-preview.png" alt="University of Ibadan Logo">
  </div>

  <!-- Login Container -->
  <div class="login-container">
    <div class="login-title">Admin Login</div>
    <?php if (isset($error)): ?>
      <p class="error-message"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <input type="text" name="username" placeholder="Enter Username" required>
      </div>
      <div class="form-group">
        <input type="password" name="password" placeholder="Enter Password" required>
      </div>
      <button type="submit" class="btn btn-full">Login</button>
    </form>
  </div>
</body>
</html>
