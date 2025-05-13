<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';
include '../includes/header.php';

// Check if user is logged in as a customer
if (!isLoggedIn() || !isCustomer()) {
    setFlashMessage('You must be logged in as a customer to access this page.', 'error');
    redirect('../login.php');
}

$customer_id = $_SESSION['user_id'];

$query = "SELECT n.*, a.StartTime, a.EndTime, a.Status as AppointmentStatus, 
          b.FirstName as BarberFirstName, b.LastName as BarberLastName,
          s.Name as ServiceName
          FROM Notifications n
          LEFT JOIN Appointments a ON n.AppointmentID = a.AppointmentID
          LEFT JOIN Barbers b ON a.BarberID = b.UserID
          LEFT JOIN ApptContains ac ON a.AppointmentID = ac.AppointmentID
          LEFT JOIN Services s ON ac.ServiceID = s.ServiceID
          WHERE n.CustomerID = ?
          ORDER BY n.SentAt DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<main>
    <section class="dashboard-section">
        <div class="container">
            <div class="dashboard-header">
                <h1>Email Notifications</h1>
                <p>See all your emails here</p>
            </div>

                <div class="dashboard-main">
                    <div class="section-card">
                        <div class="card-header">
                            <h2>All Notifications</h2>
                        </div>
                        
                        <div class="card-content">
                            <?php if ($result->num_rows > 0): ?>
                                <div class="notifications-list">
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <div class="notification-card">
                                            <div class="notification-header">
                                                <div class="notification-title">
                                                    <span class="notification-date">
                                                        <i class="far fa-clock"></i>
                                                        <?php echo date('M d, Y g:i A', strtotime($row['SentAt'])); ?>
                                                    </span>
                                                    <h3><?php echo 'Subject: ' . htmlspecialchars($row['Subject']); ?></h3>
                                                </div>
                                            </div>
                                            
                                            <div class="notification-details">
                                                <div class="notification-body">
                                                    <?php echo 'Body: ' . nl2br(htmlspecialchars($row['Body'])); ?>
                                                </div>
                                                
                                                <?php if ($row['AppointmentID']): ?>
                                                    <div class="appointment-details">
                                                        <ul>
                                                            <li>
                                                                <i class="fas fa-calendar"></i>
                                                                <strong>Date & Time:</strong> 
                                                                <?php echo date('M d, Y g:i A', strtotime($row['StartTime'])); ?> - 
                                                                <?php echo date('g:i A', strtotime($row['EndTime'])); ?>
                                                            </li>
                                                            <li>
                                                                <i class="fas fa-user-alt"></i>
                                                                <strong>Barber:</strong> 
                                                                <?php echo htmlspecialchars($row['BarberFirstName'] . ' ' . $row['BarberLastName']); ?>
                                                            </li>
                                                            <?php if ($row['ServiceName']): ?>
                                                                <li>
                                                                    <i class="fas fa-cut"></i>
                                                                    <strong>Service:</strong> 
                                                                    <?php echo htmlspecialchars($row['ServiceName']); ?>
                                                                </li>
                                                            <?php endif; ?>
                                                        </ul>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <h3>No Notifications</h3>
                                    <p>You don't have any email notifications yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

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

.card-content {
    padding: var(--space-3);
}

.notifications-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-3);
}

.notification-card {
    background-color: white;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: var(--space-2);
}

.notification-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.notification-header {
    padding: var(--space-2) var(--space-3);
    border-bottom: 1px solid var(--color-border);
    background-color: var(--color-bg-alt);
}

.notification-title {
    display: flex;
    align-items: center;
    gap: var(--space-3);
}

.notification-title h3 {
    margin: 0;
    font-size: 1.6rem;
    color: var(--color-text);
    flex: 1;
}

.notification-date {
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.notification-date i {
    font-size: 1.4rem;
}

.notification-details {
    padding: var(--space-3);
}

.notification-body {
    color: var(--color-text);
    font-size: 1.5rem;
    line-height: 1.5;
    margin-bottom: var(--space-2);
}

.appointment-details {
    background-color: var(--color-bg-alt);
    border-radius: var(--radius-sm);
    padding: var(--space-2);
    margin-top: var(--space-2);
}

.appointment-details ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-2);
}

.appointment-details li {
    display: flex;
    align-items: center;
    font-size: 1.3rem;
    color: var(--color-text);
}

.appointment-details i {
    width: 16px;
    margin-right: var(--space-2);
    color: var(--color-primary);
}

.appointment-details strong {
    margin-right: var(--space-1);
    color: var(--color-text-dark);
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
</style>

<?php include '../includes/footer.php'; ?> 