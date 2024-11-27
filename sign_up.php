<?php
session_start(); // Start the session to manage errors and success messages
include('database.php'); // Include the database connection

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Initialize error message
    $error_message = "";

    // Input validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    }

    // If there are validation errors, redirect back to the signup page
    if (!empty($error_message)) {
        $_SESSION['error'] = $error_message;
        header("Location: sign_up.php");
        exit();
    }

    // Check if the email is already registered
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "This email is already registered.";
        header("Location: sign_up.php");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the user into the database
    $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);

    if ($stmt->execute()) {
        // Registration successful, set session and redirect to the dashboard
        $_SESSION['user_id'] = $conn->insert_id; // Get the last inserted user's ID
        $_SESSION['user_name'] = $first_name . " " . $last_name; // Optionally store the user's name
        
        // Set success message
        $_SESSION['success'] = "Registration successful! Welcome to your dashboard.";

        // Redirect to dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        // Error during insertion
        $_SESSION['error'] = "Something went wrong. Please try again.";
        header("Location: sign_up.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Sign Up</h2>

        <!-- Display error or success messages -->
        <?php
        if (isset($_SESSION['error'])) {
            echo "<p class='error'>" . $_SESSION['error'] . "</p>";
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo "<p class='success'>" . $_SESSION['success'] . "</p>";
            unset($_SESSION['success']);
        }
        ?>

        <!-- Sign-Up Form -->
        <form action="sign_up.php" method="POST">
            <label for="first-name">First Name</label>
            <input type="text" id="first-name" name="first_name" placeholder="Enter your first name" required>

            <label for="last-name">Last Name</label>
            <input type="text" id="last-name" name="last_name" placeholder="Enter your last name" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email address" required>

            <label for="create-password">Create Password</label>
            <input type="password" id="create-password" name="password" placeholder="Create a password" required>

            <label for="confirm-password">Confirm Password</label>
            <input type="password" id="confirm-password" name="confirm_password" placeholder="Confirm your password" required>

            <button type="submit" class="btn">Sign Up</button>
        </form>

        <p>Already have an account? <a href="log_in.php">Login here</a></p>
    </div>
</body>
</html>
