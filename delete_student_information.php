<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Check if the 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

  

    // Prepare the SQL DELETE statement for the medical_history table
    $deleteMedicalQuery = "DELETE FROM medical_history WHERE Num = ?";
    $deleteMedicalStmt = mysqli_prepare($conn, $deleteMedicalQuery);
    mysqli_stmt_bind_param($deleteMedicalStmt, "i", $id);
    
    // Execute the statement to delete related medical history records
    mysqli_stmt_execute($deleteMedicalStmt);
    mysqli_stmt_close($deleteMedicalStmt);

    // Prepare the SQL DELETE statement for the personal_info table
    $query = "DELETE FROM personal_info WHERE Num = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);

    // Execute the statement to delete the record
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
header("Location: student_information.php");
exit();
?>
