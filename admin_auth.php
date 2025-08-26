<?php
session_start();
$valid_username = "admin";
$valid_password = "password123";

if ($_POST['username'] === $valid_username && $_POST['password'] === $valid_password) {
    $_SESSION['admin_logged_in'] = true;
    header("Location: admin.php");
} else {
    echo "<p>Invalid login. <a href='admin_login.php'>Try again</a></p>";
}
?>
