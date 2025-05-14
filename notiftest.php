<?php
include 'config/database.php';

$query = "SELECT * FROM Notifications WHERE  = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = mysqli_fetch_assoc($result)) {
    echo $row['Subject'];
    echo $row['Body'];
}

?>

<html>
    <body>
        <?php
        while ($row = mysqli_fetch_assoc($result)) {
            echo $row['Subject'];
            echo $row['Body'];
            echo "<br>";  
        }
        ?>
    </body>
</html>
