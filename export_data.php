<?php
require_once 'security_headers.php';
session_start();
include 'db.php';
require_once 'audit_logging.php';
require_once 'filename_validation.php';
require_once 'rbac.php';
require_once 'rate_limiting.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Check rate limit for data exports
    $rate_limit_result = enforce_rate_limit(
        'export_data',
        $_SESSION['user_id'],
        $_SESSION['username'],
        $_SERVER['REMOTE_ADDR']
    );

    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $format = $_POST['format'] ?? 'csv';

    // Validate date parameters
    if (!$startDate || !$endDate) {
        die('Start and end dates are required.');
    }
    
    if (!validate_date_format($startDate) || !validate_date_format($endDate)) {
        error_log("Invalid date format in data export");
        die("Invalid date format");
    }

    // Generate safe filename based on format
    $filename = generate_safe_export_filename('visitor_backup', $format, $startDate, $endDate);
    
    // Validate generated filename (defense in depth)
    if ($format === 'csv' && !validate_csv_filename($filename)) {
        error_log("Generated CSV filename failed validation: $filename");
        $filename = 'visitor_backup_' . time() . '.csv';
    }

    // Build query with filters
    $query = "SELECT * FROM visitors WHERE date BETWEEN ? AND ?";
    
    // If destination_admin or receptionist, filter by destination
    if ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'director') {
        $query .= " AND destination = ?";
    }
    
    $query .= " ORDER BY date DESC";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Data export query preparation failed: " . $conn->error);
        die("Export failed");
    }

    // Bind parameters
    if ($_SESSION['role'] !== 'super_admin' && $_SESSION['role'] !== 'director') {
        $dest_id = $_SESSION['destination_id'];
        $stmt->bind_param("ssi", $startDate, $endDate, $dest_id);
    } else {
        $stmt->bind_param("ss", $startDate, $endDate);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $record_count = $result->num_rows;

    if ($format === 'csv') {
        // CSV Export
        set_download_headers($filename, 'text/csv');

        // Log the export action
        log_data_export('csv', $record_count, ['start_date' => $startDate, 'end_date' => $endDate]);

        $output = fopen('php://output', 'w');

        // Output headers
        fputcsv($output, ['ID', 'Fullname', 'Faculty/Organization', 'Phone Number', 'Purpose', 'Destination', 'Time In', 'Time Out', 'Date']);

        // Output rows
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['fullname'],
                $row['faculty_organization'],
                $row['phone_number'],
                $row['purpose'],
                $row['destination'],
                $row['time_in'],
                $row['time_out'],
                $row['date']
            ]);
        }

        fclose($output);
        
        // Log successful export with record count
        log_rate_limit_attempt(
            $_SESSION['user_id'],
            $_SESSION['username'],
            'export_data',
            $_SERVER['REMOTE_ADDR'],
            $record_count,
            'SUCCESS'
        );
        
        $stmt->close();
        exit;

    } elseif ($format === 'json') {
        // JSON Export
        set_download_headers($filename, 'application/json');

        // Log the export action
        log_data_export('json', $record_count, ['start_date' => $startDate, 'end_date' => $endDate]);

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode($data, JSON_PRETTY_PRINT);
        
        // Log successful export with record count
        log_rate_limit_attempt(
            $_SESSION['user_id'],
            $_SESSION['username'],
            'export_data',
            $_SERVER['REMOTE_ADDR'],
            $record_count,
            'SUCCESS'
        );
        
        exit;

    } else {
        die('Invalid format selected.');
    }
} else {
    die('Invalid request.');
}
?>
