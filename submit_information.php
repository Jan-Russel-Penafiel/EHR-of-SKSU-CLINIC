<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Check if the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $firstName = mysqli_real_escape_string($conn, $_POST['FirstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['LastName']);
    $idNumber = mysqli_real_escape_string($conn, $_POST['IDNumber']);
    $gmailAccount = mysqli_real_escape_string($conn, $_POST['GmailAccount']);
    $month = mysqli_real_escape_string($conn, $_POST['Month']);
    $day = mysqli_real_escape_string($conn, $_POST['Day']);
    $year = mysqli_real_escape_string($conn, $_POST['Year']);
    $birthdate = "$year-$month-$day"; // Format: YYYY-MM-DD
    $gender = mysqli_real_escape_string($conn, $_POST['Gender']);
    $course = mysqli_real_escape_string($conn, $_POST['Course']);
    $yearLevel = mysqli_real_escape_string($conn, $_POST['Yr']);
    $section = mysqli_real_escape_string($conn, $_POST['Section']);
   
    // Check if GmailAccount already exists
    $checkEmailQuery = "SELECT GmailAccount FROM personal_info WHERE GmailAccount = '$gmailAccount'";
    $result = mysqli_query($conn, $checkEmailQuery);

    if (mysqli_num_rows($result) > 0) {
        // GmailAccount already exists, redirect back to form.php with an error message
        header("Location: login.html?error=duplicate_email please");
        exit();
    } else {
        // Handle the profile picture upload
        $targetDir = "downloads/"; // Ensure this directory exists and is writable
        $targetFile = $targetDir . basename($_FILES["ProfilePicture"]["name"]);
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        
        // Check if image file is a valid image
        $check = getimagesize($_FILES["ProfilePicture"]["tmp_name"]);
        if ($check === false) {
            header("Location: form.php?error=invalid_image");
            exit();
        }
        
        // Allow certain file formats
        if (!in_array($imageFileType, ["jpg", "png", "jpeg", "gif"])) {
            header("Location: form.php?error=invalid_format");
            exit();
        }
        
        // Upload file
        if (!move_uploaded_file($_FILES["ProfilePicture"]["tmp_name"], $targetFile)) {
            header("Location: form.php?error=upload_failed");
            exit();
        }
        
        // Calculate age
        $birthDateObj = new DateTime($birthdate);
        $currentDate = new DateTime();
        $age = $currentDate->diff($birthDateObj)->y;

        // Insert the data into the database, including the profile picture path
        $sql = "INSERT INTO personal_info (FirstName, LastName, IDNumber, GmailAccount, Birthdate, Age, Gender, Course, Yr, Section, ProfilePicture)
                VALUES ('$firstName', '$lastName', '$idNumber', '$gmailAccount', '$birthdate', '$age', '$gender', '$course', '$yearLevel', '$section', '$targetFile')";

        if (mysqli_query($conn, $sql)) {
            $_SESSION['IDNumber'] = $idNumber;
            $_SESSION['GmailAccount'] = $gmailAccount;

            // Redirect to display_student_information.php after a successful insert
            header("Location: display_student_information.php");
            exit();
        } else {
            header("Location: form.php?error=insert_failed");
            exit();
        }
    }
   
    
}
?>

