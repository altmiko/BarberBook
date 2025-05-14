<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in as a customer
if (!isLoggedIn() || !isCustomer()) {
    setFlashMessage('You must be logged in as a customer to access this page.', 'error');
    redirect('../login.php');
}

$customer_id = $_SESSION['user_id'];

// Fetch customer's current name
$nameQuery = "SELECT FirstName, LastName FROM Customers WHERE UserID = ?";
$nameStmt = $conn->prepare($nameQuery);
$nameStmt->bind_param("i", $customer_id);
$nameStmt->execute();
$customerName = $nameStmt->get_result()->fetch_assoc();

// Fetch today's appointments
$todayQuery = "SELECT a.AppointmentID, a.StartTime, a.EndTime, a.Status, s.Name as ServiceName, 
                b.FirstName as BarberFirstName, b.LastName as BarberLastName, p.Amount, p.PayMethod, p.PayStatus
                FROM Appointments a 
                JOIN Payments p ON a.PaymentID = p.PaymentID 
                JOIN ApptContains ac ON a.AppointmentID = ac.AppointmentID 
                JOIN Services s ON ac.ServiceID = s.ServiceID 
                JOIN BarberHas bh ON a.AppointmentID = bh.AppointmentID 
                JOIN Barbers b ON bh.BarberID = b.UserID 
                WHERE a.CustomerID = ? AND DATE(a.StartTime) = CURDATE() AND a.Status != 'cancelled' 
                ORDER BY a.StartTime ASC 
                LIMIT 5";

$todayStmt = $conn->prepare($todayQuery);
$todayStmt->bind_param("i", $customer_id);
$todayStmt->execute();
$todayResult = $todayStmt->get_result();


// Fetch upcoming appointments
$upcomingQuery = "SELECT a.AppointmentID, a.StartTime, a.EndTime, a.Status, s.Name as ServiceName, 
                   b.FirstName as BarberFirstName, b.LastName as BarberLastName, p.Amount, p.PayMethod, p.PayStatus
                  FROM Appointments a 
                  JOIN Payments p ON a.PaymentID = p.PaymentID 
                  JOIN ApptContains ac ON a.AppointmentID = ac.AppointmentID 
                  JOIN Services s ON ac.ServiceID = s.ServiceID 
                  JOIN BarberHas bh ON a.AppointmentID = bh.AppointmentID 
                  JOIN Barbers b ON bh.BarberID = b.UserID 
                  WHERE a.CustomerID = ? AND DATE(a.StartTime) > CURDATE() AND a.Status != 'cancelled' 
                  ORDER BY a.StartTime ASC 
                  LIMIT 5";

$upcomingStmt = $conn->prepare($upcomingQuery);
$upcomingStmt->bind_param("i", $customer_id);
$upcomingStmt->execute();
$upcomingResult = $upcomingStmt->get_result();

// Fetch recent past appointments
$pastQuery = "SELECT a.AppointmentID, a.StartTime, a.EndTime, a.Status, s.Name as ServiceName, 
              b.FirstName as BarberFirstName, b.LastName as BarberLastName, p.Amount, p.PayMethod, p.PayStatus,
              r.ReviewID, r.Rating
              FROM Appointments a 
              JOIN Payments p ON a.PaymentID = p.PaymentID 
              JOIN ApptContains ac ON a.AppointmentID = ac.AppointmentID 
              JOIN Services s ON ac.ServiceID = s.ServiceID 
              JOIN BarberHas bh ON a.AppointmentID = bh.AppointmentID 
              JOIN Barbers b ON bh.BarberID = b.UserID 
              LEFT JOIN Reviews r ON r.BarberID = b.UserID AND r.CustomerID = a.CustomerID
              WHERE a.CustomerID = ? AND a.EndTime < NOW() 
              ORDER BY a.EndTime DESC 
              LIMIT 5";

$pastStmt = $conn->prepare($pastQuery);
$pastStmt->bind_param("i", $customer_id);
$pastStmt->execute();
$pastResult = $pastStmt->get_result();

// Define page title
$page_title = "Customer Dashboard";
include '../includes/header.php';
?>

<main>
    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-header">
                <h1>Customer Dashboard</h1>
                <p>Welcome back, <?php echo htmlspecialchars($customerName['FirstName'] . ' ' . $customerName['LastName']); ?>!</p>
            </div>
            
            <div class="dashboard-overview">
                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="overview-content">
                        <h3>Upcoming Appointments</h3>
                        <p class="overview-count"><?php echo $upcomingResult->num_rows; ?></p>
                    </div>
                </div>
                
                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="overview-content">
                        <h3>Past Appointments</h3>
                        <?php 
                        // Count total past appointments
                        $pastCountQuery = "SELECT COUNT(*) as count FROM Appointments WHERE CustomerID = ? AND EndTime < NOW()";
                        $pastCountStmt = $conn->prepare($pastCountQuery);
                        $pastCountStmt->bind_param("i", $customer_id);
                        $pastCountStmt->execute();
                        $pastCountResult = $pastCountStmt->get_result();
                        $pastCount = $pastCountResult->fetch_assoc()['count'];
                        ?>
                        <p class="overview-count"><?php echo $pastCount; ?></p>
                    </div>
                </div>
                
                <div class="overview-card">
                    <div class="overview-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="overview-content">
                        <h3>Notifications</h3>
                        <?php
                        // Count unread notifications
                        $unreadQuery = "SELECT COUNT(*) as count FROM Notifications WHERE CustomerID = ? AND Status = ''";
                        $unreadStmt = $conn->prepare($unreadQuery);
                        $unreadStmt->bind_param("i", $customer_id);
                        $unreadStmt->execute();
                        $unreadResult = $unreadStmt->get_result();
                        $unreadCount = $unreadResult->fetch_assoc()['count'];
                        ?>
                        <p class="overview-count"><?php echo $unreadCount; ?> unread</p>
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
                        <a href="appointments.php" class="nav-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>My Appointments</span>
                        </a>
                        <a href="reviews.php" class="nav-item">
                            <i class="fas fa-star"></i>
                            <span>My Reviews</span>
                        </a>
                        <a href="notifications.php" class="nav-item">
                            <i class="fas fa-envelope"></i>
                            <span>Email Notifications</span>
                        </a>
                        <a href="profile.php" class="nav-item">
                            <i class="fas fa-user"></i>
                            <span>My Profile</span>
                        </a>
                    </div>
                    
                    <div class="quick-book">
                        <h3>Need a quick appointment?</h3>
                        <p>Book your next haircut in just a few clicks.</p>
                        <a href="../booking.php" class="btn btn-primary btn-block">Book Now</a>
                    </div>
                </div>
                
                <div class="dashboard-main">
                <div class="section-card">
                        <div class="card-header">
                            <h2>Today's Appointments</h2>
                            <a href="appointments.php" class="view-all">View All</a>
                        </div>
                        
                        <div class="card-content">
                            <?php if ($todayResult->num_rows > 0): ?>
                                <div class="appointments-list">
                                    <?php while ($appointment = $todayResult->fetch_assoc()): ?>
                                        <div class="appointment-card">
                                            <div class="appointment-date">
                                                <div class="date-display">
                                                    <span class="month"><?php echo date('M', strtotime($appointment['StartTime'])); ?></span>
                                                    <span class="day"><?php echo date('d', strtotime($appointment['StartTime'])); ?></span>
                                                </div>
                                                <span class="time"><?php echo date('g:i A', strtotime($appointment['StartTime'])); ?></span>
                                            </div>
                                            
                                            <div class="appointment-details">
                                                <h3><?php echo htmlspecialchars($appointment['ServiceName']); ?></h3>
                                                <p>
                                                    <i class="fas fa-user-alt"></i> 
                                                    <?php echo htmlspecialchars($appointment['BarberFirstName'] . ' ' . $appointment['BarberLastName']); ?>
                                                </p>
                                                <p>
                                                    <i class="fas fa-clock"></i>
                                                    <?php
                                                    $start = new DateTime($appointment['StartTime']);
                                                    $end = new DateTime($appointment['EndTime']);
                                                    $duration = $start->diff($end);
                                                    echo $duration->format('%h hr %i min');
                                                    ?>
                                                </p>
                                                <div class="appointment-status">
                                                    <div class="status-info">
                                                        <span class="status-badge status-<?php echo strtolower($appointment['Status']); ?>">
                                                            <?php echo ucfirst($appointment['Status']); ?>
                                                        </span>
                                                        <span class="payment-info">
                                                            <i class="fas fa-credit-card"></i>
                                                            <?php echo $appointment['PayMethod']; ?>
                                                                <?php if (!empty($appointment['TransactionID'])): ?>
                                                                    <span class="transaction-id">#<?php echo htmlspecialchars($appointment['TransactionID']); ?></span>
                                                                <?php endif; ?>
                                                        
                                                        </span>
                                                    </div>
                                                    <span class="price">BDT <?php echo $appointment['Amount']; ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="appointment-actions">
                                                <?php if ($appointment['Status'] !== 'Cancelled' && $appointment['Status'] !== 'Completed'): ?>
                                                    <a href="javascript:void(0)" onclick="confirmCancel(<?php echo $appointment['AppointmentID']; ?>, '<?php echo htmlspecialchars($appointment['ServiceName']); ?>', '<?php echo date('g:i A', strtotime($appointment['StartTime'])); ?>')" class="btn btn-outline btn-sm text-error cancel-btn">Cancel</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <h3>No Upcoming Appointments</h3>
                                    <p>You don't have any appointments scheduled.</p>
                                    <a href="../booking.php" class="btn btn-primary">Book Now</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>



                        <div class="section-card">
                        <div class="card-header">
                            <h2>Upcoming Appointments</h2>
                            <a href="appointments.php" class="view-all">View All</a>
                        </div>
                        
                        <div class="card-content">
                            <?php if ($upcomingResult->num_rows > 0): ?>
                                <div class="appointments-list">
                                    <?php while ($appointment = $upcomingResult->fetch_assoc()): ?>
                                        <div class="appointment-card">
                                            <div class="appointment-date">
                                                <div class="date-display">
                                                    <span class="month"><?php echo date('M', strtotime($appointment['StartTime'])); ?></span>
                                                    <span class="day"><?php echo date('d', strtotime($appointment['StartTime'])); ?></span>
                                                </div>
                                                <span class="time"><?php echo date('g:i A', strtotime($appointment['StartTime'])); ?></span>
                                            </div>
                                            
                                            <div class="appointment-details">
                                                <h3><?php echo htmlspecialchars($appointment['ServiceName']); ?></h3>
                                                <p>
                                                    <i class="fas fa-user-alt"></i> 
                                                    <?php echo htmlspecialchars($appointment['BarberFirstName'] . ' ' . $appointment['BarberLastName']); ?>
                                                </p>
                                                <p>
                                                    <i class="fas fa-clock"></i>
                                                    <?php
                                                    $start = new DateTime($appointment['StartTime']);
                                                    $end = new DateTime($appointment['EndTime']);
                                                    $duration = $start->diff($end);
                                                    echo $duration->format('%h hr %i min');
                                                    ?>
                                                </p>
                                                <div class="appointment-status">
                                                    <div class="status-info">
                                                        <span class="status-badge status-<?php echo strtolower($appointment['Status']); ?>">
                                                            <?php echo ucfirst($appointment['Status']); ?>
                                                        </span>
                                                        <span class="payment-info">
                                                            <i class="fas fa-credit-card"></i>
                                                            <?php echo $appointment['PayMethod']; ?>
                                                            <?php if (!empty($appointment['TransactionID'])): ?>
                                                                <?php if (!empty($appointment['TransactionID'])): ?>
                                                                    <span class="transaction-id">#<?php echo htmlspecialchars($appointment['TransactionID']); ?></span>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                    <span class="price">BDT <?php echo $appointment['Amount']; ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="appointment-actions">
                                                <?php if ($appointment['Status'] !== 'Cancelled'): ?>
                                                    <a href="javascript:void(0)" onclick="confirmCancel(<?php echo $appointment['AppointmentID']; ?>, '<?php echo htmlspecialchars($appointment['ServiceName']); ?>', '<?php echo date('g:i A', strtotime($appointment['StartTime'])); ?>')" class="btn btn-outline btn-sm text-error cancel-btn">Cancel</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <h3>No Upcoming Appointments</h3>
                                    <p>You don't have any appointments scheduled.</p>
                                    <a href="../booking.php" class="btn btn-primary">Book Now</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="section-card">
                        <div class="card-header">
                            <h2>Recent Appointments</h2>
                            <a href="appointments.php?tab=past" class="view-all">View All</a>
                        </div>
                        
                        <div class="card-content">
                            <?php if ($pastResult->num_rows > 0): ?>
                                <div class="appointments-list">
                                    <?php while ($appointment = $pastResult->fetch_assoc()): ?>
                                        <div class="appointment-card">
                                            <div class="appointment-date">
                                                <div class="date-display">
                                                    <span class="month"><?php echo date('M', strtotime($appointment['StartTime'])); ?></span>
                                                    <span class="day"><?php echo date('d', strtotime($appointment['StartTime'])); ?></span>
                                                </div>
                                                <span class="time"><?php echo date('g:i A', strtotime($appointment['StartTime'])); ?></span>
                                            </div>
                                            
                                            <div class="appointment-details">
                                                <h3><?php echo htmlspecialchars($appointment['ServiceName']); ?></h3>
                                                <p>
                                                    <i class="fas fa-user-alt"></i> 
                                                    <?php echo htmlspecialchars($appointment['BarberFirstName'] . ' ' . $appointment['BarberLastName']); ?>
                                                </p>
                                                
                                                <div class="appointment-status">
                                                    <div class="status-info">
                                                        <span class="status-badge status-<?php echo strtolower($appointment['Status']); ?>">
                                                            <?php echo ucfirst($appointment['Status']); ?>
                                                        </span>
                                                        <span class="payment-info">
                                                            <i class="fas fa-credit-card"></i>
                                                            <?php echo $appointment['PayMethod']; ?>
                                                            <?php if (!empty($appointment['TransactionID'])): ?>
                                                                <?php if (!empty($appointment['TransactionID'])): ?>
                                                                    <span class="transaction-id">#<?php echo htmlspecialchars($appointment['TransactionID']); ?></span>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                    <span class="price">BDT <?php echo $appointment['Amount']; ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="appointment-actions">
                                                <?php if ($appointment['Status'] !== 'Cancelled'): ?>
                                                    <a href="javascript:void(0)" onclick="confirmCancel(<?php echo $appointment['AppointmentID']; ?>, '<?php echo htmlspecialchars($appointment['ServiceName']); ?>', '<?php echo date('g:i A', strtotime($appointment['StartTime'])); ?>')" class="btn btn-outline btn-sm text-error cancel-btn">Cancel</a>
                                                <?php endif; ?>
                                                
                                                <?php if (empty($appointment['ReviewID']) && $appointment['Status'] === 'Completed'): ?>
                                                    <a href="leave-review.php?barber=<?php echo $appointment['BarberFirstName'] . ' ' . $appointment['BarberLastName']; ?>&appointment=<?php echo $appointment['AppointmentID']; ?>" class="btn btn-secondary btn-sm">Leave Review</a>
                                                <?php elseif (!empty($appointment['ReviewID'])): ?>
                                                    <div class="review-rating">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <?php if ($i <= $appointment['Rating']): ?>
                                                                <i class="fas fa-star"></i>
                                                            <?php else: ?>
                                                                <i class="far fa-star"></i>
                                                            <?php endif; ?>
                                                        <?php endfor; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <h3>No Past Appointments</h3>
                                    <p>You haven't had any appointments yet.</p>
                                    <a href="../booking.php" class="btn btn-primary">Book Your First Appointment</a>
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
// Helper function to format time elapsed
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // DateInterval doesn't have a weeks property, so we'll calculate it
    // and modify the days property accordingly in our working array
    $string = array(
        'y' => 'year',
        'm' => 'month',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    // Calculate weeks from days and adjust remaining days
    $weeks = floor($diff->d / 7);
    $days_remainder = $diff->d % 7;
    
    // Only include weeks if there are any
    if ($weeks > 0) {
        $string['w'] = $weeks . ' week' . ($weeks > 1 ? 's' : '');
        // Adjust the days to only show remainder
        if ($days_remainder > 0) {
            $string['d'] = $days_remainder . ' day' . ($days_remainder > 1 ? 's' : '');
        } else {
            unset($string['d']);
        }
    } else {
        // No weeks, just use days as is
        if ($diff->d > 0) {
            $string['d'] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
        } else {
            unset($string['d']);
        }
    }
    
    // Handle remaining time units
    if ($diff->y > 0) {
        $string['y'] = $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
    } else {
        unset($string['y']);
    }
    
    if ($diff->m > 0) {
        $string['m'] = $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
    } else {
        unset($string['m']);
    }
    
    if ($diff->h > 0) {
        $string['h'] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
    } else {
        unset($string['h']);
    }
    
    if ($diff->i > 0) {
        $string['i'] = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
    } else {
        unset($string['i']);
    }
    
    if ($diff->s > 0) {
        $string['s'] = $diff->s . ' second' . ($diff->s > 1 ? 's' : '');
    } else {
        unset($string['s']);
    }

    // Sort the array by key to maintain time unit order
    ksort($string);
    
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

include '../includes/footer.php';
?>

<script>
function confirmCancel(appointmentId, serviceName, time) {
    if (confirm(`Are you sure you want to cancel your ${serviceName} appointment at ${time}?`)) {
        window.location.href = `cancel-appointment.php?id=${appointmentId}`;
    }
}
</script>

<style>
/* Dashboard Styles */
.dashboard-section {
    padding: var(--space-4) 0;
    margin-top: 80px;
}

.dashboard-header {
    margin-bottom: var(--space-4);
}

.dashboard-header h1 {
    margin-bottom: var(--space-1);
}

.dashboard-header p {
    color: var(--color-text-light);
    font-size: 1.8rem;
}

.dashboard-overview {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--space-3);
    margin-bottom: var(--space-4);
}

.overview-card {
    background-color: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    padding: var(--space-3);
    display: flex;
    align-items: center;
}

.overview-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: rgba(26, 54, 93, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: var(--space-3);
}

.overview-icon i {
    font-size: 2.4rem;
    color: var(--color-primary);
}

.overview-content h3 {
    margin-bottom: 4px;
    font-size: 1.8rem;
}

.overview-count {
    font-size: 2.4rem;
    font-weight: 600;
    color: var(--color-primary);
    margin: 0;
}

.dashboard-content {
    display: flex;
    gap: var(--space-4);
}

.dashboard-sidebar {
    width: 300px;
    flex-shrink: 0;
}

.dashboard-main {
    flex: 1;
}

.dashboard-nav {
    background-color: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    margin-bottom: var(--space-3);
}

.nav-item {
    display: flex;
    align-items: center;
    padding: var(--space-2) var(--space-3);
    color: var(--color-text);
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
    position: relative;
}

.nav-item i {
    margin-right: var(--space-2);
    width: 20px;
    text-align: center;
    color: var(--color-text-light);
    transition: color 0.3s ease;
}

.nav-item:hover {
    background-color: rgba(0, 0, 0, 0.03);
    color: var(--color-primary);
}

.nav-item:hover i {
    color: var(--color-primary);
}

.nav-item.active {
    border-left-color: var(--color-primary);
    background-color: rgba(26, 54, 93, 0.05);
    color: var(--color-primary);
}

.nav-item.active i {
    color: var(--color-primary);
}

.badge {
    background-color: var(--color-error);
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-left: auto;
}

.quick-book {
    background-color: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    padding: var(--space-3);
}

.quick-book h3 {
    margin-bottom: var(--space-1);
}

.quick-book p {
    margin-bottom: var(--space-2);
    color: var(--color-text-light);
}

.btn-block {
    display: block;
    width: 100%;
}

.btn-outline {
    background-color: transparent;
    color: var(--color-primary);
    transition: all 0.2s ease;
}

.cancel-btn {
    background-color: transparent;
    border: 2px solid red;
    transition: all 0.2s ease;
}

.btn-outline:hover {
    background-color: red;
    color: white;
    border-color: red;
}

.section-card {
    background-color: white;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    margin-bottom: var(--space-4);
    overflow: hidden;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-3);
    border-bottom: 1px solid var(--color-border);
}

.card-header h2 {
    margin: 0;
    font-size: 2rem;
}

.view-all {
    color: var(--color-primary);
    font-weight: 500;
}

.card-content {
    padding: var(--space-3);
}

.empty-state {
    text-align: center;
    padding: var(--space-4) 0;
}

.empty-state-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background-color: rgba(26, 54, 93, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--space-3);
}

.empty-state-icon i {
    font-size: 3rem;
    color: var(--color-primary);
}

.empty-state h3 {
    margin-bottom: var(--space-1);
}

.empty-state p {
    color: var(--color-text-light);
    margin-bottom: var(--space-3);
}

.appointments-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.appointment-card {
    display: flex;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.appointment-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.appointment-date {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: var(--color-primary);
    color: white;
    padding: var(--space-2);
    min-width: 100px;
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
    font-size: 2.4rem;
    font-weight: 700;
    line-height: 1;
}


.payment-status {
    font-size: 1.2rem;
    font-weight: 500;
    padding: 2px 6px;
    border-radius: 4px;
}

.payment-status.pending {
    color: #f59e0b;
    background-color: rgba(245, 158, 11, 0.1);
}

.payment-status.paid {
    color: #10b981;
    background-color: rgba(16, 185, 129, 0.1);
}

.time {
    font-size: 1.4rem;
}

.appointment-details {
    flex: 1;
    padding: var(--space-2);
}

.appointment-details h3 {
    margin-bottom: 4px;
    font-size: 1.8rem;
}

.appointment-details p {
    margin-bottom: 4px;
    color: var(--color-text-light);
}

.appointment-details i {
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

.status-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.payment-info {
    font-size: 1.4rem;
    color: var(--color-text-light);
    display: flex;
    align-items: center;
    gap: 5px;
}

.payment-info i {
    font-size: 1.2rem;
    color: var(--color-primary);
}

.payment-status.pending {
    color: #f59e0b;
    font-size: 1.2rem;
    font-weight: 500;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: var(--radius-sm);
    font-size: 1.2rem;
    font-weight: 500;
}

.status-scheduled {
    background-color: rgba(92, 158, 173, 0.1);
    color: var(--color-accent);
}

.status-completed {
    background-color: rgba(67, 160, 71, 0.1);
    color: var(--color-success);
}

.status-cancelled {
    background-color: rgba(229, 57, 53, 0.1);
    color: var(--color-error);
}

.price {
    font-weight: 600;
    color: var(--color-primary);
}

.appointment-actions {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 8px;
    padding: var(--space-2);
    background-color: var(--color-bg-alt);
}

.btn-sm {
    font-size: 1.4rem;
    padding: 6px 12px;
}

.text-error {
    color: var(--color-error);
}

.text-error:hover {
    background-color: var(--color-error);
    border-color: var(--color-error);
    color: white;
}

.review-rating {
    color: var(--color-secondary);
    text-align: center;
}

@media (max-width: 991px) {
    .dashboard-content {
        flex-direction: column;
    }
    
    .dashboard-sidebar {
        width: 100%;
        margin-bottom: var(--space-3);
    }
    
    .dashboard-nav {
        display: flex;
        flex-wrap: wrap;
    }
    
    .nav-item {
        flex: 1;
        min-width: 120px;
        justify-content: center;
        text-align: center;
        border-left: none;
        border-bottom: 3px solid transparent;
    }
    
    .nav-item.active {
        border-left-color: transparent;
        border-bottom-color: var(--color-primary);
    }
    
    .nav-item i {
        margin-right: 8px;
    }
}

.status-info {
    flex-direction: row;
    align-items: center;
    gap: 10px;
}
</style>