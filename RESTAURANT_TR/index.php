<?php
// Include the database connection
include 'db.php';

// Fetch table statuses from the database
$sql = "SELECT table_number, status FROM tables ORDER BY table_number";
$result = $conn->query($sql);

// Store table statuses in an associative array
$tables = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tables[$row['table_number']] = $row['status'];
    }
}

// Check if the form has been submitted
$form_submitted = false;
$reservation_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_number = $_POST['table_id'];
    $customer_name = $_POST['customer_name'];
    $reservation_date = $_POST['reservation_date'];
    $reservation_time = date('Y-m-d H:i:s', strtotime($reservation_date));

    include 'reserve.php';
    $form_submitted = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table Reservation - Restaurant</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .restaurant-layout {
            display: grid;
            grid-template-columns: repeat(4, 100px);
            gap: 20px;
            justify-content: center;
            margin-top: 20px;
        }
        .table {
            width: 100px;
            height: 100px;
            line-height: 100px;
            border-radius: 50%;
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
        }
        .available { background-color: green; }
        .reserved { background-color: red; }
        .table-container {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2>Welcome to Our Restaurant Reservation System</h2>

    <h3>Table Layout:</h3>
    <div class="restaurant-layout">
        <?php
        for ($i = 1; $i <= 7; $i++) {
            $status = isset($tables[$i]) ? $tables[$i] : 'available';
            $class = $status === 'reserved' ? 'reserved' : 'available';
            echo "<div class='table-container'>";
            echo "<div class='table $class'>Table $i</div>";
            echo "<p>" . ucfirst($status) . "</p>";
            echo "</div>";
        }
        ?>
    </div>

    <?php if (!$form_submitted): ?>
    <h3>Reserve a Table:</h3>
    <form action="index.php" method="POST">
        <label for="table_id">Table Number (1-7):</label>
        <input type="number" name="table_id" min="1" max="7" required>
        <br><br>
        <label for="customer_name">Your Name:</label>
        <input type="text" name="customer_name" required>
        <br><br>
        <label for="reservation_date">Reservation Date and Time:</label>
        <input type="datetime-local" name="reservation_date" required>
        <br><br>
        <button type="submit">Reserve</button>
    </form>
    <?php else: ?>
        <h3><?php echo $reservation_message; ?></h3>
        <a href="index.php">
            <button>Return to Reservation Page</button>
        </a>
    <?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>
