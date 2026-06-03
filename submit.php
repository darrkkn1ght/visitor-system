<?php
// submit.php - Handles form submission
require_once 'security_headers.php';
session_start();

// ============================================================
// INPUT VALIDATION FUNCTION
// ============================================================
function validate_visitor_input($fullname, $faculty, $phone, $purpose, $visitor_type, $destination_id)
{
    $errors = [];

    // Fullname: Check not empty
    if (strlen($fullname) < 1) {
        $errors['fullname'] = "Full name is required";
    }

    // Faculty: Check not empty
    if (strlen($faculty) < 1) {
        $errors['faculty'] = "Faculty/Organization is required";
    }

    // Phone: Check not empty
    if (strlen($phone) < 1) {
        $errors['phone'] = "Phone number is required";
    }

    // Purpose: Check not empty
    if (strlen($purpose) < 1) {
        $errors['purpose'] = "Purpose is required";
    } elseif (preg_match('/<|>|javascript:|onerror=|onclick=/', $purpose)) {
        // Basic XSS check remains for security
        $errors['purpose'] = "Purpose contains invalid characters";
    }

    // Visitor type: must be one of allowed values
    $allowed_types = ['staff', 'student', 'vendor', 'family', 'guest', 'other'];
    if (!in_array($visitor_type, $allowed_types)) {
        $errors['visitor_type'] = "Invalid visitor type";
    }

    // Destination ID: must be a positive integer
    if (!is_numeric($destination_id) || intval($destination_id) <= 0) {
        $errors['destination'] = "Invalid destination selected";
    }

    return $errors;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'db.php';

    // CSRF Token Validation - Check token before processing form
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        // Fail securely without revealing the reason
        error_log("CSRF token validation failed from IP: " . $_SERVER['REMOTE_ADDR']);
        die("Request validation failed. Please try again.");
    }

    // Collect fields - handle arrays (duplicate field names in check-in vs message mode)
    // When both step-2 and message-mode inputs exist, PHP receives arrays; pick the last non-empty value
    $fullname_raw = $_POST['fullname'] ?? '';
    if (is_array($fullname_raw)) {
        $filtered = array_filter($fullname_raw, 'strlen');
        $fullname = !empty($filtered) ? trim(end($filtered)) : '';
    } else {
        $fullname = trim($fullname_raw);
    }

    $faculty_raw = $_POST['faculty'] ?? '';
    if (is_array($faculty_raw)) {
        $filtered = array_filter($faculty_raw, 'strlen');
        $faculty = !empty($filtered) ? trim(end($filtered)) : '';
    } else {
        $faculty = trim($faculty_raw);
    }

    $phone_raw = $_POST['phone_number'] ?? '';
    if (is_array($phone_raw)) {
        $filtered = array_filter($phone_raw, 'strlen');
        $phone = !empty($filtered) ? trim(end($filtered)) : '';
    } else {
        $phone = trim($phone_raw);
    }

    $purpose = trim($_POST['purpose'] ?? '');
    $destination_id = intval($_POST['destination']);
    $other_dest = isset($_POST['other_destination']) ? trim($_POST['other_destination']) : "";
    $visitor_type_raw = $_POST['visitor_type'] ?? '';
    if (is_array($visitor_type_raw)) {
        $filtered = array_filter($visitor_type_raw, 'strlen');
        $visitor_type = !empty($filtered) ? trim(end($filtered)) : '';
    } else {
        $visitor_type = trim($visitor_type_raw);
    }
    $checkin_type = $_POST['checkin_type'] ?? 'normal';

    // New message mode fields
    $submission_mode = $_POST['submission_mode'] ?? 'checkin'; // 'checkin' or 'message'
    $visitor_message = isset($_POST['visitor_message']) ? trim($_POST['visitor_message']) : null;
    $preferred_return = isset($_POST['preferred_return']) && !empty($_POST['preferred_return'])
        ? date('Y-m-d H:i:s', strtotime($_POST['preferred_return']))
        : null;
    $admin_id = isset($_POST['admin_id']) && is_numeric($_POST['admin_id'])
        ? intval($_POST['admin_id'])
        : null;
    $availability_snapshot = isset($_POST['availability_snapshot'])
        ? trim($_POST['availability_snapshot'])
        : null;
    $status_message_snapshot = isset($_POST['status_message_snapshot'])
        ? trim($_POST['status_message_snapshot'])
        : null;

    // Determine if this is message mode
    $is_message_mode = ($submission_mode === 'message' || !empty($visitor_message));

    // In message mode, use visitor_message as purpose if purpose is empty
    if ($is_message_mode && empty($purpose) && !empty($visitor_message)) {
        $purpose = $visitor_message;
    }

    // ============================================================
    // SERVER-SIDE INPUT VALIDATION
    // ============================================================
    $validation_errors = validate_visitor_input($fullname, $faculty, $phone, $purpose, $visitor_type, $destination_id);

    if (!empty($validation_errors)) {
        // Return JSON error response
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'errors' => $validation_errors
        ]);
        exit;
    }

    $is_alternate = ($checkin_type === 'alternate') ? 1 : 0;

    $date = date("Y-m-d");
    $time_in = date("Y-m-d H:i:s");
    $time_out = "";

    // Generate a token
    $token = bin2hex(random_bytes(32)); // 64-character hex token (256 bits)

    // All validation passed - proceed with insertion
    $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));

    // Set queue_status based on mode
    $queue_status = $is_message_mode ? 'pending' : null;

    // For message mode, also set is_alternate = 1 for backward compatibility
    if ($is_message_mode) {
        $is_alternate = 1;
    }

    $sql = "INSERT INTO visitors (
        fullname, faculty_organization, phone_number, purpose, visitor_message,
        preferred_return, destination, visitor_type, date, time_in, time_out,
        is_alternate, keycard_id, token, expires_at, admin_id, 
        availability_snapshot, status_message_snapshot, queue_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $keycard_id = null; // Initialize as null
    $stmt->bind_param(
        "ssssssssssssississs",
        $fullname,
        $faculty,
        $phone,
        $purpose,
        $visitor_message,
        $preferred_return,
        $destination_id,
        $visitor_type,
        $date,
        $time_in,
        $time_out,
        $is_alternate,
        $keycard_id,
        $token,
        $expires_at,
        $admin_id,
        $availability_snapshot,
        $status_message_snapshot,
        $queue_status
    );

    if ($stmt->execute()) {
        $visitor_id = $stmt->insert_id;
        $stmt->close();

        // Create enhanced notifications for admins of this destination + director
        $notif_message = "";
        $notif_type = "";
        $priority = "normal";

        if ($is_alternate) {
            $notif_message = "Message from {$fullname}: " . (strlen($purpose) > 50 ? substr($purpose, 0, 47) . '...' : $purpose);
            $notif_type = "message";
            $priority = "low";
        } else {
            $notif_message = "New visitor: {$fullname} heading to your destination.";
            $notif_type = "visitor_arrival";
            $priority = "high";
        }

        // Fetch all admins for the selected destination and all directors
        $sql_users = "SELECT id FROM users WHERE role = 'director' OR destination_id = ?";
        $stmt_users = $conn->prepare($sql_users);
        $stmt_users->bind_param("i", $destination_id);
        $stmt_users->execute();
        $result_users = $stmt_users->get_result();

        while ($user = $result_users->fetch_assoc()) {
            // Create enhanced notification with visitor context
            $sql_notif = "INSERT INTO notifications (user_id, message, visitor_id, notification_type, priority, is_read) 
                          VALUES (?, ?, ?, ?, ?, 0)";
            $stmt_notif = $conn->prepare($sql_notif);
            $stmt_notif->bind_param("isiss", $user['id'], $notif_message, $visitor_id, $notif_type, $priority);
            $stmt_notif->execute();
            $stmt_notif->close();
        }
        $stmt_users->close();

        // Realtime Notification — send for BOTH check-ins and messages
        require_once 'includes/realtime_notify.php';

        $payload = [
            'id' => $visitor_id,
            'fullname' => $fullname,
            'phone' => $phone,
            'purpose' => $purpose,
            'visitor_type' => $visitor_type,
            'submitted_at' => $time_in,
            'destination_id' => $destination_id,
            'destination_name' => '',
            'admin_id' => $admin_id
        ];

        if ($is_message_mode) {
            // Message mode: include message-specific fields
            $payload['message'] = $visitor_message;
            $payload['queue_status'] = $queue_status;
            $payload['availability_snapshot'] = $availability_snapshot;
            $payload['status_message_snapshot'] = $status_message_snapshot;
            $payload['preferred_return'] = $preferred_return;

            notify_realtime('new_message', $admin_id, $destination_id, $payload);
        } else {
            // Normal check-in: emit visitor_arrival event
            notify_realtime('visitor_arrival', $admin_id, $destination_id, $payload);
        }

        if ($is_alternate === 0) {
            // Normal check-in: Try to assign keycard
            $sql_card = "SELECT id FROM keycards WHERE destination_id = ? AND is_assigned = 0 ORDER BY id ASC LIMIT 1";
            $stmt_card = $conn->prepare($sql_card);
            $stmt_card->bind_param("i", $destination_id);
            $stmt_card->execute();
            $result_card = $stmt_card->get_result();

            if ($row = $result_card->fetch_assoc()) {
                $keycard_id = $row['id'];

                // Mark keycard as assigned
                $sql_update = "UPDATE keycards SET is_assigned = 1 WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("i", $keycard_id);
                $stmt_update->execute();
                $stmt_update->close();

                // Update visitor with keycard_id
                $sql_update_visitor = "UPDATE visitors SET keycard_id = ? WHERE id = ?";
                $stmt_update_visitor = $conn->prepare($sql_update_visitor);
                $stmt_update_visitor->bind_param("ii", $keycard_id, $visitor_id);
                $stmt_update_visitor->execute();
                $stmt_update_visitor->close();

                $stmt_card->close();

                header("Location: confirmation.php?token=" . urlencode($token));
                exit();
            } else {
                // No keycard available
                $stmt_card->close();
                header("Location: index.php?msg=" . urlencode("Number of visitor is exceeded for your destination"));
                exit();
            }
        } else {
            // Alternate check-in: redirect with message
            header("Location: index.php?msg=" . urlencode("Your message has been delivered"));
            exit();
        }
    } else {
        // Database insertion failed
        error_log("Database error inserting visitor: " . $stmt->error);
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'errors' => ['database' => 'An error occurred. Please try again later.']
        ]);
        $stmt->close();
        $conn->close();
        exit;
    }
} else {
    header("Location: index.php");
    exit();
}
