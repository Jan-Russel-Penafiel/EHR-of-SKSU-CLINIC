// verify_otp.php
<?php 
header('Content-Type: application/json');
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$GmailAccount = $data['GmailAccount'];
$otp = $data['otp'];
$newPassword = $data['newPassword'];

// Validate input
if (empty($GmailAccount) || empty($otp) || empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'Please provide Gmail account, OTP, and new password.']);
    exit;
}

// Check password length
if (strlen($newPassword) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
    exit;
}

// Check if Gmail account exists and OTP is valid and not expired
$query = "SELECT * FROM accounts WHERE GmailAccount = ? AND otp = ? AND otp_expiry > NOW()";
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $GmailAccount, $otp);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // OTP is valid and not expired, update the password
    $updateQuery = "UPDATE accounts SET password = ?, otp = NULL, otp_expiry = NULL WHERE GmailAccount = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param('ss', $newPassword, $GmailAccount);

    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password successfully reset!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reset the password. Please try again.']);
    }

    $updateStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP or OTP has expired.']);
}

$stmt->close();
$conn->close();
?>