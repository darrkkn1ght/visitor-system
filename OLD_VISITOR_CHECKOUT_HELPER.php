<!-- OLD VISITOR CHECKOUT HELPER -->
<!-- Add this to admin_dashboard.php to help identify and checkout old visitors -->

<?php

/**
 * Find visitors checked in for longer than X days
 * @param $conn - Database connection
 * @param $days - Number of days (default 30)
 * @return array - Array of old visitor records
 */
function getVisitorsCheckedInFor($conn, $days = 30) {
    $cutoff_date = date('Y-m-d H:i:s', strtotime("-$days days"));

    $sql = "SELECT
                v.id,
                v.fullname,
                v.phone_number,
                v.purpose,
                v.visitor_type,
                v.time_in,
                d.name as destination,
                v.keycard_id,
                TIMESTAMPDIFF(DAY, v.time_in, NOW()) as days_checked_in,
                TIMESTAMPDIFF(HOUR, v.time_in, NOW()) as hours_checked_in
            FROM visitors v
            LEFT JOIN destinations d ON v.destination = d.id
            WHERE v.time_out IS NULL
                  OR v.time_out = ''
                  OR v.time_out = '0000-00-00 00:00:00'
            AND v.time_in < ?
            ORDER BY v.time_in ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cutoff_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $old_visitors = [];
    while ($row = $result->fetch_assoc()) {
        $old_visitors[] = $row;
    }

    $stmt->close();
    return $old_visitors;
}

// Get counts of old visitors by time period
$old_7days = getVisitorsCheckedInFor($conn, 7);
$old_30days = getVisitorsCheckedInFor($conn, 30);
$old_90days = getVisitorsCheckedInFor($conn, 90);

?>

<!-- DISPLAY OLD VISITOR ALERTS -->
<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 10px 0; border-radius: 5px; display: none;" id="oldVisitorAlert">
    <h3 style="color: #856404; margin-top: 0;">⚠️ Old Visitor Records Found</h3>

    <p style="color: #856404;">
        These visitors have been checked in for longer than expected:
    </p>

    <ul style="color: #856404;">
        <li><strong><?= count($old_7days) ?></strong> visitor(s) checked in for 7+ days</li>
        <li><strong><?= count($old_30days) ?></strong> visitor(s) checked in for 30+ days (1+ month)</li>
        <li><strong><?= count($old_90days) ?></strong> visitor(s) checked in for 90+ days (3+ months)</li>
    </ul>

    <p style="color: #856404; font-weight: bold;">
        Action: Use filters below to view and checkout these visitors
    </p>
</div>

<!-- QUICK FILTER BUTTONS FOR OLD VISITORS -->
<div style="margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 5px;">
    <strong>Quick Filters - Find Old Visitors:</strong>

    <button onclick="filterOldVisitors(7)" style="margin: 5px; padding: 8px 12px; background: #ffc107; border: none; border-radius: 3px; cursor: pointer;">
        7+ Days Checked In
    </button>

    <button onclick="filterOldVisitors(30)" style="margin: 5px; padding: 8px 12px; background: #ff9800; border: none; border-radius: 3px; cursor: pointer;">
        30+ Days Checked In
    </button>

    <button onclick="filterOldVisitors(90)" style="margin: 5px; padding: 8px 12px; background: #f44336; border: none; border-radius: 3px; cursor: pointer;">
        90+ Days Checked In
    </button>

    <button onclick="filterOldVisitors(0)" style="margin: 5px; padding: 8px 12px; background: #2196f3; border: none; border-radius: 3px; cursor: pointer; color: white;">
        Clear Filter
    </button>
</div>

<script>
function filterOldVisitors(days) {
    if (days === 0) {
        // Clear filter
        window.location.href = window.location.pathname + '?filter=checked_in';
    } else {
        // Create a date for X days ago
        const date = new Date();
        date.setDate(date.getDate() - days);
        const dateStr = date.toISOString().split('T')[0];

        alert(`Showing visitors checked in before ${dateStr} (${days}+ days ago)`);

        // Reload with checked_in filter
        // Browser will show all checked-in visitors
        // You'll manually look for ones before the date above
        window.location.href = window.location.pathname + '?filter=checked_in';
    }
}
</script>

<?php
// Show alert if there are old visitors
if (count($old_30days) > 0) {
    echo "<script>document.getElementById('oldVisitorAlert').style.display = 'block';</script>";
}
?>
