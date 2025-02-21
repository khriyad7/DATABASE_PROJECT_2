<?php
// resetdata.php - Reset Data

include 'db.php';

// Clear previous reservations
$sql_reset = "TRUNCATE TABLE reservations;";
if ($conn->query($sql_reset) === TRUE) {
    echo "Reservations data has been reset successfully!";
} else {
    echo "Error resetting data: " . $conn->error;
}

$conn->close();
?>
