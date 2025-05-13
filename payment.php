<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Validate user is logged in as customer
if (!isLoggedIn() || !isCustomer()) {
    setFlashMessage('Please log in as a customer to make a payment.', 'error');
    redirect('login.php');
}

$customerID = $_SESSION['user_id'];
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $services = $_POST['services'] ?? [];
    $paymentMethod = $_POST['payment_method'] ?? '';
    $transactionId = $_POST['transaction_id'] ?? '';

    if (empty($services)) {
        $error = 'No services selected for payment.';
    } elseif (empty($paymentMethod)) {
        $error = 'Please select a payment method.';
    } elseif ($paymentMethod !== 'cash' && empty($transactionId)) {
        $error = 'Please enter a transaction ID.';
    } elseif ($paymentMethod !== 'cash' && strlen($transactionId) !== 11) {
        $error = 'Transaction ID must be exactly 11 characters.';
    } else {
        $conn->begin_transaction();
        try {
            // Group services by barber and start time
            $appointmentsToCreate = [];
            foreach ($services as $item) {
                $barberUserID = $item['barberId'] ?? $item['barberID'] ?? null;
                if (!$barberUserID || !isset($item['time']) || !isset($item['duration'])) {
                    throw new Exception('Missing required fields in service selection');
                }

                // Create payment record for this service
                $payMethod = ucfirst($paymentMethod);
                $payStatus = 'Pending';
                $stmt = $conn->prepare("INSERT INTO Payments (Amount, PayMethod, PayStatus, TransactionID) VALUES (?, ?, ?, ?)");
                if (!$stmt) {
                    throw new Exception('Database prepare error: ' . $conn->error);
                }

                $stmt->bind_param("dsss", $item['price'], $payMethod, $payStatus, $transactionId);
                if (!$stmt->execute()) {
                    throw new Exception('Database execute error: ' . $stmt->error);
                }

                $paymentID = $conn->insert_id;
                $stmt->close();

                if (!$paymentID) {
                    throw new Exception("Failed to create payment record for service.");
                }

                $key = $barberUserID . '_' . $item['time'];
                if (!isset($appointmentsToCreate[$key])) {
                    $appointmentsToCreate[$key] = [
                        'barberId' => $barberUserID,
                        'startTime' => $item['time'],
                        'services' => [],
                        'totalDuration' => 0,
                        'paymentID' => $paymentID
                    ];
                }
                $appointmentsToCreate[$key]['services'][] = $item;
                $appointmentsToCreate[$key]['totalDuration'] += intval($item['duration']);
            }

            $createdAppointmentIDs = [];

            foreach ($appointmentsToCreate as $apptData) {
                $startTimeStr = $apptData['startTime'];
                $startDateTime = new DateTime($startTimeStr);
                $endDateTime = clone $startDateTime;
                $endDateTime->add(new DateInterval('PT' . $apptData['totalDuration'] . 'M'));
                $endTimeStr = $endDateTime->format('Y-m-d H:i:s');
                $status = 'Scheduled';

                // Create Appointment Record
                $stmt = $conn->prepare("INSERT INTO Appointments (StartTime, EndTime, Status, PaymentID, CustomerID, BarberID) VALUES (?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    throw new Exception('Database prepare error: ' . $conn->error);
                }

                $barberID = $apptData['barberId'];
                $stmt->bind_param("sssiii", $startTimeStr, $endTimeStr, $status, $apptData['paymentID'], $customerID, $barberID);
                if (!$stmt->execute()) {
                    throw new Exception('Database execute error: ' . $stmt->error);
                }

                $appointmentID = $conn->insert_id;
                $stmt->close();

                if (!$appointmentID) {
                    throw new Exception("Failed to create appointment for barber " . $apptData['barberId'] . " at " . $apptData['startTime']);
                }
                $createdAppointmentIDs[] = $appointmentID;

                // Create notification for barber
                $notificationQuery = "INSERT INTO Notifications (RecipientEmail, SentAt, Status, Subject, Body, CustomerID, AppointmentID) 
                                    SELECT b.Email, NOW(), 'Unread', 'New Appointment', 
                                    CONCAT('You have a new appointment scheduled for ', DATE_FORMAT(?, '%M %d, %Y at %h:%i %p'), ' with ', b.FirstName, ' ', b.LastName),
                                    ?, ?
                                    FROM Customers c
                                    JOIN Barbers b ON b.UserID = ?
                                    WHERE c.UserID = ?";
                $notificationStmt = $conn->prepare($notificationQuery);
                if (!$notificationStmt) {
                    throw new Exception("Failed to prepare barber notification query: " . $conn->error);
                }
                $notificationStmt->bind_param("siiii", $startTimeStr, $customerID, $appointmentID, $barberID, $customerID);
                if (!$notificationStmt->execute()) {
                    throw new Exception("Failed to create notification for barber: " . $notificationStmt->error);
                }
                $notificationStmt->close();

                // Create notification for customer
                $customerNotificationQuery = "INSERT INTO Notifications (RecipientEmail, SentAt, Status, Subject, Body, CustomerID, AppointmentID) 
                                           SELECT c.Email, NOW(), 'Unread', 'Appointment Confirmed', 
                                           CONCAT('Your appointment has been scheduled for ', DATE_FORMAT(?, '%M %d, %Y at %h:%i %p'), ' with ', b.FirstName, ' ', b.LastName),
                                           ?, ?
                                           FROM Customers c
                                           JOIN Barbers b ON b.UserID = ?
                                           WHERE c.UserID = ?";
                $customerNotificationStmt = $conn->prepare($customerNotificationQuery);
                if (!$customerNotificationStmt) {
                    throw new Exception("Failed to prepare customer notification query: " . $conn->error);
                }
                $customerNotificationStmt->bind_param("siiii", $startTimeStr, $customerID, $appointmentID, $barberID, $customerID);
                if (!$customerNotificationStmt->execute()) {
                    throw new Exception("Failed to create notification for customer: " . $customerNotificationStmt->error);
                }
                $customerNotificationStmt->close();

                // Send email to customer
                require_once 'sendmail.php';
                $customerEmailQuery = "SELECT c.Email, c.FirstName, c.LastName, b.FirstName as BarberFirstName, b.LastName as BarberLastName 
                                     FROM Customers c 
                                     JOIN Barbers b ON b.UserID = ? 
                                     WHERE c.UserID = ?";
                $emailStmt = $conn->prepare($customerEmailQuery);
                $emailStmt->bind_param("ii", $barberID, $customerID);
                $emailStmt->execute();
                $emailResult = $emailStmt->get_result();
                $emailData = $emailResult->fetch_assoc();
                
                if ($emailData) {
                    $emailSubject = "Appointment Confirmed - BarberBook";
                    $emailBody = "
                        <h2>Your appointment has been confirmed!</h2>
                        <p>Dear {$emailData['FirstName']} {$emailData['LastName']},</p>
                        <p>Your appointment has been scheduled for " . date('F j, Y g:i A', strtotime($startTimeStr)) . " with {$emailData['BarberFirstName']} {$emailData['BarberLastName']}.</p>
                        <p>Thank you for choosing BarberBook!</p>
                        <br>
                        <p>Best regards,<br>BarberBook Team</p>
                    ";
                    sendEmail($emailData['Email'], $emailSubject, $emailBody);
                }

                // Create Slot Record
                $slotStatus = 'Booked';
                $stmt = $conn->prepare("INSERT INTO Slots (Status, Time, BarberID, AppointmentID) VALUES (?, ?, ?, ?)");
                if (!$stmt) {
                    throw new Exception('Database prepare error: ' . $conn->error);
                }

                $stmt->bind_param("ssii", $slotStatus, $startTimeStr, $barberID, $appointmentID);
                if (!$stmt->execute()) {
                    throw new Exception('Database execute error: ' . $stmt->error);
                }
                $stmt->close();
                
                // Create BarberHas Record
                $stmt = $conn->prepare("INSERT INTO BarberHas (BarberID, AppointmentID) VALUES (?, ?)");
                if (!$stmt) {
                    throw new Exception('Database prepare error: ' . $conn->error);
                }

                $stmt->bind_param("ii", $barberID, $appointmentID);
                if (!$stmt->execute()) {
                    throw new Exception('Database execute error: ' . $stmt->error);
                }
                $stmt->close();

                // Create ApptContains Records
                foreach ($apptData['services'] as $service) {
                    if (!isset($service['serviceId'])) {
                        throw new Exception('Missing service ID in service selection');
                    }

                    $stmt = $conn->prepare("INSERT INTO ApptContains (ServiceID, AppointmentID) VALUES (?, ?)");
                    if (!$stmt) {
                        throw new Exception('Database prepare error: ' . $conn->error);
                    }

                    $stmt->bind_param("ii", $service['serviceId'], $appointmentID);
                    if (!$stmt->execute()) {
                        throw new Exception('Database execute error: ' . $stmt->error);
                    }
                    $stmt->close();
                }
            }
            
            $conn->commit();
            $success = 'Booking successful! Your appointment has been confirmed.';
            // Redirect to customer dashboard with success message
            setFlashMessage($success, 'success');
            redirect('customer/dashboard.php');
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Booking failed: ' . $e->getMessage();
            error_log('Booking process error: ' . $e->getMessage());
        }
    }
}

// Get the services data from POST
$services = $_POST['services'] ?? [];

if (empty($services)) {
    setFlashMessage('No services selected for payment.', 'error');
    redirect('booking.php');
}

// Calculate total amount
$totalAmount = 0;
foreach ($services as $service) {
    if (isset($service['price']) && is_numeric($service['price'])) {
        $totalAmount += floatval($service['price']);
    }
}

include 'includes/header.php';
?>

<div class="payment-container">
    <h2>Payment Details</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="order-summary">
        <h3>Order Summary</h3>
        <div class="services-list">
            <?php foreach ($services as $service): ?>
                <div class="service-item">
                    <div class="service-details">
                        <span class="service-name"><?php echo htmlspecialchars($service['serviceName']); ?></span>
                        <span class="service-barber">Barber: <?php echo htmlspecialchars($service['barberName']); ?></span>
                        <span class="service-time">Time: <?php echo htmlspecialchars($service['date'] . ' ' . substr($service['time'], 11, 5)); ?></span>
                    </div>
                    <span class="service-price">BDT <?php echo number_format($service['price'], 2); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="total-amount">
            <strong class="paynumber"> Pay to : 01717171717</strong><strong class="total-amount">Total Amount:</strong> BDT <?php echo number_format($totalAmount, 2); ?>
        </div>
    </div>

    <div class="payment-form">
        <h3>Payment Method</h3>
        <form id="payment-form" method="POST">

            <?php foreach ($services as $index => $service): ?>
                <?php foreach ($service as $key => $value): ?>
                    <input type="hidden" name="services[<?php echo $index; ?>][<?php echo $key; ?>]" value="<?php echo htmlspecialchars($value); ?>">
                <?php endforeach; ?>
            <?php endforeach; ?>
            
            <div class="form-group">
                <label for="payment-method">Select Payment Method:</label>
                <select id="payment-method" name="payment_method" required onchange="toggleTransactionId()">
                    <option value="">Select a payment method</option>
                    <option value="bkash">bKash</option>
                    <option value="nagad">Nagad</option>
                    <option value="rocket">Rocket</option>
                    <option value="cash">Cash</option>
                </select>
            </div>

            <div class="form-group">
                <label for="transaction-id">Transaction ID (11 characters):</label>
                <input type="text" id="transaction-id" name="transaction_id" maxlength="11" minlength="11" pattern=".{11,11}">
                <small class="form-text text-muted">Enter exactly 11 characters for the transaction ID</small>
            </div>

            <button type="submit" class="pay-button">Pay Now</button>
        </form>
    </div>
</div>




<script>
function toggleTransactionId() {
    const paymentMethod = document.getElementById('payment-method').value;
    const transactionIdInput = document.getElementById('transaction-id');
    const transactionIdLabel = transactionIdInput.previousElementSibling;
    const transactionIdHelp = transactionIdInput.nextElementSibling;
    
    if (paymentMethod === 'cash') {
        transactionIdInput.removeAttribute('required');
        transactionIdInput.style.display = 'none';
        transactionIdLabel.style.display = 'none';
        transactionIdHelp.style.display = 'none';
    } else {
        transactionIdInput.setAttribute('required', '');
        transactionIdInput.style.display = 'block';
        transactionIdLabel.style.display = 'block';
        transactionIdHelp.style.display = 'block';
    }
}

// Run on page load to set initial state
document.addEventListener('DOMContentLoaded', toggleTransactionId);
</script>

<style>
    .payment-container {
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        background-color: #fff;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        border-radius: 4px;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }

    .order-summary {
        margin-bottom: 30px;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 5px;
    }

    .services-list {
        margin-bottom: 20px;
    }

    .service-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        margin-bottom: 10px;
        background-color: #fff;
        border: 1px solid #eee;
        border-radius: 4px;
    }

    .service-details {
        display: flex;
        flex-direction: column;
    }

    .service-name {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .service-barber, .service-time {
        font-size: 0.9em;
        color: #666;
    }

    .total-amount {
        font-size: 1.2em;
        padding-top: 10px;
        display: block;
        border-top: 1px solid #eee;
    }

    .paynumber{
        font-size: 1em;
    }

    .total-amount{
        font-size: 1em;
        text-align: right;
    }

    .payment-form {
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 5px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .form-group select,
    .form-group input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .form-text {
        font-size: 0.875em;
        color: #6c757d;
        margin-top: 5px;
    }

    .pay-button {
        background-color: #4CAF50;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        width: 100%;
        font-size: 1.1em;
    }


    .pay-button:hover {
        background-color: #45a049;
    }
</style>

<?php include 'includes/footer.php'; ?> 