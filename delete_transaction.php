<?php
session_start();
include_once('database.php');

// Ensure only admins can access this page
if ($_SESSION['role'] !== 'admin') {
    header("Location: log_in.php");
    exit();
}

// Get transaction ID from URL
$transaction_id = $_GET['id'] ?? null;
if (!$transaction_id) {
    header("Location: admin_dashboard.php");
    exit();
}

// Delete the transaction
$sql = "DELETE FROM transactions WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $transaction_id);
if ($stmt->execute()) {
    $_SESSION['success'] = "Transaction deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete transaction.";
}

header("Location: admin_dashboard.php"); // Redirect back to the admin dashboard
exit();

?>

<?php
// Close the database connection
$conn->close();
?>
