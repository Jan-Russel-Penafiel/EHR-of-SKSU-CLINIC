<?php
// forgot_password_backend.php

header('Content-Type: application/json');
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$GmailAccount = $data['GmailAccount'];
$newPassword = $data['newPassword'];

// Validate input
if (empty($GmailAccount) || empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'Please provide both Gmail account and new password.']);
    exit;
}

// Check if Gmail account ends with @sksu.edu.ph
if (!preg_match('/^[a-zA-Z0-9._%+-]+@sksu\.edu\.ph$/', $GmailAccount)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email. Must end with @sksu.edu.ph']);
    exit;
}

// Check password length
if (strlen($newPassword) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
    exit;
}

// Check if Gmail account exists in the database
$query = "SELECT * FROM personal_info_accounts WHERE GmailAccount = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $GmailAccount);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Account exists, update password without hashing
    $updateQuery = "UPDATE personal_info_accounts SET Password = ? WHERE GmailAccount = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param('ss', $newPassword, $GmailAccount);

    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password successfully reset!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update the password. Please try again.']);
    }

    $updateStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Gmail account not found.']);
}

$stmt->close();
$conn->close();
?>
