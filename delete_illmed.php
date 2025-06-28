<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Check if the 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare the SQL DELETE statement
    $query = "DELETE FROM illmed WHERE Num = ?"; // Assuming 'Num' is the primary key in your table
    $stmt = mysqli_prepare($conn, $query);

    // Bind the parameter to the statement
    mysqli_stmt_bind_param($stmt, "i", $id);

    // Execute the statement
    if (mysqli_stmt_execute($stmt)) {
        // Record deleted successfully
        $_SESSION['message'] = "Record deleted successfully.";
    } else {
        // Error deleting record
        $_SESSION['message'] = "Error deleting record: " . mysqli_error($conn);
    }

    // Close the statement
    mysqli_stmt_close($stmt);
} else {
    $_SESSION['message'] = "No record ID specified.";
}

// Close the database connection
mysqli_close($conn);

// Redirect back to the display page
header("Location: display_illmed.php");
exit();
?>
