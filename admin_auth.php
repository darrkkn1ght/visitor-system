<?php
session_start();
include 'db.php'; // connect to database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Fetch user from users table
    $stmt = $conn->prepare("SELECT id, username, password, role, destination_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // ✅ Verify password (use password_verify when passwords are hashed)
        if ($password === $user['password']) {
            // Save session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['destination_id'] = $user['destination_id'];

            // Redirect to dashboard
            header("Location: admin_dashboard.php");
            exit;
        }
    }

    // If login fails
    echo "<p>Invalid login. <a href='admin_login.php'>Try again</a></p>";
    
    $stmt->close();
    $conn->close();
}
?>
