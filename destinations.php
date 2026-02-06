<?php
require_once 'security_headers.php';
session_start();
include 'db.php';

// Message arrays
$messages = ['success' => [], 'error' => []];
function add_success($msg)
{
  global $messages;
  $messages['success'][] = $msg;
}
function add_error($msg)
{
  global $messages;
  $messages['error'][] = $msg;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  // Create destination
  if ($action === 'create_dest') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '')
      add_error("Destination name cannot be empty.");
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
              // Log detailed error server-side only
              error_log("Database error creating destination admin user: " . $user_stmt->error . " for username: $username");
              // Show generic error to user
              add_error("An error occurred while creating the destination admin account. Please try again.");
            }
            $user_stmt->close();
          }
        } else {
          // Log detailed error server-side only
          error_log("Database error creating destination: " . $stmt->error . " for name: $name");
          // Show generic error to user
          add_error("An error occurred while creating the destination. Please try again.");
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

  // Edit destination name
  if ($action === 'edit_dest') {
    $dest_id = intval($_POST['dest_id'] ?? 0);
    $new_name = trim($_POST['name'] ?? '');

    if ($dest_id <= 0) {
      add_error("Invalid destination ID.");
    } elseif ($new_name === '') {
      add_error("Destination name cannot be empty.");
    } else {
      $stmt = $conn->prepare("UPDATE destinations SET name = ? WHERE id = ?");
      $stmt->bind_param("si", $new_name, $dest_id);
      if ($stmt->execute()) {
        add_success("Destination name updated successfully.");
      } else {
        add_error("Error updating destination name.");
      }
      $stmt->close();
    }
    $_SESSION['messages'] = $messages;
    header("Location: destinations.php");
    exit();
  }

  // Edit keycard
  if ($action === 'edit_card') {
    $card_id = intval($_POST['card_id'] ?? 0);
    $card_number = trim($_POST['card_number'] ?? '');
    $is_assigned = intval($_POST['is_assigned'] ?? 0);

    if ($card_id <= 0) {
      add_error("Invalid card ID.");
    } elseif ($card_number === '' || !preg_match('/^\d{1,6}$/', $card_number)) {
      add_error("Invalid card number format. Use 1 to 6 digits.");
    } else {
      $stmt = $conn->prepare("UPDATE keycards SET card_number = ?, is_assigned = ? WHERE id = ?");
      $stmt->bind_param("iii", (int) $card_number, $is_assigned, $card_id);
      if ($stmt->execute()) {
        add_success("Card updated successfully.");
      } else {
        add_error("Error updating card.");
      }
      $stmt->close();
    }
    $_SESSION['messages'] = $messages;
    header("Location: destinations.php");
    exit();
  }

  // Reassign keycard to another destination
  if ($action === 'reassign_card') {
    $card_id = intval($_POST['card_id'] ?? 0);
    $new_dest_id = intval($_POST['new_dest_id'] ?? 0);

    if ($card_id <= 0 || $new_dest_id <= 0) {
      add_error("Invalid card or destination ID.");
    } else {
      $stmt = $conn->prepare("UPDATE keycards SET destination_id = ? WHERE id = ?");
      $stmt->bind_param("ii", $new_dest_id, $card_id);
      if ($stmt->execute()) {
        add_success("Card reassigned successfully.");
      } else {
        add_error("Error reassigning card.");
      }
      $stmt->close();
    }
    $_SESSION['messages'] = $messages;
    header("Location: destinations.php");
    exit();
  }

  // Delete keycard
  if ($action === 'delete_card') {
    $card_id = intval($_POST['card_id'] ?? 0);

    if ($card_id <= 0) {
      add_error("Invalid card ID.");
    } else {
      // Check if card is assigned
      $stmt = $conn->prepare("SELECT is_assigned FROM keycards WHERE id = ?");
      $stmt->bind_param("i", $card_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $card = $result->fetch_assoc();
      $stmt->close();

      if (!$card) {
        add_error("Card not found.");
      } elseif ($card['is_assigned']) {
        add_error("Cannot delete an assigned keycard.");
      } else {
        $stmt = $conn->prepare("DELETE FROM keycards WHERE id = ?");
        $stmt->bind_param("i", $card_id);
        if ($stmt->execute()) {
          add_success("Card deleted successfully.");
        } else {
          add_error("Error deleting card.");
        }
        $stmt->close();
      }
    }
    $_SESSION['messages'] = $messages;
    header("Location: destinations.php");
    exit();
  }

  // Assign keycard range
  if ($action === 'assign_range') {
    $destination_id = intval($_POST['destination_id'] ?? 0);
    $start_number = intval($_POST['start_number'] ?? 0);
    $count = intval($_POST['count'] ?? 0);

    if ($destination_id <= 0) {
      add_error("Invalid destination ID.");
    } elseif ($start_number < 1 || $start_number > 999999) {
      add_error("Invalid start number. Use 1 to 999999.");
    } elseif ($count < 1 || $count > 500) {
      add_error("Invalid count. Use 1 to 500.");
    } else {
      $success_count = 0;
      for ($i = 0; $i < $count; $i++) {
        $card_num = $start_number + $i;
        $stmt = $conn->prepare("INSERT INTO keycards (destination_id, card_number, is_assigned) VALUES (?, ?, 0) ON DUPLICATE KEY UPDATE destination_id = ?");
        $stmt->bind_param("iii", $destination_id, $card_num, $destination_id);
        if ($stmt->execute()) {
          $success_count++;
        }
        $stmt->close();
      }
      add_success("Created $success_count keycards for the range.");
    }
    $_SESSION['messages'] = $messages;
    header("Location: destinations.php");
    exit();
  }

  // Ensure minimum keycards
  if ($action === 'ensure_min') {
    $destination_id = intval($_POST['destination_id'] ?? 0);
    $min_count = 20;

    if ($destination_id <= 0) {
      add_error("Invalid destination ID.");
    } else {
      $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM keycards WHERE destination_id = ?");
      $stmt->bind_param("i", $destination_id);
      $stmt->execute();
      $result = $stmt->get_result();
      $current_count = $result->fetch_assoc()['total'];
      $stmt->close();

      if ($current_count >= $min_count) {
        add_success("Destination already has $current_count keycards (>= $min_count).");
      } else {
        $to_create = $min_count - $current_count;
        // Get highest card number
        $stmt = $conn->prepare("SELECT MAX(card_number) AS max_num FROM keycards WHERE destination_id = ?");
        $stmt->bind_param("i", $destination_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $max_num = (int) ($result->fetch_assoc()['max_num'] ?? 0);
        $stmt->close();

        $start_num = $max_num + 1;
        $success_count = 0;

        for ($i = 0; $i < $to_create; $i++) {
          $card_num = $start_num + $i;
          $stmt = $conn->prepare("INSERT INTO keycards (destination_id, card_number, is_assigned) VALUES (?, ?, 0)");
          $stmt->bind_param("iii", $destination_id, $card_num);
          if ($stmt->execute()) {
            $success_count++;
          }
          $stmt->close();
        }
        add_success("Created $success_count additional keycards. Destination now has " . ($current_count + $success_count) . " cards.");
      }
    }
    $_SESSION['messages'] = $messages;
    header("Location: destinations.php");
    exit();
  }

  // Delete destination
  if ($action === 'delete_dest') {
    $id = intval($_POST['dest_id'] ?? 0);
    if ($id <= 0)
      add_error("Invalid destination id.");
    else {
      // Check for linked visitors/keycards
      $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM visitors WHERE destination = ?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $r = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM keycards WHERE destination_id = ?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $r2 = $stmt->get_result()->fetch_assoc();
      $stmt->close();

      if ($r['c'] > 0 || $r2['c'] > 0)
        add_error("Cannot delete destination while there are visitors or keycards linked to it. Remove or reassign them first.");
      else {
        // Delete users linked to this destination
        $stmt = $conn->prepare("DELETE FROM users WHERE destination_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Delete the destination itself
        $stmt = $conn->prepare("DELETE FROM destinations WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
          add_success("Destination and its admin users deleted.");
        } else {
          // Log detailed error server-side only
          error_log("Database error deleting destination: " . $stmt->error . " for id: $id");
          // Show generic error to user
          add_error("An error occurred while deleting the destination. Please try again.");
        }
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
if ($res)
  while ($r = $res->fetch_assoc())
    $destinations[] = $r;

// helper to get keycard counts per destination
function get_keycard_counts($conn, $dest_id)
{
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
  <link rel="stylesheet" href="central.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
      <?php foreach ($messages['success'] as $m): ?>
        <div class="ok"><?= $m ?></div><?php endforeach; ?>
      <?php foreach ($messages['error'] as $m): ?>
        <div class="err"><?= htmlspecialchars($m) ?></div><?php endforeach; ?>
    </div>

    <div class="card">
      <!-- Create new destination -->
      <form method="POST" class="inline-form">
        <input type="hidden" name="action" value="create_dest">
        <input name="name" placeholder="Create A New Destination" class="flex-input" required>
        <button type="submit" class="small-btn success">Create</button>
      </form>

      <table>
        <thead>
          <tr>
            <th class="col-id">#</th>
            <th class="col-dest">Destination</th>
            <th>Keycard Pool</th>
            <th class="col-actions">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($destinations) === 0): ?>
            <tr>
              <td colspan="4" class="muted no-records">No destinations yet.</td>
            </tr>
          <?php endif; ?>

          <?php foreach ($destinations as $i => $dest):
            $counts = get_keycard_counts($conn, $dest['id']);
            ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td>
                <strong><?= htmlspecialchars($dest['name']) ?></strong>
                <div class="muted">ID: <?= $dest['id'] ?></div>
              </td>
              <td class="overflow-visible-cell">
                <div class="counts-info"><strong>Total:</strong> <?= $counts['total'] ?> &nbsp;
                  <strong>Available:</strong> <?= $counts['available'] ?></div>

                <details>
                  <summary>View / Manage Keycards</summary>

                  <div class="popup-content">
                    <table class="keycard-table" role="grid">
                      <tr class="keycard-header">
                        <th class="card-col">Card #</th>
                        <th class="status-col">Status</th>
                        <th class="edit-col">Edit</th>
                        <th class="actions-col">Other actions</th>
                      </tr>

                      <?php
                      $stmt = $conn->prepare("SELECT id, card_number, is_assigned FROM keycards WHERE destination_id = ? ORDER BY card_number ASC LIMIT 200");
                      $stmt->bind_param("i", $dest['id']);
                      $stmt->execute();
                      $res = $stmt->get_result();
                      while ($r = $res->fetch_assoc()):
                        ?>
                        <tr>
                          <td class="card-data"><?= str_pad($r['card_number'], 6, "0", STR_PAD_LEFT) ?></td>
                          <td class="status-data">
                            <?= $r['is_assigned'] ? '<span class="assigned-status">Assigned</span>' : '<span class="available-status">Available</span>' ?>
                          </td>

                          <!-- inline edit form (card number & assigned toggle) -->
                          <td class="edit-cell">
                            <form method="POST" class="inline-form">
                              <input type="hidden" name="action" value="edit_card">
                              <input type="hidden" name="card_id" value="<?= $r['id'] ?>">
                              <input name="card_number"
                                value="<?= htmlspecialchars(str_pad($r['card_number'], 6, "0", STR_PAD_LEFT)) ?>"
                                pattern="\d{1,6}" title="1 to 6 digits" class="card-input">
                              <select name="is_assigned" class="status-select">
                                <option value="0" <?= $r['is_assigned'] ? '' : 'selected' ?>>Available</option>
                                <option value="1" <?= $r['is_assigned'] ? 'selected' : '' ?>>Assigned</option>
                              </select>
                              <button type="submit" class="small-btn">Save</button>
                            </form>
                          </td>

                          <td class="edit-cell">
                            <!-- Reassign -->
                            <form method="POST" class="reassign-form">
                              <input type="hidden" name="action" value="reassign_card">
                              <input type="hidden" name="card_id" value="<?= $r['id'] ?>">
                              <select name="new_dest_id" class="dest-select">
                                <?php foreach ($destinations as $d): ?>
                                  <option value="<?= $d['id'] ?>" <?= $d['id'] == $dest['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d['name']) ?></option>
                                <?php endforeach; ?>
                              </select>
                              <button type="submit" class="small-btn">Move</button>
                            </form>

                            <!-- Delete -->
                            <form method="POST" class="delete-form" onsubmit="return confirm('Delete this card?');">
                              <input type="hidden" name="action" value="delete_card">
                              <input type="hidden" name="card_id" value="<?= $r['id'] ?>">
                              <button type="submit" class="small-btn danger">Delete</button>
                            </form>
                          </td>
                        </tr>
                      <?php endwhile;
                      $stmt->close(); ?>
                    </table>

                    <div class="muted card-note">Showing up to 200 cards; use the pool / ranges to create more.</div>
                  </div>
                </details>
              </td>

              <td>
                <form method="POST" class="inline-form dest-form">
                  <input type="hidden" name="action" value="edit_dest">
                  <input type="hidden" name="dest_id" value="<?= $dest['id'] ?>">
                  <input type="text" name="name" value="<?= htmlspecialchars($dest['name']) ?>" class="dest-input">
                  <button type="submit" class="small-btn">Save</button>
                </form>

                <form method="POST" class="dest-form" onsubmit="return confirm('Delete destination?');">
                  <input type="hidden" name="action" value="delete_dest">
                  <input type="hidden" name="dest_id" value="<?= $dest['id'] ?>">
                  <button type="submit" class="small-btn danger">Delete</button>
                </form>

                <form method="POST" class="dest-form">
                  <input type="hidden" name="action" value="assign_range">
                  <input type="hidden" name="destination_id" value="<?= $dest['id'] ?>">
                  <div class="form-row">
                    <input name="start_number" placeholder="000100" pattern="\d{1,6}" required class="range-input"
                      title="1 to 6 digits">
                    <input name="count" type="number" value="20" min="1" class="count-input">
                    <button type="submit" class="small-btn success">Assign</button>
                  </div>
                </form>

                <form method="POST">
                  <input type="hidden" name="action" value="ensure_min">
                  <input type="hidden" name="destination_id" value="<?= $dest['id'] ?>">
                  <button type="submit" class="small-btn">Ensure 20</button>
                </form>

                <!-- Batch Delete Keycards Form -->
                <form method="POST" class="dest-form"
                  onsubmit="return confirm('Are you sure you want to delete ALL keycards for this destination?');">
                  <input type="hidden" name="action" value="batch_delete_keycards">
                  <input type="hidden" name="destination_id" value="<?= (int) $dest['id'] ?>">
                  <button type="submit" class="small-btn danger">Delete All Keycards</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="back-section">
        <a href="admin_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
      </div>
    </div>
  </div>

  <script src="destinations.js" defer></script>
</body>

</html>