<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON
header('Content-Type: application/json');

// Database connection (replace with your actual connection details)
$servername = "localhost";
$username = "root";
$password = "";
$database = "ehrdb";

$conn = new mysqli($servername, $username, $password, $database);

// Check for database connection errors
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Check if the request is POST and necessary data is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Num'])) {
    // Sanitize and assign POST data to variables
    $num = $conn->real_escape_string($_POST['Num']);
    $illName = $conn->real_escape_string($_POST['IllName']);
    $medName = $conn->real_escape_string($_POST['MedName']);
    $prescription = $conn->real_escape_string($_POST['Prescription']);
    $temperature = $conn->real_escape_string($_POST['Temperature']);
    $bloodPressure = $conn->real_escape_string($_POST['BloodPressure']);
    $status = $conn->real_escape_string($_POST['Status']);
    $appointmentDate = $conn->real_escape_string($_POST['Appointment_Date']);

    // Update query to modify the record in the database, including Prescription
    $sql = "UPDATE illmed 
            SET IllName = '$illName', MedName = '$medName', 
                Prescription = '$prescription', Temperature = '$temperature', 
                BloodPressure = '$bloodPressure', Status = '$status', 
                Appointment_Date = '$appointmentDate'
            WHERE Num = '$num'";

    // Execute the query and check if successful
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Update failed: ' . $conn->error]);
    }
} else {
    // Respond with error if request method is not POST or 'Num' is missing
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

// Close database connection
$conn->close();
?>
