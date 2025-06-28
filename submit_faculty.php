<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Check if the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve form data
    $rank = mysqli_real_escape_string($conn, $_POST['Rank']);
    $firstName = mysqli_real_escape_string($conn, $_POST['FirstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['LastName']);
    $idNumber = mysqli_real_escape_string($conn, $_POST['IDNumber']);
    $gmailAccount = mysqli_real_escape_string($conn, $_POST['GmailAccount']);
    $department = mysqli_real_escape_string($conn, $_POST['Department']);
    $position = mysqli_real_escape_string($conn, $_POST['Position']);

    // Check if GmailAccount already exists
    $checkEmailQuery = "SELECT GmailAccount FROM faculty WHERE GmailAccount = '$gmailAccount'";
    $result = mysqli_query($conn, $checkEmailQuery);

    if (mysqli_num_rows($result) > 0) {
        // Redirect back to form_faculty.php with an error message
        header("Location: login_faculty.html?error=duplicate_email");
        exit();
    } else {
        // Handle the profile picture upload
        $targetDir = "downloads/"; // Ensure this directory exists and is writable
        $targetFile = $targetDir . basename($_FILES["ProfilePicture"]["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if image file is a valid image
        $check = getimagesize($_FILES["ProfilePicture"]["tmp_name"]);
        if ($check === false) {
            header("Location: form_faculty.php?error=invalid_image");
            exit();
        }

        // Allow only certain file formats
        if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
            header("Location: form_faculty.php?error=invalid_format");
            exit();
        }

        // Upload file
        if (!move_uploaded_file($_FILES["ProfilePicture"]["tmp_name"], $targetFile)) {
            header("Location: form_faculty.php?error=upload_failed");
            exit();
        }

        // Insert the data into the database, including the profile picture path
        $sql = "INSERT INTO faculty (Rank,FirstName, LastName, IDNumber, GmailAccount, Department, Position, ProfilePicture)
                VALUES ('$rank','$firstName', '$lastName', '$idNumber', '$gmailAccount', '$department', '$position', '$targetFile')";

        if (mysqli_query($conn, $sql)) {
            // Set session variables
            $_SESSION['IDNumber'] = $idNumber;
            $_SESSION['GmailAccount'] = $gmailAccount;

            // Redirect to faculty_information.php after a successful insert
            header("Location: faculty_information.php");
            exit();
        } else {
            header("Location: form_faculty.php?error=insert_failed");
            exit();
        }
    }


}
?>
