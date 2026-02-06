<?php
// index.php - Visitor input + log view
require_once 'security_headers.php';
session_start();
include 'db.php';

// Generate CSRF token for form
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch destinations with deterministic admin availability
// Priority: director > super_admin > destination_admin
// Tiebreaker: most recent last_status_change, then smallest user_id
$destinations = [];
$sql = "
    SELECT 
        d.id,
        d.name,
        u.availability_status,
        u.status_message,
        u.username as admin_username,
        u.id as admin_id,
        u.role as admin_role
    FROM destinations d
    LEFT JOIN users u ON u.id = (
        SELECT u2.id 
        FROM users u2 
        WHERE u2.destination_id = d.id 
          AND u2.role IN ('destination_admin', 'super_admin', 'director')
        ORDER BY 
            FIELD(u2.role, 'director', 'super_admin', 'destination_admin'),
            u2.last_status_change DESC,
            u2.id ASC
        LIMIT 1
    )
    ORDER BY d.name ASC
";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $destinations[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Visitor Check-In</title>
  <link rel="stylesheet" href="visitor_checkin.css">
  <link rel="stylesheet" href="visitor_checkin_status.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

  <!-- Logo -->
  <div class="logo-container">
    <img src="ui_logo-removebg-preview.png" alt="University of Ibadan Logo">
  </div>

  <!-- Admin Menu -->
  <div class="admin-menu-wrapper">
    <a href="admin_login.php" class="admin-login-btn">Admin Login</a>
  </div>




  <div class="container">
    <?php if (isset($_GET['msg']) && $_GET['msg']): ?>
      <div id="submission-message">
        <?= htmlspecialchars($_GET['msg']) ?>
      </div>
    <?php endif; ?>

    <form class="registration-form" action="submit.php" method="POST" id="checkinForm">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

      <!-- Message Mode Hidden Inputs (populated by JavaScript) -->
      <input type="hidden" name="submission_mode" id="submission_mode" value="checkin">
      <input type="hidden" name="admin_id" id="admin_id" value="">
      <input type="hidden" name="availability_snapshot" id="availability_snapshot" value="">
      <input type="hidden" name="status_message_snapshot" id="status_message_snapshot" value="">

      <h2>VISITOR CHECK-IN</h2>

      <!-- STEP 1: Select Destination -->
      <div id="step-1-destination">
        <div class="form-group">
          <label>Select Destination</label>
          <select name="destination" id="destination" required class="large-select">
            <option value="">-- Choose Where You Are Visiting --</option>
            <?php foreach ($destinations as $dest):
              $status = $dest['availability_status'] ?? 'available';
              // Logic to handle status labels...
              ?>
              <option value="<?= $dest['id'] ?>" data-status="<?= htmlspecialchars($status) ?>"
                data-message="<?= htmlspecialchars($dest['status_message'] ?? '') ?>">
                <?= htmlspecialchars($dest['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Status Preview Banner (Initially Hidden) -->
        <div id="adminStatusBanner" class="status-banner" style="display: none;">
          <div class="status-icon-large"></div>
          <div class="status-text-content">
            <h3 id="statusTitle"></h3>
            <p id="statusMessagePreview"></p>
          </div>
        </div>
      </div>

      <!-- STEP 2: Visitor Details (Hidden until destination selected & available) -->
      <div id="step-2-details" style="display: none;">
        <div class="user details">

          <!-- Row 1: Full Name -->
          <div class="form-row">
            <div class="form-group">
              <label>Full Name</label>
              <input type="text" name="fullname" placeholder="Enter Your Full Name" required>
            </div>
          </div>

          <!-- Row 2: Phone -->
          <div class="form-row">
            <div class="form-group">
              <label>Phone Number</label>
              <input type="tel" name="phone_number" placeholder="Enter Your Phone Number" required pattern="[0-9]{7,15}"
                title="Enter a valid phone number (numbers only, 7–15 digits)" oninput="validatePhone(this)">
            </div>

            <div class="form-group autocomplete-container">
              <label>Faculty/Organization</label>
              <input type="text" id="faculty" name="faculty" placeholder="Faculty / Organization" autocomplete="off"
                required>
              <div id="facultySuggestions" class="suggestion-box"></div>
            </div>
          </div>

          <!-- Row 3: Purpose -->
          <div class="form-row">
            <div class="form-group" style="width: 100%;">
              <label for="purpose">Purpose of Visit / Leave a Message</label>
              <textarea name="purpose" id="purpose" required
                placeholder="Briefly state your purpose or message..."></textarea>
            </div>
          </div>

          <!-- Row 4: Visitor Type -->
          <div class="form-group visitor-type-group">
            <label><strong>Visitor Type</strong></label>
            <div class="visitor-type-options">
              <label><input type="radio" name="visitor_type" value="staff" required> Staff</label>
              <label><input type="radio" name="visitor_type" value="student" required> Student</label>
              <label><input type="radio" name="visitor_type" value="guest" required> Guest</label>
            </div>
          </div>

          <!-- ACTION BUTTONS: Side-by-Side -->
          <div class="action-buttons-row">
            <button type="submit" class="btn btn-primary" name="checkin_type" value="normal" id="btnCheckIn">
              Check In
            </button>

            <button type="submit" class="btn btn-secondary" name="checkin_type" value="alternate" id="btnLeaveMessage"
              onclick="return confirmAlternate()">
              Leave a Message
            </button>
          </div>

        </div>
      </div>

      <!-- Message Mode Panel (Away/Unavailable states) -->
      <div id="step-message-mode" style="display: none;">
        <div class="message-mode-panel">
          <div class="message-mode-header">
            <span class="message-icon">📝</span>
            <h3>Leave a Message</h3>
          </div>

          <p class="message-mode-notice" id="messageNotice">
            The admin is currently unavailable. Please leave a message below.
          </p>

          <!-- Basic Info (Name/Phone) for message mode -->
          <div class="form-row">
            <div class="form-group">
              <label>Your Name</label>
              <input type="text" name="fullname" placeholder="Enter Your Full Name" class="msg-fullname">
            </div>
            <div class="form-group">
              <label>Phone Number</label>
              <input type="tel" name="phone_number" placeholder="Your Phone Number" class="msg-phone"
                pattern="[0-9]{7,15}">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group autocomplete-container">
              <label>Faculty/Organization</label>
              <input type="text" name="faculty" placeholder="Faculty / Organization" class="msg-faculty">
            </div>
          </div>

          <!-- Message Content -->
          <div class="form-row">
            <div class="form-group" style="width: 100%;">
              <label for="visitor_message">Your Message</label>
              <textarea name="visitor_message" id="visitor_message" rows="4"
                placeholder="Please describe your purpose or leave a detailed message..."></textarea>
            </div>
          </div>

          <!-- Preferred Return Time (Optional) -->
          <div class="form-row">
            <div class="form-group">
              <label for="preferred_return">Preferred Callback Time (Optional)</label>
              <input type="datetime-local" name="preferred_return" id="preferred_return" class="datetime-input">
            </div>
            <div class="form-group">
              <label><strong>Visitor Type</strong></label>
              <div class="visitor-type-options">
                <label><input type="radio" name="visitor_type" value="staff"> Staff</label>
                <label><input type="radio" name="visitor_type" value="student"> Student</label>
                <label><input type="radio" name="visitor_type" value="guest"> Guest</label>
              </div>
            </div>
          </div>

          <div class="action-buttons-row">
            <button type="submit" class="btn btn-primary" id="btnSubmitMessage" onclick="setSubmissionMode('message')">
              📩 Send Message
            </button>
            <button type="button" class="btn btn-outline" id="btnProceedAnyway" onclick="showProceedAnyway()">
              Proceed Anyway →
            </button>
          </div>

          <p class="proceed-hint" id="proceedHint" style="display: none;">
            <small>⚠️ The admin is unavailable. Your check-in will be queued.</small>
          </p>
        </div>
      </div>

    </form>
  </div>

  <!-- External JavaScript for CSP Compliance -->
  <script src="assets/js/realtime_client.js"></script>
  <script src="visitor_checkin.js" defer></script>

</body>

</html>