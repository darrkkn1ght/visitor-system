<?php
session_start();

$admin_user = "admin";
$admin_pass = "password123"; // You can change this later

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === $admin_user && $password === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = "Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html>  
<head>
  <title>Admin Login</title>
  <link rel="stylesheet" href="style.css">
  <style>

    body {
        background-image: url('background.jpg');
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
   <div style="position: absolute; top: 10px; left: 10px;">
    <img src="ui_logo-removebg-preview.png" alt="Logo" style="height: 100px;">
  </div>
</body>
</html>
