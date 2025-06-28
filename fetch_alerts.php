<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

function getIllnessMedicationAlerts($conn) {
    // Same query as before
}

$resultIllMed = getIllnessMedicationAlerts($conn);
$alerts = [];

if (mysqli_num_rows($resultIllMed) > 0) {
    while ($row = mysqli_fetch_assoc($resultIllMed)) {
        // Create alerts similar to the previous logic
        // ...
        $alerts[] = $alertMessage;
    }
}

echo json_encode($alerts); // Return alerts as JSON
mysqli_close($conn);
