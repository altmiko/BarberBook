<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in as a customer
if (!isLoggedIn() || !isCustomer()) {
    setFlashMessage('You must be logged in as a customer to access this page.', 'error');
    redirect('../login.php');
}

// Check if appointment ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    setFlashMessage('Invalid appointment ID.', 'error');
    redirect('dashboard.php');
}

$appointment_id = (int)$_GET['id'];
$customer_id = $_SESSION['user_id'];

try {
    // Verify that the appointment belongs to the logged-in customer
    $verifyQuery = "SELECT a.AppointmentID, a.Status, a.StartTime 
                    FROM Appointments a 
                    WHERE a.AppointmentID = ? AND a.CustomerID = ?";
    $verifyStmt = $conn->prepare($verifyQuery);
    if (!$verifyStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $verifyStmt->bind_param("ii", $appointment_id, $customer_id);
    if (!$verifyStmt->execute()) {
        throw new Exception("Execute failed: " . $verifyStmt->error);
    }
    $appointment = $verifyStmt->get_result()->fetch_assoc();

    if (!$appointment) {
        setFlashMessage('Appointment not found or you do not have permission to cancel it.', 'error');
        redirect('dashboard.php');
    }

    // Check if appointment is already cancelled
    if ($appointment['Status'] === 'Cancelled') {
        setFlashMessage('This appointment is already cancelled.', 'error');
        redirect('dashboard.php');
    }

    // Start transaction
    if (!$conn->begin_transaction()) {
        throw new Exception("Could not start transaction: " . $conn->error);
    }

    // Update appointment status to cancelled
    $updateQuery = "UPDATE Appointments SET Status = 'Cancelled' WHERE AppointmentID = ?";
    $updateStmt = $conn->prepare($updateQuery);
    if (!$updateStmt) {
        throw new Exception("Prepare update failed: " . $conn->error);
    }
    $updateStmt->bind_param("i", $appointment_id);
    if (!$updateStmt->execute()) {
        throw new Exception("Execute update failed: " . $updateStmt->error);
    }

    // Create notification for the customer
    $customerNotificationQuery = "INSERT INTO Notifications (CustomerID, Subject, Body, Status, SentAt) 
                                 VALUES (?, ?, ?, 'pending', NOW())";
    $customerNotificationStmt = $conn->prepare($customerNotificationQuery);
    if (!$customerNotificationStmt) {
        throw new Exception("Prepare customer notification failed: " . $conn->error);
    }
    $subject = "Appointment Cancelled";
    $message = "You have cancelled your appointment scheduled for " . date('F j, Y g:i A', strtotime($appointment['StartTime']));
    $customerNotificationStmt->bind_param("iss", $customer_id, $subject, $message);
    if (!$customerNotificationStmt->execute()) {
        throw new Exception("Execute customer notification failed: " . $customerNotificationStmt->error);
    }

    // Commit transaction
    if (!$conn->commit()) {
        throw new Exception("Commit failed: " . $conn->error);
    }

    setFlashMessage('Appointment has been cancelled successfully.', 'success');
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log the error
    error_log("Appointment cancellation error: " . $e->getMessage());
    
    // Set a more detailed error message
    setFlashMessage('Error: ' . $e->getMessage(), 'error');
}

// Redirect back to dashboard
redirect('dashboard.php');
?> 
