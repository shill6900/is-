<?php
session_start(); // Start the session
include('database.php'); // Include the database connection

// Check if the user is logged in (assuming user_id is stored in the session)
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to home if not logged in
    exit();
}

// Get the current user's ID from the session
$user_id = $_SESSION['user_id'];

// Handle Clear Transactions Request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clear_transactions'])) {
    $sql_clear = "UPDATE transactions SET is_cleared = TRUE WHERE user_id = ?";
    $stmt_clear = $conn->prepare($sql_clear);
    $stmt_clear->bind_param("i", $user_id);
    if ($stmt_clear->execute()) {
        $message = "Transactions cleared successfully!";
    } else {
        $message = "Failed to clear transactions: " . $stmt_clear->error;
    }
    $stmt_clear->close();
}

// Retrieve daily transaction totals for the current user, excluding cleared transactions
$sql_daily_totals = "SELECT DATE(date) AS transaction_date, SUM(amount) AS total_amount 
                     FROM transactions 
                     WHERE user_id = ? AND is_cleared = FALSE 
                     GROUP BY DATE(date) 
                     ORDER BY transaction_date DESC";
$stmt_daily_totals = $conn->prepare($sql_daily_totals);
$stmt_daily_totals->bind_param("i", $user_id); // Bind the logged-in user's ID
$stmt_daily_totals->execute();
$result_daily_totals = $stmt_daily_totals->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Report</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your stylesheet -->
</head>
<body>
    <h2>Daily Transaction Report</h2>
    <a href="dashboard.php">Back to Dashboard</a>
    
    <?php if (isset($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST">
        <button type="submit" name="clear_transactions" onclick="return confirm('Are you sure you want to clear all transactions?');">
            Clear Transactions
        </button>
    </form>

    <table>
        <tr>
            <th>Date</th>
            <th>Total Amount</th>
        </tr>

        <?php
        // Check if there are any results from the query
        if ($result_daily_totals->num_rows > 0) {
            // Output data for each day
            while ($row = $result_daily_totals->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['transaction_date']) . "</td>";
                echo "<td>$" . number_format($row['total_amount'], 2) . "</td>";
                echo "</tr>";
            }
        } else {
            // If no transactions, show a message
            echo "<tr><td colspan='2'>No transactions available for this period.</td></tr>";
        }
        ?>
    </table>
</body>
</html>

<?php
// Close the database connection
$stmt_daily_totals->close();
$conn->close();
?>
