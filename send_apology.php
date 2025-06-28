<?php
    $conn = mysqli_connect("localhost", "root", "", "ehrdb");
    session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$gmailAccount = $_SESSION['GmailAccount'] ?? null;
$errorMessage = "";
$successMessage = "";

// Function to send apology email
function sendApologyEmail($toEmail, $firstName, $lastName) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sksuisulanschoolclinic@gmail.com'; // Replace with actual Gmail
        $mail->Password = 'ukti coep ddhn tzhh';            // Replace with actual App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Set email details
        $mail->setFrom('sksuisulanschoolclinic@gmail.com', 'School Clinic');
        $mail->addAddress($toEmail, "$firstName $lastName");
        $mail->isHTML(true);
        $mail->Subject = 'Apology for Appointment Cancellation';
        $mail->Body = "
        Dear $firstName $lastName,<br><br>
    
      Dear $firstName $lastName,<br><br>

We regret to inform you that we are unable to schedule your appointment at this time. This may be due to our schedule being fully booked or the reason for the appointment not meeting the criteria set for booking. We sincerely apologize for any inconvenience this may cause.<br><br>

If you have any questions or require assistance, please don't hesitate to reach out to us. We encourage you to try scheduling again next week, and we will do our best to accommodate you.<br><br>

We truly appreciate your understanding and patience.<br><br>

Warm regards,<br>
School Clinic Team

    ";
    
    
    
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}

// Ensure Gmail account exists in session
if (!$gmailAccount) {
    $errorMessage = "It seems that you have not logging in Admin. Please login!";
} elseif (isset($_POST['selected_appointments']) || isset($_POST['selected_faculty_appointments'])) {
    $selectedAppointments = $_POST['selected_appointments'] ?? [];
    $selectedFacultyAppointments = $_POST['selected_faculty_appointments'] ?? [];

    // Combine selected appointment IDs
    $selectedIds = array_merge($selectedAppointments, $selectedFacultyAppointments);
    
    // Process each selected appointment
    foreach ($selectedIds as $Num) {
        // Fetch user information associated with the appointment
        $queryUser = "
            SELECT pi.IDNumber, pi.FirstName, pi.LastName, pi.GmailAccount
            FROM personal_info pi
            JOIN appointments a ON a.IDNumber = pi.IDNumber
            WHERE a.Num = '$Num'
            UNION 
            SELECT f.IDNumber, f.FirstName, f.LastName, f.GmailAccount
            FROM faculty f
            JOIN faculty_appointments fa ON fa.IDNumber = f.IDNumber
            WHERE fa.Num = '$Num'
        ";
        $result = mysqli_query($conn, $queryUser);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($user = mysqli_fetch_assoc($result)) {
                $firstName = $user['FirstName'];
                $lastName = $user['LastName'];
                $toEmail = $user['GmailAccount']; // Use the user's Gmail account

                // Send the apology email
                if (sendApologyEmail($toEmail, $firstName, $lastName)) {
                    $successMessage .= "Apology email sent successfully to $firstName $lastName.<br>";

                    // Delete the appointment after sending the email
                    $deleteQuery = "DELETE FROM appointments WHERE Num = '$Num'";
                    if (mysqli_query($conn, $deleteQuery)) {
                        $successMessage .= "Appointment deleted successfully.<br>";
                    } else {
                        $errorMessage .= "Failed to delete appointment <br>";
                    }

                    // Delete from faculty_appointment table
$deleteFacultyQuery = "DELETE FROM faculty_appointments WHERE Num = '$Num'";
if (mysqli_query($conn, $deleteFacultyQuery)) {
    $successMessage .= "Faculty appointment deleted successfully.<br>";
} else {
    $errorMessage .= "Failed to delete faculty appointment number <br>";
}
                } else {
                    $errorMessage .= "Failed to send apology email to $firstName $lastName.<br>";
                }
            }
        } else {
            $errorMessage .= "No user found for the selected appointment <br>";
        }
    }
}

// Display success or error messages
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Notification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Clean font */
            background-color: rgba(173, 216, 230, 0.50);
            margin: 0;
            padding: 20px;
        }

        body {
            background-image: url(image.jpeg);
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: rgba(173, 216, 230, 0.50);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            font-size: 1.1em;
            position: relative;
            transition: all 0.3s ease;
        }

        .success {
            background-color: #e6ffe6;
            border: 1px solid #4CAF50;
            color: #4CAF50;
        }

        .error {
            background-color: #ffe6e6;
            border: 1px solid #f44336;
            color: #f44336;
        }

        .redirect-message {
            text-align: center;
            margin-top: 20px;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Appointment Notification</h1>

        <?php if ($successMessage): ?>
            <div class="message success">
                <p><?php echo $successMessage; ?></p>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="message error">
                <p><?php echo $errorMessage; ?></p>
            </div>
        <?php endif; ?>

        <div class="redirect-message">
            <p>You will be redirected shortly...</p>
        </div>
    </div>

    <script>
        // Redirect after 3 seconds
        setTimeout(function() {
            window.location.href = 'display_online_appointment.php';
        }, 2000);
    </script>
</body>
</html>
