<?php
include('database.php');
session_start();

// Example: Fetch user_id from session (adjust as per your implementation)
$user_id = $_SESSION['user_id'] ?? 1; // Default to user ID 1 for demonstration

// Initialize variables
$current_month = date('Y-m'); // Current month (YYYY-MM)

// Handle form submission for setting/updating or clearing budget
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $month = $_POST['month'];
    $year = $_POST['year'];
    $amount = $_POST['amount'] ?? null;
    $formatted_month = "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT);

    if (isset($_POST['save_budget'])) {
        // Check if a budget exists for the month
        $sql = "SELECT * FROM budget WHERE month = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $formatted_month, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing budget
            $sql = "UPDATE budget SET amount = ?, status = 'active' WHERE month = ? AND user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("dsi", $amount, $formatted_month, $user_id);
            $stmt->execute();
            $message = "Budget updated successfully.";
        } else {
            // Insert new budget
            $sql = "INSERT INTO budget (user_id, month, amount, status) VALUES (?, ?, ?, 'active')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isd", $user_id, $formatted_month, $amount);
            $stmt->execute();
            $message = "Budget set successfully.";
        }
    } elseif (isset($_POST['clear_budget'])) {
        // Clear budget for the selected month
        $sql = "UPDATE budget SET status = 'cleared' WHERE month = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $formatted_month, $user_id);
        $stmt->execute();
        $message = "Budget cleared successfully.";
    }
}

// Retrieve the current month's budget
$sql = "SELECT amount, status FROM budget WHERE month = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $current_month, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$budget = $result->fetch_assoc();

// Retrieve total expenses for the current month
$sql = "SELECT SUM(amount) AS total_expenses FROM transactions WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $current_month);
$stmt->execute();
$expense_result = $stmt->get_result();
$total_expenses = $expense_result->fetch_assoc()['total_expenses'] ?? 0;

$remaining_balance = $budget && $budget['status'] == 'active' ? $budget['amount'] - $total_expenses : null;

// Fetch all transactions for the current month
$sql = "SELECT category, description, amount, date FROM transactions WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $current_month);
$stmt->execute();
$transactions = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Budget</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Monthly Budget</h2>
    <p class="message"><?php echo isset($message) ? $message : ''; ?></p>
    
    <div class="budget-info">
        <p><strong>Current Month:</strong> <?php echo $current_month; ?></p>
        <p><strong>Monthly Budget:</strong> $<?php echo $budget ? number_format($budget['amount'], 2) : 'Not set'; ?></p>
        <p><strong>Status:</strong> <?php echo $budget ? htmlspecialchars($budget['status']) : 'N/A'; ?></p>
        <p><strong>Total Expenses:</strong> $<?php echo number_format($total_expenses, 2); ?></p>
        <p><strong>Remaining Balance:</strong> $<?php echo $remaining_balance !== null ? number_format($remaining_balance, 2) : 'N/A'; ?></p>
    </div>
    
    <h3>Set, Update, or Clear Budget</h3>
    <form action="budget.php" method="POST">
        <label for="month">Month:</label>
        <select name="month" id="month" required>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>" <?php echo ($m == date('m')) ? 'selected' : ''; ?>>
                    <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                </option>
            <?php endfor; ?>
        </select>

        <label for="year">Year:</label>
        <select name="year" id="year" required>
            <?php for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++): ?>
                <option value="<?php echo $y; ?>" <?php echo ($y == date('Y')) ? 'selected' : ''; ?>>
                    <?php echo $y; ?>
                </option>
            <?php endfor; ?>
        </select>

        <label for="amount">Budget Amount:</label>
        <input type="number" name="amount" step="0.01" required>
        
        <button type="submit" name="save_budget">Save/Update Budget</button>
        <button type="submit" name="clear_budget" onclick="return confirm('Are you sure you want to clear the budget?');">Clear Budget</button>
    </form>
    
    <h3>Transactions for <?php echo $current_month; ?></h3>
    <?php if ($transactions->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount ($)</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $transactions->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo number_format($row['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No transactions recorded for this month.</p>
    <?php endif; ?>
    
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>

<?php
$conn->close();
?>
