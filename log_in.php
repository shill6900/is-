<?php
session_start();
include_once('database.php'); // Include database connection

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if inputs are empty
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: log_in.php");
        exit();
    }

    // Ensure the connection is active
    if (!$conn || $conn->connect_error) {
        $_SESSION['error'] = "Database connection error.";
        header("Location: log_in.php");
        exit();
    }

    // Prepare and execute the SQL query
    $sql = "SELECT id, first_name, last_name, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . " " . $user['last_name'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on user role
                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php"); // Redirect to admin dashboard
                } else {
                    header("Location: dashboard.php"); // Redirect to regular user dashboard
                }
                exit();
            } else {
                $_SESSION['error'] = "Invalid password.";
            }
        } else {
            $_SESSION['error'] = "No account found with this email.";
        }

        $stmt->close(); // Close the statement
    } else {
        $_SESSION['error'] = "Failed to prepare the SQL statement.";
    }

    // Redirect back to login page on failure
    header("Location: log_in.php");
    exit();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <!-- Display error messages -->
        <?php
        if (isset($_SESSION['error'])) {
            echo "<div class='error-message'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']); // Clear the error after displaying it
        }
        ?>
        <form action="log_in.php" method="POST">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn">Login</button>
        </form>
        <p>Don't have an account? <a href="sign_up.php">Sign up here</a></p>
    </div>
</body>
</html>
