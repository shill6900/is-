<?php include('database.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Transaction</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Add Transaction</h2>
    <form action="process_expense.php" method="post">
       <label for="category">category:</label>
       <input type="text" name="text" required>
        <label for="description">Description:</label>
        <input type="text" name="description" required>
        
        <label for="amount">Amount:</label>
        <input type="number" step="0.01" name="amount" required>
        
        <label for="date">Date:</label>
        <input type="date" name="date" required>
        
        <button type="submit">Add Transaction</button>
    </form>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
