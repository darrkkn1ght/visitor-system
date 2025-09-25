<?php

function previewText($text, $limit = 12) {
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
    exit;
}
// If logged in, proceed
include 'db.php';

// --- Handle filters ---
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filter_destination = isset($_GET['filter_destination']) ? $_GET['filter_destination'] : '';
$rows_per_page = isset($_GET['rows_per_page']) ? intval($_GET['rows_per_page']) : 10;

$whereParts = [];
// Apply role-based restrictions
$role = $_SESSION['role'];
$destination_id = $_SESSION['destination_id'];

if ($role === 'destination_admin') {
    $whereParts[] = "v.destination = " . intval($destination_id);
}

if ($role === 'receptionist') {
    $whereParts[] = "DATE(v.time_in) = CURDATE()"; // only today
    $whereParts[] = "(v.time_out IS NULL OR v.time_out = '' OR v.time_out = '0000-00-00 00:00:00')"; // only those still checked in
}

  if ($filter === 'checked_in') {
    $whereParts[] = "(v.time_out IS NULL OR v.time_out = '' OR v.time_out = '0000-00-00 00:00:00')";
} elseif ($filter === 'checked_out') {
    $whereParts[] = "v.time_out IS NOT NULL AND v.time_out != '' AND v.time_out != '0000-00-00 00:00:00'";
}

if ($filter_destination !== '') {
    $whereParts[] = "v.destination = " . intval($filter_destination);
}
$whereClause = count($whereParts) ? "WHERE " . implode(" AND ", $whereParts) : "";

// --- Pagination setup ---
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Count total rows
$count_sql = "SELECT COUNT(*) AS total FROM visitors v $whereClause";
$count_result = $conn->query($count_sql);
$total_rows = 0;
if ($count_result && $row = $count_result->fetch_assoc()) {
    $total_rows = $row['total'];
}
$totalPages = ceil($total_rows / $rows_per_page);
$start = ($page - 1) * $rows_per_page;

// Fetch paginated data
$sql = "SELECT v.id, v.fullname, v.faculty_organization, v.phone_number, v.purpose, 
               d.name AS destination, v.visitor_type, v.time_in, v.time_out, 
               v.is_alternate, k.card_number AS keycard
        FROM visitors v
        LEFT JOIN destinations d ON v.destination = d.id
        LEFT JOIN keycards k ON v.keycard_id = k.id
        $whereClause
        ORDER BY v.time_in DESC
        LIMIT $start, $rows_per_page";

$result = $conn->query($sql);

$paginated_data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $paginated_data[] = $row;
    }
}

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
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .dropdown { position: absolute; top: 15px; right: 20px; z-index: 1000; }
    .dropdown-button { padding: 10px 16px; font-size: 16px; background-color: #1a73e8; color: white; border: none; border-radius: 5px; cursor: pointer; }
    .dropdown-content { display: none; position: absolute; right: 0; background-color: #f9f9f9; min-width: 180px; box-shadow: 0px 8px 16px rgba(0,0,0,0.2); border-radius: 8px; z-index: 1001; }
    .dropdown-content a { color: black; padding: 12px 16px; display: block; text-decoration: none; }
    .dropdown-content a:hover { background-color: #f1f1f1; }
    .dropdown.show .dropdown-content { display: block; }

    .visitor-table th { background-color: #1a73e8; color: white; padding: 10px; text-align: left; }
    .visitor-table td { background: white; padding: 10px; border-bottom: 1px solid #ddd; }
  </style>
</head>
<body>
  
  <div style="position: absolute; top: 10px; left: 10px;">
    <img src="ui_logo-removebg-preview.png" alt="Logo" style="height: 100px;">
  </div>

  <div class="dropdown">
    <button class="dropdown-button">☰ Menu</button>
    <div class="dropdown-content">
      <?php if ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'director'): ?>
    <a href="destinations.php">Manage Destinations</a>
  <?php endif; ?>
      <a href="backup_records.php">Export</a>
      <a href="logout.php" style="color: red;">Logout</a>
    </div>
  </div>

  <div class="container">
    <h2 style="text-align: center;">VISITOR RECORDS</h2>

    <?php if ($_SESSION['role'] !== 'receptionist'): ?>
    <!-- Filter Bar -->
    <div class="filter-bar" style="text-align:center;">
      <a href="admin_dashboard.php?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">All</a>
      <a href="admin_dashboard.php?filter=checked_in" class="<?= $filter === 'checked_in' ? 'active' : '' ?>">Checked In</a>
      <a href="admin_dashboard.php?filter=checked_out" class="<?= $filter === 'checked_out' ? 'active' : '' ?>">Checked Out</a>
    </div>
<?php endif; ?>

<?php if ($_SESSION['role'] !== 'receptionist' && $_SESSION['role'] !== 'destination_admin'): ?>

    <!-- Destination Filter + Rows per Page -->
    <form method="GET" action="admin_dashboard.php" style="text-align: center; margin: 15px 0;">
      <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
      <label for="filter_destination"><strong>Destination:</strong></label>
      <select name="filter_destination" id="filter_destination" onchange="this.form.submit()">
        <option value=""> All Destinations </option>
        <?php foreach ($destinationOptions as $dest): ?>
          <option value="<?= (int)$dest['id'] ?>" <?= (string)$filter_destination === (string)$dest['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($dest['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
<?php endif; ?>
      &nbsp;&nbsp;

      <label for="rows_per_page"><strong>Rows per page:</strong></label>
      <select name="rows_per_page" id="rows_per_page" onchange="this.form.submit()">
        <?php foreach ([10,25,50,100] as $opt): ?>
          <option value="<?= $opt ?>" <?= $rows_per_page == $opt ? 'selected' : '' ?>><?= $opt ?></option>
        <?php endforeach; ?>
      </select>
    </form>


    <?php if (count($paginated_data) > 0): ?>
    <div class="table-container">
      <table class="visitor-table">
        <thead>
          <tr>
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
          <?php foreach ($paginated_data as $row): ?>
            <tr>
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
                  <span class="normal-marker">Checked Out</span>
                <?php else: ?>
                  <span class="normal-marker">Checked In</span>
                <?php endif; ?>


</td>

              <td><?= htmlspecialchars($row['time_in']) ?></td>
              <td style="font-weight: bold; text-align: center;">
                <?= $row['keycard'] ? htmlspecialchars($row['keycard']) : '—' ?>
              </td>
              <td><?= ($row['time_out'] && $row['time_out'] !== '0000-00-00 00:00:00') ? htmlspecialchars($row['time_out']) : '—' ?></td>
              
            <?php if ($_SESSION['role'] === 'receptionist'): ?>
              <td>
                <?php if (!$row['time_out'] || $row['time_out'] === '0000-00-00 00:00:00'): ?>
                  <form method="POST" action="checkout.php" style="margin:0;">
                    <input type="hidden" name="visitor_id" value="<?= $row['id'] ?>">
                    <button type="submit" class="checkout-btn">CHECK OUT</button>
                  </form>
                <?php else: ?>
                  <?= htmlspecialchars($row['time_out']) ?>
                <?php endif; ?>
              </td>
            <?php endif; ?>

            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?filter=<?= urlencode($filter) ?>&filter_destination=<?= urlencode($filter_destination) ?>&rows_per_page=<?= $rows_per_page ?>&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    </div>
    <?php else: ?>
      <p style="text-align: center; font-weight: bold;">No records available for this view.</p>
    <?php endif; ?>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const dropdown = document.querySelector(".dropdown");
      const button = document.querySelector(".dropdown-button");

      button.addEventListener("click", function (e) {
        e.stopPropagation();
        dropdown.classList.toggle("show");
      });

      document.addEventListener("click", function (event) {
        if (!dropdown.contains(event.target)) {
          dropdown.classList.remove("show");
        }
      });
    });
  </script>
</body>
</html>
