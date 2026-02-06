<?php
require_once 'security_headers.php';
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}
include 'db.php';

// Get current month and year
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Fetch events for this month
$stmt = $conn->prepare("SELECT id, event_title, organizer_name, venue, event_date, time_slot FROM events WHERE MONTH(event_date) = ? AND YEAR(event_date) = ?");
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$result = $stmt->get_result();
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[$row['event_date']][] = $row;
}
$stmt->close();

// Calendar logic
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDayOfMonth);
$dayOfWeek = date('w', $firstDayOfMonth);
$monthName = date('F', $firstDayOfMonth);

// Navigation
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth == 0) {
    $prevMonth = 12;
    $prevYear--;
}
$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth == 13) {
    $nextMonth = 1;
    $nextYear++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Calendar - Visitor System</title>
    <link rel="stylesheet" href="central.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
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

  <div class="container">
    <div class="calendar-header">
        <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>" class="nav-btn">&larr; Prev</a>
        <h2><?= $monthName . ' ' . $year ?></h2>
        <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>" class="nav-btn">Next &rarr;</a>
    </div>

    <?php if ($_SESSION['role'] === 'super_admin'): ?>
    <div class="text-center mb-20">
        <button class="btn" id="createEventBtn">+ Create New Event</button>
    </div>
    <?php endif; ?>

    <div class="calendar-grid">
        <div class="day-name">Sun</div>
        <div class="day-name">Mon</div>
        <div class="day-name">Tue</div>
        <div class="day-name">Wed</div>
        <div class="day-name">Thu</div>
        <div class="day-name">Fri</div>
        <div class="day-name">Sat</div>

        <?php
        // Padding for the first week
        for ($i = 0; $i < $dayOfWeek; $i++) {
            echo '<div class="day other-month"></div>';
        }

        // Days of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $isToday = ($currentDate == date('Y-m-d')) ? 'today' : '';
            $dayEvents = isset($events[$currentDate]) ? $events[$currentDate] : [];
            
            echo '<div class="day ' . $isToday . '" data-date="' . $currentDate . '">';
            echo '<div class="day-number">' . $day . '</div>';
            
            foreach ($dayEvents as $event) {
                echo '<div class="event-indicator">';
                echo '<span class="event-dot ' . $event['time_slot'] . '"></span>';
                echo htmlspecialchars($event['event_title']);
                echo '</div>';
            }
            
            echo '</div>';
        }
        ?>
    </div>
  </div>
  
  <script src="simple_menu.js" defer></script>
  <script src="events_data.php?month=<?= $month ?>&year=<?= $year ?>" defer></script>
  <script src="calendar.js" defer></script>

<!-- Event Details Modal -->
<div id="eventModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3 id="modalDate"></h3>
        <div id="modalBody">
            <!-- Event details will be injected here -->
        </div>
    </div>
</div>


<!-- Create Event Modal -->
<div id="createEventModal" class="modal">
    <div class="modal-content create-event-modal">
        <span class="close" data-modal-close>&times;</span>
        <h2>Create New Event</h2>
        
        <div id="createEventMessage"></div>
        
        <form id="createEventForm">
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
                    <label>Start Date</label>
                    <input type="date" name="start_date" required>
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Time Slot</label>
                    <select name="time_slot" required>
                        <option value="morning">Morning</option>
                        <option value="afternoon">Afternoon</option>
                        <option value="fullday">Full Day</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Success Message</label>
                    <select name="success_message" id="successMessageSelect">
                        <option value="default">Default Success Message</option>
                        <option value="custom">Custom Message</option>
                    </select>
                </div>
            </div>

            <div class="form-group" id="customMessageGroup">
                <label>Custom Success Message</label>
                <textarea name="custom_success_message" rows="2" placeholder="Enter custom success message..."></textarea>
            </div>

            <div class="form-group">
                <label>Description (Optional)</label>
                <textarea name="description" rows="4" placeholder="Additional details..."></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Create Event</button>
                <button type="button" class="btn-secondary" id="cancelCreateBtn">Cancel</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
