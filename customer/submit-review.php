<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if user is logged in and is a customer
if (!isLoggedIn() || !isCustomer()) {
    echo json_encode(['success' => false, 'message' => 'Please log in as a customer to submit reviews.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$customerID = $_SESSION['user_id'];
$barberID = $_POST['barber'] ?? null;
$rating = $_POST['rating'] ?? null;
$comment = $_POST['comment'] ?? null;

// Validate inputs
if (!$barberID || !$rating || !$comment) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Validate barber exists
$barberCheck = $conn->prepare("SELECT UserID FROM Barbers WHERE UserID = ?");
$barberCheck->bind_param("i", $barberID);
$barberCheck->execute();
if ($barberCheck->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid barber selected.']);
    exit;
}

// Validate rating is between 1 and 5
if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating value.']);
    exit;
}

// Sanitize comment
$comment = htmlspecialchars(trim($comment));

try {
    // Insert the review
    $stmt = $conn->prepare("INSERT INTO Reviews (CustomerID, BarberID, Rating, Comments, ReviewDate) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiis", $customerID, $barberID, $rating, $comment);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Review submitted successfully!']);
    } else {
        throw new Exception("Error executing review insertion");
    }
} catch (Exception $e) {
    error_log("Review submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error submitting review. Please try again.']);
}
?> 