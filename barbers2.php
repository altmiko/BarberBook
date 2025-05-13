<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = "Our Barbers";
include 'includes/header.php';

// Get barbers with their average ratings
$sql = "SELECT b.*, 
        COALESCE(AVG(r.Rating), 0) as avg_rating,
        COUNT(r.ReviewID) as total_reviews
        FROM Barbers b 
        LEFT JOIN Reviews r ON b.UserID = r.BarberID 
        GROUP BY b.UserID 
        ORDER BY b.FirstName";
$result = $conn->query($sql);
?>

<main>
    <section class="barbers-hero" style="margin-bottom: 3rem;">
        <div class="container margin-bottom-20">
            <h1 style="color: white;">Our Barbers</h1>
            <p>Meet our team of experienced barbers</p>
        </div>
    </section>

    <div class="container">
        <div class="barbers-grid">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="barber-card">';
                    echo '<div class="image-content">';
                    echo '<div class="overlay"></div>';
                    echo '<div class="card-image" onclick="showReviews(' . $row['UserID'] . ')">';
                    echo '<img src="https://d2zdpiztbgorvt.cloudfront.net/region1/us/807905/biz_photo/c2da6e290fa84b0392079ca2ae658f-pedro-barber-biz-photo-cf22ec40162841139be5358ccd8193-booksy.jpeg?size=640x427" alt="' . htmlspecialchars($row['FirstName']) . '" class="card-img">';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="card-content">';
                    echo '<h3 class="name">' . htmlspecialchars($row['FirstName']) . ' ' . htmlspecialchars($row['LastName']) . '</h3>';
                    
                    // Display average rating
                    $avgRating = round($row['avg_rating'], 1);
                    echo '<div class="rating">';
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $avgRating) {
                            echo '<i class="fas fa-star"></i>';
                        } elseif ($i - 0.5 <= $avgRating) {
                            echo '<i class="fas fa-star-half-alt"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    echo '<span class="rating-text">' . $avgRating . ' (' . $row['total_reviews'] . ' reviews)</span>';
                    echo '</div>';
                    
                    echo '<p class="description">' . htmlspecialchars($row['Bio']) . '</p>';
                    echo '<a href="booking.php?barber=' . $row['UserID'] . '" class="button">Book Now</a>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<p class="no-barbers">No barbers available at the moment.</p>';
            }
            ?>
        </div>
    </div>
</main>

<!-- Reviews Modal -->
<div id="reviewsModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Barber Reviews</h2>
        <div id="reviewsContent"></div>
    </div>
</div>

<div class="spacer" style="margin-top: 3rem;"></div>

<?php include 'includes/footer.php'; ?>

<style>
.barbers-hero {
    background-color: #1a365d;
    color: white;
    padding: 4rem 0;
    text-align: center;
}

.barbers-hero h1 {
    font-size: 3.6rem;
    margin-bottom: 1rem;
}

.barbers-hero p {
    font-size: 1.8rem;
    opacity: 0.9;
}

.barbers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    padding: 2rem 0;
}

.barber-card {
    border-radius: 25px;
    background-color: #FFF;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}

.barber-card:hover {
    transform: translateY(-4px);
}

.image-content,
.card-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px 14px;
}

.image-content {
    position: relative;
    row-gap: 5px;
    padding: 25px 0;
}

.overlay {
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 100%;
    background: #1A365D;
    border-radius: 25px 25px 0 25px;
}

.overlay::before,
.overlay::after {
    content: '';
    position: absolute;
    right: 0;
    bottom: -40px;
    height: 40px;
    width: 40px;
    background-color: #1a365d;
}

.overlay::after {
    border-radius: 0 25px 0 0;
    background-color: #FFF;
}

.card-image {
    position: relative;
    height: 150px;
    width: 150px;
    border-radius: 50%;
    background: #FFF;
    padding: 3px;
}

.card-image .card-img {
    height: 100%;
    width: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.name {
    font-size: 18px;
    font-weight: 500;
    color: #333;
    margin: 10px 0;
}

.description {
    font-size: 14px;
    color: #707070;
    text-align: center;
    margin-bottom: 15px;
}

.button {
    border: none;
    font-size: 16px;
    color: #FFF;
    padding: 8px 16px;
    background-color: #1A365D;
    border-radius: 6px;
    margin: 14px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.button:hover {
    background: transparent;
    border: 2px solid #1A365D;
    color: #1A365D;
}

.no-barbers {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.no-barbers p {
    color: #666;
    font-size: 1.6rem;
}

@media (max-width: 768px) {
    .barbers-hero h1 {
        font-size: 2.8rem;
    }

    .barbers-hero p {
        font-size: 1.6rem;
    }
}

.rating {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    margin-bottom: 10px;
}

.rating i {
    color: #FFC107;
    font-size: 16px;
}

.rating-text {
    color: #666;
    font-size: 14px;
    margin-left: 5px;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border-radius: 8px;
    width: 80%;
    max-width: 800px;
    max-height: 80vh;
    overflow-y: auto;
    position: relative;
}

.close {
    position: absolute;
    right: 20px;
    top: 10px;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.review-card {
    background: #fff;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.reviewer-name {
    font-weight: 500;
    color: #333;
}

.review-rating {
    color: #FFC107;
}

.review-comment {
    color: #666;
    line-height: 1.5;
}

.card-image {
    cursor: pointer;
    transition: transform 0.2s ease;
}

.card-image:hover {
    transform: scale(1.05);
}
</style>

<script>
function showReviews(barberId) {
    const modal = document.getElementById('reviewsModal');
    const content = document.getElementById('reviewsContent');
    
    // Show loading state
    content.innerHTML = '<p>Loading reviews...</p>';
    modal.style.display = 'block';
    
    // Fetch reviews
    fetch(`get_reviews.php?barber_id=${barberId}`)
        .then(response => response.json())
        .then(reviews => {
            if (reviews.length === 0) {
                content.innerHTML = '<p>No reviews yet.</p>';
                return;
            }
            
            let html = '';
            reviews.forEach(review => {
                html += `
                    <div class="review-card">
                        <div class="review-header">
                            <span class="reviewer-name">${review.customer_name}</span>
                            <div class="review-rating">
                                ${'★'.repeat(review.rating)}${'☆'.repeat(5-review.rating)}
                            </div>
                        </div>
                        <div class="review-comment">${review.comment}</div>
                    </div>
                `;
            });
            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = '<p>Error loading reviews. Please try again.</p>';
            console.error('Error:', error);
        });
}

// Close modal when clicking the X or outside the modal
document.querySelector('.close').onclick = function() {
    document.getElementById('reviewsModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('reviewsModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script> 