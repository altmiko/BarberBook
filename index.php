<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Define page title
$page_title = "Home";
include 'includes/header.php';
?>

<main>
  <!-- Hero Section -->
  <section class="hero">
    <div class="container">
      <div class="hero-content">
        <h1>Professional Haircuts & Styling</h1>
        <p>Experience the best haircuts and styling services from our expert barbers.</p>
        <div class="cta-buttons">
          <a href="booking.php" class="btn btn-primary">Book Now</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured Services -->
  <section class="services">
    <div class="container">
      <h2>Our Services</h2>
      <div class="services-grid">
        <?php
        $sql = "SELECT * FROM Services LIMIT 4";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo '<div class="service-card">';
            echo '<div class="service-icon"><i class="fas fa-cut"></i></div>';
            echo '<h3>' . htmlspecialchars($row['Name']) . '</h3>';
            echo '<p>' . htmlspecialchars($row['Description']) . '</p>';
            echo '<div class="service-details">';
            echo '<span class="duration"><i class="far fa-clock"></i> ' . htmlspecialchars($row['Duration']) . ' min</span>';
            echo '<span class="price">$' . htmlspecialchars($row['Price']) . '</span>';
            echo '</div>';
            echo '<a href="booking.php?service=' . $row['ServiceID'] . '" class="btn btn-outline">Book Now</a>';
            echo '</div>';
          }
        }
        ?>
      </div>
  </section>

  <!-- Featured Barbers
  <section class="barbers">
    <div class="container">
      <h2>Meet Our Expert Barbers</h2>
      <div class="barbers-grid">
        
        <?php /*
        $sql = "SELECT UserID, FirstName, LastName, Bio FROM Barbers LIMIT 3";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo '<div class="barber-card">';
            echo '<div class="barber-image"><img src="assets/images/barber-' . $row['UserID'] . '.jpg" alt="Barber ' . htmlspecialchars($row['FirstName']) . ' ' . htmlspecialchars($row['LastName']) . '" onerror="this.src=\'assets/images/default-barber.jpg\'"></div>';
            echo '<h3>' . htmlspecialchars($row['FirstName']) . ' ' . htmlspecialchars($row['LastName']) . '</h3>';
            echo '<p class="barber-bio">' . htmlspecialchars(substr($row['Bio'], 0, 120)) . '...</p>';
            
            // Get average rating
            $barberID = $row['UserID'];
            $ratingSQL = "SELECT AVG(Rating) as AverageRating FROM Reviews WHERE BarberID = $barberID";
            $ratingResult = $conn->query($ratingSQL);
            $ratingRow = $ratingResult->fetch_assoc();
            $averageRating = round($ratingRow['AverageRating'], 1);
            
            echo '<div class="barber-rating">';
            echo '<span class="stars">';
            for ($i = 1; $i <= 5; $i++) {
              if ($i <= $averageRating) {
                echo '<i class="fas fa-star"></i>';
              } elseif ($i - 0.5 <= $averageRating) {
                echo '<i class="fas fa-star-half-alt"></i>';
              } else {
                echo '<i class="far fa-star"></i>';
              }
            }
            echo '</span>';
            echo '<span class="rating-value">' . $averageRating . '</span>';
            echo '</div>';
            
            echo '<a href="barber.php?id=' . $row['UserID'] . '" class="btn btn-outline">View Profile</a>';
            echo '</div>';
          }
        } */
        ?> 
        
      </div>
      <div class="text-center mt-4">
        <a href="barbers.php" class="btn btn-secondary">View All Barbers</a>
      </div>
    </div>
  </section> -->

  <!-- Featured Barbers -->
  <section class="barbers">
    <div class="container">
      <h2>Meet Our Expert Barbers</h2>
      <div class="barbers-grid">
        <?php
        $sql = "SELECT UserID, FirstName, LastName, Bio FROM Barbers LIMIT 3";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo '<div class="barber-card">';
            echo '<div class="barber-image"><img src="https://images.pexels.com/photos/3998429/pexels-photo-3998429.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="Barber ' . htmlspecialchars($row['FirstName']) . ' ' . htmlspecialchars($row['LastName']) . '"></div>';
            echo '<h3>' . htmlspecialchars($row['FirstName']) . ' ' . htmlspecialchars($row['LastName']) . '</h3>';
            echo '<p class="barber-bio">' . htmlspecialchars(substr($row['Bio'], 0, 120)) . '...</p>';
            
            // Get average rating
            $barberID = $row['UserID'];
            $ratingSQL = "SELECT AVG(Rating) as AverageRating FROM Reviews WHERE BarberID = $barberID";
            $ratingResult = $conn->query($ratingSQL);
            $ratingRow = $ratingResult->fetch_assoc();
            $averageRating = round($ratingRow['AverageRating'], 1);
            
            echo '<div class="barber-rating">';
            echo '<span class="stars">';
            for ($i = 1; $i <= 5; $i++) {
              if ($i <= $averageRating) {
                echo '<i class="fas fa-star"></i>';
              } elseif ($i - 0.5 <= $averageRating) {
                echo '<i class="fas fa-star-half-alt"></i>';
              } else {
                echo '<i class="far fa-star"></i>';
              }
            }
            echo '</span>';
            echo '<span class="rating-value">' . $averageRating . '</span>';
            echo '</div>';
            
            echo '<a href="barber.php?id=' . $row['UserID'] . '" class="btn btn-outline">View Profile</a>';
            echo '</div>';
          }
        }
        ?>
      </div>
      <div class="text-center mt-4">
        <a href="barbers.php" class="btn btn-secondary">View All Barbers</a>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section class="testimonials">
    <div class="container">
      <h2>What Our Customers Say</h2>
      <div class="testimonials-slider">
        <?php
        $sql = "SELECT r.Rating, r.Comments, c.FirstName, c.LastName, b.FirstName as BarberFirstName, b.LastName as BarberLastName 
                FROM Reviews r 
                JOIN Customers c ON r.CustomerID = c.UserID 
                JOIN Barbers b ON r.BarberID = b.UserID 
                ORDER BY RAND() LIMIT 5";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo '<div class="testimonial-card">';
            echo '<div class="testimonial-rating">';
            for ($i = 1; $i <= 5; $i++) {
              if ($i <= $row['Rating']) {
                echo '<i class="fas fa-star"></i>';
              } else {
                echo '<i class="far fa-star"></i>';
              }
            }
            echo '</div>';
            echo '<p class="testimonial-comment">"' . htmlspecialchars($row['Comments']) . '"</p>';
            echo '<div class="testimonial-author">';
            echo '<p class="author-name">' . htmlspecialchars($row['FirstName']) . ' ' . htmlspecialchars(substr($row['LastName'], 0, 1)) . '.</p>';
            echo '<p class="author-info">Customer of ' . htmlspecialchars($row['BarberFirstName']) . ' ' . htmlspecialchars($row['BarberLastName']) . '</p>';
            echo '</div>';
            echo '</div>';
          }
        }
        ?>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="cta">
    <div class="container">
      <div class="cta-content">
        <h2>Ready for a Fresh Look?</h2>
        <p>Book your appointment today and experience the best barbering services.</p>
        <a href="booking.php" class="btn btn-primary">Book Now</a>
      </div>
    </div>
  </section>
</main>

<?php
include 'includes/footer.php';
?>