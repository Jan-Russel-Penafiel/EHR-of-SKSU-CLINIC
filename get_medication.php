<?php
header('Content-Type: application/json');
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

if (!isset($_GET['illName']) || empty(trim($_GET['illName']))) {
    echo json_encode(['error' => 'Invalid illness name']);
    exit();
}

$illName = mysqli_real_escape_string($conn, $_GET['illName']);
$query = "SELECT MedName FROM illmed WHERE IllName = ?";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    echo json_encode(['error' => 'Failed to prepare query']);
    exit();
}

mysqli_stmt_bind_param($stmt, "s", $illName);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$medications = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $medications[] = $row['MedName'];
    }
}

echo json_encode(['medications' => $medications]);
mysqli_close($conn);
?>
