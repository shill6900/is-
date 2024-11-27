<?php
session_start();
include_once('database.php'); // Include database connection

// Ensure only admins can access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: log_in.php");
    exit();
}

// Get user ID from URL parameter
$user_id = $_GET['user_id'] ?? null;
if (!$user_id) {
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch user information (optional, for display purposes)
$sql = "SELECT first_name, last_name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch user's transactions and budget (or other reports as needed)
$transactions_sql = "SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC";
$transactions_stmt = $conn->prepare($transactions_sql);
$transactions_stmt->bind_param("i", $user_id);
$transactions_stmt->execute();
$transactions = $transactions_stmt->get_result();

$budget_sql = "SELECT amount FROM budget WHERE user_id = ?";
$budget_stmt = $conn->prepare($budget_sql);
$budget_stmt->bind_param("i", $user_id);
$budget_stmt->execute();
$budget = $budget_stmt->get_result()->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Reports for <?php echo htmlspecialchars($user['first_name']); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Reports for <?php echo htmlspecialchars($user['first_name']) . " " . htmlspecialchars($user['last_name']); ?></h2>
    
    <h3>Transactions</h3>
    <?php if ($transactions->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $transactions->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo number_format($row['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td>
                            <a href="delete_transaction.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this transaction?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No transactions found for this user.</p>
    <?php endif; ?>

    <h3>Budget</h3>
    <p>
        <?php if ($budget): ?>
            Monthly Budget: $<?php echo number_format($budget['amount'], 2); ?>
        <?php else: ?>
            No budget set for this user.
        <?php endif; ?>
    </p>
    
    <a href="admin_dashboard.php">Back to Admin Dashboard</a>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
