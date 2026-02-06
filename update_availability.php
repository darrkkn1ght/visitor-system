<?php
/**
 * Update Availability Status API
 * 
 * AJAX endpoint for admins to update their availability status
 * Supports: available, busy, unavailable, away
 */

require_once 'security_headers.php';
session_start();
require_once 'db.php';

// Optionally include audit logging if available
if (file_exists('audit_logging.php')) {
    require_once 'audit_logging.php';
}

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

// Rate limiting: max 10 status changes per hour
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT COUNT(*) as change_count 
    FROM admin_availability_log 
    WHERE user_id = ? 
    AND changed_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row['change_count'] >= 10) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error' => 'Too many status changes. Please wait before changing again.'
    ]);
    exit;
}

// Get and validate input
$new_status = trim($_POST['status'] ?? '');
$status_message = trim($_POST['message'] ?? '');

$valid_statuses = ['available', 'busy', 'unavailable', 'away'];
if (!in_array($new_status, $valid_statuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit;
}

// Validate message length
if (strlen($status_message) > 255) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Status message too long (max 255 characters)']);
    exit;
}

// Get current status
$stmt = $conn->prepare("SELECT availability_status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$old_status = $user['availability_status'] ?? 'available';
$stmt->close();

// Update user status
$stmt = $conn->prepare("
    UPDATE users 
    SET availability_status = ?, 
        status_message = ?, 
        last_status_change = CURRENT_TIMESTAMP 
    WHERE id = ?
");
$stmt->bind_param("ssi", $new_status, $status_message, $user_id);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to update status']);
    $stmt->close();
    exit;
}
$stmt->close();

// Log the change
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

$stmt = $conn->prepare("
    INSERT INTO admin_availability_log 
    (user_id, old_status, new_status, status_message, ip_address, user_agent) 
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("isssss", $user_id, $old_status, $new_status, $status_message, $ip_address, $user_agent);
$stmt->execute();
$stmt->close();

// Realtime Notification
// We need destination_id for broadcasting to visitors
if (!isset($_SESSION['destination_id'])) {
    // Fetch if missing (should be in session)
    $stmt = $conn->prepare("SELECT destination_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($r = $res->fetch_assoc()) {
        $_SESSION['destination_id'] = $r['destination_id'];
    }
    $stmt->close();
}

require_once 'includes/realtime_notify.php';
$rt_dest_id = $_SESSION['destination_id'] ?? 0;

notify_realtime('availability_updated', $user_id, $rt_dest_id, [
    'status' => $new_status,
    'message' => $status_message,
    'last_status_change' => date('Y-m-d H:i:s'),
    'admin_id' => $user_id,
    'destination_id' => $rt_dest_id,
    'admin_username' => $_SESSION['username'] ?? 'Admin'
]);

// Audit log (optional)
if (function_exists('log_action')) {
    log_action(
        $user_id,
        $_SESSION['username'],
        $_SESSION['role'],
        'STATUS_CHANGE',
        'users',
        $user_id,
        ['old_status' => $old_status],
        ['new_status' => $new_status, 'message' => $status_message],
        "Changed availability status from $old_status to $new_status"
    );
}

// Success response
echo json_encode([
    'success' => true,
    'status' => $new_status,
    'message' => $status_message,
    'timestamp' => date('Y-m-d H:i:s')
]);
