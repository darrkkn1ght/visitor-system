<?php
/**
 * resolve_message.php
 * Allows admin to resolve, schedule, or add notes to a visitor message
 */
require_once 'security_headers.php';
session_start();

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// CSRF validation
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Invalid security token']);
    exit;
}

include 'db.php';

$visitor_id = isset($_POST['visitor_id']) ? intval($_POST['visitor_id']) : 0;
$action = isset($_POST['action']) ? trim($_POST['action']) : '';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;

if ($visitor_id <= 0) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid visitor ID']);
    exit;
}

// Validate action
$valid_actions = ['resolve', 'schedule', 'add_note'];
if (!in_array($action, $valid_actions)) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid action']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$destination_id = $_SESSION['destination_id'] ?? null;

// Verify the admin has access to this message
// Directors/super_admins can access all; others must match destination or admin_id
if ($user_role !== 'director' && $user_role !== 'super_admin') {
    $check_sql = "SELECT id FROM visitors WHERE id = ? AND (admin_id = ? OR destination = ?)";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("iii", $visitor_id, $user_id, $destination_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Access denied']);
        $check_stmt->close();
        $conn->close();
        exit;
    }
    $check_stmt->close();
}

// Perform the requested action
switch ($action) {
    case 'resolve':
        $sql = "UPDATE visitors SET queue_status = 'resolved', resolved_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $visitor_id);
        break;

    case 'schedule':
        $sql = "UPDATE visitors SET queue_status = 'scheduled' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $visitor_id);
        break;

    case 'add_note':
        if (empty($notes)) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Note content required']);
            exit;
        }
        // Append to existing notes with timestamp
        $timestamp = date('Y-m-d H:i');
        $username = $_SESSION['username'] ?? 'Admin';
        $formatted_note = "[{$timestamp}] {$username}: {$notes}";

        $sql = "UPDATE visitors SET admin_notes = CONCAT(COALESCE(admin_notes, ''), '\n', ?) WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $formatted_note, $visitor_id);
        break;
}

if ($stmt->execute()) {

    // Realtime Notification
    require_once 'includes/realtime_notify.php';

    $payload = [
        'visitor_id' => $visitor_id,
        'action' => $action,
        'queue_status' => ($action === 'resolve') ? 'resolved' : (($action === 'schedule') ? 'scheduled' : null),
        'admin_notes' => $notes, // Only if action is add_note, but simple to send
        'resolved_at' => ($action === 'resolve') ? date('Y-m-d H:i:s') : null,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    // We need admin_id/destination_id to broadcast efficiently.
    // They are not in $_POST. We checked access earlier but didn't store row.
    // Let's refetch minimal info or just broadcast to this admin's scope
    // Use session variables since the actor is the admin
    $actor_admin_id = $_SESSION['user_id'];
    $actor_dest_id = $_SESSION['destination_id'] ?? 0;

    // Note: Ideally we send to the specific admin assigned to the visitor, 
    // but broadcasting to the destination room covers it.
    notify_realtime('message_action', $actor_admin_id, $actor_dest_id, $payload);

    header('Content-Type: application/json');
    echo json_encode([
        'ok' => true,
        'action' => $action,
        'visitor_id' => $visitor_id
    ]);
} else {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Database error']);
}

$stmt->close();
$conn->close();
