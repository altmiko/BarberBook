<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = "Our Barbers";
include 'includes/header.php';

$sql = "SELECT * FROM Barbers ORDER BY FirstName";
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
                    echo '<div class="card-image">';
                    echo '<img src="https://d2zdpiztbgorvt.cloudfront.net/region1/us/807905/biz_photo/c2da6e290fa84b0392079ca2ae658f-pedro-barber-biz-photo-cf22ec40162841139be5358ccd8193-booksy.jpeg?size=640x427" alt="' . htmlspecialchars($row['FirstName']) . '" class="card-img">';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="card-content">';
                    echo '<h3 class="name">' . htmlspecialchars($row['FirstName']) . ' ' . htmlspecialchars($row['LastName']) . '</h3>';
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
</style>


</script> 