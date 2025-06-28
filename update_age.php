<?php
// Include database connection
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

// Fetch all records from the personal_info table
$query = "SELECT * FROM personal_info";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Calculate the new age
        $birthdate = $row['Birthdate'];
        $newAge = date_diff(date_create($birthdate), date_create('now'))->y; // Calculate age

        // Update the age in the database
        $updateQuery = "UPDATE personal_info SET Age = '$newAge' WHERE IDNumber = '" . $row['IDNumber'] . "'";
        mysqli_query($conn, $updateQuery);
    }
}

// Close the database connection
mysqli_close($conn);
?>
