<?php
// events_calendar.php
require_once 'security_headers.php';
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Ensure CSRF token exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
    <title>Events Calendar | Scheduler</title>
    <!-- Core styles -->
    <link rel="stylesheet" href="events_calendar.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>

    <!-- Navigation / Header -->
    <header class="app-header">
        <div class="header-left">
            <div class="logo-container" style="margin-right: 15px;">
                <?php require_once 'includes/logo_helper.php'; ?>
                <a href="<?= getLogoHref() ?>" class="logo-link" aria-label="Home">
                    <img src="ui_logo-removebg-preview.png" alt="UI Logo" style="height: 40px; width: auto;">
                </a>
            </div>
            <a href="admin_dashboard.php" class="back-link">&larr; Dashboard</a>
            <h1 class="header-title">Events Calendar</h1>
        </div>
        <div class="header-right">
            <?php if ($user_role === 'super_admin' || $user_role === 'director' || $user_role === 'destination_admin'): ?>
                <button class="btn btn-primary" id="btnCreateEvent">
                    <span class="icon">+</span> Add Event
                </button>
            <?php endif; ?>
        </div>
    </header>

    <div class="calendar-wrapper">
        <!-- Sidebar: Event Details / Agenda -->
        <aside class="calendar-sidebar" id="calendarSidebar">
            <!-- Date Selector (Small Calendar Placeholder or Date Picker) -->
            <div class="sidebar-section mini-date-display">
                <h2 id="selectedDateDisplay">Select a date</h2>
                <div id="selectedDayName" class="muted-text">To view events</div>
            </div>

            <div class="sidebar-section event-list-section">
                <h3 class="section-title">Events <span id="eventCountBadge" class="badge">0</span></h3>
                <div id="sidebarEventList" class="event-list">
                    <div class="empty-state">
                        No events selected.
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Calendar Grid -->
        <main class="calendar-main">
            <div class="calendar-controls">
                <div class="nav-cluster">
                    <button class="icon-btn" id="btnPrevMonth">&lsaquo;</button>
                    <button class="btn-text" id="btnToday">Today</button>
                    <button class="icon-btn" id="btnNextMonth">&rsaquo;</button>
                </div>
                <h2 id="currentMonthYear">Loading...</h2>
            </div>

            <div class="calendar-grid-header">
                <div>Sun</div>
                <div>Mon</div>
                <div>Tue</div>
                <div>Wed</div>
                <div>Thu</div>
                <div>Fri</div>
                <div>Sat</div>
            </div>
            <div class="calendar-grid" id="calendarGrid">
                <!-- Days injected via JS -->
            </div>
        </main>
    </div>

    <!-- Event Modal (Create / Edit) -->
    <div class="modal-overlay" id="eventModal">
        <div class="modal-card">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Event</h3>
                <button class="close-btn" id="modalClose">&times;</button>
            </div>
            <div class="modal-body">
                <form id="eventForm">
                    <input type="hidden" name="id" id="eventId">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="form-group">
                        <label>Event Title</label>
                        <input type="text" name="title" id="inpTitle" required placeholder="e.g. Weekly Meeting">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Start</label>
                            <input type="datetime-local" name="start_datetime" id="inpStart" required>
                        </div>
                        <div class="form-group">
                            <label>End</label>
                            <input type="datetime-local" name="end_datetime" id="inpEnd" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Location / Venue</label>
                        <input type="text" name="location" id="inpLocation" placeholder="e.g. Conference Room A">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="inpDesc" rows="3" placeholder="Add details..."></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Visibility</label>
                            <select name="visibility" id="inpVisibility">
                                <option value="internal">Internal Only</option>
                                <option value="public">Public</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn btn-danger" id="btnDeleteEvent"
                            style="display:none;">Delete</button>
                        <div style="flex:1"></div>
                        <button type="button" class="btn btn-secondary" id="btnCancel">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btnSave">Save Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Application State for JS -->
    <script>
        window.USER_ROLE = '<?= $user_role ?>';
    </script>
    <script src="events_calendar.js?v=<?= time() ?>"></script>
</body>

</html>