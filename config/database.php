<?php
// Database connection parameters
$servername = "barberbookdb-barberbook.j.aivencloud.com";
$username = "avnadmin";
$password = "AVNS_EHS3bUWR3_7dcdFu9Ow";
$dbname = "barberbookdb";
$port = "14282";

// Create connection
$conn = new mysqli($servername, $username, $password, $port, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set charset to ensure proper encoding
$conn->set_charset("utf8mb4");
?>
