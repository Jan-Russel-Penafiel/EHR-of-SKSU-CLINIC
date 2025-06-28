<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if a file was uploaded
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == UPLOAD_ERR_OK) {
        $idNumber = $_SESSION['IDNumber'];
        $gmailAccount = $_SESSION['GmailAccount'];

        // Specify the directory to save the uploaded files
        $uploadDir = 'downloads/';
        $fileName = uniqid() . '_' . basename($_FILES['profilePicture']['name']); // Unique file name
        $targetFilePath = $uploadDir . $fileName;

        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $targetFilePath)) {
            // Update the profile picture path in the personal_info table
            $sqlPersonalInfo = "UPDATE personal_info SET ProfilePicture = ? WHERE IDNumber = ? AND GmailAccount = ?";
            $stmtPersonalInfo = mysqli_prepare($conn, $sqlPersonalInfo);
            mysqli_stmt_bind_param($stmtPersonalInfo, "sss", $fileName, $idNumber, $gmailAccount);
            mysqli_stmt_execute($stmtPersonalInfo);

            // Check if the faculty table should be updated
            // You can set a condition to check if the user is a faculty member, e.g.:
            $isFaculty = isset($_SESSION['isFaculty']) ? $_SESSION['isFaculty'] : false;

            if (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $targetFilePath)) {
                $sqlFaculty = "UPDATE faculty SET ProfilePicture = ? WHERE IDNumber = ? AND GmailAccount = ?";
                $stmtFaculty = mysqli_prepare($conn, $sqlFaculty);
                mysqli_stmt_bind_param($stmtFaculty, "ss", $fileName, $idNumber);
                mysqli_stmt_execute($stmtFaculty);
            }

            // Check if updates were successful
            if (mysqli_stmt_affected_rows($stmtPersonalInfo) > 0 || ($isFaculty && mysqli_stmt_affected_rows($stmtFaculty) > 0)) {
                echo "Profile picture uploaded successfully!";
            } else {
                echo "Failed to update profile picture in the database.";
            }
        } else {
            echo "Error moving uploaded file.";
        }
    } else {
        echo "No file uploaded or there was an upload error.";
    }
}
?>
