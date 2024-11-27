<?php
session_start(); // Start the session
include('database.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p>Error: User not logged in. Please <a href='log_in.php'>log in</a>.</p>";
    exit();
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    // Validate inputs
    if (empty($description) || empty($amount) || empty($date)) {
        echo "<p>Error: All fields are required.</p>";
    } else {
        // Insert transaction into the database
        $sql = "INSERT INTO transactions (user_id, description, amount, date) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isds", $user_id, $description, $amount, $date);

        if ($stmt->execute()) {
            echo "<p>Transaction added successfully.</p>";
        } else {
            echo "<p>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }
}

$conn->close();
?>
<a href="add_expense.php">Back to Add Transaction</a>
<link rel="stylesheet" href="styles.css">