<?php
function previewText($text, $limit = 12) {
    $words = explode(' ', $text);
    if (count($words) > $limit) {
        return implode(' ', array_slice($words, 0, $limit)) . '...';
    }
    return $text;
}

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

include 'db.php';

// --- Handle filters ---
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$filter_destination = isset($_GET['filter_destination']) ? $_GET['filter_destination'] : '';
$rows_per_page = isset($_GET['rows_per_page']) ? intval($_GET['rows_per_page']) : 10;

$whereParts = [];
if ($filter === 'checked_in') {
    $whereParts[] = "(time_out IS NULL OR time_out = '' OR time_out = '0000-00-00 00:00:00')";
} elseif ($filter === 'checked_out') {
    $whereParts[] = "time_out IS NOT NULL AND time_out != '' AND time_out != '0000-00-00 00:00:00'";
}

// --- Fixed destinations list ---
$fixedDestinations = [
    "Director's Office",
    "ITeMS Board Secretariat",
    "Nelfund Support",
    "Workshop/Seminar"
];

// --- Destination filter ---
if ($filter_destination !== '') {
    if ($filter_destination === 'other') {
        $inList = "'" . implode("','", array_map([$conn, 'real_escape_string'], $fixedDestinations)) . "'";
        $whereParts[] = "destination NOT IN ($inList)";
    } else {
        $whereParts[] = "destination = '" . $conn->real_escape_string($filter_destination) . "'";
    }
}
$whereClause = count($whereParts) ? "WHERE " . implode(" AND ", $whereParts) : "";

// --- Pagination setup ---
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Count total rows
$count_sql = "SELECT COUNT(*) AS total FROM visitors $whereClause";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_assoc()['total'];

$totalPages = ceil($total_rows / $rows_per_page);
$start = ($page - 1) * $rows_per_page;

// Fetch paginated data
$sql = "SELECT fullname, faculty_organization, phone_number, purpose, destination, visitor_type, time_in, time_out, is_alternate 
        FROM visitors 
        $whereClause
        ORDER BY time_in DESC
        LIMIT $start, $rows_per_page";
$result = $conn->query($sql);

$paginated_data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $paginated_data[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <div style="position: absolute; top: 10px; left: 10px;">
    <img src="ui_logo-removebg-preview.png" alt="Logo" style="height: 100px;">
  </div>
  <link rel="stylesheet" href="style.css">
  <style>
    .dropdown { position: absolute; top: 15px; right: 20px; z-index: 1000; }
    .dropdown-button { padding: 10px 16px; font-size: 16px; background-color: #1a73e8; color: white; border: none; border-radius: 5px; cursor: pointer; }
    .dropdown-content { display: none; position: absolute; right: 0; background-color: #f9f9f9; min-width: 180px; box-shadow: 0px 8px 16px rgba(0,0,0,0.2); border-radius: 8px; z-index: 1001; }
    .dropdown-content a { color: black; padding: 12px 16px; display: block; text-decoration: none; }
    .dropdown-content a:hover { background-color: #f1f1f1; }
    .dropdown.show .dropdown-content { display: block; }

    .filter-bar { margin-top: 120px; text-align: center; }
    .filter-bar a { margin: 0 10px; padding: 8px 14px; background-color: #eee; color: #333; border-radius: 4px; text-decoration: none; }
    .filter-bar a.active { background-color: #1a73e8; color: white; font-weight: bold; }

    .pagination { margin-top: 20px; text-align: center; }
    .pagination a { margin: 0 5px; text-decoration: none; padding: 6px 12px; background-color: #1a73e8; color: white; border-radius: 4px; }
    .pagination a.active { background-color: #0d47a1; }

    .alt-marker { color: red; font-weight: bold; }
    .normal-marker { color: green; font-weight: bold; }

    .visitor-table { width: 95%; margin: auto; border-collapse: collapse; }
    .visitor-table th { background-color: #1a73e8; color: white; padding: 10px; text-align: left; }
    .visitor-table td { background: white; padding: 10px; border-bottom: 1px solid #ddd; }
  </style>
</head>
<body>
  <div class="dropdown">
    <button class="dropdown-button">☰ Menu</button>
    <div class="dropdown-content">
      <a href="backup_records.php">Export</a>
      <a href="logout.php" style="color: red;">Logout</a>
    </div>
  </div>

  <div class="container">
    <h2 style="text-align: center;">VISITOR RECORDS</h2>

    <!-- Filter Bar -->
    <div class="filter-bar">
      <a href="admin_dashboard.php?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">All</a>
      <a href="admin_dashboard.php?filter=checked_in" class="<?= $filter === 'checked_in' ? 'active' : '' ?>">Checked In</a>
      <a href="admin_dashboard.php?filter=checked_out" class="<?= $filter === 'checked_out' ? 'active' : '' ?>">Checked Out</a>
    </div>

    <!-- Destination Filter + Rows per Page -->
    <form method="GET" action="admin_dashboard.php" style="text-align: center; margin: 15px 0;">
      <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
      <label for="filter_destination"><strong>Destination:</strong></label>
      <select name="filter_destination" id="filter_destination" onchange="this.form.submit()">
        <option value="">-- All Destinations --</option>
        <?php foreach ($fixedDestinations as $dest): ?>
          <option value="<?= htmlspecialchars($dest) ?>" <?= $filter_destination === $dest ? 'selected' : '' ?>>
            <?= htmlspecialchars($dest) ?>
          </option>
        <?php endforeach; ?>
        <option value="other" <?= $filter_destination === 'other' ? 'selected' : '' ?>>Other</option>
      </select>

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
            <th>Check-In Mode</th>
            <th>Time In</th>
            <th>Time Out</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($paginated_data as $row): ?>
         
            <tr>
  <td class="fullname"><?= htmlspecialchars($row['fullname']) ?></td>
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
                  <span class="alt-marker">Alternate</span>
                <?php else: ?>
                  <span class="normal-marker">Normal</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($row['time_in']) ?></td>
              <td><?= ($row['time_out'] && $row['time_out'] !== '0000-00-00 00:00:00') ? htmlspecialchars($row['time_out']) : '—' ?></td>
            </tr>
  
          <?php endforeach; ?>
          
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?filter=<?= $filter ?>&filter_destination=<?= urlencode($filter_destination) ?>&rows_per_page=<?= $rows_per_page ?>&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
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
