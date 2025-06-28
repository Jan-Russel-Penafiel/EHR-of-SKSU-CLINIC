<?php
// Assuming you have a database connection established already
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

if (isset($_POST['date'])) {
    $date = $_POST['date'];
    
    // Delete the unavailable date from the nurse_schedule table
    $stmt = $conn->prepare("DELETE FROM nurse_schedule WHERE unavailable_date = ?");
    $stmt->bind_param("s", $date);

    if ($stmt->execute()) {
        echo "Unavailable date deleted successfully.";
    } else {
        echo "Error deleting the unavailable date.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
