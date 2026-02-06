<?php
require_once 'security_headers.php';
session_start();
include 'db.php';
require_once 'audit_logging.php';
require_once 'filename_validation.php';
require_once 'rbac.php';
require_once 'rate_limiting.php';

// Verify user is logged in and has permission to export
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Check permission to export reports
if (!can_export_reports($_SESSION['destination_id'] ?? null)) {
    error_log("Unauthorized export attempt by user: " . $_SESSION['username']);
    log_security_event('unauthorized_export', ['user' => $_SESSION['username']], 'MEDIUM');
    die("Access denied. You do not have permission to export data.");
}

// Check rate limit for CSV exports
$rate_limit_result = enforce_rate_limit(
    'export_csv',
    $_SESSION['user_id'],
    $_SESSION['username'],
    $_SERVER['REMOTE_ADDR']
);

// Get optional filter parameters
$date_from = $_GET['date_from'] ?? date('Y-m-d');
$date_to = $_GET['date_to'] ?? date('Y-m-d');

// Validate date parameters
if (!validate_date_format($date_from) || !validate_date_format($date_to)) {
    error_log("Invalid date format in export");
    die("Invalid date format");
}

// Generate safe filename
$filename = generate_safe_export_filename('todays_visitors', 'csv', $date_from, $date_to);

// Validate generated filename (defense in depth)
if (!validate_csv_filename($filename)) {
    error_log("Generated filename failed validation: $filename");
    $filename = 'todays_visitors_' . time() . '.csv';
}

// Set download headers with filename validation
set_download_headers($filename, 'text/csv');

// Build query with date filters
$sql = "SELECT name, person_to_see, purpose, time_in, time_out FROM visitors 
        WHERE DATE(time_in) BETWEEN ? AND ?";

// If destination_admin or receptionist, filter by destination
if ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'director') {
    $sql .= " AND destination = ?";
}

$sql .= " ORDER BY time_in DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Export CSV query preparation failed: " . $conn->error);
    die("Export failed");
}

// Bind parameters
if ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'director') {
    $dest_id = $_SESSION['destination_id'];
    $stmt->bind_param("ssi", $date_from, $date_to, $dest_id);
} else {
    $stmt->bind_param("ss", $date_from, $date_to);
}

$stmt->execute();
$result = $stmt->get_result();

// Log the export action
$record_count = $result->num_rows;
log_data_export('csv', $record_count, ['date_from' => $date_from, 'date_to' => $date_to]);

// Output CSV
$output = fopen("php://output", "w");
fputcsv($output, ['Name', 'Person to See', 'Purpose', 'Time In', 'Time Out']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
// Log successful export with record count
log_rate_limit_attempt(
    $_SESSION['user_id'],
    $_SESSION['username'],
    'export_csv',
    $_SERVER['REMOTE_ADDR'],
    $record_count,
    'SUCCESS'
);
$stmt->close();
exit();
?>
