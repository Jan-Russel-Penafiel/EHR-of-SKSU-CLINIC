<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");
require 'vendor/autoload.php'; // Adjust the path as necessary

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendConfirmationEmail($email, $firstName, $lastName, $idNumber, $studentNum) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'penafielliezl1122@gmail.com';
        $mail->Password = 'jryi njov tqmd ogct'; // Use environment variables in production
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        // Recipients
        $mail->setFrom('penafielliezl1122@gmail.com', 'School Clinic Alert');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Health Status Update for $firstName $lastName (Student ID: $idNumber)";
        $mail->Body = nl2br("✅ Condition Marked Okay for <strong>$firstName $lastName</strong> (ID: $idNumber");

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_num'])) {
    $studentNum = $_POST['student_num'];

    // Update the status in your database
    $updateQuery = "UPDATE illmed SET Status = 'Okay' WHERE Num = '$studentNum'";
    mysqli_query($conn, $updateQuery);

    // Fetch user details to send confirmation email
    $userQuery = "SELECT FirstName, LastName, IDNumber, GmailAccount FROM personal_info WHERE Num = '$studentNum'";
    $result = mysqli_query($conn, $userQuery);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $firstName = $row['FirstName'];
        $lastName = $row['LastName'];
        $idNumber = $row['IDNumber'];
        $email = $row['GmailAccount'];

        // Send confirmation email
        sendConfirmationEmail($email, $firstName, $lastName, $idNumber, $studentNum);
    }
}

// Redirect back to alerts page
header("Location: alert.php");
exit();
?>
