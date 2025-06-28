<?php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Check connection
if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()])); 
}

// Get the input JSON
$data = json_decode(file_get_contents("php://input"));

if (isset($data->GmailAccount) && isset($data->password)) {
    $GmailAccount = mysqli_real_escape_string($conn, $data->GmailAccount);
    $password = mysqli_real_escape_string($conn, $data->password);

    // Query to select the user from the accounts table
    $query = "SELECT * FROM faculty_accounts WHERE GmailAccount = '$GmailAccount' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Check if the password matches
        $row = mysqli_fetch_assoc($result);
        if ($row['Password'] === $password) {
            // User found and password matches, start session and log in
            session_start();
            $_SESSION['GmailAccount'] = $GmailAccount; // Store GmailAccount in session

            // Send success response back to JavaScript
            echo json_encode(['success' => true]);
        } else {
            // Send error response for invalid password
            echo json_encode(['success' => false, 'message' => 'Invalid password.']);
        }
    } else {
        // Send error response if Gmail account is not registered
        echo json_encode(['success' => false, 'message' => 'Account not registered.']);
    }

    // Close the database connection
    mysqli_close($conn);
} else {
    // Send error response if GmailAccount or password not set
    echo json_encode(['success' => false, 'message' => 'Gmail account and password are required.']);
}

?>
