<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $idNumber = $_POST['id_number'];
    $studentNum = $_POST['student_num'];
    $alertMessage = $_POST['alert_message'];

    if (sendAlertEmail($email, $firstName, $lastName, $idNumber, $studentNum, $alertMessage)) {
        $_SESSION['success_message'] = "Alert sent successfully to $firstName $lastName.";
        $_SESSION['alert_sent_' . $studentNum] = true; // Track if alert has been sent
    } else {
        $_SESSION['error_message'] = "Failed to send alert to $firstName $lastName.";
    }

    // Redirect back to the alert page with the status message
    header("Location: alert.php");
    exit();
}

function sendAlertEmail($email, $firstName, $lastName, $idNumber, $studentNum, $alertMessage) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sksuisulanschoolclinic@gmail.com';
        $mail->Password = 'ukti coep ddhn tzhh'; // Use environment variables in production
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('sksuisulanschoolclinic@gmail.com', 'School Clinic Alert');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Alert for $firstName $lastName (Student ID: $idNumber)";
        $mail->Body = nl2br($alertMessage);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    } finally {
        $mail->clearAddresses();
    }
}
?>
