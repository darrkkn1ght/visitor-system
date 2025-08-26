<?php
/********************************************/
/* 2. db.php - Database connection file     */
/********************************************/
$conn = new mysqli("localhost", "root", "", "visitor_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}