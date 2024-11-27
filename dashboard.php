<?php
session_start(); // Start session to manage login state
include('database.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: log_in.php");
    exit();
}

// Get current user ID
$user_id = $_SESSION['user_id'];

// Retrieve the user's current budget for the month
$current_month = date('Y-m');
$sql = "SELECT * FROM budget WHERE user_id = ? AND month = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $current_month);
$stmt->execute();
$budget_result = $stmt->get_result();
$budget = $budget_result->fetch_assoc();

// Retrieve total expenses for the current month
$sql = "SELECT SUM(amount) AS total_expenses FROM transactions WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $current_month);
$stmt->execute();
$expense_result = $stmt->get_result();
$total_expenses = $expense_result->fetch_assoc()['total_expenses'];

$remaining_balance = $budget ? $budget['amount'] - $total_expenses : null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Welcome to Your Dashboard</h2>
        <div class="budget-info">
            <p><strong>Current Month:</strong> <?php echo $current_month; ?></p>
            <p><strong>Monthly Budget:</strong> $<?php echo $budget ? number_format($budget['amount'], 2) : 'Not set'; ?></p>
            <p><strong>Total Expenses:</strong> $<?php echo number_format($total_expenses, 2); ?></p>
            <p><strong>Remaining Balance:</strong> $<?php echo $remaining_balance !== null ? number_format($remaining_balance, 2) : 'N/A'; ?></p>
        </div>
        <nav>
            <a href="add_expense.php">Manage Transactions</a>
            <a href="budget.php">Manage Budget</a>
            <a href="reports.php">View Reports</a>
            <a href="logout.php" class="btn">Logout</a>
        </nav>
    </div>
</body>
</html>

<?php
$conn->close();
?>
