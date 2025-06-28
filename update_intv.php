<?php
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

// Check if request is POST and 'Num' is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Num'])) {
    // Sanitize and assign POST data to variables
    $num = $conn->real_escape_string($_POST['Num']);
    $whatYouDo = $conn->real_escape_string($_POST['what_you_do']);
    $existingDisease = $conn->real_escape_string($_POST['existing_disease']);
    $familyHistory = $conn->real_escape_string($_POST['family_history']);
    $allergies = $conn->real_escape_string($_POST['allergies']);

    // Update query
    $sql = "UPDATE intv 
            SET  what_you_do = '$whatYouDo', 
                what_is_your_existing_desease = '$existingDisease', 
                have_you_a_family_history_desease = '$familyHistory', 
                have_you_a_allergy = '$allergies'
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
