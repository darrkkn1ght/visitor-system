<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['visitor_id'] ?? null;

    if ($id) {
        $timeOut = date('Y-m-d H:i:s');

        // 1. Get visitor's keycard_id
        $stmt = $conn->prepare("SELECT keycard_id FROM visitors WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $visitor = $result->fetch_assoc();
        $stmt->close();

        if ($visitor && $visitor['keycard_id']) {
            $keycardId = $visitor['keycard_id'];

            // 2. Free up the keycard
            $stmtCard = $conn->prepare("UPDATE keycards SET is_assigned = 0 WHERE id = ?");
            $stmtCard->bind_param("i", $keycardId);
            $stmtCard->execute();
            $stmtCard->close();
        }

        // 3. Update time_out in visitors table
        $stmt = $conn->prepare("UPDATE visitors SET time_out = ? WHERE id = ?");
        $stmt->bind_param("si", $timeOut, $id);
        $stmt->execute();
        $stmt->close();

        // 4. Backup details
        $stmt = $conn->prepare("SELECT fullname, faculty_organization, purpose, time_in FROM visitors WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

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

        $conn->close();
    }
}

header('Location: admin_dashboard.php');
exit;
