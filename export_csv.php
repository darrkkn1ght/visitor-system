<?php
include 'db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="todays_visitors.csv"');

$today = date('Y-m-d');
$sql = "SELECT name, person_to_see, purpose, time_in, time_out FROM visitors WHERE DATE(time_in) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();

$output = fopen("php://output", "w");
fputcsv($output, ['Name', 'Person to See', 'Purpose', 'Time In', 'Time Out']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
