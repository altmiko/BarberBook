<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a customer
if (!isLoggedIn() || !isCustomer()) {
    setFlashMessage('Please log in as a customer to view reviews.', 'error');
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    $response = ['success' => false, 'message' => ''];
    
    $barberID = $_POST['barber'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $comment = $_POST['comment'] ?? null;

    // Validate inputs
    if (!$barberID || !$rating || !$comment) {
        $response['message'] = 'All fields are required.';
    } else {
        // Validate barber exists
        $barberCheck = $conn->prepare("SELECT UserID FROM Barbers WHERE UserID = ?");
        $barberCheck->bind_param("i", $barberID);
        $barberCheck->execute();
        if ($barberCheck->get_result()->num_rows === 0) {
            $response['message'] = 'Invalid barber selected.';
        } else {
            // Validate rating is between 1 and 5
            if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
                $response['message'] = 'Invalid rating value.';
            } else {
                // Sanitize comment
                $comment = htmlspecialchars(trim($comment));

                try {
                    // Insert the review
                    $stmt = $conn->prepare("INSERT INTO Reviews (CustomerID, BarberID, Rating, Comments) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiis", $user_id, $barberID, $rating, $comment);
                    
                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Review submitted successfully!';
                    } else {
                        throw new Exception("Error executing review insertion");
                    }
                } catch (Exception $e) {
                    error_log("Review submission error: " . $e->getMessage());
                    $response['message'] = 'Error submitting review. Please try again.';
                }
            }
        }
    }

    // If it's an AJAX request, return JSON response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

$page_title = "Customer Reviews";

include '../includes/header.php';
?>

<div class="container mt-4">
    <div class="spacer">
        <h2>Customer Reviews</h2>
    </div>

    <!-- Review Submission Form -->
    <div class="review-form-container">
        <h3>Write a Review</h3>
        <form id="reviewForm" method="POST">
            <input type="hidden" name="action" value="submit_review">
            <div class="form-group">
                <label for="barber">Select Barber:</label>
                <select class="form-control" id="barber" name="barber" required>
                    <option value="">Choose a barber...</option>
                    <?php
                    // Fetch barbers from database
                    $barberQuery = "SELECT UserID, FirstName, LastName FROM Barbers ORDER BY FirstName, LastName";
                    $barberResult = $conn->query($barberQuery);
                    if ($barberResult) {
                        while ($barber = $barberResult->fetch_assoc()) {
                            echo "<option value='" . $barber['UserID'] . "'>" . 
                                 htmlspecialchars($barber['FirstName'] . ' ' . $barber['LastName']) . 
                                 "</option>";
                        }
                    } else {
                        echo "<option value=''>Error loading barbers</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label>Rating:</label>
                <div class="rating-input">
                    <?php for($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                        <label for="star<?php echo $i; ?>">★</label>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="form-group">
                <label for="comment">Your Review:</label>
                <textarea class="form-control" id="comment" name="comment" rows="4" required 
                          placeholder="Share your experience..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Review</button>
        </form>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?php
            $query = "SELECT c.FirstName, c.LastName, b.FirstName as BarberFirstName, b.LastName as BarberLastName, r.Rating, r.Comments
                     FROM Customers c, Barbers b, Reviews r
                     WHERE r.BarberID = b.UserID
                     AND r.CustomerID = c.UserID
                     AND r.CustomerID = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='review-card'>";
                    echo "<div class='review-header'>";
                    echo "<div class='reviewer-info'>";
                    echo "<span class='reviewer-name'>" . htmlspecialchars($row["FirstName"] . " " . $row["LastName"]) . "</span>";
                    echo "<span class='review-date'>" . date('F j, Y') . "</span>";
                    echo "</div>";
                    echo "<div class='rating'>" . str_repeat('★', $row["Rating"]) . str_repeat('☆', 5-$row["Rating"]) . "</div>";
                    echo "</div>";
                    echo "<div class='review-content'>" . htmlspecialchars($row["Comments"]) . "</div>";
                    echo "<div class='barber-info'>Barber: " . htmlspecialchars($row["BarberFirstName"] . " " . $row["BarberLastName"]) . "</div>";
                    echo "</div>";
                }
            } else {
                echo "<div class='no-reviews'>No reviews available yet.</div>";
            }
            ?>
        </div>
    </div>
</div>

<style>
body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    margin: 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    flex: 1;
}

.spacer {
    margin-top: 40px;
}

.review-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 20px;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.reviewer-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.reviewer-name {
    font-weight: bold;
    color: #333;
}

.review-date {
    color: #666;
    font-size: 0.9em;
}

.rating {
    color: #FFC107;
    font-size: 1.2em;
}

.review-content {
    color: #444;
    line-height: 1.6;
}

.barber-info {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #eee;
    color: #666;
    font-size: 0.9em;
}

.no-reviews {
    text-align: center;
    padding: 40px;
    color: #666;
    font-style: italic;
}

.review-form-container {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1em;
}

.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 5px;
}

.rating-input input {
    display: none;
}

.rating-input label {
    cursor: pointer;
    font-size: 30px;
    color: #ddd;
    transition: color 0.2s;
}

.rating-input label:hover,
.rating-input label:hover ~ label,
.rating-input input:checked ~ label {
    color: #FFC107;
}

.btn-primary {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1em;
    transition: background-color 0.3s;
}

.btn-primary:hover {
    background-color: #0056b3;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reviewForm');
    const submitButton = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Disable the button and show loading state
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting...';
        
        // Get form data
        const formData = new FormData(this);
        
        // Send the request
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            // Check if the response is JSON
            try {
                const data = JSON.parse(html);
                if (data.success) {
                    showFlashMessage('Review submitted successfully!', 'success');
                    form.reset();
                    // Reload the page to show the new review
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showFlashMessage(data.message || 'Error submitting review', 'error');
                    submitButton.disabled = false;
                    submitButton.textContent = 'Submit Review';
                }
            } catch (e) {
                // If not JSON, it's a regular page reload
                document.documentElement.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showFlashMessage('Error submitting review. Please try again.', 'error');
            submitButton.disabled = false;
            submitButton.textContent = 'Submit Review';
        });
    });
});
</script>

<?php
include '../includes/footer.php';
?> 