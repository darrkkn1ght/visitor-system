<?php
/**
 * audit_logs_viewer.php - Audit Log Viewer
 * 
 * Read-only audit log viewer for superadmin
 * Displays all admin actions, failed logins, and security events
 * Supports filtering, searching, and exporting
 */

require_once 'security_headers.php';
session_start();

// Only superadmin can access audit logs
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Check if user is superadmin
if ($_SESSION['role'] !== 'superadmin') {
    error_log("Unauthorized audit log access attempt by user: " . $_SESSION['username'] . " from IP: " . $_SERVER['REMOTE_ADDR']);
    die("Access denied. Audit logs are only available to superadmin.");
}

include 'db.php';
require_once 'audit_logging.php';

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Log the audit log access
log_audit_event('AUDIT_LOG_VIEW', null, null, [], ['viewer' => $_SESSION['username']]);

// ============================================================
// APPLY FILTERS
// ============================================================

$filters = [];
$action_filter = $_GET['action'] ?? '';
$table_filter = $_GET['table'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$records_per_page = 50;
$offset = ($page - 1) * $records_per_page;

if (!empty($action_filter)) {
    $filters['action'] = $action_filter;
}
if (!empty($table_filter)) {
    $filters['table_name'] = $table_filter;
}
if (!empty($status_filter)) {
    $filters['status'] = $status_filter;
}
if (!empty($date_from)) {
    $filters['date_from'] = $date_from;
}
if (!empty($date_to)) {
    $filters['date_to'] = $date_to;
}

// Get audit logs
$logs = get_audit_logs($filters, $records_per_page, $offset);
$total_count = count_audit_logs($filters);
$total_pages = ceil($total_count / $records_per_page);

// Get distinct action types for filter dropdown
$stmt = $conn->prepare("SELECT DISTINCT action FROM audit_log ORDER BY action");
$stmt->execute();
$actions_result = $stmt->get_result();
$available_actions = [];
while ($row = $actions_result->fetch_assoc()) {
    $available_actions[] = $row['action'];
}
$stmt->close();

// Get distinct tables for filter dropdown
$stmt = $conn->prepare("SELECT DISTINCT table_name FROM audit_log WHERE table_name IS NOT NULL ORDER BY table_name");
$stmt->execute();
$tables_result = $stmt->get_result();
$available_tables = [];
while ($row = $tables_result->fetch_assoc()) {
    $available_tables[] = $row['table_name'];
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs - Admin Only</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f5f5f5;
            font-family: 'Poppins', sans-serif;
        }

        .audit-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header h1 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 28px;
        }

        .subtitle {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .filter-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
            color: #333;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            font-family: inherit;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-label {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }

        .logs-table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f9f9f9;
            border-bottom: 2px solid #e0e0e0;
        }

        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            font-size: 13px;
            text-transform: uppercase;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
        }

        tbody tr:hover {
            background: #f9f9f9;
        }

        .action-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .action-create { background: #c8e6c9; color: #2e7d32; }
        .action-update { background: #bbdefb; color: #1565c0; }
        .action-delete { background: #ffcdd2; color: #c62828; }
        .action-login { background: #f3e5f5; color: #6a1b9a; }
        .action-login_failed { background: #ffcdd2; color: #c62828; }
        .action-export { background: #fff9c4; color: #f57f17; }
        .action-role_change { background: #ffe0b2; color: #e65100; }

        .status-success { color: #2e7d32; font-weight: 600; }
        .status-failure { color: #c62828; font-weight: 600; }

        .timestamp {
            color: #999;
            font-size: 12px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #667eea;
            font-size: 13px;
        }

        .pagination a:hover {
            background: #f0f0f0;
        }

        .pagination .current {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .details-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .details-modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            padding: 30px;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
            width: 95%;
        }

        .modal-close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #999;
        }

        .modal-close:hover {
            color: #333;
        }

        .json-display {
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .access-warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="audit-container">
        <div class="header">
            <h1>🔐 Audit Logs</h1>
            <p class="subtitle">Immutable record of all admin actions and security events (Superadmin Only)</p>
            <div class="access-warning">
                ⚠️ This page logs all access. Your viewing of audit logs is recorded and monitored.
            </div>
        </div>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-label">Total Events</div>
                <div class="stat-value"><?= number_format($total_count) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Page</div>
                <div class="stat-value"><?= $page ?> / <?= max(1, $total_pages) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Showing</div>
                <div class="stat-value"><?= count($logs) ?>/<?= $records_per_page ?></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <h3 style="margin-top: 0;">Filter Audit Logs</h3>
            <form method="GET" action="">
                <div class="filter-group">
                    <div>
                        <label for="action">Action Type</label>
                        <select name="action" id="action">
                            <option value="">All Actions</option>
                            <?php foreach ($available_actions as $act): ?>
                                <option value="<?= htmlspecialchars($act) ?>" <?= $action_filter === $act ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($act) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="table">Table</label>
                        <select name="table" id="table">
                            <option value="">All Tables</option>
                            <?php foreach ($available_tables as $tbl): ?>
                                <option value="<?= htmlspecialchars($tbl) ?>" <?= $table_filter === $tbl ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tbl) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="">All Statuses</option>
                            <option value="SUCCESS" <?= $status_filter === 'SUCCESS' ? 'selected' : '' ?>>Success</option>
                            <option value="FAILURE" <?= $status_filter === 'FAILURE' ? 'selected' : '' ?>>Failure</option>
                        </select>
                    </div>

                    <div>
                        <label for="date_from">From Date</label>
                        <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($date_from) ?>">
                    </div>

                    <div>
                        <label for="date_to">To Date</label>
                        <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($date_to) ?>">
                    </div>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">🔍 Apply Filters</button>
                    <a href="audit_logs_viewer.php" class="btn btn-secondary" style="text-decoration: none;">↺ Clear Filters</a>
                </div>
            </form>
        </div>

        <!-- Audit Logs Table -->
        <div class="logs-table">
            <?php if (count($logs) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Table</th>
                            <th>Summary</th>
                            <th>IP Address</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="timestamp">
                                    <?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($log['username']) ?></strong><br>
                                    <span style="font-size: 11px; color: #999;"><?= htmlspecialchars($log['user_role']) ?></span>
                                </td>
                                <td>
                                    <span class="action-badge action-<?= strtolower(str_replace('_', '', $log['action'])) ?>">
                                        <?= htmlspecialchars($log['action']) ?>
                                    </span>
                                </td>
                                <td><?= $log['table_name'] ? htmlspecialchars($log['table_name']) : '-' ?></td>
                                <td><?= htmlspecialchars($log['change_summary']) ?></td>
                                <td><code style="font-size: 11px;"><?= htmlspecialchars($log['ip_address']) ?></code></td>
                                <td>
                                    <span class="status-<?= strtolower($log['status']) ?>">
                                        <?= htmlspecialchars($log['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-secondary" onclick="showDetails(<?= htmlspecialchars(json_encode($log)) ?>)">
                                        View
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-data">
                    <p>No audit log entries found matching your filters.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1&action=<?= urlencode($action_filter) ?>&table=<?= urlencode($table_filter) ?>&status=<?= urlencode($status_filter) ?>">« First</a>
                    <a href="?page=<?= $page - 1 ?>&action=<?= urlencode($action_filter) ?>&table=<?= urlencode($table_filter) ?>&status=<?= urlencode($status_filter) ?>">‹ Prev</a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?>&action=<?= urlencode($action_filter) ?>&table=<?= urlencode($table_filter) ?>&status=<?= urlencode($status_filter) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&action=<?= urlencode($action_filter) ?>&table=<?= urlencode($table_filter) ?>&status=<?= urlencode($status_filter) ?>">Next ›</a>
                    <a href="?page=<?= $total_pages ?>&action=<?= urlencode($action_filter) ?>&table=<?= urlencode($table_filter) ?>&status=<?= urlencode($status_filter) ?>">Last »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Details Modal -->
    <div id="detailsModal" class="details-modal" onclick="if(event.target === this) hideDetails()">
        <div class="modal-content">
            <span class="modal-close" onclick="hideDetails()">&times;</span>
            <h2 id="modalTitle">Audit Log Details</h2>
            <table style="width: 100%; margin-top: 20px;">
                <tr>
                    <td><strong>ID:</strong></td>
                    <td id="detailId"></td>
                </tr>
                <tr>
                    <td><strong>User:</strong></td>
                    <td id="detailUser"></td>
                </tr>
                <tr>
                    <td><strong>Action:</strong></td>
                    <td id="detailAction"></td>
                </tr>
                <tr>
                    <td><strong>Table:</strong></td>
                    <td id="detailTable"></td>
                </tr>
                <tr>
                    <td><strong>Record ID:</strong></td>
                    <td id="detailRecordId"></td>
                </tr>
                <tr>
                    <td><strong>IP Address:</strong></td>
                    <td id="detailIp"></td>
                </tr>
                <tr>
                    <td><strong>Timestamp:</strong></td>
                    <td id="detailTimestamp"></td>
                </tr>
                <tr>
                    <td><strong>Status:</strong></td>
                    <td id="detailStatus"></td>
                </tr>
                <tr>
                    <td><strong>Error Message:</strong></td>
                    <td id="detailError"></td>
                </tr>
            </table>
            <div style="margin-top: 20px;">
                <h3>Old Values</h3>
                <div id="detailOldValues" class="json-display">-</div>
            </div>
            <div style="margin-top: 20px;">
                <h3>New Values</h3>
                <div id="detailNewValues" class="json-display">-</div>
            </div>
        </div>
    </div>

    <script src="audit_logs_viewer.js" defer></script>
</body>
</html>
