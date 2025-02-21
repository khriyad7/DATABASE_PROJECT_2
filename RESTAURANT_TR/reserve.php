<?php
// reserve.php - Handle Table Reservation

// Include the database connection
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['table_id']) || !isset($_POST['customer_name']) || !isset($_POST['reservation_date'])) {
        die("Error: Missing required fields.");
    }

    $table_number = $_POST['table_id']; // Get table number
    $customer_name = $_POST['customer_name'];
    $reservation_date = date('Y-m-d', strtotime($_POST['reservation_date'])); // Extract date only

    // Validate table number range
    if ($table_number < 1 || $table_number > 7) {
        die("Error: Invalid table number! Please select a table between 1 and 7.");
    }

    // Begin transaction
    $conn->begin_transaction();

    // Get the table ID based on table number
    $stmt = $conn->prepare("SELECT id, status FROM tables WHERE table_number = ? FOR UPDATE");
    $stmt->bind_param('i', $table_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $table_id = $row['id'];
        $current_status = $row['status'];

        // Check if the table is already reserved for the selected date
        $stmt = $conn->prepare("SELECT * FROM reservations WHERE table_id = ? AND DATE(reservation_time) = ?");
        $stmt->bind_param('is', $table_id, $reservation_date);
        $stmt->execute();
        $reservation_result = $stmt->get_result();

        if ($reservation_result->num_rows > 0) {
            $conn->rollback();
            die("Sorry, this table is already reserved for the selected date.");
        }

        // Insert reservation
        $stmt = $conn->prepare("INSERT INTO reservations (table_id, customer_name, reservation_time) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $table_id, $customer_name, $_POST['reservation_date']);
        if (!$stmt->execute()) {
            $conn->rollback();
            die("Error: Unable to save reservation.");
        }

        // Update table status if it was previously available
        if ($current_status === 'available') {
            $stmt = $conn->prepare("UPDATE tables SET status = 'reserved' WHERE id = ?");
            $stmt->bind_param('i', $table_id);
            if (!$stmt->execute()) {
                $conn->rollback();
                die("Error: Unable to update table status.");
            }
        }

        // Commit transaction
        $conn->commit();
        echo "Success: Reservation confirmed for $customer_name on $reservation_date.";
    } else {
        $conn->rollback();
        die("Error: Table not found!");
    }

    // Close statements
    $stmt->close();
}
?>
