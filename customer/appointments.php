<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in as a customer
if (!isLoggedIn() || !isCustomer()) {
    setFlashMessage('Please log in as a customer to view your appointments.', 'error');
    redirect('../login.php');
}

$customer_id = $_SESSION['user_id'];
$errors = [];
$success = false;

// Get customer information
$customerQuery = "SELECT FirstName, LastName FROM Customers WHERE UserID = ?";
$customerStmt = $conn->prepare($customerQuery);
$customerStmt->bind_param("i", $customer_id);
$customerStmt->execute();
$customerResult = $customerStmt->get_result();
$customer = $customerResult->fetch_assoc();

// Get all upcoming appointments
$upcomingQuery = "SELECT a.AppointmentID, a.StartTime, a.EndTime, a.Status, s.Name as ServiceName, 
                   b.FirstName as BarberFirstName, b.LastName as BarberLastName, p.Amount,
                   s.Duration, s.Price, p.PayMethod, p.PayStatus, p.TransactionID
                  FROM Appointments a 
                  JOIN Payments p ON a.PaymentID = p.PaymentID 
                  JOIN ApptContains ac ON a.AppointmentID = ac.AppointmentID 
                  JOIN Services s ON ac.ServiceID = s.ServiceID 
                  JOIN BarberHas bh ON a.AppointmentID = bh.AppointmentID 
                  JOIN Barbers b ON bh.BarberID = b.UserID 
                  WHERE a.CustomerID = ? AND DATE(a.StartTime) >= CURDATE() 
                  ORDER BY 
                    CASE a.Status 
                        WHEN 'Scheduled' THEN 1 
                        WHEN 'Completed' THEN 2 
                        WHEN 'Cancelled' THEN 3 
                        ELSE 4 
                    END,
                    a.StartTime ASC";

$upcomingStmt = $conn->prepare($upcomingQuery);
$upcomingStmt->bind_param("i", $customer_id);
$upcomingStmt->execute();
$upcomingResult = $upcomingStmt->get_result();
$upcomingAppointments = $upcomingResult->fetch_all(MYSQLI_ASSOC);

// Get all past appointments
$pastQuery = "SELECT a.AppointmentID, a.StartTime, a.EndTime, a.Status, s.Name as ServiceName, 
              b.FirstName as BarberFirstName, b.LastName as BarberLastName, p.Amount,
              s.Duration, s.Price, r.ReviewID as has_review
              FROM Appointments a 
              JOIN Payments p ON a.PaymentID = p.PaymentID 
              JOIN ApptContains ac ON a.AppointmentID = ac.AppointmentID 
              JOIN Services s ON ac.ServiceID = s.ServiceID 
              JOIN BarberHas bh ON a.AppointmentID = bh.AppointmentID 
              JOIN Barbers b ON bh.BarberID = b.UserID 
              LEFT JOIN Reviews r ON r.BarberID = b.UserID AND r.CustomerID = a.CustomerID
              WHERE a.CustomerID = ? AND a.EndTime < NOW() 
              ORDER BY a.EndTime DESC";

$pastStmt = $conn->prepare($pastQuery);
$pastStmt->bind_param("i", $customer_id);
$pastStmt->execute();
$pastResult = $pastStmt->get_result();
$pastAppointments = $pastResult->fetch_all(MYSQLI_ASSOC);

// Handle appointment cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment'])) {
    $appointmentId = (int) $_POST['appointment_id'];
    
    // Debug information
    error_log("Attempting to cancel appointment ID: " . $appointmentId);
    error_log("Customer ID: " . $customer_id);
    
    // First check if the appointment exists and belongs to the customer
    $checkQuery = "SELECT Status FROM Appointments WHERE AppointmentID = ? AND CustomerID = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ii", $appointmentId, $customer_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $appointment = $checkResult->fetch_assoc();
        error_log("Current appointment status: " . $appointment['Status']);
        
        // Only allow cancellation if the appointment is not already cancelled or completed
        if ($appointment['Status'] !== 'Cancelled' && $appointment['Status'] !== 'Completed') {
            // Update appointment status
            $updateQuery = "UPDATE Appointments SET Status = 'Cancelled' WHERE AppointmentID = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $appointmentId);
            
            if ($updateStmt->execute()) {
                error_log("Successfully updated appointment status to Cancelled");
                // Create notification for the customer
                $notificationQuery = "INSERT INTO Notifications (RecipientEmail, Status, Subject, Body, CustomerID) 
                                    SELECT Email, 'pending', 'Appointment Cancelled', 
                                    CONCAT('Your appointment scheduled for ', DATE_FORMAT(StartTime, '%M %d, %Y at %h:%i %p'), ' has been cancelled.'), 
                                    CustomerID 
                                    FROM Appointments a 
                                    JOIN Customers c ON a.CustomerID = c.UserID 
                                    WHERE a.AppointmentID = ?";
                $notificationStmt = $conn->prepare($notificationQuery);
                $notificationStmt->bind_param("i", $appointmentId);
                $notificationStmt->execute();
                
                $success = true;
            } else {
                error_log("Failed to update appointment: " . $conn->error);
                $errors['system'] = "Failed to update appointment status: " . $conn->error;
            }
        } else {
            error_log("Cannot cancel - appointment is already " . $appointment['Status']);
            $errors['system'] = "Cannot cancel an appointment that is already " . strtolower($appointment['Status']);
        }
    } else {
        error_log("Invalid appointment ID or not owned by customer");
        $errors['system'] = "Invalid appointment ID";
    }
    
    if ($success) {
        setFlashMessage('Appointment cancelled successfully.', 'success');
    } else {
        setFlashMessage('Failed to cancel appointment. Please try again.', 'error');
    }
    
    redirect('appointments.php');
}

// Define page title
$page_title = "My Appointments";
include '../includes/header.php';
?>

<main>
    <section class="appointments-section">
        <div class="container">
            <h1 class="section-title">My Appointments</h1>
            
            <?php if (!empty($errors['system'])): ?>
                <div class="alert alert-error">
                    <?php echo $errors['system']; ?>
                </div>
            <?php endif; ?>
            
            <div class="appointments-container">
                <!-- Upcoming Appointments -->
                <div class="appointments-section">
                    <h2>Upcoming Appointments</h2>
                    
                    <?php if (empty($upcomingAppointments)): ?>
                        <div class="no-appointments">
                            <p>You have no upcoming appointments.</p>
                            <a href="../booking.php" class="btn btn-primary">Book Now</a>
                        </div>
                    <?php else: ?>
                        <div class="appointments-grid">
                            <?php foreach ($upcomingAppointments as $appointment): ?>
                                <div class="appointment-card">
                                    <div class="appointment-header">
                                        <h3><?php echo htmlspecialchars($appointment['ServiceName']); ?></h3>
                                        <span class="status <?php echo strtolower($appointment['Status']); ?>">
                                            <?php echo ucfirst($appointment['Status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="appointment-details">
                                        <div class="detail-item">
                                            <i class="fas fa-calendar"></i>
                                            <span><?php echo date('F j, Y', strtotime($appointment['StartTime'])); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo date('g:i A', strtotime($appointment['StartTime'])); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-user"></i>
                                            <span><?php echo htmlspecialchars($appointment['BarberFirstName'] . ' ' . $appointment['BarberLastName']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo $appointment['Duration']; ?> minutes</span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-tag"></i>
                                            <span>BDT <?php echo $appointment['Amount']; ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-credit-card"></i>
                                            <span>
                                                <?php echo htmlspecialchars($appointment['PayMethod']); ?>
                                                <?php if ($appointment['PayStatus'] === 'Pending'): ?>
                                                    <span class="payment-status pending">(Pending)</span>
                                                <?php elseif ($appointment['PayStatus'] === 'Completed'): ?>
                                                    <span class="payment-status paid">(Paid)</span>
                                                    <?php if ($appointment['TransactionID']): ?>
                                                        <span class="transaction-id">#<?php echo htmlspecialchars($appointment['TransactionID']); ?></span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <?php if ($appointment['Status'] === 'Scheduled'): ?>
                                        <div class="appointment-actions">
                                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" 
                                                  onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['AppointmentID']; ?>">
                                                <button type="submit" name="cancel_appointment" class="btn btn-danger">Cancel Appointment</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Past Appointments -->
                <div class="appointments-section">
                    <h2>Past Appointments</h2>
                    
                    <?php if (empty($pastAppointments)): ?>
                        <div class="no-appointments">
                            <p>You have no past appointments.</p>
                        </div>
                    <?php else: ?>
                        <div class="appointments-grid">
                            <?php foreach ($pastAppointments as $appointment): ?>
                                <div class="appointment-card">
                                    <div class="appointment-header">
                                        <h3><?php echo htmlspecialchars($appointment['ServiceName']); ?></h3>
                                        <span class="status <?php echo strtolower($appointment['Status']); ?>">
                                            <?php echo ucfirst($appointment['Status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="appointment-details">
                                        <div class="detail-item">
                                            <i class="fas fa-calendar"></i>
                                            <span><?php echo date('F j, Y', strtotime($appointment['StartTime'])); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo date('g:i A', strtotime($appointment['StartTime'])); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-user"></i>
                                            <span><?php echo htmlspecialchars($appointment['BarberFirstName'] . ' ' . $appointment['BarberLastName']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo $appointment['Duration']; ?> minutes</span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-tag"></i>
                                            <span>BDT <?php echo $appointment['Amount']; ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-credit-card"></i>
                                            <span>
                                                <?php echo htmlspecialchars($appointment['PayMethod']); ?>
                                                <?php if ($appointment['PayStatus'] === 'Pending'): ?>
                                                    <span class="payment-status pending">(Pending)</span>
                                                <?php elseif ($appointment['PayStatus'] === 'Completed'): ?>
                                                    <span class="payment-status paid">(Paid)</span>
                                                    <?php if ($appointment['TransactionID']): ?>
                                                        <span class="transaction-id">#<?php echo htmlspecialchars($appointment['TransactionID']); ?></span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <?php if ($appointment['Status'] === 'Completed' && !$appointment['has_review']): ?>
                                        <div class="appointment-actions">
                                            <a href="../review.php?appointment_id=<?php echo $appointment['AppointmentID']; ?>" class="btn btn-primary">Leave a Review</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>

<style>
.appointments-section {
    padding: var(--space-6) 0;
    margin-top: 80px;
}

.section-title {
    text-align: center;
    margin-bottom: var(--space-5);
}

.appointments-container {
    max-width: 1200px;
    margin: 0 auto;
}

.appointments-section {
    margin-bottom: var(--space-6);
}

.appointments-section h2 {
    margin-bottom: var(--space-4);
    padding-bottom: var(--space-2);
    border-bottom: 1px solid var(--color-border);
}

.appointments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--space-4);
}

.appointment-card {
    background-color: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    padding: var(--space-3);
}

.appointment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-3);
}

.appointment-header h3 {
    margin: 0;
    font-size: 1.8rem;
}

.status {
    padding: 4px 12px;
    border-radius: var(--radius-sm);
    font-size: 1.2rem;
    font-weight: 500;
    border: 1px solid;
}

.status.scheduled {
    background-color: var(--color-accent-light);
    color: var(--color-accent);
    border-color: var(--color-accent);
}

.status.completed {
    background-color: var(--color-success-light);
    color: var(--color-success);
    border-color: var(--color-success);
}

.status.cancelled {
    background-color:rgb(219, 11, 11);
    color: var(--color-danger);
    border-color: var(--color-danger);
    color: white;
}

.appointment-details {
    margin-bottom: var(--space-3);
}

.detail-item {
    display: flex;
    align-items: center;
    margin-bottom: var(--space-2);
    color: var(--color-text-light);
}

.detail-item i {
    width: 20px;
    margin-right: var(--space-2);
    color: var(--color-primary);
}

.appointment-actions {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-2);
}

.no-appointments {
    text-align: center;
    padding: var(--space-4);
    background-color: var(--color-bg-alt);
    border-radius: var(--radius-md);
}

.no-appointments p {
    margin-bottom: var(--space-3);
    color: var(--color-text-light);
}

@media (max-width: 768px) {
    .appointments-grid {
        grid-template-columns: 1fr;
    }
    
    .appointment-actions {
        flex-direction: column;
    }
    
    .appointment-actions button,
    .appointment-actions a {
        width: 100%;
    }
}
</style> 