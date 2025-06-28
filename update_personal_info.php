<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if form data has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $idNumber = mysqli_real_escape_string($conn, $_POST['IDNumber']);
    $gmailAccount = mysqli_real_escape_string($conn, $_POST['GmailAccount']);
    $password = mysqli_real_escape_string($conn, $_POST['Password']);
    $years = mysqli_real_escape_string($conn, $_POST['Years']);

    // Update query for personal_info_accounts table
    $updateAccountsQuery = "
        UPDATE personal_info_accounts 
        SET IDNumber = '$idNumber', Password = '$password', years = '$years' 
        WHERE GmailAccount = '$gmailAccount'
    ";

    // Update query for personal_info table
    $updatePersonalInfoQuery = "
        UPDATE personal_info 
        SET IDNumber = '$idNumber', years = '$years' 
        WHERE GmailAccount = '$gmailAccount'
    ";

    // Execute queries
    $success = true;

    // Update personal_info_accounts table
    if (!mysqli_query($conn, $updateAccountsQuery)) {
        echo "Error updating personal_info_accounts: " . mysqli_error($conn);
        $success = false;
    }

    // Update personal_info table
    if (!mysqli_query($conn, $updatePersonalInfoQuery)) {
        echo "Error updating personal_info: " . mysqli_error($conn);
        $success = false;
    }

    // Redirect if both updates were successful
    if ($success) {
        header("Location: account.php?update=success");
        exit();
    }
}

// Close the connection
mysqli_close($conn);
?>
