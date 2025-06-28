<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Assuming PHPMailer is installed using Composer

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Use your mail server
        $mail->SMTPAuth = true;
        $mail->Username = 'sksuisulanschoolclinic@gmail.com'; // Your email address
        $mail->Password = 'ukti coep ddhn tzhh'; // Your email password (use app-specific password if 2FA is enabled)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('sksuisulanschoolclinic@gmail.com', 'Your Name');
        $mail->addAddress($to); // Send OTP to the user's Gmail account

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>