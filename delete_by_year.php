<?php
// Start the session and connect to the database
session_start();
$conn = mysqli_connect("localhost", "root", "", "ehrdb");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the year from the POST request
if (isset($_POST['year'])) {
    $year = mysqli_real_escape_string($conn, $_POST['year']);

    // Prepare and execute the delete query
    $query = "DELETE FROM personal_info_accounts WHERE years = '$year'";
    if (mysqli_query($conn, $query)) {
        echo "All accounts for the year $year have been deleted.";
    } else {
        echo "Error deleting accounts: " . mysqli_error($conn);
    }
} else {
    echo "Year not specified.";
}

// Close the database connection
mysqli_close($conn);
?>
