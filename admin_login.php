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
    else {
        $error = "Invalid credentials";
    }
    
    $stmt->close();
    $conn->close();
  }

?>
<!DOCTYPE html>
<html>  
<head>
  <title>Admin Login</title>
  <link rel="stylesheet" href="style.css">
  <style>

    body {
          background: rgba(255, 255, 255, 0.9);
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
    }

    .login-container {
        background: rgba(255, 255, 255, 0.9);
        padding: 30px;
        border-radius: 15px;
        max-width: 400px;
        margin: 100px auto;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
    }

    .login-title {
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 20px;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 10px 15px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 10px;
        box-sizing: border-box;
        font-size: 16px;
    }

    input[type="text"]::placeholder,
    input[type="password"]::placeholder {
        color: #aaa;
        font-style: italic;
    }

    button {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 10px;
        width: 100%;
        font-size: 16px;
        cursor: pointer;
    }

    button:hover {
        background-color: #0056b3;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-title">Admin Login</div>
    <?php if (isset($error)): ?>
      <p style="color: red; text-align: center;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
      <input type="text" name="username" placeholder="Enter Username" required>
      <input type="password" name="password" placeholder="Enter Password" required>
      <button type="submit">Login</button>
    </form>
  </div>
   <div style="position: absolute; top: 10px; center: 10px;">
    <img src="ui_logo-removebg-preview.png" alt="Logo" style="height: 100px;">
  </div>
</body>
</html>
