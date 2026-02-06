<?php
/**
 * Fetch Notifications (Real-time)
 * 
 * Enhanced notification endpoint with real-time polling support
 * Returns unread notifications with visitor context
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

$user_id = $_SESSION['user_id'];
$limit = isset($_GET['limit']) ? min(50, max(1, (int) $_GET['limit'])) : 10;
$unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
$since = isset($_GET['since']) ? (int) $_GET['since'] : 0;

// Build query
$where_clauses = ["n.user_id = ?"];
$params = [$user_id];
$types = "i";

if ($unread_only) {
    $where_clauses[] = "n.is_read = 0";
}

if ($since > 0) {
    $where_clauses[] = "n.id > ?";
    $params[] = $since;
    $types .= "i";
}

$where_sql = implode(" AND ", $where_clauses);

// Get notifications with visitor details
$stmt = $conn->prepare("
    SELECT 
        n.id,
        n.message,
        n.is_read,
        n.notification_type,
        n.priority,
        n.created_at,
        n.read_at,
        v.id as visitor_id,
        v.fullname as visitor_name,
        v.purpose as visitor_purpose,
        v.phone_number as visitor_phone,
        v.time_in as visitor_time_in,
        v.keycard,
        d.name as destination_name
    FROM notifications n
    LEFT JOIN visitors v ON n.visitor_id = v.id
    LEFT JOIN destinations d ON v.destination = d.id
    WHERE $where_sql
    ORDER BY n.created_at DESC
    LIMIT ?
");

$params[] = $limit;
$types .= "i";

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'id' => (int) $row['id'],
        'message' => $row['message'],
        'is_read' => (bool) $row['is_read'],
        'type' => $row['notification_type'],
        'priority' => $row['priority'],
        'created_at' => $row['created_at'],
        'read_at' => $row['read_at'],
        'time_ago' => timeAgo($row['created_at']),
        'visitor' => $row['visitor_id'] ? [
            'id' => (int) $row['visitor_id'],
            'name' => $row['visitor_name'],
            'purpose' => $row['visitor_purpose'],
            'phone' => $row['visitor_phone'],
            'time_in' => $row['visitor_time_in'],
            'keycard' => $row['keycard'],
            'destination' => $row['destination_name']
        ] : null
    ];
}

$stmt->close();

// Get unread count
$stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$unread_count = $result->fetch_assoc()['unread_count'];
$stmt->close();

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unread_count' => (int) $unread_count,
    'timestamp' => time()
]);

/**
 * Convert timestamp to human-readable "time ago" format
 */
function timeAgo($datetime)
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return $diff . ' sec' . ($diff != 1 ? 's' : '') . ' ago';
    }

    $diff = round($diff / 60);
    if ($diff < 60) {
        return $diff . ' min' . ($diff != 1 ? 's' : '') . ' ago';
    }

    $diff = round($diff / 60);
    if ($diff < 24) {
        return $diff . ' hour' . ($diff != 1 ? 's' : '') . ' ago';
    }

    $diff = round($diff / 24);
    if ($diff < 7) {
        return $diff . ' day' . ($diff != 1 ? 's' : '') . ' ago';
    }

    if ($diff < 30) {
        $diff = round($diff / 7);
        return $diff . ' week' . ($diff != 1 ? 's' : '') . ' ago';
    }

    return date('M j, Y', $timestamp);
}
