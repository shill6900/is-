<?php
session_start();
include_once('database.php'); // Include database connection

// Ensure only admins can access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: log_in.php");
    exit();
}

// Fetch all users (or select specific columns as needed)
$sql = "SELECT id, first_name, last_name, email FROM users";
$stmt = $conn->prepare($sql);
$stmt->execute();
$users = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Admin Dashboard</h2>
    <p>Welcome, <?php echo $_SESSION['user_name']; ?>! You are logged in as an admin.</p>
    
    <h3>User List</h3>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['first_name']) . " " . htmlspecialchars($user['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <a href="view_reports.php?user_id=<?php echo $user['id']; ?>">View Reports</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <a href="logout.php">Logout</a>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
