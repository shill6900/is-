<?php include('database.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Transactions</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Transaction List</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Description</th>
            <th>Amount</th>
            <th>Date</th>
        </tr>
        <?php
        $sql = "SELECT id, description, amount, date FROM transactions";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['description']}</td>
                        <td>{$row['amount']}</td>
                        <td>{$row['date']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No transactions found.</td></tr>";
        }
        ?>
    </table>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
