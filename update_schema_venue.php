<?php
include 'db.php';

// Check if venue column exists
$checkColumn = $conn->query("SHOW COLUMNS FROM events LIKE 'venue'");
if ($checkColumn->num_rows == 0) {
    // Add venue column
    $sql = "ALTER TABLE events ADD COLUMN venue VARCHAR(255) AFTER organizer_name";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'venue' added successfully.\n";
        
        // Update existing dummy data with a default venue
        $conn->query("UPDATE events SET venue = 'Main Conference Hall' WHERE venue IS NULL");
        echo "Existing events updated with default venue.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'venue' already exists.\n";
}
?>
