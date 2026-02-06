<?php
/**
 * get_message_queue.php
 * Returns pending visitor messages for the logged-in admin
 * Filters by admin_id match first, then by destination match
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

include 'db.php';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$destination_id = $_SESSION['destination_id'] ?? null;

// Build query based on role
// Directors see all pending messages
// Other admins see messages for their destination
if ($user_role === 'director' || $user_role === 'super_admin') {
    $sql = "
        SELECT 
            v.id,
            v.fullname,
            v.phone_number,
            v.purpose,
            v.visitor_message,
            v.preferred_return,
            v.time_in,
            v.queue_status,
            v.admin_notes,
            v.availability_snapshot,
            v.status_message_snapshot,
            v.admin_id,
            v.destination,
            d.name as destination_name,
            u.username as admin_username
        FROM visitors v
        LEFT JOIN destinations d ON v.destination = d.id
        LEFT JOIN users u ON v.admin_id = u.id
        WHERE v.queue_status IN ('pending', 'scheduled')
          AND (v.is_alternate = 1 OR v.visitor_message IS NOT NULL)
        ORDER BY v.time_in DESC
        LIMIT 50
    ";
    $stmt = $conn->prepare($sql);
} else {
    // Destination admin or receptionist - filter by destination
    $sql = "
        SELECT 
            v.id,
            v.fullname,
            v.phone_number,
            v.purpose,
            v.visitor_message,
            v.preferred_return,
            v.time_in,
            v.queue_status,
            v.admin_notes,
            v.availability_snapshot,
            v.status_message_snapshot,
            v.admin_id,
            v.destination,
            d.name as destination_name,
            u.username as admin_username
        FROM visitors v
        LEFT JOIN destinations d ON v.destination = d.id
        LEFT JOIN users u ON v.admin_id = u.id
        WHERE v.queue_status IN ('pending', 'scheduled')
          AND (v.is_alternate = 1 OR v.visitor_message IS NOT NULL)
          AND (v.admin_id = ? OR v.destination = ?)
        ORDER BY v.time_in DESC
        LIMIT 50
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $destination_id);
}

$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'id' => (int) $row['id'],
        'fullname' => $row['fullname'],
        'phone' => $row['phone_number'],
        'purpose' => $row['purpose'],
        'message' => $row['visitor_message'],
        'preferred_return' => $row['preferred_return'],
        'submitted_at' => $row['time_in'],
        'queue_status' => $row['queue_status'],
        'admin_notes' => $row['admin_notes'],
        'availability_snapshot' => $row['availability_snapshot'],
        'status_message_snapshot' => $row['status_message_snapshot'],
        'destination' => $row['destination_name'],
        'admin_username' => $row['admin_username']
    ];
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode([
    'ok' => true,
    'messages' => $messages,
    'count' => count($messages)
]);
