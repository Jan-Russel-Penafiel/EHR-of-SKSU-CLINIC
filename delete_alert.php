<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

if (isset($_POST['student_num'])) {
    $studentNum = $_POST['student_num'];

    // Query to delete the record from the illmed table based on student number
    $deleteQuery = "DELETE FROM illmed WHERE Num = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("s", $studentNum); // assuming Num is a string; adjust as necessary

    if ($stmt->execute()) {
        // Success message or redirect back to alerts page
        $_SESSION['message'] = "Alert for student number $studentNum has been deleted successfully.";
    } else {
        $_SESSION['error'] = "Error deleting alert: " . $conn->error;
    }

    $stmt->close();
}

mysqli_close($conn); // Close the database connection
header("Location: alert.php"); // Redirect back to the alerts page
exit();
?>
