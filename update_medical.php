<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ehrdb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "error" => "Database connection failed: " . $conn->connect_error]));
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data from the POST request
    $num = $_POST['Num'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $heartrate = $_POST['heartrate'];
    $bloodpressure = $_POST['bloodpressure'];
    $temperature = $_POST['temperature'];

    // SQL query to update the medical record
    $sql = "UPDATE medical_history SET
                height = ?,
                weight = ?,
                heartrate = ?,
                bloodpressure = ?,
                temperature = ?
            WHERE Num = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // Bind parameters to the SQL query
        $stmt->bind_param("sssssi", $height, $weight, $heartrate, $bloodpressure, $temperature, $num);

        // Execute the query
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "Failed to execute update: " . $stmt->error]);
        }
        
        // Close statement
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "error" => "Failed to prepare statement: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}

// Close the database connection
$conn->close();
?>
