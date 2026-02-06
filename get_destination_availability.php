<?php
/**
 * Get Destination Availability API
 * 
 * Returns the current availability status for a specific destination.
 * Uses deterministic admin selection:
 *   1. Role priority: director > super_admin > destination_admin
 *   2. Most recent last_status_change
 *   3. Smallest user_id
 * 
 * GET /get_destination_availability.php?destination_id=123
 * 
 * Response:
 * {
 *   "ok": true,
 *   "status": "available|busy|unavailable|away",
 *   "message": "optional status message",
 *   "source_user": { "id": 1, "role": "destination_admin", "username": "admin1" }
 * }
 */

require_once 'security_headers.php';
require_once 'db.php';

header('Content-Type: application/json');

// Validate destination_id
$destination_id = filter_input(INPUT_GET, 'destination_id', FILTER_VALIDATE_INT);

if (!$destination_id || $destination_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'error' => 'Invalid or missing destination_id'
    ]);
    exit;
}

// Check if destination exists
$stmt = $conn->prepare("SELECT id, name FROM destinations WHERE id = ?");
$stmt->bind_param("i", $destination_id);
$stmt->execute();
$dest_result = $stmt->get_result();
$destination = $dest_result->fetch_assoc();
$stmt->close();

if (!$destination) {
    http_response_code(404);
    echo json_encode([
        'ok' => false,
        'error' => 'Destination not found'
    ]);
    exit;
}

// Get deterministic admin availability
// Priority: director > super_admin > destination_admin
// Tiebreaker: most recent last_status_change, then smallest user_id
$sql = "
    SELECT 
        u.id,
        u.username,
        u.role,
        u.availability_status,
        u.status_message,
        u.last_status_change
    FROM users u
    WHERE u.destination_id = ?
      AND u.role IN ('destination_admin', 'super_admin', 'director')
    ORDER BY 
        FIELD(u.role, 'director', 'super_admin', 'destination_admin'),
        u.last_status_change DESC,
        u.id ASC
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $destination_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

// Build response
if ($admin) {
    echo json_encode([
        'ok' => true,
        'destination_id' => $destination_id,
        'destination_name' => $destination['name'],
        'status' => $admin['availability_status'] ?? 'available',
        'message' => $admin['status_message'] ?? '',
        'source_user' => [
            'id' => (int) $admin['id'],
            'username' => $admin['username'],
            'role' => $admin['role']
        ]
    ]);
} else {
    // No admin assigned to this destination
    echo json_encode([
        'ok' => true,
        'destination_id' => $destination_id,
        'destination_name' => $destination['name'],
        'status' => 'available',
        'message' => '',
        'source_user' => null
    ]);
}
