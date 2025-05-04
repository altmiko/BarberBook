<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in as a customer
if (!isLoggedIn() || !isCustomer()) {
    setFlashMessage('Please log in as a customer to book an appointment.', 'error');
    redirect('login.php');
}

$customer_id = $_SESSION['user_id'];
$errors = [];
$success = false;
$selectedDate = '';
$selectedServices = [];
$selectedBarber = '';
$selectedTime = '';

// Process booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $selectedDate = sanitize($_POST['booking_date']);
    $selectedServices = isset($_POST['service_ids']) ? $_POST['service_ids'] : [];
    $selectedBarber = (int) $_POST['barber_id'];
    $selectedTime = sanitize($_POST['time_slot']);
    
    // Validate inputs
    if (empty($selectedDate)) {
        $errors['date'] = 'Please select a date';
    } else {
        $date = new DateTime($selectedDate);
        $today = new DateTime();
        
        if ($date < $today) {
            $errors['date'] = 'Please select a future date';
        }
    }
    
    if (empty($selectedServices)) {
        $errors['service'] = 'Please select at least one service';
    }
    
    if (empty($selectedBarber)) {
        $errors['barber'] = 'Please select a barber';
    }
    
    if (empty($selectedTime)) {
        $errors['time'] = 'Please select a time slot';
    }
    
    // If no errors, proceed with booking
    if (empty($errors)) {
        // Calculate total duration and price
        $totalDuration = 0;
        $totalPrice = 0;
        $serviceDetails = [];
        
        foreach ($selectedServices as $serviceId) {
            $serviceQuery = "SELECT Duration, Price, Name FROM Services WHERE ServiceID = ?";
            $serviceStmt = $conn->prepare($serviceQuery);
            $serviceStmt->bind_param("i", $serviceId);
            $serviceStmt->execute();
            $serviceResult = $serviceStmt->get_result();
            $serviceData = $serviceResult->fetch_assoc();
            
            $totalDuration += $serviceData['Duration'];
            $totalPrice += $serviceData['Price'];
            $serviceDetails[] = $serviceData;
        }
        
        // Calculate end time
        $startDateTime = new DateTime($selectedDate . ' ' . $selectedTime);
        $endDateTime = clone $startDateTime;
        $endDateTime->add(new DateInterval('PT' . $totalDuration . 'M'));
        
        $startTime = $startDateTime->format('Y-m-d H:i:s');
        $endTime = $endDateTime->format('Y-m-d H:i:s');
        
        // Check if the barber is available at the selected time
        $availabilityQuery = "SELECT * FROM Slots 
                             JOIN Appointments ON Slots.AppointmentID = Appointments.AppointmentID
                             WHERE Slots.BarberID = ? 
                             AND Appointments.Status != 'cancelled'
                             AND (
                                 (Slots.Time BETWEEN ? AND ?)
                                 OR (? BETWEEN StartTime AND EndTime)
                             )";
        
        $availabilityStmt = $conn->prepare($availabilityQuery);
        $availabilityStmt->bind_param("isss", $selectedBarber, $startTime, $endTime, $startTime);
        $availabilityStmt->execute();
        $availabilityResult = $availabilityStmt->get_result();
        
        if ($availabilityResult->num_rows > 0) {
            $errors['time'] = 'The selected time slot is not available. Please choose another time.';
        } else {
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Create payment record
                $paymentSql = "INSERT INTO Payments (Amount, PayMethod, PayStatus) VALUES (?, 'credit_card', 'pending')";
                $paymentStmt = $conn->prepare($paymentSql);
                $paymentStmt->bind_param("i", $totalPrice);
                $paymentStmt->execute();
                $paymentId = $conn->insert_id;
                
                // Create appointment record
                $appointmentSql = "INSERT INTO Appointments (StartTime, EndTime, Status, PaymentID, CustomerID) 
                                  VALUES (?, ?, 'scheduled', ?, ?)";
                $appointmentStmt = $conn->prepare($appointmentSql);
                $appointmentStmt->bind_param("ssii", $startTime, $endTime, $paymentId, $customer_id);
                $appointmentStmt->execute();
                $appointmentId = $conn->insert_id;
                
                // Create slot
                $slotSql = "INSERT INTO Slots (Status, Time, BarberID, AppointmentID) VALUES ('booked', ?, ?, ?)";
                $slotStmt = $conn->prepare($slotSql);
                $slotStmt->bind_param("sii", $startTime, $selectedBarber, $appointmentId);
                $slotStmt->execute();
                
                // Link services to appointment
                foreach ($selectedServices as $serviceId) {
                    $serviceAppointmentSql = "INSERT INTO ApptContains (ServiceID, AppointmentID) VALUES (?, ?)";
                    $serviceAppointmentStmt = $conn->prepare($serviceAppointmentSql);
                    $serviceAppointmentStmt->bind_param("ii", $serviceId, $appointmentId);
                    $serviceAppointmentStmt->execute();
                }
                
                // Link barber to appointment
                $barberAppointmentSql = "INSERT INTO BarberHas (BarberID, AppointmentID) VALUES (?, ?)";
                $barberAppointmentStmt = $conn->prepare($barberAppointmentSql);
                $barberAppointmentStmt->bind_param("ii", $selectedBarber, $appointmentId);
                $barberAppointmentStmt->execute();
                
                // Commit the transaction
                $conn->commit();
                
                // Send notification
                $customerEmail = $_SESSION['user_email'];
                $barberQuery = "SELECT FirstName, LastName FROM Barbers WHERE UserID = ?";
                $barberStmt = $conn->prepare($barberQuery);
                $barberStmt->bind_param("i", $selectedBarber);
                $barberStmt->execute();
                $barberResult = $barberStmt->get_result();
                $barberData = $barberResult->fetch_assoc();
                
                $subject = "Appointment Confirmation - Barberbook";
                $message = "Dear " . $_SESSION['user_name'] . ",\n\n";
                $message .= "Your appointment has been confirmed for " . date('F j, Y', strtotime($selectedDate)) . " at " . date('g:i A', strtotime($selectedTime)) . ".\n\n";
                $message .= "Services:\n";
                foreach ($serviceDetails as $service) {
                    $message .= "- " . $service['Name'] . " (BDT " . $service['Price'] . ")\n";
                }
                $message .= "\nTotal Duration: " . $totalDuration . " minutes\n";
                $message .= "Total Price: BDT " . $totalPrice . "\n";
                $message .= "Barber: " . $barberData['FirstName'] . " " . $barberData['LastName'] . "\n\n";
                $message .= "Thank you for choosing Barberbook!\n";
                
                sendNotification($customerEmail, $subject, $message, $customer_id, $conn);
                
                // Set success flag and reset form
                $success = true;
                $selectedDate = '';
                $selectedServices = [];
                $selectedBarber = '';
                $selectedTime = '';
                
                setFlashMessage("Your appointment has been successfully booked! You can view your booking details in your dashboard.", "success");
                redirect("customer/dashboard.php");
                
            } catch (Exception $e) {
                // Rollback the transaction if there was an error
                $conn->rollback();
                $errors['system'] = "An error occurred: " . $e->getMessage();
            }
        }
    }
}

// Fetch available services
$servicesQuery = "SELECT * FROM Services ORDER BY Price";
$servicesResult = $conn->query($servicesQuery);

// Fetch barbers
$barbersQuery = "SELECT UserID, FirstName, LastName, Bio FROM Barbers";
$barbersResult = $conn->query($barbersQuery);

// Define page title
$page_title = "Book Appointment";
include 'includes/header.php';
?>

<main>
    <section class="booking-section">
        <div class="container">
            <h1 class="section-title">Book Your Appointment</h1>
            
            <?php if (!empty($errors['system'])): ?>
                <div class="alert alert-error">
                    <?php echo $errors['system']; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <p>Your appointment has been successfully booked! You will receive a confirmation shortly.</p>
                    <p><a href="customer/appointments.php" class="btn btn-primary">View Your Appointments</a></p>
                </div>
            <?php else: ?>
                <div class="booking-container">
                    <div class="booking-sidebar">
                        <div class="booking-info">
                            <h3>How Booking Works</h3>
                            <ul class="booking-steps">
                                <li>
                                    <span class="step-number">1</span>
                                    <div class="step-content">
                                        <h4>Select a Service</h4>
                                        <p>Choose from our range of professional barber services</p>
                                    </div>
                                </li>
                                <li>
                                    <span class="step-number">2</span>
                                    <div class="step-content">
                                        <h4>Choose Your Barber</h4>
                                        <p>Select your preferred stylist from our team</p>
                                    </div>
                                </li>
                                <li>
                                    <span class="step-number">3</span>
                                    <div class="step-content">
                                        <h4>Pick a Date & Time</h4>
                                        <p>Select an available slot that works for you</p>
                                    </div>
                                </li>
                                <li>
                                    <span class="step-number">4</span>
                                    <div class="step-content">
                                        <h4>Confirm Booking</h4>
                                        <p>Review your details and confirm your appointment</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        
                        <div class="booking-summary" id="booking-summary">
                            <h3>Booking Summary</h3>
                            <div class="summary-content">
                                <div class="summary-item">
                                    <span>Service:</span>
                                    <span id="summary-service">-</span>
                                </div>
                                <div class="summary-item">
                                    <span>Price:</span>
                                    <span id="summary-price">-</span>
                                </div>
                                <div class="summary-item">
                                    <span>Duration:</span>
                                    <span id="summary-duration">-</span>
                                </div>
                                <div class="summary-item">
                                    <span>Barber:</span>
                                    <span id="summary-barber">-</span>
                                </div>
                                <div class="summary-item">
                                    <span>Date:</span>
                                    <span id="summary-date">-</span>
                                </div>
                                <div class="summary-item">
                                    <span>Time:</span>
                                    <span id="summary-time">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="booking-form-container">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="booking-form" data-validate="true">
                            <div class="form-step active" id="step-1">
                                <h3>Select a Service</h3>
                                
                                <div class="services-selection">
                                    <?php if ($servicesResult->num_rows > 0): ?>
                                        <?php while ($service = $servicesResult->fetch_assoc()): ?>
                                            <div class="service-option">
                                                <input type="checkbox" name="service_ids[]" id="service-<?php echo $service['ServiceID']; ?>" value="<?php echo $service['ServiceID']; ?>" 
                                                    data-price="<?php echo $service['Price']; ?>" 
                                                    data-duration="<?php echo $service['Duration']; ?>"
                                                    data-name="<?php echo htmlspecialchars($service['Name']); ?>"
                                                    <?php echo (in_array($service['ServiceID'], $selectedServices)) ? 'checked' : ''; ?>>
                                                <label for="service-<?php echo $service['ServiceID']; ?>" class="service-card">
                                                    <div class="service-icon"><i class="fas fa-cut"></i></div>
                                                    <h4><?php echo htmlspecialchars($service['Name']); ?></h4>
                                                    <p><?php echo htmlspecialchars($service['Description']); ?></p>
                                                    <div class="service-details">
                                                        <span class="duration"><i class="far fa-clock"></i> <?php echo $service['Duration']; ?> min</span>
                                                        <span class="price">BDT <?php echo $service['Price']; ?></span>
                                                    </div>
                                                </label>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <p>No services available at the moment.</p>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($errors['service'])): ?>
                                    <div class="error-message"><?php echo $errors['service']; ?></div>
                                <?php endif; ?>
                                
                                <div class="form-buttons">
                                    <button type="button" class="btn btn-primary next-step" data-step="1">Continue</button>
                                </div>
                            </div>
                            
                            <div class="form-step" id="step-2">
                                <h3>Choose Your Barber</h3>
                                
                                <div class="barbers-selection">
                                    <?php if ($barbersResult->num_rows > 0): ?>
                                        <?php while ($barber = $barbersResult->fetch_assoc()): ?>
                                            <div class="barber-option">
                                                <input type="radio" name="barber_id" id="barber-<?php echo $barber['UserID']; ?>" value="<?php echo $barber['UserID']; ?>" 
                                                    data-name="<?php echo htmlspecialchars($barber['FirstName'] . ' ' . $barber['LastName']); ?>"
                                                    <?php echo ($selectedBarber == $barber['UserID']) ? 'checked' : ''; ?> required>
                                                <label for="barber-<?php echo $barber['UserID']; ?>" class="barber-card">
                                                    <div class="barber-image">
                                                        <img src="https://images.pexels.com/photos/15613465/pexels-photo-15613465/free-photo-of-man-with-beard-holding-scissors.jpeg?auto=compress&cs=tinysrgb&w=600" 
                                                             alt="<?php echo htmlspecialchars($barber['FirstName'] . ' ' . $barber['LastName']); ?>">
                                                    </div>
                                                    <h4><?php echo htmlspecialchars($barber['FirstName'] . ' ' . $barber['LastName']); ?></h4>
                                                    <p class="barber-bio"><?php echo htmlspecialchars(substr($barber['Bio'] ?? 'Professional barber with years of experience', 0, 100)) . '...'; ?></p>
                                                    
                                                    <?php
                                                    // Get average rating
                                                    $barberID = $barber['UserID'];
                                                    $ratingSQL = "SELECT AVG(Rating) as AverageRating FROM Reviews WHERE BarberID = $barberID";
                                                    $ratingResult = $conn->query($ratingSQL);
                                                    $ratingRow = $ratingResult->fetch_assoc();
                                                    $averageRating = round($ratingRow['AverageRating'], 1) ?: 0;
                                                    ?>
                                                    
                                                    <div class="barber-rating">
                                                        <span class="stars">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <?php if ($i <= $averageRating): ?>
                                                                    <i class="fas fa-star"></i>
                                                                <?php elseif ($i - 0.5 <= $averageRating): ?>
                                                                    <i class="fas fa-star-half-alt"></i>
                                                                <?php else: ?>
                                                                    <i class="far fa-star"></i>
                                                                <?php endif; ?>
                                                            <?php endfor; ?>
                                                        </span>
                                                        <span class="rating-value"><?php echo $averageRating; ?></span>
                                                    </div>
                                                </label>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <p>No barbers available at the moment.</p>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($errors['barber'])): ?>
                                    <div class="error-message"><?php echo $errors['barber']; ?></div>
                                <?php endif; ?>
                                
                                <div class="form-buttons">
                                    <button type="button" class="btn btn-outline prev-step" data-step="2">Back</button>
                                    <button type="button" class="btn btn-primary next-step" data-step="2">Continue</button>
                                </div>
                            </div>
                            
                            <div class="form-step" id="step-3">
                                <h3>Select Date & Time</h3>
                                
                                <div class="form-group">
                                    <label for="booking_date">Select Date</label>
                                    <input type="date" id="booking_date" name="booking_date" class="date-select" 
                                        value="<?php echo $selectedDate; ?>" required data-label="Date">
                                    <?php if (!empty($errors['date'])): ?>
                                        <div class="error-message"><?php echo $errors['date']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label>Select Time</label>
                                    <div class="time-slots" id="time-slots-container">
                                        <p id="time-slots-message">Please select a date and barber first to see available time slots.</p>
                                        <!-- Time slots will be populated dynamically -->
                                    </div>
                                    <?php if (!empty($errors['time'])): ?>
                                        <div class="error-message"><?php echo $errors['time']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-buttons">
                                    <button type="button" class="btn btn-outline prev-step" data-step="3">Back</button>
                                    <button type="button" class="btn btn-primary next-step" data-step="3">Continue</button>
                                </div>
                            </div>
                            
                            <div class="form-step" id="step-4">
                                <h3>Confirm Your Booking</h3>
                                
                                <div class="booking-confirmation">
                                    <p>Please review your booking details below:</p>
                                    
                                    <div class="confirmation-details">
                                        <div class="confirmation-item">
                                            <span class="label">Service:</span>
                                            <span class="value" id="confirm-service">-</span>
                                        </div>
                                        <div class="confirmation-item">
                                            <span class="label">Price:</span>
                                            <span class="value" id="confirm-price">-</span>
                                        </div>
                                        <div class="confirmation-item">
                                            <span class="label">Duration:</span>
                                            <span class="value" id="confirm-duration">-</span>
                                        </div>
                                        <div class="confirmation-item">
                                            <span class="label">Barber:</span>
                                            <span class="value" id="confirm-barber">-</span>
                                        </div>
                                        <div class="confirmation-item">
                                            <span class="label">Date:</span>
                                            <span class="value" id="confirm-date">-</span>
                                        </div>
                                        <div class="confirmation-item">
                                            <span class="label">Time:</span>
                                            <span class="value" id="confirm-time">-</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Hidden field for time selection -->
                                    <input type="hidden" name="time_slot" id="selected_time" value="<?php echo $selectedTime; ?>">
                                    
                                    <div class="form-buttons">
                                        <button type="button" class="btn btn-outline prev-step" data-step="4">Back</button>
                                        <button type="submit" class="btn btn-primary">Confirm Booking</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
include 'includes/footer.php';
?>

<style>
/* Booking Page Specific Styles */
.booking-section {
    padding: var(--space-6) 0;
    margin-top: 80px;
}

.section-title {
    text-align: center;
    margin-bottom: var(--space-5);
}

.booking-container {
    display: flex;
    gap: var(--space-4);
    margin-top: var(--space-4);
}

.booking-sidebar {
    width: 30%;
    min-width: 300px;
}

.booking-form-container {
    width: 70%;
}

.booking-info {
    background-color: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    padding: var(--space-3);
    margin-bottom: var(--space-3);
}

.booking-steps {
    margin-top: var(--space-2);
}

.booking-steps li {
    display: flex;
    margin-bottom: var(--space-2);
    align-items: flex-start;
}

.step-number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    background-color: var(--color-primary);
    color: white;
    border-radius: 50%;
    font-weight: bold;
    margin-right: var(--space-2);
    flex-shrink: 0;
}

.step-content h4 {
    margin-bottom: 4px;
    font-size: 1.8rem;
}

.step-content p {
    color: var(--color-text-light);
    margin-bottom: 0;
}

.booking-summary {
    background-color: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    padding: var(--space-3);
    position: sticky;
    top: 100px;
}

.summary-content {
    margin-top: var(--space-2);
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: var(--space-1);
    padding-bottom: var(--space-1);
    border-bottom: 1px solid var(--color-border);
}

.summary-item:last-child {
    border-bottom: none;
}

.booking-form {
    background-color: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    padding: var(--space-4);
}

.form-step {
    display: none;
}

.form-step.active {
    display: block;
}

.form-step h3 {
    margin-bottom: var(--space-3);
    padding-bottom: var(--space-2);
    border-bottom: 1px solid var(--color-border);
}

.services-selection, .barbers-selection {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: var(--space-3);
    margin-bottom: var(--space-3);
}

.service-option, .barber-option {
    position: relative;
}

.service-option input, .barber-option input {
    position: absolute;
    opacity: 0;
}

.service-card, .barber-card {
    display: block;
    background-color: white;
    border: 2px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--space-2);
    cursor: pointer;
    transition: all 0.3s ease;
    height: 100%;
}

.service-card {
    padding-bottom: var(--space-4);
}

.service-option input:checked + .service-card,
.barber-option input:checked + .barber-card {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 2px rgba(26, 54, 93, 0.2);
}

.service-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: var(--color-primary-light);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: var(--space-2);
}

.service-icon i {
    font-size: 2rem;
    color: white;
}

.service-card h4, .barber-card h4 {
    margin-bottom: var(--space-1);
    font-size: 1.8rem;
}

.service-details {
    display: flex;
    justify-content: space-between;
    margin-top: var(--space-2);
    position: absolute;
    bottom: var(--space-2);
    left: var(--space-2);
    right: var(--space-2);
}

.barber-image {
    width: 100%;
    height: 180px;
    border-radius: var(--radius-sm);
    overflow: hidden;
    margin-bottom: var(--space-2);
}

.barber-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.barber-bio {
    color: var(--color-text-light);
    margin-bottom: var(--space-2);
    font-size: 1.4rem;
}

.barber-rating {
    display: flex;
    align-items: center;
    gap: 8px;
}

.stars {
    color: var(--color-secondary);
}

.time-slots {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: var(--space-2);
    margin-top: var(--space-2);
}

.time-slot {
    padding: var(--space-1) var(--space-2);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.time-slot:hover {
    border-color: var(--color-primary-light);
}

.time-slot.selected {
    background-color: var(--color-primary);
    color: white;
    border-color: var(--color-primary);
}

.time-slot.unavailable {
    background-color: var(--color-bg-alt);
    color: var(--color-text-light);
    cursor: not-allowed;
    text-decoration: line-through;
}

.form-buttons {
    display: flex;
    justify-content: space-between;
    margin-top: var(--space-4);
}

.booking-confirmation {
    background-color: var(--color-bg-alt);
    border-radius: var(--radius-md);
    padding: var(--space-3);
    margin-bottom: var(--space-3);
}

.confirmation-details {
    margin: var(--space-3) 0;
}

.confirmation-item {
    display: flex;
    margin-bottom: var(--space-1);
}

.confirmation-item .label {
    font-weight: 600;
    width: 100px;
}

.payment-info {
    border-top: 1px solid var(--color-border);
    padding-top: var(--space-2);
    font-size: 1.4rem;
    color: var(--color-text-light);
}

@media (max-width: 991px) {
    .booking-container {
        flex-direction: column;
    }
    
    .booking-sidebar, .booking-form-container {
        width: 100%;
    }
    
    .booking-sidebar {
        order: 2;
    }
    
    .booking-form-container {
        order: 1;
        margin-bottom: var(--space-3);
    }
    
    .booking-summary {
        position: static;
    }
}

@media (max-width: 768px) {
    .services-selection, .barbers-selection {
        grid-template-columns: 1fr;
    }
    
    .time-slots {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 576px) {
    .time-slots {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .form-buttons {
        flex-direction: column;
        gap: var(--space-2);
    }
    
    .form-buttons button {
        width: 100%;
    }
}
</style>

<script>
// Handle multi-step form
const steps = document.querySelectorAll('.form-step');
const nextButtons = document.querySelectorAll('.next-step');
const prevButtons = document.querySelectorAll('.prev-step');

// Service selection summary update
const serviceCheckboxes = document.querySelectorAll('input[name="service_ids[]"]');
const barberRadios = document.querySelectorAll('input[name="barber_id"]');
const dateInput = document.getElementById('booking_date');
const summaryService = document.getElementById('summary-service');
const summaryPrice = document.getElementById('summary-price');
const summaryDuration = document.getElementById('summary-duration');
const summaryBarber = document.getElementById('summary-barber');
const summaryDate = document.getElementById('summary-date');
const summaryTime = document.getElementById('summary-time');

// Confirmation page elements
const confirmService = document.getElementById('confirm-service');
const confirmPrice = document.getElementById('confirm-price');
const confirmDuration = document.getElementById('confirm-duration');
const confirmBarber = document.getElementById('confirm-barber');
const confirmDate = document.getElementById('confirm-date');
const confirmTime = document.getElementById('confirm-time');

// Selected time hidden input
const selectedTimeInput = document.getElementById('selected_time');
const timeSlotsContainer = document.getElementById('time-slots-container');

// Next step buttons
nextButtons.forEach(button => {
    button.addEventListener('click', function() {
        const currentStep = parseInt(this.getAttribute('data-step'));
        
        // Validate current step
        let isValid = true;
        
        if (currentStep === 1) {
            const selectedServices = Array.from(document.querySelectorAll('input[name="service_ids[]"]:checked'));
            if (selectedServices.length === 0) {
                isValid = false;
                alert('Please select at least one service to continue.');
            }
        } else if (currentStep === 2) {
            const selectedBarber = document.querySelector('input[name="barber_id"]:checked');
            if (!selectedBarber) {
                isValid = false;
                alert('Please select a barber to continue.');
            }
        } else if (currentStep === 3) {
            const selectedDate = document.getElementById('booking_date').value;
            const selectedTime = document.getElementById('selected_time').value;
            
            if (!selectedDate) {
                isValid = false;
                alert('Please select a date to continue.');
            } else if (!selectedTime) {
                isValid = false;
                alert('Please select a time slot to continue.');
            }
        }
        
        if (isValid) {
            document.getElementById(`step-${currentStep}`).classList.remove('active');
            document.getElementById(`step-${currentStep + 1}`).classList.add('active');
            
            // Update confirmation page on last step
            if (currentStep === 3) {
                updateConfirmationPage();
            }
        }
    });
});

// Previous step buttons
prevButtons.forEach(button => {
    button.addEventListener('click', function() {
        const currentStep = parseInt(this.getAttribute('data-step'));
        document.getElementById(`step-${currentStep}`).classList.remove('active');
        document.getElementById(`step-${currentStep - 1}`).classList.add('active');
    });
});

// Service selection
serviceCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        const selectedServices = Array.from(document.querySelectorAll('input[name="service_ids[]"]:checked'));
        const serviceNames = selectedServices.map(service => service.getAttribute('data-name')).join(', ');
        const totalPrice = selectedServices.reduce((sum, service) => sum + parseFloat(service.getAttribute('data-price')), 0);
        const totalDuration = selectedServices.reduce((sum, service) => sum + parseInt(service.getAttribute('data-duration')), 0);
        
        summaryService.textContent = serviceNames || '-';
        summaryPrice.textContent = totalPrice > 0 ? `BDT ${totalPrice}` : '-';
        summaryDuration.textContent = totalDuration > 0 ? `${totalDuration} min` : '-';
        
        // Also update time slots if date and barber are selected
        if (dateInput.value && document.querySelector('input[name="barber_id"]:checked')) {
            loadTimeSlots();
        }
    });
});

// Barber selection
barberRadios.forEach(radio => {
    radio.addEventListener('change', function() {
        const barberName = this.getAttribute('data-name');
        summaryBarber.textContent = barberName;
        
        // Also update time slots if date is selected
        if (dateInput.value) {
            loadTimeSlots();
        }
    });
});

// Date selection
dateInput.addEventListener('change', function() {
    const selectedDate = new Date(this.value);
    const formattedDate = selectedDate.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    summaryDate.textContent = formattedDate;
    
    // Also update time slots if barber is selected
    if (document.querySelector('input[name="barber_id"]:checked')) {
        loadTimeSlots();
    }
});

// Time slot selection
function handleTimeSlotSelection() {
    const timeSlots = document.querySelectorAll('.time-slot:not(.unavailable)');
    
    timeSlots.forEach(slot => {
        slot.addEventListener('click', function() {
            // Remove selected class from all slots
            timeSlots.forEach(s => s.classList.remove('selected'));
            
            // Add selected class to clicked slot
            this.classList.add('selected');
            
            // Update hidden input and summary
            const selectedTime = this.getAttribute('data-time');
            selectedTimeInput.value = selectedTime;
            
            const formattedTime = new Date(`2000-01-01T${selectedTime}`).toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            
            summaryTime.textContent = formattedTime;
        });
    });
}

// Load available time slots
function loadTimeSlots() {
    const selectedDate = dateInput.value;
    const selectedBarber = document.querySelector('input[name="barber_id"]:checked').value;
    const selectedServices = Array.from(document.querySelectorAll('input[name="service_ids[]"]:checked'));
    
    if (!selectedDate || !selectedBarber || selectedServices.length === 0) {
        timeSlotsContainer.innerHTML = '<p>Please select a date, at least one service, and a barber first.</p>';
        return;
    }
    
    timeSlotsContainer.innerHTML = '<p>Loading available time slots...</p>';
    
    // Calculate total duration
    const totalDuration = selectedServices.reduce((sum, service) => {
        return sum + parseInt(service.getAttribute('data-duration'));
    }, 0);
    
    // Generate time slots
    setTimeout(() => {
        const timeSlots = [];
        const unavailableTimes = ['10:00', '11:30', '14:00', '16:30']; // Example of unavailable times
        
        for (let hour = 9; hour < 18; hour++) {
            for (let minute = 0; minute < 60; minute += 30) {
                const formattedHour = hour.toString().padStart(2, '0');
                const formattedMinute = minute.toString().padStart(2, '0');
                const timeString = `${formattedHour}:${formattedMinute}`;
                
                // Check if this slot allows enough time for all services before closing (6 PM)
                const slotTime = new Date();
                slotTime.setHours(hour, minute);
                
                const endTime = new Date(slotTime.getTime() + totalDuration * 60000);
                if (endTime.getHours() >= 18) {
                    continue; // Skip if services would end after closing time
                }
                
                const isUnavailable = unavailableTimes.includes(timeString);
                timeSlots.push({ time: timeString, unavailable: isUnavailable });
            }
        }
        
        // Render time slots
        let slotsHTML = '';
        if (timeSlots.length === 0) {
            slotsHTML = '<p>No available time slots for the selected date and service duration.</p>';
        } else {
            timeSlots.forEach(slot => {
                const displayTime = new Date(`2000-01-01T${slot.time}:00`).toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
                
                const unavailableClass = slot.unavailable ? 'unavailable' : '';
                slotsHTML += `<div class="time-slot ${unavailableClass}" data-time="${slot.time}">${displayTime}</div>`;
            });
        }
        
        timeSlotsContainer.innerHTML = slotsHTML;
        
        // Add event listeners to the new time slots
        handleTimeSlotSelection();
    }, 500);
}

// Update confirmation page
function updateConfirmationPage() {
    const selectedServices = Array.from(document.querySelectorAll('input[name="service_ids[]"]:checked'));
    const serviceNames = selectedServices.map(service => service.getAttribute('data-name')).join(', ');
    const totalPrice = selectedServices.reduce((sum, service) => sum + parseFloat(service.getAttribute('data-price')), 0);
    const totalDuration = selectedServices.reduce((sum, service) => sum + parseInt(service.getAttribute('data-duration')), 0);
    
    // Get selected barber
    const selectedBarber = document.querySelector('input[name="barber_id"]:checked');
    const barberName = selectedBarber.getAttribute('data-name');
    
    // Get selected date
    const dateValue = dateInput.value;
    const selectedDate = new Date(dateValue);
    const formattedDate = selectedDate.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    // Get selected time
    const timeValue = selectedTimeInput.value;
    const formattedTime = new Date(`2000-01-01T${timeValue}:00`).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
    
    // Update confirmation page
    confirmService.textContent = serviceNames;
    confirmPrice.textContent = `BDT ${totalPrice}`;
    confirmDuration.textContent = `${totalDuration} min`;
    confirmBarber.textContent = barberName;
    confirmDate.textContent = formattedDate;
    confirmTime.textContent = formattedTime;
}

// Initialize any pre-selected options
window.addEventListener('DOMContentLoaded', () => {
    // Check if any service is pre-selected
    const preSelectedServices = Array.from(document.querySelectorAll('input[name="service_ids[]"]:checked'));
    if (preSelectedServices.length > 0) {
        const serviceNames = preSelectedServices.map(service => service.getAttribute('data-name')).join(', ');
        summaryService.textContent = serviceNames;
    }
    
    // Check if any barber is pre-selected
    const preSelectedBarber = document.querySelector('input[name="barber_id"]:checked');
    if (preSelectedBarber) {
        const barberName = preSelectedBarber.getAttribute('data-name');
        summaryBarber.textContent = barberName;
    }
    
    // Check if date is pre-selected
    if (dateInput.value) {
        const selectedDate = new Date(dateInput.value);
        const formattedDate = selectedDate.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        summaryDate.textContent = formattedDate;
    }
    
    // Check if time is pre-selected
    if (selectedTimeInput.value) {
        const formattedTime = new Date(`2000-01-01T${selectedTimeInput.value}:00`).toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
        
        summaryTime.textContent = formattedTime;
    }
    
    // Load time slots if all required fields are selected
    if (dateInput.value && document.querySelector('input[name="barber_id"]:checked') && document.querySelector('input[name="service_ids[]"]:checked')) {
        loadTimeSlots();
    }
});
</script>