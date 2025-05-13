<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if barber_id is provided
if (!isset($_GET['barber_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Barber ID is required']);
    exit;
}

$barber_id = (int)$_GET['barber_id'];

// Get reviews for the barber
$query = "SELECT r.Rating, r.Comments as comment, 
          CONCAT(c.FirstName, ' ', c.LastName) as customer_name
          FROM Reviews r 
          JOIN Customers c ON r.CustomerID = c.UserID 
          WHERE r.BarberID = ? 
          ORDER BY r.ReviewID DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $barber_id);
$stmt->execute();
$result = $stmt->get_result();

$reviews = [];
while ($row = $result->fetch_assoc()) {
    $reviews[] = [
        'rating' => (int)$row['Rating'],
        'comment' => htmlspecialchars($row['comment']),
        'customer_name' => htmlspecialchars($row['customer_name'])
    ];
}

// Return reviews as JSON
header('Content-Type: application/json');
echo json_encode($reviews); 