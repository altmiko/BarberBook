<?php
session_start();
require_once 'config/database.php'; // Adjust path as needed
require_once 'includes/functions.php'; // Adjust path as needed

// Validate user is logged in as customer
if (!isLoggedIn() || !isCustomer()) {
    setFlashMessage('Please log in as a customer to book an appointment.', 'error');
    redirect('login.php');
}

// Validate barber parameter if present
if (isset($_GET['barber'])) {
    $barber_id = intval($_GET['barber']);
    $barber_check = $conn->prepare("SELECT UserID FROM Barbers WHERE UserID = ?");
    $barber_check->bind_param("i", $barber_id);
    $barber_check->execute();
    $barber_result = $barber_check->get_result();
    
    if ($barber_result->num_rows === 0) {
        setFlashMessage('Invalid barber selected.', 'error');
        redirect('barbers.php');
    }
    $barber_check->close();
}

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$page_title = "Book Appointment - Step 2";

// --- PHP Backend Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'An error occurred.'];

    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'User not logged in. Please login to book an appointment.';
        echo json_encode($response);
        exit;
    }
    $customerID = $_SESSION['user_id'];

    if ($_POST['action'] === 'fetch_bookings') {
        $selectedDate = $_POST['date'] ?? date('Y-m-d');
        try {
            // Validate date format
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
                throw new Exception('Invalid date format');
            }

            // Fetch appointments that start on the selected date
            $stmt = $conn->prepare("
                SELECT 
                    s.Time, 
                    s.BarberID,
                    a.EndTime
                FROM Slots s
                JOIN Appointments a ON s.AppointmentID = a.AppointmentID
                WHERE DATE(s.Time) = ? AND s.Status = 'Booked'
                AND a.Status = 'Scheduled';
            ");
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . $conn->error);
            }

            $stmt->bind_param("s", $selectedDate);
            if (!$stmt->execute()) {
                throw new Exception('Database execute error: ' . $stmt->error);
            }

            $result = $stmt->get_result();
            $bookedSlots = [];
            while ($row = $result->fetch_assoc()) {
                $bookedSlots[] = $row;
            }
            $stmt->close();
            $response = ['success' => true, 'bookedSlots' => $bookedSlots];
        } catch (Exception $e) {
            $response['message'] = 'Error fetching bookings: ' . $e->getMessage();
            error_log('Booking fetch error: ' . $e->getMessage());
        }
        echo json_encode($response);
        exit;
    } 
    elseif ($_POST['action'] === 'process_booking') {
        $selectedServices = $_POST['services'] ?? [];
        
        if (empty($selectedServices)) {
            $response['message'] = 'No services selected.';
            echo json_encode($response);
            exit;
        }

        $conn->begin_transaction();
        try {
            // Check for existing appointments for this customer at any of the requested start times
            $requestedStartTimes = [];
            foreach ($selectedServices as $item) {
                if (!isset($item['time'])) { // item['time'] is "YYYY-MM-DD HH:MM:SS" from the cart
                    throw new Exception('Missing time in service selection for booking validation.');
                }
                // Ensure we only check unique start times to avoid redundant DB queries
                if (!in_array($item['time'], $requestedStartTimes)) {
                    $requestedStartTimes[] = $item['time'];
                }
            }

            if (empty($requestedStartTimes) && !empty($selectedServices)) {
                 throw new Exception('Could not determine start times for booking validation.');
            }

            if (!empty($requestedStartTimes)) {
                // Using a temporary table for complex IN clause with many values can be more efficient
                // but for a typical number of cart items, a direct IN clause is fine.
                $placeholders = implode(',', array_fill(0, count($requestedStartTimes), '?'));
                $types = 'i' . str_repeat('s', count($requestedStartTimes)); // CustomerID (i) + N start times (s)
                $params = array_merge([$customerID], $requestedStartTimes);

                $clash_check_sql = "
                    SELECT AppointmentID, StartTime FROM Appointments 
                    WHERE CustomerID = ? 
                    AND StartTime IN ($placeholders)
                    AND Status = 'Scheduled'
                    LIMIT 1
                ";
                
                $clash_check_stmt = $conn->prepare($clash_check_sql);
                if (!$clash_check_stmt) {
                    throw new Exception('Database prepare error for clash check: ' . $conn->error);
                }
                
                $clash_check_stmt->bind_param($types, ...$params);

                if (!$clash_check_stmt->execute()) {
                    throw new Exception('Database execute error for clash check: ' . $clash_check_stmt->error);
                }
                $clash_result = $clash_check_stmt->get_result();
                if ($clash_row = $clash_result->fetch_assoc()) {
                    $clashing_time = new DateTime($clash_row['StartTime']);
                    throw new Exception('Booking failed: You already have an appointment scheduled at this time slot.');
                }
                $clash_check_stmt->close();
            }

            $totalAmount = 0;
            foreach($selectedServices as $item) {
                if (!isset($item['price']) || !is_numeric($item['price'])) {
                    throw new Exception('Invalid price in service selection');
                }
                $totalAmount += floatval($item['price']);
            }

            // Group services by barber and start time
            $appointmentsToCreate = [];
            foreach ($selectedServices as $item) {
                // The barberId from frontend is actually the UserID from Barbers table
                $barberUserID = $item['barberId'] ?? $item['barberID'] ?? null;
                if (!$barberUserID || !isset($item['time']) || !isset($item['duration'])) {
                    throw new Exception('Missing required fields in service selection');
                }

                // Verify the barber exists and get their UserID
                $barberCheck = $conn->prepare("SELECT UserID FROM Barbers WHERE UserID = ?");
                $barberCheck->bind_param("i", $barberUserID);
                $barberCheck->execute();
                $barberResult = $barberCheck->get_result();
                
                if ($barberResult->num_rows === 0) {
                    throw new Exception('Invalid barber selected');
                }
                $barberCheck->close();

                // Create individual payment record for this service
                $payMethod = 'Online';
                $payStatus = 'Pending';
                $stmt = $conn->prepare("INSERT INTO Payments (Amount, PayMethod, PayStatus) VALUES (?, ?, ?)");
                if (!$stmt) {
                    throw new Exception('Database prepare error: ' . $conn->error);
                }

                $stmt->bind_param("dss", $item['price'], $payMethod, $payStatus);
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

                // 2. Create Appointment Record
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

                // 3. Create Slot Record 
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
                
                // 4. Create BarberHas Record 
                $stmt = $conn->prepare("INSERT INTO BarberHas (BarberID, AppointmentID) VALUES (?, ?)");
                if (!$stmt) {
                    throw new Exception('Database prepare error: ' . $conn->error);
                }

                $stmt->bind_param("ii", $barberID, $appointmentID);
                if (!$stmt->execute()) {
                    throw new Exception('Database execute error: ' . $stmt->error);
                }
                $stmt->close();

                // 5. Create ApptContains Records
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
            $response = [
                'success' => true, 
                'message' => 'Booking successful! Your appointment has been confirmed.', 
                'appointment_ids' => $createdAppointmentIDs
            ];

        } catch (Exception $e) {
            $conn->rollback();
            $response['message'] = $e->getMessage();
            error_log('Booking process error: ' . $e->getMessage());
        }
        echo json_encode($response);
        exit;
    }
}

// Fetch Barbers with error handling
$barbers = [];
$result = $conn->query("SELECT UserID, FirstName, LastName FROM Barbers ORDER BY FirstName, LastName");
if (!$result) {
    error_log('Error fetching barbers: ' . $conn->error);
    setFlashMessage('Error loading barbers. Please try again later.', 'error');
} else {
    while($row = $result->fetch_assoc()) {
        $barbers[] = $row;
    }
}

// Fetch Services with error handling
$services = [];
$result = $conn->query("SELECT ServiceID, Name, Description, Duration, Price FROM Services ORDER BY Name");
if (!$result) {
    error_log('Error fetching services: ' . $conn->error);
    setFlashMessage('Error loading services. Please try again later.', 'error');
} else {
    while($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

include 'includes/header.php'; // Assumes you have a header file
?>

<style>
    body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f4f4; color: #333; }
    .booking-container { display: flex; max-width: 1600px; margin: 20px auto; background-color: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); padding: 20px; }
    .grid-controls { margin-bottom: 20px; display: flex; align-items: center; gap: 15px; }
    .grid-controls label { font-weight: bold; }
    .grid-controls input[type="date"] { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
    
    .booking-grid-area { flex: 3; overflow-x: auto; }
    .booking-grid { border-collapse: collapse; width: 100%; table-layout: fixed; }
    .booking-grid th, .booking-grid td { border: 1px solid #ddd; padding: 0; text-align: center; vertical-align: top; }
    .booking-grid th { background-color: #f0f0f0; height: 40px; font-size: 0.9em; }
    .booking-grid td { height: 60px; }
    .barber-name-col { width: 150px; background-color: #f8f8f8; font-weight: bold; padding: 10px; font-size: 0.9em; }
    
    .slot {
        cursor: pointer;
        background-color: #e8f5e9; /* Light green for available */
        transition: background-color 0.3s;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        width: 100%;
        font-size: 0.8em;
        color: #388e3c;
    }
    .slot:hover { background-color: #c8e6c9; }
    .slot.booked {
        background-color: #ffebee; /* Light red for booked */
        color: #c62828;
        cursor: not-allowed;
        font-style: italic;
    }
    .slot.partially-booked { /* If you implement more granular checks */
        background-color: #fff9c4; /* Light yellow */
        color: #f57f17;
    }
    .slot.selected-slot {
        background-color: #bbdefb; /* Light blue for selection process */
        border: 2px solid #2196f3;
    }
    .slot.processing {
        background-color: #e3f2fd; /* Light blue for processing */
        color: #1565c0;
        font-style: italic;
    }

    .order-summary-area { flex: 1; margin-left: 20px; padding: 15px; background-color: #f9f9f9; border-radius: 5px; }
    .order-summary-area h3 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    #selected-services-list { list-style: none; padding: 0; }
    #selected-services-list li { 
        background-color: #fff; margin-bottom: 10px; padding: 10px; border-radius: 3px; border: 1px solid #eee;
        font-size: 0.9em; display: flex; justify-content: space-between; align-items: center;
    }
    #selected-services-list li .service-details { flex-grow: 1; }
    #selected-services-list li .service-name { font-weight: bold; }
    #selected-services-list li .service-time, #selected-services-list li .service-barber { font-size: 0.85em; color: #555; }
    #selected-services-list li .remove-service { cursor: pointer; color: red; font-weight: bold; padding: 0 5px; }

    #total-amount { font-weight: bold; font-size: 1.2em; margin-top: 15px; }
    #confirm-booking-btn { 
        background-color: #4CAF50; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer;
        font-size: 1em; width: 100%; margin-top: 20px; transition: background-color 0.3s;
    }
    #confirm-booking-btn:hover { background-color: #45a049; }
    #confirm-booking-btn:disabled { background-color: #ccc; cursor: not-allowed; }

    /* Modal Styles */
    .modal {
        display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%;
        overflow: auto; background-color: rgba(0,0,0,0.5);
    }
    .modal-content {
        background-color: #fff; margin: 10% auto; padding: 25px; border: 1px solid #ddd;
        width: 80%; max-width: 600px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px; }
    .modal-header h4 { margin: 0; font-size: 1.5em; color: #333; }
    .close-btn { color: #aaa; font-size: 28px; font-weight: bold; cursor: pointer; }
    .close-btn:hover, .close-btn:focus { color: #333; text-decoration: none; }
    
    #services-checkbox-list { max-height: 300px; overflow-y: auto; margin-bottom: 20px; }
    .service-item-label { display: block; margin-bottom: 12px; padding: 10px; background-color: #f9f9f9; border-radius: 4px; cursor: pointer; transition: background-color 0.2s; }
    .service-item-label:hover { background-color: #f0f0f0; }
    .service-item-label input[type="radio"] { margin-right: 10px; vertical-align: middle; }
    .service-item-label .service-info { display: inline-block; vertical-align: middle; }
    .service-item-label .service-name { font-weight: bold; }
    .service-item-label .service-meta { font-size: 0.9em; color: #555; }

    #add-services-to-cart-btn {
        background-color: #007bff; color: white; padding: 10px 18px; border: none; border-radius: 4px;
        cursor: pointer; font-size: 1em; transition: background-color 0.3s;
    }
    #add-services-to-cart-btn:hover { background-color: #0056b3; }
    .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
    .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; }
    .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; }

</style>

<div class="booking-container">
    <div class="booking-grid-area">
        <div id="booking-message" class="alert" style="display:none;"></div>
        <h2>Select Date & Time Slot</h2>
        <div class="grid-controls">
            <label for="booking-date">Select Date:</label>
            <input type="date" id="booking-date" name="booking-date" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>">
        </div>

        <table class="booking-grid">
            <thead>
                <tr id="time-slots-header">
                    <th class="barber-name-col" style="padding: 10px;">Barber</th>
                    <?php
                    // 12 slots from 9 AM to 8 PM (slot is start time)
                    for ($i = 9; $i <= 20; $i++) { // 9 AM to 8 PM (inclusive for start times)
                        $period = $i >= 12 ? 'PM' : 'AM';
                        $displayHour = $i > 12 ? $i - 12 : $i;
                        $nextHour = $i + 1 > 12 ? ($i + 1) - 12 : $i + 1;
                        $nextPeriod = $i + 1 >= 12 ? 'PM' : 'AM';
                        echo "<th>" . $displayHour . ":00 " . $period . " - " . $nextHour . ":00 " . $nextPeriod . "</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody id="booking-grid-body">
                <?php foreach ($barbers as $barber): ?>
                    <tr data-barber-id="<?php echo $barber['UserID']; ?>">
                        <td class="barber-name-col"><?php echo htmlspecialchars($barber['FirstName'] . ' ' . $barber['LastName']); ?></td>
                        <?php
                        for ($i = 9; $i <= 20; $i++) {
                            $timeStr = str_pad($i, 2, "0", STR_PAD_LEFT) . ":00:00";
                            echo "<td data-time-slot=\"" . $timeStr . "\" class=\"slot-cell\">";
                            echo "<div class=\"slot\" data-barber-id=\"" . $barber['UserID'] . "\" data-time=\"" . $timeStr . "\">Available</div>";
                            echo "</td>";
                        }
                        ?>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($barbers)): ?>
                    <tr><td colspan="13">No barbers available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="order-summary-area">
        <h3>Your Selections</h3>
        <ul id="selected-services-list">
            <!-- Selected services will be appended here by JS -->
        </ul>
        <div id="total-amount">Total: BDT 0.00</div>
        <button id="confirm-booking-btn" disabled>Confirm & Book</button>
        <p id="login-prompt" style="color:red; display:none;">Please <a href="login.php">login</a> to book.</p>
    </div>
</div>

<!-- Service Selection Modal -->
<div id="serviceModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h4>Select Service(s)</h4>
            <span class="close-btn" id="closeServiceModal">&times;</span>
        </div>
        <p>For: <strong id="modalBarberName"></strong> at <strong id="modalSlotTime"></strong></p>
        <div id="services-checkbox-list">
            <?php foreach ($services as $service): ?>
                <label class="service-item-label">
                    <input type="radio" name="selected_service" value="<?php echo $service['ServiceID']; ?>"
                           data-name="<?php echo htmlspecialchars($service['Name']); ?>"
                           data-price="<?php echo $service['Price']; ?>"
                           data-duration="<?php echo $service['Duration']; ?>">
                    <span class="service-info">
                        <span class="service-name"><?php echo htmlspecialchars($service['Name']); ?></span><br>
                        <span class="service-meta">Duration: <?php echo $service['Duration']; ?> mins | Price: BDT <?php echo $service['Price']; ?></span>
                        <?php if (!empty($service['Description'])): ?>
                            <br><span class="service-meta" style="font-size:0.8em;"><?php echo htmlspecialchars(substr($service['Description'], 0, 70)); ?>...</span>
                        <?php endif; ?>
                    </span>
                </label>
            <?php endforeach; ?>
            <?php if (empty($services)): ?>
                <p>No services found.</p>
            <?php endif; ?>
        </div>
        <div id="modal-message" class="alert" style="display:none; margin-bottom: 15px;"></div>
        <button id="add-services-to-cart-btn">Add to Selections</button>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookingDateInput = document.getElementById('booking-date');
    const gridBody = document.getElementById('booking-grid-body');
    const serviceModal = document.getElementById('serviceModal');
    const closeServiceModalBtn = document.getElementById('closeServiceModal');
    const addServicesToCartBtn = document.getElementById('add-services-to-cart-btn');
    const servicesCheckboxList = document.getElementById('services-checkbox-list');
    const selectedServicesListUL = document.getElementById('selected-services-list');
    const totalAmountDiv = document.getElementById('total-amount');
    const confirmBookingBtn = document.getElementById('confirm-booking-btn');
    const bookingMessageDiv = document.getElementById('booking-message');
    const loginPrompt = document.getElementById('login-prompt');

    let currentSelectedSlot = null; // { barberId, barberName, time, date }
    let cart = []; // { uniqueId, barberId, barberName, date, time, serviceId, serviceName, price, duration }
    let bookedSlotsData = []; // Stores fetched booked slots for the current date

    const timeSlots = Array.from({length: 12}, (_, i) => (i + 9).toString().padStart(2, '0') + ":00:00"); // 09:00:00 to 20:00:00

    // Check login status (very basic, relies on PHP session variable)
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    if (!isLoggedIn) {
        confirmBookingBtn.disabled = true;
        loginPrompt.style.display = 'block';
    }

    function displayMessage(message, isSuccess) {
        const messageDiv = document.getElementById('booking-message');
        messageDiv.textContent = message;
        messageDiv.className = 'alert ' + (isSuccess ? 'alert-success' : 'alert-danger');
        messageDiv.style.display = 'block';
        
        // Only auto-hide success messages
        if (isSuccess) {
            setTimeout(() => { messageDiv.style.display = 'none'; }, 5000);
        }
    }

    function displayModalMessage(message, isSuccess) {
        const messageDiv = document.getElementById('modal-message');
        messageDiv.textContent = message;
        messageDiv.className = 'alert ' + (isSuccess ? 'alert-success' : 'alert-danger');
        messageDiv.style.display = 'block';
        
        // Scroll the message into view
        messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Only auto-hide success messages
        if (isSuccess) {
            setTimeout(() => { messageDiv.style.display = 'none'; }, 5000);
        }
    }

    async function fetchBookedSlots(date) {
        const formData = new FormData();
        formData.append('action', 'fetch_bookings');
        formData.append('date', date);

        try {
            const response = await fetch('<?php echo $_SERVER["PHP_SELF"]; ?>', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                bookedSlotsData = data.bookedSlots;
            } else {
                displayMessage(data.message || 'Error fetching booking data.', false);
                bookedSlotsData = [];
            }
        } catch (error) {
            displayMessage('Network error fetching booking data: ' + error, false);
            bookedSlotsData = [];
        }
        updateGridUI();
    }
    
    function isSlotBooked(barberId, date, time) {
        // date part of time is YYYY-MM-DD HH:MM:SS
        const slotDateTimeStr = date + ' ' + time; // e.g., "2023-10-27 09:00:00"
        
        return bookedSlotsData.some(booked => {
            const bookedStartDateTime = new Date(booked.Time);
            const bookedEndDateTime = new Date(booked.EndTime);
            const slotStartDateTime = new Date(slotDateTimeStr);
            const slotEndDateTime = new Date(slotStartDateTime.getTime() + (60 * 60 * 1000)); // Add 1 hour for slot duration

            // Check if the barber matches and the slot overlaps with the booking
            return booked.BarberID.toString() === barberId.toString() &&
                   slotStartDateTime < bookedEndDateTime && // Slot starts before booking ends
                   slotEndDateTime > bookedStartDateTime;   // Slot ends after booking starts
        });
    }

    function updateGridUI() {
        const selectedDate = bookingDateInput.value;
        document.querySelectorAll('.slot').forEach(slotDiv => {
            const barberId = slotDiv.dataset.barberId;
            const time = slotDiv.dataset.time;

            // Remove existing classes and event listeners
            slotDiv.classList.remove('booked', 'selected-slot');
            slotDiv.textContent = 'Available';
            
            // Add click event listener
            slotDiv.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent event bubbling
                if (!this.classList.contains('booked')) {
                    openServiceModal(barberId, time, selectedDate);
                }
            });

            if (isSlotBooked(barberId, selectedDate, time)) {
                slotDiv.classList.add('booked');
                slotDiv.textContent = 'Booked';
            }
        });
    }

    function openServiceModal(barberId, time, date) {
        if (!isLoggedIn) {
            displayMessage('Please login to select services and book.', false);
            return;
        }
        
        const barberRow = document.querySelector(`tr[data-barber-id='${barberId}']`);
        const barberName = barberRow ? barberRow.querySelector('.barber-name-col').textContent : 'Unknown Barber';
        
        currentSelectedSlot = { barberId, barberName, time, date };
        document.getElementById('modalBarberName').textContent = barberName;
        
        // Format the time for display
        const timeObj = new Date(date + 'T' + time);
        const formattedTime = timeObj.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
        });
        document.getElementById('modalSlotTime').textContent = `${date} at ${formattedTime}`;
        
        // Reset checkboxes and modal message
        servicesCheckboxList.querySelectorAll('input[type="radio"]').forEach(cb => cb.checked = false);
        const modalMessage = document.getElementById('modal-message');
        modalMessage.style.display = 'none';
        modalMessage.textContent = '';
        
        // Show the modal
        serviceModal.style.display = 'block';
    }

    closeServiceModalBtn.onclick = function() {
        serviceModal.style.display = 'none';
        currentSelectedSlot = null;
        // Clear modal message when closing
        const modalMessage = document.getElementById('modal-message');
        modalMessage.style.display = 'none';
        modalMessage.textContent = '';
    }

    window.onclick = function(event) {
        if (event.target == serviceModal) {
            serviceModal.style.display = 'none';
            currentSelectedSlot = null;
            // Clear modal message when closing
            const modalMessage = document.getElementById('modal-message');
            modalMessage.style.display = 'none';
            modalMessage.textContent = '';
        }
    }

    addServicesToCartBtn.onclick = function() {
        if (!currentSelectedSlot) return;

        const selectedService = servicesCheckboxList.querySelector('input[type="radio"]:checked');
        const modalMessage = document.getElementById('modal-message');
        
        if (!selectedService) {
            displayModalMessage('Please select a service before adding to selections.', false);
            return;
        }

        const potentialNewCartItem = {
            uniqueId: 'item_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
            barberId: currentSelectedSlot.barberId,
            barberName: currentSelectedSlot.barberName,
            date: currentSelectedSlot.date,
            time: currentSelectedSlot.date + ' ' + currentSelectedSlot.time,
            serviceId: selectedService.value,
            serviceName: selectedService.dataset.name,
            price: parseFloat(selectedService.dataset.price),
            duration: parseInt(selectedService.dataset.duration)
        };

        // Check if there's already an item in the cart for this exact date and time slot
        const existingCartItemForTimeSlot = cart.find(item => item.time === potentialNewCartItem.time);

        if (existingCartItemForTimeSlot) {
            // If it's for the SAME barber, it's an update to the service for this specific slot.
            if (existingCartItemForTimeSlot.barberId === potentialNewCartItem.barberId) {
                // Remove the old item for this specific barber and slot, new one will be added.
                cart = cart.filter(item => item.uniqueId !== existingCartItemForTimeSlot.uniqueId);
            } else {
                // It's for a DIFFERENT barber at the same time. This is a conflict.
                const displayTime = new Date(potentialNewCartItem.time).toLocaleTimeString('en-US', { 
                    hour: 'numeric', 
                    minute: '2-digit',
                    hour12: true 
                });
                serviceModal.style.display = 'none'; // Close the modal
                showFlashMessage(`You already have a service selected for ${displayTime}`, 'error');
                return; // Prevent adding
            }
        }

        // Add the new item (or updated item)
        cart.push(potentialNewCartItem);

        // Update the slot display
        // First, reset any other slot that might have been marked as 'processing' for this time by this user if they changed barber for the same time slot
        // (though the logic above should prevent adding, this is a safeguard for UI consistency if cart was manipulated externally)
        cart.forEach(cartItem => {
            const slotEl = document.querySelector(`.slot[data-barber-id="${cartItem.barberId}"][data-time="${cartItem.time.split(' ')[1]}"]`);
            if (slotEl && cartItem.time.startsWith(currentSelectedSlot.date)) { // ensure it's for current date view
                 if (cartItem.uniqueId === potentialNewCartItem.uniqueId) {
                    slotEl.classList.add('processing');
                    slotEl.textContent = potentialNewCartItem.serviceName;
                 } else if (cartItem.time !== potentialNewCartItem.time) { // if it's a different time, it should retain its state or be 'Available'
                    // if not booked from DB, ensure it's available if no longer in cart
                    if (!isSlotBooked(cartItem.barberId, cartItem.time.split(' ')[0], cartItem.time.split(' ')[1])) {
                         // This part of resetting unrelated slots might be too complex here and better handled by updateGridUI or cart removal logic
                    }
                 }
            }
        });
        // Specifically update the current slot
        const currentSlotElement = document.querySelector(`.slot[data-barber-id="${currentSelectedSlot.barberId}"][data-time="${currentSelectedSlot.time}"]`);
        if (currentSlotElement) {
            // Clear other 'processing' slots for the same time if the user changed barber (already handled by conflict check)
            document.querySelectorAll(`.slot[data-time="${currentSelectedSlot.time}"]`).forEach(s => {
                if (s !== currentSlotElement && s.classList.contains('processing')) {
                    // Check if this other processing slot is still in cart; if not, reset it
                    const inCart = cart.some(ci => ci.barberId === s.dataset.barberId && ci.time === (currentSelectedSlot.date + ' ' + s.dataset.time));
                    if(!inCart && !isSlotBooked(s.dataset.barberId, currentSelectedSlot.date, s.dataset.time)) {
                        s.classList.remove('processing');
                        s.textContent = 'Available';
                    }
                }
            });
            currentSlotElement.classList.add('processing');
            currentSlotElement.textContent = selectedService.dataset.name;
        }
        
        modalMessage.style.display = 'none';
        modalMessage.textContent = '';
        
        updateCartUI();
        serviceModal.style.display = 'none';
        currentSelectedSlot = null;
    }

    function updateCartUI() {
        selectedServicesListUL.innerHTML = '';
        let currentTotal = 0;
        if (cart.length === 0) {
            selectedServicesListUL.innerHTML = '<li>No services selected yet.</li>';
            confirmBookingBtn.disabled = true;
        } else {
            cart.forEach(item => {
                const li = document.createElement('li');
                li.innerHTML = `
                    <div class="service-details">
                        <span class="service-name">${item.serviceName}</span><br>
                        <span class="service-barber">Barber: ${item.barberName}</span><br>
                        <span class="service-time">Time: ${item.date} ${item.time.substring(11,16)}</span>
                    </div>
                    <span>BDT ${item.price.toFixed(2)}</span>
                    <span class="remove-service" data-unique-id="${item.uniqueId}">&times;</span>
                `;
                selectedServicesListUL.appendChild(li);
                currentTotal += item.price;

                li.querySelector('.remove-service').addEventListener('click', function() {
                    removeFromCart(this.dataset.uniqueId);
                });
            });
            confirmBookingBtn.disabled = !isLoggedIn; // Only enable if logged in
        }
        totalAmountDiv.textContent = `Total: BDT ${currentTotal.toFixed(2)}`;
    }
    
    function removeFromCart(uniqueId) {
        const item = cart.find(item => item.uniqueId === uniqueId);
        if (item) {
            // Remove processing state from the slot
            const slotElement = document.querySelector(`.slot[data-barber-id="${item.barberId}"][data-time="${item.time.split(' ')[1]}"]`);
            if (slotElement) {
                slotElement.classList.remove('processing');
                slotElement.textContent = 'Available';
            }
        }
        cart = cart.filter(item => item.uniqueId !== uniqueId);
        updateCartUI();
    }

    bookingDateInput.addEventListener('change', function() {
        // Clear the cart
        cart = [];
        updateCartUI();
        
        // Reset all slot visual states
        document.querySelectorAll('.slot').forEach(slot => {
            slot.classList.remove('processing');
            if (!slot.classList.contains('booked')) {
                slot.textContent = 'Available';
            }
        });
        
        // Show notification
        showFlashMessage('Date changed. Cart has been cleared.', 'success');
        
        // Fetch new booked slots for the selected date
        fetchBookedSlots(this.value);
    });

    // Add flash message function
    function showFlashMessage(message, type = 'success') {
        const flashDiv = document.createElement('div');
        flashDiv.className = `flash-message ${type}`;
        flashDiv.textContent = message;
        
        // Add styles
        flashDiv.style.position = 'fixed';
        flashDiv.style.top = '20px';
        flashDiv.style.right = '20px';
        flashDiv.style.padding = '15px 25px';
        flashDiv.style.borderRadius = '4px';
        flashDiv.style.color = '#fff';
        flashDiv.style.zIndex = '1000';
        flashDiv.style.animation = 'slideIn 0.5s ease-out';
        flashDiv.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
        
        // Set background color based on type
        if (type === 'success') {
            flashDiv.style.backgroundColor = '#4CAF50';
        } else if (type === 'error') {
            flashDiv.style.backgroundColor = '#f44336';
        }
        
        // Add to document
        document.body.appendChild(flashDiv);
        
        // Remove after 5 seconds
        setTimeout(() => {
            flashDiv.style.animation = 'slideOut 0.5s ease-in';
            setTimeout(() => {
                document.body.removeChild(flashDiv);
            }, 500);
        }, 5000);
    }

    // Add keyframe animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    // Update the confirm booking button click handler
    confirmBookingBtn.addEventListener('click', async function() {
        if (cart.length === 0 || !isLoggedIn) {
            showFlashMessage('Please select services and ensure you are logged in.', 'error');
            return;
        }

        // Create a form and submit it to payment.php
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'payment.php';

        // Add cart data as hidden inputs
        cart.forEach((item, index) => {
            for (const key in item) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `services[${index}][${key}]`;
                input.value = item[key];
                form.appendChild(input);
            }
        });

        // Add the form to the document and submit it
        document.body.appendChild(form);
        form.submit();
    });

    // Initial load
    fetchBookedSlots(bookingDateInput.value);
    updateCartUI(); // Initialize cart UI (e.g. "No services")
});
</script>

<?php
    include 'includes/footer.php';
?>
</body>
</html> 