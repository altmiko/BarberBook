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
$errors = [];
$success = false;

// Get barber information
$barberQuery = "SELECT FirstName, LastName FROM Barbers WHERE UserID = ?";
$barberStmt = $conn->prepare($barberQuery);
$barberStmt->bind_param("i", $barber_id);
$barberStmt->execute();
$barberResult = $barberStmt->get_result();
$barber = $barberResult->fetch_assoc();

// Get all upcoming appointments
$upcomingQuery = "SELECT a.AppointmentID, a.StartTime, a.EndTime, a.Status, s.Name as ServiceName, 
                   c.FirstName as CustomerFirstName, c.LastName as CustomerLastName, p.Amount,
                   s.Duration, s.Price, p.PayMethod, p.PayStatus, p.TransactionID
                     FROM Appointments a 
                     JOIN Payments p ON a.PaymentID = p.PaymentID 
                  JOIN ApptContains ac ON a.AppointmentID = ac.AppointmentID 
                  JOIN Services s ON ac.ServiceID = s.ServiceID 
                  JOIN BarberHas bh ON a.AppointmentID = bh.AppointmentID 
                     JOIN Customers c ON a.CustomerID = c.UserID 
                  WHERE bh.BarberID = ? AND DATE(a.StartTime) >= CURDATE() 
                  ORDER BY 
                    CASE a.Status 
                        WHEN 'Scheduled' THEN 1 
                        WHEN 'Completed' THEN 2 
                        WHEN 'Cancelled' THEN 3 
                        ELSE 4 
                    END,
                    a.StartTime ASC";

$upcomingStmt = $conn->prepare($upcomingQuery);
$upcomingStmt->bind_param("i", $barber_id);
$upcomingStmt->execute();
$upcomingResult = $upcomingStmt->get_result();
$upcomingAppointments = $upcomingResult->fetch_all(MYSQLI_ASSOC);

// Get all past appointments
$pastQuery = "SELECT a.AppointmentID, a.StartTime, a.EndTime, a.Status, s.Name as ServiceName, 
              c.FirstName as CustomerFirstName, c.LastName as CustomerLastName, p.Amount,
              s.Duration, s.Price, p.PayMethod, p.PayStatus, p.TransactionID
              FROM Appointments a 
              JOIN Payments p ON a.PaymentID = p.PaymentID 
                     JOIN ApptContains ac ON a.AppointmentID = ac.AppointmentID 
                     JOIN Services s ON ac.ServiceID = s.ServiceID 
                     JOIN BarberHas bh ON a.AppointmentID = bh.AppointmentID 
              JOIN Customers c ON a.CustomerID = c.UserID 
              WHERE bh.BarberID = ? AND a.EndTime < NOW() 
              ORDER BY a.EndTime DESC";

$pastStmt = $conn->prepare($pastQuery);
$pastStmt->bind_param("i", $barber_id);
$pastStmt->execute();
$pastResult = $pastStmt->get_result();
$pastAppointments = $pastResult->fetch_all(MYSQLI_ASSOC);

// Handle appointment cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment'])) {
    $appointmentId = (int) $_POST['appointment_id'];
    
    // First check if the appointment exists and belongs to the barber
    $checkQuery = "SELECT Status FROM Appointments a 
                   JOIN BarberHas bh ON a.AppointmentID = bh.AppointmentID 
                   WHERE a.AppointmentID = ? AND bh.BarberID = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ii", $appointmentId, $barber_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $appointment = $checkResult->fetch_assoc();
        
        // Only allow cancellation if the appointment is not already cancelled or completed
        if ($appointment['Status'] !== 'Cancelled' && $appointment['Status'] !== 'Completed') {
            // Update appointment status
            $updateQuery = "UPDATE Appointments SET Status = 'Cancelled' WHERE AppointmentID = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $appointmentId);
            
            if ($updateStmt->execute()) {
                // Create notification for the customer
                $notificationQuery = "INSERT INTO Notifications (RecipientEmail, Status, Subject, Body, CustomerID, AppointmentID) 
                                    SELECT c.Email, 'pending', 'Appointment Cancelled by Barber', 
                                    CONCAT('Your appointment scheduled for ', DATE_FORMAT(a.StartTime, '%M %d, %Y at %h:%i %p'), ' has been cancelled by the barber.'), 
                                    a.CustomerID, a.AppointmentID
                                    FROM Appointments a 
                                    JOIN Customers c ON a.CustomerID = c.UserID 
                                    WHERE a.AppointmentID = ?";
                $notificationStmt = $conn->prepare($notificationQuery);
                $notificationStmt->bind_param("i", $appointmentId);
                $notificationStmt->execute();
                
                $success = true;
            } else {
                $errors['system'] = "Failed to update appointment status: " . $conn->error;
            }
        } else {
            $errors['system'] = "Cannot cancel an appointment that is already " . strtolower($appointment['Status']);
        }
    } else {
        $errors['system'] = "Invalid appointment ID";
    }
    
    if ($success) {
        setFlashMessage('Appointment cancelled successfully.', 'success');
    } else {
        setFlashMessage('Failed to cancel appointment. Please try again.', 'error');
    }
    
    redirect('appointments.php');
}

// Handle appointment completion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_appointment'])) {
    $appointmentId = (int) $_POST['appointment_id'];
    
    // First check if the appointment exists and belongs to the barber
    $checkQuery = "SELECT Status FROM Appointments a 
                   JOIN BarberHas bh ON a.AppointmentID = bh.AppointmentID 
                   WHERE a.AppointmentID = ? AND bh.BarberID = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ii", $appointmentId, $barber_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $appointment = $checkResult->fetch_assoc();
        
        // Only allow completion if the appointment is scheduled
        if ($appointment['Status'] === 'Scheduled') {
            // Update appointment status
            $updateQuery = "UPDATE Appointments SET Status = 'Completed' WHERE AppointmentID = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $appointmentId);
            
            if ($updateStmt->execute()) {
                // Create notification for the customer
                $notificationQuery = "INSERT INTO Notifications (RecipientEmail, Status, Subject, Body, CustomerID, AppointmentID) 
                                    SELECT c.Email, 'pending', 'Appointment Completed', 
                                    CONCAT('Your appointment scheduled for ', DATE_FORMAT(a.StartTime, '%M %d, %Y at %h:%i %p'), ' has been marked as completed.'), 
                                    a.CustomerID, a.AppointmentID
                                    FROM Appointments a 
                                    JOIN Customers c ON a.CustomerID = c.UserID 
                                    WHERE a.AppointmentID = ?";
                $notificationStmt = $conn->prepare($notificationQuery);
                $notificationStmt->bind_param("i", $appointmentId);
                $notificationStmt->execute();
                
                $success = true;
            } else {
                $errors['system'] = "Failed to update appointment status: " . $conn->error;
            }
        } else {
            $errors['system'] = "Cannot complete an appointment that is not scheduled";
        }
    } else {
        $errors['system'] = "Invalid appointment ID";
    }
    
    if ($success) {
        setFlashMessage('Appointment marked as completed successfully.', 'success');
    } else {
        setFlashMessage('Failed to complete appointment. Please try again.', 'error');
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
                                            <span><?php echo htmlspecialchars($appointment['CustomerFirstName'] . ' ' . $appointment['CustomerLastName']); ?></span>
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
                                
                                                    <?php if ($appointment['TransactionID']): ?>
                                                        <span class="transaction-id">#<?php echo htmlspecialchars($appointment['TransactionID']); ?></span>
                                                    <?php endif; ?>
                                                
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <?php if ($appointment['Status'] === 'Scheduled'): ?>
                                        <div class="appointment-actions">
                                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" 
                                                  onsubmit="return confirm('Are you sure you want to complete this appointment?');">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['AppointmentID']; ?>">
                                                <button type="submit" name="complete_appointment" class="btn btn-success">Complete Appointment</button>
                                            </form>
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
                                            <span><?php echo htmlspecialchars($appointment['CustomerFirstName'] . ' ' . $appointment['CustomerLastName']); ?></span>
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
                                                
                                                    <?php if ($appointment['TransactionID']): ?>
                                                        <span class="transaction-id">#<?php echo htmlspecialchars($appointment['TransactionID']); ?></span>
                                                    <?php endif; ?>
                                    
                                            </span>
                                        </div>
                                    </div>
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
    background-color: rgb(219, 11, 11);
    color: white;
    border-color: var(--color-danger);
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
    margin-top: var(--space-3);
    padding-top: var(--space-3);
    border-top: 1px solid var(--color-border);
}

.appointment-actions form {
    flex: 1;
}

.appointment-actions button {
    width: 100%;
    padding: 0.8rem 1.2rem;
    border: none;
    border-radius: var(--radius-sm);
    font-size: 1.4rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
}

.appointment-actions button[name="complete_appointment"] {
    background-color: var(--color-success);
    color: white;
}

.appointment-actions button[name="complete_appointment"]:hover {
    background-color: rgb(0, 73, 16);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.appointment-actions button[name="cancel_appointment"] {
    background-color: rgb(177, 0, 0);
    color: white;
}

.appointment-actions button[name="cancel_appointment"]:hover {
    background-color: rgb(77, 0, 0);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.appointment-actions button:active {
    transform: translateY(0);
    box-shadow: none;
}

.appointment-actions button::before {
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
}

.appointment-actions button[name="complete_appointment"]::before {
    content: "\f00c";
}

.appointment-actions button[name="cancel_appointment"]::before {
    content: "\f00d";
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

.payment-status {
    font-size: 1.2rem;
    padding: 2px 8px;
    border-radius: var(--radius-sm);
}

.payment-status.pending {
    background-color: var(--color-warning-light);
    color: var(--color-warning);
}

.payment-status.paid {
    background-color: var(--color-success-light);
    color: var(--color-success);
}

.transaction-id {
    font-size: 1.2rem;
    color: var(--color-text-light);
    margin-left: var(--space-2);
}

@media (max-width: 768px) {
    .appointments-grid {
        grid-template-columns: 1fr;
    }

    .appointment-actions {
        flex-direction: column;
        gap: var(--space-2);
    }
    
    .appointment-actions button {
        padding: 1rem 1.2rem;
    }
}
</style> 