<?php
// Include database connection file
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Set response header to JSON
header('Content-Type: application/json');

// Initialize response array
$response = ['success' => false, 'message' => ''];

try {
    // Check if the POST data is set
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve and sanitize POST data
        $num = intval($_POST['Num']);
        $idNumber = mysqli_real_escape_string($conn, $_POST['IDNumber']);
        $firstName = mysqli_real_escape_string($conn, $_POST['FirstName']);
        $lastName = mysqli_real_escape_string($conn, $_POST['LastName']);
        $gmailAccount = mysqli_real_escape_string($conn, $_POST['GmailAccount']);

        // Validate required fields
        if (empty($num) || empty($idNumber) || empty($firstName) || empty($lastName) || empty($gmailAccount)) {
            throw new Exception("Missing required fields.");
        }

        // Prepare and execute the update query (without Rank, Department, and Position)
        $sql = "UPDATE faculty SET 
                    IDNumber = '$idNumber',
                    FirstName = '$firstName',
                    LastName = '$lastName',
                    GmailAccount = '$gmailAccount'
                WHERE Num = $num";

        if (mysqli_query($conn, $sql)) {
            $response['success'] = true;
            $response['message'] = "Record updated successfully.";
        } else {
            throw new Exception("Error updating record: " . mysqli_error($conn));
        }
    } else {
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Return the response as JSON
echo json_encode($response);

// Close the database connection
mysqli_close($conn);
?>
