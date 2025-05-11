<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in as a barber
if (!isLoggedIn() || !isBarber()) {
    setFlashMessage('You must be logged in as a barber to access this page.', 'error');
    redirect('../login.php');
}

// Function to cancel appointment
function cancelAppointment($appointment_id, $barber_id, $conn) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First check if the appointment exists and belongs to this barber
        $checkQuery = "SELECT a.*, c.Email as CustomerEmail 
                      FROM Appointments a 
                      JOIN BarberHas bh ON a.AppointmentID = bh.AppointmentID 
                      JOIN Customers c ON a.CustomerID = c.UserID 
                      WHERE a.AppointmentID = ? AND bh.BarberID = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("ii", $appointment_id, $barber_id);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Appointment not found or you don't have permission to cancel it.");
        }
        
        $appointment = $result->fetch_assoc();
        
        // Check if appointment is already cancelled
        if ($appointment['Status'] === 'Cancelled') {
            throw new Exception("This appointment is already cancelled.");
        }
        
        // Update appointment status
        $updateQuery = "UPDATE Appointments SET Status = 'Cancelled' WHERE AppointmentID = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("i", $appointment_id);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Failed to update appointment status.");
        }
        
        // Update slot status to Available
        $slotQuery = "UPDATE Slots SET Status = 'Available' WHERE AppointmentID = ?";
        $slotStmt = $conn->prepare($slotQuery);
        $slotStmt->bind_param("i", $appointment_id);
        
        if (!$slotStmt->execute()) {
            throw new Exception("Failed to update slot status.");
        }
        
        // Get customer ID for notification
        $customerQuery = "SELECT CustomerID FROM Appointments WHERE AppointmentID = ?";
        $customerStmt = $conn->prepare($customerQuery);
        $customerStmt->bind_param("i", $appointment_id);
        $customerStmt->execute();
        $customerResult = $customerStmt->get_result();
        $customerData = $customerResult->fetch_assoc();
        
        // Create notification for customer
        $notificationQuery = "INSERT INTO Notifications (RecipientEmail, Status, Subject, Body, CustomerID) 
                            VALUES (?, 'Unread', 'Appointment Cancelled', ?, ?)";
        $notificationStmt = $conn->prepare($notificationQuery);
        $message = "Your appointment has been cancelled by the barber.";
        $notificationStmt->bind_param("ssi", $appointment['CustomerEmail'], $message, $customerData['CustomerID']);
        
        if (!$notificationStmt->execute()) {
            throw new Exception("Failed to create notification.");
        }
        
        // Commit transaction
        $conn->commit();
        
        return [
            'success' => true,
            'message' => 'Appointment cancelled successfully.'
        ];
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $barber_id = $_SESSION['user_id'];
    $result = cancelAppointment($appointment_id, $barber_id, $conn);
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Handle GET request (for direct URL access)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $appointment_id = intval($_GET['id']);
    $barber_id = $_SESSION['user_id'];
    $result = cancelAppointment($appointment_id, $barber_id, $conn);
    
    if ($result['success']) {
        setFlashMessage($result['message'], 'success');
    } else {
        setFlashMessage($result['message'], 'error');
    }
    
    redirect('dashboard.php');
}

// If no valid request, redirect to dashboard
redirect('dashboard.php');
?> 