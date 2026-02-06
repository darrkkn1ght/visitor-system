<?php
require_once 'security_headers.php';
session_start();
include 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if it's an AJAX request
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Update notifications to mark all as read
$sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

$success = $stmt->execute();
$stmt->close();
$conn->close();

if ($is_ajax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success]);
    exit;
}

// Redirect back to dashboard if not AJAX
header("Location: admin_dashboard.php");
exit;
?>