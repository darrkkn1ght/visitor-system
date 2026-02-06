<?php
require_once 'security_headers.php';
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    exit;
}

include 'db.php';

// Get current month and year
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Fetch events for this month
$stmt = $conn->prepare("SELECT id, event_title, organizer_name, venue, event_date, time_slot FROM events WHERE MONTH(event_date) = ? AND YEAR(event_date) = ?");
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$result = $stmt->get_result();
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[$row['event_date']][] = $row;
}
$stmt->close();

// Set content type header
header('Content-Type: application/javascript');
echo "// Events data for calendar\n";
echo "window.eventsData = " . json_encode($events) . ";";
?>
