<?php
/**
 * Admin Profile Dashboard
 * 
 * Personal dashboard for admins to manage:
 * - Availability status
 * - Profile information
 * - Password changes
 * - Notifications
 * - Statistics
 */

require_once 'security_headers.php';
session_start();
require_once 'db.php';

// Authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Password change handling
$password_message = '';
$password_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    // CSRF validation
    if (!hash_equals($_POST['csrf_token'] ?? '', $_SESSION['csrf_token'] ?? '')) {
        $password_error = 'CSRF token validation failed';
    } else {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!password_verify($current_password, $user['password'])) {
            $password_error = 'Current password is incorrect';
        } elseif ($new_password !== $confirm_password) {
            $password_error = 'New passwords do not match';
        } elseif (strlen($new_password) < 8) {
            $password_error = 'Password must be at least 8 characters';
        } else {
            // Update password
            $hashed = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $_SESSION['user_id']);

            if ($stmt->execute()) {
                $password_message = 'Password changed successfully!';

                // Audit log (optional)
                if (file_exists('audit_logging.php')) {
                    require_once 'audit_logging.php';
                    if (function_exists('log_action')) {
                        log_action(
                            $_SESSION['user_id'],
                            $_SESSION['username'],
                            $_SESSION['role'],
                            'PASSWORD_CHANGE',
                            'users',
                            $_SESSION['user_id'],
                            null,
                            null,
                            'User changed their password'
                        );
                    }
                }
            } else {
                $password_error = 'Failed to update password';
            }
            $stmt->close();
        }
    }
}

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
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

// Get today's statistics (for destination admins)
$stats = [
    'visitors_today' => 0,
    'total_visitors' => 0,
    'checked_in_now' => 0,
    'avg_duration' => 0
];

if ($profile['role'] === 'destination_admin' && $profile['destination_id']) {
    // Visitors today
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM visitors 
        WHERE destination = ? AND DATE(time_in) = CURDATE()
    ");
    $stmt->bind_param("i", $profile['destination_id']);
    $stmt->execute();
    $stats['visitors_today'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // Total visitors
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM visitors WHERE destination = ?");
    $stmt->bind_param("i", $profile['destination_id']);
    $stmt->execute();
    $stats['total_visitors'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // Currently checked in
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM visitors 
        WHERE destination = ? AND time_out IS NULL OR time_out = '0000-00-00 00:00:00'
    ");
    $stmt->bind_param("i", $profile['destination_id']);
    $stmt->execute();
    $stats['checked_in_now'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // Average duration
    $stmt = $conn->prepare("
        SELECT AVG(TIMESTAMPDIFF(MINUTE, time_in, time_out)) as avg_mins
        FROM visitors 
        WHERE destination = ? 
        AND time_out IS NOT NULL 
        AND time_out != '0000-00-00 00:00:00'
        AND DATE(time_in) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    $stmt->bind_param("i", $profile['destination_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stats['avg_duration'] = round($result['avg_mins'] ?? 0);
    $stmt->close();
}

// Get recent notifications
$stmt = $conn->prepare("
    SELECT 
        n.*,
        v.fullname as visitor_name,
        v.purpose
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

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile -
        <?= htmlspecialchars($profile['username']) ?>
    </title>
    <link rel="stylesheet" href="admin_profile.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <!-- Logo -->
    <div class="logo-container">
        <?php require_once 'includes/logo_helper.php'; ?>
        <a href="<?= getLogoHref() ?>" class="logo-link" aria-label="Home">
            <img src="ui_logo-removebg-preview.png" alt="UI Logo" class="logo">
        </a>
    </div>

    <!-- Top Navigation -->
    <div class="top-nav">
        <div class="nav-left">
            <h1>My Profile</h1>
        </div>
        <div class="nav-right">
            <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </div>
    </div>

    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <div class="avatar-circle">
                    <?= strtoupper(substr($profile['username'], 0, 2)) ?>
                </div>
            </div>
            <div class="profile-info">
                <h2>
                    <?= htmlspecialchars($profile['username']) ?>
                </h2>
                <div class="profile-role">
                    <span class="role-badge role-<?= htmlspecialchars($profile['role']) ?>">
                        <?= ucwords(str_replace('_', ' ', $profile['role'])) ?>
                    </span>
                </div>
                <?php if ($profile['destination_name']): ?>
                    <div class="profile-destination">
                        📍
                        <?= htmlspecialchars($profile['destination_name']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Availability Status Section -->
        <div class="card">
            <h3 class="card-title">Availability Status</h3>
            <div class="availability-section">
                <div class="current-status">
                    <span class="status-label">Current Status:</span>
                    <span class="status-badge status-<?= htmlspecialchars($profile['availability_status']) ?>"
                        id="currentStatusBadge">
                        <?= ucfirst($profile['availability_status']) ?>
                    </span>
                </div>

                <div class="status-buttons">
                    <button class="status-btn status-available" data-status="available">
                        <span class="status-icon">✓</span> Available
                    </button>
                    <button class="status-btn status-busy" data-status="busy">
                        <span class="status-icon">⏱</span> Busy
                    </button>
                    <button class="status-btn status-unavailable" data-status="unavailable">
                        <span class="status-icon">✕</span> Unavailable
                    </button>
                    <button class="status-btn status-away" data-status="away">
                        <span class="status-icon">☾</span> Away
                    </button>
                </div>

                <div class="status-message-section">
                    <label for="statusMessage">Status Message (optional):</label>
                    <input type="text" id="statusMessage" class="status-message-input"
                        placeholder="e.g., In a meeting until 3 PM"
                        value="<?= htmlspecialchars($profile['status_message'] ?? '') ?>" maxlength="255">
                    <div class="char-counter">
                        <span id="charCount">0</span>/255
                    </div>
                </div>

                <div id="statusUpdateMessage" class="status-update-message"></div>
            </div>
        </div>

        <!-- Recent Notifications -->
        <div class="card">
            <div class="card-header-flex">
                <h3 class="card-title">Recent Notifications</h3>
                <span class="notification-badge">
                    <?= $unread_count ?> unread
                </span>
            </div>

            <div class="notifications-list" id="notificationsList">
                <?php if (empty($recent_notifications)): ?>
                    <div class="no-notifications">
                        <p>No notifications yet</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_notifications as $notif): ?>
                        <div class="notification-item <?= $notif['is_read'] ? 'read' : 'unread' ?>"
                            data-notification-id="<?= $notif['id'] ?>" onclick="toggleNotification(this)">
                            <div class="notification-icon">
                                <?php if ($notif['notification_type'] === 'visitor_arrival'): ?>
                                    🔔
                                <?php elseif ($notif['notification_type'] === 'checkout'): ?>
                                    👋
                                <?php else: ?>
                                    ℹ️
                                <?php endif; ?>
                            </div>
                            <div class="notification-content">
                                <div class="notification-header">
                                    <span class="notification-time">
                                        <?= date('M j, g:i A', strtotime($notif['created_at'])) ?>
                                    </span>
                                </div>
                                <div class="notification-preview">
                                    <?= htmlspecialchars(strlen($notif['message']) > 50 ? substr($notif['message'], 0, 47) . '...' : $notif['message']) ?>
                                </div>
                                <div class="notification-full">
                                    <?= nl2br(htmlspecialchars($notif['message'])) ?>
                                    <?php if ($notif['visitor_name']): ?>
                                        <br><small><strong>Visitor:</strong> <?= htmlspecialchars($notif['visitor_name']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a href="notifications.php" class="view-all-link">View All Notifications</a>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card">
            <h3 class="card-title">Change Password</h3>

            <?php if ($password_message): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($password_message) ?>
                </div>
            <?php endif; ?>

            <?php if ($password_error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($password_error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="password-form">
                <input type="hidden" name="action" value="change_password">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required
                        autocomplete="current-password">
                </div>

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8"
                        autocomplete="new-password">
                    <div class="password-requirements">
                        <small>Must be at least 8 characters long</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8"
                        autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>

        <!-- Statistics (for destination admins) -->
        <?php if ($profile['role'] === 'destination_admin'): ?>
            <div class="card">
                <h3 class="card-title">Today's Statistics</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value">
                            <?= $stats['visitors_today'] ?>
                        </div>
                        <div class="stat-label">Visitors Today</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">
                            <?= $stats['total_visitors'] ?>
                        </div>
                        <div class="stat-label">Total Visitors</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">
                            <?= $stats['checked_in_now'] ?>
                        </div>
                        <div class="stat-label">Currently Checked In</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">
                            <?= $stats['avg_duration'] ?> min
                        </div>
                        <div class="stat-label">Avg. Visit Duration</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Pass data to JavaScript - MUST be before admin_profile.js loads
        window.PROFILE_DATA = {
            userId: <?= $_SESSION['user_id'] ?>,
            username: <?= json_encode($profile['username']) ?>,
            currentStatus: <?= json_encode($profile['availability_status']) ?>,
            csrfToken: '<?= $_SESSION['csrf_token'] ?>'
        };
    </script>
    <script src="admin_profile.js?v=<?= time() ?>"></script>
</body>

</html>