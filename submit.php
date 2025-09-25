<?php
// submit.php - Handles form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'db.php';

    // Collect fields
    $fullname       = trim($_POST['fullname']);
    $faculty        = trim($_POST['faculty']);
    $phone          = trim($_POST['phone_number']);
    $purpose        = trim($_POST['purpose']);
    $destination_id = intval($_POST['destination']);
    $other_dest     = isset($_POST['other_destination']) ? trim($_POST['other_destination']) : "";
    $visitor_type   = trim($_POST['visitor_type']);
    $checkin_type   = $_POST['checkin_type'] ?? 'normal';

    $is_alternate = ($checkin_type === 'alternate') ? 1 : 0;

    $date     = date("Y-m-d");
    $time_in  = date("Y-m-d H:i:s");
    $time_out = "";

    // Generate a token
    $token = bin2hex(random_bytes(32)); // 64-character hex token (256 bits)

    // Validation
    if (!empty($fullname) && !empty($faculty) && !empty($phone) && !empty($purpose) && !empty($destination_id) && !empty($visitor_type)) {

        // Insert visitor record first (no keycard yet)
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $sql = "INSERT INTO visitors (fullname, faculty_organization, phone_number, purpose, destination, visitor_type, date, time_in, time_out, is_alternate, keycard_id, token, expires_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssssiiss",
            $fullname,
            $faculty,
            $phone,
            $purpose,
            $destination_id,
            $visitor_type,
            $date,
            $time_in,
            $time_out,
            $is_alternate,
            $keycard_id,
            $token,
            $expires_at
        );

        if ($stmt->execute()) {
            $visitor_id = $stmt->insert_id;
            $stmt->close();

            // Create notifications for admins of this destination + director
$notif_message = "New visitor: {$fullname} heading to your destination.";

// Fetch all admins for the selected destination and all directors
$sql_users = "SELECT id FROM users WHERE role = 'director' OR destination_id = ?";
$stmt_users = $conn->prepare($sql_users);
$stmt_users->bind_param("i", $destination_id);
$stmt_users->execute();
$result_users = $stmt_users->get_result();

while ($user = $result_users->fetch_assoc()) {
    $sql_notif = "INSERT INTO notifications (user_id, message, is_read) VALUES (?, ?, 0)";
    $stmt_notif = $conn->prepare($sql_notif);
    $stmt_notif->bind_param("is", $user['id'], $notif_message);
    $stmt_notif->execute();
    $stmt_notif->close();
}
$stmt_users->close();


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
            echo "<p>Error: " . htmlspecialchars($stmt->error) . "</p>";
            $stmt->close();
            $conn->close();
        }
    } else {
        echo "<link rel='stylesheet' href='style.css'>";
        echo "<div class='container'><p>Error: All fields are required.</p>";
        echo "<a href='index.php' class='link-btn'>Back</a></div>";
    }
} else {
    header("Location: index.php");
    exit();
}

