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

// Fetch barber's current information
$barberQuery = "SELECT FirstName, LastName FROM Barbers WHERE UserID = ?";
$barberStmt = $conn->prepare($barberQuery);
$barberStmt->bind_param("i", $barber_id);
$barberStmt->execute();
$barber = $barberStmt->get_result()->fetch_assoc();

// Fetch today's appointments
$todayQuery = "SELECT a.AppointmentID, a.StartTime, a.EndTime, a.Status, s.Name as ServiceName, 
               c.FirstName as CustomerFirstName, c.LastName as CustomerLastName, p.Amount 
              FROM Appointments a 
              JOIN Payments p ON a.PaymentID = p.PaymentID 
              JOIN Customers c ON a.CustomerID = c.UserID 
              JOIN ApptContains ac ON a.AppointmentID = ac.AppointmentID 
              JOIN Services s ON ac.ServiceID = s.ServiceID 
              JOIN BarberHas bh ON a.AppointmentID = bh.AppointmentID 
              WHERE bh.BarberID = ? AND DATE(a.StartTime) = CURDATE() AND a.Status != 'Cancelled' 
              ORDER BY a.StartTime ASC";

$todayStmt = $conn->prepare($todayQuery);
$todayStmt->bind_param("i", $barber_id);
$todayStmt->execute();
$todayResult = $todayStmt->get_result();

// Fetch future appointments
$futureQuery = "SELECT a.AppointmentID, a.StartTime, a.EndTime, a.Status, s.Name as ServiceName, 
               c.FirstName as CustomerFirstName, c.LastName as CustomerLastName, p.Amount
               FROM Appointments a, Services s, Customers c, Payments p, BarberHas bh, ApptContains ac
               WHERE bh.AppointmentID = a.AppointmentID
               AND a.CustomerID = c.UserID
               AND a.PaymentID = p.PaymentID
               AND a.AppointmentID = ac.AppointmentID
               AND ac.ServiceID = s.ServiceID
               AND bh.BarberID = ?
               AND a.Status != 'cancelled'
               AND a.StartTime > CURDATE()
               ORDER BY a.StartTime ASC";

$futureStmt = $conn->prepare($futureQuery);
$futureStmt->bind_param("i", $barber_id);
$futureStmt->execute();
$futureResult = $futureStmt->get_result();

// Fetch recent reviews
$reviewsQuery = "SELECT r.ReviewID, r.Rating, r.Comments, r.CustomerID, 
                c.FirstName as CustomerFirstName, c.LastName as CustomerLastName 
                FROM Reviews r 
                JOIN Customers c ON r.CustomerID = c.UserID 
                WHERE r.BarberID = ? 
                ORDER BY r.ReviewID DESC 
                LIMIT 3";

$reviewsStmt = $conn->prepare($reviewsQuery);
$reviewsStmt->bind_param("i", $barber_id);
$reviewsStmt->execute();
$reviewsResult = $reviewsStmt->get_result();

// Get barber's stats
$statsQuery = "SELECT 
                (SELECT COUNT(*) FROM BarberHas bh JOIN Appointments a ON bh.AppointmentID = a.AppointmentID WHERE bh.BarberID = ? AND a.Status = 'scheduled') as upcomingCount,
                (SELECT COUNT(*) FROM BarberHas bh JOIN Appointments a ON bh.AppointmentID = a.AppointmentID WHERE bh.BarberID = ? AND a.Status = 'completed') as completedCount,
                (SELECT ROUND(AVG(r.Rating), 1) FROM Reviews r WHERE r.BarberID = ?) as avgRating,
                (SELECT COUNT(*) FROM Reviews r WHERE r.BarberID = ?) as reviewCount";

$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param("iiii", $barber_id, $barber_id, $barber_id, $barber_id);
$statsStmt->execute();
$statsResult = $statsStmt->get_result();
$stats = $statsResult->fetch_assoc();

// Define page title
$page_title = "Barber Dashboard";
include '../includes/header.php';
?>

<main>
    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-header">
                <h1 style="font-size: 2.4rem;">Barber Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($barber['FirstName'] . ' ' . $barber['LastName']); ?>!</p>
            </div>
            
            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="overview-content">
                        <h3>Appointments Today</h3>
                        <p class="overview-count"><?php echo $todayResult->num_rows; ?></p>
                    </div>
                </div>
                
                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="overview-content">
                        <h3>Completed Services</h3>
                        <p class="overview-count"><?php echo $stats['completedCount']; ?></p>
                    </div>
                </div>
                
                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="overview-content">
                        <h3>Rating</h3>
                        <p class="overview-count">
                            <?php echo $stats['avgRating'] ? $stats['avgRating'] : 'N/A'; ?>
                            <span class="rating-count">(<?php echo $stats['reviewCount']; ?> reviews)</span>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-content">
                <div class="dashboard-sidebar">
                    <div class="dashboard-nav">
                        <a href="dashboard.php" class="nav-item active">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="reviews.php" class="nav-item">
                            <i class="fas fa-star"></i>
                            <span>Reviews</span>
                        </a>
                        <a href="profile.php" class="nav-item">
                            <i class="fas fa-user"></i>
                            <span>My Profile</span>
                        </a>
                    </div>
                    
                    <div class="day-summary">
                        <h3>Today's Summary</h3>
                        
                        <?php if ($todayResult->num_rows > 0): ?>
                            <div class="time-slots">
                                <?php
                                // Working hours: 9 AM to 6 PM
                                $workingHours = [];
                                for ($i = 9; $i <= 18; $i++) {
                                    $workingHours[] = sprintf('%02d:00', $i);
                                    if ($i < 18) { // Don't add 18:30 as it's past closing
                                        $workingHours[] = sprintf('%02d:30', $i);
                                    }
                                }
                                
                                // Reset pointer to beginning of result set
                                $todayResult->data_seek(0);
                                
                                $appointments = [];
                                while ($appt = $todayResult->fetch_assoc()) {
                                    $startTime = date('H:i', strtotime($appt['StartTime']));
                                    $endTime = date('H:i', strtotime($appt['EndTime']));
                                    $appointments[$startTime] = [
                                        'id' => $appt['AppointmentID'],
                                        'customer' => $appt['CustomerFirstName'] . ' ' . $appt['CustomerLastName'],
                                        'service' => $appt['ServiceName'],
                                        'endTime' => $endTime,
                                        'status' => $appt['Status']
                                    ];
                                }
                                
                                $currentTime = date('H:i');
                                
                                foreach ($workingHours as $time):
                                    $timeClass = 'time-slot';
                                    $slotContent = '';
                                    
                                    // Is this time in the past?
                                    if ($time < $currentTime) {
                                        $timeClass .= ' past';
                                    }
                                    
                                    // Is there an appointment at this time?
                                    if (isset($appointments[$time])) {
                                        $appt = $appointments[$time];
                                        $timeClass .= ' booked';
                                        if ($appt['status'] === 'completed') {
                                            $timeClass .= ' completed';
                                        }
                                        

                                        $slotContent .= '<span class="slot-customer">' . htmlspecialchars($appt['customer']) . '</span>';
                                        $slotContent .= '<span class="slot-service">' . htmlspecialchars($appt['service']) . '</span>';
                                        $slotContent .= '</a>';
                                    }
                                    
                                    // Is this time part of an ongoing appointment?
                                    foreach ($appointments as $startTime => $appt) {
                                        if ($time > $startTime && $time < $appt['endTime'] && !isset($appointments[$time])) {
                                            $timeClass .= ' ongoing';
                                            break;
                                        }
                                    }
                                ?>
                                    <div class="<?php echo $timeClass; ?>">
                                        <span class="slot-time"><?php echo date('g:i A', strtotime("2000-01-01 $time")); ?></span>
                                        <?php echo $slotContent; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                        <?php else: ?>
                            <div class="empty-schedule">
                                <p>You have no appointments scheduled for today.</p>
                                <p class="day-off">Enjoy your day off!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="dashboard-main">
                    <div class="section-card">
                        <div class="card-header">
                            <h2>Today's Appointments</h2>
                            <a href="appointments.php?date=today" class="view-all">View All</a>
                        </div>
                        
                        <div class="card-content">
                            <?php
                            // Reset pointer to beginning of result set
                            $todayResult->data_seek(0);
                            
                            if ($todayResult->num_rows > 0):
                            ?>
                                <div class="appointments-list">
                                    <?php while ($appointment = $todayResult->fetch_assoc()): ?>
                                        <div class="appointment-card">
                                            
                                            
                                            <div class="appointment-details">
                                                <h3><?php echo htmlspecialchars($appointment['ServiceName']); ?></h3>
                                                <p class="customer-name">
                                                    <i class="fas fa-user"></i>
                                                    <?php echo htmlspecialchars($appointment['CustomerFirstName'] . ' ' . $appointment['CustomerLastName']); ?>
                                                </p>
                                                <div class="appointment-status">
                                                    <span class="status-badge status-<?php echo strtolower($appointment['Status']); ?>">
                                                        <?php echo ucfirst($appointment['Status']); ?>
                                                    </span>
                                                    <span class="price">BDT <?php echo $appointment['Amount']; ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="appointment-actions">
                                                <form method="POST" action="cancel-appointment.php" 
                                                      onsubmit="return handleCancelAppointment(this);">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['AppointmentID']; ?>">
                                                    <button type="submit" name="cancel_appointment" class="btn btn-danger btn-sm">Cancel</button>
                                                </form>
                                                <?php if ($appointment['Status'] === 'Scheduled'): ?>
                                                    <a href="mark-completed.php?id=<?php echo $appointment['AppointmentID']; ?>" class="btn btn-primary btn-sm">Complete</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <h3>No Appointments Today</h3>
                                    <p>You don't have any appointments scheduled for today.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Upcoming Appointments -->
                    <div class="section-card">
                        <div class="card-header">
                            <h2>Upcoming Appointments</h2>
                            <a href="appointments.php?date=upcoming" class="view-all">View All</a>
                        </div>
                        
                        <div class="card-content">
                            <?php
                            // Reset pointer to beginning of result set
                            $futureResult->data_seek(0);
                            
                            if ($futureResult->num_rows > 0):
                            ?>
                                <div class="appointments-list">
                                    <?php while ($appointment = $futureResult->fetch_assoc()): ?>
                                        <div class="appointment-card">
                                            <div class="appointment-time">
                                                <div class="date-display">
                                                    <span class="month"><?php echo date('M', strtotime($appointment['StartTime'])); ?></span>
                                                    <span class="day"><?php echo date('d', strtotime($appointment['StartTime'])); ?></span>
                                                </div>
                                                <span class="time"><?php echo date('g:i A', strtotime($appointment['StartTime'])); ?></span>
                                                <span class="duration">
                                                    <?php
                                                    $start = new DateTime($appointment['StartTime']);
                                                    $end = new DateTime($appointment['EndTime']);
                                                    $duration = $start->diff($end);
                                                    echo $duration->format('%h hr %i min');
                                                    ?>
                                                </span>
                                            </div>
                                            
                                            <div class="appointment-details">
                                                <h3><?php echo htmlspecialchars($appointment['ServiceName']); ?></h3>
                                                <p class="customer-name">
                                                    <i class="fas fa-user"></i>
                                                    <?php echo htmlspecialchars($appointment['CustomerFirstName'] . ' ' . $appointment['CustomerLastName']); ?>
                                                </p>
                                                <div class="appointment-status">
                                                    <span class="status-badge status-<?php echo strtolower($appointment['Status']); ?>">
                                                        <?php echo ucfirst($appointment['Status']); ?>
                                                    </span>
                                                    <span class="price">BDT <?php echo $appointment['Amount']; ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="appointment-actions">
                                                <form method="POST" action="cancel-appointment.php" 
                                                      onsubmit="return handleCancelAppointment(this);">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['AppointmentID']; ?>">
                                                    <button type="submit" name="cancel_appointment" class="btn btn-danger btn-sm">Cancel</button>
                                                </form>
                                                <?php if ($appointment['Status'] === 'scheduled'): ?>
                                                    <a href="mark-completed.php?id=<?php echo $appointment['AppointmentID']; ?>" class="btn btn-primary btn-sm">Complete</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <h3>No Appointments Today</h3>
                                    <p>You don't have any appointments scheduled for today.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
      
                    <div class="section-card">
                        <div class="card-header">
                            <h2>Recent Reviews</h2>
                            <a href="reviews.php" class="view-all">View All</a>
                        </div>
                        
                        <div class="card-content">
                            <?php if ($reviewsResult->num_rows > 0): ?>
                                <div class="reviews-list">
                                    <?php while ($review = $reviewsResult->fetch_assoc()): ?>
                                        <div class="review-card">
                                            <div class="review-header">
                                                <div class="customer-info">
                                                    <div class="customer-avatar">
                                                        <?php
                                                        $initials = strtoupper(substr($review['CustomerFirstName'], 0, 1) . substr($review['CustomerLastName'], 0, 1));
                                                        echo $initials;
                                                        ?>
                                                    </div>
                                                    <div class="customer-name">
                                                        <h4><?php echo htmlspecialchars($review['CustomerFirstName'] . ' ' . $review['CustomerLastName']); ?></h4>
                                                    </div>
                                                </div>
                                                <div class="review-rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?php if ($i <= $review['Rating']): ?>
                                                            <i class="fas fa-star"></i>
                                                        <?php else: ?>
                                                            <i class="far fa-star"></i>
                                                        <?php endif; ?>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <div class="review-body">
                                                <p><?php echo htmlspecialchars($review['Comments']); ?></p>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <h3>No Reviews Yet</h3>
                                    <p>You haven't received any reviews from customers yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
include '../includes/footer.php';
?>

<script>
function handleCancelAppointment(form) {
    if (confirm('Are you sure you want to cancel this appointment?')) {
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showMessage(data.message, 'success');
                // Reload the page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Show error message
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage('An error occurred while cancelling the appointment.', 'error');
        });
        
        return false; // Prevent form submission
    }
    return false; // Prevent form submission if not confirmed
}

function showMessage(message, type) {
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    messageDiv.style.position = 'fixed';
    messageDiv.style.top = '20px';
    messageDiv.style.right = '20px';
    messageDiv.style.zIndex = '9999';
    messageDiv.style.minWidth = '300px';
    messageDiv.style.textAlign = 'center';
    
    // Add message content
    messageDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to page
    document.body.appendChild(messageDiv);
    
    // Remove after 3 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 3000);
}
</script>

<style>
/* Dashboard Styles */
.dashboard-section {
    padding: 2rem 0;
    margin-top: 80px;
    min-height: calc(100vh - 80px);
    background-color: #f5f7fa;
}

.dashboard-header {
    margin-bottom: 2rem;
    padding: 0 1rem;
}

.dashboard-header h1 {
    margin-bottom: 0.5rem;
    font-size: 2.4rem;
}

.dashboard-header p {
    color: #666;
    font-size: 1.6rem;
}

.dashboard-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding: 0 1rem;
}

.overview-card {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    transition: transform 0.2s ease;
}

.overview-card:hover {
    transform: translateY(-2px);
}

.overview-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: rgba(26, 54, 93, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1.5rem;
}

.overview-icon i {
    font-size: 2rem;
    color: #1a365d;
}

.overview-content h3 {
    margin-bottom: 4px;
    font-size: 1.6rem;
    color: #333;
}

.overview-count {
    font-size: 2rem;
    font-weight: 600;
    color: #1a365d;
    margin: 0;
}

.rating-count {
    font-size: 1.4rem;
    font-weight: normal;
    color: #666;
}

.dashboard-content {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 2rem;
    padding: 0 1rem;
    max-width: 1400px;
    margin: 0 auto;
}

.dashboard-sidebar {
    position: sticky;
    top: 80px;
    height: fit-content;
}

.dashboard-nav {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-bottom: 1.5rem;
}

.nav-item {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    color: #333;
    border-left: 3px solid transparent;
    transition: all 0.2s ease;
    text-decoration: none;
    font-size: 1.4rem;
}

.nav-item i {
    margin-right: 1rem;
    width: 20px;
    text-align: center;
    color: #666;
    transition: color 0.2s ease;
}

.nav-item:hover {
    background-color: rgba(0, 0, 0, 0.03);
    color: #1a365d;
}

.nav-item:hover i {
    color: #1a365d;
}

.nav-item.active {
    border-left-color: #1a365d;
    background-color: rgba(26, 54, 93, 0.05);
    color: #1a365d;
}

.nav-item.active i {
    color: #1a365d;
}

.day-summary {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 1.5rem;
}

.day-summary h3 {
    margin-bottom: 1rem;
    font-size: 1.8rem;
}

.time-slots {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.time-slot {
    display: flex;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e2e8f0;
    transition: background-color 0.2s ease;
}

.time-slot:last-child {
    border-bottom: none;
}

.time-slot.past {
    opacity: 0.6;
}

.time-slot.booked {
    background-color: rgba(26, 54, 93, 0.05);
}

.time-slot.ongoing {
    background-color: rgba(26, 54, 93, 0.05);
}

.time-slot.completed {
    background-color: rgba(67, 160, 71, 0.05);
}

.slot-time {
    min-width: 80px;
    font-weight: 500;
}

.slot-details {
    flex: 1;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
}

.slot-customer {
    font-weight: 500;
}

.slot-service {
    font-size: 1.4rem;
    color: #666;
}

.empty-schedule {
    text-align: center;
    padding: 2rem 0;
}

.day-off {
    color: #1a365d;
    font-weight: 500;
    font-size: 1.6rem;
}

.section-card {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    overflow: hidden;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.card-header h2 {
    margin: 0;
    font-size: 1.8rem;
}

.view-all {
    color: #1a365d;
    font-weight: 500;
    text-decoration: none;
}

.card-content {
    padding: 1.5rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 0;
}

.empty-state-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: rgba(26, 54, 93, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}

.empty-state-icon i {
    font-size: 2.4rem;
    color: #1a365d;
}

.empty-state h3 {
    margin-bottom: 0.5rem;
    font-size: 1.8rem;
}

.empty-state p {
    color: #666;
    margin-bottom: 1.5rem;
}

.appointments-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.appointment-card {
    display: flex;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.appointment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.appointment-time {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: #1a365d;
    color: white;
    padding: 1rem;
    min-width: 120px;
    text-align: center;
}

.date-display {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 4px;
}

.month {
    font-size: 1.4rem;
    text-transform: uppercase;
    font-weight: 500;
}

.day {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
}

.appointment-time .time {
    font-size: 1.6rem;
    font-weight: 600;
    margin: 4px 0;
}

.appointment-time .duration {
    font-size: 1.2rem;
    opacity: 0.8;
}

.appointment-details {
    flex: 1;
    padding: 1rem;
}

.appointment-details h3 {
    margin-bottom: 4px;
    font-size: 1.6rem;
}

.customer-name {
    margin-bottom: 4px;
    color: #666;
}

.customer-name i {
    width: 20px;
    text-align: center;
    margin-right: 4px;
}

.appointment-status {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 1.2rem;
    font-weight: 500;
}

.status-scheduled {
    background-color: rgba(92, 158, 173, 0.1);
    color: #5c9ead;
}

.status-completed {
    background-color: rgba(67, 160, 71, 0.1);
    color: #43a047;
}

.status-cancelled {
    background-color: rgba(229, 57, 53, 0.1);
    color: #e53935;
}

.price {
    font-weight: 600;
    color: #1a365d;
}

.appointment-actions {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 8px;
    padding: 1rem;
    background-color: #f8fafc;
}

.btn-sm {
    font-size: 1.4rem;
    padding: 6px 12px;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.review-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.5rem;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.customer-info {
    display: flex;
    align-items: center;
}

.customer-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #1a365d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-right: 1rem;
}

.customer-name h4 {
    margin: 0;
    font-size: 1.6rem;
}

.review-rating {
    color: #f59e0b;
}

.review-body p {
    margin: 0;
    color: #333;
    line-height: 1.5;
}

@media (max-width: 1200px) {
    .dashboard-content {
        grid-template-columns: 250px 1fr;
    }
}

@media (max-width: 991px) {
    .dashboard-content {
        grid-template-columns: 1fr;
    }
    
    .dashboard-sidebar {
        position: static;
        width: 100%;
    }
    
    .dashboard-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.5rem;
    }
    
    .nav-item {
        flex: 1;
        min-width: 120px;
        justify-content: center;
        text-align: center;
        border-left: none;
        border-bottom: 2px solid transparent;
        padding: 0.8rem 1rem;
    }
    
    .nav-item.active {
        border-left-color: transparent;
        border-bottom-color: #1a365d;
    }
    
    .nav-item i {
        margin-right: 0.5rem;
    }
    
    .day-summary {
        margin-top: 1.5rem;
    }
}

@media (max-width: 768px) {
    .dashboard-overview {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .appointment-card {
        flex-direction: column;
    }
    
    .appointment-time {
        flex-direction: row;
        justify-content: space-between;
        width: 100%;
        padding: 1rem 1.5rem;
    }
    
    .appointment-actions {
        flex-direction: row;
        justify-content: flex-end;
        gap: 0.5rem;
    }
}

@media (max-width: 576px) {
    .dashboard-overview {
        grid-template-columns: 1fr;
    }
    
    .dashboard-nav {
        flex-direction: column;
        padding: 0;
    }
    
    .nav-item {
        width: 100%;
        justify-content: flex-start;
        text-align: left;
        border-left: 3px solid transparent;
        border-bottom: none;
        padding: 1rem 1.5rem;
    }
    
    .nav-item.active {
        border-left-color: #1a365d;
        border-bottom-color: transparent;
    }
    
    .dashboard-header h1 {
        font-size: 2rem;
    }
    
    .dashboard-header p {
        font-size: 1.4rem;
    }
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.alert-dismissible {
    padding-right: 40px;
}

.btn-close {
    position: absolute;
    top: 0;
    right: 0;
    padding: 15px;
    background: transparent;
    border: 0;
    cursor: pointer;
}


</style>