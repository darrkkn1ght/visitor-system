<?php
session_start();
include 'db.php';

// Message arrays
$messages = ['success' => [], 'error' => []];
function add_success($msg) { global $messages; $messages['success'][] = $msg; }
function add_error($msg) { global $messages; $messages['error'][] = $msg; }

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Create destination
    if ($action === 'create_dest') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '') add_error("Destination name cannot be empty.");
        else {
            // Check for duplicate name
            $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM destinations WHERE name = ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $exists = $stmt->get_result()->fetch_assoc()['c'] ?? 0;
            $stmt->close();

            if ($exists > 0) {
                add_error("Destination name already exists. Please choose a different name.");
            } else {
                $stmt = $conn->prepare("INSERT INTO destinations (name) VALUES (?)");
                $stmt->bind_param("s", $name);
                if ($stmt->execute()) {
                    $destination_id = $stmt->insert_id;
                    $username = strtolower(preg_replace('/\s+/', '', $name)) . '_admin';
                    $default_password_plain = "changeme123";
                    $default_password = password_hash($default_password_plain, PASSWORD_DEFAULT);
                    $role = "destination_admin";

                    // Check if username exists
                    $user_check_stmt = $conn->prepare("SELECT COUNT(*) AS c FROM users WHERE username = ?");
                    $user_check_stmt->bind_param("s", $username);
                    $user_check_stmt->execute();
                    $user_exists = $user_check_stmt->get_result()->fetch_assoc()['c'] ?? 0;
                    $user_check_stmt->close();

                    if ($user_exists > 0) {
                        add_error("Destination admin username '$username' already exists. Please choose a different destination name.");
                    } else {
                        $user_stmt = $conn->prepare("INSERT INTO users (username, password, role, destination_id) VALUES (?, ?, ?, ?)");
                        $user_stmt->bind_param("sssi", $username, $default_password, $role, $destination_id);
                        if ($user_stmt->execute()) {
                            add_success(
                                "Destination '<strong>" . htmlspecialchars($name, ENT_QUOTES) . "</strong>' created.<br>" .
                                "Destination admin credentials:<br>" .
                                "Username: <strong>" . htmlspecialchars($username, ENT_QUOTES) . "</strong><br>" .
                                "Password: <strong>$default_password_plain</strong><br>" .
                                "<span style='color:#c92a2a'>Please copy and share these credentials securely!</span>"
                            );
                        } else {
                            add_error("Failed to create destination admin user: " . $user_stmt->error);
                        }
                        $user_stmt->close();
                    }
                } else {
                    add_error("Create failed: " . $stmt->error);
                }
                $stmt->close();
            }
        }
        // PRG redirect
        $_SESSION['messages'] = $messages;
        header("Location: destinations.php");
        exit();
    }

    // Batch delete keycards
    if ($action === 'batch_delete_keycards') {
        $destination_id = intval($_POST['destination_id']);
        $check_sql = "SELECT COUNT(*) AS assigned_count FROM keycards WHERE destination_id = ? AND is_assigned = 1";
        $stmt_check = $conn->prepare($check_sql);
        $stmt_check->bind_param("i", $destination_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $assigned_count = $result_check->fetch_assoc()['assigned_count'];
        $stmt_check->close();

        if ($assigned_count > 0) {
            add_error("Cannot delete: Some keycards are currently assigned to visitors.");
        } else {
            $del_sql = "DELETE FROM keycards WHERE destination_id = ?";
            $stmt = $conn->prepare($del_sql);
            $stmt->bind_param("i", $destination_id);
            if ($stmt->execute()) {
                add_success("All keycards for this destination have been deleted.");
            } else {
                add_error("Error deleting keycards.");
            }
            $stmt->close();
        }
        // PRG redirect
        $_SESSION['messages'] = $messages;
        header("Location: destinations.php");
        exit();
    }

    // Delete destination
    if ($action === 'delete_dest') {
        $id = intval($_POST['dest_id'] ?? 0);
        if ($id <= 0) add_error("Invalid destination id.");
        else {
            // Check for linked visitors/keycards
            $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM visitors WHERE destination = ?");
            $stmt->bind_param("i", $id); $stmt->execute();
            $r = $stmt->get_result()->fetch_assoc(); $stmt->close();

            $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM keycards WHERE destination_id = ?");
            $stmt->bind_param("i", $id); $stmt->execute();
            $r2 = $stmt->get_result()->fetch_assoc(); $stmt->close();

            if ($r['c'] > 0 || $r2['c'] > 0) add_error("Cannot delete destination while there are visitors or keycards linked to it. Remove or reassign them first.");
            else {
                // Delete users linked to this destination
                $stmt = $conn->prepare("DELETE FROM users WHERE destination_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();

                // Delete the destination itself
                $stmt = $conn->prepare("DELETE FROM destinations WHERE id = ?");
                $stmt->bind_param("i", $id);
                if ($stmt->execute()) add_success("Destination and its admin users deleted.");
                else add_error("Delete failed: " . $stmt->error);
                $stmt->close();
            }
        }
        // PRG redirect
        $_SESSION['messages'] = $messages;
        header("Location: destinations.php");
        exit();
    }
}

// Load messages after redirect
if (isset($_SESSION['messages'])) {
    $messages = $_SESSION['messages'];
    unset($_SESSION['messages']);
}

// fetch destinations
$destinations = [];
$res = $conn->query("SELECT id, name FROM destinations ORDER BY name ASC");
if ($res) while ($r = $res->fetch_assoc()) $destinations[] = $r;

// helper to get keycard counts per destination
function get_keycard_counts($conn, $dest_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total, SUM(CASE WHEN is_assigned = 0 THEN 1 ELSE 0 END) AS available FROM keycards WHERE destination_id = ?");
    $stmt->bind_param("i", $dest_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return ['total' => intval($row['total'] ?? 0), 'available' => intval($row['available'] ?? 0)];
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Destinations — Management</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body { font-family: Arial, sans-serif; background:#f9fafc; margin:18px; color:#222; }
    .container { max-width: 2500px; margin:0 auto; }
    h2 { margin:0 0 14px 0; font-size:22px; color:#1a3a6b; }
    .top-actions { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; gap:12px; }
    .messages { margin-bottom:12px; }
    .messages .ok { background:#d4edda; color:#155724; padding:10px; border-radius:6px; margin-bottom:6px; }
    .messages .err { background:#f8d7da; color:#721c24; padding:10px; border-radius:6px; margin-bottom:6px; }
    .messages .ok, .messages .err {
      transition: opacity 0.7s;
    }
    .messages .fade-out {
      opacity: 0;
    }
    .card { background:#fff; padding:14px; border-radius:8px; box-shadow:0 2px 8px rgba(12,30,63,0.04); }
    .toolbar { display:flex; gap:8px; align-items:center; }
    .small-btn { padding:6px 12px; border-radius:6px; border:1px solid #cfd8e3; background:#fff; cursor:pointer; font-size:13px; }
    .small-btn:hover { background:#f1f5fb; }
    .danger { color:#c92a2a; border-color:#f2c2c2; }
    .success { color:#117a37; border-color:#bfe6c7; }
    form.inline-form input[type="text"], form.inline-form input[type="number"], select { padding:6px; border:1px solid #ddd; border-radius:6px; }
    table { width:100%; border-collapse:collapse; margin-top:12px; }
    th, td { padding:10px; border-bottom:1px solid #eef1f6; vertical-align:top; text-align:left; }
    th { background:#fbfdff; color:#123; font-weight:600; border-top:1px solid #eef1f6; }
    tr:nth-child(even) td { background:#fbfcff; }
    details summary { cursor:pointer; font-weight:600; color:#1a3a6b; margin-bottom:6px; }
    .keycard-table { width:100%; border:1px solid #e6ecf5; border-radius:6px; overflow:hidden; }
    .muted { color:#666; font-size:13px; }
    .back-link { display:inline-block; margin-top:14px; text-decoration:none; color:#1a73e8; }
    .back-link:hover { text-decoration:underline; }
    .form-row { display:flex; gap:8px; align-items:center; }
    .form-row input[type="text"]{ width:120px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="top-actions">
      <div>
        <h2>Destinations Management</h2>
        <div class="muted">Manage destinations and their keycard pools (super admin)</div>
      </div>
      <div class="toolbar">
        <a href="admin_dashboard.php" class="small-btn">Back to Dashboard</a>
      </div>
    </div>

    <div class="messages">
      <?php foreach ($messages['success'] as $m): ?><div class="ok"><?= $m ?></div><?php endforeach; ?>
      <?php foreach ($messages['error'] as $m): ?><div class="err"><?= htmlspecialchars($m) ?></div><?php endforeach; ?>
    </div>

    <div class="card">
      <!-- Create new destination -->
      <form method="POST" style="display:flex; gap:8px; align-items:center; margin-bottom:12px;">
        <input type="hidden" name="action" value="create_dest">
        <input name="name" placeholder="Create A New Destination" style="flex:1;padding:8px;border-radius:6px;border:1px solid #ddd" required>
        <button type="submit" class="small-btn success">Create</button>
      </form>

      <table>
        <thead>
          <tr>
            <th style="width:50px">#</th>
            <th style="width:25%">Destination</th>
            <th>Keycard Pool</th>
            <th style="width:320px">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($destinations) === 0): ?>
            <tr><td colspan="4" class="muted" style="padding:20px">No destinations yet.</td></tr>
          <?php endif; ?>

          <?php foreach ($destinations as $i => $dest):
            $counts = get_keycard_counts($conn, $dest['id']);
          ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td>
                <strong><?= htmlspecialchars($dest['name']) ?></strong>
                <div class="muted">ID: <?= $dest['id'] ?></div>
              </td>
              <td>
                <div style="margin-bottom:8px"><strong>Total:</strong> <?= $counts['total'] ?> &nbsp; <strong>Available:</strong> <?= $counts['available'] ?></div>

                <details>
                  <summary>View / Manage Keycards</summary>

                  <table class="keycard-table" role="grid" style="margin-top:8px;">
                    <tr style="background:#f7f9fc;">
                      <th style="width:120px;padding:8px">Card #</th>
                      <th style="width:120px;padding:8px">Status</th>
                      <th style="width:260px;padding:8px">Edit</th>
                      <th style="padding:8px">Other actions</th>
                    </tr>

                    <?php
                      $stmt = $conn->prepare("SELECT id, card_number, is_assigned FROM keycards WHERE destination_id = ? ORDER BY card_number ASC LIMIT 200");
                      $stmt->bind_param("i", $dest['id']);
                      $stmt->execute();
                      $res = $stmt->get_result();
                      while ($r = $res->fetch_assoc()):
                    ?>
                      <tr>
                        <td style="padding:8px;"><?= str_pad($r['card_number'], 6, "0", STR_PAD_LEFT) ?></td>
                        <td style="padding:8px;"><?= $r['is_assigned'] ? '<span style="color:#b02a37;font-weight:600">Assigned</span>' : '<span style="color:#117a37;font-weight:600">Available</span>' ?></td>

                        <!-- inline edit form (card number & assigned toggle) -->
                        <td style="padding:8px">
                          <form method="POST" class="inline-form" style="display:flex; gap:6px; align-items:center;">
                            <input type="hidden" name="action" value="edit_card">
                            <input type="hidden" name="card_id" value="<?= $r['id'] ?>">
                            <input name="card_number" value="<?= htmlspecialchars(str_pad($r['card_number'],6,"0",STR_PAD_LEFT)) ?>" pattern="\d{1,6}" title="1 to 6 digits" style="width:90px;padding:6px;border:1px solid #ddd;border-radius:6px;">
                            <select name="is_assigned" style="padding:6px;border:1px solid #ddd;border-radius:6px;">
                              <option value="0" <?= $r['is_assigned'] ? '' : 'selected' ?>>Available</option>
                              <option value="1" <?= $r['is_assigned'] ? 'selected' : '' ?>>Assigned</option>
                            </select>
                            <button type="submit" class="small-btn">Save</button>
                          </form>
                        </td>

                        <td style="padding:8px">
                          <!-- Reassign -->
                          <form method="POST" style="display:inline-block; margin-right:6px;">
                            <input type="hidden" name="action" value="reassign_card">
                            <input type="hidden" name="card_id" value="<?= $r['id'] ?>">
                            <select name="new_dest_id" style="padding:6px;border:1px solid #ddd;border-radius:6px;">
                              <?php foreach ($destinations as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= $d['id']==$dest['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
                              <?php endforeach; ?>
                            </select>
                            <button type="submit" class="small-btn">Move</button>
                          </form>

                          <!-- Delete -->
                          <form method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this card?');">
                            <input type="hidden" name="action" value="delete_card">
                            <input type="hidden" name="card_id" value="<?= $r['id'] ?>">
                            <button type="submit" class="small-btn danger">Delete</button>
                          </form>
                        </td>
                      </tr>
                    <?php endwhile; $stmt->close(); ?>
                  </table>

                  <div class="muted" style="margin-top:8px">Showing up to 200 cards; use the pool / ranges to create more.</div>
                </details>
              </td>

              <td>
                <form method="POST" class="inline-form" style="margin-bottom:6px;">
                  <input type="hidden" name="action" value="edit_dest">
                  <input type="hidden" name="dest_id" value="<?= $dest['id'] ?>">
                  <input type="text" name="name" value="<?= htmlspecialchars($dest['name']) ?>" style="padding:6px;border:1px solid #ddd;border-radius:6px;">
                  <button type="submit" class="small-btn">Save</button>
                </form>

                <form method="POST" onsubmit="return confirm('Delete destination?');" style="margin-bottom:6px;">
                  <input type="hidden" name="action" value="delete_dest">
                  <input type="hidden" name="dest_id" value="<?= $dest['id'] ?>">
                  <button type="submit" class="small-btn danger">Delete</button>
                </form>

                <form method="POST" style="margin-bottom:6px;">
                  <input type="hidden" name="action" value="assign_range">
                  <input type="hidden" name="destination_id" value="<?= $dest['id'] ?>">
                  <div class="form-row">
                    <input name="start_number" placeholder="000100" pattern="\d{1,6}" required style="padding:6px;border:1px solid #ddd;border-radius:6px;" title="1 to 6 digits">
                    <input name="count" type="number" value="20" min="1" style="width:70px;padding:6px;border:1px solid #ddd;border-radius:6px;">
                    <button type="submit" class="small-btn success">Assign</button>
                  </div>
                </form>

                <form method="POST">
                  <input type="hidden" name="action" value="ensure_min">
                  <input type="hidden" name="destination_id" value="<?= $dest['id'] ?>">
                  <button type="submit" class="small-btn">Ensure 20</button>
                </form>

                <!-- Batch Delete Keycards Form -->
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete ALL keycards for this destination?');" style="margin-bottom:6px;">
                  <input type="hidden" name="action" value="batch_delete_keycards">
                  <input type="hidden" name="destination_id" value="<?= (int)$dest['id'] ?>">
                  <button type="submit" class="small-btn dangerr">Delete All Keycards</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div style="margin-top:12px;">
        <a href="admin_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
      </div>
    </div>
  </div>

  <script>
  setTimeout(function() {
    document.querySelectorAll('.messages .ok, .messages .err').forEach(function(el) {
      el.classList.add('fade-out');
      setTimeout(function() {
        el.style.display = 'none';
      }, 700); // Match the CSS transition duration
    });
  }, 4000); // 4 seconds before fade starts

  setTimeout(function() {
    document.querySelectorAll('.confirmation-popup').forEach(function(el) {
      el.classList.add('fade-out');
      setTimeout(function() {
        el.style.display = 'none';
      }, 700);
    });
  }, 4000);
  </script>
</body>
</html>
