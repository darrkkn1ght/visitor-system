<?php
// get_events.php
require_once 'security_headers.php';
session_start();
include 'db.php';

// Prepare header
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get filter params
$start = $_GET['start'] ?? ''; // Format: YYYY-MM-DD
$end = $_GET['end'] ?? '';     // Format: YYYY-MM-DD

// Validation
if (!$start || !$end) {
    // Default to current month if not specified
    $start = date('Y-m-01');
    $end = date('Y-m-t');
}

try {
    // Query based on provided schema:
    // id, event_title, organizer_name, venue, event_date, time_slot, description

    $query = "
        SELECT 
            id,
            event_title as title,
            organizer_name as organizer,
            venue,
            event_date as date,
            time_slot,
            description
        FROM events 
        WHERE event_date BETWEEN ? AND ?
        ORDER BY event_date ASC
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("ss", $start, $end);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        // Construct event object as requested
        $events[] = [
            'id' => $row['id'],
            'title' => $row['title'], // mapped from event_title
            'organizer' => $row['organizer'], // mapped from organizer_name
            'venue' => $row['venue'],
            'date' => $row['date'],     // event_date
            'time_slot' => $row['time_slot'],
            'description' => $row['description'],
            // Backwards compatibility / Frontend helper fields
            // We can also infer start/end timestamp-like strings if needed, but JS will now use 'date'
        ];
    }

    echo json_encode(['success' => true, 'events' => $events]);

} catch (Exception $e) {
    error_log("Error fetching events: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'error' => $e->getMessage() // Dev mode details
    ]);
}
$conn->close();
?>