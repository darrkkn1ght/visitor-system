<?php
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Update notifications to mark all as read
$sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();
$conn->close();

// Redirect back to dashboard
header("Location: admin_dashboard.php");
exit;
?>
