<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "barberbook";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set charset to ensure proper encoding
$conn->set_charset("utf8mb4");
?>