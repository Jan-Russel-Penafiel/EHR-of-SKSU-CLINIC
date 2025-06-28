<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idNumber = $_POST['idNumber'];

    // Database connection
    $conn = new mysqli("localhost", "root", "", "ehrdb");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch user information including GmailAccount
    $sqlUserInfo = "SELECT * FROM personal_info WHERE IDNumber = '$idNumber'";
    $userInfo = $conn->query($sqlUserInfo)->fetch_assoc();

    if (!$userInfo) {
        die("No user found with the provided ID Number.");
    }

    // Extract the Gmail account to use as the recipient address
    $recipientEmail = $userInfo['GmailAccount'];

    // Fetch only prescription records
    $sqlTreatments = "SELECT * FROM illmed WHERE IDNumber = '$idNumber'";
    $resultIllmed = $conn->query($sqlTreatments);

    // Prepare email content
    $message = "<h1>Prescription Reminder</h1>";
    $message .= "<p><strong>Name:</strong> {$userInfo['FirstName']} {$userInfo['LastName']}</p>";
    $message .= "<p><strong>Reminder:</strong> Please take the prescribed medication as per the details below.</p>";

// Prepare email content
$message = "<h1 style='color: #4CAF50;'>Prescription Reminder</h1>";
$message .= "<p style='font-size: 16px;'>Dear {$userInfo['FirstName']} {$userInfo['LastName']},</p>";
$message .= "<p style='font-size: 16px;'>This is a reminder to follow the prescribed medication details below. Please take the necessary steps as advised by your healthcare provider.</p>";

if ($resultIllmed && $resultIllmed->num_rows > 0) {
    $message .= "<h3 style='color: #333;'>Prescription Details:</h3>";
    $message .= "<table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>";
    $message .= "<thead><tr><th style='border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2; text-align: left;'>Illness</th>";
    $message .= "<th style='border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2; text-align: left;'>Medication</th>";
    $message .= "<th style='border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2; text-align: left;'>Prescription</th>";
    $message .= "<th style='border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2; text-align: left;'>Temperature</th>";
    $message .= "<th style='border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2; text-align: left;'>Blood Pressure</th>";
    $message .= "<th style='border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2; text-align: left;'>Appointment Date</th></tr></thead>";

    while ($row = $resultIllmed->fetch_assoc()) {
        $message .= "<tr style='border-bottom: 1px solid #ddd;'>";
        $message .= "<td style='border: 1px solid #ddd; padding: 8px;'>{$row['IllName']}</td>";
        $message .= "<td style='border: 1px solid #ddd; padding: 8px;'>{$row['MedName']}</td>";
        $message .= "<td style='border: 1px solid #ddd; padding: 8px;'>{$row['Prescription']}</td>";
        $message .= "<td style='border: 1px solid #ddd; padding: 8px;'>{$row['Temperature']} °C</td>";
        $message .= "<td style='border: 1px solid #ddd; padding: 8px;'>{$row['BloodPressure']} mmHg</td>";
        $message .= "<td style='border: 1px solid #ddd; padding: 8px;'>" . date("F d, Y", strtotime($row['Appointment_Date'])) . "</td>";
        $message .= "</tr>";
    }
    $message .= "</table>";
} else {
    $message .= "<p style='font-size: 16px;'>No prescription records found for your ID Number.</p>";
}

$message .= "<p style='font-size: 16px;'>Please ensure to follow the prescribed instructions carefully. If you have any questions, feel free to contact your healthcare provider.</p>";
$message .= "<p style='font-size: 16px;'>Thank you, and stay healthy!</p>";

    // Setup PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sksuisulanschoolclinic@gmail.com';
        $mail->Password = 'ukti coep ddhn tzhh'; // App-specific password if using 2FA
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Set recipient to the GmailAccount from personal_info
        $mail->setFrom('sksuisulanschoolclinic@gmail.com', 'School Clinic');
        $mail->addAddress($recipientEmail);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Prescription Reminder';
        $mail->Body = $message;

        $mail->send();
        echo "Prescription details sent successfully to $recipientEmail!";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

    // Close connection
    $conn->close();
}
?>
