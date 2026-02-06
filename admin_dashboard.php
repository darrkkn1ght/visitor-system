<?php
require_once 'security_headers.php';

function previewText($text, $limit = 12)
{
  $words = explode(' ', $text);
  if (count($words) > $limit) {
    return implode(' ', array_slice($words, 0, $limit)) . '...';
  }
  return $text;
}

session_start();
// If not logged in, kick back to login
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
  header("Location: admin_login.php");
}

// Ensure CSRF token exists
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Check if user must change password (first login)
include 'db.php';
$stmt = $conn->prepare("SELECT must_change_password FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user && $user['must_change_password'] == 1) {
  header("Location: change_password.php?reason=first_login");
  exit;
}

// --- FETCH PROFILE & NOTIFICATIONS (Merged from admin_profile.php) ---
// Get user profile data
$stmt = $conn->prepare("
    SELECT 
        u.*,
        d.name as destination_name
    FROM users u
    LEFT JOIN destinations d ON u.destination_id = d.id
    WHERE u.id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get recent notifications
$stmt = $conn->prepare("
    SELECT n.*, v.fullname as visitor_name 
    FROM notifications n
    LEFT JOIN visitors v ON n.visitor_id = v.id
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$recent_notifications = [];
while ($row = $result->fetch_assoc()) {
  $recent_notifications[] = $row;
}
$stmt->close();

// Get unread count
$stmt = $conn->prepare("SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$unread_count = $stmt->get_result()->fetch_assoc()['unread'];
$stmt->close();

// Generate CSRF token for forms
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Handle filters ---
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// For receptionist, default filter should be 'checked_in' instead of 'all'
if ($_SESSION['role'] === 'receptionist' && $filter === 'all') {
  $filter = 'checked_in';
}

$filter_destination = isset($_GET['filter_destination']) ? intval($_GET['filter_destination']) : 0;
$rows_per_page = isset($_GET['rows_per_page']) ? intval($_GET['rows_per_page']) : 10;

// Build dynamic query with parameterized conditions
$whereParts = [];
$params = [];
$paramTypes = "";

// Apply role-based restrictions
$role = $_SESSION['role'];
$destination_id = $_SESSION['destination_id'];

// Debug: Log filter values
error_log("DEBUG: filter_destination = " . ($filter_destination ?? 'NULL') . ", role = " . $role);

if ($role === 'destination_admin') {
  $whereParts[] = "v.destination = ?";
  $params[] = $destination_id;
  $paramTypes .= "i";
  error_log("DEBUG: Destination admin restricted to destination_id = " . $destination_id);
}

if ($role === 'receptionist') {
  $whereParts[] = "DATE(v.time_in) = CURDATE()"; // only today's visitors
}

if ($filter === 'checked_in') {
  $whereParts[] = "(v.time_out IS NULL OR v.time_out = '' OR v.time_out = '0000-00-00 00:00:00')";
} elseif ($filter === 'checked_out') {
  $whereParts[] = "v.time_out IS NOT NULL AND v.time_out != '' AND v.time_out != '0000-00-00 00:00:00'";
}

if ($filter_destination !== 0) {
  $whereParts[] = "v.destination = ?";
  $params[] = $filter_destination;
  $paramTypes .= "i";
  error_log("DEBUG: Added destination filter = " . $filter_destination);
}

// Debug: Log final WHERE clause and parameters
$whereClause = count($whereParts) ? "WHERE " . implode(" AND ", $whereParts) : "";
error_log("DEBUG: Final WHERE clause = " . $whereClause);
error_log("DEBUG: Final parameters = " . json_encode($params));

// --- Pagination setup ---
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Count total rows with prepared statement
$count_sql = "SELECT COUNT(*) AS total FROM visitors v $whereClause";
$count_stmt = $conn->prepare($count_sql);
if ($paramTypes && count($params) > 0) {
  $count_stmt->bind_param($paramTypes, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = 0;
if ($count_result && $row = $count_result->fetch_assoc()) {
  $total_rows = $row['total'];
}
$count_stmt->close();

$totalPages = ceil($total_rows / $rows_per_page);
$start = ($page - 1) * $rows_per_page;

// Fetch paginated data with prepared statement
$sql = "SELECT v.id, v.fullname, v.faculty_organization, v.phone_number, v.purpose, 
               d.name AS destination, v.visitor_type, v.time_in, v.time_out, 
               v.is_alternate, k.card_number AS keycard
        FROM visitors v
        LEFT JOIN destinations d ON v.destination = d.id
        LEFT JOIN keycards k ON v.keycard_id = k.id
        $whereClause
        ORDER BY v.time_in DESC
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);
if ($paramTypes && count($params) > 0) {
  // Add LIMIT parameters to the end
  $allParams = array_merge($params, [$start, $rows_per_page]);
  $allTypes = $paramTypes . "ii";
  $stmt->bind_param($allTypes, ...$allParams);
} else {
  $stmt->bind_param("ii", $start, $rows_per_page);
}
$stmt->execute();
$result = $stmt->get_result();

$paginated_data = [];
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $paginated_data[] = $row;
  }
}
$stmt->close();

// Fetch all destinations
$destinationOptions = [];
$dest_sql = "SELECT id, name FROM destinations ORDER BY name ASC";
$dest_result = $conn->query($dest_sql);
if ($dest_result && $dest_result->num_rows > 0) {
  while ($row = $dest_result->fetch_assoc()) {
    $destinationOptions[] = $row;
  }
}

$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="admin_dashboard.css?v=<?= time() ?>">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

  <!-- Top Header -->
  <header class="app-header">
    <div class="logo-container">
      <img src="ui_logo-removebg-preview.png" alt="UI Logo">
    </div>

    <div class="simple-menu-container">
      <button class="menu-toggle" id="menuToggle">
        <span>Menu</span>
        <span class="hamburger">☰</span>
      </button>

      <div class="menu-panel" id="menuPanel">
        <div class="menu-header">
          <span>Admin Menu</span>
          <button class="menu-close" id="menuClose">×</button>
        </div>
        <a href="admin_dashboard.php"><span class="menu-icon">📊</span> Dashboard</a>
        <a href="notifications.php"><span class="menu-icon">🔔</span> All Notifications</a>
        <a href="change_password.php"><span class="menu-icon">🔒</span> Change Password</a>
        <a href="logout.php" class="logout-btn">Logout</a>
      </div>
    </div>
  </header>

  <!-- Main Admin Dashboard Layout -->
  <div class="adm-dash container dashboard-container">

    <div class="dashboard-grid">

      <!-- SIDEBAR: Profile, Availability & Notifications -->
      <aside class="dashboard-sidebar">

        <!-- 1. Profile Summary -->
        <div class="sidebar-profile">
          <div class="sidebar-avatar">
            <?= strtoupper(substr($profile['username'], 0, 2)) ?>
          </div>
          <div class="sidebar-name"><?= htmlspecialchars($profile['username']) ?></div>
          <div class="sidebar-role">
            <span class="role-badge role-<?= htmlspecialchars($profile['role']) ?>">
              <?= ucwords(str_replace('_', ' ', $profile['role'])) ?>
            </span>
          </div>
          <?php if ($profile['destination_name']): ?>
            <div class="profile-destination">
              📍 <?= htmlspecialchars($profile['destination_name']) ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- 2. Availability Control (Dropdown) -->
        <div class="sidebar-availability">
          <div class="sidebar-section-title">
            <span>My Availability</span>
            <span class="status-indicator">●</span>
          </div>

          <div class="status-dropdown-wrapper">
            <select id="statusDropdown"
              class="status-select status-<?= htmlspecialchars($profile['availability_status']) ?>">
              <option value="available" <?= $profile['availability_status'] === 'available' ? 'selected' : '' ?>>✓
                Available</option>
              <option value="busy" <?= $profile['availability_status'] === 'busy' ? 'selected' : '' ?>>⏱ Busy</option>
              <option value="unavailable" <?= $profile['availability_status'] === 'unavailable' ? 'selected' : '' ?>>✕
                Unavailable</option>
              <option value="away" <?= $profile['availability_status'] === 'away' ? 'selected' : '' ?>>☾ Away</option>
            </select>
          </div>

          <input type="text" id="statusMessage" class="status-message-input-sm"
            placeholder="Status message (e.g. In a meeting)"
            value="<?= htmlspecialchars($profile['status_message'] ?? '') ?>">
          <div id="statusUpdateMessage" class="status-update-message"></div>
        </div>

        <!-- 3. Recent Notifications -->
        <div class="sidebar-notifications-section">
          <div class="sidebar-section-title">
            <span>Notifications</span>
            <span class="notification-badge"><?= $unread_count ?></span>
          </div>
          <div class="sidebar-notifications" id="sidebarNotificationsList">
            <?php if (empty($recent_notifications)): ?>
              <div class="notif-empty">No new notifications</div>
            <?php else: ?>
              <?php foreach ($recent_notifications as $notif): ?>
                <div class="notif-item-sm <?= $notif['is_read'] ? 'read' : 'unread' ?>"
                  data-notification-id="<?= $notif['id'] ?>" onclick="toggleSidebarNotification(this)">
                  <span class="notif-time-sm"><?= date('g:i A', strtotime($notif['created_at'])) ?></span>
                  <?= htmlspecialchars(strlen($notif['message']) > 40 ? substr($notif['message'], 0, 37) . '...' : $notif['message']) ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <a href="notifications.php" class="notif-view-all">View All &rarr;</a>
        </div>

        <!-- 4. Messages / Queue -->
        <div class="sidebar-messages-section" style="margin-top: 20px;">
          <div class="sidebar-section-title">
            <span>Messages / Queue</span>
            <span class="notification-badge" id="queueCount">0</span>
          </div>
          <div class="sidebar-messages" id="sidebarMessageQueue">
            <div class="queue-empty">Loading...</div>
          </div>
        </div>

      </aside>

      <!-- MAIN CONTENT: Visitor Records -->
      <main class="dashboard-main">
        <h2 class="page-title">Visitor Records</h2>

        <?php if ($_SESSION['role'] !== 'receptionist'): ?>
          <!-- Filter Bar - Admin View Only -->
          <div class="filter-bar">
            <a href="admin_dashboard.php?filter=all&filter_destination=<?= urlencode($filter_destination) ?>&rows_per_page=<?= urlencode($rows_per_page) ?>"
              class="<?= $filter === 'all' ? 'active' : '' ?>">All</a>
            <a href="admin_dashboard.php?filter=checked_in&filter_destination=<?= urlencode($filter_destination) ?>&rows_per_page=<?= urlencode($rows_per_page) ?>"
              class="<?= $filter === 'checked_in' ? 'active' : '' ?>">Checked In</a>
            <a href="admin_dashboard.php?filter=checked_out&filter_destination=<?= urlencode($filter_destination) ?>&rows_per_page=<?= urlencode($rows_per_page) ?>"
              class="<?= $filter === 'checked_out' ? 'active' : '' ?>">Checked Out</a>
          </div>
        <?php else: ?>
          <!-- Filter Bar - Receptionist View (Today Only) -->
          <div class="filter-bar">
            <a href="admin_dashboard.php?filter=checked_in"
              class="<?= $filter === 'checked_in' || $filter === 'all' ? 'active' : '' ?>">Checked In Today</a>
            <a href="admin_dashboard.php?filter=checked_out"
              class="<?= $filter === 'checked_out' ? 'active' : '' ?>">Checked Out Today</a>
          </div>
        <?php endif; ?>

        <?php if ($_SESSION['role'] !== 'receptionist' && $_SESSION['role'] !== 'destination_admin'): ?>
          <!-- Destination Filter + Rows per Page -->
          <form method="GET" action="admin_dashboard.php" class="filter-controls" id="filterForm">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">

            <div class="filter-group">
              <label for="filter_destination">Destination:</label>
              <select name="filter_destination" id="filter_destination" onchange="this.form.submit()">
                <option value="0" <?= $filter_destination == 0 ? 'selected' : '' ?>>All Destinations</option>
                <?php foreach ($destinationOptions as $dest): ?>
                  <option value="<?= $dest['id'] ?>" <?= $filter_destination == $dest['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($dest['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="filter-group">
              <label for="rows_per_page">Rows per page:</label>
              <select name="rows_per_page" id="rows_per_page" onchange="this.form.submit()">
                <?php foreach ([10, 25, 50, 100] as $opt): ?>
                  <option value="<?= $opt ?>" <?= $rows_per_page == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </form>
        <?php endif; ?>


        <?php if (count($paginated_data) > 0): ?>
          <div class="table-container">
            <table class="visitor-table">
              <thead>
                <tr>
                  <th>S/N</th>
                  <th>Full Name</th>
                  <th>Faculty/Organization</th>
                  <th>Phone</th>
                  <th>Purpose</th>
                  <th>Destination</th>
                  <th>Visitor Type</th>
                  <th>Visitor Status</th>
                  <th>Time In</th>
                  <th>Keycard</th>
                  <th>Time Out</th>
                  <?php if ($_SESSION['role'] === 'receptionist'): ?>
                    <th>Action</th>
                  <?php endif; ?>

                </tr>
              </thead>
              <tbody>
                <?php $serial_number = $start + 1; ?>
                <?php foreach ($paginated_data as $row): ?>
                  <tr>
                    <td><?= $serial_number ?></td>
                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                    <td><?= htmlspecialchars($row['faculty_organization']) ?></td>
                    <td><?= htmlspecialchars($row['phone_number']) ?></td>
                    <td>
                      <div class="tooltip">
                        <?= htmlspecialchars(previewText($row['purpose'], 12)) ?>
                        <span class="tooltiptext"><?= htmlspecialchars($row['purpose']) ?></span>
                      </div>
                    </td>
                    <td><?= htmlspecialchars($row['destination']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($row['visitor_type'])) ?></td>
                    <td>
                      <?php if ($row['is_alternate'] == 1): ?>
                        <span class="alt-marker">Message</span>
                      <?php elseif ($row['time_out'] && $row['time_out'] !== '0000-00-00 00:00:00'): ?>
                        <span class="checked-out-marker">Checked Out</span>
                      <?php else: ?>
                        <span class="normal-marker">Checked In</span>
                      <?php endif; ?>


                    </td>

                    <td><?= htmlspecialchars($row['time_in']) ?></td>
                    <td class="keycard-cell">
                      <?= $row['keycard'] ? htmlspecialchars($row['keycard']) : '—' ?>
                    </td>
                    <td>
                      <?= ($row['time_out'] && $row['time_out'] !== '0000-00-00 00:00:00') ? htmlspecialchars($row['time_out']) : '—' ?>
                    </td>

                    <?php if ($_SESSION['role'] === 'receptionist'): ?>
                      <td>
                        <?php if (!$row['time_out'] || $row['time_out'] === '0000-00-00 00:00:00'): ?>
                          <form method="POST" action="checkout.php" class="checkout-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="visitor_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="checkout-btn">CHECK OUT</button>
                          </form>
                        <?php else: ?>
                          <?= htmlspecialchars($row['time_out']) ?>
                        <?php endif; ?>
                      </td>
                    <?php endif; ?>

                  </tr>
                  <?php $serial_number++; ?>
                <?php endforeach; ?>
              </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?filter=<?= urlencode($filter) ?>&filter_destination=<?= urlencode($filter_destination) ?>&rows_per_page=<?= $rows_per_page ?>&page=<?= $i ?>"
                  class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
              <?php endfor; ?>
            </div>
          </div>
        <?php else: ?>
          <p class="no-records-message">No records available for this view.</p>
        <?php endif; ?>
      </main>
    </div> <!-- End Dashboard Grid -->
  </div> <!-- End Container -->

  <script>
    // Pass Profile Data for JS - Essential for new Sidebar functions
    window.PROFILE_DATA = {
      userId: <?= $_SESSION['user_id'] ?>,
      destinationId: <?= $_SESSION['destination_id'] ?? 0 ?>,
      role: '<?= $_SESSION['role'] ?>',
      csrfToken: '<?= $_SESSION['csrf_token'] ?>'
    };
  </script>
  <script src="assets/js/realtime_client.js"></script>
  <script src="admin_dashboard.js"></script>
  <script src="simple_menu.js"></script>
</body>

</html>