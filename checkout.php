<?php
require_once 'security_headers.php';
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        error_log("CSRF token validation failed in checkout.php from IP: " . $_SERVER['REMOTE_ADDR']);
        die("Request validation failed. Please try again.");
    }

    $id = $_POST['visitor_id'] ?? null;

    if ($id) {
        $timeOut = date('Y-m-d H:i:s');

        // Start database transaction
        $conn->begin_transaction();

        try {
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
                if (!$stmtCard->execute()) {
                    throw new Exception("Failed to update keycard");
                }
                $stmtCard->close();
            }

            // 3. Update time_out in visitors table
            $stmt = $conn->prepare("UPDATE visitors SET time_out = ? WHERE id = ?");
            $stmt->bind_param("si", $timeOut, $id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update visitor checkout time");
            }
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

            // Commit transaction
            $conn->commit();
        } catch (Exception $e) {
            // Rollback on any error
            $conn->rollback();
            error_log("Checkout transaction failed: " . $e->getMessage());
            die("Checkout failed. Please try again.");
        }

        $conn->close();
    }
}

header('Location: admin_dashboard.php');
exit;
