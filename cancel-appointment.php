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
        $notificationQuery = "INSERT INTO Notifications (RecipientEmail, Status, Subject, Body, CustomerID, AppointmentID, SentAt) 
                            SELECT c.Email, 'Unread', 'Appointment Cancelled by Barber', 
                            CONCAT('Your appointment scheduled for ', DATE_FORMAT(a.StartTime, '%M %d, %Y at %h:%i %p'), 
                            ' has been cancelled by ', b.FirstName, ' ', b.LastName, '.'),
                            c.UserID, a.AppointmentID, NOW()
                            FROM Appointments a
                            JOIN Customers c ON a.CustomerID = c.UserID
                            JOIN BarberHas bh ON a.AppointmentID = bh.AppointmentID
                            JOIN Barbers b ON bh.BarberID = b.UserID
                            WHERE a.AppointmentID = ? AND bh.BarberID = ?";
        $notificationStmt = $conn->prepare($notificationQuery);
        if (!$notificationStmt) {
            throw new Exception("Failed to prepare notification query: " . $conn->error);
        }
        $notificationStmt->bind_param("ii", $appointment_id, $barber_id);
        if (!$notificationStmt->execute()) {
            throw new Exception("Failed to create notification: " . $notificationStmt->error);
        }
        
        // Send email to customer
        require_once '../sendmail.php';
        $emailQuery = "SELECT c.Email, c.FirstName, c.LastName, b.FirstName as BarberFirstName, b.LastName as BarberLastName, a.StartTime
                      FROM Appointments a
                      JOIN Customers c ON a.CustomerID = c.UserID
                      JOIN BarberHas bh ON a.AppointmentID = bh.AppointmentID
                      JOIN Barbers b ON bh.BarberID = b.UserID
                      WHERE a.AppointmentID = ? AND bh.BarberID = ?";
        $emailStmt = $conn->prepare($emailQuery);
        $emailStmt->bind_param("ii", $appointment_id, $barber_id);
        $emailStmt->execute();
        $emailResult = $emailStmt->get_result();
        $emailData = $emailResult->fetch_assoc();
        
        if ($emailData) {
            $emailSubject = "Appointment Cancelled by Barber - BarberBook";
            $emailBody = "
                <h2>Appointment Cancellation Notice</h2>
                <p>Dear {$emailData['FirstName']} {$emailData['LastName']},</p>
                <p>Your appointment scheduled for " . date('F j, Y g:i A', strtotime($emailData['StartTime'])) . 
                " has been cancelled by {$emailData['BarberFirstName']} {$emailData['BarberLastName']}.</p>
                <p>We apologize for any inconvenience this may have caused.</p>
                <br>
                <p>Best regards,<br>BarberBook Team</p>
            ";
            sendEmail($emailData['Email'], $emailSubject, $emailBody);
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