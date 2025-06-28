<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb");
if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . mysqli_connect_error()]));
}

if (isset($_POST['GmailAccount']) && isset($_POST['table'])) {
    $gmailAccount = mysqli_real_escape_string($conn, $_POST['GmailAccount']);
    $table = mysqli_real_escape_string($conn, $_POST['table']);

    // Check table name to prevent SQL injection
    if ($table === 'personal_info_accounts' || $table === 'faculty_accounts') {
        // Delete query
        $query = "DELETE FROM $table WHERE GmailAccount = '$gmailAccount' LIMIT 1";
        $result = mysqli_query($conn, $query);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting account: ' . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid table name.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request parameters.']);
}

mysqli_close($conn);
?>
