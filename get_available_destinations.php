<?php
/**
 * Get Available Destinations API
 * 
 * Returns list of destinations with admin availability status
 * Used by visitor check-in form to show real-time availability
 */

require_once 'security_headers.php';
require_once 'db.php';

header('Content-Type: application/json');

// Get all destinations with admin availability
$stmt = $conn->prepare("
    SELECT 
        d.id,
        d.name,
        u.availability_status,
        u.status_message,
        u.username as admin_username,
        CONCAT(
            UPPER(SUBSTRING(u.username, 1, 1)),
            SUBSTRING(u.username, 2)
        ) as admin_name
    FROM destinations d
    LEFT JOIN users u ON d.id = u.destination_id AND u.role IN ('destination_admin', 'super_admin', 'director')
    ORDER BY d.name ASC
");

$stmt->execute();
$result = $stmt->get_result();

$destinations = [];
while ($row = $result->fetch_assoc()) {
    $destinations[] = [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'status' => $row['availability_status'] ?? 'available',
        'status_message' => $row['status_message'],
        'admin_name' => $row['admin_name'] ?? 'No admin assigned',
        'is_available' => ($row['availability_status'] === 'available' || $row['availability_status'] === null)
    ];
}

$stmt->close();

echo json_encode([
    'success' => true,
    'destinations' => $destinations,
    'timestamp' => date('Y-m-d H:i:s')
]);
