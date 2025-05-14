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
$todayQuery = "SELECT a.AppointmentID, a.StartTime, a.EndTime, a.Status, 
               GROUP_CONCAT(s.Name SEPARATOR ', ') as ServiceNames,
               GROUP_CONCAT(s.ServiceID) as ServiceIDs,
               GROUP_CONCAT(DISTINCT p.PayMethod) as PayMethods,
               GROUP_CONCAT(DISTINCT p.TransactionID) as TransactionIDs,
               SUM(p.Amount) as TotalAmount,
               c.FirstName as CustomerFirstName, c.LastName as CustomerLastName
              FROM Appointments a 
              JOIN Payments p ON a.PaymentID = p.PaymentID 
              JOIN Customers c ON a.CustomerID = c.UserID 
              JOIN ApptContains ac ON a.AppointmentID = ac.AppointmentID 
              JOIN Services s ON ac.ServiceID = s.ServiceID 
              JOIN BarberHas bh ON a.AppointmentID = bh.AppointmentID 
              WHERE bh.BarberID = ? AND DATE(a.StartTime) = CURDATE() AND a.Status != 'Cancelled' 
              GROUP BY a.AppointmentID
              ORDER BY a.StartTime ASC";

$todayStmt = $conn->prepare($todayQuery);
$todayStmt->bind_param("i", $barber_id);
$todayStmt->execute();
$todayResult = $todayStmt->get_result();

// Fetch future appointments
$futureQuery = "SELECT a.AppointmentID, a.StartTime, a.EndTime, a.Status, 
               GROUP_CONCAT(s.Name SEPARATOR ', ') as ServiceNames,
               GROUP_CONCAT(s.ServiceID) as ServiceIDs,
               GROUP_CONCAT(DISTINCT p.PayMethod) as PayMethods,
               GROUP_CONCAT(DISTINCT p.TransactionID) as TransactionIDs,
               SUM(p.Amount) as TotalAmount,
               c.FirstName as CustomerFirstName, c.LastName as CustomerLastName
               FROM Appointments a 
               JOIN Payments p ON a.PaymentID = p.PaymentID 
               JOIN Customers c ON a.CustomerID = c.UserID 
               JOIN ApptContains ac ON a.AppointmentID = ac.AppointmentID 
               JOIN Services s ON ac.ServiceID = s.ServiceID 
               JOIN BarberHas bh ON a.AppointmentID = bh.AppointmentID 
               WHERE bh.BarberID = ? AND DATE(a.StartTime) > CURDATE() AND a.Status != 'cancelled' 
               GROUP BY a.AppointmentID
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
                <h1>Barber Dashboard</h1>
                <p class="welcome-name">Welcome back, <?php echo htmlspecialchars($barber['FirstName'] . ' ' . $barber['LastName']); ?>!</p>
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
                        <a href="appointments.php" class="nav-item">
                            <i class="fas fa-calendar-check"></i>
                            <span>My Appointments</span>
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
                                                <h3><?php echo htmlspecialchars($appointment['ServiceNames']); ?></h3>
                                                <p class="customer-name">
                                                    <i class="fas fa-user"></i>
                                                    <?php echo htmlspecialchars($appointment['CustomerFirstName'] . ' ' . $appointment['CustomerLastName']); ?>
                                                </p>
                                                <div class="appointment-status">
                                                    <div class="status-info">
                                                        <span class="status-badge status-<?php echo strtolower($appointment['Status']); ?>">
                                                            <?php echo ucfirst($appointment['Status']); ?>
                                                        </span>
                                                        <span class="payment-info">
                                                            <i class="fas fa-credit-card"></i>
                                                            <?php echo htmlspecialchars($appointment['PayMethods']); ?>
                                                            <?php if (!empty($appointment['TransactionIDs'])): ?>
                                                                <span class="transaction-id">#<?php echo htmlspecialchars($appointment['TransactionIDs']); ?></span>
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                    <span class="price">BDT <?php echo $appointment['TotalAmount']; ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="appointment-actions">
                                                <?php if ($appointment['Status'] != 'Completed'): ?>
                                                    <form method="POST" action="cancel-appointment.php" 
                                                        onsubmit="return handleCancelAppointment(this);">
                                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['AppointmentID']; ?>">
                                                        <button type="submit" name="cancel_appointment" class="btn btn-outline btn-sm text-error btn-danger cancel-btn">Cancel</button>
                                                    </form>
                                                <?php endif; ?>
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
                                                <h3><?php echo htmlspecialchars($appointment['ServiceNames']); ?></h3>
                                                <p class="customer-name">
                                                    <i class="fas fa-user"></i>
                                                    <?php echo htmlspecialchars($appointment['CustomerFirstName'] . ' ' . $appointment['CustomerLastName']); ?>
                                                </p>
                                                <div class="appointment-status">
                                                    <div class="status-info">
                                                        <span class="status-badge status-<?php echo strtolower($appointment['Status']); ?>">
                                                            <?php echo ucfirst($appointment['Status']); ?>
                                                        </span>
                                                        <span class="payment-info">
                                                            <i class="fas fa-credit-card"></i>
                                                            <?php echo htmlspecialchars($appointment['PayMethods']); ?>
                                                            <?php if (!empty($appointment['TransactionIDs'])): ?>
                                                                <span class="transaction-id">#<?php echo htmlspecialchars($appointment['TransactionIDs']); ?></span>
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                    <span class="price">BDT <?php echo $appointment['TotalAmount']; ?></span>
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
                                    <p>You don't have any upcoming appointments.</p>
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

.rating-count {
    font-size: 1.4rem;
    font-weight: normal;
    color: #666;
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

.appointment-time {
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
    flex-direction: row;
    align-items: center;
    gap: 10px;
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

.payment-status.partial {
    color: #f59e0b;
    background-color: rgba(245, 158, 11, 0.1);
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

.btn-danger {
    background-color: transparent;
    color: var(--color-error);
    border: 2px solid var(--color-error);
    transition: all 0.2s ease;
    font-weight: 500;
}

.btn-danger:hover {
    background-color: var(--color-error);
    color: white;
    border-color: var(--color-error);
}

.transaction-id {
    font-size: 1.2rem;
    color: var(--color-text-light);
    margin-left: 5px;
    font-family: monospace;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.review-card {
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    padding: var(--space-3);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-2);
}

.customer-info {
    display: flex;
    align-items: center;
}

.customer-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--color-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-right: var(--space-2);
}

.customer-name h4 {
    margin: 0;
    font-size: 1.6rem;
}

.review-rating {
    color: var(--color-secondary);
}

.review-body p {
    margin: 0;
    color: var(--color-text);
    line-height: 1.5;
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

@media (max-width: 768px) {
    .appointment-card {
        flex-direction: column;
    }
    
    .appointment-time {
        flex-direction: row;
        justify-content: space-between;
        width: 100%;
        padding: var(--space-2) var(--space-3);
    }
    
    .date-display {
        flex-direction: row;
        gap: 8px;
        align-items: center;
        margin-bottom: 0;
    }
    
    .appointment-actions {
        flex-direction: row;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    
    .status-info {
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .payment-info {
        margin-left: 0;
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
        padding: var(--space-2) var(--space-3);
    }
    
    .nav-item.active {
        border-left-color: var(--color-primary);
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