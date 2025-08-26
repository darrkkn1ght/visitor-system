<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $format = $_POST['format'] ?? 'csv';

    if (!$startDate || !$endDate) {
        die('Start and end dates are required.');
    }

    // Query data from database
    $stmt = $conn->prepare("SELECT * FROM visitors WHERE date BETWEEN ? AND ?");
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($format === 'csv') {
        // CSV Export
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="visitor_backup_' . $startDate . '_to_' . $endDate . '.csv"');

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
        exit;

    } elseif ($format === 'json') {
        // JSON Export
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="visitor_backup_' . $startDate . '_to_' . $endDate . '.json"');

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;

    } else {
        die('Invalid format selected.');
    }
} else {
    die('Invalid request.');
}
?>
