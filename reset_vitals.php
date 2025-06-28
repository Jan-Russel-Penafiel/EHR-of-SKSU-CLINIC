<?php
    $conn = mysqli_connect("localhost", "root", "", "ehrdb");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentNum = $_POST['student_num'];

    // Update the vitals back to normal values
    $query = "
        UPDATE illmed 
        SET Temperature = 36, BloodPressure = '120/80' 
        WHERE Num = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $studentNum);
    
    if ($stmt->execute()) {
        // Redirect back to the alert page or display a success message
        header("Location: alert.php?success=VitalsReset");
    } else {
        // Handle error case
        echo "Error resetting vitals: " . $stmt->error;
    }

    $stmt->close();
}

mysqli_close($conn);
?>
