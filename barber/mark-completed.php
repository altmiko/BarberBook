<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in as a barber
if (!isLoggedIn() || !isBarber()) {
    setFlashMessage('You must be logged in as a barber to access this page.', 'error');
    redirect('../login.php');
}

$barber_id = $_SESSION['user_id'];

// Check if appointment ID is provided
if (!isset($_GET['id'])) {
    setFlashMessage('No appointment selected.', 'error');
    redirect('dashboard.php');
}

$appointment_id = $_GET['id'];

// Verify the appointment belongs to this barber and get appointment details
$query = "SELECT a.*, c.FirstName, c.LastName, c.Email as CustomerEmail 
          FROM Appointments a 
          JOIN BarberHas bh ON a.AppointmentID = bh.AppointmentID 
          JOIN Customers c ON a.CustomerID = c.UserID 
          WHERE a.AppointmentID = ? AND bh.BarberID = ? AND a.Status = 'Scheduled'";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $appointment_id, $barber_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    setFlashMessage('Invalid appointment or you do not have permission to complete this appointment.', 'error');
    redirect('dashboard.php');
}

$appointment = $result->fetch_assoc();

// Update appointment status to Completed
$updateQuery = "UPDATE Appointments a
                JOIN Payments p ON p.PaymentID = a.PaymentID
                JOIN Customers c ON c.UserID = a.CustomerID
                JOIN BarberHas bh ON bh.AppointmentID = a.AppointmentID
                SET a.Status = 'Completed'
                WHERE a.AppointmentID = ? AND bh.BarberID = ? AND a.Status = 'Scheduled'";

$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("ii", $appointment_id, $barber_id);

$updatePayment = "UPDATE Payments p
                JOIN Appointments a ON a.PaymentID = p.PaymentID
                JOIN Customers c ON c.UserID = a.CustomerID
                JOIN BarberHas bh ON bh.AppointmentID = a.AppointmentID
                SET p.PayStatus = 'Completed'
                WHERE a.AppointmentID = ? AND bh.BarberID = ?";

$updatePaymentStmt = $conn->prepare($updatePayment);
$updatePaymentStmt->bind_param("ii", $appointment_id, $barber_id);

// Start transaction
$conn->begin_transaction();

try {
    // Execute both updates
    if (!$updateStmt->execute()) {
        throw new Exception("Failed to update appointment status: " . $updateStmt->error);
    }
    
    if (!$updatePaymentStmt->execute()) {
        throw new Exception("Failed to update payment status: " . $updatePaymentStmt->error);
    }
    
    // Create notification for customer
    $notificationQuery = "INSERT INTO Notifications (RecipientEmail, Status, Subject, Body, CustomerID, AppointmentID, SentAt) 
                        SELECT c.Email, 'Unread', 'Appointment Completed', 
                        CONCAT('Your appointment scheduled for ', DATE_FORMAT(a.StartTime, '%M %d, %Y at %h:%i %p'), ' has been marked as completed by ', b.FirstName, ' ', b.LastName),
                        c.UserID, a.AppointmentID, NOW()
                        FROM Appointments a
                        JOIN Customers c ON a.CustomerID = c.UserID
                        JOIN Barbers b ON b.UserID = ?
                        WHERE a.AppointmentID = ?";
    $notificationStmt = $conn->prepare($notificationQuery);
    if (!$notificationStmt) {
        throw new Exception("Failed to prepare notification query: " . $conn->error);
    }
    
    $notificationStmt->bind_param("ii", $barber_id, $appointment_id);
    if (!$notificationStmt->execute()) {
        throw new Exception("Failed to create completion notification: " . $notificationStmt->error);
    }
    
    // Commit transaction
    $conn->commit();
    setFlashMessage('Appointment marked as completed successfully.', 'success');
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    error_log("Error in mark-completed.php: " . $e->getMessage());
    setFlashMessage('Failed to mark appointment as completed: ' . $e->getMessage(), 'error');
}

redirect('dashboard.php');

// Define page title
$page_title = "Mark Appointment Completed";
include '../includes/header.php';
?>

<?php
include '../includes/footer.php';
?> 