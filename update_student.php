<?php
header('Content-Type: application/json');

// Database connection (replace with your actual connection details)
$servername = "localhost"; // Your database server
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "ehrdb"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check for database connection errors
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Check if request is POST and 'Num' is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Num'])) {
    // Sanitize and assign POST data to variables
    $num = intval($_POST['Num']); // Assuming Num is an integer
    $idNumber = $conn->real_escape_string($_POST['IDNumber']); // Added IDNumber
    $firstName = $conn->real_escape_string($_POST['firstName']);
    $lastName = $conn->real_escape_string($_POST['lastName']);
    $gmail = $conn->real_escape_string($_POST['gmail']);

    // Update query
    $sql = "UPDATE personal_info 
            SET IDNumber = '$idNumber', 
                FirstName = '$firstName', 
                LastName = '$lastName', 
                GmailAccount = '$gmail' 
            WHERE Num = '$num'";

    // Execute query
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Update failed: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

// Close connection
$conn->close();
?>
