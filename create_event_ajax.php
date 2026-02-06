<?php
require_once 'security_headers.php';
session_start();

// Check if user is logged in and is super admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

if ($_SESSION['role'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only Super Admins can create events']);
    exit;
}

include 'db.php';

// Validate and sanitize input
$title = trim($_POST['event_title'] ?? '');
$organizer = trim($_POST['organizer_name'] ?? '');
$venue = trim($_POST['venue'] ?? '');
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$slot = $_POST['time_slot'] ?? '';
$description = trim($_POST['description'] ?? '');
$success_message_type = $_POST['success_message'] ?? 'default';
$custom_success_message = trim($_POST['custom_success_message'] ?? '');

// Validation
if (empty($title) || empty($organizer) || empty($venue) || empty($start_date) || empty($end_date) || empty($slot)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
    exit;
}

// Validate dates
if (strtotime($start_date) > strtotime($end_date)) {
    echo json_encode(['success' => false, 'message' => 'Start date cannot be after end date']);
    exit;
}

// Prepare success message
$success_msg = "Event created successfully!";
if ($success_message_type === 'custom' && !empty($custom_success_message)) {
    $success_msg = $custom_success_message;
}

try {
    // Create events for each date in the range
    $current_date = new DateTime($start_date);
    $end_date_obj = new DateTime($end_date);
    $events_created = 0;
    
    $conn->begin_transaction();
    
    while ($current_date <= $end_date_obj) {
        $date_str = $current_date->format('Y-m-d');
        
        $stmt = $conn->prepare("INSERT INTO events (event_title, organizer_name, venue, event_date, time_slot, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $title, $organizer, $venue, $date_str, $slot, $description);
        
        if ($stmt->execute()) {
            $events_created++;
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }
        
        $stmt->close();
        $current_date->modify('+1 day');
    }
    
    $conn->commit();
    
    if ($events_created > 1) {
        $success_msg = "Successfully created {$events_created} events from " . date('M j, Y', strtotime($start_date)) . " to " . date('M j, Y', strtotime($end_date));
    }
    
    echo json_encode(['success' => true, 'message' => $success_msg]);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Error creating events: " . $e->getMessage() . " from IP: " . $_SERVER['REMOTE_ADDR']);
    echo json_encode(['success' => false, 'message' => 'An error occurred while creating the events. Please try again later.']);
}

$conn->close();
?>
