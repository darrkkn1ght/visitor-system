<?php
require_once 'security_headers.php';
session_start();
include 'db.php';

// strict role check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

if ($_SESSION['role'] !== 'super_admin') {
    die("Access Denied: Only Super Admins can create events.");
}

$msg = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['event_title']);
    $organizer = trim($_POST['organizer_name']);
    $venue = trim($_POST['venue']);
    $date = $_POST['event_date'];
    $slot = $_POST['time_slot'];
    $description = trim($_POST['description']);

    if (empty($title) || empty($organizer) || empty($date) || empty($slot) || empty($venue)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO events (event_title, organizer_name, venue, event_date, time_slot, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $title, $organizer, $venue, $date, $slot, $description);
        
        if ($stmt->execute()) {
            $msg = "Event created successfully!";
        } else {
            // Log detailed error server-side only
            error_log("Database error creating event: " . $stmt->error . " from IP: " . $_SERVER['REMOTE_ADDR']);
            // Show generic error to user
            $error = "An error occurred while creating the event. Please try again later.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Event</title>
    <link rel="stylesheet" href="central.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        .form-container {
            max-width: 600px;
            margin: 100px auto 40px;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #1a73e8;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success { background: #e8f5e9; color: #2e7d32; }
        .alert-error { background: #ffebee; color: #c62828; }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
            transition: 0.3s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: #1a73e8;
            outline: none;
        }
    </style>
</head>
<body>

  <div class="logo-top-left">
    <img src="ui_logo-removebg-preview.png" alt="Logo">
  </div>

  <!-- New Simple Menu -->
  <div class="simple-menu-container">
    <button class="menu-toggle" id="menuToggle">☰ Menu</button>
    <div class="menu-panel" id="menuPanel">
      <div class="menu-header">
        <strong>Navigation</strong>
        <button class="menu-close" id="menuClose">×</button>
      </div>
      <?php if ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'director'): ?>
        <a href="destinations.php"><span class="menu-icon">⚷</span> Manage Destinations</a>
      <?php endif; ?>
      <a href="admin_dashboard.php"><span class="menu-icon">☰</span> Dashboard</a>
      <a href="events_calendar.php"><span class="menu-icon">◷</span> Events Calendar</a>
      <a href="backup_records.php"><span class="menu-icon">⬇</span> Export</a>
      <a href="logout.php" class="logout-btn"><span class="menu-icon">⎋</span> Logout</a>
    </div>
  </div>

  <div class="container form-container">
    <h2>Create New Event</h2>
    
    <?php if ($msg): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="create_event.php">
        <div class="form-row">
            <div class="form-group">
                <label>Event Title</label>
                <input type="text" name="event_title" required placeholder="e.g. Annual Staff Meeting">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Organizer</label>
                <input type="text" name="organizer_name" required placeholder="e.g. HR Department">
            </div>
            <div class="form-group">
                <label>Venue</label>
                <input type="text" name="venue" required placeholder="e.g. Conference Hall A">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="event_date" required>
            </div>
            <div class="form-group">
                <label>Time Slot</label>
                <select name="time_slot" required>
                    <option value="morning">Morning</option>
                    <option value="afternoon">Afternoon</option>
                    <option value="fullday">Full Day</option>
                </select>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label>Description (Optional)</label>
            <textarea name="description" rows="4" placeholder="Additional details..."></textarea>
        </div>

        <button type="submit" class="btn">Create Event</button>
        <a href="events_calendar.php" style="display:block; text-align:center; margin-top:15px; text-decoration:none; color:#666;">Back to Calendar</a>
    </form>
  </div>

  <script src="simple_menu.js"></script>
</body>
</html>
