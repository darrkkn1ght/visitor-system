<?php
// submit.php - Handles form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'db.php';

    // Collect fields
    $fullname       = trim($_POST['fullname']);
    $faculty        = trim($_POST['faculty']);   // from your form "faculty"
    $phone          = trim($_POST['phone_number']);
    $purpose        = trim($_POST['purpose']);
    $destination    = trim($_POST['destination']);
    $other_dest     = isset($_POST['other_destination']) ? trim($_POST['other_destination']) : "";
    $visitor_type   = trim($_POST['visitor_type']); // staff / student / guest
    $checkin_type   = $_POST['checkin_type'] ?? 'normal'; // normal or alternate

    // Handle "Other" destination properly
    if ($destination === "Other" && !empty($other_dest)) {
        $destination = $other_dest;
    }

    // Marker for Alternate Check-In
    $is_alternate = ($checkin_type === 'alternate') ? 1 : 0;

    // Date and time
    $date     = date("Y-m-d");
    $time_in  = date("Y-m-d H:i:s");
    $time_out = "";

    // Validation (all required, including faculty/organization for guests)
    if (!empty($fullname) && !empty($faculty) && !empty($phone) && !empty($purpose) && !empty($destination) && !empty($visitor_type)) {
        
        // Insert query (now includes visitor_type and is_alternate)
        $sql = "INSERT INTO visitors (fullname, faculty_organization, phone_number, purpose, destination, visitor_type, date, time_in, time_out, is_alternate) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssi", $fullname, $faculty, $phone, $purpose, $destination, $visitor_type, $date, $time_in, $time_out, $is_alternate);

        if ($stmt->execute()) {
            header("Location: index.php");
            exit();
        } else {
            echo "<p>Error: " . htmlspecialchars($stmt->error) . "</p>";
        }

        $stmt->close();
        $conn->close();
    } else {
        // If validation fails
        echo "<link rel='stylesheet' href='style.css'>";
        echo "<div class='container'><p>Error: All fields are required.</p>";
        echo "<a href='index.php' class='link-btn'>Back</a></div>";
    }
} else {
    header("Location: index.php");
    exit();
}
?>
