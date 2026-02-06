<?php
// update_event.php
require_once 'security_headers.php';
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$id = intval($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$start_datetime = trim($_POST['start_datetime'] ?? '');
$end_datetime = trim($_POST['end_datetime'] ?? '');
$location = trim($_POST['location'] ?? '');
$visibility = trim($_POST['visibility'] ?? 'internal');

if ($id <= 0 || empty($title) || empty($start_datetime)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Sync legacy columns
$legacy_date = date('Y-m-d', strtotime($start_datetime));

try {
    $stmt = $conn->prepare("UPDATE events SET 
        title = ?, event_title = ?,
        description = ?,
        start_datetime = ?, end_datetime = ?,
        location = ?, venue = ?,
        visibility = ?,
        event_date = ?
        WHERE id = ?");

    $stmt->bind_param(
        "sssssssssi",
        $title,
        $title,
        $description,
        $start_datetime,
        $end_datetime,
        $location,
        $location,
        $visibility,
        $legacy_date,
        $id
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
    } else {
        throw new Exception($stmt->error);
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Update event error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating event']);
}
$conn->close();
?>