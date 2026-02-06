<?php
/**
 * Mark Notification as Read API
 * 
 * Helper endpoint to mark a single notification as read
 * Used when a user clicks/expands a notification
 */

require_once 'security_headers.php';
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// CSRF validation
if (!hash_equals($_POST['csrf_token'] ?? '', $_SESSION['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'CSRF token validation failed']);
    exit;
}

$notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;
$user_id = $_SESSION['user_id'];

if ($notification_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid notification ID']);
    exit;
}

// Update notification - ensure it belongs to the current user
$stmt = $conn->prepare("
    UPDATE notifications 
    SET is_read = 1, read_at = CURRENT_TIMESTAMP 
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $notification_id, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Successfully marked as read
        echo json_encode([
            'success' => true,
            'notification_id' => $notification_id,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    } else {
        // Either already read or doesn't exist/belong to user
        // We'll return success anyway to update UI, but note no change
        echo json_encode([
            'success' => true,
            'notification_id' => $notification_id,
            'message' => 'Notification already read or not found'
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

$stmt->close();
$conn->close();
