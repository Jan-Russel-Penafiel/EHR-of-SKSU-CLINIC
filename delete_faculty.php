<?php
session_start(); // Start session to use session variables
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Check if the 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    $facultyId = intval($_GET['id']); // Use intval to ensure it’s an integer

    // Prepare the SQL DELETE statement
    $query = "DELETE FROM faculty WHERE Num = ?"; // Assuming 'Num' is the primary key in your table
    $stmt = mysqli_prepare($conn, $query);

    // Bind the parameter to the statement
    mysqli_stmt_bind_param($stmt, "i", $facultyId);

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
header("Location: display_faculty.php"); // Adjusted to redirect to faculty_list.php
exit();
?>
