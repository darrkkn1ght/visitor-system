<?php
/**
 * Notifications Inbox
 * 
 * Full page notification manager with filtering and pagination
 */

require_once 'security_headers.php';
session_start();
require_once 'db.php';

// Authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Filter handling
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all'; // all, unread, read
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Build query
$where_clauses = ["n.user_id = ?"];
$params = [$_SESSION['user_id']];
$types = "i";

if ($filter === 'unread') {
    $where_clauses[] = "n.is_read = 0";
} elseif ($filter === 'read') {
    $where_clauses[] = "n.is_read = 1";
}

$where_sql = implode(" AND ", $where_clauses);

// Get total count for pagination
$count_sql = "SELECT COUNT(*) as total FROM notifications n WHERE $where_sql";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$total_rows = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$stmt->close();

// Get notifications
$sql = "
    SELECT 
        n.*,
        v.fullname as visitor_name,
        v.purpose
    FROM notifications n
    LEFT JOIN visitors v ON n.visitor_id = v.id
    WHERE $where_sql
    ORDER BY n.created_at DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
// Add limit/offset parameters
$params[] = $limit;
$params[] = $offset;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Visitor Management</title>
    <link rel="stylesheet" href="admin_profile.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Inbox specific overrides */
        .inbox-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 1px;
        }

        .filter-tab {
            padding: 10px 20px;
            cursor: pointer;
            text-decoration: none;
            color: #64748b;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }

        .filter-tab:hover {
            color: var(--ui-primary);
        }

        .filter-tab.active {
            color: var(--ui-primary);
            border-bottom-color: var(--ui-primary);
        }

        .inbox-list {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--ui-primary);
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .empty-state {
            padding: 40px;
            text-align: center;
            color: #94a3b8;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }

        .page-link {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: white;
            color: var(--ui-dark);
            text-decoration: none;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        .page-link.active {
            background: var(--ui-primary);
            color: white;
            border-color: var(--ui-primary);
        }

        .delete-btn {
            color: #ef4444;
            background: none;
            border: none;
            cursor: pointer;
            padding: 5px;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .notification-item:hover .delete-btn {
            opacity: 1;
        }
    </style>
</head>

<body>
    <!-- Top Nav (Same as Profile) -->
    <div class="top-nav">
        <div class="nav-left">
            <h1>Notifications</h1>
        </div>
        <div class="nav-right">
            <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
            <a href="admin_profile.php" class="nav-link">My Profile</a>
            <a href="logout.php" class="nav-link">Logout</a>
        </div>
    </div>

    <div class="inbox-container">
        <a href="admin_profile.php" class="back-link">← Back to Profile</a>

        <div class="filter-tabs">
            <a href="?filter=all" class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>">All</a>
            <a href="?filter=unread" class="filter-tab <?= $filter === 'unread' ? 'active' : '' ?>">Unread</a>
            <a href="?filter=read" class="filter-tab <?= $filter === 'read' ? 'active' : '' ?>">Read</a>
        </div>

        <div class="inbox-list" id="notificationsList">
            <?php if (empty($notifications)): ?>
                <div class="empty-state">
                    <div style="font-size: 40px; margin-bottom: 10px;">📭</div>
                    <p>No notifications found</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                    <div class="notification-item <?= $notif['is_read'] ? 'read' : 'unread' ?>"
                        data-notification-id="<?= $notif['id'] ?>" onclick="toggleNotification(this)">

                        <div class="notification-icon">
                            <?php if ($notif['notification_type'] === 'visitor_arrival'): ?>
                                🔔
                            <?php elseif ($notif['notification_type'] === 'checkout'): ?>
                                👋
                            <?php elseif ($notif['notification_type'] === 'message'): ?>
                                ✉️
                            <?php else: ?>
                                ℹ️
                            <?php endif; ?>
                        </div>

                        <div class="notification-content">
                            <div class="notification-header">
                                <span class="notification-date">
                                    <?= date('M j, g:i A', strtotime($notif['created_at'])) ?>
                                </span>
                            </div>

                            <div class="notification-preview">
                                <?= htmlspecialchars(strlen($notif['message']) > 100 ? substr($notif['message'], 0, 97) . '...' : $notif['message']) ?>
                            </div>

                            <div class="notification-full">
                                <?= nl2br(htmlspecialchars($notif['message'])) ?>
                                <?php if ($notif['visitor_name']): ?>
                                    <div class="visitor-details-mini"
                                        style="margin-top: 10px; font-size: 0.9em; color: #64748b; background: #f8fafc; padding: 10px; border-radius: 6px;">
                                        <strong>Visitor:</strong>
                                        <?= htmlspecialchars($notif['visitor_name']) ?><br>
                                        <strong>Purpose:</strong>
                                        <?= htmlspecialchars($notif['purpose']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?filter=<?= $filter ?>&page=<?= $i ?>" class="page-link <?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pass data to JS -->
    <script>
        window.PROFILE_DATA = {
            csrfToken: '<?= $_SESSION['csrf_token'] ?>'
        };
    </script>
    <script src="admin_profile.js?v=<?= time() ?>"></script>
</body>

</html>