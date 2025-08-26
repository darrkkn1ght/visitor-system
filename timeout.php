<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $time_out = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("UPDATE visitors SET time_out = ? WHERE id = ?");
    $stmt->bind_param("si", $time_out, $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo "success";
} else {
    echo "error";
}
