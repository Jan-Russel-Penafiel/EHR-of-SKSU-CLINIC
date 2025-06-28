<?php
// Enable error reporting for debugging (optional, disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

header('Content-Type: application/json');

// Decode the incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Check if required fields are provided
if (!isset($data['IDNumber'], $data['GmailAccount'], $data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
    exit;
}

// Sanitize input data
$IDNumber = mysqli_real_escape_string($conn, $data['IDNumber']);
$GmailAccount = mysqli_real_escape_string($conn, $data['GmailAccount']);
$password = mysqli_real_escape_string($conn, $data['password']); // No hashing

try {
    // Check if GmailAccount already exists
    $checkQuery = "SELECT * FROM faculty_accounts WHERE GmailAccount = '$GmailAccount'";
    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['success' => false, 'message' => 'This Gmail account is already registered.']);
        exit;
    }

    // Insert new faculty account into the database
    $insertQuery = "INSERT INTO faculty_accounts (IDNumber, GmailAccount, password) VALUES ('$IDNumber', '$GmailAccount', '$password')"; // No hashed password

    if (mysqli_query($conn, $insertQuery)) {
        echo json_encode(['success' => true, 'message' => 'Account created successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create account.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Close the database connection
mysqli_close($conn);
?>
