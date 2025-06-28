<?php
header('Content-Type: application/json');
$conn = mysqli_connect("localhost", "root", "", "ehrdb");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Path to the PHPMailer's autoloader

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$GmailAccount = $data['GmailAccount'];

// Validate input
if (empty($GmailAccount)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a Gmail account.']);
    exit;
}

// Check if Gmail account ends with @sksu.edu.ph
if (!preg_match('/^[a-zA-Z0-9._%+-]+@sksu\.edu\.ph$/', $GmailAccount)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email. Must end with @sksu.edu.ph']);
    exit;
}

// Check if Gmail account exists in the database
$query = "SELECT * FROM personal_info_accounts WHERE GmailAccount = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $GmailAccount);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Account exists, generate OTP
    $otp = rand(100000, 999999);  
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes')); // OTP expiry time (15 minutes from now)
    
    // Update OTP and expiry in the database
    $updateQuery = "UPDATE personal_info_accounts SET otp = ?, otp_expiry = ? WHERE GmailAccount = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param('sss', $otp, $otp_expiry, $GmailAccount);
    
    if ($updateStmt->execute()) {
        // Send OTP to Gmail account using PHPMailer
        $emailSubject = "Your OTP for password reset";
        $emailBody = "Your OTP for resetting your password is: $otp. It will expire in 15 minutes.";

        // PHPMailer setup
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'sksuisulanschoolclinic@gmail.com'; // Use your Gmail address
            $mail->Password = 'ukti coep ddhn tzhh'; // Use generated app-specific password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('sksuisulanschoolclinic@gmail.com', 'School Clinic');
            $mail->addAddress($GmailAccount); // Recipient's Gmail

            // Content
            $mail->isHTML(true);
            $mail->Subject = $emailSubject;
            $mail->Body    = $emailBody;

            // Send the email
            $mail->send();
            
            echo json_encode(['success' => true, 'message' => 'OTP sent to your Gmail account.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to send OTP. Error: ' . $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to generate OTP. Please try again.']);
    }
    
    $updateStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Gmail account not found.']);
}

$stmt->close();
$conn->close();
?>
