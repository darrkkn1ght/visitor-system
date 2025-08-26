<?php
include 'db.php'; // include your DB connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['visitor_id'] ?? null;

    if ($id) {
        $timeOut = date('Y-m-d H:i:s');

        // Update time_out in the database
        $stmt = $conn->prepare("UPDATE visitors SET time_out = ? WHERE id = ?");
        $stmt->bind_param("si", $timeOut, $id);
        $stmt->execute();

        // Fetch details for backup
        $stmt = $conn->prepare("SELECT fullname, faculty_organization, purpose, time_in FROM visitors WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            $backupRow = [
                $row['fullname'],
                $row['faculty_organization'],
                $row['purpose'],
                $row['time_in'],
                $timeOut
            ];

            $file = __DIR__ . '/backup/checked_out.csv';
            if (!file_exists(dirname($file))) {
                mkdir(dirname($file), 0777, true);
            }

            $addHeader = !file_exists($file);
            $handle = fopen($file, 'a');
            if ($addHeader) {
                fputcsv($handle, ['Fullname', 'Faculty/Org', 'Purpose', 'Time In', 'Time Out']);
            }
            fputcsv($handle, $backupRow);
            fclose($handle);
        }

        $stmt->close();
        $conn->close();
    }
}

header('Location: index.php');
exit;
?>
