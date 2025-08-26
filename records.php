<?php
// File: records.php
include 'db.php';
$today = date('Y-m-d');
$sql = "SELECT * FROM visitors WHERE DATE(time_in) = ? ORDER BY time_in DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Today's Visitor Records</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(to bottom right, #e1f0ff, #f5faff);
      padding: 30px;
      font-family: 'Segoe UI', sans-serif;
    }
    .container {
      max-width: 1000px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0,0,0,0.1);
    }
    table {
      width: 100%;
    }
    table th, table td {
      padding: 10px;
      text-align: center;
      vertical-align: middle;
    }
    .btn-checkout {
      background-color: #dc3545;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 5px;
    }
    .btn-checkout:disabled {
      background-color: #6c757d;
    }
    h2 {
      font-weight: bold;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2 class="text-center">Today's Visitor Records</h2>
    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="table-primary">
          <tr>
            <th>Full Name</th>
            <th>Faculty/Org</th>
            <th>Phone</th>
            <th>Purpose</th>
            <th>Destination</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row['fullname']) ?></td>
            <td><?= htmlspecialchars($row['faculty_organization']) ?></td>
            <td><?= htmlspecialchars($row['phone_number']) ?></td>
            <td><?= htmlspecialchars($row['purpose']) ?></td>
            <td><?= htmlspecialchars($row['destination']) ?></td>
            <td><?= $row['time_in'] ?></td>
            <td><?= $row['time_out'] ?: '---' ?></td>
            <td>
              <?php if (is_null($row['time_out']) || $row['time_out'] == '0000-00-00 00:00:00'): ?>
                <button type="button" onclick="checkOutVisitor(<?= $row['id'] ?>, this)" class="btn-checkout">Check Out</button>
              <?php else: ?>
                <span class="text-muted">Checked Out</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <a href="index.php" class="btn btn-secondary mt-3">Back to Home</a>
  </div>

  <script>
    function checkOutVisitor(id, btn) {
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "timeout.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onload = function () {
        if (xhr.status === 200 && xhr.responseText.trim() === "success") {
          btn.textContent = "Checked Out";
          btn.disabled = true;
          btn.classList.remove("btn-checkout");
          btn.classList.add("btn-secondary");
        } else {
          alert("Error checking out. Please try again.");
        }
      };
      xhr.send("id=" + encodeURIComponent(id));
    }
  </script>
</body>
</html>
