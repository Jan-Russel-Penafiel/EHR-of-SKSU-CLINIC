<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if request is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve data from the request
    $table = $_POST['editTable'];
    $num = $_POST['editNum'];
    $idNumber = $_POST['IDNumber'];
    $gmailAccount = $_POST['GmailAccount'];
    $password = $_POST['Password'];
    $years = $_POST['years'];

    // Validate inputs
    if (empty($table) || empty($num) || empty($idNumber) || empty($gmailAccount) || empty($password) || empty($years)) {
        echo "All fields are required.";
        exit;
    }

    // Prepare update query
    $table = mysqli_real_escape_string($conn, $table);
    $updateQuery = "UPDATE $table SET 
                    IDNumber = ?, 
                    GmailAccount = ?, 
                    Password = ?, 
                    years = ? 
                    WHERE Num = ?";

    $stmt = mysqli_prepare($conn, $updateQuery);
    if ($stmt) {
        // Bind parameters
        mysqli_stmt_bind_param($stmt, "ssssi", $idNumber, $gmailAccount, $password, $years, $num);
        // Execute the statement
        if (mysqli_stmt_execute($stmt)) {
            echo "Account updated successfully.";
        } else {
            echo "Error updating account: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing statement: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request method.";
}

// Close database connection
mysqli_close($conn);
?>
