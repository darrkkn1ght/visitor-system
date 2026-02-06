<?php
// create_event.php
require_once 'security_headers.php';
session_start();
include 'db.php';

header('Content-Type: application/json');

// Check Auth
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// CSRF Check
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

// Input Sanitation
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$start_datetime = trim($_POST['start_datetime'] ?? '');
$end_datetime = trim($_POST['end_datetime'] ?? '');
$location = trim($_POST['location'] ?? '');
// $visibility is ignored as per instructions

// Determine Organizer
$organizer = 'Admin';
if (!empty($_SESSION['username'])) {
    $organizer = $_SESSION['username'];
} elseif (!empty($_SESSION['user_id'])) {
    // Optional: fetch username from DB if needed, but session usually has it per login logic
}

if (empty($title) || empty($start_datetime) || empty($end_datetime)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields (Title, Start, End)']);
    exit;
}

// DERIVE DATE AND TIME SLOT
// 1. event_date = DATE(start_datetime)
$timestamp_start = strtotime($start_datetime);
$timestamp_end = strtotime($end_datetime);

if ($timestamp_start === false || $timestamp_end === false) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit;
}

$event_date = date('Y-m-d', $timestamp_start);

// 2. time_slot logic
// - fullday if duration >= 6 hours OR (start <= 10:00 and end >= 16:00)
// - morning if start hour < 12 and end hour <= 13
// - afternoon otherwise

$duration_hours = ($timestamp_end - $timestamp_start) / 3600;
$start_hour = (int) date('H', $timestamp_start); // 0-23
$end_hour = (int) date('H', $timestamp_end);

$time_slot = 'afternoon'; // Default fallthrough

if ($duration_hours >= 6 || ($start_hour <= 10 && $end_hour >= 16)) {
    $time_slot = 'fullday';
} elseif ($start_hour < 12 && $end_hour <= 13) {
    $time_slot = 'morning';
} else {
    $time_slot = 'afternoon';
}

try {
    // Insert strictly into: event_title, organizer_name, venue, event_date, time_slot, description
    $stmt = $conn->prepare("INSERT INTO events (event_title, organizer_name, venue, event_date, time_slot, description) VALUES (?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Bind: ssssss
    $stmt->bind_param(
        "ssssss",
        $title,
        $organizer,
        $location, // venue mapped from location input
        $event_date,
        $time_slot,
        $description
    );

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Event created successfully',
            'event' => [
                'id' => $stmt->insert_id,
                'title' => $title,
                'start' => $start_datetime, // Return what was sent for immediate UI update if needed
                'end' => $end_datetime,
                'date' => $event_date,
                'time_slot' => $time_slot
            ]
        ]);
    } else {
        throw new Exception($stmt->error);
    }
    $stmt->close();

} catch (Exception $e) {
    error_log("Create event error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error saving event',
        'error' => $e->getMessage() // Dev details
    ]);
}
$conn->close();
?>